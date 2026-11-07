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

// Tự động nâng cấp cấu trúc bảng để hỗ trợ:
// - Số xe (assigned_plate) là UNIQUE
// - Nhiều xe có thể dùng chung SĐT tài xế
// - Lưu thêm CCCD, GPLX
function ensure_schema($pdo) {
    $flagFile = sys_get_temp_dir() . '/vietsun_schema_v5.flag';
    if (!file_exists($flagFile)) {
        try { $pdo->exec("ALTER TABLE `drivers` DROP INDEX `phone`"); } catch (Exception $e) {}
        try { $pdo->exec("ALTER TABLE `drivers` MODIFY `phone` VARCHAR(30) NULL"); } catch (Exception $e) {}
        try { $pdo->exec("ALTER TABLE `drivers` MODIFY `pin_code` VARCHAR(100) NOT NULL DEFAULT '123456'"); } catch (Exception $e) {}
        try { $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `cccd` VARCHAR(100) NULL"); } catch (Exception $e) {}
        try { $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `gplx` VARCHAR(100) NULL"); } catch (Exception $e) {}
        try { $pdo->exec("ALTER TABLE `drivers` ADD UNIQUE INDEX `idx_assigned_plate` (`assigned_plate`)"); } catch (Exception $e) {}

        // Bảng lưu yêu cầu cập nhật từ Vendor & Tài xế chờ Điều vận duyệt trước khi đẩy lên TMS Vietsun
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `tms_pending_approvals` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_po` VARCHAR(32) NOT NULL,
                    `code_po` VARCHAR(64) NOT NULL DEFAULT '',
                    `trip_date` VARCHAR(20) NOT NULL DEFAULT '',
                    `xuat_nhap` VARCHAR(10) NOT NULL DEFAULT '',
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
        } catch (Exception $e) {}

        // Xóa các dòng demo cũ bị lệch mã (HLP -> HOANGLONGPHAT)
        try {
            $pdo->exec("DELETE FROM `vendors` WHERE `vendor_code` = 'HLP'");
            $pdo->exec("UPDATE `vendors` SET `password_hash` = '123456' WHERE `password_hash` IN ('hlp@123', 'tranco@123', 'vp@123')");
        } catch (Exception $e) {}

        @file_put_contents($flagFile, time());
    }

    // Luôn luôn dọn sạch vendor và xe nhà VIETSUN nếu còn sót trong DB (chỉ giữ Vendor ngoài)
    try {
        $stFindVietsun = $pdo->query("SELECT id FROM vendors WHERE vendor_code = 'VIETSUN'")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($stFindVietsun)) {
            $inClause = implode(',', array_map('intval', $stFindVietsun));
            $pdo->exec("DELETE FROM drivers WHERE vendor_id IN ($inClause)");
            $pdo->exec("DELETE FROM vendors WHERE id IN ($inClause)");
        }
        // Chuẩn hóa thống nhất tên vendor trong DB để không bị xung đột (VD: PHUCTRUONGAN -> PHUC TRUONG AN)
        $pdo->exec("UPDATE `vendors` SET `vendor_name` = 'PHUC TRUONG AN' WHERE `vendor_code` = 'PHUCTRUONGAN'");
        $pdo->exec("UPDATE `tms_pending_approvals` SET `vendor_name` = 'PHUC TRUONG AN' WHERE `vendor_name` = 'PHUCTRUONGAN'");
    } catch (Exception $e) {}
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
            $vName = strtoupper(trim($cols[0] ?? ''));
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

    $changesJson = json_encode($changes, JSON_UNESCAPED_UNICODE);
    if ($existRow) {
        $stUpd = $pdo->prepare("
            UPDATE tms_pending_approvals
            SET code_po = ?, trip_date = ?, xuat_nhap = ?, khach_kho = ?, actor_name = ?, vendor_name = ?, changes_json = ?, created_at = NOW()
            WHERE id = ?
        ");
        $stUpd->execute([$codePoFinal, $tripDate, $xuatNhap, $khachKho, $actorName, $vendorName, $changesJson, $existRow['id']]);
        $pendingId = (int)$existRow['id'];
    } else {
        $stIns = $pdo->prepare("
            INSERT INTO tms_pending_approvals (id_po, code_po, trip_date, xuat_nhap, khach_kho, actor_role, actor_name, vendor_name, changes_json, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')
        ");
        $stIns->execute([(string)$idPo, $codePoFinal, $tripDate, $xuatNhap, $khachKho, $actorRole, $actorName, $vendorName, $changesJson]);
        $pendingId = (int)$pdo->lastInsertId();
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
    $pdo = get_db();
    ensure_schema($pdo);

    // Tự động đồng bộ lần đầu nếu bảng drivers có ít hơn 10 xe
    $drvCnt = (int)$pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
    if ($drvCnt < 10) {
        sync_from_google_sheet($pdo);
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
            'vendorName' => $vendor['vendor_name'],
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
    // 2B. ĐĂNG NHẬP QUẢN TRỊ VIÊN (/admin) - PASS: 69
    // ========================================================================
    if ($route === 'auth/admin-login') {
        $pass = trim($jsonBody['password'] ?? '');
        if ($pass !== '69') {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Mật khẩu quản trị không đúng!']);
            exit;
        }
        $token = make_token(['role' => 'admin', 'admin' => true]);
        echo json_encode(['ok' => true, 'token' => $token]);
        exit;
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
        $drivers = $pdo->query("
            SELECT d.assigned_plate AS plate, d.driver_name AS name, d.phone, d.cccd, d.gplx, v.vendor_name AS vendor
            FROM drivers d
            JOIN vendors v ON d.vendor_id = v.id
            WHERE d.is_active = 1 AND v.vendor_code != 'VIETSUN'
            ORDER BY v.vendor_name ASC, d.assigned_plate ASC
        ")->fetchAll();
        echo json_encode(['ok' => true, 'vendors' => $drivers, 'total' => count($drivers)]);
        exit;
    }

    if ($route === 'return_json') {
        $ngay = $_GET['ngay'] ?? ($_GET['date'] ?? null);
        $force = !empty($_GET['refresh']);
        if ($ngay && preg_match('/^\d{4}-\d{2}-01$/', $ngay)) {
            echo json_encode(fetch_single_cycle($ngay, $force));
        } else {
            echo json_encode(fetch_vietsun_shipments($ngay, $force));
        }
        exit;
    }
    if ($route === 'index-crud' || $route === 'index_crud') {
        echo push_vietsun_crud($_POST ?: $jsonBody);
        exit;
    }

    // Kiểm tra quyền Admin cho các route /admin/*
    if (strpos($route, 'admin/') === 0) {
        $authPayload = verify_token($authHeader);
        $isAdmin = ($authPayload && ($authPayload['role'] ?? '') === 'admin');
        $isVendorFleetOp = ($authPayload && ($authPayload['role'] ?? '') === 'vendor' && in_array($route, ['admin/save-driver', 'admin/delete-driver'], true));
        if (!$isAdmin && !$isVendorFleetOp) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Yêu cầu mật khẩu Quản trị viên (/admin)!']);
            exit;
        }
    }

    // API Đồng bộ Google Sheet ngay lập tức
    if ($route === 'admin/sync-sheet') {
        $res = sync_from_google_sheet($pdo);
        echo json_encode(['ok' => true, 'message' => 'Đã đồng bộ từ Google Sheet thành công!', 'stats' => $res, 'synced' => $res]);
        exit;
    }

    // API Lấy toàn bộ danh sách Vendor, Tài xế & Yêu cầu chờ duyệt cho Dashboard Quản trị
    if ($route === 'admin/overview') {
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

        $pending = $pdo->query("
            SELECT * FROM tms_pending_approvals
            ORDER BY FIELD(status, 'PENDING', 'APPROVED', 'REJECTED'), id DESC
            LIMIT 250
        ")->fetchAll();
        foreach ($pending as &$pr) {
            $pr['changes'] = json_decode($pr['changes_json'] ?? '{}', true) ?: [];
        }
        unset($pr);

        $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM tms_pending_approvals WHERE status = 'PENDING'")->fetchColumn();

        echo json_encode([
            'ok'               => true,
            'vendors'          => $vendors,
            'drivers'          => $drivers,
            'pendingCount'     => $pendingCount,
            'pendingApprovals' => $pending
        ]);
        exit;
    }

    // API Lấy danh sách thay đổi chờ duyệt (Dùng cho nút thông báo nhanh hoặc poll)
    if ($route === 'admin/pending-approvals') {
        $pending = $pdo->query("
            SELECT * FROM tms_pending_approvals
            ORDER BY FIELD(status, 'PENDING', 'APPROVED', 'REJECTED'), id DESC
            LIMIT 250
        ")->fetchAll();
        foreach ($pending as &$pr) {
            $pr['changes'] = json_decode($pr['changes_json'] ?? '{}', true) ?: [];
        }
        unset($pr);
        $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM tms_pending_approvals WHERE status = 'PENDING'")->fetchColumn();
        echo json_encode([
            'ok'               => true,
            'pendingCount'     => $pendingCount,
            'pendingApprovals' => $pending
        ]);
        exit;
    }

    // API Duyệt thông tin từ Vendor/Tài xế -> Đẩy chính thức về Vietsun TMS
    if ($route === 'admin/approve-update') {
        $id = isset($jsonBody['id']) ? (int)$jsonBody['id'] : 0;
        $approveAll = !empty($jsonBody['approveAll']);

        if ($approveAll) {
            $rows = $pdo->query("SELECT * FROM tms_pending_approvals WHERE status = 'PENDING' ORDER BY id ASC")->fetchAll();
        } else {
            $st = $pdo->prepare("SELECT * FROM tms_pending_approvals WHERE id = ? AND status = 'PENDING' LIMIT 1");
            $st->execute([$id]);
            $row = $st->fetch();
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
        $id = isset($jsonBody['id']) ? (int)$jsonBody['id'] : 0;
        $rejectAll = !empty($jsonBody['rejectAll']);
        if ($rejectAll) {
            $pdo->exec("UPDATE tms_pending_approvals SET status = 'REJECTED', reviewed_at = NOW() WHERE status = 'PENDING'");
        } elseif ($id > 0) {
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
            $fieldsToQueue['soContPo'] = strtoupper(trim($jsonBody['soCont']));
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
        $actorName = $actorRole === 'VENDOR' ? ($user['vendorName'] ?? 'Vendor') : trim(($user['plate'] ?? '') . ' - ' . ($user['driverName'] ?? ''));
        $vendorName = strtoupper(trim($user['vendorName'] ?? ''));
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
        $vendorName = strtoupper(trim($user['vendorName'] ?? ''));
        $fieldsToQueue = ['soXePo' => $soXe];
        if ($vendorName !== '') {
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
