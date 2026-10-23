#!/bin/bash
# =============================================
# Backup MySQL Database
# Dùng: ./scripts/backup-db.sh
# =============================================

set -e

# Load .env nếu có
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

DB_NAME="${MYSQL_DATABASE:-portfolio_db}"
DB_USER="${MYSQL_USER:-portfolio_user}"
DB_PASS="${MYSQL_PASSWORD:-}"
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql"

mkdir -p "$BACKUP_DIR"

echo "📦 Đang backup database: ${DB_NAME}..."

docker compose exec -T mysql mysqldump \
    -u"${DB_USER}" \
    -p"${DB_PASS}" \
    --single-transaction \
    --routines \
    --triggers \
    --hex-blob \
    "${DB_NAME}" > "${BACKUP_FILE}"

gzip "${BACKUP_FILE}"
echo "✅ Backup xong: ${BACKUP_FILE}.gz"
echo "   Kích thước: $(du -sh ${BACKUP_FILE}.gz | cut -f1)"

# Giữ tối đa 10 bản backup gần nhất
ls -t "${BACKUP_DIR}"/*.sql.gz 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true
echo "🧹 Đã xóa backup cũ (giữ 10 bản gần nhất)"
