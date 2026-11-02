#!/bin/bash
echo "===================================================="
echo "🚀 ĐANG ĐỒNG BỘ & SỬA LỖI HỆ THỐNG VIETSUN TMS..."
echo "===================================================="

WEB_ROOT="/www/wwwroot/vs.huuhoa.net"
cd "$WEB_ROOT" || exit 1

# 1. Tải code mới nhất từ GitHub
rm -rf repo_temp
git clone https://github.com/jaylinhsep/vietsun-tms-mobile.git repo_temp
cp -rf repo_temp/* "$WEB_ROOT/"
rm -rf repo_temp

# 2. Nạp dữ liệu mẫu Vendor & Tài xế vào MySQL
mysql -u vietsun_vendor -phuyhoang106 vietsun_vendor < "$WEB_ROOT/server/seed_data.sql" 2>/dev/null

# 3. Chuẩn hóa quyền file cho OpenLiteSpeed (Thư mục 755, File 644, Owner www:www)
chattr -i "$WEB_ROOT/.user.ini" 2>/dev/null
chown -R www:www "$WEB_ROOT"
find "$WEB_ROOT" -type d -exec chmod 755 {} \;
find "$WEB_ROOT" -type f -exec chmod 644 {} \;
chattr +i "$WEB_ROOT/.user.ini" 2>/dev/null

# 4. Khởi động lại Node.js Backend ở cổng 3005
sed -i 's/PORT=3000/PORT=3005/g' "$WEB_ROOT/server/.env" 2>/dev/null
pkill -f "node.*server.js" 2>/dev/null
cd "$WEB_ROOT/server" && nohup node server.js > /tmp/vietsun_node.log 2>&1 &

# 5. Reload lại OpenLiteSpeed
/usr/local/lsws/bin/lswsctrl reload 2>/dev/null || systemctl reload lsws 2>/dev/null || /etc/init.d/lsws reload 2>/dev/null

echo "===================================================="
echo "✅ HOÀN TẤT 100%! HỆ THỐNG ĐÃ SẴN SÀNG:"
echo "👉 Trang điều hành: https://vs.huuhoa.net"
echo "👉 App Tài xế:      https://vs.huuhoa.net/driver"
echo "👉 Cổng Vendor:     https://vs.huuhoa.net/vendor"
echo "===================================================="
