<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (isset(['api']) || isset(['route'])) {
    require_once __DIR__ . '/api/index.php';
    exit;
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
      gap: 8px;
      min-width: 0;
      flex: 1;
      overflow: hidden;
    }

    .brand-text {
      min-width: 0;
      flex: 1;
      overflow: hidden;
    }

    .brand-logo {
      width: 34px;
      height: 34px;
      background: #fff;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: var(--primary);
      font-size: 13.5px;
      letter-spacing: -0.5px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      flex-shrink: 0;
    }

    .brand-title {
      font-weight: 800;
      font-size: 15px;
      letter-spacing: 0.2px;
      line-height: 1.15;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .brand-sub {
      font-size: 11px;
      opacity: 0.92;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 5px;
      line-height: 1.25;
      margin-top: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #4ade80;
      display: inline-block;
      box-shadow: 0 0 6px #4ade80;
      flex-shrink: 0;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 5px;
      flex-shrink: 0;
    }

    .icon-btn {
      background: rgba(255, 255, 255, 0.16);
      border: 1px solid rgba(255, 255, 255, 0.24);
      color: #fff;
      border-radius: 9px;
      padding: 6px 8px;
      font-size: 11.5px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
      flex-shrink: 0;
      cursor: pointer;
      transition: all 0.15s;
    }

    /* FULL-SCREEN LOGIN OVERLAY */
    .login-overlay {
      position: fixed;
      inset: 0;
      z-index: 3000;
      background: linear-gradient(145deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-overlay.open {
      display: flex;
    }

    .login-card {
      width: 100%;
      max-width: 390px;
      background: #ffffff;
      border-radius: 20px;
      padding: 26px 22px 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
      animation: fadeUp 0.25s ease;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-logo-box {
      width: 56px;
      height: 56px;
      margin: 0 auto 12px;
      border-radius: 16px;
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
      color: #ffffff;
      font-weight: 900;
      font-size: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.32);
    }

    .login-error {
      display: none;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
      padding: 9px 12px;
      border-radius: 10px;
      font-size: 12.5px;
      font-weight: 600;
      margin-bottom: 12px;
      text-align: center;
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

    #chipSingleDate {
      position: relative;
      cursor: pointer;
      margin: 0;
    }
    #quickSingleDatePicker {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
      border: none;
      z-index: 2;
    }
    #quickSingleDatePicker::-webkit-calendar-picker-indicator {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      opacity: 0;
      cursor: pointer;
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
      border-left: 5px solid #ea580c;
      background: #fffbf5;
    }

    .po-card.type-N {
      border-left: 5px solid #16a34a;
      background: #ffffff;
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
      background: #ffedd5;
      color: #c2410c;
      border: 1px solid #fed7aa;
    }

    .badge-nhap {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
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
      grid-template-columns: minmax(0, 1.08fr) minmax(0, 1fr);
      gap: 8px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 8px 10px;
      margin-bottom: 8px;
      overflow: hidden;
    }

    .cont-side, .truck-side {
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-width: 0;
      overflow: hidden;
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
      font-size: 11px;
      font-weight: 700;
      color: #334155;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-top: 1px;
      max-width: 100%;
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
      gap: 5px;
      margin-top: 9px;
      width: 100%;
    }

    .btn-card {
      flex: 1 1 0;
      min-width: 0;
      padding: 7px 5px;
      border-radius: 9px;
      font-size: 11.5px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      transition: all 0.15s;
    }

    .btn-edit {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
      flex: 1.05 1 0;
    }

    .btn-zalo {
      background: #f1f5f9;
      color: #0f172a;
      border: 1px solid #cbd5e1;
      flex: 1 1 0;
    }

    .btn-call {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
      flex: 0 0 36px;
      width: 36px;
      padding: 7px 0;
      text-decoration: none;
    }

    /* FLOATING 20' / 40' QUICK FILTER DOCK (BOTTOM RIGHT) */
    .floating-size-dock {
      position: fixed;
      right: 12px;
      bottom: calc(68px + var(--safe-bottom));
      z-index: 95;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .fab-size-btn {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.94);
      backdrop-filter: blur(6px);
      border: 1.5px solid #cbd5e1;
      box-shadow: 0 4px 14px rgba(15, 23, 42, 0.16);
      color: #1e293b;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2px;
      transition: all 0.16s ease;
      user-select: none;
      -webkit-tap-highlight-color: transparent;
    }

    .fab-size-btn:active {
      transform: scale(0.93);
    }

    .fab-size-lbl {
      font-size: 13px;
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.3px;
    }

    .fab-size-cnt {
      font-size: 10px;
      font-weight: 700;
      color: #64748b;
      background: #f1f5f9;
      padding: 0 5px;
      border-radius: 6px;
      margin-top: 2px;
      line-height: 1.25;
    }

    .fab-size-btn.active-20 {
      background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
      border-color: #1d4ed8;
      color: #ffffff;
      box-shadow: 0 6px 18px rgba(37, 99, 235, 0.42);
      transform: scale(1.06);
    }

    .fab-size-btn.active-40 {
      background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
      border-color: #c2410c;
      color: #ffffff;
      box-shadow: 0 6px 18px rgba(234, 88, 12, 0.42);
      transform: scale(1.06);
    }

    .fab-size-btn.active-20 .fab-size-cnt,
    .fab-size-btn.active-40 .fab-size-cnt {
      background: rgba(255, 255, 255, 0.24);
      color: #ffffff;
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

    /* =========================================================================
       DESKTOP SPREADSHEET TABLE MODE (NHẬP NHANH ĐVVT & SỐ XE NHƯ EXCEL/TMS WEB)
       ========================================================================= */
    body.desktop-table-mode .list-container {
      display: block !important;
      max-width: 100% !important;
      padding: 4px 10px 84px;
    }

    body.desktop-table-mode .summary-strip {
      display: flex !important;
      align-items: center;
      gap: 10px;
      max-width: 100% !important;
      padding: 10px 10px 4px;
    }

    body.desktop-table-mode .summary-strip .stat-card {
      flex: 1 1 0;
      min-width: 110px;
    }

    body.desktop-table-mode .filter-section,
    body.desktop-table-mode .chuhang-bar {
      max-width: 100% !important;
    }

    /* DESKTOP BULK ACTIONS (IN PKL HÀNG LOẠT / IN BB) */
    .desktop-bulk-ctrl {
      display: none;
      position: relative;
      align-items: center;
      margin-left: auto;
      flex-shrink: 0;
    }

    body.desktop-table-mode .desktop-bulk-ctrl {
      display: flex;
    }

    .bulk-dropdown-wrap {
      position: relative;
      display: inline-block;
    }

    .btn-bulk-main {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
      color: #ffffff;
      border: 1px solid #1d4ed8;
      border-radius: 9px;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
      transition: all 0.15s ease;
      white-space: nowrap;
    }

    .btn-bulk-main:hover {
      background: linear-gradient(135deg, #172554 0%, #1d4ed8 100%);
      box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
      transform: translateY(-1px);
    }

    .bulk-dropdown-pop {
      display: none;
      position: absolute;
      top: calc(100% + 6px);
      right: 0;
      min-width: 215px;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 10px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
      padding: 6px;
      z-index: 1000;
    }

    .bulk-dropdown-pop.show {
      display: block;
      animation: bulkFadeDown 0.15s ease-out;
    }

    @keyframes bulkFadeDown {
      from { opacity: 0; transform: translateY(-4px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .bulk-opt-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.15s ease;
      user-select: none;
    }

    .bulk-opt-item:hover {
      background: #eff6ff;
    }

    .bulk-opt-icon {
      font-size: 20px;
      flex-shrink: 0;
    }

    .bulk-opt-info {
      display: flex;
      flex-direction: column;
      text-align: left;
    }

    .bulk-opt-title {
      font-size: 13px;
      font-weight: 800;
      color: #1e3a8a;
    }

    .bulk-opt-desc {
      font-size: 11px;
      color: #64748b;
      margin-top: 1px;
    }

    /* ACTIVE BAR IN SUMMARY STRIP */
    .bulk-active-bar {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #ffffff;
      padding: 4px 6px;
      border: 1.5px solid #3b82f6;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }

    .bulk-tag-carrier {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #e0f2fe;
      color: #0369a1;
      padding: 6px 10px;
      border-radius: 7px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      border: 1px solid #bae6fd;
      transition: all 0.15s ease;
      user-select: none;
    }

    .bulk-tag-carrier:hover {
      background: #bae6fd;
      color: #0c4a6e;
    }

    .btn-bulk-execute {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
      color: #ffffff;
      border: none;
      border-radius: 7px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3);
      transition: all 0.15s ease;
      white-space: nowrap;
    }

    .btn-bulk-execute:hover {
      background: linear-gradient(135deg, #166534 0%, #15803d 100%);
      box-shadow: 0 3px 8px rgba(22, 163, 74, 0.4);
      transform: translateY(-1px);
    }

    .btn-bulk-exit {
      background: #ffffff;
      color: #64748b;
      border: 1px solid #cbd5e1;
      border-radius: 7px;
      padding: 6px 10px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .btn-bulk-exit:hover {
      background: #fee2e2;
      color: #dc2626;
      border-color: #fca5a5;
    }

    /* =========================================================================
       VISUAL BIÊN BẢN (BB) TEMPLATE MANAGER MODAL & A4 WYSIWYG PAPER
       ========================================================================= */
    .bb-mgr-box {
      background: #ffffff;
      border-radius: 14px;
      box-shadow: 0 25px 60px rgba(15, 23, 42, 0.35);
      width: 96vw;
      max-width: 1320px;
      height: 92vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .bb-mgr-header {
      background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
      color: #ffffff;
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-shrink: 0;
    }
    .bb-mgr-body {
      display: flex;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }
    .bb-mgr-sidebar {
      width: 285px;
      border-right: 1px solid #cbd5e1;
      background: #f8fafc;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
    }
    .bb-mgr-list {
      flex: 1;
      overflow-y: auto;
      padding: 10px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .bb-tpl-card {
      background: #ffffff;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      padding: 10px 12px;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .bb-tpl-card:hover {
      border-color: #0d9488;
      background: #f0fdfa;
    }
    .bb-tpl-card.active {
      border-color: #0f766e;
      background: #ccfbf1;
      box-shadow: 0 2px 8px rgba(15, 118, 110, 0.15);
    }
    .bb-tpl-card-title {
      font-size: 13px;
      font-weight: 800;
      color: #0f172a;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
    }
    .bb-tpl-card-kw {
      font-size: 11px;
      color: #0f766e;
      font-weight: 600;
      margin-top: 4px;
      word-break: break-word;
    }
    .bb-mgr-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      background: #f1f5f9;
      overflow: hidden;
    }
    .bb-mgr-meta-bar {
      background: #ffffff;
      border-bottom: 1px solid #cbd5e1;
      padding: 10px 14px;
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 10px;
      flex-shrink: 0;
    }
    .bb-meta-field {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .bb-meta-field label {
      font-size: 11px;
      font-weight: 800;
      color: #334155;
      text-transform: uppercase;
    }
    .bb-meta-inp {
      border: 1px solid #94a3b8;
      border-radius: 7px;
      padding: 6px 10px;
      font-size: 12.5px;
      font-weight: 600;
      color: #0f172a;
    }
    .bb-ac-wrap {
      position: relative;
      display: flex;
      flex: 1;
    }
    .bb-ac-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      max-height: 240px;
      overflow-y: auto;
      background: #ffffff;
      border: 1.5px solid #0d9488;
      border-radius: 9px;
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.22);
      z-index: 10050;
      padding: 4px;
    }
    .bb-ac-dropdown.open {
      display: block;
    }
    .bb-ac-item {
      padding: 7px 10px;
      border-radius: 6px;
      font-size: 12.5px;
      font-weight: 600;
      color: #0f172a;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      transition: background 0.12s;
    }
    .bb-ac-item:hover {
      background: #ccfbf1;
      color: #0f766e;
    }
    .bb-mgr-toolbar {
      background: #f8fafc;
      border-bottom: 1px solid #cbd5e1;
      padding: 8px 14px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }
    .bb-tool-btn {
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 12px;
      font-weight: 700;
      color: #1e293b;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: all 0.12s;
    }
    .bb-tool-btn:hover {
      background: #e2e8f0;
      border-color: #94a3b8;
    }
    .bb-var-chip {
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 11.5px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      transition: all 0.12s;
    }
    .bb-var-chip:hover {
      background: #dbeafe;
      border-color: #60a5fa;
      transform: translateY(-1px);
    }
    .bb-mgr-canvas-wrap {
      flex: 1;
      overflow: auto;
      padding: 18px;
      background: #cbd5e1;
    }
    .bb-a4-editor {
      width: 190mm;
      min-height: 255mm;
      margin: 0 auto;
      background: #ffffff;
      padding: 8mm 8mm;
      box-shadow: 0 6px 24px rgba(15, 23, 42, 0.22);
      outline: none;
      font-family: "Times New Roman", Times, serif;
      color: #000000;
      position: relative;
    }
    .bb-a4-editor:focus {
      box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.35), 0 6px 24px rgba(15, 23, 42, 0.22);
    }
    .bb-mgr-footer {
      background: #ffffff;
      border-top: 1px solid #cbd5e1;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-shrink: 0;
    }
    /* Shared A4 Template Classes for Live Editor & Print */
    .bb-vietsun { font-family: "Times New Roman", Times, serif; font-size: 13pt; color: #000; }
    .vs-div-picture { width: 100%; min-height: 225mm; position: relative; }
    .vs-watermark-img { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 82%; max-height: 175mm; object-fit: contain; opacity: 0.22; pointer-events: none; z-index: 0; }
    .vs-seal-img { position: absolute; top: 18mm; left: 10mm; width: 39mm; height: 39mm; object-fit: contain; pointer-events: none; z-index: 1; }
    .vs-content-layer { position: relative; z-index: 2; }
    .vs-header-table { width: 100%; border-collapse: collapse; }
    .vs-info-table { width: 100%; border-collapse: collapse; line-height: 1.8; vertical-align: top; }
    .vs-info-table th, .vs-info-table td { padding: 0 2px; vertical-align: top; }
    .vs-bangND { width: 100%; border-collapse: collapse; line-height: 1.8; margin: 4px 0; }
    .vs-bangND th, .vs-bangND td { border: 1px solid #000; padding: 2px 10px; text-align: center; }
    .vs-sig-row { display: flex; justify-content: space-between; text-align: center; font-weight: 700; margin-top: 8px; }
    .vs-sig-col { width: 33.33%; }
    .bb-4oranges { font-family: "Times New Roman", Times, serif; color: #000; padding-top: 4mm; }
    .thna-header { display: flex; align-items: flex-start; gap: 16px; padding-left: 8px; }
    .thna-logo-col { flex-shrink: 0; padding-top: 2px; }
    .thna-logo-img { width: 160px; height: auto; display: block; }
    .thna-info-col { font-family: Arial, Helvetica, sans-serif; color: #222; line-height: 1.38; }
    .thna-title { font-size: 14pt; font-weight: 700; color: #0000FF; margin-bottom: 3px; }
    .thna-sub { font-size: 10pt; margin-bottom: 2px; }
    .thna-line { font-size: 10pt; color: #222; }
    .thna-link { color: #0000FF; text-decoration: underline; }
    .thna-divider { font-family: "Times New Roman", Times, serif; font-size: 11pt; text-align: center; letter-spacing: 0.4px; margin: 12px 0 14px; overflow: hidden; white-space: nowrap; }
    .thna-doc-title { font-size: 16pt; font-weight: 700; text-align: center; margin-bottom: 6px; }
    .thna-doc-date { font-size: 12pt; text-align: right; padding-right: 28px; margin-bottom: 14px; }
    .thna-section { line-height: 1.85; }
    .thna-bold-12 { font-size: 12pt; font-weight: 700; }
    .thna-bold-13 { font-size: 13pt; font-weight: 700; }
    .thna-driver-line { font-size: 13pt; margin-top: 2px; }
    .thna-table { width: 100%; border-collapse: collapse; margin: 14px 0 16px; font-size: 12pt; }
    .thna-table th, .thna-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
    .thna-table thead th { font-weight: 700; text-align: center; height: 26px; }
    .thna-table tbody td { height: 36px; }
    .thna-weight-words { font-size: 13pt; padding-left: 24px; margin: 16px 0 22px; }
    .thna-signatures { display: flex; justify-content: space-between; font-size: 13pt; padding: 0 36px; margin-top: 16px; }
    .thna-sig-col { text-align: center; width: 45%; }
    .thna-sig-title { margin-bottom: 68px; }
    .thna-sig-sub { font-size: 12.5pt; }

    .pc-table-wrap {
      width: 100%;
      overflow-x: auto;
      background: #ffffff;
      border: 1px solid #94a3b8;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    }

    .pc-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      color: #0f172a;
      table-layout: auto;
    }

    .pc-table th,
    .pc-table td {
      border: 1px solid #94a3b8;
      padding: 4px 6px;
      vertical-align: middle;
      line-height: 1.25;
    }

    .pc-table thead th {
      position: sticky;
      top: 0;
      z-index: 10;
      background: #cfe8fc;
      color: #0f172a;
      font-weight: 800;
      font-size: 11px;
      text-align: center;
      white-space: nowrap;
      padding: 6px 5px;
      text-transform: uppercase;
    }

    .pc-table tbody tr.pc-row-xuat {
      background-color: #fff7ed !important;
    }

    .pc-table tbody tr.pc-row-xuat:hover {
      background-color: #fed7aa !important;
    }

    .pc-table tbody tr.pc-row-nhap {
      background-color: #ffffff;
    }

    .pc-table tbody tr.pc-row-nhap:hover {
      background-color: #f1f5f9;
    }

    .pc-table tbody tr.pc-row-active {
      background: #fef08a !important;
    }

    .pc-cell-cyan {
      background: #d8f3ff !important;
      padding: 0 !important;
      min-width: 115px;
    }

    .pc-cell-edit {
      padding: 0 !important;
    }

    .pc-inp {
      width: 100%;
      height: 28px;
      border: 1.5px solid transparent;
      background: transparent;
      padding: 2px 6px;
      font-size: 12.5px;
      font-family: inherit;
      color: #0f172a;
      outline: none;
      box-sizing: border-box;
      transition: all 0.12s;
    }

    .pc-inp:focus {
      background: #ffffff !important;
      border-color: #2563eb !important;
      box-shadow: inset 0 0 0 1px #2563eb;
    }

    .pc-inp.pc-saving {
      background: #fef3c7 !important;
    }

    .pc-inp.pc-saved {
      background: #dcfce7 !important;
      color: #166534 !important;
      font-weight: 800;
    }

    .pc-truncate {
      max-width: 185px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .pc-actions-cell {
      white-space: nowrap;
      text-align: center;
      padding: 2px 4px !important;
    }

    .pc-act-btn {
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      color: #0f172a;
      border-radius: 5px;
      padding: 2px 6px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      margin: 0 1px;
    }

    .pc-act-btn:hover {
      background: #e2e8f0;
    }

    .pc-del-btn {
      border: 1px solid #fecaca;
      background: #fff1f2;
      color: #dc2626;
      border-radius: 4px;
      padding: 1px 4px;
      font-size: 11px;
      line-height: 1.2;
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .pc-del-btn:hover {
      background: #fee2e2;
      border-color: #f87171;
      transform: scale(1.1);
    }

    /* FLOATING AUTOCOMPLETE FOR DESKTOP TABLE CELLS */
    .pc-ac-floating {
      position: fixed;
      z-index: 5000;
      background: #ffffff;
      border: 1.5px solid #2563eb;
      border-radius: 10px;
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.25);
      max-height: 240px;
      width: 310px;
      overflow-y: auto;
      display: none;
    }

    .pc-ac-floating.open {
      display: block;
    }
  </style>
</head>
<body>

  <div class="toast-container" id="toastContainer"></div>
  <div class="pc-ac-floating" id="pcAcFloating"></div>

  <!-- FULL-SCREEN LOGIN OVERLAY FOR MULTI-USER ACCESS -->
  <div class="login-overlay" id="loginOverlay">
    <div class="login-card">
      <div class="login-logo-box">VS</div>
      <div style="text-align:center;margin-bottom:18px;">
        <div style="font-size:18px;font-weight:800;color:#0f172a;">VIETSUN TMS MOBILE</div>
        <div style="font-size:12.5px;color:#64748b;margin-top:3px;">Đăng nhập bằng tài khoản TMS của bạn</div>
      </div>

      <div class="login-error" id="loginErrorBox"></div>

      <form onsubmit="submitLoginScreen(event)">
        <div class="form-group" style="margin-bottom:12px;">
          <label>👤 Tài khoản TMS (Username)</label>
          <input
            type="text"
            class="form-control"
            id="loginUsername"
            placeholder="Tên tài khoản điều độ..."
            autocomplete="username"
            required
          />
        </div>

        <div class="form-group" style="margin-bottom:14px;">
          <label>🔑 Mật khẩu (Password)</label>
          <input
            type="password"
            class="form-control"
            id="loginPassword"
            placeholder="Nhập mật khẩu..."
            autocomplete="current-password"
            required
          />
        </div>

        <button type="submit" class="btn-primary-lg" id="btnLoginSubmit" style="width:100%;padding:12px;">
          🔐 ĐĂNG NHẬP HỆ THỐNG
        </button>
      </form>
    </div>
  </div>

  <!-- HEADER -->
  <header class="app-header">
    <div class="header-top">
      <div class="brand">
        <div class="brand-logo">VS</div>
        <div class="brand-text">
          <div class="brand-title">VIETSUN TMS</div>
          <div class="brand-sub">
            <span class="status-dot" id="connDot"></span>
            <span id="syncTimeLabel">Đang tải...</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <button class="icon-btn" id="btnInstallPwa" style="display:none;" onclick="installPWA()" title="Cài App ra màn hình chính">
          📲 Cài
        </button>
        <button class="icon-btn" onclick="openAccountModal()" title="Tài khoản & Cài đặt">
          👤 <span id="userLabel">TK</span>
        </button>
        <button class="icon-btn" onclick="refreshData(true)" title="Tải lại dữ liệu mới nhất">
          🔄 Mới
        </button>
      </div>
    </div>

    <div class="search-row">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input
          type="search"
          id="searchInput"
          placeholder="Tìm Số Cont, Biển số xe, Khách hàng, Kho, Seal..."
          autocomplete="off"
          oninput="onSearchInput()"
        />
        <button class="clear-search" id="btnClearSearch" onclick="clearSearch()">✕</button>
      </div>
      <button class="filter-toggle-btn" id="btnToggleDesktop" onclick="toggleDesktopMode()" title="Chuyển chế độ Danh sách Bảng PC (nhập nhanh ĐVVT & Số xe) / Thẻ Mobile">
        <span id="lblDesktopMode">🖥️ Bảng PC</span>
      </button>
      <button class="filter-toggle-btn" onclick="toggleAdvancedFilters()">
        <span>⚡ Lọc</span>
        <span class="filter-badge" id="activeFilterCount">1</span>
      </button>
    </div>
  </header>

  <!-- QUICK DATE & FILTER SECTION -->
  <section class="filter-section">
    <!-- Quick Date Chips -->
    <div class="quick-tabs" id="quickDateTabs">
      <button class="chip active" data-date-mode="today" onclick="setQuickDate('today')" id="chipToday">📅 Hôm nay</button>
      <button class="chip" data-date-mode="tomorrow" onclick="setQuickDate('tomorrow')" id="chipTomorrow">⏭️ Ngày mai</button>
      <button class="chip" data-date-mode="d2" onclick="setQuickDate('d2')" id="chipD2">📆 Ngày D+2</button>
      <button class="chip" data-date-mode="d3" onclick="setQuickDate('d3')" id="chipD3">📆 Ngày D+3</button>
      <button class="chip" data-date-mode="yesterday" onclick="setQuickDate('yesterday')" id="chipYesterday">⏮️ Hôm qua</button>
      <label class="chip" data-date-mode="single" id="chipSingleDate" onclick="openQuickSingleDatePicker(event)">
        <span id="chipSingleDateLabel">🎯 Chọn ngày</span>
        <input type="date" id="quickSingleDatePicker" onchange="onQuickSingleDatePick(this.value)" oninput="onQuickSingleDatePick(this.value)" onclick="event.stopPropagation(); try{if(this.showPicker)this.showPicker();}catch(_){}" />
      </label>
      <button class="chip" data-date-mode="3days" onclick="setQuickDate('3days')">📆 ±3 ngày</button>
      <button class="chip" data-date-mode="unset" onclick="setQuickDate('unset')">⚠️ Chưa xếp ngày</button>
      <button class="chip" data-date-mode="all" onclick="setQuickDate('all')">🗂️ Tất cả</button>
    </div>

    <!-- Collapsible Advanced Filters -->
    <div class="advanced-filters" id="advancedFilters">
      <div class="filter-grid">
        <div class="filter-field">
          <label>Lọc theo loại ngày</label>
          <select id="filterDateType" onchange="applyFilters()">
            <option value="ngayTraHang">Ngày Đóng / Giao hàng</option>
            <option value="ngayTauChayPo">Ngày Tàu chạy / cập</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Trạng thái điều xe</label>
          <select id="filterTruckStatus" onchange="applyFilters()">
            <option value="ALL">-- Tất cả trạng thái --</option>
            <option value="NO_TRUCK">⚠️ Chưa có số xe</option>
            <option value="HAS_TRUCK">✅ Đã gán số xe</option>
            <option value="NO_CONT">⚠️ Chưa có số Cont</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Từ ngày (Hoặc chọn 1 ngày)</label>
          <input type="date" id="filterFromDate" onchange="onCustomDateChange('from')" />
        </div>
        <div class="filter-field">
          <label>Đến ngày</label>
          <input type="date" id="filterToDate" onchange="onCustomDateChange('to')" />
        </div>
        <div class="filter-field">
          <label>Đơn vị vận tải (Vendor)</label>
          <select id="filterVendor" onchange="applyFilters()">
            <option value="ALL">-- Tất cả ĐVVT --</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Tỉnh / Khu vực</label>
          <select id="filterProvince" onchange="applyFilters()">
            <option value="ALL">-- Tất cả Tỉnh --</option>
          </select>
        </div>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-top:4px;">
        <span style="font-size:11.5px; color:#64748b;" id="serverDateInfo">Mốc dữ liệu API: Hôm nay</span>
        <div style="display:flex; gap:6px;">
          <button class="mini-btn" onclick="resetAllFilters()">Đặt lại bộ lọc</button>
          <button class="mini-btn" style="background:#0284c7;color:#fff;border-color:#0284c7;" onclick="promptApiDate()">Đổi ngày gốc API</button>
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
      <div class="stat-lbl">Tổng Lô/Cont</div>
    </div>
    <div class="stat-card" id="stat2040">
      <div class="stat-val" style="color:#0284c7;" id="count2040">0 / 0</div>
      <div class="stat-lbl">Cont 20' / 40'</div>
    </div>
    <div class="stat-card" id="statHasTruck" onclick="setTruckQuickFilter('HAS_TRUCK')">
      <div class="stat-val" style="color:#16a34a;" id="countHasTruck">0</div>
      <div class="stat-lbl">Đã có xe</div>
    </div>
    <div class="stat-card" id="statNoTruck" onclick="setTruckQuickFilter('NO_TRUCK')">
      <div class="stat-val" style="color:#d97706;" id="countNoTruck">0</div>
      <div class="stat-lbl">Chưa có xe</div>
    </div>

    <!-- BULK ACTIONS (DESKTOP MODE - TAB HÀNG XUẤT IN PKL / TAB HÀNG NHẬP IN BB) -->
    <div class="desktop-bulk-ctrl" id="desktopBulkCtrl" style="display:none;">
      <!-- State 1: Dropdown Button -->
      <div class="bulk-dropdown-wrap" id="bulkDropdownWrap">
        <button type="button" class="btn-bulk-main" id="btnBulkPrintMain" onclick="toggleBulkDropdown(event)" title="In PKL hàng loạt theo ĐVVT">
          <span style="font-size:15px;">🖨️</span>
          <span id="lblBulkAction">In PKL hàng loạt</span>
          <span style="font-size:11px;margin-left:2px;">▾</span>
        </button>
        <div class="bulk-dropdown-pop" id="bulkDropdownPop">
          <div class="bulk-opt-item" onclick="selectBulkCarrier('VIETSUN')">
            <span class="bulk-opt-icon">🚛</span>
            <div class="bulk-opt-info">
              <span class="bulk-opt-title">VIETSUN</span>
              <span class="bulk-opt-desc">Xe nhà Vietsun</span>
            </div>
          </div>
          <div class="bulk-opt-item" onclick="selectBulkCarrier('VENDOR')">
            <span class="bulk-opt-icon">🚚</span>
            <div class="bulk-opt-info">
              <span class="bulk-opt-title">VENDOR</span>
              <span class="bulk-opt-desc">Các đội xe còn lại</span>
            </div>
          </div>
        </div>
      </div>

      <!-- State 2: Active Filter & Print All Bar (Tab Hàng Xuất) -->
      <div class="bulk-active-bar" id="bulkActiveBar" style="display:none;">
        <div class="bulk-tag-carrier" id="bulkTagCarrier" onclick="toggleBulkDropdown(event)" title="Bấm đổi lọc ĐVVT">
          <span id="bulkCarrierIcon">🚛</span>
          <span id="bulkCarrierText">VIETSUN</span>
          <span style="font-size:10px;margin-left:2px;">▾</span>
        </div>
        <button type="button" class="btn-bulk-execute" id="btnBulkExecute" onclick="executeBulkPrint()" title="Bấm để in tất cả phiếu đã chọn">
          <span style="font-size:15px;">🖨️</span>
          <span>Print All (<strong id="bulkSelectedCount">0</strong>)</span>
        </button>
        <button type="button" class="btn-bulk-exit" onclick="exitBulkPrintMode()" title="Hủy & trở về bảng bình thường">✕ Hủy</button>
      </div>

      <!-- State 3: Direct Print Biên Bản Button (Tab Hàng Nhập - Thực thi in luôn không cần sổ ĐVVT) -->
      <div class="bulk-active-bar" id="bulkNhapDirectBar" style="display:none;border-color:#0d9488;box-shadow:0 2px 8px rgba(13,148,136,0.18);">
        <button type="button" class="btn-bulk-exit" onclick="openBbTemplateManager()" style="background:#f0fdfa;color:#0f766e;border-color:#99f6e4;font-weight:800;" title="Quản lý & Chỉnh sửa trực quan các mẫu Biên Bản theo Kho Nhận">
          ⚙️ Mẫu BB (<span id="bbTplCountBadge">2</span>)
        </button>
        <button type="button" class="btn-bulk-execute" id="btnBulkNhapPrint" onclick="executeBulkPrint()" style="background:linear-gradient(135deg, #0f766e 0%, #0d9488 100%);" title="In ngay các Biên Bản đã tick chọn (Tự nhận diện Form theo Kho Nhận)">
          <span style="font-size:15px;">🖨️</span>
          <span>In Biên Bản (<strong id="bulkNhapSelectedCount">0</strong>)</span>
        </button>
        <button type="button" class="btn-bulk-exit" id="btnBulkNhapClear" onclick="clearAllBulkSelection()" style="display:none;" title="Bỏ tick tất cả">✕ Bỏ chọn</button>
      </div>
    </div>
  </div>

  <!-- MAIN SHIPMENT LIST -->
  <main class="list-container" id="poListContainer">
    <div class="state-box">
      <div class="spinner"></div>
      <div style="font-weight:700; color:#334155;">Đang tải dữ liệu điều độ...</div>
      <div style="font-size:12px; margin-top:4px; color:#64748b;">Đang đồng bộ dữ liệu hệ thống...</div>
    </div>
  </main>

  <!-- FLOATING 20' / 40' CONTAINER SIZE FILTER DOCK (BOTTOM RIGHT) -->
  <div class="floating-size-dock" id="floatingSizeDock">
    <button type="button" class="fab-size-btn" id="fabSize20" onclick="toggleContSizeFilter('20')" title="Bấm để lọc Cont 20' (Bấm lại để bỏ lọc)">
      <span class="fab-size-lbl">20'</span>
      <span class="fab-size-cnt" id="fabCount20">0</span>
    </button>
    <button type="button" class="fab-size-btn" id="fabSize40" onclick="toggleContSizeFilter('40')" title="Bấm để lọc Cont 40' (Bấm lại để bỏ lọc)">
      <span class="fab-size-lbl">40'</span>
      <span class="fab-size-cnt" id="fabCount40">0</span>
    </button>
  </div>

  <!-- BOTTOM NAVIGATION (XUAT / NHAP SWITCHER) -->
  <nav class="bottom-nav">
    <button class="nav-item active" data-xn="ALL" onclick="setXuatNhapFilter('ALL')">
      <span class="nav-icon">📋</span>
      <span>Tất cả (<span id="navCountAll">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="X" onclick="setXuatNhapFilter('X')">
      <span class="nav-icon">📤</span>
      <span>Hàng Xuất (<span id="navCountX">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="N" onclick="setXuatNhapFilter('N')">
      <span class="nav-icon">📥</span>
      <span>Hàng Nhập (<span id="navCountN">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="NO_TRUCK" onclick="setXuatNhapFilter('NO_TRUCK')">
      <span class="nav-icon">🚛</span>
      <span>Chờ điều xe (<span id="navCountWait">0</span>)</span>
    </button>
  </nav>

  <!-- EDIT MODAL (BOTTOM SHEET) -->
  <div class="modal-backdrop" id="editModal" onclick="if(event.target===this) closeEditModal()">
    <div class="bottom-sheet">
      <div class="sheet-header">
        <div class="sheet-title">
          <span id="modalBadgeType" class="badge badge-xuat">XUẤT</span>
          <span id="modalTitleText">Cập nhật Lô hàng #210165</span>
        </div>
        <button class="sheet-close" onclick="closeEditModal()">✕</button>
      </div>

      <div class="sheet-body">
        <div class="info-banner">
          <div class="info-banner-title" id="modalCustomerName">Cty Dynaplast</div>
          <div id="modalRouteSub" style="color:#475569;">Tàu: VIETSUN DYNAMIC (1236N) • Chủ hàng: LOG</div>
        </div>

        <form id="editPoForm" onsubmit="saveAllChanges(event)">
          <input type="hidden" id="editIdPo" />
          <input type="hidden" id="editCodePo" />
          <input type="hidden" id="editXuatNhapPo" />

          <div class="form-section-title">
            <span>1. Điều Vận & Phương Tiện</span>
            <span style="font-size:11px; color:#64748b; font-weight:600;">Tự động lưu khi đổi hoặc bấm Lưu</span>
          </div>

          <div class="form-grid">
            <!-- NGAY GIAO / DONG HANG -->
            <div class="form-group full">
              <label>
                <span id="lblNgayTraHang">📅 Ngày Đóng / Giao Hàng</span>
                <span id="status_ngayTraHang" style="font-size:11px;"></span>
              </label>
              <input
                type="date"
                class="form-control"
                id="inp_ngayTraHang"
                onchange="onFieldChanged('ngayTraHang')"
              />
              <div class="quick-date-btns">
                <button type="button" class="mini-btn" onclick="setModalDate(0)">Hôm nay</button>
                <button type="button" class="mini-btn" onclick="setModalDate(1)">Ngày mai</button>
                <button type="button" class="mini-btn" onclick="setModalDate(2)">+2 ngày</button>
                <button type="button" class="mini-btn" style="color:#dc2626;" onclick="setModalDate(null)">Chưa xếp ngày</button>
              </div>
            </div>

            <!-- SO CONT -->
            <div class="form-group">
              <label>
                <span>📦 Số Container</span>
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
                <span>🔒 Số Seal (Chì)</span>
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
                <span>🚛 Số Xe Vận Tải</span>
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
                placeholder="Gõ biển số (VD: 61H...)"
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
                <span>🏢 Đơn Vị Vận Tải</span>
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
                placeholder="Chọn hoặc gõ Vendor..."
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
                  <div style="font-weight:800;" id="drvNameText">👨‍✈️ Tài xế: --</div>
                  <div style="font-size:11.5px; margin-top:2px;" id="drvSubText">📞 SĐT: --</div>
                </div>
                <a href="#" id="drvCallBtn" class="btn-card btn-call" style="padding:6px 10px; font-size:12px;">📞 Gọi TX</a>
              </div>
            </div>
          </div>

          <!-- MỤC 2 CHO HÀNG XUẤT: BÃI / CẢNG & KẾT HỢP -->
          <div id="secXuatPort">
            <div class="form-section-title">
              <span>2. Bãi / Cảng & Kết Hợp</span>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label><span>🏗️ Nơi lấy cont / rỗng</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_noiLayContPo"
                  placeholder="VD: VS, KH 20', KH 40'..."
                  onchange="onFieldChanged('noiLayContPo')"
                />
              </div>

              <div class="form-group">
                <label><span>⚓ Cảng đi / Hạ cont</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_cangDiPo"
                  placeholder="VD: BẾN NGHÉ"
                  onchange="onFieldChanged('cangDiPo')"
                />
              </div>

              <div class="form-group">
                <label><span>🚢 Cảng đến (Phía Bắc)</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_tinhXuatHangPo"
                  list="dlCangDen"
                  placeholder="VD: CLO, HPG, DAN..."
                  onchange="onFieldChanged('tinhXuatHangPo')"
                />
              </div>

              <div class="form-group">
                <label><span>🔄 Hình thức kết hợp</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_hinhThucKHopPo"
                  list="dlKetHop"
                  placeholder="VD: 80%, Kết hợp, Hạ rỗng"
                  onchange="onFieldChanged('hinhThucKHopPo')"
                />
              </div>

              <div class="form-group">
                <label>
                  <span>⏱️ Thời gian hạ cont</span>
                  <button type="button" class="mini-btn" style="padding:1px 5px;font-size:10px;" onclick="setPortTimeNow()">Giờ hiện tại</button>
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
          </div>

          <!-- MỤC 2 CHO HÀNG NHẬP: THÔNG TIN TÀU - CẢNG (LẤY TỪ TMS - DỮ LIỆU CỨNG KHÓA CHỈ XEM) -->
          <div id="secNhapVessel" style="display:none;">
            <div class="form-section-title">
              <span>2. Thông Tin Tàu & Cảng (TMS)</span>
              <span style="font-size:11px; color:#64748b; font-weight:600;">🔒 Dữ liệu cố định</span>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label><span>🚢 Tên Tàu (TÀU)</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_tauPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>

              <div class="form-group">
                <label><span>🧭 Số Chuyến (CHUYẾN)</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_chuyenPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>

              <div class="form-group">
                <label><span>⚓ Cảng cập / POD</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_cangDenPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>

              <div class="form-group">
                <label><span>📅 Ngày tàu chạy / cập</span></label>
                <input
                  type="date"
                  class="form-control"
                  id="inp_ngayTauChayPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>
            </div>
          </div>

          <div class="form-section-title">
            <span>3. Kho Hàng, Địa Chỉ & Liên Hệ</span>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label><span>🏭 Kho đóng / trả hàng</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_khoNhanHangPo"
                onchange="onFieldChanged('khoNhanHangPo')"
              />
            </div>

            <div class="form-group">
              <label><span>📍 Tỉnh / Thành phố</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_tinhNhanPo"
                list="dlTinh"
                onchange="onFieldChanged('tinhNhanPo')"
              />
            </div>

            <div class="form-group full">
              <label><span>🗺️ Địa chỉ chi tiết</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_diaChiNhanPo"
                onchange="onFieldChanged('diaChiNhanPo')"
              />
            </div>

            <div class="form-group full">
              <label><span>📞 Người liên hệ & SĐT</span></label>
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
        <button type="button" class="btn-secondary-lg" id="btnModalDelivery" onclick="copyCurrentPoToZalo(2)" title="Copy THÔNG TIN GIAO HÀNG (kèm Tài xế & SĐT)">
          🚛 Giao Hàng
        </button>
        <button type="button" class="btn-secondary-lg" id="btnModalTb" onclick="copyCurrentPoToZalo(1)" title="Copy THÔNG BÁO HÀNG ĐẾN / THÔNG TIN XE ĐÓNG HÀNG">
          💬 Copy TB
        </button>
        <button type="button" class="btn-secondary-lg" id="btnModalPkl" style="display:none;background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="openPklFromEditModal()" title="Xuất / In Phiếu PKL Vào Cổng">
          📄 Phiếu PKL
        </button>
        <button type="button" class="btn-secondary-lg" style="background:#fff1f2;color:#dc2626;border-color:#fecaca;" onclick="if(currentEditingPo) openDeleteBookingModal(currentEditingPo.idPo)" title="Xóa booking thừa">
          🗑️ Xóa
        </button>
        <button type="button" class="btn-primary-lg" id="btnSaveAll" onclick="saveAllChanges(event)">
          💾 Lưu TMS
        </button>
      </div>
    </div>
  </div>

  <!-- PKL (PACKING LIST) PREVIEW & PDF EXPORT MODAL -->
  <div class="modal-backdrop" id="pklModal" onclick="if(event.target===this) closePklModal()">
    <div class="bottom-sheet" style="max-height:94vh;">
      <div class="sheet-header">
        <div>
          <div class="sheet-title" id="pklModalTitle">📄 Phiếu PKL Hạ Cảng (Packing List)</div>
          <div id="pklModalSub" style="font-size:12px;color:#475569;margin-top:2px;">Tự động trích xuất trực tiếp từ dữ liệu Hàng Xuất TMS</div>
        </div>
        <button class="sheet-close" onclick="closePklModal()">✕</button>
      </div>
      <div class="sheet-body" style="padding:12px 14px;">
        <div id="pklStatusBanner" style="font-size:12.5px;font-weight:600;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;padding:8px 12px;border-radius:9px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
          <span id="pklStatusText">✅ Đã tạo phiếu PKL trực tiếp từ TMS!</span>
          <button type="button" class="mini-btn" style="background:#fff;color:#0369a1;border:1px solid #bae6fd;" onclick="fetchPklFromGoogleSheet()">🔄 Lấy từ Sheet PKL cũ</button>
        </div>
        <div style="width:100%;overflow:auto;border:1px solid #cbd5e1;border-radius:10px;background:#f8fafc;box-shadow:inset 0 2px 6px rgba(0,0,0,0.05);">
          <canvas id="pklCanvas" style="width:100%;height:auto;display:block;background:#fff;"></canvas>
        </div>
      </div>
      <div class="sheet-footer">
        <button type="button" class="btn-secondary-lg" style="background:#f3e8ff;color:#6b21a8;border-color:#d8b4fe;" onclick="sharePklPdf()">
          📤 Gửi Zalo / Chia sẻ
        </button>
        <button type="button" class="btn-secondary-lg" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="downloadPklPdf()">
          📥 Tải PDF
        </button>
        <button type="button" class="btn-primary-lg" onclick="printPklCanvas()">
          🖨️ In Phiếu PKL
        </button>
      </div>
    </div>
  </div>

  <!-- MODAL XÁC NHẬN XÓA BOOKING THỪA -->
  <div class="modal-backdrop" id="deleteConfirmModal" style="align-items:center;justify-content:center;z-index:9999;" onclick="if(event.target===this) closeDeleteModal()">
    <div style="background:#ffffff;border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,0.35);max-width:420px;width:92%;padding:22px;text-align:center;">
      <div style="font-size:38px;margin-bottom:6px;">🗑️</div>
      <div style="font-size:16px;font-weight:800;color:#dc2626;margin-bottom:8px;">XÁC NHẬN XÓA BOOKING THỪA</div>
      <div id="deleteModalDesc" style="font-size:13px;color:#334155;line-height:1.55;margin-bottom:14px;">
        Bạn có chắc muốn xóa booking thừa này không?
      </div>
      <div style="font-size:12.5px;color:#64748b;margin-bottom:8px;">
        Nhập chữ <b style="color:#dc2626;background:#fee2e2;padding:1px 6px;border-radius:4px;font-family:monospace;">ok</b> để mở khóa nút xóa:
      </div>
      <input type="text" id="deleteConfirmInput" class="form-control" placeholder="Gõ: ok"
        autocomplete="off" autocapitalize="off" spellcheck="false"
        style="text-align:center;font-weight:800;font-size:16px;max-width:160px;margin:0 auto 16px;border:2px solid #cbd5e1;border-radius:8px;"
        oninput="checkDeleteOkInput(this)" onkeydown="if(event.key==='Enter') executeDeleteBooking()" />
      <div style="display:flex;gap:10px;justify-content:center;">
        <button type="button" class="btn-card" style="padding:8px 16px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:8px;font-weight:600;cursor:pointer;" onclick="closeDeleteModal()">Hủy bỏ</button>
        <button type="button" id="btnConfirmDelete" style="padding:8px 18px;background:#dc2626;color:#ffffff;border:none;border-radius:8px;font-weight:700;opacity:0.35;cursor:not-allowed;transition:all 0.15s ease;" disabled onclick="executeDeleteBooking()">Xác nhận xóa</button>
      </div>
    </div>
  </div>

  <!-- MODAL QUẢN LÝ & SOẠN THẢO TRỰC QUAN MẪU BIÊN BẢN (BB TEMPLATE MANAGER) -->
  <div class="modal-backdrop" id="bbTemplateModal" style="align-items:center;justify-content:center;z-index:9998;" onclick="if(event.target===this) closeBbTemplateManager()">
    <div class="bb-mgr-box">
      <div class="bb-mgr-header">
        <div>
          <div style="font-size:16px;font-weight:800;">⚙️ Quản Lý & Thiết Kế Mẫu Biên Bản Theo Kho Nhận (Visual Editor)</div>
          <div style="font-size:12px;opacity:0.92;margin-top:2px;">Tự động nhận diện Form khi in hàng loạt theo từ khóa KHO NHẬN • Hỗ trợ chỉnh sửa trực tiếp trên giấy A4 hoặc Copy-Paste từ Word</div>
        </div>
        <button type="button" class="sheet-close" style="background:rgba(255,255,255,0.2);color:#fff;" onclick="closeBbTemplateManager()">✕</button>
      </div>

      <div class="bb-mgr-body">
        <!-- Cột trái: Danh sách Mẫu BB -->
        <div class="bb-mgr-sidebar">
          <div style="padding:10px;border-bottom:1px solid #cbd5e1;display:flex;flex-direction:column;gap:6px;">
            <button type="button" class="btn-bulk-execute" style="width:100%;justify-content:center;background:linear-gradient(135deg,#1d4ed8,#2563eb);" onclick="triggerBbWordDocxUpload(true)">
              📄 Tạo Form Từ File Word (.docx)
            </button>
            <button type="button" class="btn-bulk-execute" style="width:100%;justify-content:center;background:linear-gradient(135deg,#0f766e,#0d9488);" onclick="createNewBbTemplate()">
              ➕ Tạo Form Kho Mới (Mẫu trống)
            </button>
            <button type="button" class="bb-tool-btn" style="width:100%;justify-content:center;padding:6px;" onclick="cloneCurrentBbTemplate()">
              📋 Nhân bản từ Form đang chọn
            </button>
            <input type="file" id="bbWordDocxInput" accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="display:none;" onchange="handleBbWordDocxSelected(this)" />
          </div>
          <div class="bb-mgr-list" id="bbTemplateSidebarList">
            <!-- Populated dynamically -->
          </div>
        </div>

        <!-- Cột phải: Cấu hình Từ khóa + Thanh công cụ Word + Trang giấy A4 -->
        <div class="bb-mgr-main">
          <!-- Hàng 1: Tên Form, Nhãn nút, Khách Hàng (TMS), Kho Nhận (TMS) -->
          <div class="bb-mgr-meta-bar">
            <div class="bb-meta-field" style="width:195px;">
              <label>Tên Mẫu Biên Bản</label>
              <input type="text" id="bbTplName" class="bb-meta-inp" placeholder="VD: Form Kho 4 Oranges" />
            </div>
            <div class="bb-meta-field" style="width:105px;">
              <label>Nhãn nút bảng</label>
              <input type="text" id="bbTplBadge" class="bb-meta-inp" placeholder="VD: 4Oranges" maxlength="16" />
            </div>
            <div class="bb-meta-field" style="flex:1;min-width:210px;">
              <label>Khách Hàng từ TMS (Gõ để tìm • Để trống = Mọi KH)</label>
              <div class="bb-ac-wrap">
                <input type="text" id="bbTplCustomerKeywords" class="bb-meta-inp" style="width:100%;" autocomplete="off"
                  placeholder="Gõ tìm tên Khách hàng (VD: Á Châu)..."
                  onfocus="openBbMetaAc('cust')"
                  oninput="openBbMetaAc('cust')" />
                <div class="bb-ac-dropdown" id="bbAcCustDropdown"></div>
              </div>
            </div>
            <div class="bb-meta-field" style="flex:1.15;min-width:230px;">
              <label>Từ khóa KHO NHẬN từ TMS (Gõ để tìm • Cách dấu phẩy)</label>
              <div class="bb-ac-wrap">
                <input type="text" id="bbTplKeywords" class="bb-meta-inp" style="width:100%;" autocomplete="off"
                  placeholder="Gõ tìm tên Kho nhận (VD: 4 Oranges)..."
                  onfocus="openBbMetaAc('kho')"
                  oninput="openBbMetaAc('kho')" />
                <div class="bb-ac-dropdown" id="bbAcKhoDropdown"></div>
              </div>
            </div>
          </div>

          <!-- Hàng 2: Thanh công cụ định dạng & Chèn Biến Tự Động -->
          <div class="bb-mgr-toolbar">
            <button type="button" class="bb-tool-btn" style="background:#eff6ff;color:#1d4ed8;border-color:#93c5fd;" onclick="triggerBbWordDocxUpload(false)" title="Tải file Word (.docx) đè vào mẫu đang mở">📄 Nhập từ Word (.docx)</button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('bold')" title="In đậm (Ctrl+B)"><b>B</b></button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('italic')" title="In nghiêng (Ctrl+I)"><i>I</i></button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('underline')" title="Gạch chân (Ctrl+U)"><u>U</u></button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('justifyLeft')" title="Căn trái">⬅️</button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('justifyCenter')" title="Căn giữa">↔️</button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('justifyRight')" title="Căn phải">➡️</button>
            <button type="button" class="bb-tool-btn" onclick="triggerBbLogoUpload()" title="Tải ảnh Logo từ máy tính chèn vào phiếu">🖼️ Chèn/Đổi Logo</button>
            <input type="file" id="bbLogoFileInput" accept="image/*" style="display:none;" onchange="handleBbLogoFileSelected(this)" />
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); insertBbSampleTable()" title="Chèn khung bảng hàng hóa">📊 +Bảng</button>

            <span style="color:#94a3b8;margin:0 2px;">|</span>
            <span style="font-size:11px;font-weight:800;color:#475569;">CHÈN BIẾN TỰ ĐỘNG:</span>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{SO_CONT}}')" title="Tự thay bằng Số Container">📦 {{SO_CONT}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{SO_SEAL}}')" title="Tự thay bằng Số Seal">🔒 {{SO_SEAL}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{NGAY}}')" title="Ngày giao (DD)">📅 {{NGAY}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{THANG}}')" title="Tháng giao (MM)">📅 {{THANG}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{NAM}}')" title="Năm giao (YYYY)">📅 {{NAM}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{KHO_NHAN}}')" title="Tên Kho Nhận">🏭 {{KHO_NHAN}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{DIA_CHI}}')" title="Địa chỉ giao + Tỉnh">📍 {{DIA_CHI}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{SO_XE}}')" title="Biển số xe">🚛 {{SO_XE}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{TRONG_LUONG}}')" title="Trọng lượng (tấn)">⚖️ {{TRONG_LUONG}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{HANG_HOA}}')" title="Tên hàng hóa">📋 {{HANG_HOA}}</button>
          </div>

          <!-- Hàng 3: Khung Trang Giấy A4 Trực Quan -->
          <div class="bb-mgr-canvas-wrap">
            <div id="bbVisualEditorCanvas" class="bb-a4-editor" contenteditable="true" spellcheck="false"></div>
          </div>

          <!-- Footer: Nút Xem Thử, In Thử, Khôi phục, Xóa, Lưu -->
          <div class="bb-mgr-footer">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <button type="button" class="bb-tool-btn" id="btnBbTogglePreview" onclick="toggleBbEditorLivePreview()" style="padding:7px 12px;background:#eff6ff;color:#1d4ed8;border-color:#93c5fd;">
                👁️ Xem thử với dữ liệu thật
              </button>
              <button type="button" class="bb-tool-btn" onclick="testPrintCurrentBbTemplate()" style="padding:7px 12px;">
                🖨️ In thử mẫu này
              </button>
              <button type="button" class="bb-tool-btn" id="btnBbRestoreDefault" onclick="restoreDefaultBbTemplate()" style="padding:7px 12px;color:#b45309;border-color:#fcd34d;background:#fffbeb;">
                ↺ Khôi phục mẫu gốc
              </button>
              <button type="button" class="bb-tool-btn" id="btnBbDeleteTpl" onclick="deleteCurrentBbTemplate()" style="padding:7px 12px;color:#dc2626;border-color:#fca5a5;background:#fef2f2;">
                🗑️ Xóa Form này
              </button>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <button type="button" class="btn-bulk-exit" onclick="closeBbTemplateManager()" style="padding:8px 16px;">Đóng</button>
              <button type="button" class="btn-bulk-execute" onclick="saveCurrentBbTemplate()" style="padding:8px 20px;background:linear-gradient(135deg,#0f766e,#0d9488);">
                💾 Lưu Mẫu Biên Bản
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ACCOUNT / SETTINGS MODAL -->
  <div class="modal-backdrop" id="accountModal" onclick="if(event.target===this) closeAccountModal()">
    <div class="bottom-sheet">
      <div class="sheet-header">
        <div class="sheet-title">🔐 Tài Khoản & Kết Nối Vietsun TMS</div>
        <button class="sheet-close" onclick="closeAccountModal()">✕</button>
      </div>
      <div class="sheet-body">
        <div class="form-group">
          <label>Tài khoản đăng nhập (Username)</label>
          <input type="text" class="form-control" id="cfgUsername" value="" placeholder="Nhập tài khoản điều độ" />
        </div>
        <div class="form-group">
          <label>Mật khẩu (Password)</label>
          <input type="password" class="form-control" id="cfgPassword" value="" placeholder="••••••••" />
        </div>
        <div class="form-group">
          <label>Chuỗi phiên làm việc (userString)</label>
          <input type="text" class="form-control" id="cfgUserString" value="" placeholder="Tự động đồng bộ từ máy chủ" style="font-size:11.5px; font-family:monospace;" />
        </div>
        <div class="form-group">
          <label>Ngày mốc lấy dữ liệu API (return_json.php?ngay=)</label>
          <input type="date" class="form-control" id="cfgApiDate" />
        </div>
        <div class="info-banner" style="margin-top:10px;background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <strong>🚛 Đồng bộ Đội xe TMS & Vendor (Google Sheet):</strong>
            <button type="button" class="mini-btn" style="background:#16a34a;color:#fff;border:none;" onclick="syncLiveDriverDatabase(true)">↻ Đồng bộ ngay</button>
          </div>
          <div id="driverSyncStatusText" style="margin-top:4px;font-size:12px;">
            Tự động cập nhật Đội xe VIETSUN từ TMS & Đội xe Vendor từ Google Sheet mỗi lần mở App.
          </div>
          <div style="margin-top:4px;font-size:11.5px;">
            📄 <a href="https://docs.google.com/spreadsheets/d/13ndQsSmBsKlHjIRQR0S0MO5BPqqL6J4CECBAYfQi1oo/edit?gid=1046738475#gid=1046738475" target="_blank" style="color:#15803d;font-weight:700;text-decoration:underline;">Mở bảng Google Sheet Đội xe Vendor</a>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
            <a href="/admin" target="_blank" style="background:#1e3a8a;color:#fff;padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:700;text-decoration:none;">⚙️ Quản lý User (/admin)</a>
            <a href="/vendor" target="_blank" style="background:#0284c7;color:#fff;padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:700;text-decoration:none;">🏢 Cổng Vendor (/vendor)</a>
            <a href="/driver" target="_blank" style="background:#16a34a;color:#fff;padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:700;text-decoration:none;">🚛 App Tài xế (/driver)</a>
          </div>
        </div>
        <div class="info-banner" style="margin-top:10px;">
          <strong>Hướng dẫn cài App trên Điện thoại:</strong><br/>
          • <strong>Android (Chrome):</strong> Bấm nút <strong>"📲 Cài App"</strong> trên thanh tiêu đề.<br/>
          • <strong>iPhone (Safari):</strong> Bấm nút <strong>Chia sẻ (⬆️)</strong> ➔ <em>"Thêm vào MH chính"</em>.
        </div>
      </div>
      <div class="sheet-footer">
        <button type="button" class="btn-secondary-lg" style="color:#dc2626;border-color:#fecaca;background:#fef2f2;" onclick="logoutAccount()">🚪 Đăng xuất / Đổi TK</button>
        <button type="button" class="btn-primary-lg" onclick="saveAccountSettings()">✅ Lưu & Đồng bộ</button>
      </div>
    </div>
  </div>

  <!-- AUTOCOMPLETE DATALISTS -->
  <datalist id="dlSoXe"></datalist>
  <datalist id="dlVendor"></datalist>
  <datalist id="dlTinh"></datalist>
  <datalist id="dlCangDen">
    <option value="CLO"></option>
    <option value="HPG"></option>
    <option value="DAN"></option>
  </datalist>
  <datalist id="dlKetHop">
    <option value="80%"></option>
    <option value="100%"></option>
    <option value="Kết hợp"></option>
    <option value="Hạ rỗng"></option>
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

    // Database Tài xế, SĐT, CCCD, GPLX Đội xe VIETSUN từ TMS
    const VIETSUN_DRIVER_DB = {
      "50H91309":{"cccd":"079097000590  Nơi cấp: Cục QLHC & TTXH","gplx":"790152274557 Nơi cấp:SỞ GTVT","name":"HUỲNH THANH TÙNG","phone":"0356601380"},
      "50H67772":{"cccd":"075082015319  Nơi cấp: Cục QLHC & TTXH","gplx":"740113002170 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN PHƯƠNG","phone":"0931249725"},
      "50H91554":{"cccd":"044082006183  Nơi cấp: Cục QLHC & TTXH","gplx":"790105007335 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN TUẤN","phone":"0378857982"},
      "50E52975":{"cccd":"038085016388  Nơi cấp: Cục QLHC & TTXH","gplx":"790152206084 Nơi cấp:SỞ GTVT","name":"NGUYỄN TRỌNG TRƯỜNG","phone":"0974533635"},
      "50H10121":{"cccd":"037089000509  Nơi cấp: Cục QLHC & TTXH","gplx":"790144799134 Nơi cấp:SỞ GTVT","name":"PHAN VĂN HUYNH","phone":"0987437351"},
      "50H76073":{"cccd":"075092005970  Nơi cấp: Cục QLHC & TTXH","gplx":"790219058674 Nơi cấp:BỘ CÔNG AN","name":"NGUYỄN VĂN THANH","phone":"0914210965"},
      "50H10152":{"cccd":"017084008865  Nơi cấp: Cục QLHC & TTXH","gplx":"310162850107 Nơi cấp:sở GTVT","name":"QUẢN VĂN NGỌC","phone":"0975075027"},
      "50H05689":{"cccd":"094086003131  Nơi cấp: Cục QLHC & TTXH","gplx":"740114002180 Nơi cấp:SỞ GTVT","name":"NGÔ MINH TRỌNG","phone":"0985955580"},
      "50H10035":{"cccd":"075089026187  Nơi cấp: Cục QLHC & TTXH","gplx":"750130023874 Nơi cấp:Sở GTVT","name":"TRẦN TRÍ TÀI","phone":"0919480367"},
      "50H05455":{"cccd":"035074010129  Nơi cấp: Cục QLHC & TTXH","gplx":"790146894437 Nơi cấp:SỞ GTVT","name":"NGUYỄN THÁI CƠ","phone":"0986192693"},
      "50H68309":{"cccd":"094085000613  Nơi cấp: Cục QLHC & TTXH","gplx":"790133745151 Nơi cấp:SỞ GTVT","name":"LÊ VĂN NGOAN","phone":"0795884070"},
      "50E14550":{"cccd":"080093000650  Nơi cấp: Cục QLHC & TTXH","gplx":"790159130760 Nơi cấp:SỞ GTVT","name":"LÊ HOÀNG ÂN","phone":"0966464917"},
      "50E14009":{"cccd":"087201009730  Nơi cấp: Cục QLHC & TTXH","gplx":"790206185040 Nơi cấp:bộ công an","name":"NGUYỄN MINH TRIẾT","phone":"0364334382"},
      "50E53597":{"cccd":"077089005508  Nơi cấp: Cục QLHC & TTXH","gplx":"790110015510 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN TÀI","phone":"0335946252"},
      "50E52920":{"cccd":"080093006093  Nơi cấp: Cục QLHC & TTXH","gplx":"790160094374 Nơi cấp:SỞ GTVT","name":"ĐỖ MINH PHỤNG","phone":"0967799542"},
      "50H10017":{"cccd":"080081000387  Nơi cấp: Cục QLHC & TTXH","gplx":"790156088701 Nơi cấp:BỘ GTVT","name":"NGUYỄN VƯƠNG QUỐC","phone":"0936736236"},
      "50H68333":{"cccd":"038085020371  Nơi cấp: Cục QLHC & TTXH","gplx":"790159210491 Nơi cấp:SỞ GTVT","name":"HOÀNG VĂN LỰC","phone":"0902856068"},
      "50H10011":{"cccd":"079201021731  Nơi cấp: Cục QLHC & TTXH","gplx":"790204030139 Nơi cấp:SỞ GTVT","name":"PHAN HOÀNG HÙNG","phone":"0707231297"},
      "50E15217":{"cccd":"080074000416  Nơi cấp: Cục QLHC & TTXH","gplx":"790966004092 Nơi cấp:SỞ GTVT","name":"HUỲNH THANH TÂM","phone":"0937373731"},
      "50H76816":{"cccd":"079078002279  Nơi cấp: Cục QLHC & TTXH","gplx":"790152151977 Nơi cấp:SỞ GTVT","name":"PHAN VĂN TÚ","phone":"0904334484"},
      "50H90600":{"cccd":"079074022039  Nơi cấp: Cục QLHC & TTXH","gplx":"790009228687 Nơi cấp:SỞ GTVT","name":"DƯƠNG HIẾU HÒA","phone":"0796449644"},
      "51D03966":{"cccd":"075082009103  Nơi cấp: Cục QLHC & TTXH","gplx":"750053003841 Nơi cấp:Cục Cảnh sát giao thông","name":"TRANG MINH HẬU","phone":"0979577801"},
      "50H68200":{"cccd":"042080010369  Nơi cấp: Cục QLHC & TTXH","gplx":"790106009000 Nơi cấp:SỞ GTVT","name":"NGUYỄN VIẾT XUÂN","phone":"0979483434"},
      "50H90144":{"cccd":"075091024017  Nơi cấp: Cục QLHC & TTXH","gplx":"790171923030 Nơi cấp:BCA","name":"PHẠM VĂN BÌNH","phone":"0909092471"},
      "50H10208":{"cccd":"030062009674  Nơi cấp: Cục QLHC & TTXH","gplx":"750100001394 Nơi cấp:SỞ GTVT","name":"NGUYỄN ĐỨC TRIỆU","phone":"0938025734"},
      "50H91424":{"cccd":"079077005704  Nơi cấp: Cục QLHC & TTXH","gplx":"790135410947 Nơi cấp:sở GTVT","name":"ĐỖ THÀNH HƯNG","phone":"0916597944"},
      "50E48301":{"cccd":"084083014543  Nơi cấp: Cục QLHC & TTXH","gplx":"790101006837 Nơi cấp:SỞ GTVT","name":"NGUYỄN HỮU TƯỜNG","phone":"0933943550"},
      "50H05535":{"cccd":"054088010647  Nơi cấp: Cục QLHC & TTXH","gplx":"540111812711 Nơi cấp:SỞ GTVT","name":"LÂM TỬ TY","phone":"0961191339"},
      "50E14112":{"cccd":"074078009227  Nơi cấp: Cục QLHC & TTXH","gplx":"740044000054 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN XUÂN","phone":"0964337309"},
      "50H10224":{"cccd":"093098001120  Nơi cấp: Cục QLHC & TTXH","gplx":"790163115562 Nơi cấp:BCA","name":"NGUYỄN VĂN HẬU","phone":"0938234301"},
      "50H76406":{"cccd":"038082023809  Nơi cấp: Cục QLHC & TTXH","gplx":"740086003914 Nơi cấp:sở GTVT","name":"ĐỖ VĂN TUẤN","phone":"0906534154"},
      "50E54493":{"cccd":"075090016892  Nơi cấp: Cục QLHC & TTXH","gplx":"790137778023 Nơi cấp:SỞ GTVT","name":"NGUYỄN TUẤN ANH","phone":"0979222556"},
      "50H91213":{"cccd":"091085002627  Nơi cấp: Cục QLHC & TTXH","gplx":"790102029798 Nơi cấp:SỞ GTVT","name":"HUỲNH VĂN SỬ","phone":"0908727855"},
      "50H91917":{"cccd":"040086020171  Nơi cấp: Cục QLHC & TTXH","gplx":"750087003158 Nơi cấp:SỞ GTVT","name":"TRẦN DUY BÌNH","phone":"0973607075"},
      "50E54092":{"cccd":"046083015082  Nơi cấp: Cục QLHC & TTXH","gplx":"790107001117 Nơi cấp:SỞ GTVT","name":"PHAN THẾ MINH SƠN","phone":"0889243552"},
      "50E52617":{"cccd":"084096005671  Nơi cấp: Cục QLHC & TTXH","gplx":"840164002355 Nơi cấp:SỞ GTVT","name":"HUỲNH VĂN TẤN ĐẠT","phone":"0974891088"},
      "50H05658":{"cccd":"042090011523  Nơi cấp: Cục QLHC & TTXH","gplx":"310176002813 Nơi cấp:SỞ GTVT","name":"HOÀNG HỮU LONG","phone":"0971772771"},
      "50E14029":{"cccd":"038081001711  Nơi cấp: Cục QLHC & TTXH","gplx":"790168165517 Nơi cấp:sở GTVT","name":"HÀ VĂN LUẬT","phone":"0961535776"},
      "50H05808":{"cccd":"040081035050  Nơi cấp: Cục QLHC & TTXH","gplx":"420098000382 Nơi cấp:SỞ GTVT","name":"NGUYỄN THÀNH TRUNG","phone":"0973029550"},
      "50H76226":{"cccd":"079089006263  Nơi cấp: Cục QLHC & TTXH","gplx":"790115230930 Nơi cấp:SỞ GTVT","name":"ĐOÀN TÍN THÀNH","phone":"0903379977"},
      "50H76364":{"cccd":"094085015886  Nơi cấp: cuc QL HC & TTXH","gplx":"310150041834 Nơi cấp:Sở GTVT","name":"LÊ MINH HIỂN","phone":"0902484393"},
      "50E54447":{"cccd":"052088013362  Nơi cấp: Cục QLHC & TTXH","gplx":"790148929197 Nơi cấp:SỞ GTVT","name":"LÊ TRỌNG NHŨ","phone":"0985468603"},
      "50H76548":{"cccd":"084093004347  Nơi cấp: Cục QLHC & TTXH","gplx":"790185182027 Nơi cấp:SỞ GTVT","name":"HUỲNH VĂN CHÍ NGUYỆN","phone":"0327348324"},
      "50H67574":{"cccd":"087084012933  Nơi cấp: Cục QLHC & TTXH","gplx":"790135004317 Nơi cấp:BCA","name":"HUỲNH VĂN TÂM","phone":"0934044395"},
      "50E13972":{"cccd":"084083011298  Nơi cấp: Cục QLHC & TTXH","gplx":"790154124087 Nơi cấp:SỞ GTVT","name":"TRƯƠNG HOÀNG TÂM","phone":"0933436783"},
      "50H76100":{"cccd":"038087010115  Nơi cấp: Cục QLHC & TTXH","gplx":"790111019706 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN TUẤN","phone":"0932636166"},
      "50H75037":{"cccd":"038090000713  Nơi cấp: Cục QLHC & TTXH","gplx":"790159083060 Nơi cấp:sở GTVT","name":"HÀ VĂN HUỲNH","phone":"0962595828"},
      "50H91136":{"cccd":"075084012309  Nơi cấp: Cục QLHC & TTXH","gplx":"740103000597 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN LÂM","phone":"0907451526"},
      "50H91077":{"cccd":"084078005598  Nơi cấp: Cục QLHC & TTXH","gplx":"790133026546 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN VINH","phone":"0939534848"},
      "50H75631":{"cccd":"040094035935  Nơi cấp: Cục QLHC & TTXH","gplx":"790158168344 Nơi cấp:SỞ GTVT","name":"PHẠM VĂN HOÀI","phone":"0987130446"},
      "50H76822":{"cccd":"092068002539  Nơi cấp: Cục QLHC & TTXH","gplx":"740100001386 Nơi cấp:SỞ GTVT","name":"ĐOÀN VĂN KHÔI","phone":"07074042920"},
      "50H91025":{"cccd":"096085012314  Nơi cấp: Cục QLHC & TTXH","gplx":"960104002204 Nơi cấp:SỞ GTVT","name":"LÊ CHÍ BÉ","phone":"0949325326"},
      "50H91065":{"cccd":"084087008097  Nơi cấp: Cục QLHC & TTXH","gplx":"790120002234 Nơi cấp:sở GTVT","name":"LÊ VĂN LỌ","phone":"0931781902"},
      "50H90495":{"cccd":"038087003353  Nơi cấp: Cục QLHC & TTXH","gplx":"790135394702 Nơi cấp:sở GTVT","name":"HÀ VĂN NAM","phone":"0962156417"},
      "50H76011":{"cccd":"052086008837  Nơi cấp: Cục QLHC & TTXH","gplx":"520098005953 Nơi cấp:sở GTVT","name":"BÙI QUỐC BẢO","phone":"0984771750"},
      "50E53813":{"cccd":"070090005753  Nơi cấp: Cục QLHC & TTXH","gplx":"700140007614 Nơi cấp:SỞ GTVT","name":"BÙI THẾ PHONG","phone":"0919412472"},
      "50H75942":{"cccd":"038079021380  Nơi cấp: Cục QLHC & TTXH","gplx":"790156178864 Nơi cấp:sở GTVT","name":"HÀ VĂN QUÂN","phone":"0968858255"},
      "50H10039":{"cccd":"046086012096  Nơi cấp: Cục QLHC & TTXH","gplx":"460070000841 Nơi cấp:BCA","name":"TRẦN QUỐC THANH","phone":"0344275975"},
      "50H67809":{"cccd":"019064000138  Nơi cấp: Cục QLHC & TTXH","gplx":"740109000223 Nơi cấp:SỞ GTVT","name":"ĐẶNG VĂN GIANG","phone":"0986779164"},
      "50H10106":{"cccd":"051093007367  Nơi cấp: Cục QLHC & TTXH","gplx":"790144963131 Nơi cấp:SỞ GTVT","name":"ĐOÀN TRUNG ĐIỆP","phone":"0966469601"},
      "50H75270":{"cccd":"051086015636  Nơi cấp: Cục QLHC & TTXH","gplx":"790116256113 Nơi cấp:sở GTVT","name":"TRẦN VĂN CHIẾN","phone":"0908491943"},
      "50H05806":{"cccd":"042072019464  Nơi cấp: Cục QLHC & TTXH","gplx":"740138003479 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN HÀ","phone":"0976404108"},
      "50H10056":{"cccd":"080084015762  Nơi cấp: Cục QLHC & TTXH","gplx":"800072000197 Nơi cấp:BCA","name":"LÊ BẢO QUỐC","phone":"0946070660"},
      "50H05545":{"cccd":"040086034549  Nơi cấp: Cục QLHC & TTXH","gplx":"400138010216 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN QUANG","phone":"0919960266"},
      "50E52957":{"cccd":"084080010693  Nơi cấp: Cục QLHC & TTXH","gplx":"790118015806 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN CHIẾN","phone":"0933893216"},
      "50H75167":{"cccd":"084088003267  Nơi cấp: Cục QLHC & TTXH","gplx":"790121231718 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN KHOA","phone":"0937773854"},
      "50H76863":{"cccd":"093094003991  Nơi cấp: Cục QLHC & TTXH","gplx":"930172002271 Nơi cấp:SỞ GTVT","name":"PHẠM TUẤN KHANH","phone":"0832385658"},
      "50H90045":{"cccd":"079072041145  Nơi cấp: Cục QLHC & TTXH","gplx":"790109010227 Nơi cấp:SỞ GTVT","name":"HỒ NGỌC THANH","phone":"0908067972"},
      "50E53620":{"cccd":"086094004600  Nơi cấp: Cục QLHC & TTXH","gplx":"790140976232 Nơi cấp:SỞ GTVT","name":"TRƯƠNG NGỌC ĐÔNG","phone":"0707200832"},
      "50H76757":{"cccd":"096079008895  Nơi cấp: Cục QLHC & TTXH","gplx":"790101005351 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN GIÀU","phone":"0943998978"},
      "50H91113":{"cccd":"093091008061  Nơi cấp: Cục QLHC & TTXH","gplx":"790161177742 Nơi cấp:sở GTVT","name":"NGUYỄN KIM TƯ","phone":"0933989655"},
      "50H90944":{"cccd":"044073008800  Nơi cấp: Cục QLHC & TTXH","gplx":"740107007134 Nơi cấp:SỞ GTVT","name":"ĐỖ HỒNG TUYẾN","phone":"0918569137"},
      "50H91272":{"cccd":"038078006397  Nơi cấp: Cục QLHC & TTXH","gplx":"790106000982 Nơi cấp:sở GTVT","name":"VŨ QUANG TUÂN","phone":"0937823360"},
      "50H10031":{"cccd":"087078013673  Nơi cấp: Cục QLHC & TTXH","gplx":"790064018256 Nơi cấp:Sở GTVT","name":"NGUYỄN VĨNH PHƯỚC","phone":"0939454501"},
      "50H68711":{"cccd":"","gplx":"","name":"NGUYỄN VƯƠNG QUỐC","phone":"0936736236"}
    };

    // Database Đội xe VENDOR từ Google Sheet (Tự động đồng bộ live mỗi khi mở App)
    const INITIAL_VENDOR_DRIVER_DB = {"50E65375":{"vendor":"AN THANH PHAT","name":"LƯƠNG THẾ DUY","phone":"0888352428","cccd":"040093028954","gplx":"400131030660"},"50E65951":{"vendor":"AN THANH PHAT","name":"HUỲNH VĂN HOÀI","phone":"0356615616","cccd":"058085005988","gplx":"740116000256"},"50F09323":{"vendor":"BAO NAM PHAT","name":"CAO THANH HÀ","phone":"0918959143","cccd":"044090013803","gplx":"440128001907"},"61H13200":{"vendor":"DUY LINH PHAT","name":"PHẠM NGỌC SÁNG","phone":"0865150567","cccd":"042094002527","gplx":"740149000715"},"50E34934":{"vendor":"DUY LINH PHAT","name":"PHẠM TRUNG THÀNH","phone":"0962678328","cccd":"042091005641","gplx":"Đang Cập Nhật"},"50E36061":{"vendor":"DUY LINH PHAT","name":"TRẦN HOÀI NAM","phone":"0979345291","cccd":"042086007623","gplx":"Đang Cập Nhật"},"50E65255":{"vendor":"HOANG LONG PHAT","name":"TRƯƠNG VĂN VƯƠNG","phone":"0377111572","cccd":"040095005341","gplx":"310187036636"},"50E65490":{"vendor":"HOANG LONG PHAT","name":"VÕ QUANG TRÀ","phone":"0962199166","cccd":"44090000239","gplx":"740155014797"},"50E32542":{"vendor":"HOANG LONG PHAT","name":"HOÀNG VĂN HẠNH","phone":"0377526846","cccd":"40084026157","gplx":"790140907355"},"50H90438":{"vendor":"HOANG LONG PHAT","name":"TRẦN KIM HOÀNG","phone":"0962655237","cccd":"40091018132","gplx":"750123004257"},"50E47317":{"vendor":"HOANG LONG PHAT","name":"NGUYỄN XUÂN THÀNH","phone":"0973101711","cccd":"40083012978","gplx":"310160865442"},"61H16670":{"vendor":"HOANG LONG PHAT","name":"HOÀNG VĂN HIẾU","phone":"0988339961","cccd":"40082015118","gplx":"Đang Cập Nhật"},"61H17260":{"vendor":"HOANG LONG PHAT","name":"HOÀNG TÙNG HIÊN","phone":"0908520359","cccd":"40081035719","gplx":"790148939862"},"50E39861":{"vendor":"HOANG LONG PHAT","name":"BÙI VĂN VỸ","phone":"0869308684","cccd":"40084024922","gplx":"400149029045"},"50E48367":{"vendor":"HOANG LONG PHAT","name":"LÊ ĐĂNG HIẾU","phone":"0988753861","cccd":"40090027025","gplx":"740151016368"},"51C12474":{"vendor":"LONG DAT","name":"PHẠM TUẤN PHONG","phone":"0901473423","cccd":"075078015607","gplx":"Đang Cập Nhật"},"50E81550":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN TRỌNG TUẤN","phone":"0988062493","cccd":"042084009898","gplx":"740163007170"},"61H11280":{"vendor":"NHAT SON","name":"NÔNG THÀNH NHƠN","phone":"0979193442","cccd":"019078012146","gplx":"740102000956"},"51C50602":{"vendor":"NHAT SON","name":"NÔNG MẠNH HÙNG","phone":"0938465862","cccd":"019091010032","gplx":"790131013629"},"50E31341":{"vendor":"NHAT SON","name":"LÊ ĐỨC KHÁNH","phone":"0976713778","cccd":"042091006497","gplx":"790140962813"},"61H00804":{"vendor":"NHAT SON","name":"NGUYỄN MINH HẢI","phone":"0971416122","cccd":"075086010901","gplx":"660118809593"},"50H76338":{"vendor":"NHAT SON","name":"NGUYỄN VĂN TÁM","phone":"0989411427","cccd":"038086031466","gplx":"790100004928"},"50H08207":{"vendor":"TAN HOANG QUANG","name":"VŨ ĐÌNH QUANG","phone":"0941119990","cccd":"042074000729","gplx":"640152780236"},"50E44057":{"vendor":"AN THANH PHAT","name":"VŨ ĐÌNH ĐỨC","phone":"0967594486","cccd":"079070032340","gplx":"790126011573"},"50E64009":{"vendor":"NGOC SANG","name":"HOÀNG LIÊN SƠN","phone":"0976044156","cccd":"40083031314","gplx":"709103005326"},"61H02908":{"vendor":"NGOC SANG","name":"HOÀNG LIÊN SƠN","phone":"0976044156","cccd":"40083031314","gplx":"709103005326"},"61H18983":{"vendor":"NGOC SANG","name":"BIỆN VĂN SỸ","phone":"0984225866","cccd":"42089019684","gplx":"420117000487"},"61H18956":{"vendor":"NGOC SANG","name":"NGUYỄN TIẾN NAM","phone":"0967676191","cccd":"66088007336","gplx":"660098801837"},"50E46727":{"vendor":"NGOC SANG","name":"TRẦN TRUNG ANH","phone":"0365766107","cccd":"40095005664","gplx":"790186088997"},"51C92758":{"vendor":"TRANCO","name":"PHẠM XUÂN THÀNH","phone":"0919920606","cccd":"34078006055","gplx":"790108009813"},"50E64511":{"vendor":"TRANCO","name":"PHAN THANH HẢI","phone":"0936713738","cccd":"094091002766","gplx":"790151084638"},"51D49674":{"vendor":"TRANCO","name":"CA CHÍ LINH","phone":"0945111033","cccd":"95085001585","gplx":"740116010976"},"50H44818":{"vendor":"TRANCO","name":"CA CHÍ LUÂN","phone":"0938525450","cccd":"95090005271","gplx":"790155186369"},"50H91518":{"vendor":"TRANCO","name":"PHẠM VĂN HIỂN","phone":"0932035841","cccd":"082088018626","gplx":"790176107446"},"51D14461":{"vendor":"TRANCO","name":"VÕ VĂN CỌP","phone":"0984932852","cccd":"094093008021","gplx":"940122000105"},"51D44303":{"vendor":"TRANCO","name":"NGUYỄN TẤN THÀNH","phone":"0355977727","cccd":"92093005581","gplx":"790140987564"},"50E76112":{"vendor":"PHUC AN","name":"MAI THANH TUẤN","phone":"0764153826","cccd":"51084007153","gplx":"790122230314"},"51C24203":{"vendor":"PHUC AN","name":"PHẠM VĂN THẮNG","phone":"0332609828","cccd":"37073011281","gplx":"790063005376"},"50H63178":{"vendor":"PHUC AN","name":"VỎ NGỌC CHÁNH","phone":"0909589764","cccd":"51085007848","gplx":"079117234884"},"61E03747":{"vendor":"NHAT SON","name":"NGUYỄN VĂN HƯNG","phone":"0972284449","cccd":"42081007010","gplx":"740108001690"},"61H20933":{"vendor":"NHAT SON","name":"CAO VĂN HIẾU","phone":"0703368267","cccd":"44093012924","gplx":"440149000565"},"61H12006":{"vendor":"NHAT SON","name":"NGUYỄN ĐÌNH LƯU","phone":"0962938537","cccd":"40093012668","gplx":"790136757755"},"61C41926":{"vendor":"NHAT SON","name":"CAO ĐỨC SƠN","phone":"0394423593","cccd":"44093004938","gplx":"440116002897"},"61C41405":{"vendor":"NHAT SON","name":"LƯƠNG VĂN LONG","phone":"0347068826","cccd":"038090043631","gplx":"790197296879"},"61H10617":{"vendor":"NHAT SON","name":"TRẦN BÁ NAM","phone":"0963871670","cccd":"4409601067","gplx":"790223014918"},"61H18984":{"vendor":"NHAT SON","name":"NGUYỄN VĂN ĐẠO","phone":"0869607757","cccd":"44096002563","gplx":"790174083630"},"50H71376":{"vendor":"TUAN HUNG","name":"PHAN HÒA VINH","phone":"0972609079","cccd":"66093011471","gplx":"660164016143"},"51C70823":{"vendor":"TUAN HUNG","name":"LÊ TRỌNG THẮNG","phone":"0373455800","cccd":"4008702238","gplx":"790111227327"},"50E60924":{"vendor":"VUONG PHAT","name":"TRẦN HỮU TINH","phone":"0339676007","cccd":"42075015762","gplx":"660101001809"},"50E64826":{"vendor":"VUONG PHAT","name":"TRẦN HẢI NAM","phone":"0862768559","cccd":"044098006799","gplx":"790213015459"},"50H67539":{"vendor":"VUONG PHAT","name":"CAO NGỌC LÊ","phone":"0366189811","cccd":"0440930310422","gplx":"750228035374"},"50E67400":{"vendor":"VUONG PHAT","name":"PHẠM VĂN KÝ","phone":"0975334334","cccd":"042088007658","gplx":"740156006283"},"61C15993":{"vendor":"PHUC TRUONG AN","name":"LÊ NGỌC NGÀ","phone":"0964298181","cccd":"66087103543","gplx":"640093006558"},"50E31803":{"vendor":"PHUC TRUONG AN","name":"NGUYỄN NGỌC CÔNG THÀNH","phone":"0971787679","cccd":"040084015706","gplx":"790178119715"},"61H00797":{"vendor":"PHUC TRUONG AN","name":"NGUYỄN VĂN TÙNG","phone":"0975200338","cccd":"042093019869","gplx":"420145004091"},"61E03709":{"vendor":"PHUC TRUONG AN","name":"ĐẶNG SỸ HẢI","phone":"0899335116","cccd":"040081024057","gplx":"740149013045"},"61H11121":{"vendor":"PHUC TRUONG AN","name":"PHẠM VĂN HUY","phone":"0784868678","cccd":"042086005529","gplx":"740103017148"},"50E88503":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN HUY","phone":"0975664423","cccd":"042083002403","gplx":"790117003088"},"50E90884":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN QUÝ","phone":"0968368665","cccd":"042075001054","gplx":"790104003725"},"38H03141":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN HẢI","phone":"0963236546","cccd":"042080012499","gplx":"420107000370"},"50E80187":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN THUẬN","phone":"0929919298","cccd":"070096003624","gplx":"680199000717"},"37H00745":{"vendor":"ANH DUC","name":"PHẠM VĂN TÀI","phone":"0367308298","cccd":"40091027080","gplx":"400121016449"},"50H46207":{"vendor":"GIA KHANG","name":"NGÔ NGỌC TRUNG","phone":"0387618457","cccd":"77094004769","gplx":"790164019380"},"50F09314":{"vendor":"THIEN PHUC PHAT","name":"NGUYỄN TRỌNG PHÚC","phone":"0901464743","cccd":"42085015705","gplx":"790108009499"},"50F07996":{"vendor":"THIEN PHUC PHAT","name":"NGUYỄN NGỌC HÀ","phone":"0333470682","cccd":"79078001175","gplx":"790026241939"},"50F07610":{"vendor":"THIEN PHUC PHAT","name":"HUỲNH THANH TÚ","phone":"0333376115","cccd":"52085019130","gplx":"790159221192"},"61H16720":{"vendor":"NGOC SANG","name":"VÕ HOÀNG VĂN","phone":"0971891362","cccd":"009409317243","gplx":"790171925001"},"50H47386":{"vendor":"GIA KHANG","name":"NGUYỄN VĂN LÂU","phone":"0938327078","cccd":"077092006919","gplx":"790144904325"},"50H70989":{"vendor":"GIA KHANG","name":"PHẠM PHÚ KIÊN","phone":"0932636387","cccd":"082097002570","gplx":"820154004437"},"50H47265":{"vendor":"GIA KHANG","name":"VĂN THANH HÙNG","phone":"0901455673","cccd":"060072000200","gplx":"740125002117"},"50H46817":{"vendor":"GIA KHANG","name":"TRẦN NGỌC TỨ","phone":"0782482587","cccd":"070090004773","gplx":"790120256107"},"50E77282":{"vendor":"GIA KHANG","name":"NGUYỄN CHÍ KIÊN","phone":"0788937807","cccd":"086086013500","gplx":"74013600710"},"50E32110":{"vendor":"TRANCO","name":"LÊ QUÝ SƠN","phone":"0989322411","cccd":"079083008027","gplx":"790105016353"},"50E89021":{"vendor":"HOANG LONG PHAT","name":"NGUYỄN QUỐC VIỆT","phone":"0975322746","cccd":"044094000961","gplx":"440169000527"},"50E47942":{"vendor":"MAI NHAT ANH","name":"ĐOÀN QUỐC XIÊM","phone":"0764850270","cccd":"","gplx":""},"50E46720":{"vendor":"MAI NHAT ANH","name":"NGUYỄN THÀNH ĐƯỢC","phone":"0947395620","cccd":"","gplx":""},"50E48450":{"vendor":"MAI NHAT ANH","name":"NGUYỄN CHÍ HƯỚNG","phone":"0777508000","cccd":"","gplx":""},"50E48074":{"vendor":"MAI NHAT ANH","name":"DƯƠNG ĐẠI HÀO","phone":"0968065464","cccd":"","gplx":""},"50H25079":{"vendor":"DUONG NGUYEN","name":"KIỀU GIA NGỌC","phone":"0876450913","cccd":"058089001180","gplx":"790137788347"},"50H46638":{"vendor":"GIA KHANG","name":"PHẠM THÀNH NHÂN","phone":"0939143102","cccd":"082096001875","gplx":"790155722714"},"50H16154":{"vendor":"GIA KHANG","name":"HUỲNH VĂN DUẨN","phone":"0777805689","cccd":"091085013755","gplx":"790106003330"},"50H24509":{"vendor":"DUONG NGUYEN","name":"NGUYỄN TRUNG BÌNH","phone":"0903631292","cccd":"079070003223","gplx":"790143789279"},"50H24419":{"vendor":"DUONG NGUYEN","name":"PHẠM VĂN THẢO","phone":"0962852047","cccd":"066091005627","gplx":"790126243611"},"61H07722":{"vendor":"NHAT SON","name":"VI THÁI BÌNH","phone":"0974411698","cccd":"038095014861","gplx":"790220087375"},"50E14850":{"vendor":"DUONG NGUYEN","name":"TRẦN QUANG BÉ","phone":"0986222784","cccd":"052068023764","gplx":"640132001962"},"50H91801":{"vendor":"DUONG NGUYEN","name":"NGUYỄN THÀNH HẢI","phone":"0902897928","cccd":"080085009060","gplx":"790176015376"},"50H08622":{"vendor":"DUONG NGUYEN","name":"DƯƠNG THANH TĨNH","phone":"0971213742","cccd":"054092009544","gplx":"790173082072"},"50H54182":{"vendor":"TRUC ANH","name":"LÊ VĂN DŨNG","phone":"0989667444","cccd":"044084012790","gplx":""},"50E14570":{"vendor":"DUONG NGUYEN","name":"KIỀU GIA NGÀ","phone":"0935584402","cccd":"058092005948","gplx":"790148026117"},"51D31338":{"vendor":"TRANCO","name":"NGÔ ANH KIỆT","phone":"0913683141","cccd":"079076014400","gplx":"790114015514"},"50E15514":{"vendor":"DUONG NGUYEN","name":"HÀ MINH HẬU","phone":"0388580088","cccd":"054085007992","gplx":""},"50H24580":{"vendor":"DUONG NGUYEN","name":"LÊ VĂN GIÀU","phone":"0933363774","cccd":"092087004728","gplx":"790117233047"},"51D35878":{"vendor":"DUONG NGUYEN","name":"HUỲNH NGỌC TOÀN","phone":"0394797939","cccd":"060098008301","gplx":"790196274755"},"50E14613":{"vendor":"DUONG NGUYEN","name":"DƯƠNG ĐÌNH KHOA","phone":"0327155050","cccd":"183664365","gplx":"790163163208"},"50H44778":{"vendor":"DUONG NGUYEN","name":"TRẦN NGỌC NGHĨA","phone":"0971463239","cccd":"052091008859","gplx":"520135002804"},"51C40249":{"vendor":"HIEU THUAN THANH","name":"TRẦN VĂN THÁI","phone":"0767599616","cccd":"040092039083","gplx":"790151215740"},"50H08397":{"vendor":"HIEU THUAN THANH","name":"VÕ PHÚ TIẾN","phone":"0347344739","cccd":"094091007316","gplx":"740160001740"},"50H75283":{"vendor":"TRANCO","name":"VÕ VĂN HIẾU","phone":"0909409079","cccd":"094085003898","gplx":"790172020367"},"50H68351":{"vendor":"TRANCO","name":"NGUYỄN NGỌC THANH","phone":"0764754733","cccd":"079074028029","gplx":"790094010189"},"50H75912":{"vendor":"TRANCO","name":"NGUYỄN VĂN HIẾU","phone":"0947906767","cccd":"087084013978","gplx":"830151003470"},"50H47324":{"vendor":"GIA KHANG","name":"CHÂU CHÍ THÀNH","phone":"0968018724","cccd":"084085005489","gplx":"790116022428"},"50H31241":{"vendor":"GIA KHANG","name":"TRẦN TUẤN THÀNH","phone":"0966921015","cccd":"082096007987","gplx":"790143967064"},"51C96068":{"vendor":"TRANCO","name":"NGUYỄN KHẮC DUY","phone":"0822678132","cccd":"087084017883","gplx":"790132399830"},"51C98819":{"vendor":"GIA BAO","name":"NGUYỄN MINH SĨ","phone":"0967952544","cccd":"064093007650","gplx":""},"50E40521":{"vendor":"TRANCO","name":"PHAN VĂN HẬU","phone":"0934038315","cccd":"082092009808","gplx":"790142806728"},"50H70423":{"vendor":"DUC HAI","name":"NGUYỄN THANH TỈNH","phone":"0926381168","cccd":"042085000345","gplx":"7901060033790"},"67H04431":{"vendor":"DANG HUNG","name":"ĐẶNG NGỌC NHU","phone":"0973439438","cccd":"","gplx":"740123005536"},"50E14923":{"vendor":"DUONG NGUYEN","name":"TRẦN ĐÌNH BẢO","phone":"0909736747","cccd":"079092025653","gplx":"790159190467"},"51C48540":{"vendor":"GIA KHANG","name":"LÊ BÉ LONG","phone":"0706336749","cccd":"092076003333","gplx":"790159088759"},"50H03656":{"vendor":"DUC HAI","name":"NGUYỄN VĂN KHIÊM","phone":"0908820932","cccd":"025075013027","gplx":"740117000239"},"50H75046":{"vendor":"TRANCO","name":"VÕ VĂN LÂM","phone":"0937905891","cccd":"086086013218","gplx":"790121015060"},"50F09325":{"vendor":"THIEN PHUC PHAT","name":"LÊ KHẮC HOẠT","phone":"0765876315","cccd":"038084012067","gplx":"790077010940"},"50H78733":{"vendor":"GIA BAO","name":"PHAN THANH THẢO","phone":"0966340732","cccd":"080093016662","gplx":""},"51D64911":{"vendor":"DUONG NGUYEN","name":"HUỲNH VĂN TUẤN","phone":"0336786549","cccd":"054099009789","gplx":"640225014484"},"50H78894":{"vendor":"GIA BAO","name":"NGUYỄN HOÀNG ANH","phone":"0938723620","cccd":"080091009105","gplx":""},"51C91569":{"vendor":"DUONG NGUYEN","name":"PHẠM LONG BÌNH","phone":"0932041042","cccd":"079086017688","gplx":"790114027629"},"51C13223":{"vendor":"DUONG NGUYEN","name":"HỒ VĂN SẼ","phone":"0908330553","cccd":"083088015808","gplx":"790067231856"},"50H43304":{"vendor":"DUONG NGUYEN","name":"NGUYỄN THÀNH TẤN","phone":"0363615064","cccd":"054091005727","gplx":"540140001708"}};

    // Unified live Driver Database (VIETSUN + VENDOR)
    const DRIVER_DB = {};
    const DRIVER_DOCS_BY_NAME = {};

    function initBaseDriverDb() {
      for (const [plate, info] of Object.entries(VIETSUN_DRIVER_DB)) {
        DRIVER_DB[plate] = {
          vendor: "VIETSUN",
          name: info.name || "",
          phone: info.phone || "",
          cccd: info.cccd || "",
          gplx: info.gplx || ""
        };
        if (info.name && (info.cccd || info.gplx)) {
          DRIVER_DOCS_BY_NAME[info.name.toUpperCase().trim()] = {
            cccd: info.cccd,
            gplx: info.gplx
          };
        }
      }
      for (const [plate, info] of Object.entries(INITIAL_VENDOR_DRIVER_DB)) {
        DRIVER_DB[plate] = {
          vendor: info.vendor || "VENDOR",
          name: info.name || "",
          phone: info.phone || "",
          cccd: info.cccd || "",
          gplx: info.gplx || ""
        };
      }
      // Load cached live updates from localStorage if available
      try {
        const cached = JSON.parse(localStorage.getItem("vs_live_driver_db") || "null");
        if (cached && typeof cached === "object") {
          for (const [plate, info] of Object.entries(cached)) {
            DRIVER_DB[plate] = Object.assign({}, DRIVER_DB[plate] || {}, info);
          }
        }
      } catch (_) {}
    }
    initBaseDriverDb();

    function getDriverForPlate(plate) {
      if (!plate) return null;
      const clean = plate.toUpperCase().replace(/[^A-Z0-9]/g, "");
      return DRIVER_DB[clean] || null;
    }

    const VENDOR_NAME_NORMALIZE = {
      NHATSON: "NHAT SON",
      NGOCSANG: "NGOC SANG",
      PHUCAN: "PHUC AN",
      TUANHUNG: "TUAN HUNG",
      VUONGPHAT: "VUONG PHAT",
      PHUCTRUONGAN: "PHUC TRUONG AN",
      GIAKHANG: "GIA KHANG",
      DUCANH: "ANH DUC",
      HOANGLONGPHAT: "HOANG LONG PHAT",
      DAILONGPHAT: "DAI LONG PHAT",
      BAONAMPHAT: "BAO NAM PHAT",
      ANTHANHPHAT: "AN THANH PHAT",
      DUYLINHPHAT: "DUY LINH PHAT",
      NHANBAOPHAT: "NHAN BAO PHAT",
      THIENPHUCPHAT: "THIEN PHUC PHAT",
      TANHOANGQUANG: "TAN HOANG QUANG",
      MAINHATANH: "MAI NHAT ANH",
      DUONGNGUYEN: "DUONG NGUYEN",
      GIABAO: "GIA BAO",
      DUCHAI: "DUC HAI",
      DANGHUNG: "DANG HUNG",
      TRUCANH: "TRUC ANH"
    };

    function normalizeVendorNameClient(raw) {
      const clean = (raw || "").toUpperCase().trim();
      const noSp = clean.replace(/\s+/g, "");
      if (VENDOR_NAME_NORMALIZE[noSp]) return VENDOR_NAME_NORMALIZE[noSp];
      // Also match against DEFAULT_VENDORS without spaces
      const matched = DEFAULT_VENDORS.find(v => v.replace(/\s+/g, "") === noSp);
      return matched || clean;
    }

    async function syncLiveDriverDatabase(showNotify = false) {
      const statusEl = document.getElementById("driverSyncStatusText");
      if (statusEl) statusEl.textContent = "⏳ Đang đồng bộ Đội xe từ TMS & Google Sheet...";
      let vsUpdated = 0;
      let vdUpdated = 0;

      try {
        const urlsToTry = [
          "/api/index.php?route=drivers",
          "/api/drivers",
          "?api=drivers"
        ];
        let data = null;
        for (const u of urlsToTry) {
          try {
            const r = await fetch(u, { cache: "no-store" });
            if (r.ok) {
              data = await r.json();
              if (data && data.ok) break;
            }
          } catch (_) {}
        }

        if (data && data.ok) {
          // 1. Update Vietsun Fleet from live TMS ajax-gvt.php (loai=26)
          if (Array.isArray(data.vietsun)) {
            data.vietsun.forEach(item => {
              const p = (item.plate || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
              if (!p) return;
              const prev = DRIVER_DB[p] || {};
              const nameUpper = (item.name || "").toUpperCase().trim();
              const docsByName = DRIVER_DOCS_BY_NAME[nameUpper] || {};
              DRIVER_DB[p] = {
                vendor: "VIETSUN",
                name: item.name || prev.name || "",
                phone: item.phone || prev.phone || "",
                cccd: prev.cccd || docsByName.cccd || "",
                gplx: prev.gplx || docsByName.gplx || ""
              };
              if (!DEFAULT_XE_VIETSUN.includes(p)) DEFAULT_XE_VIETSUN.push(p);
              vsUpdated++;
            });
          }

          // 2. Update Vendor Fleet from live Google Sheet
          if (Array.isArray(data.vendors)) {
            data.vendors.forEach(item => {
              const p = (item.plate || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
              if (!p) return;
              const normVendor = normalizeVendorNameClient(item.vendor);
              DRIVER_DB[p] = {
                vendor: normVendor || "VENDOR",
                name: item.name || "",
                phone: item.phone || "",
                cccd: item.cccd || "",
                gplx: item.gplx || ""
              };
              vdUpdated++;
            });
          }
        } else {
          // Fallback: Fetch Google Sheet CSV directly from browser
          const sheetCsvUrl = "https://docs.google.com/spreadsheets/d/13ndQsSmBsKlHjIRQR0S0MO5BPqqL6J4CECBAYfQi1oo/export?format=csv&gid=1046738475";
          const r = await fetch(sheetCsvUrl, { cache: "no-store" });
          if (r.ok) {
            const csvText = await r.text();
            const lines = csvText.split(/\r?\n/);
            for (let i = 1; i < lines.length; i++) {
              const line = lines[i].trim();
              if (!line) continue;
              const cols = line.split(",");
              if (cols.length < 2) continue;
              const p = (cols[1] || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
              if (!p) continue;
              const normVendor = normalizeVendorNameClient(cols[0]);
              DRIVER_DB[p] = {
                vendor: normVendor || "VENDOR",
                name: (cols[2] || "").trim(),
                phone: (cols[3] || "").trim(),
                cccd: (cols[4] || "").trim(),
                gplx: (cols[5] || "").trim()
              };
              vdUpdated++;
            }
          }
        }

        try {
          localStorage.setItem("vs_live_driver_db", JSON.stringify(DRIVER_DB));
        } catch (_) {}

        populateDynamicDropdowns();
        if (allShipments.length > 0) renderShipmentList();

        const totalPlates = Object.keys(DRIVER_DB).length;
        if (statusEl) {
          statusEl.innerHTML = `✅ Đã đồng bộ <b>${totalPlates} xe</b> (VIETSUN: ${vsUpdated || 74} xe • Vendor Google Sheet: ${vdUpdated || 118} xe) lúc ${new Date().toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" })}`;
        }
        if (showNotify) {
          showToast(`Đã đồng bộ ${totalPlates} xe & tài xế từ TMS và Google Sheet!`, "success");
        }
      } catch (err) {
        console.warn("Driver sync warning:", err);
        if (statusEl) {
          statusEl.textContent = `Đang dùng danh bạ lưu sẵn (${Object.keys(DRIVER_DB).length} xe VIETSUN & Vendor).`;
        }
      }
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

    const isExplicitlyLoggedIn = safeGetStorage("vs_auth_v2", "") === "1";

    let state = {
      isLoggedIn: isExplicitlyLoggedIn,
      username: isExplicitlyLoggedIn ? safeGetStorage("vs_username", "") : "",
      password: isExplicitlyLoggedIn ? safeGetStorage("vs_password", "") : "",
      userString: isExplicitlyLoggedIn ? safeGetStorage("vs_userString", "") : "",
      apiDate: getTodayISO(),
      dateMode: "today",          // today | tomorrow | yesterday | 3days | unset | all | custom
      dateType: "ngayTraHang",    // ngayTraHang | ngayTauChayPo
      fromDate: getTodayISO(),
      toDate: getTodayISO(),
      xuatNhap: "ALL",            // ALL | X | N | NO_TRUCK
      chuHang: "ALL",             // ALL | VSV | LOG | VSHPG...
      truckStatus: "ALL",         // ALL | NO_TRUCK | HAS_TRUCK | NO_CONT
      contSize: "ALL",            // ALL | 20 | 40
      vendor: "ALL",
      province: "ALL",
      searchQuery: "",
      viewMode: safeGetStorage("vs_view_mode", "cards"), // "cards" | "table"
      bulkMode: false,
      bulkCarrier: null, // "VIETSUN" | "VENDOR"
      bulkSelectedIds: new Set()
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
      if (!isoStr || isoStr === "2021-11-11" || isoStr === "1970-01-01") return "Chưa xếp ngày";
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
      el.innerHTML = `<span>${type === "success" ? "✅" : type === "warning" ? "⚠️" : "❌"}</span><span>${msg}</span>`;
      container.appendChild(el);
      setTimeout(() => {
        el.style.opacity = "0";
        el.style.transition = "opacity 0.25s";
        setTimeout(() => el.remove(), 250);
      }, 2600);
    }

    // ISO 6346 Container Check Digit Validator chuẩn quốc tế
    function validateContainerISO(contStr) {
      if (!contStr) return { valid: false, empty: true, message: "Chưa nhập" };
      const csc = contStr.toUpperCase().replace(/[^A-Z0-9]+/g, "");
      if (csc.length !== 11) {
        return { valid: false, empty: false, message: `Độ dài ${csc.length}/11 ký tự` };
      }
      // Bảng quy đổi ký tự chuẩn ISO 6346 (bỏ qua các bội số của 11: 11, 22, 33)
      const charMap = {
        A: 10, B: 12, C: 13, D: 14, E: 15, F: 16, G: 17, H: 18, I: 19, J: 20,
        K: 21, L: 23, M: 24, N: 25, O: 26, P: 27, Q: 28, R: 29, S: 30, T: 31,
        U: 32, V: 34, W: 35, X: 36, Y: 37, Z: 38
      };
      let sum = 0;
      for (let i = 0; i < 10; i++) {
        const ch = csc[i];
        let val = (ch >= '0' && ch <= '9') ? parseInt(ch, 10) : charMap[ch];
        if (val === undefined) {
          return { valid: false, empty: false, message: `Ký tự ${ch} không hợp lệ` };
        }
        sum += val * Math.pow(2, i);
      }
      const check = (sum % 11) % 10;
      const lastDigit = parseInt(csc.charAt(10), 10);
      if (check === lastDigit) {
        return { valid: true, empty: false, message: "Chuẩn ISO ✓" };
      } else {
        return { valid: false, empty: false, message: `Sai số cuối (đúng: ${check})` };
      }
    }

    // =========================================================================
    // 4. API COMMUNICATION (LOCAL API PROXY)
    // =========================================================================
    const loadedCycleSet = new Set();

    // Vietsun TMS return_json.php groups shipments by billing period: 26th of (M-1) -> 25th of M
    function getBillingCycleParam(isoStr) {
      if (!isoStr || typeof isoStr !== "string") isoStr = getTodayISO();
      const parts = isoStr.split("-").map(Number);
      if (parts.length !== 3 || isNaN(parts[0])) return getTodayISO();
      let [y, m, d] = parts;
      if (d >= 26) {
        m += 1;
        if (m > 12) {
          m = 1;
          y += 1;
        }
      }
      return `${y}-${String(m).padStart(2, "0")}-01`;
    }

    function normalizeShipmentRecord(item) {
      return {
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
        tinhXuatHangPo: decodeHtmlEntities(item.tinhXuatHangPo || "").trim(),
        soContPo: (item.soContPo || "").trim(),
        soSealPo: (item.soSealPo || "").trim(),
        soXePo: (item.soXePo || "").trim(),
        donViVcPo: (item.donViVcPo || "").trim(),
        chuHangPo: (item.chuHangPo || "").trim()
      };
    }

    async function fetchSingleCycleFromApi(cycleDateParam) {
      const urlsToTry = [
        `/api/index.php?route=return_json&ngay=${encodeURIComponent(cycleDateParam)}`,
        `/api/return_json?ngay=${encodeURIComponent(cycleDateParam)}`
      ];

      let lastErr = null;
      for (const url of urlsToTry) {
        try {
          const resp = await fetch(url, { cache: "no-store" });
          if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
          const text = await resp.text();
          const cleaned = text.trim();
          if (cleaned.startsWith("<")) throw new Error("Phản hồi HTML thay vì JSON");
          const data = JSON.parse(cleaned);
          if (Array.isArray(data)) return data;
        } catch (err) {
          lastErr = err;
        }
      }
      throw lastErr || new Error("Không thể kết nối API");
    }

    async function fetchShipmentsFromApi(ngayParam) {
      const baseIso = ngayParam || getTodayISO();
      // Always cover -10 days to +15 days across Vietsun's 25th-of-month cutoff (so D+1, D+2, D+3, etc. are always present)
      const cyclesToFetch = Array.from(new Set([
        getBillingCycleParam(addDaysISO(baseIso, -10)),
        getBillingCycleParam(baseIso),
        getBillingCycleParam(addDaysISO(baseIso, 15))
      ]));

      const results = await Promise.all(
        cyclesToFetch.map(c => fetchSingleCycleFromApi(c).catch(err => {
          console.warn("Cycle fetch warning:", c, err);
          return null;
        }))
      );

      const validArrays = results.filter(r => Array.isArray(r));
      if (validArrays.length === 0) {
        throw new Error("Không thể kết nối API");
      }

      loadedCycleSet.clear();
      cyclesToFetch.forEach((c, idx) => {
        if (Array.isArray(results[idx])) loadedCycleSet.add(c);
      });

      const mergedMap = new Map();
      validArrays.forEach(arr => {
        arr.forEach(item => {
          if (item && item.idPo) {
            mergedMap.set(String(item.idPo), item);
          }
        });
      });
      return Array.from(mergedMap.values());
    }

    async function ensureDateCyclesLoaded(fromIso, toIso) {
      const needed = new Set();
      if (fromIso) needed.add(getBillingCycleParam(fromIso));
      if (toIso) needed.add(getBillingCycleParam(toIso));
      const missing = Array.from(needed).filter(c => !loadedCycleSet.has(c));
      if (missing.length === 0) return;

      try {
        showToast(`⏳ Đang tải thêm dữ liệu kỳ ${missing.join(", ")}...`, "success");
        const extraArrays = await Promise.all(
          missing.map(c => fetchSingleCycleFromApi(c).catch(() => []))
        );
        const existingMap = new Map();
        allShipments.forEach(s => existingMap.set(String(s.idPo), s));

        let added = 0;
        extraArrays.forEach((arr, idx) => {
          if (Array.isArray(arr) && arr.length > 0) {
            loadedCycleSet.add(missing[idx]);
            arr.forEach(raw => {
              if (raw && raw.idPo && !existingMap.has(String(raw.idPo))) {
                existingMap.set(String(raw.idPo), normalizeShipmentRecord(raw));
                added++;
              }
            });
          }
        });

        if (added > 0) {
          allShipments = Array.from(existingMap.values());
          allShipments.sort((a, b) => Number(b.idPo) - Number(a.idPo));
          populateDynamicDropdowns();
          applyFilters();
          showToast(`Đã bổ sung ${added} lô hàng cho ngày đã chọn!`, "success");
        }
      } catch (e) {
        console.warn("Extra cycle load error:", e);
      }
    }

    async function postCrudUpdate(payloadObj) {
      const formBody = new URLSearchParams();
      for (const [k, v] of Object.entries(payloadObj)) {
        formBody.append(k, v);
      }

      // Luôn ưu tiên endpoint API Gateway trên máy chủ
      const urlsToTry = [
        "/api/index.php?route=index-crud",
        "/api/index-crud"
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
          const text = await resp.text();
          if (text.includes("Lỗi đăng nhập") || text.includes("<!DOCTYPE")) {
            throw new Error(text.includes("Lỗi đăng nhập") ? "Lỗi phiên đăng nhập TMS" : "Phản hồi không hợp lệ từ máy chủ");
          }
          return text;
        } catch (err) {
          lastErr = err;
        }
      }
      throw lastErr || new Error("Không thể gửi lệnh cập nhật");
    }

    // =========================================================================
    // 5. DATA LOADING & FILTERING
    // =========================================================================
    async function refreshData(showNotification = false) {
      const realToday = getTodayISO();
      // Tự động nhảy sang ngày hôm nay mới nếu qua đêm hoặc đang ở chế độ xem Hôm nay
      if (state.dateMode === "today" || !state.apiDate || state.apiDate < realToday) {
        state.apiDate = realToday;
        if (state.dateMode === "today") {
          state.fromDate = realToday;
          state.toDate = realToday;
          const elFrom = document.getElementById("filterFromDate");
          const elTo = document.getElementById("filterToDate");
          if (elFrom) elFrom.value = realToday;
          if (elTo) elTo.value = realToday;
        }
      }
      updateQuickDateChipLabels();

      const syncLbl = document.getElementById("syncTimeLabel");
      syncLbl.textContent = "Đang đồng bộ...";
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
          tinhXuatHangPo: decodeHtmlEntities(item.tinhXuatHangPo || "").trim(),
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
        syncLbl.textContent = `Cập nhật ${nowStr} (${allShipments.length} lô)`;
        document.getElementById("connDot").style.background = "#4ade80";

        populateDynamicDropdowns();
        applyFilters();
        if (showNotification) {
          showToast(`Đã tải ${allShipments.length} lô hàng từ hệ thống!`, "success");
          syncLiveDriverDatabase(false);
        }
      } catch (err) {
        console.error(err);
        document.getElementById("connDot").style.background = "#f59e0b";
        const cached = safeGetStorage("vs_cached_shipments", null);
        if (cached) {
          allShipments = JSON.parse(cached);
          syncLbl.textContent = `Bản lưu tạm (${allShipments.length} lô)`;
          populateDynamicDropdowns();
          applyFilters();
          showToast("Đang hiển thị dữ liệu lưu tạm trên máy", "warning");
        } else {
          syncLbl.textContent = "Lỗi kết nối";
          document.getElementById("poListContainer").innerHTML = `
            <div class="state-box">
              <div style="font-size:36px;margin-bottom:8px;">⚠️</div>
              <div style="font-weight:700;color:#0f172a;">Không thể tải dữ liệu trực tiếp</div>
              <div style="font-size:12.5px;margin-top:6px;">Hãy chạy file <b>Chay_App_TMS.bat</b> trên máy tính hoặc đưa file lên host Vietsun để tránh chặn CORS của trình duyệt.</div>
              <button class="btn-primary-lg" style="margin:14px auto 0;max-width:220px;" onclick="refreshData(true)">Thử lại</button>
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

      // Add all trucks from DRIVER_DB (both VIETSUN and Google Sheet Vendor trucks)
      for (const [plate, info] of Object.entries(DRIVER_DB)) {
        if (!plate) continue;
        const vName = (info.vendor || "").toUpperCase().trim();
        knownTruckMap.set(plate, { plate, vendor: vName || "VENDOR", count: 5 });
        xeSet.add(plate);
        if (vName) vendorSet.add(vName);
      }

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
        const rawDv = normalizeVendorNameClient(s.donViVcPo || "");
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
      filterVendor.innerHTML = `<option value="ALL">-- Tất cả ĐVVT --</option>` +
        Array.from(vendorSet).sort().map(v => `<option value="${v}">${v}</option>`).join("");
      filterVendor.value = curV;

      const filterProv = document.getElementById("filterProvince");
      const curP = state.province;
      filterProv.innerHTML = `<option value="ALL">-- Tất cả Tỉnh --</option>` +
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
        if (state.vendor !== "ALL" && normalizeVendorNameClient(item.donViVcPo) !== state.vendor) return false;
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

      // Build ChuHang (Đối tượng) Chips with real-time counts
      renderChuHangChips(afterXN);

      // 3. Filter by ChuHang (Đối tượng)
      const afterChuHang = afterXN.filter(item => {
        if (state.chuHang === "ALL") return true;
        return (item.chuHangPo || "KHÁC") === state.chuHang;
      });

      // Update Floating 20' / 40' Dock Counts & Active State
      let dockC20 = 0, dockC40 = 0;
      afterChuHang.forEach(item => {
        if (Number(item.cont20Po) > 0) dockC20++;
        if (Number(item.cont40Po) > 0) dockC40++;
      });
      const elFab20 = document.getElementById("fabSize20");
      const elFab40 = document.getElementById("fabSize40");
      const elCnt20 = document.getElementById("fabCount20");
      const elCnt40 = document.getElementById("fabCount40");
      if (elCnt20) elCnt20.textContent = dockC20;
      if (elCnt40) elCnt40.textContent = dockC40;
      if (elFab20) elFab20.classList.toggle("active-20", state.contSize === "20");
      if (elFab40) elFab40.classList.toggle("active-40", state.contSize === "40");

      // 4. Filter by Container Size (20' / 40')
      filteredShipments = afterChuHang.filter(item => {
        if (state.contSize === "20") return Number(item.cont20Po) > 0;
        if (state.contSize === "40") return Number(item.cont40Po) > 0;
        return true;
      });

      // Lọc theo đơn vị vận chuyển in hàng loạt (VIETSUN vs VENDOR: các đội còn lại)
      if (state.bulkMode && state.bulkCarrier) {
        filteredShipments = filteredShipments.filter(item => {
          const rawDv = normalizeVendorNameClient(item.donViVcPo || "");
          if (state.bulkCarrier === "VIETSUN") {
            return rawDv === "VIETSUN";
          } else if (state.bulkCarrier === "VENDOR") {
            if (rawDv === "VIETSUN") return false;
            const autoV = (knownTruckMap.get(item.soXePo) || {}).vendor;
            return !!rawDv || (autoV && autoV !== "VIETSUN");
          }
          return true;
        });
      }

      // Ưu tiên các dòng chưa có Đơn vị V/C và Số xe lên top đầu (cho cả hàng Nhập & Xuất)
      filteredShipments.sort((a, b) => {
        const getPriority = (item) => {
          const hasV = !!(item.donViVcPo && item.donViVcPo.trim());
          const hasX = !!(item.soXePo && item.soXePo.trim());
          if (!hasV && !hasX) return 0; // Chưa có cả ĐVVC lẫn Số xe -> Ưu tiên cao nhất
          if (!hasX || !hasV) return 1; // Thiếu 1 trong 2 -> Ưu tiên kế tiếp
          return 2;                     // Đã có đủ cả 2 -> Xếp sau
        };
        const pA = getPriority(a);
        const pB = getPriority(b);
        if (pA !== pB) return pA - pB;
        return Number(b.idPo) - Number(a.idPo);
      });

      // Update Summary Stats
      updateSummaryCards(filteredShipments);
      updateActiveFilterBadge();

      // Giới hạn số dòng vẽ ra DOM mỗi lượt là 150 dòng (Bảng PC) / 60 thẻ (Mobile)
      // để khi chọn mục "Tất cả" (3.000+ lô) trình duyệt vẫn mượt tức thì không tốn RAM
      renderLimit = state.viewMode === "table" ? 150 : 60;
      renderShipmentList();
      updateBulkControlsUI();
    }

    function applyViewModeUI() {
      const isTable = state.viewMode === "table";
      document.body.classList.toggle("desktop-table-mode", isTable);
      const lbl = document.getElementById("lblDesktopMode");
      const btn = document.getElementById("btnToggleDesktop");
      if (lbl) lbl.textContent = isTable ? "📱 Thẻ Mobile" : "🖥️ Bảng PC";
      if (btn) {
        btn.style.background = isTable ? "#dcfce7" : "#ffffff";
        btn.style.color = isTable ? "#15803d" : "#1e3a8a";
      }
      updateBulkControlsUI();
    }

    function toggleDesktopMode() {
      closePcAcFloating();
      state.viewMode = state.viewMode === "table" ? "cards" : "table";
      try {
        localStorage.setItem("vs_view_mode", state.viewMode);
      } catch (_) {}
      applyViewModeUI();
      renderLimit = state.viewMode === "table" ? 300 : 60;
      renderShipmentList();
      showToast(
        state.viewMode === "table"
          ? "🖥️ Đã chuyển sang chế độ Bảng PC (Gõ trực tiếp ĐVVT & Số xe, bấm Enter / Mũi tên ⬆️⬇️ để nhảy dòng)"
          : "📱 Đã chuyển về chế độ Thẻ Mobile",
        "success"
      );
    }

    function renderChuHangChips(listBeforeChuHang) {
      const counts = {};
      listBeforeChuHang.forEach(item => {
        const ch = item.chuHangPo || "KHÁC";
        counts[ch] = (counts[ch] || 0) + 1;
      });

      const sortedKeys = Object.keys(counts).sort((a, b) => counts[b] - counts[a]);
      const container = document.getElementById("chuHangTabs");

      let html = `
        <button class="chip chip-blue ${state.chuHang === "ALL" ? "active" : ""}" onclick="setChuHangFilter('ALL')">
          🏢 Tất cả đối tượng <span class="count">${listBeforeChuHang.length}</span>
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
      if (state.contSize !== "ALL") c++;
      if (state.vendor !== "ALL") c++;
      if (state.province !== "ALL") c++;
      if (state.searchQuery) c++;
      document.getElementById("activeFilterCount").textContent = c;
    }

    // =========================================================================
    // 6. RENDER SHIPMENT CARDS
    // =========================================================================
    function escapeAttr(val) {
      return String(val || "")
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    }

    function calcLuuFreeQuaHan(item) {
      if (item.xuatNhapPo === "X") {
        return {
          col1: item.tinhXuatHangPo || "",
          col2: item.noiLayContPo || "",
          col3: item.hinhThucKHopPo || ""
        };
      }
      // For Import (N): NGÀY TÀU CẬP = ngayTauChayPo + 2 days
      const tauIso = item.ngayTauChayPo;
      if (!tauIso || tauIso === "2021-11-11" || tauIso === "1970-01-01") {
        return { col1: "", col2: "7", col3: "" };
      }
      const tauCapIso = addDaysISO(tauIso, 2);
      const giaoIso = (!item.ngayTraHang || item.ngayTraHang === "2021-11-11" || item.ngayTraHang === "1970-01-01")
        ? getTodayISO()
        : item.ngayTraHang;
      const d1 = new Date(tauCapIso + "T00:00:00");
      const d2 = new Date(giaoIso + "T00:00:00");
      const diffDays = Math.round((d2 - d1) / 86400000) + 1;
      const ngayLuu = Math.max(1, diffDays);
      const free = 7;
      const quaHan = Math.max(0, ngayLuu - free);
      return {
        col1: String(ngayLuu),
        col2: String(free),
        col3: quaHan > 0 ? String(quaHan) : "0"
      };
    }

    function renderDesktopTableView(slice, hasMore) {
      // Tab Hàng Nhập (N) bỏ luôn cột Xóa, luôn hiển thị cột Tick chọn
      const isBulk = !!state.bulkMode || state.xuatNhap === "N";
      const allSelected = slice.length > 0 && slice.every(i => state.bulkSelectedIds && state.bulkSelectedIds.has(String(i.idPo)));

      const rowsHtml = slice.map((item, idx) => {
        const isXuat = item.xuatNhapPo === "X";
        const isUnsetDate = !item.ngayTraHang || item.ngayTraHang === "2021-11-11" || item.ngayTraHang === "1970-01-01";
        const ngayGiaoStr = isUnsetDate ? "" : formatIsoToVN(item.ngayTraHang);
        const drvInfo = getDriverForPlate(item.soXePo);
        const drvTooltip = drvInfo ? `Tài xế: ${drvInfo.name} - SĐT: ${drvInfo.phone || ""}` : "";
        const cangDenVal = isXuat ? (item.tinhXuatHangPo || item.cangDenPo || "") : (item.cangDenPo || "");
        const rowClass = isXuat ? "pc-row-xuat" : "pc-row-nhap";
        const isItemChecked = !!(state.bulkSelectedIds && state.bulkSelectedIds.has(String(item.idPo)));

        const actionColTd = isBulk ? `
          <td style="text-align:center;padding:2px 3px !important;width:40px;background:#eff6ff;" onclick="event.stopPropagation()">
            <input type="checkbox" class="chk-bulk-item" value="${item.idPo}" ${isItemChecked ? "checked" : ""} onchange="onToggleBulkItem('${item.idPo}', this.checked)" style="width:16px;height:16px;cursor:pointer;vertical-align:middle;" />
          </td>
        ` : `
          <td style="text-align:center;padding:2px 3px !important;width:36px;">
            <button type="button" class="pc-del-btn" onclick="openDeleteBookingModal('${item.idPo}')" title="Xóa booking thừa #${item.idPo}">🗑️</button>
          </td>
        `;

        return `
          <tr id="pc-row-${item.idPo}" data-row-idx="${idx}" class="${rowClass}" ondblclick="openEditModal('${item.idPo}')" title="Nhấp đúp chuột để mở chi tiết #${item.idPo}">
            ${actionColTd}
            <td style="text-align:center;white-space:nowrap;font-weight:700;color:#2563eb;cursor:pointer;" onclick="openEditModal('${item.idPo}')" title="Bấm mở chi tiết #${item.idPo}">${escapeAttr(item.chuyenPo)}</td>
            <td class="pc-truncate" title="${escapeAttr(item.tenKhachPo)}">${escapeAttr(item.tenKhachPo)}</td>
            <td class="pc-truncate" style="${!isXuat ? 'font-weight:700;color:#0f172a;' : ''}" title="${escapeAttr(item.khoNhanHangPo)}">${escapeAttr(item.khoNhanHangPo)}</td>
            <td class="pc-truncate" style="max-width:105px;" title="${escapeAttr(item.lienHeNhanPo)}">${escapeAttr(item.lienHeNhanPo)}</td>
            <td class="pc-truncate" style="max-width:160px;" title="${escapeAttr(item.diaChiNhanPo)}">${escapeAttr(item.diaChiNhanPo)}</td>
            <td style="text-align:center;white-space:nowrap;">${escapeAttr(item.tinhNhanPo)}</td>
            <td class="pc-cell-edit" style="min-width:110px;">
              <input type="text" class="pc-inp" style="font-weight:800;text-align:center;text-transform:uppercase;${!isXuat ? 'background:#f1f5f9;cursor:not-allowed;' : ''}"
                id="pc_cont_${item.idPo}" value="${escapeAttr(item.soContPo)}"
                data-row="${idx}" data-col="cont"
                ${!isXuat ? 'readonly title="Số cont hàng nhập không được sửa"' : ''}
                onfocus="onPcRowFocus(this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'cont', '${item.idPo}')"
                onchange="saveInlinePcField('${item.idPo}', 'soContPo', this.value, this)"
                ondblclick="openEditModal('${item.idPo}')" />
            </td>
            <td class="pc-cell-edit" style="min-width:80px;">
              <input type="text" class="pc-inp" style="font-weight:700;text-align:center;${!isXuat ? 'background:#f1f5f9;cursor:not-allowed;' : ''}"
                id="pc_seal_${item.idPo}" value="${escapeAttr(item.soSealPo)}"
                data-row="${idx}" data-col="seal"
                ${!isXuat ? 'readonly title="Số seal hàng nhập không được sửa"' : ''}
                onfocus="onPcRowFocus(this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'seal', '${item.idPo}')"
                onchange="saveInlinePcField('${item.idPo}', 'soSealPo', this.value, this)" />
            </td>
            <td style="text-align:center;font-weight:700;">${Number(item.cont20Po) > 0 ? "1" : ""}</td>
            <td style="text-align:center;font-weight:700;">${Number(item.cont40Po) > 0 ? "1" : ""}</td>
            <td class="pc-cell-edit" style="min-width:92px;">
              <input type="text" class="pc-inp" style="color:#991b1b;font-weight:700;text-align:center;"
                id="pc_ngay_${item.idPo}" value="${ngayGiaoStr}" placeholder="DD/MM/YYYY"
                data-row="${idx}" data-col="ngay"
                onfocus="onPcRowFocus(this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'ngay', '${item.idPo}')"
                onchange="saveInlinePcDate('${item.idPo}', this.value, this)" />
            </td>
            <td style="text-align:center;font-weight:700;color:#0369a1;white-space:nowrap;" title="${escapeAttr(cangDenVal)}">${escapeAttr(cangDenVal) || "-"}</td>
            <td class="pc-cell-cyan">
              <input type="text" class="pc-inp" style="font-weight:700;text-align:center;text-transform:uppercase;"
                id="pc_dv_${item.idPo}" value="${escapeAttr(item.donViVcPo)}" placeholder="Gõ ĐVVT..."
                autocomplete="off" data-row="${idx}" data-col="dv"
                onfocus="onPcRowFocus(this); openPcVendorAc('${item.idPo}', this)"
                oninput="openPcVendorAc('${item.idPo}', this)"
                onblur="onPcCellBlur('${item.idPo}', 'donViVcPo', this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'dv', '${item.idPo}')" />
            </td>
            <td class="pc-cell-cyan">
              <input type="text" class="pc-inp" style="font-weight:800;color:#0369a1;text-align:center;text-transform:uppercase;"
                id="pc_xe_${item.idPo}" value="${escapeAttr(item.soXePo)}" placeholder="Gõ Số xe..."
                autocomplete="off" data-row="${idx}" data-col="xe" title="${escapeAttr(drvTooltip)}"
                onfocus="onPcRowFocus(this); openPcTruckAc('${item.idPo}', this)"
                oninput="onPcTruckInput('${item.idPo}', this)"
                onblur="onPcCellBlur('${item.idPo}', 'soXePo', this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'xe', '${item.idPo}')" />
            </td>
            <td class="pc-actions-cell">
              ${isXuat ? `
                <button type="button" class="pc-act-btn" onclick="copyPoInfoById('${item.idPo}', 1)" title="Copy Thông tin xe đóng hàng">🚛 TT Xe</button>
                <button type="button" class="pc-act-btn" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="openPklModal('${item.idPo}')" title="Phiếu PKL">📄 PKL</button>
              ` : (() => {
                const bbTpl = detectBbTemplate(item);
                const isCustomBb = bbTpl.code !== "STANDARD";
                return `
                  <button type="button" class="pc-act-btn" onclick="copyPoInfoById('${item.idPo}', 2)" title="Copy Thông tin giao hàng">🚛 Giao</button>
                  <button type="button" class="pc-act-btn" onclick="copyPoInfoById('${item.idPo}', 1)" title="Copy Thông báo hàng đến">💬 TB</button>
                  <button type="button" class="pc-act-btn" style="${isCustomBb ? 'background:#fffbeb;color:#b45309;border-color:#fcd34d;' : 'background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;'}" onclick="printSingleBienBan('${item.idPo}')" title="In Biên Bản (${bbTpl.name})">📄 ${isCustomBb ? bbTpl.shortBadge : 'BB'}</button>
                `;
              })()}
            </td>
          </tr>
        `;
      }).join("");

      const moreBtnHtml = hasMore
        ? `<button class="load-more-btn" onclick="loadMoreCards()">Xem thêm (${filteredShipments.length - renderLimit} lô hàng nữa)...</button>`
        : "";

      const actionColTh = isBulk ? `
        <th style="width:40px;text-align:center;background:#dbeafe;" title="Tick all: Chọn / Bỏ chọn tất cả">
          <input type="checkbox" id="chkBulkPrintAll" onchange="toggleSelectAllBulk(this.checked)" ${allSelected ? "checked" : ""} style="width:16px;height:16px;cursor:pointer;vertical-align:middle;" />
        </th>
      ` : `
        <th style="width:36px;text-align:center;" title="Xóa booking thừa">XÓA</th>
      `;

      return `
        <div class="pc-table-wrap">
          <table class="pc-table">
            <thead>
              <tr>
                ${actionColTh}
                <th style="width:72px;text-align:center;">CHUYẾN</th>
                <th>KHÁCH HÀNG</th>
                <th>KHO NHẬN/ĐÓNG</th>
                <th style="width:100px;max-width:105px;">LIÊN HỆ</th>
                <th style="width:150px;max-width:160px;">ĐỊA CHỈ</th>
                <th style="width:65px;text-align:center;">TỈNH</th>
                <th style="width:110px;text-align:center;">SỐ CONT</th>
                <th style="width:80px;text-align:center;">SEAL</th>
                <th style="width:35px;text-align:center;">20</th>
                <th style="width:35px;text-align:center;">40</th>
                <th style="width:92px;text-align:center;">NGÀY GIAO/ĐÓNG</th>
                <th style="width:75px;text-align:center;">CẢNG ĐẾN</th>
                <th style="background:#a5f3fc;color:#0f172a;min-width:120px;">ĐƠN VỊ V/C ⚡</th>
                <th style="background:#a5f3fc;color:#0f172a;min-width:110px;">SỐ XE ⚡</th>
                <th style="width:155px;text-align:center;">THAO TÁC</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml}
            </tbody>
          </table>
        </div>
        ${moreBtnHtml}
      `;
    }

    let activePcAcContext = null; // { idPo, field, inputEl }
    let isSelectingPcAc = false;

    function onPcRowFocus(inputEl) {
      document.querySelectorAll(".pc-table tbody tr.pc-row-active").forEach(tr => tr.classList.remove("pc-row-active"));
      const tr = inputEl.closest("tr");
      if (tr) tr.classList.add("pc-row-active");
    }

    function positionPcAcFloating(inputEl) {
      const box = document.getElementById("pcAcFloating");
      if (!box || !inputEl) return;
      const rect = inputEl.getBoundingClientRect();
      const spaceBelow = window.innerHeight - rect.bottom;
      const width = Math.max(290, rect.width);
      box.style.width = `${width}px`;
      const left = Math.min(window.innerWidth - width - 10, Math.max(8, rect.left));
      box.style.left = `${left}px`;
      if (spaceBelow < 220 && rect.top > 230) {
        box.style.top = "auto";
        box.style.bottom = `${window.innerHeight - rect.top + 3}px`;
      } else {
        box.style.bottom = "auto";
        box.style.top = `${rect.bottom + 3}px`;
      }
    }

    function closePcAcFloating() {
      const box = document.getElementById("pcAcFloating");
      if (box) box.classList.remove("open");
      activePcAcContext = null;
    }

    document.addEventListener("mousedown", (e) => {
      if (!e.target.closest("#pcAcFloating") && !e.target.closest(".pc-inp")) {
        closePcAcFloating();
      }
    });

    function openPcVendorAc(idPo, inputEl) {
      activePcAcContext = { idPo, field: "donViVcPo", inputEl };
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      const q = (inputEl.value || "").toUpperCase().trim();
      const isInitial = po && q === (po.donViVcPo || "").toUpperCase().trim();
      const filterQ = isInitial ? "" : q;

      const matches = knownVendorList.filter(v => !filterQ || v.includes(filterQ)).slice(0, 35);
      const box = document.getElementById("pcAcFloating");
      if (matches.length === 0) {
        box.classList.remove("open");
        return;
      }

      positionPcAcFloating(inputEl);
      box.innerHTML = `
        <div class="ac-header">
          <span>🏢 Chọn ĐVVT (Lô #${idPo})</span>
          <span onclick="closePcAcFloating()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(v => `
          <div class="ac-item" onmousedown="selectPcVendorAc('${idPo}', '${v.replace(/'/g, "\\'")}')">
            <span>🏢 ${v}</span>
          </div>
        `).join("")}
      `;
      box.classList.add("open");
    }

    function selectPcVendorAc(idPo, vendor) {
      isSelectingPcAc = true;
      const inp = document.getElementById(`pc_dv_${idPo}`);
      if (inp) {
        inp.value = vendor;
        saveInlinePcField(idPo, "donViVcPo", vendor, inp);
      }
      closePcAcFloating();
      // Move focus to Số Xe of the same row for ultra-fast entry
      const xeInp = document.getElementById(`pc_xe_${idPo}`);
      if (xeInp) setTimeout(() => { xeInp.focus(); xeInp.select(); }, 30);
      setTimeout(() => { isSelectingPcAc = false; }, 250);
    }

    function openPcTruckAc(idPo, inputEl) {
      activePcAcContext = { idPo, field: "soXePo", inputEl };
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      const dvInp = document.getElementById(`pc_dv_${idPo}`);
      const curVendor = ((dvInp ? dvInp.value : (po && po.donViVcPo)) || "").toUpperCase().trim();
      const q = (inputEl.value || "").toUpperCase().replace(/\s+/g, "").trim();
      const isInitial = po && q === (po.soXePo || "").toUpperCase().trim();
      const filterQ = isInitial ? "" : q;

      const allTrucks = Array.from(knownTruckMap.values());
      let matches = allTrucks.filter(t => !filterQ || t.plate.includes(filterQ));
      matches.sort((a, b) => {
        const aSameV = curVendor && a.vendor === curVendor ? 1 : 0;
        const bSameV = curVendor && b.vendor === curVendor ? 1 : 0;
        if (aSameV !== bSameV) return bSameV - aSameV;
        if (filterQ) {
          const aStarts = a.plate.startsWith(filterQ) ? 1 : 0;
          const bStarts = b.plate.startsWith(filterQ) ? 1 : 0;
          if (aStarts !== bStarts) return bStarts - aStarts;
        }
        return b.count - a.count;
      });
      matches = matches.slice(0, 35);

      const box = document.getElementById("pcAcFloating");
      if (matches.length === 0) {
        box.classList.remove("open");
        return;
      }

      positionPcAcFloating(inputEl);
      box.innerHTML = `
        <div class="ac-header">
          <span>🚛 Gợi ý Số xe ${curVendor ? `(${curVendor})` : `(Lô #${idPo})`}</span>
          <span onclick="closePcAcFloating()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(t => {
          const isMatchVendor = curVendor && t.vendor === curVendor;
          const drv = getDriverForPlate(t.plate);
          const vLabel = (drv && drv.vendor) || t.vendor || "";
          const subLabel = drv && drv.name
            ? `${drv.name}${drv.phone ? ` (${drv.phone})` : ""}${vLabel ? ` • ${vLabel}` : ""}`
            : vLabel;
          return `
            <div class="ac-item ${isMatchVendor ? 'same-vendor' : ''}" onmousedown="selectPcTruckAc('${idPo}', '${t.plate}', '${vLabel.replace(/'/g, "\\'")}')">
              <span>🚛 ${t.plate}</span>
              ${subLabel ? `<span class="ac-sub ${isMatchVendor ? 'match-vendor' : ''}">${isMatchVendor ? '★ ' : ''}${subLabel}</span>` : ''}
            </div>
          `;
        }).join("")}
      `;
      box.classList.add("open");
    }

    function selectPcTruckAc(idPo, plate, vendor) {
      isSelectingPcAc = true;
      const xeInp = document.getElementById(`pc_xe_${idPo}`);
      const dvInp = document.getElementById(`pc_dv_${idPo}`);
      if (xeInp) xeInp.value = plate;
      if (vendor && dvInp && dvInp.value !== vendor) {
        dvInp.value = vendor;
        saveInlinePcField(idPo, "donViVcPo", vendor, dvInp);
      }
      if (xeInp) {
        saveInlinePcField(idPo, "soXePo", plate, xeInp);
      }
      closePcAcFloating();
      // Automatically jump down to next row's Số Xe input for rapid dispatching!
      if (xeInp) {
        const curRow = Number(xeInp.dataset.row);
        const nextXe = document.querySelector(`.pc-inp[data-row="${curRow + 1}"][data-col="xe"]`);
        if (nextXe) setTimeout(() => { nextXe.focus(); nextXe.select(); }, 40);
      }
      setTimeout(() => { isSelectingPcAc = false; }, 250);
    }

    function onPcTruckInput(idPo, inputEl) {
      const raw = (inputEl.value || "").toUpperCase().replace(/\s+/g, "");
      inputEl.value = raw;
      const drv = getDriverForPlate(raw);
      const dvInp = document.getElementById(`pc_dv_${idPo}`);
      if (drv && drv.vendor && dvInp) {
        dvInp.value = drv.vendor;
      } else if (raw.length >= 5) {
        const matchedVs = DEFAULT_XE_VIETSUN.find(x => x === raw || x.endsWith(raw));
        if (matchedVs && dvInp && (!dvInp.value || dvInp.value === "VIETSUN")) {
          dvInp.value = "VIETSUN";
        }
      }
      openPcTruckAc(idPo, inputEl);
    }

    function onPcCellBlur(idPo, fieldName, inputEl) {
      setTimeout(() => {
        if (isSelectingPcAc) return;
        const po = allShipments.find(x => String(x.idPo) === String(idPo));
        if (!po) return;
        const val = (inputEl.value || "").toUpperCase().trim();
        if (val !== (po[fieldName] || "").toUpperCase().trim()) {
          saveInlinePcField(idPo, fieldName, val, inputEl);
        }
      }, 180);
    }

    function onPcCellKeyDown(e, rowIdx, colName, idPo) {
      if (e.key === "Escape") {
        closePcAcFloating();
        return;
      }
      if (e.key === "Enter" || e.key === "ArrowDown" || e.key === "ArrowUp") {
        e.preventDefault();
        closePcAcFloating();
        const inputEl = e.target;
        const val = (inputEl.value || "").trim();
        const po = allShipments.find(x => String(x.idPo) === String(idPo));

        // Save current cell if modified
        if (po) {
          if (colName === "ngay") {
            saveInlinePcDate(idPo, val, inputEl);
          } else {
            const fieldMap = { cont: "soContPo", seal: "soSealPo", dv: "donViVcPo", xe: "soXePo" };
            const fieldName = fieldMap[colName];
            if (fieldName && val.toUpperCase() !== (po[fieldName] || "").toUpperCase()) {
              // If typing short 4-5 digit plate in xe, auto-expand if unique match in knownTruckMap
              let finalVal = val.toUpperCase();
              if (colName === "xe" && finalVal.length >= 4 && finalVal.length < 7) {
                const found = Array.from(knownTruckMap.values()).find(t => t.plate.endsWith(finalVal));
                if (found) {
                  finalVal = found.plate;
                  inputEl.value = finalVal;
                  const drv = getDriverForPlate(finalVal);
                  const vName = (drv && drv.vendor) || found.vendor || "";
                  const dvInp = document.getElementById(`pc_dv_${idPo}`);
                  if (vName && dvInp) {
                    dvInp.value = vName;
                    saveInlinePcField(idPo, "donViVcPo", vName, dvInp);
                  }
                }
              }
              saveInlinePcField(idPo, fieldName, finalVal, inputEl);
            }
          }
        }

        // Move focus to next/previous row
        const targetRow = e.key === "ArrowUp" ? rowIdx - 1 : rowIdx + 1;
        const nextInp = document.querySelector(`.pc-inp[data-row="${targetRow}"][data-col="${colName}"]`);
        if (nextInp) {
          nextInp.focus();
          nextInp.select();
        }
      }
    }

    async function saveInlinePcDate(idPo, vnDateStr, inputEl) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      const clean = (vnDateStr || "").trim();
      let isoVal = "2021-11-11";
      let payloadDate = "";
      if (clean) {
        const parts = clean.split(/[\/\-]/);
        if (parts.length === 3) {
          const dd = parts[0].padStart(2, "0");
          const mm = parts[1].padStart(2, "0");
          const yyyy = parts[2].length === 2 ? "20" + parts[2] : parts[2];
          isoVal = `${yyyy}-${mm}-${dd}`;
          payloadDate = `${dd}/${mm}/${yyyy}`;
        }
      }
      if (isoVal === (po.ngayTraHang || "2021-11-11")) return;

      if (inputEl) {
        inputEl.classList.remove("pc-saved");
        inputEl.classList.add("pc-saving");
      }
      try {
        const kieuNgay = po.xuatNhapPo === "X" ? "8" : "9";
        await postCrudUpdate({
          TenDangNhap: state.username,
          userString: state.userString,
          cv: 3,
          loai: 1,
          iddt: 1,
          cot: `b18<<|${kieuNgay}<<|20<<|${po.idPo}<<|ngayTraHang<<|${po.codePo}`,
          thongtin: payloadDate
        });
        po.ngayTraHang = isoVal;
        if (inputEl) {
          inputEl.classList.remove("pc-saving");
          inputEl.classList.add("pc-saved");
        }
        showToast(`Đã lưu Ngày (${payloadDate || "Chưa xếp"}) lô #${po.idPo}!`, "success");
      } catch (err) {
        if (inputEl) inputEl.classList.remove("pc-saving");
        showToast(`Lỗi lưu ngày lô #${po.idPo}: ${err.message}`, "error");
      }
    }

    async function saveInlinePcField(idPo, fieldName, rawVal, inputEl) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      if (po.xuatNhapPo !== "X" && (fieldName === "soContPo" || fieldName === "soSealPo")) {
        return; // Khóa cứng cont/seal hàng nhập
      }
      rawVal = (rawVal || "").toUpperCase().trim();

      if (inputEl) {
        inputEl.classList.remove("pc-saved");
        inputEl.classList.add("pc-saving");
      }

      try {
        if (fieldName === "soContPo") {
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 5,
            loai: 21,
            iddt: po.idPo,
            cot: rawVal,
            thongtin: po.codePo
          });
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|50<<|${po.idPo}<<|soContPo<<|${po.codePo}`,
            thongtin: rawVal
          });
          po.soContPo = rawVal;
        } else if (fieldName === "soXePo") {
          const drv = getDriverForPlate(rawVal);
          const dvInp = document.getElementById(`pc_dv_${po.idPo}`);
          let autoVendor = (drv && drv.vendor) || "";
          if (!autoVendor && DEFAULT_XE_VIETSUN.includes(rawVal)) autoVendor = "VIETSUN";

          // 1. Luôn lưu Số xe chắc chắn bằng lệnh b18 chuẩn
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|50<<|${po.idPo}<<|soXePo<<|${po.codePo}`,
            thongtin: rawVal
          });
          po.soXePo = rawVal;

          // 2. Gửi thêm b18newtruck để TMS nhận diện xe Vietsun
          let htmlResp = "";
          try {
            htmlResp = await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18newtruck<<|4<<|50<<|${po.idPo}<<|soXePo<<|${po.codePo}`,
              thongtin: rawVal
            });
          } catch (_) {}

          if (htmlResp && htmlResp.includes("reload_input_ajax")) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlResp, "text/html");
            const kqDv = doc.getElementById(`kq_donViVcPo_${po.idPo}`);
            if (kqDv && kqDv.textContent && kqDv.textContent.trim()) {
              autoVendor = kqDv.textContent.trim().toUpperCase();
            }
          }

          if (autoVendor && po.donViVcPo !== autoVendor) {
            po.donViVcPo = autoVendor;
            if (dvInp) {
              dvInp.value = autoVendor;
              dvInp.classList.add("pc-saved");
            }
            await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18<<|4<<|30<<|${po.idPo}<<|donViVcPo<<|${po.codePo}`,
              thongtin: autoVendor
            });
          }
          saveRecentTruck(rawVal, po.donViVcPo || autoVendor);
        } else if (fieldName === "donViVcPo") {
          // Lưu Đơn vị vận tải bằng b18 chuẩn
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|30<<|${po.idPo}<<|donViVcPo<<|${po.codePo}`,
            thongtin: rawVal
          });
          po.donViVcPo = rawVal;
        } else {
          const maxLen = 50;
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|${maxLen}<<|${po.idPo}<<|${fieldName}<<|${po.codePo}`,
            thongtin: rawVal
          });
          po[fieldName] = rawVal;
        }

        if (inputEl) {
          inputEl.classList.remove("pc-saving");
          inputEl.classList.add("pc-saved");
        }
        updateSummaryCards(filteredShipments);
        showToast(`✅ Đã lưu ${fieldName === 'soXePo' ? 'Số xe' : fieldName === 'donViVcPo' ? 'ĐVVT' : fieldName}: ${rawVal || '(Trống)'} (Lô #${po.idPo})`, "success");
      } catch (err) {
        if (inputEl) inputEl.classList.remove("pc-saving");
        showToast(`Lỗi lưu ${fieldName} lô #${po.idPo}: ${err.message}`, "error");
      }
    }

    function renderShipmentList() {
      applyViewModeUI();
      const container = document.getElementById("poListContainer");
      if (filteredShipments.length === 0) {
        container.innerHTML = `
          <div class="state-box">
            <div style="font-size:38px;margin-bottom:8px;">📭</div>
            <div style="font-weight:700;color:#334155;font-size:15px;">Không có lô hàng nào khớp bộ lọc</div>
            <div style="font-size:12.5px;margin-top:4px;">Thử chọn "Tất cả (30 ngày)" hoặc đổi đối tượng/loại hàng.</div>
            <button class="mini-btn" style="margin-top:12px;padding:8px 14px;" onclick="setQuickDate('all')">Xem tất cả 30 ngày</button>
          </div>
        `;
        return;
      }

      const slice = filteredShipments.slice(0, renderLimit);
      const hasMore = filteredShipments.length > renderLimit;

      if (state.viewMode === "table") {
        container.innerHTML = renderDesktopTableView(slice, hasMore);
        return;
      }

      const todayIso = getTodayISO();

      const cardsHtml = slice.map(item => {
        const isXuat = item.xuatNhapPo === "X";
        const typeBadge = isXuat
          ? `<span class="badge badge-xuat">📤 XUẤT</span>`
          : item.xuatNhapPo === "N"
          ? `<span class="badge badge-nhap">📥 NHẬP</span>`
          : `<span class="badge badge-chuhang">${item.xuatNhapPo}</span>`;

        const sizeStr = Number(item.cont40Po) > 0 ? "40'HC" : Number(item.cont20Po) > 0 ? "20'DC" : "";
        const sizeBadge = sizeStr ? `<span class="badge badge-size">${sizeStr}</span>` : "";

        // ISO Check on card
        let isoHtml = "";
        if (item.soContPo) {
          const check = validateContainerISO(item.soContPo);
          isoHtml = check.valid
            ? `<span class="iso-badge iso-ok" title="Đúng chuẩn ISO 6346">✓</span>`
            : `<span class="iso-badge iso-warn" title="${check.message}">!</span>`;
        }

        const contDisplay = item.soContPo
          ? `<div class="cont-number">${item.soContPo} ${isoHtml}</div>`
          : `<div class="cont-number empty-val">Chưa có số Cont</div>`;

        const sealDisplay = item.soSealPo
          ? `Seal: <b>${item.soSealPo}</b>`
          : `Seal: <span style="color:#94a3b8;">--</span>`;

        const hasTruck = Boolean(item.soXePo);
        const drvInfo = getDriverForPlate(item.soXePo);
        const truckDisplay = hasTruck
          ? `<div class="truck-number">🚛 ${item.soXePo}</div>`
          : `<div class="truck-number unassigned">⚠️ Chưa điều xe</div>`;

        const vendorDisplay = drvInfo
          ? `<div class="vendor-name" style="color:#15803d;" title="${drvInfo.name} - ${drvInfo.phone || ""}">👨‍✈️ ${drvInfo.name}</div>`
          : item.donViVcPo
          ? `<div class="vendor-name">🏢 ${item.donViVcPo}</div>`
          : `<div class="vendor-name" style="color:#94a3b8;">ĐVVT: Chưa gán</div>`;

        // Delivery / Packing Date pill
        const isUnsetDate = !item.ngayTraHang || item.ngayTraHang === "2021-11-11" || item.ngayTraHang === "1970-01-01";
        const isTodayDate = item.ngayTraHang === todayIso;
        const dateClass = isUnsetDate ? "date-unset" : isTodayDate ? "date-today" : "";
        const dateLabelPrefix = isXuat ? "Ngày đóng" : "Ngày giao";
        const dateFormatted = formatIsoToVN(item.ngayTraHang);

        // Extract phone number if available in lienHeNhanPo
        const phoneMatch = (item.lienHeNhanPo || "").match(/(0[0-9.\-\s]{8,13})/);
        const cleanPhone = phoneMatch ? phoneMatch[1].replace(/[^0-9]/g, "") : "";

        const locationParts = [item.khoNhanHangPo, item.diaChiNhanPo, item.tinhNhanPo]
          .filter(x => x && x !== "." && x !== "THONG BAO SAU");
        const locationText = locationParts.length > 0 ? locationParts.join(" • ") : "Địa chỉ: Thông báo sau";

        // For Import (N): Highlight Warehouse Name (khoNhanHangPo) prominently instead of Customer Name
        const rawKho = (item.khoNhanHangPo && item.khoNhanHangPo !== ".") ? item.khoNhanHangPo.trim() : "";
        const isKhoTbs = !rawKho || rawKho.toUpperCase() === "THONG BAO SAU";
        const addrOnlyParts = [item.diaChiNhanPo, item.tinhNhanPo]
          .filter(x => x && x !== "." && x !== "THONG BAO SAU");
        const addrOnlyText = addrOnlyParts.length > 0 ? addrOnlyParts.join(" • ") : "Địa chỉ: Thông báo sau";

        const customerOrWarehouseHtml = isXuat
          ? `
              <div class="customer-name">
                <span>👤 ${item.tenKhachPo || "Khách lẻ"}</span>
                <span style="font-size:11.5px;font-weight:600;color:#64748b;">${item.tenHangHoaPo || ""} ${item.trongLuongPo ? `(${item.trongLuongPo}T)` : ""}</span>
              </div>
              <div class="warehouse-addr">📍 ${locationText}</div>
            `
          : `
              <div class="customer-name">
                <span style="color:${isKhoTbs ? '#d97706' : '#0f172a'};font-size:14.5px;font-weight:800;">🏭 ${rawKho || "Chưa cập nhật Kho"}</span>
                <span style="font-size:11.5px;font-weight:600;color:#64748b;">${item.tenHangHoaPo || ""} ${item.trongLuongPo ? `(${item.trongLuongPo}T)` : ""}</span>
              </div>
              <div class="warehouse-addr">📍 ${addrOnlyText} • <span style="color:#334155;font-weight:600;">👤 KH: ${item.tenKhachPo || "Khách lẻ"}</span></div>
            `;

        const portBadgeHtml = isXuat
          ? (item.tinhXuatHangPo ? `<span class="badge" style="background:#e0f2fe;color:#0369a1;">🚢 ${item.tinhXuatHangPo}</span>` : "")
          : (item.cangDenPo ? `<span class="badge" style="background:#dcfce7;color:#15803d;">⚓ ${item.cangDenPo}</span>` : "");

        return `
          <div class="po-card type-${item.xuatNhapPo} ${!hasTruck ? 'no-truck' : ''}" id="card-${item.idPo}">
            <div class="card-top">
              <div class="badges-left">
                ${typeBadge}
                <span class="badge badge-chuhang">🏢 ${item.chuHangPo || "N/A"}</span>
                ${sizeBadge}
                ${portBadgeHtml}
                ${item.hinhThucKHopPo ? `<span class="badge" style="background:#f3e8ff;color:#7e22ce;">🔗 ${item.hinhThucKHopPo}</span>` : ""}
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
                <div class="field-mini-label">XE / ĐƠN VỊ VẬN TẢI</div>
                ${truckDisplay}
                ${vendorDisplay}
              </div>
            </div>

            <div class="customer-row" onclick="openEditModal('${item.idPo}')">
              ${customerOrWarehouseHtml}
              ${item.lienHeNhanPo && item.lienHeNhanPo !== "." ? `<div style="font-size:11.5px;color:#0369a1;margin-top:2px;font-weight:600;">📞 LH: ${item.lienHeNhanPo}</div>` : ""}
            </div>

            <div class="card-meta">
              <div class="meta-item">
                <span>🚢 ${item.tauPo || "--"} (${item.chuyenPo || "--"})${!isXuat && item.ngayTauChayPo ? ` • ${formatIsoToVN(item.ngayTauChayPo)}` : ""}</span>
              </div>
              <div class="meta-item">
                <span class="date-pill ${dateClass}">📅 ${dateLabelPrefix}: ${dateFormatted}</span>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn-card btn-edit" onclick="openEditModal('${item.idPo}')">
                ✏️ Cập nhật
              </button>
              ${!isXuat ? `
              <button class="btn-card btn-zalo" onclick="copyPoInfoById('${item.idPo}', 2)" title="Copy THÔNG TIN GIAO HÀNG (Biểu tượng xe)">
                🚛 Giao hàng
              </button>
              ` : ""}
              <button class="btn-card btn-zalo" onclick="copyPoInfoById('${item.idPo}', 1)" title="${isXuat ? 'Copy THÔNG TIN XE ĐÓNG HÀNG' : 'Copy THÔNG BÁO HÀNG ĐẾN'}">
                ${isXuat ? "🚛 Thông tin xe" : "💬 TB"}
              </button>
              ${isXuat ? `<button class="btn-card btn-zalo" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="openPklModal('${item.idPo}')" title="In / Gửi Phiếu PKL Hạ Cảng">📄 PKL</button>` : `<button class="btn-card btn-zalo" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;" onclick="printSingleBienBan('${item.idPo}')" title="In Biên Bản Giao Nhận Hàng">📄 BB</button>`}
              ${cleanPhone ? `<a href="tel:${cleanPhone}" class="btn-card btn-call" title="Gọi liên hệ kho">📞</a>` : ""}
            </div>
          </div>
        `;
      }).join("");

      const moreBtnHtml = hasMore
        ? `<button class="load-more-btn" onclick="loadMoreCards()">Xem thêm (${filteredShipments.length - renderLimit} lô hàng nữa)...</button>`
        : "";

      container.innerHTML = cardsHtml + moreBtnHtml;
    }

    function loadMoreCards() {
      renderLimit += state.viewMode === "table" ? 300 : 60;
      renderShipmentList();
    }

    // =========================================================================
    // 7. FILTER UI ACTIONS
    // =========================================================================
    function updateQuickDateChipLabels() {
      const realToday = getTodayISO();
      const d_prev = addDaysISO(realToday, -1);
      const d1 = addDaysISO(realToday, 1);
      const d2 = addDaysISO(realToday, 2);
      const d3 = addDaysISO(realToday, 3);
      const elToday = document.getElementById("chipToday") || document.querySelector('[data-date-mode="today"]');
      const elTom = document.getElementById("chipTomorrow");
      const elD2 = document.getElementById("chipD2");
      const elD3 = document.getElementById("chipD3");
      const elYest = document.getElementById("chipYesterday") || document.querySelector('[data-date-mode="yesterday"]');
      if (elToday) elToday.textContent = `📅 Hôm nay (${realToday.slice(8, 10)}/${realToday.slice(5, 7)})`;
      if (elTom) elTom.textContent = `⏭️ Ngày mai (${d1.slice(8, 10)}/${d1.slice(5, 7)})`;
      if (elD2) elD2.textContent = `📆 Ngày D+2 (${d2.slice(8, 10)}/${d2.slice(5, 7)})`;
      if (elD3) elD3.textContent = `📆 Ngày D+3 (${d3.slice(8, 10)}/${d3.slice(5, 7)})`;
      if (elYest) elYest.textContent = `⏮️ Hôm qua (${d_prev.slice(8, 10)}/${d_prev.slice(5, 7)})`;
      const serverDateEl = document.getElementById("serverDateInfo");
      if (serverDateEl) serverDateEl.textContent = `Mốc dữ liệu API: ${formatIsoToVN(state.apiDate || realToday)}`;
    }
    updateQuickDateChipLabels();

    // Tự động kiểm tra sang ngày mới khi người dùng mở lại điện thoại hoặc tab trình duyệt
    let lastSeenDateISO = getTodayISO();
    function checkCalendarDateRollover() {
      const currentToday = getTodayISO();
      if (currentToday !== lastSeenDateISO) {
        console.log("[Calendar] Phát hiện sang ngày mới:", lastSeenDateISO, "->", currentToday);
        lastSeenDateISO = currentToday;
        state.apiDate = currentToday;
        if (state.dateMode === "today") {
          state.fromDate = currentToday;
          state.toDate = currentToday;
          const elFrom = document.getElementById("filterFromDate");
          const elTo = document.getElementById("filterToDate");
          if (elFrom) elFrom.value = currentToday;
          if (elTo) elTo.value = currentToday;
        }
        updateQuickDateChipLabels();
        refreshData(false);
      } else {
        updateQuickDateChipLabels();
      }
    }
    document.addEventListener("visibilitychange", () => {
      if (document.visibilityState === "visible") checkCalendarDateRollover();
    });
    window.addEventListener("focus", checkCalendarDateRollover);
    setInterval(checkCalendarDateRollover, 60000);

    function setQuickDate(mode) {
      state.dateMode = mode;
      const today = getTodayISO();
      state.apiDate = today;
      updateQuickDateChipLabels();

      if (mode === "today") {
        state.fromDate = today;
        state.toDate = today;
      } else if (mode === "tomorrow") {
        state.fromDate = addDaysISO(today, 1);
        state.toDate = addDaysISO(today, 1);
      } else if (mode === "d2") {
        state.fromDate = addDaysISO(today, 2);
        state.toDate = addDaysISO(today, 2);
      } else if (mode === "d3") {
        state.fromDate = addDaysISO(today, 3);
        state.toDate = addDaysISO(today, 3);
      } else if (mode === "yesterday") {
        state.fromDate = addDaysISO(today, -1);
        state.toDate = addDaysISO(today, -1);
      } else if (mode === "3days") {
        state.fromDate = addDaysISO(today, -1);
        state.toDate = addDaysISO(today, 3);
      }

      document.getElementById("filterFromDate").value = state.fromDate;
      document.getElementById("filterToDate").value = state.toDate;

      const singleLbl = document.getElementById("chipSingleDateLabel");
      if (singleLbl) singleLbl.textContent = "🎯 Chọn ngày";

      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.dateMode === mode);
      });

      if (mode !== "all" && mode !== "unset") {
        ensureDateCyclesLoaded(state.fromDate, state.toDate);
      }
      applyFilters();
    }

    function openQuickSingleDatePicker(e) {
      const inp = document.getElementById("quickSingleDatePicker");
      if (!inp) return;
      if (!inp.value && state.fromDate) {
        inp.value = state.fromDate;
      }
      try {
        if (typeof inp.showPicker === "function") {
          inp.showPicker();
          return;
        }
      } catch (err) {}
      // Fallback cho trình duyệt cũ không hỗ trợ showPicker: mở bảng lọc nâng cao và focus vào ô Từ ngày
      const adv = document.getElementById("advancedFilters");
      if (adv && !adv.classList.contains("open")) {
        adv.classList.add("open");
      }
      const fromInp = document.getElementById("filterFromDate");
      if (fromInp) fromInp.focus();
    }

    function onQuickSingleDatePick(isoVal) {
      if (!isoVal) return;
      state.dateMode = "single";
      state.fromDate = isoVal;
      state.toDate = isoVal;
      document.getElementById("filterFromDate").value = isoVal;
      document.getElementById("filterToDate").value = isoVal;

      const singleLbl = document.getElementById("chipSingleDateLabel");
      if (singleLbl) {
        singleLbl.textContent = `🎯 Ngày ${isoVal.slice(8, 10)}/${isoVal.slice(5, 7)}`;
      }
      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.dateMode === "single");
      });

      ensureDateCyclesLoaded(isoVal, isoVal);
      applyFilters();
    }

    function onCustomDateChange(which) {
      const fromInp = document.getElementById("filterFromDate");
      const toInp = document.getElementById("filterToDate");
      const prevMode = state.dateMode;
      state.dateMode = "custom";

      if (which === "from" && fromInp.value) {
        // If user just switched from a quick tab to pick a specific date, or if ToDate < FromDate, sync ToDate = FromDate
        if (prevMode !== "custom" || !toInp.value || toInp.value < fromInp.value) {
          toInp.value = fromInp.value;
        }
      } else if (which === "to" && toInp.value) {
        if (!fromInp.value || fromInp.value > toInp.value) {
          fromInp.value = toInp.value;
        }
      }

      state.fromDate = fromInp.value;
      state.toDate = toInp.value;

      const singleLbl = document.getElementById("chipSingleDateLabel");
      if (singleLbl) {
        if (state.fromDate && state.fromDate === state.toDate) {
          singleLbl.textContent = `🎯 Ngày ${state.fromDate.slice(8, 10)}/${state.fromDate.slice(5, 7)}`;
        } else {
          singleLbl.textContent = "🎯 Chọn ngày";
        }
      }

      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => {
        btn.classList.toggle("active", state.fromDate === state.toDate && btn.dataset.dateMode === "single");
      });

      ensureDateCyclesLoaded(state.fromDate, state.toDate);
      applyFilters();
    }

    function setXuatNhapFilter(xn) {
      if (state.bulkMode && xn !== state.xuatNhap) {
        state.bulkMode = false;
        state.bulkCarrier = null;
        if (state.bulkSelectedIds) state.bulkSelectedIds.clear();
        closeBulkDropdown();
      }
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

    function toggleContSizeFilter(size) {
      if (state.contSize === size) {
        state.contSize = "ALL";
        showToast("Đã bỏ lọc kích thước Cont (Hiển thị tất cả 20' & 40')", "success");
      } else {
        state.contSize = size;
        showToast(`Đang lọc Cont ${size}'`, "success");
      }
      applyFilters();
    }

    let searchDebounceTimer = null;
    function onSearchInput() {
      const val = document.getElementById("searchInput").value;
      state.searchQuery = val;
      document.getElementById("btnClearSearch").style.display = val ? "flex" : "none";
      if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(() => {
        applyFilters();
      }, 100);
    }

    function clearSearch() {
      if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
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
      state.contSize = "ALL";
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
      badge.textContent = isXuat ? "📤 HÀNG XUẤT" : "📥 HÀNG NHẬP";

      document.getElementById("modalTitleText").textContent = `Lô #${po.idPo} (${po.chuHangPo || ""})`;
      if (isXuat) {
        document.getElementById("modalCustomerName").textContent = `👤 ${po.tenKhachPo || "Khách hàng"}`;
        const cangDenSub = po.tinhXuatHangPo ? ` • Cảng đến: ${po.tinhXuatHangPo}` : "";
        document.getElementById("modalRouteSub").textContent =
          `Tàu: ${po.tauPo || "--"} (${po.chuyenPo || "--"}) • Ngày tàu: ${formatIsoToVN(po.ngayTauChayPo)} • Loại: ${Number(po.cont40Po) > 0 ? "40'" : "20'"}${cangDenSub}`;
      } else {
        const khoDisplay = (po.khoNhanHangPo && po.khoNhanHangPo !== ".") ? po.khoNhanHangPo : "Chưa cập nhật Kho";
        document.getElementById("modalCustomerName").textContent = `🏭 Kho: ${khoDisplay}`;
        document.getElementById("modalRouteSub").textContent =
          `👤 Khách: ${po.tenKhachPo || "--"} • Tàu: ${po.tauPo || "--"} (${po.chuyenPo || "--"}) • Cảng: ${po.cangDenPo || "--"} • Loại: ${Number(po.cont40Po) > 0 ? "40'" : "20'"}`;
      }

      // Toggle Section 2: Export (Bãi/Cảng & Kết hợp) vs Import (Thông tin Tàu & Cảng)
      const secXuat = document.getElementById("secXuatPort");
      const secNhap = document.getElementById("secNhapVessel");
      if (secXuat) secXuat.style.display = isXuat ? "block" : "none";
      if (secNhap) secNhap.style.display = isXuat ? "none" : "block";

      document.getElementById("lblNgayTraHang").textContent =
        isXuat ? "📅 Ngày Đóng Hàng (ngayTraHang)" : "📅 Ngày Giao / Trả Hàng (ngayTraHang)";

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
      document.getElementById("inp_tinhXuatHangPo").value = po.tinhXuatHangPo || "";
      document.getElementById("inp_hinhThucKHopPo").value = po.hinhThucKHopPo || "";
      document.getElementById("inp_portTimePo").value = po.portTimePo || "";

      // Populate Import Vessel & Port fields
      document.getElementById("inp_tauPo").value = po.tauPo || "";
      document.getElementById("inp_chuyenPo").value = po.chuyenPo || "";
      document.getElementById("inp_cangDenPo").value = po.cangDenPo || "";
      const isUnsetVesselDate = !po.ngayTauChayPo || po.ngayTauChayPo === "2021-11-11" || po.ngayTauChayPo === "1970-01-01";
      document.getElementById("inp_ngayTauChayPo").value = isUnsetVesselDate ? "" : po.ngayTauChayPo;

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

      // Khóa cứng số cont, số seal ở Tab Nhập
      const contInp = document.getElementById("inp_soContPo");
      const sealInp = document.getElementById("inp_soSealPo");
      if (contInp) {
        contInp.readOnly = !isXuat;
        contInp.style.backgroundColor = !isXuat ? "#f1f5f9" : "";
        contInp.style.cursor = !isXuat ? "not-allowed" : "";
        contInp.title = !isXuat ? "Số cont hàng nhập là dữ liệu cứng từ hãng tàu, không được sửa" : "";
      }
      if (sealInp) {
        sealInp.readOnly = !isXuat;
        sealInp.style.backgroundColor = !isXuat ? "#f1f5f9" : "";
        sealInp.style.cursor = !isXuat ? "not-allowed" : "";
        sealInp.title = !isXuat ? "Số seal hàng nhập là dữ liệu cứng từ hãng tàu, không được sửa" : "";
      }

      // Configure Modal Footer Buttons for Export vs Import
      const btnDeliv = document.getElementById("btnModalDelivery");
      if (btnDeliv) btnDeliv.style.display = isXuat ? "none" : "inline-flex";
      const btnTb = document.getElementById("btnModalTb");
      if (btnTb) btnTb.textContent = isXuat ? "🚛 Thông tin xe" : "💬 Copy TB";
      const btnPkl = document.getElementById("btnModalPkl");
      if (btnPkl) btnPkl.style.display = isXuat ? "inline-flex" : "none";
      document.getElementById("editModal").classList.add("open");
    }

    function updateModalDriverInfo(plate) {
      const box = document.getElementById("driverInfoBox");
      if (!box) return;
      const drv = getDriverForPlate(plate);
      if (drv) {
        const vBadge = drv.vendor ? `[${drv.vendor}]` : "";
        box.style.display = "block";
        box.innerHTML = `👨‍✈️ Tài xế <b>${vBadge}</b>: <strong>${drv.name || "Chưa cập nhật"}</strong>${drv.phone ? ` • 📞 <a href="tel:${drv.phone}" style="color:#0369a1;text-decoration:underline;">${drv.phone}</a>` : ""}${drv.cccd ? `<div style="font-size:11px;color:#475569;margin-top:2px;">CCCD: ${drv.cccd}${drv.gplx ? ` • GPLX: ${drv.gplx}` : ""}</div>` : ""}`;
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
        ? `Gợi ý số xe (Ưu tiên ĐVVT: ${curVendor})`
        : `Gợi ý biển số xe (${matches.length} xe)`;

      dropdown.innerHTML = `
        <div class="ac-header">
          <span>🚛 ${headerTitle}</span>
          <span onclick="hideAllAutocomplete()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(t => {
          const isMatchVendor = curVendor && t.vendor === curVendor;
          const drv = getDriverForPlate(t.plate);
          const vLabel = (drv && drv.vendor) || t.vendor || "";
          const subLabel = drv && drv.name
            ? `${drv.name}${drv.phone ? ` (${drv.phone})` : ""}${vLabel ? ` • ${vLabel}` : ""}`
            : vLabel;
          return `
            <div class="ac-item ${isMatchVendor ? 'same-vendor' : ''}" onclick="selectTruckSuggestion('${t.plate}', '${vLabel.replace(/'/g, "\\'")}')">
              <span>🚛 ${t.plate}</span>
              ${subLabel ? `<span class="ac-sub ${isMatchVendor ? 'match-vendor' : ''}">${isMatchVendor ? '★ ' : ''}${subLabel}</span>` : ''}
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
          <span>🏢 Chọn Đơn Vị Vận Tải (${matches.length})</span>
          <span onclick="hideAllAutocomplete()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(v => `
          <div class="ac-item" onclick="selectVendorSuggestion('${v.replace(/'/g, "\\'")}')">
            <span>🏢 ${v}</span>
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
          sizeMismatch = " (Cont 40' trên lệnh 20'!)";
        } else if (Number(currentEditingPo.cont40Po) === 1 && CHECK_CONT_20.includes(prefix5)) {
          sizeMismatch = " (Cont 20' trên lệnh 40'!)";
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

      const drv = getDriverForPlate(raw);
      if (drv && drv.vendor) {
        const vendorInp = document.getElementById("inp_donViVcPo");
        if (!vendorInp.value || vendorInp.value !== drv.vendor) {
          vendorInp.value = drv.vendor;
        }
      } else {
        // Replicate gimax.js searchCities: if license plate is in Vietsun fleet, auto-fill VIETSUN
        const matchedVs = DEFAULT_XE_VIETSUN.find(x => x === raw || (raw.length >= 4 && x.endsWith(raw)));
        if (matchedVs && raw.length >= 5) {
          const vendorInp = document.getElementById("inp_donViVcPo");
          if (!vendorInp.value || vendorInp.value === "VIETSUN") {
            vendorInp.value = "VIETSUN";
          }
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
      if (!isXuat && (fieldName === "soContPo" || fieldName === "soSealPo")) {
        return; // Khóa cứng cont/seal hàng nhập
      }
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
        } else if (fieldName === "ngayTauChayPo") {
          const vnDate = rawVal ? formatIsoToPayloadDate(rawVal) : "";
          const kieuNgay = isXuat ? "8" : "9";
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|${kieuNgay}<<|20<<|${idPo}<<|ngayTauChayPo<<|${codePo}`,
            thongtin: vnDate
          });
          currentEditingPo.ngayTauChayPo = rawVal || "";
        } else if (fieldName === "soXePo") {
          rawVal = rawVal.toUpperCase();
          // 1. Luôn lưu Số xe chắc chắn bằng lệnh b18 chuẩn
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|50<<|${idPo}<<|soXePo<<|${codePo}`,
            thongtin: rawVal
          });
          currentEditingPo.soXePo = rawVal;

          // 2. Gửi thêm b18newtruck để TMS nhận diện xe Vietsun
          let htmlResp = "";
          try {
            htmlResp = await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18newtruck<<|4<<|50<<|${idPo}<<|soXePo<<|${codePo}`,
              thongtin: rawVal
            });
          } catch (_) {}

          const curDvInp = document.getElementById("inp_donViVcPo");
          saveRecentTruck(rawVal, curDvInp ? curDvInp.value : "");

          // Tự động nhận diện Vendor nếu có
          let autoDv = "";
          if (htmlResp && htmlResp.includes("reload_input_ajax")) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlResp, "text/html");
            const kqDv = doc.getElementById(`kq_donViVcPo_${idPo}`);
            if (kqDv && kqDv.textContent && kqDv.textContent.trim()) {
              autoDv = kqDv.textContent.trim().toUpperCase();
            }
          }
          if (!autoDv && rawVal) {
            const drv = getDriverForPlate(rawVal);
            if (drv && drv.vendor) autoDv = drv.vendor;
            else if (DEFAULT_XE_VIETSUN.includes(rawVal)) autoDv = "VIETSUN";
          }
          if (autoDv && curDvInp && (!curDvInp.value || curDvInp.value === "VIETSUN" || curDvInp.value !== autoDv)) {
            curDvInp.value = autoDv;
            currentEditingPo.donViVcPo = autoDv;
            curDvInp.classList.add("saved");
            await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18<<|4<<|30<<|${idPo}<<|donViVcPo<<|${codePo}`,
              thongtin: autoDv
            });
          }
        } else if (fieldName === "donViVcPo") {
          rawVal = rawVal.toUpperCase();
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|30<<|${idPo}<<|donViVcPo<<|${codePo}`,
            thongtin: rawVal
          });
          currentEditingPo.donViVcPo = rawVal;
        } else {
          // Map field lengths exactly as gimax.js
          const lengthMap = {
            soSealPo: 50,
            donViVcPo: 30,
            noiLayContPo: 100,
            cangDiPo: 100,
            tinhXuatHangPo: 50,
            hinhThucKHopPo: 50,
            portTimePo: 20,
            tauPo: 30,
            chuyenPo: 30,
            cangDenPo: 30,
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
        showToast(`Đã lưu ${fieldName} lên hệ thống TMS!`, "success");
      } catch (err) {
        console.error(err);
        inpEl.classList.remove("saving");
        showToast(`Lỗi lưu ${fieldName}: ${err.message}`, "error");
      }
    }

    async function saveAllChanges(e) {
      if (e) e.preventDefault();
      if (!currentEditingPo) return;
      const btn = document.getElementById("btnSaveAll");
      btn.disabled = true;
      btn.textContent = "⏳ Đang lưu lên TMS...";

      try {
        const isXuat = currentEditingPo.xuatNhapPo === "X";
        const fieldsToCheck = isXuat
          ? [
              "ngayTraHang", "soContPo", "soSealPo", "soXePo", "donViVcPo",
              "noiLayContPo", "cangDiPo", "tinhXuatHangPo", "hinhThucKHopPo", "portTimePo",
              "khoNhanHangPo", "tinhNhanPo", "diaChiNhanPo", "lienHeNhanPo"
            ]
          : [
              "ngayTraHang", "soContPo", "soSealPo", "soXePo", "donViVcPo",
              "khoNhanHangPo", "tinhNhanPo", "diaChiNhanPo", "lienHeNhanPo"
            ];

        for (const f of fieldsToCheck) {
          const inp = document.getElementById(`inp_${f}`);
          if (!inp) continue;
          const val = inp.value.trim();
          const curVal = (f === "ngayTraHang" || f === "ngayTauChayPo")
            ? (currentEditingPo[f] === "2021-11-11" ? "" : (currentEditingPo[f] || ""))
            : (currentEditingPo[f] || "");
          if (val !== curVal || dirtyFields.has(f)) {
            await syncSingleFieldToServer(f);
          }
        }

        showToast(`Đã đồng bộ hoàn tất lô #${currentEditingPo.idPo}!`, "success");
        setTimeout(() => closeEditModal(), 350);
      } finally {
        btn.disabled = false;
        btn.textContent = "💾 Lưu Cập Nhật Lên TMS";
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

      // Lookup Vietsun / Vendor driver details by license plate
      const drv = getDriverForPlate(soXe);
      const tenLaiXe = drv ? drv.name : (po.taiXePo || "");
      const sdtLaiXe = drv ? drv.phone : (po.sdtTaiXePo || "");

      let cccdLaiXe = "     Nơi cấp: ";
      if (drv && drv.cccd) {
        if (drv.cccd.includes("Nơi cấp")) {
          cccdLaiXe = drv.cccd.replace(/\s*Nơi cấp:/i, "     Nơi cấp:");
        } else {
          cccdLaiXe = `${drv.cccd}     Nơi cấp: Cục QLHC & TTXH`;
        }
      }

      let gplxLaiXe = "           Nơi cấp:";
      if (drv && drv.gplx && drv.gplx !== "Đang Cập Nhật") {
        if (drv.gplx.includes("Nơi cấp")) {
          gplxLaiXe = drv.gplx.replace(/\s*Nơi cấp:/i, "           Nơi cấp:");
        } else {
          gplxLaiXe = `${drv.gplx}           Nơi cấp:SỞ GTVT`;
        }
      }

      if (loai === 1) {
        // Icon 💬: Thông báo hàng đến (Hàng Nhập) hoặc Thông tin đóng hàng (Hàng Xuất)
        if (!isXuat) {
          return (
            `THÔNG BÁO HÀNG ĐẾN:\n` +
            `- Tên khách: ${khach}\n` +
            `- Kho trả: ${kho}\n` +
            `- Địa chỉ: ${diachi}\t- Tỉnh: ${tinh}\n` +
            `- Liên hệ: ${lienhe}\n` +
            `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
            `- Tên tàu: ${tau}\t\t\t\t- Số chuyến: ${chuyen}\n` +
            `- Ngày giao: ${ngayGiao}`
          );
        } else {
          return (
            `THÔNG TIN ĐÓNG HÀNG NGÀY ${ngayGiao}:\n` +
            `- Khách hàng: ${khach}\n` +
            `- Kho ĐÓNG: ${kho}\n` +
            `- Địa chỉ: ${diachi}\n` +
            `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
            `- Nơi cấp rỗng: ${noiCapRong}\n` +
            `- Thời gian dự kiến tới kho: \n` +
            `- Số xe: ${soXe}\n` +
            `- Tên Lái Xe: ${tenLaiXe}\n` +
            `- Số CMND: ${cccdLaiXe}\n` +
            `- Số GPLX: ${gplxLaiXe}\n` +
            `- SĐT: ${sdtLaiXe}`
          );
        }
      }

      // Default loai === 2 (Icon 🚛 chiếc xe): THÔNG TIN GIAO HÀNG / ĐÓNG HÀNG
      if (isXuat) {
        return (
          `THÔNG TIN ĐÓNG HÀNG NGÀY ${ngayGiao}:\n` +
          `- Khách hàng: ${khach}\n` +
          `- Kho ĐÓNG: ${kho}\n` +
          `- Địa chỉ: ${diachi}\n` +
          `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
          `- Nơi cấp rỗng: ${noiCapRong}\n` +
          `- Thời gian dự kiến tới kho: \n` +
          `- Số xe: ${soXe}\n` +
          `- Tên Lái Xe: ${tenLaiXe}\n` +
          `- Số CMND: ${cccdLaiXe}\n` +
          `- Số GPLX: ${gplxLaiXe}\n` +
          `- SĐT: ${sdtLaiXe}`
        );
      }
      return (
        `THÔNG TIN GIAO HÀNG:\n` +
        `- Kho GIAO: ${kho}\n` +
        `- Địa chỉ: ${diachi}\t- Tỉnh: ${tinh}\n` +
        `- Liên hệ: ${lienhe}\n` +
        `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
        `- Ngày GIAO: ${ngayGiao}\n` +
        `- Số xe: ${soXe}\n` +
        `- Tên Lái Xe: ${tenLaiXe}\n` +
        `- SĐT: ${sdtLaiXe}\n` +
        `- Thời gian tới kho: `
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
        showToast("Không thể tự động copy, vui lòng thử lại", "error");
      }
      document.body.removeChild(ta);
    }

    function copyPoInfoById(idPo, loai = 2) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      const text = buildZaloText(po, loai);
      const drv = getDriverForPlate(po.soXePo);
      const drvNote = drv ? ` (TX: ${drv.name})` : "";
      const label = loai === 1 ? "Thông báo" : "TT Giao hàng";
      copyTextToClipboard(text, `Đã copy ${label} Cont ${po.soContPo || "#" + po.idPo}${drvNote}!`);
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
        cangDiPo: document.getElementById("inp_cangDiPo").value || currentEditingPo.cangDiPo,
        tinhXuatHangPo: document.getElementById("inp_tinhXuatHangPo").value || currentEditingPo.tinhXuatHangPo,
        khoNhanHangPo: document.getElementById("inp_khoNhanHangPo").value || currentEditingPo.khoNhanHangPo,
        tinhNhanPo: document.getElementById("inp_tinhNhanPo").value || currentEditingPo.tinhNhanPo,
        diaChiNhanPo: document.getElementById("inp_diaChiNhanPo").value || currentEditingPo.diaChiNhanPo,
        lienHeNhanPo: document.getElementById("inp_lienHeNhanPo").value || currentEditingPo.lienHeNhanPo
      });
      const text = buildZaloText(livePo, loai);
      const drv = getDriverForPlate(livePo.soXePo);
      const drvNote = drv ? ` (TX: ${drv.name})` : "";
      const label = loai === 1 ? "Thông báo" : "TT Giao hàng";
      copyTextToClipboard(text, `Đã copy ${label} Cont ${livePo.soContPo || "#" + livePo.idPo}${drvNote}!`);
    }

    // =========================================================================
    // 9B. PKL (PACKING LIST) A4 LANDSCAPE GENERATOR & PDF SHARE / PRINT
    // =========================================================================
    const GAS_PKL_WEBAPP_URL = "https://script.google.com/macros/s/AKfycby0p1n6JKiVPJ7BSqEzbGpwDouH0k55PAYif4O7bsVBN4jYGul7700eZavmR5A6vOxF/exec";
    let currentPklPo = null;
    let customGasPdfBase64 = null;
    let vietsunLogoImg = null;

    // Preload Vietsun Logo for PKL Canvas
    (function preloadPklLogo() {
      const img = new Image();
      img.crossOrigin = "anonymous";
      img.onload = () => { vietsunLogoImg = img; };
      img.src = "logoVietsunColor.png";
    })();

    function openPklFromEditModal() {
      if (!currentEditingPo) return;
      const livePo = Object.assign({}, currentEditingPo, {
        ngayTraHang: document.getElementById("inp_ngayTraHang").value || currentEditingPo.ngayTraHang,
        soContPo: document.getElementById("inp_soContPo").value || currentEditingPo.soContPo,
        soSealPo: document.getElementById("inp_soSealPo").value || currentEditingPo.soSealPo,
        soXePo: document.getElementById("inp_soXePo").value || currentEditingPo.soXePo,
        donViVcPo: document.getElementById("inp_donViVcPo").value || currentEditingPo.donViVcPo,
        noiLayContPo: document.getElementById("inp_noiLayContPo").value || currentEditingPo.noiLayContPo,
        cangDiPo: document.getElementById("inp_cangDiPo").value || currentEditingPo.cangDiPo,
        tinhXuatHangPo: document.getElementById("inp_tinhXuatHangPo").value || currentEditingPo.tinhXuatHangPo,
        khoNhanHangPo: document.getElementById("inp_khoNhanHangPo").value || currentEditingPo.khoNhanHangPo,
        tinhNhanPo: document.getElementById("inp_tinhNhanPo").value || currentEditingPo.tinhNhanPo,
        diaChiNhanPo: document.getElementById("inp_diaChiNhanPo").value || currentEditingPo.diaChiNhanPo,
        lienHeNhanPo: document.getElementById("inp_lienHeNhanPo").value || currentEditingPo.lienHeNhanPo
      });
      openPklWithPoObject(livePo);
    }

    function openPklModal(idPo) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      openPklWithPoObject(po);
    }

    function openPklWithPoObject(po) {
      currentPklPo = po;
      customGasPdfBase64 = null;
      document.getElementById("pklModalTitle").textContent = `📄 Phiếu PKL: ${po.soContPo || "#" + po.idPo} (${po.soXePo || "Chưa gán xe"})`;
      document.getElementById("pklModalSub").textContent = `Khách: ${po.tenKhachPo || "--"} • Tàu: ${po.tauPo || "--"} (${po.chuyenPo || "--"})`;
      const statusText = document.getElementById("pklStatusText");
      statusText.style.color = "#047857";
      statusText.textContent = `✅ Đã tạo phiếu PKL trực tiếp từ TMS (${po.donViVcPo || "VIETSUN"} - ${po.soXePo || ""})`;

      renderPklToCanvas(po);
      document.getElementById("pklModal").classList.add("open");
    }

    function closePklModal() {
      document.getElementById("pklModal").classList.remove("open");
    }

    // =========================================================================
    // 9. XÓA BOOKING THỪA (index-crud.php: cv=4, loai=19)
    // =========================================================================
    let pendingDeletePoId = null;

    function openDeleteBookingModal(idPo) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      pendingDeletePoId = idPo;
      const descEl = document.getElementById("deleteModalDesc");
      if (descEl) {
        const info = po
          ? `lô <b>#${po.idPo}</b> (Chuyến: <b>${po.chuyenPo || "--"}</b> • KH: <b>${po.tenKhachPo || "--"}</b>${po.soContPo ? ` • Cont: <b>${po.soContPo}</b>` : ""})`
          : `lô <b>#${idPo}</b>`;
        descEl.innerHTML = `Bạn có chắc chắn muốn xóa booking thừa ${info} không?<br><span style="color:#dc2626;font-size:12px;font-weight:600;">Hành động này sẽ gửi lệnh xóa trực tiếp lên hệ thống TMS Vietsun.</span>`;
      }
      const inp = document.getElementById("deleteConfirmInput");
      if (inp) {
        inp.value = "";
      }
      const btn = document.getElementById("btnConfirmDelete");
      if (btn) {
        btn.disabled = true;
        btn.style.opacity = "0.35";
        btn.style.cursor = "not-allowed";
        btn.textContent = "Xác nhận xóa";
      }
      const modal = document.getElementById("deleteConfirmModal");
      if (modal) {
        modal.classList.add("open");
        setTimeout(() => { if (inp) inp.focus(); }, 120);
      }
    }

    function checkDeleteOkInput(el) {
      const val = (el.value || "").trim().toLowerCase();
      const btn = document.getElementById("btnConfirmDelete");
      if (!btn) return;
      if (val === "ok") {
        btn.disabled = false;
        btn.style.opacity = "1";
        btn.style.cursor = "pointer";
      } else {
        btn.disabled = true;
        btn.style.opacity = "0.35";
        btn.style.cursor = "not-allowed";
      }
    }

    function closeDeleteModal() {
      const modal = document.getElementById("deleteConfirmModal");
      if (modal) modal.classList.remove("open");
      pendingDeletePoId = null;
    }

    async function executeDeleteBooking() {
      const inp = document.getElementById("deleteConfirmInput");
      if (!inp || (inp.value || "").trim().toLowerCase() !== "ok") {
        showToast("Vui lòng gõ chữ 'ok' để xác nhận xóa!", "warning");
        return;
      }
      if (!pendingDeletePoId) return;
      const targetId = pendingDeletePoId;
      const btn = document.getElementById("btnConfirmDelete");
      if (btn) {
        btn.disabled = true;
        btn.textContent = "Đang xóa...";
      }

      try {
        await postCrudUpdate({
          TenDangNhap: state.username,
          userString: state.userString,
          cv: 4,
          loai: 19,
          iddt: 1,
          cot: 0,
          thongtin: String(targetId)
        });

        // Xóa khỏi danh sách bộ nhớ
        allShipments = allShipments.filter(x => String(x.idPo) !== String(targetId));
        filteredShipments = filteredShipments.filter(x => String(x.idPo) !== String(targetId));

        closeDeleteModal();
        if (currentEditingPo && String(currentEditingPo.idPo) === String(targetId)) {
          closeEditModal();
        }
        applyFilters();
        showToast(`Đã xóa booking thừa #${targetId} thành công!`, "success");
      } catch (err) {
        console.error("Lỗi khi xóa booking:", err);
        showToast("Lỗi khi xóa booking: " + (err.message || "Không thể kết nối TMS"), "error");
      } finally {
        if (btn) {
          btn.textContent = "Xác nhận xóa";
          btn.disabled = false;
        }
      }
    }

    function drawUnderlinedText(ctx, text, x, y, align = "left") {
      ctx.textAlign = align;
      ctx.fillText(text, x, y);
      const metrics = ctx.measureText(text);
      let startX = x;
      if (align === "center") startX = x - metrics.width / 2;
      else if (align === "right") startX = x - metrics.width;
      ctx.beginPath();
      ctx.moveTo(startX, y + 4);
      ctx.lineTo(startX + metrics.width, y + 4);
      ctx.lineWidth = 1.6;
      ctx.strokeStyle = ctx.fillStyle;
      ctx.stroke();
    }

    function renderPklToCanvas(po, targetCanvas) {
      const canvas = targetCanvas || document.getElementById("pklCanvas");
      if (!canvas) return;
      const W = 1684;
      const H = 1190;
      canvas.width = W;
      canvas.height = H;
      const ctx = canvas.getContext("2d");

      // White A4 Landscape background
      ctx.fillStyle = "#ffffff";
      ctx.fillRect(0, 0, W, H);

      const vendor = (po.donViVcPo || "VIETSUN").toUpperCase().trim();
      const plate = (po.soXePo || "").toUpperCase().trim();
      const cangXep = (po.cangDiPo || "BẾN NGHÉ").toUpperCase().trim();
      const cangDo = (po.tinhXuatHangPo || po.cangDenPo || "").toUpperCase().trim();
      let chuHang = (po.chuHangPo || "").toUpperCase().trim();
      if (chuHang && !chuHang.startsWith("NHAT VIET")) {
        chuHang = "NHAT VIET - " + chuHang;
      }
      const ngayTau = (po.ngayTauChayPo && po.ngayTauChayPo !== "2021-11-11") ? formatIsoToVN(po.ngayTauChayPo) : "";
      const tau = (po.tauPo || "").toUpperCase().trim();
      const chuyen = (po.chuyenPo || "").toUpperCase().trim();
      const soCont = (po.soContPo || "").toUpperCase().trim();
      const soSeal = (po.soSealPo || "").trim();
      const c20 = Number(po.cont20Po) > 0 ? "1" : "0";
      const c40 = Number(po.cont40Po) > 0 ? "1" : "0";
      const hangHoa = (po.tenHangHoaPo || "").trim();
      const trongLuong = po.trongLuongPo ? Number(po.trongLuongPo).toFixed(2) : "";
      const tenKhach = (po.tenKhachPo || "").trim();
      const ngayHa = (po.ngayTraHang && po.ngayTraHang !== "2021-11-11") ? formatIsoToVN(po.ngayTraHang) : formatIsoToVN(getTodayISO());

      // 1. Top-Right Red Fleet & Plate Header
      ctx.fillStyle = "#ea0000";
      ctx.font = 'bold 36px "Times New Roman", Times, serif';
      ctx.textAlign = "right";
      ctx.fillText(`${vendor}${plate ? " - " + plate : ""}`, 1580, 140);

      // 2. Top-Left Vietsun Logo & Company Block
      if (vietsunLogoImg && vietsunLogoImg.complete && vietsunLogoImg.naturalWidth > 0) {
        ctx.drawImage(vietsunLogoImg, 145, 210, 305, 155);
      } else {
        ctx.fillStyle = "#1e3a8a";
        ctx.font = 'bold 54px "Times New Roman", Times, serif';
        ctx.textAlign = "center";
        ctx.fillText("VSC", 295, 295);
        ctx.font = '18px Arial, sans-serif';
        ctx.fillText("VIETSUN CORPORATION", 295, 335);
      }

      ctx.fillStyle = "#000000";
      ctx.textAlign = "left";
      ctx.font = 'bold 17.5px "Times New Roman", Times, serif';
      ctx.fillText("CÔNG TY CP NHẬT VIỆT- VIETSUN CORP", 104, 460);
      ctx.fillText("HÃNG TÀU NHẬT VIỆT - VIETSUN LINES", 104, 490);
      ctx.font = 'bold 16.5px "Times New Roman", Times, serif';
      ctx.fillText("Số 13-15, Đường B4, P. An Khánh, TP.HCM", 104, 520);
      ctx.fillText("TEL: 028 3826 9453 FAX: 028.39433114", 104, 550);
      ctx.fillText("WEBSITE : www.vietsunlogistic.com", 104, 580);

      // 3. Center-Right Header Block
      ctx.fillStyle = "#000000";
      ctx.font = 'bold 31px "Times New Roman", Times, serif';
      ctx.textAlign = "center";
      ctx.fillText("BẢNG KÊ HÀNG HOÁ VẬN CHUYỂN NỘI ĐỊA", 1074, 235);

      ctx.font = '25px "Times New Roman", Times, serif';
      ctx.fillText("(CONTAINER PACKING LIST)", 1074, 274);

      ctx.font = 'bold 25px "Times New Roman", Times, serif';
      drawUnderlinedText(ctx, "KÍNH GỬI CẢNG:", 566, 310, "left");
      drawUnderlinedText(ctx, cangXep, 828, 310, "left");

      ctx.font = 'bold 22px "Times New Roman", Times, serif';
      drawUnderlinedText(ctx, "CHI PHÍ HẠ TẠI CẢNG THANH TOÁN SAU CHO CÔNG TY CP NHẬT VIỆT", 566, 348, "left");

      ctx.font = 'italic 23px "Times New Roman", Times, serif';
      ctx.textAlign = "left";
      ctx.fillText("(Note : Vui lòng đối chiếu trên hệ thống với hãng tàu )", 566, 382);

      // 4. Upper Vessel & Route Table (x: 564..1584, y: 412..576)
      const ux = [564, 716, 824, 970, 1126, 1278, 1418, 1584];
      const uy = [412, 439, 466, 493, 520, 548, 576];

      ctx.strokeStyle = "#000000";
      ctx.lineWidth = 1.6;
      ctx.strokeRect(ux[0], uy[0], ux[7] - ux[0], uy[6] - uy[0]);

      // Horizontal lines in Upper Table
      for (let r = 1; r <= 4; r++) {
        ctx.beginPath();
        ctx.moveTo(ux[0], uy[r]);
        ctx.lineTo(ux[7], uy[r]);
        ctx.stroke();
      }
      // Sub-row line between SOC and COC (from ux[1] to ux[5])
      ctx.beginPath();
      ctx.moveTo(ux[1], uy[5]);
      ctx.lineTo(ux[5], uy[5]);
      ctx.stroke();

      // Vertical lines in Upper Table
      // Col 1 border at ux[2]=824 (all rows)
      ctx.beginPath(); ctx.moveTo(ux[2], uy[0]); ctx.lineTo(ux[2], uy[6]); ctx.stroke();
      // Sub-col at ux[1]=716 (rows 4..6)
      ctx.beginPath(); ctx.moveTo(ux[1], uy[4]); ctx.lineTo(ux[1], uy[6]); ctx.stroke();
      // 20'/40'/45' dividers at ux[3]=970, ux[4]=1126 (rows 3..6)
      ctx.beginPath(); ctx.moveTo(ux[3], uy[3]); ctx.lineTo(ux[3], uy[6]); ctx.stroke();
      ctx.beginPath(); ctx.moveTo(ux[4], uy[3]); ctx.lineTo(ux[4], uy[6]); ctx.stroke();
      // Col border at ux[5]=1278 (all rows)
      ctx.beginPath(); ctx.moveTo(ux[5], uy[0]); ctx.lineTo(ux[5], uy[6]); ctx.stroke();
      // Col border at ux[6]=1418 (rows 0..3)
      ctx.beginPath(); ctx.moveTo(ux[6], uy[0]); ctx.lineTo(ux[6], uy[3]); ctx.stroke();

      // Upper Table Text
      ctx.fillStyle = "#000000";
      ctx.font = 'italic 18px "Times New Roman", Times, serif';
      ctx.textAlign = "left";
      ctx.fillText("Chủ hàng", ux[0] + 6, uy[0] + 20);
      ctx.fillText("Dự kiến xuất tàu", ux[0] + 6, uy[1] + 20);
      ctx.fillText("Số chuyến", ux[0] + 6, uy[2] + 20);
      ctx.fillText("Loại container", ux[0] + 6, uy[3] + 20);
      ctx.fillText("Số lượng", ux[0] + 6, uy[4] + 34);

      ctx.fillText("Ngày tàu chạy", ux[5] + 6, uy[0] + 20);
      ctx.fillText("Cảng xếp", ux[5] + 6, uy[1] + 20);
      ctx.fillText("Cảng dỡ", ux[5] + 6, uy[2] + 20);

      ctx.textAlign = "center";
      ctx.fillText("20'", (ux[2] + ux[3]) / 2, uy[3] + 20);
      ctx.fillText("40'", (ux[3] + ux[4]) / 2, uy[3] + 20);
      ctx.fillText("45'", (ux[4] + ux[5]) / 2, uy[3] + 20);
      ctx.fillText("CLOSING TIME", (ux[5] + ux[7]) / 2, uy[3] + 20);
      ctx.fillText("SOC", (ux[1] + ux[2]) / 2, uy[4] + 20);
      ctx.fillText("COC", (ux[1] + ux[2]) / 2, uy[5] + 20);

      // Upper Table Values
      const midValX = (ux[2] + ux[5]) / 2;
      const rightValX = (ux[6] + ux[7]) / 2;

      ctx.font = 'bold 19px "Times New Roman", Times, serif';
      ctx.fillStyle = "#000000";
      ctx.fillText(chuHang, midValX, uy[0] + 20);
      ctx.fillText(cangXep, rightValX, uy[1] + 20);
      ctx.fillText("TRƯỚC: .....H..... NGÀY ...../...../202", (ux[5] + ux[7]) / 2, uy[4] + 35);

      ctx.fillStyle = "#ea0000";
      ctx.fillText(ngayTau, rightValX, uy[0] + 20);
      ctx.fillText(tau, midValX, uy[1] + 20);
      ctx.fillText(chuyen, midValX, uy[2] + 20);
      ctx.fillText(cangDo, rightValX, uy[2] + 20);

      // 5. Middle Container Table (x: 100..1584, y: 602..718)
      const mx = [100, 166, 428, 564, 644, 738, 824, 1136, 1278, 1412, 1584];
      const my = [602, 629, 656, 687, 718];

      ctx.strokeStyle = "#000000";
      ctx.lineWidth = 1.6;
      ctx.strokeRect(mx[0], my[0], mx[10] - mx[0], my[4] - my[0]);

      // Horizontal lines in Middle Table
      // Subheader line under "Loại container" (mx[3]..mx[6])
      ctx.beginPath(); ctx.moveTo(mx[3], my[1]); ctx.lineTo(mx[6], my[1]); ctx.stroke();
      for (let r = 2; r <= 3; r++) {
        ctx.beginPath(); ctx.moveTo(mx[0], my[r]); ctx.lineTo(mx[10], my[r]); ctx.stroke();
      }

      // Vertical lines in Middle Table
      for (let c = 1; c <= 9; c++) {
        const startY = (c === 4 || c === 5) ? my[1] : my[0];
        ctx.beginPath(); ctx.moveTo(mx[c], startY); ctx.lineTo(mx[c], my[4]); ctx.stroke();
      }

      // Middle Table Headers
      ctx.fillStyle = "#000000";
      ctx.font = 'bold italic 18px "Times New Roman", Times, serif';
      ctx.textAlign = "center";
      ctx.fillText("STT", (mx[0] + mx[1]) / 2, my[0] + 34);
      ctx.fillText("Số container", (mx[1] + mx[2]) / 2, my[0] + 34);
      ctx.fillText("Số seal", (mx[2] + mx[3]) / 2, my[0] + 34);
      ctx.fillText("Loại container", (mx[3] + mx[6]) / 2, my[0] + 20);
      ctx.fillText("20", (mx[3] + mx[4]) / 2, my[1] + 20);
      ctx.fillText("40", (mx[4] + mx[5]) / 2, my[1] + 20);
      ctx.fillText("45'", (mx[5] + mx[6]) / 2, my[1] + 20);
      ctx.fillText("Hàng Hoá", (mx[6] + mx[7]) / 2, my[0] + 34);
      ctx.fillText("Trọng lượng", (mx[7] + mx[8]) / 2, my[0] + 34);
      ctx.fillText("DG - U.N.", (mx[8] + mx[9]) / 2, my[0] + 24);
      ctx.fillText("CLASS", (mx[8] + mx[9]) / 2, my[0] + 45);
      ctx.fillText("DG - U.N. Number", (mx[9] + mx[10]) / 2, my[0] + 34);

      // Middle Table Row 1 Data
      ctx.font = '22px "Times New Roman", Times, serif';
      ctx.fillStyle = "#000000";
      ctx.fillText("1", (mx[0] + mx[1]) / 2, my[2] + 23);

      ctx.font = '24px "Times New Roman", Times, serif';
      ctx.fillStyle = "#ea0000";
      ctx.fillText(soCont, (mx[1] + mx[2]) / 2, my[2] + 23);
      ctx.fillText(soSeal, (mx[2] + mx[3]) / 2, my[2] + 23);
      ctx.fillText(c20, (mx[3] + mx[4]) / 2, my[2] + 23);
      ctx.fillText(c40, (mx[4] + mx[5]) / 2, my[2] + 23);
      ctx.fillText("0", (mx[5] + mx[6]) / 2, my[2] + 23);
      ctx.fillText(hangHoa, (mx[6] + mx[7]) / 2, my[2] + 23);
      ctx.fillText(trongLuong, (mx[7] + mx[8]) / 2, my[2] + 23);

      // 6. Bottom Notes & Signatures
      ctx.fillStyle = "#000000";
      ctx.font = '17.5px "Times New Roman", Times, serif';
      ctx.textAlign = "left";
      ctx.fillText("Khách hàng chịu trách nhiệm về việc kê khai packing list.", 104, 762);
      ctx.fillText("Nội dung trên packing list và trên chi tiết khách hàng cung cấp lập Bill phải trùng khớp, chính xác với thực tế.", 104, 790);
      ctx.fillText("Không nhận các mặt hàng thuộc danh sách hàng \"cấm vận chuyển\" theo Luật pháp Việt Nam.", 104, 818);
      ctx.fillText("Không nhận vận chuyển các mặt hàng : Gỗ tròn, gỗ tự nhiên, sắt / thép vụn – phế liệu - than đóng xá, than bùn.", 104, 846);
      ctx.fillText("Hàng nguy hiểm phải thông báo trước, được khai báo và tuân theo quy định vận chuyển.", 104, 874);
      ctx.fillText(tenKhach, 104, 902);

      // Right Box: Giờ, ngày container hạ cảng
      ctx.strokeRect(1136, 742, 448, 29);
      ctx.font = 'italic 19px "Times New Roman", Times, serif';
      drawUnderlinedText(ctx, "Giờ, ngày container hạ cảng", 1360, 763, "center");

      ctx.font = 'bold 18px "Times New Roman", Times, serif';
      ctx.textAlign = "center";
      ctx.fillText(ngayHa, 1360, 804);

      // Signatures
      ctx.font = 'bold 19px "Times New Roman", Times, serif';
      ctx.fillText("ĐẠI DIỆN CHỦ HÀNG", 368, 926);
      ctx.fillText(`ĐẠI DIỆN CẢNG ${cangXep}`, 1298, 926);

      ctx.font = '18px "Times New Roman", Times, serif';
      ctx.fillText("(ký và ghi rõ họ tên)", 368, 954);
      ctx.fillText("(ký và ghi rõ họ tên)", 1298, 954);
    }

    // Pure-JS A4 Landscape PDF Builder from Canvas (Zero external libraries)
    function buildPklPdfBlob() {
      if (customGasPdfBase64) {
        const arr = customGasPdfBase64.split(",");
        const bstr = atob(arr[1] || arr[0]);
        const u8 = new Uint8Array(bstr.length);
        for (let i = 0; i < bstr.length; i++) u8[i] = bstr.charCodeAt(i);
        return new Blob([u8], { type: "application/pdf" });
      }

      const canvas = document.getElementById("pklCanvas");
      const dataUrl = canvas.toDataURL("image/jpeg", 0.95);
      const base64 = dataUrl.split(",")[1];
      const binStr = atob(base64);
      const jpgBytes = new Uint8Array(binStr.length);
      for (let i = 0; i < binStr.length; i++) {
        jpgBytes[i] = binStr.charCodeAt(i);
      }

      const w = canvas.width;
      const h = canvas.height;
      const pageW = 841.89;
      const pageH = 595.28;

      const enc = new TextEncoder();
      const chunks = [];
      const offsets = [0];
      let byteLen = 0;

      function pushStr(s) {
        const b = enc.encode(s);
        chunks.push(b);
        byteLen += b.length;
      }
      function pushBytes(b) {
        chunks.push(b);
        byteLen += b.length;
      }

      pushStr("%PDF-1.4\n%\xFF\xFF\xFF\xFF\n");

      // 1: Catalog
      offsets[1] = byteLen;
      pushStr("1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n");

      // 2: Pages
      offsets[2] = byteLen;
      pushStr("2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n");

      // 3: Page (A4 Landscape)
      offsets[3] = byteLen;
      pushStr(`3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${pageW} ${pageH}] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>\nendobj\n`);

      // 4: Image XObject
      offsets[4] = byteLen;
      pushStr(`4 0 obj\n<< /Type /XObject /Subtype /Image /Width ${w} /Height ${h} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${jpgBytes.length} >>\nstream\n`);
      pushBytes(jpgBytes);
      pushStr("\nendstream\nendobj\n");

      // 5: Content Stream
      const contentStream = `q ${pageW} 0 0 ${pageH} 0 0 cm /Im0 Do Q\n`;
      offsets[5] = byteLen;
      pushStr(`5 0 obj\n<< /Length ${contentStream.length} >>\nstream\n${contentStream}endstream\nendobj\n`);

      // xref
      const xrefOffset = byteLen;
      pushStr("xref\n0 6\n0000000000 65535 f \n");
      for (let i = 1; i <= 5; i++) {
        pushStr(String(offsets[i]).padStart(10, "0") + " 00000 n \n");
      }
      pushStr(`trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n${xrefOffset}\n%%EOF`);

      return new Blob(chunks, { type: "application/pdf" });
    }

    function getPklFileName() {
      if (!currentPklPo) return "PackingList.pdf";
      const plate = (currentPklPo.soXePo || "XE").replace(/[^A-Z0-9]/gi, "");
      const cont = (currentPklPo.soContPo || currentPklPo.idPo || "CONT").replace(/[^A-Z0-9]/gi, "");
      return `PKL_${plate}_${cont}.pdf`;
    }

    async function sharePklPdf() {
      try {
        const blob = buildPklPdfBlob();
        const fileName = getPklFileName();
        const file = new File([blob], fileName, { type: "application/pdf" });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
          await navigator.share({
            files: [file],
            title: `Phiếu PKL ${currentPklPo?.soContPo || ""}`,
            text: `Phiếu PKL hạ cảng xe ${currentPklPo?.soXePo || ""} - Cont ${currentPklPo?.soContPo || ""}`
          });
          showToast("Đã mở bảng chia sẻ PDF!", "success");
        } else {
          downloadPklPdf();
        }
      } catch (err) {
        if (err && err.name !== "AbortError") {
          downloadPklPdf();
        }
      }
    }

    function downloadPklPdf() {
      const blob = buildPklPdfBlob();
      const fileName = getPklFileName();
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 3000);
      showToast(`Đã tải file ${fileName}!`, "success");
    }

    function printPklCanvas() {
      const canvas = document.getElementById("pklCanvas");
      const imgData = canvas.toDataURL("image/png");
      const w = window.open("", "_blank");
      if (!w) {
        downloadPklPdf();
        return;
      }
      w.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>${getPklFileName()}</title>
          <style>
            @page { size: A4 landscape; margin: 0; }
            body { margin: 0; display: flex; align-items: center; justify-content: center; background: #fff; }
            img { width: 100vw; height: 100vh; object-fit: contain; }
          </style>
        </head>
        <body onload="setTimeout(() => { window.print(); }, 200);">
          <img src="${imgData}" />
        </body>
        </html>
      `);
      w.document.close();
    }

    // =========================================================================
    // 9C. BULK PRINTING: PKL (TAB HÀNG XUẤT) & BIÊN BẢN (TAB HÀNG NHẬP)
    //     Tự động nhận diện Form Biên Bản theo KHO NHẬN (khoNhanHangPo)
    // =========================================================================
    const PROVINCE_VN_LABEL = {
      "HO CHI MINH": "Tp. Hồ Chí Minh",
      "BINH DUONG": "Bình Dương",
      "DONG NAI": "Đồng Nai",
      "LONG AN": "Long An",
      "TAY NINH": "Tây Ninh",
      "BA RIA - VUNG TAU": "Bà Rịa - Vũng Tàu",
      "BINH PHUOC": "Bình Phước",
      "BINH THUAN": "Bình Thuận",
      "LAM DONG": "Lâm Đồng",
      "TIEN GIANG": "Tiền Giang",
      "BEN TRE": "Bến Tre",
      "TRA VINH": "Trà Vinh",
      "VINH LONG": "Vĩnh Long",
      "DONG THAP": "Đồng Tháp",
      "AN GIANG": "An Giang",
      "KIEN GIANG": "Kiên Giang",
      "CAN THO": "Cần Thơ",
      "HAU GIANG": "Hậu Giang",
      "SOC TRANG": "Sóc Trăng",
      "BAC LIEU": "Bạc Liêu",
      "CA MAU": "Cà Mau",
      "THONG BAO SAU": "Thông báo sau"
    };

    function toCleanUpperCode(str) {
      return String(str || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d")
        .replace(/Đ/g, "D")
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, "");
    }

    function getDefault4OrangesHtml() {
      const baseOrigin = window.location.origin;
      return `<div class="bb-4oranges">
  <div class="thna-header">
    <div class="thna-logo-col">
      <img src="${baseOrigin}/logoThna.png" alt="THNA Logo" class="thna-logo-img" />
    </div>
    <div class="thna-info-col">
      <div class="thna-title">THNA GROUP</div>
      <div class="thna-sub"><span style="color:#0000FF;">The No.1 Vietnam </span><span style="color:#222222;">CaCO3 powder</span></div>
      <div class="thna-line">2<sup>nd</sup> Floor, Trung Hai Nghe An Building, Quy Hop, Nghe An, Vietnam</div>
      <div class="thna-line">Phone:&nbsp; +84 238 398 2777 &ndash;&nbsp;&nbsp; Email:&nbsp;<span class="thna-link">info@thnagroup.com</span></div>
      <div class="thna-line">Hotline: +84 97&nbsp; 109 2777 &ndash;&nbsp; Website:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="thna-link">thnagroup.com</span></div>
    </div>
  </div>
  <div class="thna-divider">-----------------------------------------------------------------------------------------------------------------------------------------</div>
  <div class="thna-doc-title">BIÊN BẢN GIAO NHẬN HÀNG HÓA</div>
  <div class="thna-doc-date">Ngày {{NGAY}}&nbsp;&nbsp;tháng {{THANG}}&nbsp;&nbsp;&nbsp;năm {{NAM}}</div>
  <div class="thna-section">
    <div class="thna-bold-12">BÊN GIAO HÀNG: CÔNG TY CỔ PHẦN TRUNG HẢI- NGHỆ AN GROUP.</div>
    <div class="thna-bold-12" style="padding-left:28px;">(THNA GROUP)</div>
    <div class="thna-driver-line">
      <span style="display:inline-block;width:58%;padding-left:48px;">Tên Tài xế:</span>
      <span>Biển số xe:</span>
    </div>
  </div>
  <div class="thna-section" style="margin-top:14px;">
    <div class="thna-bold-13">BÊN NHẬN HÀNG: CÔNG TY 4ORANGES CO.,LTD</div>
    <div class="thna-bold-13">Địa chỉ:&nbsp; Lô C-02-1, KCN Đức&nbsp; Hoà 1, Ấp 5, Xã Mỹ Hạnh, Tỉnh Tây Ninh, Việt Nam.</div>
  </div>
  <table class="thna-table">
    <thead>
      <tr>
        <th style="width:7.6%;">STT</th>
        <th style="width:23.7%;">Số cont/Seal</th>
        <th style="width:15.6%;">Số lượng</th>
        <th style="width:26.55%;">Khối lượng ( tấn)</th>
        <th style="width:26.55%;">Mã Hàng</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:center;">1</td>
        <td style="text-align:center;">{{SO_CONT}}</td>
        <td></td>
        <td></td>
        <td style="text-align:center;">THNA07</td>
      </tr>
      <tr>
        <td style="text-align:center;">2</td>
        <td style="text-align:center;padding-left:22px;">{{SO_SEAL}}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <div class="thna-weight-words">Khối lượng hàng bằng chữ: </div>
  <div class="thna-signatures">
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên nhận hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên giao hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
  </div>
</div>`;
    }

    function getDefaultStandardHtml() {
      const baseOrigin = window.location.origin;
      return `<div class="bb-vietsun">
  <div class="vs-div-picture">
    <img class="vs-watermark-img" src="${baseOrigin}/vietsunwatermark2.png" alt="" />
    <img class="vs-seal-img" src="${baseOrigin}/seal_vietsun.png" alt="" />
    <div class="vs-content-layer">
      <table class="vs-header-table">
        <tr>
          <td style="width:170px;vertical-align:top;text-align:center;">
            <img src="${baseOrigin}/logo-vietsunnew.png" alt="Company Logo" style="height:75px;width:auto;" />
          </td>
          <td style="text-align:center;font-family:Arial,Helvetica,sans-serif;">
            <div style="font-size:19px;font-weight:700;line-height:1.2;">VIETSUN LOGISTICS - VIETSUN CORPORATION</div>
            <div style="font-size:12.5px;line-height:1.35;margin-top:4px;">
              Số 13-15 đường B4, Phường An Khánh, Thành Phố Hồ Chí Minh<br/>
              Tel : 08 38269 539 - 38269 540 -38269 541 - Ext :307; Fax: (84.8) 8269542<br/>
              Website : www.vietsunlogistic.com<br/><br/>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:center;padding-top:4px;">
            <span style="font-size:23pt;font-weight:700;">BIÊN BẢN GIAO NHẬN HÀNG</span>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:right;padding-top:2px;">
            <span style="font-size:12.5pt;font-style:italic;">{{NGAY_THANG_NAM}}</span>
          </td>
        </tr>
      </table>
      <p style="margin:8px 0 4px;line-height:1.75;">
        Căn cứ vào hợp đồng vận chuyển (hoặc booking note) số ................... ngày ...... tháng ... năm....<br/>
        <span style="font-weight:700;text-decoration:underline;">Chúng tôi gồm:</span>
      </p>
      <table class="vs-info-table">
        <tbody>
          <tr>
            <th colspan="4" style="text-align:left;"><span style="text-decoration:underline;">BÊN GIAO</span>: CÔNG TY CỔ PHẦN NHẬT VIỆT</th>
          </tr>
          <tr>
            <td style="width:15%;">Địa chỉ</td>
            <td colspan="3">:Số 13-15 đường B4, Phường An Khánh, Thành Phố Hồ Chí Minh</td>
          </tr>
          <tr>
            <td>Điện thoại</td>
            <td colspan="3">: 08 38269539 </td>
          </tr>
          <tr>
            <td>Đại diện</td>
            <td style="width:53%;">: Ông......................................................................</td>
            <td style="width:11%;">Chức vụ</td>
            <td>: NV Lái xe</td>
          </tr>
          <tr>
            <td>Số xe vận tải</td>
            <td colspan="3">:..............................................................................</td>
          </tr>
          <tr>
            <th style="text-align:left;text-decoration:underline;vertical-align:top;">BÊN NHẬN</th>
            <th colspan="3" style="text-align:left;text-transform:uppercase;">: {{KHO_NHAN}}</th>
          </tr>
          <tr>
            <td style="vertical-align:top;">Địa chỉ</td>
            <td colspan="3">: {{DIA_CHI}}</td>
          </tr>
          <tr>
            <td>Đại diện</td>
            <td>: Ông/Bà................................................................</td>
            <td>Chức vụ</td>
            <td>:.....................................</td>
          </tr>
        </tbody>
      </table>
      <div style="margin:4px 0;">Cùng thống nhất lập biên bản giao nhận hàng hóa trên với nội dung như sau:</div>
      <table class="vs-bangND">
        <thead>
          <tr>
            <th style="width:28%;">SỐ CONT</th>
            <th style="width:22%;">SỐ SEAL</th>
            <th style="width:25%;">HÀNG HÓA</th>
            <th style="width:25%;">GHI CHÚ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{SO_CONT}}</td>
            <td>{{SO_SEAL}}</td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td><span style="color:#ffffff;">.</span></td>
          </tr>
        </tbody>
      </table>
      <p style="margin:8px 0 12px;line-height:1.8;">
        <span style="font-weight:700;text-decoration:underline;">Hình thức giao nhận: </span> Nguyên Cont, nguyên Seal. Bên giao không chịu trách nhiệm về số lượng, trọng lượng hàng hóa bên trong Cont.<br/>
        <span style="font-weight:700;text-decoration:underline;">Địa điểm giao, nhận hàng: </span>..............................................................................................................<br/>
        <span style="font-weight:700;text-decoration:underline;">Chứng từ kèm theo: </span>........................................................................................................................<br/>
        Hai bên đã kiểm tra kỹ số Cont, số Seal như trên là đúng và cùng thống nhất giao nhận hàng với nội dung như trên. Biên bản được lập thành hai (02) bản, nội dung và giá trị như nhau mỗi bên giữ một (01) bản ./
      </p>
      <div class="vs-sig-row">
        <div class="vs-sig-col">ĐẠI DIỆN BÊN GIAO</div>
        <div class="vs-sig-col"></div>
        <div class="vs-sig-col">ĐẠI DIỆN BÊN NHẬN</div>
      </div>
    </div>
  </div>
</div>`;
    }

    function getBuiltInBbTemplates() {
      return [
        {
          id: "4ORANGES",
          name: "Form 4 Oranges (THNA Group)",
          short_badge: "4Oranges",
          keywords: "4 ORANGES, 4ORANGES",
          html_content: getDefault4OrangesHtml(),
          is_active: 1,
          sort_order: 1
        },
        {
          id: "STANDARD",
          name: "Form Chuẩn Vietsun",
          short_badge: "BB",
          keywords: "*",
          html_content: getDefaultStandardHtml(),
          is_active: 1,
          sort_order: 999
        }
      ];
    }

    let bbTemplatesList = [];
    let currentEditingBbTplId = "4ORANGES";
    let isBbLivePreviewMode = false;
    let rawDraftBbHtmlBeforePreview = "";

    function initBbTemplatesLocal() {
      const builtIns = getBuiltInBbTemplates();
      let saved = [];
      try {
        saved = JSON.parse(localStorage.getItem("vs_bb_templates_v1") || "[]");
      } catch (_) {}

      const map = new Map();
      builtIns.forEach(t => map.set(t.id, { ...t }));
      if (Array.isArray(saved)) {
        saved.forEach(t => {
          if (t && t.id && t.html_content) {
            map.set(t.id, { ...t, is_active: Number(t.is_active ?? 1) });
          }
        });
      }
      bbTemplatesList = Array.from(map.values()).sort((a, b) => Number(a.sort_order || 10) - Number(b.sort_order || 10));
      updateBbTplCountBadge();
    }

    async function loadBbTemplatesFromServer() {
      initBbTemplatesLocal();
      try {
        const resp = await fetch("/api/index.php?route=bb_templates");
        if (!resp.ok) return;
        const data = await resp.json();
        if (data && data.ok && Array.isArray(data.templates) && data.templates.length > 0) {
          const builtIns = getBuiltInBbTemplates();
          const map = new Map();
          builtIns.forEach(t => map.set(t.id, { ...t }));
          data.templates.forEach(t => {
            if (t && t.id && t.html_content) {
              map.set(t.id, { ...t, is_active: Number(t.is_active ?? 1) });
            }
          });
          bbTemplatesList = Array.from(map.values()).sort((a, b) => Number(a.sort_order || 10) - Number(b.sort_order || 10));
          try {
            localStorage.setItem("vs_bb_templates_v1", JSON.stringify(bbTemplatesList));
          } catch (_) {}
          updateBbTplCountBadge();
          if (state.viewMode === "table" && state.xuatNhap === "N") {
            renderShipmentList();
          }
        }
      } catch (_) {}
    }

    let bbTemplateCacheByKho = new Map();

    function updateBbTplCountBadge() {
      bbTemplateCacheByKho.clear();
      const el = document.getElementById("bbTplCountBadge");
      if (el) el.textContent = bbTemplatesList.length || 2;
    }

    // Khởi tạo ngay danh sách Form BB khi nạp trang
    initBbTemplatesLocal();
    setTimeout(() => { loadBbTemplatesFromServer(); }, 600);

    function detectBbTemplate(po) {
      if (!bbTemplatesList || bbTemplatesList.length === 0) initBbTemplatesLocal();
      const rawKho = (po && po.khoNhanHangPo ? String(po.khoNhanHangPo) : "").trim();
      const rawKhach = (po && po.tenKhachPo ? String(po.tenKhachPo) : "").trim();
      const cacheKey = `${rawKhach}|||${rawKho}`;
      if (bbTemplateCacheByKho.has(cacheKey)) {
        return bbTemplateCacheByKho.get(cacheKey);
      }
      const cleanKho = toCleanUpperCode(rawKho);
      const cleanKhach = toCleanUpperCode(rawKhach);

      if (cleanKho || cleanKhach) {
        for (const tpl of bbTemplatesList) {
          if (tpl.id === "STANDARD" || !Number(tpl.is_active ?? 1)) continue;

          const custKwList = String(tpl.customer_keywords || "")
            .split(",")
            .map(k => k.trim())
            .filter(k => k && k !== "*");
          const khoKwList = String(tpl.keywords || "")
            .split(",")
            .map(k => k.trim())
            .filter(k => k && k !== "*");

          if (custKwList.length === 0 && khoKwList.length === 0) continue;

          // Kiểm tra khớp Khách Hàng (nếu Form có khai báo Từ khóa Khách Hàng)
          let custMatch = true;
          if (custKwList.length > 0) {
            custMatch = custKwList.some(kw => {
              const cKw = toCleanUpperCode(kw);
              return cKw && (cleanKhach.includes(cKw) || rawKhach.toUpperCase().includes(kw.toUpperCase()));
            });
          }

          // Kiểm tra khớp Kho Nhận (nếu Form có khai báo Từ khóa Kho Nhận)
          let khoMatch = true;
          if (khoKwList.length > 0) {
            khoMatch = khoKwList.some(kw => {
              const cKw = toCleanUpperCode(kw);
              return cKw && (cleanKho.includes(cKw) || rawKho.toUpperCase().includes(kw.toUpperCase()));
            });
          }

          if (custMatch && khoMatch) {
            const res = {
              id: tpl.id,
              code: tpl.id,
              name: tpl.name,
              shortBadge: tpl.short_badge || "Form Riêng",
              html_content: tpl.html_content
            };
            bbTemplateCacheByKho.set(cacheKey, res);
            return res;
          }
        }
      }

      const stdTpl = bbTemplatesList.find(t => t.id === "STANDARD") || getBuiltInBbTemplates()[1];
      const stdRes = {
        id: "STANDARD",
        code: "STANDARD",
        name: stdTpl.name || "Form Chuẩn Vietsun",
        shortBadge: stdTpl.short_badge || "BB",
        html_content: stdTpl.html_content
      };
      bbTemplateCacheByKho.set(cacheKey, stdRes);
      return stdRes;
    }

    function renderTemplateWithPoData(htmlContent, po) {
      const baseOrigin = window.location.origin;
      const soCont = escapeAttr((po.soContPo || "").toUpperCase().trim());
      const soSeal = escapeAttr((po.soSealPo || "").trim());
      const hasValidDate = po.ngayTraHang && po.ngayTraHang !== "2021-11-11" && po.ngayTraHang !== "1970-01-01";

      let dd = ".....", mm = ".....", yyyy = String(new Date().getFullYear());
      if (hasValidDate) {
        const parts = String(po.ngayTraHang).split("-");
        if (parts.length === 3) {
          yyyy = parts[0];
          mm = parts[1];
          dd = parts[2];
        }
      }

      const vsDateStr = hasValidDate
        ? `Ngày ${dd} tháng ${mm}  năm ${yyyy} `
        : `Ngày ..... tháng ..... năm .......`;
      const khoNhan = escapeAttr((po.khoNhanHangPo || po.tenKhachPo || "").trim());
      const khachHang = escapeAttr((po.tenKhachPo || "").trim());
      const rawDiaChi = (po.diaChiNhanPo || "").trim();
      const rawTinh = (po.tinhNhanPo || "").toUpperCase().trim();
      const tinhLabel = PROVINCE_VN_LABEL[rawTinh] || po.tinhNhanPo || "";
      const fullDiaChi = escapeAttr([rawDiaChi, tinhLabel].filter(Boolean).join(", "));
      const soXe = escapeAttr((po.soXePo || "").toUpperCase().trim());
      const donViVc = escapeAttr((po.donViVcPo || "").toUpperCase().trim());
      const drvInfo = getDriverForPlate(po.soXePo);
      const taiXe = escapeAttr(drvInfo && drvInfo.name ? drvInfo.name : "");
      const sdtTaiXe = escapeAttr(drvInfo && drvInfo.phone ? drvInfo.phone : "");
      const trongLuong = escapeAttr(po.trongLuongPo ? String(po.trongLuongPo) : "");
      const hangHoa = escapeAttr((po.tenHangHoaPo || "").trim());
      const tauChuyen = escapeAttr([po.tauPo, po.chuyenPo].filter(Boolean).join(" / "));

      return String(htmlContent || "")
        .replace(/\{\{BASE_ORIGIN\}\}/g, baseOrigin)
        .replace(/\{\{SO_CONT\}\}/g, soCont)
        .replace(/\{\{SO_SEAL\}\}/g, soSeal)
        .replace(/\{\{NGAY\}\}/g, dd)
        .replace(/\{\{THANG\}\}/g, mm)
        .replace(/\{\{NAM\}\}/g, yyyy)
        .replace(/\{\{NGAY_THANG_NAM\}\}/g, vsDateStr)
        .replace(/\{\{KHO_NHAN\}\}/g, khoNhan)
        .replace(/\{\{KHACH_HANG\}\}/g, khachHang)
        .replace(/\{\{DIA_CHI\}\}/g, fullDiaChi)
        .replace(/\{\{TINH\}\}/g, escapeAttr(tinhLabel))
        .replace(/\{\{SO_XE\}\}/g, soXe)
        .replace(/\{\{DON_VI_VC\}\}/g, donViVc)
        .replace(/\{\{TAI_XE\}\}/g, taiXe)
        .replace(/\{\{SDT_TAI_XE\}\}/g, sdtTaiXe)
        .replace(/\{\{TRONG_LUONG\}\}/g, trongLuong)
        .replace(/\{\{HANG_HOA\}\}/g, hangHoa)
        .replace(/\{\{TAU_CHUYEN\}\}/g, tauChuyen);
    }

    // =========================================================================
    // TRÌNH QUẢN LÝ & SOẠN THẢO TRỰC QUAN MẪU BIÊN BẢN (VISUAL BB TEMPLATE MANAGER)
    // =========================================================================
    function openBbTemplateManager(selectId) {
      if (!bbTemplatesList || bbTemplatesList.length === 0) initBbTemplatesLocal();
      const targetId = selectId || currentEditingBbTplId || (bbTemplatesList[0] && bbTemplatesList[0].id) || "4ORANGES";
      selectBbTemplateInEditor(targetId);
      const modal = document.getElementById("bbTemplateModal");
      if (modal) modal.classList.add("open");
    }

    function closeBbTemplateManager() {
      closeAllBbMetaAc();
      const modal = document.getElementById("bbTemplateModal");
      if (modal) modal.classList.remove("open");
      if (isBbLivePreviewMode) {
        isBbLivePreviewMode = false;
      }
    }

    function closeAllBbMetaAc() {
      const d1 = document.getElementById("bbAcCustDropdown");
      const d2 = document.getElementById("bbAcKhoDropdown");
      if (d1) d1.classList.remove("open");
      if (d2) d2.classList.remove("open");
    }

    // Gõ tìm kiếm trực tiếp Khách Hàng & Kho Nhận từ TMS (Hỗ trợ gõ có dấu / không dấu ở bất kỳ vị trí nào)
    function openBbMetaAc(type) {
      const isCust = type === "cust";
      const inp = document.getElementById(isCust ? "bbTplCustomerKeywords" : "bbTplKeywords");
      const drop = document.getElementById(isCust ? "bbAcCustDropdown" : "bbAcKhoDropdown");
      const otherDrop = document.getElementById(isCust ? "bbAcKhoDropdown" : "bbAcCustDropdown");
      if (otherDrop) otherDrop.classList.remove("open");
      if (!inp || !drop || inp.disabled) return;

      // Lấy từ khóa đang gõ sau dấu phẩy cuối cùng
      const parts = inp.value.split(",");
      const lastSeg = (parts[parts.length - 1] || "").trim();
      const qClean = toCleanUpperCode(lastSeg);
      const qLower = lastSeg.toLowerCase();

      // Danh sách Khách hàng đang chọn (để ưu tiên gợi ý Kho nhận thuộc Khách hàng đó)
      const selectedCustKws = (document.getElementById("bbTplCustomerKeywords")?.value || "")
        .split(",")
        .map(k => toCleanUpperCode(k.trim()))
        .filter(Boolean);

      const countMap = new Map();
      const prioritySet = new Set();

      allShipments.forEach(item => {
        if (item.xuatNhapPo !== "N") return;
        const val = (isCust ? item.tenKhachPo : item.khoNhanHangPo || "").trim();
        if (!val || val === ".") return;
        countMap.set(val, (countMap.get(val) || 0) + 1);

        if (!isCust && selectedCustKws.length > 0) {
          const itemCustClean = toCleanUpperCode(item.tenKhachPo || "");
          if (selectedCustKws.some(ck => itemCustClean.includes(ck))) {
            prioritySet.add(val);
          }
        }
      });

      let candidates = Array.from(countMap.keys());
      if (qClean || qLower) {
        candidates = candidates.filter(name => {
          return name.toLowerCase().includes(qLower) || toCleanUpperCode(name).includes(qClean);
        });
      }

      candidates.sort((a, b) => {
        const pA = prioritySet.has(a) ? 1 : 0;
        const pB = prioritySet.has(b) ? 1 : 0;
        if (pA !== pB) return pB - pA;
        return a.localeCompare(b, "vi");
      });

      if (candidates.length === 0) {
        drop.innerHTML = `<div style="padding:8px 10px;font-size:12px;color:#64748b;">Không tìm thấy ${isCust ? 'Khách hàng' : 'Kho nhận'} khớp "${escapeAttr(lastSeg)}" trên TMS (Bạn vẫn có thể tự gõ từ khóa)</div>`;
        drop.classList.add("open");
        return;
      }

      drop.innerHTML = candidates.slice(0, 60).map(name => {
        const cnt = countMap.get(name) || 0;
        const isPrio = prioritySet.has(name);
        return `
          <div class="bb-ac-item" onmousedown="event.preventDefault(); selectBbMetaAcItem('${type}', '${escapeAttr(name)}')">
            <span>${isPrio ? '⭐ ' : (isCust ? '👤 ' : '🏭 ')}${escapeAttr(name)}</span>
            <span style="font-size:11px;color:${isPrio ? '#b45309' : '#64748b'};background:${isPrio ? '#fef3c7' : '#f1f5f9'};padding:1px 6px;border-radius:4px;">
              ${isPrio ? 'Kho của KH đang chọn • ' : ''}${cnt} lô
            </span>
          </div>
        `;
      }).join("");
      drop.classList.add("open");
    }

    function selectBbMetaAcItem(type, chosenName) {
      const isCust = type === "cust";
      const inp = document.getElementById(isCust ? "bbTplCustomerKeywords" : "bbTplKeywords");
      const drop = document.getElementById(isCust ? "bbAcCustDropdown" : "bbAcKhoDropdown");
      if (!inp) return;

      const parts = inp.value.split(",").map(s => s.trim()).filter(Boolean);
      // Thay thế phần tử đang gõ dở cuối cùng bằng tên đã chọn (và thêm dấu phẩy chờ nếu muốn chọn tiếp)
      if (parts.length <= 1) {
        inp.value = chosenName;
      } else {
        parts[parts.length - 1] = chosenName;
        // Loại bỏ trùng lặp
        inp.value = Array.from(new Set(parts)).join(", ");
      }
      if (drop) drop.classList.remove("open");

      // Nếu đang tạo Form Kho Mới mà tên Form vẫn là mặc định, tự động điền gợi ý Tên Form & Nhãn nút
      if (!isCust) {
        const nameInp = document.getElementById("bbTplName");
        const badgeInp = document.getElementById("bbTplBadge");
        if (nameInp && (!nameInp.value || nameInp.value === "Form Kho Mới")) {
          nameInp.value = `Form ${chosenName}`;
        }
        if (badgeInp && (!badgeInp.value || badgeInp.value === "FormRiêng")) {
          badgeInp.value = chosenName.replace(/^(Cty|Công ty|Kho|CN)\s+/i, "").trim().slice(0, 12);
        }
      }
    }

    document.addEventListener("mousedown", function(e) {
      const cInp = document.getElementById("bbTplCustomerKeywords");
      const cDrop = document.getElementById("bbAcCustDropdown");
      const kInp = document.getElementById("bbTplKeywords");
      const kDrop = document.getElementById("bbAcKhoDropdown");
      if (cDrop && cDrop.classList.contains("open") && !cDrop.contains(e.target) && e.target !== cInp) {
        cDrop.classList.remove("open");
      }
      if (kDrop && kDrop.classList.contains("open") && !kDrop.contains(e.target) && e.target !== kInp) {
        kDrop.classList.remove("open");
      }
    });

    function renderBbTemplateSidebar() {
      const listEl = document.getElementById("bbTemplateSidebarList");
      if (!listEl) return;
      listEl.innerHTML = bbTemplatesList.map(tpl => {
        const isAct = tpl.id === currentEditingBbTplId;
        const isStd = tpl.id === "STANDARD";
        let kwText = "Mặc định cho tất cả kho còn lại";
        if (!isStd) {
          const parts = [];
          if (tpl.customer_keywords) parts.push(`👤 KH: ${tpl.customer_keywords}`);
          parts.push(`🏭 Kho: ${tpl.keywords || "(Chưa gán)"}`);
          kwText = parts.join(" • ");
        }
        return `
          <div class="bb-tpl-card ${isAct ? 'active' : ''}" onclick="selectBbTemplateInEditor('${escapeAttr(tpl.id)}')">
            <div class="bb-tpl-card-title">
              <span>📄 ${escapeAttr(tpl.name)}</span>
              <span style="font-size:10.5px;background:${isStd ? '#dbeafe' : '#fef3c7'};color:${isStd ? '#1d4ed8' : '#b45309'};padding:2px 6px;border-radius:5px;">${escapeAttr(tpl.short_badge || 'BB')}</span>
            </div>
            <div class="bb-tpl-card-kw">${escapeAttr(kwText)}</div>
          </div>
        `;
      }).join("");
    }

    function selectBbTemplateInEditor(id) {
      isBbLivePreviewMode = false;
      const btnPrev = document.getElementById("btnBbTogglePreview");
      if (btnPrev) {
        btnPrev.innerHTML = "👁️ Xem thử với dữ liệu thật";
        btnPrev.style.background = "#eff6ff";
        btnPrev.style.color = "#1d4ed8";
        btnPrev.style.borderColor = "#93c5fd";
      }

      const tpl = bbTemplatesList.find(t => t.id === id) || bbTemplatesList[0];
      if (!tpl) return;
      currentEditingBbTplId = tpl.id;
      renderBbTemplateSidebar();

      const nameInp = document.getElementById("bbTplName");
      const badgeInp = document.getElementById("bbTplBadge");
      const custInp = document.getElementById("bbTplCustomerKeywords");
      const kwInp = document.getElementById("bbTplKeywords");
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const btnDel = document.getElementById("btnBbDeleteTpl");
      const btnRest = document.getElementById("btnBbRestoreDefault");

      if (nameInp) nameInp.value = tpl.name || "";
      if (badgeInp) badgeInp.value = tpl.short_badge || "BB";
      if (custInp) {
        custInp.value = tpl.id === "STANDARD" ? "* (Mọi Khách Hàng)" : (tpl.customer_keywords || "");
        custInp.disabled = tpl.id === "STANDARD";
      }
      if (kwInp) {
        kwInp.value = tpl.id === "STANDARD" ? "* (Mọi Kho Nhận)" : (tpl.keywords || "");
        kwInp.disabled = tpl.id === "STANDARD";
      }
      if (canvas) {
        canvas.contentEditable = "true";
        canvas.innerHTML = tpl.html_content || "";
      }
      if (btnDel) {
        btnDel.style.display = tpl.id === "STANDARD" ? "none" : "inline-flex";
      }
      if (btnRest) {
        btnRest.style.display = (tpl.id === "STANDARD" || tpl.id === "4ORANGES") ? "inline-flex" : "none";
      }
    }

    function createNewBbTemplate() {
      const newId = "TPL_" + Date.now();
      const baseOrigin = window.location.origin;
      const newTpl = {
        id: newId,
        name: "Form Kho Mới",
        short_badge: "FormRiêng",
        customer_keywords: "",
        keywords: "",
        is_active: 1,
        sort_order: bbTemplatesList.length + 1,
        html_content: `<div class="bb-4oranges">
  <div class="thna-header">
    <div class="thna-logo-col">
      <img src="${baseOrigin}/logo-vietsunnew.png" alt="Logo" class="thna-logo-img" style="max-height:75px;width:auto;" />
    </div>
    <div class="thna-info-col">
      <div class="thna-title" style="color:#1e3a8a;">TÊN ĐƠN VỊ / CHỦ HÀNG</div>
      <div class="thna-line">Địa chỉ: Nhập địa chỉ công ty hoặc bấm nút "📄 Nhập từ Word (.docx)" ở trên</div>
      <div class="thna-line">Điện thoại: .................................</div>
    </div>
  </div>
  <div class="thna-divider">-----------------------------------------------------------------------------------------------------------------------------------------</div>
  <div class="thna-doc-title">BIÊN BẢN GIAO NHẬN HÀNG HÓA</div>
  <div class="thna-doc-date">Ngày {{NGAY}}&nbsp;&nbsp;tháng {{THANG}}&nbsp;&nbsp;&nbsp;năm {{NAM}}</div>
  <div class="thna-section">
    <div class="thna-bold-12">BÊN GIAO HÀNG: CÔNG TY CỔ PHẦN NHẬT VIỆT (VIETSUN)</div>
    <div class="thna-driver-line">
      <span style="display:inline-block;width:58%;">Tên Tài xế: ...................................................</span>
      <span>Biển số xe: {{SO_XE}}</span>
    </div>
  </div>
  <div class="thna-section" style="margin-top:12px;">
    <div class="thna-bold-13">BÊN NHẬN HÀNG: {{KHO_NHAN}}</div>
    <div class="thna-bold-13">Địa chỉ: {{DIA_CHI}}</div>
  </div>
  <table class="thna-table">
    <thead>
      <tr>
        <th style="width:8%;">STT</th>
        <th style="width:25%;">Số cont/Seal</th>
        <th style="width:18%;">Số lượng</th>
        <th style="width:24%;">Khối lượng (tấn)</th>
        <th style="width:25%;">Ghi chú / Mã hàng</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:center;">1</td>
        <td style="text-align:center;">{{SO_CONT}}</td>
        <td></td>
        <td style="text-align:center;">{{TRONG_LUONG}}</td>
        <td style="text-align:center;">{{HANG_HOA}}</td>
      </tr>
      <tr>
        <td style="text-align:center;">2</td>
        <td style="text-align:center;">{{SO_SEAL}}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <div class="thna-signatures">
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên nhận hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên giao hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
  </div>
</div>`
      };
      const stdIdx = bbTemplatesList.findIndex(t => t.id === "STANDARD");
      if (stdIdx >= 0) bbTemplatesList.splice(stdIdx, 0, newTpl);
      else bbTemplatesList.push(newTpl);

      selectBbTemplateInEditor(newId);
      const kwInp = document.getElementById("bbTplKeywords");
      if (kwInp) kwInp.focus();
      showToast("Đã tạo Form mới! Hãy gõ tìm Kho Nhận / Khách Hàng hoặc Upload file Word (.docx).", "info");
    }

    function cloneCurrentBbTemplate() {
      const cur = bbTemplatesList.find(t => t.id === currentEditingBbTplId);
      if (!cur) return;
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const currentHtml = isBbLivePreviewMode ? rawDraftBbHtmlBeforePreview : (canvas ? canvas.innerHTML : cur.html_content);
      const newId = "TPL_" + Date.now();
      const cloned = {
        id: newId,
        name: cur.name + " (Bản sao)",
        short_badge: (cur.short_badge || "Form").slice(0, 12) + "2",
        customer_keywords: cur.customer_keywords || "",
        keywords: "",
        is_active: 1,
        sort_order: bbTemplatesList.length + 1,
        html_content: currentHtml
      };
      const stdIdx = bbTemplatesList.findIndex(t => t.id === "STANDARD");
      if (stdIdx >= 0) bbTemplatesList.splice(stdIdx, 0, cloned);
      else bbTemplatesList.push(cloned);

      selectBbTemplateInEditor(newId);
      showToast("Đã nhân bản Form! Hãy gán Từ khóa Kho Nhận mới và bấm Lưu.", "success");
    }

    // =========================================================================
    // BỘ ĐỌC & NHẬN DIỆN FILE WORD (.DOCX) TRỰC TIẾP TRÊN TRÌNH DUYỆT
    // =========================================================================
    let isUploadingDocxAsNewTemplate = false;

    function triggerBbWordDocxUpload(asNewTemplate) {
      isUploadingDocxAsNewTemplate = !!asNewTemplate;
      const inp = document.getElementById("bbWordDocxInput");
      if (inp) {
        inp.value = "";
        inp.click();
      }
    }

    function uint8ToBase64(u8Arr) {
      let binary = "";
      const chunkSize = 8192;
      for (let i = 0; i < u8Arr.length; i += chunkSize) {
        binary += String.fromCharCode.apply(null, u8Arr.subarray(i, i + chunkSize));
      }
      return btoa(binary);
    }

    async function unzipDocxBuffer(arrayBuffer) {
      const view = new DataView(arrayBuffer);
      const bytes = new Uint8Array(arrayBuffer);
      const decoder = new TextDecoder("utf-8");

      let eocdOffset = -1;
      for (let i = bytes.length - 22; i >= Math.max(0, bytes.length - 65557); i--) {
        if (view.getUint32(i, true) === 0x06054b50) {
          eocdOffset = i;
          break;
        }
      }
      if (eocdOffset < 0) throw new Error("File không đúng định dạng Word (.docx)!");

      const cdCount = view.getUint16(eocdOffset + 10, true);
      let cdOffset = view.getUint32(eocdOffset + 16, true);
      const files = {};

      for (let i = 0; i < cdCount; i++) {
        if (view.getUint32(cdOffset, true) !== 0x02014b50) break;
        const method = view.getUint16(cdOffset + 10, true);
        const compSize = view.getUint32(cdOffset + 20, true);
        const nameLen = view.getUint16(cdOffset + 28, true);
        const extraLen = view.getUint16(cdOffset + 30, true);
        const commentLen = view.getUint16(cdOffset + 32, true);
        const localHeaderOffset = view.getUint32(cdOffset + 42, true);

        const fileName = decoder.decode(bytes.subarray(cdOffset + 46, cdOffset + 46 + nameLen));
        cdOffset += 46 + nameLen + extraLen + commentLen;

        if (view.getUint32(localHeaderOffset, true) !== 0x04034b50) continue;
        const localNameLen = view.getUint16(localHeaderOffset + 26, true);
        const localExtraLen = view.getUint16(localHeaderOffset + 28, true);
        const dataStart = localHeaderOffset + 30 + localNameLen + localExtraLen;
        const compBytes = bytes.subarray(dataStart, dataStart + compSize);

        if (method === 0) {
          files[fileName] = compBytes;
        } else if (method === 8) {
          const ds = new DecompressionStream("deflate-raw");
          const writer = ds.writable.getWriter();
          writer.write(compBytes);
          writer.close();
          const resp = new Response(ds.readable);
          const ab = await resp.arrayBuffer();
          files[fileName] = new Uint8Array(ab);
        }
      }
      return files;
    }

    function parseDocxXmlToHtml(rawDocXml, rawRelsXml, mediaBase64Map) {
      const relsMap = {};
      const relMatches = (rawRelsXml || "").match(/<Relationship [^>]+>/g) || [];
      relMatches.forEach(r => {
        const id = (r.match(/Id="([^"]+)"/) || [])[1];
        const target = (r.match(/Target="([^"]+)"/) || [])[1];
        if (id && target) relsMap[id] = target;
      });

      const cleanXml = rawDocXml.replace(/<mc:Fallback>[\s\S]*?<\/mc:Fallback>/g, "");
      const bodyMatch = cleanXml.match(/<w:body>([\s\S]*?)<\/w:body>/);
      const bodyXml = bodyMatch ? bodyMatch[1] : cleanXml;

      function escHtml(s) {
        return String(s || "")
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;");
      }

      function extractRunInfo(rXml, defaultSzHalfPt) {
        const tMatches = rXml.match(/<w:t[^>]*>([\s\S]*?)<\/w:t>/g) || [];
        const tabCount = (rXml.match(/<w:tab\/>/g) || []).length;
        const brCount = (rXml.match(/<w:br\/>/g) || []).length;
        const text = tMatches.map(t => t.replace(/<[^>]+>/g, "")).join("");
        if (!text && !tabCount && !brCount) return null;

        let htmlText = escHtml(text);
        if (tabCount > 0) htmlText += "\t".repeat(tabCount);
        if (brCount > 0) htmlText += "\n".repeat(brCount);

        const rPr = (rXml.match(/<w:rPr>([\s\S]*?)<\/w:rPr>/) || [])[1] || "";
        const isBold = /<w:b\/>|<w:b w:val="(?!false|0)/.test(rPr);
        const isItalic = /<w:i\/>|<w:i w:val="(?!false|0)/.test(rPr);
        const isUnderline = /<w:u w:val="(?!none)/.test(rPr);
        const vertAlign = (rPr.match(/<w:vertAlign w:val="([^"]+)"/) || [])[1] || "";
        const color = (rPr.match(/<w:color w:val="([^"]+)"/) || [])[1] || "";
        const szHalf = (rPr.match(/<w:sz w:val="(\d+)"/) || [])[1] || defaultSzHalfPt || "";
        const fontAscii = (rPr.match(/<w:rFonts[^>]*w:ascii="([^"]+)"/) || [])[1] || "";

        const styles = [];
        if (isBold) styles.push("font-weight:700");
        if (isItalic) styles.push("font-style:italic");
        if (isUnderline) styles.push("text-decoration:underline");
        if (color && color !== "auto" && color !== "000000") styles.push(`color:#${color}`);
        if (szHalf) styles.push(`font-size:${(Number(szHalf) / 2).toFixed(1).replace(/\.0$/, "")}pt`);
        if (fontAscii && /arial/i.test(fontAscii)) styles.push("font-family:Arial,Helvetica,sans-serif");

        return { text: htmlText, vertAlign, styleStr: styles.join(";") };
      }

      function parseSimpleParagraph(pXml) {
        const pWithoutDrawings = pXml.replace(/<w:drawing>[\s\S]*?<\/w:drawing>/g, "");
        const pPr = (pWithoutDrawings.match(/<w:pPr>([\s\S]*?)<\/w:pPr>/) || [])[1] || "";
        const jc = (pPr.match(/<w:jc w:val="([^"]+)"/) || [])[1] || "left";
        const defSz = (pPr.match(/<w:sz w:val="(\d+)"/) || [])[1] || "";
        const indLeft = (pPr.match(/<w:ind[^>]*w:left="(\d+)"/) || [])[1] || "";
        const indFirst = (pPr.match(/<w:ind[^>]*w:firstLine="(\d+)"/) || [])[1] || "";

        const runs = pWithoutDrawings.match(/<w:r[ >][\s\S]*?<\/w:r>/g) || [];
        const merged = [];
        runs.forEach(r => {
          const info = extractRunInfo(r, defSz);
          if (!info) return;
          const prev = merged[merged.length - 1];
          if (prev && prev.styleStr === info.styleStr && prev.vertAlign === info.vertAlign) {
            prev.text += info.text;
          } else {
            merged.push(info);
          }
        });

        const inner = merged.map(m => {
          let t = m.text
            .replace(/  /g, " &nbsp;")
            .replace(/\t/g, "&emsp;&emsp;&emsp;")
            .replace(/\n/g, "<br/>");
          if (m.vertAlign === "superscript") t = `<sup>${t}</sup>`;
          else if (m.vertAlign === "subscript") t = `<sub>${t}</sub>`;
          return m.styleStr ? `<span style="${m.styleStr}">${t}</span>` : t;
        }).join("");

        if (!inner.replace(/&nbsp;|&emsp;|\s|<[^>]+>/g, "")) return "";

        const pStyles = ["margin:3px 0", "line-height:1.55"];
        if (jc === "center") pStyles.push("text-align:center");
        else if (jc === "right") pStyles.push("text-align:right;padding-right:24px");
        else if (jc === "both") pStyles.push("text-align:justify");

        const totalIndentTwips = Number(indLeft || 0) + Number(indFirst || 0);
        if (totalIndentTwips > 200 && jc === "left") {
          const px = Math.min(120, Math.round(totalIndentTwips / 18));
          pStyles.push(`padding-left:${px}px`);
        }

        return `<div style="${pStyles.join(";")}">${inner}</div>`;
      }

      function parseDrawings(xmlChunk) {
        const drawings = xmlChunk.match(/<w:drawing>[\s\S]*?<\/w:drawing>/g) || [];
        if (drawings.length === 0) return "";

        let htmlOut = "";
        drawings.forEach(drw => {
          const blips = [...drw.matchAll(/<a:blip[^>]*r:embed="([^"]+)"/g)].map(m => m[1]);
          const txbxMatches = [...drw.matchAll(/<w:txbxContent>([\s\S]*?)<\/w:txbxContent>/g)].map(m => m[1]);

          const imgsHtml = blips.map(rId => {
            const target = relsMap[rId] || "";
            const b64 = mediaBase64Map[target] || mediaBase64Map["word/" + target] || "";
            return b64 ? `<img src="${b64}" class="thna-logo-img" style="max-width:165px;max-height:95px;width:auto;height:auto;display:block;" />` : "";
          }).filter(Boolean).join("");

          const txbxHtml = txbxMatches.map(tb => {
            const paras = tb.match(/<w:p[ >][\s\S]*?<\/w:p>/g) || [];
            return paras.map(p => parseSimpleParagraph(p)).filter(Boolean).join("");
          }).filter(Boolean).join("");

          if (imgsHtml && txbxHtml) {
            htmlOut += `<div class="thna-header" style="display:flex;align-items:flex-start;gap:16px;padding-left:8px;margin-bottom:6px;">
              <div class="thna-logo-col" style="flex-shrink:0;">${imgsHtml}</div>
              <div class="thna-info-col" style="flex:1;line-height:1.38;">${txbxHtml}</div>
            </div>`;
          } else if (imgsHtml) {
            htmlOut += `<div style="margin:6px 0;">${imgsHtml}</div>`;
          } else if (txbxHtml) {
            htmlOut += `<div style="margin:6px 0;">${txbxHtml}</div>`;
          }
        });
        return htmlOut;
      }

      function parseTable(tblXml) {
        const gridCols = [...tblXml.matchAll(/<w:gridCol w:w="(\d+)"\/>/g)].map(m => Number(m[1]));
        if (gridCols.length <= 1 && /<w:drawing>/.test(tblXml)) {
          return parseDrawings(tblXml);
        }

        const totalW = gridCols.reduce((a, b) => a + b, 0) || 1;
        const colPercents = gridCols.map(w => ((w / totalW) * 100).toFixed(2) + "%");

        const trs = tblXml.match(/<w:tr[ >][\s\S]*?<\/w:tr>/g) || [];
        const rowsHtml = trs.map((tr, rIdx) => {
          const tcs = tr.match(/<w:tc[ >][\s\S]*?<\/w:tc>/g) || [];
          const cellsHtml = tcs.map((tc, cIdx) => {
            const paras = tc.match(/<w:p[ >][\s\S]*?<\/w:p>/g) || [];
            const cellInner = paras.map(p => parseSimpleParagraph(p)).filter(Boolean).join("") || "";
            const wStyle = colPercents[cIdx] ? `width:${colPercents[cIdx]};` : "";
            const tag = rIdx === 0 ? "th" : "td";
            return `<${tag} style="${wStyle}border:1px solid #000;padding:6px 8px;vertical-align:middle;">${cellInner}</${tag}>`;
          }).join("");
          return `<tr>${cellsHtml}</tr>`;
        }).join("");

        return `<table class="thna-table" style="width:100%;border-collapse:collapse;margin:14px 0;">${rowsHtml}</table>`;
      }

      const topTokens = [];
      let pos = 0;
      while (pos < bodyXml.length) {
        const nextTbl = bodyXml.indexOf("<w:tbl>", pos);
        const nextP = bodyXml.slice(pos).search(/<w:p[ >]/);
        const absNextP = nextP >= 0 ? pos + nextP : -1;

        if (nextTbl >= 0 && (absNextP < 0 || nextTbl < absNextP)) {
          const endTbl = bodyXml.indexOf("</w:tbl>", nextTbl);
          if (endTbl < 0) break;
          topTokens.push({ type: "tbl", xml: bodyXml.slice(nextTbl, endTbl + 8) });
          pos = endTbl + 8;
        } else if (absNextP >= 0) {
          const endP = bodyXml.indexOf("</w:p>", absNextP);
          if (endP < 0) break;
          topTokens.push({ type: "p", xml: bodyXml.slice(absNextP, endP + 6) });
          pos = endP + 6;
        } else {
          break;
        }
      }

      const parts = [];
      topTokens.forEach(tok => {
        if (tok.type === "tbl") {
          const h = parseTable(tok.xml);
          if (h) parts.push(h);
        } else if (tok.type === "p") {
          if (/<w:drawing>/.test(tok.xml)) {
            const dh = parseDrawings(tok.xml);
            if (dh) parts.push(dh);
          }
          const ph = parseSimpleParagraph(tok.xml);
          if (ph) parts.push(ph);
        }
      });

      let finalHtml = `<div class="bb-4oranges">${parts.join("\n")}</div>`;

      // Tự động nhận diện và chuyển Ngày giao, Số Cont, Số Seal thành biến động {{...}}
      finalHtml = finalHtml.replace(
        /(Ngày(?:\s|&nbsp;)*)(\d{1,2}|\.{2,})((?:\s|&nbsp;)*tháng(?:\s|&nbsp;)*)(\d{1,2}|\.{2,})((?:\s|&nbsp;)*năm(?:\s|&nbsp;)*)(\d{4}|\.{2,})/gi,
        "$1{{NGAY}}$3{{THANG}}$5{{NAM}}"
      );

      let foundCont = false;
      finalHtml = finalHtml.replace(/\b([A-Z]{3}U\s*\d{6,7})\b/g, () => {
        foundCont = true;
        return "{{SO_CONT}}";
      });

      if (foundCont) {
        finalHtml = finalHtml.replace(
          /(<td[^>]*>\s*<div[^>]*>\s*(?:<span[^>]*>)?2(?:<\/span>)?\s*<\/div>\s*<\/td>\s*<td[^>]*>\s*<div[^>]*>\s*(?:<span[^>]*>)?(?:\s|&nbsp;)*)([A-Za-z0-9]{5,10})((?:<\/span>)?\s*<\/div>\s*<\/td>)/i,
          "$1{{SO_SEAL}}$3"
        );
      }

      return finalHtml;
    }

    async function handleBbWordDocxSelected(inputEl) {
      const file = inputEl.files && inputEl.files[0];
      if (!file) return;

      try {
        showToast("⏳ Đang đọc và nhận diện bố cục từ file Word (.docx)...", "info");
        const ab = await file.arrayBuffer();
        const filesMap = await unzipDocxBuffer(ab);
        const decoder = new TextDecoder("utf-8");

        const docXmlBytes = filesMap["word/document.xml"];
        if (!docXmlBytes) throw new Error("Không tìm thấy nội dung word/document.xml trong file!");
        const docXml = decoder.decode(docXmlBytes);
        const relsXml = filesMap["word/_rels/document.xml.rels"]
          ? decoder.decode(filesMap["word/_rels/document.xml.rels"])
          : "";

        const mediaBase64Map = {};
        for (const [fName, fBytes] of Object.entries(filesMap)) {
          if (fName.startsWith("word/media/")) {
            const ext = (fName.split(".").pop() || "png").toLowerCase();
            const mime = ext === "jpg" || ext === "jpeg" ? "image/jpeg" : "image/png";
            const dataUri = `data:${mime};base64,${uint8ToBase64(fBytes)}`;
            mediaBase64Map[fName] = dataUri;
            mediaBase64Map[fName.replace(/^word\//, "")] = dataUri;
          }
        }

        const parsedHtml = parseDocxXmlToHtml(docXml, relsXml, mediaBase64Map);
        const cleanFileTitle = file.name.replace(/\.docx$/i, "").trim();

        if (isUploadingDocxAsNewTemplate || currentEditingBbTplId === "STANDARD") {
          const newId = "TPL_" + Date.now();
          const newTpl = {
            id: newId,
            name: `Form ${cleanFileTitle}`,
            short_badge: cleanFileTitle.slice(0, 12),
            customer_keywords: "",
            keywords: "",
            is_active: 1,
            sort_order: bbTemplatesList.length + 1,
            html_content: parsedHtml
          };
          const stdIdx = bbTemplatesList.findIndex(t => t.id === "STANDARD");
          if (stdIdx >= 0) bbTemplatesList.splice(stdIdx, 0, newTpl);
          else bbTemplatesList.push(newTpl);
          selectBbTemplateInEditor(newId);
        } else {
          isBbLivePreviewMode = false;
          const canvas = document.getElementById("bbVisualEditorCanvas");
          if (canvas) {
            canvas.contentEditable = "true";
            canvas.innerHTML = parsedHtml;
          }
        }

        showToast("✅ Đã nhận diện Form từ file Word (.docx) và tự động gắn biến {{SO_CONT}}, {{SO_SEAL}}, {{NGAY}}! Hãy chọn Kho Nhận và bấm Lưu.", "success");
      } catch (err) {
        console.error("DOCX Parse Error:", err);
        showToast("⚠️ Không thể đọc file Word: " + (err?.message || "Vui lòng chọn đúng file .docx"), "error");
      }
    }

    function execBbEditorCmd(cmd, val = null) {
      if (isBbLivePreviewMode) {
        showToast("Đang ở chế độ Xem thử. Hãy bấm 'Quay lại Chế độ Soạn Mẫu' để chỉnh sửa!", "warning");
        return;
      }
      const canvas = document.getElementById("bbVisualEditorCanvas");
      if (canvas) canvas.focus();
      document.execCommand(cmd, false, val);
    }

    function insertBbVariable(varToken) {
      if (isBbLivePreviewMode) {
        showToast("Hãy tắt chế độ Xem thử trước khi chèn biến!", "warning");
        return;
      }
      const canvas = document.getElementById("bbVisualEditorCanvas");
      if (!canvas) return;
      canvas.focus();
      const sel = window.getSelection();
      if (sel && sel.rangeCount > 0 && canvas.contains(sel.anchorNode)) {
        document.execCommand("insertText", false, varToken);
      } else {
        canvas.innerHTML += ` <span>${varToken}</span>`;
      }
    }

    function insertBbSampleTable() {
      if (isBbLivePreviewMode) return;
      const tblHtml = `<table class="thna-table" style="width:100%;border-collapse:collapse;margin:12px 0;">
        <thead>
          <tr>
            <th style="border:1px solid #000;padding:6px;">STT</th>
            <th style="border:1px solid #000;padding:6px;">Số cont/Seal</th>
            <th style="border:1px solid #000;padding:6px;">Số lượng</th>
            <th style="border:1px solid #000;padding:6px;">Khối lượng (tấn)</th>
            <th style="border:1px solid #000;padding:6px;">Mã Hàng / Ghi chú</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="border:1px solid #000;padding:6px;text-align:center;">1</td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{SO_CONT}}</td>
            <td style="border:1px solid #000;padding:6px;"></td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{TRONG_LUONG}}</td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{HANG_HOA}}</td>
          </tr>
          <tr>
            <td style="border:1px solid #000;padding:6px;text-align:center;">2</td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{SO_SEAL}}</td>
            <td style="border:1px solid #000;padding:6px;"></td>
            <td style="border:1px solid #000;padding:6px;"></td>
            <td style="border:1px solid #000;padding:6px;"></td>
          </tr>
        </tbody>
      </table>`;
      const canvas = document.getElementById("bbVisualEditorCanvas");
      if (!canvas) return;
      canvas.focus();
      document.execCommand("insertHTML", false, tblHtml);
    }

    function triggerBbLogoUpload() {
      if (isBbLivePreviewMode) {
        showToast("Hãy tắt chế độ Xem thử trước khi đổi Logo!", "warning");
        return;
      }
      const inp = document.getElementById("bbLogoFileInput");
      if (inp) { inp.value = ""; inp.click(); }
    }

    function handleBbLogoFileSelected(inputEl) {
      const file = inputEl.files && inputEl.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function(e) {
        const dataUrl = e.target.result;
        const canvas = document.getElementById("bbVisualEditorCanvas");
        if (!canvas) return;
        const existingLogo = canvas.querySelector(".thna-logo-img") || canvas.querySelector(".vs-header-table img") || canvas.querySelector("img");
        if (existingLogo) {
          existingLogo.src = dataUrl;
          showToast("Đã cập nhật ảnh Logo trên mẫu Biên Bản!", "success");
        } else {
          canvas.focus();
          document.execCommand("insertHTML", false, `<img src="${dataUrl}" style="max-width:165px;height:auto;" />`);
          showToast("Đã chèn ảnh Logo vào mẫu Biên Bản!", "success");
        }
      };
      reader.readAsDataURL(file);
    }

    function getSamplePoForBbPreview() {
      const custInp = document.getElementById("bbTplCustomerKeywords");
      const kwInp = document.getElementById("bbTplKeywords");
      const custList = ((custInp && custInp.value) || "").split(",").map(k => k.trim()).filter(k => k && !k.startsWith("*"));
      const kwList = ((kwInp && kwInp.value) || "").split(",").map(k => k.trim()).filter(k => k && !k.startsWith("*"));
      let matchedPo = null;
      if (custList.length > 0 || kwList.length > 0) {
        matchedPo = allShipments.find(i => {
          if (i.xuatNhapPo !== "N") return false;
          const cKho = toCleanUpperCode(i.khoNhanHangPo || "");
          const cKhach = toCleanUpperCode(i.tenKhachPo || "");
          const okCust = custList.length === 0 || custList.some(k => cKhach.includes(toCleanUpperCode(k)));
          const okKho = kwList.length === 0 || kwList.some(k => cKho.includes(toCleanUpperCode(k)));
          return okCust && okKho;
        });
      }
      if (!matchedPo) {
        matchedPo = filteredShipments.find(i => i.xuatNhapPo === "N") || allShipments.find(i => i.xuatNhapPo === "N");
      }
      return matchedPo || {
        idPo: "999999",
        soContPo: "VSGU2250272",
        soSealPo: "693903",
        ngayTraHang: getTodayISO(),
        khoNhanHangPo: kwList[0] || "Cty 4 Oranges",
        tenKhachPo: custList[0] || "Cty KS Á Châu",
        diaChiNhanPo: "Lô C-02-1, KCN Đức Hòa 1, Ấp 5, Xã Mỹ Hạnh",
        tinhNhanPo: "TAY NINH",
        soXePo: "50H10208",
        donViVcPo: "VIETSUN",
        trongLuongPo: "27.00",
        tenHangHoaPo: "Bột đá",
        tauPo: "VIETSUN RELIANCE",
        chuyenPo: "2639S"
      };
    }

    function toggleBbEditorLivePreview() {
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const btnPrev = document.getElementById("btnBbTogglePreview");
      if (!canvas) return;

      if (!isBbLivePreviewMode) {
        rawDraftBbHtmlBeforePreview = canvas.innerHTML;
        const samplePo = getSamplePoForBbPreview();
        canvas.innerHTML = renderTemplateWithPoData(rawDraftBbHtmlBeforePreview, samplePo);
        canvas.contentEditable = "false";
        isBbLivePreviewMode = true;
        if (btnPrev) {
          btnPrev.innerHTML = "✏️ Quay lại Chế độ Soạn Mẫu (Hiện {{...}})";
          btnPrev.style.background = "#fef3c7";
          btnPrev.style.color = "#b45309";
          btnPrev.style.borderColor = "#fcd34d";
        }
        showToast(`Đang xem thử với dữ liệu lô #${samplePo.idPo} (${samplePo.tenKhachPo} ➔ ${samplePo.khoNhanHangPo})`, "info");
      } else {
        canvas.innerHTML = rawDraftBbHtmlBeforePreview;
        canvas.contentEditable = "true";
        isBbLivePreviewMode = false;
        if (btnPrev) {
          btnPrev.innerHTML = "👁️ Xem thử với dữ liệu thật";
          btnPrev.style.background = "#eff6ff";
          btnPrev.style.color = "#1d4ed8";
          btnPrev.style.borderColor = "#93c5fd";
        }
      }
    }

    async function saveCurrentBbTemplate() {
      const tpl = bbTemplatesList.find(t => t.id === currentEditingBbTplId);
      if (!tpl) return;

      const nameVal = (document.getElementById("bbTplName")?.value || "").trim();
      const badgeVal = (document.getElementById("bbTplBadge")?.value || "BB").trim();
      const custVal = tpl.id === "STANDARD" ? "" : (document.getElementById("bbTplCustomerKeywords")?.value || "").trim();
      const kwVal = tpl.id === "STANDARD" ? "*" : (document.getElementById("bbTplKeywords")?.value || "").trim();
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const htmlVal = isBbLivePreviewMode ? rawDraftBbHtmlBeforePreview : (canvas ? canvas.innerHTML : tpl.html_content);

      if (!nameVal) {
        showToast("⚠️ Vui lòng nhập Tên Mẫu Biên Bản!", "warning");
        return;
      }
      if (tpl.id !== "STANDARD" && !kwVal && !custVal) {
        showToast("⚠️ Vui lòng chọn ít nhất 1 từ khóa KHO NHẬN hoặc KHÁCH HÀNG để hệ thống tự động nhận diện!", "warning");
        return;
      }

      tpl.name = nameVal;
      tpl.short_badge = badgeVal || "FormRiêng";
      tpl.customer_keywords = custVal;
      tpl.keywords = kwVal;
      tpl.html_content = htmlVal;
      tpl.is_active = 1;

      try {
        localStorage.setItem("vs_bb_templates_v1", JSON.stringify(bbTemplatesList));
      } catch (_) {}

      renderBbTemplateSidebar();
      updateBbTplCountBadge();
      renderShipmentList();

      try {
        const resp = await fetch("/api/index.php?route=bb_templates/save", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(tpl)
        });
        const res = await resp.json();
        if (res && res.ok) {
          showToast(`✅ Đã lưu "${tpl.name}" và đồng bộ lên Server!`, "success");
        } else {
          showToast(`Đã lưu "${tpl.name}" trên máy!`, "success");
        }
      } catch (_) {
        showToast(`Đã lưu "${tpl.name}" trên trình duyệt!`, "success");
      }
    }

    function restoreDefaultBbTemplate() {
      const builtIn = getBuiltInBbTemplates().find(t => t.id === currentEditingBbTplId);
      if (!builtIn) return;
      if (!confirm(`Bạn có chắc muốn khôi phục "${builtIn.name}" về thiết kế gốc ban đầu không?`)) return;

      const idx = bbTemplatesList.findIndex(t => t.id === currentEditingBbTplId);
      if (idx >= 0) {
        bbTemplatesList[idx] = { ...builtIn };
      }
      selectBbTemplateInEditor(currentEditingBbTplId);
      saveCurrentBbTemplate();
    }

    async function deleteCurrentBbTemplate() {
      if (currentEditingBbTplId === "STANDARD") {
        showToast("⚠️ Không thể xóa Form Chuẩn mặc định!", "warning");
        return;
      }
      const tpl = bbTemplatesList.find(t => t.id === currentEditingBbTplId);
      if (!tpl) return;
      if (!confirm(`Bạn có chắc muốn xóa "${tpl.name}" không?`)) return;

      const delId = tpl.id;
      bbTemplatesList = bbTemplatesList.filter(t => t.id !== delId);
      try {
        localStorage.setItem("vs_bb_templates_v1", JSON.stringify(bbTemplatesList));
      } catch (_) {}

      try {
        await fetch("/api/index.php?route=bb_templates/delete", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: delId })
        });
      } catch (_) {}

      updateBbTplCountBadge();
      selectBbTemplateInEditor(bbTemplatesList[0]?.id || "STANDARD");
      renderShipmentList();
      showToast(`Đã xóa mẫu "${tpl.name}"!`, "info");
    }

    function testPrintCurrentBbTemplate() {
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const rawHtml = isBbLivePreviewMode ? rawDraftBbHtmlBeforePreview : (canvas ? canvas.innerHTML : "");
      const samplePo = getSamplePoForBbPreview();
      const filledHtml = renderTemplateWithPoData(rawHtml, samplePo);
      const w = window.open("", "_blank");
      if (!w) {
        showToast("⚠️ Trình duyệt chặn pop-up!", "error");
        return;
      }
      w.document.open();
      w.document.write(buildFullBienBanPrintDocument([`<div class="bb-page">${filledHtml}</div>`], "In thử Mẫu Biên Bản", "1 mẫu thử"));
      w.document.close();
    }

    function toggleBulkDropdown(e) {
      if (e) e.stopPropagation();
      const pop = document.getElementById("bulkDropdownPop");
      if (pop) pop.classList.toggle("show");
    }

    function closeBulkDropdown() {
      const pop = document.getElementById("bulkDropdownPop");
      if (pop) pop.classList.remove("show");
    }

    function selectBulkCarrier(carrier) {
      closeBulkDropdown();
      state.bulkMode = true;
      state.bulkCarrier = carrier;

      // Nếu không ở Tab Xuất (X) hoặc Tab Nhập (N) thì mặc định chuyển về Tab Xuất (X)
      if (state.xuatNhap !== "X" && state.xuatNhap !== "N") {
        state.xuatNhap = "X";
        document.querySelectorAll(".bottom-nav .nav-item").forEach(btn => {
          btn.classList.toggle("active", btn.dataset.xn === "X");
        });
      }

      state.bulkSelectedIds = new Set();
      applyFilters();

      // Mặc định tick chọn tất cả các dòng tìm thấy theo ĐVVT được lọc
      filteredShipments.forEach(item => {
        state.bulkSelectedIds.add(String(item.idPo));
      });

      renderShipmentList();
      updateBulkControlsUI();
      updateBulkHeaderCheckbox();
      updateBulkSelectedCount();

      const docLabel = state.xuatNhap === "N" ? "Biên Bản (BB)" : "PKL";
      showToast(`Đã bật chế độ in ${docLabel} hàng loạt cho ${carrier} (${filteredShipments.length} lô)!`, "info");
    }

    function exitBulkPrintMode() {
      state.bulkMode = false;
      state.bulkCarrier = null;
      if (state.bulkSelectedIds) state.bulkSelectedIds.clear();
      closeBulkDropdown();
      updateBulkControlsUI();
      applyFilters();
      showToast("Đã thoát chế độ in hàng loạt.", "info");
    }

    function onToggleBulkItem(idPo, isChecked) {
      if (!state.bulkSelectedIds) state.bulkSelectedIds = new Set();
      if (isChecked) {
        state.bulkSelectedIds.add(String(idPo));
      } else {
        state.bulkSelectedIds.delete(String(idPo));
      }
      updateBulkHeaderCheckbox();
      updateBulkSelectedCount();
    }

    function toggleSelectAllBulk(isChecked) {
      if (!state.bulkSelectedIds) state.bulkSelectedIds = new Set();
      const checkboxes = document.querySelectorAll(".chk-bulk-item");
      checkboxes.forEach(cb => {
        cb.checked = isChecked;
        const id = cb.value;
        if (isChecked) {
          state.bulkSelectedIds.add(String(id));
        } else {
          state.bulkSelectedIds.delete(String(id));
        }
      });
      updateBulkSelectedCount();
    }

    function updateBulkHeaderCheckbox() {
      const headerCb = document.getElementById("chkBulkPrintAll");
      if (!headerCb) return;
      const checkboxes = document.querySelectorAll(".chk-bulk-item");
      if (checkboxes.length === 0) {
        headerCb.checked = false;
        return;
      }
      headerCb.checked = Array.from(checkboxes).every(cb => cb.checked);
    }

    function getVisibleSelectedCount() {
      if (!state.bulkSelectedIds || state.bulkSelectedIds.size === 0) return 0;
      let cnt = 0;
      filteredShipments.forEach(item => {
        if (state.bulkSelectedIds.has(String(item.idPo))) cnt++;
      });
      return cnt;
    }

    function clearAllBulkSelection() {
      if (state.bulkSelectedIds) state.bulkSelectedIds.clear();
      const checkboxes = document.querySelectorAll(".chk-bulk-item");
      checkboxes.forEach(cb => { cb.checked = false; });
      const headerCb = document.getElementById("chkBulkPrintAll");
      if (headerCb) headerCb.checked = false;
      updateBulkSelectedCount();
    }

    function updateBulkSelectedCount() {
      const cnt = getVisibleSelectedCount();
      const countEl = document.getElementById("bulkSelectedCount");
      if (countEl) countEl.textContent = cnt;
      const nhapCountEl = document.getElementById("bulkNhapSelectedCount");
      if (nhapCountEl) nhapCountEl.textContent = cnt;
      const nhapClearBtn = document.getElementById("btnBulkNhapClear");
      if (nhapClearBtn) nhapClearBtn.style.display = cnt > 0 ? "inline-block" : "none";
    }

    function updateBulkControlsUI() {
      const ctrl = document.getElementById("desktopBulkCtrl");
      if (!ctrl) return;
      const isTable = state.viewMode === "table";
      const isXuat = state.xuatNhap === "X";
      const isNhap = state.xuatNhap === "N";

      // Hiện khi ở chế độ Bảng PC và Tab Hàng Xuất (In PKL) hoặc Tab Hàng Nhập (In BB)
      if (!isTable || (!isXuat && !isNhap && !state.bulkMode)) {
        ctrl.style.display = "none";
        closeBulkDropdown();
        return;
      }

      ctrl.style.display = "flex";
      const wrap = document.getElementById("bulkDropdownWrap");
      const activeBar = document.getElementById("bulkActiveBar");
      const nhapDirectBar = document.getElementById("bulkNhapDirectBar");
      const cnt = getVisibleSelectedCount();

      // Đối với Tab Hàng Nhập (N): Hiển thị luôn nút Thực Thi In Biên Bản (không sổ dropdown ĐVVT)
      if (isNhap) {
        if (wrap) wrap.style.display = "none";
        if (activeBar) activeBar.style.display = "none";
        if (nhapDirectBar) nhapDirectBar.style.display = "inline-flex";
        const nhapCountEl = document.getElementById("bulkNhapSelectedCount");
        if (nhapCountEl) nhapCountEl.textContent = cnt;
        const nhapClearBtn = document.getElementById("btnBulkNhapClear");
        if (nhapClearBtn) nhapClearBtn.style.display = cnt > 0 ? "inline-block" : "none";
        updateBbTplCountBadge();
        return;
      }

      // Đối với Tab Hàng Xuất (X): Sổ dropdown chọn VIETSUN / VENDOR rồi mới hiện Print All
      if (nhapDirectBar) nhapDirectBar.style.display = "none";
      const lblAction = document.getElementById("lblBulkAction");
      const mainBtn = document.getElementById("btnBulkPrintMain");
      if (lblAction) lblAction.textContent = "In PKL hàng loạt";
      if (mainBtn) {
        mainBtn.title = "In PKL hàng loạt theo ĐVVT";
        mainBtn.style.background = "linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)";
        mainBtn.style.borderColor = "#1d4ed8";
      }

      if (state.bulkMode) {
        if (wrap) wrap.style.display = "none";
        if (activeBar) activeBar.style.display = "inline-flex";
        const iconEl = document.getElementById("bulkCarrierIcon");
        const textEl = document.getElementById("bulkCarrierText");
        const countEl = document.getElementById("bulkSelectedCount");
        if (iconEl) iconEl.textContent = state.bulkCarrier === "VIETSUN" ? "🚛" : "🚚";
        if (textEl) textEl.textContent = state.bulkCarrier;
        if (countEl) countEl.textContent = cnt;
      } else {
        if (wrap) wrap.style.display = "inline-block";
        if (activeBar) activeBar.style.display = "none";
      }
    }

    function printSingleBienBan(idPo) {
      const po = allShipments.find(i => String(i.idPo) === String(idPo));
      if (!po) {
        showToast("⚠️ Không tìm thấy thông tin lô hàng!", "warning");
        return;
      }
      executeBulkBienBanPrint([po]);
    }

    function buildBienBanPageHtml(po, isNotFirstPage) {
      const tpl = detectBbTemplate(po);
      const rawHtml = tpl.html_content || getDefaultStandardHtml();
      const filledInner = renderTemplateWithPoData(rawHtml, po);
      return `<div class="bb-page ${isNotFirstPage ? 'page-break' : ''}" contenteditable="true">${filledInner}</div>`;
    }

    function buildFullBienBanPrintDocument(pagesHtmlArr, title, summaryBreakdown) {
      const count = pagesHtmlArr.length;
      return `<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>${escapeAttr(title)}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 10mm 10mm 10mm 10mm;
    }
    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    html, body {
      margin: 0;
      padding: 0;
      background: #f1f5f9;
      color: #000000;
      font-family: "Times New Roman", Times, serif;
    }
    .no-print-bar {
      position: sticky;
      top: 0;
      z-index: 9999;
      background: #ffffff;
      border-bottom: 1px solid #cbd5e1;
      padding: 10px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      font-family: Arial, Helvetica, sans-serif;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    .no-print-info { font-size: 13px; font-weight: 700; color: #0f172a; }
    .no-print-sub { font-size: 12px; color: #0369a1; font-weight: 600; margin-top: 2px; }
    .no-print-actions { display: flex; align-items: center; gap: 8px; }
    .btn-top-print { background: #f59e0b; color: #000; border: 1px solid #d97706; border-radius: 6px; padding: 7px 16px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-top-print:hover { background: #eab308; }
    .btn-top-close { background: #fff; color: #dc2626; border: 1px solid #dc2626; border-radius: 6px; padding: 7px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-top-close:hover { background: #fee2e2; }
    .bb-page { width: 190mm; margin: 12px auto; background: #fff; padding: 6mm 6mm; box-shadow: 0 2px 10px rgba(0,0,0,0.1); outline: none; }
    .page-break { page-break-before: always; break-before: page; }
    @media print {
      html, body { background: #ffffff; }
      .no-print-bar { display: none !important; }
      .bb-page { margin: 0 auto; padding: 0; box-shadow: none; width: 190mm; }
    }
    .bb-vietsun { font-family: "Times New Roman", Times, serif; font-size: 13pt; color: #000; }
    .vs-div-picture { width: 190mm; min-height: 225mm; position: relative; }
    .vs-watermark-img { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 82%; max-height: 175mm; object-fit: contain; opacity: 0.22; pointer-events: none; z-index: 0; }
    .vs-seal-img { position: absolute; top: 18mm; left: 10mm; width: 39mm; height: 39mm; object-fit: contain; pointer-events: none; z-index: 1; }
    .vs-content-layer { position: relative; z-index: 2; }
    .vs-header-table { width: 100%; border-collapse: collapse; }
    .vs-info-table { width: 100%; border-collapse: collapse; line-height: 1.8; vertical-align: top; }
    .vs-info-table th, .vs-info-table td { padding: 0 2px; vertical-align: top; }
    .vs-bangND { width: 100%; border-collapse: collapse; line-height: 1.8; margin: 4px 0; }
    .vs-bangND th, .vs-bangND td { border: 1px solid #000; padding: 2px 10px; text-align: center; }
    .vs-sig-row { display: flex; justify-content: space-between; text-align: center; font-weight: 700; margin-top: 8px; }
    .vs-sig-col { width: 33.33%; }
    .bb-4oranges { font-family: "Times New Roman", Times, serif; color: #000; padding-top: 4mm; }
    .thna-header { display: flex; align-items: flex-start; gap: 16px; padding-left: 8px; }
    .thna-logo-col { flex-shrink: 0; padding-top: 2px; }
    .thna-logo-img { width: 160px; height: auto; display: block; }
    .thna-info-col { font-family: Arial, Helvetica, sans-serif; color: #222; line-height: 1.38; }
    .thna-title { font-size: 14pt; font-weight: 700; color: #0000FF; margin-bottom: 3px; }
    .thna-sub { font-size: 10pt; margin-bottom: 2px; }
    .thna-line { font-size: 10pt; color: #222; }
    .thna-link { color: #0000FF; text-decoration: underline; }
    .thna-divider { font-family: "Times New Roman", Times, serif; font-size: 11pt; text-align: center; letter-spacing: 0.4px; margin: 12px 0 14px; overflow: hidden; white-space: nowrap; }
    .thna-doc-title { font-size: 16pt; font-weight: 700; text-align: center; margin-bottom: 6px; }
    .thna-doc-date { font-size: 12pt; text-align: right; padding-right: 28px; margin-bottom: 14px; }
    .thna-section { line-height: 1.85; }
    .thna-bold-12 { font-size: 12pt; font-weight: 700; }
    .thna-bold-13 { font-size: 13pt; font-weight: 700; }
    .thna-driver-line { font-size: 13pt; margin-top: 2px; }
    .thna-table { width: 100%; border-collapse: collapse; margin: 14px 0 16px; font-size: 12pt; }
    .thna-table th, .thna-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
    .thna-table thead th { font-weight: 700; text-align: center; height: 26px; }
    .thna-table tbody td { height: 36px; }
    .thna-weight-words { font-size: 13pt; padding-left: 24px; margin: 16px 0 22px; }
    .thna-signatures { display: flex; justify-content: space-between; font-size: 13pt; padding: 0 36px; margin-top: 16px; }
    .thna-sig-col { text-align: center; width: 45%; }
    .thna-sig-title { margin-bottom: 68px; }
    .thna-sig-sub { font-size: 12.5pt; }
  </style>
</head>
<body>
  <div class="no-print-bar">
    <div>
      <div class="no-print-info">🖨️ Danh sách in: ${count} Biên Bản Giao Nhận Hàng Nhập</div>
      <div class="no-print-sub">Tự động nhận diện Form theo Kho Nhận: ${escapeAttr(summaryBreakdown)} (Có thể bấm vào chữ trên phiếu để sửa trực tiếp trước khi in)</div>
    </div>
    <div class="no-print-actions">
      <button type="button" class="btn-top-print" onclick="window.print()">🖨️ In Phiếu (${count})</button>
      <button type="button" class="btn-top-close" onclick="window.close()">✕ Đóng trang in</button>
    </div>
  </div>
  ${pagesHtmlArr.join("")}
  <script>
    window.addEventListener("load", function() {
      setTimeout(function() {
        window.focus();
        window.print();
      }, 300);
    });
  <\/script>
</body>
</html>`;
    }

    function executeBulkBienBanPrint(selectedPos) {
      if (!selectedPos || selectedPos.length === 0) {
        showToast("⚠️ Vui lòng chọn ít nhất 1 lô hàng Nhập để in Biên Bản!", "warning");
        return;
      }

      const tplCounts = {};
      const pagesHtmlArr = selectedPos.map((po, idx) => {
        const tpl = detectBbTemplate(po);
        tplCounts[tpl.name] = (tplCounts[tpl.name] || 0) + 1;
        return buildBienBanPageHtml(po, idx > 0);
      });

      const summaryBreakdown = Object.entries(tplCounts)
        .map(([name, cnt]) => `${cnt} ${name}`)
        .join(" • ");

      showToast(`Đang mở trang in ${selectedPos.length} Biên Bản (${summaryBreakdown})...`, "success");

      const carrierLabel = state.bulkCarrier ? ` - ${state.bulkCarrier}` : "";
      const title = `In ${selectedPos.length} Biên Bản Giao Nhận${carrierLabel} (${formatIsoToVN(state.fromDate)})`;
      const printHtml = buildFullBienBanPrintDocument(pagesHtmlArr, title, summaryBreakdown);

      const w = window.open("", "_blank");
      if (!w) {
        showToast("⚠️ Trình duyệt đang chặn cửa sổ In. Vui lòng cho phép Pop-up trên trang này!", "error");
        return;
      }
      w.document.open();
      w.document.write(printHtml);
      w.document.close();
    }

    function executeBulkPrint() {
      const isNhap = state.xuatNhap === "N";
      const docLabel = isNhap ? "Biên Bản" : "PKL";

      if (!state.bulkSelectedIds || state.bulkSelectedIds.size === 0) {
        showToast(`⚠️ Vui lòng tick chọn ít nhất 1 lô hàng để in ${docLabel}!`, "warning");
        return;
      }

      const selectedPos = [];
      filteredShipments.forEach(item => {
        if (state.bulkSelectedIds.has(String(item.idPo))) {
          selectedPos.push(item);
        }
      });

      if (selectedPos.length === 0) {
        showToast("⚠️ Không tìm thấy thông tin các lô hàng đã chọn!", "warning");
        return;
      }

      // Nếu đang ở Tab Hàng Nhập (N) -> Chạy bộ In Biên Bản tự nhận diện Form theo Kho Nhận
      if (isNhap) {
        executeBulkBienBanPrint(selectedPos);
        return;
      }

      // Nếu đang ở Tab Hàng Xuất (X) -> Chạy bộ In PKL hàng loạt
      showToast(`Đang kết xuất ${selectedPos.length} phiếu PKL (${state.bulkCarrier})...`, "info");

      const offCanvas = document.createElement("canvas");
      const pagesHtmlArr = [];

      try {
        for (let i = 0; i < selectedPos.length; i++) {
          const po = selectedPos[i];
          renderPklToCanvas(po, offCanvas);
          const imgData = offCanvas.toDataURL("image/jpeg", 0.96);
          pagesHtmlArr.push(`
            <div class="print-page">
              <img src="${imgData}" alt="PKL #${po.idPo}" />
            </div>
          `);
        }
      } catch (err) {
        console.error("Lỗi kết xuất canvas PKL:", err);
        showToast("⚠️ Lỗi khi xuất hình ảnh PKL: " + (err?.message || err), "error");
        return;
      }

      const title = `In ${selectedPos.length} Phiếu PKL - ${state.bulkCarrier} (${formatIsoToVN(state.fromDate)})`;
      const printHtml = `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>${escapeAttr(title)}</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 0;
    }
    html, body {
      margin: 0;
      padding: 0;
      background: #ffffff;
    }
    .print-page {
      width: 100vw;
      height: 100vh;
      page-break-after: always;
      break-after: page;
      display: flex;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      overflow: hidden;
    }
    .print-page:last-child {
      page-break-after: avoid;
      break-after: avoid;
    }
    .print-page img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }
  </style>
</head>
<body onload="setTimeout(function(){ window.focus(); window.print(); }, 350);">
  ${pagesHtmlArr.join("")}
</body>
</html>`;

      const w = window.open("", "_blank");
      if (!w) {
        showToast("⚠️ Trình duyệt đang chặn cửa sổ In. Vui lòng cho phép Pop-up trên trang này!", "error");
        return;
      }
      w.document.open();
      w.document.write(printHtml);
      w.document.close();
    }

    // Đóng dropdown khi click ra ngoài
    document.addEventListener("click", function(e) {
      const pop = document.getElementById("bulkDropdownPop");
      const mainBtn = document.getElementById("btnBulkPrintMain");
      const tagBtn = document.getElementById("bulkTagCarrier");
      if (pop && pop.classList.contains("show")) {
        if (!pop.contains(e.target) && (!mainBtn || !mainBtn.contains(e.target)) && (!tagBtn || !tagBtn.contains(e.target))) {
          closeBulkDropdown();
        }
      }
    });

    function fetchJsonpFromGas(url) {
      return new Promise((resolve, reject) => {
        const cbName = "pkl_jsonp_" + Math.round(Math.random() * 1000000);
        window[cbName] = (data) => {
          delete window[cbName];
          if (script && script.parentNode) script.parentNode.removeChild(script);
          resolve(data);
        };
        const script = document.createElement("script");
        script.src = url + (url.includes("?") ? "&" : "?") + "callback=" + cbName;
        script.onerror = () => {
          delete window[cbName];
          if (script && script.parentNode) script.parentNode.removeChild(script);
          reject(new Error("Không kết nối được Google Apps Script"));
        };
        document.body.appendChild(script);
      });
    }

    async function fetchPklFromGoogleSheet() {
      if (!currentPklPo) return;
      const plate = (currentPklPo.soXePo || "").trim();
      if (!plate) {
        showToast("Lô hàng này chưa có Biển số xe để tra cứu!", "warning");
        return;
      }
      const statusText = document.getElementById("pklStatusText");
      statusText.style.color = "#0369a1";
      statusText.textContent = `⏳ Đang lấy PDF từ Google Sheet PKL cho xe ${plate}...`;
      try {
        const res = await fetchJsonpFromGas(`${GAS_PKL_WEBAPP_URL}?plate=${encodeURIComponent(plate)}`);
        if (res && res.status === "success" && res.pdfBase64) {
          customGasPdfBase64 = res.pdfBase64;
          statusText.style.color = "#15803d";
          statusText.textContent = `✅ Đã đồng bộ file PDF từ Sheet PKL cho xe ${plate}! Bấm Tải PDF hoặc Gửi Zalo.`;
          showToast(`Đã lấy PDF từ Google Sheet PKL cho xe ${plate}!`, "success");
        } else {
          statusText.style.color = "#dc2626";
          statusText.textContent = `⚠️ ${res?.message || "Chưa có xe này trên Sheet PKL cũ (Đang dùng bản trực tiếp từ TMS)"}`;
        }
      } catch (err) {
        statusText.style.color = "#dc2626";
        statusText.textContent = `⚠️ Lỗi gọi Sheet PKL cũ: ${err.message} (Đang dùng bản trực tiếp từ TMS)`;
      }
    }

    // =========================================================================
    // 10. ACCOUNT SETTINGS, MULTI-USER LOGIN & PWA INSTALL
    // =========================================================================
    async function submitLoginScreen(e) {
      if (e) e.preventDefault();
      const uName = document.getElementById("loginUsername").value.trim();
      const uPass = document.getElementById("loginPassword").value.trim();
      const errBox = document.getElementById("loginErrorBox");
      const btn = document.getElementById("btnLoginSubmit");

      errBox.style.display = "none";
      if (!uName || !uPass) {
        errBox.textContent = "Vui lòng nhập đầy đủ Tài khoản và Mật khẩu!";
        errBox.style.display = "block";
        return;
      }

      btn.disabled = true;
      btn.textContent = "⏳ Đang xác thực với TMS...";

      try {
        const urlsToTry = [
          "/api/index.php?route=login",
          "/api/login"
        ];
        let loginResult = null;

        for (const u of urlsToTry) {
          try {
            const resp = await fetch(u, {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ username: uName, password: uPass })
            });
            const info = await resp.json();
            loginResult = { status: resp.status, info };
            break;
          } catch (_) {}
        }

        if (loginResult && loginResult.info && !loginResult.info.ok) {
          errBox.textContent = loginResult.info.error || "Sai tên đăng nhập hoặc mật khẩu TMS!";
          errBox.style.display = "block";
          return;
        }

        state.username = uName;
        state.password = uPass;
        state.userString = (loginResult && loginResult.info && loginResult.info.userString)
          ? loginResult.info.userString
          : `${uName}<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.`;
        state.isLoggedIn = true;

        try {
          localStorage.setItem("vs_auth_v2", "1");
          localStorage.setItem("vs_username", state.username);
          localStorage.setItem("vs_password", state.password);
          localStorage.setItem("vs_userString", state.userString);
        } catch (_) {}

        document.getElementById("userLabel").textContent = state.username;
        document.getElementById("loginOverlay").classList.remove("open");
        showToast(`Xin chào ${state.username}! Đang tải dữ liệu...`, "success");
        refreshData(false);
        syncLiveDriverDatabase(false);
      } finally {
        btn.disabled = false;
        btn.textContent = "🔐 ĐĂNG NHẬP HỆ THỐNG";
      }
    }

    function logoutAccount() {
      closeAccountModal();
      state.isLoggedIn = false;
      state.username = "";
      state.password = "";
      state.userString = "";
      try {
        localStorage.removeItem("vs_auth_v2");
        localStorage.removeItem("vs_username");
        localStorage.removeItem("vs_password");
        localStorage.removeItem("vs_userString");
      } catch (_) {}
      document.getElementById("loginUsername").value = "";
      document.getElementById("loginPassword").value = "";
      document.getElementById("loginErrorBox").style.display = "none";
      document.getElementById("loginOverlay").classList.add("open");
      setTimeout(() => document.getElementById("loginUsername").focus(), 150);
    }

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
      const uName = document.getElementById("cfgUsername").value.trim();
      const uPass = document.getElementById("cfgPassword").value.trim();
      if (!uName || !uPass) {
        showToast("Vui lòng nhập đầy đủ Tài khoản và Mật khẩu!", "error");
        return;
      }
      state.username = uName;
      state.password = uPass;
      state.userString = document.getElementById("cfgUserString").value.trim();
      state.apiDate = document.getElementById("cfgApiDate").value || getTodayISO();

      // Try logging in via /api/index.php?route=login
      try {
        const resp = await fetch("/api/index.php?route=login", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username: state.username, password: state.password })
        });
        const info = await resp.json();
        if (!resp.ok || !info.ok) {
          showToast(info.error || "Sai tài khoản hoặc mật khẩu TMS!", "error");
          return;
        }
        if (info.userString) {
          state.userString = info.userString;
          document.getElementById("cfgUserString").value = info.userString;
        }
      } catch (_) {}

      state.isLoggedIn = true;
      try {
        localStorage.setItem("vs_auth_v2", "1");
        localStorage.setItem("vs_username", state.username);
        localStorage.setItem("vs_password", state.password);
        localStorage.setItem("vs_userString", state.userString);
        localStorage.setItem("vs_apiDate", state.apiDate);
      } catch (_) {}

      document.getElementById("userLabel").textContent = state.username;
      document.getElementById("serverDateInfo").textContent = `Mốc dữ liệu API: ${formatIsoToVN(state.apiDate)}`;
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
        alert("📲 Để cài App trên iPhone / iPad:\n\n1. Mở link này bằng trình duyệt Safari.\n2. Bấm biểu tượng Chia sẻ (ô vuông có mũi tên hướng lên ⬆️ ở thanh dưới cùng).\n3. Kéo xuống chọn 'Thêm vào MH chính' (Add to Home Screen) ➔ Bấm 'Thêm'.");
      }
    }

    // Initialize App
    window.addEventListener("DOMContentLoaded", () => {
      const realToday = getTodayISO();
      state.apiDate = realToday;
      if (state.dateMode === "today") {
        state.fromDate = realToday;
        state.toDate = realToday;
      }
      updateQuickDateChipLabels();

      if (isIOS && !isStandalone) {
        document.getElementById("btnInstallPwa").style.display = "inline-flex";
      }
      if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js").then(reg => reg.update()).catch(() => {});
      }
      document.getElementById("filterFromDate").value = state.fromDate;
      document.getElementById("filterToDate").value = state.toDate;
      document.getElementById("serverDateInfo").textContent = `Mốc dữ liệu API: ${formatIsoToVN(state.apiDate)}`;

      if (!state.isLoggedIn || !state.username) {
        document.getElementById("loginOverlay").classList.add("open");
        setTimeout(() => document.getElementById("loginUsername").focus(), 150);
      } else {
        document.getElementById("userLabel").textContent = state.username;
        refreshData(false);
        syncLiveDriverDatabase(false);
      }
    });
  </script>
</body>
</html><!DOCTYPE html>
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
      gap: 8px;
      min-width: 0;
      flex: 1;
      overflow: hidden;
    }

    .brand-text {
      min-width: 0;
      flex: 1;
      overflow: hidden;
    }

    .brand-logo {
      width: 34px;
      height: 34px;
      background: #fff;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: var(--primary);
      font-size: 13.5px;
      letter-spacing: -0.5px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      flex-shrink: 0;
    }

    .brand-title {
      font-weight: 800;
      font-size: 15px;
      letter-spacing: 0.2px;
      line-height: 1.15;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .brand-sub {
      font-size: 11px;
      opacity: 0.92;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 5px;
      line-height: 1.25;
      margin-top: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #4ade80;
      display: inline-block;
      box-shadow: 0 0 6px #4ade80;
      flex-shrink: 0;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 5px;
      flex-shrink: 0;
    }

    .icon-btn {
      background: rgba(255, 255, 255, 0.16);
      border: 1px solid rgba(255, 255, 255, 0.24);
      color: #fff;
      border-radius: 9px;
      padding: 6px 8px;
      font-size: 11.5px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
      flex-shrink: 0;
      cursor: pointer;
      transition: all 0.15s;
    }

    /* FULL-SCREEN LOGIN OVERLAY */
    .login-overlay {
      position: fixed;
      inset: 0;
      z-index: 3000;
      background: linear-gradient(145deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-overlay.open {
      display: flex;
    }

    .login-card {
      width: 100%;
      max-width: 390px;
      background: #ffffff;
      border-radius: 20px;
      padding: 26px 22px 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
      animation: fadeUp 0.25s ease;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-logo-box {
      width: 56px;
      height: 56px;
      margin: 0 auto 12px;
      border-radius: 16px;
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
      color: #ffffff;
      font-weight: 900;
      font-size: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.32);
    }

    .login-error {
      display: none;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
      padding: 9px 12px;
      border-radius: 10px;
      font-size: 12.5px;
      font-weight: 600;
      margin-bottom: 12px;
      text-align: center;
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

    #chipSingleDate {
      position: relative;
      cursor: pointer;
      margin: 0;
    }
    #quickSingleDatePicker {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
      border: none;
      z-index: 2;
    }
    #quickSingleDatePicker::-webkit-calendar-picker-indicator {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      margin: 0;
      padding: 0;
      opacity: 0;
      cursor: pointer;
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
      border-left: 5px solid #ea580c;
      background: #fffbf5;
    }

    .po-card.type-N {
      border-left: 5px solid #16a34a;
      background: #ffffff;
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
      background: #ffedd5;
      color: #c2410c;
      border: 1px solid #fed7aa;
    }

    .badge-nhap {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
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
      grid-template-columns: minmax(0, 1.08fr) minmax(0, 1fr);
      gap: 8px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 8px 10px;
      margin-bottom: 8px;
      overflow: hidden;
    }

    .cont-side, .truck-side {
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-width: 0;
      overflow: hidden;
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
      font-size: 11px;
      font-weight: 700;
      color: #334155;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-top: 1px;
      max-width: 100%;
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
      gap: 5px;
      margin-top: 9px;
      width: 100%;
    }

    .btn-card {
      flex: 1 1 0;
      min-width: 0;
      padding: 7px 5px;
      border-radius: 9px;
      font-size: 11.5px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 3px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      transition: all 0.15s;
    }

    .btn-edit {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
      flex: 1.05 1 0;
    }

    .btn-zalo {
      background: #f1f5f9;
      color: #0f172a;
      border: 1px solid #cbd5e1;
      flex: 1 1 0;
    }

    .btn-call {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
      flex: 0 0 36px;
      width: 36px;
      padding: 7px 0;
      text-decoration: none;
    }

    /* FLOATING 20' / 40' QUICK FILTER DOCK (BOTTOM RIGHT) */
    .floating-size-dock {
      position: fixed;
      right: 12px;
      bottom: calc(68px + var(--safe-bottom));
      z-index: 95;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .fab-size-btn {
      width: 46px;
      height: 46px;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.94);
      backdrop-filter: blur(6px);
      border: 1.5px solid #cbd5e1;
      box-shadow: 0 4px 14px rgba(15, 23, 42, 0.16);
      color: #1e293b;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2px;
      transition: all 0.16s ease;
      user-select: none;
      -webkit-tap-highlight-color: transparent;
    }

    .fab-size-btn:active {
      transform: scale(0.93);
    }

    .fab-size-lbl {
      font-size: 13px;
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -0.3px;
    }

    .fab-size-cnt {
      font-size: 10px;
      font-weight: 700;
      color: #64748b;
      background: #f1f5f9;
      padding: 0 5px;
      border-radius: 6px;
      margin-top: 2px;
      line-height: 1.25;
    }

    .fab-size-btn.active-20 {
      background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
      border-color: #1d4ed8;
      color: #ffffff;
      box-shadow: 0 6px 18px rgba(37, 99, 235, 0.42);
      transform: scale(1.06);
    }

    .fab-size-btn.active-40 {
      background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);
      border-color: #c2410c;
      color: #ffffff;
      box-shadow: 0 6px 18px rgba(234, 88, 12, 0.42);
      transform: scale(1.06);
    }

    .fab-size-btn.active-20 .fab-size-cnt,
    .fab-size-btn.active-40 .fab-size-cnt {
      background: rgba(255, 255, 255, 0.24);
      color: #ffffff;
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

    /* =========================================================================
       DESKTOP SPREADSHEET TABLE MODE (NHẬP NHANH ĐVVT & SỐ XE NHƯ EXCEL/TMS WEB)
       ========================================================================= */
    body.desktop-table-mode .list-container {
      display: block !important;
      max-width: 100% !important;
      padding: 4px 10px 84px;
    }

    body.desktop-table-mode .summary-strip {
      display: flex !important;
      align-items: center;
      gap: 10px;
      max-width: 100% !important;
      padding: 10px 10px 4px;
    }

    body.desktop-table-mode .summary-strip .stat-card {
      flex: 1 1 0;
      min-width: 110px;
    }

    body.desktop-table-mode .filter-section,
    body.desktop-table-mode .chuhang-bar {
      max-width: 100% !important;
    }

    /* DESKTOP BULK ACTIONS (IN PKL HÀNG LOẠT / IN BB) */
    .desktop-bulk-ctrl {
      display: none;
      position: relative;
      align-items: center;
      margin-left: auto;
      flex-shrink: 0;
    }

    body.desktop-table-mode .desktop-bulk-ctrl {
      display: flex;
    }

    .bulk-dropdown-wrap {
      position: relative;
      display: inline-block;
    }

    .btn-bulk-main {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
      color: #ffffff;
      border: 1px solid #1d4ed8;
      border-radius: 9px;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
      transition: all 0.15s ease;
      white-space: nowrap;
    }

    .btn-bulk-main:hover {
      background: linear-gradient(135deg, #172554 0%, #1d4ed8 100%);
      box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
      transform: translateY(-1px);
    }

    .bulk-dropdown-pop {
      display: none;
      position: absolute;
      top: calc(100% + 6px);
      right: 0;
      min-width: 215px;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 10px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
      padding: 6px;
      z-index: 1000;
    }

    .bulk-dropdown-pop.show {
      display: block;
      animation: bulkFadeDown 0.15s ease-out;
    }

    @keyframes bulkFadeDown {
      from { opacity: 0; transform: translateY(-4px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .bulk-opt-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.15s ease;
      user-select: none;
    }

    .bulk-opt-item:hover {
      background: #eff6ff;
    }

    .bulk-opt-icon {
      font-size: 20px;
      flex-shrink: 0;
    }

    .bulk-opt-info {
      display: flex;
      flex-direction: column;
      text-align: left;
    }

    .bulk-opt-title {
      font-size: 13px;
      font-weight: 800;
      color: #1e3a8a;
    }

    .bulk-opt-desc {
      font-size: 11px;
      color: #64748b;
      margin-top: 1px;
    }

    /* ACTIVE BAR IN SUMMARY STRIP */
    .bulk-active-bar {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #ffffff;
      padding: 4px 6px;
      border: 1.5px solid #3b82f6;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }

    .bulk-tag-carrier {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #e0f2fe;
      color: #0369a1;
      padding: 6px 10px;
      border-radius: 7px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      border: 1px solid #bae6fd;
      transition: all 0.15s ease;
      user-select: none;
    }

    .bulk-tag-carrier:hover {
      background: #bae6fd;
      color: #0c4a6e;
    }

    .btn-bulk-execute {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
      color: #ffffff;
      border: none;
      border-radius: 7px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3);
      transition: all 0.15s ease;
      white-space: nowrap;
    }

    .btn-bulk-execute:hover {
      background: linear-gradient(135deg, #166534 0%, #15803d 100%);
      box-shadow: 0 3px 8px rgba(22, 163, 74, 0.4);
      transform: translateY(-1px);
    }

    .btn-bulk-exit {
      background: #ffffff;
      color: #64748b;
      border: 1px solid #cbd5e1;
      border-radius: 7px;
      padding: 6px 10px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .btn-bulk-exit:hover {
      background: #fee2e2;
      color: #dc2626;
      border-color: #fca5a5;
    }

    /* =========================================================================
       VISUAL BIÊN BẢN (BB) TEMPLATE MANAGER MODAL & A4 WYSIWYG PAPER
       ========================================================================= */
    .bb-mgr-box {
      background: #ffffff;
      border-radius: 14px;
      box-shadow: 0 25px 60px rgba(15, 23, 42, 0.35);
      width: 96vw;
      max-width: 1320px;
      height: 92vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .bb-mgr-header {
      background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
      color: #ffffff;
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-shrink: 0;
    }
    .bb-mgr-body {
      display: flex;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }
    .bb-mgr-sidebar {
      width: 285px;
      border-right: 1px solid #cbd5e1;
      background: #f8fafc;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
    }
    .bb-mgr-list {
      flex: 1;
      overflow-y: auto;
      padding: 10px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .bb-tpl-card {
      background: #ffffff;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      padding: 10px 12px;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .bb-tpl-card:hover {
      border-color: #0d9488;
      background: #f0fdfa;
    }
    .bb-tpl-card.active {
      border-color: #0f766e;
      background: #ccfbf1;
      box-shadow: 0 2px 8px rgba(15, 118, 110, 0.15);
    }
    .bb-tpl-card-title {
      font-size: 13px;
      font-weight: 800;
      color: #0f172a;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
    }
    .bb-tpl-card-kw {
      font-size: 11px;
      color: #0f766e;
      font-weight: 600;
      margin-top: 4px;
      word-break: break-word;
    }
    .bb-mgr-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
      background: #f1f5f9;
      overflow: hidden;
    }
    .bb-mgr-meta-bar {
      background: #ffffff;
      border-bottom: 1px solid #cbd5e1;
      padding: 10px 14px;
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 10px;
      flex-shrink: 0;
    }
    .bb-meta-field {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
    .bb-meta-field label {
      font-size: 11px;
      font-weight: 800;
      color: #334155;
      text-transform: uppercase;
    }
    .bb-meta-inp {
      border: 1px solid #94a3b8;
      border-radius: 7px;
      padding: 6px 10px;
      font-size: 12.5px;
      font-weight: 600;
      color: #0f172a;
    }
    .bb-ac-wrap {
      position: relative;
      display: flex;
      flex: 1;
    }
    .bb-ac-dropdown {
      display: none;
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      max-height: 240px;
      overflow-y: auto;
      background: #ffffff;
      border: 1.5px solid #0d9488;
      border-radius: 9px;
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.22);
      z-index: 10050;
      padding: 4px;
    }
    .bb-ac-dropdown.open {
      display: block;
    }
    .bb-ac-item {
      padding: 7px 10px;
      border-radius: 6px;
      font-size: 12.5px;
      font-weight: 600;
      color: #0f172a;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      transition: background 0.12s;
    }
    .bb-ac-item:hover {
      background: #ccfbf1;
      color: #0f766e;
    }
    .bb-mgr-toolbar {
      background: #f8fafc;
      border-bottom: 1px solid #cbd5e1;
      padding: 8px 14px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }
    .bb-tool-btn {
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 12px;
      font-weight: 700;
      color: #1e293b;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: all 0.12s;
    }
    .bb-tool-btn:hover {
      background: #e2e8f0;
      border-color: #94a3b8;
    }
    .bb-var-chip {
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 11.5px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      transition: all 0.12s;
    }
    .bb-var-chip:hover {
      background: #dbeafe;
      border-color: #60a5fa;
      transform: translateY(-1px);
    }
    .bb-mgr-canvas-wrap {
      flex: 1;
      overflow: auto;
      padding: 18px;
      background: #cbd5e1;
    }
    .bb-a4-editor {
      width: 190mm;
      min-height: 255mm;
      margin: 0 auto;
      background: #ffffff;
      padding: 8mm 8mm;
      box-shadow: 0 6px 24px rgba(15, 23, 42, 0.22);
      outline: none;
      font-family: "Times New Roman", Times, serif;
      color: #000000;
      position: relative;
    }
    .bb-a4-editor:focus {
      box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.35), 0 6px 24px rgba(15, 23, 42, 0.22);
    }
    .bb-mgr-footer {
      background: #ffffff;
      border-top: 1px solid #cbd5e1;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-shrink: 0;
    }
    /* Shared A4 Template Classes for Live Editor & Print */
    .bb-vietsun { font-family: "Times New Roman", Times, serif; font-size: 13pt; color: #000; }
    .vs-div-picture { width: 100%; min-height: 225mm; position: relative; }
    .vs-watermark-img { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 82%; max-height: 175mm; object-fit: contain; opacity: 0.22; pointer-events: none; z-index: 0; }
    .vs-seal-img { position: absolute; top: 18mm; left: 10mm; width: 39mm; height: 39mm; object-fit: contain; pointer-events: none; z-index: 1; }
    .vs-content-layer { position: relative; z-index: 2; }
    .vs-header-table { width: 100%; border-collapse: collapse; }
    .vs-info-table { width: 100%; border-collapse: collapse; line-height: 1.8; vertical-align: top; }
    .vs-info-table th, .vs-info-table td { padding: 0 2px; vertical-align: top; }
    .vs-bangND { width: 100%; border-collapse: collapse; line-height: 1.8; margin: 4px 0; }
    .vs-bangND th, .vs-bangND td { border: 1px solid #000; padding: 2px 10px; text-align: center; }
    .vs-sig-row { display: flex; justify-content: space-between; text-align: center; font-weight: 700; margin-top: 8px; }
    .vs-sig-col { width: 33.33%; }
    .bb-4oranges { font-family: "Times New Roman", Times, serif; color: #000; padding-top: 4mm; }
    .thna-header { display: flex; align-items: flex-start; gap: 16px; padding-left: 8px; }
    .thna-logo-col { flex-shrink: 0; padding-top: 2px; }
    .thna-logo-img { width: 160px; height: auto; display: block; }
    .thna-info-col { font-family: Arial, Helvetica, sans-serif; color: #222; line-height: 1.38; }
    .thna-title { font-size: 14pt; font-weight: 700; color: #0000FF; margin-bottom: 3px; }
    .thna-sub { font-size: 10pt; margin-bottom: 2px; }
    .thna-line { font-size: 10pt; color: #222; }
    .thna-link { color: #0000FF; text-decoration: underline; }
    .thna-divider { font-family: "Times New Roman", Times, serif; font-size: 11pt; text-align: center; letter-spacing: 0.4px; margin: 12px 0 14px; overflow: hidden; white-space: nowrap; }
    .thna-doc-title { font-size: 16pt; font-weight: 700; text-align: center; margin-bottom: 6px; }
    .thna-doc-date { font-size: 12pt; text-align: right; padding-right: 28px; margin-bottom: 14px; }
    .thna-section { line-height: 1.85; }
    .thna-bold-12 { font-size: 12pt; font-weight: 700; }
    .thna-bold-13 { font-size: 13pt; font-weight: 700; }
    .thna-driver-line { font-size: 13pt; margin-top: 2px; }
    .thna-table { width: 100%; border-collapse: collapse; margin: 14px 0 16px; font-size: 12pt; }
    .thna-table th, .thna-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
    .thna-table thead th { font-weight: 700; text-align: center; height: 26px; }
    .thna-table tbody td { height: 36px; }
    .thna-weight-words { font-size: 13pt; padding-left: 24px; margin: 16px 0 22px; }
    .thna-signatures { display: flex; justify-content: space-between; font-size: 13pt; padding: 0 36px; margin-top: 16px; }
    .thna-sig-col { text-align: center; width: 45%; }
    .thna-sig-title { margin-bottom: 68px; }
    .thna-sig-sub { font-size: 12.5pt; }

    .pc-table-wrap {
      width: 100%;
      overflow-x: auto;
      background: #ffffff;
      border: 1px solid #94a3b8;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    }

    .pc-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      color: #0f172a;
      table-layout: auto;
    }

    .pc-table th,
    .pc-table td {
      border: 1px solid #94a3b8;
      padding: 4px 6px;
      vertical-align: middle;
      line-height: 1.25;
    }

    .pc-table thead th {
      position: sticky;
      top: 0;
      z-index: 10;
      background: #cfe8fc;
      color: #0f172a;
      font-weight: 800;
      font-size: 11px;
      text-align: center;
      white-space: nowrap;
      padding: 6px 5px;
      text-transform: uppercase;
    }

    .pc-table tbody tr.pc-row-xuat {
      background-color: #fff7ed !important;
    }

    .pc-table tbody tr.pc-row-xuat:hover {
      background-color: #fed7aa !important;
    }

    .pc-table tbody tr.pc-row-nhap {
      background-color: #ffffff;
    }

    .pc-table tbody tr.pc-row-nhap:hover {
      background-color: #f1f5f9;
    }

    .pc-table tbody tr.pc-row-active {
      background: #fef08a !important;
    }

    .pc-cell-cyan {
      background: #d8f3ff !important;
      padding: 0 !important;
      min-width: 115px;
    }

    .pc-cell-edit {
      padding: 0 !important;
    }

    .pc-inp {
      width: 100%;
      height: 28px;
      border: 1.5px solid transparent;
      background: transparent;
      padding: 2px 6px;
      font-size: 12.5px;
      font-family: inherit;
      color: #0f172a;
      outline: none;
      box-sizing: border-box;
      transition: all 0.12s;
    }

    .pc-inp:focus {
      background: #ffffff !important;
      border-color: #2563eb !important;
      box-shadow: inset 0 0 0 1px #2563eb;
    }

    .pc-inp.pc-saving {
      background: #fef3c7 !important;
    }

    .pc-inp.pc-saved {
      background: #dcfce7 !important;
      color: #166534 !important;
      font-weight: 800;
    }

    .pc-truncate {
      max-width: 185px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .pc-actions-cell {
      white-space: nowrap;
      text-align: center;
      padding: 2px 4px !important;
    }

    .pc-act-btn {
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      color: #0f172a;
      border-radius: 5px;
      padding: 2px 6px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
      margin: 0 1px;
    }

    .pc-act-btn:hover {
      background: #e2e8f0;
    }

    .pc-del-btn {
      border: 1px solid #fecaca;
      background: #fff1f2;
      color: #dc2626;
      border-radius: 4px;
      padding: 1px 4px;
      font-size: 11px;
      line-height: 1.2;
      cursor: pointer;
      transition: all 0.15s ease;
    }

    .pc-del-btn:hover {
      background: #fee2e2;
      border-color: #f87171;
      transform: scale(1.1);
    }

    /* FLOATING AUTOCOMPLETE FOR DESKTOP TABLE CELLS */
    .pc-ac-floating {
      position: fixed;
      z-index: 5000;
      background: #ffffff;
      border: 1.5px solid #2563eb;
      border-radius: 10px;
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.25);
      max-height: 240px;
      width: 310px;
      overflow-y: auto;
      display: none;
    }

    .pc-ac-floating.open {
      display: block;
    }
  </style>
</head>
<body>

  <div class="toast-container" id="toastContainer"></div>
  <div class="pc-ac-floating" id="pcAcFloating"></div>

  <!-- FULL-SCREEN LOGIN OVERLAY FOR MULTI-USER ACCESS -->
  <div class="login-overlay" id="loginOverlay">
    <div class="login-card">
      <div class="login-logo-box">VS</div>
      <div style="text-align:center;margin-bottom:18px;">
        <div style="font-size:18px;font-weight:800;color:#0f172a;">VIETSUN TMS MOBILE</div>
        <div style="font-size:12.5px;color:#64748b;margin-top:3px;">Đăng nhập bằng tài khoản TMS của bạn</div>
      </div>

      <div class="login-error" id="loginErrorBox"></div>

      <form onsubmit="submitLoginScreen(event)">
        <div class="form-group" style="margin-bottom:12px;">
          <label>👤 Tài khoản TMS (Username)</label>
          <input
            type="text"
            class="form-control"
            id="loginUsername"
            placeholder="Tên tài khoản điều độ..."
            autocomplete="username"
            required
          />
        </div>

        <div class="form-group" style="margin-bottom:14px;">
          <label>🔑 Mật khẩu (Password)</label>
          <input
            type="password"
            class="form-control"
            id="loginPassword"
            placeholder="Nhập mật khẩu..."
            autocomplete="current-password"
            required
          />
        </div>

        <button type="submit" class="btn-primary-lg" id="btnLoginSubmit" style="width:100%;padding:12px;">
          🔐 ĐĂNG NHẬP HỆ THỐNG
        </button>
      </form>
    </div>
  </div>

  <!-- HEADER -->
  <header class="app-header">
    <div class="header-top">
      <div class="brand">
        <div class="brand-logo">VS</div>
        <div class="brand-text">
          <div class="brand-title">VIETSUN TMS</div>
          <div class="brand-sub">
            <span class="status-dot" id="connDot"></span>
            <span id="syncTimeLabel">Đang tải...</span>
          </div>
        </div>
      </div>
      <div class="header-actions">
        <button class="icon-btn" id="btnInstallPwa" style="display:none;" onclick="installPWA()" title="Cài App ra màn hình chính">
          📲 Cài
        </button>
        <button class="icon-btn" onclick="openAccountModal()" title="Tài khoản & Cài đặt">
          👤 <span id="userLabel">TK</span>
        </button>
        <button class="icon-btn" onclick="refreshData(true)" title="Tải lại dữ liệu mới nhất">
          🔄 Mới
        </button>
      </div>
    </div>

    <div class="search-row">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input
          type="search"
          id="searchInput"
          placeholder="Tìm Số Cont, Biển số xe, Khách hàng, Kho, Seal..."
          autocomplete="off"
          oninput="onSearchInput()"
        />
        <button class="clear-search" id="btnClearSearch" onclick="clearSearch()">✕</button>
      </div>
      <button class="filter-toggle-btn" id="btnToggleDesktop" onclick="toggleDesktopMode()" title="Chuyển chế độ Danh sách Bảng PC (nhập nhanh ĐVVT & Số xe) / Thẻ Mobile">
        <span id="lblDesktopMode">🖥️ Bảng PC</span>
      </button>
      <button class="filter-toggle-btn" onclick="toggleAdvancedFilters()">
        <span>⚡ Lọc</span>
        <span class="filter-badge" id="activeFilterCount">1</span>
      </button>
    </div>
  </header>

  <!-- QUICK DATE & FILTER SECTION -->
  <section class="filter-section">
    <!-- Quick Date Chips -->
    <div class="quick-tabs" id="quickDateTabs">
      <button class="chip active" data-date-mode="today" onclick="setQuickDate('today')" id="chipToday">📅 Hôm nay</button>
      <button class="chip" data-date-mode="tomorrow" onclick="setQuickDate('tomorrow')" id="chipTomorrow">⏭️ Ngày mai</button>
      <button class="chip" data-date-mode="d2" onclick="setQuickDate('d2')" id="chipD2">📆 Ngày D+2</button>
      <button class="chip" data-date-mode="d3" onclick="setQuickDate('d3')" id="chipD3">📆 Ngày D+3</button>
      <button class="chip" data-date-mode="yesterday" onclick="setQuickDate('yesterday')" id="chipYesterday">⏮️ Hôm qua</button>
      <label class="chip" data-date-mode="single" id="chipSingleDate" onclick="openQuickSingleDatePicker(event)">
        <span id="chipSingleDateLabel">🎯 Chọn ngày</span>
        <input type="date" id="quickSingleDatePicker" onchange="onQuickSingleDatePick(this.value)" oninput="onQuickSingleDatePick(this.value)" onclick="event.stopPropagation(); try{if(this.showPicker)this.showPicker();}catch(_){}" />
      </label>
      <button class="chip" data-date-mode="3days" onclick="setQuickDate('3days')">📆 ±3 ngày</button>
      <button class="chip" data-date-mode="unset" onclick="setQuickDate('unset')">⚠️ Chưa xếp ngày</button>
      <button class="chip" data-date-mode="all" onclick="setQuickDate('all')">🗂️ Tất cả</button>
    </div>

    <!-- Collapsible Advanced Filters -->
    <div class="advanced-filters" id="advancedFilters">
      <div class="filter-grid">
        <div class="filter-field">
          <label>Lọc theo loại ngày</label>
          <select id="filterDateType" onchange="applyFilters()">
            <option value="ngayTraHang">Ngày Đóng / Giao hàng</option>
            <option value="ngayTauChayPo">Ngày Tàu chạy / cập</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Trạng thái điều xe</label>
          <select id="filterTruckStatus" onchange="applyFilters()">
            <option value="ALL">-- Tất cả trạng thái --</option>
            <option value="NO_TRUCK">⚠️ Chưa có số xe</option>
            <option value="HAS_TRUCK">✅ Đã gán số xe</option>
            <option value="NO_CONT">⚠️ Chưa có số Cont</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Từ ngày (Hoặc chọn 1 ngày)</label>
          <input type="date" id="filterFromDate" onchange="onCustomDateChange('from')" />
        </div>
        <div class="filter-field">
          <label>Đến ngày</label>
          <input type="date" id="filterToDate" onchange="onCustomDateChange('to')" />
        </div>
        <div class="filter-field">
          <label>Đơn vị vận tải (Vendor)</label>
          <select id="filterVendor" onchange="applyFilters()">
            <option value="ALL">-- Tất cả ĐVVT --</option>
          </select>
        </div>
        <div class="filter-field">
          <label>Tỉnh / Khu vực</label>
          <select id="filterProvince" onchange="applyFilters()">
            <option value="ALL">-- Tất cả Tỉnh --</option>
          </select>
        </div>
      </div>
      <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-top:4px;">
        <span style="font-size:11.5px; color:#64748b;" id="serverDateInfo">Mốc dữ liệu API: Hôm nay</span>
        <div style="display:flex; gap:6px;">
          <button class="mini-btn" onclick="resetAllFilters()">Đặt lại bộ lọc</button>
          <button class="mini-btn" style="background:#0284c7;color:#fff;border-color:#0284c7;" onclick="promptApiDate()">Đổi ngày gốc API</button>
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
      <div class="stat-lbl">Tổng Lô/Cont</div>
    </div>
    <div class="stat-card" id="stat2040">
      <div class="stat-val" style="color:#0284c7;" id="count2040">0 / 0</div>
      <div class="stat-lbl">Cont 20' / 40'</div>
    </div>
    <div class="stat-card" id="statHasTruck" onclick="setTruckQuickFilter('HAS_TRUCK')">
      <div class="stat-val" style="color:#16a34a;" id="countHasTruck">0</div>
      <div class="stat-lbl">Đã có xe</div>
    </div>
    <div class="stat-card" id="statNoTruck" onclick="setTruckQuickFilter('NO_TRUCK')">
      <div class="stat-val" style="color:#d97706;" id="countNoTruck">0</div>
      <div class="stat-lbl">Chưa có xe</div>
    </div>

    <!-- BULK ACTIONS (DESKTOP MODE - TAB HÀNG XUẤT IN PKL / TAB HÀNG NHẬP IN BB) -->
    <div class="desktop-bulk-ctrl" id="desktopBulkCtrl" style="display:none;">
      <!-- State 1: Dropdown Button -->
      <div class="bulk-dropdown-wrap" id="bulkDropdownWrap">
        <button type="button" class="btn-bulk-main" id="btnBulkPrintMain" onclick="toggleBulkDropdown(event)" title="In PKL hàng loạt theo ĐVVT">
          <span style="font-size:15px;">🖨️</span>
          <span id="lblBulkAction">In PKL hàng loạt</span>
          <span style="font-size:11px;margin-left:2px;">▾</span>
        </button>
        <div class="bulk-dropdown-pop" id="bulkDropdownPop">
          <div class="bulk-opt-item" onclick="selectBulkCarrier('VIETSUN')">
            <span class="bulk-opt-icon">🚛</span>
            <div class="bulk-opt-info">
              <span class="bulk-opt-title">VIETSUN</span>
              <span class="bulk-opt-desc">Xe nhà Vietsun</span>
            </div>
          </div>
          <div class="bulk-opt-item" onclick="selectBulkCarrier('VENDOR')">
            <span class="bulk-opt-icon">🚚</span>
            <div class="bulk-opt-info">
              <span class="bulk-opt-title">VENDOR</span>
              <span class="bulk-opt-desc">Các đội xe còn lại</span>
            </div>
          </div>
        </div>
      </div>

      <!-- State 2: Active Filter & Print All Bar (Tab Hàng Xuất) -->
      <div class="bulk-active-bar" id="bulkActiveBar" style="display:none;">
        <div class="bulk-tag-carrier" id="bulkTagCarrier" onclick="toggleBulkDropdown(event)" title="Bấm đổi lọc ĐVVT">
          <span id="bulkCarrierIcon">🚛</span>
          <span id="bulkCarrierText">VIETSUN</span>
          <span style="font-size:10px;margin-left:2px;">▾</span>
        </div>
        <button type="button" class="btn-bulk-execute" id="btnBulkExecute" onclick="executeBulkPrint()" title="Bấm để in tất cả phiếu đã chọn">
          <span style="font-size:15px;">🖨️</span>
          <span>Print All (<strong id="bulkSelectedCount">0</strong>)</span>
        </button>
        <button type="button" class="btn-bulk-exit" onclick="exitBulkPrintMode()" title="Hủy & trở về bảng bình thường">✕ Hủy</button>
      </div>

      <!-- State 3: Direct Print Biên Bản Button (Tab Hàng Nhập - Thực thi in luôn không cần sổ ĐVVT) -->
      <div class="bulk-active-bar" id="bulkNhapDirectBar" style="display:none;border-color:#0d9488;box-shadow:0 2px 8px rgba(13,148,136,0.18);">
        <button type="button" class="btn-bulk-exit" onclick="openBbTemplateManager()" style="background:#f0fdfa;color:#0f766e;border-color:#99f6e4;font-weight:800;" title="Quản lý & Chỉnh sửa trực quan các mẫu Biên Bản theo Kho Nhận">
          ⚙️ Mẫu BB (<span id="bbTplCountBadge">2</span>)
        </button>
        <button type="button" class="btn-bulk-execute" id="btnBulkNhapPrint" onclick="executeBulkPrint()" style="background:linear-gradient(135deg, #0f766e 0%, #0d9488 100%);" title="In ngay các Biên Bản đã tick chọn (Tự nhận diện Form theo Kho Nhận)">
          <span style="font-size:15px;">🖨️</span>
          <span>In Biên Bản (<strong id="bulkNhapSelectedCount">0</strong>)</span>
        </button>
        <button type="button" class="btn-bulk-exit" id="btnBulkNhapClear" onclick="clearAllBulkSelection()" style="display:none;" title="Bỏ tick tất cả">✕ Bỏ chọn</button>
      </div>
    </div>
  </div>

  <!-- MAIN SHIPMENT LIST -->
  <main class="list-container" id="poListContainer">
    <div class="state-box">
      <div class="spinner"></div>
      <div style="font-weight:700; color:#334155;">Đang tải dữ liệu điều độ...</div>
      <div style="font-size:12px; margin-top:4px; color:#64748b;">Đang đồng bộ dữ liệu hệ thống...</div>
    </div>
  </main>

  <!-- FLOATING 20' / 40' CONTAINER SIZE FILTER DOCK (BOTTOM RIGHT) -->
  <div class="floating-size-dock" id="floatingSizeDock">
    <button type="button" class="fab-size-btn" id="fabSize20" onclick="toggleContSizeFilter('20')" title="Bấm để lọc Cont 20' (Bấm lại để bỏ lọc)">
      <span class="fab-size-lbl">20'</span>
      <span class="fab-size-cnt" id="fabCount20">0</span>
    </button>
    <button type="button" class="fab-size-btn" id="fabSize40" onclick="toggleContSizeFilter('40')" title="Bấm để lọc Cont 40' (Bấm lại để bỏ lọc)">
      <span class="fab-size-lbl">40'</span>
      <span class="fab-size-cnt" id="fabCount40">0</span>
    </button>
  </div>

  <!-- BOTTOM NAVIGATION (XUAT / NHAP SWITCHER) -->
  <nav class="bottom-nav">
    <button class="nav-item active" data-xn="ALL" onclick="setXuatNhapFilter('ALL')">
      <span class="nav-icon">📋</span>
      <span>Tất cả (<span id="navCountAll">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="X" onclick="setXuatNhapFilter('X')">
      <span class="nav-icon">📤</span>
      <span>Hàng Xuất (<span id="navCountX">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="N" onclick="setXuatNhapFilter('N')">
      <span class="nav-icon">📥</span>
      <span>Hàng Nhập (<span id="navCountN">0</span>)</span>
    </button>
    <button class="nav-item" data-xn="NO_TRUCK" onclick="setXuatNhapFilter('NO_TRUCK')">
      <span class="nav-icon">🚛</span>
      <span>Chờ điều xe (<span id="navCountWait">0</span>)</span>
    </button>
  </nav>

  <!-- EDIT MODAL (BOTTOM SHEET) -->
  <div class="modal-backdrop" id="editModal" onclick="if(event.target===this) closeEditModal()">
    <div class="bottom-sheet">
      <div class="sheet-header">
        <div class="sheet-title">
          <span id="modalBadgeType" class="badge badge-xuat">XUẤT</span>
          <span id="modalTitleText">Cập nhật Lô hàng #210165</span>
        </div>
        <button class="sheet-close" onclick="closeEditModal()">✕</button>
      </div>

      <div class="sheet-body">
        <div class="info-banner">
          <div class="info-banner-title" id="modalCustomerName">Cty Dynaplast</div>
          <div id="modalRouteSub" style="color:#475569;">Tàu: VIETSUN DYNAMIC (1236N) • Chủ hàng: LOG</div>
        </div>

        <form id="editPoForm" onsubmit="saveAllChanges(event)">
          <input type="hidden" id="editIdPo" />
          <input type="hidden" id="editCodePo" />
          <input type="hidden" id="editXuatNhapPo" />

          <div class="form-section-title">
            <span>1. Điều Vận & Phương Tiện</span>
            <span style="font-size:11px; color:#64748b; font-weight:600;">Tự động lưu khi đổi hoặc bấm Lưu</span>
          </div>

          <div class="form-grid">
            <!-- NGAY GIAO / DONG HANG -->
            <div class="form-group full">
              <label>
                <span id="lblNgayTraHang">📅 Ngày Đóng / Giao Hàng</span>
                <span id="status_ngayTraHang" style="font-size:11px;"></span>
              </label>
              <input
                type="date"
                class="form-control"
                id="inp_ngayTraHang"
                onchange="onFieldChanged('ngayTraHang')"
              />
              <div class="quick-date-btns">
                <button type="button" class="mini-btn" onclick="setModalDate(0)">Hôm nay</button>
                <button type="button" class="mini-btn" onclick="setModalDate(1)">Ngày mai</button>
                <button type="button" class="mini-btn" onclick="setModalDate(2)">+2 ngày</button>
                <button type="button" class="mini-btn" style="color:#dc2626;" onclick="setModalDate(null)">Chưa xếp ngày</button>
              </div>
            </div>

            <!-- SO CONT -->
            <div class="form-group">
              <label>
                <span>📦 Số Container</span>
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
                <span>🔒 Số Seal (Chì)</span>
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
                <span>🚛 Số Xe Vận Tải</span>
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
                placeholder="Gõ biển số (VD: 61H...)"
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
                <span>🏢 Đơn Vị Vận Tải</span>
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
                placeholder="Chọn hoặc gõ Vendor..."
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
                  <div style="font-weight:800;" id="drvNameText">👨‍✈️ Tài xế: --</div>
                  <div style="font-size:11.5px; margin-top:2px;" id="drvSubText">📞 SĐT: --</div>
                </div>
                <a href="#" id="drvCallBtn" class="btn-card btn-call" style="padding:6px 10px; font-size:12px;">📞 Gọi TX</a>
              </div>
            </div>
          </div>

          <!-- MỤC 2 CHO HÀNG XUẤT: BÃI / CẢNG & KẾT HỢP -->
          <div id="secXuatPort">
            <div class="form-section-title">
              <span>2. Bãi / Cảng & Kết Hợp</span>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label><span>🏗️ Nơi lấy cont / rỗng</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_noiLayContPo"
                  placeholder="VD: VS, KH 20', KH 40'..."
                  onchange="onFieldChanged('noiLayContPo')"
                />
              </div>

              <div class="form-group">
                <label><span>⚓ Cảng đi / Hạ cont</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_cangDiPo"
                  placeholder="VD: BẾN NGHÉ"
                  onchange="onFieldChanged('cangDiPo')"
                />
              </div>

              <div class="form-group">
                <label><span>🚢 Cảng đến (Phía Bắc)</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_tinhXuatHangPo"
                  list="dlCangDen"
                  placeholder="VD: CLO, HPG, DAN..."
                  onchange="onFieldChanged('tinhXuatHangPo')"
                />
              </div>

              <div class="form-group">
                <label><span>🔄 Hình thức kết hợp</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_hinhThucKHopPo"
                  list="dlKetHop"
                  placeholder="VD: 80%, Kết hợp, Hạ rỗng"
                  onchange="onFieldChanged('hinhThucKHopPo')"
                />
              </div>

              <div class="form-group">
                <label>
                  <span>⏱️ Thời gian hạ cont</span>
                  <button type="button" class="mini-btn" style="padding:1px 5px;font-size:10px;" onclick="setPortTimeNow()">Giờ hiện tại</button>
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
          </div>

          <!-- MỤC 2 CHO HÀNG NHẬP: THÔNG TIN TÀU - CẢNG (LẤY TỪ TMS - DỮ LIỆU CỨNG KHÓA CHỈ XEM) -->
          <div id="secNhapVessel" style="display:none;">
            <div class="form-section-title">
              <span>2. Thông Tin Tàu & Cảng (TMS)</span>
              <span style="font-size:11px; color:#64748b; font-weight:600;">🔒 Dữ liệu cố định</span>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label><span>🚢 Tên Tàu (TÀU)</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_tauPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>

              <div class="form-group">
                <label><span>🧭 Số Chuyến (CHUYẾN)</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_chuyenPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>

              <div class="form-group">
                <label><span>⚓ Cảng cập / POD</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="inp_cangDenPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>

              <div class="form-group">
                <label><span>📅 Ngày tàu chạy / cập</span></label>
                <input
                  type="date"
                  class="form-control"
                  id="inp_ngayTauChayPo"
                  readonly
                  disabled
                  style="background:#f1f5f9;color:#475569;cursor:not-allowed;"
                />
              </div>
            </div>
          </div>

          <div class="form-section-title">
            <span>3. Kho Hàng, Địa Chỉ & Liên Hệ</span>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label><span>🏭 Kho đóng / trả hàng</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_khoNhanHangPo"
                onchange="onFieldChanged('khoNhanHangPo')"
              />
            </div>

            <div class="form-group">
              <label><span>📍 Tỉnh / Thành phố</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_tinhNhanPo"
                list="dlTinh"
                onchange="onFieldChanged('tinhNhanPo')"
              />
            </div>

            <div class="form-group full">
              <label><span>🗺️ Địa chỉ chi tiết</span></label>
              <input
                type="text"
                class="form-control"
                id="inp_diaChiNhanPo"
                onchange="onFieldChanged('diaChiNhanPo')"
              />
            </div>

            <div class="form-group full">
              <label><span>📞 Người liên hệ & SĐT</span></label>
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
        <button type="button" class="btn-secondary-lg" id="btnModalDelivery" onclick="copyCurrentPoToZalo(2)" title="Copy THÔNG TIN GIAO HÀNG (kèm Tài xế & SĐT)">
          🚛 Giao Hàng
        </button>
        <button type="button" class="btn-secondary-lg" id="btnModalTb" onclick="copyCurrentPoToZalo(1)" title="Copy THÔNG BÁO HÀNG ĐẾN / THÔNG TIN XE ĐÓNG HÀNG">
          💬 Copy TB
        </button>
        <button type="button" class="btn-secondary-lg" id="btnModalPkl" style="display:none;background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="openPklFromEditModal()" title="Xuất / In Phiếu PKL Vào Cổng">
          📄 Phiếu PKL
        </button>
        <button type="button" class="btn-secondary-lg" style="background:#fff1f2;color:#dc2626;border-color:#fecaca;" onclick="if(currentEditingPo) openDeleteBookingModal(currentEditingPo.idPo)" title="Xóa booking thừa">
          🗑️ Xóa
        </button>
        <button type="button" class="btn-primary-lg" id="btnSaveAll" onclick="saveAllChanges(event)">
          💾 Lưu TMS
        </button>
      </div>
    </div>
  </div>

  <!-- PKL (PACKING LIST) PREVIEW & PDF EXPORT MODAL -->
  <div class="modal-backdrop" id="pklModal" onclick="if(event.target===this) closePklModal()">
    <div class="bottom-sheet" style="max-height:94vh;">
      <div class="sheet-header">
        <div>
          <div class="sheet-title" id="pklModalTitle">📄 Phiếu PKL Hạ Cảng (Packing List)</div>
          <div id="pklModalSub" style="font-size:12px;color:#475569;margin-top:2px;">Tự động trích xuất trực tiếp từ dữ liệu Hàng Xuất TMS</div>
        </div>
        <button class="sheet-close" onclick="closePklModal()">✕</button>
      </div>
      <div class="sheet-body" style="padding:12px 14px;">
        <div id="pklStatusBanner" style="font-size:12.5px;font-weight:600;color:#047857;background:#ecfdf5;border:1px solid #a7f3d0;padding:8px 12px;border-radius:9px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
          <span id="pklStatusText">✅ Đã tạo phiếu PKL trực tiếp từ TMS!</span>
          <button type="button" class="mini-btn" style="background:#fff;color:#0369a1;border:1px solid #bae6fd;" onclick="fetchPklFromGoogleSheet()">🔄 Lấy từ Sheet PKL cũ</button>
        </div>
        <div style="width:100%;overflow:auto;border:1px solid #cbd5e1;border-radius:10px;background:#f8fafc;box-shadow:inset 0 2px 6px rgba(0,0,0,0.05);">
          <canvas id="pklCanvas" style="width:100%;height:auto;display:block;background:#fff;"></canvas>
        </div>
      </div>
      <div class="sheet-footer">
        <button type="button" class="btn-secondary-lg" style="background:#f3e8ff;color:#6b21a8;border-color:#d8b4fe;" onclick="sharePklPdf()">
          📤 Gửi Zalo / Chia sẻ
        </button>
        <button type="button" class="btn-secondary-lg" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="downloadPklPdf()">
          📥 Tải PDF
        </button>
        <button type="button" class="btn-primary-lg" onclick="printPklCanvas()">
          🖨️ In Phiếu PKL
        </button>
      </div>
    </div>
  </div>

  <!-- MODAL XÁC NHẬN XÓA BOOKING THỪA -->
  <div class="modal-backdrop" id="deleteConfirmModal" style="align-items:center;justify-content:center;z-index:9999;" onclick="if(event.target===this) closeDeleteModal()">
    <div style="background:#ffffff;border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,0.35);max-width:420px;width:92%;padding:22px;text-align:center;">
      <div style="font-size:38px;margin-bottom:6px;">🗑️</div>
      <div style="font-size:16px;font-weight:800;color:#dc2626;margin-bottom:8px;">XÁC NHẬN XÓA BOOKING THỪA</div>
      <div id="deleteModalDesc" style="font-size:13px;color:#334155;line-height:1.55;margin-bottom:14px;">
        Bạn có chắc muốn xóa booking thừa này không?
      </div>
      <div style="font-size:12.5px;color:#64748b;margin-bottom:8px;">
        Nhập chữ <b style="color:#dc2626;background:#fee2e2;padding:1px 6px;border-radius:4px;font-family:monospace;">ok</b> để mở khóa nút xóa:
      </div>
      <input type="text" id="deleteConfirmInput" class="form-control" placeholder="Gõ: ok"
        autocomplete="off" autocapitalize="off" spellcheck="false"
        style="text-align:center;font-weight:800;font-size:16px;max-width:160px;margin:0 auto 16px;border:2px solid #cbd5e1;border-radius:8px;"
        oninput="checkDeleteOkInput(this)" onkeydown="if(event.key==='Enter') executeDeleteBooking()" />
      <div style="display:flex;gap:10px;justify-content:center;">
        <button type="button" class="btn-card" style="padding:8px 16px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:8px;font-weight:600;cursor:pointer;" onclick="closeDeleteModal()">Hủy bỏ</button>
        <button type="button" id="btnConfirmDelete" style="padding:8px 18px;background:#dc2626;color:#ffffff;border:none;border-radius:8px;font-weight:700;opacity:0.35;cursor:not-allowed;transition:all 0.15s ease;" disabled onclick="executeDeleteBooking()">Xác nhận xóa</button>
      </div>
    </div>
  </div>

  <!-- MODAL QUẢN LÝ & SOẠN THẢO TRỰC QUAN MẪU BIÊN BẢN (BB TEMPLATE MANAGER) -->
  <div class="modal-backdrop" id="bbTemplateModal" style="align-items:center;justify-content:center;z-index:9998;" onclick="if(event.target===this) closeBbTemplateManager()">
    <div class="bb-mgr-box">
      <div class="bb-mgr-header">
        <div>
          <div style="font-size:16px;font-weight:800;">⚙️ Quản Lý & Thiết Kế Mẫu Biên Bản Theo Kho Nhận (Visual Editor)</div>
          <div style="font-size:12px;opacity:0.92;margin-top:2px;">Tự động nhận diện Form khi in hàng loạt theo từ khóa KHO NHẬN • Hỗ trợ chỉnh sửa trực tiếp trên giấy A4 hoặc Copy-Paste từ Word</div>
        </div>
        <button type="button" class="sheet-close" style="background:rgba(255,255,255,0.2);color:#fff;" onclick="closeBbTemplateManager()">✕</button>
      </div>

      <div class="bb-mgr-body">
        <!-- Cột trái: Danh sách Mẫu BB -->
        <div class="bb-mgr-sidebar">
          <div style="padding:10px;border-bottom:1px solid #cbd5e1;display:flex;flex-direction:column;gap:6px;">
            <button type="button" class="btn-bulk-execute" style="width:100%;justify-content:center;background:linear-gradient(135deg,#1d4ed8,#2563eb);" onclick="triggerBbWordDocxUpload(true)">
              📄 Tạo Form Từ File Word (.docx)
            </button>
            <button type="button" class="btn-bulk-execute" style="width:100%;justify-content:center;background:linear-gradient(135deg,#0f766e,#0d9488);" onclick="createNewBbTemplate()">
              ➕ Tạo Form Kho Mới (Mẫu trống)
            </button>
            <button type="button" class="bb-tool-btn" style="width:100%;justify-content:center;padding:6px;" onclick="cloneCurrentBbTemplate()">
              📋 Nhân bản từ Form đang chọn
            </button>
            <input type="file" id="bbWordDocxInput" accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="display:none;" onchange="handleBbWordDocxSelected(this)" />
          </div>
          <div class="bb-mgr-list" id="bbTemplateSidebarList">
            <!-- Populated dynamically -->
          </div>
        </div>

        <!-- Cột phải: Cấu hình Từ khóa + Thanh công cụ Word + Trang giấy A4 -->
        <div class="bb-mgr-main">
          <!-- Hàng 1: Tên Form, Nhãn nút, Khách Hàng (TMS), Kho Nhận (TMS) -->
          <div class="bb-mgr-meta-bar">
            <div class="bb-meta-field" style="width:195px;">
              <label>Tên Mẫu Biên Bản</label>
              <input type="text" id="bbTplName" class="bb-meta-inp" placeholder="VD: Form Kho 4 Oranges" />
            </div>
            <div class="bb-meta-field" style="width:105px;">
              <label>Nhãn nút bảng</label>
              <input type="text" id="bbTplBadge" class="bb-meta-inp" placeholder="VD: 4Oranges" maxlength="16" />
            </div>
            <div class="bb-meta-field" style="flex:1;min-width:210px;">
              <label>Khách Hàng từ TMS (Gõ để tìm • Để trống = Mọi KH)</label>
              <div class="bb-ac-wrap">
                <input type="text" id="bbTplCustomerKeywords" class="bb-meta-inp" style="width:100%;" autocomplete="off"
                  placeholder="Gõ tìm tên Khách hàng (VD: Á Châu)..."
                  onfocus="openBbMetaAc('cust')"
                  oninput="openBbMetaAc('cust')" />
                <div class="bb-ac-dropdown" id="bbAcCustDropdown"></div>
              </div>
            </div>
            <div class="bb-meta-field" style="flex:1.15;min-width:230px;">
              <label>Từ khóa KHO NHẬN từ TMS (Gõ để tìm • Cách dấu phẩy)</label>
              <div class="bb-ac-wrap">
                <input type="text" id="bbTplKeywords" class="bb-meta-inp" style="width:100%;" autocomplete="off"
                  placeholder="Gõ tìm tên Kho nhận (VD: 4 Oranges)..."
                  onfocus="openBbMetaAc('kho')"
                  oninput="openBbMetaAc('kho')" />
                <div class="bb-ac-dropdown" id="bbAcKhoDropdown"></div>
              </div>
            </div>
          </div>

          <!-- Hàng 2: Thanh công cụ định dạng & Chèn Biến Tự Động -->
          <div class="bb-mgr-toolbar">
            <button type="button" class="bb-tool-btn" style="background:#eff6ff;color:#1d4ed8;border-color:#93c5fd;" onclick="triggerBbWordDocxUpload(false)" title="Tải file Word (.docx) đè vào mẫu đang mở">📄 Nhập từ Word (.docx)</button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('bold')" title="In đậm (Ctrl+B)"><b>B</b></button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('italic')" title="In nghiêng (Ctrl+I)"><i>I</i></button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('underline')" title="Gạch chân (Ctrl+U)"><u>U</u></button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('justifyLeft')" title="Căn trái">⬅️</button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('justifyCenter')" title="Căn giữa">↔️</button>
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); execBbEditorCmd('justifyRight')" title="Căn phải">➡️</button>
            <button type="button" class="bb-tool-btn" onclick="triggerBbLogoUpload()" title="Tải ảnh Logo từ máy tính chèn vào phiếu">🖼️ Chèn/Đổi Logo</button>
            <input type="file" id="bbLogoFileInput" accept="image/*" style="display:none;" onchange="handleBbLogoFileSelected(this)" />
            <button type="button" class="bb-tool-btn" onmousedown="event.preventDefault(); insertBbSampleTable()" title="Chèn khung bảng hàng hóa">📊 +Bảng</button>

            <span style="color:#94a3b8;margin:0 2px;">|</span>
            <span style="font-size:11px;font-weight:800;color:#475569;">CHÈN BIẾN TỰ ĐỘNG:</span>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{SO_CONT}}')" title="Tự thay bằng Số Container">📦 {{SO_CONT}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{SO_SEAL}}')" title="Tự thay bằng Số Seal">🔒 {{SO_SEAL}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{NGAY}}')" title="Ngày giao (DD)">📅 {{NGAY}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{THANG}}')" title="Tháng giao (MM)">📅 {{THANG}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{NAM}}')" title="Năm giao (YYYY)">📅 {{NAM}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{KHO_NHAN}}')" title="Tên Kho Nhận">🏭 {{KHO_NHAN}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{DIA_CHI}}')" title="Địa chỉ giao + Tỉnh">📍 {{DIA_CHI}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{SO_XE}}')" title="Biển số xe">🚛 {{SO_XE}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{TRONG_LUONG}}')" title="Trọng lượng (tấn)">⚖️ {{TRONG_LUONG}}</button>
            <button type="button" class="bb-var-chip" onmousedown="event.preventDefault(); insertBbVariable('{{HANG_HOA}}')" title="Tên hàng hóa">📋 {{HANG_HOA}}</button>
          </div>

          <!-- Hàng 3: Khung Trang Giấy A4 Trực Quan -->
          <div class="bb-mgr-canvas-wrap">
            <div id="bbVisualEditorCanvas" class="bb-a4-editor" contenteditable="true" spellcheck="false"></div>
          </div>

          <!-- Footer: Nút Xem Thử, In Thử, Khôi phục, Xóa, Lưu -->
          <div class="bb-mgr-footer">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <button type="button" class="bb-tool-btn" id="btnBbTogglePreview" onclick="toggleBbEditorLivePreview()" style="padding:7px 12px;background:#eff6ff;color:#1d4ed8;border-color:#93c5fd;">
                👁️ Xem thử với dữ liệu thật
              </button>
              <button type="button" class="bb-tool-btn" onclick="testPrintCurrentBbTemplate()" style="padding:7px 12px;">
                🖨️ In thử mẫu này
              </button>
              <button type="button" class="bb-tool-btn" id="btnBbRestoreDefault" onclick="restoreDefaultBbTemplate()" style="padding:7px 12px;color:#b45309;border-color:#fcd34d;background:#fffbeb;">
                ↺ Khôi phục mẫu gốc
              </button>
              <button type="button" class="bb-tool-btn" id="btnBbDeleteTpl" onclick="deleteCurrentBbTemplate()" style="padding:7px 12px;color:#dc2626;border-color:#fca5a5;background:#fef2f2;">
                🗑️ Xóa Form này
              </button>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <button type="button" class="btn-bulk-exit" onclick="closeBbTemplateManager()" style="padding:8px 16px;">Đóng</button>
              <button type="button" class="btn-bulk-execute" onclick="saveCurrentBbTemplate()" style="padding:8px 20px;background:linear-gradient(135deg,#0f766e,#0d9488);">
                💾 Lưu Mẫu Biên Bản
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ACCOUNT / SETTINGS MODAL -->
  <div class="modal-backdrop" id="accountModal" onclick="if(event.target===this) closeAccountModal()">
    <div class="bottom-sheet">
      <div class="sheet-header">
        <div class="sheet-title">🔐 Tài Khoản & Kết Nối Vietsun TMS</div>
        <button class="sheet-close" onclick="closeAccountModal()">✕</button>
      </div>
      <div class="sheet-body">
        <div class="form-group">
          <label>Tài khoản đăng nhập (Username)</label>
          <input type="text" class="form-control" id="cfgUsername" value="" placeholder="Nhập tài khoản điều độ" />
        </div>
        <div class="form-group">
          <label>Mật khẩu (Password)</label>
          <input type="password" class="form-control" id="cfgPassword" value="" placeholder="••••••••" />
        </div>
        <div class="form-group">
          <label>Chuỗi phiên làm việc (userString)</label>
          <input type="text" class="form-control" id="cfgUserString" value="" placeholder="Tự động đồng bộ từ máy chủ" style="font-size:11.5px; font-family:monospace;" />
        </div>
        <div class="form-group">
          <label>Ngày mốc lấy dữ liệu API (return_json.php?ngay=)</label>
          <input type="date" class="form-control" id="cfgApiDate" />
        </div>
        <div class="info-banner" style="margin-top:10px;background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <strong>🚛 Đồng bộ Đội xe TMS & Vendor (Google Sheet):</strong>
            <button type="button" class="mini-btn" style="background:#16a34a;color:#fff;border:none;" onclick="syncLiveDriverDatabase(true)">↻ Đồng bộ ngay</button>
          </div>
          <div id="driverSyncStatusText" style="margin-top:4px;font-size:12px;">
            Tự động cập nhật Đội xe VIETSUN từ TMS & Đội xe Vendor từ Google Sheet mỗi lần mở App.
          </div>
          <div style="margin-top:4px;font-size:11.5px;">
            📄 <a href="https://docs.google.com/spreadsheets/d/13ndQsSmBsKlHjIRQR0S0MO5BPqqL6J4CECBAYfQi1oo/edit?gid=1046738475#gid=1046738475" target="_blank" style="color:#15803d;font-weight:700;text-decoration:underline;">Mở bảng Google Sheet Đội xe Vendor</a>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
            <a href="/admin" target="_blank" style="background:#1e3a8a;color:#fff;padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:700;text-decoration:none;">⚙️ Quản lý User (/admin)</a>
            <a href="/vendor" target="_blank" style="background:#0284c7;color:#fff;padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:700;text-decoration:none;">🏢 Cổng Vendor (/vendor)</a>
            <a href="/driver" target="_blank" style="background:#16a34a;color:#fff;padding:5px 10px;border-radius:6px;font-size:11.5px;font-weight:700;text-decoration:none;">🚛 App Tài xế (/driver)</a>
          </div>
        </div>
        <div class="info-banner" style="margin-top:10px;">
          <strong>Hướng dẫn cài App trên Điện thoại:</strong><br/>
          • <strong>Android (Chrome):</strong> Bấm nút <strong>"📲 Cài App"</strong> trên thanh tiêu đề.<br/>
          • <strong>iPhone (Safari):</strong> Bấm nút <strong>Chia sẻ (⬆️)</strong> ➔ <em>"Thêm vào MH chính"</em>.
        </div>
      </div>
      <div class="sheet-footer">
        <button type="button" class="btn-secondary-lg" style="color:#dc2626;border-color:#fecaca;background:#fef2f2;" onclick="logoutAccount()">🚪 Đăng xuất / Đổi TK</button>
        <button type="button" class="btn-primary-lg" onclick="saveAccountSettings()">✅ Lưu & Đồng bộ</button>
      </div>
    </div>
  </div>

  <!-- AUTOCOMPLETE DATALISTS -->
  <datalist id="dlSoXe"></datalist>
  <datalist id="dlVendor"></datalist>
  <datalist id="dlTinh"></datalist>
  <datalist id="dlCangDen">
    <option value="CLO"></option>
    <option value="HPG"></option>
    <option value="DAN"></option>
  </datalist>
  <datalist id="dlKetHop">
    <option value="80%"></option>
    <option value="100%"></option>
    <option value="Kết hợp"></option>
    <option value="Hạ rỗng"></option>
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

    // Database Tài xế, SĐT, CCCD, GPLX Đội xe VIETSUN từ TMS
    const VIETSUN_DRIVER_DB = {
      "50H91309":{"cccd":"079097000590  Nơi cấp: Cục QLHC & TTXH","gplx":"790152274557 Nơi cấp:SỞ GTVT","name":"HUỲNH THANH TÙNG","phone":"0356601380"},
      "50H67772":{"cccd":"075082015319  Nơi cấp: Cục QLHC & TTXH","gplx":"740113002170 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN PHƯƠNG","phone":"0931249725"},
      "50H91554":{"cccd":"044082006183  Nơi cấp: Cục QLHC & TTXH","gplx":"790105007335 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN TUẤN","phone":"0378857982"},
      "50E52975":{"cccd":"038085016388  Nơi cấp: Cục QLHC & TTXH","gplx":"790152206084 Nơi cấp:SỞ GTVT","name":"NGUYỄN TRỌNG TRƯỜNG","phone":"0974533635"},
      "50H10121":{"cccd":"037089000509  Nơi cấp: Cục QLHC & TTXH","gplx":"790144799134 Nơi cấp:SỞ GTVT","name":"PHAN VĂN HUYNH","phone":"0987437351"},
      "50H76073":{"cccd":"075092005970  Nơi cấp: Cục QLHC & TTXH","gplx":"790219058674 Nơi cấp:BỘ CÔNG AN","name":"NGUYỄN VĂN THANH","phone":"0914210965"},
      "50H10152":{"cccd":"017084008865  Nơi cấp: Cục QLHC & TTXH","gplx":"310162850107 Nơi cấp:sở GTVT","name":"QUẢN VĂN NGỌC","phone":"0975075027"},
      "50H05689":{"cccd":"094086003131  Nơi cấp: Cục QLHC & TTXH","gplx":"740114002180 Nơi cấp:SỞ GTVT","name":"NGÔ MINH TRỌNG","phone":"0985955580"},
      "50H10035":{"cccd":"075089026187  Nơi cấp: Cục QLHC & TTXH","gplx":"750130023874 Nơi cấp:Sở GTVT","name":"TRẦN TRÍ TÀI","phone":"0919480367"},
      "50H05455":{"cccd":"035074010129  Nơi cấp: Cục QLHC & TTXH","gplx":"790146894437 Nơi cấp:SỞ GTVT","name":"NGUYỄN THÁI CƠ","phone":"0986192693"},
      "50H68309":{"cccd":"094085000613  Nơi cấp: Cục QLHC & TTXH","gplx":"790133745151 Nơi cấp:SỞ GTVT","name":"LÊ VĂN NGOAN","phone":"0795884070"},
      "50E14550":{"cccd":"080093000650  Nơi cấp: Cục QLHC & TTXH","gplx":"790159130760 Nơi cấp:SỞ GTVT","name":"LÊ HOÀNG ÂN","phone":"0966464917"},
      "50E14009":{"cccd":"087201009730  Nơi cấp: Cục QLHC & TTXH","gplx":"790206185040 Nơi cấp:bộ công an","name":"NGUYỄN MINH TRIẾT","phone":"0364334382"},
      "50E53597":{"cccd":"077089005508  Nơi cấp: Cục QLHC & TTXH","gplx":"790110015510 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN TÀI","phone":"0335946252"},
      "50E52920":{"cccd":"080093006093  Nơi cấp: Cục QLHC & TTXH","gplx":"790160094374 Nơi cấp:SỞ GTVT","name":"ĐỖ MINH PHỤNG","phone":"0967799542"},
      "50H10017":{"cccd":"080081000387  Nơi cấp: Cục QLHC & TTXH","gplx":"790156088701 Nơi cấp:BỘ GTVT","name":"NGUYỄN VƯƠNG QUỐC","phone":"0936736236"},
      "50H68333":{"cccd":"038085020371  Nơi cấp: Cục QLHC & TTXH","gplx":"790159210491 Nơi cấp:SỞ GTVT","name":"HOÀNG VĂN LỰC","phone":"0902856068"},
      "50H10011":{"cccd":"079201021731  Nơi cấp: Cục QLHC & TTXH","gplx":"790204030139 Nơi cấp:SỞ GTVT","name":"PHAN HOÀNG HÙNG","phone":"0707231297"},
      "50E15217":{"cccd":"080074000416  Nơi cấp: Cục QLHC & TTXH","gplx":"790966004092 Nơi cấp:SỞ GTVT","name":"HUỲNH THANH TÂM","phone":"0937373731"},
      "50H76816":{"cccd":"079078002279  Nơi cấp: Cục QLHC & TTXH","gplx":"790152151977 Nơi cấp:SỞ GTVT","name":"PHAN VĂN TÚ","phone":"0904334484"},
      "50H90600":{"cccd":"079074022039  Nơi cấp: Cục QLHC & TTXH","gplx":"790009228687 Nơi cấp:SỞ GTVT","name":"DƯƠNG HIẾU HÒA","phone":"0796449644"},
      "51D03966":{"cccd":"075082009103  Nơi cấp: Cục QLHC & TTXH","gplx":"750053003841 Nơi cấp:Cục Cảnh sát giao thông","name":"TRANG MINH HẬU","phone":"0979577801"},
      "50H68200":{"cccd":"042080010369  Nơi cấp: Cục QLHC & TTXH","gplx":"790106009000 Nơi cấp:SỞ GTVT","name":"NGUYỄN VIẾT XUÂN","phone":"0979483434"},
      "50H90144":{"cccd":"075091024017  Nơi cấp: Cục QLHC & TTXH","gplx":"790171923030 Nơi cấp:BCA","name":"PHẠM VĂN BÌNH","phone":"0909092471"},
      "50H10208":{"cccd":"030062009674  Nơi cấp: Cục QLHC & TTXH","gplx":"750100001394 Nơi cấp:SỞ GTVT","name":"NGUYỄN ĐỨC TRIỆU","phone":"0938025734"},
      "50H91424":{"cccd":"079077005704  Nơi cấp: Cục QLHC & TTXH","gplx":"790135410947 Nơi cấp:sở GTVT","name":"ĐỖ THÀNH HƯNG","phone":"0916597944"},
      "50E48301":{"cccd":"084083014543  Nơi cấp: Cục QLHC & TTXH","gplx":"790101006837 Nơi cấp:SỞ GTVT","name":"NGUYỄN HỮU TƯỜNG","phone":"0933943550"},
      "50H05535":{"cccd":"054088010647  Nơi cấp: Cục QLHC & TTXH","gplx":"540111812711 Nơi cấp:SỞ GTVT","name":"LÂM TỬ TY","phone":"0961191339"},
      "50E14112":{"cccd":"074078009227  Nơi cấp: Cục QLHC & TTXH","gplx":"740044000054 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN XUÂN","phone":"0964337309"},
      "50H10224":{"cccd":"093098001120  Nơi cấp: Cục QLHC & TTXH","gplx":"790163115562 Nơi cấp:BCA","name":"NGUYỄN VĂN HẬU","phone":"0938234301"},
      "50H76406":{"cccd":"038082023809  Nơi cấp: Cục QLHC & TTXH","gplx":"740086003914 Nơi cấp:sở GTVT","name":"ĐỖ VĂN TUẤN","phone":"0906534154"},
      "50E54493":{"cccd":"075090016892  Nơi cấp: Cục QLHC & TTXH","gplx":"790137778023 Nơi cấp:SỞ GTVT","name":"NGUYỄN TUẤN ANH","phone":"0979222556"},
      "50H91213":{"cccd":"091085002627  Nơi cấp: Cục QLHC & TTXH","gplx":"790102029798 Nơi cấp:SỞ GTVT","name":"HUỲNH VĂN SỬ","phone":"0908727855"},
      "50H91917":{"cccd":"040086020171  Nơi cấp: Cục QLHC & TTXH","gplx":"750087003158 Nơi cấp:SỞ GTVT","name":"TRẦN DUY BÌNH","phone":"0973607075"},
      "50E54092":{"cccd":"046083015082  Nơi cấp: Cục QLHC & TTXH","gplx":"790107001117 Nơi cấp:SỞ GTVT","name":"PHAN THẾ MINH SƠN","phone":"0889243552"},
      "50E52617":{"cccd":"084096005671  Nơi cấp: Cục QLHC & TTXH","gplx":"840164002355 Nơi cấp:SỞ GTVT","name":"HUỲNH VĂN TẤN ĐẠT","phone":"0974891088"},
      "50H05658":{"cccd":"042090011523  Nơi cấp: Cục QLHC & TTXH","gplx":"310176002813 Nơi cấp:SỞ GTVT","name":"HOÀNG HỮU LONG","phone":"0971772771"},
      "50E14029":{"cccd":"038081001711  Nơi cấp: Cục QLHC & TTXH","gplx":"790168165517 Nơi cấp:sở GTVT","name":"HÀ VĂN LUẬT","phone":"0961535776"},
      "50H05808":{"cccd":"040081035050  Nơi cấp: Cục QLHC & TTXH","gplx":"420098000382 Nơi cấp:SỞ GTVT","name":"NGUYỄN THÀNH TRUNG","phone":"0973029550"},
      "50H76226":{"cccd":"079089006263  Nơi cấp: Cục QLHC & TTXH","gplx":"790115230930 Nơi cấp:SỞ GTVT","name":"ĐOÀN TÍN THÀNH","phone":"0903379977"},
      "50H76364":{"cccd":"094085015886  Nơi cấp: cuc QL HC & TTXH","gplx":"310150041834 Nơi cấp:Sở GTVT","name":"LÊ MINH HIỂN","phone":"0902484393"},
      "50E54447":{"cccd":"052088013362  Nơi cấp: Cục QLHC & TTXH","gplx":"790148929197 Nơi cấp:SỞ GTVT","name":"LÊ TRỌNG NHŨ","phone":"0985468603"},
      "50H76548":{"cccd":"084093004347  Nơi cấp: Cục QLHC & TTXH","gplx":"790185182027 Nơi cấp:SỞ GTVT","name":"HUỲNH VĂN CHÍ NGUYỆN","phone":"0327348324"},
      "50H67574":{"cccd":"087084012933  Nơi cấp: Cục QLHC & TTXH","gplx":"790135004317 Nơi cấp:BCA","name":"HUỲNH VĂN TÂM","phone":"0934044395"},
      "50E13972":{"cccd":"084083011298  Nơi cấp: Cục QLHC & TTXH","gplx":"790154124087 Nơi cấp:SỞ GTVT","name":"TRƯƠNG HOÀNG TÂM","phone":"0933436783"},
      "50H76100":{"cccd":"038087010115  Nơi cấp: Cục QLHC & TTXH","gplx":"790111019706 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN TUẤN","phone":"0932636166"},
      "50H75037":{"cccd":"038090000713  Nơi cấp: Cục QLHC & TTXH","gplx":"790159083060 Nơi cấp:sở GTVT","name":"HÀ VĂN HUỲNH","phone":"0962595828"},
      "50H91136":{"cccd":"075084012309  Nơi cấp: Cục QLHC & TTXH","gplx":"740103000597 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN LÂM","phone":"0907451526"},
      "50H91077":{"cccd":"084078005598  Nơi cấp: Cục QLHC & TTXH","gplx":"790133026546 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN VINH","phone":"0939534848"},
      "50H75631":{"cccd":"040094035935  Nơi cấp: Cục QLHC & TTXH","gplx":"790158168344 Nơi cấp:SỞ GTVT","name":"PHẠM VĂN HOÀI","phone":"0987130446"},
      "50H76822":{"cccd":"092068002539  Nơi cấp: Cục QLHC & TTXH","gplx":"740100001386 Nơi cấp:SỞ GTVT","name":"ĐOÀN VĂN KHÔI","phone":"07074042920"},
      "50H91025":{"cccd":"096085012314  Nơi cấp: Cục QLHC & TTXH","gplx":"960104002204 Nơi cấp:SỞ GTVT","name":"LÊ CHÍ BÉ","phone":"0949325326"},
      "50H91065":{"cccd":"084087008097  Nơi cấp: Cục QLHC & TTXH","gplx":"790120002234 Nơi cấp:sở GTVT","name":"LÊ VĂN LỌ","phone":"0931781902"},
      "50H90495":{"cccd":"038087003353  Nơi cấp: Cục QLHC & TTXH","gplx":"790135394702 Nơi cấp:sở GTVT","name":"HÀ VĂN NAM","phone":"0962156417"},
      "50H76011":{"cccd":"052086008837  Nơi cấp: Cục QLHC & TTXH","gplx":"520098005953 Nơi cấp:sở GTVT","name":"BÙI QUỐC BẢO","phone":"0984771750"},
      "50E53813":{"cccd":"070090005753  Nơi cấp: Cục QLHC & TTXH","gplx":"700140007614 Nơi cấp:SỞ GTVT","name":"BÙI THẾ PHONG","phone":"0919412472"},
      "50H75942":{"cccd":"038079021380  Nơi cấp: Cục QLHC & TTXH","gplx":"790156178864 Nơi cấp:sở GTVT","name":"HÀ VĂN QUÂN","phone":"0968858255"},
      "50H10039":{"cccd":"046086012096  Nơi cấp: Cục QLHC & TTXH","gplx":"460070000841 Nơi cấp:BCA","name":"TRẦN QUỐC THANH","phone":"0344275975"},
      "50H67809":{"cccd":"019064000138  Nơi cấp: Cục QLHC & TTXH","gplx":"740109000223 Nơi cấp:SỞ GTVT","name":"ĐẶNG VĂN GIANG","phone":"0986779164"},
      "50H10106":{"cccd":"051093007367  Nơi cấp: Cục QLHC & TTXH","gplx":"790144963131 Nơi cấp:SỞ GTVT","name":"ĐOÀN TRUNG ĐIỆP","phone":"0966469601"},
      "50H75270":{"cccd":"051086015636  Nơi cấp: Cục QLHC & TTXH","gplx":"790116256113 Nơi cấp:sở GTVT","name":"TRẦN VĂN CHIẾN","phone":"0908491943"},
      "50H05806":{"cccd":"042072019464  Nơi cấp: Cục QLHC & TTXH","gplx":"740138003479 Nơi cấp:sở GTVT","name":"NGUYỄN VĂN HÀ","phone":"0976404108"},
      "50H10056":{"cccd":"080084015762  Nơi cấp: Cục QLHC & TTXH","gplx":"800072000197 Nơi cấp:BCA","name":"LÊ BẢO QUỐC","phone":"0946070660"},
      "50H05545":{"cccd":"040086034549  Nơi cấp: Cục QLHC & TTXH","gplx":"400138010216 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN QUANG","phone":"0919960266"},
      "50E52957":{"cccd":"084080010693  Nơi cấp: Cục QLHC & TTXH","gplx":"790118015806 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN CHIẾN","phone":"0933893216"},
      "50H75167":{"cccd":"084088003267  Nơi cấp: Cục QLHC & TTXH","gplx":"790121231718 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN KHOA","phone":"0937773854"},
      "50H76863":{"cccd":"093094003991  Nơi cấp: Cục QLHC & TTXH","gplx":"930172002271 Nơi cấp:SỞ GTVT","name":"PHẠM TUẤN KHANH","phone":"0832385658"},
      "50H90045":{"cccd":"079072041145  Nơi cấp: Cục QLHC & TTXH","gplx":"790109010227 Nơi cấp:SỞ GTVT","name":"HỒ NGỌC THANH","phone":"0908067972"},
      "50E53620":{"cccd":"086094004600  Nơi cấp: Cục QLHC & TTXH","gplx":"790140976232 Nơi cấp:SỞ GTVT","name":"TRƯƠNG NGỌC ĐÔNG","phone":"0707200832"},
      "50H76757":{"cccd":"096079008895  Nơi cấp: Cục QLHC & TTXH","gplx":"790101005351 Nơi cấp:SỞ GTVT","name":"NGUYỄN VĂN GIÀU","phone":"0943998978"},
      "50H91113":{"cccd":"093091008061  Nơi cấp: Cục QLHC & TTXH","gplx":"790161177742 Nơi cấp:sở GTVT","name":"NGUYỄN KIM TƯ","phone":"0933989655"},
      "50H90944":{"cccd":"044073008800  Nơi cấp: Cục QLHC & TTXH","gplx":"740107007134 Nơi cấp:SỞ GTVT","name":"ĐỖ HỒNG TUYẾN","phone":"0918569137"},
      "50H91272":{"cccd":"038078006397  Nơi cấp: Cục QLHC & TTXH","gplx":"790106000982 Nơi cấp:sở GTVT","name":"VŨ QUANG TUÂN","phone":"0937823360"},
      "50H10031":{"cccd":"087078013673  Nơi cấp: Cục QLHC & TTXH","gplx":"790064018256 Nơi cấp:Sở GTVT","name":"NGUYỄN VĨNH PHƯỚC","phone":"0939454501"},
      "50H68711":{"cccd":"","gplx":"","name":"NGUYỄN VƯƠNG QUỐC","phone":"0936736236"}
    };

    // Database Đội xe VENDOR từ Google Sheet (Tự động đồng bộ live mỗi khi mở App)
    const INITIAL_VENDOR_DRIVER_DB = {"50E65375":{"vendor":"AN THANH PHAT","name":"LƯƠNG THẾ DUY","phone":"0888352428","cccd":"040093028954","gplx":"400131030660"},"50E65951":{"vendor":"AN THANH PHAT","name":"HUỲNH VĂN HOÀI","phone":"0356615616","cccd":"058085005988","gplx":"740116000256"},"50F09323":{"vendor":"BAO NAM PHAT","name":"CAO THANH HÀ","phone":"0918959143","cccd":"044090013803","gplx":"440128001907"},"61H13200":{"vendor":"DUY LINH PHAT","name":"PHẠM NGỌC SÁNG","phone":"0865150567","cccd":"042094002527","gplx":"740149000715"},"50E34934":{"vendor":"DUY LINH PHAT","name":"PHẠM TRUNG THÀNH","phone":"0962678328","cccd":"042091005641","gplx":"Đang Cập Nhật"},"50E36061":{"vendor":"DUY LINH PHAT","name":"TRẦN HOÀI NAM","phone":"0979345291","cccd":"042086007623","gplx":"Đang Cập Nhật"},"50E65255":{"vendor":"HOANG LONG PHAT","name":"TRƯƠNG VĂN VƯƠNG","phone":"0377111572","cccd":"040095005341","gplx":"310187036636"},"50E65490":{"vendor":"HOANG LONG PHAT","name":"VÕ QUANG TRÀ","phone":"0962199166","cccd":"44090000239","gplx":"740155014797"},"50E32542":{"vendor":"HOANG LONG PHAT","name":"HOÀNG VĂN HẠNH","phone":"0377526846","cccd":"40084026157","gplx":"790140907355"},"50H90438":{"vendor":"HOANG LONG PHAT","name":"TRẦN KIM HOÀNG","phone":"0962655237","cccd":"40091018132","gplx":"750123004257"},"50E47317":{"vendor":"HOANG LONG PHAT","name":"NGUYỄN XUÂN THÀNH","phone":"0973101711","cccd":"40083012978","gplx":"310160865442"},"61H16670":{"vendor":"HOANG LONG PHAT","name":"HOÀNG VĂN HIẾU","phone":"0988339961","cccd":"40082015118","gplx":"Đang Cập Nhật"},"61H17260":{"vendor":"HOANG LONG PHAT","name":"HOÀNG TÙNG HIÊN","phone":"0908520359","cccd":"40081035719","gplx":"790148939862"},"50E39861":{"vendor":"HOANG LONG PHAT","name":"BÙI VĂN VỸ","phone":"0869308684","cccd":"40084024922","gplx":"400149029045"},"50E48367":{"vendor":"HOANG LONG PHAT","name":"LÊ ĐĂNG HIẾU","phone":"0988753861","cccd":"40090027025","gplx":"740151016368"},"51C12474":{"vendor":"LONG DAT","name":"PHẠM TUẤN PHONG","phone":"0901473423","cccd":"075078015607","gplx":"Đang Cập Nhật"},"50E81550":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN TRỌNG TUẤN","phone":"0988062493","cccd":"042084009898","gplx":"740163007170"},"61H11280":{"vendor":"NHAT SON","name":"NÔNG THÀNH NHƠN","phone":"0979193442","cccd":"019078012146","gplx":"740102000956"},"51C50602":{"vendor":"NHAT SON","name":"NÔNG MẠNH HÙNG","phone":"0938465862","cccd":"019091010032","gplx":"790131013629"},"50E31341":{"vendor":"NHAT SON","name":"LÊ ĐỨC KHÁNH","phone":"0976713778","cccd":"042091006497","gplx":"790140962813"},"61H00804":{"vendor":"NHAT SON","name":"NGUYỄN MINH HẢI","phone":"0971416122","cccd":"075086010901","gplx":"660118809593"},"50H76338":{"vendor":"NHAT SON","name":"NGUYỄN VĂN TÁM","phone":"0989411427","cccd":"038086031466","gplx":"790100004928"},"50H08207":{"vendor":"TAN HOANG QUANG","name":"VŨ ĐÌNH QUANG","phone":"0941119990","cccd":"042074000729","gplx":"640152780236"},"50E44057":{"vendor":"AN THANH PHAT","name":"VŨ ĐÌNH ĐỨC","phone":"0967594486","cccd":"079070032340","gplx":"790126011573"},"50E64009":{"vendor":"NGOC SANG","name":"HOÀNG LIÊN SƠN","phone":"0976044156","cccd":"40083031314","gplx":"709103005326"},"61H02908":{"vendor":"NGOC SANG","name":"HOÀNG LIÊN SƠN","phone":"0976044156","cccd":"40083031314","gplx":"709103005326"},"61H18983":{"vendor":"NGOC SANG","name":"BIỆN VĂN SỸ","phone":"0984225866","cccd":"42089019684","gplx":"420117000487"},"61H18956":{"vendor":"NGOC SANG","name":"NGUYỄN TIẾN NAM","phone":"0967676191","cccd":"66088007336","gplx":"660098801837"},"50E46727":{"vendor":"NGOC SANG","name":"TRẦN TRUNG ANH","phone":"0365766107","cccd":"40095005664","gplx":"790186088997"},"51C92758":{"vendor":"TRANCO","name":"PHẠM XUÂN THÀNH","phone":"0919920606","cccd":"34078006055","gplx":"790108009813"},"50E64511":{"vendor":"TRANCO","name":"PHAN THANH HẢI","phone":"0936713738","cccd":"094091002766","gplx":"790151084638"},"51D49674":{"vendor":"TRANCO","name":"CA CHÍ LINH","phone":"0945111033","cccd":"95085001585","gplx":"740116010976"},"50H44818":{"vendor":"TRANCO","name":"CA CHÍ LUÂN","phone":"0938525450","cccd":"95090005271","gplx":"790155186369"},"50H91518":{"vendor":"TRANCO","name":"PHẠM VĂN HIỂN","phone":"0932035841","cccd":"082088018626","gplx":"790176107446"},"51D14461":{"vendor":"TRANCO","name":"VÕ VĂN CỌP","phone":"0984932852","cccd":"094093008021","gplx":"940122000105"},"51D44303":{"vendor":"TRANCO","name":"NGUYỄN TẤN THÀNH","phone":"0355977727","cccd":"92093005581","gplx":"790140987564"},"50E76112":{"vendor":"PHUC AN","name":"MAI THANH TUẤN","phone":"0764153826","cccd":"51084007153","gplx":"790122230314"},"51C24203":{"vendor":"PHUC AN","name":"PHẠM VĂN THẮNG","phone":"0332609828","cccd":"37073011281","gplx":"790063005376"},"50H63178":{"vendor":"PHUC AN","name":"VỎ NGỌC CHÁNH","phone":"0909589764","cccd":"51085007848","gplx":"079117234884"},"61E03747":{"vendor":"NHAT SON","name":"NGUYỄN VĂN HƯNG","phone":"0972284449","cccd":"42081007010","gplx":"740108001690"},"61H20933":{"vendor":"NHAT SON","name":"CAO VĂN HIẾU","phone":"0703368267","cccd":"44093012924","gplx":"440149000565"},"61H12006":{"vendor":"NHAT SON","name":"NGUYỄN ĐÌNH LƯU","phone":"0962938537","cccd":"40093012668","gplx":"790136757755"},"61C41926":{"vendor":"NHAT SON","name":"CAO ĐỨC SƠN","phone":"0394423593","cccd":"44093004938","gplx":"440116002897"},"61C41405":{"vendor":"NHAT SON","name":"LƯƠNG VĂN LONG","phone":"0347068826","cccd":"038090043631","gplx":"790197296879"},"61H10617":{"vendor":"NHAT SON","name":"TRẦN BÁ NAM","phone":"0963871670","cccd":"4409601067","gplx":"790223014918"},"61H18984":{"vendor":"NHAT SON","name":"NGUYỄN VĂN ĐẠO","phone":"0869607757","cccd":"44096002563","gplx":"790174083630"},"50H71376":{"vendor":"TUAN HUNG","name":"PHAN HÒA VINH","phone":"0972609079","cccd":"66093011471","gplx":"660164016143"},"51C70823":{"vendor":"TUAN HUNG","name":"LÊ TRỌNG THẮNG","phone":"0373455800","cccd":"4008702238","gplx":"790111227327"},"50E60924":{"vendor":"VUONG PHAT","name":"TRẦN HỮU TINH","phone":"0339676007","cccd":"42075015762","gplx":"660101001809"},"50E64826":{"vendor":"VUONG PHAT","name":"TRẦN HẢI NAM","phone":"0862768559","cccd":"044098006799","gplx":"790213015459"},"50H67539":{"vendor":"VUONG PHAT","name":"CAO NGỌC LÊ","phone":"0366189811","cccd":"0440930310422","gplx":"750228035374"},"50E67400":{"vendor":"VUONG PHAT","name":"PHẠM VĂN KÝ","phone":"0975334334","cccd":"042088007658","gplx":"740156006283"},"61C15993":{"vendor":"PHUC TRUONG AN","name":"LÊ NGỌC NGÀ","phone":"0964298181","cccd":"66087103543","gplx":"640093006558"},"50E31803":{"vendor":"PHUC TRUONG AN","name":"NGUYỄN NGỌC CÔNG THÀNH","phone":"0971787679","cccd":"040084015706","gplx":"790178119715"},"61H00797":{"vendor":"PHUC TRUONG AN","name":"NGUYỄN VĂN TÙNG","phone":"0975200338","cccd":"042093019869","gplx":"420145004091"},"61E03709":{"vendor":"PHUC TRUONG AN","name":"ĐẶNG SỸ HẢI","phone":"0899335116","cccd":"040081024057","gplx":"740149013045"},"61H11121":{"vendor":"PHUC TRUONG AN","name":"PHẠM VĂN HUY","phone":"0784868678","cccd":"042086005529","gplx":"740103017148"},"50E88503":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN HUY","phone":"0975664423","cccd":"042083002403","gplx":"790117003088"},"50E90884":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN QUÝ","phone":"0968368665","cccd":"042075001054","gplx":"790104003725"},"38H03141":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN HẢI","phone":"0963236546","cccd":"042080012499","gplx":"420107000370"},"50E80187":{"vendor":"NHAN BAO PHAT","name":"NGUYỄN VĂN THUẬN","phone":"0929919298","cccd":"070096003624","gplx":"680199000717"},"37H00745":{"vendor":"ANH DUC","name":"PHẠM VĂN TÀI","phone":"0367308298","cccd":"40091027080","gplx":"400121016449"},"50H46207":{"vendor":"GIA KHANG","name":"NGÔ NGỌC TRUNG","phone":"0387618457","cccd":"77094004769","gplx":"790164019380"},"50F09314":{"vendor":"THIEN PHUC PHAT","name":"NGUYỄN TRỌNG PHÚC","phone":"0901464743","cccd":"42085015705","gplx":"790108009499"},"50F07996":{"vendor":"THIEN PHUC PHAT","name":"NGUYỄN NGỌC HÀ","phone":"0333470682","cccd":"79078001175","gplx":"790026241939"},"50F07610":{"vendor":"THIEN PHUC PHAT","name":"HUỲNH THANH TÚ","phone":"0333376115","cccd":"52085019130","gplx":"790159221192"},"61H16720":{"vendor":"NGOC SANG","name":"VÕ HOÀNG VĂN","phone":"0971891362","cccd":"009409317243","gplx":"790171925001"},"50H47386":{"vendor":"GIA KHANG","name":"NGUYỄN VĂN LÂU","phone":"0938327078","cccd":"077092006919","gplx":"790144904325"},"50H70989":{"vendor":"GIA KHANG","name":"PHẠM PHÚ KIÊN","phone":"0932636387","cccd":"082097002570","gplx":"820154004437"},"50H47265":{"vendor":"GIA KHANG","name":"VĂN THANH HÙNG","phone":"0901455673","cccd":"060072000200","gplx":"740125002117"},"50H46817":{"vendor":"GIA KHANG","name":"TRẦN NGỌC TỨ","phone":"0782482587","cccd":"070090004773","gplx":"790120256107"},"50E77282":{"vendor":"GIA KHANG","name":"NGUYỄN CHÍ KIÊN","phone":"0788937807","cccd":"086086013500","gplx":"74013600710"},"50E32110":{"vendor":"TRANCO","name":"LÊ QUÝ SƠN","phone":"0989322411","cccd":"079083008027","gplx":"790105016353"},"50E89021":{"vendor":"HOANG LONG PHAT","name":"NGUYỄN QUỐC VIỆT","phone":"0975322746","cccd":"044094000961","gplx":"440169000527"},"50E47942":{"vendor":"MAI NHAT ANH","name":"ĐOÀN QUỐC XIÊM","phone":"0764850270","cccd":"","gplx":""},"50E46720":{"vendor":"MAI NHAT ANH","name":"NGUYỄN THÀNH ĐƯỢC","phone":"0947395620","cccd":"","gplx":""},"50E48450":{"vendor":"MAI NHAT ANH","name":"NGUYỄN CHÍ HƯỚNG","phone":"0777508000","cccd":"","gplx":""},"50E48074":{"vendor":"MAI NHAT ANH","name":"DƯƠNG ĐẠI HÀO","phone":"0968065464","cccd":"","gplx":""},"50H25079":{"vendor":"DUONG NGUYEN","name":"KIỀU GIA NGỌC","phone":"0876450913","cccd":"058089001180","gplx":"790137788347"},"50H46638":{"vendor":"GIA KHANG","name":"PHẠM THÀNH NHÂN","phone":"0939143102","cccd":"082096001875","gplx":"790155722714"},"50H16154":{"vendor":"GIA KHANG","name":"HUỲNH VĂN DUẨN","phone":"0777805689","cccd":"091085013755","gplx":"790106003330"},"50H24509":{"vendor":"DUONG NGUYEN","name":"NGUYỄN TRUNG BÌNH","phone":"0903631292","cccd":"079070003223","gplx":"790143789279"},"50H24419":{"vendor":"DUONG NGUYEN","name":"PHẠM VĂN THẢO","phone":"0962852047","cccd":"066091005627","gplx":"790126243611"},"61H07722":{"vendor":"NHAT SON","name":"VI THÁI BÌNH","phone":"0974411698","cccd":"038095014861","gplx":"790220087375"},"50E14850":{"vendor":"DUONG NGUYEN","name":"TRẦN QUANG BÉ","phone":"0986222784","cccd":"052068023764","gplx":"640132001962"},"50H91801":{"vendor":"DUONG NGUYEN","name":"NGUYỄN THÀNH HẢI","phone":"0902897928","cccd":"080085009060","gplx":"790176015376"},"50H08622":{"vendor":"DUONG NGUYEN","name":"DƯƠNG THANH TĨNH","phone":"0971213742","cccd":"054092009544","gplx":"790173082072"},"50H54182":{"vendor":"TRUC ANH","name":"LÊ VĂN DŨNG","phone":"0989667444","cccd":"044084012790","gplx":""},"50E14570":{"vendor":"DUONG NGUYEN","name":"KIỀU GIA NGÀ","phone":"0935584402","cccd":"058092005948","gplx":"790148026117"},"51D31338":{"vendor":"TRANCO","name":"NGÔ ANH KIỆT","phone":"0913683141","cccd":"079076014400","gplx":"790114015514"},"50E15514":{"vendor":"DUONG NGUYEN","name":"HÀ MINH HẬU","phone":"0388580088","cccd":"054085007992","gplx":""},"50H24580":{"vendor":"DUONG NGUYEN","name":"LÊ VĂN GIÀU","phone":"0933363774","cccd":"092087004728","gplx":"790117233047"},"51D35878":{"vendor":"DUONG NGUYEN","name":"HUỲNH NGỌC TOÀN","phone":"0394797939","cccd":"060098008301","gplx":"790196274755"},"50E14613":{"vendor":"DUONG NGUYEN","name":"DƯƠNG ĐÌNH KHOA","phone":"0327155050","cccd":"183664365","gplx":"790163163208"},"50H44778":{"vendor":"DUONG NGUYEN","name":"TRẦN NGỌC NGHĨA","phone":"0971463239","cccd":"052091008859","gplx":"520135002804"},"51C40249":{"vendor":"HIEU THUAN THANH","name":"TRẦN VĂN THÁI","phone":"0767599616","cccd":"040092039083","gplx":"790151215740"},"50H08397":{"vendor":"HIEU THUAN THANH","name":"VÕ PHÚ TIẾN","phone":"0347344739","cccd":"094091007316","gplx":"740160001740"},"50H75283":{"vendor":"TRANCO","name":"VÕ VĂN HIẾU","phone":"0909409079","cccd":"094085003898","gplx":"790172020367"},"50H68351":{"vendor":"TRANCO","name":"NGUYỄN NGỌC THANH","phone":"0764754733","cccd":"079074028029","gplx":"790094010189"},"50H75912":{"vendor":"TRANCO","name":"NGUYỄN VĂN HIẾU","phone":"0947906767","cccd":"087084013978","gplx":"830151003470"},"50H47324":{"vendor":"GIA KHANG","name":"CHÂU CHÍ THÀNH","phone":"0968018724","cccd":"084085005489","gplx":"790116022428"},"50H31241":{"vendor":"GIA KHANG","name":"TRẦN TUẤN THÀNH","phone":"0966921015","cccd":"082096007987","gplx":"790143967064"},"51C96068":{"vendor":"TRANCO","name":"NGUYỄN KHẮC DUY","phone":"0822678132","cccd":"087084017883","gplx":"790132399830"},"51C98819":{"vendor":"GIA BAO","name":"NGUYỄN MINH SĨ","phone":"0967952544","cccd":"064093007650","gplx":""},"50E40521":{"vendor":"TRANCO","name":"PHAN VĂN HẬU","phone":"0934038315","cccd":"082092009808","gplx":"790142806728"},"50H70423":{"vendor":"DUC HAI","name":"NGUYỄN THANH TỈNH","phone":"0926381168","cccd":"042085000345","gplx":"7901060033790"},"67H04431":{"vendor":"DANG HUNG","name":"ĐẶNG NGỌC NHU","phone":"0973439438","cccd":"","gplx":"740123005536"},"50E14923":{"vendor":"DUONG NGUYEN","name":"TRẦN ĐÌNH BẢO","phone":"0909736747","cccd":"079092025653","gplx":"790159190467"},"51C48540":{"vendor":"GIA KHANG","name":"LÊ BÉ LONG","phone":"0706336749","cccd":"092076003333","gplx":"790159088759"},"50H03656":{"vendor":"DUC HAI","name":"NGUYỄN VĂN KHIÊM","phone":"0908820932","cccd":"025075013027","gplx":"740117000239"},"50H75046":{"vendor":"TRANCO","name":"VÕ VĂN LÂM","phone":"0937905891","cccd":"086086013218","gplx":"790121015060"},"50F09325":{"vendor":"THIEN PHUC PHAT","name":"LÊ KHẮC HOẠT","phone":"0765876315","cccd":"038084012067","gplx":"790077010940"},"50H78733":{"vendor":"GIA BAO","name":"PHAN THANH THẢO","phone":"0966340732","cccd":"080093016662","gplx":""},"51D64911":{"vendor":"DUONG NGUYEN","name":"HUỲNH VĂN TUẤN","phone":"0336786549","cccd":"054099009789","gplx":"640225014484"},"50H78894":{"vendor":"GIA BAO","name":"NGUYỄN HOÀNG ANH","phone":"0938723620","cccd":"080091009105","gplx":""},"51C91569":{"vendor":"DUONG NGUYEN","name":"PHẠM LONG BÌNH","phone":"0932041042","cccd":"079086017688","gplx":"790114027629"},"51C13223":{"vendor":"DUONG NGUYEN","name":"HỒ VĂN SẼ","phone":"0908330553","cccd":"083088015808","gplx":"790067231856"},"50H43304":{"vendor":"DUONG NGUYEN","name":"NGUYỄN THÀNH TẤN","phone":"0363615064","cccd":"054091005727","gplx":"540140001708"}};

    // Unified live Driver Database (VIETSUN + VENDOR)
    const DRIVER_DB = {};
    const DRIVER_DOCS_BY_NAME = {};

    function initBaseDriverDb() {
      for (const [plate, info] of Object.entries(VIETSUN_DRIVER_DB)) {
        DRIVER_DB[plate] = {
          vendor: "VIETSUN",
          name: info.name || "",
          phone: info.phone || "",
          cccd: info.cccd || "",
          gplx: info.gplx || ""
        };
        if (info.name && (info.cccd || info.gplx)) {
          DRIVER_DOCS_BY_NAME[info.name.toUpperCase().trim()] = {
            cccd: info.cccd,
            gplx: info.gplx
          };
        }
      }
      for (const [plate, info] of Object.entries(INITIAL_VENDOR_DRIVER_DB)) {
        DRIVER_DB[plate] = {
          vendor: info.vendor || "VENDOR",
          name: info.name || "",
          phone: info.phone || "",
          cccd: info.cccd || "",
          gplx: info.gplx || ""
        };
      }
      // Load cached live updates from localStorage if available
      try {
        const cached = JSON.parse(localStorage.getItem("vs_live_driver_db") || "null");
        if (cached && typeof cached === "object") {
          for (const [plate, info] of Object.entries(cached)) {
            DRIVER_DB[plate] = Object.assign({}, DRIVER_DB[plate] || {}, info);
          }
        }
      } catch (_) {}
    }
    initBaseDriverDb();

    function getDriverForPlate(plate) {
      if (!plate) return null;
      const clean = plate.toUpperCase().replace(/[^A-Z0-9]/g, "");
      return DRIVER_DB[clean] || null;
    }

    const VENDOR_NAME_NORMALIZE = {
      NHATSON: "NHAT SON",
      NGOCSANG: "NGOC SANG",
      PHUCAN: "PHUC AN",
      TUANHUNG: "TUAN HUNG",
      VUONGPHAT: "VUONG PHAT",
      PHUCTRUONGAN: "PHUC TRUONG AN",
      GIAKHANG: "GIA KHANG",
      DUCANH: "ANH DUC",
      HOANGLONGPHAT: "HOANG LONG PHAT",
      DAILONGPHAT: "DAI LONG PHAT",
      BAONAMPHAT: "BAO NAM PHAT",
      ANTHANHPHAT: "AN THANH PHAT",
      DUYLINHPHAT: "DUY LINH PHAT",
      NHANBAOPHAT: "NHAN BAO PHAT",
      THIENPHUCPHAT: "THIEN PHUC PHAT",
      TANHOANGQUANG: "TAN HOANG QUANG",
      MAINHATANH: "MAI NHAT ANH",
      DUONGNGUYEN: "DUONG NGUYEN",
      GIABAO: "GIA BAO",
      DUCHAI: "DUC HAI",
      DANGHUNG: "DANG HUNG",
      TRUCANH: "TRUC ANH"
    };

    function normalizeVendorNameClient(raw) {
      const clean = (raw || "").toUpperCase().trim();
      const noSp = clean.replace(/\s+/g, "");
      if (VENDOR_NAME_NORMALIZE[noSp]) return VENDOR_NAME_NORMALIZE[noSp];
      // Also match against DEFAULT_VENDORS without spaces
      const matched = DEFAULT_VENDORS.find(v => v.replace(/\s+/g, "") === noSp);
      return matched || clean;
    }

    async function syncLiveDriverDatabase(showNotify = false) {
      const statusEl = document.getElementById("driverSyncStatusText");
      if (statusEl) statusEl.textContent = "⏳ Đang đồng bộ Đội xe từ TMS & Google Sheet...";
      let vsUpdated = 0;
      let vdUpdated = 0;

      try {
        const urlsToTry = [
          "/api/index.php?route=drivers",
          "/api/drivers",
          "?api=drivers"
        ];
        let data = null;
        for (const u of urlsToTry) {
          try {
            const r = await fetch(u, { cache: "no-store" });
            if (r.ok) {
              data = await r.json();
              if (data && data.ok) break;
            }
          } catch (_) {}
        }

        if (data && data.ok) {
          // 1. Update Vietsun Fleet from live TMS ajax-gvt.php (loai=26)
          if (Array.isArray(data.vietsun)) {
            data.vietsun.forEach(item => {
              const p = (item.plate || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
              if (!p) return;
              const prev = DRIVER_DB[p] || {};
              const nameUpper = (item.name || "").toUpperCase().trim();
              const docsByName = DRIVER_DOCS_BY_NAME[nameUpper] || {};
              DRIVER_DB[p] = {
                vendor: "VIETSUN",
                name: item.name || prev.name || "",
                phone: item.phone || prev.phone || "",
                cccd: prev.cccd || docsByName.cccd || "",
                gplx: prev.gplx || docsByName.gplx || ""
              };
              if (!DEFAULT_XE_VIETSUN.includes(p)) DEFAULT_XE_VIETSUN.push(p);
              vsUpdated++;
            });
          }

          // 2. Update Vendor Fleet from live Google Sheet
          if (Array.isArray(data.vendors)) {
            data.vendors.forEach(item => {
              const p = (item.plate || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
              if (!p) return;
              const normVendor = normalizeVendorNameClient(item.vendor);
              DRIVER_DB[p] = {
                vendor: normVendor || "VENDOR",
                name: item.name || "",
                phone: item.phone || "",
                cccd: item.cccd || "",
                gplx: item.gplx || ""
              };
              vdUpdated++;
            });
          }
        } else {
          // Fallback: Fetch Google Sheet CSV directly from browser
          const sheetCsvUrl = "https://docs.google.com/spreadsheets/d/13ndQsSmBsKlHjIRQR0S0MO5BPqqL6J4CECBAYfQi1oo/export?format=csv&gid=1046738475";
          const r = await fetch(sheetCsvUrl, { cache: "no-store" });
          if (r.ok) {
            const csvText = await r.text();
            const lines = csvText.split(/\r?\n/);
            for (let i = 1; i < lines.length; i++) {
              const line = lines[i].trim();
              if (!line) continue;
              const cols = line.split(",");
              if (cols.length < 2) continue;
              const p = (cols[1] || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
              if (!p) continue;
              const normVendor = normalizeVendorNameClient(cols[0]);
              DRIVER_DB[p] = {
                vendor: normVendor || "VENDOR",
                name: (cols[2] || "").trim(),
                phone: (cols[3] || "").trim(),
                cccd: (cols[4] || "").trim(),
                gplx: (cols[5] || "").trim()
              };
              vdUpdated++;
            }
          }
        }

        try {
          localStorage.setItem("vs_live_driver_db", JSON.stringify(DRIVER_DB));
        } catch (_) {}

        populateDynamicDropdowns();
        if (allShipments.length > 0) renderShipmentList();

        const totalPlates = Object.keys(DRIVER_DB).length;
        if (statusEl) {
          statusEl.innerHTML = `✅ Đã đồng bộ <b>${totalPlates} xe</b> (VIETSUN: ${vsUpdated || 74} xe • Vendor Google Sheet: ${vdUpdated || 118} xe) lúc ${new Date().toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" })}`;
        }
        if (showNotify) {
          showToast(`Đã đồng bộ ${totalPlates} xe & tài xế từ TMS và Google Sheet!`, "success");
        }
      } catch (err) {
        console.warn("Driver sync warning:", err);
        if (statusEl) {
          statusEl.textContent = `Đang dùng danh bạ lưu sẵn (${Object.keys(DRIVER_DB).length} xe VIETSUN & Vendor).`;
        }
      }
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

    const isExplicitlyLoggedIn = safeGetStorage("vs_auth_v2", "") === "1";

    let state = {
      isLoggedIn: isExplicitlyLoggedIn,
      username: isExplicitlyLoggedIn ? safeGetStorage("vs_username", "") : "",
      password: isExplicitlyLoggedIn ? safeGetStorage("vs_password", "") : "",
      userString: isExplicitlyLoggedIn ? safeGetStorage("vs_userString", "") : "",
      apiDate: getTodayISO(),
      dateMode: "today",          // today | tomorrow | yesterday | 3days | unset | all | custom
      dateType: "ngayTraHang",    // ngayTraHang | ngayTauChayPo
      fromDate: getTodayISO(),
      toDate: getTodayISO(),
      xuatNhap: "ALL",            // ALL | X | N | NO_TRUCK
      chuHang: "ALL",             // ALL | VSV | LOG | VSHPG...
      truckStatus: "ALL",         // ALL | NO_TRUCK | HAS_TRUCK | NO_CONT
      contSize: "ALL",            // ALL | 20 | 40
      vendor: "ALL",
      province: "ALL",
      searchQuery: "",
      viewMode: safeGetStorage("vs_view_mode", "cards"), // "cards" | "table"
      bulkMode: false,
      bulkCarrier: null, // "VIETSUN" | "VENDOR"
      bulkSelectedIds: new Set()
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
      if (!isoStr || isoStr === "2021-11-11" || isoStr === "1970-01-01") return "Chưa xếp ngày";
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
      el.innerHTML = `<span>${type === "success" ? "✅" : type === "warning" ? "⚠️" : "❌"}</span><span>${msg}</span>`;
      container.appendChild(el);
      setTimeout(() => {
        el.style.opacity = "0";
        el.style.transition = "opacity 0.25s";
        setTimeout(() => el.remove(), 250);
      }, 2600);
    }

    // ISO 6346 Container Check Digit Validator chuẩn quốc tế
    function validateContainerISO(contStr) {
      if (!contStr) return { valid: false, empty: true, message: "Chưa nhập" };
      const csc = contStr.toUpperCase().replace(/[^A-Z0-9]+/g, "");
      if (csc.length !== 11) {
        return { valid: false, empty: false, message: `Độ dài ${csc.length}/11 ký tự` };
      }
      // Bảng quy đổi ký tự chuẩn ISO 6346 (bỏ qua các bội số của 11: 11, 22, 33)
      const charMap = {
        A: 10, B: 12, C: 13, D: 14, E: 15, F: 16, G: 17, H: 18, I: 19, J: 20,
        K: 21, L: 23, M: 24, N: 25, O: 26, P: 27, Q: 28, R: 29, S: 30, T: 31,
        U: 32, V: 34, W: 35, X: 36, Y: 37, Z: 38
      };
      let sum = 0;
      for (let i = 0; i < 10; i++) {
        const ch = csc[i];
        let val = (ch >= '0' && ch <= '9') ? parseInt(ch, 10) : charMap[ch];
        if (val === undefined) {
          return { valid: false, empty: false, message: `Ký tự ${ch} không hợp lệ` };
        }
        sum += val * Math.pow(2, i);
      }
      const check = (sum % 11) % 10;
      const lastDigit = parseInt(csc.charAt(10), 10);
      if (check === lastDigit) {
        return { valid: true, empty: false, message: "Chuẩn ISO ✓" };
      } else {
        return { valid: false, empty: false, message: `Sai số cuối (đúng: ${check})` };
      }
    }

    // =========================================================================
    // 4. API COMMUNICATION (LOCAL API PROXY)
    // =========================================================================
    const loadedCycleSet = new Set();

    // Vietsun TMS return_json.php groups shipments by billing period: 26th of (M-1) -> 25th of M
    function getBillingCycleParam(isoStr) {
      if (!isoStr || typeof isoStr !== "string") isoStr = getTodayISO();
      const parts = isoStr.split("-").map(Number);
      if (parts.length !== 3 || isNaN(parts[0])) return getTodayISO();
      let [y, m, d] = parts;
      if (d >= 26) {
        m += 1;
        if (m > 12) {
          m = 1;
          y += 1;
        }
      }
      return `${y}-${String(m).padStart(2, "0")}-01`;
    }

    function normalizeShipmentRecord(item) {
      return {
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
        tinhXuatHangPo: decodeHtmlEntities(item.tinhXuatHangPo || "").trim(),
        soContPo: (item.soContPo || "").trim(),
        soSealPo: (item.soSealPo || "").trim(),
        soXePo: (item.soXePo || "").trim(),
        donViVcPo: (item.donViVcPo || "").trim(),
        chuHangPo: (item.chuHangPo || "").trim()
      };
    }

    async function fetchSingleCycleFromApi(cycleDateParam) {
      const urlsToTry = [
        `/api/index.php?route=return_json&ngay=${encodeURIComponent(cycleDateParam)}`,
        `/api/return_json?ngay=${encodeURIComponent(cycleDateParam)}`
      ];

      let lastErr = null;
      for (const url of urlsToTry) {
        try {
          const resp = await fetch(url, { cache: "no-store" });
          if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
          const text = await resp.text();
          const cleaned = text.trim();
          if (cleaned.startsWith("<")) throw new Error("Phản hồi HTML thay vì JSON");
          const data = JSON.parse(cleaned);
          if (Array.isArray(data)) return data;
        } catch (err) {
          lastErr = err;
        }
      }
      throw lastErr || new Error("Không thể kết nối API");
    }

    async function fetchShipmentsFromApi(ngayParam) {
      const baseIso = ngayParam || getTodayISO();
      // Always cover -10 days to +15 days across Vietsun's 25th-of-month cutoff (so D+1, D+2, D+3, etc. are always present)
      const cyclesToFetch = Array.from(new Set([
        getBillingCycleParam(addDaysISO(baseIso, -10)),
        getBillingCycleParam(baseIso),
        getBillingCycleParam(addDaysISO(baseIso, 15))
      ]));

      const results = await Promise.all(
        cyclesToFetch.map(c => fetchSingleCycleFromApi(c).catch(err => {
          console.warn("Cycle fetch warning:", c, err);
          return null;
        }))
      );

      const validArrays = results.filter(r => Array.isArray(r));
      if (validArrays.length === 0) {
        throw new Error("Không thể kết nối API");
      }

      loadedCycleSet.clear();
      cyclesToFetch.forEach((c, idx) => {
        if (Array.isArray(results[idx])) loadedCycleSet.add(c);
      });

      const mergedMap = new Map();
      validArrays.forEach(arr => {
        arr.forEach(item => {
          if (item && item.idPo) {
            mergedMap.set(String(item.idPo), item);
          }
        });
      });
      return Array.from(mergedMap.values());
    }

    async function ensureDateCyclesLoaded(fromIso, toIso) {
      const needed = new Set();
      if (fromIso) needed.add(getBillingCycleParam(fromIso));
      if (toIso) needed.add(getBillingCycleParam(toIso));
      const missing = Array.from(needed).filter(c => !loadedCycleSet.has(c));
      if (missing.length === 0) return;

      try {
        showToast(`⏳ Đang tải thêm dữ liệu kỳ ${missing.join(", ")}...`, "success");
        const extraArrays = await Promise.all(
          missing.map(c => fetchSingleCycleFromApi(c).catch(() => []))
        );
        const existingMap = new Map();
        allShipments.forEach(s => existingMap.set(String(s.idPo), s));

        let added = 0;
        extraArrays.forEach((arr, idx) => {
          if (Array.isArray(arr) && arr.length > 0) {
            loadedCycleSet.add(missing[idx]);
            arr.forEach(raw => {
              if (raw && raw.idPo && !existingMap.has(String(raw.idPo))) {
                existingMap.set(String(raw.idPo), normalizeShipmentRecord(raw));
                added++;
              }
            });
          }
        });

        if (added > 0) {
          allShipments = Array.from(existingMap.values());
          allShipments.sort((a, b) => Number(b.idPo) - Number(a.idPo));
          populateDynamicDropdowns();
          applyFilters();
          showToast(`Đã bổ sung ${added} lô hàng cho ngày đã chọn!`, "success");
        }
      } catch (e) {
        console.warn("Extra cycle load error:", e);
      }
    }

    async function postCrudUpdate(payloadObj) {
      const formBody = new URLSearchParams();
      for (const [k, v] of Object.entries(payloadObj)) {
        formBody.append(k, v);
      }

      // Luôn ưu tiên endpoint API Gateway trên máy chủ
      const urlsToTry = [
        "/api/index.php?route=index-crud",
        "/api/index-crud"
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
          const text = await resp.text();
          if (text.includes("Lỗi đăng nhập") || text.includes("<!DOCTYPE")) {
            throw new Error(text.includes("Lỗi đăng nhập") ? "Lỗi phiên đăng nhập TMS" : "Phản hồi không hợp lệ từ máy chủ");
          }
          return text;
        } catch (err) {
          lastErr = err;
        }
      }
      throw lastErr || new Error("Không thể gửi lệnh cập nhật");
    }

    // =========================================================================
    // 5. DATA LOADING & FILTERING
    // =========================================================================
    async function refreshData(showNotification = false) {
      const realToday = getTodayISO();
      // Tự động nhảy sang ngày hôm nay mới nếu qua đêm hoặc đang ở chế độ xem Hôm nay
      if (state.dateMode === "today" || !state.apiDate || state.apiDate < realToday) {
        state.apiDate = realToday;
        if (state.dateMode === "today") {
          state.fromDate = realToday;
          state.toDate = realToday;
          const elFrom = document.getElementById("filterFromDate");
          const elTo = document.getElementById("filterToDate");
          if (elFrom) elFrom.value = realToday;
          if (elTo) elTo.value = realToday;
        }
      }
      updateQuickDateChipLabels();

      const syncLbl = document.getElementById("syncTimeLabel");
      syncLbl.textContent = "Đang đồng bộ...";
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
          tinhXuatHangPo: decodeHtmlEntities(item.tinhXuatHangPo || "").trim(),
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
        syncLbl.textContent = `Cập nhật ${nowStr} (${allShipments.length} lô)`;
        document.getElementById("connDot").style.background = "#4ade80";

        populateDynamicDropdowns();
        applyFilters();
        if (showNotification) {
          showToast(`Đã tải ${allShipments.length} lô hàng từ hệ thống!`, "success");
          syncLiveDriverDatabase(false);
        }
      } catch (err) {
        console.error(err);
        document.getElementById("connDot").style.background = "#f59e0b";
        const cached = safeGetStorage("vs_cached_shipments", null);
        if (cached) {
          allShipments = JSON.parse(cached);
          syncLbl.textContent = `Bản lưu tạm (${allShipments.length} lô)`;
          populateDynamicDropdowns();
          applyFilters();
          showToast("Đang hiển thị dữ liệu lưu tạm trên máy", "warning");
        } else {
          syncLbl.textContent = "Lỗi kết nối";
          document.getElementById("poListContainer").innerHTML = `
            <div class="state-box">
              <div style="font-size:36px;margin-bottom:8px;">⚠️</div>
              <div style="font-weight:700;color:#0f172a;">Không thể tải dữ liệu trực tiếp</div>
              <div style="font-size:12.5px;margin-top:6px;">Hãy chạy file <b>Chay_App_TMS.bat</b> trên máy tính hoặc đưa file lên host Vietsun để tránh chặn CORS của trình duyệt.</div>
              <button class="btn-primary-lg" style="margin:14px auto 0;max-width:220px;" onclick="refreshData(true)">Thử lại</button>
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

      // Add all trucks from DRIVER_DB (both VIETSUN and Google Sheet Vendor trucks)
      for (const [plate, info] of Object.entries(DRIVER_DB)) {
        if (!plate) continue;
        const vName = (info.vendor || "").toUpperCase().trim();
        knownTruckMap.set(plate, { plate, vendor: vName || "VENDOR", count: 5 });
        xeSet.add(plate);
        if (vName) vendorSet.add(vName);
      }

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
        const rawDv = normalizeVendorNameClient(s.donViVcPo || "");
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
      filterVendor.innerHTML = `<option value="ALL">-- Tất cả ĐVVT --</option>` +
        Array.from(vendorSet).sort().map(v => `<option value="${v}">${v}</option>`).join("");
      filterVendor.value = curV;

      const filterProv = document.getElementById("filterProvince");
      const curP = state.province;
      filterProv.innerHTML = `<option value="ALL">-- Tất cả Tỉnh --</option>` +
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
        if (state.vendor !== "ALL" && normalizeVendorNameClient(item.donViVcPo) !== state.vendor) return false;
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

      // Build ChuHang (Đối tượng) Chips with real-time counts
      renderChuHangChips(afterXN);

      // 3. Filter by ChuHang (Đối tượng)
      const afterChuHang = afterXN.filter(item => {
        if (state.chuHang === "ALL") return true;
        return (item.chuHangPo || "KHÁC") === state.chuHang;
      });

      // Update Floating 20' / 40' Dock Counts & Active State
      let dockC20 = 0, dockC40 = 0;
      afterChuHang.forEach(item => {
        if (Number(item.cont20Po) > 0) dockC20++;
        if (Number(item.cont40Po) > 0) dockC40++;
      });
      const elFab20 = document.getElementById("fabSize20");
      const elFab40 = document.getElementById("fabSize40");
      const elCnt20 = document.getElementById("fabCount20");
      const elCnt40 = document.getElementById("fabCount40");
      if (elCnt20) elCnt20.textContent = dockC20;
      if (elCnt40) elCnt40.textContent = dockC40;
      if (elFab20) elFab20.classList.toggle("active-20", state.contSize === "20");
      if (elFab40) elFab40.classList.toggle("active-40", state.contSize === "40");

      // 4. Filter by Container Size (20' / 40')
      filteredShipments = afterChuHang.filter(item => {
        if (state.contSize === "20") return Number(item.cont20Po) > 0;
        if (state.contSize === "40") return Number(item.cont40Po) > 0;
        return true;
      });

      // Lọc theo đơn vị vận chuyển in hàng loạt (VIETSUN vs VENDOR: các đội còn lại)
      if (state.bulkMode && state.bulkCarrier) {
        filteredShipments = filteredShipments.filter(item => {
          const rawDv = normalizeVendorNameClient(item.donViVcPo || "");
          if (state.bulkCarrier === "VIETSUN") {
            return rawDv === "VIETSUN";
          } else if (state.bulkCarrier === "VENDOR") {
            if (rawDv === "VIETSUN") return false;
            const autoV = (knownTruckMap.get(item.soXePo) || {}).vendor;
            return !!rawDv || (autoV && autoV !== "VIETSUN");
          }
          return true;
        });
      }

      // Ưu tiên các dòng chưa có Đơn vị V/C và Số xe lên top đầu (cho cả hàng Nhập & Xuất)
      filteredShipments.sort((a, b) => {
        const getPriority = (item) => {
          const hasV = !!(item.donViVcPo && item.donViVcPo.trim());
          const hasX = !!(item.soXePo && item.soXePo.trim());
          if (!hasV && !hasX) return 0; // Chưa có cả ĐVVC lẫn Số xe -> Ưu tiên cao nhất
          if (!hasX || !hasV) return 1; // Thiếu 1 trong 2 -> Ưu tiên kế tiếp
          return 2;                     // Đã có đủ cả 2 -> Xếp sau
        };
        const pA = getPriority(a);
        const pB = getPriority(b);
        if (pA !== pB) return pA - pB;
        return Number(b.idPo) - Number(a.idPo);
      });

      // Update Summary Stats
      updateSummaryCards(filteredShipments);
      updateActiveFilterBadge();

      // Giới hạn số dòng vẽ ra DOM mỗi lượt là 150 dòng (Bảng PC) / 60 thẻ (Mobile)
      // để khi chọn mục "Tất cả" (3.000+ lô) trình duyệt vẫn mượt tức thì không tốn RAM
      renderLimit = state.viewMode === "table" ? 150 : 60;
      renderShipmentList();
      updateBulkControlsUI();
    }

    function applyViewModeUI() {
      const isTable = state.viewMode === "table";
      document.body.classList.toggle("desktop-table-mode", isTable);
      const lbl = document.getElementById("lblDesktopMode");
      const btn = document.getElementById("btnToggleDesktop");
      if (lbl) lbl.textContent = isTable ? "📱 Thẻ Mobile" : "🖥️ Bảng PC";
      if (btn) {
        btn.style.background = isTable ? "#dcfce7" : "#ffffff";
        btn.style.color = isTable ? "#15803d" : "#1e3a8a";
      }
      updateBulkControlsUI();
    }

    function toggleDesktopMode() {
      closePcAcFloating();
      state.viewMode = state.viewMode === "table" ? "cards" : "table";
      try {
        localStorage.setItem("vs_view_mode", state.viewMode);
      } catch (_) {}
      applyViewModeUI();
      renderLimit = state.viewMode === "table" ? 300 : 60;
      renderShipmentList();
      showToast(
        state.viewMode === "table"
          ? "🖥️ Đã chuyển sang chế độ Bảng PC (Gõ trực tiếp ĐVVT & Số xe, bấm Enter / Mũi tên ⬆️⬇️ để nhảy dòng)"
          : "📱 Đã chuyển về chế độ Thẻ Mobile",
        "success"
      );
    }

    function renderChuHangChips(listBeforeChuHang) {
      const counts = {};
      listBeforeChuHang.forEach(item => {
        const ch = item.chuHangPo || "KHÁC";
        counts[ch] = (counts[ch] || 0) + 1;
      });

      const sortedKeys = Object.keys(counts).sort((a, b) => counts[b] - counts[a]);
      const container = document.getElementById("chuHangTabs");

      let html = `
        <button class="chip chip-blue ${state.chuHang === "ALL" ? "active" : ""}" onclick="setChuHangFilter('ALL')">
          🏢 Tất cả đối tượng <span class="count">${listBeforeChuHang.length}</span>
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
      if (state.contSize !== "ALL") c++;
      if (state.vendor !== "ALL") c++;
      if (state.province !== "ALL") c++;
      if (state.searchQuery) c++;
      document.getElementById("activeFilterCount").textContent = c;
    }

    // =========================================================================
    // 6. RENDER SHIPMENT CARDS
    // =========================================================================
    function escapeAttr(val) {
      return String(val || "")
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    }

    function calcLuuFreeQuaHan(item) {
      if (item.xuatNhapPo === "X") {
        return {
          col1: item.tinhXuatHangPo || "",
          col2: item.noiLayContPo || "",
          col3: item.hinhThucKHopPo || ""
        };
      }
      // For Import (N): NGÀY TÀU CẬP = ngayTauChayPo + 2 days
      const tauIso = item.ngayTauChayPo;
      if (!tauIso || tauIso === "2021-11-11" || tauIso === "1970-01-01") {
        return { col1: "", col2: "7", col3: "" };
      }
      const tauCapIso = addDaysISO(tauIso, 2);
      const giaoIso = (!item.ngayTraHang || item.ngayTraHang === "2021-11-11" || item.ngayTraHang === "1970-01-01")
        ? getTodayISO()
        : item.ngayTraHang;
      const d1 = new Date(tauCapIso + "T00:00:00");
      const d2 = new Date(giaoIso + "T00:00:00");
      const diffDays = Math.round((d2 - d1) / 86400000) + 1;
      const ngayLuu = Math.max(1, diffDays);
      const free = 7;
      const quaHan = Math.max(0, ngayLuu - free);
      return {
        col1: String(ngayLuu),
        col2: String(free),
        col3: quaHan > 0 ? String(quaHan) : "0"
      };
    }

    function renderDesktopTableView(slice, hasMore) {
      // Tab Hàng Nhập (N) bỏ luôn cột Xóa, luôn hiển thị cột Tick chọn
      const isBulk = !!state.bulkMode || state.xuatNhap === "N";
      const allSelected = slice.length > 0 && slice.every(i => state.bulkSelectedIds && state.bulkSelectedIds.has(String(i.idPo)));

      const rowsHtml = slice.map((item, idx) => {
        const isXuat = item.xuatNhapPo === "X";
        const isUnsetDate = !item.ngayTraHang || item.ngayTraHang === "2021-11-11" || item.ngayTraHang === "1970-01-01";
        const ngayGiaoStr = isUnsetDate ? "" : formatIsoToVN(item.ngayTraHang);
        const drvInfo = getDriverForPlate(item.soXePo);
        const drvTooltip = drvInfo ? `Tài xế: ${drvInfo.name} - SĐT: ${drvInfo.phone || ""}` : "";
        const cangDenVal = isXuat ? (item.tinhXuatHangPo || item.cangDenPo || "") : (item.cangDenPo || "");
        const rowClass = isXuat ? "pc-row-xuat" : "pc-row-nhap";
        const isItemChecked = !!(state.bulkSelectedIds && state.bulkSelectedIds.has(String(item.idPo)));

        const actionColTd = isBulk ? `
          <td style="text-align:center;padding:2px 3px !important;width:40px;background:#eff6ff;" onclick="event.stopPropagation()">
            <input type="checkbox" class="chk-bulk-item" value="${item.idPo}" ${isItemChecked ? "checked" : ""} onchange="onToggleBulkItem('${item.idPo}', this.checked)" style="width:16px;height:16px;cursor:pointer;vertical-align:middle;" />
          </td>
        ` : `
          <td style="text-align:center;padding:2px 3px !important;width:36px;">
            <button type="button" class="pc-del-btn" onclick="openDeleteBookingModal('${item.idPo}')" title="Xóa booking thừa #${item.idPo}">🗑️</button>
          </td>
        `;

        return `
          <tr id="pc-row-${item.idPo}" data-row-idx="${idx}" class="${rowClass}" ondblclick="openEditModal('${item.idPo}')" title="Nhấp đúp chuột để mở chi tiết #${item.idPo}">
            ${actionColTd}
            <td style="text-align:center;white-space:nowrap;font-weight:700;color:#2563eb;cursor:pointer;" onclick="openEditModal('${item.idPo}')" title="Bấm mở chi tiết #${item.idPo}">${escapeAttr(item.chuyenPo)}</td>
            <td class="pc-truncate" title="${escapeAttr(item.tenKhachPo)}">${escapeAttr(item.tenKhachPo)}</td>
            <td class="pc-truncate" style="${!isXuat ? 'font-weight:700;color:#0f172a;' : ''}" title="${escapeAttr(item.khoNhanHangPo)}">${escapeAttr(item.khoNhanHangPo)}</td>
            <td class="pc-truncate" style="max-width:105px;" title="${escapeAttr(item.lienHeNhanPo)}">${escapeAttr(item.lienHeNhanPo)}</td>
            <td class="pc-truncate" style="max-width:160px;" title="${escapeAttr(item.diaChiNhanPo)}">${escapeAttr(item.diaChiNhanPo)}</td>
            <td style="text-align:center;white-space:nowrap;">${escapeAttr(item.tinhNhanPo)}</td>
            <td class="pc-cell-edit" style="min-width:110px;">
              <input type="text" class="pc-inp" style="font-weight:800;text-align:center;text-transform:uppercase;${!isXuat ? 'background:#f1f5f9;cursor:not-allowed;' : ''}"
                id="pc_cont_${item.idPo}" value="${escapeAttr(item.soContPo)}"
                data-row="${idx}" data-col="cont"
                ${!isXuat ? 'readonly title="Số cont hàng nhập không được sửa"' : ''}
                onfocus="onPcRowFocus(this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'cont', '${item.idPo}')"
                onchange="saveInlinePcField('${item.idPo}', 'soContPo', this.value, this)"
                ondblclick="openEditModal('${item.idPo}')" />
            </td>
            <td class="pc-cell-edit" style="min-width:80px;">
              <input type="text" class="pc-inp" style="font-weight:700;text-align:center;${!isXuat ? 'background:#f1f5f9;cursor:not-allowed;' : ''}"
                id="pc_seal_${item.idPo}" value="${escapeAttr(item.soSealPo)}"
                data-row="${idx}" data-col="seal"
                ${!isXuat ? 'readonly title="Số seal hàng nhập không được sửa"' : ''}
                onfocus="onPcRowFocus(this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'seal', '${item.idPo}')"
                onchange="saveInlinePcField('${item.idPo}', 'soSealPo', this.value, this)" />
            </td>
            <td style="text-align:center;font-weight:700;">${Number(item.cont20Po) > 0 ? "1" : ""}</td>
            <td style="text-align:center;font-weight:700;">${Number(item.cont40Po) > 0 ? "1" : ""}</td>
            <td class="pc-cell-edit" style="min-width:92px;">
              <input type="text" class="pc-inp" style="color:#991b1b;font-weight:700;text-align:center;"
                id="pc_ngay_${item.idPo}" value="${ngayGiaoStr}" placeholder="DD/MM/YYYY"
                data-row="${idx}" data-col="ngay"
                onfocus="onPcRowFocus(this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'ngay', '${item.idPo}')"
                onchange="saveInlinePcDate('${item.idPo}', this.value, this)" />
            </td>
            <td style="text-align:center;font-weight:700;color:#0369a1;white-space:nowrap;" title="${escapeAttr(cangDenVal)}">${escapeAttr(cangDenVal) || "-"}</td>
            <td class="pc-cell-cyan">
              <input type="text" class="pc-inp" style="font-weight:700;text-align:center;text-transform:uppercase;"
                id="pc_dv_${item.idPo}" value="${escapeAttr(item.donViVcPo)}" placeholder="Gõ ĐVVT..."
                autocomplete="off" data-row="${idx}" data-col="dv"
                onfocus="onPcRowFocus(this); openPcVendorAc('${item.idPo}', this)"
                oninput="openPcVendorAc('${item.idPo}', this)"
                onblur="onPcCellBlur('${item.idPo}', 'donViVcPo', this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'dv', '${item.idPo}')" />
            </td>
            <td class="pc-cell-cyan">
              <input type="text" class="pc-inp" style="font-weight:800;color:#0369a1;text-align:center;text-transform:uppercase;"
                id="pc_xe_${item.idPo}" value="${escapeAttr(item.soXePo)}" placeholder="Gõ Số xe..."
                autocomplete="off" data-row="${idx}" data-col="xe" title="${escapeAttr(drvTooltip)}"
                onfocus="onPcRowFocus(this); openPcTruckAc('${item.idPo}', this)"
                oninput="onPcTruckInput('${item.idPo}', this)"
                onblur="onPcCellBlur('${item.idPo}', 'soXePo', this)"
                onkeydown="onPcCellKeyDown(event, ${idx}, 'xe', '${item.idPo}')" />
            </td>
            <td class="pc-actions-cell">
              ${isXuat ? `
                <button type="button" class="pc-act-btn" onclick="copyPoInfoById('${item.idPo}', 1)" title="Copy Thông tin xe đóng hàng">🚛 TT Xe</button>
                <button type="button" class="pc-act-btn" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="openPklModal('${item.idPo}')" title="Phiếu PKL">📄 PKL</button>
              ` : (() => {
                const bbTpl = detectBbTemplate(item);
                const isCustomBb = bbTpl.code !== "STANDARD";
                return `
                  <button type="button" class="pc-act-btn" onclick="copyPoInfoById('${item.idPo}', 2)" title="Copy Thông tin giao hàng">🚛 Giao</button>
                  <button type="button" class="pc-act-btn" onclick="copyPoInfoById('${item.idPo}', 1)" title="Copy Thông báo hàng đến">💬 TB</button>
                  <button type="button" class="pc-act-btn" style="${isCustomBb ? 'background:#fffbeb;color:#b45309;border-color:#fcd34d;' : 'background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;'}" onclick="printSingleBienBan('${item.idPo}')" title="In Biên Bản (${bbTpl.name})">📄 ${isCustomBb ? bbTpl.shortBadge : 'BB'}</button>
                `;
              })()}
            </td>
          </tr>
        `;
      }).join("");

      const moreBtnHtml = hasMore
        ? `<button class="load-more-btn" onclick="loadMoreCards()">Xem thêm (${filteredShipments.length - renderLimit} lô hàng nữa)...</button>`
        : "";

      const actionColTh = isBulk ? `
        <th style="width:40px;text-align:center;background:#dbeafe;" title="Tick all: Chọn / Bỏ chọn tất cả">
          <input type="checkbox" id="chkBulkPrintAll" onchange="toggleSelectAllBulk(this.checked)" ${allSelected ? "checked" : ""} style="width:16px;height:16px;cursor:pointer;vertical-align:middle;" />
        </th>
      ` : `
        <th style="width:36px;text-align:center;" title="Xóa booking thừa">XÓA</th>
      `;

      return `
        <div class="pc-table-wrap">
          <table class="pc-table">
            <thead>
              <tr>
                ${actionColTh}
                <th style="width:72px;text-align:center;">CHUYẾN</th>
                <th>KHÁCH HÀNG</th>
                <th>KHO NHẬN/ĐÓNG</th>
                <th style="width:100px;max-width:105px;">LIÊN HỆ</th>
                <th style="width:150px;max-width:160px;">ĐỊA CHỈ</th>
                <th style="width:65px;text-align:center;">TỈNH</th>
                <th style="width:110px;text-align:center;">SỐ CONT</th>
                <th style="width:80px;text-align:center;">SEAL</th>
                <th style="width:35px;text-align:center;">20</th>
                <th style="width:35px;text-align:center;">40</th>
                <th style="width:92px;text-align:center;">NGÀY GIAO/ĐÓNG</th>
                <th style="width:75px;text-align:center;">CẢNG ĐẾN</th>
                <th style="background:#a5f3fc;color:#0f172a;min-width:120px;">ĐƠN VỊ V/C ⚡</th>
                <th style="background:#a5f3fc;color:#0f172a;min-width:110px;">SỐ XE ⚡</th>
                <th style="width:155px;text-align:center;">THAO TÁC</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml}
            </tbody>
          </table>
        </div>
        ${moreBtnHtml}
      `;
    }

    let activePcAcContext = null; // { idPo, field, inputEl }
    let isSelectingPcAc = false;

    function onPcRowFocus(inputEl) {
      document.querySelectorAll(".pc-table tbody tr.pc-row-active").forEach(tr => tr.classList.remove("pc-row-active"));
      const tr = inputEl.closest("tr");
      if (tr) tr.classList.add("pc-row-active");
    }

    function positionPcAcFloating(inputEl) {
      const box = document.getElementById("pcAcFloating");
      if (!box || !inputEl) return;
      const rect = inputEl.getBoundingClientRect();
      const spaceBelow = window.innerHeight - rect.bottom;
      const width = Math.max(290, rect.width);
      box.style.width = `${width}px`;
      const left = Math.min(window.innerWidth - width - 10, Math.max(8, rect.left));
      box.style.left = `${left}px`;
      if (spaceBelow < 220 && rect.top > 230) {
        box.style.top = "auto";
        box.style.bottom = `${window.innerHeight - rect.top + 3}px`;
      } else {
        box.style.bottom = "auto";
        box.style.top = `${rect.bottom + 3}px`;
      }
    }

    function closePcAcFloating() {
      const box = document.getElementById("pcAcFloating");
      if (box) box.classList.remove("open");
      activePcAcContext = null;
    }

    document.addEventListener("mousedown", (e) => {
      if (!e.target.closest("#pcAcFloating") && !e.target.closest(".pc-inp")) {
        closePcAcFloating();
      }
    });

    function openPcVendorAc(idPo, inputEl) {
      activePcAcContext = { idPo, field: "donViVcPo", inputEl };
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      const q = (inputEl.value || "").toUpperCase().trim();
      const isInitial = po && q === (po.donViVcPo || "").toUpperCase().trim();
      const filterQ = isInitial ? "" : q;

      const matches = knownVendorList.filter(v => !filterQ || v.includes(filterQ)).slice(0, 35);
      const box = document.getElementById("pcAcFloating");
      if (matches.length === 0) {
        box.classList.remove("open");
        return;
      }

      positionPcAcFloating(inputEl);
      box.innerHTML = `
        <div class="ac-header">
          <span>🏢 Chọn ĐVVT (Lô #${idPo})</span>
          <span onclick="closePcAcFloating()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(v => `
          <div class="ac-item" onmousedown="selectPcVendorAc('${idPo}', '${v.replace(/'/g, "\\'")}')">
            <span>🏢 ${v}</span>
          </div>
        `).join("")}
      `;
      box.classList.add("open");
    }

    function selectPcVendorAc(idPo, vendor) {
      isSelectingPcAc = true;
      const inp = document.getElementById(`pc_dv_${idPo}`);
      if (inp) {
        inp.value = vendor;
        saveInlinePcField(idPo, "donViVcPo", vendor, inp);
      }
      closePcAcFloating();
      // Move focus to Số Xe of the same row for ultra-fast entry
      const xeInp = document.getElementById(`pc_xe_${idPo}`);
      if (xeInp) setTimeout(() => { xeInp.focus(); xeInp.select(); }, 30);
      setTimeout(() => { isSelectingPcAc = false; }, 250);
    }

    function openPcTruckAc(idPo, inputEl) {
      activePcAcContext = { idPo, field: "soXePo", inputEl };
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      const dvInp = document.getElementById(`pc_dv_${idPo}`);
      const curVendor = ((dvInp ? dvInp.value : (po && po.donViVcPo)) || "").toUpperCase().trim();
      const q = (inputEl.value || "").toUpperCase().replace(/\s+/g, "").trim();
      const isInitial = po && q === (po.soXePo || "").toUpperCase().trim();
      const filterQ = isInitial ? "" : q;

      const allTrucks = Array.from(knownTruckMap.values());
      let matches = allTrucks.filter(t => !filterQ || t.plate.includes(filterQ));
      matches.sort((a, b) => {
        const aSameV = curVendor && a.vendor === curVendor ? 1 : 0;
        const bSameV = curVendor && b.vendor === curVendor ? 1 : 0;
        if (aSameV !== bSameV) return bSameV - aSameV;
        if (filterQ) {
          const aStarts = a.plate.startsWith(filterQ) ? 1 : 0;
          const bStarts = b.plate.startsWith(filterQ) ? 1 : 0;
          if (aStarts !== bStarts) return bStarts - aStarts;
        }
        return b.count - a.count;
      });
      matches = matches.slice(0, 35);

      const box = document.getElementById("pcAcFloating");
      if (matches.length === 0) {
        box.classList.remove("open");
        return;
      }

      positionPcAcFloating(inputEl);
      box.innerHTML = `
        <div class="ac-header">
          <span>🚛 Gợi ý Số xe ${curVendor ? `(${curVendor})` : `(Lô #${idPo})`}</span>
          <span onclick="closePcAcFloating()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(t => {
          const isMatchVendor = curVendor && t.vendor === curVendor;
          const drv = getDriverForPlate(t.plate);
          const vLabel = (drv && drv.vendor) || t.vendor || "";
          const subLabel = drv && drv.name
            ? `${drv.name}${drv.phone ? ` (${drv.phone})` : ""}${vLabel ? ` • ${vLabel}` : ""}`
            : vLabel;
          return `
            <div class="ac-item ${isMatchVendor ? 'same-vendor' : ''}" onmousedown="selectPcTruckAc('${idPo}', '${t.plate}', '${vLabel.replace(/'/g, "\\'")}')">
              <span>🚛 ${t.plate}</span>
              ${subLabel ? `<span class="ac-sub ${isMatchVendor ? 'match-vendor' : ''}">${isMatchVendor ? '★ ' : ''}${subLabel}</span>` : ''}
            </div>
          `;
        }).join("")}
      `;
      box.classList.add("open");
    }

    function selectPcTruckAc(idPo, plate, vendor) {
      isSelectingPcAc = true;
      const xeInp = document.getElementById(`pc_xe_${idPo}`);
      const dvInp = document.getElementById(`pc_dv_${idPo}`);
      if (xeInp) xeInp.value = plate;
      if (vendor && dvInp && dvInp.value !== vendor) {
        dvInp.value = vendor;
        saveInlinePcField(idPo, "donViVcPo", vendor, dvInp);
      }
      if (xeInp) {
        saveInlinePcField(idPo, "soXePo", plate, xeInp);
      }
      closePcAcFloating();
      // Automatically jump down to next row's Số Xe input for rapid dispatching!
      if (xeInp) {
        const curRow = Number(xeInp.dataset.row);
        const nextXe = document.querySelector(`.pc-inp[data-row="${curRow + 1}"][data-col="xe"]`);
        if (nextXe) setTimeout(() => { nextXe.focus(); nextXe.select(); }, 40);
      }
      setTimeout(() => { isSelectingPcAc = false; }, 250);
    }

    function onPcTruckInput(idPo, inputEl) {
      const raw = (inputEl.value || "").toUpperCase().replace(/\s+/g, "");
      inputEl.value = raw;
      const drv = getDriverForPlate(raw);
      const dvInp = document.getElementById(`pc_dv_${idPo}`);
      if (drv && drv.vendor && dvInp) {
        dvInp.value = drv.vendor;
      } else if (raw.length >= 5) {
        const matchedVs = DEFAULT_XE_VIETSUN.find(x => x === raw || x.endsWith(raw));
        if (matchedVs && dvInp && (!dvInp.value || dvInp.value === "VIETSUN")) {
          dvInp.value = "VIETSUN";
        }
      }
      openPcTruckAc(idPo, inputEl);
    }

    function onPcCellBlur(idPo, fieldName, inputEl) {
      setTimeout(() => {
        if (isSelectingPcAc) return;
        const po = allShipments.find(x => String(x.idPo) === String(idPo));
        if (!po) return;
        const val = (inputEl.value || "").toUpperCase().trim();
        if (val !== (po[fieldName] || "").toUpperCase().trim()) {
          saveInlinePcField(idPo, fieldName, val, inputEl);
        }
      }, 180);
    }

    function onPcCellKeyDown(e, rowIdx, colName, idPo) {
      if (e.key === "Escape") {
        closePcAcFloating();
        return;
      }
      if (e.key === "Enter" || e.key === "ArrowDown" || e.key === "ArrowUp") {
        e.preventDefault();
        closePcAcFloating();
        const inputEl = e.target;
        const val = (inputEl.value || "").trim();
        const po = allShipments.find(x => String(x.idPo) === String(idPo));

        // Save current cell if modified
        if (po) {
          if (colName === "ngay") {
            saveInlinePcDate(idPo, val, inputEl);
          } else {
            const fieldMap = { cont: "soContPo", seal: "soSealPo", dv: "donViVcPo", xe: "soXePo" };
            const fieldName = fieldMap[colName];
            if (fieldName && val.toUpperCase() !== (po[fieldName] || "").toUpperCase()) {
              // If typing short 4-5 digit plate in xe, auto-expand if unique match in knownTruckMap
              let finalVal = val.toUpperCase();
              if (colName === "xe" && finalVal.length >= 4 && finalVal.length < 7) {
                const found = Array.from(knownTruckMap.values()).find(t => t.plate.endsWith(finalVal));
                if (found) {
                  finalVal = found.plate;
                  inputEl.value = finalVal;
                  const drv = getDriverForPlate(finalVal);
                  const vName = (drv && drv.vendor) || found.vendor || "";
                  const dvInp = document.getElementById(`pc_dv_${idPo}`);
                  if (vName && dvInp) {
                    dvInp.value = vName;
                    saveInlinePcField(idPo, "donViVcPo", vName, dvInp);
                  }
                }
              }
              saveInlinePcField(idPo, fieldName, finalVal, inputEl);
            }
          }
        }

        // Move focus to next/previous row
        const targetRow = e.key === "ArrowUp" ? rowIdx - 1 : rowIdx + 1;
        const nextInp = document.querySelector(`.pc-inp[data-row="${targetRow}"][data-col="${colName}"]`);
        if (nextInp) {
          nextInp.focus();
          nextInp.select();
        }
      }
    }

    async function saveInlinePcDate(idPo, vnDateStr, inputEl) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      const clean = (vnDateStr || "").trim();
      let isoVal = "2021-11-11";
      let payloadDate = "";
      if (clean) {
        const parts = clean.split(/[\/\-]/);
        if (parts.length === 3) {
          const dd = parts[0].padStart(2, "0");
          const mm = parts[1].padStart(2, "0");
          const yyyy = parts[2].length === 2 ? "20" + parts[2] : parts[2];
          isoVal = `${yyyy}-${mm}-${dd}`;
          payloadDate = `${dd}/${mm}/${yyyy}`;
        }
      }
      if (isoVal === (po.ngayTraHang || "2021-11-11")) return;

      if (inputEl) {
        inputEl.classList.remove("pc-saved");
        inputEl.classList.add("pc-saving");
      }
      try {
        const kieuNgay = po.xuatNhapPo === "X" ? "8" : "9";
        await postCrudUpdate({
          TenDangNhap: state.username,
          userString: state.userString,
          cv: 3,
          loai: 1,
          iddt: 1,
          cot: `b18<<|${kieuNgay}<<|20<<|${po.idPo}<<|ngayTraHang<<|${po.codePo}`,
          thongtin: payloadDate
        });
        po.ngayTraHang = isoVal;
        if (inputEl) {
          inputEl.classList.remove("pc-saving");
          inputEl.classList.add("pc-saved");
        }
        showToast(`Đã lưu Ngày (${payloadDate || "Chưa xếp"}) lô #${po.idPo}!`, "success");
      } catch (err) {
        if (inputEl) inputEl.classList.remove("pc-saving");
        showToast(`Lỗi lưu ngày lô #${po.idPo}: ${err.message}`, "error");
      }
    }

    async function saveInlinePcField(idPo, fieldName, rawVal, inputEl) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      if (po.xuatNhapPo !== "X" && (fieldName === "soContPo" || fieldName === "soSealPo")) {
        return; // Khóa cứng cont/seal hàng nhập
      }
      rawVal = (rawVal || "").toUpperCase().trim();

      if (inputEl) {
        inputEl.classList.remove("pc-saved");
        inputEl.classList.add("pc-saving");
      }

      try {
        if (fieldName === "soContPo") {
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 5,
            loai: 21,
            iddt: po.idPo,
            cot: rawVal,
            thongtin: po.codePo
          });
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|50<<|${po.idPo}<<|soContPo<<|${po.codePo}`,
            thongtin: rawVal
          });
          po.soContPo = rawVal;
        } else if (fieldName === "soXePo") {
          const drv = getDriverForPlate(rawVal);
          const dvInp = document.getElementById(`pc_dv_${po.idPo}`);
          let autoVendor = (drv && drv.vendor) || "";
          if (!autoVendor && DEFAULT_XE_VIETSUN.includes(rawVal)) autoVendor = "VIETSUN";

          // 1. Luôn lưu Số xe chắc chắn bằng lệnh b18 chuẩn
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|50<<|${po.idPo}<<|soXePo<<|${po.codePo}`,
            thongtin: rawVal
          });
          po.soXePo = rawVal;

          // 2. Gửi thêm b18newtruck để TMS nhận diện xe Vietsun
          let htmlResp = "";
          try {
            htmlResp = await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18newtruck<<|4<<|50<<|${po.idPo}<<|soXePo<<|${po.codePo}`,
              thongtin: rawVal
            });
          } catch (_) {}

          if (htmlResp && htmlResp.includes("reload_input_ajax")) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlResp, "text/html");
            const kqDv = doc.getElementById(`kq_donViVcPo_${po.idPo}`);
            if (kqDv && kqDv.textContent && kqDv.textContent.trim()) {
              autoVendor = kqDv.textContent.trim().toUpperCase();
            }
          }

          if (autoVendor && po.donViVcPo !== autoVendor) {
            po.donViVcPo = autoVendor;
            if (dvInp) {
              dvInp.value = autoVendor;
              dvInp.classList.add("pc-saved");
            }
            await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18<<|4<<|30<<|${po.idPo}<<|donViVcPo<<|${po.codePo}`,
              thongtin: autoVendor
            });
          }
          saveRecentTruck(rawVal, po.donViVcPo || autoVendor);
        } else if (fieldName === "donViVcPo") {
          // Lưu Đơn vị vận tải bằng b18 chuẩn
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|30<<|${po.idPo}<<|donViVcPo<<|${po.codePo}`,
            thongtin: rawVal
          });
          po.donViVcPo = rawVal;
        } else {
          const maxLen = 50;
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|${maxLen}<<|${po.idPo}<<|${fieldName}<<|${po.codePo}`,
            thongtin: rawVal
          });
          po[fieldName] = rawVal;
        }

        if (inputEl) {
          inputEl.classList.remove("pc-saving");
          inputEl.classList.add("pc-saved");
        }
        updateSummaryCards(filteredShipments);
        showToast(`✅ Đã lưu ${fieldName === 'soXePo' ? 'Số xe' : fieldName === 'donViVcPo' ? 'ĐVVT' : fieldName}: ${rawVal || '(Trống)'} (Lô #${po.idPo})`, "success");
      } catch (err) {
        if (inputEl) inputEl.classList.remove("pc-saving");
        showToast(`Lỗi lưu ${fieldName} lô #${po.idPo}: ${err.message}`, "error");
      }
    }

    function renderShipmentList() {
      applyViewModeUI();
      const container = document.getElementById("poListContainer");
      if (filteredShipments.length === 0) {
        container.innerHTML = `
          <div class="state-box">
            <div style="font-size:38px;margin-bottom:8px;">📭</div>
            <div style="font-weight:700;color:#334155;font-size:15px;">Không có lô hàng nào khớp bộ lọc</div>
            <div style="font-size:12.5px;margin-top:4px;">Thử chọn "Tất cả (30 ngày)" hoặc đổi đối tượng/loại hàng.</div>
            <button class="mini-btn" style="margin-top:12px;padding:8px 14px;" onclick="setQuickDate('all')">Xem tất cả 30 ngày</button>
          </div>
        `;
        return;
      }

      const slice = filteredShipments.slice(0, renderLimit);
      const hasMore = filteredShipments.length > renderLimit;

      if (state.viewMode === "table") {
        container.innerHTML = renderDesktopTableView(slice, hasMore);
        return;
      }

      const todayIso = getTodayISO();

      const cardsHtml = slice.map(item => {
        const isXuat = item.xuatNhapPo === "X";
        const typeBadge = isXuat
          ? `<span class="badge badge-xuat">📤 XUẤT</span>`
          : item.xuatNhapPo === "N"
          ? `<span class="badge badge-nhap">📥 NHẬP</span>`
          : `<span class="badge badge-chuhang">${item.xuatNhapPo}</span>`;

        const sizeStr = Number(item.cont40Po) > 0 ? "40'HC" : Number(item.cont20Po) > 0 ? "20'DC" : "";
        const sizeBadge = sizeStr ? `<span class="badge badge-size">${sizeStr}</span>` : "";

        // ISO Check on card
        let isoHtml = "";
        if (item.soContPo) {
          const check = validateContainerISO(item.soContPo);
          isoHtml = check.valid
            ? `<span class="iso-badge iso-ok" title="Đúng chuẩn ISO 6346">✓</span>`
            : `<span class="iso-badge iso-warn" title="${check.message}">!</span>`;
        }

        const contDisplay = item.soContPo
          ? `<div class="cont-number">${item.soContPo} ${isoHtml}</div>`
          : `<div class="cont-number empty-val">Chưa có số Cont</div>`;

        const sealDisplay = item.soSealPo
          ? `Seal: <b>${item.soSealPo}</b>`
          : `Seal: <span style="color:#94a3b8;">--</span>`;

        const hasTruck = Boolean(item.soXePo);
        const drvInfo = getDriverForPlate(item.soXePo);
        const truckDisplay = hasTruck
          ? `<div class="truck-number">🚛 ${item.soXePo}</div>`
          : `<div class="truck-number unassigned">⚠️ Chưa điều xe</div>`;

        const vendorDisplay = drvInfo
          ? `<div class="vendor-name" style="color:#15803d;" title="${drvInfo.name} - ${drvInfo.phone || ""}">👨‍✈️ ${drvInfo.name}</div>`
          : item.donViVcPo
          ? `<div class="vendor-name">🏢 ${item.donViVcPo}</div>`
          : `<div class="vendor-name" style="color:#94a3b8;">ĐVVT: Chưa gán</div>`;

        // Delivery / Packing Date pill
        const isUnsetDate = !item.ngayTraHang || item.ngayTraHang === "2021-11-11" || item.ngayTraHang === "1970-01-01";
        const isTodayDate = item.ngayTraHang === todayIso;
        const dateClass = isUnsetDate ? "date-unset" : isTodayDate ? "date-today" : "";
        const dateLabelPrefix = isXuat ? "Ngày đóng" : "Ngày giao";
        const dateFormatted = formatIsoToVN(item.ngayTraHang);

        // Extract phone number if available in lienHeNhanPo
        const phoneMatch = (item.lienHeNhanPo || "").match(/(0[0-9.\-\s]{8,13})/);
        const cleanPhone = phoneMatch ? phoneMatch[1].replace(/[^0-9]/g, "") : "";

        const locationParts = [item.khoNhanHangPo, item.diaChiNhanPo, item.tinhNhanPo]
          .filter(x => x && x !== "." && x !== "THONG BAO SAU");
        const locationText = locationParts.length > 0 ? locationParts.join(" • ") : "Địa chỉ: Thông báo sau";

        // For Import (N): Highlight Warehouse Name (khoNhanHangPo) prominently instead of Customer Name
        const rawKho = (item.khoNhanHangPo && item.khoNhanHangPo !== ".") ? item.khoNhanHangPo.trim() : "";
        const isKhoTbs = !rawKho || rawKho.toUpperCase() === "THONG BAO SAU";
        const addrOnlyParts = [item.diaChiNhanPo, item.tinhNhanPo]
          .filter(x => x && x !== "." && x !== "THONG BAO SAU");
        const addrOnlyText = addrOnlyParts.length > 0 ? addrOnlyParts.join(" • ") : "Địa chỉ: Thông báo sau";

        const customerOrWarehouseHtml = isXuat
          ? `
              <div class="customer-name">
                <span>👤 ${item.tenKhachPo || "Khách lẻ"}</span>
                <span style="font-size:11.5px;font-weight:600;color:#64748b;">${item.tenHangHoaPo || ""} ${item.trongLuongPo ? `(${item.trongLuongPo}T)` : ""}</span>
              </div>
              <div class="warehouse-addr">📍 ${locationText}</div>
            `
          : `
              <div class="customer-name">
                <span style="color:${isKhoTbs ? '#d97706' : '#0f172a'};font-size:14.5px;font-weight:800;">🏭 ${rawKho || "Chưa cập nhật Kho"}</span>
                <span style="font-size:11.5px;font-weight:600;color:#64748b;">${item.tenHangHoaPo || ""} ${item.trongLuongPo ? `(${item.trongLuongPo}T)` : ""}</span>
              </div>
              <div class="warehouse-addr">📍 ${addrOnlyText} • <span style="color:#334155;font-weight:600;">👤 KH: ${item.tenKhachPo || "Khách lẻ"}</span></div>
            `;

        const portBadgeHtml = isXuat
          ? (item.tinhXuatHangPo ? `<span class="badge" style="background:#e0f2fe;color:#0369a1;">🚢 ${item.tinhXuatHangPo}</span>` : "")
          : (item.cangDenPo ? `<span class="badge" style="background:#dcfce7;color:#15803d;">⚓ ${item.cangDenPo}</span>` : "");

        return `
          <div class="po-card type-${item.xuatNhapPo} ${!hasTruck ? 'no-truck' : ''}" id="card-${item.idPo}">
            <div class="card-top">
              <div class="badges-left">
                ${typeBadge}
                <span class="badge badge-chuhang">🏢 ${item.chuHangPo || "N/A"}</span>
                ${sizeBadge}
                ${portBadgeHtml}
                ${item.hinhThucKHopPo ? `<span class="badge" style="background:#f3e8ff;color:#7e22ce;">🔗 ${item.hinhThucKHopPo}</span>` : ""}
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
                <div class="field-mini-label">XE / ĐƠN VỊ VẬN TẢI</div>
                ${truckDisplay}
                ${vendorDisplay}
              </div>
            </div>

            <div class="customer-row" onclick="openEditModal('${item.idPo}')">
              ${customerOrWarehouseHtml}
              ${item.lienHeNhanPo && item.lienHeNhanPo !== "." ? `<div style="font-size:11.5px;color:#0369a1;margin-top:2px;font-weight:600;">📞 LH: ${item.lienHeNhanPo}</div>` : ""}
            </div>

            <div class="card-meta">
              <div class="meta-item">
                <span>🚢 ${item.tauPo || "--"} (${item.chuyenPo || "--"})${!isXuat && item.ngayTauChayPo ? ` • ${formatIsoToVN(item.ngayTauChayPo)}` : ""}</span>
              </div>
              <div class="meta-item">
                <span class="date-pill ${dateClass}">📅 ${dateLabelPrefix}: ${dateFormatted}</span>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn-card btn-edit" onclick="openEditModal('${item.idPo}')">
                ✏️ Cập nhật
              </button>
              ${!isXuat ? `
              <button class="btn-card btn-zalo" onclick="copyPoInfoById('${item.idPo}', 2)" title="Copy THÔNG TIN GIAO HÀNG (Biểu tượng xe)">
                🚛 Giao hàng
              </button>
              ` : ""}
              <button class="btn-card btn-zalo" onclick="copyPoInfoById('${item.idPo}', 1)" title="${isXuat ? 'Copy THÔNG TIN XE ĐÓNG HÀNG' : 'Copy THÔNG BÁO HÀNG ĐẾN'}">
                ${isXuat ? "🚛 Thông tin xe" : "💬 TB"}
              </button>
              ${isXuat ? `<button class="btn-card btn-zalo" style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;" onclick="openPklModal('${item.idPo}')" title="In / Gửi Phiếu PKL Hạ Cảng">📄 PKL</button>` : `<button class="btn-card btn-zalo" style="background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;" onclick="printSingleBienBan('${item.idPo}')" title="In Biên Bản Giao Nhận Hàng">📄 BB</button>`}
              ${cleanPhone ? `<a href="tel:${cleanPhone}" class="btn-card btn-call" title="Gọi liên hệ kho">📞</a>` : ""}
            </div>
          </div>
        `;
      }).join("");

      const moreBtnHtml = hasMore
        ? `<button class="load-more-btn" onclick="loadMoreCards()">Xem thêm (${filteredShipments.length - renderLimit} lô hàng nữa)...</button>`
        : "";

      container.innerHTML = cardsHtml + moreBtnHtml;
    }

    function loadMoreCards() {
      renderLimit += state.viewMode === "table" ? 300 : 60;
      renderShipmentList();
    }

    // =========================================================================
    // 7. FILTER UI ACTIONS
    // =========================================================================
    function updateQuickDateChipLabels() {
      const realToday = getTodayISO();
      const d_prev = addDaysISO(realToday, -1);
      const d1 = addDaysISO(realToday, 1);
      const d2 = addDaysISO(realToday, 2);
      const d3 = addDaysISO(realToday, 3);
      const elToday = document.getElementById("chipToday") || document.querySelector('[data-date-mode="today"]');
      const elTom = document.getElementById("chipTomorrow");
      const elD2 = document.getElementById("chipD2");
      const elD3 = document.getElementById("chipD3");
      const elYest = document.getElementById("chipYesterday") || document.querySelector('[data-date-mode="yesterday"]');
      if (elToday) elToday.textContent = `📅 Hôm nay (${realToday.slice(8, 10)}/${realToday.slice(5, 7)})`;
      if (elTom) elTom.textContent = `⏭️ Ngày mai (${d1.slice(8, 10)}/${d1.slice(5, 7)})`;
      if (elD2) elD2.textContent = `📆 Ngày D+2 (${d2.slice(8, 10)}/${d2.slice(5, 7)})`;
      if (elD3) elD3.textContent = `📆 Ngày D+3 (${d3.slice(8, 10)}/${d3.slice(5, 7)})`;
      if (elYest) elYest.textContent = `⏮️ Hôm qua (${d_prev.slice(8, 10)}/${d_prev.slice(5, 7)})`;
      const serverDateEl = document.getElementById("serverDateInfo");
      if (serverDateEl) serverDateEl.textContent = `Mốc dữ liệu API: ${formatIsoToVN(state.apiDate || realToday)}`;
    }
    updateQuickDateChipLabels();

    // Tự động kiểm tra sang ngày mới khi người dùng mở lại điện thoại hoặc tab trình duyệt
    let lastSeenDateISO = getTodayISO();
    function checkCalendarDateRollover() {
      const currentToday = getTodayISO();
      if (currentToday !== lastSeenDateISO) {
        console.log("[Calendar] Phát hiện sang ngày mới:", lastSeenDateISO, "->", currentToday);
        lastSeenDateISO = currentToday;
        state.apiDate = currentToday;
        if (state.dateMode === "today") {
          state.fromDate = currentToday;
          state.toDate = currentToday;
          const elFrom = document.getElementById("filterFromDate");
          const elTo = document.getElementById("filterToDate");
          if (elFrom) elFrom.value = currentToday;
          if (elTo) elTo.value = currentToday;
        }
        updateQuickDateChipLabels();
        refreshData(false);
      } else {
        updateQuickDateChipLabels();
      }
    }
    document.addEventListener("visibilitychange", () => {
      if (document.visibilityState === "visible") checkCalendarDateRollover();
    });
    window.addEventListener("focus", checkCalendarDateRollover);
    setInterval(checkCalendarDateRollover, 60000);

    function setQuickDate(mode) {
      state.dateMode = mode;
      const today = getTodayISO();
      state.apiDate = today;
      updateQuickDateChipLabels();

      if (mode === "today") {
        state.fromDate = today;
        state.toDate = today;
      } else if (mode === "tomorrow") {
        state.fromDate = addDaysISO(today, 1);
        state.toDate = addDaysISO(today, 1);
      } else if (mode === "d2") {
        state.fromDate = addDaysISO(today, 2);
        state.toDate = addDaysISO(today, 2);
      } else if (mode === "d3") {
        state.fromDate = addDaysISO(today, 3);
        state.toDate = addDaysISO(today, 3);
      } else if (mode === "yesterday") {
        state.fromDate = addDaysISO(today, -1);
        state.toDate = addDaysISO(today, -1);
      } else if (mode === "3days") {
        state.fromDate = addDaysISO(today, -1);
        state.toDate = addDaysISO(today, 3);
      }

      document.getElementById("filterFromDate").value = state.fromDate;
      document.getElementById("filterToDate").value = state.toDate;

      const singleLbl = document.getElementById("chipSingleDateLabel");
      if (singleLbl) singleLbl.textContent = "🎯 Chọn ngày";

      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.dateMode === mode);
      });

      if (mode !== "all" && mode !== "unset") {
        ensureDateCyclesLoaded(state.fromDate, state.toDate);
      }
      applyFilters();
    }

    function openQuickSingleDatePicker(e) {
      const inp = document.getElementById("quickSingleDatePicker");
      if (!inp) return;
      if (!inp.value && state.fromDate) {
        inp.value = state.fromDate;
      }
      try {
        if (typeof inp.showPicker === "function") {
          inp.showPicker();
          return;
        }
      } catch (err) {}
      // Fallback cho trình duyệt cũ không hỗ trợ showPicker: mở bảng lọc nâng cao và focus vào ô Từ ngày
      const adv = document.getElementById("advancedFilters");
      if (adv && !adv.classList.contains("open")) {
        adv.classList.add("open");
      }
      const fromInp = document.getElementById("filterFromDate");
      if (fromInp) fromInp.focus();
    }

    function onQuickSingleDatePick(isoVal) {
      if (!isoVal) return;
      state.dateMode = "single";
      state.fromDate = isoVal;
      state.toDate = isoVal;
      document.getElementById("filterFromDate").value = isoVal;
      document.getElementById("filterToDate").value = isoVal;

      const singleLbl = document.getElementById("chipSingleDateLabel");
      if (singleLbl) {
        singleLbl.textContent = `🎯 Ngày ${isoVal.slice(8, 10)}/${isoVal.slice(5, 7)}`;
      }
      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => {
        btn.classList.toggle("active", btn.dataset.dateMode === "single");
      });

      ensureDateCyclesLoaded(isoVal, isoVal);
      applyFilters();
    }

    function onCustomDateChange(which) {
      const fromInp = document.getElementById("filterFromDate");
      const toInp = document.getElementById("filterToDate");
      const prevMode = state.dateMode;
      state.dateMode = "custom";

      if (which === "from" && fromInp.value) {
        // If user just switched from a quick tab to pick a specific date, or if ToDate < FromDate, sync ToDate = FromDate
        if (prevMode !== "custom" || !toInp.value || toInp.value < fromInp.value) {
          toInp.value = fromInp.value;
        }
      } else if (which === "to" && toInp.value) {
        if (!fromInp.value || fromInp.value > toInp.value) {
          fromInp.value = toInp.value;
        }
      }

      state.fromDate = fromInp.value;
      state.toDate = toInp.value;

      const singleLbl = document.getElementById("chipSingleDateLabel");
      if (singleLbl) {
        if (state.fromDate && state.fromDate === state.toDate) {
          singleLbl.textContent = `🎯 Ngày ${state.fromDate.slice(8, 10)}/${state.fromDate.slice(5, 7)}`;
        } else {
          singleLbl.textContent = "🎯 Chọn ngày";
        }
      }

      document.querySelectorAll("#quickDateTabs .chip").forEach(btn => {
        btn.classList.toggle("active", state.fromDate === state.toDate && btn.dataset.dateMode === "single");
      });

      ensureDateCyclesLoaded(state.fromDate, state.toDate);
      applyFilters();
    }

    function setXuatNhapFilter(xn) {
      if (state.bulkMode && xn !== state.xuatNhap) {
        state.bulkMode = false;
        state.bulkCarrier = null;
        if (state.bulkSelectedIds) state.bulkSelectedIds.clear();
        closeBulkDropdown();
      }
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

    function toggleContSizeFilter(size) {
      if (state.contSize === size) {
        state.contSize = "ALL";
        showToast("Đã bỏ lọc kích thước Cont (Hiển thị tất cả 20' & 40')", "success");
      } else {
        state.contSize = size;
        showToast(`Đang lọc Cont ${size}'`, "success");
      }
      applyFilters();
    }

    let searchDebounceTimer = null;
    function onSearchInput() {
      const val = document.getElementById("searchInput").value;
      state.searchQuery = val;
      document.getElementById("btnClearSearch").style.display = val ? "flex" : "none";
      if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(() => {
        applyFilters();
      }, 100);
    }

    function clearSearch() {
      if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
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
      state.contSize = "ALL";
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
      badge.textContent = isXuat ? "📤 HÀNG XUẤT" : "📥 HÀNG NHẬP";

      document.getElementById("modalTitleText").textContent = `Lô #${po.idPo} (${po.chuHangPo || ""})`;
      if (isXuat) {
        document.getElementById("modalCustomerName").textContent = `👤 ${po.tenKhachPo || "Khách hàng"}`;
        const cangDenSub = po.tinhXuatHangPo ? ` • Cảng đến: ${po.tinhXuatHangPo}` : "";
        document.getElementById("modalRouteSub").textContent =
          `Tàu: ${po.tauPo || "--"} (${po.chuyenPo || "--"}) • Ngày tàu: ${formatIsoToVN(po.ngayTauChayPo)} • Loại: ${Number(po.cont40Po) > 0 ? "40'" : "20'"}${cangDenSub}`;
      } else {
        const khoDisplay = (po.khoNhanHangPo && po.khoNhanHangPo !== ".") ? po.khoNhanHangPo : "Chưa cập nhật Kho";
        document.getElementById("modalCustomerName").textContent = `🏭 Kho: ${khoDisplay}`;
        document.getElementById("modalRouteSub").textContent =
          `👤 Khách: ${po.tenKhachPo || "--"} • Tàu: ${po.tauPo || "--"} (${po.chuyenPo || "--"}) • Cảng: ${po.cangDenPo || "--"} • Loại: ${Number(po.cont40Po) > 0 ? "40'" : "20'"}`;
      }

      // Toggle Section 2: Export (Bãi/Cảng & Kết hợp) vs Import (Thông tin Tàu & Cảng)
      const secXuat = document.getElementById("secXuatPort");
      const secNhap = document.getElementById("secNhapVessel");
      if (secXuat) secXuat.style.display = isXuat ? "block" : "none";
      if (secNhap) secNhap.style.display = isXuat ? "none" : "block";

      document.getElementById("lblNgayTraHang").textContent =
        isXuat ? "📅 Ngày Đóng Hàng (ngayTraHang)" : "📅 Ngày Giao / Trả Hàng (ngayTraHang)";

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
      document.getElementById("inp_tinhXuatHangPo").value = po.tinhXuatHangPo || "";
      document.getElementById("inp_hinhThucKHopPo").value = po.hinhThucKHopPo || "";
      document.getElementById("inp_portTimePo").value = po.portTimePo || "";

      // Populate Import Vessel & Port fields
      document.getElementById("inp_tauPo").value = po.tauPo || "";
      document.getElementById("inp_chuyenPo").value = po.chuyenPo || "";
      document.getElementById("inp_cangDenPo").value = po.cangDenPo || "";
      const isUnsetVesselDate = !po.ngayTauChayPo || po.ngayTauChayPo === "2021-11-11" || po.ngayTauChayPo === "1970-01-01";
      document.getElementById("inp_ngayTauChayPo").value = isUnsetVesselDate ? "" : po.ngayTauChayPo;

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

      // Khóa cứng số cont, số seal ở Tab Nhập
      const contInp = document.getElementById("inp_soContPo");
      const sealInp = document.getElementById("inp_soSealPo");
      if (contInp) {
        contInp.readOnly = !isXuat;
        contInp.style.backgroundColor = !isXuat ? "#f1f5f9" : "";
        contInp.style.cursor = !isXuat ? "not-allowed" : "";
        contInp.title = !isXuat ? "Số cont hàng nhập là dữ liệu cứng từ hãng tàu, không được sửa" : "";
      }
      if (sealInp) {
        sealInp.readOnly = !isXuat;
        sealInp.style.backgroundColor = !isXuat ? "#f1f5f9" : "";
        sealInp.style.cursor = !isXuat ? "not-allowed" : "";
        sealInp.title = !isXuat ? "Số seal hàng nhập là dữ liệu cứng từ hãng tàu, không được sửa" : "";
      }

      // Configure Modal Footer Buttons for Export vs Import
      const btnDeliv = document.getElementById("btnModalDelivery");
      if (btnDeliv) btnDeliv.style.display = isXuat ? "none" : "inline-flex";
      const btnTb = document.getElementById("btnModalTb");
      if (btnTb) btnTb.textContent = isXuat ? "🚛 Thông tin xe" : "💬 Copy TB";
      const btnPkl = document.getElementById("btnModalPkl");
      if (btnPkl) btnPkl.style.display = isXuat ? "inline-flex" : "none";
      document.getElementById("editModal").classList.add("open");
    }

    function updateModalDriverInfo(plate) {
      const box = document.getElementById("driverInfoBox");
      if (!box) return;
      const drv = getDriverForPlate(plate);
      if (drv) {
        const vBadge = drv.vendor ? `[${drv.vendor}]` : "";
        box.style.display = "block";
        box.innerHTML = `👨‍✈️ Tài xế <b>${vBadge}</b>: <strong>${drv.name || "Chưa cập nhật"}</strong>${drv.phone ? ` • 📞 <a href="tel:${drv.phone}" style="color:#0369a1;text-decoration:underline;">${drv.phone}</a>` : ""}${drv.cccd ? `<div style="font-size:11px;color:#475569;margin-top:2px;">CCCD: ${drv.cccd}${drv.gplx ? ` • GPLX: ${drv.gplx}` : ""}</div>` : ""}`;
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
        ? `Gợi ý số xe (Ưu tiên ĐVVT: ${curVendor})`
        : `Gợi ý biển số xe (${matches.length} xe)`;

      dropdown.innerHTML = `
        <div class="ac-header">
          <span>🚛 ${headerTitle}</span>
          <span onclick="hideAllAutocomplete()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(t => {
          const isMatchVendor = curVendor && t.vendor === curVendor;
          const drv = getDriverForPlate(t.plate);
          const vLabel = (drv && drv.vendor) || t.vendor || "";
          const subLabel = drv && drv.name
            ? `${drv.name}${drv.phone ? ` (${drv.phone})` : ""}${vLabel ? ` • ${vLabel}` : ""}`
            : vLabel;
          return `
            <div class="ac-item ${isMatchVendor ? 'same-vendor' : ''}" onclick="selectTruckSuggestion('${t.plate}', '${vLabel.replace(/'/g, "\\'")}')">
              <span>🚛 ${t.plate}</span>
              ${subLabel ? `<span class="ac-sub ${isMatchVendor ? 'match-vendor' : ''}">${isMatchVendor ? '★ ' : ''}${subLabel}</span>` : ''}
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
          <span>🏢 Chọn Đơn Vị Vận Tải (${matches.length})</span>
          <span onclick="hideAllAutocomplete()" style="cursor:pointer;color:#2563eb;">Đóng ✕</span>
        </div>
        ${matches.map(v => `
          <div class="ac-item" onclick="selectVendorSuggestion('${v.replace(/'/g, "\\'")}')">
            <span>🏢 ${v}</span>
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
          sizeMismatch = " (Cont 40' trên lệnh 20'!)";
        } else if (Number(currentEditingPo.cont40Po) === 1 && CHECK_CONT_20.includes(prefix5)) {
          sizeMismatch = " (Cont 20' trên lệnh 40'!)";
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

      const drv = getDriverForPlate(raw);
      if (drv && drv.vendor) {
        const vendorInp = document.getElementById("inp_donViVcPo");
        if (!vendorInp.value || vendorInp.value !== drv.vendor) {
          vendorInp.value = drv.vendor;
        }
      } else {
        // Replicate gimax.js searchCities: if license plate is in Vietsun fleet, auto-fill VIETSUN
        const matchedVs = DEFAULT_XE_VIETSUN.find(x => x === raw || (raw.length >= 4 && x.endsWith(raw)));
        if (matchedVs && raw.length >= 5) {
          const vendorInp = document.getElementById("inp_donViVcPo");
          if (!vendorInp.value || vendorInp.value === "VIETSUN") {
            vendorInp.value = "VIETSUN";
          }
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
      if (!isXuat && (fieldName === "soContPo" || fieldName === "soSealPo")) {
        return; // Khóa cứng cont/seal hàng nhập
      }
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
        } else if (fieldName === "ngayTauChayPo") {
          const vnDate = rawVal ? formatIsoToPayloadDate(rawVal) : "";
          const kieuNgay = isXuat ? "8" : "9";
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|${kieuNgay}<<|20<<|${idPo}<<|ngayTauChayPo<<|${codePo}`,
            thongtin: vnDate
          });
          currentEditingPo.ngayTauChayPo = rawVal || "";
        } else if (fieldName === "soXePo") {
          rawVal = rawVal.toUpperCase();
          // 1. Luôn lưu Số xe chắc chắn bằng lệnh b18 chuẩn
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|50<<|${idPo}<<|soXePo<<|${codePo}`,
            thongtin: rawVal
          });
          currentEditingPo.soXePo = rawVal;

          // 2. Gửi thêm b18newtruck để TMS nhận diện xe Vietsun
          let htmlResp = "";
          try {
            htmlResp = await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18newtruck<<|4<<|50<<|${idPo}<<|soXePo<<|${codePo}`,
              thongtin: rawVal
            });
          } catch (_) {}

          const curDvInp = document.getElementById("inp_donViVcPo");
          saveRecentTruck(rawVal, curDvInp ? curDvInp.value : "");

          // Tự động nhận diện Vendor nếu có
          let autoDv = "";
          if (htmlResp && htmlResp.includes("reload_input_ajax")) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlResp, "text/html");
            const kqDv = doc.getElementById(`kq_donViVcPo_${idPo}`);
            if (kqDv && kqDv.textContent && kqDv.textContent.trim()) {
              autoDv = kqDv.textContent.trim().toUpperCase();
            }
          }
          if (!autoDv && rawVal) {
            const drv = getDriverForPlate(rawVal);
            if (drv && drv.vendor) autoDv = drv.vendor;
            else if (DEFAULT_XE_VIETSUN.includes(rawVal)) autoDv = "VIETSUN";
          }
          if (autoDv && curDvInp && (!curDvInp.value || curDvInp.value === "VIETSUN" || curDvInp.value !== autoDv)) {
            curDvInp.value = autoDv;
            currentEditingPo.donViVcPo = autoDv;
            curDvInp.classList.add("saved");
            await postCrudUpdate({
              TenDangNhap: state.username,
              userString: state.userString,
              cv: 3,
              loai: 1,
              iddt: 1,
              cot: `b18<<|4<<|30<<|${idPo}<<|donViVcPo<<|${codePo}`,
              thongtin: autoDv
            });
          }
        } else if (fieldName === "donViVcPo") {
          rawVal = rawVal.toUpperCase();
          await postCrudUpdate({
            TenDangNhap: state.username,
            userString: state.userString,
            cv: 3,
            loai: 1,
            iddt: 1,
            cot: `b18<<|4<<|30<<|${idPo}<<|donViVcPo<<|${codePo}`,
            thongtin: rawVal
          });
          currentEditingPo.donViVcPo = rawVal;
        } else {
          // Map field lengths exactly as gimax.js
          const lengthMap = {
            soSealPo: 50,
            donViVcPo: 30,
            noiLayContPo: 100,
            cangDiPo: 100,
            tinhXuatHangPo: 50,
            hinhThucKHopPo: 50,
            portTimePo: 20,
            tauPo: 30,
            chuyenPo: 30,
            cangDenPo: 30,
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
        showToast(`Đã lưu ${fieldName} lên hệ thống TMS!`, "success");
      } catch (err) {
        console.error(err);
        inpEl.classList.remove("saving");
        showToast(`Lỗi lưu ${fieldName}: ${err.message}`, "error");
      }
    }

    async function saveAllChanges(e) {
      if (e) e.preventDefault();
      if (!currentEditingPo) return;
      const btn = document.getElementById("btnSaveAll");
      btn.disabled = true;
      btn.textContent = "⏳ Đang lưu lên TMS...";

      try {
        const isXuat = currentEditingPo.xuatNhapPo === "X";
        const fieldsToCheck = isXuat
          ? [
              "ngayTraHang", "soContPo", "soSealPo", "soXePo", "donViVcPo",
              "noiLayContPo", "cangDiPo", "tinhXuatHangPo", "hinhThucKHopPo", "portTimePo",
              "khoNhanHangPo", "tinhNhanPo", "diaChiNhanPo", "lienHeNhanPo"
            ]
          : [
              "ngayTraHang", "soContPo", "soSealPo", "soXePo", "donViVcPo",
              "khoNhanHangPo", "tinhNhanPo", "diaChiNhanPo", "lienHeNhanPo"
            ];

        for (const f of fieldsToCheck) {
          const inp = document.getElementById(`inp_${f}`);
          if (!inp) continue;
          const val = inp.value.trim();
          const curVal = (f === "ngayTraHang" || f === "ngayTauChayPo")
            ? (currentEditingPo[f] === "2021-11-11" ? "" : (currentEditingPo[f] || ""))
            : (currentEditingPo[f] || "");
          if (val !== curVal || dirtyFields.has(f)) {
            await syncSingleFieldToServer(f);
          }
        }

        showToast(`Đã đồng bộ hoàn tất lô #${currentEditingPo.idPo}!`, "success");
        setTimeout(() => closeEditModal(), 350);
      } finally {
        btn.disabled = false;
        btn.textContent = "💾 Lưu Cập Nhật Lên TMS";
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

      // Lookup Vietsun / Vendor driver details by license plate
      const drv = getDriverForPlate(soXe);
      const tenLaiXe = drv ? drv.name : (po.taiXePo || "");
      const sdtLaiXe = drv ? drv.phone : (po.sdtTaiXePo || "");

      let cccdLaiXe = "     Nơi cấp: ";
      if (drv && drv.cccd) {
        if (drv.cccd.includes("Nơi cấp")) {
          cccdLaiXe = drv.cccd.replace(/\s*Nơi cấp:/i, "     Nơi cấp:");
        } else {
          cccdLaiXe = `${drv.cccd}     Nơi cấp: Cục QLHC & TTXH`;
        }
      }

      let gplxLaiXe = "           Nơi cấp:";
      if (drv && drv.gplx && drv.gplx !== "Đang Cập Nhật") {
        if (drv.gplx.includes("Nơi cấp")) {
          gplxLaiXe = drv.gplx.replace(/\s*Nơi cấp:/i, "           Nơi cấp:");
        } else {
          gplxLaiXe = `${drv.gplx}           Nơi cấp:SỞ GTVT`;
        }
      }

      if (loai === 1) {
        // Icon 💬: Thông báo hàng đến (Hàng Nhập) hoặc Thông tin đóng hàng (Hàng Xuất)
        if (!isXuat) {
          return (
            `THÔNG BÁO HÀNG ĐẾN:\n` +
            `- Tên khách: ${khach}\n` +
            `- Kho trả: ${kho}\n` +
            `- Địa chỉ: ${diachi}\t- Tỉnh: ${tinh}\n` +
            `- Liên hệ: ${lienhe}\n` +
            `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
            `- Tên tàu: ${tau}\t\t\t\t- Số chuyến: ${chuyen}\n` +
            `- Ngày giao: ${ngayGiao}`
          );
        } else {
          return (
            `THÔNG TIN ĐÓNG HÀNG NGÀY ${ngayGiao}:\n` +
            `- Khách hàng: ${khach}\n` +
            `- Kho ĐÓNG: ${kho}\n` +
            `- Địa chỉ: ${diachi}\n` +
            `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
            `- Nơi cấp rỗng: ${noiCapRong}\n` +
            `- Thời gian dự kiến tới kho: \n` +
            `- Số xe: ${soXe}\n` +
            `- Tên Lái Xe: ${tenLaiXe}\n` +
            `- Số CMND: ${cccdLaiXe}\n` +
            `- Số GPLX: ${gplxLaiXe}\n` +
            `- SĐT: ${sdtLaiXe}`
          );
        }
      }

      // Default loai === 2 (Icon 🚛 chiếc xe): THÔNG TIN GIAO HÀNG / ĐÓNG HÀNG
      if (isXuat) {
        return (
          `THÔNG TIN ĐÓNG HÀNG NGÀY ${ngayGiao}:\n` +
          `- Khách hàng: ${khach}\n` +
          `- Kho ĐÓNG: ${kho}\n` +
          `- Địa chỉ: ${diachi}\n` +
          `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
          `- Nơi cấp rỗng: ${noiCapRong}\n` +
          `- Thời gian dự kiến tới kho: \n` +
          `- Số xe: ${soXe}\n` +
          `- Tên Lái Xe: ${tenLaiXe}\n` +
          `- Số CMND: ${cccdLaiXe}\n` +
          `- Số GPLX: ${gplxLaiXe}\n` +
          `- SĐT: ${sdtLaiXe}`
        );
      }
      return (
        `THÔNG TIN GIAO HÀNG:\n` +
        `- Kho GIAO: ${kho}\n` +
        `- Địa chỉ: ${diachi}\t- Tỉnh: ${tinh}\n` +
        `- Liên hệ: ${lienhe}\n` +
        `- Số cont: ${soCont}\t\t- Số seal: ${soSeal}\n` +
        `- Ngày GIAO: ${ngayGiao}\n` +
        `- Số xe: ${soXe}\n` +
        `- Tên Lái Xe: ${tenLaiXe}\n` +
        `- SĐT: ${sdtLaiXe}\n` +
        `- Thời gian tới kho: `
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
        showToast("Không thể tự động copy, vui lòng thử lại", "error");
      }
      document.body.removeChild(ta);
    }

    function copyPoInfoById(idPo, loai = 2) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      const text = buildZaloText(po, loai);
      const drv = getDriverForPlate(po.soXePo);
      const drvNote = drv ? ` (TX: ${drv.name})` : "";
      const label = loai === 1 ? "Thông báo" : "TT Giao hàng";
      copyTextToClipboard(text, `Đã copy ${label} Cont ${po.soContPo || "#" + po.idPo}${drvNote}!`);
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
        cangDiPo: document.getElementById("inp_cangDiPo").value || currentEditingPo.cangDiPo,
        tinhXuatHangPo: document.getElementById("inp_tinhXuatHangPo").value || currentEditingPo.tinhXuatHangPo,
        khoNhanHangPo: document.getElementById("inp_khoNhanHangPo").value || currentEditingPo.khoNhanHangPo,
        tinhNhanPo: document.getElementById("inp_tinhNhanPo").value || currentEditingPo.tinhNhanPo,
        diaChiNhanPo: document.getElementById("inp_diaChiNhanPo").value || currentEditingPo.diaChiNhanPo,
        lienHeNhanPo: document.getElementById("inp_lienHeNhanPo").value || currentEditingPo.lienHeNhanPo
      });
      const text = buildZaloText(livePo, loai);
      const drv = getDriverForPlate(livePo.soXePo);
      const drvNote = drv ? ` (TX: ${drv.name})` : "";
      const label = loai === 1 ? "Thông báo" : "TT Giao hàng";
      copyTextToClipboard(text, `Đã copy ${label} Cont ${livePo.soContPo || "#" + livePo.idPo}${drvNote}!`);
    }

    // =========================================================================
    // 9B. PKL (PACKING LIST) A4 LANDSCAPE GENERATOR & PDF SHARE / PRINT
    // =========================================================================
    const GAS_PKL_WEBAPP_URL = "https://script.google.com/macros/s/AKfycby0p1n6JKiVPJ7BSqEzbGpwDouH0k55PAYif4O7bsVBN4jYGul7700eZavmR5A6vOxF/exec";
    let currentPklPo = null;
    let customGasPdfBase64 = null;
    let vietsunLogoImg = null;

    // Preload Vietsun Logo for PKL Canvas
    (function preloadPklLogo() {
      const img = new Image();
      img.crossOrigin = "anonymous";
      img.onload = () => { vietsunLogoImg = img; };
      img.src = "logoVietsunColor.png";
    })();

    function openPklFromEditModal() {
      if (!currentEditingPo) return;
      const livePo = Object.assign({}, currentEditingPo, {
        ngayTraHang: document.getElementById("inp_ngayTraHang").value || currentEditingPo.ngayTraHang,
        soContPo: document.getElementById("inp_soContPo").value || currentEditingPo.soContPo,
        soSealPo: document.getElementById("inp_soSealPo").value || currentEditingPo.soSealPo,
        soXePo: document.getElementById("inp_soXePo").value || currentEditingPo.soXePo,
        donViVcPo: document.getElementById("inp_donViVcPo").value || currentEditingPo.donViVcPo,
        noiLayContPo: document.getElementById("inp_noiLayContPo").value || currentEditingPo.noiLayContPo,
        cangDiPo: document.getElementById("inp_cangDiPo").value || currentEditingPo.cangDiPo,
        tinhXuatHangPo: document.getElementById("inp_tinhXuatHangPo").value || currentEditingPo.tinhXuatHangPo,
        khoNhanHangPo: document.getElementById("inp_khoNhanHangPo").value || currentEditingPo.khoNhanHangPo,
        tinhNhanPo: document.getElementById("inp_tinhNhanPo").value || currentEditingPo.tinhNhanPo,
        diaChiNhanPo: document.getElementById("inp_diaChiNhanPo").value || currentEditingPo.diaChiNhanPo,
        lienHeNhanPo: document.getElementById("inp_lienHeNhanPo").value || currentEditingPo.lienHeNhanPo
      });
      openPklWithPoObject(livePo);
    }

    function openPklModal(idPo) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      if (!po) return;
      openPklWithPoObject(po);
    }

    function openPklWithPoObject(po) {
      currentPklPo = po;
      customGasPdfBase64 = null;
      document.getElementById("pklModalTitle").textContent = `📄 Phiếu PKL: ${po.soContPo || "#" + po.idPo} (${po.soXePo || "Chưa gán xe"})`;
      document.getElementById("pklModalSub").textContent = `Khách: ${po.tenKhachPo || "--"} • Tàu: ${po.tauPo || "--"} (${po.chuyenPo || "--"})`;
      const statusText = document.getElementById("pklStatusText");
      statusText.style.color = "#047857";
      statusText.textContent = `✅ Đã tạo phiếu PKL trực tiếp từ TMS (${po.donViVcPo || "VIETSUN"} - ${po.soXePo || ""})`;

      renderPklToCanvas(po);
      document.getElementById("pklModal").classList.add("open");
    }

    function closePklModal() {
      document.getElementById("pklModal").classList.remove("open");
    }

    // =========================================================================
    // 9. XÓA BOOKING THỪA (index-crud.php: cv=4, loai=19)
    // =========================================================================
    let pendingDeletePoId = null;

    function openDeleteBookingModal(idPo) {
      const po = allShipments.find(x => String(x.idPo) === String(idPo));
      pendingDeletePoId = idPo;
      const descEl = document.getElementById("deleteModalDesc");
      if (descEl) {
        const info = po
          ? `lô <b>#${po.idPo}</b> (Chuyến: <b>${po.chuyenPo || "--"}</b> • KH: <b>${po.tenKhachPo || "--"}</b>${po.soContPo ? ` • Cont: <b>${po.soContPo}</b>` : ""})`
          : `lô <b>#${idPo}</b>`;
        descEl.innerHTML = `Bạn có chắc chắn muốn xóa booking thừa ${info} không?<br><span style="color:#dc2626;font-size:12px;font-weight:600;">Hành động này sẽ gửi lệnh xóa trực tiếp lên hệ thống TMS Vietsun.</span>`;
      }
      const inp = document.getElementById("deleteConfirmInput");
      if (inp) {
        inp.value = "";
      }
      const btn = document.getElementById("btnConfirmDelete");
      if (btn) {
        btn.disabled = true;
        btn.style.opacity = "0.35";
        btn.style.cursor = "not-allowed";
        btn.textContent = "Xác nhận xóa";
      }
      const modal = document.getElementById("deleteConfirmModal");
      if (modal) {
        modal.classList.add("open");
        setTimeout(() => { if (inp) inp.focus(); }, 120);
      }
    }

    function checkDeleteOkInput(el) {
      const val = (el.value || "").trim().toLowerCase();
      const btn = document.getElementById("btnConfirmDelete");
      if (!btn) return;
      if (val === "ok") {
        btn.disabled = false;
        btn.style.opacity = "1";
        btn.style.cursor = "pointer";
      } else {
        btn.disabled = true;
        btn.style.opacity = "0.35";
        btn.style.cursor = "not-allowed";
      }
    }

    function closeDeleteModal() {
      const modal = document.getElementById("deleteConfirmModal");
      if (modal) modal.classList.remove("open");
      pendingDeletePoId = null;
    }

    async function executeDeleteBooking() {
      const inp = document.getElementById("deleteConfirmInput");
      if (!inp || (inp.value || "").trim().toLowerCase() !== "ok") {
        showToast("Vui lòng gõ chữ 'ok' để xác nhận xóa!", "warning");
        return;
      }
      if (!pendingDeletePoId) return;
      const targetId = pendingDeletePoId;
      const btn = document.getElementById("btnConfirmDelete");
      if (btn) {
        btn.disabled = true;
        btn.textContent = "Đang xóa...";
      }

      try {
        await postCrudUpdate({
          TenDangNhap: state.username,
          userString: state.userString,
          cv: 4,
          loai: 19,
          iddt: 1,
          cot: 0,
          thongtin: String(targetId)
        });

        // Xóa khỏi danh sách bộ nhớ
        allShipments = allShipments.filter(x => String(x.idPo) !== String(targetId));
        filteredShipments = filteredShipments.filter(x => String(x.idPo) !== String(targetId));

        closeDeleteModal();
        if (currentEditingPo && String(currentEditingPo.idPo) === String(targetId)) {
          closeEditModal();
        }
        applyFilters();
        showToast(`Đã xóa booking thừa #${targetId} thành công!`, "success");
      } catch (err) {
        console.error("Lỗi khi xóa booking:", err);
        showToast("Lỗi khi xóa booking: " + (err.message || "Không thể kết nối TMS"), "error");
      } finally {
        if (btn) {
          btn.textContent = "Xác nhận xóa";
          btn.disabled = false;
        }
      }
    }

    function drawUnderlinedText(ctx, text, x, y, align = "left") {
      ctx.textAlign = align;
      ctx.fillText(text, x, y);
      const metrics = ctx.measureText(text);
      let startX = x;
      if (align === "center") startX = x - metrics.width / 2;
      else if (align === "right") startX = x - metrics.width;
      ctx.beginPath();
      ctx.moveTo(startX, y + 4);
      ctx.lineTo(startX + metrics.width, y + 4);
      ctx.lineWidth = 1.6;
      ctx.strokeStyle = ctx.fillStyle;
      ctx.stroke();
    }

    function renderPklToCanvas(po, targetCanvas) {
      const canvas = targetCanvas || document.getElementById("pklCanvas");
      if (!canvas) return;
      const W = 1684;
      const H = 1190;
      canvas.width = W;
      canvas.height = H;
      const ctx = canvas.getContext("2d");

      // White A4 Landscape background
      ctx.fillStyle = "#ffffff";
      ctx.fillRect(0, 0, W, H);

      const vendor = (po.donViVcPo || "VIETSUN").toUpperCase().trim();
      const plate = (po.soXePo || "").toUpperCase().trim();
      const cangXep = (po.cangDiPo || "BẾN NGHÉ").toUpperCase().trim();
      const cangDo = (po.tinhXuatHangPo || po.cangDenPo || "").toUpperCase().trim();
      let chuHang = (po.chuHangPo || "").toUpperCase().trim();
      if (chuHang && !chuHang.startsWith("NHAT VIET")) {
        chuHang = "NHAT VIET - " + chuHang;
      }
      const ngayTau = (po.ngayTauChayPo && po.ngayTauChayPo !== "2021-11-11") ? formatIsoToVN(po.ngayTauChayPo) : "";
      const tau = (po.tauPo || "").toUpperCase().trim();
      const chuyen = (po.chuyenPo || "").toUpperCase().trim();
      const soCont = (po.soContPo || "").toUpperCase().trim();
      const soSeal = (po.soSealPo || "").trim();
      const c20 = Number(po.cont20Po) > 0 ? "1" : "0";
      const c40 = Number(po.cont40Po) > 0 ? "1" : "0";
      const hangHoa = (po.tenHangHoaPo || "").trim();
      const trongLuong = po.trongLuongPo ? Number(po.trongLuongPo).toFixed(2) : "";
      const tenKhach = (po.tenKhachPo || "").trim();
      const ngayHa = (po.ngayTraHang && po.ngayTraHang !== "2021-11-11") ? formatIsoToVN(po.ngayTraHang) : formatIsoToVN(getTodayISO());

      // 1. Top-Right Red Fleet & Plate Header
      ctx.fillStyle = "#ea0000";
      ctx.font = 'bold 36px "Times New Roman", Times, serif';
      ctx.textAlign = "right";
      ctx.fillText(`${vendor}${plate ? " - " + plate : ""}`, 1580, 140);

      // 2. Top-Left Vietsun Logo & Company Block
      if (vietsunLogoImg && vietsunLogoImg.complete && vietsunLogoImg.naturalWidth > 0) {
        ctx.drawImage(vietsunLogoImg, 145, 210, 305, 155);
      } else {
        ctx.fillStyle = "#1e3a8a";
        ctx.font = 'bold 54px "Times New Roman", Times, serif';
        ctx.textAlign = "center";
        ctx.fillText("VSC", 295, 295);
        ctx.font = '18px Arial, sans-serif';
        ctx.fillText("VIETSUN CORPORATION", 295, 335);
      }

      ctx.fillStyle = "#000000";
      ctx.textAlign = "left";
      ctx.font = 'bold 17.5px "Times New Roman", Times, serif';
      ctx.fillText("CÔNG TY CP NHẬT VIỆT- VIETSUN CORP", 104, 460);
      ctx.fillText("HÃNG TÀU NHẬT VIỆT - VIETSUN LINES", 104, 490);
      ctx.font = 'bold 16.5px "Times New Roman", Times, serif';
      ctx.fillText("Số 13-15, Đường B4, P. An Khánh, TP.HCM", 104, 520);
      ctx.fillText("TEL: 028 3826 9453 FAX: 028.39433114", 104, 550);
      ctx.fillText("WEBSITE : www.vietsunlogistic.com", 104, 580);

      // 3. Center-Right Header Block
      ctx.fillStyle = "#000000";
      ctx.font = 'bold 31px "Times New Roman", Times, serif';
      ctx.textAlign = "center";
      ctx.fillText("BẢNG KÊ HÀNG HOÁ VẬN CHUYỂN NỘI ĐỊA", 1074, 235);

      ctx.font = '25px "Times New Roman", Times, serif';
      ctx.fillText("(CONTAINER PACKING LIST)", 1074, 274);

      ctx.font = 'bold 25px "Times New Roman", Times, serif';
      drawUnderlinedText(ctx, "KÍNH GỬI CẢNG:", 566, 310, "left");
      drawUnderlinedText(ctx, cangXep, 828, 310, "left");

      ctx.font = 'bold 22px "Times New Roman", Times, serif';
      drawUnderlinedText(ctx, "CHI PHÍ HẠ TẠI CẢNG THANH TOÁN SAU CHO CÔNG TY CP NHẬT VIỆT", 566, 348, "left");

      ctx.font = 'italic 23px "Times New Roman", Times, serif';
      ctx.textAlign = "left";
      ctx.fillText("(Note : Vui lòng đối chiếu trên hệ thống với hãng tàu )", 566, 382);

      // 4. Upper Vessel & Route Table (x: 564..1584, y: 412..576)
      const ux = [564, 716, 824, 970, 1126, 1278, 1418, 1584];
      const uy = [412, 439, 466, 493, 520, 548, 576];

      ctx.strokeStyle = "#000000";
      ctx.lineWidth = 1.6;
      ctx.strokeRect(ux[0], uy[0], ux[7] - ux[0], uy[6] - uy[0]);

      // Horizontal lines in Upper Table
      for (let r = 1; r <= 4; r++) {
        ctx.beginPath();
        ctx.moveTo(ux[0], uy[r]);
        ctx.lineTo(ux[7], uy[r]);
        ctx.stroke();
      }
      // Sub-row line between SOC and COC (from ux[1] to ux[5])
      ctx.beginPath();
      ctx.moveTo(ux[1], uy[5]);
      ctx.lineTo(ux[5], uy[5]);
      ctx.stroke();

      // Vertical lines in Upper Table
      // Col 1 border at ux[2]=824 (all rows)
      ctx.beginPath(); ctx.moveTo(ux[2], uy[0]); ctx.lineTo(ux[2], uy[6]); ctx.stroke();
      // Sub-col at ux[1]=716 (rows 4..6)
      ctx.beginPath(); ctx.moveTo(ux[1], uy[4]); ctx.lineTo(ux[1], uy[6]); ctx.stroke();
      // 20'/40'/45' dividers at ux[3]=970, ux[4]=1126 (rows 3..6)
      ctx.beginPath(); ctx.moveTo(ux[3], uy[3]); ctx.lineTo(ux[3], uy[6]); ctx.stroke();
      ctx.beginPath(); ctx.moveTo(ux[4], uy[3]); ctx.lineTo(ux[4], uy[6]); ctx.stroke();
      // Col border at ux[5]=1278 (all rows)
      ctx.beginPath(); ctx.moveTo(ux[5], uy[0]); ctx.lineTo(ux[5], uy[6]); ctx.stroke();
      // Col border at ux[6]=1418 (rows 0..3)
      ctx.beginPath(); ctx.moveTo(ux[6], uy[0]); ctx.lineTo(ux[6], uy[3]); ctx.stroke();

      // Upper Table Text
      ctx.fillStyle = "#000000";
      ctx.font = 'italic 18px "Times New Roman", Times, serif';
      ctx.textAlign = "left";
      ctx.fillText("Chủ hàng", ux[0] + 6, uy[0] + 20);
      ctx.fillText("Dự kiến xuất tàu", ux[0] + 6, uy[1] + 20);
      ctx.fillText("Số chuyến", ux[0] + 6, uy[2] + 20);
      ctx.fillText("Loại container", ux[0] + 6, uy[3] + 20);
      ctx.fillText("Số lượng", ux[0] + 6, uy[4] + 34);

      ctx.fillText("Ngày tàu chạy", ux[5] + 6, uy[0] + 20);
      ctx.fillText("Cảng xếp", ux[5] + 6, uy[1] + 20);
      ctx.fillText("Cảng dỡ", ux[5] + 6, uy[2] + 20);

      ctx.textAlign = "center";
      ctx.fillText("20'", (ux[2] + ux[3]) / 2, uy[3] + 20);
      ctx.fillText("40'", (ux[3] + ux[4]) / 2, uy[3] + 20);
      ctx.fillText("45'", (ux[4] + ux[5]) / 2, uy[3] + 20);
      ctx.fillText("CLOSING TIME", (ux[5] + ux[7]) / 2, uy[3] + 20);
      ctx.fillText("SOC", (ux[1] + ux[2]) / 2, uy[4] + 20);
      ctx.fillText("COC", (ux[1] + ux[2]) / 2, uy[5] + 20);

      // Upper Table Values
      const midValX = (ux[2] + ux[5]) / 2;
      const rightValX = (ux[6] + ux[7]) / 2;

      ctx.font = 'bold 19px "Times New Roman", Times, serif';
      ctx.fillStyle = "#000000";
      ctx.fillText(chuHang, midValX, uy[0] + 20);
      ctx.fillText(cangXep, rightValX, uy[1] + 20);
      ctx.fillText("TRƯỚC: .....H..... NGÀY ...../...../202", (ux[5] + ux[7]) / 2, uy[4] + 35);

      ctx.fillStyle = "#ea0000";
      ctx.fillText(ngayTau, rightValX, uy[0] + 20);
      ctx.fillText(tau, midValX, uy[1] + 20);
      ctx.fillText(chuyen, midValX, uy[2] + 20);
      ctx.fillText(cangDo, rightValX, uy[2] + 20);

      // 5. Middle Container Table (x: 100..1584, y: 602..718)
      const mx = [100, 166, 428, 564, 644, 738, 824, 1136, 1278, 1412, 1584];
      const my = [602, 629, 656, 687, 718];

      ctx.strokeStyle = "#000000";
      ctx.lineWidth = 1.6;
      ctx.strokeRect(mx[0], my[0], mx[10] - mx[0], my[4] - my[0]);

      // Horizontal lines in Middle Table
      // Subheader line under "Loại container" (mx[3]..mx[6])
      ctx.beginPath(); ctx.moveTo(mx[3], my[1]); ctx.lineTo(mx[6], my[1]); ctx.stroke();
      for (let r = 2; r <= 3; r++) {
        ctx.beginPath(); ctx.moveTo(mx[0], my[r]); ctx.lineTo(mx[10], my[r]); ctx.stroke();
      }

      // Vertical lines in Middle Table
      for (let c = 1; c <= 9; c++) {
        const startY = (c === 4 || c === 5) ? my[1] : my[0];
        ctx.beginPath(); ctx.moveTo(mx[c], startY); ctx.lineTo(mx[c], my[4]); ctx.stroke();
      }

      // Middle Table Headers
      ctx.fillStyle = "#000000";
      ctx.font = 'bold italic 18px "Times New Roman", Times, serif';
      ctx.textAlign = "center";
      ctx.fillText("STT", (mx[0] + mx[1]) / 2, my[0] + 34);
      ctx.fillText("Số container", (mx[1] + mx[2]) / 2, my[0] + 34);
      ctx.fillText("Số seal", (mx[2] + mx[3]) / 2, my[0] + 34);
      ctx.fillText("Loại container", (mx[3] + mx[6]) / 2, my[0] + 20);
      ctx.fillText("20", (mx[3] + mx[4]) / 2, my[1] + 20);
      ctx.fillText("40", (mx[4] + mx[5]) / 2, my[1] + 20);
      ctx.fillText("45'", (mx[5] + mx[6]) / 2, my[1] + 20);
      ctx.fillText("Hàng Hoá", (mx[6] + mx[7]) / 2, my[0] + 34);
      ctx.fillText("Trọng lượng", (mx[7] + mx[8]) / 2, my[0] + 34);
      ctx.fillText("DG - U.N.", (mx[8] + mx[9]) / 2, my[0] + 24);
      ctx.fillText("CLASS", (mx[8] + mx[9]) / 2, my[0] + 45);
      ctx.fillText("DG - U.N. Number", (mx[9] + mx[10]) / 2, my[0] + 34);

      // Middle Table Row 1 Data
      ctx.font = '22px "Times New Roman", Times, serif';
      ctx.fillStyle = "#000000";
      ctx.fillText("1", (mx[0] + mx[1]) / 2, my[2] + 23);

      ctx.font = '24px "Times New Roman", Times, serif';
      ctx.fillStyle = "#ea0000";
      ctx.fillText(soCont, (mx[1] + mx[2]) / 2, my[2] + 23);
      ctx.fillText(soSeal, (mx[2] + mx[3]) / 2, my[2] + 23);
      ctx.fillText(c20, (mx[3] + mx[4]) / 2, my[2] + 23);
      ctx.fillText(c40, (mx[4] + mx[5]) / 2, my[2] + 23);
      ctx.fillText("0", (mx[5] + mx[6]) / 2, my[2] + 23);
      ctx.fillText(hangHoa, (mx[6] + mx[7]) / 2, my[2] + 23);
      ctx.fillText(trongLuong, (mx[7] + mx[8]) / 2, my[2] + 23);

      // 6. Bottom Notes & Signatures
      ctx.fillStyle = "#000000";
      ctx.font = '17.5px "Times New Roman", Times, serif';
      ctx.textAlign = "left";
      ctx.fillText("Khách hàng chịu trách nhiệm về việc kê khai packing list.", 104, 762);
      ctx.fillText("Nội dung trên packing list và trên chi tiết khách hàng cung cấp lập Bill phải trùng khớp, chính xác với thực tế.", 104, 790);
      ctx.fillText("Không nhận các mặt hàng thuộc danh sách hàng \"cấm vận chuyển\" theo Luật pháp Việt Nam.", 104, 818);
      ctx.fillText("Không nhận vận chuyển các mặt hàng : Gỗ tròn, gỗ tự nhiên, sắt / thép vụn – phế liệu - than đóng xá, than bùn.", 104, 846);
      ctx.fillText("Hàng nguy hiểm phải thông báo trước, được khai báo và tuân theo quy định vận chuyển.", 104, 874);
      ctx.fillText(tenKhach, 104, 902);

      // Right Box: Giờ, ngày container hạ cảng
      ctx.strokeRect(1136, 742, 448, 29);
      ctx.font = 'italic 19px "Times New Roman", Times, serif';
      drawUnderlinedText(ctx, "Giờ, ngày container hạ cảng", 1360, 763, "center");

      ctx.font = 'bold 18px "Times New Roman", Times, serif';
      ctx.textAlign = "center";
      ctx.fillText(ngayHa, 1360, 804);

      // Signatures
      ctx.font = 'bold 19px "Times New Roman", Times, serif';
      ctx.fillText("ĐẠI DIỆN CHỦ HÀNG", 368, 926);
      ctx.fillText(`ĐẠI DIỆN CẢNG ${cangXep}`, 1298, 926);

      ctx.font = '18px "Times New Roman", Times, serif';
      ctx.fillText("(ký và ghi rõ họ tên)", 368, 954);
      ctx.fillText("(ký và ghi rõ họ tên)", 1298, 954);
    }

    // Pure-JS A4 Landscape PDF Builder from Canvas (Zero external libraries)
    function buildPklPdfBlob() {
      if (customGasPdfBase64) {
        const arr = customGasPdfBase64.split(",");
        const bstr = atob(arr[1] || arr[0]);
        const u8 = new Uint8Array(bstr.length);
        for (let i = 0; i < bstr.length; i++) u8[i] = bstr.charCodeAt(i);
        return new Blob([u8], { type: "application/pdf" });
      }

      const canvas = document.getElementById("pklCanvas");
      const dataUrl = canvas.toDataURL("image/jpeg", 0.95);
      const base64 = dataUrl.split(",")[1];
      const binStr = atob(base64);
      const jpgBytes = new Uint8Array(binStr.length);
      for (let i = 0; i < binStr.length; i++) {
        jpgBytes[i] = binStr.charCodeAt(i);
      }

      const w = canvas.width;
      const h = canvas.height;
      const pageW = 841.89;
      const pageH = 595.28;

      const enc = new TextEncoder();
      const chunks = [];
      const offsets = [0];
      let byteLen = 0;

      function pushStr(s) {
        const b = enc.encode(s);
        chunks.push(b);
        byteLen += b.length;
      }
      function pushBytes(b) {
        chunks.push(b);
        byteLen += b.length;
      }

      pushStr("%PDF-1.4\n%\xFF\xFF\xFF\xFF\n");

      // 1: Catalog
      offsets[1] = byteLen;
      pushStr("1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n");

      // 2: Pages
      offsets[2] = byteLen;
      pushStr("2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n");

      // 3: Page (A4 Landscape)
      offsets[3] = byteLen;
      pushStr(`3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${pageW} ${pageH}] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>\nendobj\n`);

      // 4: Image XObject
      offsets[4] = byteLen;
      pushStr(`4 0 obj\n<< /Type /XObject /Subtype /Image /Width ${w} /Height ${h} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ${jpgBytes.length} >>\nstream\n`);
      pushBytes(jpgBytes);
      pushStr("\nendstream\nendobj\n");

      // 5: Content Stream
      const contentStream = `q ${pageW} 0 0 ${pageH} 0 0 cm /Im0 Do Q\n`;
      offsets[5] = byteLen;
      pushStr(`5 0 obj\n<< /Length ${contentStream.length} >>\nstream\n${contentStream}endstream\nendobj\n`);

      // xref
      const xrefOffset = byteLen;
      pushStr("xref\n0 6\n0000000000 65535 f \n");
      for (let i = 1; i <= 5; i++) {
        pushStr(String(offsets[i]).padStart(10, "0") + " 00000 n \n");
      }
      pushStr(`trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n${xrefOffset}\n%%EOF`);

      return new Blob(chunks, { type: "application/pdf" });
    }

    function getPklFileName() {
      if (!currentPklPo) return "PackingList.pdf";
      const plate = (currentPklPo.soXePo || "XE").replace(/[^A-Z0-9]/gi, "");
      const cont = (currentPklPo.soContPo || currentPklPo.idPo || "CONT").replace(/[^A-Z0-9]/gi, "");
      return `PKL_${plate}_${cont}.pdf`;
    }

    async function sharePklPdf() {
      try {
        const blob = buildPklPdfBlob();
        const fileName = getPklFileName();
        const file = new File([blob], fileName, { type: "application/pdf" });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
          await navigator.share({
            files: [file],
            title: `Phiếu PKL ${currentPklPo?.soContPo || ""}`,
            text: `Phiếu PKL hạ cảng xe ${currentPklPo?.soXePo || ""} - Cont ${currentPklPo?.soContPo || ""}`
          });
          showToast("Đã mở bảng chia sẻ PDF!", "success");
        } else {
          downloadPklPdf();
        }
      } catch (err) {
        if (err && err.name !== "AbortError") {
          downloadPklPdf();
        }
      }
    }

    function downloadPklPdf() {
      const blob = buildPklPdfBlob();
      const fileName = getPklFileName();
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 3000);
      showToast(`Đã tải file ${fileName}!`, "success");
    }

    function printPklCanvas() {
      const canvas = document.getElementById("pklCanvas");
      const imgData = canvas.toDataURL("image/png");
      const w = window.open("", "_blank");
      if (!w) {
        downloadPklPdf();
        return;
      }
      w.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>${getPklFileName()}</title>
          <style>
            @page { size: A4 landscape; margin: 0; }
            body { margin: 0; display: flex; align-items: center; justify-content: center; background: #fff; }
            img { width: 100vw; height: 100vh; object-fit: contain; }
          </style>
        </head>
        <body onload="setTimeout(() => { window.print(); }, 200);">
          <img src="${imgData}" />
        </body>
        </html>
      `);
      w.document.close();
    }

    // =========================================================================
    // 9C. BULK PRINTING: PKL (TAB HÀNG XUẤT) & BIÊN BẢN (TAB HÀNG NHẬP)
    //     Tự động nhận diện Form Biên Bản theo KHO NHẬN (khoNhanHangPo)
    // =========================================================================
    const PROVINCE_VN_LABEL = {
      "HO CHI MINH": "Tp. Hồ Chí Minh",
      "BINH DUONG": "Bình Dương",
      "DONG NAI": "Đồng Nai",
      "LONG AN": "Long An",
      "TAY NINH": "Tây Ninh",
      "BA RIA - VUNG TAU": "Bà Rịa - Vũng Tàu",
      "BINH PHUOC": "Bình Phước",
      "BINH THUAN": "Bình Thuận",
      "LAM DONG": "Lâm Đồng",
      "TIEN GIANG": "Tiền Giang",
      "BEN TRE": "Bến Tre",
      "TRA VINH": "Trà Vinh",
      "VINH LONG": "Vĩnh Long",
      "DONG THAP": "Đồng Tháp",
      "AN GIANG": "An Giang",
      "KIEN GIANG": "Kiên Giang",
      "CAN THO": "Cần Thơ",
      "HAU GIANG": "Hậu Giang",
      "SOC TRANG": "Sóc Trăng",
      "BAC LIEU": "Bạc Liêu",
      "CA MAU": "Cà Mau",
      "THONG BAO SAU": "Thông báo sau"
    };

    function toCleanUpperCode(str) {
      return String(str || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d")
        .replace(/Đ/g, "D")
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, "");
    }

    function getDefault4OrangesHtml() {
      const baseOrigin = window.location.origin;
      return `<div class="bb-4oranges">
  <div class="thna-header">
    <div class="thna-logo-col">
      <img src="${baseOrigin}/logoThna.png" alt="THNA Logo" class="thna-logo-img" />
    </div>
    <div class="thna-info-col">
      <div class="thna-title">THNA GROUP</div>
      <div class="thna-sub"><span style="color:#0000FF;">The No.1 Vietnam </span><span style="color:#222222;">CaCO3 powder</span></div>
      <div class="thna-line">2<sup>nd</sup> Floor, Trung Hai Nghe An Building, Quy Hop, Nghe An, Vietnam</div>
      <div class="thna-line">Phone:&nbsp; +84 238 398 2777 &ndash;&nbsp;&nbsp; Email:&nbsp;<span class="thna-link">info@thnagroup.com</span></div>
      <div class="thna-line">Hotline: +84 97&nbsp; 109 2777 &ndash;&nbsp; Website:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="thna-link">thnagroup.com</span></div>
    </div>
  </div>
  <div class="thna-divider">-----------------------------------------------------------------------------------------------------------------------------------------</div>
  <div class="thna-doc-title">BIÊN BẢN GIAO NHẬN HÀNG HÓA</div>
  <div class="thna-doc-date">Ngày {{NGAY}}&nbsp;&nbsp;tháng {{THANG}}&nbsp;&nbsp;&nbsp;năm {{NAM}}</div>
  <div class="thna-section">
    <div class="thna-bold-12">BÊN GIAO HÀNG: CÔNG TY CỔ PHẦN TRUNG HẢI- NGHỆ AN GROUP.</div>
    <div class="thna-bold-12" style="padding-left:28px;">(THNA GROUP)</div>
    <div class="thna-driver-line">
      <span style="display:inline-block;width:58%;padding-left:48px;">Tên Tài xế:</span>
      <span>Biển số xe:</span>
    </div>
  </div>
  <div class="thna-section" style="margin-top:14px;">
    <div class="thna-bold-13">BÊN NHẬN HÀNG: CÔNG TY 4ORANGES CO.,LTD</div>
    <div class="thna-bold-13">Địa chỉ:&nbsp; Lô C-02-1, KCN Đức&nbsp; Hoà 1, Ấp 5, Xã Mỹ Hạnh, Tỉnh Tây Ninh, Việt Nam.</div>
  </div>
  <table class="thna-table">
    <thead>
      <tr>
        <th style="width:7.6%;">STT</th>
        <th style="width:23.7%;">Số cont/Seal</th>
        <th style="width:15.6%;">Số lượng</th>
        <th style="width:26.55%;">Khối lượng ( tấn)</th>
        <th style="width:26.55%;">Mã Hàng</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:center;">1</td>
        <td style="text-align:center;">{{SO_CONT}}</td>
        <td></td>
        <td></td>
        <td style="text-align:center;">THNA07</td>
      </tr>
      <tr>
        <td style="text-align:center;">2</td>
        <td style="text-align:center;padding-left:22px;">{{SO_SEAL}}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <div class="thna-weight-words">Khối lượng hàng bằng chữ: </div>
  <div class="thna-signatures">
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên nhận hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên giao hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
  </div>
</div>`;
    }

    function getDefaultStandardHtml() {
      const baseOrigin = window.location.origin;
      return `<div class="bb-vietsun">
  <div class="vs-div-picture">
    <img class="vs-watermark-img" src="${baseOrigin}/vietsunwatermark2.png" alt="" />
    <img class="vs-seal-img" src="${baseOrigin}/seal_vietsun.png" alt="" />
    <div class="vs-content-layer">
      <table class="vs-header-table">
        <tr>
          <td style="width:170px;vertical-align:top;text-align:center;">
            <img src="${baseOrigin}/logo-vietsunnew.png" alt="Company Logo" style="height:75px;width:auto;" />
          </td>
          <td style="text-align:center;font-family:Arial,Helvetica,sans-serif;">
            <div style="font-size:19px;font-weight:700;line-height:1.2;">VIETSUN LOGISTICS - VIETSUN CORPORATION</div>
            <div style="font-size:12.5px;line-height:1.35;margin-top:4px;">
              Số 13-15 đường B4, Phường An Khánh, Thành Phố Hồ Chí Minh<br/>
              Tel : 08 38269 539 - 38269 540 -38269 541 - Ext :307; Fax: (84.8) 8269542<br/>
              Website : www.vietsunlogistic.com<br/><br/>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:center;padding-top:4px;">
            <span style="font-size:23pt;font-weight:700;">BIÊN BẢN GIAO NHẬN HÀNG</span>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:right;padding-top:2px;">
            <span style="font-size:12.5pt;font-style:italic;">{{NGAY_THANG_NAM}}</span>
          </td>
        </tr>
      </table>
      <p style="margin:8px 0 4px;line-height:1.75;">
        Căn cứ vào hợp đồng vận chuyển (hoặc booking note) số ................... ngày ...... tháng ... năm....<br/>
        <span style="font-weight:700;text-decoration:underline;">Chúng tôi gồm:</span>
      </p>
      <table class="vs-info-table">
        <tbody>
          <tr>
            <th colspan="4" style="text-align:left;"><span style="text-decoration:underline;">BÊN GIAO</span>: CÔNG TY CỔ PHẦN NHẬT VIỆT</th>
          </tr>
          <tr>
            <td style="width:15%;">Địa chỉ</td>
            <td colspan="3">:Số 13-15 đường B4, Phường An Khánh, Thành Phố Hồ Chí Minh</td>
          </tr>
          <tr>
            <td>Điện thoại</td>
            <td colspan="3">: 08 38269539 </td>
          </tr>
          <tr>
            <td>Đại diện</td>
            <td style="width:53%;">: Ông......................................................................</td>
            <td style="width:11%;">Chức vụ</td>
            <td>: NV Lái xe</td>
          </tr>
          <tr>
            <td>Số xe vận tải</td>
            <td colspan="3">:..............................................................................</td>
          </tr>
          <tr>
            <th style="text-align:left;text-decoration:underline;vertical-align:top;">BÊN NHẬN</th>
            <th colspan="3" style="text-align:left;text-transform:uppercase;">: {{KHO_NHAN}}</th>
          </tr>
          <tr>
            <td style="vertical-align:top;">Địa chỉ</td>
            <td colspan="3">: {{DIA_CHI}}</td>
          </tr>
          <tr>
            <td>Đại diện</td>
            <td>: Ông/Bà................................................................</td>
            <td>Chức vụ</td>
            <td>:.....................................</td>
          </tr>
        </tbody>
      </table>
      <div style="margin:4px 0;">Cùng thống nhất lập biên bản giao nhận hàng hóa trên với nội dung như sau:</div>
      <table class="vs-bangND">
        <thead>
          <tr>
            <th style="width:28%;">SỐ CONT</th>
            <th style="width:22%;">SỐ SEAL</th>
            <th style="width:25%;">HÀNG HÓA</th>
            <th style="width:25%;">GHI CHÚ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{SO_CONT}}</td>
            <td>{{SO_SEAL}}</td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td><span style="color:#ffffff;">.</span></td>
          </tr>
        </tbody>
      </table>
      <p style="margin:8px 0 12px;line-height:1.8;">
        <span style="font-weight:700;text-decoration:underline;">Hình thức giao nhận: </span> Nguyên Cont, nguyên Seal. Bên giao không chịu trách nhiệm về số lượng, trọng lượng hàng hóa bên trong Cont.<br/>
        <span style="font-weight:700;text-decoration:underline;">Địa điểm giao, nhận hàng: </span>..............................................................................................................<br/>
        <span style="font-weight:700;text-decoration:underline;">Chứng từ kèm theo: </span>........................................................................................................................<br/>
        Hai bên đã kiểm tra kỹ số Cont, số Seal như trên là đúng và cùng thống nhất giao nhận hàng với nội dung như trên. Biên bản được lập thành hai (02) bản, nội dung và giá trị như nhau mỗi bên giữ một (01) bản ./
      </p>
      <div class="vs-sig-row">
        <div class="vs-sig-col">ĐẠI DIỆN BÊN GIAO</div>
        <div class="vs-sig-col"></div>
        <div class="vs-sig-col">ĐẠI DIỆN BÊN NHẬN</div>
      </div>
    </div>
  </div>
</div>`;
    }

    function getDefaultVietXoJumboHtml() {
      const baseOrigin = window.location.origin;
      return `<div class="bb-4oranges">
  <div class="thna-header" style="display:flex;align-items:center;gap:16px;padding-left:4px;margin-bottom:6px;">
    <div class="thna-logo-col" style="flex-shrink:0;">
      <img src="${baseOrigin}/logoThna.png" alt="THNA Logo" class="thna-logo-img" style="width:170px;max-height:110px;object-fit:contain;display:block;" />
    </div>
    <div class="thna-info-col" style="flex:1;">
      <div style="margin:2px 0;line-height:1.35;text-align:center;"><span style="font-weight:700;font-size:12pt;">CÔNG TY CỔ PHẦN TRUNG HẢI - NGHỆ AN GROUP</span></div>
      <div style="margin:2px 0;line-height:1.35;"><span style="font-weight:700;font-size:11pt;">Trụ sở: Khối &nbsp;Bắc Hồ - TT. Quỳ Hợp – Huyện Quỳ Hợp – Tỉnh &nbsp;Nghệ An</span></div>
      <div style="margin:2px 0;line-height:1.35;"><span style="font-weight:700;font-size:11pt;">ĐT VP:</span><span style="font-size:11pt;"> 038 398 2777 – Fax: 038 398 3777 – Hotline: 097 555 6777</span></div>
      <div style="margin:2px 0;line-height:1.35;"><span style="font-weight:700;font-size:11pt;">VP Đại diện:</span><span style="font-size:11pt;"> Lô 30 Vinaconex9 – Đại Lộ Lê-nin – TP.Vinh – Nghệ An</span></div>
      <div style="margin:2px 0;line-height:1.35;"><span style="font-weight:700;font-size:11pt;">Nhà máy</span><span style="font-size:11pt;">: KCN Châu Quang – Quỳ Hợp – Nghệ An</span></div>
      <div style="margin:2px 0;line-height:1.35;"><span style="font-size:11pt;">Email: thna.caco3@gmail.com - Website : trunghainastone.com</span></div>
    </div>
  </div>
  <div style="border-bottom:3px double #000;margin:8px 0 12px;height:2px;"></div>
  <div style="margin:4px 0;line-height:1.6;text-align:center;"><span style="font-weight:700;font-size:16pt;">BIÊN BẢN GIAO NHẬN</span></div>
  <div style="margin:4px 0;line-height:1.6;text-align:center;"><span style="font-weight:700;font-size:12pt;">Số: XK &nbsp; &nbsp; &nbsp;/T{{THANG}}-26</span></div>
  <div style="margin:4px 0;line-height:1.6;"><span style="font-weight:700;font-size:11pt;">BÊN GIAO HÀNG: CTY CP TRUNG HẢI NGHỆ AN GROUP</span></div>
  <div style="margin:4px 0;line-height:1.6;"><span style="font-size:12pt;">Địa chỉ: Khối Bắc Hồ, Thị trấn Quỳ Hợp, Huyện Quỳ Hợp, Tỉnh Nghệ An</span></div>
  <div style="margin:4px 0;line-height:1.6;font-size:12pt;">
    <span style="display:inline-block;width:52%;">Đại diện:</span>
    <span>Chức vụ:</span>
  </div>
  <div style="margin:4px 0;line-height:1.6;"><span style="font-weight:700;font-size:12pt;">BÊN NHẬN HÀNG: : CÔNG TY CỔ PHẦN THÉP VIỆT XÔ THÀNH PHỐ HỒ CHÍ MINH</span></div>
  <div style="margin:4px 0;line-height:1.6;"><span style="font-size:13pt;">Địa chỉ : Ấp Công Bính, Xã Tân Tây, Tỉnh Tây Ninh</span></div>
  <div style="margin:4px 0;line-height:1.6;font-size:12pt;">
    <span style="display:inline-block;width:52%;">Đại diện:</span>
    <span>Chức vụ:</span>
  </div>
  <div style="margin:4px 0;line-height:1.6;"><span style="font-size:14pt;">Hai bên giao nhận hàng bột đá &nbsp;vôi trắng siêu mịn với nội dung như sau:</span></div>
  <table class="thna-table" style="width:100%;border-collapse:collapse;margin:12px 0;">
    <tr>
      <th style="width:7.26%;border:1px solid #000;padding:5px 6px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">STT</span></th>
      <th style="width:20.89%;border:1px solid #000;padding:5px 6px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">Mặt hàng</span></th>
      <th style="width:16.92%;border:1px solid #000;padding:5px 6px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">Trọng lượng<br/>(tấn/bao)</span></th>
      <th style="width:11.64%;border:1px solid #000;padding:5px 6px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">Số bao</span></th>
      <th style="width:15.10%;border:1px solid #000;padding:5px 6px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">Số lượng<br/>(tấn)</span></th>
      <th style="width:28.19%;border:1px solid #000;padding:5px 6px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">Số cont/seal</span></th>
    </tr>
    <tr>
      <td style="width:7.26%;border:1px solid #000;padding:8px 6px;vertical-align:middle;text-align:center;"><span style="font-size:14pt;">1</span></td>
      <td style="width:20.89%;border:1px solid #000;padding:8px 6px;vertical-align:middle;text-align:center;"><span style="font-size:14pt;">THNA25 T</span></td>
      <td style="width:16.92%;border:1px solid #000;padding:8px 6px;vertical-align:middle;text-align:center;"><span style="font-size:14pt;">1.35</span></td>
      <td style="width:11.64%;border:1px solid #000;padding:8px 6px;vertical-align:middle;text-align:center;"><span style="font-size:14pt;">20</span></td>
      <td style="width:15.10%;border:1px solid #000;padding:8px 6px;vertical-align:middle;text-align:center;"><span style="font-size:14pt;">27</span></td>
      <td style="width:28.19%;border:1px solid #000;padding:8px 6px;vertical-align:middle;text-align:center;"><span style="font-size:13pt;">{{SO_CONT}}<br/>seal: {{SO_SEAL}}</span></td>
    </tr>
  </table>
  <div style="margin:6px 0;line-height:1.6;"><span style="font-style:italic;font-size:14pt;">Trọng lượng bằng chữ: Hai mươi bảy tấn chẵn./</span></div>
  <div style="margin:6px 0;line-height:1.6;text-align:right;padding-right:24px;"><span style="font-style:italic;font-size:13pt;">Hôm nay, Ngày {{NGAY}} Tháng {{THANG}} Năm {{NAM}}</span></div>
  <table style="width:100%;border-collapse:collapse;margin:14px 0;">
    <tr>
      <td style="width:33.33%;border:none;padding:6px 8px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">ĐẠI DIỆN GIAO HÀNG</span></td>
      <td style="width:33.33%;border:none;padding:6px 8px;vertical-align:middle;text-align:center;"></td>
      <td style="width:33.33%;border:none;padding:6px 8px;vertical-align:middle;text-align:center;"><span style="font-weight:700;font-size:12pt;">ĐẠI DIỆN NHẬN HÀNG</span></td>
    </tr>
  </table>
</div>`;
    }

    function getBuiltInBbTemplates() {
      return [
        {
          id: "4ORANGES",
          name: "Form 4 Oranges (THNA Group)",
          short_badge: "4Oranges",
          customer_keywords: "Cty Trung Hải",
          keywords: "4 ORANGES, 4ORANGES",
          html_content: getDefault4OrangesHtml(),
          is_active: 1,
          sort_order: 1
        },
        {
          id: "VIETXO",
          name: "Form Việt Xô - Jumbo (THNA Group)",
          short_badge: "ViệtXô",
          customer_keywords: "Cty Trung Hải",
          keywords: "Cty Việt Xô, VIỆT XÔ, VIET XO",
          html_content: getDefaultVietXoJumboHtml(),
          is_active: 1,
          sort_order: 2
        },
        {
          id: "STANDARD",
          name: "Form Chuẩn Vietsun",
          short_badge: "BB",
          customer_keywords: "",
          keywords: "*",
          html_content: getDefaultStandardHtml(),
          is_active: 1,
          sort_order: 999
        }
      ];
    }

    let bbTemplatesList = [];
    let currentEditingBbTplId = "4ORANGES";
    let isBbLivePreviewMode = false;
    let rawDraftBbHtmlBeforePreview = "";

    function initBbTemplatesLocal() {
      const builtIns = getBuiltInBbTemplates();
      let saved = [];
      try {
        saved = JSON.parse(localStorage.getItem("vs_bb_templates_v1") || "[]");
      } catch (_) {}

      const map = new Map();
      builtIns.forEach(t => map.set(t.id, { ...t }));
      if (Array.isArray(saved)) {
        saved.forEach(t => {
          if (t && t.id && t.html_content) {
            map.set(t.id, { ...t, is_active: Number(t.is_active ?? 1) });
          }
        });
      }
      bbTemplatesList = Array.from(map.values()).sort((a, b) => Number(a.sort_order || 10) - Number(b.sort_order || 10));
      updateBbTplCountBadge();
    }

    async function loadBbTemplatesFromServer() {
      initBbTemplatesLocal();
      try {
        const resp = await fetch("/api/index.php?route=bb_templates");
        if (!resp.ok) return;
        const data = await resp.json();
        if (data && data.ok && Array.isArray(data.templates) && data.templates.length > 0) {
          const builtIns = getBuiltInBbTemplates();
          const map = new Map();
          builtIns.forEach(t => map.set(t.id, { ...t }));
          data.templates.forEach(t => {
            if (t && t.id && t.html_content) {
              map.set(t.id, { ...t, is_active: Number(t.is_active ?? 1) });
            }
          });
          bbTemplatesList = Array.from(map.values()).sort((a, b) => Number(a.sort_order || 10) - Number(b.sort_order || 10));
          try {
            localStorage.setItem("vs_bb_templates_v1", JSON.stringify(bbTemplatesList));
          } catch (_) {}
          updateBbTplCountBadge();
          if (state.viewMode === "table" && state.xuatNhap === "N") {
            renderShipmentList();
          }
        }
      } catch (_) {}
    }

    let bbTemplateCacheByKho = new Map();

    function updateBbTplCountBadge() {
      bbTemplateCacheByKho.clear();
      const el = document.getElementById("bbTplCountBadge");
      if (el) el.textContent = bbTemplatesList.length || 2;
    }

    // Khởi tạo ngay danh sách Form BB khi nạp trang
    initBbTemplatesLocal();
    setTimeout(() => { loadBbTemplatesFromServer(); }, 600);

    function detectBbTemplate(po) {
      if (!bbTemplatesList || bbTemplatesList.length === 0) initBbTemplatesLocal();
      const rawKho = (po && po.khoNhanHangPo ? String(po.khoNhanHangPo) : "").trim();
      const rawKhach = (po && po.tenKhachPo ? String(po.tenKhachPo) : "").trim();
      const cacheKey = `${rawKhach}|||${rawKho}`;
      if (bbTemplateCacheByKho.has(cacheKey)) {
        return bbTemplateCacheByKho.get(cacheKey);
      }
      const cleanKho = toCleanUpperCode(rawKho);
      const cleanKhach = toCleanUpperCode(rawKhach);

      if (cleanKho || cleanKhach) {
        for (const tpl of bbTemplatesList) {
          if (tpl.id === "STANDARD" || !Number(tpl.is_active ?? 1)) continue;

          const custKwList = String(tpl.customer_keywords || "")
            .split(",")
            .map(k => k.trim())
            .filter(k => k && k !== "*");
          const khoKwList = String(tpl.keywords || "")
            .split(",")
            .map(k => k.trim())
            .filter(k => k && k !== "*");

          if (custKwList.length === 0 && khoKwList.length === 0) continue;

          // Kiểm tra khớp Khách Hàng (nếu Form có khai báo Từ khóa Khách Hàng)
          let custMatch = true;
          if (custKwList.length > 0) {
            custMatch = custKwList.some(kw => {
              const cKw = toCleanUpperCode(kw);
              return cKw && (cleanKhach.includes(cKw) || rawKhach.toUpperCase().includes(kw.toUpperCase()));
            });
          }

          // Kiểm tra khớp Kho Nhận (nếu Form có khai báo Từ khóa Kho Nhận)
          let khoMatch = true;
          if (khoKwList.length > 0) {
            khoMatch = khoKwList.some(kw => {
              const cKw = toCleanUpperCode(kw);
              return cKw && (cleanKho.includes(cKw) || rawKho.toUpperCase().includes(kw.toUpperCase()));
            });
          }

          if (custMatch && khoMatch) {
            const res = {
              id: tpl.id,
              code: tpl.id,
              name: tpl.name,
              shortBadge: tpl.short_badge || "Form Riêng",
              html_content: tpl.html_content
            };
            bbTemplateCacheByKho.set(cacheKey, res);
            return res;
          }
        }
      }

      const stdTpl = bbTemplatesList.find(t => t.id === "STANDARD") || getBuiltInBbTemplates()[1];
      const stdRes = {
        id: "STANDARD",
        code: "STANDARD",
        name: stdTpl.name || "Form Chuẩn Vietsun",
        shortBadge: stdTpl.short_badge || "BB",
        html_content: stdTpl.html_content
      };
      bbTemplateCacheByKho.set(cacheKey, stdRes);
      return stdRes;
    }

    function renderTemplateWithPoData(htmlContent, po) {
      const baseOrigin = window.location.origin;
      const soCont = escapeAttr((po.soContPo || "").toUpperCase().trim());
      const soSeal = escapeAttr((po.soSealPo || "").trim());
      const hasValidDate = po.ngayTraHang && po.ngayTraHang !== "2021-11-11" && po.ngayTraHang !== "1970-01-01";

      let dd = ".....", mm = ".....", yyyy = String(new Date().getFullYear());
      if (hasValidDate) {
        const parts = String(po.ngayTraHang).split("-");
        if (parts.length === 3) {
          yyyy = parts[0];
          mm = parts[1];
          dd = parts[2];
        }
      }

      const vsDateStr = hasValidDate
        ? `Ngày ${dd} tháng ${mm}  năm ${yyyy} `
        : `Ngày ..... tháng ..... năm .......`;
      const khoNhan = escapeAttr((po.khoNhanHangPo || po.tenKhachPo || "").trim());
      const khachHang = escapeAttr((po.tenKhachPo || "").trim());
      const rawDiaChi = (po.diaChiNhanPo || "").trim();
      const rawTinh = (po.tinhNhanPo || "").toUpperCase().trim();
      const tinhLabel = PROVINCE_VN_LABEL[rawTinh] || po.tinhNhanPo || "";
      const fullDiaChi = escapeAttr([rawDiaChi, tinhLabel].filter(Boolean).join(", "));
      const soXe = escapeAttr((po.soXePo || "").toUpperCase().trim());
      const donViVc = escapeAttr((po.donViVcPo || "").toUpperCase().trim());
      const drvInfo = getDriverForPlate(po.soXePo);
      const taiXe = escapeAttr(drvInfo && drvInfo.name ? drvInfo.name : "");
      const sdtTaiXe = escapeAttr(drvInfo && drvInfo.phone ? drvInfo.phone : "");
      const trongLuong = escapeAttr(po.trongLuongPo ? String(po.trongLuongPo) : "");
      const hangHoa = escapeAttr((po.tenHangHoaPo || "").trim());
      const tauChuyen = escapeAttr([po.tauPo, po.chuyenPo].filter(Boolean).join(" / "));

      return String(htmlContent || "")
        .replace(/\{\{BASE_ORIGIN\}\}/g, baseOrigin)
        .replace(/\{\{SO_CONT\}\}/g, soCont)
        .replace(/\{\{SO_SEAL\}\}/g, soSeal)
        .replace(/\{\{NGAY\}\}/g, dd)
        .replace(/\{\{THANG\}\}/g, mm)
        .replace(/\{\{NAM\}\}/g, yyyy)
        .replace(/\{\{NGAY_THANG_NAM\}\}/g, vsDateStr)
        .replace(/\{\{KHO_NHAN\}\}/g, khoNhan)
        .replace(/\{\{KHACH_HANG\}\}/g, khachHang)
        .replace(/\{\{DIA_CHI\}\}/g, fullDiaChi)
        .replace(/\{\{TINH\}\}/g, escapeAttr(tinhLabel))
        .replace(/\{\{SO_XE\}\}/g, soXe)
        .replace(/\{\{DON_VI_VC\}\}/g, donViVc)
        .replace(/\{\{TAI_XE\}\}/g, taiXe)
        .replace(/\{\{SDT_TAI_XE\}\}/g, sdtTaiXe)
        .replace(/\{\{TRONG_LUONG\}\}/g, trongLuong)
        .replace(/\{\{HANG_HOA\}\}/g, hangHoa)
        .replace(/\{\{TAU_CHUYEN\}\}/g, tauChuyen);
    }

    // =========================================================================
    // TRÌNH QUẢN LÝ & SOẠN THẢO TRỰC QUAN MẪU BIÊN BẢN (VISUAL BB TEMPLATE MANAGER)
    // =========================================================================
    function openBbTemplateManager(selectId) {
      if (!bbTemplatesList || bbTemplatesList.length === 0) initBbTemplatesLocal();
      const targetId = selectId || currentEditingBbTplId || (bbTemplatesList[0] && bbTemplatesList[0].id) || "4ORANGES";
      selectBbTemplateInEditor(targetId);
      const modal = document.getElementById("bbTemplateModal");
      if (modal) modal.classList.add("open");
    }

    function closeBbTemplateManager() {
      closeAllBbMetaAc();
      const modal = document.getElementById("bbTemplateModal");
      if (modal) modal.classList.remove("open");
      if (isBbLivePreviewMode) {
        isBbLivePreviewMode = false;
      }
    }

    function closeAllBbMetaAc() {
      const d1 = document.getElementById("bbAcCustDropdown");
      const d2 = document.getElementById("bbAcKhoDropdown");
      if (d1) d1.classList.remove("open");
      if (d2) d2.classList.remove("open");
    }

    // Gõ tìm kiếm trực tiếp Khách Hàng & Kho Nhận từ TMS (Hỗ trợ gõ có dấu / không dấu ở bất kỳ vị trí nào)
    function openBbMetaAc(type) {
      const isCust = type === "cust";
      const inp = document.getElementById(isCust ? "bbTplCustomerKeywords" : "bbTplKeywords");
      const drop = document.getElementById(isCust ? "bbAcCustDropdown" : "bbAcKhoDropdown");
      const otherDrop = document.getElementById(isCust ? "bbAcKhoDropdown" : "bbAcCustDropdown");
      if (otherDrop) otherDrop.classList.remove("open");
      if (!inp || !drop || inp.disabled) return;

      // Lấy từ khóa đang gõ sau dấu phẩy cuối cùng
      const parts = inp.value.split(",");
      const lastSeg = (parts[parts.length - 1] || "").trim();
      const qClean = toCleanUpperCode(lastSeg);
      const qLower = lastSeg.toLowerCase();

      // Danh sách Khách hàng đang chọn (để ưu tiên gợi ý Kho nhận thuộc Khách hàng đó)
      const selectedCustKws = (document.getElementById("bbTplCustomerKeywords")?.value || "")
        .split(",")
        .map(k => toCleanUpperCode(k.trim()))
        .filter(Boolean);

      const countMap = new Map();
      const prioritySet = new Set();

      allShipments.forEach(item => {
        if (item.xuatNhapPo !== "N") return;
        const val = (isCust ? item.tenKhachPo : item.khoNhanHangPo || "").trim();
        if (!val || val === ".") return;
        countMap.set(val, (countMap.get(val) || 0) + 1);

        if (!isCust && selectedCustKws.length > 0) {
          const itemCustClean = toCleanUpperCode(item.tenKhachPo || "");
          if (selectedCustKws.some(ck => itemCustClean.includes(ck))) {
            prioritySet.add(val);
          }
        }
      });

      let candidates = Array.from(countMap.keys());
      if (qClean || qLower) {
        candidates = candidates.filter(name => {
          return name.toLowerCase().includes(qLower) || toCleanUpperCode(name).includes(qClean);
        });
      }

      candidates.sort((a, b) => {
        const pA = prioritySet.has(a) ? 1 : 0;
        const pB = prioritySet.has(b) ? 1 : 0;
        if (pA !== pB) return pB - pA;
        return a.localeCompare(b, "vi");
      });

      if (candidates.length === 0) {
        drop.innerHTML = `<div style="padding:8px 10px;font-size:12px;color:#64748b;">Không tìm thấy ${isCust ? 'Khách hàng' : 'Kho nhận'} khớp "${escapeAttr(lastSeg)}" trên TMS (Bạn vẫn có thể tự gõ từ khóa)</div>`;
        drop.classList.add("open");
        return;
      }

      drop.innerHTML = candidates.slice(0, 60).map(name => {
        const cnt = countMap.get(name) || 0;
        const isPrio = prioritySet.has(name);
        return `
          <div class="bb-ac-item" onmousedown="event.preventDefault(); selectBbMetaAcItem('${type}', '${escapeAttr(name)}')">
            <span>${isPrio ? '⭐ ' : (isCust ? '👤 ' : '🏭 ')}${escapeAttr(name)}</span>
            <span style="font-size:11px;color:${isPrio ? '#b45309' : '#64748b'};background:${isPrio ? '#fef3c7' : '#f1f5f9'};padding:1px 6px;border-radius:4px;">
              ${isPrio ? 'Kho của KH đang chọn • ' : ''}${cnt} lô
            </span>
          </div>
        `;
      }).join("");
      drop.classList.add("open");
    }

    function selectBbMetaAcItem(type, chosenName) {
      const isCust = type === "cust";
      const inp = document.getElementById(isCust ? "bbTplCustomerKeywords" : "bbTplKeywords");
      const drop = document.getElementById(isCust ? "bbAcCustDropdown" : "bbAcKhoDropdown");
      if (!inp) return;

      const parts = inp.value.split(",").map(s => s.trim()).filter(Boolean);
      // Thay thế phần tử đang gõ dở cuối cùng bằng tên đã chọn (và thêm dấu phẩy chờ nếu muốn chọn tiếp)
      if (parts.length <= 1) {
        inp.value = chosenName;
      } else {
        parts[parts.length - 1] = chosenName;
        // Loại bỏ trùng lặp
        inp.value = Array.from(new Set(parts)).join(", ");
      }
      if (drop) drop.classList.remove("open");

      // Nếu đang tạo Form Kho Mới mà tên Form vẫn là mặc định, tự động điền gợi ý Tên Form & Nhãn nút
      if (!isCust) {
        const nameInp = document.getElementById("bbTplName");
        const badgeInp = document.getElementById("bbTplBadge");
        if (nameInp && (!nameInp.value || nameInp.value === "Form Kho Mới")) {
          nameInp.value = `Form ${chosenName}`;
        }
        if (badgeInp && (!badgeInp.value || badgeInp.value === "FormRiêng")) {
          badgeInp.value = chosenName.replace(/^(Cty|Công ty|Kho|CN)\s+/i, "").trim().slice(0, 12);
        }
      }
    }

    document.addEventListener("mousedown", function(e) {
      const cInp = document.getElementById("bbTplCustomerKeywords");
      const cDrop = document.getElementById("bbAcCustDropdown");
      const kInp = document.getElementById("bbTplKeywords");
      const kDrop = document.getElementById("bbAcKhoDropdown");
      if (cDrop && cDrop.classList.contains("open") && !cDrop.contains(e.target) && e.target !== cInp) {
        cDrop.classList.remove("open");
      }
      if (kDrop && kDrop.classList.contains("open") && !kDrop.contains(e.target) && e.target !== kInp) {
        kDrop.classList.remove("open");
      }
    });

    function renderBbTemplateSidebar() {
      const listEl = document.getElementById("bbTemplateSidebarList");
      if (!listEl) return;
      listEl.innerHTML = bbTemplatesList.map(tpl => {
        const isAct = tpl.id === currentEditingBbTplId;
        const isStd = tpl.id === "STANDARD";
        let kwText = "Mặc định cho tất cả kho còn lại";
        if (!isStd) {
          const parts = [];
          if (tpl.customer_keywords) parts.push(`👤 KH: ${tpl.customer_keywords}`);
          parts.push(`🏭 Kho: ${tpl.keywords || "(Chưa gán)"}`);
          kwText = parts.join(" • ");
        }
        return `
          <div class="bb-tpl-card ${isAct ? 'active' : ''}" onclick="selectBbTemplateInEditor('${escapeAttr(tpl.id)}')">
            <div class="bb-tpl-card-title">
              <span>📄 ${escapeAttr(tpl.name)}</span>
              <span style="font-size:10.5px;background:${isStd ? '#dbeafe' : '#fef3c7'};color:${isStd ? '#1d4ed8' : '#b45309'};padding:2px 6px;border-radius:5px;">${escapeAttr(tpl.short_badge || 'BB')}</span>
            </div>
            <div class="bb-tpl-card-kw">${escapeAttr(kwText)}</div>
          </div>
        `;
      }).join("");
    }

    function selectBbTemplateInEditor(id) {
      isBbLivePreviewMode = false;
      const btnPrev = document.getElementById("btnBbTogglePreview");
      if (btnPrev) {
        btnPrev.innerHTML = "👁️ Xem thử với dữ liệu thật";
        btnPrev.style.background = "#eff6ff";
        btnPrev.style.color = "#1d4ed8";
        btnPrev.style.borderColor = "#93c5fd";
      }

      const tpl = bbTemplatesList.find(t => t.id === id) || bbTemplatesList[0];
      if (!tpl) return;
      currentEditingBbTplId = tpl.id;
      renderBbTemplateSidebar();

      const nameInp = document.getElementById("bbTplName");
      const badgeInp = document.getElementById("bbTplBadge");
      const custInp = document.getElementById("bbTplCustomerKeywords");
      const kwInp = document.getElementById("bbTplKeywords");
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const btnDel = document.getElementById("btnBbDeleteTpl");
      const btnRest = document.getElementById("btnBbRestoreDefault");

      if (nameInp) nameInp.value = tpl.name || "";
      if (badgeInp) badgeInp.value = tpl.short_badge || "BB";
      if (custInp) {
        custInp.value = tpl.id === "STANDARD" ? "* (Mọi Khách Hàng)" : (tpl.customer_keywords || "");
        custInp.disabled = tpl.id === "STANDARD";
      }
      if (kwInp) {
        kwInp.value = tpl.id === "STANDARD" ? "* (Mọi Kho Nhận)" : (tpl.keywords || "");
        kwInp.disabled = tpl.id === "STANDARD";
      }
      if (canvas) {
        canvas.contentEditable = "true";
        canvas.innerHTML = tpl.html_content || "";
      }
      if (btnDel) {
        btnDel.style.display = tpl.id === "STANDARD" ? "none" : "inline-flex";
      }
      if (btnRest) {
        btnRest.style.display = (tpl.id === "STANDARD" || tpl.id === "4ORANGES" || tpl.id === "VIETXO") ? "inline-flex" : "none";
      }
    }

    function createNewBbTemplate() {
      const newId = "TPL_" + Date.now();
      const baseOrigin = window.location.origin;
      const newTpl = {
        id: newId,
        name: "Form Kho Mới",
        short_badge: "FormRiêng",
        customer_keywords: "",
        keywords: "",
        is_active: 1,
        sort_order: bbTemplatesList.length + 1,
        html_content: `<div class="bb-4oranges">
  <div class="thna-header">
    <div class="thna-logo-col">
      <img src="${baseOrigin}/logo-vietsunnew.png" alt="Logo" class="thna-logo-img" style="max-height:75px;width:auto;" />
    </div>
    <div class="thna-info-col">
      <div class="thna-title" style="color:#1e3a8a;">TÊN ĐƠN VỊ / CHỦ HÀNG</div>
      <div class="thna-line">Địa chỉ: Nhập địa chỉ công ty hoặc bấm nút "📄 Nhập từ Word (.docx)" ở trên</div>
      <div class="thna-line">Điện thoại: .................................</div>
    </div>
  </div>
  <div class="thna-divider">-----------------------------------------------------------------------------------------------------------------------------------------</div>
  <div class="thna-doc-title">BIÊN BẢN GIAO NHẬN HÀNG HÓA</div>
  <div class="thna-doc-date">Ngày {{NGAY}}&nbsp;&nbsp;tháng {{THANG}}&nbsp;&nbsp;&nbsp;năm {{NAM}}</div>
  <div class="thna-section">
    <div class="thna-bold-12">BÊN GIAO HÀNG: CÔNG TY CỔ PHẦN NHẬT VIỆT (VIETSUN)</div>
    <div class="thna-driver-line">
      <span style="display:inline-block;width:58%;">Tên Tài xế: ...................................................</span>
      <span>Biển số xe: {{SO_XE}}</span>
    </div>
  </div>
  <div class="thna-section" style="margin-top:12px;">
    <div class="thna-bold-13">BÊN NHẬN HÀNG: {{KHO_NHAN}}</div>
    <div class="thna-bold-13">Địa chỉ: {{DIA_CHI}}</div>
  </div>
  <table class="thna-table">
    <thead>
      <tr>
        <th style="width:8%;">STT</th>
        <th style="width:25%;">Số cont/Seal</th>
        <th style="width:18%;">Số lượng</th>
        <th style="width:24%;">Khối lượng (tấn)</th>
        <th style="width:25%;">Ghi chú / Mã hàng</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:center;">1</td>
        <td style="text-align:center;">{{SO_CONT}}</td>
        <td></td>
        <td style="text-align:center;">{{TRONG_LUONG}}</td>
        <td style="text-align:center;">{{HANG_HOA}}</td>
      </tr>
      <tr>
        <td style="text-align:center;">2</td>
        <td style="text-align:center;">{{SO_SEAL}}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <div class="thna-signatures">
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên nhận hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
    <div class="thna-sig-col">
      <div class="thna-sig-title">Đại diện bên giao hàng</div>
      <div class="thna-sig-sub">(Ký và ghi rõ họ tên)</div>
    </div>
  </div>
</div>`
      };
      const stdIdx = bbTemplatesList.findIndex(t => t.id === "STANDARD");
      if (stdIdx >= 0) bbTemplatesList.splice(stdIdx, 0, newTpl);
      else bbTemplatesList.push(newTpl);

      selectBbTemplateInEditor(newId);
      const kwInp = document.getElementById("bbTplKeywords");
      if (kwInp) kwInp.focus();
      showToast("Đã tạo Form mới! Hãy gõ tìm Kho Nhận / Khách Hàng hoặc Upload file Word (.docx).", "info");
    }

    function cloneCurrentBbTemplate() {
      const cur = bbTemplatesList.find(t => t.id === currentEditingBbTplId);
      if (!cur) return;
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const currentHtml = isBbLivePreviewMode ? rawDraftBbHtmlBeforePreview : (canvas ? canvas.innerHTML : cur.html_content);
      const newId = "TPL_" + Date.now();
      const cloned = {
        id: newId,
        name: cur.name + " (Bản sao)",
        short_badge: (cur.short_badge || "Form").slice(0, 12) + "2",
        customer_keywords: cur.customer_keywords || "",
        keywords: "",
        is_active: 1,
        sort_order: bbTemplatesList.length + 1,
        html_content: currentHtml
      };
      const stdIdx = bbTemplatesList.findIndex(t => t.id === "STANDARD");
      if (stdIdx >= 0) bbTemplatesList.splice(stdIdx, 0, cloned);
      else bbTemplatesList.push(cloned);

      selectBbTemplateInEditor(newId);
      showToast("Đã nhân bản Form! Hãy gán Từ khóa Kho Nhận mới và bấm Lưu.", "success");
    }

    // =========================================================================
    // BỘ ĐỌC & NHẬN DIỆN FILE WORD (.DOCX) TRỰC TIẾP TRÊN TRÌNH DUYỆT
    // =========================================================================
    let isUploadingDocxAsNewTemplate = false;

    function triggerBbWordDocxUpload(asNewTemplate) {
      isUploadingDocxAsNewTemplate = !!asNewTemplate;
      const inp = document.getElementById("bbWordDocxInput");
      if (inp) {
        inp.value = "";
        inp.click();
      }
    }

    function uint8ToBase64(u8Arr) {
      let binary = "";
      const chunkSize = 8192;
      for (let i = 0; i < u8Arr.length; i += chunkSize) {
        binary += String.fromCharCode.apply(null, u8Arr.subarray(i, i + chunkSize));
      }
      return btoa(binary);
    }

    async function unzipDocxBuffer(arrayBuffer) {
      const view = new DataView(arrayBuffer);
      const bytes = new Uint8Array(arrayBuffer);
      const decoder = new TextDecoder("utf-8");

      let eocdOffset = -1;
      for (let i = bytes.length - 22; i >= Math.max(0, bytes.length - 65557); i--) {
        if (view.getUint32(i, true) === 0x06054b50) {
          eocdOffset = i;
          break;
        }
      }
      if (eocdOffset < 0) throw new Error("File không đúng định dạng Word (.docx)!");

      const cdCount = view.getUint16(eocdOffset + 10, true);
      let cdOffset = view.getUint32(eocdOffset + 16, true);
      const files = {};

      for (let i = 0; i < cdCount; i++) {
        if (view.getUint32(cdOffset, true) !== 0x02014b50) break;
        const method = view.getUint16(cdOffset + 10, true);
        const compSize = view.getUint32(cdOffset + 20, true);
        const nameLen = view.getUint16(cdOffset + 28, true);
        const extraLen = view.getUint16(cdOffset + 30, true);
        const commentLen = view.getUint16(cdOffset + 32, true);
        const localHeaderOffset = view.getUint32(cdOffset + 42, true);

        const fileName = decoder.decode(bytes.subarray(cdOffset + 46, cdOffset + 46 + nameLen));
        cdOffset += 46 + nameLen + extraLen + commentLen;

        if (view.getUint32(localHeaderOffset, true) !== 0x04034b50) continue;
        const localNameLen = view.getUint16(localHeaderOffset + 26, true);
        const localExtraLen = view.getUint16(localHeaderOffset + 28, true);
        const dataStart = localHeaderOffset + 30 + localNameLen + localExtraLen;
        const compBytes = bytes.subarray(dataStart, dataStart + compSize);

        if (method === 0) {
          files[fileName] = compBytes;
        } else if (method === 8) {
          const ds = new DecompressionStream("deflate-raw");
          const writer = ds.writable.getWriter();
          writer.write(compBytes);
          writer.close();
          const resp = new Response(ds.readable);
          const ab = await resp.arrayBuffer();
          files[fileName] = new Uint8Array(ab);
        }
      }
      return files;
    }

    function findBalancedClosingTag(xml, startIdx, tagName) {
      const openRe = new RegExp(`<${tagName}(?:\\s|>|\\/)`, "g");
      const closeStr = `</${tagName}>`;
      let depth = 0;
      let i = startIdx;
      while (i < xml.length) {
        const nextOpen = xml.slice(i).search(openRe);
        const absOpen = nextOpen >= 0 ? i + nextOpen : -1;
        const absClose = xml.indexOf(closeStr, i);
        if (absClose < 0) return -1;

        if (absOpen >= 0 && absOpen < absClose) {
          const gt = xml.indexOf(">", absOpen);
          if (gt > absOpen && xml[gt - 1] === "/") {
            if (depth === 0 && absOpen === startIdx) return gt + 1;
            i = gt + 1;
          } else {
            depth++;
            i = gt + 1;
          }
        } else {
          depth--;
          if (depth <= 0) {
            return absClose + closeStr.length;
          }
          i = absClose + closeStr.length;
        }
      }
      return -1;
    }

    function parseDocxXmlToHtml(rawDocXml, rawRelsXml, mediaBase64Map) {
      const relsMap = {};
      const relMatches = (rawRelsXml || "").match(/<Relationship [^>]+>/g) || [];
      relMatches.forEach(r => {
        const id = (r.match(/Id="([^"]+)"/) || [])[1];
        const target = (r.match(/Target="([^"]+)"/) || [])[1];
        if (id && target) relsMap[id] = target;
      });

      const cleanXml = rawDocXml.replace(/<mc:Fallback>[\s\S]*?<\/mc:Fallback>/g, "");
      const bodyMatch = cleanXml.match(/<w:body>([\s\S]*?)<\/w:body>/);
      const bodyXml = bodyMatch ? bodyMatch[1] : cleanXml;

      function escHtml(s) {
        return String(s || "")
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;");
      }

      function extractRunInfo(rXml, defaultSzHalfPt, defaultBold) {
        const tMatches = rXml.match(/<w:t[^>]*>([\s\S]*?)<\/w:t>/g) || [];
        const tabCount = (rXml.match(/<w:tab\/>/g) || []).length;
        const brCount = (rXml.match(/<w:br\/>/g) || []).length;
        const text = tMatches.map(t => t.replace(/<[^>]+>/g, "")).join("");
        if (!text && !tabCount && !brCount) return null;

        let htmlText = "";
        const innerTokens = rXml.match(/<w:t[^>]*>[\s\S]*?<\/w:t>|<w:tab\/>|<w:br\/>/g) || [];
        innerTokens.forEach(tok => {
          if (tok === "<w:tab/>") htmlText += "\t";
          else if (tok === "<w:br/>") htmlText += "\n";
          else htmlText += escHtml(tok.replace(/<[^>]+>/g, ""));
        });

        const rPr = (rXml.match(/<w:rPr>([\s\S]*?)<\/w:rPr>/) || [])[1] || "";
        const isBold = /<w:b\/>|<w:b w:val="(?!false|0)/.test(rPr) || (defaultBold && !/<w:b w:val="(?:false|0)"/.test(rPr));
        const isItalic = /<w:i\/>|<w:i w:val="(?!false|0)/.test(rPr);
        const isUnderline = /<w:u w:val="(?!none)/.test(rPr) || /<w:rStyle w:val="Hyperlink"\/>/.test(rPr);
        const vertAlign = (rPr.match(/<w:vertAlign w:val="([^"]+)"/) || [])[1] || "";
        const color = (rPr.match(/<w:color w:val="([^"]+)"/) || [])[1] || "";
        const szHalf = (rPr.match(/<w:sz w:val="(\d+)"/) || [])[1]
          || (rPr.match(/<w:szCs w:val="(\d+)"/) || [])[1]
          || defaultSzHalfPt || "26";
        const fontAscii = (rPr.match(/<w:rFonts[^>]*w:ascii="([^"]+)"/) || [])[1] || "";

        const styles = [];
        if (isBold) styles.push("font-weight:700");
        if (isItalic) styles.push("font-style:italic");
        if (isUnderline) styles.push("text-decoration:underline");
        if (color && color !== "auto" && color !== "000000") styles.push(`color:#${color}`);
        if (szHalf) styles.push(`font-size:${(Number(szHalf) / 2).toFixed(1).replace(/\.0$/, "")}pt`);
        if (fontAscii && /arial/i.test(fontAscii)) styles.push("font-family:Arial,Helvetica,sans-serif");

        return { text: htmlText, vertAlign, styleStr: styles.join(";") };
      }

      function parseSimpleParagraph(pXml, insideHeaderBox = false) {
        const pWithoutDrawings = pXml.replace(/<w:drawing>[\s\S]*?<\/w:drawing>/g, "");
        const pPr = (pWithoutDrawings.match(/<w:pPr>([\s\S]*?)<\/w:pPr>/) || [])[1] || "";
        const jc = (pPr.match(/<w:jc w:val="([^"]+)"/) || [])[1] || "left";
        const pRPr = (pPr.match(/<w:rPr>([\s\S]*?)<\/w:rPr>/) || [])[1] || "";
        const defSz = (pRPr.match(/<w:sz w:val="(\d+)"/) || [])[1]
          || (pRPr.match(/<w:szCs w:val="(\d+)"/) || [])[1]
          || (insideHeaderBox ? "22" : "26");
        const indLeft = (pPr.match(/<w:ind[^>]*w:left="(\d+)"/) || [])[1] || "";
        const indFirst = (pPr.match(/<w:ind[^>]*w:firstLine="(\d+)"/) || [])[1] || "";
        const hasBottomDoubleBdr = /<w:pBdr>[\s\S]*?<w:bottom w:val="double"/.test(pPr);
        const hasBottomSingleBdr = /<w:pBdr>[\s\S]*?<w:bottom w:val="single"/.test(pPr);

        const runs = pWithoutDrawings.match(/<w:r[ >][\s\S]*?<\/w:r>/g) || [];
        const merged = [];
        runs.forEach(r => {
          const info = extractRunInfo(r, defSz, false);
          if (!info) return;
          const prev = merged[merged.length - 1];
          if (prev && prev.styleStr === info.styleStr && prev.vertAlign === info.vertAlign) {
            prev.text += info.text;
          } else {
            merged.push(info);
          }
        });

        const rawPlainText = merged.map(m => m.text).join("");
        if (!rawPlainText.trim()) {
          if (hasBottomDoubleBdr) {
            return `<div style="border-bottom:3px double #000;margin:8px 0 12px;height:2px;"></div>`;
          }
          if (hasBottomSingleBdr) {
            return `<div style="border-bottom:1px solid #000;margin:8px 0 12px;height:1px;"></div>`;
          }
          return "";
        }

        if (/Đại\s*diện\s*:/i.test(rawPlainText) && /Chức\s*vụ\s*:/i.test(rawPlainText)) {
          return `<div style="margin:4px 0;line-height:1.6;font-size:12pt;">
            <span style="display:inline-block;width:52%;">Đại diện:</span>
            <span>Chức vụ:</span>
          </div>`;
        }

        const leadingTabs = (rawPlainText.match(/^\t+/) || [""])[0].length;

        const inner = merged.map(m => {
          let t = m.text;
          if (leadingTabs >= 4) {
            t = t.replace(/^\t+/, "");
          }
          t = t
            .replace(/  /g, " &nbsp;")
            .replace(/\t/g, "&emsp;&emsp;&emsp;")
            .replace(/\n/g, "<br/>");
          if (m.vertAlign === "superscript") t = `<sup>${t}</sup>`;
          else if (m.vertAlign === "subscript") t = `<sub>${t}</sub>`;
          return m.styleStr ? `<span style="${m.styleStr}">${t}</span>` : t;
        }).join("");

        const pStyles = [insideHeaderBox ? "margin:2px 0;line-height:1.35" : "margin:4px 0;line-height:1.6"];
        if (leadingTabs >= 4 || jc === "right") {
          pStyles.push("text-align:right;padding-right:24px");
        } else if (jc === "center") {
          pStyles.push("text-align:center");
        } else if (jc === "both") {
          pStyles.push("text-align:justify");
        }

        const totalIndentTwips = Number(indLeft || 0) + Number(indFirst || 0);
        if (totalIndentTwips > 200 && jc === "left" && leadingTabs < 4) {
          const px = Math.min(120, Math.round(totalIndentTwips / 18));
          pStyles.push(`padding-left:${px}px`);
        }

        if (hasBottomDoubleBdr) {
          pStyles.push("border-bottom:3px double #000;padding-bottom:6px;margin-bottom:10px");
        } else if (hasBottomSingleBdr) {
          pStyles.push("border-bottom:1px solid #000;padding-bottom:6px;margin-bottom:10px");
        }

        return `<div style="${pStyles.join(";")}">${inner}</div>`;
      }

      function parseDrawings(xmlChunk) {
        const drawings = xmlChunk.match(/<w:drawing>[\s\S]*?<\/w:drawing>/g) || [];
        if (drawings.length === 0) return "";

        let htmlOut = "";
        drawings.forEach(drw => {
          const blips = [...drw.matchAll(/<a:blip[^>]*r:embed="([^"]+)"/g)].map(m => m[1]);
          const txbxMatches = [...drw.matchAll(/<w:txbxContent>([\s\S]*?)<\/w:txbxContent>/g)].map(m => m[1]);

          const imgsHtml = blips.map(rId => {
            const target = relsMap[rId] || "";
            const b64 = mediaBase64Map[target] || mediaBase64Map["word/" + target] || "";
            return b64 ? `<img src="${b64}" class="thna-logo-img" style="width:170px;max-height:110px;object-fit:contain;display:block;" />` : "";
          }).filter(Boolean).join("");

          const txbxHtml = txbxMatches.map(tb => {
            const paras = tb.match(/<w:p[ >][\s\S]*?<\/w:p>/g) || [];
            return paras.map(p => parseSimpleParagraph(p, true)).filter(Boolean).join("");
          }).filter(Boolean).join("");

          if (imgsHtml && txbxHtml) {
            htmlOut += `<div class="thna-header" style="display:flex;align-items:center;gap:16px;padding-left:4px;margin-bottom:6px;">
              <div class="thna-logo-col" style="flex-shrink:0;">${imgsHtml}</div>
              <div class="thna-info-col" style="flex:1;">${txbxHtml}</div>
            </div>`;
          } else if (imgsHtml) {
            htmlOut += `<div style="margin:6px 0;">${imgsHtml}</div>`;
          } else if (txbxHtml) {
            htmlOut += `<div style="margin:6px 0;">${txbxHtml}</div>`;
          }
        });
        return htmlOut;
      }

      function parseTable(tblXml) {
        const gridCols = [...tblXml.matchAll(/<w:gridCol w:w="(\d+)"\/>/g)].map(m => Number(m[1]));
        if (gridCols.length <= 1 && /<w:drawing>/.test(tblXml)) {
          return parseDrawings(tblXml);
        }

        const tblPr = (tblXml.match(/<w:tblPr>([\s\S]*?)<\/w:tblPr>/) || [])[1] || "";
        const hasNoBorder = /<w:tblBorders>[\s\S]*?<w:top w:val="(?:none|nil)"/.test(tblPr);
        const borderCss = hasNoBorder ? "border:none;" : "border:1px solid #000;";

        const totalW = gridCols.reduce((a, b) => a + b, 0) || 1;
        const colPercents = gridCols.map(w => ((w / totalW) * 100).toFixed(2) + "%");

        const trs = tblXml.match(/<w:tr[ >][\s\S]*?<\/w:tr>/g) || [];
        const rowsHtml = trs.map((tr, rIdx) => {
          const tcs = tr.match(/<w:tc[ >][\s\S]*?<\/w:tc>/g) || [];
          const cellsHtml = tcs.map((tc, cIdx) => {
            const paras = tc.match(/<w:p[ >][\s\S]*?<\/w:p>/g) || [];
            const cellInner = paras.map(p => parseSimpleParagraph(p)).filter(Boolean).join("") || "";
            const wStyle = colPercents[cIdx] ? `width:${colPercents[cIdx]};` : "";
            const tag = (!hasNoBorder && rIdx === 0) ? "th" : "td";
            return `<${tag} style="${wStyle}${borderCss}padding:5px 6px;vertical-align:middle;">${cellInner}</${tag}>`;
          }).join("");
          return `<tr>${cellsHtml}</tr>`;
        }).join("");

        const tblClass = hasNoBorder ? "" : ' class="thna-table"';
        return `<table${tblClass} style="width:100%;border-collapse:collapse;margin:12px 0;">${rowsHtml}</table>`;
      }

      const topTokens = [];
      let pos = 0;
      while (pos < bodyXml.length) {
        const sub = bodyXml.slice(pos);
        const tblMatch = sub.search(/<w:tbl(?:>|\s)/);
        const pMatch = sub.search(/<w:p(?:>|\s|\/>)/);

        const absTbl = tblMatch >= 0 ? pos + tblMatch : -1;
        const absP = pMatch >= 0 ? pos + pMatch : -1;

        if (absTbl >= 0 && (absP < 0 || absTbl < absP)) {
          const endTbl = findBalancedClosingTag(bodyXml, absTbl, "w:tbl");
          if (endTbl < 0) break;
          topTokens.push({ type: "tbl", xml: bodyXml.slice(absTbl, endTbl) });
          pos = endTbl;
        } else if (absP >= 0) {
          const endP = findBalancedClosingTag(bodyXml, absP, "w:p");
          if (endP < 0) break;
          topTokens.push({ type: "p", xml: bodyXml.slice(absP, endP) });
          pos = endP;
        } else {
          break;
        }
      }

      const parts = [];
      topTokens.forEach(tok => {
        if (tok.type === "tbl") {
          const h = parseTable(tok.xml);
          if (h) parts.push(h);
        } else if (tok.type === "p") {
          if (/<w:drawing>/.test(tok.xml)) {
            const dh = parseDrawings(tok.xml);
            if (dh) parts.push(dh);
          }
          const ph = parseSimpleParagraph(tok.xml);
          if (ph) parts.push(ph);
        }
      });

      let finalHtml = `<div class="bb-4oranges">${parts.join("\n")}</div>`;

      // Tự động nhận diện và chuyển Ngày giao, Số Cont, Số Seal thành biến động {{...}}
      finalHtml = finalHtml.replace(
        /(Ngày(?:\s|&nbsp;)*)(\d{1,2}|\.{2,})((?:\s|&nbsp;)*(?:tháng|Tháng)(?:\s|&nbsp;)*)(\d{1,2}|\.{2,})((?:\s|&nbsp;)*(?:năm|Năm)(?:\s|&nbsp;)*)(\d{4}|\.{2,})/g,
        "$1{{NGAY}}$3{{THANG}}$5{{NAM}}"
      );

      let foundCont = false;
      finalHtml = finalHtml.replace(/\b([A-Z]{3}U\s*\d{6,7})\b/g, () => {
        foundCont = true;
        return "{{SO_CONT}}";
      });

      // Nhận diện số Seal dạng "seal: 691148" (như Form Việt Xô - Jumbo)
      finalHtml = finalHtml.replace(/(seal\s*:\s*(?:<[^>]+>|\s|&nbsp;)*)([A-Za-z0-9]{5,12})/gi, "$1{{SO_SEAL}}");

      // Nhận diện số Seal nằm ở dòng STT 2 (như Form 4 Oranges)
      if (foundCont && !finalHtml.includes("{{SO_SEAL}}")) {
        finalHtml = finalHtml.replace(
          /(<td[^>]*>\s*<div[^>]*>\s*(?:<span[^>]*>)?2(?:<\/span>)?\s*<\/div>\s*<\/td>\s*<td[^>]*>\s*<div[^>]*>\s*(?:<span[^>]*>)?(?:\s|&nbsp;)*)([A-Za-z0-9]{5,10})((?:<\/span>)?\s*<\/div>\s*<\/td>)/i,
          "$1{{SO_SEAL}}$3"
        );
      }

      return finalHtml;
    }

    async function handleBbWordDocxSelected(inputEl) {
      const file = inputEl.files && inputEl.files[0];
      if (!file) return;

      try {
        showToast("⏳ Đang đọc và nhận diện bố cục từ file Word (.docx)...", "info");
        const ab = await file.arrayBuffer();
        const filesMap = await unzipDocxBuffer(ab);
        const decoder = new TextDecoder("utf-8");

        const docXmlBytes = filesMap["word/document.xml"];
        if (!docXmlBytes) throw new Error("Không tìm thấy nội dung word/document.xml trong file!");
        const docXml = decoder.decode(docXmlBytes);
        const relsXml = filesMap["word/_rels/document.xml.rels"]
          ? decoder.decode(filesMap["word/_rels/document.xml.rels"])
          : "";

        const mediaBase64Map = {};
        for (const [fName, fBytes] of Object.entries(filesMap)) {
          if (fName.startsWith("word/media/")) {
            const ext = (fName.split(".").pop() || "png").toLowerCase();
            const mime = ext === "jpg" || ext === "jpeg" ? "image/jpeg" : "image/png";
            const dataUri = `data:${mime};base64,${uint8ToBase64(fBytes)}`;
            mediaBase64Map[fName] = dataUri;
            mediaBase64Map[fName.replace(/^word\//, "")] = dataUri;
          }
        }

        const parsedHtml = parseDocxXmlToHtml(docXml, relsXml, mediaBase64Map);
        const cleanFileTitle = file.name.replace(/\.docx$/i, "").trim();

        // Tự động gợi ý Khách Hàng & Kho Nhận từ TMS dựa trên nội dung file Word và tên file
        const plainDocUpper = toCleanUpperCode(parsedHtml.replace(/<[^>]+>/g, " ") + " " + cleanFileTitle);
        let autoCust = "";
        let autoKho = "";
        const uniqueCusts = new Set();
        const uniqueKhos = new Set();
        (allShipments || []).forEach(s => {
          if (s.xuatNhapPo !== "N") return;
          if (s.tenKhachPo) uniqueCusts.add(String(s.tenKhachPo).trim());
          if (s.khoNhanHangPo) uniqueKhos.add(String(s.khoNhanHangPo).trim());
        });
        for (const cName of uniqueCusts) {
          const core = toCleanUpperCode(cName.replace(/^(CTY|CONG TY|CP|TNHH|MTV|KS)\s+/gi, ""));
          if (core.length >= 4 && plainDocUpper.includes(core)) {
            autoCust = cName;
            break;
          }
        }
        for (const kName of uniqueKhos) {
          const core = toCleanUpperCode(kName.replace(/^(CTY|CONG TY|KHO|CN|TNHH|CP)\s+/gi, ""));
          if (core.length >= 4 && plainDocUpper.includes(core)) {
            autoKho = kName;
            break;
          }
        }

        if (isUploadingDocxAsNewTemplate || currentEditingBbTplId === "STANDARD") {
          const newId = "TPL_" + Date.now();
          const newTpl = {
            id: newId,
            name: `Form ${cleanFileTitle}`,
            short_badge: cleanFileTitle.slice(0, 12),
            customer_keywords: autoCust,
            keywords: autoKho,
            is_active: 1,
            sort_order: bbTemplatesList.length + 1,
            html_content: parsedHtml
          };
          const stdIdx = bbTemplatesList.findIndex(t => t.id === "STANDARD");
          if (stdIdx >= 0) bbTemplatesList.splice(stdIdx, 0, newTpl);
          else bbTemplatesList.push(newTpl);
          selectBbTemplateInEditor(newId);
        } else {
          isBbLivePreviewMode = false;
          const canvas = document.getElementById("bbVisualEditorCanvas");
          if (canvas) {
            canvas.contentEditable = "true";
            canvas.innerHTML = parsedHtml;
          }
          const custInp = document.getElementById("bbTplCustomerKeywords");
          const kwInp = document.getElementById("bbTplKeywords");
          if (custInp && !custInp.value.trim() && autoCust) custInp.value = autoCust;
          if (kwInp && !kwInp.value.trim() && autoKho) kwInp.value = autoKho;
        }

        showToast("✅ Đã nhận diện Form từ file Word (.docx) và tự động gắn biến {{SO_CONT}}, {{SO_SEAL}}, {{NGAY}}! Hãy chọn Kho Nhận và bấm Lưu.", "success");
      } catch (err) {
        console.error("DOCX Parse Error:", err);
        showToast("⚠️ Không thể đọc file Word: " + (err?.message || "Vui lòng chọn đúng file .docx"), "error");
      }
    }

    function execBbEditorCmd(cmd, val = null) {
      if (isBbLivePreviewMode) {
        showToast("Đang ở chế độ Xem thử. Hãy bấm 'Quay lại Chế độ Soạn Mẫu' để chỉnh sửa!", "warning");
        return;
      }
      const canvas = document.getElementById("bbVisualEditorCanvas");
      if (canvas) canvas.focus();
      document.execCommand(cmd, false, val);
    }

    function insertBbVariable(varToken) {
      if (isBbLivePreviewMode) {
        showToast("Hãy tắt chế độ Xem thử trước khi chèn biến!", "warning");
        return;
      }
      const canvas = document.getElementById("bbVisualEditorCanvas");
      if (!canvas) return;
      canvas.focus();
      const sel = window.getSelection();
      if (sel && sel.rangeCount > 0 && canvas.contains(sel.anchorNode)) {
        document.execCommand("insertText", false, varToken);
      } else {
        canvas.innerHTML += ` <span>${varToken}</span>`;
      }
    }

    function insertBbSampleTable() {
      if (isBbLivePreviewMode) return;
      const tblHtml = `<table class="thna-table" style="width:100%;border-collapse:collapse;margin:12px 0;">
        <thead>
          <tr>
            <th style="border:1px solid #000;padding:6px;">STT</th>
            <th style="border:1px solid #000;padding:6px;">Số cont/Seal</th>
            <th style="border:1px solid #000;padding:6px;">Số lượng</th>
            <th style="border:1px solid #000;padding:6px;">Khối lượng (tấn)</th>
            <th style="border:1px solid #000;padding:6px;">Mã Hàng / Ghi chú</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="border:1px solid #000;padding:6px;text-align:center;">1</td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{SO_CONT}}</td>
            <td style="border:1px solid #000;padding:6px;"></td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{TRONG_LUONG}}</td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{HANG_HOA}}</td>
          </tr>
          <tr>
            <td style="border:1px solid #000;padding:6px;text-align:center;">2</td>
            <td style="border:1px solid #000;padding:6px;text-align:center;">{{SO_SEAL}}</td>
            <td style="border:1px solid #000;padding:6px;"></td>
            <td style="border:1px solid #000;padding:6px;"></td>
            <td style="border:1px solid #000;padding:6px;"></td>
          </tr>
        </tbody>
      </table>`;
      const canvas = document.getElementById("bbVisualEditorCanvas");
      if (!canvas) return;
      canvas.focus();
      document.execCommand("insertHTML", false, tblHtml);
    }

    function triggerBbLogoUpload() {
      if (isBbLivePreviewMode) {
        showToast("Hãy tắt chế độ Xem thử trước khi đổi Logo!", "warning");
        return;
      }
      const inp = document.getElementById("bbLogoFileInput");
      if (inp) { inp.value = ""; inp.click(); }
    }

    function handleBbLogoFileSelected(inputEl) {
      const file = inputEl.files && inputEl.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function(e) {
        const dataUrl = e.target.result;
        const canvas = document.getElementById("bbVisualEditorCanvas");
        if (!canvas) return;
        const existingLogo = canvas.querySelector(".thna-logo-img") || canvas.querySelector(".vs-header-table img") || canvas.querySelector("img");
        if (existingLogo) {
          existingLogo.src = dataUrl;
          showToast("Đã cập nhật ảnh Logo trên mẫu Biên Bản!", "success");
        } else {
          canvas.focus();
          document.execCommand("insertHTML", false, `<img src="${dataUrl}" style="max-width:165px;height:auto;" />`);
          showToast("Đã chèn ảnh Logo vào mẫu Biên Bản!", "success");
        }
      };
      reader.readAsDataURL(file);
    }

    function getSamplePoForBbPreview() {
      const custInp = document.getElementById("bbTplCustomerKeywords");
      const kwInp = document.getElementById("bbTplKeywords");
      const custList = ((custInp && custInp.value) || "").split(",").map(k => k.trim()).filter(k => k && !k.startsWith("*"));
      const kwList = ((kwInp && kwInp.value) || "").split(",").map(k => k.trim()).filter(k => k && !k.startsWith("*"));
      let matchedPo = null;
      if (custList.length > 0 || kwList.length > 0) {
        matchedPo = allShipments.find(i => {
          if (i.xuatNhapPo !== "N") return false;
          const cKho = toCleanUpperCode(i.khoNhanHangPo || "");
          const cKhach = toCleanUpperCode(i.tenKhachPo || "");
          const okCust = custList.length === 0 || custList.some(k => cKhach.includes(toCleanUpperCode(k)));
          const okKho = kwList.length === 0 || kwList.some(k => cKho.includes(toCleanUpperCode(k)));
          return okCust && okKho;
        });
      }
      if (!matchedPo) {
        matchedPo = filteredShipments.find(i => i.xuatNhapPo === "N") || allShipments.find(i => i.xuatNhapPo === "N");
      }
      return matchedPo || {
        idPo: "999999",
        soContPo: "VSGU2250272",
        soSealPo: "693903",
        ngayTraHang: getTodayISO(),
        khoNhanHangPo: kwList[0] || "Cty 4 Oranges",
        tenKhachPo: custList[0] || "Cty KS Á Châu",
        diaChiNhanPo: "Lô C-02-1, KCN Đức Hòa 1, Ấp 5, Xã Mỹ Hạnh",
        tinhNhanPo: "TAY NINH",
        soXePo: "50H10208",
        donViVcPo: "VIETSUN",
        trongLuongPo: "27.00",
        tenHangHoaPo: "Bột đá",
        tauPo: "VIETSUN RELIANCE",
        chuyenPo: "2639S"
      };
    }

    function toggleBbEditorLivePreview() {
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const btnPrev = document.getElementById("btnBbTogglePreview");
      if (!canvas) return;

      if (!isBbLivePreviewMode) {
        rawDraftBbHtmlBeforePreview = canvas.innerHTML;
        const samplePo = getSamplePoForBbPreview();
        canvas.innerHTML = renderTemplateWithPoData(rawDraftBbHtmlBeforePreview, samplePo);
        canvas.contentEditable = "false";
        isBbLivePreviewMode = true;
        if (btnPrev) {
          btnPrev.innerHTML = "✏️ Quay lại Chế độ Soạn Mẫu (Hiện {{...}})";
          btnPrev.style.background = "#fef3c7";
          btnPrev.style.color = "#b45309";
          btnPrev.style.borderColor = "#fcd34d";
        }
        showToast(`Đang xem thử với dữ liệu lô #${samplePo.idPo} (${samplePo.tenKhachPo} ➔ ${samplePo.khoNhanHangPo})`, "info");
      } else {
        canvas.innerHTML = rawDraftBbHtmlBeforePreview;
        canvas.contentEditable = "true";
        isBbLivePreviewMode = false;
        if (btnPrev) {
          btnPrev.innerHTML = "👁️ Xem thử với dữ liệu thật";
          btnPrev.style.background = "#eff6ff";
          btnPrev.style.color = "#1d4ed8";
          btnPrev.style.borderColor = "#93c5fd";
        }
      }
    }

    async function saveCurrentBbTemplate() {
      const tpl = bbTemplatesList.find(t => t.id === currentEditingBbTplId);
      if (!tpl) return;

      const nameVal = (document.getElementById("bbTplName")?.value || "").trim();
      const badgeVal = (document.getElementById("bbTplBadge")?.value || "BB").trim();
      const custVal = tpl.id === "STANDARD" ? "" : (document.getElementById("bbTplCustomerKeywords")?.value || "").trim();
      const kwVal = tpl.id === "STANDARD" ? "*" : (document.getElementById("bbTplKeywords")?.value || "").trim();
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const htmlVal = isBbLivePreviewMode ? rawDraftBbHtmlBeforePreview : (canvas ? canvas.innerHTML : tpl.html_content);

      if (!nameVal) {
        showToast("⚠️ Vui lòng nhập Tên Mẫu Biên Bản!", "warning");
        return;
      }
      if (tpl.id !== "STANDARD" && !kwVal && !custVal) {
        showToast("⚠️ Vui lòng chọn ít nhất 1 từ khóa KHO NHẬN hoặc KHÁCH HÀNG để hệ thống tự động nhận diện!", "warning");
        return;
      }

      tpl.name = nameVal;
      tpl.short_badge = badgeVal || "FormRiêng";
      tpl.customer_keywords = custVal;
      tpl.keywords = kwVal;
      tpl.html_content = htmlVal;
      tpl.is_active = 1;

      try {
        localStorage.setItem("vs_bb_templates_v1", JSON.stringify(bbTemplatesList));
      } catch (_) {}

      renderBbTemplateSidebar();
      updateBbTplCountBadge();
      renderShipmentList();

      try {
        const resp = await fetch("/api/index.php?route=bb_templates/save", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(tpl)
        });
        const res = await resp.json();
        if (res && res.ok) {
          showToast(`✅ Đã lưu "${tpl.name}" và đồng bộ lên Server!`, "success");
        } else {
          showToast(`Đã lưu "${tpl.name}" trên máy!`, "success");
        }
      } catch (_) {
        showToast(`Đã lưu "${tpl.name}" trên trình duyệt!`, "success");
      }
    }

    function restoreDefaultBbTemplate() {
      const builtIn = getBuiltInBbTemplates().find(t => t.id === currentEditingBbTplId);
      if (!builtIn) return;
      if (!confirm(`Bạn có chắc muốn khôi phục "${builtIn.name}" về thiết kế gốc ban đầu không?`)) return;

      const idx = bbTemplatesList.findIndex(t => t.id === currentEditingBbTplId);
      if (idx >= 0) {
        bbTemplatesList[idx] = { ...builtIn };
      }
      selectBbTemplateInEditor(currentEditingBbTplId);
      saveCurrentBbTemplate();
    }

    async function deleteCurrentBbTemplate() {
      if (currentEditingBbTplId === "STANDARD") {
        showToast("⚠️ Không thể xóa Form Chuẩn mặc định!", "warning");
        return;
      }
      const tpl = bbTemplatesList.find(t => t.id === currentEditingBbTplId);
      if (!tpl) return;
      if (!confirm(`Bạn có chắc muốn xóa "${tpl.name}" không?`)) return;

      const delId = tpl.id;
      bbTemplatesList = bbTemplatesList.filter(t => t.id !== delId);
      try {
        localStorage.setItem("vs_bb_templates_v1", JSON.stringify(bbTemplatesList));
      } catch (_) {}

      try {
        await fetch("/api/index.php?route=bb_templates/delete", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: delId })
        });
      } catch (_) {}

      updateBbTplCountBadge();
      selectBbTemplateInEditor(bbTemplatesList[0]?.id || "STANDARD");
      renderShipmentList();
      showToast(`Đã xóa mẫu "${tpl.name}"!`, "info");
    }

    function testPrintCurrentBbTemplate() {
      const canvas = document.getElementById("bbVisualEditorCanvas");
      const rawHtml = isBbLivePreviewMode ? rawDraftBbHtmlBeforePreview : (canvas ? canvas.innerHTML : "");
      const samplePo = getSamplePoForBbPreview();
      const filledHtml = renderTemplateWithPoData(rawHtml, samplePo);
      const w = window.open("", "_blank");
      if (!w) {
        showToast("⚠️ Trình duyệt chặn pop-up!", "error");
        return;
      }
      w.document.open();
      w.document.write(buildFullBienBanPrintDocument([`<div class="bb-page">${filledHtml}</div>`], "In thử Mẫu Biên Bản", "1 mẫu thử"));
      w.document.close();
    }

    function toggleBulkDropdown(e) {
      if (e) e.stopPropagation();
      const pop = document.getElementById("bulkDropdownPop");
      if (pop) pop.classList.toggle("show");
    }

    function closeBulkDropdown() {
      const pop = document.getElementById("bulkDropdownPop");
      if (pop) pop.classList.remove("show");
    }

    function selectBulkCarrier(carrier) {
      closeBulkDropdown();
      state.bulkMode = true;
      state.bulkCarrier = carrier;

      // Nếu không ở Tab Xuất (X) hoặc Tab Nhập (N) thì mặc định chuyển về Tab Xuất (X)
      if (state.xuatNhap !== "X" && state.xuatNhap !== "N") {
        state.xuatNhap = "X";
        document.querySelectorAll(".bottom-nav .nav-item").forEach(btn => {
          btn.classList.toggle("active", btn.dataset.xn === "X");
        });
      }

      state.bulkSelectedIds = new Set();
      applyFilters();

      // Mặc định tick chọn tất cả các dòng tìm thấy theo ĐVVT được lọc
      filteredShipments.forEach(item => {
        state.bulkSelectedIds.add(String(item.idPo));
      });

      renderShipmentList();
      updateBulkControlsUI();
      updateBulkHeaderCheckbox();
      updateBulkSelectedCount();

      const docLabel = state.xuatNhap === "N" ? "Biên Bản (BB)" : "PKL";
      showToast(`Đã bật chế độ in ${docLabel} hàng loạt cho ${carrier} (${filteredShipments.length} lô)!`, "info");
    }

    function exitBulkPrintMode() {
      state.bulkMode = false;
      state.bulkCarrier = null;
      if (state.bulkSelectedIds) state.bulkSelectedIds.clear();
      closeBulkDropdown();
      updateBulkControlsUI();
      applyFilters();
      showToast("Đã thoát chế độ in hàng loạt.", "info");
    }

    function onToggleBulkItem(idPo, isChecked) {
      if (!state.bulkSelectedIds) state.bulkSelectedIds = new Set();
      if (isChecked) {
        state.bulkSelectedIds.add(String(idPo));
      } else {
        state.bulkSelectedIds.delete(String(idPo));
      }
      updateBulkHeaderCheckbox();
      updateBulkSelectedCount();
    }

    function toggleSelectAllBulk(isChecked) {
      if (!state.bulkSelectedIds) state.bulkSelectedIds = new Set();
      const checkboxes = document.querySelectorAll(".chk-bulk-item");
      checkboxes.forEach(cb => {
        cb.checked = isChecked;
        const id = cb.value;
        if (isChecked) {
          state.bulkSelectedIds.add(String(id));
        } else {
          state.bulkSelectedIds.delete(String(id));
        }
      });
      updateBulkSelectedCount();
    }

    function updateBulkHeaderCheckbox() {
      const headerCb = document.getElementById("chkBulkPrintAll");
      if (!headerCb) return;
      const checkboxes = document.querySelectorAll(".chk-bulk-item");
      if (checkboxes.length === 0) {
        headerCb.checked = false;
        return;
      }
      headerCb.checked = Array.from(checkboxes).every(cb => cb.checked);
    }

    function getVisibleSelectedCount() {
      if (!state.bulkSelectedIds || state.bulkSelectedIds.size === 0) return 0;
      let cnt = 0;
      filteredShipments.forEach(item => {
        if (state.bulkSelectedIds.has(String(item.idPo))) cnt++;
      });
      return cnt;
    }

    function clearAllBulkSelection() {
      if (state.bulkSelectedIds) state.bulkSelectedIds.clear();
      const checkboxes = document.querySelectorAll(".chk-bulk-item");
      checkboxes.forEach(cb => { cb.checked = false; });
      const headerCb = document.getElementById("chkBulkPrintAll");
      if (headerCb) headerCb.checked = false;
      updateBulkSelectedCount();
    }

    function updateBulkSelectedCount() {
      const cnt = getVisibleSelectedCount();
      const countEl = document.getElementById("bulkSelectedCount");
      if (countEl) countEl.textContent = cnt;
      const nhapCountEl = document.getElementById("bulkNhapSelectedCount");
      if (nhapCountEl) nhapCountEl.textContent = cnt;
      const nhapClearBtn = document.getElementById("btnBulkNhapClear");
      if (nhapClearBtn) nhapClearBtn.style.display = cnt > 0 ? "inline-block" : "none";
    }

    function updateBulkControlsUI() {
      const ctrl = document.getElementById("desktopBulkCtrl");
      if (!ctrl) return;
      const isTable = state.viewMode === "table";
      const isXuat = state.xuatNhap === "X";
      const isNhap = state.xuatNhap === "N";

      // Hiện khi ở chế độ Bảng PC và Tab Hàng Xuất (In PKL) hoặc Tab Hàng Nhập (In BB)
      if (!isTable || (!isXuat && !isNhap && !state.bulkMode)) {
        ctrl.style.display = "none";
        closeBulkDropdown();
        return;
      }

      ctrl.style.display = "flex";
      const wrap = document.getElementById("bulkDropdownWrap");
      const activeBar = document.getElementById("bulkActiveBar");
      const nhapDirectBar = document.getElementById("bulkNhapDirectBar");
      const cnt = getVisibleSelectedCount();

      // Đối với Tab Hàng Nhập (N): Hiển thị luôn nút Thực Thi In Biên Bản (không sổ dropdown ĐVVT)
      if (isNhap) {
        if (wrap) wrap.style.display = "none";
        if (activeBar) activeBar.style.display = "none";
        if (nhapDirectBar) nhapDirectBar.style.display = "inline-flex";
        const nhapCountEl = document.getElementById("bulkNhapSelectedCount");
        if (nhapCountEl) nhapCountEl.textContent = cnt;
        const nhapClearBtn = document.getElementById("btnBulkNhapClear");
        if (nhapClearBtn) nhapClearBtn.style.display = cnt > 0 ? "inline-block" : "none";
        updateBbTplCountBadge();
        return;
      }

      // Đối với Tab Hàng Xuất (X): Sổ dropdown chọn VIETSUN / VENDOR rồi mới hiện Print All
      if (nhapDirectBar) nhapDirectBar.style.display = "none";
      const lblAction = document.getElementById("lblBulkAction");
      const mainBtn = document.getElementById("btnBulkPrintMain");
      if (lblAction) lblAction.textContent = "In PKL hàng loạt";
      if (mainBtn) {
        mainBtn.title = "In PKL hàng loạt theo ĐVVT";
        mainBtn.style.background = "linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)";
        mainBtn.style.borderColor = "#1d4ed8";
      }

      if (state.bulkMode) {
        if (wrap) wrap.style.display = "none";
        if (activeBar) activeBar.style.display = "inline-flex";
        const iconEl = document.getElementById("bulkCarrierIcon");
        const textEl = document.getElementById("bulkCarrierText");
        const countEl = document.getElementById("bulkSelectedCount");
        if (iconEl) iconEl.textContent = state.bulkCarrier === "VIETSUN" ? "🚛" : "🚚";
        if (textEl) textEl.textContent = state.bulkCarrier;
        if (countEl) countEl.textContent = cnt;
      } else {
        if (wrap) wrap.style.display = "inline-block";
        if (activeBar) activeBar.style.display = "none";
      }
    }

    function printSingleBienBan(idPo) {
      const po = allShipments.find(i => String(i.idPo) === String(idPo));
      if (!po) {
        showToast("⚠️ Không tìm thấy thông tin lô hàng!", "warning");
        return;
      }
      executeBulkBienBanPrint([po]);
    }

    function buildBienBanPageHtml(po, isNotFirstPage) {
      const tpl = detectBbTemplate(po);
      const rawHtml = tpl.html_content || getDefaultStandardHtml();
      const filledInner = renderTemplateWithPoData(rawHtml, po);
      return `<div class="bb-page ${isNotFirstPage ? 'page-break' : ''}" contenteditable="true">${filledInner}</div>`;
    }

    function buildFullBienBanPrintDocument(pagesHtmlArr, title, summaryBreakdown) {
      const count = pagesHtmlArr.length;
      return `<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>${escapeAttr(title)}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 10mm 10mm 10mm 10mm;
    }
    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    html, body {
      margin: 0;
      padding: 0;
      background: #f1f5f9;
      color: #000000;
      font-family: "Times New Roman", Times, serif;
    }
    .no-print-bar {
      position: sticky;
      top: 0;
      z-index: 9999;
      background: #ffffff;
      border-bottom: 1px solid #cbd5e1;
      padding: 10px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      font-family: Arial, Helvetica, sans-serif;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    .no-print-info { font-size: 13px; font-weight: 700; color: #0f172a; }
    .no-print-sub { font-size: 12px; color: #0369a1; font-weight: 600; margin-top: 2px; }
    .no-print-actions { display: flex; align-items: center; gap: 8px; }
    .btn-top-print { background: #f59e0b; color: #000; border: 1px solid #d97706; border-radius: 6px; padding: 7px 16px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-top-print:hover { background: #eab308; }
    .btn-top-close { background: #fff; color: #dc2626; border: 1px solid #dc2626; border-radius: 6px; padding: 7px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-top-close:hover { background: #fee2e2; }
    .bb-page { width: 190mm; margin: 12px auto; background: #fff; padding: 6mm 6mm; box-shadow: 0 2px 10px rgba(0,0,0,0.1); outline: none; }
    .page-break { page-break-before: always; break-before: page; }
    @media print {
      html, body { background: #ffffff; }
      .no-print-bar { display: none !important; }
      .bb-page { margin: 0 auto; padding: 0; box-shadow: none; width: 190mm; }
    }
    .bb-vietsun { font-family: "Times New Roman", Times, serif; font-size: 13pt; color: #000; }
    .vs-div-picture { width: 190mm; min-height: 225mm; position: relative; }
    .vs-watermark-img { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 82%; max-height: 175mm; object-fit: contain; opacity: 0.22; pointer-events: none; z-index: 0; }
    .vs-seal-img { position: absolute; top: 18mm; left: 10mm; width: 39mm; height: 39mm; object-fit: contain; pointer-events: none; z-index: 1; }
    .vs-content-layer { position: relative; z-index: 2; }
    .vs-header-table { width: 100%; border-collapse: collapse; }
    .vs-info-table { width: 100%; border-collapse: collapse; line-height: 1.8; vertical-align: top; }
    .vs-info-table th, .vs-info-table td { padding: 0 2px; vertical-align: top; }
    .vs-bangND { width: 100%; border-collapse: collapse; line-height: 1.8; margin: 4px 0; }
    .vs-bangND th, .vs-bangND td { border: 1px solid #000; padding: 2px 10px; text-align: center; }
    .vs-sig-row { display: flex; justify-content: space-between; text-align: center; font-weight: 700; margin-top: 8px; }
    .vs-sig-col { width: 33.33%; }
    .bb-4oranges { font-family: "Times New Roman", Times, serif; color: #000; padding-top: 4mm; }
    .thna-header { display: flex; align-items: flex-start; gap: 16px; padding-left: 8px; }
    .thna-logo-col { flex-shrink: 0; padding-top: 2px; }
    .thna-logo-img { width: 160px; height: auto; display: block; }
    .thna-info-col { font-family: Arial, Helvetica, sans-serif; color: #222; line-height: 1.38; }
    .thna-title { font-size: 14pt; font-weight: 700; color: #0000FF; margin-bottom: 3px; }
    .thna-sub { font-size: 10pt; margin-bottom: 2px; }
    .thna-line { font-size: 10pt; color: #222; }
    .thna-link { color: #0000FF; text-decoration: underline; }
    .thna-divider { font-family: "Times New Roman", Times, serif; font-size: 11pt; text-align: center; letter-spacing: 0.4px; margin: 12px 0 14px; overflow: hidden; white-space: nowrap; }
    .thna-doc-title { font-size: 16pt; font-weight: 700; text-align: center; margin-bottom: 6px; }
    .thna-doc-date { font-size: 12pt; text-align: right; padding-right: 28px; margin-bottom: 14px; }
    .thna-section { line-height: 1.85; }
    .thna-bold-12 { font-size: 12pt; font-weight: 700; }
    .thna-bold-13 { font-size: 13pt; font-weight: 700; }
    .thna-driver-line { font-size: 13pt; margin-top: 2px; }
    .thna-table { width: 100%; border-collapse: collapse; margin: 14px 0 16px; font-size: 12pt; }
    .thna-table th, .thna-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
    .thna-table thead th { font-weight: 700; text-align: center; height: 26px; }
    .thna-table tbody td { height: 36px; }
    .thna-weight-words { font-size: 13pt; padding-left: 24px; margin: 16px 0 22px; }
    .thna-signatures { display: flex; justify-content: space-between; font-size: 13pt; padding: 0 36px; margin-top: 16px; }
    .thna-sig-col { text-align: center; width: 45%; }
    .thna-sig-title { margin-bottom: 68px; }
    .thna-sig-sub { font-size: 12.5pt; }
  </style>
</head>
<body>
  <div class="no-print-bar">
    <div>
      <div class="no-print-info">🖨️ Danh sách in: ${count} Biên Bản Giao Nhận Hàng Nhập</div>
      <div class="no-print-sub">Tự động nhận diện Form theo Kho Nhận: ${escapeAttr(summaryBreakdown)} (Có thể bấm vào chữ trên phiếu để sửa trực tiếp trước khi in)</div>
    </div>
    <div class="no-print-actions">
      <button type="button" class="btn-top-print" onclick="window.print()">🖨️ In Phiếu (${count})</button>
      <button type="button" class="btn-top-close" onclick="window.close()">✕ Đóng trang in</button>
    </div>
  </div>
  ${pagesHtmlArr.join("")}
  <script>
    window.addEventListener("load", function() {
      setTimeout(function() {
        window.focus();
        window.print();
      }, 300);
    });
  <\/script>
</body>
</html>`;
    }

    function executeBulkBienBanPrint(selectedPos) {
      if (!selectedPos || selectedPos.length === 0) {
        showToast("⚠️ Vui lòng chọn ít nhất 1 lô hàng Nhập để in Biên Bản!", "warning");
        return;
      }

      const tplCounts = {};
      const pagesHtmlArr = selectedPos.map((po, idx) => {
        const tpl = detectBbTemplate(po);
        tplCounts[tpl.name] = (tplCounts[tpl.name] || 0) + 1;
        return buildBienBanPageHtml(po, idx > 0);
      });

      const summaryBreakdown = Object.entries(tplCounts)
        .map(([name, cnt]) => `${cnt} ${name}`)
        .join(" • ");

      showToast(`Đang mở trang in ${selectedPos.length} Biên Bản (${summaryBreakdown})...`, "success");

      const carrierLabel = state.bulkCarrier ? ` - ${state.bulkCarrier}` : "";
      const title = `In ${selectedPos.length} Biên Bản Giao Nhận${carrierLabel} (${formatIsoToVN(state.fromDate)})`;
      const printHtml = buildFullBienBanPrintDocument(pagesHtmlArr, title, summaryBreakdown);

      const w = window.open("", "_blank");
      if (!w) {
        showToast("⚠️ Trình duyệt đang chặn cửa sổ In. Vui lòng cho phép Pop-up trên trang này!", "error");
        return;
      }
      w.document.open();
      w.document.write(printHtml);
      w.document.close();
    }

    function executeBulkPrint() {
      const isNhap = state.xuatNhap === "N";
      const docLabel = isNhap ? "Biên Bản" : "PKL";

      if (!state.bulkSelectedIds || state.bulkSelectedIds.size === 0) {
        showToast(`⚠️ Vui lòng tick chọn ít nhất 1 lô hàng để in ${docLabel}!`, "warning");
        return;
      }

      const selectedPos = [];
      filteredShipments.forEach(item => {
        if (state.bulkSelectedIds.has(String(item.idPo))) {
          selectedPos.push(item);
        }
      });

      if (selectedPos.length === 0) {
        showToast("⚠️ Không tìm thấy thông tin các lô hàng đã chọn!", "warning");
        return;
      }

      // Nếu đang ở Tab Hàng Nhập (N) -> Chạy bộ In Biên Bản tự nhận diện Form theo Kho Nhận
      if (isNhap) {
        executeBulkBienBanPrint(selectedPos);
        return;
      }

      // Nếu đang ở Tab Hàng Xuất (X) -> Chạy bộ In PKL hàng loạt
      showToast(`Đang kết xuất ${selectedPos.length} phiếu PKL (${state.bulkCarrier})...`, "info");

      const offCanvas = document.createElement("canvas");
      const pagesHtmlArr = [];

      try {
        for (let i = 0; i < selectedPos.length; i++) {
          const po = selectedPos[i];
          renderPklToCanvas(po, offCanvas);
          const imgData = offCanvas.toDataURL("image/jpeg", 0.96);
          pagesHtmlArr.push(`
            <div class="print-page">
              <img src="${imgData}" alt="PKL #${po.idPo}" />
            </div>
          `);
        }
      } catch (err) {
        console.error("Lỗi kết xuất canvas PKL:", err);
        showToast("⚠️ Lỗi khi xuất hình ảnh PKL: " + (err?.message || err), "error");
        return;
      }

      const title = `In ${selectedPos.length} Phiếu PKL - ${state.bulkCarrier} (${formatIsoToVN(state.fromDate)})`;
      const printHtml = `<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>${escapeAttr(title)}</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 0;
    }
    html, body {
      margin: 0;
      padding: 0;
      background: #ffffff;
    }
    .print-page {
      width: 100vw;
      height: 100vh;
      page-break-after: always;
      break-after: page;
      display: flex;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      overflow: hidden;
    }
    .print-page:last-child {
      page-break-after: avoid;
      break-after: avoid;
    }
    .print-page img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
    }
  </style>
</head>
<body onload="setTimeout(function(){ window.focus(); window.print(); }, 350);">
  ${pagesHtmlArr.join("")}
</body>
</html>`;

      const w = window.open("", "_blank");
      if (!w) {
        showToast("⚠️ Trình duyệt đang chặn cửa sổ In. Vui lòng cho phép Pop-up trên trang này!", "error");
        return;
      }
      w.document.open();
      w.document.write(printHtml);
      w.document.close();
    }

    // Đóng dropdown khi click ra ngoài
    document.addEventListener("click", function(e) {
      const pop = document.getElementById("bulkDropdownPop");
      const mainBtn = document.getElementById("btnBulkPrintMain");
      const tagBtn = document.getElementById("bulkTagCarrier");
      if (pop && pop.classList.contains("show")) {
        if (!pop.contains(e.target) && (!mainBtn || !mainBtn.contains(e.target)) && (!tagBtn || !tagBtn.contains(e.target))) {
          closeBulkDropdown();
        }
      }
    });

    function fetchJsonpFromGas(url) {
      return new Promise((resolve, reject) => {
        const cbName = "pkl_jsonp_" + Math.round(Math.random() * 1000000);
        window[cbName] = (data) => {
          delete window[cbName];
          if (script && script.parentNode) script.parentNode.removeChild(script);
          resolve(data);
        };
        const script = document.createElement("script");
        script.src = url + (url.includes("?") ? "&" : "?") + "callback=" + cbName;
        script.onerror = () => {
          delete window[cbName];
          if (script && script.parentNode) script.parentNode.removeChild(script);
          reject(new Error("Không kết nối được Google Apps Script"));
        };
        document.body.appendChild(script);
      });
    }

    async function fetchPklFromGoogleSheet() {
      if (!currentPklPo) return;
      const plate = (currentPklPo.soXePo || "").trim();
      if (!plate) {
        showToast("Lô hàng này chưa có Biển số xe để tra cứu!", "warning");
        return;
      }
      const statusText = document.getElementById("pklStatusText");
      statusText.style.color = "#0369a1";
      statusText.textContent = `⏳ Đang lấy PDF từ Google Sheet PKL cho xe ${plate}...`;
      try {
        const res = await fetchJsonpFromGas(`${GAS_PKL_WEBAPP_URL}?plate=${encodeURIComponent(plate)}`);
        if (res && res.status === "success" && res.pdfBase64) {
          customGasPdfBase64 = res.pdfBase64;
          statusText.style.color = "#15803d";
          statusText.textContent = `✅ Đã đồng bộ file PDF từ Sheet PKL cho xe ${plate}! Bấm Tải PDF hoặc Gửi Zalo.`;
          showToast(`Đã lấy PDF từ Google Sheet PKL cho xe ${plate}!`, "success");
        } else {
          statusText.style.color = "#dc2626";
          statusText.textContent = `⚠️ ${res?.message || "Chưa có xe này trên Sheet PKL cũ (Đang dùng bản trực tiếp từ TMS)"}`;
        }
      } catch (err) {
        statusText.style.color = "#dc2626";
        statusText.textContent = `⚠️ Lỗi gọi Sheet PKL cũ: ${err.message} (Đang dùng bản trực tiếp từ TMS)`;
      }
    }

    // =========================================================================
    // 10. ACCOUNT SETTINGS, MULTI-USER LOGIN & PWA INSTALL
    // =========================================================================
    async function submitLoginScreen(e) {
      if (e) e.preventDefault();
      const uName = document.getElementById("loginUsername").value.trim();
      const uPass = document.getElementById("loginPassword").value.trim();
      const errBox = document.getElementById("loginErrorBox");
      const btn = document.getElementById("btnLoginSubmit");

      errBox.style.display = "none";
      if (!uName || !uPass) {
        errBox.textContent = "Vui lòng nhập đầy đủ Tài khoản và Mật khẩu!";
        errBox.style.display = "block";
        return;
      }

      btn.disabled = true;
      btn.textContent = "⏳ Đang xác thực với TMS...";

      try {
        const urlsToTry = [
          "/api/index.php?route=login",
          "/api/login"
        ];
        let loginResult = null;

        for (const u of urlsToTry) {
          try {
            const resp = await fetch(u, {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ username: uName, password: uPass })
            });
            const info = await resp.json();
            loginResult = { status: resp.status, info };
            break;
          } catch (_) {}
        }

        if (loginResult && loginResult.info && !loginResult.info.ok) {
          errBox.textContent = loginResult.info.error || "Sai tên đăng nhập hoặc mật khẩu TMS!";
          errBox.style.display = "block";
          return;
        }

        state.username = uName;
        state.password = uPass;
        state.userString = (loginResult && loginResult.info && loginResult.info.userString)
          ? loginResult.info.userString
          : `${uName}<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.`;
        state.isLoggedIn = true;

        try {
          localStorage.setItem("vs_auth_v2", "1");
          localStorage.setItem("vs_username", state.username);
          localStorage.setItem("vs_password", state.password);
          localStorage.setItem("vs_userString", state.userString);
        } catch (_) {}

        document.getElementById("userLabel").textContent = state.username;
        document.getElementById("loginOverlay").classList.remove("open");
        showToast(`Xin chào ${state.username}! Đang tải dữ liệu...`, "success");
        refreshData(false);
        syncLiveDriverDatabase(false);
      } finally {
        btn.disabled = false;
        btn.textContent = "🔐 ĐĂNG NHẬP HỆ THỐNG";
      }
    }

    function logoutAccount() {
      closeAccountModal();
      state.isLoggedIn = false;
      state.username = "";
      state.password = "";
      state.userString = "";
      try {
        localStorage.removeItem("vs_auth_v2");
        localStorage.removeItem("vs_username");
        localStorage.removeItem("vs_password");
        localStorage.removeItem("vs_userString");
      } catch (_) {}
      document.getElementById("loginUsername").value = "";
      document.getElementById("loginPassword").value = "";
      document.getElementById("loginErrorBox").style.display = "none";
      document.getElementById("loginOverlay").classList.add("open");
      setTimeout(() => document.getElementById("loginUsername").focus(), 150);
    }

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
      const uName = document.getElementById("cfgUsername").value.trim();
      const uPass = document.getElementById("cfgPassword").value.trim();
      if (!uName || !uPass) {
        showToast("Vui lòng nhập đầy đủ Tài khoản và Mật khẩu!", "error");
        return;
      }
      state.username = uName;
      state.password = uPass;
      state.userString = document.getElementById("cfgUserString").value.trim();
      state.apiDate = document.getElementById("cfgApiDate").value || getTodayISO();

      // Try logging in via /api/index.php?route=login
      try {
        const resp = await fetch("/api/index.php?route=login", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username: state.username, password: state.password })
        });
        const info = await resp.json();
        if (!resp.ok || !info.ok) {
          showToast(info.error || "Sai tài khoản hoặc mật khẩu TMS!", "error");
          return;
        }
        if (info.userString) {
          state.userString = info.userString;
          document.getElementById("cfgUserString").value = info.userString;
        }
      } catch (_) {}

      state.isLoggedIn = true;
      try {
        localStorage.setItem("vs_auth_v2", "1");
        localStorage.setItem("vs_username", state.username);
        localStorage.setItem("vs_password", state.password);
        localStorage.setItem("vs_userString", state.userString);
        localStorage.setItem("vs_apiDate", state.apiDate);
      } catch (_) {}

      document.getElementById("userLabel").textContent = state.username;
      document.getElementById("serverDateInfo").textContent = `Mốc dữ liệu API: ${formatIsoToVN(state.apiDate)}`;
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
        alert("📲 Để cài App trên iPhone / iPad:\n\n1. Mở link này bằng trình duyệt Safari.\n2. Bấm biểu tượng Chia sẻ (ô vuông có mũi tên hướng lên ⬆️ ở thanh dưới cùng).\n3. Kéo xuống chọn 'Thêm vào MH chính' (Add to Home Screen) ➔ Bấm 'Thêm'.");
      }
    }

    // Initialize App
    window.addEventListener("DOMContentLoaded", () => {
      const realToday = getTodayISO();
      state.apiDate = realToday;
      if (state.dateMode === "today") {
        state.fromDate = realToday;
        state.toDate = realToday;
      }
      updateQuickDateChipLabels();

      if (isIOS && !isStandalone) {
        document.getElementById("btnInstallPwa").style.display = "inline-flex";
      }
      if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("sw.js").then(reg => reg.update()).catch(() => {});
      }
      document.getElementById("filterFromDate").value = state.fromDate;
      document.getElementById("filterToDate").value = state.toDate;
      document.getElementById("serverDateInfo").textContent = `Mốc dữ liệu API: ${formatIsoToVN(state.apiDate)}`;

      if (!state.isLoggedIn || !state.username) {
        document.getElementById("loginOverlay").classList.add("open");
        setTimeout(() => document.getElementById("loginUsername").focus(), 150);
      } else {
        document.getElementById("userLabel").textContent = state.username;
        refreshData(false);
        syncLiveDriverDatabase(false);
      }
    });
  </script>
</body>
</html>
