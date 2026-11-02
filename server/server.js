const express = require('express');
const cors = require('cors');
const path = require('path');
const jwt = require('jsonwebtoken');
require('dotenv').config();

const { pool, query } = require('./db');
const { fetchVietsunCycle, pushUpdateToVietsun } = require('./vietsun_client');

const app = express();
const PORT = process.env.PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'vsho_tms_secret_key_2026_@vietsun';

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static frontend files (PWA)
app.use(express.static(path.join(__dirname, '..')));

// Helper: Generate JWT token
function generateToken(payload) {
  return jwt.sign(payload, JWT_SECRET, { expiresIn: '30d' });
}

// Middleware: Authenticate Token
function authenticateToken(req, res, next) {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];
  if (!token) return res.status(401).json({ ok: false, error: 'Thiếu Token xác thực' });

  jwt.verify(token, JWT_SECRET, (err, user) => {
    if (err) return res.status(403).json({ ok: false, error: 'Token không hợp lệ hoặc đã hết hạn' });
    req.user = user;
    next();
  });
}

// In-memory cache for Vietsun TMS shipments
let cachedShipments = [];
let lastSyncTime = 0;

// =============================================================================
// 1. SYNC VIETSUN SHIPMENTS IN BACKGROUND (EVERY 3 MINUTES)
// =============================================================================
async function syncVietsunShipments() {
  try {
    const today = new Date().toISOString().slice(0, 10);
    const data = await fetchVietsunCycle(today);
    if (Array.isArray(data) && data.length > 0) {
      cachedShipments = data;
      lastSyncTime = Date.now();
      console.log(`[SyncEngine] Successfully synced ${cachedShipments.length} shipments from Vietsun TMS.`);
    }
  } catch (err) {
    console.error('[SyncEngine] Sync error:', err.message);
  }
}

// Initial sync and scheduled interval
syncVietsunShipments();
setInterval(syncVietsunShipments, 3 * 60 * 1000); // 3 phút / lần

// =============================================================================
// 2. AUTHENTICATION APIS (VENDOR & DRIVER)
// =============================================================================

// Driver Login (SĐT + Mã PIN)
app.post('/api/auth/driver-login', async (req, res) => {
  const { phone, pin } = req.body;
  if (!phone || !pin) {
    return res.status(400).json({ ok: false, error: 'Vui lòng nhập Số điện thoại và Mã PIN' });
  }

  try {
    const drivers = await query(
      'SELECT d.*, v.vendor_name FROM drivers d JOIN vendors v ON d.vendor_id = v.id WHERE d.phone = ? AND d.pin_code = ? AND d.is_active = 1 LIMIT 1',
      [phone.trim(), pin.trim()]
    );

    if (!drivers || drivers.length === 0) {
      return res.status(401).json({ ok: false, error: 'Số điện thoại hoặc Mã PIN không đúng' });
    }

    const driver = drivers[0];
    const token = generateToken({
      role: 'driver',
      driverId: driver.id,
      vendorId: driver.vendor_id,
      vendorName: driver.vendor_name,
      driverName: driver.driver_name,
      plate: driver.assigned_plate
    });

    res.json({
      ok: true,
      token,
      driver: {
        id: driver.id,
        name: driver.driver_name,
        phone: driver.phone,
        plate: driver.assigned_plate,
        vendor: driver.vendor_name
      }
    });
  } catch (err) {
    console.error('Driver login error:', err);
    res.status(500).json({ ok: false, error: 'Lỗi máy chủ khi đăng nhập' });
  }
});

// Vendor Owner Login (Vendor Code + Password)
app.post('/api/auth/vendor-login', async (req, res) => {
  const { code, password } = req.body;
  if (!code || !password) {
    return res.status(400).json({ ok: false, error: 'Vui lòng nhập Mã nhà xe và Mật khẩu' });
  }

  try {
    const vendors = await query(
      'SELECT * FROM vendors WHERE vendor_code = ? AND is_active = 1 LIMIT 1',
      [code.trim().toUpperCase()]
    );

    if (!vendors || vendors.length === 0 || vendors[0].password_hash !== password) {
      return res.status(401).json({ ok: false, error: 'Sai Mã nhà xe hoặc Mật khẩu' });
    }

    const vendor = vendors[0];
    const token = generateToken({
      role: 'vendor',
      vendorId: vendor.id,
      vendorName: vendor.vendor_name,
      vendorCode: vendor.vendor_code
    });

    res.json({
      ok: true,
      token,
      vendor: {
        id: vendor.id,
        name: vendor.vendor_name,
        code: vendor.vendor_code
      }
    });
  } catch (err) {
    console.error('Vendor login error:', err);
    res.status(500).json({ ok: false, error: 'Lỗi máy chủ khi đăng nhập' });
  }
});

// =============================================================================
// 3. DRIVER APIS (CHỈ THẤY XE MÌNH PHỤ TRÁCH & NHẬP NHANH CONT/SEAL/TIẾN ĐỘ)
// =============================================================================

// Lấy danh sách chuyến hôm nay cho tài xế
app.get('/api/driver/my-trips', authenticateToken, async (req, res) => {
  if (req.user.role !== 'driver') {
    return res.status(403).json({ ok: false, error: 'Quyền truy cập không hợp lệ' });
  }

  const driverPlate = (req.user.plate || '').replace(/\s+/g, '').toUpperCase();

  // Lọc từ cachedShipments khớp với biển số xe của tài xế
  const myTrips = cachedShipments.filter(item => {
    const itemPlate = (item.soXePo || '').replace(/\s+/g, '').toUpperCase();
    return itemPlate && itemPlate.includes(driverPlate);
  });

  res.json({
    ok: true,
    trips: myTrips,
    driverInfo: {
      name: req.user.driverName,
      plate: req.user.plate,
      vendor: req.user.vendorName
    }
  });
});

// Tài xế cập nhật thông tin chuyến (Số Cont, Số Seal, Giờ Cảng...)
// Tự động đẩy ngược về Vietsun TMS
app.post('/api/driver/update-trip', authenticateToken, async (req, res) => {
  if (req.user.role !== 'driver') {
    return res.status(403).json({ ok: false, error: 'Chỉ tài xế mới được thao tác' });
  }

  const { idPo, soCont, soSeal, portTime, cangDen } = req.body;
  if (!idPo) return res.status(400).json({ ok: false, error: 'Thiếu mã idPo của chuyến xe' });

  try {
    const pushTasks = [];

    // loai 4: Số Cont
    if (soCont !== undefined) {
      pushTasks.push(pushUpdateToVietsun({ cv: 4, loai: 4, iddt: idPo, cot: 1, thongtin: soCont.trim() }));
    }
    // loai 5: Số Seal
    if (soSeal !== undefined) {
      pushTasks.push(pushUpdateToVietsun({ cv: 4, loai: 5, iddt: idPo, cot: 1, thongtin: soSeal.trim() }));
    }
    // loai 6: Giờ Cảng / Port Time
    if (portTime !== undefined) {
      pushTasks.push(pushUpdateToVietsun({ cv: 4, loai: 6, iddt: idPo, cot: 1, thongtin: portTime.trim() }));
    }
    // loai 30: Cảng đến
    if (cangDen !== undefined) {
      pushTasks.push(pushUpdateToVietsun({ cv: 4, loai: 30, iddt: idPo, cot: 1, thongtin: cangDen.trim() }));
    }

    await Promise.all(pushTasks);

    // Kích hoạt đồng bộ lại bộ nhớ đệm
    setTimeout(syncVietsunShipments, 1500);

    res.json({ ok: true, message: 'Đã cập nhật dữ liệu và đẩy ngược về Vietsun TMS thành công!' });
  } catch (err) {
    console.error('Driver update error:', err);
    res.status(500).json({ ok: false, error: 'Lỗi khi cập nhật về Vietsun TMS' });
  }
});

// Tài xế / Xe nhập chi phí phát sinh (Neo xe, Nâng hạ, Rửa cont...)
app.post('/api/driver/add-expense', authenticateToken, async (req, res) => {
  const { idPo, expenseType, amount, note, receiptImageUrl } = req.body;
  if (!idPo || !expenseType || !amount) {
    return res.status(400).json({ ok: false, error: 'Vui lòng nhập đầy đủ loại chi phí và số tiền' });
  }

  try {
    await query(
      'INSERT INTO trip_expenses (booking_id_po, driver_id, expense_type, amount, note, receipt_image_url) VALUES (?, ?, ?, ?, ?, ?)',
      [idPo, req.user.driverId || null, expenseType, amount, note || '', receiptImageUrl || '']
    );

    res.json({ ok: true, message: 'Đã ghi nhận chi phí phát sinh!' });
  } catch (err) {
    console.error('Add expense error:', err);
    res.status(500).json({ ok: false, error: 'Lỗi khi lưu chi phí phát sinh' });
  }
});

// =============================================================================
// 4. VENDOR APIS (QUẢN LÝ ĐỘI XE, BÁO CÁO DOANH THU & ĐỐI CHIẾU CÔNG NỢ)
// =============================================================================

// Lấy danh sách booking của toàn bộ đội xe Vendor
app.get('/api/vendor/trips', authenticateToken, async (req, res) => {
  if (req.user.role !== 'vendor') {
    return res.status(403).json({ ok: false, error: 'Quyền truy cập không hợp lệ' });
  }

  const vName = (req.user.vendorName || '').toUpperCase();

  // Lọc chỉ các chuyến thuộc Vendor này
  const vendorTrips = cachedShipments.filter(item => {
    const itemVendor = (item.donViVcPo || '').toUpperCase();
    return itemVendor && itemVendor.includes(vName);
  });

  res.json({
    ok: true,
    total: vendorTrips.length,
    trips: vendorTrips
  });
});

// Báo cáo Doanh thu & Đối chiếu Công nợ (kèm cước Debit cố định & điều chỉnh)
app.get('/api/vendor/revenue-report', authenticateToken, async (req, res) => {
  if (req.user.role !== 'vendor') {
    return res.status(403).json({ ok: false, error: 'Quyền truy cập không hợp lệ' });
  }

  const { fromDate, toDate } = req.query;

  try {
    // Lấy các booking đã hoàn thành trong bảng completed_bookings
    let sql = 'SELECT * FROM completed_bookings WHERE vendor_name = ?';
    const params = [req.user.vendorName];

    if (fromDate && toDate) {
      sql += ' AND ngay_tra_hang BETWEEN ? AND ?';
      params.push(fromDate, toDate);
    }
    sql += ' ORDER BY ngay_tra_hang DESC, id DESC';

    const completed = await query(sql, params);

    // Lấy chi phí phát sinh kèm theo
    const expenses = await query(
      'SELECT te.* FROM trip_expenses te JOIN completed_bookings cb ON te.booking_id_po = cb.id_po WHERE cb.vendor_name = ?',
      [req.user.vendorName]
    );

    // Tính tổng tiền
    let totalRevenue = 0;
    let totalExpenses = 0;

    completed.forEach(item => {
      const finalRate = item.debit_rate_adjusted !== null ? Number(item.debit_rate_adjusted) : Number(item.debit_rate);
      totalRevenue += finalRate;
    });

    expenses.forEach(exp => {
      if (exp.status === 'APPROVED' || exp.status === 'PENDING') {
        totalExpenses += Number(exp.amount);
      }
    });

    res.json({
      ok: true,
      summary: {
        totalTrips: completed.length,
        totalRevenue,
        totalExpenses,
        netTotal: totalRevenue + totalExpenses
      },
      bookings: completed,
      expenses
    });
  } catch (err) {
    console.error('Revenue report error:', err);
    res.status(500).json({ ok: false, error: 'Lỗi khi tính toán báo cáo doanh thu' });
  }
});

// Điều chỉnh giá cước Debit cho một chuyến xe
app.post('/api/vendor/adjust-rate', authenticateToken, async (req, res) => {
  if (req.user.role !== 'vendor') {
    return res.status(403).json({ ok: false, error: 'Quyền truy cập không hợp lệ' });
  }

  const { idPo, adjustedRate, reason } = req.body;
  if (!idPo || adjustedRate === undefined) {
    return res.status(400).json({ ok: false, error: 'Thiếu thông tin điều chỉnh cước' });
  }

  try {
    await query(
      'UPDATE completed_bookings SET debit_rate_adjusted = ?, adjust_reason = ? WHERE id_po = ? AND vendor_name = ?',
      [adjustedRate, reason || '', idPo, req.user.vendorName]
    );

    res.json({ ok: true, message: 'Đã cập nhật giá cước đối soát thành công!' });
  } catch (err) {
    console.error('Adjust rate error:', err);
    res.status(500).json({ ok: false, error: 'Lỗi khi cập nhật giá cước' });
  }
});

// Start Server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`====================================================`);
  console.log(`🚀 VIETSUN VENDOR HUB & TWO-WAY SYNC SERVER RUNNING`);
  console.log(`📡 URL: http://0.0.0.0:${PORT}`);
  console.log(`🔗 Database: ${process.env.DB_NAME || 'vietsun_vendor'}`);
  console.log(`====================================================`);
});
