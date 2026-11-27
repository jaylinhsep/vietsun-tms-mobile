<?php
/**
 * VIETSUN VENDOR & DRIVER HUB - API GATEWAY (/api/index.php)
 * Quản lý xác thực, đồng bộ Google Sheet Đội xe Vendor, đồng bộ 2 chiều Vietsun TMS,
 * quản lý Vendor, Tài xế/Số xe, Báo cáo doanh thu & Chi phí phát sinh.
 */
error_reporting(0);
@ini_set('memory_limit', '512M');
@ini_set('pcre.backtrack_limit', '10000000');
@set_time_limit(120);
date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$route = isset($_GET['route']) ? trim($_GET['route'], '/') : '';
if (strpos($route, '?') !== false) {
    $route = explode('?', $route)[0];
}
if (!$route) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $route = preg_replace('#^/api(/index\.php)?/?#', '', $uriPath);
    $route = trim($route, '/');
}

$rawBody = file_get_contents('php://input');
$jsonBody = json_decode($rawBody, true);
if (!is_array($jsonBody)) {
    $jsonBody = $_POST;
}

$authHeader = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('getallheaders')) {
    foreach (getallheaders() as $k => $v) {
        if (strtolower($k) === 'authorization') {
            $authHeader = $v;
            break;
        }
    }
}

$DB_HOST = '127.0.0.1';
$DB_USER = 'vietsun_vendor';
$DB_PASS = 'huyhoang106';
$DB_NAME = 'vietsun_vendor';
$JWT_SECRET = 'vsho_tms_secret_key_2026_@vietsun';
$REMOTE_BASE = 'https://vsho.vietsunlogistics.com';
$COOKIE_FILE = sys_get_temp_dir() . '/vietsun_tms_cookie.txt';
$SHEET_CSV_URL = 'https://docs.google.com/spreadsheets/d/13ndQsSmBsKlHjIRQR0S0MO5BPqqL6J4CECBAYfQi1oo/export?format=csv&gid=1046738475';

function get_db() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $pdo->exec("SET time_zone = '+07:00'");
    }
    return $pdo;
}

// Chuyển chuỗi tiếng Việt có dấu / khoảng trắng -> VIẾT LIỀN KHÔNG DẤU IN HOA
function to_clean_code($str) {
    $str = trim($str ?? '');
    $unicode = [
        'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd'=>'đ',
        'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i'=>'í|ì|ỉ|ĩ|ị',
        'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
        'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'D'=>'Đ',
        'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
        'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
    ];
    foreach ($unicode as $nonUnicode => $uni) {
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $str));
}

// Chuẩn hóa tên ĐVVT / Vendor để tránh xung đột giữa viết liền và có dấu cách (VD: PHUCTRUONGAN -> PHUC TRUONG AN)
function normalize_vendor_name($name) {
    $clean = trim((string)$name);
    if ($clean === '') return '';
    $code = to_clean_code($clean);

    static $map = [
        'PHUCTRUONGAN' => 'PHUC TRUONG AN',
        'PHUCAN'       => 'PHUC AN',
        'TUANHUNG'     => 'TUAN HUNG',
        'VUONGPHAT'    => 'VUONG PHAT',
        'NHATSON'      => 'NHAT SON',
        'NGOCSANG'     => 'NGOC SANG',
        'GIAKHANG'     => 'GIA KHANG',
        'DUCANH'       => 'ANH DUC',
        'HOANGLONGPHAT'=> 'HOANG LONG PHAT',
        'DAILONGPHAT'  => 'DAI LONG PHAT',
        'BAONAMPHAT'   => 'BAO NAM PHAT',
        'ANTHANHPHAT'  => 'AN THANH PHAT',
        'DUYLINHPHAT'  => 'DUY LINH PHAT',
        'NHANBAOPHAT'  => 'NHAN BAO PHAT',
        'THIENPHUCPHAT'=> 'THIEN PHUC PHAT',
        'TANHOANGQUANG'=> 'TAN HOANG QUANG',
        'MAINHATANH'   => 'MAI NHAT ANH',
        'DUONGNGUYEN'  => 'DUONG NGUYEN',
        'GIABAO'       => 'GIA BAO',
        'DUCHAI'       => 'DUC HAI',
        'DANGHUNG'     => 'DANG HUNG',
        'TRUCANH'      => 'TRUC ANH'
    ];
    return $map[$code] ?? $clean;
}

// Kiểm tra số container theo chuẩn quốc tế ISO 6346
function validate_container_iso($contStr) {
    if (!$contStr) return ['valid' => false, 'message' => 'Chưa nhập'];
    $csc = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string)$contStr));
    if (strlen($csc) !== 11) {
        return ['valid' => false, 'message' => 'Độ dài ' . strlen($csc) . '/11 ký tự'];
    }
    $charMap = [
        'A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19, 'J' => 20,
        'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29, 'S' => 30, 'T' => 31,
        'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38
    ];
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $ch = $csc[$i];
        if ($ch >= '0' && $ch <= '9') {
            $val = (int)$ch;
        } elseif (isset($charMap[$ch])) {
            $val = $charMap[$ch];
        } else {
            return ['valid' => false, 'message' => "Ký tự $ch không hợp lệ"];
        }
        $sum += $val * (1 << $i);
    }
    $check = ($sum % 11) % 10;
    $lastDigit = (int)$csc[10];
    if ($check === $lastDigit) {
        return ['valid' => true, 'message' => 'Chuẩn ISO ✓'];
    }
    return ['valid' => false, 'message' => "Sai số cuối (đúng: $check)"];
}

// Tự động nâng cấp cấu trúc bảng để hỗ trợ:
// - Số xe (assigned_plate) là UNIQUE
// - Nhiều xe có thể dùng chung SĐT tài xế
// - Lưu thêm CCCD, GPLX
function ensure_schema($pdo) {
    $flagFile = sys_get_temp_dir() . '/vietsun_schema_v13.flag';
    if (file_exists($flagFile)) {
        return;
    }

    // 1. Đảm bảo bảng tms_pending_approvals, tms_bb_templates, tms_tt_status, driver_assignment_history và trip_driver_snapshots tồn tại
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `driver_assignment_history` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `assigned_plate` VARCHAR(32) NOT NULL,
                `vendor_id` INT NOT NULL DEFAULT 0,
                `vendor_name` VARCHAR(120) NOT NULL DEFAULT '',
                `driver_name` VARCHAR(120) NOT NULL,
                `phone` VARCHAR(30) NULL,
                `cccd` VARCHAR(100) NULL,
                `gplx` VARCHAR(100) NULL,
                `assigned_from` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `assigned_to` DATETIME NULL DEFAULT NULL,
                `changed_by` VARCHAR(64) NOT NULL DEFAULT 'SYSTEM',
                `note` VARCHAR(255) NULL DEFAULT NULL,
                INDEX `idx_plate_dates` (`assigned_plate`, `assigned_from`, `assigned_to`),
                INDEX `idx_driver_name` (`driver_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `trip_driver_snapshots` (
                `id_po` VARCHAR(32) PRIMARY KEY,
                `code_po` VARCHAR(64) NOT NULL DEFAULT '',
                `trip_date` VARCHAR(20) NOT NULL DEFAULT '',
                `xuat_nhap` VARCHAR(10) NOT NULL DEFAULT '',
                `cont_size` VARCHAR(10) NOT NULL DEFAULT '20',
                `khach_kho` VARCHAR(255) NOT NULL DEFAULT '',
                `so_cont` VARCHAR(32) NOT NULL DEFAULT '',
                `assigned_plate` VARCHAR(32) NOT NULL,
                `driver_name` VARCHAR(120) NOT NULL,
                `driver_phone` VARCHAR(30) NOT NULL DEFAULT '',
                `driver_cccd` VARCHAR(100) NOT NULL DEFAULT '',
                `driver_gplx` VARCHAR(100) NOT NULL DEFAULT '',
                `vendor_name` VARCHAR(120) NOT NULL DEFAULT '',
                `locked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_plate_tripdate` (`assigned_plate`, `trip_date`),
                INDEX `idx_driver_tripdate` (`driver_name`, `trip_date`),
                INDEX `idx_vendor_tripdate` (`vendor_name`, `trip_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `tms_tt_status` (
                `id_po` VARCHAR(32) PRIMARY KEY,
                `is_checked` TINYINT(1) NULL DEFAULT NULL,
                `note_po` VARCHAR(100) NULL DEFAULT NULL,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $colNoteChk = $pdo->query("SHOW COLUMNS FROM `tms_tt_status` LIKE 'note_po'")->fetch();
        if (!$colNoteChk) {
            $pdo->exec("ALTER TABLE `tms_tt_status` MODIFY `is_checked` TINYINT(1) NULL DEFAULT NULL");
            $pdo->exec("ALTER TABLE `tms_tt_status` ADD COLUMN `note_po` VARCHAR(100) NULL DEFAULT NULL AFTER `is_checked`");
        }
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `tms_pending_approvals` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `id_po` VARCHAR(32) NOT NULL,
                `code_po` VARCHAR(64) NOT NULL DEFAULT '',
                `trip_date` VARCHAR(20) NOT NULL DEFAULT '',
                `xuat_nhap` VARCHAR(10) NOT NULL DEFAULT '',
                `cont_size` VARCHAR(10) NOT NULL DEFAULT '',
                `khach_kho` VARCHAR(255) NOT NULL DEFAULT '',
                `actor_role` VARCHAR(20) NOT NULL,
                `actor_name` VARCHAR(120) NOT NULL,
                `vendor_name` VARCHAR(120) NOT NULL DEFAULT '',
                `changes_json` TEXT NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'PENDING',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `reviewed_at` DATETIME NULL,
                INDEX `idx_po_status` (`id_po`, `status`),
                INDEX `idx_status_created` (`status`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $colChk = $pdo->query("SHOW COLUMNS FROM `tms_pending_approvals` LIKE 'cont_size'")->fetch();
        if (!$colChk) {
            $pdo->exec("ALTER TABLE `tms_pending_approvals` ADD COLUMN `cont_size` VARCHAR(10) NOT NULL DEFAULT '' AFTER `xuat_nhap`");
        }

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `tms_bb_templates` (
                `id` VARCHAR(64) PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `short_badge` VARCHAR(64) NOT NULL DEFAULT 'Form Riêng',
                `customer_keywords` TEXT NULL,
                `keywords` TEXT NOT NULL,
                `html_content` MEDIUMTEXT NOT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT NOT NULL DEFAULT 10,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $colCustChk = $pdo->query("SHOW COLUMNS FROM `tms_bb_templates` LIKE 'customer_keywords'")->fetch();
        if (!$colCustChk) {
            $pdo->exec("ALTER TABLE `tms_bb_templates` ADD COLUMN `customer_keywords` TEXT NULL AFTER `short_badge`");
        }
    } catch (Exception $e) {}

    // 2. Nâng cấp cấu trúc các bảng phụ (chạy một lần)
    try { $pdo->exec("ALTER TABLE `drivers` DROP INDEX `phone`"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` MODIFY `phone` VARCHAR(30) NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` MODIFY `pin_code` VARCHAR(100) NOT NULL DEFAULT '123456'"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `cccd` VARCHAR(100) NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `gplx` VARCHAR(100) NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` ADD UNIQUE INDEX `idx_assigned_plate` (`assigned_plate`)"); } catch (Exception $e) {}

    try {
        $pdo->exec("DELETE FROM `vendors` WHERE `vendor_code` = 'HLP'");
        $pdo->exec("UPDATE `vendors` SET `password_hash` = '123456' WHERE `password_hash` IN ('hlp@123', 'tranco@123', 'vp@123')");
    } catch (Exception $e) {}

    // 3. Dọn sạch vendor và xe nhà VIETSUN nếu còn sót trong DB & chuẩn hóa tên Vendor (chạy một lần)
    try {
        $stFindVietsun = $pdo->query("SELECT id FROM vendors WHERE vendor_code = 'VIETSUN'")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($stFindVietsun)) {
            $inClause = implode(',', array_map('intval', $stFindVietsun));
            $pdo->exec("DELETE FROM drivers WHERE vendor_id IN ($inClause)");
            $pdo->exec("DELETE FROM vendors WHERE id IN ($inClause)");
        }
        $vendorFixes = [
            'PHUCTRUONGAN'  => 'PHUC TRUONG AN',
            'TUANHUNG'      => 'TUAN HUNG',
            'PHUCAN'        => 'PHUC AN',
            'VUONGPHAT'     => 'VUONG PHAT',
            'NHATSON'       => 'NHAT SON',
            'NGOCSANG'      => 'NGOC SANG',
            'GIAKHANG'      => 'GIA KHANG',
            'DUCANH'        => 'ANH DUC',
            'HOANGLONGPHAT' => 'HOANG LONG PHAT',
            'DAILONGPHAT'   => 'DAI LONG PHAT',
            'BAONAMPHAT'    => 'BAO NAM PHAT',
            'ANTHANHPHAT'   => 'AN THANH PHAT',
            'DUYLINHPHAT'   => 'DUY LINH PHAT',
            'NHANBAOPHAT'   => 'NHAN BAO PHAT',
            'THIENPHUCPHAT' => 'THIEN PHUC PHAT',
            'TANHOANGQUANG' => 'TAN HOANG QUANG',
            'MAINHATANH'    => 'MAI NHAT ANH',
            'DUONGNGUYEN'   => 'DUONG NGUYEN',
            'GIABAO'        => 'GIA BAO',
            'DUCHAI'        => 'DUC HAI',
            'DANGHUNG'      => 'DANG HUNG',
            'TRUCANH'       => 'TRUC ANH'
        ];
        $stUpdV  = $pdo->prepare("UPDATE `vendors` SET `vendor_name` = ? WHERE `vendor_code` = ? OR REPLACE(UPPER(`vendor_name`), ' ', '') = ?");
        $stUpdP1 = $pdo->prepare("UPDATE `tms_pending_approvals` SET `vendor_name` = ? WHERE REPLACE(UPPER(`vendor_name`), ' ', '') = ?");
        $stUpdP2 = $pdo->prepare("UPDATE `tms_pending_approvals` SET `actor_name` = ? WHERE REPLACE(UPPER(`actor_name`), ' ', '') = ?");
        $stUpdP3 = $pdo->prepare("UPDATE `tms_pending_approvals` SET `changes_json` = REPLACE(`changes_json`, ?, ?)");

        foreach ($vendorFixes as $vCode => $vFull) {
            $stUpdV->execute([$vFull, $vCode, $vCode]);
            $stUpdP1->execute([$vFull, $vCode]);
            $stUpdP2->execute([$vFull, $vCode]);
            $stUpdP3->execute(['"' . $vCode . '"', '"' . $vFull . '"']);
        }

        $stPendActor = $pdo->query("SELECT id, actor_name FROM tms_pending_approvals WHERE actor_role = 'VENDOR'")->fetchAll();
        foreach ($stPendActor as $pa) {
            $normActor = normalize_vendor_name($pa['actor_name']);
            if ($normActor !== $pa['actor_name']) {
                $pdo->prepare("UPDATE tms_pending_approvals SET actor_name = ?, vendor_name = ? WHERE id = ?")->execute([$normActor, $normActor, $pa['id']]);
            }
        }

        $stPendClean = $pdo->query("SELECT id, changes_json FROM tms_pending_approvals WHERE status = 'PENDING' AND changes_json LIKE '%donViVcPo%'")->fetchAll();
        foreach ($stPendClean as $pc) {
            $cj = json_decode($pc['changes_json'], true);
            if (is_array($cj) && isset($cj['donViVcPo'])) {
                $oldN = normalize_vendor_name($cj['donViVcPo']['old'] ?? '');
                $newN = normalize_vendor_name($cj['donViVcPo']['new'] ?? '');
                if ($oldN !== '' && to_clean_code($oldN) === to_clean_code($newN)) {
                    unset($cj['donViVcPo']);
                    $pdo->prepare("UPDATE tms_pending_approvals SET changes_json = ? WHERE id = ?")->execute([json_encode($cj, JSON_UNESCAPED_UNICODE), $pc['id']]);
                }
            }
        }

        // 4. Khởi tạo lịch sử bàn giao xe ban đầu từ bảng drivers & khôi phục snapshot lịch sử từ tms_pending_approvals
        $histCnt = (int)$pdo->query("SELECT COUNT(*) FROM `driver_assignment_history`")->fetchColumn();
        if ($histCnt === 0) {
            $curDrivers = $pdo->query("
                SELECT d.*, v.vendor_name
                FROM drivers d
                LEFT JOIN vendors v ON d.vendor_id = v.id
            ")->fetchAll();
            $stInsHist = $pdo->prepare("
                INSERT INTO `driver_assignment_history`
                (`assigned_plate`, `vendor_id`, `vendor_name`, `driver_name`, `phone`, `cccd`, `gplx`, `assigned_from`, `assigned_to`, `changed_by`, `note`)
                VALUES (?, ?, ?, ?, ?, ?, ?, '2026-01-01 00:00:00', NULL, 'INIT', 'Khởi tạo từ danh sách đội xe')
            ");
            foreach ($curDrivers as $cd) {
                $p = to_clean_code($cd['assigned_plate'] ?? '');
                $dn = strtoupper(trim($cd['driver_name'] ?? ''));
                if ($p && $dn) {
                    $stInsHist->execute([
                        $p,
                        (int)($cd['vendor_id'] ?? 0),
                        normalize_vendor_name($cd['vendor_name'] ?? ''),
                        $dn,
                        trim($cd['phone'] ?? ''),
                        trim($cd['cccd'] ?? ''),
                        trim($cd['gplx'] ?? '')
                    ]);
                }
            }
        }

        // Khôi phục snapshot các chuyến đã từng được tài xế gửi duyệt trong tms_pending_approvals (bảo toàn tên tài xế cũ đã chạy)
        $snapCnt = (int)$pdo->query("SELECT COUNT(*) FROM `trip_driver_snapshots`")->fetchColumn();
        if ($snapCnt === 0) {
            $pastDrvApprovals = $pdo->query("
                SELECT * FROM `tms_pending_approvals`
                WHERE `actor_role` = 'DRIVER' AND `status` IN ('APPROVED', 'PENDING')
                ORDER BY `id` ASC
            ")->fetchAll();
            $stSnapInit = $pdo->prepare("
                INSERT INTO `trip_driver_snapshots`
                (`id_po`, `code_po`, `trip_date`, `xuat_nhap`, `cont_size`, `khach_kho`, `so_cont`, `assigned_plate`, `driver_name`, `vendor_name`, `locked_at`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `driver_name` = VALUES(`driver_name`),
                    `assigned_plate` = VALUES(`assigned_plate`),
                    `so_cont` = IF(VALUES(`so_cont`) != '', VALUES(`so_cont`), `so_cont`)
            ");
            foreach ($pastDrvApprovals as $pa) {
                $parts = explode('-', (string)$pa['actor_name'], 2);
                $pClean = to_clean_code($parts[0] ?? '');
                $dNameSnap = strtoupper(trim($parts[1] ?? ($pa['actor_name'] ?? '')));
                if (!$pClean || !$dNameSnap) continue;
                $cj = json_decode($pa['changes_json'] ?? '{}', true) ?: [];
                $soContSnap = strtoupper(trim($cj['soContPo']['new'] ?? ''));
                $stSnapInit->execute([
                    (string)$pa['id_po'],
                    (string)($pa['code_po'] ?? ''),
                    (string)($pa['trip_date'] ?? ''),
                    (string)($pa['xuat_nhap'] ?? ''),
                    (string)($pa['cont_size'] ?: '20'),
                    (string)($pa['khach_kho'] ?? ''),
                    $soContSnap,
                    $pClean,
                    $dNameSnap,
                    normalize_vendor_name($pa['vendor_name'] ?? ''),
                    $pa['created_at'] ?: date('Y-m-d H:i:s')
                ]);
            }
        }
    } catch (Exception $e) {}

    @file_put_contents($flagFile, time());
}

// Ghi nhận lịch sử khi đổi tài xế trên cùng một biển số xe & cập nhật các yêu cầu đang PENDING
function record_driver_assignment_change($pdo, $plate, $vendorId, $newDriverName, $phone, $cccd, $gplx, $changedBy = 'ADMIN') {
    $cleanPlate = to_clean_code($plate);
    $cleanNewName = strtoupper(trim($newDriverName));
    if (!$cleanPlate || !$cleanNewName) return;

    try {
        $vName = '';
        if ($vendorId > 0) {
            $stV = $pdo->prepare("SELECT vendor_name FROM vendors WHERE id = ? LIMIT 1");
            $stV->execute([$vendorId]);
            $vName = normalize_vendor_name($stV->fetchColumn() ?: '');
        }

        // Kiểm tra bản ghi đang active (assigned_to IS NULL) của biển số này
        $stCur = $pdo->prepare("
            SELECT * FROM `driver_assignment_history`
            WHERE `assigned_plate` = ? AND `assigned_to` IS NULL
            ORDER BY `id` DESC LIMIT 1
        ");
        $stCur->execute([$cleanPlate]);
        $curHist = $stCur->fetch();

        if (!$curHist) {
            // Nếu chưa có trong lịch sử, kiểm tra trong bảng drivers hiện tại trước khi ghi đè
            $stOldDrv = $pdo->prepare("
                SELECT d.*, v.vendor_name
                FROM drivers d
                LEFT JOIN vendors v ON d.vendor_id = v.id
                WHERE d.assigned_plate = ? LIMIT 1
            ");
            $stOldDrv->execute([$cleanPlate]);
            $oldDrv = $stOldDrv->fetch();
            if ($oldDrv) {
                $oldName = strtoupper(trim($oldDrv['driver_name'] ?? ''));
                if ($oldName && $oldName !== $cleanNewName) {
                    // Lưu lại giai đoạn của tài xế cũ đã kết thúc tại thời điểm bàn giao
                    $stInsOld = $pdo->prepare("
                        INSERT INTO `driver_assignment_history`
                        (`assigned_plate`, `vendor_id`, `vendor_name`, `driver_name`, `phone`, `cccd`, `gplx`, `assigned_from`, `assigned_to`, `changed_by`, `note`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, '2026-01-01 00:00:00', NOW(), ?, 'Tài xế cũ trước khi bàn giao xe')
                    ");
                    $stInsOld->execute([
                        $cleanPlate,
                        (int)($oldDrv['vendor_id'] ?? $vendorId),
                        normalize_vendor_name($oldDrv['vendor_name'] ?? $vName),
                        $oldName,
                        trim($oldDrv['phone'] ?? ''),
                        trim($oldDrv['cccd'] ?? ''),
                        trim($oldDrv['gplx'] ?? ''),
                        $changedBy
                    ]);
                }
            }

            // Mở bản ghi mới cho tài xế hiện tại
            $stInsNew = $pdo->prepare("
                INSERT INTO `driver_assignment_history`
                (`assigned_plate`, `vendor_id`, `vendor_name`, `driver_name`, `phone`, `cccd`, `gplx`, `assigned_from`, `assigned_to`, `changed_by`, `note`)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NULL, ?, 'Bắt đầu nhận xe')
            ");
            $stInsNew->execute([$cleanPlate, $vendorId, $vName, $cleanNewName, $phone, $cccd, $gplx, $changedBy]);
        } else {
            $oldHistName = strtoupper(trim($curHist['driver_name'] ?? ''));
            if ($oldHistName !== $cleanNewName) {
                // Đóng giai đoạn của tài xế cũ
                $pdo->prepare("UPDATE `driver_assignment_history` SET `assigned_to` = NOW() WHERE `id` = ?")->execute([$curHist['id']]);
                // Tạo giai đoạn mới cho tài xế mới nhận xe
                $stInsNew = $pdo->prepare("
                    INSERT INTO `driver_assignment_history`
                    (`assigned_plate`, `vendor_id`, `vendor_name`, `driver_name`, `phone`, `cccd`, `gplx`, `assigned_from`, `assigned_to`, `changed_by`, `note`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NULL, ?, ?)
                ");
                $stInsNew->execute([
                    $cleanPlate,
                    $vendorId,
                    $vName,
                    $cleanNewName,
                    $phone,
                    $cccd,
                    $gplx,
                    $changedBy,
                    "Thay thế tài xế cũ: $oldHistName"
                ]);
            } else {
                // Cùng một tài xế nhưng cập nhật SĐT / CCCD / GPLX / Nhà xe
                $stUpdSame = $pdo->prepare("
                    UPDATE `driver_assignment_history`
                    SET `vendor_id` = ?, `vendor_name` = ?, `phone` = ?, `cccd` = ?, `gplx` = ?
                    WHERE `id` = ?
                ");
                $stUpdSame->execute([$vendorId, $vName, $phone, $cccd, $gplx, $curHist['id']]);
            }
        }

        // CHỈ cập nhật tên tài xế mới cho các yêu cầu ĐANG CHỜ DUYỆT (status = 'PENDING') của biển số xe này
        // Các yêu cầu ĐÃ DUYỆT (status = 'APPROVED') trong quá khứ được GIỮ NGUYÊN 100% tên tài xế cũ đã chạy!
        $newActorLabel = "$cleanPlate - $cleanNewName";
        $stUpdPend = $pdo->prepare("
            UPDATE `tms_pending_approvals`
            SET `actor_name` = ?, `vendor_name` = IF(? != '', ?, `vendor_name`)
            WHERE `actor_role` = 'DRIVER'
              AND `status` = 'PENDING'
              AND (`actor_name` LIKE ? OR `actor_name` = ?)
        ");
        $stUpdPend->execute([$newActorLabel, $vName, $vName, $cleanPlate . ' -%', $cleanPlate]);

        // Cập nhật snapshot của riêng ngày hôm nay (nếu chuyến hôm nay đang PENDING hoặc vừa gán lại tài xế mới trong ngày)
        $todayIso = date('Y-m-d');
        $stUpdSnapToday = $pdo->prepare("
            UPDATE `trip_driver_snapshots`
            SET `driver_name` = ?, `driver_phone` = ?, `driver_cccd` = ?, `driver_gplx` = ?, `vendor_name` = IF(? != '', ?, `vendor_name`)
            WHERE `assigned_plate` = ? AND `trip_date` >= ?
              AND `id_po` IN (SELECT `id_po` FROM `tms_pending_approvals` WHERE `status` = 'PENDING')
        ");
        $stUpdSnapToday->execute([$cleanNewName, $phone, $cccd, $gplx, $vName, $vName, $cleanPlate, $todayIso]);
    } catch (Exception $e) {}
}

// Chốt cứng (Snapshot) thông tin tài xế thực chạy vào chuyến hàng để phục vụ tính lương lâu dài
function lock_trip_driver_snapshot($pdo, $idPo, $plate, $driverName = '', $driverPhone = '', $driverCccd = '', $driverGplx = '', $vendorName = '', $tripObj = null) {
    $idPo = trim((string)$idPo);
    $cleanPlate = to_clean_code($plate);
    if ($idPo === '' || $cleanPlate === '') return;

    try {
        if ($driverName === '') {
            $stD = $pdo->prepare("
                SELECT d.driver_name, d.phone, d.cccd, d.gplx, v.vendor_name
                FROM drivers d
                LEFT JOIN vendors v ON d.vendor_id = v.id
                WHERE d.assigned_plate = ? LIMIT 1
            ");
            $stD->execute([$cleanPlate]);
            $dRow = $stD->fetch();
            if ($dRow) {
                $driverName  = strtoupper(trim($dRow['driver_name'] ?? ''));
                $driverPhone = trim($dRow['phone'] ?? '');
                $driverCccd  = trim($dRow['cccd'] ?? '');
                $driverGplx  = trim($dRow['gplx'] ?? '');
                if ($vendorName === '') {
                    $vendorName = normalize_vendor_name($dRow['vendor_name'] ?? '');
                }
            }
        }
        if ($driverName === '') return;

        $codePo   = (string)($tripObj['codePo'] ?? '');
        $tripDate = $tripObj ? get_shipment_date_iso($tripObj) : date('Y-m-d');
        $xuatNhap = (string)($tripObj['xuatNhapPo'] ?? '');
        $contSize = ((int)($tripObj['cont40Po'] ?? 0) > 0) ? '40' : '20';
        $khachKho = (string)(($tripObj['khoNhanHangPo'] ?? '') ?: ($tripObj['tenKhachPo'] ?? ''));
        $soCont   = strtoupper(trim((string)($tripObj['soContPo'] ?? '')));

        $stSnap = $pdo->prepare("
            INSERT INTO `trip_driver_snapshots`
            (`id_po`, `code_po`, `trip_date`, `xuat_nhap`, `cont_size`, `khach_kho`, `so_cont`, `assigned_plate`, `driver_name`, `driver_phone`, `driver_cccd`, `driver_gplx`, `vendor_name`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                `code_po`        = IF(VALUES(`code_po`) != '', VALUES(`code_po`), `code_po`),
                `trip_date`      = IF(VALUES(`trip_date`) != '', VALUES(`trip_date`), `trip_date`),
                `xuat_nhap`      = IF(VALUES(`xuat_nhap`) != '', VALUES(`xuat_nhap`), `xuat_nhap`),
                `cont_size`      = IF(VALUES(`cont_size`) != '', VALUES(`cont_size`), `cont_size`),
                `khach_kho`      = IF(VALUES(`khach_kho`) != '', VALUES(`khach_kho`), `khach_kho`),
                `so_cont`        = IF(VALUES(`so_cont`) != '', VALUES(`so_cont`), `so_cont`),
                `assigned_plate` = VALUES(`assigned_plate`),
                `driver_name`    = VALUES(`driver_name`),
                `driver_phone`   = IF(VALUES(`driver_phone`) != '', VALUES(`driver_phone`), `driver_phone`),
                `driver_cccd`    = IF(VALUES(`driver_cccd`) != '', VALUES(`driver_cccd`), `driver_cccd`),
                `driver_gplx`    = IF(VALUES(`driver_gplx`) != '', VALUES(`driver_gplx`), `driver_gplx`),
                `vendor_name`    = IF(VALUES(`vendor_name`) != '', VALUES(`vendor_name`), `vendor_name`)
        ");
        $stSnap->execute([
            $idPo, $codePo, $tripDate, $xuatNhap, $contSize, $khachKho, $soCont,
            $cleanPlate, strtoupper(trim($driverName)), $driverPhone, $driverCccd, $driverGplx, normalize_vendor_name($vendorName)
        ]);
    } catch (Exception $e) {}
}

// Tìm đúng tài xế đã chạy chuyến hàng (Ưu tiên: 1. Snapshot đã chốt -> 2. Lịch sử bàn giao theo ngày chuyến -> 3. Tài xế hiện tại)
function resolve_driver_for_trip($idPo, $plate, $tripDateIso, &$snapshotsMap, &$historyByPlate, &$currentFleetMap) {
    $pid = (string)$idPo;
    $cleanPlate = to_clean_code($plate);
    if ($cleanPlate === '') return null;

    // 1. Nếu chuyến đã được chốt snapshot với chính biển số xe này -> trả về đúng tài xế đã chốt
    if (isset($snapshotsMap[$pid])) {
        $snap = $snapshotsMap[$pid];
        if (to_clean_code($snap['assigned_plate'] ?? '') === $cleanPlate && !empty($snap['driver_name'])) {
            return [
                'plate'  => $cleanPlate,
                'name'   => $snap['driver_name'],
                'phone'  => $snap['driver_phone'] ?? '',
                'cccd'   => $snap['driver_cccd'] ?? '',
                'gplx'   => $snap['driver_gplx'] ?? '',
                'source' => 'SNAPSHOT'
            ];
        }
    }

    // 2. Đối chiếu theo mốc thời gian bàn giao xe trong driver_assignment_history
    if ($tripDateIso !== '' && isset($historyByPlate[$cleanPlate])) {
        $tripDayEnd   = $tripDateIso . ' 23:59:59';
        $tripDayStart = $tripDateIso . ' 00:00:00';
        foreach ($historyByPlate[$cleanPlate] as $h) {
            $from = $h['assigned_from'] ?? '2000-01-01 00:00:00';
            $to   = $h['assigned_to'] ?? null;
            if ($from <= $tripDayEnd && ($to === null || $to >= $tripDayStart)) {
                return [
                    'plate'  => $cleanPlate,
                    'name'   => $h['driver_name'] ?? '',
                    'phone'  => $h['phone'] ?? '',
                    'cccd'   => $h['cccd'] ?? '',
                    'gplx'   => $h['gplx'] ?? '',
                    'source' => 'HISTORY'
                ];
            }
        }
    }

    // 3. Mặc định lấy tài xế đang phụ trách xe hiện tại
    if (isset($currentFleetMap[$cleanPlate])) {
        $cur = $currentFleetMap[$cleanPlate];
        return [
            'plate'  => $cleanPlate,
            'name'   => $cur['name'] ?? '',
            'phone'  => $cur['phone'] ?? '',
            'cccd'   => $cur['cccd'] ?? '',
            'gplx'   => $cur['gplx'] ?? '',
            'source' => 'CURRENT'
        ];
    }
    return null;
}

// Đồng bộ trực tiếp từ Google Sheet Đội xe Vendor (loại trừ xe nhà VIETSUN)
function sync_from_google_sheet($pdo) {
    global $SHEET_CSV_URL;
    ensure_schema($pdo);

    // Xóa vendor VIETSUN và đội xe nhà VIETSUN nếu có
    try {
        $stFindVietsun = $pdo->query("SELECT id FROM vendors WHERE vendor_code = 'VIETSUN'")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($stFindVietsun)) {
            $inClause = implode(',', array_map('intval', $stFindVietsun));
            $pdo->exec("DELETE FROM drivers WHERE vendor_id IN ($inClause)");
            $pdo->exec("DELETE FROM vendors WHERE id IN ($inClause)");
        }
    } catch (Exception $e) {}

    $rows = [];

    // Kéo trực tiếp CSV từ Google Sheet
    $ch = curl_init($SHEET_CSV_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (VietsunSyncHub/1.0)'
    ]);
    $csvText = curl_exec($ch);
    curl_close($ch);

    if ($csvText && strlen($csvText) > 20) {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvText));
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') continue;
            $cols = str_getcsv($line);
            if (count($cols) < 2) continue;
            $vName = normalize_vendor_name(strtoupper(trim($cols[0] ?? '')));
            $plate = to_clean_code($cols[1] ?? '');
            if (!$vName || !$plate) continue;
            $vCode = to_clean_code($vName);
            // Bỏ qua xe VIETSUN (xe nhà, không phải Vendor)
            if ($vCode === 'VIETSUN') continue;
            $dName = trim($cols[2] ?? '');
            $phone = trim($cols[3] ?? '');
            $cccd  = trim($cols[4] ?? '');
            $gplx  = trim($cols[5] ?? '');
            $rows[] = [$vName, $plate, $dName, $phone, $cccd, $gplx];
        }
    }

    $vendorCount = 0;
    $driverCount = 0;
    $vendorMap = []; // vendor_code => id

    // Lấy các vendor hiện có
    $existingVendors = $pdo->query("SELECT id, vendor_code, vendor_name FROM vendors")->fetchAll();
    foreach ($existingVendors as $ev) {
        $vendorMap[$ev['vendor_code']] = $ev['id'];
    }

    $stmtInsertVendor = $pdo->prepare("INSERT INTO vendors (vendor_name, vendor_code, password_hash, is_active) VALUES (?, ?, '123456', 1) ON DUPLICATE KEY UPDATE vendor_name = VALUES(vendor_name)");
    $stmtUpsertDriver = $pdo->prepare("
        INSERT INTO drivers (vendor_id, driver_name, phone, pin_code, assigned_plate, cccd, gplx, is_active)
        VALUES (?, ?, ?, '123456', ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE
            vendor_id = VALUES(vendor_id),
            driver_name = IF(VALUES(driver_name) != '', VALUES(driver_name), driver_name),
            phone = IF(VALUES(phone) != '', VALUES(phone), phone),
            cccd = IF(VALUES(cccd) != '', VALUES(cccd), cccd),
            gplx = IF(VALUES(gplx) != '', VALUES(gplx), gplx)
    ");

    foreach ($rows as $r) {
        list($vName, $plate, $dName, $phone, $cccd, $gplx) = $r;
        $vName = normalize_vendor_name($vName);
        $vCode = to_clean_code($vName);
        if (!$vCode || !$plate) continue;

        if (!isset($vendorMap[$vCode])) {
            $stmtInsertVendor->execute([$vName, $vCode]);
            $vid = $pdo->lastInsertId();
            if (!$vid) {
                $st = $pdo->prepare("SELECT id FROM vendors WHERE vendor_code = ? LIMIT 1");
                $st->execute([$vCode]);
                $vid = $st->fetchColumn();
            }
            $vendorMap[$vCode] = $vid;
            $vendorCount++;
        } else {
            // Luôn cập nhật vendor_name theo tên chuẩn mới nhất từ Google Sheet
            $vid = $vendorMap[$vCode];
            $stUpdateVName = $pdo->prepare("UPDATE vendors SET vendor_name = ? WHERE id = ?");
            $stUpdateVName->execute([$vName, $vid]);
        }

        $vid = $vendorMap[$vCode];
        if ($vid) {
            $finalDName = strtoupper(trim($dName ?: "Tài xế $plate"));
            record_driver_assignment_change($pdo, $plate, $vid, $finalDName, $phone, $cccd, $gplx, 'SHEET_SYNC');
            $stmtUpsertDriver->execute([$vid, $finalDName, $phone, $plate, $cccd, $gplx]);
            $driverCount++;
        }
    }

    return [
        'totalVendors' => count($vendorMap),
        'syncedDrivers' => $driverCount
    ];
}

function make_token($payload) {
    global $JWT_SECRET;
    $header = rtrim(strtr(base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'])), '+/', '-_'), '=');
    $payload['exp'] = time() + 86400 * 30;
    $body = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $sig = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$body", $JWT_SECRET, true)), '+/', '-_'), '=');
    return "$header.$body.$sig";
}

function verify_token($authHeader) {
    global $JWT_SECRET;
    if (!preg_match('/Bearer\s+(.+)/i', $authHeader, $m)) return null;
    $parts = explode('.', trim($m[1]));
    if (count($parts) !== 3) return null;
    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    if (!$payload) return null;
    return $payload;
}

function vietsun_login() {
    global $REMOTE_BASE, $COOKIE_FILE;
    $ch = curl_init("$REMOTE_BASE/0login.php");
    $postData = http_build_query([
        'username' => 'trucking14',
        'password' => '1',
        'login'    => 'ĐĂNG NHẬP'
    ]);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $COOKIE_FILE,
        CURLOPT_COOKIEFILE     => $COOKIE_FILE,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (VietsunSyncHub/1.0)'
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// Tính chu kỳ kế toán Vietsun TMS (26 tháng M-1 -> 25 tháng M => YYYY-MM-01)
function get_billing_cycle_param($isoDate) {
    if (!$isoDate || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $isoDate, $m)) {
        $isoDate = date('Y-m-d');
        preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $isoDate, $m);
    }
    $y = (int)$m[1];
    $mo = (int)$m[2];
    $d = (int)$m[3];
    if ($d >= 26) {
        $mo += 1;
        if ($mo > 12) {
            $mo = 1;
            $y += 1;
        }
    }
    return sprintf('%04d-%02d-01', $y, $mo);
}

function normalize_shipment_item($item) {
    if (!is_array($item)) return $item;
    $decodeFields = ['tenKhachPo', 'khoNhanHangPo', 'diaChiNhanPo', 'tinhNhanPo', 'lienHeNhanPo', 'tenHangHoaPo', 'noiLayContPo', 'cangDiPo', 'cangDenPo', 'tinhXuatHangPo'];
    foreach ($decodeFields as $f) {
        if (isset($item[$f]) && is_string($item[$f])) {
            $item[$f] = html_entity_decode($item[$f], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }

    // Nếu thông tin kho đóng / trả rỗng hoặc chỉ là dấu chấm/gạch ngang -> mặc nhiên lấy tên công ty / khách hàng
    $rawKho = trim((string)($item['khoNhanHangPo'] ?? ''));
    $rawKhach = trim((string)($item['tenKhachPo'] ?? ''));
    if ($rawKho === '' || preg_match('/^[\.\-\s,]+$/u', $rawKho)) {
        if ($rawKhach !== '' && !preg_match('/^[\.\-\s,]+$/u', $rawKhach)) {
            $item['khoNhanHangPo'] = $rawKhach;
        }
    }

    // Chuẩn hóa tên ĐVVT (Đơn vị vận chuyển) để không bao giờ bị lệch hay trùng tên viết liền
    if (!empty($item['donViVcPo'])) {
        $item['donViVcPo'] = normalize_vendor_name($item['donViVcPo']);
    }

    return $item;
}

function fetch_single_cycle($cycleDate, $forceRefresh = false) {
    global $REMOTE_BASE, $COOKIE_FILE;
    $safeCycle = preg_replace('/[^0-9\-]/', '', $cycleDate);
    $cacheFile = sys_get_temp_dir() . '/vietsun_cycle_' . $safeCycle . '.json';
    // TTL cache chu kỳ 60 giây để cập nhật chuyến xe nhanh chóng
    if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < 60)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && count($cached) > 0) return $cached;
    }

    if (!file_exists($COOKIE_FILE)) {
        vietsun_login();
    }
    $url = "$REMOTE_BASE/return_json.php?ngay=" . urlencode($safeCycle);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR      => $COOKIE_FILE,
        CURLOPT_COOKIEFILE     => $COOKIE_FILE,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (VietsunSyncHub/1.0)'
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $trimmed = ltrim($resp ?: '');
    if ($httpCode == 302 || $trimmed === '' || ($trimmed[0] !== '[' && $trimmed[0] !== '{')) {
        vietsun_login();
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR      => $COOKIE_FILE,
            CURLOPT_COOKIEFILE     => $COOKIE_FILE,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (VietsunSyncHub/1.0)'
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
    }

    $data = json_decode(trim($resp ?: ''), true);
    if (is_array($data)) {
        file_put_contents($cacheFile, json_encode($data));
        return $data;
    }
    return [];
}

function parse_tms_ajax_debit_html($html, &$map) {
    if (!$html) return;
    $numFields = [
        'donGiaDebitPo',
        'donGia1ThucTraChoxePo',
        'donGIaPhuLuc',
        'bocXep2ChiHoPo',
        'cpBocXepChiHoPo',
        'bocXep1GnChiPo',
        'veCauDuongPo',
        'neoXeDuongCamPo',
        'chiPhiNeoXePo',
        'cpduongcam',
        'cpkhacpo',
        'chiHoNangContPo',
        'chiHoHaContPo',
        'tienCuocPo',
        'cuocVanChuyenPo'
    ];
    foreach ($numFields as $prefix) {
        if (preg_match_all('/id="' . $prefix . '(\d+)"[^>]*value="([^"]*)"/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $id = $m[1];
                $num = (int)preg_replace('/[^0-9]/', '', $m[2]);
                if (!isset($map[$id])) $map[$id] = [];
                if ($num > 0 || !isset($map[$id][$prefix])) {
                    $map[$id][$prefix] = $num;
                }
            }
        }
    }
    $strFields = ['ghiChuPo', 'hinhThucKHopPo', 'notePo'];
    foreach ($strFields as $prefix) {
        if (preg_match_all('/id="' . $prefix . '(\d+)"[^>]*value="([^"]*)"/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $id = $m[1];
                $val = html_entity_decode(trim($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (!isset($map[$id])) $map[$id] = [];
                if (($val !== '' && $val !== '.') || !isset($map[$id][$prefix])) {
                    $map[$id][$prefix] = $val;
                }
            }
        }
    }
}

function call_tms_post($endpoint, $postFields) {
    global $REMOTE_BASE, $COOKIE_FILE;
    $ch = curl_init("$REMOTE_BASE/$endpoint");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($postFields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $COOKIE_FILE,
        CURLOPT_COOKIEFILE     => $COOKIE_FILE,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (VietsunSyncHub/1.0)',
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8']
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return $resp ?: '';
}

// Lấy danh sách đội xe & tài xế VIETSUN trực tiếp từ TMS (ajax-gvt.php loai=26)
function fetch_live_vietsun_fleet($forceRefresh = false) {
    global $COOKIE_FILE;
    $cacheFile = sys_get_temp_dir() . '/vietsun_fleet_cache.json';
    // Cache 30 phút để giảm tải hệ thống TMS
    if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < 1800)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && !empty($cached)) return $cached;
    }

    if (!file_exists($COOKIE_FILE)) {
        vietsun_login();
    }

    $userString = 'trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.';
    $postData = [
        'userString' => $userString,
        'loai'       => '26',
        'query'      => '0',
        'cv'         => '0',
        'id'         => '1'
    ];
    $html = call_tms_post('ajax-gvt.php', $postData);
    if (!$html || strpos($html, '<tr') === false) {
        vietsun_login();
        $html = call_tms_post('ajax-gvt.php', $postData);
    }

    $list = [];
    if ($html) {
        preg_match_all('/<tr\s+id="tr\d+">([\s\S]*?)<\/tr>/i', $html, $rowMatches);
        if (!empty($rowMatches[1])) {
            foreach ($rowMatches[1] as $inner) {
                if (preg_match('/<th[^>]*>([^<]*)<\/th>\s*<td[^>]*>([^<]*)<\/td>\s*<td[^>]*>([^<]*)<\/td>/i', $inner, $m)) {
                    $plate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $m[1]));
                    $name = trim(strip_tags($m[2]));
                    $phone = trim(strip_tags($m[3]));
                    if ($plate && $plate !== 'DEPOT') {
                        $list[] = [
                            'vendor' => 'VIETSUN',
                            'plate'  => $plate,
                            'name'   => $name,
                            'phone'  => $phone
                        ];
                    }
                }
            }
        }
    }
    if (!empty($list)) {
        @file_put_contents($cacheFile, json_encode($list, JSON_UNESCAPED_UNICODE));
    }
    return $list;
}

function build_date_windows($startDate, $endDate, $windowDays = 10) {
    $startTs = strtotime($startDate);
    $endTs   = strtotime($endDate);
    if (!$startTs) $startTs = time() - 35 * 86400;
    if (!$endTs)   $endTs   = time() + 10 * 86400;
    if ($startTs > $endTs) {
        $tmp = $startTs;
        $startTs = $endTs;
        $endTs = $tmp;
    }
    $windows = [];
    $cur = $startTs;
    while ($cur <= $endTs) {
        $winEnd = min($endTs, $cur + ($windowDays - 1) * 86400);
        $windows[] = [date('Y-m-d', $cur), date('Y-m-d', $winEnd)];
        $cur = $winEnd + 86400;
    }
    return $windows;
}

function fetch_tms_debit_map($startDate, $endDate, $forceRefresh = false) {
    global $COOKIE_FILE;
    $safeKey = preg_replace('/[^0-9\-]/', '', $startDate . '_' . $endDate);
    $cacheFile = sys_get_temp_dir() . '/vietsun_debit_map_v8_' . $safeKey . '.json';
    // Cache bảng giá 2 ngày (48 giờ = 172800s) theo yêu cầu để giảm tải máy chủ và mở tab siêu tốc
    if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < 172800)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && count($cached) > 0) return $cached;
    }

    if (!file_exists($COOKIE_FILE)) {
        vietsun_login();
    }

    $userString = 'trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.';
    $map = [];

    // Chia nhỏ khoảng thời gian thành các cửa sổ 7 ngày cho cả NHẬP (LIMIT 1700 dòng, ~1100 dòng/7 ngày)
    // và XUẤT (LIMIT 1200 dòng, ~850 dòng/7 ngày). Đảm bảo tuyệt đối không bao giờ chạm ngưỡng LIMIT SQL trên TMS!
    $nhapWindows = build_date_windows($startDate, $endDate, 7);
    $xuatWindows = build_date_windows($startDate, $endDate, 7);

    // 1. Lấy đầy đủ Hàng Nhập (Đơn giá Debit, Đơn giá Vendor donGia1ThucTraChoxePo, Chi hộ bốc xếp, Vé cầu đường, Phí khác, Hình thức kết hợp)
    // qua NHẬP - ALL (kieuXemTrucking = 7, idxem = 7) theo từng cửa sổ 7 ngày
    call_tms_post('index-crud.php', [
        'TenDangNhap' => 'trucking14',
        'userString'  => $userString,
        'cv'          => 3,
        'loai'        => 1,
        'iddt'        => 1,
        'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
        'thongtin'    => '7'
    ]);
    $loggedRetried = false;
    foreach ($nhapWindows as $win) {
        $htmlNhap7 = call_tms_post('index-ajax.php', [
            'userString' => $userString,
            'cv'         => 78,
            'idxem'      => 7,
            'kieuxem'    => 0,
            'loaiload'   => 4,
            'startDate'  => $win[0],
            'endDate'    => $win[1],
            'widthmh'    => 1920
        ]);
        if (!$loggedRetried && strpos($htmlNhap7, 'donGiaDebitPo') === false && strpos($htmlNhap7, 'donGia1ThucTraChoxePo') === false) {
            $loggedRetried = true;
            vietsun_login();
            call_tms_post('index-crud.php', [
                'TenDangNhap' => 'trucking14',
                'userString'  => $userString,
                'cv'          => 3,
                'loai'        => 1,
                'iddt'        => 1,
                'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
                'thongtin'    => '7'
            ]);
            $htmlNhap7 = call_tms_post('index-ajax.php', [
                'userString' => $userString,
                'cv'         => 78,
                'idxem'      => 7,
                'kieuxem'    => 0,
                'loaiload'   => 4,
                'startDate'  => $win[0],
                'endDate'    => $win[1],
                'widthmh'    => 1920
            ]);
        }
        parse_tms_ajax_debit_html($htmlNhap7, $map);
        $htmlNhap7 = null;
    }

    // 2. Lấy cước Debit, Đơn giá Vendor donGia1ThucTraChoxePo, Chi hộ bốc xếp & Tỷ lệ kết hợp của Hàng Xuất (XUẤT - CHI PHÍ: kieuXemTrucking = 3, idxem = 2)
    // theo từng cửa sổ 7 ngày để không bao giờ vượt trần LIMIT 1200 dòng của TMS
    call_tms_post('index-crud.php', [
        'TenDangNhap' => 'trucking14',
        'userString'  => $userString,
        'cv'          => 3,
        'loai'        => 1,
        'iddt'        => 1,
        'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
        'thongtin'    => '3'
    ]);
    foreach ($xuatWindows as $win) {
        $htmlXuat = call_tms_post('index-ajax.php', [
            'userString' => $userString,
            'cv'         => 78,
            'idxem'      => 2,
            'kieuxem'    => 0,
            'loaiload'   => 4,
            'startDate'  => $win[0],
            'endDate'    => $win[1],
            'widthmh'    => 1920
        ]);
        parse_tms_ajax_debit_html($htmlXuat, $map);
        $htmlXuat = null;
    }

    // Khôi phục kieuXemTrucking = 6 (NHẬP mặc định)
    call_tms_post('index-crud.php', [
        'TenDangNhap' => 'trucking14',
        'userString'  => $userString,
        'cv'          => 3,
        'loai'        => 1,
        'iddt'        => 1,
        'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
        'thongtin'    => '6'
    ]);

    if (count($map) > 0) {
        @file_put_contents($cacheFile, json_encode($map));
    }
    return $map;
}

function get_shipment_date_iso($item) {
    $n = trim($item['ngayTraHang'] ?? '');
    if ($n !== '' && $n !== '2021-11-11' && $n !== '1970-01-01') return $n;
    $th = trim($item['timeThucHien'] ?? '');
    if ($th !== '' && $th !== '2021-11-11' && $th !== '1970-01-01') return $th;
    return '';
}

function fetch_vietsun_shipments($targetDate = null, $forceRefresh = false, $includeDebit = true, $fromDate = null, $toDate = null) {
    $baseTs = $targetDate ? strtotime($targetDate) : time();
    if (!$baseTs) $baseTs = time();

    $fromTs = ($fromDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) ? strtotime($fromDate) : null;
    $toTs   = ($toDate   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate))   ? strtotime($toDate)   : null;
    if ($fromTs && $toTs && $fromTs > $toTs) {
        $tmp = $fromTs;
        $fromTs = $toTs;
        $toTs = $tmp;
    }

    // Cache danh sách chuyến: 60s để nhận chuyến mới/xe mới nhanh chóng (giá cước vẫn giữ cache 48h riêng biệt bên dưới)
    $cacheType = $includeDebit ? 'full' : 'nodbt';
    $rangeSuffix = ($fromTs && $toTs) ? ('_' . date('Ymd', $fromTs) . '_' . date('Ymd', $toTs)) : '';
    $cacheDateKey = date('Y-m-d', $baseTs) . $rangeSuffix;
    $cacheFile = sys_get_temp_dir() . "/vietsun_shipments_v8_{$cacheType}_{$cacheDateKey}.json";
    $cacheTtl = 60;

    $merged = null;
    if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && count($cached) > 0) {
            $merged = [];
            foreach ($cached as $item) {
                if (isset($item['idPo'])) {
                    $merged[$item['idPo']] = $item;
                }
            }
        }
    }

    if ($merged === null) {
        // Mặc định lấy cả chu kỳ trước (-35 ngày), chu kỳ hiện tại và chu kỳ tới (+15 ngày)
        // để luôn bao phủ trọn vẹn kỳ 26 tháng trước -> 25 tháng này ngay cả khi đang ở ngày 26..31 đầu kỳ mới!
        $rawCycles = [
            get_billing_cycle_param(date('Y-m-d', $baseTs - 35 * 86400)),
            get_billing_cycle_param(date('Y-m-d', $baseTs - 20 * 86400)),
            get_billing_cycle_param(date('Y-m-d', $baseTs)),
            get_billing_cycle_param(date('Y-m-d', $baseTs + 15 * 86400))
        ];
        if ($fromTs && $toTs) {
            for ($ts = $fromTs - 5 * 86400; $ts <= $toTs + 25 * 86400; $ts += 15 * 86400) {
                $rawCycles[] = get_billing_cycle_param(date('Y-m-d', $ts));
            }
        }
        $cycles = array_values(array_unique(array_filter($rawCycles)));

        $merged = [];
        foreach ($cycles as $c) {
            $arr = fetch_single_cycle($c, $forceRefresh);
            if (is_array($arr)) {
                foreach ($arr as $item) {
                    if (isset($item['idPo'])) {
                        $merged[$item['idPo']] = normalize_shipment_item($item);
                    }
                }
            }
        }

        // Nếu includeDebit = true (dành cho /vendor hoặc admin đối soát), mới lấy cước Debit và chi phí phát sinh
        if ($includeDebit) {
            // Lấy chi phí phát sinh do Tài xế báo trên App (/driver) để gộp vào cột Chi hộ bốc xếp & Chi phí khác
            $drvBocXepMap = [];
            $drvCpKhacMap = [];
            $drvExpNoteMap = [];
            $drvTotalExpMap = [];
            $drvExpDetailsMap = [];
    try {
        $pdo = get_db();
        $stExp = $pdo->query("SELECT booking_id_po, expense_type, amount, note FROM trip_expenses ORDER BY id ASC");
        if ($stExp) {
            foreach ($stExp->fetchAll() as $er) {
                $pid = (string)($er['booking_id_po'] ?? '');
                if ($pid === '') continue;
                $amt = (int)round((float)($er['amount'] ?? 0));
                $etype = trim($er['expense_type'] ?? '');
                $enote = trim($er['note'] ?? '');
                if (stripos($etype, 'bốc xếp') !== false || stripos($etype, 'boc xep') !== false) {
                    $drvBocXepMap[$pid] = ($drvBocXepMap[$pid] ?? 0) + $amt;
                } else {
                    $drvCpKhacMap[$pid] = ($drvCpKhacMap[$pid] ?? 0) + $amt;
                }
                $drvTotalExpMap[$pid] = ($drvTotalExpMap[$pid] ?? 0) + $amt;
                $desc = ($etype ?: 'Chi phí') . ($amt > 0 ? (': ' . number_format($amt) . 'đ') : '') . ($enote ? " ($enote)" : '');
                if (!isset($drvExpDetailsMap[$pid])) $drvExpDetailsMap[$pid] = [];
                $drvExpDetailsMap[$pid][] = $desc;
                if ($enote !== '') {
                    $drvExpNoteMap[$pid] = isset($drvExpNoteMap[$pid]) ? ($drvExpNoteMap[$pid] . '; ' . $enote) : $enote;
                }
            }
        }
    } catch (Exception $e) {}

    // Lấy bảng giá Debit & Chi hộ bốc xếp & Tỷ lệ kết hợp từ TMS
    // Mặc định lùi 40 ngày để luôn bao phủ từ ngày 26 tháng trước dù đang ở cuối tháng
    $defaultStartTs = $baseTs - 40 * 86400;
    $defaultEndTs   = $baseTs + 10 * 86400;
    $effStartTs = $fromTs ? min($defaultStartTs, $fromTs) : $defaultStartTs;
    $effEndTs   = $toTs   ? max($defaultEndTs, $toTs)     : $defaultEndTs;
    $startDate = date('Y-m-d', $effStartTs);
    $endDate   = date('Y-m-d', $effEndTs);
    $debitMap  = fetch_tms_debit_map($startDate, $endDate, $forceRefresh);

    // Gắn thông tin cước và chi phí phát sinh từ TMS Vietsun
    foreach ($merged as $idPo => &$item) {
        $dInfo = $debitMap[$idPo] ?? [];
        $item['donGiaDebitPo']         = isset($dInfo['donGiaDebitPo']) && $dInfo['donGiaDebitPo'] > 0 ? (int)$dInfo['donGiaDebitPo'] : null;
        $item['donGia1ThucTraChoxePo'] = isset($dInfo['donGia1ThucTraChoxePo']) && $dInfo['donGia1ThucTraChoxePo'] > 0 ? (int)$dInfo['donGia1ThucTraChoxePo'] : null;
        $item['donGIaPhuLuc']          = isset($dInfo['donGIaPhuLuc']) && $dInfo['donGIaPhuLuc'] > 0 ? (int)$dInfo['donGIaPhuLuc'] : null;
        $item['bocXep1GnChiPo']        = isset($dInfo['bocXep1GnChiPo']) && $dInfo['bocXep1GnChiPo'] > 0 ? (int)$dInfo['bocXep1GnChiPo'] : 0;
        $item['chiHoNangContPo']       = isset($dInfo['chiHoNangContPo']) && $dInfo['chiHoNangContPo'] > 0 ? (int)$dInfo['chiHoNangContPo'] : 0;
        $item['chiHoHaContPo']         = isset($dInfo['chiHoHaContPo']) && $dInfo['chiHoHaContPo'] > 0 ? (int)$dInfo['chiHoHaContPo'] : 0;
        $item['neoXeDuongCamPo']       = isset($dInfo['neoXeDuongCamPo']) && $dInfo['neoXeDuongCamPo'] > 0 ? (int)$dInfo['neoXeDuongCamPo'] : 0;
        $item['cpduongcam']            = isset($dInfo['cpduongcam']) && $dInfo['cpduongcam'] > 0 ? (int)$dInfo['cpduongcam'] : 0;
        $item['cpkhacpo']              = isset($dInfo['cpkhacpo']) && $dInfo['cpkhacpo'] > 0 ? (int)$dInfo['cpkhacpo'] : 0;
        $item['chiPhiNeoXePo']         = isset($dInfo['chiPhiNeoXePo']) && $dInfo['chiPhiNeoXePo'] > 0 ? (int)$dInfo['chiPhiNeoXePo'] : 0;
        if (!empty($dInfo['hinhThucKHopPo'])) {
            $item['hinhThucKHopPo'] = $dInfo['hinhThucKHopPo'];
        }
        if (!empty($dInfo['ghiChuPo']) && $dInfo['ghiChuPo'] !== '.') {
            $item['ghiChuPo'] = $dInfo['ghiChuPo'];
        }
        if (!empty($dInfo['notePo']) && $dInfo['notePo'] !== '.') {
            $item['notePo'] = $dInfo['notePo'];
        }

        // Chi hộ bốc xếp (TMS hoặc Tài xế báo)
        $tmsBocXep = max((int)($dInfo['bocXep2ChiHoPo'] ?? 0), (int)($dInfo['cpBocXepChiHoPo'] ?? 0));
        $drvBocXep = (int)($drvBocXepMap[(string)$idPo] ?? 0);
        $item['_tmsBocXep'] = $tmsBocXep;
        $item['_driverBocXep'] = $drvBocXep;
        $item['_chiHoBocXep'] = $tmsBocXep > 0 ? $tmsBocXep : $drvBocXep;

        // Chi phí phát sinh khác: Chỉ lấy tổng của 3 dòng [PHÍ NEO XE (neoXeDuongCamPo) + ĐƯỜNG CẤM (cpduongcam) + PHÍ KHÁC (cpkhacpo)]
        // hoặc cột gộp [NEO XE, ĐC - XE (chiPhiNeoXePo)]. Tuyệt đối KHÔNG cộng dồn cả hai để tránh bị nhân đôi!
        $sumCont3 = (int)($dInfo['neoXeDuongCamPo'] ?? 0)
            + (int)($dInfo['cpduongcam'] ?? 0)
            + (int)($dInfo['cpkhacpo'] ?? 0);
        $xeCol = (int)($dInfo['chiPhiNeoXePo'] ?? 0);
        $tmsCpKhac = $sumCont3 > 0 ? $sumCont3 : $xeCol;
        $drvCpKhac = (int)($drvCpKhacMap[(string)$idPo] ?? 0);
        $item['_tmsCpKhac'] = $tmsCpKhac;
        $item['_driverCpKhac'] = $drvCpKhac;
        $item['_cpKhac'] = $tmsCpKhac > 0 ? $tmsCpKhac : $drvCpKhac;

        // Vé cầu đường
        $tmsVeCauDuong = (int)($dInfo['veCauDuongPo'] ?? 0);
        $item['_veCauDuong'] = $tmsVeCauDuong;

        // Tổng phát sinh TMS vs Phát sinh thực tế tài xế nhập
        $tmsTotalExp = $tmsBocXep + $tmsCpKhac + $tmsVeCauDuong;
        $drvTotalExp = (int)($drvTotalExpMap[(string)$idPo] ?? 0);
        $item['_tmsPhatSinh'] = $tmsTotalExp;
        $item['_driverPhatSinh'] = $drvTotalExp;
        $item['_chenhLechPhatSinh'] = $drvTotalExp - $tmsTotalExp;
        $item['_driverExpDetail'] = implode('; ', $drvExpDetailsMap[(string)$idPo] ?? []);
        $item['_expenseNote'] = $drvExpNoteMap[(string)$idPo] ?? '';

        // Ghi nhận Tỷ lệ kết hợp (nếu có để hiển thị tham khảo)
        $kh = trim($dInfo['hinhThucKHopPo'] ?? ($item['hinhThucKHopPo'] ?? ''));
        $gc = trim($dInfo['ghiChuPo'] ?? ($item['ghiChuPo'] ?? ''));
        if ($kh === '' && preg_match('/(\d+(?:\.\d+)?\s*%|\bR\b)/i', $gc, $mGc)) {
            $kh = strtoupper(str_replace(' ', '', $mGc[1]));
        }
        $item['_tyLeKetHop'] = $kh;

        // CỘT CƯỚC DEBIT: CẢ HÀNG NHẬP VÀ HÀNG XUẤT ĐỀU LẤY DUY NHẤT TỪ Ô "ĐƠN GIÁ VENDOR" (donGia1ThucTraChoxePo) TRONG TMS VIETSUN.
        // KHÔNG CẦN NHÂN TỶ LỆ KẾT HỢP GÌ CẢ. NẾU Ô ĐÓ CHƯA CÓ GIÁ THÌ ĐỂ TRỐNG (null).
        if (!empty($item['donGia1ThucTraChoxePo']) && $item['donGia1ThucTraChoxePo'] > 0) {
            $item['_debitRate'] = (int)$item['donGia1ThucTraChoxePo'];
            $item['_debitType'] = 'VENDOR_RATE';
            $item['_debitNote'] = 'Đơn giá Vendor';
        } else {
            $item['_debitRate'] = null;
            $item['_debitType'] = 'EMPTY';
            $item['_debitNote'] = '';
        }
    }
    unset($item);
} // end if ($includeDebit)

    // Khóa chỉnh sửa nếu ngày của chuyến đã qua (tripDate < hôm nay)
    $todayIso = date('Y-m-d');
    foreach ($merged as $idPo => &$item) {
        $tripDateIso = get_shipment_date_iso($item);
        $item['_tripDateIso'] = $tripDateIso;
        $item['_isLocked']    = ($tripDateIso !== '' && $tripDateIso < $todayIso);
        $item['_pendingApproval'] = null;
    }
    unset($item);

    // Lưu vào cache tổng hợp (file cache) nếu vừa crawl mới
    if (count($merged) > 0) {
        @file_put_contents($cacheFile, json_encode(array_values($merged)));
    }
} // end if ($merged === null)

    // Ghép các thay đổi đang CHỜ DUYỆT (status = 'PENDING') từ bảng tms_pending_approvals
    // để Vendor & Tài xế vẫn thấy thông tin họ vừa nhập kèm trạng thái "Chờ Điều vận duyệt"
    try {
        $pdo = get_db();
        $stPend = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' ORDER BY id ASC");
        $pendRows = $stPend ? $stPend->fetchAll() : [];
        foreach ($pendRows as $pr) {
            $pid = $pr['id_po'];
            if (!isset($merged[$pid])) continue;
            $changes = json_decode($pr['changes_json'], true);
            if (!is_array($changes)) continue;

            if (!isset($merged[$pid]['_tmsOriginal'])) {
                $merged[$pid]['_tmsOriginal'] = [];
            }
            foreach ($changes as $field => $diff) {
                $newVal = $diff['new'] ?? '';
                if ($field === 'donViVcPo') {
                    $newVal = normalize_vendor_name($newVal);
                }
                if (!array_key_exists($field, $merged[$pid]['_tmsOriginal'])) {
                    $merged[$pid]['_tmsOriginal'][$field] = $merged[$pid][$field] ?? '';
                }
                $merged[$pid][$field] = $newVal;
            }
            $merged[$pid]['_pendingApproval'] = [
                'id'         => (int)$pr['id'],
                'actor_role' => $pr['actor_role'],
                'actor_name' => $pr['actor_name'],
                'created_at' => $pr['created_at'],
                'changes'    => $changes
            ];
        }
    } catch (Exception $e) {}

    // Sắp xếp mới nhất lên đầu
    krsort($merged, SORT_NUMERIC);
    return array_values($merged);
}

// Đưa thay đổi của Vendor hoặc Tài xế vào hàng đợi chờ Điều vận duyệt (Và chặn sửa nếu đã qua ngày)
function queue_pending_tms_approval($pdo, $idPo, $codePo, $actorRole, $actorName, $vendorName, $requestedFields) {
    $all = fetch_vietsun_shipments(null, false);
    $foundTrip = null;
    foreach ($all as $t) {
        if ((string)($t['idPo'] ?? '') === (string)$idPo) {
            $foundTrip = $t;
            break;
        }
    }

    $tripDate = $foundTrip ? get_shipment_date_iso($foundTrip) : '';
    $todayIso = date('Y-m-d');
    if ($tripDate !== '' && $tripDate < $todayIso) {
        return [
            'ok'     => false,
            'locked' => true,
            'error'  => "Chuyến hàng ngày $tripDate đã kết thúc ngày và bị khóa sổ! Bạn không thể tự ý chỉnh sửa."
        ];
    }

    $codePoFinal = $codePo ?: ($foundTrip['codePo'] ?? '');
    $xuatNhap    = $foundTrip['xuatNhapPo'] ?? '';
    $is40        = (int)($foundTrip['cont40Po'] ?? 0) > 0;
    $contSize    = $is40 ? '40' : '20';
    $khachKho    = ($foundTrip['khoNhanHangPo'] ?? '') ?: ($foundTrip['tenKhachPo'] ?? '');
    $origMap     = $foundTrip['_tmsOriginal'] ?? [];

    // Kiểm tra xem đã có bản ghi PENDING nào của cùng id_po + actor_role chưa
    $stExist = $pdo->prepare("SELECT * FROM tms_pending_approvals WHERE id_po = ? AND actor_role = ? AND status = 'PENDING' ORDER BY id DESC LIMIT 1");
    $stExist->execute([(string)$idPo, $actorRole]);
    $existRow = $stExist->fetch();

    $changes = $existRow ? (json_decode($existRow['changes_json'], true) ?: []) : [];
    foreach ($requestedFields as $field => $newVal) {
        $newVal = trim((string)$newVal);
        $oldVal = isset($origMap[$field]) ? $origMap[$field] : (isset($changes[$field]['old']) ? $changes[$field]['old'] : ($foundTrip[$field] ?? ''));
        $changes[$field] = [
            'old' => (string)$oldVal,
            'new' => $newVal
        ];
    }

    // Luôn bảo toàn tên ĐVVT: nếu chuyến gốc trên TMS đã có ĐVVT rồi thì bỏ ghi đè donViVcPo, tránh mất dấu cách
    if (isset($changes['donViVcPo'])) {
        $norm = normalize_vendor_name($changes['donViVcPo']['new']);
        $origDv = trim($origMap['donViVcPo'] ?? ($foundTrip['donViVcPo'] ?? ''));
        $oldNorm = normalize_vendor_name($origDv);
        if ($origDv !== '' && to_clean_code($origDv) === to_clean_code($norm)) {
            unset($changes['donViVcPo']);
        } else {
            $changes['donViVcPo']['old'] = $oldNorm ?: $changes['donViVcPo']['old'];
            $changes['donViVcPo']['new'] = $norm;
        }
    }

    if ($actorRole === 'VENDOR') {
        $actorName = normalize_vendor_name($actorName);
        $vendorName = normalize_vendor_name($vendorName);
    }

    $changesJson = json_encode($changes, JSON_UNESCAPED_UNICODE);
    try {
        if ($existRow) {
            $stUpd = $pdo->prepare("
                UPDATE tms_pending_approvals
                SET code_po = ?, trip_date = ?, xuat_nhap = ?, cont_size = ?, khach_kho = ?, actor_name = ?, vendor_name = ?, changes_json = ?, created_at = NOW()
                WHERE id = ?
            ");
            $stUpd->execute([$codePoFinal, $tripDate, $xuatNhap, $contSize, $khachKho, $actorName, $vendorName, $changesJson, $existRow['id']]);
            $pendingId = (int)$existRow['id'];
        } else {
            $stIns = $pdo->prepare("
                INSERT INTO tms_pending_approvals (id_po, code_po, trip_date, xuat_nhap, cont_size, khach_kho, actor_role, actor_name, vendor_name, changes_json, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')
            ");
            $stIns->execute([(string)$idPo, $codePoFinal, $tripDate, $xuatNhap, $contSize, $khachKho, $actorRole, $actorName, $vendorName, $changesJson]);
            $pendingId = (int)$pdo->lastInsertId();
        }
    } catch (PDOException $pdoEx) {
        // Tự động khôi phục nếu database chưa kịp nâng cấp cột cont_size
        if (strpos($pdoEx->getMessage(), 'cont_size') !== false || $pdoEx->getCode() == '42S22') {
            try {
                $pdo->exec("ALTER TABLE `tms_pending_approvals` ADD COLUMN `cont_size` VARCHAR(10) NOT NULL DEFAULT '' AFTER `xuat_nhap`");
                if ($existRow) {
                    $stUpd = $pdo->prepare("
                        UPDATE tms_pending_approvals
                        SET code_po = ?, trip_date = ?, xuat_nhap = ?, cont_size = ?, khach_kho = ?, actor_name = ?, vendor_name = ?, changes_json = ?, created_at = NOW()
                        WHERE id = ?
                    ");
                    $stUpd->execute([$codePoFinal, $tripDate, $xuatNhap, $contSize, $khachKho, $actorName, $vendorName, $changesJson, $existRow['id']]);
                    $pendingId = (int)$existRow['id'];
                } else {
                    $stIns = $pdo->prepare("
                        INSERT INTO tms_pending_approvals (id_po, code_po, trip_date, xuat_nhap, cont_size, khach_kho, actor_role, actor_name, vendor_name, changes_json, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')
                    ");
                    $stIns->execute([(string)$idPo, $codePoFinal, $tripDate, $xuatNhap, $contSize, $khachKho, $actorRole, $actorName, $vendorName, $changesJson]);
                    $pendingId = (int)$pdo->lastInsertId();
                }
            } catch (Exception $retryEx) {
                throw $pdoEx;
            }
        } else {
            throw $pdoEx;
        }
    }

    return [
        'ok'        => true,
        'pending'   => true,
        'pendingId' => $pendingId,
        'changes'   => $changes
    ];
}

function push_vietsun_crud($params) {
    global $REMOTE_BASE, $COOKIE_FILE;
    if (!file_exists($COOKIE_FILE)) {
        vietsun_login();
    }
    if (empty($params['TenDangNhap']) || $params['TenDangNhap'] === 'admin') {
        $params['TenDangNhap'] = 'trucking14';
    }
    if (empty($params['userString']) || strpos($params['userString'], '<<|') === false) {
        $params['userString'] = 'trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.';
    }
    $ch = curl_init("$REMOTE_BASE/index-crud.php");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $COOKIE_FILE,
        CURLOPT_COOKIEFILE     => $COOKIE_FILE,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8']
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    foreach (glob(sys_get_temp_dir() . '/vietsun_cycle_*.json') as $cf) {
        @unlink($cf);
    }
    foreach (glob(sys_get_temp_dir() . '/vietsun_debit_map_*.json') as $cf) {
        @unlink($cf);
    }
    foreach (glob(sys_get_temp_dir() . '/vietsun_shipments_*.json') as $cf) {
        @unlink($cf);
    }
    return $res;
}

function update_tms_field($idPo, $codePo, $fieldName, $val) {
    $val = trim((string)$val);
    if ($fieldName === 'soContPo') {
        $val = strtoupper($val);
        push_vietsun_crud([
            'cv'       => 5,
            'loai'     => 21,
            'iddt'     => $idPo,
            'cot'      => $val,
            'thongtin' => $codePo
        ]);
        return push_vietsun_crud([
            'cv'       => 3,
            'loai'     => 1,
            'iddt'     => 1,
            'cot'      => "b18<<|4<<|50<<|{$idPo}<<|soContPo<<|{$codePo}",
            'thongtin' => $val
        ]);
    }
    if ($fieldName === 'soXePo') {
        $val = strtoupper($val);
        return push_vietsun_crud([
            'cv'       => 3,
            'loai'     => 1,
            'iddt'     => 1,
            'cot'      => "b18<<|4<<|50<<|{$idPo}<<|soXePo<<|{$codePo}",
            'thongtin' => $val
        ]);
    }
    $lenMap = [
        'soSealPo'       => 50,
        'donViVcPo'      => 30,
        'noiLayContPo'   => 100,
        'cangDiPo'       => 100,
        'tinhXuatHangPo' => 50,
        'cangDenPo'      => 30,
        'portTimePo'     => 20
    ];
    $maxLen = $lenMap[$fieldName] ?? 100;
    return push_vietsun_crud([
        'cv'       => 3,
        'loai'     => 1,
        'iddt'     => 1,
        'cot'      => "b18<<|4<<|{$maxLen}<<|{$idPo}<<|{$fieldName}<<|{$codePo}",
        'thongtin' => $val
    ]);
}

try {
    // FAST-PATH CHO return_json THEO CHU KỲ (KHÔNG CẦN KẾT NỐI MYSQL, STREAM TRỰC TIẾP TỪ FILE CACHE + STALE-WHILE-REVALIDATE)
    if ($route === 'return_json') {
        $ngay = $_GET['ngay'] ?? ($_GET['date'] ?? null);
        $force = !empty($_GET['refresh']);
        if ($ngay && preg_match('/^\d{4}-\d{2}-01$/', $ngay)) {
            $safeCycle = preg_replace('/[^0-9\-]/', '', $ngay);
            $cacheFile = sys_get_temp_dir() . '/vietsun_cycle_' . $safeCycle . '.json';
            if (!$force && file_exists($cacheFile) && filesize($cacheFile) > 10) {
                $age = time() - filemtime($cacheFile);
                if ($age < 60) {
                    readfile($cacheFile);
                    exit;
                }
                if ($age < 86400) {
                    // Trả ngay dữ liệu cache trong ~5ms cho trình duyệt, sau đó tự làm mới ngầm từ TMS
                    @ignore_user_abort(true);
                    readfile($cacheFile);
                    if (function_exists('fastcgi_finish_request')) {
                        fastcgi_finish_request();
                    } else {
                        @ob_end_flush();
                        @flush();
                    }
                    fetch_single_cycle($ngay, true);
                    exit;
                }
            }
            $data = fetch_single_cycle($ngay, $force);
            if (file_exists($cacheFile) && filesize($cacheFile) > 10) {
                readfile($cacheFile);
            } else {
                echo json_encode($data);
            }
            exit;
        }
    }

    $pdo = get_db();
    $schemaFlag = sys_get_temp_dir() . '/vietsun_schema_v13.flag';
    if (!file_exists($schemaFlag)) {
        ensure_schema($pdo);
    }

    // ========================================================================
    // 1. ĐĂNG NHẬP TÀI XẾ (USER = SỐ XE HOẶC SĐT, PASS MẶC ĐỊNH = 123456)
    // ========================================================================
    if ($route === 'auth/driver-login') {
        $rawUser = trim($jsonBody['plate'] ?? ($jsonBody['username'] ?? ($jsonBody['phone'] ?? '')));
        $pin = trim($jsonBody['password'] ?? ($jsonBody['pin'] ?? ''));
        if (!$rawUser || !$pin) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Biển số xe và Mật khẩu']);
            exit;
        }
        $cleanPlate = to_clean_code($rawUser);
        $stmt = $pdo->prepare('
            SELECT d.*, v.vendor_name, v.vendor_code 
            FROM drivers d 
            JOIN vendors v ON d.vendor_id = v.id 
            WHERE (d.assigned_plate = ? OR d.phone = ?) AND d.pin_code = ? AND d.is_active = 1 
            LIMIT 1
        ');
        $stmt->execute([$cleanPlate, $rawUser, $pin]);
        $driver = $stmt->fetch();
        if (!$driver) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Biển số xe hoặc Mật khẩu không đúng!']);
            exit;
        }
        $token = make_token([
            'role'       => 'driver',
            'driverId'   => $driver['id'],
            'vendorId'   => $driver['vendor_id'],
            'vendorName' => $driver['vendor_name'],
            'driverName' => $driver['driver_name'],
            'plate'      => $driver['assigned_plate']
        ]);
        echo json_encode([
            'ok'     => true,
            'token'  => $token,
            'driver' => [
                'id'     => $driver['id'],
                'name'   => $driver['driver_name'],
                'phone'  => $driver['phone'],
                'plate'  => $driver['assigned_plate'],
                'cccd'   => $driver['cccd'] ?? '',
                'gplx'   => $driver['gplx'] ?? '',
                'vendor' => $driver['vendor_name']
            ]
        ]);
        exit;
    }

    // ========================================================================
    // 2. ĐĂNG NHẬP CHỦ VENDOR (ID = TÊN VENDOR VIẾT LIỀN KHÔNG DẤU, PASS = 123456)
    // ========================================================================
    if ($route === 'auth/vendor-login') {
        $rawCode = trim($jsonBody['code'] ?? '');
        $code = to_clean_code($rawCode);
        $password = trim($jsonBody['password'] ?? '');
        if (!$code || !$password) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập ID Vendor và Mật khẩu']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT * FROM vendors WHERE (vendor_code = ? OR REPLACE(UPPER(vendor_name), " ", "") = ?) AND is_active = 1 LIMIT 1');
        $stmt->execute([$code, $code]);
        $vendor = $stmt->fetch();
        if (!$vendor || $vendor['password_hash'] !== $password) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Sai ID nhà xe hoặc Mật khẩu!']);
            exit;
        }
        $token = make_token([
            'role'       => 'vendor',
            'vendorId'   => $vendor['id'],
            'vendorName' => normalize_vendor_name($vendor['vendor_name']),
            'vendorCode' => $vendor['vendor_code']
        ]);
        echo json_encode([
            'ok'     => true,
            'token'  => $token,
            'vendor' => [
                'id'   => $vendor['id'],
                'name' => $vendor['vendor_name'],
                'code' => $vendor['vendor_code']
            ]
        ]);
        exit;
    }

    // ========================================================================
    // 2B. ĐĂNG NHẬP QUẢN TRỊ VIÊN (/admin) - PASS: 69 (SUPER ADMIN) HOẶC 40 (DUYỆT CONT XUẤT 40")
    // ========================================================================
    if ($route === 'auth/admin-login') {
        $pass = trim($jsonBody['password'] ?? '');
        if ($pass === '69') {
            $token = make_token(['role' => 'admin', 'admin' => true, 'level' => 'super']);
            echo json_encode([
                'ok'         => true,
                'token'      => $token,
                'adminLevel' => 'super',
                'level'      => 69,
                'roleName'   => 'Toàn quyền Quản trị (Super Admin)'
            ]);
            exit;
        } elseif ($pass === '40') {
            $token = make_token(['role' => 'admin', 'admin' => true, 'level' => 'export_40']);
            echo json_encode([
                'ok'         => true,
                'token'      => $token,
                'adminLevel' => 'export_40',
                'level'      => 40,
                'roleName'   => 'Duyệt Cont Hàng Xuất 40"'
            ]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Mật khẩu quản trị không đúng!']);
            exit;
        }
    }

    // ========================================================================
    // 3. PROXY CHO TRANG ĐIỀU HÀNH CHÍNH & DASHBOARD QUẢN TRỊ (/admin)
    // ========================================================================
    if ($route === 'login' || $route === 'auth/login') {
        $uName = trim($jsonBody['username'] ?? ($_POST['username'] ?? ''));
        if (!$uName) $uName = 'trucking14';
        echo json_encode([
            'ok'         => true,
            'username'   => $uName,
            'userString' => 'trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.'
        ]);
        exit;
    }

    if ($route === 'drivers' || $route === 'api/drivers') {
        $force = !empty($_GET['refresh']);
        $vietsunFleet = fetch_live_vietsun_fleet($force);

        $drivers = $pdo->query("
            SELECT d.assigned_plate AS plate, d.driver_name AS name, d.phone, d.cccd, d.gplx, v.vendor_name AS vendor
            FROM drivers d
            JOIN vendors v ON d.vendor_id = v.id
            WHERE d.is_active = 1 AND v.vendor_code != 'VIETSUN'
            ORDER BY v.vendor_name ASC, d.assigned_plate ASC
        ")->fetchAll();
        foreach ($drivers as &$drvRow) {
            $drvRow['vendor'] = normalize_vendor_name($drvRow['vendor'] ?? '');
        }
        unset($drvRow);

        $vendorNames = [];
        $vRows = $pdo->query("SELECT vendor_name FROM vendors WHERE is_active = 1 AND vendor_code != 'VIETSUN' ORDER BY vendor_name ASC")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($vRows as $vn) {
            $normVn = normalize_vendor_name($vn);
            if ($normVn !== '' && !in_array($normVn, $vendorNames, true)) {
                $vendorNames[] = $normVn;
            }
        }

        echo json_encode([
            'ok'           => true,
            'vietsun'      => $vietsunFleet,
            'vietsunCount' => count($vietsunFleet),
            'vendors'      => $drivers,
            'vendorNames'  => $vendorNames,
            'vendorCount'  => count($drivers),
            'total'        => count($vietsunFleet) + count($drivers)
        ]);
        exit;
    }

    if ($route === 'return_json') {
        $ngay = $_GET['ngay'] ?? ($_GET['date'] ?? null);
        $force = !empty($_GET['refresh']);
        $includeDebit = empty($_GET['fast']);
        if ($ngay && preg_match('/^\d{4}-\d{2}-01$/', $ngay)) {
            echo json_encode(array_values(fetch_single_cycle($ngay, $force)));
        } else {
            echo json_encode(array_values(fetch_vietsun_shipments($ngay, $force, $includeDebit)));
        }
        exit;
    }
    if ($route === 'index-crud' || $route === 'index_crud') {
        $payload = $_POST ?: $jsonBody;
        // Nếu người dùng lưu cột Notes (notePo) từ popup, lưu ngay vào tms_tt_status & file cache để đồng bộ tức thì
        if (is_array($payload) && isset($payload['cot']) && preg_match('/<<\|(\d+)<<\|notePo<<\|/', (string)$payload['cot'], $mNote)) {
            $notePid = (string)$mNote[1];
            $noteVal = trim((string)($payload['thongtin'] ?? ''));
            try {
                $stNote = $pdo->prepare("
                    INSERT INTO `tms_tt_status` (`id_po`, `note_po`)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE `note_po` = VALUES(`note_po`), `updated_at` = CURRENT_TIMESTAMP
                ");
                $stNote->execute([$notePid, $noteVal]);
            } catch (Exception $e) {}
            $ttCacheFile = sys_get_temp_dir() . '/vietsun_tt_checked_v2.json';
            if (file_exists($ttCacheFile)) {
                $cached = json_decode(@file_get_contents($ttCacheFile), true);
                if (is_array($cached)) {
                    if (!isset($cached['notesMap']) || !is_array($cached['notesMap'])) $cached['notesMap'] = [];
                    if ($noteVal !== '') $cached['notesMap'][$notePid] = $noteVal;
                    else unset($cached['notesMap'][$notePid]);
                    @file_put_contents($ttCacheFile, json_encode($cached));
                }
            }
        }
        echo push_vietsun_crud($payload);
        exit;
    }

    // Quản lý các Mẫu Biên Bản Giao Nhận (BB Templates) theo Khách Hàng & Kho Nhận
    if ($route === 'bb_templates' || $route === 'api/bb_templates') {
        $stmt = $pdo->query("SELECT `id`, `name`, `short_badge`, COALESCE(`customer_keywords`, '') AS `customer_keywords`, `keywords`, `html_content`, `is_active`, `sort_order`, `updated_at` FROM `tms_bb_templates` ORDER BY `sort_order` ASC, `updated_at` DESC");
        $rows = $stmt->fetchAll();
        echo json_encode(['ok' => true, 'templates' => $rows]);
        exit;
    }
    if ($route === 'bb_templates/save') {
        $id = trim((string)($jsonBody['id'] ?? ''));
        $name = trim((string)($jsonBody['name'] ?? ''));
        $shortBadge = trim((string)($jsonBody['short_badge'] ?? 'Form Riêng'));
        $customerKeywords = trim((string)($jsonBody['customer_keywords'] ?? ''));
        $keywords = trim((string)($jsonBody['keywords'] ?? ''));
        $htmlContent = (string)($jsonBody['html_content'] ?? '');
        $isActive = isset($jsonBody['is_active']) ? (int)$jsonBody['is_active'] : 1;
        $sortOrder = isset($jsonBody['sort_order']) ? (int)$jsonBody['sort_order'] : 10;

        if ($id === '' || $name === '' || $htmlContent === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập đầy đủ Tên Form và Nội dung mẫu Biên bản!']);
            exit;
        }
        $stmt = $pdo->prepare("
            INSERT INTO `tms_bb_templates` (`id`, `name`, `short_badge`, `customer_keywords`, `keywords`, `html_content`, `is_active`, `sort_order`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`),
                `short_badge` = VALUES(`short_badge`),
                `customer_keywords` = VALUES(`customer_keywords`),
                `keywords` = VALUES(`keywords`),
                `html_content` = VALUES(`html_content`),
                `is_active` = VALUES(`is_active`),
                `sort_order` = VALUES(`sort_order`),
                `updated_at` = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$id, $name, $shortBadge, $customerKeywords, $keywords, $htmlContent, $isActive, $sortOrder]);
        echo json_encode(['ok' => true, 'id' => $id]);
        exit;
    }
    if ($route === 'bb_templates/delete') {
        $id = trim((string)($jsonBody['id'] ?? ''));
        if ($id === '' || $id === 'STANDARD') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Không thể xóa Form Chuẩn mặc định!']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM `tms_bb_templates` WHERE `id` = ?");
        $stmt->execute([$id]);
        echo json_encode(['ok' => true, 'id' => $id]);
        exit;
    }

    // Đồng bộ & Cập nhật cột TT (trangThaiDieuXePo) và cột Notes (notePo) của Tab Hàng Nhập từ TMS gốc
    if ($route === 'tt_status' || $route === 'api/tt_status') {
        $force = !empty($_GET['refresh']);
        $ttCacheFile = sys_get_temp_dir() . '/vietsun_tt_checked_v2.json';

        $syncTtFromTms = function($pdo) use ($ttCacheFile, $COOKIE_FILE) {
            if (!file_exists($COOKIE_FILE)) {
                vietsun_login();
            }
            $userString = 'trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.';
            call_tms_post('index-crud.php', [
                'TenDangNhap' => 'trucking14',
                'userString'  => $userString,
                'cv'          => 3,
                'loai'        => 1,
                'iddt'        => 1,
                'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
                'thongtin'    => '6'
            ]);
            $html = call_tms_post('index-ajax.php', [
                'userString' => $userString,
                'cv'         => 78,
                'idxem'      => 7,
                'kieuxem'    => 0,
                'loaiload'   => 4,
                'startDate'  => date('Y-m-d', time() - 25 * 86400),
                'endDate'    => date('Y-m-d', time() + 5 * 86400),
                'widthmh'    => 1920
            ]);
            $set = [];
            if ($html && preg_match_all('/id=["\']a-trangThaiDieuXePo-(\d+)["\'][^>]*checked/i', $html, $m)) {
                foreach ($m[1] as $pid) {
                    $set[(string)$pid] = true;
                }
            }
            $notesMap = [];
            if ($html && preg_match_all('/id=["\']notePo(\d+)["\'][^>]*value=["\']([^"\']*)["\']/i', $html, $mn, PREG_SET_ORDER)) {
                foreach ($mn as $mRow) {
                    $pid = (string)$mRow[1];
                    $val = trim(html_entity_decode($mRow[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    if ($val !== '') {
                        $notesMap[$pid] = $val;
                    }
                }
            }
            try {
                $rows = $pdo->query("SELECT `id_po`, `is_checked`, `note_po` FROM `tms_tt_status`")->fetchAll();
                foreach ($rows as $r) {
                    $pid = (string)$r['id_po'];
                    if ($r['is_checked'] !== null) {
                        if ((int)$r['is_checked'] === 1) {
                            $set[$pid] = true;
                        } else {
                            unset($set[$pid]);
                        }
                    }
                    if ($r['note_po'] !== null) {
                        $nVal = trim((string)$r['note_po']);
                        if ($nVal !== '') {
                            $notesMap[$pid] = $nVal;
                        } else {
                            unset($notesMap[$pid]);
                        }
                    }
                }
            } catch (Exception $e) {}
            $out = ['ok' => true, 'checkedIds' => array_keys($set), 'notesMap' => $notesMap];
            @file_put_contents($ttCacheFile, json_encode($out));
            return $out;
        };

        if (!$force && file_exists($ttCacheFile) && filesize($ttCacheFile) > 5) {
            $age = time() - filemtime($ttCacheFile);
            if ($age < 60) {
                readfile($ttCacheFile);
                exit;
            }
            if ($age < 86400) {
                @ignore_user_abort(true);
                readfile($ttCacheFile);
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } else {
                    @ob_end_flush();
                    @flush();
                }
                $syncTtFromTms($pdo);
                exit;
            }
        }
        echo json_encode($syncTtFromTms($pdo));
        exit;
    }

    if ($route === 'tt_status/toggle' || $route === 'api/tt_status/toggle') {
        $idPo = preg_replace('/[^0-9]/', '', (string)($jsonBody['idPo'] ?? ''));
        $isChecked = !empty($jsonBody['checked']) ? 1 : 0;
        if ($idPo === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Thiếu idPo']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `tms_tt_status` (`id_po`, `is_checked`)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE `is_checked` = VALUES(`is_checked`), `updated_at` = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$idPo, $isChecked]);
        } catch (Exception $e) {}

        // Cập nhật ngay file cache để các tab/thiết bị khác thấy tức thì
        $ttCacheFile = sys_get_temp_dir() . '/vietsun_tt_checked_v2.json';
        if (file_exists($ttCacheFile)) {
            $cached = json_decode(@file_get_contents($ttCacheFile), true);
            if (is_array($cached) && isset($cached['checkedIds']) && is_array($cached['checkedIds'])) {
                $set = array_fill_keys(array_map('strval', $cached['checkedIds']), true);
                if ($isChecked) $set[$idPo] = true;
                else unset($set[$idPo]);
                $cached['checkedIds'] = array_keys($set);
                @file_put_contents($ttCacheFile, json_encode($cached));
            }
        }

        // Đẩy trực tiếp về TMS gốc (index-crud.php: cv=5, loai=20, iddt='6-trangThaiDieuXePo', cot=idPo, thongtin=1|0)
        push_vietsun_crud([
            'cv'       => 5,
            'loai'     => 20,
            'iddt'     => '6-trangThaiDieuXePo',
            'cot'      => $idPo,
            'thongtin' => (string)$isChecked,
            'minData'  => 0,
            'maxData'  => 0
        ]);

        echo json_encode(['ok' => true, 'idPo' => $idPo, 'checked' => (bool)$isChecked]);
        exit;
    }

    // Kiểm tra quyền Admin cho các route /admin/*
    if (strpos($route, 'admin/') === 0) {
        $authPayload = verify_token($authHeader);
        $isAdmin = ($authPayload && ($authPayload['role'] ?? '') === 'admin');
        $adminLevel = $authPayload['level'] ?? 'super';
        $isVendorFleetOp = ($authPayload && ($authPayload['role'] ?? '') === 'vendor' && in_array($route, ['admin/save-driver', 'admin/delete-driver', 'admin/driver-history'], true));
        if (!$isAdmin && !$isVendorFleetOp) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Yêu cầu mật khẩu Quản trị viên (/admin)!']);
            exit;
        }

        // Chặn thao tác Nhà xe / Đội xe / Sync Sheet đối với tài khoản phụ (pass: 40 - chỉ duyệt xuất 40)
        if ($isAdmin && $adminLevel === 'export_40' && in_array($route, ['admin/sync-sheet', 'admin/save-vendor', 'admin/delete-vendor', 'admin/save-driver', 'admin/delete-driver'], true)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Tài khoản phụ (Duyệt xuất 40) không có quyền thao tác mục này!']);
            exit;
        }
    }

    // Helper bổ sung thông tin changes & cont_size (20 / 40) cho các bản ghi chờ duyệt
    $enrichPendingRows = function(&$rows, $pdo) {
        $allShipmentsCache = null;
        $liveDriverMap = null;
        foreach ($rows as &$pr) {
            $pr['changes'] = json_decode($pr['changes_json'] ?? '{}', true) ?: [];
            if ($pr['actor_role'] === 'VENDOR') {
                $pr['actor_name'] = normalize_vendor_name($pr['actor_name']);
            } elseif ($pr['actor_role'] === 'DRIVER' && ($pr['status'] ?? '') === 'PENDING') {
                // Nếu yêu cầu ĐANG CHỜ DUYỆT (PENDING): Luôn đối chiếu lại tên tài xế hiện tại từ bảng drivers
                // để không bao giờ bị hiển thị cache tên tài xế cũ từ token điện thoại.
                // Còn các yêu cầu ĐÃ DUYỆT (APPROVED): Giữ nguyên 100% tên tài xế cũ đã chạy tại thời điểm đó!
                if ($liveDriverMap === null) {
                    $liveDriverMap = [];
                    try {
                        $stDrvAll = $pdo->query("
                            SELECT d.assigned_plate, d.driver_name, v.vendor_name
                            FROM drivers d
                            LEFT JOIN vendors v ON d.vendor_id = v.id
                        ");
                        if ($stDrvAll) {
                            foreach ($stDrvAll->fetchAll() as $dr) {
                                $p = to_clean_code($dr['assigned_plate'] ?? '');
                                if ($p) {
                                    $liveDriverMap[$p] = [
                                        'name'   => strtoupper(trim($dr['driver_name'] ?? '')),
                                        'vendor' => normalize_vendor_name($dr['vendor_name'] ?? '')
                                    ];
                                }
                            }
                        }
                    } catch (Exception $e) {}
                }
                $parts = explode('-', (string)($pr['actor_name'] ?? ''), 2);
                $plateClean = to_clean_code($parts[0] ?? '');
                if ($plateClean && isset($liveDriverMap[$plateClean]) && $liveDriverMap[$plateClean]['name'] !== '') {
                    $freshActor = $plateClean . ' - ' . $liveDriverMap[$plateClean]['name'];
                    if ($freshActor !== $pr['actor_name']) {
                        $pr['actor_name'] = $freshActor;
                        if (!empty($liveDriverMap[$plateClean]['vendor'])) {
                            $pr['vendor_name'] = $liveDriverMap[$plateClean]['vendor'];
                        }
                        try {
                            $pdo->prepare("UPDATE tms_pending_approvals SET actor_name = ?, vendor_name = ? WHERE id = ?")
                                ->execute([$pr['actor_name'], $pr['vendor_name'], $pr['id']]);
                        } catch (Exception $e) {}
                    }
                }
            }
            if (!empty($pr['vendor_name'])) {
                $pr['vendor_name'] = normalize_vendor_name($pr['vendor_name']);
            }
            if (isset($pr['changes']['donViVcPo'])) {
                $oldNorm = normalize_vendor_name($pr['changes']['donViVcPo']['old'] ?? '');
                $newNorm = normalize_vendor_name($pr['changes']['donViVcPo']['new'] ?? '');
                if ($oldNorm !== '' && to_clean_code($oldNorm) === to_clean_code($newNorm)) {
                    unset($pr['changes']['donViVcPo']);
                } else {
                    $pr['changes']['donViVcPo']['old'] = $oldNorm;
                    $pr['changes']['donViVcPo']['new'] = $newNorm;
                }
            }
            if (empty($pr['cont_size'])) {
                $cj = $pr['changes_json'] ?? '';
                if (strpos($cj, "40'") !== false || strpos($cj, '40"') !== false) {
                    $pr['cont_size'] = '40';
                } else {
                    if ($allShipmentsCache === null) {
                        $allShipmentsCache = fetch_vietsun_shipments(null, false);
                    }
                    $found = null;
                    foreach ($allShipmentsCache as $stmp) {
                        if ((string)($stmp['idPo'] ?? '') === (string)$pr['id_po']) {
                            $found = $stmp;
                            break;
                        }
                    }
                    if ($found) {
                        $pr['cont_size'] = ((int)($found['cont40Po'] ?? 0) > 0) ? '40' : '20';
                        try {
                            $pdo->prepare("UPDATE tms_pending_approvals SET cont_size = ? WHERE id = ?")->execute([$pr['cont_size'], $pr['id']]);
                        } catch (Exception $e) {}
                    } else {
                        $pr['cont_size'] = '20';
                    }
                }
            }
        }
        unset($pr);
    };

    // API Đồng bộ Google Sheet đã được vô hiệu hóa để tránh ghi đè dữ liệu chỉnh sửa trực tiếp trên admin.vietsun.site
    if ($route === 'admin/sync-sheet') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Tính năng đồng bộ từ Google Sheet đã được tắt. Mọi thông tin Nhà xe & Tài xế hiện được quản lý trực tiếp trên admin.vietsun.site!']);
        exit;
    }

    // API Lấy toàn bộ danh sách Vendor, Tài xế & Yêu cầu chờ duyệt cho Dashboard Quản trị
    if ($route === 'admin/overview') {
        $adminLevel = $authPayload['level'] ?? 'super';
        $isExport40 = ($adminLevel === 'export_40');

        $vendors = [];
        $drivers = [];
        if (!$isExport40) {
            $vendors = $pdo->query("
                SELECT v.*, v.phone AS contact_phone,
                       (SELECT COUNT(*) FROM drivers d WHERE d.vendor_id = v.id) AS driver_count,
                       (SELECT COUNT(*) FROM drivers d WHERE d.vendor_id = v.id) AS truck_count
                FROM vendors v
                WHERE v.vendor_code != 'VIETSUN'
                ORDER BY v.vendor_name ASC
            ")->fetchAll();

            $drivers = $pdo->query("
                SELECT d.*, d.driver_name AS full_name, v.vendor_name, v.vendor_code
                FROM drivers d
                JOIN vendors v ON d.vendor_id = v.id
                WHERE v.vendor_code != 'VIETSUN'
                ORDER BY v.vendor_name ASC, d.assigned_plate ASC
            ")->fetchAll();
        }

        $allPending = $pdo->query("
            SELECT * FROM tms_pending_approvals
            ORDER BY FIELD(status, 'PENDING', 'APPROVED', 'REJECTED'), id DESC
            LIMIT 250
        ")->fetchAll();

        $enrichPendingRows($allPending, $pdo);

        if ($isExport40) {
            $pending = array_values(array_filter($allPending, function($r) {
                return $r['xuat_nhap'] === 'X' && (string)($r['cont_size'] ?? '') === '40';
            }));
            $pendingCount = count(array_filter($pending, function($r) { return $r['status'] === 'PENDING'; }));
        } else {
            $pending = $allPending;
            $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM tms_pending_approvals WHERE status = 'PENDING'")->fetchColumn();
        }

        echo json_encode([
            'ok'               => true,
            'adminLevel'       => $adminLevel,
            'isExport40'       => $isExport40,
            'vendors'          => $vendors,
            'drivers'          => $drivers,
            'pendingCount'     => $pendingCount,
            'pendingApprovals' => $pending
        ]);
        exit;
    }

    // API Lấy danh sách thay đổi chờ duyệt (Dùng cho nút thông báo nhanh hoặc poll)
    if ($route === 'admin/pending-approvals') {
        $adminLevel = $authPayload['level'] ?? 'super';
        $isExport40 = ($adminLevel === 'export_40');

        $allPending = $pdo->query("
            SELECT * FROM tms_pending_approvals
            ORDER BY FIELD(status, 'PENDING', 'APPROVED', 'REJECTED'), id DESC
            LIMIT 250
        ")->fetchAll();

        $enrichPendingRows($allPending, $pdo);

        if ($isExport40) {
            $pending = array_values(array_filter($allPending, function($r) {
                return $r['xuat_nhap'] === 'X' && (string)($r['cont_size'] ?? '') === '40';
            }));
            $pendingCount = count(array_filter($pending, function($r) { return $r['status'] === 'PENDING'; }));
        } else {
            $pending = $allPending;
            $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM tms_pending_approvals WHERE status = 'PENDING'")->fetchColumn();
        }

        echo json_encode([
            'ok'               => true,
            'adminLevel'       => $adminLevel,
            'isExport40'       => $isExport40,
            'pendingCount'     => $pendingCount,
            'pendingApprovals' => $pending
        ]);
        exit;
    }

    // API Duyệt thông tin từ Vendor/Tài xế -> Đẩy chính thức về Vietsun TMS
    if ($route === 'admin/approve-update') {
        $adminLevel = $authPayload['level'] ?? 'super';
        $isExport40 = ($adminLevel === 'export_40');
        $id = isset($jsonBody['id']) ? (int)$jsonBody['id'] : 0;
        $approveAll = !empty($jsonBody['approveAll']);
        $scope = strtoupper(trim((string)($jsonBody['scope'] ?? '')));
        $reqIds = isset($jsonBody['ids']) && is_array($jsonBody['ids'])
            ? array_values(array_filter(array_map('intval', $jsonBody['ids'])))
            : [];

        if ($approveAll) {
            if ($isExport40 || $scope === 'EXPORT_40') {
                if (!empty($reqIds)) {
                    $inList = implode(',', $reqIds);
                    $rows = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' AND xuat_nhap = 'X' AND cont_size = '40' AND id IN ($inList) ORDER BY id ASC")->fetchAll();
                } else {
                    $rows = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' AND xuat_nhap = 'X' AND cont_size = '40' ORDER BY id ASC")->fetchAll();
                }
            } elseif ($scope === 'MAIN') {
                if (!empty($reqIds)) {
                    $inList = implode(',', $reqIds);
                    $rows = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' AND NOT (xuat_nhap = 'X' AND cont_size = '40') AND id IN ($inList) ORDER BY id ASC")->fetchAll();
                } else {
                    $rows = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' AND NOT (xuat_nhap = 'X' AND cont_size = '40') ORDER BY id ASC")->fetchAll();
                }
            } elseif (!empty($reqIds)) {
                $inList = implode(',', $reqIds);
                $rows = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' AND id IN ($inList) ORDER BY id ASC")->fetchAll();
            } else {
                // Mặc định Main View chỉ duyệt Nhập 20, Nhập 40, Xuất 20 (tránh duyệt nhầm Xuất 40)
                $rows = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' AND NOT (xuat_nhap = 'X' AND cont_size = '40') ORDER BY id ASC")->fetchAll();
            }
        } else {
            $st = $pdo->prepare("SELECT * FROM tms_pending_approvals WHERE id = ? AND status = 'PENDING' LIMIT 1");
            $st->execute([$id]);
            $row = $st->fetch();
            if ($row && $isExport40) {
                if ($row['xuat_nhap'] !== 'X' || (string)($row['cont_size'] ?? '') !== '40') {
                    http_response_code(403);
                    echo json_encode(['ok' => false, 'error' => 'Bạn chỉ có quyền duyệt yêu cầu của cont Hàng Xuất 40!']);
                    exit;
                }
            }
            $rows = $row ? [$row] : [];
        }

        if (!$rows) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Không tìm thấy yêu cầu chờ duyệt hợp lệ!']);
            exit;
        }

        $approvedCount = 0;
        $stMark = $pdo->prepare("UPDATE tms_pending_approvals SET status = 'APPROVED', reviewed_at = NOW() WHERE id = ?");
        foreach ($rows as $r) {
            $idPo = $r['id_po'];
            $codePo = $r['code_po'];
            $changes = json_decode($r['changes_json'] ?? '{}', true) ?: [];
            if (isset($changes['soXePo']['new']) && trim((string)$changes['soXePo']['new']) !== '') {
                $pCleanChk = to_clean_code($changes['soXePo']['new']);
                if ($pCleanChk) {
                    $stVChk = $pdo->prepare("
                        SELECT v.vendor_name
                        FROM drivers d
                        JOIN vendors v ON d.vendor_id = v.id
                        WHERE d.assigned_plate = ? AND v.vendor_code != 'VIETSUN'
                        LIMIT 1
                    ");
                    $stVChk->execute([$pCleanChk]);
                    $vFromAdmin = normalize_vendor_name($stVChk->fetchColumn() ?: '');
                    if ($vFromAdmin !== '') {
                        $changes['donViVcPo'] = [
                            'old' => $changes['donViVcPo']['old'] ?? '',
                            'new' => $vFromAdmin
                        ];
                    }
                }
            }
            foreach ($changes as $fieldName => $diff) {
                $newVal = $diff['new'] ?? '';
                if ($fieldName === 'donViVcPo') {
                    $newVal = normalize_vendor_name($newVal);
                }
                update_tms_field($idPo, $codePo, $fieldName, $newVal);
            }
            // Chốt cứng (Snapshot) tên tài xế thực chạy tại thời điểm duyệt đẩy TMS để bảo toàn lịch sử tính lương
            if (($r['actor_role'] ?? '') === 'DRIVER') {
                $parts = explode('-', (string)($r['actor_name'] ?? ''), 2);
                $pClean = to_clean_code($parts[0] ?? '');
                $dNameSnap = strtoupper(trim($parts[1] ?? ''));
                if ($pClean) {
                    $fakeTrip = [
                        'codePo'        => $codePo,
                        'ngayTraHang'   => $r['trip_date'] ?? '',
                        'xuatNhapPo'    => $r['xuat_nhap'] ?? '',
                        'cont40Po'      => ((string)($r['cont_size'] ?? '') === '40') ? 1 : 0,
                        'khoNhanHangPo' => $r['khach_kho'] ?? '',
                        'soContPo'      => $changes['soContPo']['new'] ?? ''
                    ];
                    lock_trip_driver_snapshot($pdo, $idPo, $pClean, $dNameSnap, '', '', '', $r['vendor_name'] ?? '', $fakeTrip);
                }
            } elseif (isset($changes['soXePo']['new']) && trim((string)$changes['soXePo']['new']) !== '') {
                $pClean = to_clean_code($changes['soXePo']['new']);
                if ($pClean) {
                    $fakeTrip = [
                        'codePo'        => $codePo,
                        'ngayTraHang'   => $r['trip_date'] ?? '',
                        'xuatNhapPo'    => $r['xuat_nhap'] ?? '',
                        'cont40Po'      => ((string)($r['cont_size'] ?? '') === '40') ? 1 : 0,
                        'khoNhanHangPo' => $r['khach_kho'] ?? '',
                        'soContPo'      => $changes['soContPo']['new'] ?? ''
                    ];
                    lock_trip_driver_snapshot($pdo, $idPo, $pClean, '', '', '', '', $r['vendor_name'] ?? '', $fakeTrip);
                }
            }
            $stMark->execute([$r['id']]);
            $approvedCount++;
        }

        echo json_encode([
            'ok'            => true,
            'approvedCount' => $approvedCount,
            'message'       => "Đã duyệt và đẩy $approvedCount yêu cầu vào Vietsun TMS thành công!"
        ]);
        exit;
    }

    // API Từ chối thay đổi từ Vendor/Tài xế (Giữ nguyên dữ liệu gốc trên TMS)
    if ($route === 'admin/reject-update') {
        $adminLevel = $authPayload['level'] ?? 'super';
        $isExport40 = ($adminLevel === 'export_40');
        $id = isset($jsonBody['id']) ? (int)$jsonBody['id'] : 0;
        $rejectAll = !empty($jsonBody['rejectAll']);

        if ($rejectAll) {
            if ($isExport40) {
                $pdo->exec("UPDATE tms_pending_approvals SET status = 'REJECTED', reviewed_at = NOW() WHERE status = 'PENDING' AND xuat_nhap = 'X' AND cont_size = '40'");
            } else {
                $pdo->exec("UPDATE tms_pending_approvals SET status = 'REJECTED', reviewed_at = NOW() WHERE status = 'PENDING'");
            }
        } elseif ($id > 0) {
            if ($isExport40) {
                $stChk = $pdo->prepare("SELECT xuat_nhap, cont_size FROM tms_pending_approvals WHERE id = ?");
                $stChk->execute([$id]);
                $chk = $stChk->fetch();
                if ($chk && ($chk['xuat_nhap'] !== 'X' || (string)($chk['cont_size'] ?? '') !== '40')) {
                    http_response_code(403);
                    echo json_encode(['ok' => false, 'error' => 'Bạn chỉ có quyền từ chối yêu cầu của cont Hàng Xuất 40!']);
                    exit;
                }
            }
            $st = $pdo->prepare("UPDATE tms_pending_approvals SET status = 'REJECTED', reviewed_at = NOW() WHERE id = ?");
            $st->execute([$id]);
        }
        echo json_encode(['ok' => true, 'message' => 'Đã từ chối yêu cầu thay đổi!']);
        exit;
    }

    // API Thêm / Sửa Vendor từ Admin Dashboard
    if ($route === 'admin/save-vendor') {
        $id = isset($jsonBody['id']) ? (int)$jsonBody['id'] : 0;
        $vName = strtoupper(trim($jsonBody['vendor_name'] ?? ''));
        $vCode = to_clean_code($jsonBody['vendor_code'] ?: $vName);
        $phone = trim($jsonBody['phone'] ?? ($jsonBody['contact_phone'] ?? ''));
        $pass  = trim($jsonBody['password_hash'] ?? ($jsonBody['password'] ?? '123456'));
        if (!$vName || !$vCode) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Tên Vendor']);
            exit;
        }
        if ($id > 0) {
            $st = $pdo->prepare("UPDATE vendors SET vendor_name = ?, vendor_code = ?, phone = ?, password_hash = ? WHERE id = ?");
            $st->execute([$vName, $vCode, $phone, $pass, $id]);
        } else {
            $st = $pdo->prepare("INSERT INTO vendors (vendor_name, vendor_code, phone, password_hash, is_active) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE vendor_name = VALUES(vendor_name), phone = VALUES(phone), password_hash = VALUES(password_hash)");
            $st->execute([$vName, $vCode, $phone, $pass]);
        }
        echo json_encode(['ok' => true, 'message' => 'Đã lưu thông tin Vendor!']);
        exit;
    }

    // API Xóa Vendor
    if ($route === 'admin/delete-vendor') {
        $id = (int)($jsonBody['id'] ?? 0);
        if ($id > 0) {
            $st = $pdo->prepare("DELETE FROM vendors WHERE id = ?");
            $st->execute([$id]);
        }
        echo json_encode(['ok' => true, 'message' => 'Đã xóa Vendor!']);
        exit;
    }

    // API Thêm / Sửa Xe & Tài xế từ Admin hoặc Vendor Dashboard
    if ($route === 'admin/save-driver') {
        $id = isset($jsonBody['id']) ? (int)$jsonBody['id'] : 0;
        $vendorId = (int)($jsonBody['vendor_id'] ?? 0);
        $plate = to_clean_code($jsonBody['assigned_plate'] ?? '');
        $dName = strtoupper(trim($jsonBody['driver_name'] ?? ($jsonBody['full_name'] ?? '')));
        $phone = trim($jsonBody['phone'] ?? '');
        $pin   = trim($jsonBody['pin_code'] ?? '123456');
        $cccd  = trim($jsonBody['cccd'] ?? '');
        $gplx  = trim($jsonBody['gplx'] ?? '');

        if (!$plate || !$vendorId) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Vui lòng chọn Nhà xe và nhập Biển số xe']);
            exit;
        }

        $finalDName = $dName ?: "Tài xế $plate";
        $changedByRole = ($authPayload['role'] ?? '') === 'vendor'
            ? ('VENDOR: ' . normalize_vendor_name($authPayload['vendorName'] ?? ''))
            : 'ADMIN';

        // Ghi nhận lịch sử bàn giao xe (lưu lại tài xế cũ trước khi cập nhật tài xế mới) & đồng bộ các yêu cầu PENDING
        record_driver_assignment_change($pdo, $plate, $vendorId, $finalDName, $phone, $cccd, $gplx, $changedByRole);

        if ($id > 0) {
            $st = $pdo->prepare("UPDATE drivers SET vendor_id = ?, assigned_plate = ?, driver_name = ?, phone = ?, pin_code = ?, cccd = ?, gplx = ? WHERE id = ?");
            $st->execute([$vendorId, $plate, $finalDName, $phone, $pin ?: '123456', $cccd, $gplx, $id]);
        } else {
            $st = $pdo->prepare("
                INSERT INTO drivers (vendor_id, assigned_plate, driver_name, phone, pin_code, cccd, gplx, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                    vendor_id = VALUES(vendor_id),
                    driver_name = VALUES(driver_name),
                    phone = VALUES(phone),
                    pin_code = VALUES(pin_code),
                    cccd = VALUES(cccd),
                    gplx = VALUES(gplx)
            ");
            $st->execute([$vendorId, $plate, $finalDName, $phone, $pin ?: '123456', $cccd, $gplx]);
        }
        echo json_encode(['ok' => true, 'message' => 'Đã lưu thông tin Xe & Tài xế và ghi nhận lịch sử bàn giao xe!']);
        exit;
    }

    // API Xem Lịch sử bàn giao xe & Thống kê chuyến theo từng Tài xế (kể cả tài xế cũ đã đổi xe) để tính lương
    if ($route === 'admin/driver-history') {
        $filterPlate = to_clean_code($_GET['plate'] ?? '');
        $filterVendorId = (int)($_GET['vendor_id'] ?? 0);
        if (($authPayload['role'] ?? '') === 'vendor') {
            $filterVendorId = (int)($authPayload['vendorId'] ?? 0);
        }

        $whereHist = [];
        $paramsHist = [];
        if ($filterPlate !== '') {
            $whereHist[] = "assigned_plate = ?";
            $paramsHist[] = $filterPlate;
        }
        if ($filterVendorId > 0) {
            $whereHist[] = "vendor_id = ?";
            $paramsHist[] = $filterVendorId;
        }
        $sqlHist = "SELECT * FROM driver_assignment_history";
        if (!empty($whereHist)) {
            $sqlHist .= " WHERE " . implode(" AND ", $whereHist);
        }
        $sqlHist .= " ORDER BY assigned_plate ASC, id DESC LIMIT 500";
        $stH = $pdo->prepare($sqlHist);
        $stH->execute($paramsHist);
        $historyList = $stH->fetchAll();

        $whereSnap = [];
        $paramsSnap = [];
        if ($filterPlate !== '') {
            $whereSnap[] = "assigned_plate = ?";
            $paramsSnap[] = $filterPlate;
        }
        if ($filterVendorId > 0) {
            $stVName = $pdo->prepare("SELECT vendor_name FROM vendors WHERE id = ? LIMIT 1");
            $stVName->execute([$filterVendorId]);
            $vNameForSnap = normalize_vendor_name($stVName->fetchColumn() ?: '');
            if ($vNameForSnap !== '') {
                $whereSnap[] = "vendor_name = ?";
                $paramsSnap[] = $vNameForSnap;
            }
        }
        $sqlSnap = "SELECT * FROM trip_driver_snapshots";
        if (!empty($whereSnap)) {
            $sqlSnap .= " WHERE " . implode(" AND ", $whereSnap);
        }
        $sqlSnap .= " ORDER BY trip_date DESC, id_po DESC LIMIT 1000";
        $stS = $pdo->prepare($sqlSnap);
        $stS->execute($paramsSnap);
        $snapshotsList = $stS->fetchAll();

        echo json_encode([
            'ok'        => true,
            'history'   => $historyList,
            'snapshots' => $snapshotsList
        ]);
        exit;
    }

    // API Xóa Xe / Tài xế
    if ($route === 'admin/delete-driver') {
        $id = (int)($jsonBody['id'] ?? 0);
        if ($id > 0) {
            $st = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
            $st->execute([$id]);
        }
        echo json_encode(['ok' => true, 'message' => 'Đã xóa xe khỏi danh sách!']);
        exit;
    }

    // ========================================================================
    // PHÂN HỆ GIÁ CƯỚC & ĐỊNH DANH TUYẾN ĐƯỜNG / MÃ KHO 2026 (vietsun.site/cuoc)
    // ========================================================================
    if (strpos($route, 'cuoc/') === 0) {
        $ensureCuocTables = function($pdo) {
            $flagFile = sys_get_temp_dir() . '/vietsun_cuoc_schema_v5.flag';
            if (file_exists($flagFile)) {
                return;
            }
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS tms_pricing_config (
                    config_key VARCHAR(64) PRIMARY KEY,
                    config_val LONGTEXT NOT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS tms_routes (
                    route_code VARCHAR(32) PRIMARY KEY,
                    dia_diem VARCHAR(255) NOT NULL,
                    tinh VARCHAR(100) NOT NULL DEFAULT '',
                    base_rates_json LONGTEXT NOT NULL,
                    period_rates_json LONGTEXT NULL,
                    notes VARCHAR(255) DEFAULT '',
                    sort_order INT NOT NULL DEFAULT 0,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            try {
                $pdo->exec("ALTER TABLE tms_routes ADD COLUMN period_rates_json LONGTEXT NULL AFTER base_rates_json");
            } catch (Exception $e) {}
            try {
                $pdo->exec("ALTER TABLE tms_routes MODIFY COLUMN base_rates_json LONGTEXT NOT NULL");
                $pdo->exec("ALTER TABLE tms_pricing_config MODIFY COLUMN config_val LONGTEXT NOT NULL");
            } catch (Exception $e) {}

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS tms_warehouses_2026 (
                    wh_code VARCHAR(32) PRIMARY KEY,
                    kho_name VARCHAR(255) NOT NULL DEFAULT '',
                    dia_chi TEXT,
                    tinh_tms VARCHAR(100) DEFAULT '',
                    customers TEXT,
                    route_code VARCHAR(32) DEFAULT '',
                    match_confidence VARCHAR(20) DEFAULT 'NONE',
                    match_reason VARCHAR(255) DEFAULT '',
                    total_count INT NOT NULL DEFAULT 0,
                    nhap_count INT NOT NULL DEFAULT 0,
                    xuat_count INT NOT NULL DEFAULT 0,
                    custom_rates_json TEXT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_route_code (route_code),
                    INDEX idx_total_count (total_count)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Seed or sync from cuoc_seed_2026.json
            $routeCnt = (int)$pdo->query("SELECT COUNT(*) FROM tms_routes")->fetchColumn();
            $whCnt = (int)$pdo->query("SELECT COUNT(*) FROM tms_warehouses_2026")->fetchColumn();
            $seedFile = __DIR__ . '/cuoc_seed_2026.json';
            if (file_exists($seedFile)) {
                $seed = json_decode(file_get_contents($seedFile), true);
                if (is_array($seed)) {
                    // Build lookup map from 21/07 VAT integer -> exact 21/07 pre-VAT float
                    $noVat2107Map = [];
                    if (!empty($seed['routes'])) {
                        foreach ($seed['routes'] as $sr) {
                            $p21 = $sr['period_rates']['2026-07-21'] ?? [];
                            foreach ($p21 as $cObj) {
                                if (!empty($cObj['c20']) && !empty($cObj['c20_no_vat'])) {
                                    $noVat2107Map[(int)$cObj['c20']] = (float)$cObj['c20_no_vat'];
                                }
                                if (!empty($cObj['c40']) && !empty($cObj['c40_no_vat'])) {
                                    $noVat2107Map[(int)$cObj['c40']] = (float)$cObj['c40_no_vat'];
                                }
                            }
                        }
                    }

                    // Existing routes in DB (to preserve any custom vendor columns added by user)
                    $existingRoutesMap = [];
                    if ($routeCnt > 0) {
                        $exRows = $pdo->query("SELECT route_code, base_rates_json, period_rates_json FROM tms_routes")->fetchAll();
                        foreach ($exRows as $er) {
                            $existingRoutesMap[$er['route_code']] = [
                                'base'    => json_decode($er['base_rates_json'] ?? '{}', true) ?: [],
                                'periods' => json_decode($er['period_rates_json'] ?? '{}', true) ?: []
                            ];
                        }
                    }

                    $rawCarriers = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'carriers_json'")->fetchColumn();
                    $activeCarriers = json_decode($rawCarriers ?: '[]', true) ?: ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];
                    $rawLinks = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'vendor_links_json'")->fetchColumn();
                    $vendorLinks = json_decode($rawLinks ?: '{}', true) ?: [];
                    $default6Carriers = ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];

                    // Detect or default custom vendors to link with NHAT SON so they auto-jump prices with NHAT SON
                    foreach ($activeCarriers as $ac) {
                        if (in_array($ac, $default6Carriers, true)) continue;
                        if (empty($vendorLinks[$ac])) {
                            $vendorLinks[$ac] = ['source' => 'NHAT SON', 'factor_percent' => 100.0, 'delta_amount' => 0];
                        }
                    }

                    $pdo->beginTransaction();
                    try {
                        if (!empty($seed['routes'])) {
                            $stUpsertR = $pdo->prepare("
                                INSERT INTO tms_routes (route_code, dia_diem, tinh, base_rates_json, period_rates_json, sort_order)
                                VALUES (?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE
                                    dia_diem = VALUES(dia_diem),
                                    tinh = VALUES(tinh),
                                    base_rates_json = VALUES(base_rates_json),
                                    period_rates_json = VALUES(period_rates_json),
                                    sort_order = VALUES(sort_order)
                            ");
                            foreach ($seed['routes'] as $idx => $r) {
                                $rc = $r['route_code'] ?? '';
                                $pRates = $r['period_rates'] ?? [];
                                $exData = $existingRoutesMap[$rc] ?? null;

                                // If user added custom vendors on VPS, sync their 21/07, 28/07, 25/08 (INT) snapshots from their linked source carrier (default NHAT SON)
                                foreach ($activeCarriers as $ac) {
                                    if (isset($pRates['2026-08-25'][$ac])) {
                                        continue;
                                    }
                                    $lnk = $vendorLinks[$ac] ?? ['source' => 'NHAT SON', 'factor_percent' => 100.0, 'delta_amount' => 0];
                                    $srcKey = !empty($lnk['source']) && in_array($lnk['source'], $default6Carriers, true) ? $lnk['source'] : 'NHAT SON';
                                    $fPct = isset($lnk['factor_percent']) ? (float)$lnk['factor_percent'] : 100.0;
                                    $dAmt = isset($lnk['delta_amount']) ? (int)$lnk['delta_amount'] : 0;

                                    if ($fPct == 100.0 && $dAmt == 0) {
                                        foreach (['2026-07-21', '2026-07-28', '2026-08-25'] as $pKey) {
                                            $pRates[$pKey][$ac] = $pRates[$pKey][$srcKey] ?? ($pRates[$pKey]['NHAT SON'] ?? ($pRates[$pKey]['VIETSUN'] ?? ['c20' => 0, 'c40' => 0, 'c20_no_vat' => 0, 'c40_no_vat' => 0]));
                                        }
                                    } else {
                                        $src21 = $pRates['2026-07-21'][$srcKey] ?? ($pRates['2026-07-21']['NHAT SON'] ?? ['c20' => 0, 'c40' => 0, 'c20_no_vat' => 0, 'c40_no_vat' => 0]);
                                        $nv20_21 = !empty($src21['c20_no_vat']) ? max(0.0, (float)$src21['c20_no_vat'] * ($fPct / 100.0) + ($dAmt / 1.08)) : 0.0;
                                        $nv40_21 = !empty($src21['c40_no_vat']) ? max(0.0, (float)$src21['c40_no_vat'] * ($fPct / 100.0) + ($dAmt / 1.08)) : 0.0;
                                        $pRates['2026-07-21'][$ac] = [
                                            'c20_no_vat' => $nv20_21,
                                            'c40_no_vat' => $nv40_21,
                                            'c20'        => $nv20_21 > 0 ? (int)round($nv20_21 * 1.08) : 0,
                                            'c40'        => $nv40_21 > 0 ? (int)round($nv40_21 * 1.08) : 0
                                        ];

                                        $nv20_28 = $nv20_21 * 1.032;
                                        $nv40_28 = $nv40_21 * 1.032;
                                        $pRates['2026-07-28'][$ac] = [
                                            'c20_no_vat' => $nv20_28,
                                            'c40_no_vat' => $nv40_28,
                                            'c20'        => $nv20_28 > 0 ? (int)round($nv20_28 * 1.08) : 0,
                                            'c40'        => $nv40_28 > 0 ? (int)round($nv40_28 * 1.08) : 0
                                        ];

                                        $nv20_25 = $nv20_28 > 0 ? (int)floor($nv20_28 * 1.032 + 1e-9) : 0;
                                        $nv40_25 = $nv40_28 > 0 ? (int)floor($nv40_28 * 1.032 + 1e-9) : 0;
                                        $pRates['2026-08-25'][$ac] = [
                                            'c20_no_vat' => $nv20_25,
                                            'c40_no_vat' => $nv40_25,
                                            'c20'        => $nv20_25 > 0 ? (int)floor($nv20_25 * 1.08 + 1e-9) : 0,
                                            'c40'        => $nv40_25 > 0 ? (int)floor($nv40_25 * 1.08 + 1e-9) : 0
                                        ];
                                    }
                                }

                                $activeBase = $pRates['2026-08-25'] ?? ($r['base_rates'] ?? []);
                                $stUpsertR->execute([
                                    $rc,
                                    $r['dia_diem'] ?? '',
                                    $r['tinh'] ?? '',
                                    json_encode($activeBase, JSON_UNESCAPED_UNICODE),
                                    json_encode($pRates, JSON_UNESCAPED_UNICODE),
                                    $idx + 1
                                ]);
                            }
                        }
                        if ($whCnt === 0 && !empty($seed['warehouses'])) {
                            $stW = $pdo->prepare("
                                INSERT IGNORE INTO tms_warehouses_2026
                                (wh_code, kho_name, dia_chi, tinh_tms, customers, route_code, match_confidence, match_reason, total_count, nhap_count, xuat_count, custom_rates_json)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            foreach ($seed['warehouses'] as $w) {
                                $stW->execute([
                                    $w['wh_code'] ?? '',
                                    mb_substr((string)($w['kho_name'] ?? ''), 0, 250),
                                    (string)($w['dia_chi'] ?? ''),
                                    mb_substr((string)($w['tinh_tms'] ?? ''), 0, 95),
                                    (string)($w['customers'] ?? ''),
                                    (string)($w['route_code'] ?? ''),
                                    (string)($w['match_confidence'] ?? 'NONE'),
                                    mb_substr((string)($w['match_reason'] ?? ''), 0, 250),
                                    (int)($w['total_count'] ?? 0),
                                    (int)($w['nhap_count'] ?? 0),
                                    (int)($w['xuat_count'] ?? 0),
                                    !empty($w['custom_rates']) ? json_encode($w['custom_rates'], JSON_UNESCAPED_UNICODE) : null
                                ]);
                            }
                        } elseif (!empty($seed['warehouses'])) {
                            $stUpWh = $pdo->prepare("
                                UPDATE tms_warehouses_2026
                                SET route_code = ?, match_confidence = ?, match_reason = ?, custom_rates_json = ?
                                WHERE wh_code = ? AND (route_code = '' OR route_code IS NULL)
                            ");
                            foreach ($seed['warehouses'] as $w) {
                                $rc = (string)($w['route_code'] ?? '');
                                $mc = (string)($w['match_confidence'] ?? 'NONE');
                                if ($rc !== '' || $mc === 'SPECIAL' || $mc === 'EXCEPTION') {
                                    $stUpWh->execute([
                                        $rc,
                                        $mc,
                                        mb_substr((string)($w['match_reason'] ?? ''), 0, 250),
                                        !empty($w['custom_rates']) ? json_encode($w['custom_rates'], JSON_UNESCAPED_UNICODE) : null,
                                        $w['wh_code'] ?? ''
                                    ]);
                                }
                            }
                        }

                        $stCfgUp = $pdo->prepare("
                            INSERT INTO tms_pricing_config (config_key, config_val)
                            VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
                        ");
                        $stCfgUp->execute(['active_period_id', (string)($seed['active_period_id'] ?? '2026-08-25')]);
                        $stCfgUp->execute(['periods_json', json_encode($seed['periods'] ?? [], JSON_UNESCAPED_UNICODE)]);
                        $stCfgUp->execute(['adj_percent', '0.032']);
                        $stCfgUp->execute(['do_base_price', '28540']);
                        $stCfgUp->execute(['effective_date', '2026-08-25']);
                        $stCfgUp->execute(['vendor_links_json', json_encode($vendorLinks, JSON_UNESCAPED_UNICODE)]);

                        $stCfgIgnore = $pdo->prepare("INSERT IGNORE INTO tms_pricing_config (config_key, config_val) VALUES (?, ?)");
                        $stCfgIgnore->execute(['notes_json', json_encode($seed['notes'] ?? [], JSON_UNESCAPED_UNICODE)]);
                        $stCfgIgnore->execute(['carriers_json', json_encode(['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'], JSON_UNESCAPED_UNICODE)]);
                        $pdo->commit();
                    } catch (Exception $ex) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                    }
                }
            }

            // Chuẩn hóa Tỉnh sau sáp nhập trên cả bảng tms_routes & tms_warehouses_2026
            $mergeSqlMap = [
                'HCM'       => ['Bình Dương', 'BINH DUONG', 'BRVT', 'Bà Rịa - Vũng Tàu', 'BA RIA VUNG TAU', 'HO CHI MINH', 'TP.HCM', 'TPHCM'],
                'Lâm Đồng'  => ['Bình Thuận', 'BINH THUAN', 'Đăk Nông', 'Đắk Nông', 'DAK NONG', 'LAM DONG'],
                'Đồng Nai'  => ['Bình Phước', 'BINH PHUOC', 'DONG NAI'],
                'Tây Ninh'  => ['Long An', 'LONG AN', 'TAY NINH'],
                'Đồng Tháp' => ['Tiền Giang', 'TIEN GIANG', 'DONG THAP'],
                'Vĩnh Long' => ['Trà Vinh', 'TRA VINH', 'Bến Tre', 'BEN TRE', 'VINH LONG'],
                'Cần Thơ'   => ['Hậu Giang', 'HAU GIANG', 'Sóc Trăng', 'SOC TRANG', 'CAN THO'],
                'Cà Mau'    => ['Bạc Liêu', 'BAC LIEU', 'CA MAU'],
                'An Giang'  => ['Kiên Giang', 'KIEN GIANG', 'AN GIANG']
            ];
            foreach ($mergeSqlMap as $newProv => $oldList) {
                $placeholders = implode(',', array_fill(0, count($oldList), '?'));
                $params = array_merge([$newProv], $oldList);
                $stUpR = $pdo->prepare("UPDATE tms_routes SET tinh = ? WHERE tinh IN ($placeholders)");
                $stUpR->execute($params);
                $stUpW = $pdo->prepare("UPDATE tms_warehouses_2026 SET tinh_tms = ? WHERE tinh_tms IN ($placeholders)");
                $stUpW->execute($params);
            }

            @file_put_contents($flagFile, date('c'));
        };

        $ensureCuocTables($pdo);

        // GET cuoc/overview: Trả về toàn bộ Tuyến đường (kèm lịch sử các kỳ điều chỉnh), Cấu hình & Danh sách Kho 2026
        if ($route === 'cuoc/overview') {
            $cfgRows = $pdo->query("SELECT config_key, config_val FROM tms_pricing_config")->fetchAll();
            $cfg = [];
            foreach ($cfgRows as $cr) {
                $cfg[$cr['config_key']] = $cr['config_val'];
            }
            $adjPercent = isset($cfg['adj_percent']) ? (float)$cfg['adj_percent'] : 0.032;
            $activePeriodId = $cfg['active_period_id'] ?? '2026-08-25';
            $periods = json_decode($cfg['periods_json'] ?? '[]', true) ?: [
                [
                    'id'             => '2026-07-21',
                    'effective_date' => '2026-07-21',
                    'label'          => 'Kỳ 21/07/2026 (Dầu DO 23.320 đ/lít | +3.0%)',
                    'do_price'       => 23320,
                    'adj_percent'    => 3.0,
                    'base_period_id' => '2026-07-07'
                ],
                [
                    'id'             => '2026-07-28',
                    'effective_date' => '2026-07-28',
                    'label'          => 'Kỳ 28/07/2026 (Dầu DO 25.760 đ/lít | +3.2%)',
                    'do_price'       => 25760,
                    'adj_percent'    => 3.2,
                    'base_period_id' => '2026-07-21'
                ],
                [
                    'id'             => '2026-08-25',
                    'effective_date' => '2026-08-25',
                    'label'          => 'Kỳ 25/08/2026 (Dầu DO 28.540 đ/lít | +3.2%)',
                    'do_price'       => 28540,
                    'adj_percent'    => 3.2,
                    'base_period_id' => '2026-07-28'
                ]
            ];
            $carriers = json_decode($cfg['carriers_json'] ?? '[]', true) ?: ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];
            $vendorLinks = json_decode($cfg['vendor_links_json'] ?? '{}', true) ?: [];
            $notes = json_decode($cfg['notes_json'] ?? '[]', true) ?: [];

            $routesRaw = $pdo->query("SELECT * FROM tms_routes ORDER BY sort_order ASC, route_code ASC")->fetchAll();
            $routes = [];
            foreach ($routesRaw as $rr) {
                $baseRates = json_decode($rr['base_rates_json'] ?? '{}', true) ?: [];
                $periodRates = !empty($rr['period_rates_json']) ? (json_decode($rr['period_rates_json'], true) ?: []) : [];
                if (isset($periodRates[$activePeriodId])) {
                    $baseRates = $periodRates[$activePeriodId];
                }
                $routes[] = [
                    'route_code'   => $rr['route_code'],
                    'dia_diem'     => $rr['dia_diem'],
                    'tinh'         => $rr['tinh'],
                    'notes'        => $rr['notes'] ?? '',
                    'sort_order'   => (int)$rr['sort_order'],
                    'base_rates'   => $baseRates,
                    'period_rates' => $periodRates
                ];
            }

            $whRaw = $pdo->query("SELECT * FROM tms_warehouses_2026 ORDER BY total_count DESC, wh_code ASC")->fetchAll();
            $warehouses = [];
            foreach ($whRaw as $wr) {
                $warehouses[] = [
                    'wh_code'          => $wr['wh_code'],
                    'kho_name'         => $wr['kho_name'],
                    'dia_chi'          => $wr['dia_chi'],
                    'tinh_tms'         => $wr['tinh_tms'],
                    'customers'        => $wr['customers'],
                    'route_code'       => $wr['route_code'] ?? '',
                    'match_confidence' => $wr['match_confidence'] ?? 'NONE',
                    'match_reason'     => $wr['match_reason'] ?? '',
                    'total_count'      => (int)$wr['total_count'],
                    'nhap_count'       => (int)$wr['nhap_count'],
                    'xuat_count'       => (int)$wr['xuat_count'],
                    'custom_rates'     => !empty($wr['custom_rates_json']) ? (json_decode($wr['custom_rates_json'], true) ?: null) : null
                ];
            }

            echo json_encode([
                'ok'         => true,
                'config'     => [
                    'adj_percent'      => $adjPercent,
                    'do_base_price'    => (int)($cfg['do_base_price'] ?? 28540),
                    'effective_date'   => $cfg['effective_date'] ?? '2026-08-25',
                    'active_period_id' => $activePeriodId,
                    'periods'          => $periods,
                    'carriers'         => $carriers,
                    'vendor_links'     => $vendorLinks,
                    'notes'            => $notes
                ],
                'routes'     => $routes,
                'warehouses' => $warehouses
            ]);
            exit;
        }

        // POST cuoc/create-period: Tạo đợt điều chỉnh giá cước mới (Lấy giá chưa gồm VAT kỳ trước × (1 + %) -> sau đó × 1.08 VAT 8% làm tròn hàng đơn vị) và lưu bảng giá đã điều chỉnh
        if ($route === 'cuoc/create-period') {
            $effectiveDate = trim((string)($jsonBody['effective_date'] ?? date('Y-m-d')));
            $doPrice = (int)($jsonBody['do_price'] ?? 28540);
            $adjPercent = isset($jsonBody['adj_percent']) ? (float)$jsonBody['adj_percent'] : 0.0;
            $basePeriodId = trim((string)($jsonBody['base_period_id'] ?? ''));
            $customLabel = trim((string)($jsonBody['label'] ?? ''));

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $effectiveDate)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Ngày áp dụng không hợp lệ (Định dạng chuẩn: YYYY-MM-DD)!']);
                exit;
            }

            $rawPeriods = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'periods_json'")->fetchColumn();
            $periods = json_decode($rawPeriods ?: '[]', true) ?: [];
            $curActiveId = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'active_period_id'")->fetchColumn() ?: '2026-08-25';
            if ($basePeriodId === '') {
                $basePeriodId = $curActiveId;
            }

            // Tạo ID duy nhất cho kỳ điều chỉnh
            $periodId = $effectiveDate;
            $overwrite = !empty($jsonBody['overwrite']);
            if (!$overwrite) {
                $suffix = 2;
                $existingIds = array_column($periods, 'id');
                while (in_array($periodId, $existingIds, true)) {
                    $periodId = $effectiveDate . '-v' . $suffix;
                    $suffix++;
                }
            }

            $parts = explode('-', $effectiveDate);
            $vnDate = count($parts) === 3 ? ($parts[2] . '/' . $parts[1] . '/' . $parts[0]) : $effectiveDate;
            $signStr = ($adjPercent >= 0 ? '+' : '') . rtrim(rtrim(number_format($adjPercent, 2, '.', ''), '0'), '.') . '%';
            $doFmt = number_format($doPrice, 0, ',', '.');
            $label = $customLabel !== '' ? $customLabel : "Kỳ {$vnDate} (Dầu DO {$doFmt} đ/lít | {$signStr})";

            $factor = 1.0 + ($adjPercent / 100.0);
            $rawCarriers = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'carriers_json'")->fetchColumn();
            $carriers = json_decode($rawCarriers ?: '[]', true) ?: ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];
            $rawLinks = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'vendor_links_json'")->fetchColumn();
            $vendorLinks = json_decode($rawLinks ?: '{}', true) ?: [];

            $allRoutes = $pdo->query("SELECT route_code, base_rates_json, period_rates_json FROM tms_routes")->fetchAll();
            $stUpR = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ?, period_rates_json = ? WHERE route_code = ?");

            $pdo->beginTransaction();
            try {
                foreach ($allRoutes as $rr) {
                    $br = json_decode($rr['base_rates_json'] ?? '{}', true) ?: [];
                    $pr = !empty($rr['period_rates_json']) ? (json_decode($rr['period_rates_json'], true) ?: []) : [];
                    $srcTable = $pr[$basePeriodId] ?? $br;

                    $newTable = [];
                    // Tính chuẩn VIETSUN theo hàm INT chuẩn Excel: INT(no_vat * factor) -> INT(new_no_vat * 1.08)
                    $vsSrc = $srcTable['VIETSUN'] ?? ['c20' => 0, 'c40' => 0, 'c20_no_vat' => 0, 'c40_no_vat' => 0];
                    $vsNoVat20 = !empty($vsSrc['c20_no_vat']) ? (float)$vsSrc['c20_no_vat'] : (!empty($vsSrc['c20']) ? ((float)$vsSrc['c20'] / 1.08) : 0.0);
                    $vsNoVat40 = !empty($vsSrc['c40_no_vat']) ? (float)$vsSrc['c40_no_vat'] : (!empty($vsSrc['c40']) ? ((float)$vsSrc['c40'] / 1.08) : 0.0);

                    $newVsNoVat20 = $vsNoVat20 > 0 ? (int)floor($vsNoVat20 * $factor + 1e-9) : 0;
                    $newVsNoVat40 = $vsNoVat40 > 0 ? (int)floor($vsNoVat40 * $factor + 1e-9) : 0;
                    $newVsVat20 = $newVsNoVat20 > 0 ? (int)floor($newVsNoVat20 * 1.08 + 1e-9) : 0;
                    $newVsVat40 = $newVsNoVat40 > 0 ? (int)floor($newVsNoVat40 * 1.08 + 1e-9) : 0;

                    $newTable['VIETSUN'] = [
                        'c20_no_vat' => $newVsNoVat20,
                        'c40_no_vat' => $newVsNoVat40,
                        'c20'        => $newVsVat20,
                        'c40'        => $newVsVat40
                    ];

                    // B1: Tính các vendor gốc (không phụ thuộc hoặc chính là nguồn)
                    foreach ($carriers as $cName) {
                        if ($cName === 'VIETSUN') continue;
                        $cSrc = $srcTable[$cName] ?? [];
                        $srcNoVat20 = !empty($cSrc['c20_no_vat']) ? (float)$cSrc['c20_no_vat'] : (!empty($cSrc['c20']) ? ((float)$cSrc['c20'] / 1.08) : $vsNoVat20);
                        $srcNoVat40 = !empty($cSrc['c40_no_vat']) ? (float)$cSrc['c40_no_vat'] : (!empty($cSrc['c40']) ? ((float)$cSrc['c40'] / 1.08) : $vsNoVat40);

                        $newNoVat20 = $srcNoVat20 > 0 ? (int)floor($srcNoVat20 * $factor + 1e-9) : 0;
                        $newNoVat40 = $srcNoVat40 > 0 ? (int)floor($srcNoVat40 * $factor + 1e-9) : 0;
                        $newTable[$cName] = [
                            'c20_no_vat' => $newNoVat20,
                            'c40_no_vat' => $newNoVat40,
                            'c20'        => $newNoVat20 > 0 ? (int)floor($newNoVat20 * 1.08 + 1e-9) : 0,
                            'c40'        => $newNoVat40 > 0 ? (int)floor($newNoVat40 * 1.08 + 1e-9) : 0
                        ];
                    }

                    // B2: Các vendor liên kết (VD: lấy theo giá NHAT SON) tự động nhảy giá theo đúng vendor nguồn
                    foreach ($carriers as $cName) {
                        if ($cName === 'VIETSUN' || empty($vendorLinks[$cName]['source'])) continue;
                        $lnk = $vendorLinks[$cName];
                        $srcCarrier = $lnk['source'];
                        if ($srcCarrier === $cName || !isset($newTable[$srcCarrier])) continue;
                        $fPct = isset($lnk['factor_percent']) ? (float)$lnk['factor_percent'] : 100.0;
                        $dAmt = isset($lnk['delta_amount']) ? (int)$lnk['delta_amount'] : 0;
                        if ($fPct == 100.0 && $dAmt == 0) {
                            $newTable[$cName] = $newTable[$srcCarrier];
                        } else {
                            $sObj = $newTable[$srcCarrier];
                            $nv20 = !empty($sObj['c20_no_vat']) ? (int)floor(max(0.0, (float)$sObj['c20_no_vat'] * ($fPct / 100.0) + ($dAmt / 1.08)) + 1e-9) : 0;
                            $nv40 = !empty($sObj['c40_no_vat']) ? (int)floor(max(0.0, (float)$sObj['c40_no_vat'] * ($fPct / 100.0) + ($dAmt / 1.08)) + 1e-9) : 0;
                            $newTable[$cName] = [
                                'c20_no_vat' => $nv20,
                                'c40_no_vat' => $nv40,
                                'c20'        => $nv20 > 0 ? (int)floor($nv20 * 1.08 + 1e-9) : 0,
                                'c40'        => $nv40 > 0 ? (int)floor($nv40 * 1.08 + 1e-9) : 0
                            ];
                        }
                    }

                    $pr[$periodId] = $newTable;
                    $stUpR->execute([
                        json_encode($newTable, JSON_UNESCAPED_UNICODE),
                        json_encode($pr, JSON_UNESCAPED_UNICODE),
                        $rr['route_code']
                    ]);
                }

                $newPeriodMeta = [
                    'id'             => $periodId,
                    'effective_date' => $effectiveDate,
                    'label'          => $label,
                    'do_price'       => $doPrice,
                    'adj_percent'    => $adjPercent,
                    'base_period_id' => $basePeriodId,
                    'created_at'     => date('Y-m-d H:i:s')
                ];
                $foundIdx = -1;
                foreach ($periods as $pIdx => $pm) {
                    if (($pm['id'] ?? '') === $periodId) {
                        $foundIdx = $pIdx;
                        break;
                    }
                }
                if ($foundIdx >= 0) {
                    $periods[$foundIdx] = $newPeriodMeta;
                } else {
                    $periods[] = $newPeriodMeta;
                }

                $stCfg = $pdo->prepare("
                    INSERT INTO tms_pricing_config (config_key, config_val)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
                ");
                $stCfg->execute(['periods_json', json_encode(array_values($periods), JSON_UNESCAPED_UNICODE)]);
                $stCfg->execute(['active_period_id', $periodId]);
                $stCfg->execute(['effective_date', $effectiveDate]);
                $stCfg->execute(['do_base_price', (string)$doPrice]);
                $stCfg->execute(['adj_percent', (string)($adjPercent / 100.0)]);
                $pdo->commit();
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $ex;
            }

            echo json_encode([
                'ok'               => true,
                'active_period_id' => $periodId,
                'periods'          => array_values($periods),
                'message'          => "Đã tạo và lưu bảng giá điều chỉnh [$label] thành công!"
            ]);
            exit;
        }

        // POST cuoc/set-active-period: Chuyển đổi kỳ bảng giá đang áp dụng mặc định
        if ($route === 'cuoc/set-active-period') {
            $periodId = trim((string)($jsonBody['period_id'] ?? ''));
            $rawPeriods = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'periods_json'")->fetchColumn();
            $periods = json_decode($rawPeriods ?: '[]', true) ?: [];
            $targetMeta = null;
            foreach ($periods as $pm) {
                if (($pm['id'] ?? '') === $periodId) {
                    $targetMeta = $pm;
                    break;
                }
            }
            if (!$targetMeta) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Không tìm thấy kỳ điều chỉnh giá này!']);
                exit;
            }

            $allRoutes = $pdo->query("SELECT route_code, base_rates_json, period_rates_json FROM tms_routes")->fetchAll();
            $stUpR = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ? WHERE route_code = ?");
            $pdo->beginTransaction();
            try {
                foreach ($allRoutes as $rr) {
                    $pr = !empty($rr['period_rates_json']) ? (json_decode($rr['period_rates_json'], true) ?: []) : [];
                    if (isset($pr[$periodId])) {
                        $stUpR->execute([json_encode($pr[$periodId], JSON_UNESCAPED_UNICODE), $rr['route_code']]);
                    }
                }
                $stCfg = $pdo->prepare("
                    INSERT INTO tms_pricing_config (config_key, config_val)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
                ");
                $stCfg->execute(['active_period_id', $periodId]);
                $stCfg->execute(['effective_date', $targetMeta['effective_date'] ?? $periodId]);
                $stCfg->execute(['do_base_price', (string)($targetMeta['do_price'] ?? 28540)]);
                $stCfg->execute(['adj_percent', (string)(((float)($targetMeta['adj_percent'] ?? 3.2)) / 100.0)]);
                $pdo->commit();
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $ex;
            }

            echo json_encode([
                'ok'               => true,
                'active_period_id' => $periodId,
                'message'          => "Đã chuyển bảng giá áp dụng sang [{$targetMeta['label']}]!"
            ]);
            exit;
        }

        // POST cuoc/delete-period: Xóa một kỳ điều chỉnh giá khỏi lịch sử
        if ($route === 'cuoc/delete-period') {
            $periodId = trim((string)($jsonBody['period_id'] ?? ''));
            $rawPeriods = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'periods_json'")->fetchColumn();
            $periods = json_decode($rawPeriods ?: '[]', true) ?: [];
            if (count($periods) <= 1) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Phải giữ lại ít nhất 1 kỳ bảng giá gốc!']);
                exit;
            }
            $newPeriods = array_values(array_filter($periods, function($pm) use ($periodId) {
                return ($pm['id'] ?? '') !== $periodId;
            }));
            if (count($newPeriods) === count($periods)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Không tìm thấy kỳ giá cần xóa!']);
                exit;
            }
            $latestPeriod = $newPeriods[count($newPeriods) - 1];
            $newActiveId = $latestPeriod['id'];

            $allRoutes = $pdo->query("SELECT route_code, base_rates_json, period_rates_json FROM tms_routes")->fetchAll();
            $stUpR = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ?, period_rates_json = ? WHERE route_code = ?");
            $pdo->beginTransaction();
            try {
                foreach ($allRoutes as $rr) {
                    $pr = !empty($rr['period_rates_json']) ? (json_decode($rr['period_rates_json'], true) ?: []) : [];
                    unset($pr[$periodId]);
                    $activeBr = $pr[$newActiveId] ?? (json_decode($rr['base_rates_json'] ?? '{}', true) ?: []);
                    $stUpR->execute([
                        json_encode($activeBr, JSON_UNESCAPED_UNICODE),
                        json_encode($pr, JSON_UNESCAPED_UNICODE),
                        $rr['route_code']
                    ]);
                }
                $stCfg = $pdo->prepare("
                    INSERT INTO tms_pricing_config (config_key, config_val)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
                ");
                $stCfg->execute(['periods_json', json_encode($newPeriods, JSON_UNESCAPED_UNICODE)]);
                $stCfg->execute(['active_period_id', $newActiveId]);
                $stCfg->execute(['effective_date', $latestPeriod['effective_date'] ?? $newActiveId]);
                $stCfg->execute(['do_base_price', (string)($latestPeriod['do_price'] ?? 28540)]);
                $pdo->commit();
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $ex;
            }

            echo json_encode([
                'ok'               => true,
                'active_period_id' => $newActiveId,
                'periods'          => $newPeriods,
                'message'          => "Đã xóa kỳ giá [$periodId] và chuyển về kỳ [{$latestPeriod['label']}]!"
            ]);
            exit;
        }

        // POST cuoc/save-config: Cập nhật % biến động cước chung, giá dầu cơ sở, danh sách nhà xe
        if ($route === 'cuoc/save-config') {
            $st = $pdo->prepare("
                INSERT INTO tms_pricing_config (config_key, config_val)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
            ");
            if (isset($jsonBody['adj_percent'])) {
                $st->execute(['adj_percent', (string)((float)$jsonBody['adj_percent'])]);
            }
            if (isset($jsonBody['do_base_price'])) {
                $st->execute(['do_base_price', (string)((int)$jsonBody['do_base_price'])]);
            }
            if (isset($jsonBody['effective_date'])) {
                $st->execute(['effective_date', trim((string)$jsonBody['effective_date'])]);
            }
            if (isset($jsonBody['carriers']) && is_array($jsonBody['carriers'])) {
                $st->execute(['carriers_json', json_encode(array_values($jsonBody['carriers']), JSON_UNESCAPED_UNICODE)]);
            }
            echo json_encode(['ok' => true, 'message' => 'Đã lưu cấu hình biến động giá cước!']);
            exit;
        }

        // POST cuoc/add-or-clone-vendor: Thêm mới hoặc Sửa (đổi tên) Vendor, hỗ trợ tách nhiều Vendor ngăn cách bằng dấu phẩy hoặc giữ nguyên tên, và áp nhanh giá cước
        if ($route === 'cuoc/add-or-clone-vendor') {
            $oldVendorName = strtoupper(trim($jsonBody['old_vendor_name'] ?? ''));
            $rawVendorName = strtoupper(trim($jsonBody['vendor_name'] ?? ''));
            $sourceCarrier = strtoupper(trim($jsonBody['source_carrier'] ?? ''));
            $factorPercent = isset($jsonBody['factor_percent']) ? (float)$jsonBody['factor_percent'] : 100.0;
            $deltaAmount = isset($jsonBody['delta_amount']) ? (int)$jsonBody['delta_amount'] : 0;
            $splitComma = !empty($jsonBody['split_comma']);

            if ($rawVendorName === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Tên Vendor / Nhà xe!']);
                exit;
            }
            if ($oldVendorName === 'VIETSUN' && $rawVendorName !== 'VIETSUN') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Không thể đổi tên cột chuẩn VIETSUN!']);
                exit;
            }

            // Danh sách tên vendor đích (hỗ trợ tách dấu phẩy nếu người dùng nhập nhiều Vendor cùng lúc hoặc bấm Tách)
            $targetVendors = [];
            if ($splitComma && strpos($rawVendorName, ',') !== false) {
                foreach (explode(',', $rawVendorName) as $part) {
                    $p = trim($part);
                    if ($p !== '' && !in_array($p, $targetVendors, true)) {
                        $targetVendors[] = $p;
                    }
                }
            } else {
                $targetVendors = [$rawVendorName];
            }

            // 1. Cập nhật danh sách carriers_json
            $rawCarriers = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'carriers_json'")->fetchColumn();
            $carriers = json_decode($rawCarriers ?: '[]', true) ?: ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];

            if ($oldVendorName !== '' && $oldVendorName !== 'VIETSUN') {
                // Thay thế tại đúng vị trí của oldVendorName
                $newCarriers = [];
                $replaced = false;
                foreach ($carriers as $c) {
                    if (strtoupper(trim($c)) === $oldVendorName) {
                        foreach ($targetVendors as $tv) {
                            if (!in_array($tv, $newCarriers, true)) {
                                $newCarriers[] = $tv;
                            }
                        }
                        $replaced = true;
                    } else {
                        if (!in_array($c, $newCarriers, true)) {
                            $newCarriers[] = $c;
                        }
                    }
                }
                if (!$replaced) {
                    foreach ($targetVendors as $tv) {
                        if (!in_array($tv, $newCarriers, true)) {
                            $newCarriers[] = $tv;
                        }
                    }
                }
                $carriers = array_values($newCarriers);
            } else {
                foreach ($targetVendors as $tv) {
                    if (!in_array($tv, $carriers, true)) {
                        $carriers[] = $tv;
                    }
                }
            }

            $stCfg = $pdo->prepare("
                INSERT INTO tms_pricing_config (config_key, config_val)
                VALUES ('carriers_json', ?)
                ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
            ");
            $stCfg->execute([json_encode(array_values($carriers), JSON_UNESCAPED_UNICODE)]);

            // Lưu liên kết vendor_links_json để khi điều chỉnh giá hoặc sửa giá đơn vị nguồn (VD: NHAT SON), các vendor theo sau tự động nhảy giá
            $rawLinks = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'vendor_links_json'")->fetchColumn();
            $vendorLinks = json_decode($rawLinks ?: '{}', true) ?: [];
            $oldLink = ($oldVendorName !== '' && isset($vendorLinks[$oldVendorName])) ? $vendorLinks[$oldVendorName] : null;
            foreach ($targetVendors as $tv) {
                if ($sourceCarrier !== '' && $sourceCarrier !== '__KEEP_CURRENT__') {
                    $vendorLinks[$tv] = [
                        'source'         => $sourceCarrier,
                        'factor_percent' => $factorPercent,
                        'delta_amount'   => $deltaAmount
                    ];
                } elseif ($oldLink) {
                    $vendorLinks[$tv] = $oldLink;
                }
            }
            if ($oldVendorName !== '' && !in_array($oldVendorName, $targetVendors, true)) {
                unset($vendorLinks[$oldVendorName]);
            }
            $stCfgLink = $pdo->prepare("
                INSERT INTO tms_pricing_config (config_key, config_val)
                VALUES ('vendor_links_json', ?)
                ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
            ");
            $stCfgLink->execute([json_encode($vendorLinks, JSON_UNESCAPED_UNICODE)]);

            // 2. Cập nhật base_rates_json & period_rates_json trên toàn bộ 246 tuyến đường (Đổi tên key cũ -> mới, và/hoặc Áp nhanh giá từ source_carrier)
            $updatedRoutes = 0;
            if ($oldVendorName !== '' || $sourceCarrier !== '') {
                $allRoutes = $pdo->query("SELECT route_code, base_rates_json, period_rates_json FROM tms_routes")->fetchAll();
                $stUp = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ?, period_rates_json = ? WHERE route_code = ?");
                $pdo->beginTransaction();
                try {
                    $cloneRateForTable = function(&$tbl) use ($oldVendorName, $targetVendors, $sourceCarrier, $factorPercent, $deltaAmount) {
                        $oldRateObj = ($oldVendorName !== '' && isset($tbl[$oldVendorName])) ? $tbl[$oldVendorName] : ['c20' => 0, 'c40' => 0, 'c20_no_vat' => 0, 'c40_no_vat' => 0];
                        foreach ($targetVendors as $tv) {
                            if ($sourceCarrier !== '') {
                                $srcKey = ($sourceCarrier === '__KEEP_CURRENT__' && $oldVendorName !== '') ? $oldVendorName : $sourceCarrier;
                                $src = ($srcKey === $oldVendorName) ? $oldRateObj : ($tbl[$srcKey] ?? ($tbl['VIETSUN'] ?? ['c20' => 0, 'c40' => 0]));
                                $src20 = (float)($src['c20'] ?? 0);
                                $src40 = (float)($src['c40'] ?? 0);
                                $srcNoVat20 = !empty($src['c20_no_vat']) ? (float)$src['c20_no_vat'] : ($src20 / 1.08);
                                $srcNoVat40 = !empty($src['c40_no_vat']) ? (float)$src['c40_no_vat'] : ($src40 / 1.08);

                                if ($factorPercent == 100.0 && $deltaAmount == 0) {
                                    $tbl[$tv] = [
                                        'c20'        => (int)round($src20),
                                        'c40'        => (int)round($src40),
                                        'c20_no_vat' => $srcNoVat20,
                                        'c40_no_vat' => $srcNoVat40
                                    ];
                                } else {
                                    $newNoVat20 = $srcNoVat20 > 0 ? (int)floor(max(0.0, $srcNoVat20 * ($factorPercent / 100.0) + ($deltaAmount / 1.08)) + 1e-9) : 0;
                                    $newNoVat40 = $srcNoVat40 > 0 ? (int)floor(max(0.0, $srcNoVat40 * ($factorPercent / 100.0) + ($deltaAmount / 1.08)) + 1e-9) : 0;
                                    $tbl[$tv] = [
                                        'c20'        => $newNoVat20 > 0 ? (int)floor($newNoVat20 * 1.08 + 1e-9) : 0,
                                        'c40'        => $newNoVat40 > 0 ? (int)floor($newNoVat40 * 1.08 + 1e-9) : 0,
                                        'c20_no_vat' => $newNoVat20,
                                        'c40_no_vat' => $newNoVat40
                                    ];
                                }
                            } elseif ($oldVendorName !== '' && !isset($tbl[$tv])) {
                                $tbl[$tv] = $oldRateObj;
                            }
                        }
                        if ($oldVendorName !== '' && !in_array($oldVendorName, $targetVendors, true)) {
                            unset($tbl[$oldVendorName]);
                        }
                    };

                    foreach ($allRoutes as $rr) {
                        $br = json_decode($rr['base_rates_json'] ?? '{}', true) ?: [];
                        $pr = !empty($rr['period_rates_json']) ? (json_decode($rr['period_rates_json'], true) ?: []) : [];
                        $cloneRateForTable($br);
                        foreach ($pr as $pKey => &$pTbl) {
                            if (is_array($pTbl)) {
                                $cloneRateForTable($pTbl);
                            }
                        }
                        unset($pTbl);
                        $stUp->execute([
                            json_encode($br, JSON_UNESCAPED_UNICODE),
                            json_encode($pr, JSON_UNESCAPED_UNICODE),
                            $rr['route_code']
                        ]);
                        $updatedRoutes++;
                    }
                    $pdo->commit();
                } catch (Exception $ex) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    throw $ex;
                }
            }

            $vLabel = implode(', ', $targetVendors);
            echo json_encode([
                'ok'             => true,
                'vendor_name'    => $vLabel,
                'carriers'       => $carriers,
                'updated_routes' => $updatedRoutes,
                'message'        => $oldVendorName !== ''
                    ? "Đã cập nhật Vendor [$oldVendorName] ➔ [$vLabel] trên toàn bộ $updatedRoutes tuyến đường!"
                    : ($sourceCarrier !== ''
                        ? "Đã thêm Vendor [$vLabel] và áp nhanh giá cước cho $updatedRoutes tuyến đường!"
                        : "Đã thêm mới Vendor [$vLabel] vào bảng giá!")
            ]);
            exit;
        }

        // POST cuoc/delete-vendor: Xóa 1 cột Vendor khỏi bảng giá và dọn dữ liệu base_rates_json & period_rates_json
        if ($route === 'cuoc/delete-vendor') {
            $vendorName = strtoupper(trim($jsonBody['vendor_name'] ?? ''));
            if ($vendorName === '' || $vendorName === 'VIETSUN') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Không thể xóa cột giá chuẩn VIETSUN!']);
                exit;
            }
            $rawCarriers = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'carriers_json'")->fetchColumn();
            $carriers = json_decode($rawCarriers ?: '[]', true) ?: ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];
            $carriers = array_values(array_filter($carriers, function($c) use ($vendorName) {
                return strtoupper(trim($c)) !== $vendorName;
            }));
            $stCfg = $pdo->prepare("
                INSERT INTO tms_pricing_config (config_key, config_val)
                VALUES ('carriers_json', ?)
                ON DUPLICATE KEY UPDATE config_val = VALUES(config_val)
            ");
            $stCfg->execute([json_encode($carriers, JSON_UNESCAPED_UNICODE)]);

            // Xóa key của vendor này trong base_rates_json & period_rates_json
            $allRoutes = $pdo->query("SELECT route_code, base_rates_json, period_rates_json FROM tms_routes")->fetchAll();
            $stUp = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ?, period_rates_json = ? WHERE route_code = ?");
            $pdo->beginTransaction();
            try {
                foreach ($allRoutes as $rr) {
                    $br = json_decode($rr['base_rates_json'] ?? '{}', true) ?: [];
                    $pr = !empty($rr['period_rates_json']) ? (json_decode($rr['period_rates_json'], true) ?: []) : [];
                    unset($br[$vendorName]);
                    foreach ($pr as $pKey => &$pTbl) {
                        if (is_array($pTbl)) unset($pTbl[$vendorName]);
                    }
                    unset($pTbl);
                    $stUp->execute([
                        json_encode($br, JSON_UNESCAPED_UNICODE),
                        json_encode($pr, JSON_UNESCAPED_UNICODE),
                        $rr['route_code']
                    ]);
                }
                $pdo->commit();
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
            }

            echo json_encode(['ok' => true, 'carriers' => $carriers, 'message' => "Đã xóa Vendor [$vendorName] khỏi bảng giá!"]);
            exit;
        }

        // POST cuoc/save-route: Thêm mới hoặc cập nhật Mã Tuyến Đường & Bảng giá Vietsun + Vendor (Lưu vào kỳ đang xem/áp dụng)
        if ($route === 'cuoc/save-route') {
            $routeCode = strtoupper(trim($jsonBody['route_code'] ?? ''));
            $diaDiem = trim($jsonBody['dia_diem'] ?? '');
            $tinh = trim($jsonBody['tinh'] ?? '');
            $baseRates = $jsonBody['base_rates'] ?? [];
            $periodId = trim((string)($jsonBody['period_id'] ?? ''));
            $notes = trim($jsonBody['notes'] ?? '');
            if ($routeCode === '' || $diaDiem === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Mã Tuyến Đường và Địa Điểm!']);
                exit;
            }
            $curActiveId = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'active_period_id'")->fetchColumn() ?: '2026-08-25';
            if ($periodId === '') {
                $periodId = $curActiveId;
            }

            // Chuẩn hóa c20_no_vat & c40_no_vat và lấp các ô trống bằng giá VIETSUN
            $vs20 = (int)($baseRates['VIETSUN']['c20'] ?? 0);
            $vs40 = (int)($baseRates['VIETSUN']['c40'] ?? 0);
            $vsNoVat20 = !empty($baseRates['VIETSUN']['c20_no_vat']) ? (float)$baseRates['VIETSUN']['c20_no_vat'] : ($vs20 / 1.08);
            $vsNoVat40 = !empty($baseRates['VIETSUN']['c40_no_vat']) ? (float)$baseRates['VIETSUN']['c40_no_vat'] : ($vs40 / 1.08);

            foreach ($baseRates as $cKey => &$cObj) {
                $c20 = (int)($cObj['c20'] ?? 0);
                $c40 = (int)($cObj['c40'] ?? 0);
                if ($cKey !== 'VIETSUN') {
                    if ($c20 <= 0) $c20 = $vs20;
                    if ($c40 <= 0) $c40 = $vs40;
                }
                $nv20 = !empty($cObj['c20_no_vat']) && abs(floor((float)$cObj['c20_no_vat'] * 1.08 + 1e-9) - $c20) <= 2
                    ? (float)$cObj['c20_no_vat']
                    : ($c20 === $vs20 ? $vsNoVat20 : (int)floor($c20 / 1.08 + 1e-9));
                $nv40 = !empty($cObj['c40_no_vat']) && abs(floor((float)$cObj['c40_no_vat'] * 1.08 + 1e-9) - $c40) <= 2
                    ? (float)$cObj['c40_no_vat']
                    : ($c40 === $vs40 ? $vsNoVat40 : (int)floor($c40 / 1.08 + 1e-9));
                $cObj = [
                    'c20'        => $c20,
                    'c40'        => $c40,
                    'c20_no_vat' => $nv20,
                    'c40_no_vat' => $nv40
                ];
            }
            unset($cObj);

            // Tự động đồng bộ các vendor khác đang lấy theo giá NHAT SON (hoặc đơn vị nguồn khác) khi điều chỉnh giá tuyến này
            $rawLinks = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'vendor_links_json'")->fetchColumn();
            $vendorLinks = json_decode($rawLinks ?: '{}', true) ?: [];
            foreach ($vendorLinks as $vTarget => $lnk) {
                $srcCarrier = $lnk['source'] ?? '';
                if ($srcCarrier !== '' && $srcCarrier !== $vTarget && isset($baseRates[$srcCarrier])) {
                    $fPct = isset($lnk['factor_percent']) ? (float)$lnk['factor_percent'] : 100.0;
                    $dAmt = isset($lnk['delta_amount']) ? (int)$lnk['delta_amount'] : 0;
                    if ($fPct == 100.0 && $dAmt == 0) {
                        $baseRates[$vTarget] = $baseRates[$srcCarrier];
                    } else {
                        $sObj = $baseRates[$srcCarrier];
                        $nv20 = !empty($sObj['c20_no_vat']) ? (int)floor(max(0.0, (float)$sObj['c20_no_vat'] * ($fPct / 100.0) + ($dAmt / 1.08)) + 1e-9) : 0;
                        $nv40 = !empty($sObj['c40_no_vat']) ? (int)floor(max(0.0, (float)$sObj['c40_no_vat'] * ($fPct / 100.0) + ($dAmt / 1.08)) + 1e-9) : 0;
                        $baseRates[$vTarget] = [
                            'c20_no_vat' => $nv20,
                            'c40_no_vat' => $nv40,
                            'c20'        => $nv20 > 0 ? (int)floor($nv20 * 1.08 + 1e-9) : 0,
                            'c40'        => $nv40 > 0 ? (int)floor($nv40 * 1.08 + 1e-9) : 0
                        ];
                    }
                }
            }

            $stEx = $pdo->prepare("SELECT base_rates_json, period_rates_json, sort_order FROM tms_routes WHERE route_code = ? LIMIT 1");
            $stEx->execute([$routeCode]);
            $exRow = $stEx->fetch();
            $pr = ($exRow && !empty($exRow['period_rates_json'])) ? (json_decode($exRow['period_rates_json'], true) ?: []) : [];
            $pr[$periodId] = $baseRates;

            $activeBase = ($periodId === $curActiveId || !$exRow) ? $baseRates : (json_decode($exRow['base_rates_json'] ?? '{}', true) ?: $baseRates);

            $sortOrder = (int)($jsonBody['sort_order'] ?? ($exRow['sort_order'] ?? 0));
            if ($sortOrder <= 0) {
                $sortOrder = ((int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM tms_routes")->fetchColumn()) + 1;
            }
            $st = $pdo->prepare("
                INSERT INTO tms_routes (route_code, dia_diem, tinh, base_rates_json, period_rates_json, notes, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    dia_diem = VALUES(dia_diem),
                    tinh = VALUES(tinh),
                    base_rates_json = VALUES(base_rates_json),
                    period_rates_json = VALUES(period_rates_json),
                    notes = VALUES(notes)
            ");
            $st->execute([
                $routeCode,
                $diaDiem,
                $tinh,
                json_encode($activeBase, JSON_UNESCAPED_UNICODE),
                json_encode($pr, JSON_UNESCAPED_UNICODE),
                $notes,
                $sortOrder
            ]);
            echo json_encode(['ok' => true, 'route_code' => $routeCode, 'message' => "Đã lưu mã tuyến $routeCode ($diaDiem)!"]);
            exit;
        }

        // POST cuoc/delete-route: Xóa Mã Tuyến Đường
        if ($route === 'cuoc/delete-route') {
            $routeCode = trim($jsonBody['route_code'] ?? '');
            if ($routeCode !== '') {
                $st = $pdo->prepare("DELETE FROM tms_routes WHERE route_code = ?");
                $st->execute([$routeCode]);
                $stW = $pdo->prepare("UPDATE tms_warehouses_2026 SET route_code = '', match_confidence = 'NONE' WHERE route_code = ?");
                $stW->execute([$routeCode]);
            }
            echo json_encode(['ok' => true, 'message' => "Đã xóa tuyến $routeCode!"]);
            exit;
        }

        // POST cuoc/assign-warehouse: Gán 1 hoặc nhiều Mã Kho vào Mã Tuyến Đường (hoặc NGOAI-LE)
        if ($route === 'cuoc/assign-warehouse') {
            $whCodes = $jsonBody['wh_codes'] ?? [];
            if (!empty($jsonBody['wh_code']) && empty($whCodes)) {
                $whCodes = [$jsonBody['wh_code']];
            }
            $routeCode = strtoupper(trim($jsonBody['route_code'] ?? ''));
            $reason = trim($jsonBody['match_reason'] ?? 'Gán thủ công');
            $conf = ($routeCode === 'NGOAI-LE') ? 'EXCEPTION' : ($routeCode !== '' ? 'MANUAL' : 'NONE');
            $customRates = isset($jsonBody['custom_rates']) && is_array($jsonBody['custom_rates'])
                ? json_encode($jsonBody['custom_rates'], JSON_UNESCAPED_UNICODE)
                : null;

            if (empty($whCodes)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Chưa chọn Mã Kho để gán tuyến!']);
                exit;
            }
            if ($customRates !== null) {
                $st = $pdo->prepare("
                    UPDATE tms_warehouses_2026
                    SET route_code = ?, match_confidence = ?, match_reason = ?, custom_rates_json = ?
                    WHERE wh_code = ?
                ");
                foreach ($whCodes as $wc) {
                    $st->execute([$routeCode, $conf, $reason, $customRates, trim((string)$wc)]);
                }
            } else {
                $st = $pdo->prepare("
                    UPDATE tms_warehouses_2026
                    SET route_code = ?, match_confidence = ?, match_reason = ?
                    WHERE wh_code = ?
                ");
                foreach ($whCodes as $wc) {
                    $st->execute([$routeCode, $conf, $reason, trim((string)$wc)]);
                }
            }
            echo json_encode([
                'ok'         => true,
                'updated'    => count($whCodes),
                'route_code' => $routeCode,
                'message'    => 'Đã cập nhật Mã Tuyến Đường cho ' . count($whCodes) . ' kho!'
            ]);
            exit;
        }

        // POST cuoc/save-warehouse: Thêm kho mới hoặc tùy chỉnh thông tin Kho (kể cả giá Ngoại lệ custom_rates)
        if ($route === 'cuoc/save-warehouse') {
            $whCode = strtoupper(trim($jsonBody['wh_code'] ?? ''));
            $khoName = trim($jsonBody['kho_name'] ?? '');
            $diaChi = trim($jsonBody['dia_chi'] ?? '');
            $tinhTms = trim($jsonBody['tinh_tms'] ?? '');
            $customers = trim($jsonBody['customers'] ?? '');
            $routeCode = strtoupper(trim($jsonBody['route_code'] ?? ''));
            $totalCount = isset($jsonBody['total_count']) ? (int)$jsonBody['total_count'] : 0;
            $nhapCount = isset($jsonBody['nhap_count']) ? (int)$jsonBody['nhap_count'] : 0;
            $xuatCount = isset($jsonBody['xuat_count']) ? (int)$jsonBody['xuat_count'] : 0;
            $customRatesJson = isset($jsonBody['custom_rates']) && is_array($jsonBody['custom_rates'])
                ? json_encode($jsonBody['custom_rates'], JSON_UNESCAPED_UNICODE)
                : null;

            if ($khoName === '' && $diaChi === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Tên Kho hoặc Địa chỉ kho!']);
                exit;
            }
            if ($whCode === '') {
                $maxCodeRow = $pdo->query("SELECT wh_code FROM tms_warehouses_2026 WHERE wh_code LIKE 'KHO-%' ORDER BY LENGTH(wh_code) DESC, wh_code DESC LIMIT 1")->fetchColumn();
                $nextNum = 1;
                if ($maxCodeRow && preg_match('/KHO-(\d+)/', $maxCodeRow, $m)) {
                    $nextNum = ((int)$m[1]) + 1;
                } else {
                    $nextNum = ((int)$pdo->query("SELECT COUNT(*) FROM tms_warehouses_2026")->fetchColumn()) + 1;
                }
                $whCode = 'KHO-' . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
            }
            $conf = ($routeCode === 'NGOAI-LE') ? 'EXCEPTION' : ($routeCode !== '' ? 'MANUAL' : 'NONE');
            $reason = ($routeCode === 'NGOAI-LE')
                ? 'Kho Ngoại Lệ — Giữ nguyên giá đã Debit'
                : ($routeCode !== '' ? 'Cập nhật trực tiếp' : '');

            $st = $pdo->prepare("
                INSERT INTO tms_warehouses_2026 (wh_code, kho_name, dia_chi, tinh_tms, customers, route_code, match_confidence, match_reason, total_count, nhap_count, xuat_count, custom_rates_json)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    kho_name = VALUES(kho_name),
                    dia_chi = VALUES(dia_chi),
                    tinh_tms = VALUES(tinh_tms),
                    customers = VALUES(customers),
                    route_code = VALUES(route_code),
                    match_confidence = VALUES(match_confidence),
                    match_reason = VALUES(match_reason),
                    custom_rates_json = COALESCE(VALUES(custom_rates_json), custom_rates_json)
            ");
            $st->execute([
                $whCode,
                $khoName,
                $diaChi,
                $tinhTms,
                $customers,
                $routeCode,
                $conf,
                $reason,
                $totalCount,
                $nhapCount,
                $xuatCount,
                $customRatesJson
            ]);
            echo json_encode(['ok' => true, 'wh_code' => $whCode, 'message' => "Đã lưu thông tin kho $whCode!"]);
            exit;
        }

        // POST cuoc/delete-warehouse: Xóa 1 hoặc nhiều Mã Kho khỏi bảng định danh kho
        if ($route === 'cuoc/delete-warehouse') {
            $whCodes = $jsonBody['wh_codes'] ?? [];
            if (!empty($jsonBody['wh_code']) && empty($whCodes)) {
                $whCodes = [$jsonBody['wh_code']];
            }
            if (empty($whCodes)) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Chưa chọn Mã Kho cần xóa!']);
                exit;
            }
            $st = $pdo->prepare("DELETE FROM tms_warehouses_2026 WHERE wh_code = ?");
            foreach ($whCodes as $wc) {
                $st->execute([trim((string)$wc)]);
            }
            echo json_encode([
                'ok'      => true,
                'deleted' => count($whCodes),
                'message' => 'Đã xóa ' . count($whCodes) . ' mã kho khỏi danh sách!'
            ]);
            exit;
        }

        // GET/POST cuoc/ketoan-vs & cuoc/ketoan-vs-sync:
        // - Cơ chế Cache MySQL (tms_ketoan_bookings_cache) + Slim Payload giúp mở tab Kế Toán VS trong < 0.2s (0s chờ TMS gốc, không bao giờ nghẽn cổ chai).
        // - Đồng bộ cuốn chiếu từng cửa sổ nhỏ (cuoc/ketoan-vs-sync) hoặc đồng bộ nhanh 7 ngày gần nhất (~2-3s).
        if ($route === 'cuoc/ketoan-vs' || $route === 'cuoc/ketoan-vs-sync') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `tms_ketoan_bookings_cache` (
                    `id_po` VARCHAR(32) PRIMARY KEY,
                    `trip_date` VARCHAR(20) NOT NULL DEFAULT '',
                    `xuat_nhap` VARCHAR(5) NOT NULL DEFAULT 'N',
                    `cont_40` TINYINT(1) NOT NULL DEFAULT 0,
                    `payload_json` TEXT NOT NULL,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_trip_date` (`trip_date`),
                    INDEX `idx_xn_date` (`xuat_nhap`, `trip_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // Helper chuẩn bị map Tài xế + Chi phí TX báo trên App
            $buildDriverAndExpenseMaps = function($pdo) {
                $fleetMap = [];
                $snapshotsMap = [];
                $historyByPlate = [];
                $drvBocXepMap = [];
                $drvCpKhacMap = [];
                $drvExpNoteMap = [];
                $drvTotalExpMap = [];
                $drvExpDetailsMap = [];
                try {
                    $stDrv = $pdo->query("SELECT assigned_plate, driver_name, phone, cccd, gplx FROM drivers");
                    if ($stDrv) {
                        foreach ($stDrv->fetchAll() as $dr) {
                            $p = to_clean_code($dr['assigned_plate'] ?? '');
                            if ($p) {
                                $fleetMap[$p] = [
                                    'plate' => $p,
                                    'name'  => $dr['driver_name'] ?? '',
                                    'phone' => $dr['phone'] ?? '',
                                    'cccd'  => $dr['cccd'] ?? '',
                                    'gplx'  => $dr['gplx'] ?? ''
                                ];
                            }
                        }
                    }
                    foreach (fetch_live_vietsun_fleet(false) as $vsDrv) {
                        $p = to_clean_code($vsDrv['plate'] ?? '');
                        if ($p && !isset($fleetMap[$p])) {
                            $fleetMap[$p] = [
                                'plate' => $p,
                                'name'  => $vsDrv['name'] ?? '',
                                'phone' => $vsDrv['phone'] ?? '',
                                'cccd'  => '',
                                'gplx'  => ''
                            ];
                        }
                    }
                    $stSnapAll = $pdo->query("SELECT id_po, assigned_plate, driver_name, driver_phone, driver_cccd, driver_gplx FROM trip_driver_snapshots");
                    if ($stSnapAll) {
                        foreach ($stSnapAll->fetchAll() as $sr) {
                            $snapshotsMap[(string)$sr['id_po']] = $sr;
                        }
                    }
                    $stHistAll = $pdo->query("SELECT assigned_plate, driver_name, phone, cccd, gplx, assigned_from, assigned_to FROM driver_assignment_history ORDER BY id DESC");
                    if ($stHistAll) {
                        foreach ($stHistAll->fetchAll() as $hr) {
                            $hp = to_clean_code($hr['assigned_plate'] ?? '');
                            if ($hp) $historyByPlate[$hp][] = $hr;
                        }
                    }
                    $stExp = $pdo->query("SELECT booking_id_po, expense_type, amount, note FROM trip_expenses ORDER BY id ASC");
                    if ($stExp) {
                        foreach ($stExp->fetchAll() as $er) {
                            $pid = (string)($er['booking_id_po'] ?? '');
                            if ($pid === '') continue;
                            $amt = (int)round((float)($er['amount'] ?? 0));
                            $etype = trim($er['expense_type'] ?? '');
                            $enote = trim($er['note'] ?? '');
                            if (stripos($etype, 'bốc xếp') !== false || stripos($etype, 'boc xep') !== false) {
                                $drvBocXepMap[$pid] = ($drvBocXepMap[$pid] ?? 0) + $amt;
                            } else {
                                $drvCpKhacMap[$pid] = ($drvCpKhacMap[$pid] ?? 0) + $amt;
                            }
                            $drvTotalExpMap[$pid] = ($drvTotalExpMap[$pid] ?? 0) + $amt;
                            $desc = ($etype ?: 'Chi phí') . ($amt > 0 ? (': ' . number_format($amt) . 'đ') : '') . ($enote ? " ($enote)" : '');
                            $drvExpDetailsMap[$pid][] = $desc;
                            if ($enote !== '') {
                                $drvExpNoteMap[$pid] = isset($drvExpNoteMap[$pid]) ? ($drvExpNoteMap[$pid] . '; ' . $enote) : $enote;
                            }
                        }
                    }
                } catch (Exception $e) {}

                return [
                    'fleetMap'         => $fleetMap,
                    'snapshotsMap'     => $snapshotsMap,
                    'historyByPlate'   => $historyByPlate,
                    'drvBocXepMap'     => $drvBocXepMap,
                    'drvCpKhacMap'     => $drvCpKhacMap,
                    'drvExpNoteMap'    => $drvExpNoteMap,
                    'drvTotalExpMap'   => $drvTotalExpMap,
                    'drvExpDetailsMap' => $drvExpDetailsMap
                ];
            };

            // Helper rút gọn (Slim) 1 booking chỉ giữ đúng các trường Kế Toán VS cần (giảm 75% dung lượng JSON)
            $makeSlimBooking = function($item, $dInfo, &$maps) {
                $idPo = (string)($item['idPo'] ?? '');
                $dIso = get_shipment_date_iso($item);
                $is40 = ((int)($item['cont40Po'] ?? 0) > 0) ? 1 : 0;
                $xn   = (($item['xuatNhapPo'] ?? 'N') === 'X') ? 'X' : 'N';

                $donGiaDebit = isset($dInfo['donGiaDebitPo']) && $dInfo['donGiaDebitPo'] > 0
                    ? (int)$dInfo['donGiaDebitPo']
                    : (isset($item['donGiaDebitPo']) && $item['donGiaDebitPo'] > 0 ? (int)$item['donGiaDebitPo'] : 0);

                $donGiaVendor = isset($dInfo['donGia1ThucTraChoxePo']) && $dInfo['donGia1ThucTraChoxePo'] > 0
                    ? (int)$dInfo['donGia1ThucTraChoxePo']
                    : (isset($item['donGia1ThucTraChoxePo']) && $item['donGia1ThucTraChoxePo'] > 0 ? (int)$item['donGia1ThucTraChoxePo'] : 0);

                $tmsBocXep = max(
                    (int)($dInfo['bocXep2ChiHoPo'] ?? 0),
                    (int)($dInfo['cpBocXepChiHoPo'] ?? 0),
                    (int)($item['_tmsBocXep'] ?? 0)
                );
                $drvBocXep = (int)($maps['drvBocXepMap'][$idPo] ?? ($item['_driverBocXep'] ?? 0));

                $sumCont3 = (int)($dInfo['neoXeDuongCamPo'] ?? 0) + (int)($dInfo['cpduongcam'] ?? 0) + (int)($dInfo['cpkhacpo'] ?? 0);
                $xeCol = (int)($dInfo['chiPhiNeoXePo'] ?? 0);
                $tmsCpKhac = $sumCont3 > 0 ? $sumCont3 : ($xeCol > 0 ? $xeCol : (int)($item['_tmsCpKhac'] ?? 0));
                $drvCpKhac = (int)($maps['drvCpKhacMap'][$idPo] ?? ($item['_driverCpKhac'] ?? 0));

                $veCauDuong = isset($dInfo['veCauDuongPo']) ? (int)$dInfo['veCauDuongPo'] : (int)($item['_veCauDuong'] ?? 0);
                $drvTotalExp = (int)($maps['drvTotalExpMap'][$idPo] ?? ($item['_driverPhatSinh'] ?? 0));

                $kh = trim($dInfo['hinhThucKHopPo'] ?? ($item['hinhThucKHopPo'] ?? ($item['_tyLeKetHop'] ?? '')));
                $gc = trim($dInfo['ghiChuPo'] ?? ($item['ghiChuPo'] ?? ''));
                if ($gc === '.') $gc = '';
                $nt = trim($dInfo['notePo'] ?? ($item['notePo'] ?? ''));
                if ($nt === '.') $nt = '';
                if ($kh === '' && $gc !== '' && preg_match('/(\d+(?:\.\d+)?\s*%|\bR\b)/i', $gc, $mGc)) {
                    $kh = strtoupper(str_replace(' ', '', $mGc[1]));
                }

                $drvName = $item['_driverName'] ?? '';
                $drvPhone = $item['_driverPhone'] ?? '';
                $itemPlate = to_clean_code($item['soXePo'] ?? '');
                if ($itemPlate !== '' && $drvName === '') {
                    $resolvedDrv = resolve_driver_for_trip($idPo, $itemPlate, $dIso, $maps['snapshotsMap'], $maps['historyByPlate'], $maps['fleetMap']);
                    if ($resolvedDrv) {
                        $drvName  = $resolvedDrv['name'] ?? '';
                        $drvPhone = $resolvedDrv['phone'] ?? '';
                    }
                }

                return [
                    'idPo'                   => $idPo,
                    'codePo'                 => (string)($item['codePo'] ?? ''),
                    '_tripDateIso'           => $dIso,
                    'ngayTraHang'            => $dIso,
                    'xuatNhapPo'             => $xn,
                    'cont40Po'               => $is40,
                    'soContPo'               => (string)($item['soContPo'] ?? ''),
                    'soSealPo'               => (string)($item['soSealPo'] ?? ''),
                    'tenKhachPo'             => (string)($item['tenKhachPo'] ?? ''),
                    'khoNhanHangPo'          => (string)($item['khoNhanHangPo'] ?? ''),
                    'diaChiNhanPo'           => (string)($item['diaChiNhanPo'] ?? ''),
                    'tinhNhanPo'             => (string)($item['tinhNhanPo'] ?? ''),
                    'donViVcPo'              => (string)($item['donViVcPo'] ?? ''),
                    'soXePo'                 => (string)($item['soXePo'] ?? ''),
                    '_driverName'            => $drvName,
                    '_driverPhone'           => $drvPhone,
                    'hinhThucKHopPo'         => $kh,
                    '_tyLeKetHop'            => $kh,
                    'donGiaDebitPo'          => $donGiaDebit > 0 ? $donGiaDebit : null,
                    'donGia1ThucTraChoxePo'  => $donGiaVendor > 0 ? $donGiaVendor : null,
                    '_debitRate'             => $donGiaVendor > 0 ? $donGiaVendor : null,
                    '_tmsBocXep'             => $tmsBocXep,
                    '_driverBocXep'          => $drvBocXep,
                    '_chiHoBocXep'           => $tmsBocXep > 0 ? $tmsBocXep : $drvBocXep,
                    '_tmsCpKhac'             => $tmsCpKhac,
                    '_driverCpKhac'          => $drvCpKhac,
                    '_cpKhac'                => $tmsCpKhac > 0 ? $tmsCpKhac : $drvCpKhac,
                    '_veCauDuong'            => $veCauDuong,
                    '_driverPhatSinh'        => $drvTotalExp,
                    '_driverExpDetail'       => isset($maps['drvExpDetailsMap'][$idPo]) ? implode('; ', $maps['drvExpDetailsMap'][$idPo]) : ($item['_driverExpDetail'] ?? ''),
                    '_expenseNote'           => $maps['drvExpNoteMap'][$idPo] ?? ($item['_expenseNote'] ?? ''),
                    'ghiChuPo'               => $gc,
                    'notePo'                 => $nt
                ];
            };

            // ROUTE 1: POST/GET cuoc/ketoan-vs-sync — Đồng bộ cuốn chiếu 1 cửa sổ ngắn (VD: 5-7 ngày) từ TMS gốc vào MySQL Cache
            // Mỗi request chỉ gọi 1 lần index-ajax Nhập + 1 lần index-ajax Xuất -> chỉ mất ~2-3 giây, KHÔNG BAO GIỜ NGHẼN CỔ CHAI!
            if ($route === 'cuoc/ketoan-vs-sync') {
                $winStart = trim((string)($_GET['winStart'] ?? ($jsonBody['winStart'] ?? '')));
                $winEnd   = trim((string)($_GET['winEnd']   ?? ($jsonBody['winEnd']   ?? '')));
                $forceCycle = !empty($_GET['forceCycle']) || !empty($jsonBody['forceCycle']);

                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $winStart) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $winEnd)) {
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'error' => 'Khoảng ngày đồng bộ winStart / winEnd không hợp lệ!']);
                    exit;
                }

                $sTs = strtotime($winStart);
                $eTs = strtotime($winEnd);
                if ($sTs > $eTs) {
                    $tmp = $sTs; $sTs = $eTs; $eTs = $tmp;
                    $winStart = date('Y-m-d', $sTs);
                    $winEnd   = date('Y-m-d', $eTs);
                }

                // 1. Lấy danh sách booking từ các chu kỳ liên quan (dùng cache chu kỳ nếu không forceCycle để siêu nhanh)
                $cycles = array_values(array_unique([
                    get_billing_cycle_param(date('Y-m-d', $sTs - 5 * 86400)),
                    get_billing_cycle_param($winStart),
                    get_billing_cycle_param($winEnd),
                    get_billing_cycle_param(date('Y-m-d', $eTs + 10 * 86400))
                ]));
                $windowBookings = [];
                foreach ($cycles as $c) {
                    $arr = fetch_single_cycle($c, $forceCycle);
                    if (is_array($arr)) {
                        foreach ($arr as $rawIt) {
                            if (!isset($rawIt['idPo'])) continue;
                            $normIt = normalize_shipment_item($rawIt);
                            $dIso = get_shipment_date_iso($normIt);
                            if ($dIso !== '' && $dIso >= $winStart && $dIso <= $winEnd) {
                                $windowBookings[(string)$normIt['idPo']] = $normIt;
                            }
                        }
                    }
                }

                // 2. Chỉ gọi TMS index-ajax.php cho ĐÚNG cửa sổ ngắn [winStart, winEnd] này
                $debitMap = fetch_tms_debit_map($winStart, $winEnd, true);
                $maps = $buildDriverAndExpenseMaps($pdo);

                // 3. Upsert vào bảng MySQL tms_ketoan_bookings_cache
                $stUpsert = $pdo->prepare("
                    INSERT INTO `tms_ketoan_bookings_cache` (`id_po`, `trip_date`, `xuat_nhap`, `cont_40`, `payload_json`)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        `trip_date`    = VALUES(`trip_date`),
                        `xuat_nhap`    = VALUES(`xuat_nhap`),
                        `cont_40`      = VALUES(`cont_40`),
                        `payload_json` = VALUES(`payload_json`),
                        `updated_at`   = CURRENT_TIMESTAMP
                ");

                $pdo->beginTransaction();
                $savedCount = 0;
                try {
                    foreach ($windowBookings as $pid => $item) {
                        $dInfo = $debitMap[$pid] ?? [];
                        $slim = $makeSlimBooking($item, $dInfo, $maps);
                        $stUpsert->execute([
                            $pid,
                            $slim['_tripDateIso'],
                            $slim['xuatNhapPo'],
                            (int)$slim['cont40Po'],
                            json_encode($slim, JSON_UNESCAPED_UNICODE)
                        ]);
                        $savedCount++;
                    }
                    $pdo->commit();
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    throw $e;
                }

                echo json_encode([
                    'ok'         => true,
                    'winStart'   => $winStart,
                    'winEnd'     => $winEnd,
                    'savedCount' => $savedCount,
                    'debitCount' => count($debitMap)
                ]);
                exit;
            }

            // ROUTE 2: GET cuoc/ketoan-vs — Đọc tức thì từ MySQL Cache (< 0.15s).
            // Nếu bảng MySQL Cache chưa có dữ liệu nào, tự động warm-up nhanh từ các file cache sẵn có trong /tmp (không block gọi 16 request TMS)
            $days = isset($_GET['days']) ? max(1, min(120, (int)$_GET['days'])) : 30;
            $toDate = trim((string)($_GET['toDate'] ?? ($jsonBody['toDate'] ?? date('Y-m-d'))));
            $fromDate = trim((string)($_GET['fromDate'] ?? ($jsonBody['fromDate'] ?? '')));

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
                $toDate = date('Y-m-d');
            }
            $toTs = strtotime($toDate) ?: time();
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
                $fromTs = $toTs - ($days * 86400);
                $fromDate = date('Y-m-d', $fromTs);
            } else {
                $fromTs = strtotime($fromDate) ?: ($toTs - ($days * 86400));
            }
            if ($fromTs > $toTs) {
                $tmp = $fromTs; $fromTs = $toTs; $toTs = $tmp;
                $fromDate = date('Y-m-d', $fromTs);
                $toDate = date('Y-m-d', $toTs);
            }

            $cntExisting = (int)$pdo->query("SELECT COUNT(*) FROM `tms_ketoan_bookings_cache` WHERE `trip_date` BETWEEN " . $pdo->quote($fromDate) . " AND " . $pdo->quote($toDate))->fetchColumn();

            $needsCostSync = false;
            // Nếu MySQL Cache đang trống trong khoảng ngày này: Nạp tức thì danh sách booking từ return_json.php (chỉ 2 request JSON nhẹ ~0.8s)
            // kết hợp với bất kỳ file cache vietsun_debit_map_v8_*.json nào đang có sẵn trong /tmp để hiển thị ngay lập tức!
            if ($cntExisting === 0) {
                $rawCycles = [];
                for ($ts = $fromTs - 5 * 86400; $ts <= $toTs + 15 * 86400; $ts += 15 * 86400) {
                    $rawCycles[] = get_billing_cycle_param(date('Y-m-d', $ts));
                }
                $cycles = array_values(array_unique(array_filter($rawCycles)));
                $mergedBookings = [];
                foreach ($cycles as $c) {
                    $arr = fetch_single_cycle($c, false);
                    if (is_array($arr)) {
                        foreach ($arr as $rawIt) {
                            if (!isset($rawIt['idPo'])) continue;
                            $normIt = normalize_shipment_item($rawIt);
                            $dIso = get_shipment_date_iso($normIt);
                            if ($dIso !== '' && $dIso >= $fromDate && $dIso <= $toDate) {
                                $mergedBookings[(string)$normIt['idPo']] = $normIt;
                            }
                        }
                    }
                }

                // Tận dụng các file cache debit_map sẵn có trong /tmp (nếu có) mà không phải chờ gọi mới 16 cửa sổ
                $existingDebitMap = [];
                foreach (glob(sys_get_temp_dir() . '/vietsun_debit_map_v8_*.json') as $df) {
                    $dArr = json_decode(@file_get_contents($df), true);
                    if (is_array($dArr)) {
                        foreach ($dArr as $pid => $dVal) {
                            if (!isset($existingDebitMap[$pid])) {
                                $existingDebitMap[$pid] = $dVal;
                            } else {
                                $existingDebitMap[$pid] = array_merge($existingDebitMap[$pid], $dVal);
                            }
                        }
                    }
                }
                if (empty($existingDebitMap)) {
                    $needsCostSync = true;
                }

                $maps = $buildDriverAndExpenseMaps($pdo);
                $stUpsert = $pdo->prepare("
                    INSERT INTO `tms_ketoan_bookings_cache` (`id_po`, `trip_date`, `xuat_nhap`, `cont_40`, `payload_json`)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        `trip_date`    = VALUES(`trip_date`),
                        `xuat_nhap`    = VALUES(`xuat_nhap`),
                        `cont_40`      = VALUES(`cont_40`),
                        `payload_json` = VALUES(`payload_json`),
                        `updated_at`   = CURRENT_TIMESTAMP
                ");
                $pdo->beginTransaction();
                try {
                    foreach ($mergedBookings as $pid => $item) {
                        $dInfo = $existingDebitMap[$pid] ?? [];
                        $slim = $makeSlimBooking($item, $dInfo, $maps);
                        $stUpsert->execute([
                            $pid,
                            $slim['_tripDateIso'],
                            $slim['xuatNhapPo'],
                            (int)$slim['cont40Po'],
                            json_encode($slim, JSON_UNESCAPED_UNICODE)
                        ]);
                    }
                    $pdo->commit();
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                }
            }

            // Tối ưu cực đại: Ghép trực tiếp chuỗi payload_json từ MySQL mà KHÔNG json_decode + json_encode lại 6,500 dòng (~5MB)
            $jsonFragments = [];
            $nhapCount = 0;
            $xuatCount = 0;
            $c20Count = 0;
            $c40Count = 0;
            $hasAnyCostCount = 0;
            $lastUpdatedAt = '';

            $stFast = $pdo->prepare("
                SELECT `xuat_nhap`, `cont_40`, `payload_json`, `updated_at`
                FROM `tms_ketoan_bookings_cache`
                WHERE `trip_date` BETWEEN ? AND ?
                ORDER BY `trip_date` DESC, CAST(`id_po` AS UNSIGNED) DESC
            ");
            $stFast->execute([$fromDate, $toDate]);
            while ($cr = $stFast->fetch()) {
                $pJson = $cr['payload_json'] ?? '';
                if ($pJson === '') continue;
                if ($lastUpdatedAt === '' || ($cr['updated_at'] ?? '') > $lastUpdatedAt) {
                    $lastUpdatedAt = $cr['updated_at'] ?? '';
                }
                if (($cr['xuat_nhap'] ?? 'N') === 'X') $xuatCount++; else $nhapCount++;
                if ((int)($cr['cont_40'] ?? 0) > 0) $c40Count++; else $c20Count++;
                if (strpos($pJson, '"donGia1ThucTraChoxePo":null') === false
                    || strpos($pJson, '"donGiaDebitPo":null') === false
                    || strpos($pJson, '"_tmsBocXep":0') === false
                    || strpos($pJson, '"_veCauDuong":0') === false
                    || strpos($pJson, '"_tmsCpKhac":0') === false) {
                    $hasAnyCostCount++;
                }
                $jsonFragments[] = $pJson;
            }

            $totalCount = count($jsonFragments);
            if ($totalCount > 0 && $hasAnyCostCount === 0) {
                $needsCostSync = true;
            }

            echo '{"ok":true'
                . ',"fromDate":' . json_encode($fromDate)
                . ',"toDate":' . json_encode($toDate)
                . ',"total":' . $totalCount
                . ',"nhapCount":' . $nhapCount
                . ',"xuatCount":' . $xuatCount
                . ',"c20Count":' . $c20Count
                . ',"c40Count":' . $c40Count
                . ',"hasAnyCostCount":' . $hasAnyCostCount
                . ',"needsCostSync":' . ($needsCostSync ? 'true' : 'false')
                . ',"lastUpdatedAt":' . json_encode($lastUpdatedAt)
                . ',"bookings":[' . implode(',', $jsonFragments) . ']}';
            exit;
        }
    }

    // ========================================================================
    // CÁC ROUTE CẦN TOKEN ĐĂNG NHẬP (TÀI XẾ / VENDOR)
    // ========================================================================
    $user = verify_token($authHeader);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Phiên đăng nhập không hợp lệ']);
        exit;
    }

    // Đổi mật khẩu cho Tài xế
    if ($route === 'driver/change-password') {
        $newPin = trim($jsonBody['newPassword'] ?? '');
        if (strlen($newPin) < 4) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Mật khẩu mới phải từ 4 ký tự trở lên']);
            exit;
        }
        $st = $pdo->prepare("UPDATE drivers SET pin_code = ? WHERE id = ?");
        $st->execute([$newPin, $user['driverId']]);
        echo json_encode(['ok' => true, 'message' => 'Đã đổi mật khẩu thành công!']);
        exit;
    }

    // Đổi mật khẩu cho Chủ Vendor
    if ($route === 'vendor/change-password') {
        $newPass = trim($jsonBody['newPassword'] ?? '');
        if (strlen($newPass) < 4) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Mật khẩu mới phải từ 4 ký tự trở lên']);
            exit;
        }
        $st = $pdo->prepare("UPDATE vendors SET password_hash = ? WHERE id = ?");
        $st->execute([$newPass, $user['vendorId']]);
        echo json_encode(['ok' => true, 'message' => 'Đã đổi mật khẩu nhà xe thành công!']);
        exit;
    }

    // Lấy danh sách đội xe của riêng Vendor đang đăng nhập
    if ($route === 'vendor/my-drivers') {
        $st = $pdo->prepare("SELECT d.*, d.driver_name AS full_name FROM drivers d WHERE d.vendor_id = ? ORDER BY d.assigned_plate ASC");
        $st->execute([$user['vendorId']]);
        echo json_encode(['ok' => true, 'drivers' => $st->fetchAll()]);
        exit;
    }

    // Lấy danh sách chuyến của Tài xế
    if ($route === 'driver/my-trips') {
        $plate = to_clean_code($user['plate'] ?? '');
        if (!$plate) {
            echo json_encode(['ok' => true, 'trips' => [], 'importConts' => [], 'driverInfo' => []]);
            exit;
        }
        $targetDate = $_GET['date'] ?? null;
        $fromDate = $_GET['fromDate'] ?? null;
        $toDate = $_GET['toDate'] ?? null;
        $forceRefresh = !empty($_GET['refresh']);

        $stDrv = $pdo->prepare("SELECT driver_name, phone, cccd, gplx FROM drivers WHERE assigned_plate = ? LIMIT 1");
        $stDrv->execute([$plate]);
        $drvRow = $stDrv->fetch() ?: [];

        // Hỗ trợ lọc theo Kỳ (26->25) hoặc khoảng thời gian tự chọn
        if ($fromDate && $toDate) {
            $fTs = strtotime($fromDate);
            $tTs = strtotime($toDate);
            $cycles = [];
            if ($fTs && $tTs) {
                for ($ts = $fTs; $ts <= $tTs + 86400 * 35; $ts += 86400 * 15) {
                    $cycles[] = get_billing_cycle_param(date('Y-m-d', $ts));
                }
            }
            $cycles = array_unique(array_filter($cycles));
            if (empty($cycles)) {
                $cycles = [get_billing_cycle_param($fromDate)];
            }
            $all = [];
            foreach ($cycles as $c) {
                $arr = fetch_single_cycle($c, $forceRefresh);
                if (is_array($arr)) {
                    foreach ($arr as $item) {
                        if (isset($item['idPo'])) {
                            $all[$item['idPo']] = normalize_shipment_item($item);
                        }
                    }
                }
            }
        } else {
            // Tài xế chỉ xem chuyến chạy, bỏ qua bảng cước Debit đối soát để tải tức thì
            $all = fetch_vietsun_shipments($targetDate, $forceRefresh, false);
        }

        $myTrips = [];
        foreach ($all as $item) {
            $itemPlate = to_clean_code($item['soXePo'] ?? '');
            if ($itemPlate !== '' && ($itemPlate === $plate || strpos($itemPlate, $plate) !== false)) {
                $myTrips[] = $item;
            }
        }

        // Trích xuất danh sách cont trả hàng nhập (N) của CHÍNH TÀI XẾ ĐÓ hôm nay hoặc hôm qua (day -1)
        // để hỗ trợ kết hợp vỏ đóng hàng xuất -> tự động nhảy nơi lấy rỗng KH 20'/40'
        $todayIso = date('Y-m-d');
        $yesterdayIso = date('Y-m-d', strtotime('-1 day'));
        $importConts = [];
        foreach ($all as $item) {
            $itemPlate = to_clean_code($item['soXePo'] ?? '');
            // Chỉ lấy các chuyến hàng nhập của chính xe tài xế này
            if ($itemPlate === '' || ($itemPlate !== $plate && strpos($itemPlate, $plate) === false)) {
                continue;
            }
            $isNhap = ($item['xuatNhapPo'] ?? '') === 'N';
            if (!$isNhap) continue;
            $soCont = strtoupper(trim($item['soContPo'] ?? ''));
            if ($soCont === '' || $soCont === '--' || strpos($soCont, 'CHỜ') !== false) continue;
            $tripDate = get_shipment_date_iso($item);
            if ($tripDate === $todayIso || $tripDate === $yesterdayIso) {
                $is40 = (int)($item['cont40Po'] ?? 0) > 0;
                $size = $is40 ? '40' : '20';
                $kho = ($item['khoNhanHangPo'] ?? '') ?: ($item['tenKhachPo'] ?? '');
                $soXe = trim($item['soXePo'] ?? '');
                $importConts[$soCont] = [
                    'soCont'   => $soCont,
                    'size'     => $size,
                    'tripDate' => $tripDate,
                    'kho'      => $kho,
                    'soXe'     => $soXe,
                    'khLabel'  => "KH $size'"
                ];
            }
        }

        echo json_encode([
            'ok'          => true,
            'today'       => date('Y-m-d'),
            'trips'       => $myTrips,
            'importConts' => array_values($importConts),
            'driverInfo'  => [
                'name'   => $drvRow['driver_name'] ?? ($user['driverName'] ?? ''),
                'plate'  => $user['plate'] ?? '',
                'vendor' => $user['vendorName'] ?? '',
                'phone'  => $drvRow['phone'] ?? '',
                'cccd'   => $drvRow['cccd'] ?? '',
                'gplx'   => $drvRow['gplx'] ?? ''
            ]
        ]);
        exit;
    }

    // Tài xế hoặc Chủ Vendor cập nhật Cont/Seal/Cảng đến/Nơi lấy rỗng -> Đưa vào hàng đợi CHỜ ĐIỀU VẬN DUYỆT (Khóa nếu qua ngày)
    if ($route === 'driver/update-trip' || $route === 'vendor/update-trip') {
        $idPo = $jsonBody['idPo'] ?? '';
        $codePo = $jsonBody['codePo'] ?? '';
        if (!$idPo) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Thiếu mã chuyến idPo']);
            exit;
        }
        $fieldsToQueue = [];
        if (isset($jsonBody['soCont'])) {
            $cleanCont = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string)$jsonBody['soCont']));
            if ($cleanCont !== '') {
                $chkIso = validate_container_iso($cleanCont);
                if (!$chkIso['valid']) {
                    http_response_code(400);
                    echo json_encode([
                        'ok' => false,
                        'error' => "Số container \"$cleanCont\" không đúng chuẩn quốc tế ISO 6346! ({$chkIso['message']})"
                    ]);
                    exit;
                }
            }
            $fieldsToQueue['soContPo'] = $cleanCont;
        }
        if (isset($jsonBody['soSeal'])) {
            $fieldsToQueue['soSealPo'] = trim($jsonBody['soSeal']);
        }
        if (isset($jsonBody['noiLayCont']) && trim($jsonBody['noiLayCont']) !== '') {
            $fieldsToQueue['noiLayContPo'] = strtoupper(trim($jsonBody['noiLayCont']));
        }
        if (isset($jsonBody['portTime']) && trim($jsonBody['portTime']) !== '') {
            $fieldsToQueue['portTimePo'] = trim($jsonBody['portTime']);
        }
        if (isset($jsonBody['cangDen']) && trim($jsonBody['cangDen']) !== '') {
            $fieldsToQueue['tinhXuatHangPo'] = strtoupper(trim($jsonBody['cangDen']));
        }

        $actorRole = ($user['role'] ?? '') === 'vendor' ? 'VENDOR' : 'DRIVER';
        $vName = normalize_vendor_name($user['vendorName'] ?? ($user['vendorCode'] ?? 'Vendor'));
        $liveDriverName = $user['driverName'] ?? '';
        $liveDriverPhone = '';
        $liveDriverCccd = '';
        $liveDriverGplx = '';
        $cleanUserPlate = to_clean_code($user['plate'] ?? '');

        // LUÔN truy vấn tên tài xế và nhà xe mới nhất trực tiếp từ bảng drivers theo biển số xe,
        // tuyệt đối không dùng $user['driverName'] cũ bị lưu cache trong JWT token trên điện thoại!
        if ($actorRole === 'DRIVER' && $cleanUserPlate !== '') {
            try {
                $stLiveDrv = $pdo->prepare("
                    SELECT d.driver_name, d.phone, d.cccd, d.gplx, v.vendor_name
                    FROM drivers d
                    LEFT JOIN vendors v ON d.vendor_id = v.id
                    WHERE d.assigned_plate = ? LIMIT 1
                ");
                $stLiveDrv->execute([$cleanUserPlate]);
                $liveRow = $stLiveDrv->fetch();
                if ($liveRow) {
                    if (!empty($liveRow['driver_name'])) {
                        $liveDriverName = strtoupper(trim($liveRow['driver_name']));
                    }
                    $liveDriverPhone = trim($liveRow['phone'] ?? '');
                    $liveDriverCccd  = trim($liveRow['cccd'] ?? '');
                    $liveDriverGplx  = trim($liveRow['gplx'] ?? '');
                    if (!empty($liveRow['vendor_name'])) {
                        $vName = normalize_vendor_name($liveRow['vendor_name']);
                    }
                }
            } catch (Exception $e) {}
        }

        $actorName = $actorRole === 'VENDOR' ? $vName : trim($cleanUserPlate . ' - ' . $liveDriverName);
        $vendorName = $vName;
        $qRes = queue_pending_tms_approval($pdo, $idPo, $codePo, $actorRole, $actorName, $vendorName, $fieldsToQueue);
        if (!$qRes['ok']) {
            http_response_code(403);
            echo json_encode($qRes);
            exit;
        }

        // Chốt snapshot tài xế thực chạy chuyến này vào trip_driver_snapshots để bảo toàn dữ liệu tính lương
        if ($actorRole === 'DRIVER' && $cleanUserPlate !== '' && $liveDriverName !== '') {
            $fakeTrip = [
                'codePo'   => $codePo,
                'soContPo' => $fieldsToQueue['soContPo'] ?? ''
            ];
            lock_trip_driver_snapshot($pdo, $idPo, $cleanUserPlate, $liveDriverName, $liveDriverPhone, $liveDriverCccd, $liveDriverGplx, $vendorName, $fakeTrip);
        }

        echo json_encode([
            'ok'         => true,
            'pending'    => true,
            'driverName' => $liveDriverName,
            'message'    => 'Đã gửi thông tin chờ Điều vận kiểm duyệt trước khi đẩy lên TMS!'
        ]);
        exit;
    }

    // Chủ Vendor gán / đổi Số xe -> Đưa vào hàng đợi CHỜ ĐIỀU VẬN DUYỆT (Khóa nếu qua ngày)
    if ($route === 'vendor/assign-truck') {
        $idPo = $jsonBody['idPo'] ?? '';
        $codePo = $jsonBody['codePo'] ?? '';
        $soXe = to_clean_code($jsonBody['soXePo'] ?? '');
        if (!$idPo) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Thiếu mã lô hàng idPo']);
            exit;
        }
        $vendorName = normalize_vendor_name($user['vendorName'] ?? '');
        $fieldsToQueue = ['soXePo' => $soXe];

        // Lấy thông tin chuyến hiện tại trên TMS
        $all = fetch_vietsun_shipments(null, false);
        $currTrip = null;
        foreach ($all as $t) {
            if ((string)($t['idPo'] ?? '') === (string)$idPo) {
                $currTrip = $t;
                break;
            }
        }
        // Nếu chuyến trên TMS đã có ĐVVT rồi (VD: "PHUC TRUONG AN") thì TUYỆT ĐỐI KHÔNG ghi đè,
        // để bảo toàn nguyên vẹn tên ĐVVT có dấu cách từ Điều vận.
        // Chỉ khi trên TMS chưa có ĐVVT mới bổ sung donViVcPo đã chuẩn hóa.
        $currDv = trim($currTrip['donViVcPo'] ?? '');
        if ($currDv === '' && $vendorName !== '') {
            $fieldsToQueue['donViVcPo'] = $vendorName;
        }
        $qRes = queue_pending_tms_approval($pdo, $idPo, $codePo, 'VENDOR', $vendorName, $vendorName, $fieldsToQueue);
        if (!$qRes['ok']) {
            http_response_code(403);
            echo json_encode($qRes);
            exit;
        }

        // Chốt snapshot tài xế đang phụ trách xe tại thời điểm Chủ Vendor điều xe cho chuyến này
        if ($soXe !== '') {
            lock_trip_driver_snapshot($pdo, $idPo, $soXe, '', '', '', '', $vendorName, $currTrip);
        }

        echo json_encode([
            'ok'      => true,
            'pending' => true,
            'soXePo'  => $soXe,
            'message' => "Đã lưu xe $soXe (Đang chờ Điều vận duyệt đẩy lên TMS)!"
        ]);
        exit;
    }

    // Tài xế báo chi phí phát sinh (Khóa nếu qua ngày)
    if ($route === 'driver/add-expense') {
        $idPo = $jsonBody['idPo'] ?? '';
        $expenseType = $jsonBody['expenseType'] ?? '';
        $amount = $jsonBody['amount'] ?? 0;
        $note = $jsonBody['note'] ?? '';

        $all = fetch_vietsun_shipments(null, false);
        foreach ($all as $t) {
            if ((string)($t['idPo'] ?? '') === (string)$idPo) {
                $td = get_shipment_date_iso($t);
                if ($td !== '' && $td < date('Y-m-d')) {
                    http_response_code(403);
                    echo json_encode(['ok' => false, 'error' => "Chuyến hàng ngày $td đã kết thúc ngày và bị khóa sổ!"]);
                    exit;
                }
                break;
            }
        }

        $stmt = $pdo->prepare('INSERT INTO trip_expenses (booking_id_po, driver_id, expense_type, amount, note) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$idPo, $user['driverId'] ?? null, $expenseType, $amount, $note]);
        echo json_encode(['ok' => true, 'message' => 'Đã lưu chi phí phát sinh chờ đối soát!']);
        exit;
    }

    // Vendor lấy danh sách chuyến của toàn đội xe
    if ($route === 'vendor/trips') {
        $vName = strtoupper(trim($user['vendorName'] ?? ''));
        $vCode = to_clean_code($user['vendorCode'] ?? $vName);
        $targetDate = $_GET['date'] ?? null;
        $fromDate = $_GET['fromDate'] ?? null;
        $toDate = $_GET['toDate'] ?? null;
        $forceRefresh = !empty($_GET['refresh']);

        // Lấy danh sách biển số xe thuộc Vendor này để đối chiếu cả theo số xe lẫn tên ĐVVT
        $stDrv = $pdo->prepare("SELECT assigned_plate, driver_name, phone, cccd, gplx FROM drivers WHERE vendor_id = ?");
        $stDrv->execute([$user['vendorId']]);
        $drvRows = $stDrv->fetchAll();
        $fleetMap = [];
        foreach ($drvRows as $dr) {
            $p = to_clean_code($dr['assigned_plate']);
            if ($p) {
                $fleetMap[$p] = [
                    'plate' => $p,
                    'name'  => $dr['driver_name'] ?? '',
                    'phone' => $dr['phone'] ?? '',
                    'cccd'  => $dr['cccd'] ?? '',
                    'gplx'  => $dr['gplx'] ?? ''
                ];
            }
        }

        // Tải bảng snapshot tài xế theo chuyến & lịch sử bàn giao xe để các chuyến cũ luôn giữ đúng tên Tài xế cũ phục vụ tính lương
        $snapshotsMap = [];
        $historyByPlate = [];
        try {
            $stSnapAll = $pdo->query("SELECT id_po, assigned_plate, driver_name, driver_phone, driver_cccd, driver_gplx FROM trip_driver_snapshots");
            if ($stSnapAll) {
                foreach ($stSnapAll->fetchAll() as $sr) {
                    $snapshotsMap[(string)$sr['id_po']] = $sr;
                }
            }
            $stHistAll = $pdo->query("SELECT assigned_plate, driver_name, phone, cccd, gplx, assigned_from, assigned_to FROM driver_assignment_history ORDER BY id DESC");
            if ($stHistAll) {
                foreach ($stHistAll->fetchAll() as $hr) {
                    $hp = to_clean_code($hr['assigned_plate'] ?? '');
                    if ($hp) {
                        $historyByPlate[$hp][] = $hr;
                        // Bổ sung cả các biển số từng thuộc lịch sử của Vendor để không sót chuyến cũ
                    }
                }
            }
        } catch (Exception $e) {}

        // Vendor cần cước Debit để xem tab Chuyến xe & Đối soát (bao phủ đầy đủ kỳ fromDate -> toDate)
        $all = fetch_vietsun_shipments($targetDate, $forceRefresh, true, $fromDate, $toDate);
        $vendorTrips = [];
        $todayIso = date('Y-m-d');
        foreach ($all as $item) {
            $itemVendor = strtoupper(trim($item['donViVcPo'] ?? ''));
            $itemVCode = to_clean_code($itemVendor);
            $itemPlate = to_clean_code($item['soXePo'] ?? '');

            $matchVendor = ($itemVCode !== '' && ($itemVCode === $vCode || strpos($itemVCode, $vCode) !== false));
            $matchPlate  = ($itemPlate !== '' && isset($fleetMap[$itemPlate]));

            if ($matchVendor || $matchPlate) {
                if ($itemPlate !== '') {
                    $tripDateIso = get_shipment_date_iso($item);
                    $resolvedDrv = resolve_driver_for_trip($item['idPo'] ?? '', $itemPlate, $tripDateIso, $snapshotsMap, $historyByPlate, $fleetMap);
                    if ($resolvedDrv) {
                        $item['_driverName']   = $resolvedDrv['name'];
                        $item['_driverPhone']  = $resolvedDrv['phone'];
                        $item['_driverCccd']   = $resolvedDrv['cccd'];
                        $item['_driverGplx']   = $resolvedDrv['gplx'];
                        $item['_driverSource'] = $resolvedDrv['source'];

                        // Tự động chốt snapshot cho các chuyến đã chạy (ngày <= hôm nay) nhưng chưa có trong trip_driver_snapshots
                        if (!isset($snapshotsMap[(string)($item['idPo'] ?? '')]) && $tripDateIso !== '' && $tripDateIso <= $todayIso && !empty($resolvedDrv['name'])) {
                            lock_trip_driver_snapshot(
                                $pdo,
                                $item['idPo'],
                                $itemPlate,
                                $resolvedDrv['name'],
                                $resolvedDrv['phone'],
                                $resolvedDrv['cccd'],
                                $resolvedDrv['gplx'],
                                $vName,
                                $item
                            );
                            $snapshotsMap[(string)$item['idPo']] = [
                                'id_po'          => (string)$item['idPo'],
                                'assigned_plate' => $itemPlate,
                                'driver_name'    => $resolvedDrv['name'],
                                'driver_phone'   => $resolvedDrv['phone'],
                                'driver_cccd'    => $resolvedDrv['cccd'],
                                'driver_gplx'    => $resolvedDrv['gplx']
                            ];
                        }
                    }
                }
                $vendorTrips[] = $item;
            }
        }
        $todayIso = date('Y-m-d');
        $yesterdayIso = date('Y-m-d', strtotime('-1 day'));
        $importConts = [];
        foreach ($vendorTrips as $item) {
            $isNhap = ($item['xuatNhapPo'] ?? '') === 'N';
            if (!$isNhap) continue;
            $soCont = strtoupper(trim($item['soContPo'] ?? ''));
            if ($soCont === '' || $soCont === '--' || strpos($soCont, 'CHỜ') !== false) continue;
            $tripDate = get_shipment_date_iso($item);
            if ($tripDate === $todayIso || $tripDate === $yesterdayIso) {
                $is40 = (int)($item['cont40Po'] ?? 0) > 0;
                $size = $is40 ? '40' : '20';
                $kho = ($item['khoNhanHangPo'] ?? '') ?: ($item['tenKhachPo'] ?? '');
                $soXe = trim($item['soXePo'] ?? '');
                $importConts[$soCont] = [
                    'soCont'     => $soCont,
                    'size'       => $size,
                    'tripDate'   => $tripDate,
                    'kho'        => $kho,
                    'soXe'       => $soXe,
                    'cleanPlate' => to_clean_code($soXe),
                    'khLabel'    => "KH $size'"
                ];
            }
        }

        echo json_encode([
            'ok'          => true,
            'today'       => date('Y-m-d'),
            'total'       => count($vendorTrips),
            'fleetPlates' => array_values($fleetMap),
            'trips'       => $vendorTrips,
            'importConts' => array_values($importConts)
        ]);
        exit;
    }

    // Vendor Báo cáo Doanh thu & Công nợ
    if ($route === 'vendor/revenue-report') {
        $vName = $user['vendorName'] ?? '';
        $fromDate = $_GET['fromDate'] ?? '';
        $toDate = $_GET['toDate'] ?? '';

        $sql = 'SELECT * FROM completed_bookings WHERE vendor_name = ?';
        $params = [$vName];
        if ($fromDate && $toDate) {
            $sql .= ' AND ngay_tra_hang BETWEEN ? AND ?';
            $params[] = $fromDate;
            $params[] = $toDate;
        }
        $sql .= ' ORDER BY ngay_tra_hang DESC, id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $completed = $stmt->fetchAll();

        $stmtExp = $pdo->prepare('SELECT * FROM trip_expenses ORDER BY id DESC LIMIT 200');
        $stmtExp->execute();
        $expenses = $stmtExp->fetchAll();

        $totalRevenue = 0;
        $totalExpenses = 0;
        foreach ($completed as $c) {
            $rate = $c['debit_rate_adjusted'] !== null ? (float)$c['debit_rate_adjusted'] : (float)$c['debit_rate'];
            $totalRevenue += $rate;
        }
        foreach ($expenses as $e) {
            $totalExpenses += (float)$e['amount'];
        }

        echo json_encode([
            'ok'       => true,
            'summary'  => [
                'totalTrips'    => count($completed),
                'totalRevenue'  => $totalRevenue,
                'totalExpenses' => $totalExpenses,
                'netTotal'      => $totalRevenue + $totalExpenses
            ],
            'bookings' => $completed,
            'expenses' => $expenses
        ]);
        exit;
    }

    // Vendor điều chỉnh cước Debit
    if ($route === 'vendor/adjust-rate') {
        $idPo = $jsonBody['idPo'] ?? '';
        $adjustedRate = $jsonBody['adjustedRate'] ?? 0;
        $reason = $jsonBody['reason'] ?? '';
        $stmt = $pdo->prepare('UPDATE completed_bookings SET debit_rate_adjusted = ?, adjust_reason = ? WHERE id_po = ? AND vendor_name = ?');
        $stmt->execute([$adjustedRate, $reason, $idPo, $user['vendorName'] ?? '']);
        echo json_encode(['ok' => true, 'message' => 'Đã cập nhật giá cước đối soát!']);
        exit;
    }

    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Route không tồn tại: ' . $route]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
