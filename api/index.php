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
    $flagFile = sys_get_temp_dir() . '/vietsun_schema_v12.flag';
    if (file_exists($flagFile)) {
        return;
    }

    // 1. Đảm bảo bảng tms_pending_approvals, tms_bb_templates và tms_tt_status tồn tại
    try {
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
    } catch (Exception $e) {}

    @file_put_contents($flagFile, time());
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
            $stmtUpsertDriver->execute([$vid, $dName ?: "Tài xế $plate", $phone, $plate, $cccd, $gplx]);
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
        'chiHoHaContPo'
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

function fetch_tms_debit_map($startDate, $endDate, $forceRefresh = false) {
    global $COOKIE_FILE;
    $safeKey = preg_replace('/[^0-9\-]/', '', $startDate . '_' . $endDate);
    $cacheFile = sys_get_temp_dir() . '/vietsun_debit_map_v5_' . $safeKey . '.json';
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

    // 1. Lấy cước Debit toàn bộ Hàng Nhập qua view NHẬP (kieuXemTrucking = 6, idxem = 6: giới hạn 4000 dòng, bao phủ tới idPo mới nhất)
    call_tms_post('index-crud.php', [
        'TenDangNhap' => 'trucking14',
        'userString'  => $userString,
        'cv'          => 3,
        'loai'        => 1,
        'iddt'        => 1,
        'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
        'thongtin'    => '6'
    ]);
    $htmlNhap6 = call_tms_post('index-ajax.php', [
        'userString' => $userString,
        'cv'         => 78,
        'idxem'      => 6,
        'kieuxem'    => 0,
        'loaiload'   => 4,
        'startDate'  => $startDate,
        'endDate'    => $endDate,
        'widthmh'    => 1920
    ]);
    if (strpos($htmlNhap6, 'donGiaDebitPo') === false) {
        vietsun_login();
        call_tms_post('index-crud.php', [
            'TenDangNhap' => 'trucking14',
            'userString'  => $userString,
            'cv'          => 3,
            'loai'        => 1,
            'iddt'        => 1,
            'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
            'thongtin'    => '6'
        ]);
        $htmlNhap6 = call_tms_post('index-ajax.php', [
            'userString' => $userString,
            'cv'         => 78,
            'idxem'      => 6,
            'kieuxem'    => 0,
            'loaiload'   => 4,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
            'widthmh'    => 1920
        ]);
    }
    parse_tms_ajax_debit_html($htmlNhap6, $map);
    $htmlNhap6 = null;

    // 2. Lấy thêm chi tiết Hàng Nhập (Chi hộ bốc xếp, Vé cầu đường, Phí khác, Hình thức kết hợp) qua NHẬP - ALL (kieuXemTrucking = 7, idxem = 7)
    // Chia 2 khoảng 12 ngày để không chạm ngưỡng LIMIT 1700 dòng của SQL trên TMS
    call_tms_post('index-crud.php', [
        'TenDangNhap' => 'trucking14',
        'userString'  => $userString,
        'cv'          => 3,
        'loai'        => 1,
        'iddt'        => 1,
        'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
        'thongtin'    => '7'
    ]);
    $endTs = strtotime($endDate) ?: time();
    $nhapWindows = [
        [date('Y-m-d', $endTs - 24 * 86400), date('Y-m-d', $endTs - 12 * 86400)],
        [date('Y-m-d', $endTs - 11 * 86400), $endDate]
    ];
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
        parse_tms_ajax_debit_html($htmlNhap7, $map);
        $htmlNhap7 = null;
    }

    // 3. Lấy cước Debit, Chi hộ bốc xếp & Tỷ lệ kết hợp của Hàng Xuất (XUẤT - CHI PHÍ: kieuXemTrucking = 3, idxem = 2, kieuxem = 0)
    // Chia 2 khoảng để không chạm ngưỡng LIMIT 1200 dòng của SQL trên TMS
    call_tms_post('index-crud.php', [
        'TenDangNhap' => 'trucking14',
        'userString'  => $userString,
        'cv'          => 3,
        'loai'        => 1,
        'iddt'        => 1,
        'cot'         => 't9<<|1<<|1<<|20<<|kieuXemTrucking',
        'thongtin'    => '3'
    ]);
    $startTs = strtotime($startDate) ?: ($endTs - 35 * 86400);
    $midTs   = (int)(($startTs + $endTs) / 2);
    $xuatWindows = [
        [$startDate, date('Y-m-d', $midTs)],
        [date('Y-m-d', $midTs + 86400), $endDate]
    ];
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

function fetch_vietsun_shipments($targetDate = null, $forceRefresh = false, $includeDebit = true) {
    $baseTs = $targetDate ? strtotime($targetDate) : time();
    if (!$baseTs) $baseTs = time();

    // Cache danh sách chuyến: 60s để nhận chuyến mới/xe mới nhanh chóng (giá cước vẫn giữ cache 48h riêng biệt bên dưới)
    $cacheType = $includeDebit ? 'full' : 'nodbt';
    $cacheDateKey = date('Y-m-d', $baseTs);
    $cacheFile = sys_get_temp_dir() . "/vietsun_shipments_v5_{$cacheType}_{$cacheDateKey}.json";
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
        $cycles = array_unique([
            get_billing_cycle_param(date('Y-m-d', $baseTs - 20 * 86400)),
            get_billing_cycle_param(date('Y-m-d', $baseTs)),
            get_billing_cycle_param(date('Y-m-d', $baseTs + 15 * 86400))
        ]);

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

    // Lấy bảng giá Debit & Chi hộ bốc xếp & Tỷ lệ kết hợp từ TMS trong khoảng [baseTs - 35 ngày, baseTs + 10 ngày]
    $startDate = date('Y-m-d', $baseTs - 35 * 86400);
    $endDate   = date('Y-m-d', $baseTs + 10 * 86400);
    $debitMap  = fetch_tms_debit_map($startDate, $endDate, $forceRefresh);

    // Gắn thông tin cước và chi phí phát sinh từ TMS Vietsun
    foreach ($merged as $idPo => &$item) {
        $dInfo = $debitMap[$idPo] ?? [];
        $item['donGiaDebitPo']         = isset($dInfo['donGiaDebitPo']) && $dInfo['donGiaDebitPo'] > 0 ? (int)$dInfo['donGiaDebitPo'] : null;
        $item['donGia1ThucTraChoxePo'] = isset($dInfo['donGia1ThucTraChoxePo']) && $dInfo['donGia1ThucTraChoxePo'] > 0 ? (int)$dInfo['donGia1ThucTraChoxePo'] : null;
        if (!empty($dInfo['hinhThucKHopPo'])) {
            $item['hinhThucKHopPo'] = $dInfo['hinhThucKHopPo'];
        }
        if (!empty($dInfo['ghiChuPo']) && $dInfo['ghiChuPo'] !== '.') {
            $item['ghiChuPo'] = $dInfo['ghiChuPo'];
        }

        // Chi hộ bốc xếp (TMS hoặc Tài xế báo)
        $tmsBocXep = max((int)($dInfo['bocXep2ChiHoPo'] ?? 0), (int)($dInfo['cpBocXepChiHoPo'] ?? 0));
        $drvBocXep = (int)($drvBocXepMap[(string)$idPo] ?? 0);
        $item['_tmsBocXep'] = $tmsBocXep;
        $item['_driverBocXep'] = $drvBocXep;
        $item['_chiHoBocXep'] = $tmsBocXep > 0 ? $tmsBocXep : $drvBocXep;

        // Chi phí phát sinh khác (Neo xe, đường cấm, nâng hạ, phí khác...)
        $tmsCpKhac = (int)($dInfo['cpkhacpo'] ?? 0)
            + max((int)($dInfo['neoXeDuongCamPo'] ?? 0), (int)($dInfo['chiPhiNeoXePo'] ?? 0))
            + (int)($dInfo['cpduongcam'] ?? 0)
            + (int)($dInfo['chiHoNangContPo'] ?? 0)
            + (int)($dInfo['chiHoHaContPo'] ?? 0);
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
            'cot'      => "b18newtruck<<|4<<|50<<|{$idPo}<<|soXePo<<|{$codePo}",
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
    $schemaFlag = sys_get_temp_dir() . '/vietsun_schema_v12.flag';
    if (!file_exists($schemaFlag)) {
        ensure_schema($pdo);
        $drvCnt = (int)$pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
        if ($drvCnt < 10) {
            sync_from_google_sheet($pdo);
        }
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

        echo json_encode([
            'ok'           => true,
            'vietsun'      => $vietsunFleet,
            'vietsunCount' => count($vietsunFleet),
            'vendors'      => $drivers,
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
        $isVendorFleetOp = ($authPayload && ($authPayload['role'] ?? '') === 'vendor' && in_array($route, ['admin/save-driver', 'admin/delete-driver'], true));
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
        foreach ($rows as &$pr) {
            $pr['changes'] = json_decode($pr['changes_json'] ?? '{}', true) ?: [];
            if ($pr['actor_role'] === 'VENDOR') {
                $pr['actor_name'] = normalize_vendor_name($pr['actor_name']);
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

    // API Đồng bộ Google Sheet ngay lập tức
    if ($route === 'admin/sync-sheet') {
        $res = sync_from_google_sheet($pdo);
        echo json_encode(['ok' => true, 'message' => 'Đã đồng bộ từ Google Sheet thành công!', 'stats' => $res, 'synced' => $res]);
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
            foreach ($changes as $fieldName => $diff) {
                $newVal = $diff['new'] ?? '';
                if ($fieldName === 'donViVcPo') {
                    $newVal = normalize_vendor_name($newVal);
                }
                update_tms_field($idPo, $codePo, $fieldName, $newVal);
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

        if ($id > 0) {
            $st = $pdo->prepare("UPDATE drivers SET vendor_id = ?, assigned_plate = ?, driver_name = ?, phone = ?, pin_code = ?, cccd = ?, gplx = ? WHERE id = ?");
            $st->execute([$vendorId, $plate, $dName ?: "Tài xế $plate", $phone, $pin ?: '123456', $cccd, $gplx, $id]);
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
            $st->execute([$vendorId, $plate, $dName ?: "Tài xế $plate", $phone, $pin ?: '123456', $cccd, $gplx]);
        }
        echo json_encode(['ok' => true, 'message' => 'Đã lưu thông tin Xe & Tài xế!']);
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
            $flagFile = sys_get_temp_dir() . '/vietsun_cuoc_schema_v3.flag';
            if (file_exists($flagFile)) {
                return;
            }
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS tms_pricing_config (
                    config_key VARCHAR(64) PRIMARY KEY,
                    config_val TEXT NOT NULL,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS tms_routes (
                    route_code VARCHAR(32) PRIMARY KEY,
                    dia_diem VARCHAR(255) NOT NULL,
                    tinh VARCHAR(100) NOT NULL DEFAULT '',
                    base_rates_json TEXT NOT NULL,
                    notes VARCHAR(255) DEFAULT '',
                    sort_order INT NOT NULL DEFAULT 0,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
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
                    $pdo->beginTransaction();
                    try {
                        if ($routeCnt === 0 && !empty($seed['routes'])) {
                            $stR = $pdo->prepare("
                                INSERT IGNORE INTO tms_routes (route_code, dia_diem, tinh, base_rates_json, sort_order)
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            foreach ($seed['routes'] as $idx => $r) {
                                $stR->execute([
                                    $r['route_code'] ?? '',
                                    $r['dia_diem'] ?? '',
                                    $r['tinh'] ?? '',
                                    json_encode($r['base_rates'] ?? [], JSON_UNESCAPED_UNICODE),
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
                            // Cập nhật bổ sung cho các kho trong DB đang chưa gán mã tuyến (route_code = '') từ kết quả đối soát lịch sử Debit 25/08
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
                        $stCfg = $pdo->prepare("INSERT IGNORE INTO tms_pricing_config (config_key, config_val) VALUES (?, ?)");
                        $stCfg->execute(['adj_percent', (string)($seed['adj_percent'] ?? 0.032)]);
                        $stCfg->execute(['do_base_price', '18090']);
                        $stCfg->execute(['effective_date', '2026-07-21']);
                        $stCfg->execute(['notes_json', json_encode($seed['notes'] ?? [], JSON_UNESCAPED_UNICODE)]);
                        $stCfg->execute(['carriers_json', json_encode(['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'], JSON_UNESCAPED_UNICODE)]);
                        $pdo->commit();
                    } catch (Exception $ex) {
                        if ($pdo->inTransaction()) $pdo->rollBack();
                    }
                }
            }

            // Yêu cầu 1: Những ô cước mà Vendor đang để trống (0) thì tự động lấy bằng giá VIETSUN
            $rawCarriers = $pdo->query("SELECT config_val FROM tms_pricing_config WHERE config_key = 'carriers_json'")->fetchColumn();
            $activeCarriers = json_decode($rawCarriers ?: '[]', true) ?: ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];
            $allRoutesInDb = $pdo->query("SELECT route_code, base_rates_json FROM tms_routes")->fetchAll();
            $stFillR = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ? WHERE route_code = ?");
            $pdo->beginTransaction();
            try {
                foreach ($allRoutesInDb as $rr) {
                    $br = json_decode($rr['base_rates_json'] ?? '{}', true) ?: [];
                    $vs20 = (int)($br['VIETSUN']['c20'] ?? 0);
                    $vs40 = (int)($br['VIETSUN']['c40'] ?? 0);
                    $changed = false;
                    foreach ($activeCarriers as $ac) {
                        if ($ac === 'VIETSUN') continue;
                        if (!isset($br[$ac])) {
                            $br[$ac] = ['c20' => $vs20, 'c40' => $vs40];
                            $changed = true;
                        } else {
                            if (empty($br[$ac]['c20']) && $vs20 > 0) {
                                $br[$ac]['c20'] = $vs20;
                                $changed = true;
                            }
                            if (empty($br[$ac]['c40']) && $vs40 > 0) {
                                $br[$ac]['c40'] = $vs40;
                                $changed = true;
                            }
                        }
                    }
                    if ($changed) {
                        $stFillR->execute([json_encode($br, JSON_UNESCAPED_UNICODE), $rr['route_code']]);
                    }
                }
                $pdo->commit();
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
            }

            // Chuẩn hóa Tỉnh sau sáp nhập trên cả bảng tms_routes & tms_warehouses_2026:
            // Bình Dương => HCM, BRVT => HCM, Bình Thuận => Lâm Đồng, Đăk Nông => Lâm Đồng,
            // Bình Phước => Đồng Nai, Long An => Tây Ninh, Tiền Giang => Đồng Tháp,
            // Trà Vinh => Vĩnh Long, Bến Tre => Vĩnh Long, Hậu Giang => Cần Thơ,
            // Bạc Liêu => Cà Mau, Kiên Giang => An Giang, Sóc Trăng => Cần Thơ.
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

        // GET cuoc/overview: Trả về toàn bộ Tuyến đường, Cấu hình % biến động cước & Danh sách Kho 2026
        if ($route === 'cuoc/overview') {
            $cfgRows = $pdo->query("SELECT config_key, config_val FROM tms_pricing_config")->fetchAll();
            $cfg = [];
            foreach ($cfgRows as $cr) {
                $cfg[$cr['config_key']] = $cr['config_val'];
            }
            $adjPercent = isset($cfg['adj_percent']) ? (float)$cfg['adj_percent'] : 0.032;
            $carriers = json_decode($cfg['carriers_json'] ?? '[]', true) ?: ['VIETSUN', 'NHAT SON', 'DUONG NGUYEN', 'PHUC TRUONG AN', 'TAN TIEN PHAT', 'TUAN HUNG'];
            $notes = json_decode($cfg['notes_json'] ?? '[]', true) ?: [];

            $routesRaw = $pdo->query("SELECT * FROM tms_routes ORDER BY sort_order ASC, route_code ASC")->fetchAll();
            $routes = [];
            foreach ($routesRaw as $rr) {
                $routes[] = [
                    'route_code' => $rr['route_code'],
                    'dia_diem'   => $rr['dia_diem'],
                    'tinh'       => $rr['tinh'],
                    'notes'      => $rr['notes'] ?? '',
                    'sort_order' => (int)$rr['sort_order'],
                    'base_rates' => json_decode($rr['base_rates_json'] ?? '{}', true) ?: []
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
                    'adj_percent'    => $adjPercent,
                    'do_base_price'  => (int)($cfg['do_base_price'] ?? 18090),
                    'effective_date' => $cfg['effective_date'] ?? '2026-07-21',
                    'carriers'       => $carriers,
                    'notes'          => $notes
                ],
                'routes'     => $routes,
                'warehouses' => $warehouses
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

            // 2. Cập nhật base_rates_json trên toàn bộ 246 tuyến đường (Đổi tên key cũ -> mới, và/hoặc Áp nhanh giá từ source_carrier)
            $updatedRoutes = 0;
            if ($oldVendorName !== '' || $sourceCarrier !== '') {
                $allRoutes = $pdo->query("SELECT route_code, base_rates_json FROM tms_routes")->fetchAll();
                $stUp = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ? WHERE route_code = ?");
                $pdo->beginTransaction();
                try {
                    foreach ($allRoutes as $rr) {
                        $br = json_decode($rr['base_rates_json'] ?? '{}', true) ?: [];
                        $oldRateObj = ($oldVendorName !== '' && isset($br[$oldVendorName])) ? $br[$oldVendorName] : ['c20' => 0, 'c40' => 0];

                        foreach ($targetVendors as $tv) {
                            if ($sourceCarrier !== '') {
                                $srcKey = ($sourceCarrier === '__KEEP_CURRENT__' && $oldVendorName !== '') ? $oldVendorName : $sourceCarrier;
                                $src = ($srcKey === $oldVendorName) ? $oldRateObj : ($br[$srcKey] ?? ['c20' => 0, 'c40' => 0]);
                                $src20 = (float)($src['c20'] ?? 0);
                                $src40 = (float)($src['c40'] ?? 0);
                                $new20 = $src20 > 0 ? max(0, (int)round($src20 * ($factorPercent / 100.0) + $deltaAmount)) : 0;
                                $new40 = $src40 > 0 ? max(0, (int)round($src40 * ($factorPercent / 100.0) + $deltaAmount)) : 0;
                                $br[$tv] = ['c20' => $new20, 'c40' => $new40];
                            } elseif ($oldVendorName !== '' && !isset($br[$tv])) {
                                $br[$tv] = $oldRateObj;
                            }
                        }

                        if ($oldVendorName !== '' && !in_array($oldVendorName, $targetVendors, true)) {
                            unset($br[$oldVendorName]);
                        }

                        $stUp->execute([json_encode($br, JSON_UNESCAPED_UNICODE), $rr['route_code']]);
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

        // POST cuoc/delete-vendor: Xóa 1 cột Vendor khỏi bảng giá và dọn dữ liệu base_rates_json
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

            // Xóa key của vendor này trong base_rates_json
            $allRoutes = $pdo->query("SELECT route_code, base_rates_json FROM tms_routes")->fetchAll();
            $stUp = $pdo->prepare("UPDATE tms_routes SET base_rates_json = ? WHERE route_code = ?");
            $pdo->beginTransaction();
            try {
                foreach ($allRoutes as $rr) {
                    $br = json_decode($rr['base_rates_json'] ?? '{}', true) ?: [];
                    if (isset($br[$vendorName])) {
                        unset($br[$vendorName]);
                        $stUp->execute([json_encode($br, JSON_UNESCAPED_UNICODE), $rr['route_code']]);
                    }
                }
                $pdo->commit();
            } catch (Exception $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
            }

            echo json_encode(['ok' => true, 'carriers' => $carriers, 'message' => "Đã xóa Vendor [$vendorName] khỏi bảng giá!"]);
            exit;
        }

        // POST cuoc/save-route: Thêm mới hoặc cập nhật Mã Tuyến Đường & Bảng giá Vietsun + Vendor
        if ($route === 'cuoc/save-route') {
            $routeCode = strtoupper(trim($jsonBody['route_code'] ?? ''));
            $diaDiem = trim($jsonBody['dia_diem'] ?? '');
            $tinh = trim($jsonBody['tinh'] ?? '');
            $baseRates = $jsonBody['base_rates'] ?? [];
            $notes = trim($jsonBody['notes'] ?? '');
            if ($routeCode === '' || $diaDiem === '') {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Mã Tuyến Đường và Địa Điểm!']);
                exit;
            }
            $sortOrder = (int)($jsonBody['sort_order'] ?? 0);
            if ($sortOrder <= 0) {
                $sortOrder = ((int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM tms_routes")->fetchColumn()) + 1;
            }
            $st = $pdo->prepare("
                INSERT INTO tms_routes (route_code, dia_diem, tinh, base_rates_json, notes, sort_order)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    dia_diem = VALUES(dia_diem),
                    tinh = VALUES(tinh),
                    base_rates_json = VALUES(base_rates_json),
                    notes = VALUES(notes)
            ");
            $st->execute([
                $routeCode,
                $diaDiem,
                $tinh,
                json_encode($baseRates, JSON_UNESCAPED_UNICODE),
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
        $actorName = $actorRole === 'VENDOR' ? $vName : trim(($user['plate'] ?? '') . ' - ' . ($user['driverName'] ?? ''));
        $vendorName = $vName;
        $qRes = queue_pending_tms_approval($pdo, $idPo, $codePo, $actorRole, $actorName, $vendorName, $fieldsToQueue);
        if (!$qRes['ok']) {
            http_response_code(403);
            echo json_encode($qRes);
            exit;
        }
        echo json_encode([
            'ok'      => true,
            'pending' => true,
            'message' => 'Đã gửi thông tin chờ Điều vận kiểm duyệt trước khi đẩy lên TMS!'
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
        $forceRefresh = !empty($_GET['refresh']);

        // Lấy danh sách biển số xe thuộc Vendor này để đối chiếu cả по số xe lẫn tên ĐVVT
        $stDrv = $pdo->prepare("SELECT assigned_plate, driver_name, phone FROM drivers WHERE vendor_id = ?");
        $stDrv->execute([$user['vendorId']]);
        $drvRows = $stDrv->fetchAll();
        $fleetMap = [];
        foreach ($drvRows as $dr) {
            $p = to_clean_code($dr['assigned_plate']);
            if ($p) {
                $fleetMap[$p] = [
                    'plate' => $p,
                    'name'  => $dr['driver_name'] ?? '',
                    'phone' => $dr['phone'] ?? ''
                ];
            }
        }

        // Vendor cần cước Debit để xem tab Chuyến xe & Đối soát
        $all = fetch_vietsun_shipments($targetDate, $forceRefresh, true);
        $vendorTrips = [];
        foreach ($all as $item) {
            $itemVendor = strtoupper(trim($item['donViVcPo'] ?? ''));
            $itemVCode = to_clean_code($itemVendor);
            $itemPlate = to_clean_code($item['soXePo'] ?? '');

            $matchVendor = ($itemVCode !== '' && ($itemVCode === $vCode || strpos($itemVCode, $vCode) !== false));
            $matchPlate  = ($itemPlate !== '' && isset($fleetMap[$itemPlate]));

            if ($matchVendor || $matchPlate) {
                if ($itemPlate !== '' && isset($fleetMap[$itemPlate])) {
                    $item['_driverName']  = $fleetMap[$itemPlate]['name'];
                    $item['_driverPhone'] = $fleetMap[$itemPlate]['phone'];
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
