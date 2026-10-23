<?php
/**
 * VIETSUN TMS MOBILE - STANDALONE HOSTING EDITION
 * Hoáº¡t Ä‘á»™ng Ä‘á»™c láº­p 100% trÃªn Domain / Hosting riÃªng cá»§a báº¡n.
 * Káº¿t ná»‘i qua Server-side cURL tá»›i https://vsho.vietsunlogistics.com (KhÃ´ng xung Ä‘á»™t code gá»‘c, khÃ´ng lá»—i CORS)
 */
error_reporting(0);
$REMOTE_BASE = 'https://vsho.vietsunlogistics.com';
$COOKIE_FILE = sys_get_temp_dir() . '/vietsun_tms_cookie.txt';

function tms_remote_login($username, $password, $remoteBase, $cookieFile) {
    $ch = curl_init("$remoteBase/0login.php");
    $postData = http_build_query([
        'username' => $username,
        'password' => $password,
        'login'    => 'ÄÄ‚NG NHáº¬P'
    ]);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Linux; Android 14) VietsunTMSMobile/1.0'
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    $userString = 'trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.';
    if (preg_match('/id="userString"\s+value="([^"]+)"/', $html, $matches)) {
        $userString = $matches[1];
    }
    return $userString;
}

if (isset($_GET['api'])) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $api = $_GET['api'];

    // 1. API ÄÄƒng nháº­p tÃ i khoáº£n TMS
    if ($api === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);
        $user = isset($body['username']) ? $body['username'] : 'trucking14';
        $pass = isset($body['password']) ? $body['password'] : '1';
        $uStr = tms_remote_login($user, $pass, $REMOTE_BASE, $COOKIE_FILE);
        echo json_encode([
            'ok'         => true,
            'username'   => $user,
            'userString' => $uStr
        ]);
        exit;
    }

    // 2. API Truy xuáº¥t dá»¯ liá»‡u xuáº¥t nháº­p (return_json.php)
    if ($api === 'return_json') {
        header('Content-Type: application/json; charset=utf-8');
        $ngay = isset($_GET['ngay']) ? urlencode($_GET['ngay']) : date('Y-m-d');
        $ch = curl_init("$REMOTE_BASE/return_json.php?ngay=$ngay");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_ENCODING       => ''
        ]);
        $json = curl_exec($ch);
        curl_close($ch);
        echo $json ? trim($json) : '[]';
        exit;
    }

    // 3. API Cáº­p nháº­t thÃ´ng tin Cont, Seal, Sá»‘ xe, ÄVVT, NgÃ y giao (index-crud.php)
    if ($api === 'index_crud' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: text/html; charset=utf-8');
        if (!file_exists($COOKIE_FILE)) {
            tms_remote_login('trucking14', '1', $REMOTE_BASE, $COOKIE_FILE);
        }
        $rawPost = file_get_contents('php://input');
        $ch = curl_init("$REMOTE_BASE/index-crud.php");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $rawPost,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR      => $COOKIE_FILE,
            CURLOPT_COOKIEFILE     => $COOKIE_FILE,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8']
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        echo $resp;
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
  <meta name="theme-color" content="#1e40af" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-title" content="Vietsun TMS" />
  <meta name="apple-mobile-web-app-status-bar-style" content="default" />
  <meta name="mobile-web-app-capable" content="yes" />
  <title>Vietsun TMS Mobile</title>
  <link rel="manifest" href="manifest.json" />
  <link rel="icon" type="image/svg+xml" href="icon.svg" />
  <link rel="apple-touch-icon" href="icon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #2563eb;
      --primary-dark: #1e40af;
      --primary-light: #eff6ff;
      --secondary: #0284c7;
      --secondary-light: #e0f2fe;
      --success: #16a34a;
      --success-light: #dcfce7;
      --warning: #d97706;
      --warning-light: #fef3c7;
      --danger: #dc2626;
      --danger-light: #fee2e2;
      --bg: #f1f5f9;
      --card: #ffffff;
      --text: #0f172a;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --radius: 14px;
      --shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
      --safe-bottom: env(safe-area-inset-bottom, 12px);
    }

    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--bg);
      color: var(--text);
      font-size: 14px;
      line-height: 1.45;
      padding-bottom: calc(70px + var(--safe-bottom));
      overflow-x: hidden;
    }

    /* TOP APP BAR */
    .app-header {
      position: sticky;
      top: 0;
      z-index: 100;
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
      color: #fff;
      padding: 10px 14px 12px;
      box-shadow: 0 4px 14px rgba(30, 58, 138, 0.22);
    }

    .header-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 10px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .brand-logo {
      width: 36px;
      height: 36px;
      background: #fff;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: var(--primary);
      font-size: 14px;
      letter-spacing: -0.5px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      flex-shrink: 0;
    }

    .brand-title {
      font-weight: 800;
      font-size: 16px;
      letter-spacing: 0.2px;
      line-height: 1.15;
    }

    .brand-sub {
      font-size: 11px;
      opacity: 0.88;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #4ade80;
      display: inline-block;
      box-shadow: 0 0 6px #4ade80;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .icon-btn {
      background: rgba(255, 255, 255, 0.16);
      border: 1px solid rgba(255, 255, 255, 0.22);
      color: #fff;
      border-radius: 10px;
      padding: 7px 10px;
      font-size: 12px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      cursor: pointer;
      transition: all 0.15s;
    }

    .icon-btn:active {
      transform: scale(0.95);
      background: rgba(255, 255, 255, 0.28);
    }

    /* SEARCH BAR */
    .search-row {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .search-box {
      position: relative;
      flex: 1;
    }

    .search-box input {
      width: 100%;
      padding: 10px 34px 10px 36px;
      border-radius: 11px;
      border: none;
      background: #ffffff;
      color: var(--text);
      font-size: 14px;
      font-weight: 500;
      outline: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .search-box input::placeholder {
      color: #94a3b8;
      font-weight: 400;
    }

    .search-icon {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      font-size: 15px;
      pointer-events: none;
    }

    .clear-search {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      background: #e2e8f0;
      border: none;
      color: #475569;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      font-size: 12px;
      display: none;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .filter-toggle-btn {
      background: #fff;
      color: var(--primary);
      border: none;
      border-radius: 11px;
      padding: 10px 12px;
      font-weight: 700;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 5px;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      flex-shrink: 0;
      position: relative;
    }

    .filter-badge {
      background: var(--primary);
      color: #fff;
      font-size: 10px;
      border-radius: 99px;
      padding: 1px 5px;
      min-width: 16px;
      text-align: center;
    }

    /* FILTER PANEL */
    .filter-section {
      background: #fff;
      border-bottom: 1px solid var(--border);
      padding: 10px 12px;
      box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }

    .quick-tabs {
      display: flex;
      gap: 6px;
      overflow-x: auto;
      scrollbar-width: none;
      padding-bottom: 4px;
    }
    .quick-tabs::-webkit-scrollbar { display: none; }

    .chip {
      padding: 6px 12px;
      border-radius: 99px;
      border: 1px solid var(--border);
      background: #f8fafc;
      color: #334155;
      font-size: 12.5px;
      font-weight: 600;
      white-space: nowrap;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.15s;
      user-select: none;
    }

    .chip .count {
      background: rgba(15, 23, 42, 0.08);
      padding: 1px 6px;
      border-radius: 99px;
      font-size: 11px;
      font-weight: 700;
    }

    .chip.active {
      background: var(--primary);
      color: #fff;
      border-color: var(--primary);
      box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }

    .chip.active .count {
      background: rgba(255, 255, 255, 0.25);
      color: #fff;
    }

    .chip.chip-blue.active {
      background: #0f766e;
      border-color: #0f766e;
      box-shadow: 0 2px 6px rgba(15, 118, 110, 0.25);
    }

    .advanced-filters {
      display: none;
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px dashed var(--border);
    }

    .advanced-filters.open {
      display: block;
    }

    .filter-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-bottom: 8px;
    }

    .filter-field label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      margin-bottom: 3px;
      text-transform: uppercase;
    }

    .filter-field select,
    .filter-field input {
      width: 100%;
      padding: 8px 10px;
      border: 1px solid #cbd5e1;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
      background: #f8fafc;
      outline: none;
    }

    .filter-field select:focus,
    .filter-field input:focus {
      border-color: var(--primary);
      background: #fff;
    }

    /* SUMMARY BAR */
    .summary-strip {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
      padding: 10px 12px 4px;
    }

    .stat-card {
      background: #fff;
      border-radius: 11px;
      padding: 8px 6px;
      text-align: center;
      border: 1px solid var(--border);
      box-shadow: 0 1px 3px rgba(0,0,0,0.03);
      cursor: pointer;
      transition: all 0.15s;
    }

    .stat-card.active-stat {
      border-color: var(--primary);
      background: #eff6ff;
    }

    .stat-val {
      font-size: 16px;
      font-weight: 800;
      color: var(--text);
      line-height: 1.1;
    }

    .stat-lbl {
      font-size: 10.5px;
      font-weight: 600;
      color: var(--text-muted);
      margin-top: 2px;
    }

    /* OBJECT / CHU HANG BAR */
    .chuhang-bar {
      padding: 6px 12px 4px;
    }

    .chuhang-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      margin-bottom: 4px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* SHIPMENT LIST */
    .list-container {
      padding: 6px 12px 24px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .po-card {
      background: var(--card);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      padding: 12px;
      transition: transform 0.12s, box-shadow 0.12s;
      position: relative;
      overflow: hidden;
    }

    .po-card:active {
      transform: scale(0.992);
    }

    .po-card.type-X {
      border-left: 4px solid #0284c7;
    }

    .po-card.type-N {
      border-left: 4px solid #16a34a;
    }

    .po-card.no-truck {
      border-right: 3px solid #f59e0b;
    }

    .card-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 6px;
      margin-bottom: 8px;
    }

    .badges-left {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 5px;
    }

    .badge {
      font-size: 11px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      gap: 3px;
    }

    .badge-xuat {
      background: #e0f2fe;
      color: #0369a1;
    }

    .badge-nhap {
      background: #dcfce7;
      color: #15803d;
    }

    .badge-chuhang {
      background: #f1f5f9;
      color: #334155;
      border: 1px solid #cbd5e1;
    }

    .badge-size {
      background: #fef3c7;
      color: #b45309;
    }

    .po-id {
      font-size: 11.5px;
      font-weight: 700;
      color: #64748b;
      font-family: monospace;
    }

    /* CONT & TRUCK HIGHLIGHT ROW */
    .cont-truck-box {
      display: grid;
      grid-template-columns: 1.15fr 1fr;
      gap: 8px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 8px 10px;
      margin-bottom: 8px;
    }

    .cont-side, .truck-side {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .truck-side {
      border-left: 1px dashed #cbd5e1;
      padding-left: 8px;
    }

    .field-mini-label {
      font-size: 10px;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .cont-number {
      font-size: 15px;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: 0.3px;
      font-family: 'Inter', monospace;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .cont-number.empty-val {
      color: #dc2626;
      font-style: italic;
      font-size: 13px;
      font-weight: 600;
    }

    .iso-badge {
      font-size: 9.5px;
      padding: 1px 4px;
      border-radius: 4px;
      font-weight: 700;
    }
    .iso-ok { background: #dcfce7; color: #15803d; }
    .iso-warn { background: #fef3c7; color: #b45309; }

    .seal-number {
      font-size: 12px;
      color: #475569;
      font-weight: 600;
      margin-top: 1px;
    }

    .truck-number {
      font-size: 14.5px;
      font-weight: 800;
      color: #0369a1;
    }

    .truck-number.unassigned {
      color: #d97706;
      font-size: 12.5px;
      font-weight: 700;
    }

    .vendor-name {
      font-size: 11.5px;
      font-weight: 700;
      color: #334155;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-top: 1px;
    }

    /* CUSTOMER & ROUTE INFO */
    .customer-row {
      margin-bottom: 6px;
    }

    .customer-name {
      font-weight: 700;
      font-size: 14px;
      color: #0f172a;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
    }

    .warehouse-addr {
      font-size: 12.5px;
      color: #475569;
      margin-top: 2px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* FOOTER META OF CARD */
    .card-meta {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
      padding-top: 7px;
      border-top: 1px solid #f1f5f9;
      font-size: 12px;
      color: #475569;
    }

    .meta-item {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-weight: 600;
    }

    .date-pill {
      padding: 2px 8px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 11.5px;
      background: #f1f5f9;
      color: #0f172a;
    }

    .date-pill.date-unset {
      background: #fee2e2;
      color: #b91c1c;
    }

    .date-pill.date-today {
      background: #dcfce7;
      color: #15803d;
    }

    .card-actions {
      display: flex;
      gap: 6px;
      margin-top: 9px;
    }

    .btn-card {
      flex: 1;
      padding: 8px 10px;
      border-radius: 9px;
      font-size: 12.5px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      transition: all 0.15s;
    }

    .btn-edit {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
    }

    .btn-zalo {
      background: #f1f5f9;
      color: #0f172a;
      border: 1px solid #cbd5e1;
      flex: 0 0 auto;
    }

    .btn-call {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
      flex: 0 0 auto;
      text-decoration: none;
    }

    /* BOTTOM SHEET MODAL FOR EDITING */
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(2px);
      z-index: 200;
      display: none;
      align-items: flex-end;
      justify-content: center;
    }

    .modal-backdrop.open {
      display: flex;
    }

    .bottom-sheet {
      background: #fff;
      width: 100%;
      max-width: 640px;
      max-height: 92vh;
      border-radius: 20px 20px 0 0;
      display: flex;
      flex-direction: column;
      box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.2);
      animation: slideUp 0.22s ease-out;
    }

    @keyframes slideUp {
      from { transform: translateY(100%); }
      to { transform: translateY(0); }
    }

    .sheet-header {
      padding: 12px 16px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f8fafc;
      border-radius: 20px 20px 0 0;
    }

    .sheet-title {
      font-weight: 800;
      font-size: 15px;
      color: var(--text);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .sheet-close {
      background: #e2e8f0;
      border: none;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      font-size: 16px;
      font-weight: 700;
      color: #475569;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .sheet-body {
      padding: 14px 16px;
      overflow-y: auto;
      flex: 1;
    }

    .info-banner {
      background: #f1f5f9;
      border-radius: 10px;
      padding: 10px 12px;
      margin-bottom: 14px;
      border-left: 4px solid var(--secondary);
      font-size: 12.5px;
    }

    .info-banner-title {
      font-weight: 800;
      font-size: 14px;
      color: #0f172a;
      margin-bottom: 3px;
    }

    .form-section-title {
      font-size: 11.5px;
      font-weight: 800;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.4px;
      margin: 12px 0 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .form-group {
      margin-bottom: 10px;
      position: relative;
    }

    /* CUSTOM AUTOCOMPLETE DROPDOWN FOR IOS & ANDROID */
    .ac-dropdown {
      position: absolute;
      top: calc(100% + 2px);
      left: 0;
      right: 0;
      background: #ffffff;
      border: 1.5px solid var(--primary);
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(15, 23, 42, 0.22);
      max-height: 210px;
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
      z-index: 999;
      display: none;
    }

    .ac-dropdown.open {
      display: block;
    }

    .ac-header {
      padding: 6px 10px;
      font-size: 10.5px;
      font-weight: 700;
      color: #64748b;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
    }

    .ac-item {
      padding: 10px 12px;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      cursor: pointer;
      font-size: 13.5px;
      font-weight: 700;
      color: #0f172a;
      background: #fff;
    }

    .ac-item:last-child {
      border-bottom: none;
    }

    .ac-item:active, .ac-item.same-vendor {
      background: #eff6ff;
    }

    .ac-sub {
      font-size: 11px;
      font-weight: 600;
      color: #0284c7;
      background: #e0f2fe;
      padding: 2px 7px;
      border-radius: 6px;
      white-space: nowrap;
      max-width: 48%;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .ac-sub.match-vendor {
      background: #dcfce7;
      color: #15803d;
    }

    .form-group.full {
      grid-column: 1 / -1;
    }

    .form-group label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 12px;
      font-weight: 700;
      color: #334155;
      margin-bottom: 4px;
    }

    .form-control {
      width: 100%;
      padding: 10px 12px;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      color: #0f172a;
      background: #fff;
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s;
      font-family: inherit;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
    }

    .form-control.saving {
      border-color: #f59e0b;
      background: #fffbeb;
    }

    .form-control.saved {
      border-color: #16a34a;
      background: #f0fdf4;
    }

    .form-control.warn-iso {
      border-color: #f59e0b;
      background: #fef3c7;
    }

    .quick-date-btns {
      display: flex;
      gap: 6px;
      margin-top: 5px;
      flex-wrap: wrap;
    }

    .mini-btn {
      font-size: 11px;
      font-weight: 700;
      padding: 4px 8px;
      border-radius: 6px;
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      color: #334155;
      cursor: pointer;
    }

    .mini-btn:active {
      background: #e2e8f0;
    }

    .sheet-footer {
      padding: 12px 16px calc(12px + var(--safe-bottom));
      border-top: 1px solid var(--border);
      background: #fff;
      display: flex;
      gap: 10px;
    }

    .btn-primary-lg {
      flex: 1;
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
      color: #fff;
      border: none;
      border-radius: 12px;
      padding: 13px 16px;
      font-size: 14.5px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-secondary-lg {
      background: #f1f5f9;
      color: #334155;
      border: 1px solid #cbd5e1;
      border-radius: 12px;
      padding: 13px 14px;
      font-size: 13.5px;
      font-weight: 700;
      cursor: pointer;
    }

    /* TOAST NOTIFICATION */
    .toast-container {
      position: fixed;
      top: 16px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 6px;
      width: 92%;
      max-width: 400px;
      pointer-events: none;
    }

    .toast {
      background: #0f172a;
      color: #fff;
      padding: 11px 14px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 600;
      box-shadow: 0 6px 20px rgba(0,0,0,0.25);
      display: flex;
      align-items: center;
      gap: 8px;
      animation: fadeInDown 0.2s ease;
    }

    .toast.success { border-left: 4px solid #22c55e; }
    .toast.warning { border-left: 4px solid #f59e0b; }
    .toast.error { border-left: 4px solid #ef4444; }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-12px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* BOTTOM NAVIGATION */
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #ffffff;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: space-around;
      padding: 6px 8px calc(6px + var(--safe-bottom));
      z-index: 90;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.04);
    }

    .nav-item {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 2px;
      color: #64748b;
      font-size: 11px;
      font-weight: 600;
      background: none;
      border: none;
      padding: 4px 0;
      cursor: pointer;
    }

    .nav-item.active {
      color: var(--primary);
      font-weight: 800;
    }

    .nav-icon {
      font-size: 18px;
      line-height: 1;
    }

    /* LOADING & EMPTY STATES */
    .state-box {
      text-align: center;
      padding: 40px 20px;
      color: var(--text-muted);
    }

    .spinner {
      width: 36px;
      height: 36px;
      border: 3.5px solid #e2e8f0;
      border-top-color: var(--primary);
      border-radius: 50%;
      animation: spin 0.75s linear infinite;
      margin: 0 auto 12px;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .load-more-btn {
      width: 100%;
      padding: 12px;
      border-radius: 12px;
      border: 1px solid #cbd5e1;
      background: #fff;
      font-weight: 700;
      color: #334155;
      cursor: pointer;
      margin-top: 6px;
    }

    @media (min-width: 768px) {
      .list-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        max-width: 1200px;
        margin: 0 auto;
      }
      .summary-strip, .filter-section, .chuhang-bar {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
      }
    }
  </style>
</head>
<body>

  <div class="toast-container" id="toastContainer"></div>

  <!-- HEADER -->
  <header class="app-header">
    <div class="header-top">
      <div class="brand">
        <div class="brand-logo">VS</div>
        <div>
          <div class="brand-title">VIETSUN TMS</div>
          <div class="brand-sub">
            <span class="status-dot" id="connDot"></span>
            <span id="userLabel">TK: trucking14</span>
            <span>â€¢</span>
            <span id="syncTimeLabel">Äang táº£i...</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <button class="icon-btn" id="btnInstallPwa" style="display:none;" onclick="installPWA()">
          ðŸ“² CÃ i App
        </button>
        <button class="icon-btn" onclick="openAccountModal()" title="Cáº¥u hÃ¬nh tÃ i khoáº£n">
          ðŸ‘¤ TK
        </button>
        <button class="icon-btn" onclick="refreshData(true)" title="Táº£i láº¡i dá»¯ liá»‡u má»›i nháº¥t">
          ðŸ”„ LÃ m má»›i
        </button>
      </div>
    </div>

    <div class="search-row">
      <div class="search-box">
        <span class="search-icon">ðŸ”</span>
        <input
          type="search"
          id="searchInput"
          placeholder="TÃ¬m Sá»‘ Cont, Biá»ƒn sá»‘ xe, KhÃ¡ch hÃ ng, Kho, Seal..."
          autocomplete="off"
          oninput="onSearchInput()"
        />
        <button class="clear-search" id="btnClearSearch" onclick="clearSearch()">âœ•</button>
      </div>
      <button class="filter-toggle-btn" onclick="toggleAdvancedFilters()">
        <span>âš¡ Lá»c</span>
        <span class="filter-badge" id="activeFilterCount">1</span>
      </button>
    </div>
  </header>

  <!-- QUICK DATE & FILTER SECTION -->
  <section class="filter-section">
    <!-- Quick Date Chips -->
    <div class="quick-tabs" id="quickDateTabs">
      <button class="chip active" data-date-mode="today" onclick="setQuickDate('today')">ðŸ“… HÃ´m nay</button>
      <button class="chip" data-date-mode="tomorrow" onclick="setQuickDate('tomorrow')">â­ï¸ NgÃ y mai</button>
      <button class="chip" data-date-mode="yesterday" onclick="setQuickDate('yesterday')">â®ï¸ HÃ´m qua</button>
      <button class="chip" data-date-mode="3days" onclick="setQuickDate('3days')">ðŸ“† Â±3 ngÃ y</button>
      <button class="chip" data-date-mode="unset" onclick="setQuickDate('unset')">âš ï¸ ChÆ°a xáº¿p ngÃ y</button>
      <button class="chip" data-date-mode="all" onclick="setQuickDate('all')">ðŸ—‚ï¸ Táº¥t cáº£ (30 ngÃ y)</button>
    </div>

    <!-- Collapsible Advanced Filters -->
    <div class="advanced-filters" id="advancedFilters">
      <div class="filter-grid">
        <div class="filter-field">
          <label>Lá»c theo loáº¡i ngÃ y</label>
          <select id="filterDateType" onchange="applyFilters()">
            <option value="ngayTraHang">NgÃ y ÄÃ³ng / Giao hÃ ng</option>
            <option value="ngayTauChayPo">NgÃ y TÃ u cháº¡y / cáº­p</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Tráº¡ng thÃ¡i Ä‘iá»u xe</label>
          <select id="filterTruckStatus" onchange="applyFilters()">
            <option value="ALL">-- Táº¥t cáº£ tráº¡ng thÃ¡i --</option>
            <option value="NO_TRUCK">âš ï¸ ChÆ°a cÃ³ sá»‘ xe</option>
            <option value="HAS_TRUCK">âœ… ÄÃ£ gÃ¡n sá»‘ xe</option>
            <option value="NO_CONT">âš ï¸ ChÆ°a cÃ³ sá»‘ Cont</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Tá»« ngÃ y</label>
          <input type="date" id="filterFromDate" onchange="onCustomDateChange()" />
        </div>
        <div class="filter-field">
          <label>Äáº¿n ngÃ y</label>
          <input type="date" id="filterToDate" onchange="onCustomDateChange()" />
        </div>
        <div class="filter-field">
          <label>ÄÆ¡n vá»‹ váº­n táº£i (Vendor)</label>
          <select id="filterVendor" onchange="applyFilters()">
            <option value="ALL">-- Táº¥t cáº£ ÄVVT --</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Tá»‰nh / Khu vá»±c</label>
          <select id="filterProvince" onchange="applyFilters()">
            <option value="ALL">-- Táº¥t cáº£ Tá»‰nh --</option>
          </select>
        </div>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-top:4px;">
        <span style="font-size:11.5px; color:#64748b;" id="serverDateInfo">Má»‘c dá»¯ liá»‡u API: HÃ´m nay</span>
        <div style="display:flex; gap:6px;">
          <button class="mini-btn" onclick="resetAllFilters()">Äáº·t láº¡i bá»™ lá»c</button>
          <button class="mini-btn" style="background:#0284c7;color:#fff;border-color:#0284c7;" onclick="promptApiDate()">Äá»•i ngÃ y gá»‘c API</button>
        </div>
      </div>
    </div>
  </section>

  <!-- OBJECT / CHU HANG CHIPS -->
  <div class="chuhang-bar">
    <div class="quick-tabs" id="chuHangTabs">
      <!-- Populated dynamically -->
    </div>
  </div>

  <!-- SUMMARY STATS -->
  <div class="summary-strip">
    <div class="stat-card" id="statAll" onclick="setTruckQuickFilter('ALL')">
      <div class="stat-val" id="countTotal">0</div>
      <div class="stat-lbl">Tá»•ng LÃ´/Cont</div>
    </div>
    <div class="stat-card" id="stat2040">
      <div class="stat-val" style="color:#0284c7;" id="count2040">0 / 0</div>
      <div class="stat-lbl">Cont 20' / 40'</div>
    </div>
    <div class="stat-card" id="statHasTruck" onclick="setTruckQuickFilter('HAS_TRUCK')">
      <div class="stat-val" style="color:#16a34a;" id="countHasTruck">0</div>
      <div class="stat-lbl">ÄÃ£ cÃ³ xe</div>
    </div>
    <div class="stat-card" id="statNoTruck" onclick="setTruckQuickFilter('NO_TRUCK')">
      <div class="stat-val" style="color:#d97706;" id="countNoTruck">0</div>
      <div class="stat-lbl">ChÆ°a cÃ³ xe</div>
    </div>
  </div>

  <!-- MAIN SHIPMENT LIST -->
  <main class="list-container" id="poListContainer">
    <div class="state-box">
      <div class="spinner"></div>
      <div style="font-weight:700; color:#334155;">Äang táº£i dá»¯ liá»‡u xuáº¥t nháº­p tá»« Vietsun TMS...</div>
      <div style="font-size:12px; margin-top:4px;">Káº¿t ná»‘i https://vsho.vietsunlogistics.com</div>
    </div>
  </main>

  <!-- BOTTOM NAVIGATION (XUAT / NHAP SWITCHER) -->
  <nav class="bottom-nav">
    <button class="nav-item active" data-xn="ALL" onclick="setXuatNhapFilter('ALL')">
      <span class="nav-icon">ðŸ“‹</span>
      <span>Táº¥t cáº£ (<span id="navCountAll">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="X" onclick="setXuatNhapFilter('X')">
      <span class="nav-icon">ðŸ“¤</span>
      <span>HÃ ng Xuáº¥t (<span id="navCountX">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="N" onclick="setXuatNhapFilter('N')">
      <span class="nav-icon">ðŸ“¥</span>
      <span>HÃ ng Nháº­p (<span id="navCountN">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="NO_TRUCK" onclick="setXuatNhapFilter('NO_TRUCK')">
      <span class="nav-icon">ðŸš›</span>
      <span>Chá» Ä‘iá»u xe (<span id="navCountWait">0</span>)</span>
    </button>
  </nav>

  <!-- EDIT MODAL (BOTTOM SHEET) -->
  <div class="modal-backdrop" id="editModal" onclick="if(event.target===this) closeEditModal()">
    <div class="bottom-sheet">
      <div class="sheet-header">
        <div class="sheet-title">
          <span id="modalBadgeType" class="badge badge-xuat">XUáº¤T</span>
          <span id="modalTitleText">Cáº­p nháº­t LÃ´ hÃ ng #210165</span>
        </div>
        <button class="sheet-close" onclick="closeEditModal()">âœ•</button>
      </div>

      <div class="sheet-body">
        <div class="info-banner">
          <div class="info-banner-title" id="modalCustomerName">Cty Dynaplast</div>
          <div id="modalRouteSub" style="color:#475569;">TÃ u: VIETSUN DYNAMIC (1236N) â€¢ Chá»§ hÃ ng: LOG</div>
        </div>

        <form id="editPoForm" onsubmit="saveAllChanges(event)">
          <input type="hidden" id="editIdPo" />
          <input type="hidden" id="editCodePo" />
          <input type="hidden" id="editXuatNhapPo" />

          <div class="form-section-title">
            <span>1. Äiá»u Váº­n & PhÆ°Æ¡ng Tiá»‡n</span>
            <span style="font-size:11px; color:#64748b; font-weight:600;">Tá»± Ä‘á»™ng lÆ°u khi Ä‘á»•i hoáº·c báº¥m LÆ°u</span>
          </div>

          <div class="form-grid">
            <!-- NGAY GIAO / DONG HANG -->
            <div class="form-group full">
              <label>
                <span id="lblNgayTraHang">ðŸ“… NgÃ y ÄÃ³ng / Giao HÃ ng</span>
                <span id="status_ngayTraHang" style="font-size:11px;"></span>
              </label>
              <input
                type="date"
                class="form-control"
                id="inp_ngayTraHang"
                onchange="onFieldChanged('ngayTraHang')"
              />
              <div class="quick-date-btns">
                <button type="button" class="mini-btn" onclick="setModalDate(0)">HÃ´m nay</button>
                <button type="button" class="mini-btn" onclick="setModalDate(1)">NgÃ y mai</button>
                <button type="button" class="mini-btn" onclick="setModalDate(2)">+2 ngÃ y</button>
                <button type="button" class="mini-btn" style="color:#dc2626;" onclick="setModalDate(null)">ChÆ°a xáº¿p ngÃ y</button>
              </div>
            </div>

            <!-- SO CONT -->
            <div class="form-group">
              <label>
                <span>ðŸ“¦ Sá»‘ Container</span>
                <span id="isoCheckBadge" class="iso-badge"></span>
              </label>
              <input
                type="text"
                class="form-control"
                id="inp_soContPo"
                placeholder="VD: VSGU2240757"
                style="text-transform:uppercase; font-family:monospace;"
                oninput="onContInput()"
                onchange="onFieldChanged('soContPo')"
              />
            </div>

            <!-- SO SEAL -->
            <div class="form-group">
              <label>
                <span>ðŸ”’ Sá»‘ Seal (ChÃ¬)</span>
                <span id="status_soSealPo" style="font-size:11px;"></span>
              </label>
              <input
                type="text"
                class="form-control"
                id="inp_soSealPo"
                placeholder="VD: 683274"
                onchange="onFieldChanged('soSealPo')"
              />
            </div>

            <!-- SO XE VAN TAI -->
            <div class="form-group">
              <label>
                <span>ðŸš› Sá»‘ Xe Váº­n Táº£i</span>
                <span id="status_soXePo" style="font-size:11px;"></span>
              </label>
              <input
                type="text"
                class="form-control"
                id="inp_soXePo"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="characters"
                spellcheck="false"
                placeholder="GÃµ biá»ƒn sá»‘ (VD: 61H...)"
                style="text-transform:uppercase;"
                onfocus="showTruckSuggestions()"
                oninput="onTruckInput()"
                onblur="onTruckBlur()"
              />
              <div class="ac-dropdown" id="ac_soXePo"></div>
            </div>

            <!-- DON VI VAN TAI -->
            <div class="form-group">
              <label>
                <span>ðŸ¢ ÄÆ¡n Vá»‹ Váº­n Táº£i</span>
                <span id="status_donViVcPo" style="font-size:11px;"></span>
              </label>
              <input
                type="text"
                class="form-control"
                id="inp_donViVcPo"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="characters"
                spellcheck="false"
                placeholder="Chá»n hoáº·c gÃµ Vendor..."
                style="text-transform:uppercase;"
                onfocus="showVendorSuggestions()"
                oninput="showVendorSuggestions()"
                onblur="onVendorBlur()"
              />
              <div class="ac-dropdown" id="ac_donViVcPo"></div>
            </div>

            <!-- VIETSUN DRIVER INFO BOX (AUTO-SHOWN WHEN VIETSUN TRUCK SELECTED) -->
            <div class="form-group full" id="driverInfoBox" style="display:none; margin-bottom:4px;">
              <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:8px 12px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
                <div style="font-size:12.5px; color:#166534;">
                  <div style="font-weight:800;" id="drvNameText">ðŸ‘¨â€âœˆï¸ TÃ i xáº¿: --</div>
                  <div style="font-size:11.5px; margin-top:2px;" id="drvSubText">ðŸ“ž SÄT: --</div>
                </div>
                <a href="#" id="drvCallBtn" class="btn-card btn-call" style="padding:6px 10px; font-size:12px;">ðŸ“ž Gá»i TX</a>
              </div>
            </div>
          </div>

          <div class="form-section-title">
            <span>2. BÃ£i / Cáº£ng & Káº¿t Há»£p</span>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label><span>ðŸ—ï¸ NÆ¡i láº¥y cont / rá»—ng</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_noiLayContPo"
                placeholder="VD: VS, KH 20', KH 40'..."
                onchange="onFieldChanged('noiLayContPo')"
              />
            </div>

            <div class="form-group">
              <label><span>âš“ Cáº£ng Ä‘i / Háº¡ cont</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_cangDiPo"
                placeholder="VD: Báº¾N NGHÃ‰"
                onchange="onFieldChanged('cangDiPo')"
              />
            </div>

            <div class="form-group">
              <label><span>ðŸ”„ HÃ¬nh thá»©c káº¿t há»£p</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_hinhThucKHopPo"
                list="dlKetHop"
                placeholder="VD: 80%, Káº¿t há»£p, Háº¡ rá»—ng"
                onchange="onFieldChanged('hinhThucKHopPo')"
              />
            </div>

            <div class="form-group">
              <label>
                <span>â±ï¸ Thá»i gian háº¡ cont</span>
                <button type="button" class="mini-btn" style="padding:1px 5px;font-size:10px;" onclick="setPortTimeNow()">Giá» hiá»‡n táº¡i</button>
              </label>
              <input
                type="text"
                class="form-control"
                id="inp_portTimePo"
                placeholder="DD/MM/YYYY HH:mm"
                onchange="onFieldChanged('portTimePo')"
              />
            </div>
          </div>

          <div class="form-section-title">
            <span>3. Kho HÃ ng, Äá»‹a Chá»‰ & LiÃªn Há»‡</span>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label><span>ðŸ­ Kho Ä‘Ã³ng / tráº£ hÃ ng</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_khoNhanHangPo"
                onchange="onFieldChanged('khoNhanHangPo')"
              />
            </div>

            <div class="form-group">
              <label><span>ðŸ“ Tá»‰nh / ThÃ nh phá»‘</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_tinhNhanPo"
                list="dlTinh"
                onchange="onFieldChanged('tinhNhanPo')"
              />
            </div>

            <div class="form-group full">
              <label><span>ðŸ—ºï¸ Äá»‹a chá»‰ chi tiáº¿t</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_diaChiNhanPo"
                onchange="onFieldChanged('diaChiNhanPo')"
              />
            </div>

            <div class="form-group full">
              <label><span>ðŸ“ž NgÆ°á»i liÃªn há»‡ & SÄT</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_lienHeNhanPo"
                onchange="onFieldChanged('lienHeNhanPo')"
              />
            </div>
          </div>
        </form>
      </div>

      <div class="sheet-footer">
        <button type="button" class="btn-secondary-lg" onclick="copyCurrentPoToZalo(2)" title="Copy THÃ”NG TIN GIAO HÃ€NG (kÃ¨m TÃ i xáº¿ & SÄT)">
          ðŸš› Copy Giao HÃ ng
        </button>
        <button type="button" class="btn-secondary-lg" onclick="copyCurrentPoToZalo(1)" title="Copy THÃ”NG BÃO HÃ€NG Äáº¾N / ÄÃ“NG HÃ€NG">
          ðŸ’¬ Copy TB
        </button>
        <button type="button" class="btn-primary-lg" id="btnSaveAll" onclick="saveAllChanges(event)">
          ðŸ’¾ LÆ°u TMS
        </button>
      </div>
    </div>
  </div>

  <!-- ACCOUNT / SETTINGS MODAL -->
  <div class="modal-backdrop" id="accountModal" onclick="if(event.target===this) closeAccountModal()">
    <div class="bottom-sheet">
      <div class="sheet-header">
        <div class="sheet-title">ðŸ” TÃ i Khoáº£n & Káº¿t Ná»‘i Vietsun TMS</div>
        <button class="sheet-close" onclick="closeAccountModal()">âœ•</button>
      </div>
      <div class="sheet-body">
        <div class="form-group">
          <label>TÃ i khoáº£n Ä‘Äƒng nháº­p (Username)</label>
          <input type="text" class="form-control" id="cfgUsername" value="trucking14" />
        </div>
        <div class="form-group">
          <label>Máº­t kháº©u (Password)</label>
          <input type="password" class="form-control" id="cfgPassword" value="1" />
        </div>
        <div class="form-group">
          <label>Chuá»—i phiÃªn lÃ m viá»‡c (userString)</label>
          <input type="text" class="form-control" id="cfgUserString" value="trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1." style="font-size:11.5px; font-family:monospace;" />
        </div>
        <div class="form-group">
          <label>NgÃ y má»‘c láº¥y dá»¯ liá»‡u API (return_json.php?ngay=)</label>
          <input type="date" class="form-control" id="cfgApiDate" />
        </div>
        <div class="info-banner" style="margin-top:12px;">
          <strong>HÆ°á»›ng dáº«n cÃ i App trÃªn Android:</strong><br/>
          Má»Ÿ trang nÃ y báº±ng trÃ¬nh duyá»‡t <strong>Chrome</strong> trÃªn Ä‘iá»‡n thoáº¡i Android âž” Báº¥m nÃºt <strong>"ðŸ“² CÃ i App"</strong> trÃªn thanh tiÃªu Ä‘á» (hoáº·c menu â‹® âž” <em>"ThÃªm vÃ o mÃ n hÃ¬nh chÃ­nh / CÃ i Ä‘áº·t á»©ng dá»¥ng"</em>).
        </div>
      </div>
      <div class="sheet-footer">
        <button type="button" class="btn-secondary-lg" onclick="closeAccountModal()">ÄÃ³ng</button>
        <button type="button" class="btn-primary-lg" onclick="saveAccountSettings()">âœ… ÄÄƒng nháº­p & Äá»“ng bá»™</button>
      </div>
    </div>
  </div>

  <!-- AUTOCOMPLETE DATALISTS -->
  <datalist id="dlSoXe"></datalist>
  <datalist id="dlVendor"></datalist>
  <datalist id="dlTinh"></datalist>
  <datalist id="dlKetHop">
    <option value="80%"></option>
    <option value="100%"></option>
    <option value="Káº¿t há»£p"></option>
    <option value="Háº¡ rá»—ng"></option>
  </datalist>

  <script>
    // =========================================================================
    // 1. MASTER DATA FROM VIETSUN TMS (EXTRACTED FROM LIVE SYSTEM)
    // =========================================================================
    const DEFAULT_XE_VIETSUN = [
      "50H05455","50H10011","50H10031","50H10035","50H10106","50H10121","50H90600","50H91424",
      "50H90753","50H68711","50H75292","50H68034","50H67574","50H91272","50E14112","50H05545",
      "50H76816","50H75167","50E14550","50H76863","50H91136","50H05535","50H68200","50H05658",
      "50H05689","50H05806","50H05808","50H10017","50H10039","50H10056","50H10152","50H10153",
      "50H10208","50H10224","50H10228","50H76822","50H76364","50H76757","50H91213","50H67772",
      "50H76073","50H76548","50H91309","50H90944","50H90045","50H68333","50H76011","50E14009",
      "50H76226","51D03966","50H67809","50H75631","50H91783","50H75270","50H76406","50H75037",
      "50H67914","50H90495","50H75942","50H76940","50E15217","50E13972","50H68309","50H76100",
      "50H68606","50H75819","50H91554","50E14029","50H91917","50H91025","50H91113","50H90144",
      "50H91065","50H91077","50E48301","50E54493","50E52975","50E53620","50E52957","50E53597",
      "50E54092","50E53813","50E52617","50E54447","50E52920","50F21358","50F21525","50F21764","50F23020"
    ];

    // Database TÃ i xáº¿, SÄT, CCCD, GPLX Äá»™i xe VIETSUN tá»« TMS
    const VIETSUN_DRIVER_DB = {
      "50H91309":{"cccd":"079097000590  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790152274557 NÆ¡i cáº¥p:Sá»ž GTVT","name":"HUá»²NH THANH TÃ™NG","phone":"0356601380"},
      "50H67772":{"cccd":"075082015319  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740113002170 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N PHÆ¯Æ NG","phone":"0931249725"},
      "50H91554":{"cccd":"044082006183  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790105007335 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"NGUYá»„N VÄ‚N TUáº¤N","phone":"0378857982"},
      "50E52975":{"cccd":"038085016388  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790152206084 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N TRá»ŒNG TRÆ¯á»œNG","phone":"0974533635"},
      "50H10121":{"cccd":"037089000509  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790144799134 NÆ¡i cáº¥p:Sá»ž GTVT","name":"PHAN VÄ‚N HUYNH","phone":"0987437351"},
      "50H76073":{"cccd":"075092005970  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790219058674 NÆ¡i cáº¥p:Bá»˜ CÃ”NG AN","name":"NGUYá»„N VÄ‚N THANH","phone":"0914210965"},
      "50H10152":{"cccd":"017084008865  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"310162850107 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"QUáº¢N VÄ‚N NGá»ŒC","phone":"0975075027"},
      "50H05689":{"cccd":"094086003131  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740114002180 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGÃ” MINH TRá»ŒNG","phone":"0985955580"},
      "50H10035":{"cccd":"075089026187  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"750130023874 NÆ¡i cáº¥p:Sá»Ÿ GTVT","name":"TRáº¦N TRÃ TÃ€I","phone":"0919480367"},
      "50H05455":{"cccd":"035074010129  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790146894437 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N THÃI CÆ ","phone":"0986192693"},
      "50H68309":{"cccd":"094085000613  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790133745151 NÆ¡i cáº¥p:Sá»ž GTVT","name":"LÃŠ VÄ‚N NGOAN","phone":"0795884070"},
      "50E14550":{"cccd":"080093000650  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790159130760 NÆ¡i cáº¥p:Sá»ž GTVT","name":"LÃŠ HOÃ€NG Ã‚N","phone":"0966464917"},
      "50E14009":{"cccd":"087201009730  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790206185040 NÆ¡i cáº¥p:bá»™ cÃ´ng an","name":"NGUYá»„N MINH TRIáº¾T","phone":"0364334382"},
      "50E53597":{"cccd":"077089005508  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790110015510 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N TÃ€I","phone":"0335946252"},
      "50E52920":{"cccd":"080093006093  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790160094374 NÆ¡i cáº¥p:Sá»ž GTVT","name":"Äá»– MINH PHá»¤NG","phone":"0967799542"},
      "50H10017":{"cccd":"080081000387  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790156088701 NÆ¡i cáº¥p:Bá»˜ GTVT","name":"NGUYá»„N VÆ¯Æ NG QUá»C","phone":"0936736236"},
      "50H68333":{"cccd":"038085020371  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790159210491 NÆ¡i cáº¥p:Sá»ž GTVT","name":"HOÃ€NG VÄ‚N Lá»°C","phone":"0902856068"},
      "50H10011":{"cccd":"079201021731  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790204030139 NÆ¡i cáº¥p:Sá»ž GTVT","name":"PHAN HOÃ€NG HÃ™NG","phone":"0707231297"},
      "50E15217":{"cccd":"080074000416  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790966004092 NÆ¡i cáº¥p:Sá»ž GTVT","name":"HUá»²NH THANH TÃ‚M","phone":"0937373731"},
      "50H76816":{"cccd":"079078002279  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790152151977 NÆ¡i cáº¥p:Sá»ž GTVT","name":"PHAN VÄ‚N TÃš","phone":"0904334484"},
      "50H90600":{"cccd":"079074022039  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790009228687 NÆ¡i cáº¥p:Sá»ž GTVT","name":"DÆ¯Æ NG HIáº¾U HÃ’A","phone":"0796449644"},
      "51D03966":{"cccd":"075082009103  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"750053003841 NÆ¡i cáº¥p:Cá»¥c Cáº£nh sÃ¡t giao thÃ´ng","name":"TRANG MINH Háº¬U","phone":"0979577801"},
      "50H68200":{"cccd":"042080010369  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790106009000 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VIáº¾T XUÃ‚N","phone":"0979483434"},
      "50H90144":{"cccd":"075091024017  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790171923030 NÆ¡i cáº¥p:BCA","name":"PHáº M VÄ‚N BÃŒNH","phone":"0909092471"},
      "50H10208":{"cccd":"030062009674  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"750100001394 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N Äá»¨C TRIá»†U","phone":"0938025734"},
      "50H91424":{"cccd":"079077005704  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790135410947 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"Äá»– THÃ€NH HÆ¯NG","phone":"0916597944"},
      "50E48301":{"cccd":"084083014543  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790101006837 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N Há»®U TÆ¯á»œNG","phone":"0933943550"},
      "50H05535":{"cccd":"054088010647  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"540111812711 NÆ¡i cáº¥p:Sá»ž GTVT","name":"LÃ‚M Tá»¬ TY","phone":"0961191339"},
      "50E14112":{"cccd":"074078009227  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740044000054 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"NGUYá»„N VÄ‚N XUÃ‚N","phone":"0964337309"},
      "50H10224":{"cccd":"093098001120  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790163115562 NÆ¡i cáº¥p:BCA","name":"NGUYá»„N VÄ‚N Háº¬U","phone":"0938234301"},
      "50H76406":{"cccd":"038082023809  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740086003914 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"Äá»– VÄ‚N TUáº¤N","phone":"0906534154"},
      "50E54493":{"cccd":"075090016892  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790137778023 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N TUáº¤N ANH","phone":"0979222556"},
      "50H91213":{"cccd":"091085002627  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790102029798 NÆ¡i cáº¥p:Sá»ž GTVT","name":"HUá»²NH VÄ‚N Sá»¬","phone":"0908727855"},
      "50H91917":{"cccd":"040086020171  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"750087003158 NÆ¡i cáº¥p:Sá»ž GTVT","name":"TRáº¦N DUY BÃŒNH","phone":"0973607075"},
      "50E54092":{"cccd":"046083015082  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790107001117 NÆ¡i cáº¥p:Sá»ž GTVT","name":"PHAN THáº¾ MINH SÆ N","phone":"0889243552"},
      "50E52617":{"cccd":"084096005671  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"840164002355 NÆ¡i cáº¥p:Sá»ž GTVT","name":"HUá»²NH VÄ‚N Táº¤N Äáº T","phone":"0974891088"},
      "50H05658":{"cccd":"042090011523  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"310176002813 NÆ¡i cáº¥p:Sá»ž GTVT","name":"HOÃ€NG Há»®U LONG","phone":"0971772771"},
      "50E14029":{"cccd":"038081001711  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790168165517 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"HÃ€ VÄ‚N LUáº¬T","phone":"0961535776"},
      "50H05808":{"cccd":"040081035050  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"420098000382 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N THÃ€NH TRUNG","phone":"0973029550"},
      "50H76226":{"cccd":"079089006263  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790115230930 NÆ¡i cáº¥p:Sá»ž GTVT","name":"ÄOÃ€N TÃN THÃ€NH","phone":"0903379977"},
      "50H76364":{"cccd":"094085015886  NÆ¡i cáº¥p: cuc QL HC & TTXH","gplx":"310150041834 NÆ¡i cáº¥p:Sá»Ÿ GTVT","name":"LÃŠ MINH HIá»‚N","phone":"0902484393"},
      "50E54447":{"cccd":"052088013362  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790148929197 NÆ¡i cáº¥p:Sá»ž GTVT","name":"LÃŠ TRá»ŒNG NHÅ¨","phone":"0985468603"},
      "50H76548":{"cccd":"084093004347  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790185182027 NÆ¡i cáº¥p:Sá»ž GTVT","name":"HUá»²NH VÄ‚N CHÃ NGUYá»†N","phone":"0327348324"},
      "50H67574":{"cccd":"087084012933  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790135004317 NÆ¡i cáº¥p:BCA","name":"HUá»²NH VÄ‚N TÃ‚M","phone":"0934044395"},
      "50E13972":{"cccd":"084083011298  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790154124087 NÆ¡i cáº¥p:Sá»ž GTVT","name":"TRÆ¯Æ NG HOÃ€NG TÃ‚M","phone":"0933436783"},
      "50H76100":{"cccd":"038087010115  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790111019706 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"NGUYá»„N VÄ‚N TUáº¤N","phone":"0932636166"},
      "50H75037":{"cccd":"038090000713  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790159083060 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"HÃ€ VÄ‚N HUá»²NH","phone":"0962595828"},
      "50H91136":{"cccd":"075084012309  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740103000597 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N LÃ‚M","phone":"0907451526"},
      "50H91077":{"cccd":"084078005598  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790133026546 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N VINH","phone":"0939534848"},
      "50H75631":{"cccd":"040094035935  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790158168344 NÆ¡i cáº¥p:Sá»ž GTVT","name":"PHáº M VÄ‚N HOÃ€I","phone":"0987130446"},
      "50H76822":{"cccd":"092068002539  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740100001386 NÆ¡i cáº¥p:Sá»ž GTVT","name":"ÄOÃ€N VÄ‚N KHÃ”I","phone":"07074042920"},
      "50H91025":{"cccd":"096085012314  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"960104002204 NÆ¡i cáº¥p:Sá»ž GTVT","name":"LÃŠ CHÃ BÃ‰","phone":"0949325326"},
      "50H91065":{"cccd":"084087008097  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790120002234 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"LÃŠ VÄ‚N Lá»Œ","phone":"0931781902"},
      "50H90495":{"cccd":"038087003353  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790135394702 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"HÃ€ VÄ‚N NAM","phone":"0962156417"},
      "50H76011":{"cccd":"052086008837  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"520098005953 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"BÃ™I QUá»C Báº¢O","phone":"0984771750"},
      "50E53813":{"cccd":"070090005753  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"700140007614 NÆ¡i cáº¥p:Sá»ž GTVT","name":"BÃ™I THáº¾ PHONG","phone":"0919412472"},
      "50H75942":{"cccd":"038079021380  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790156178864 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"HÃ€ VÄ‚N QUÃ‚N","phone":"0968858255"},
      "50H10039":{"cccd":"046086012096  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"460070000841 NÆ¡i cáº¥p:BCA","name":"TRáº¦N QUá»C THANH","phone":"0344275975"},
      "50H67809":{"cccd":"019064000138  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740109000223 NÆ¡i cáº¥p:Sá»ž GTVT","name":"Äáº¶NG VÄ‚N GIANG","phone":"0986779164"},
      "50H10106":{"cccd":"051093007367  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790144963131 NÆ¡i cáº¥p:Sá»ž GTVT","name":"ÄOÃ€N TRUNG ÄIá»†P","phone":"0966469601"},
      "50H75270":{"cccd":"051086015636  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790116256113 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"TRáº¦N VÄ‚N CHIáº¾N","phone":"0908491943"},
      "50H05806":{"cccd":"042072019464  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740138003479 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"NGUYá»„N VÄ‚N HÃ€","phone":"0976404108"},
      "50H10056":{"cccd":"080084015762  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"800072000197 NÆ¡i cáº¥p:BCA","name":"LÃŠ Báº¢O QUá»C","phone":"0946070660"},
      "50H05545":{"cccd":"040086034549  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"400138010216 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N QUANG","phone":"0919960266"},
      "50E52957":{"cccd":"084080010693  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790118015806 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N CHIáº¾N","phone":"0933893216"},
      "50H75167":{"cccd":"084088003267  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790121231718 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N KHOA","phone":"0937773854"},
      "50H76863":{"cccd":"093094003991  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"930172002271 NÆ¡i cáº¥p:Sá»ž GTVT","name":"PHáº M TUáº¤N KHANH","phone":"0832385658"},
      "50H90045":{"cccd":"079072041145  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790109010227 NÆ¡i cáº¥p:Sá»ž GTVT","name":"Há»’ NGá»ŒC THANH","phone":"0908067972"},
      "50E53620":{"cccd":"086094004600  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790140976232 NÆ¡i cáº¥p:Sá»ž GTVT","name":"TRÆ¯Æ NG NGá»ŒC ÄÃ”NG","phone":"0707200832"},
      "50H76757":{"cccd":"096079008895  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790101005351 NÆ¡i cáº¥p:Sá»ž GTVT","name":"NGUYá»„N VÄ‚N GIÃ€U","phone":"0943998978"},
      "50H91113":{"cccd":"093091008061  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790161177742 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"NGUYá»„N KIM TÆ¯","phone":"0933989655"},
      "50H90944":{"cccd":"044073008800  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"740107007134 NÆ¡i cáº¥p:Sá»ž GTVT","name":"Äá»– Há»’NG TUYáº¾N","phone":"0918569137"},
      "50H91272":{"cccd":"038078006397  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790106000982 NÆ¡i cáº¥p:sá»Ÿ GTVT","name":"VÅ¨ QUANG TUÃ‚N","phone":"0937823360"},
      "50H10031":{"cccd":"087078013673  NÆ¡i cáº¥p: Cá»¥c QLHC & TTXH","gplx":"790064018256 NÆ¡i cáº¥p:Sá»Ÿ GTVT","name":"NGUYá»„N VÄ¨NH PHÆ¯á»šC","phone":"0939454501"},
      "50H68711":{"cccd":"","gplx":"","name":"NGUYá»„N VÆ¯Æ NG QUá»C","phone":"0936736236"}
    };

    function getDriverForPlate(plate) {
      if (!plate) return null;
      const clean = plate.toUpperCase().replace(/[^A-Z0-9]/g, "");
      return VIETSUN_DRIVER_DB[clean] || null;
    }

    const DEFAULT_VENDORS = [
      "VIETSUN","TUAN HUNG","DOAN TIEN PHAT","TAN AN","NHAT SON","NGOC DUNG","DUONG NGUYEN",
      "MINH DUNG","THUAN PHONG","TMC","BAO LONG","TRUC ANH","TAN TIEN PHAT","PHUC TRUONG AN",
      "MR THANH","GIAO LENH","BAI","CY","VIET HUYNH GIA","HOANG HUY PHAT","MR TAM","VENDOR",
      "MR THANG","MINH LAM","HAMA","TAN NAM CHINH","LTL","CHL","KHOI PHAT","THAI NGUYEN LAM - TNL",
      "VUONG PHONG","DAI CAT","TANG THIEN PHU","NAM LONG","DELTA","THUAN THANH CONG","LE TRAN PHAT",
      "VIEN DONG","DONG HAI","ANH KHOI","PHAT DAT","ANH THU","VN LOGS","KHANH PHUONG","DONG TU",
      "REDSUN","THINH EXIM VN","HOANG LONG PHAT","GIA KHANG","QUOC ANH","MR.LINH","SONG BE",
      "NGOC SANG","LE LAM PHAT","NGOC TUAN ANH","PHUONG HA","HAI ANH","CAT KHANG","PHUONG DONG",
      "HUY HOANG","DINH AN","VAN XUAN","THANH HUONG","DBQ","THUAN THANH","TRANCO","V&A","HTX",
      "TUAN KHAI","VUONG PHAT","HOA PHAT LOGISTICS","LONG DAT","NHAT VIET 36","NHAT VIET 38",
      "NHAT VIET 18","AN THANH PHAT","PHUC AN","KIM LOC MAI","MAI NHAT ANH","DUY LINH PHAT",
      "NHAN BAO PHAT","ANH DUC","HIEU DAI PHAT","SONG MA NHAT LONG","HUYEN TRAM","TAN HOANG QUANG",
      "BAO NAM PHAT","THIEN PHUC PHAT","DAI LONG PHAT","DUC HAI","GIA BAO","DANG HUNG"
    ];

    const DEFAULT_PROVINCES = [
      "HO CHI MINH","BINH DUONG","DONG NAI","TAY NINH","LONG AN","BA RIA - VUNG TAU",
      "TIEN GIANG","BEN TRE","BINH PHUOC","BINH THUAN","LAM DONG","CAN THO","DONG THAP","VINH LONG"
    ];

    const CHECK_CONT_20 = ["CAIU2", "CAIU3", "CAIU6", "DRYU3", "GAOU2", "TRHU1", "TRHU3", "VSGU2"];
    const CHECK_CONT_40 = ["CAIU4", "DRYU9", "GAOU6", "TCNU1", "TCNU2", "TCNU3", "TCNU4", "VSGU4"];

    // =========================================================================
    // 2. APPLICATION STATE
    // =========================================================================
    const REMOTE_BASE = "https://vsho.vietsunlogistics.com";
    let allShipments = [];
    let filteredShipments = [];
    let renderLimit = 60;

    function safeGetStorage(key, fallback) {
      try {
        return localStorage.getItem(key) || fallback;
      } catch (_) {
        return fallback;
      }
    }

    let state = {
      username: safeGetStorage("vs_username", "trucking14"),
      password: safeGetStorage("vs_password", "1"),
      userString: safeGetStorage("vs_userString", "trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1."),
      apiDate: safeGetStorage("vs_apiDate", getTodayISO()),
      dateMode: "today",          // today | tomorrow | yesterday | 3days | unset | all | custom
      dateType: "ngayTraHang",    // ngayTraHang | ngayTauChayPo
      fromDate: getTodayISO(),
      toDate: getTodayISO(),
      xuatNhap: "ALL",            // ALL | X | N | NO_TRUCK
      chuHang: "ALL",             // ALL | VSV | LOG | VSHPG...
      truckStatus: "ALL",         // ALL | NO_TRUCK | HAS_TRUCK | NO_CONT
      vendor: "ALL",
      province: "ALL",
      searchQuery: ""
    };

    let currentEditingPo = null;
    let dirtyFields = new Set();

    // =========================================================================
    // 3. DATE & UTILITY HELPERS
    // =========================================================================
    function getTodayISO() {
      const now = new Date();
      const yyyy = now.getFullYear();
      const mm = String(now.getMonth() + 1).padStart(2, "0");
      const dd = String(now.getDate()).padStart(2, "0");
      return `${yyyy}-${mm}-${dd}`;
    }

    function addDaysISO(baseIso, days) {
      const [y, m, d] = baseIso.split("-").map(Number);
      const dt = new Date(y, m - 1, d);
      dt.setDate(dt.getDate() + days);
      const yyyy = dt.getFullYear();
      const mm = String(dt.getMonth() + 1).padStart(2, "0");
      const dd = String(dt.getDate()).padStart(2, "0");
      return `${yyyy}-${mm}-${dd}`;
    }

    function formatIsoToVN(isoStr) {
      if (!isoStr || isoStr === "2021-11-11" || isoStr === "1970-01-01") return "ChÆ°a xáº¿p ngÃ y";
      if (isoStr === "2020-01-01") return "CY";
      const parts = isoStr.split("-");
      if (parts.length !== 3) return isoStr;
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function formatIsoToPayloadDate(isoStr) {
      if (!isoStr || isoStr === "2021-11-11") return "";
      const parts = isoStr.split("-");
      if (parts.length !== 3) return isoStr;
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function decodeHtmlEntities(str) {
      if (!str) return "";
      return String(str)
        .replace(/&#34;/g, '"')
        .replace(/&#39;/g, "'")
        .replace(/&amp;/g, "&")
        .replace(/&lt;/g, "<")
        .replace(/&gt;/g, ">");
    }

    function showToast(msg, type = "success") {
      const container = document.getElementById("toastContainer");
      const el = document.createElement("div");
      el.className = `toast ${type}`;
      el.innerHTML = `<span>${type === "success" ? "âœ…" : type === "warning" ? "âš ï¸" : "âŒ"}</span><span>${msg}</span>`;
      container.appendChild(el);
      setTimeout(() => {
        el.style.opacity = "0";
        el.style.transition = "opacity 0.25s";
        setTimeout(() => el.remove(), 250);
      }, 2600);
    }

    // ISO 6346 Container Check Digit Validator (matches gimax.js iso6346CheckDigit)
    function validateContainerISO(contStr) {
      if (!contStr) return { valid: false, empty: true, message: "ChÆ°a nháº­p" };
      const csc = contStr.toUpperCase().replace(/[^A-Z0-9]+/g, "");
      if (csc.length !== 11) {
        return { valid: false, empty: false, message: `Äá»™ dÃ i ${csc.length}/11 kÃ½ tá»±` };
      }
      let sum = 0;
      for (let i = 0; i < 10; i++) {
        let n = csc.charCodeAt(i);
        n -= n < 58 ? 48 : 55;
        n += (n - 1) / 10;
        sum += Math.floor(n) << i;
      }
      const check = (sum % 11) % 10;
      const lastDigit = parseInt(csc.charAt(10), 10);
      if (check === lastDigit) {
        return { valid: true, empty: false, message: "Chuáº©n ISO âœ“" };
      } else {
        return { valid: false, empty: false, message: `Sai sá»‘ cuá»‘i (Ä‘Ãºng: ${check})` };
      }
    }

    // =========================================================================
    // 4. API COMMUNICATION (AUTO-DETECT LOCAL PROXY / SAME-ORIGIN / FALLBACK)
    // =========================================================================
    function isSameOriginVietsun() {
      return window.location.hostname.includes("vietsunlogistics.com");
    }

    async function fetchShipmentsFromApi(ngayParam) {
      const urlsToTry = [
        `?api=return_json&ngay=${encodeURIComponent(ngayParam)}`,
        `/api/return_json?ngay=${encodeURIComponent(ngayParam)}`,
        `${REMOTE_BASE}/return_json.php?ngay=${encodeURIComponent(ngayParam)}`
      ];

      let lastErr = null;
      for (const url of urlsToTry) {
        try {
          const resp = await fetch(url, { cache: "no-store" });
          if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
          const text = await resp.text();
          const cleaned = text.trim();
          const data = JSON.parse(cleaned);
          if (Array.isArray(data)) return data;
        } catch (err) {
          lastErr = err;
        }
      }
      throw lastErr || new Error("KhÃ´ng thá»ƒ káº¿t ná»‘i API");
    }

    async function postCrudUpdate(payloadObj) {
      const formBody = new URLSearchParams();
      for (const [k, v] of Object.entries(payloadObj)) {
        formBody.append(k, v);
      }

      const urlsToTry = [
        "?api=index_crud",
        "/api/index-crud",
        `${REMOTE_BASE}/index-crud.php`
      ];

      let lastErr = null;
      for (const url of urlsToTry) {
        try {
          const resp = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: formBody.toString()
          });
          if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
          return await resp.text();
        } catch (err) {
          lastErr = err;
        }
      }
      throw lastErr || new Error("KhÃ´ng thá»ƒ gá»­i lá»‡nh cáº­p nháº­t");
    }

    // =========================================================================
    // 5. DATA LOADING & FILTERING
    // =========================================================================
    async function refreshData(showNotification = false) {
      const syncLbl = document.getElementById("syncTimeLabel");
      syncLbl.textContent = "Äang Ä‘á»“ng bá»™...";
      try {
        const data = await fetchShipmentsFromApi(state.apiDate);
        allShipments = data.map(item => ({
          ...item,
          tenKhachPo: decodeHtmlEntities(item.tenKhachPo || ""),
          khoNhanHangPo: decodeHtmlEntities(item.khoNhanHangPo || ""),
          diaChiNhanPo: decodeHtmlEntities(item.diaChiNhanPo || ""),
          tinhNhanPo: decodeHtmlEntities(item.tinhNhanPo || ""),
          lienHeNhanPo: decodeHtmlEntities(item.lienHeNhanPo || ""),
          tenHangHoaPo: decodeHtmlEntities(item.tenHangHoaPo || ""),
          noiLayContPo: decodeHtmlEntities(item.noiLayContPo || ""),
          cangDiPo: decodeHtmlEntities(item.cangDiPo || ""),
          cangDenPo: decodeHtmlEntities(item.cangDenPo || ""),
          soContPo: (item.soContPo || "").trim(),
          soSealPo: (item.soSealPo || "").trim(),
          soXePo: (item.soXePo || "").trim(),
          donViVcPo: (item.donViVcPo || "").trim(),
          chuHangPo: (item.chuHangPo || "").trim()
        }));

        // Sort newest ID or date first
        allShipments.sort((a, b) => Number(b.idPo) - Number(a.idPo));

        // Safely cache only latest 500 records (~350KB) to avoid iOS Safari 5MB localStorage QuotaExceededError
        try {
          localStorage.setItem("vs_cached_shipments", JSON.stringify(allShipments.slice(0, 500)));
        } catch (storageErr) {
          console.warn("Storage quota warning (ignored):", storageErr);
        }

        const nowStr = new Date().toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
        syncLbl.textContent = `Cáº­p nháº­t ${nowStr} (${allShipments.length} lÃ´)`;
        document.getElementById("connDot").style.background = "#4ade80";

        populateDynamicDropdowns();
        applyFilters();
        if (showNotification) {
          showToast(`ÄÃ£ táº£i ${allShipments.length} lÃ´ hÃ ng tá»« há»‡ thá»‘ng!`, "success");
        }
      } catch (err) {
        console.error(err);
        document.getElementById("connDot").style.background = "#f59e0b";
        const cached = safeGetStorage("vs_cached_shipments", null);
        if (cached) {
          allShipments = JSON.parse(cached);
          syncLbl.textContent = `Báº£n lÆ°u táº¡m (${allShipments.length} lÃ´)`;
          populateDynamicDropdowns();
          applyFilters();
          showToast("Äang hiá»ƒn thá»‹ dá»¯ liá»‡u lÆ°u táº¡m trÃªn mÃ¡y", "warning");
        } else {
          syncLbl.textContent = "Lá»—i káº¿t ná»‘i";
          document.getElementById("poListContainer").innerHTML = `
            <div class="state-box">
              <div style="font-size:36px;margin-bottom:8px;">âš ï¸</div>
              <div style="font-weight:700;color:#0f172a;">KhÃ´ng thá»ƒ táº£i dá»¯ liá»‡u trá»±c tiáº¿p</div>
              <div style="font-size:12.5px;margin-top:6px;">HÃ£y cháº¡y file <b>Chay_App_TMS.bat</b> trÃªn mÃ¡y tÃ­nh hoáº·c Ä‘Æ°a file lÃªn host Vietsun Ä‘á»ƒ trÃ¡nh cháº·n CORS cá»§a trÃ¬nh duyá»‡t.</div>
              <button class="btn-primary-lg" style="margin:14px auto 0;max-width:220px;" onclick="refreshData(true)">Thá»­ láº¡i</button>
            </div>
          `;
        }
      }
    }

    let knownTruckMap = new Map(); // plate -> { plate, vendor, count }
    let knownVendorList = [...DEFAULT_VENDORS];

    function populateDynamicDropdowns() {
      // Populate Datalists & Custom Autocomplete Maps
      const xeSet = new Set(DEFAULT_XE_VIETSUN);
      const vendorSet = new Set(DEFAULT_VENDORS);
      const provSet = new Set(DEFAULT_PROVINCES);

      knownTruckMap.clear();
      DEFAULT_XE_VIETSUN.forEach(x => {
        if (x) knownTruckMap.set(x, { plate: x, vendor: "VIETSUN", count: 1 });
      });

      // Also load user's recently typed trucks from localStorage
      try {
        const recent = JSON.parse(localStorage.getItem("vs_recent_trucks") || "[]");
        recent.forEach(r => {
          if (r && r.plate) {
            knownTruckMap.set(r.plate, { plate: r.plate, vendor: r.vendor || "", count: 50 });
            xeSet.add(r.plate);
          }
        });
      } catch (_) {}

      allShipments.forEach(s => {
        const rawXe = (s.soXePo || "").toUpperCase().replace(/\s+/g, "").trim();
        const rawDv = (s.donViVcPo || "").toUpperCase().trim();
        if (rawXe) {
          xeSet.add(rawXe);
          const existing = knownTruckMap.get(rawXe);
          if (existing) {
            existing.count++;
            if (!existing.vendor && rawDv) existing.vendor = rawDv;
          } else {
            knownTruckMap.set(rawXe, { plate: rawXe, vendor: rawDv, count: 1 });
          }
        }
        if (rawDv) vendorSet.add(rawDv);
        if (s.tinhNhanPo && s.tinhNhanPo !== "THONG BAO SAU") provSet.add(s.tinhNhanPo.toUpperCase());
      });

      knownVendorList = Array.from(vendorSet).sort();

      document.getElementById("dlSoXe").innerHTML = Array.from(xeSet)
        .map(x => `<option value="${x}"></option>`).join("");
      document.getElementById("dlVendor").innerHTML = Array.from(vendorSet)
        .map(v => `<option value="${v}"></option>`).join("");
      document.getElementById("dlTinh").innerHTML = Array.from(provSet)
        .map(p => `<option value="${p}"></option>`).join("");

      // Populate Filter Selects
      const filterVendor = document.getElementById("filterVendor");
      const curV = state.vendor;
      filterVendor.innerHTML = `<option value="ALL">-- Táº¥t cáº£ ÄVVT --</option>` +
        Array.from(vendorSet).sort().map(v => `<option value="${v}">${v}</option>`).join("");
      filterVendor.value = curV;

      const filterProv = document.getElementById("filterProvince");
      const curP = state.province;
      filterProv.innerHTML = `<option value="ALL">-- Táº¥t cáº£ Tá»‰nh --</option>` +
        Array.from(provSet).sort().map(p => `<option value="${p}">${p}</option>`).join("");
      filterProv.value = curP;
    }

    function matchesDateFilter(item) {
      const dateVal = item[state.dateType] || "";
      if (state.dateMode === "all") return true;
      if (state.dateMode === "unset") {
        return !dateVal || dateVal === "2021-11-11" || dateVal === "1970-01-01";
      }
      if (!dateVal || dateVal === "2021-11-11" || dateVal === "1970-01-01") return false;
      return dateVal >= state.fromDate && dateVal <= state.toDate;
    }

    function applyFilters() {
      state.dateType = document.getElementById("filterDateType").value;
      state.truckStatus = document.getElementById("filterTruckStatus").value;
      state.vendor = document.getElementById("filterVendor").value;
      state.province = document.getElementById("filterProvince").value;

      // 1. Filter by Date & Search first so we can compute accurate counts for X/N & ChuHang
      const q = state.searchQuery.toLowerCase().trim();

      const baseDateAndSearch = allShipments.filter(item => {
        if (!matchesDateFilter(item)) return false;
        if (state.vendor !== "ALL" && (item.donViVcPo || "").toUpperCase() !== state.vendor) return false;
        if (state.province !== "ALL" && (item.tinhNhanPo || "").toUpperCase() !== state.province) return false;
        if (state.truckStatus === "NO_TRUCK" && item.soXePo) return false;
        if (state.truckStatus === "HAS_TRUCK" && !item.soXePo) return false;
        if (state.truckStatus === "NO_CONT" && item.soContPo) return false;

        if (q) {
          const hay = `${item.idPo} ${item.soContPo} ${item.soSealPo} ${item.soXePo} ${item.tenKhachPo} ${item.khoNhanHangPo} ${item.diaChiNhanPo} ${item.tinhNhanPo} ${item.donViVcPo} ${item.tauPo} ${item.chuyenPo} ${item.chuHangPo}`.toLowerCase();
          if (!hay.includes(q)) return false;
        }
        return true;
      });

      // Update Bottom Nav Counts
      const countAllXN = baseDateAndSearch.length;
      const countX = baseDateAndSearch.filter(i => i.xuatNhapPo === "X").length;
      const countN = baseDateAndSearch.filter(i => i.xuatNhapPo === "N").length;
      const countWait = baseDateAndSearch.filter(i => !i.soXePo).length;

      document.getElementById("navCountAll").textContent = countAllXN;
      document.getElementById("navCountX").textContent = countX;
      document.getElementById("navCountN").textContent = countN;
      document.getElementById("navCountWait").textContent = countWait;

      // 2. Filter by Xuat/Nhap
      const afterXN = baseDateAndSearch.filter(item => {
        if (state.xuatNhap === "X") return item.xuatNhapPo === "X";
        if (state.xuatNhap === "N") return item.xuatNhapPo === "N";
        if (state.xuatNhap === "NO_TRUCK") return !item.soXePo;
        return true;
      });

      // Build ChuHang (Äá»‘i tÆ°á»£ng) Chips with real-time counts
      renderChuHangChips(afterXN);

      // 3. Filter by ChuHang (Äá»‘i tÆ°á»£ng)
      filteredShipments = afterXN.filter(item => {
        if (state.chuHang === "ALL") return true;
        return (item.chuHangPo || "KHÃC") === state.chuHang;
      });

      // Update Summary Stats
      updateSummaryCards(filteredShipments);
      updateActiveFilterBadge();

      renderLimit = 60;
      renderShipmentList();
    }

    function renderChuHangChips(listBeforeChuHang) {
      const counts = {};
      listBeforeChuHang.forEach(item => {
        const ch = item.chuHangPo || "KHÃC";
        counts[ch] = (counts[ch] || 0) + 1;
      });

      const sortedKeys = Object.keys(counts).sort((a, b) => counts[b] - counts[a]);
      const container = document.getElementById("chuHangTabs");

      let html = `
        <button class="chip chip-blue ${state.chuHang === "ALL" ? "active" : ""}" onclick="setChuHangFilter('ALL')">
          ðŸ¢ Táº¥t cáº£ Ä‘á»‘i tÆ°á»£ng <span class="count">${listBeforeChuHang.length}</span>
        </button>
      `;

      sortedKeys.forEach(ch => {
        const isActive = state.chuHang === ch ? "active" : "";
        html += `
          <button class="chip chip-blue ${isActive}" onclick="setChuHangFilter('${ch}')">
            ${ch} <span class="count">${counts[ch]}</span>
          </button>
        `;
      });

      container.innerHTML = html;
    }

    function updateSummaryCards(list) {
      const total = list.length;
      let c20 = 0, c40 = 0, hasTruck = 0, noTruck = 0;

      list.forEach(item => {
        if (Number(item.cont20Po) > 0) c20 += Number(item.cont20Po);
        if (Number(item.cont40Po) > 0) c40 += Number(item.cont40Po);
        if (item.soXePo && item.soXePo.trim() !== "") hasTruck++;
        else noTruck++;
      });

      document.getElementById("countTotal").textContent = total;
      document.getElementById("count2040").textContent = `${c20} / ${c40}`;
      document.getElementById("countHasTruck").textContent = hasTruck;
      document.getElementById("countNoTruck").textContent = noTruck;

      document.getElementById("statAll").classList.toggle("active-stat", state.truckStatus === "ALL");
      document.getElementById("statHasTruck").classList.toggle("active-stat", state.truckStatus === "HAS_TRUCK");
      document.getElementById("statNoTruck").classList.toggle("active-stat", state.truckStatus === "NO_TRUCK");
    }

    function updateActiveFilterBadge() {
      let c = 0;
      if (state.dateMode !== "all") c++;
      if (state.xuatNhap !== "ALL") c++;
      if (state.chuHang !== "ALL") c++;
      if (state.truckStatus !== "ALL") c++;
      if (state.vendor !== "ALL") c++;
      if (state.province !== "ALL") c++;
      if (state.searchQuery) c++;
      document.getElementById("activeFilterCount").textContent = c;
    }

    // =========================================================================
    // 6. RENDER SHIPMENT CARDS
    // =========================================================================
    function renderShipmentList() {
      const container = document.getElementById("poListContainer");
      if (filteredShipments.length === 0) {
        container.innerHTML = `
          <div class="state-box">
            <div style="font-size:38px;margin-bottom:8px;">ðŸ“­</div>
            <div style="font-weight:700;color:#334155;font-size:15px;">KhÃ´ng cÃ³ lÃ´ hÃ ng nÃ o khá»›p bá»™ lá»c</div>
            <div style="font-size:12.5px;margin-top:4px;">Thá»­ chá»n "Táº¥t cáº£ (30 ngÃ y)" hoáº·c Ä‘á»•i Ä‘á»‘i tÆ°á»£ng/loáº¡i hÃ ng.</div>
            <button class="mini-btn" style="margin-top:12px;padding:8px 14px;" onclick="setQuickDate('all')">Xem táº¥t cáº£ 30 ngÃ y</button>
          </div>
        `;
        return;
      }

      const slice = filteredShipments.slice(0, renderLimit);
      const todayIso = getTodayISO();

      const cardsHtml = slice.map(item => {
        const isXuat = item.xuatNhapPo === "X";
        const typeBadge = isXuat
          ? `<span class="badge badge-xuat">ðŸ“¤ XUáº¤T</span>`
          : item.xuatNhapPo === "N"
          ? `<span class="badge badge-nhap">ðŸ“¥ NHáº¬P</span>`
          : `<span class="badge badge-chuhang">${item.xuatNhapPo}</span>`;

        const sizeStr = Number(item.cont40Po) > 0 ? "40'HC" : Number(item.cont20Po) > 0 ? "20'DC" : "";
        const sizeBadge = sizeStr ? `<span class="badge badge-size">${sizeStr}</span>` : "";

        // ISO Check on card
        let isoHtml = "";
        if (item.soContPo) {
          const check = validateContainerISO(item.soContPo);
          isoHtml = check.valid
            ? `<span class="iso-badge iso-ok" title="ÄÃºng chuáº©n ISO 6346">âœ“</span>`
            : `<span class="iso-badge iso-warn" title="${check.message}">!</span>`;
        }

        const contDisplay = item.soContPo
          ? `<div class="cont-number">${item.soContPo} ${isoHtml}</div>`
          : `<div class="cont-number empty-val">ChÆ°a cÃ³ sá»‘ Cont</div>`;

        const sealDisplay = item.soSealPo
          ? `Seal: <b>${item.soSealPo}</b>`
          : `Seal: <span style="color:#94a3b8;">--</span>`;

        const hasTruck = Boolean(item.soXePo);
        const drvInfo = getDriverForPlate(item.soXePo);
        const truckDisplay = hasTruck
          ? `<div class="truck-number">ðŸš› ${item.soXePo}</div>`
          : `<div class="truck-number unassigned">âš ï¸ ChÆ°a Ä‘iá»u xe</div>`;

        const vendorDisplay = drvInfo
          ? `<div class="vendor-name" style="color:#15803d;">ðŸ‘¨â€âœˆï¸ ${drvInfo.name} ${drvInfo.phone ? `(${drvInfo.phone})` : ""}</div>`
          : item.donViVcPo
          ? `<div class="vendor-name">ðŸ¢ ${item.donViVcPo}</div>`
          : `<div class="vendor-name" style="color:#94a3b8;">ÄVVT: ChÆ°a gÃ¡n</div>`;

        // Delivery / Packing Date pill
        const isUnsetDate = !item.ngayTraHang || item.ngayTraHang === "2021-11-11" || item.ngayTraHang === "1970-01-01";
        const isTodayDate = item.ngayTraHang === todayIso;
        const dateClass = isUnsetDate ? "date-unset" : isTodayDate ? "date-today" : "";
        const dateLabelPrefix = isXuat ? "NgÃ y Ä‘Ã³ng" : "NgÃ y giao";
        const dateFormatted = formatIsoToVN(item.ngayTraHang);

        // Extract phone number if available in lienHeNhanPo
        const phoneMatch = (item.lienHeNhanPo || "").match(/(0[0-9.\-\s]{8,13})/);
        const cleanPhone = phoneMatch ? phoneMatch[1].replace(/[^0-9]/g, "") : "";

        const locationParts = [item.khoNhanHangPo, item.diaChiNhanPo, item.tinhNhanPo]
          .filter(x => x && x !== "." && x !== "THONG BAO SAU");
        const locationText = locationParts.length > 0 ? locationParts.join(" â€¢ ") : "Äá»‹a chá»‰: ThÃ´ng bÃ¡o sau";

        return `
          <div class="po-card type-${item.xuatNhapPo} ${!hasTruck ? 'no-truck' : ''}" id="card-${item.idPo}">
            <div class="card-top">
              <div class="badges-left">
                ${typeBadge}
                <span class="badge badge-chuhang">ðŸ¢ ${item.chuHangPo || "N/A"}</span>
                ${sizeBadge}
                ${item.hinhThucKHopPo ? `<span class="badge" style="background:#f3e8ff;color:#7e22ce;">ðŸ”— ${item.hinhThucKHopPo}</span>` : ""}
              </div>
              <span class="po-id">#${item.idPo}</span>
            </div>

            <div class="cont-truck-box" onclick="openEditModal('${item.idPo}')">
              <div class="cont-side">
                <div class="field-mini-label">CONTAINER / SEAL</div>
                ${contDisplay}
                <div class="seal-number">${sealDisplay}</div>
              </div>
              <div class="truck-side">
                <div class="field-mini-label">XE / ÄÆ N Vá»Š Váº¬N Táº¢I</div>
                ${truckDisplay}
                ${vendorDisplay}
              </div>
            </div>

            <div class="customer-row" onclick="openEditModal('${item.idPo}')">
              <div class="customer-name">
                <span>ðŸ‘¤ ${item.tenKhachPo || "KhÃ¡ch láº»"}</span>
                <span style="font-size:11.5px;font-weight:600;color:#64748b;">${item.tenHangHoaPo || ""} ${item.trongLuongPo ? `(${item.trongLuongPo}T)` : ""}</span>
              </div>
              <div class="warehouse-addr">ðŸ“ ${locationText}</div>
              ${item.lienHeNhanPo && item.lienHeNhanPo !== "." ? `<div style="font-size:11.5px;color:#0369a1;margin-top:2px;font-weight:600;">ðŸ“ž LH: ${item.lienHeNhanPo}</div>` : ""}
            </div>

            <div class="card-meta">
              <div class="meta-item">
                <span>ðŸš¢ ${item.tauPo || "--"} (${item.chuyenPo || "--"})</span>
              </div>
              <div class="meta-item">
                <span class="date-pill ${dateClass}">ðŸ“… ${dateLabelPrefix}: ${dateFormatted}</span>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn-card btn-edit" onclick="openEditModal('${item.idPo}')">
                âœï¸ Cáº­p nháº­t
              </button>
              <button class="btn-card btn-zalo" onclick="copyPoInfoById('${item.idPo}', 2)" title="Copy THÃ”NG TIN GIAO HÃ€NG (Biá»ƒu tÆ°á»£ng xe)">
                ðŸš› Giao hÃ ng
              </button>
              <button class="btn-card btn-zalo" onclick="copyPoInfoById('${item.idPo}', 1)" title="Copy THÃ”NG BÃO HÃ€NG Äáº¾N / ÄÃ“NG HÃ€NG">
                ðŸ’¬ TB
              </button>
              ${cleanPhone ? `<a href="tel:${cleanPhone}" class="btn-card btn-call" title="Gá»i liÃªn há»‡ kho">ðŸ“ž</a>` : ""}
            </div>
          </div>
        `;
      }).join("");

      const hasMore = filteredShipments.length > renderLimit;
      const moreBtnHtml = hasMore
        ? `<button class="load-more-btn" onclick="loadMoreCards()">Xem thÃªm (${filteredShipments.length - renderLimit} lÃ´ hÃ ng ná»¯a)...</button>`
        : "";

      container.innerHTML = cardsHtml + moreBtnHtml;
    }

    function loadMoreCards() {
      renderLimit += 60;
      renderShipmentList();
    }

    // =========================================================================
    // 7. FILTER UI ACTIONS
    // =========================================================================
    function setQuickDate(mode) {
      state.dateMode = mode;
      const today = state.apiDate || getTodayISO();

      if (mode === "today") {
        state.fromDate = today;
        state.toDate = today;
      } else if (mode === "tomorrow") {
        state.fromDate = addDaysISO(today, 1);
        state.toDate = addDaysISO(today, 1);
      } else if (mode === "yesterday") {
        state.fromDate = addDaysISO(today, -1);
        state.toDate = addDaysISO(today, -1);
      } else if (mode === "3days") {
        state.fromDate = addDaysISO(today, -1);
        state.toDate = addDaysISO(today, 2);
      }

      document.getElementById("filterFromDate").value = state.fromDate;
      document.getElementById("filterToDate").value = state.toDate;

      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.dateMode === mode);
      });

      applyFilters();
    }

    function onCustomDateChange() {
      state.dateMode = "custom";
      state.fromDate = document.getElementById("filterFromDate").value;
      state.toDate = document.getElementById("filterToDate").value;
      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => btn.classList.remove("active"));
      applyFilters();
    }

    function setXuatNhapFilter(xn) {
      state.xuatNhap = xn;
      document.querySelectorAll(".bottom-nav .nav-item").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.xn === xn);
      });
      applyFilters();
    }

    function setChuHangFilter(ch) {
      state.chuHang = ch;
      applyFilters();
    }

    function setTruckQuickFilter(status) {
      state.truckStatus = state.truckStatus === status ? "ALL" : status;
      document.getElementById("filterTruckStatus").value = state.truckStatus;
      applyFilters();
    }

    function onSearchInput() {
      const val = document.getElementById("searchInput").value;
      state.searchQuery = val;
      document.getElementById("btnClearSearch").style.display = val ? "flex" : "none";
      applyFilters();
    }

    function clearSearch() {
      document.getElementById("searchInput").value = "";
      state.searchQuery = "";
      document.getElementById("btnClearSearch").style.display = "none";
      applyFilters();
    }

    function toggleAdvancedFilters() {
      document.getElementById("advancedFilters").classList.toggle("open");
    }

    function resetAllFilters() {
      state.chuHang = "ALL";
      state.xuatNhap = "ALL";
      state.truckStatus = "ALL";
      state.vendor = "ALL";
      state.province = "ALL";
      document.getElementById("filterTruckStatus").value = "ALL";
      document.getElementById("filterVendor").value = "ALL";
      document.getElementById("filterProvince").value = "ALL";
      clearSearch();
      setQuickDate("today");
    }

    function promptApiDate() {
      openAccountModal();
    }

    // =========================================================================
    // 8. EDIT MODAL & LIVE UPDATE TO VIETSUN TMS (index-crud.php)
    // =========================================================================
    function openEditModal(idPo) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      currentEditingPo = po;
      dirtyFields.clear();

      const isXuat = po.xuatNhapPo === "X";
      const badge = document.getElementById("modalBadgeType");
      badge.className = `badge ${isXuat ? "badge-xuat" : "badge-nhap"}`;
      badge.textContent = isXuat ? "ðŸ“¤ HÃ€NG XUáº¤T" : "ðŸ“¥ HÃ€NG NHáº¬P";

      document.getElementById("modalTitleText").textContent = `LÃ´ #${po.idPo} (${po.chuHangPo || ""})`;
      document.getElementById("modalCustomerName").textContent = po.tenKhachPo || "KhÃ¡ch hÃ ng";
      document.getElementById("modalRouteSub").textContent =
        `TÃ u: ${po.tauPo || "--"} (${po.chuyenPo || "--"}) â€¢ NgÃ y tÃ u: ${formatIsoToVN(po.ngayTauChayPo)} â€¢ Loáº¡i: ${Number(po.cont40Po) > 0 ? "40'" : "20'"}`;

      document.getElementById("lblNgayTraHang").textContent =
        isXuat ? "ðŸ“… NgÃ y ÄÃ³ng HÃ ng (ngayTraHang)" : "ðŸ“… NgÃ y Giao / Tráº£ HÃ ng (ngayTraHang)";

      document.getElementById("editIdPo").value = po.idPo;
      document.getElementById("editCodePo").value = po.codePo || "";
      document.getElementById("editXuatNhapPo").value = po.xuatNhapPo || "N";

      // Populate inputs
      const isUnsetDate = !po.ngayTraHang || po.ngayTraHang === "2021-11-11" || po.ngayTraHang === "1970-01-01";
      document.getElementById("inp_ngayTraHang").value = isUnsetDate ? "" : po.ngayTraHang;
      document.getElementById("inp_soContPo").value = po.soContPo || "";
      document.getElementById("inp_soSealPo").value = po.soSealPo || "";
      document.getElementById("inp_soXePo").value = po.soXePo || "";
      document.getElementById("inp_donViVcPo").value = po.donViVcPo || "";
      document.getElementById("inp_noiLayContPo").value = po.noiLayContPo || "";
      document.getElementById("inp_cangDiPo").value = po.cangDiPo || po.noiHaContPo || "";
      document.getElementById("inp_hinhThucKHopPo").value = po.hinhThucKHopPo || "";
      document.getElementById("inp_portTimePo").value = po.portTimePo || "";
      document.getElementById("inp_khoNhanHangPo").value = po.khoNhanHangPo || "";
      document.getElementById("inp_tinhNhanPo").value = po.tinhNhanPo || "";
      document.getElementById("inp_diaChiNhanPo").value = po.diaChiNhanPo || "";
      document.getElementById("inp_lienHeNhanPo").value = po.lienHeNhanPo || "";

      // Clear status styling
      document.querySelectorAll("#editPoForm .form-control").forEach(el => {
        el.classList.remove("saving", "saved", "warn-iso");
      });

      onContInput();
      updateModalDriverInfo(po.soXePo || "");
      document.getElementById("editModal").classList.add("open");
    }

    function updateModalDriverInfo(plate) {
      const box = document.getElementById("driverInfoBox");
      if (!box) return;
      const drv = getDriverForPlate(plate);
      if (drv) {
        box.style.display = "block";
        box.innerHTML = `ðŸ‘¨â€âœˆï¸ TÃ i xáº¿ VIETSUN: <strong>${drv.name}</strong> â€¢ ðŸ“ž <a href="tel:${drv.phone}" style="color:#0369a1;text-decoration:underline;">${drv.phone}</a>${drv.cccd ? `<div style="font-size:11px;color:#475569;margin-top:2px;">CCCD: ${drv.cccd}</div>` : ""}`;
      } else {
        box.style.display = "none";
        box.innerHTML = "";
      }
    }

    function closeEditModal() {
      hideAllAutocomplete();
      document.getElementById("editModal").classList.remove("open");
      currentEditingPo = null;
    }

    function hideAllAutocomplete() {
      document.querySelectorAll(".ac-dropdown").forEach(el => el.classList.remove("open"));
    }

    // Close autocomplete when tapping outside input/dropdown
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".form-group")) {
        hideAllAutocomplete();
      }
    });

    function showTruckSuggestions() {
      hideAllAutocomplete();
      const inp = document.getElementById("inp_soXePo");
      const curVendor = (document.getElementById("inp_donViVcPo").value || "").toUpperCase().trim();
      const q = (inp.value || "").toUpperCase().replace(/\s+/g, "").trim();
      const dropdown = document.getElementById("ac_soXePo");

      const allTrucks = Array.from(knownTruckMap.values());
      // Filter matching query (or show all if query matches exact current Editing PO's initial value on focus)
      const isInitialUnchanged = currentEditingPo && q === (currentEditingPo.soXePo || "").toUpperCase().trim();
      const filterQuery = isInitialUnchanged ? "" : q;

      let matches = allTrucks.filter(t => {
        if (!filterQuery) return true;
        return t.plate.includes(filterQuery);
      });

      // Sort: 1) Same vendor as currently filled donViVcPo, 2) Starts with query, 3) Usage count
      matches.sort((a, b) => {
        const aSameV = curVendor && a.vendor === curVendor ? 1 : 0;
        const bSameV = curVendor && b.vendor === curVendor ? 1 : 0;
        if (aSameV !== bSameV) return bSameV - aSameV;

        if (filterQuery) {
          const aStarts = a.plate.startsWith(filterQuery) ? 1 : 0;
          const bStarts = b.plate.startsWith(filterQuery) ? 1 : 0;
          if (aStarts !== bStarts) return bStarts - aStarts;
        }
        return b.count - a.count;
      });

      matches = matches.slice(0, 35);
      if (matches.length === 0) {
        dropdown.classList.remove("open");
        return;
      }

      const headerTitle = curVendor
        ? `Gá»£i Ã½ sá»‘ xe (Æ¯u tiÃªn ÄVVT: ${curVendor})`
        : `Gá»£i Ã½ biá»ƒn sá»‘ xe (${matches.length} xe)`;

      dropdown.innerHTML = `
        <div class="ac-header">
          <span>ðŸš› ${headerTitle}</span>
          <span onclick="hideAllAutocomplete()" style="cursor:pointer;color:#2563eb;">ÄÃ³ng âœ•</span>
        </div>
        ${matches.map(t => {
          const isMatchVendor = curVendor && t.vendor === curVendor;
          const drv = getDriverForPlate(t.plate);
          const subLabel = drv
            ? `${drv.name} (${drv.phone})`
            : (t.vendor || "");
          return `
            <div class="ac-item ${isMatchVendor ? 'same-vendor' : ''}" onclick="selectTruckSuggestion('${t.plate}', '${t.vendor || ""}')">
              <span>ðŸš› ${t.plate}</span>
              ${subLabel ? `<span class="ac-sub ${isMatchVendor ? 'match-vendor' : ''}">${isMatchVendor ? 'â˜… ' : ''}${subLabel}</span>` : ''}
            </div>
          `;
        }).join("")}
      `;
      dropdown.classList.add("open");
    }

    let isSelectingSuggestion = false;

    function selectTruckSuggestion(plate, vendor) {
      isSelectingSuggestion = true;
      const inpXe = document.getElementById("inp_soXePo");
      const inpDv = document.getElementById("inp_donViVcPo");
      inpXe.value = plate;
      if (vendor) {
        inpDv.value = vendor;
      }
      updateModalDriverInfo(plate);
      hideAllAutocomplete();
      saveRecentTruck(plate, vendor || inpDv.value);
      onFieldChanged("soXePo");
      setTimeout(() => { isSelectingSuggestion = false; }, 300);
    }

    function onTruckBlur() {
      setTimeout(() => {
        if (isSelectingSuggestion || !currentEditingPo) return;
        const inpXe = document.getElementById("inp_soXePo");
        const val = (inpXe.value || "").toUpperCase().trim();
        if (val !== (currentEditingPo.soXePo || "").toUpperCase().trim()) {
          onFieldChanged("soXePo");
        }
      }, 220);
    }

    function saveRecentTruck(plate, vendor) {
      if (!plate) return;
      try {
        const cleanPlate = plate.toUpperCase().replace(/\s+/g, "").trim();
        const cleanVendor = (vendor || "").toUpperCase().trim();
        knownTruckMap.set(cleanPlate, { plate: cleanPlate, vendor: cleanVendor, count: 99 });
        let recent = JSON.parse(localStorage.getItem("vs_recent_trucks") || "[]");
        recent = recent.filter(r => r.plate !== cleanPlate);
        recent.unshift({ plate: cleanPlate, vendor: cleanVendor });
        localStorage.setItem("vs_recent_trucks", JSON.stringify(recent.slice(0, 50)));
      } catch (_) {}
    }

    function showVendorSuggestions() {
      hideAllAutocomplete();
      const inp = document.getElementById("inp_donViVcPo");
      const q = (inp.value || "").toUpperCase().trim();
      const dropdown = document.getElementById("ac_donViVcPo");

      const isInitialUnchanged = currentEditingPo && q === (currentEditingPo.donViVcPo || "").toUpperCase().trim();
      const filterQuery = isInitialUnchanged ? "" : q;

      let matches = knownVendorList.filter(v => !filterQuery || v.includes(filterQuery)).slice(0, 40);
      if (matches.length === 0) {
        dropdown.classList.remove("open");
        return;
      }

      dropdown.innerHTML = `
        <div class="ac-header">
          <span>ðŸ¢ Chá»n ÄÆ¡n Vá»‹ Váº­n Táº£i (${matches.length})</span>
          <span onclick="hideAllAutocomplete()" style="cursor:pointer;color:#2563eb;">ÄÃ³ng âœ•</span>
        </div>
        ${matches.map(v => `
          <div class="ac-item" onclick="selectVendorSuggestion('${v.replace(/'/g, "\\'")}')">
            <span>ðŸ¢ ${v}</span>
          </div>
        `).join("")}
      `;
      dropdown.classList.add("open");
    }

    function selectVendorSuggestion(vendor) {
      isSelectingSuggestion = true;
      document.getElementById("inp_donViVcPo").value = vendor;
      hideAllAutocomplete();
      onFieldChanged("donViVcPo");
      setTimeout(() => { isSelectingSuggestion = false; }, 300);
    }

    function onVendorBlur() {
      setTimeout(() => {
        if (isSelectingSuggestion || !currentEditingPo) return;
        const inpDv = document.getElementById("inp_donViVcPo");
        const val = (inpDv.value || "").toUpperCase().trim();
        if (val !== (currentEditingPo.donViVcPo || "").toUpperCase().trim()) {
          onFieldChanged("donViVcPo");
        }
      }, 220);
    }

    function setModalDate(offsetDays) {
      const inp = document.getElementById("inp_ngayTraHang");
      if (offsetDays === null) {
        inp.value = "";
      } else {
        inp.value = addDaysISO(getTodayISO(), offsetDays);
      }
      onFieldChanged("ngayTraHang");
    }

    function setPortTimeNow() {
      const now = new Date();
      const dd = String(now.getDate()).padStart(2, "0");
      const mm = String(now.getMonth() + 1).padStart(2, "0");
      const yyyy = now.getFullYear();
      const hh = String(now.getHours()).padStart(2, "0");
      const min = String(now.getMinutes()).padStart(2, "0");
      document.getElementById("inp_portTimePo").value = `${dd}/${mm}/${yyyy} ${hh}:${min}`;
      onFieldChanged("portTimePo");
    }

    function onContInput() {
      const inp = document.getElementById("inp_soContPo");
      const badge = document.getElementById("isoCheckBadge");
      const val = inp.value.toUpperCase().replace(/[^A-Z0-9]/g, "");
      inp.value = val;

      if (!val) {
        badge.textContent = "";
        badge.className = "iso-badge";
        inp.classList.remove("warn-iso");
        return;
      }

      const res = validateContainerISO(val);
      // Also check 20/40 prefix mismatch like gimax.js
      let sizeMismatch = "";
      if (currentEditingPo && val.length >= 5) {
        const prefix5 = val.slice(0, 5);
        if (Number(currentEditingPo.cont20Po) === 1 && CHECK_CONT_40.includes(prefix5)) {
          sizeMismatch = " (Cont 40' trÃªn lá»‡nh 20'!)";
        } else if (Number(currentEditingPo.cont40Po) === 1 && CHECK_CONT_20.includes(prefix5)) {
          sizeMismatch = " (Cont 20' trÃªn lá»‡nh 40'!)";
        }
      }

      badge.textContent = res.message + sizeMismatch;
      if (res.valid && !sizeMismatch) {
        badge.className = "iso-badge iso-ok";
        inp.classList.remove("warn-iso");
      } else {
        badge.className = "iso-badge iso-warn";
        inp.classList.add("warn-iso");
      }
    }

    function onTruckInput() {
      const inp = document.getElementById("inp_soXePo");
      const raw = inp.value.toUpperCase().trim();
      inp.value = raw;

      // Replicate gimax.js searchCities: if license plate is in Vietsun fleet, auto-fill VIETSUN
      const matchedVs = DEFAULT_XE_VIETSUN.find(x => x === raw || (raw.length >= 4 && x.endsWith(raw)));
      if (matchedVs && raw.length >= 5) {
        const vendorInp = document.getElementById("inp_donViVcPo");
        if (!vendorInp.value || vendorInp.value === "VIETSUN") {
          vendorInp.value = "VIETSUN";
        }
      }
      updateModalDriverInfo(raw);
      showTruckSuggestions();
    }

    function onFieldChanged(fieldName) {
      dirtyFields.add(fieldName);
      // Immediately sync this single field to Vietsun TMS (just like web fSua3 onchange!)
      syncSingleFieldToServer(fieldName);
    }

    async function syncSingleFieldToServer(fieldName) {
      if (!currentEditingPo) return;
      const idPo = currentEditingPo.idPo;
      const codePo = currentEditingPo.codePo;
      const isXuat = currentEditingPo.xuatNhapPo === "X";
      const inpEl = document.getElementById(`inp_${fieldName}`);
      if (!inpEl) return;

      let rawVal = inpEl.value.trim();
      inpEl.classList.remove("saved");
      inpEl.classList.add("saving");

      try {
        if (fieldName === "soContPo") {
          rawVal = rawVal.toUpperCase();
          // 1. Send cv=5, loai=21 (exact fSua34 logic from gimax.js)
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 5,
            loai: 21,
            iddt: idPo,
            cot: rawVal,
            thongtin: codePo
          });
          // 2. Also send standard b18<<|4<<|50<<|idPo<<|soContPo<<|codePo
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|50<<|${idPo}<<|soContPo<<|${codePo}`,
            thongtin: rawVal
          });
          currentEditingPo.soContPo = rawVal;
        } else if (fieldName === "ngayTraHang") {
          const vnDate = rawVal ? formatIsoToPayloadDate(rawVal) : "";
          const kieuNgay = isXuat ? "8" : "9";
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|${kieuNgay}<<|20<<|${idPo}<<|ngayTraHang<<|${codePo}`,
            thongtin: vnDate
          });
          currentEditingPo.ngayTraHang = rawVal || "2021-11-11";
        } else if (fieldName === "soXePo") {
          rawVal = rawVal.toUpperCase();
          const htmlResp = await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18newtruck<<|4<<|50<<|${idPo}<<|soXePo<<|${codePo}`,
            thongtin: rawVal
          });
          currentEditingPo.soXePo = rawVal;
          saveRecentTruck(rawVal, document.getElementById("inp_donViVcPo").value);

          // Parse #reload_input_ajax response if server auto-resolved Vendor
          if (htmlResp && htmlResp.includes("reload_input_ajax")) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlResp, "text/html");
            const kqXe = doc.getElementById(`kq_soXePo_${idPo}`);
            const kqDv = doc.getElementById(`kq_donViVcPo_${idPo}`);
            if (kqXe && kqXe.textContent) {
              currentEditingPo.soXePo = kqXe.textContent.trim();
              document.getElementById("inp_soXePo").value = currentEditingPo.soXePo;
            }
            if (kqDv && kqDv.textContent) {
              currentEditingPo.donViVcPo = kqDv.textContent.trim();
              document.getElementById("inp_donViVcPo").value = currentEditingPo.donViVcPo;
              document.getElementById("inp_donViVcPo").classList.add("saved");
            }
          }
        } else {
          // Map field lengths exactly as gimax.js
          const lengthMap = {
            soSealPo: 50,
            donViVcPo: 30,
            noiLayContPo: 100,
            cangDiPo: 100,
            hinhThucKHopPo: 50,
            portTimePo: 20,
            khoNhanHangPo: 150,
            tinhNhanPo: 50,
            diaChiNhanPo: 220,
            lienHeNhanPo: 150
          };
          const maxLen = lengthMap[fieldName] || 100;
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|${maxLen}<<|${idPo}<<|${fieldName}<<|${codePo}`,
            thongtin: rawVal
          });
          currentEditingPo[fieldName] = rawVal;
        }

        inpEl.classList.remove("saving");
        inpEl.classList.add("saved");
        dirtyFields.delete(fieldName);
        try {
          localStorage.setItem("vs_cached_shipments", JSON.stringify(allShipments.slice(0, 500)));
        } catch (_) {}
        applyFilters();
        showToast(`ÄÃ£ lÆ°u ${fieldName} lÃªn há»‡ thá»‘ng TMS!`, "success");
      } catch (err) {
        console.error(err);
        inpEl.classList.remove("saving");
        showToast(`Lá»—i lÆ°u ${fieldName}: ${err.message}`, "error");
      }
    }

    async function saveAllChanges(e) {
      if (e) e.preventDefault();
      if (!currentEditingPo) return;
      const btn = document.getElementById("btnSaveAll");
      btn.disabled = true;
      btn.textContent = "â³ Äang lÆ°u lÃªn TMS...";

      try {
        const fieldsToCheck = [
          "ngayTraHang", "soContPo", "soSealPo", "soXePo", "donViVcPo",
          "noiLayContPo", "cangDiPo", "hinhThucKHopPo", "portTimePo",
          "khoNhanHangPo", "tinhNhanPo", "diaChiNhanPo", "lienHeNhanPo"
        ];

        for (const f of fieldsToCheck) {
          const inp = document.getElementById(`inp_${f}`);
          if (!inp) continue;
          const val = inp.value.trim();
          const curVal = f === "ngayTraHang"
            ? (currentEditingPo.ngayTraHang === "2021-11-11" ? "" : (currentEditingPo.ngayTraHang || ""))
            : (currentEditingPo[f] || "");
          if (val !== curVal || dirtyFields.has(f)) {
            await syncSingleFieldToServer(f);
          }
        }

        showToast(`ÄÃ£ Ä‘á»“ng bá»™ hoÃ n táº¥t lÃ´ #${currentEditingPo.idPo}!`, "success");
        setTimeout(() => closeEditModal(), 350);
      } finally {
        btn.disabled = false;
        btn.textContent = "ðŸ’¾ LÆ°u Cáº­p Nháº­t LÃªn TMS";
      }
    }

    // =========================================================================
    // 9. ZALO COPY HELPER (EXACT MATCH WITH TMS WEB copyttkh(id, loai))
    // =========================================================================
    function buildZaloText(po, loai = 2) {
      const isXuat = po.xuatNhapPo === "X";
      const khach = po.tenKhachPo || "";
      const kho = po.khoNhanHangPo || "";
      const diachi = po.diaChiNhanPo || "";
      const tinh = po.tinhNhanPo || "";
      const lienhe = (po.lienHeNhanPo && po.lienHeNhanPo !== ".") ? po.lienHeNhanPo : "";
      const soCont = po.soContPo || "";
      const soSeal = po.soSealPo || "";
      const tau = po.tauPo || "";
      const chuyen = po.chuyenPo || "";
      const ngayGiao = (po.ngayTraHang && po.ngayTraHang !== "2021-11-11" && po.ngayTraHang !== "1970-01-01")
        ? formatIsoToVN(po.ngayTraHang)
        : "";
      const noiCapRong = po.noiLayContPo || "";
      const soXe = po.soXePo || "";

      // Lookup Vietsun driver details by license plate
      const drv = getDriverForPlate(soXe);
      const tenLaiXe = drv ? drv.name : (po.taiXePo || "");
      const sdtLaiXe = drv ? drv.phone : (po.sdtTaiXePo || "");
      const cccdLaiXe = drv && drv.cccd ? drv.cccd : "  NÆ¡i cáº¥p: ";
      const gplxLaiXe = drv && drv.gplx ? drv.gplx : " NÆ¡i cáº¥p:";

      if (loai === 1) {
        // Icon ðŸ’¬: ThÃ´ng bÃ¡o hÃ ng Ä‘áº¿n (HÃ ng Nháº­p) hoáº·c ÄÃ³ng hÃ ng (HÃ ng Xuáº¥t)
        if (!isXuat) {
          return (
            `THÃ”NG BÃO HÃ€NG Äáº¾N:\n` +
            `- TÃªn khÃ¡ch: ${khach}\n` +
            `- Kho tráº£: ${kho}\n` +
            `- Äá»‹a chá»‰: ${diachi}\t- Tá»‰nh: ${tinh}\n` +
            `- LiÃªn há»‡: ${lienhe}\n` +
            `- Sá»‘ cont: ${soCont}\t\t- Sá»‘ seal: ${soSeal}\n` +
            `- TÃªn tÃ u: ${tau}\t\t\t\t- Sá»‘ chuyáº¿n: ${chuyen}\n` +
            `- NgÃ y giao: ${ngayGiao}`
          );
        } else {
          return (
            `ÄÃ³ng hÃ ng: ${kho} (${diachi} - ${tinh})\n` +
            `NgÃ y: ${ngayGiao}\n` +
            `Cont: ${soCont} Seal:${soSeal}\n` +
            `NÆ¡i cáº¥p rá»—ng: ${noiCapRong}\n` +
            `Thá»i gian dá»± kiáº¿n tá»›i kho: \n` +
            `Sá»‘ xe:${soXe}\n` +
            `TX: ${tenLaiXe}\n` +
            `Sá»‘ CMND/CCCD:${cccdLaiXe}\n` +
            `Sá»‘ GPLX:${gplxLaiXe}\n` +
            `SÄT:${sdtLaiXe}`
          );
        }
      }

      // Default loai === 2 (Icon ðŸš› chiáº¿c xe): THÃ”NG TIN GIAO HÃ€NG / ÄÃ“NG HÃ€NG
      const loaigiao = isXuat ? "ÄÃ“NG" : "GIAO";
      return (
        `THÃ”NG TIN ${loaigiao} HÃ€NG:\n` +
        `- Kho ${loaigiao}: ${kho}\n` +
        `- Äá»‹a chá»‰: ${diachi}\t- Tá»‰nh: ${tinh}\n` +
        `- LiÃªn há»‡: ${lienhe}\n` +
        `- Sá»‘ cont: ${soCont}\t\t- Sá»‘ seal: ${soSeal}\n` +
        `- NgÃ y ${loaigiao}: ${ngayGiao}\n` +
        `- Sá»‘ xe: ${soXe}\n` +
        `- TÃªn LÃ¡i Xe: ${tenLaiXe}\n` +
        `- SÄT: ${sdtLaiXe}\n` +
        `- Thá»i gian tá»›i kho: `
      );
    }

    function copyTextToClipboard(text, successMsg) {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
          showToast(successMsg, "success");
        }).catch(() => fallbackCopyText(text, successMsg));
      } else {
        fallbackCopyText(text, successMsg);
      }
    }

    function fallbackCopyText(text, successMsg) {
      const ta = document.createElement("textarea");
      ta.value = text;
      ta.style.position = "fixed";
      ta.style.left = "-9999px";
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      try {
        document.execCommand("copy");
        showToast(successMsg, "success");
      } catch (_) {
        showToast("KhÃ´ng thá»ƒ tá»± Ä‘á»™ng copy, vui lÃ²ng thá»­ láº¡i", "error");
      }
      document.body.removeChild(ta);
    }

    function copyPoInfoById(idPo, loai = 2) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      const text = buildZaloText(po, loai);
      const drv = getDriverForPlate(po.soXePo);
      const drvNote = drv ? ` (TX: ${drv.name})` : "";
      const label = loai === 1 ? "ThÃ´ng bÃ¡o" : "TT Giao hÃ ng";
      copyTextToClipboard(text, `ÄÃ£ copy ${label} Cont ${po.soContPo || "#" + po.idPo}${drvNote}!`);
    }

    function copyCurrentPoToZalo(loai = 2) {
      if (!currentEditingPo) return;
      // Read live inputs from modal so user can copy immediately after typing truck plate
      const livePo = Object.assign({}, currentEditingPo, {
        ngayTraHang: document.getElementById("inp_ngayTraHang").value || currentEditingPo.ngayTraHang,
        soContPo: document.getElementById("inp_soContPo").value || currentEditingPo.soContPo,
        soSealPo: document.getElementById("inp_soSealPo").value || currentEditingPo.soSealPo,
        soXePo: document.getElementById("inp_soXePo").value || currentEditingPo.soXePo,
        donViVcPo: document.getElementById("inp_donViVcPo").value || currentEditingPo.donViVcPo,
        noiLayContPo: document.getElementById("inp_noiLayContPo").value || currentEditingPo.noiLayContPo,
        khoNhanHangPo: document.getElementById("inp_khoNhanHangPo").value || currentEditingPo.khoNhanHangPo,
        tinhNhanPo: document.getElementById("inp_tinhNhanPo").value || currentEditingPo.tinhNhanPo,
        diaChiNhanPo: document.getElementById("inp_diaChiNhanPo").value || currentEditingPo.diaChiNhanPo,
        lienHeNhanPo: document.getElementById("inp_lienHeNhanPo").value || currentEditingPo.lienHeNhanPo
      });
      const text = buildZaloText(livePo, loai);
      const drv = getDriverForPlate(livePo.soXePo);
      const drvNote = drv ? ` (TX: ${drv.name})` : "";
      const label = loai === 1 ? "ThÃ´ng bÃ¡o" : "TT Giao hÃ ng";
      copyTextToClipboard(text, `ÄÃ£ copy ${label} Cont ${livePo.soContPo || "#" + livePo.idPo}${drvNote}!`);
    }

    // =========================================================================
    // 10. ACCOUNT SETTINGS & PWA INSTALL
    // =========================================================================
    function openAccountModal() {
      document.getElementById("cfgUsername").value = state.username;
      document.getElementById("cfgPassword").value = state.password;
      document.getElementById("cfgUserString").value = state.userString;
      document.getElementById("cfgApiDate").value = state.apiDate;
      document.getElementById("accountModal").classList.add("open");
    }

    function closeAccountModal() {
      document.getElementById("accountModal").classList.remove("open");
    }

    async function saveAccountSettings() {
      state.username = document.getElementById("cfgUsername").value.trim() || "trucking14";
      state.password = document.getElementById("cfgPassword").value.trim() || "1";
      state.userString = document.getElementById("cfgUserString").value.trim();
      state.apiDate = document.getElementById("cfgApiDate").value || getTodayISO();

      // Try logging in via ?api=login
      try {
        const resp = await fetch("?api=login", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username: state.username, password: state.password })
        });
        if (resp.ok) {
          const info = await resp.json();
          if (info.userString) {
            state.userString = info.userString;
            document.getElementById("cfgUserString").value = info.userString;
          }
        }
      } catch (_) {}

      try {
        localStorage.setItem("vs_username", state.username);
        localStorage.setItem("vs_password", state.password);
        localStorage.setItem("vs_userString", state.userString);
        localStorage.setItem("vs_apiDate", state.apiDate);
      } catch (_) {}

      document.getElementById("userLabel").textContent = `TK: ${state.username}`;
      document.getElementById("serverDateInfo").textContent = `Má»‘c dá»¯ liá»‡u API: ${formatIsoToVN(state.apiDate)}`;
      closeAccountModal();
      refreshData(true);
    }

    // PWA Installation Prompt (Android Chrome + iOS Safari Support)
    let deferredPrompt = null;
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;

    window.addEventListener("beforeinstallprompt", (e) => {
      e.preventDefault();
      deferredPrompt = e;
      document.getElementById("btnInstallPwa").style.display = "inline-flex";
    });

    async function installPWA() {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        document.getElementById("btnInstallPwa").style.display = "none";
      } else if (isIOS) {
        alert("ðŸ“² Äá»ƒ cÃ i App trÃªn iPhone / iPad:\n\n1. Má»Ÿ link nÃ y báº±ng trÃ¬nh duyá»‡t Safari.\n2. Báº¥m biá»ƒu tÆ°á»£ng Chia sáº» (Ã´ vuÃ´ng cÃ³ mÅ©i tÃªn hÆ°á»›ng lÃªn â¬†ï¸ á»Ÿ thanh dÆ°á»›i cÃ¹ng).\n3. KÃ©o xuá»‘ng chá»n 'ThÃªm vÃ o MH chÃ­nh' (Add to Home Screen) âž” Báº¥m 'ThÃªm'.");
      }
    }

    // Initialize App
    window.addEventListener("DOMContentLoaded", () => {
      if (isIOS && !isStandalone) {
        document.getElementById("btnInstallPwa").style.display = "inline-flex";
      }
      if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js").catch(() => {});
      }
      document.getElementById("userLabel").textContent = `TK: ${state.username}`;
      document.getElementById("filterFromDate").value = state.fromDate;
      document.getElementById("filterToDate").value = state.toDate;
      document.getElementById("serverDateInfo").textContent = `Má»‘c dá»¯ liá»‡u API: ${formatIsoToVN(state.apiDate)}`;
      refreshData(false);
    });
  </script>
</body>
</html>
