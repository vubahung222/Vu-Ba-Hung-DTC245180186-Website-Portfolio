#!/bin/bash
# =============================================
# Restore MySQL Database từ backup
# Dùng: ./scripts/restore-db.sh backups/portfolio_db_20250101_120000.sql.gz
# =============================================

set -e

BACKUP_FILE="$1"

if [ -z "$BACKUP_FILE" ]; then
    echo "❌ Cần chỉ định file backup!"
    echo "   Dùng: ./scripts/restore-db.sh backups/portfolio_db_YYYYMMDD_HHMMSS.sql.gz"
    exit 1
fi

if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Không tìm thấy file: ${BACKUP_FILE}"
    exit 1
fi

# Load .env
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

DB_NAME="${MYSQL_DATABASE:-portfolio_db}"
DB_USER="${MYSQL_USER:-portfolio_user}"
DB_PASS="${MYSQL_PASSWORD:-}"

echo "⚠️  CẢNH BÁO: Sẽ GKHI ĐÈ dữ liệu hiện tại của database '${DB_NAME}'!"
read -p "Bạn có chắc muốn tiếp tục? (yes/N): " confirm
if [ "$confirm" != "yes" ]; then
    echo "❌ Đã hủy."
    exit 0
fi

echo "📂 Đang restore từ: ${BACKUP_FILE}..."

gunzip -c "${BACKUP_FILE}" | docker compose exec -T mysql mysql \
    -u"${DB_USER}" \
    -p"${DB_PASS}" \
    "${DB_NAME}"

echo "✅ Restore thành công!"
