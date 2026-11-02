<?php
/**
 * VIETSUN VENDOR & DRIVER HUB - API GATEWAY (/api/index.php)
 * 1. Chuyển tiếp vào Node.js Middleware (127.0.0.1:3005) nếu Node đang chạy.
 * 2. Tự động xử lý trực tiếp bằng PHP + MySQL + Vietsun cURL nếu Node chưa phản hồi.
 */
error_reporting(0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Xác định route từ ?route=... hoặc từ REQUEST_URI
$route = isset($_GET['route']) ? trim($_GET['route'], '/') : '';
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

// Lấy Authorization header
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

// ============================================================================
// BƯỚC 1: THỬ CHUYỂN TIẾP VÀO NODE.JS (127.0.0.1:3005)
// ============================================================================
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$nodeUrl = 'http://127.0.0.1:3005/api/' . $route . ($queryString ? '?' . $queryString : '');

$ch = curl_init($nodeUrl);
$forwardHeaders = ['Content-Type: application/json'];
if ($authHeader) {
    $forwardHeaders[] = 'Authorization: ' . $authHeader;
}
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => $_SERVER['REQUEST_METHOD'],
    CURLOPT_HTTPHEADER     => $forwardHeaders,
    CURLOPT_TIMEOUT        => 4,
    CURLOPT_CONNECTTIMEOUT => 1
]);
if (!empty($rawBody)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rawBody);
}
$nodeResp = curl_exec($ch);
$nodeHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($nodeResp !== false && $nodeHttpCode >= 200 && $nodeHttpCode < 500 && $nodeHttpCode !== 404) {
    http_response_code($nodeHttpCode);
    echo $nodeResp;
    exit;
}

// ============================================================================
// BƯỚC 2: ENGINE DỰ PHÒNG TRỰC TIẾP (PHP + MYSQL + VIETSUN TMS CURL)
// ============================================================================
$DB_HOST = '127.0.0.1';
$DB_USER = 'vietsun_vendor';
$DB_PASS = 'huyhoang106';
$DB_NAME = 'vietsun_vendor';
$JWT_SECRET = 'vsho_tms_secret_key_2026_@vietsun';
$REMOTE_BASE = 'https://vsho.vietsunlogistics.com';
$COOKIE_FILE = sys_get_temp_dir() . '/vietsun_tms_cookie.txt';

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
        'password' => '14',
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

function fetch_vietsun_shipments() {
    global $REMOTE_BASE, $COOKIE_FILE;
    $cacheFile = sys_get_temp_dir() . '/vietsun_hub_cache.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 120)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && count($cached) > 0) return $cached;
    }

    if (!file_exists($COOKIE_FILE)) {
        vietsun_login();
    }
    $ngay = date('Y-m-d');
    $url = "$REMOTE_BASE/return_json.php?ngay=" . urlencode($ngay);
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
    // Invalidate cache so next fetch reflects update
    @unlink(sys_get_temp_dir() . '/vietsun_hub_cache.json');
    return $res;
}

try {
    // 1. ĐĂNG NHẬP TÀI XẾ
    if ($route === 'auth/driver-login') {
        $phone = trim($jsonBody['phone'] ?? '');
        $pin = trim($jsonBody['pin'] ?? '');
        if (!$phone || !$pin) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Số điện thoại và Mã PIN']);
            exit;
        }
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT d.*, v.vendor_name FROM drivers d JOIN vendors v ON d.vendor_id = v.id WHERE d.phone = ? AND d.pin_code = ? AND d.is_active = 1 LIMIT 1');
        $stmt->execute([$phone, $pin]);
        $driver = $stmt->fetch();
        if (!$driver) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Số điện thoại hoặc Mã PIN không đúng']);
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
                'vendor' => $driver['vendor_name']
            ]
        ]);
        exit;
    }

    // 2. ĐĂNG NHẬP CHỦ VENDOR
    if ($route === 'auth/vendor-login') {
        $code = strtoupper(trim($jsonBody['code'] ?? ''));
        $password = trim($jsonBody['password'] ?? '');
        if (!$code || !$password) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Vui lòng nhập Mã nhà xe và Mật khẩu']);
            exit;
        }
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT * FROM vendors WHERE vendor_code = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$code]);
        $vendor = $stmt->fetch();
        if (!$vendor || $vendor['password_hash'] !== $password) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Sai Mã nhà xe hoặc Mật khẩu']);
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

    // 2.5 PROXY CHO TRANG CHỦ ĐIỀU HÀNH (return_json & index-crud)
    if ($route === 'return_json') {
        $data = fetch_vietsun_shipments();
        echo json_encode($data);
        exit;
    }
    if ($route === 'index-crud' || $route === 'index_crud') {
        $res = push_vietsun_crud($_POST ?: $jsonBody);
        echo $res;
        exit;
    }

    // Các route bên dưới cần xác thực Token
    $user = verify_token($authHeader);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Phiên đăng nhập không hợp lệ']);
        exit;
    }

    // 3. LẤY CHUYẾN CỦA TÀI XẾ
    if ($route === 'driver/my-trips') {
        $plate = strtoupper(preg_replace('/\s+/', '', $user['plate'] ?? ''));
        $all = fetch_vietsun_shipments();
        $myTrips = [];
        foreach ($all as $item) {
            $itemPlate = strtoupper(preg_replace('/\s+/', '', $item['soXePo'] ?? ''));
            if ($itemPlate !== '' && strpos($itemPlate, $plate) !== false) {
                $myTrips[] = $item;
            }
        }
        echo json_encode([
            'ok'         => true,
            'trips'      => $myTrips,
            'driverInfo' => [
                'name'   => $user['driverName'] ?? '',
                'plate'  => $user['plate'] ?? '',
                'vendor' => $user['vendorName'] ?? ''
            ]
        ]);
        exit;
    }

    // 4. TÀI XẾ CẬP NHẬT CONT/SEAL/CẢNG ĐẾN -> ĐẨY VỀ VIETSUN TMS
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

    // 5. TÀI XẾ BÁO CHI PHÍ PHÁT SINH
    if ($route === 'driver/add-expense') {
        $idPo = $jsonBody['idPo'] ?? '';
        $expenseType = $jsonBody['expenseType'] ?? '';
        $amount = $jsonBody['amount'] ?? 0;
        $note = $jsonBody['note'] ?? '';
        $pdo = get_db();
        $stmt = $pdo->prepare('INSERT INTO trip_expenses (booking_id_po, driver_id, expense_type, amount, note) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$idPo, $user['driverId'] ?? null, $expenseType, $amount, $note]);
        echo json_encode(['ok' => true, 'message' => 'Đã lưu chi phí phát sinh!']);
        exit;
    }

    // 6. VENDOR LẤY DANH SÁCH CHUYẾN ĐỘI XE
    if ($route === 'vendor/trips') {
        $vName = strtoupper(trim($user['vendorName'] ?? ''));
        $all = fetch_vietsun_shipments();
        $vendorTrips = [];
        foreach ($all as $item) {
            $itemVendor = strtoupper(trim($item['donViVcPo'] ?? ''));
            if ($itemVendor !== '' && strpos($itemVendor, $vName) !== false) {
                $vendorTrips[] = $item;
            }
        }
        echo json_encode([
            'ok'    => true,
            'total' => count($vendorTrips),
            'trips' => $vendorTrips
        ]);
        exit;
    }

    // 7. VENDOR BÁO CÁO DOANH THU & CÔNG NỢ
    if ($route === 'vendor/revenue-report') {
        $pdo = get_db();
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

    // 8. VENDOR ĐIỀU CHỈNH CƯỚC DEBIT
    if ($route === 'vendor/adjust-rate') {
        $idPo = $jsonBody['idPo'] ?? '';
        $adjustedRate = $jsonBody['adjustedRate'] ?? 0;
        $reason = $jsonBody['reason'] ?? '';
        $pdo = get_db();
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
