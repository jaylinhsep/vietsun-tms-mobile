const fetch = require('node-fetch');
const fs = require('fs');
const path = require('path');
require('dotenv').config();

const REMOTE_BASE = process.env.VIETSUN_BASE || 'https://vsho.vietsunlogistics.com';
const USERNAME = process.env.VIETSUN_USER || 'trucking14';
const PASSWORD = process.env.VIETSUN_PASS || '14';

let sessionCookie = null;
let lastLoginTime = 0;

// Helper: Ensure authenticated session with Vietsun TMS
async function ensureVietsunLogin() {
  const now = Date.now();
  if (sessionCookie && now - lastLoginTime < 1800000) { // Valid 30 mins
    return sessionCookie;
  }

  try {
    const postBody = new URLSearchParams();
    postBody.append('username', USERNAME);
    postBody.append('password', PASSWORD);
    postBody.append('login', 'ĐĂNG NHẬP');

    const res = await fetch(`${REMOTE_BASE}/0login.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'User-Agent': 'Mozilla/5.0 (NodeJS VietsunSyncHub/1.0)'
      },
      body: postBody.toString(),
      redirect: 'manual'
    });

    const setCookies = res.headers.raw()['set-cookie'];
    if (setCookies && setCookies.length > 0) {
      sessionCookie = setCookies.map(c => c.split(';')[0]).join('; ');
      lastLoginTime = now;
      console.log('[VietsunSync] Logged in successfully. Cookie cached.');
      return sessionCookie;
    }
  } catch (err) {
    console.error('[VietsunSync] Remote login error:', err.message);
  }
  return sessionCookie;
}

// 1. Kéo dữ liệu chu kỳ từ Vietsun (return_json.php)
async function fetchVietsunCycle(cycleIsoDate) {
  let cookie = await ensureVietsunLogin();
  const url = `${REMOTE_BASE}/return_json.php?ngay=${encodeURIComponent(cycleIsoDate)}`;
  
  let res = await fetch(url, {
    headers: {
      'Cookie': cookie || '',
      'User-Agent': 'Mozilla/5.0 (NodeJS VietsunSyncHub/1.0)'
    }
  });

  // Re-login if redirected or expired
  if (res.status === 302 || res.headers.get('location')) {
    sessionCookie = null;
    cookie = await ensureVietsunLogin();
    res = await fetch(url, {
      headers: {
        'Cookie': cookie || '',
        'User-Agent': 'Mozilla/5.0 (NodeJS VietsunSyncHub/1.0)'
      }
    });
  }

  const raw = await res.text();
  try {
    const data = JSON.parse(raw.trim());
    return Array.isArray(data) ? data : [];
  } catch (parseErr) {
    console.error('[VietsunSync] JSON parse error:', parseErr.message);
    return [];
  }
}

// 2. Đẩy ngược cập nhật về Vietsun TMS (index-crud.php)
// Schema: cv=4, loai=LoaiCot, iddt=ID, cot=1/0, thongtin=GiaTri
// Các loại cột:
// loai 2: soXePo
// loai 3: donViVcPo
// loai 4: soContPo
// loai 5: soSealPo
// loai 6: portTimePo
// loai 7: ngayTraHang
// loai 30: cangDenPo (Cảng dỡ)
// loai 19: Xóa booking (cv=4, iddt=1, cot=0, thongtin=idPo)
async function pushUpdateToVietsun(params) {
  let cookie = await ensureVietsunLogin();
  const url = `${REMOTE_BASE}/index-crud.php`;

  const bodyParams = new URLSearchParams();
  for (const [key, val] of Object.entries(params)) {
    bodyParams.append(key, val !== undefined && val !== null ? String(val) : '');
  }

  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Cookie': cookie || '',
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'User-Agent': 'Mozilla/5.0 (NodeJS VietsunSyncHub/1.0)'
    },
    body: bodyParams.toString()
  });

  const text = await res.text();
  return text;
}

module.exports = {
  fetchVietsunCycle,
  pushUpdateToVietsun
};
