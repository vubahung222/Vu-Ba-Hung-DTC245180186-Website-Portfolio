#!/bin/bash
# =============================================
# Tạo chứng chỉ SSL tự ký cho Nginx
# Dùng cho môi trường dev/demo
# =============================================

set -e

CERT_DIR="$(dirname "$0")/../nginx/ssl"
mkdir -p "$CERT_DIR"

echo "📜 Tạo SSL certificate tự ký..."
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$CERT_DIR/server.key" \
    -out    "$CERT_DIR/server.crt" \
    -subj "/C=VN/ST=Ho Chi Minh/L=Ho Chi Minh City/O=Portfolio Student/OU=Dev/CN=localhost" \
    -addext "subjectAltName=DNS:localhost,IP:127.0.0.1"

chmod 600 "$CERT_DIR/server.key"
chmod 644 "$CERT_DIR/server.crt"

echo "✅ SSL certificate đã tạo tại: $CERT_DIR/"
echo "   server.crt (public)"
echo "   server.key (private — KHÔNG commit vào Git)"
echo ""
echo "⚠️  Khi truy cập https://localhost, trình duyệt sẽ cảnh báo."
echo "   Nhấn 'Advanced > Proceed to localhost' để tiếp tục."
