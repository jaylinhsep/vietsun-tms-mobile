<?php
/**
 * VIETSUN VENDOR & DRIVER HUB - API GATEWAY (/api/index.php)
 * Quản lý xác thực, đồng bộ Google Sheet Đội xe Vendor, đồng bộ 2 chiều Vietsun TMS,
 * quản lý Vendor, Tài xế/Số xe, Báo cáo doanh thu & Chi phí phát sinh.
 */
error_reporting(0);
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

// Tự động nâng cấp cấu trúc bảng để hỗ trợ:
// - Số xe (assigned_plate) là UNIQUE
// - Nhiều xe có thể dùng chung SĐT tài xế
// - Lưu thêm CCCD, GPLX
function ensure_schema($pdo) {
    $flagFile = sys_get_temp_dir() . '/vietsun_schema_v2.flag';
    if (file_exists($flagFile)) return;

    try { $pdo->exec("ALTER TABLE `drivers` DROP INDEX `phone`"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` MODIFY `phone` VARCHAR(30) NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` MODIFY `pin_code` VARCHAR(100) NOT NULL DEFAULT '123456'"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `cccd` VARCHAR(100) NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` ADD COLUMN `gplx` VARCHAR(100) NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `drivers` ADD UNIQUE INDEX `idx_assigned_plate` (`assigned_plate`)"); } catch (Exception $e) {}

    // Xóa các dòng demo cũ bị lệch mã (HLP -> HOANGLONGPHAT)
    try {
        $pdo->exec("DELETE FROM `vendors` WHERE `vendor_code` = 'HLP'");
        $pdo->exec("UPDATE `vendors` SET `password_hash` = '123456' WHERE `password_hash` IN ('hlp@123', 'tranco@123', 'vietsun@123', 'vp@123')");
    } catch (Exception $e) {}

    @file_put_contents($flagFile, time());
}

// Đồng bộ trực tiếp từ Google Sheet Đội xe Vendor & Đội xe Vietsun
function sync_from_google_sheet($pdo) {
    global $SHEET_CSV_URL;
    ensure_schema($pdo);

    // Dữ liệu đội xe Vietsun mặc định
    $vietsunList = [
        ["VIETSUN","50H91309","HUỲNH THANH TÙNG","0356601380","079097000590","790152274557"],
        ["VIETSUN","50H67772","NGUYỄN VĂN PHƯƠNG","0931249725","075082015319","740113002170"],
        ["VIETSUN","50H91554","NGUYỄN VĂN TUẤN","0378857982","044082006183","790105007335"],
        ["VIETSUN","50E52975","NGUYỄN TRỌNG TRƯỜNG","0974533635","038085016388","790152206084"],
        ["VIETSUN","50H10121","PHAN VĂN HUYNH","0987437351","037089000509","790144799134"],
        ["VIETSUN","50H76073","NGUYỄN VĂN THANH","0914210965","075092005970","790219058674"],
        ["VIETSUN","50H10152","QUẢN VĂN NGỌC","0975075027","017084008865","310162850107"],
        ["VIETSUN","50H05689","NGÔ MINH TRỌNG","0985955580","094086003131","740114002180"],
        ["VIETSUN","50H10035","TRẦN TRÍ TÀI","0919480367","075089026187","750130023874"],
        ["VIETSUN","50H05455","NGUYỄN THÁI CƠ","0986192693","035074010129","790146894437"],
        ["VIETSUN","50H68309","LÊ VĂN NGOAN","0795884070","094085000613","790133745151"],
        ["VIETSUN","50E14550","LÊ HOÀNG ÂN","0966464917","080093000650","790159130760"],
        ["VIETSUN","50E14009","NGUYỄN MINH TRIẾT","0364334382","087201009730","790206185040"],
        ["VIETSUN","50E53597","NGUYỄN VĂN TÀI","0335946252","077089005508","790110015510"],
        ["VIETSUN","50E52920","ĐỖ MINH PHỤNG","0967799542","080093006093","790160094374"],
        ["VIETSUN","50H10017","NGUYỄN VƯƠNG QUỐC","0936736236","080081000387","790156088701"],
        ["VIETSUN","50H68333","HOÀNG VĂN LỰC","0902856068","038085020371","790159210491"],
        ["VIETSUN","50H10011","PHAN HOÀNG HÙNG","0707231297","079201021731","790204030139"],
        ["VIETSUN","50E15217","HUỲNH THANH TÂM","0937373731","080074000416","790966004092"],
        ["VIETSUN","50H76816","PHAN VĂN TÚ","0904334484","079078002279","790152151977"],
        ["VIETSUN","50H90600","DƯƠNG HIẾU HÒA","0796449644","079074022039","790009228687"],
        ["VIETSUN","51D03966","TRANG MINH HẬU","0979577801","075082009103","750053003841"],
        ["VIETSUN","50H68200","NGUYỄN VIẾT XUÂN","0979483434","042080010369","790106009000"],
        ["VIETSUN","50H90144","PHẠM VĂN BÌNH","0909092471","075091024017","790171923030"],
        ["VIETSUN","50H10208","NGUYỄN ĐỨC TRIỆU","0938025734","030062009674","750100001394"],
        ["VIETSUN","50H91424","ĐỖ THÀNH HƯNG","0916597944","079077005704","790135410947"],
        ["VIETSUN","50E48301","NGUYỄN HỮU TƯỜNG","0933943550","084083014543","790101006837"],
        ["VIETSUN","50H05535","LÂM TỬ TY","0961191339","054088010647","540111812711"],
        ["VIETSUN","50E14112","NGUYỄN VĂN XUÂN","0964337309","074078009227","740044000054"],
        ["VIETSUN","50H10224","NGUYỄN VĂN HẬU","0938234301","093098001120","790163115562"],
        ["VIETSUN","50H76406","ĐỖ VĂN TUẤN","0906534154","038082023809","740086003914"],
        ["VIETSUN","50E54493","NGUYỄN TUẤN ANH","0979222556","075090016892","790137778023"],
        ["VIETSUN","50H91213","HUỲNH VĂN SỬ","0908727855","091085002627","790102029798"],
        ["VIETSUN","50H91917","TRẦN DUY BÌNH","0973607075","040086020171","750087003158"],
        ["VIETSUN","50E54092","PHAN THẾ MINH SƠN","0889243552","046083015082","790107001117"],
        ["VIETSUN","50E52617","HUỲNH VĂN TẤN ĐẠT","0974891088","084096005671","840164002355"],
        ["VIETSUN","50H05658","HOÀNG HỮU LONG","0971772771","042090011523","310176002813"],
        ["VIETSUN","50E14029","HÀ VĂN LUẬT","0961535776","038081001711","790168165517"],
        ["VIETSUN","50H05808","NGUYỄN THÀNH TRUNG","0973029550","040081035050","420098000382"],
        ["VIETSUN","50H76226","ĐOÀN TÍN THÀNH","0903379977","079089006263","790115230930"],
        ["VIETSUN","50H76364","LÊ MINH HIỂN","0902484393","094085015886","310150041834"],
        ["VIETSUN","50E54447","LÊ TRỌNG NHŨ","0985468603","052088013362","790148929197"],
        ["VIETSUN","50H76548","HUỲNH VĂN CHÍ NGUYỆN","0327348324","084093004347","790185182027"],
        ["VIETSUN","50H67574","HUỲNH VĂN TÂM","0934044395","087084012933","790135004317"],
        ["VIETSUN","50E13972","TRƯƠNG HOÀNG TÂM","0933436783","084083011298","790154124087"],
        ["VIETSUN","50H76100","NGUYỄN VĂN TUẤN","0932636166","038087010115","790111019706"],
        ["VIETSUN","50H75037","HÀ VĂN HUỲNH","0962595828","038090000713","790159083060"],
        ["VIETSUN","50H91136","NGUYỄN VĂN LÂM","0907451526","075084012309","740103000597"],
        ["VIETSUN","50H91077","NGUYỄN VĂN VINH","0939534848","084078005598","790133026546"],
        ["VIETSUN","50H75631","PHẠM VĂN HOÀI","0987130446","040094035935","790158168344"],
        ["VIETSUN","50H76822","ĐOÀN VĂN KHÔI","07074042920","092068002539","740100001386"],
        ["VIETSUN","50H91025","LÊ CHÍ BÉ","0949325326","096085012314","960104002204"],
        ["VIETSUN","50H91065","LÊ VĂN LỌ","0931781902","084087008097","790120002234"],
        ["VIETSUN","50H90495","HÀ VĂN NAM","0962156417","038087003353","790135394702"],
        ["VIETSUN","50H76011","BÙI QUỐC BẢO","0984771750","052086008837","520098005953"],
        ["VIETSUN","50E53813","BÙI THẾ PHONG","0919412472","070090005753","700140007614"],
        ["VIETSUN","50H75942","HÀ VĂN QUÂN","0968858255","038079021380","790156178864"],
        ["VIETSUN","50H10039","TRẦN QUỐC THANH","0344275975","046086012096","460070000841"],
        ["VIETSUN","50H67809","ĐẶNG VĂN GIANG","0986779164","019064000138","740109000223"],
        ["VIETSUN","50H10106","ĐOÀN TRUNG ĐIỆP","0966469601","051093007367","790144963131"],
        ["VIETSUN","50H75270","TRẦN VĂN CHIẾN","0908491943","051086015636","790116256113"],
        ["VIETSUN","50H05806","NGUYỄN VĂN HÀ","0976404108","042072019464","740138003479"],
        ["VIETSUN","50H10056","LÊ BẢO QUỐC","0946070660","080084015762","800072000197"],
        ["VIETSUN","50H05545","NGUYỄN VĂN QUANG","0919960266","040086034549","400138010216"],
        ["VIETSUN","50E52957","NGUYỄN VĂN CHIẾN","0933893216","084080010693","790118015806"],
        ["VIETSUN","50H75167","NGUYỄN VĂN KHOA","0937773854","084088003267","790121231718"],
        ["VIETSUN","50H76863","PHẠM TUẤN KHANH","0832385658","093094003991","930172002271"],
        ["VIETSUN","50H90045","HỒ NGỌC THANH","0908067972","079072041145","790109010227"],
        ["VIETSUN","50E53620","TRƯƠNG NGỌC ĐÔNG","0707200832","086094004600","790140976232"],
        ["VIETSUN","50H76757","NGUYỄN VĂN GIÀU","0943998978","096079008895","790101005351"],
        ["VIETSUN","50H91113","NGUYỄN KIM TƯ","0933989655","093091008061","790161177742"],
        ["VIETSUN","50H90944","ĐỖ HỒNG TUYẾN","0918569137","044073008800","740107007134"],
        ["VIETSUN","50H91272","VŨ QUANG TUÂN","0937823360","038078006397","790106000982"],
        ["VIETSUN","50H10031","NGUYỄN VĨNH PHƯỚC","0939454501","087078013673","790064018256"],
        ["VIETSUN","50H68711","NGUYỄN VƯƠNG QUỐC","0936736236","",""]
    ];

    $rows = $vietsunList;

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
    return $item;
}

function fetch_single_cycle($cycleDate, $forceRefresh = false) {
    global $REMOTE_BASE, $COOKIE_FILE;
    $safeCycle = preg_replace('/[^0-9\-]/', '', $cycleDate);
    $cacheFile = sys_get_temp_dir() . '/vietsun_cycle_' . $safeCycle . '.json';
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

function fetch_vietsun_shipments($targetDate = null, $forceRefresh = false) {
    $baseTs = $targetDate ? strtotime($targetDate) : time();
    if (!$baseTs) $baseTs = time();

    $cycles = array_unique([
        get_billing_cycle_param(date('Y-m-d', $baseTs - 10 * 86400)),
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
    // Sắp xếp mới nhất lên đầu
    krsort($merged, SORT_NUMERIC);
    return array_values($merged);
}

function push_vietsun_crud($params) {
    global $REMOTE_BASE, $COOKIE_FILE;
    if (!file_exists($COOKIE_FILE)) {
        vietsun_login();
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
    @unlink(sys_get_temp_dir() . '/vietsun_hub_cache.json');
    return $res;
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
    // 3. PROXY CHO TRANG ĐIỀU HÀNH CHÍNH & DASHBOARD QUẢN TRỊ (/admin)
    // ========================================================================
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

    // API Đồng bộ Google Sheet ngay lập tức
    if ($route === 'admin/sync-sheet') {
        $res = sync_from_google_sheet($pdo);
        echo json_encode(['ok' => true, 'message' => 'Đã đồng bộ từ Google Sheet thành công!', 'stats' => $res, 'synced' => $res]);
        exit;
    }

    // API Lấy toàn bộ danh sách Vendor & Tài xế cho Dashboard Quản trị
    if ($route === 'admin/overview') {
        $vendors = $pdo->query("
            SELECT v.*, v.phone AS contact_phone,
                   (SELECT COUNT(*) FROM drivers d WHERE d.vendor_id = v.id) AS driver_count,
                   (SELECT COUNT(*) FROM drivers d WHERE d.vendor_id = v.id) AS truck_count
            FROM vendors v
            ORDER BY v.vendor_name ASC
        ")->fetchAll();

        $drivers = $pdo->query("
            SELECT d.*, d.driver_name AS full_name, v.vendor_name, v.vendor_code
            FROM drivers d
            JOIN vendors v ON d.vendor_id = v.id
            ORDER BY v.vendor_name ASC, d.assigned_plate ASC
        ")->fetchAll();

        echo json_encode([
            'ok'      => true,
            'vendors' => $vendors,
            'drivers' => $drivers
        ]);
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
        $targetDate = $_GET['date'] ?? null;
        $forceRefresh = !empty($_GET['refresh']);
        $all = fetch_vietsun_shipments($targetDate, $forceRefresh);
        $myTrips = [];
        foreach ($all as $item) {
            $itemPlate = to_clean_code($item['soXePo'] ?? '');
            if ($itemPlate !== '' && ($itemPlate === $plate || strpos($itemPlate, $plate) !== false)) {
                $myTrips[] = $item;
            }
        }
        echo json_encode([
            'ok'         => true,
            'today'      => date('Y-m-d'),
            'trips'      => $myTrips,
            'driverInfo' => [
                'name'   => $user['driverName'] ?? '',
                'plate'  => $user['plate'] ?? '',
                'vendor' => $user['vendorName'] ?? ''
            ]
        ]);
        exit;
    }

    // Tài xế cập nhật Cont/Seal/Cảng đến -> Đẩy về Vietsun TMS
    if ($route === 'driver/update-trip') {
        $idPo = $jsonBody['idPo'] ?? '';
        if (!$idPo) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Thiếu mã chuyến idPo']);
            exit;
        }
        if (isset($jsonBody['soCont'])) {
            push_vietsun_crud(['cv' => 4, 'loai' => 4, 'iddt' => $idPo, 'cot' => 1, 'thongtin' => trim($jsonBody['soCont'])]);
        }
        if (isset($jsonBody['soSeal'])) {
            push_vietsun_crud(['cv' => 4, 'loai' => 5, 'iddt' => $idPo, 'cot' => 1, 'thongtin' => trim($jsonBody['soSeal'])]);
        }
        if (isset($jsonBody['portTime']) && trim($jsonBody['portTime']) !== '') {
            push_vietsun_crud(['cv' => 4, 'loai' => 6, 'iddt' => $idPo, 'cot' => 1, 'thongtin' => trim($jsonBody['portTime'])]);
        }
        if (isset($jsonBody['cangDen']) && trim($jsonBody['cangDen']) !== '') {
            push_vietsun_crud(['cv' => 4, 'loai' => 30, 'iddt' => $idPo, 'cot' => 1, 'thongtin' => trim($jsonBody['cangDen'])]);
        }
        echo json_encode(['ok' => true, 'message' => 'Đã cập nhật và đẩy ngược về Vietsun TMS!']);
        exit;
    }

    // Tài xế báo chi phí phát sinh
    if ($route === 'driver/add-expense') {
        $idPo = $jsonBody['idPo'] ?? '';
        $expenseType = $jsonBody['expenseType'] ?? '';
        $amount = $jsonBody['amount'] ?? 0;
        $note = $jsonBody['note'] ?? '';
        $stmt = $pdo->prepare('INSERT INTO trip_expenses (booking_id_po, driver_id, expense_type, amount, note) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$idPo, $user['driverId'] ?? null, $expenseType, $amount, $note]);
        echo json_encode(['ok' => true, 'message' => 'Đã lưu chi phí phát sinh!']);
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

        $all = fetch_vietsun_shipments($targetDate, $forceRefresh);
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
        echo json_encode([
            'ok'          => true,
            'today'       => date('Y-m-d'),
            'total'       => count($vendorTrips),
            'fleetPlates' => array_values($fleetMap),
            'trips'       => $vendorTrips
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
