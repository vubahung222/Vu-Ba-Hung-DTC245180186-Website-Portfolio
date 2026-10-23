# 🌐 Portfolio Website - Đề 13
**Môn:** Triển khai và Quản trị Hệ thống Phần mềm  
**Sinh viên:** Vũ Bá Hùng — MSSV: DTC245180186 — Lớp: KTPM K23C  
**Giảng viên:** Hoàng Văn Tâm  
**Trường/Khoa:** Trường Đại học Công nghệ Thông tin và Truyền thông - Đại học Thái Nguyên

---

## 🏗️ Kiến trúc hệ thống

```
Internet
    │
    ▼
[Nginx :80/:443]
  ├─→ Redirect HTTP → HTTPS
  ├─→ FastCGI → [PHP-FPM :9000]
  │              └─→ [MySQL :3306]
  └─→ Static files (CSS/JS/Uploads)

Monitoring Network:
  [Prometheus] ← scrape ← [cAdvisor, nginx-exporter, mysql-exporter]
  [Grafana] → query → [Prometheus, Loki]
  [Loki] ← push ← [Promtail]
  [Promtail] ← tail ← [Nginx logs, Docker logs]
```

## 📦 Services & Ports

| Service | Image | Port (host) | Mục đích |
|---------|-------|-------------|----------|
| nginx | nginx:1.25.4-alpine | 80, 443 | Reverse proxy, HTTPS |
| php | php:8.2.18-fpm-alpine | internal | PHP-FPM application |
| mysql | mysql:8.0.36 | internal only | Database |
| phpmyadmin | phpmyadmin:5.2.1 | 127.0.0.1:8080 | DB admin UI |
| prometheus | prom/prometheus:v2.50.1 | 127.0.0.1:9090 | Metrics collection |
| grafana | grafana/grafana:10.3.3 | 127.0.0.1:3000 | Visualization |
| loki | grafana/loki:2.9.4 | 127.0.0.1:3100 | Log aggregation |
| promtail | grafana/promtail:2.9.4 | internal | Log shipping |
| cadvisor | gcr.io/cadvisor/cadvisor:v0.47.2 | internal | Container metrics |
| nginx-exporter | nginx/nginx-prometheus-exporter:1.1.0 | internal | Nginx metrics |
| mysql-exporter | prom/mysqld-exporter:v0.15.1 | internal | MySQL metrics |

## 🌐 Networks

| Network | Services |
|---------|----------|
| portfolio_frontend | nginx |
| portfolio_backend | nginx, php, mysql, phpmyadmin, prometheus, mysql-exporter |
| portfolio_monitoring | nginx, prometheus, grafana, loki, promtail, cadvisor, nginx-exporter, mysql-exporter |

## ⚡ Yêu cầu môi trường

- Docker Engine ≥ 24.0
- Docker Compose v2 (plugin, không phải `docker-compose` cũ)
- Windows + Docker Desktop (WSL2 backend) **hoặc** Linux/macOS
- OpenSSL (để tạo SSL certificate, có sẵn trong Git for Windows)
- Tối thiểu 4GB RAM khả dụng

## 🚀 Triển khai lần đầu

### 1. Clone repository
```bash
git clone https://github.com/vubahung222/portfolio-DTC245180186.git
cd portfolio-DTC245180186
```

### 2. Tạo file `.env`
```bash
cp .env.example .env
# Chỉnh sửa .env — thay đổi TẤT CẢ mật khẩu!
nano .env   # hoặc notepad .env trên Windows
```

**Các giá trị BẮT BUỘC phải đổi trong `.env`:**
```
MYSQL_ROOT_PASSWORD=<mật_khẩu_mạnh>
MYSQL_PASSWORD=<mật_khẩu_mạnh>
GF_SECURITY_ADMIN_PASSWORD=<mật_khẩu_mạnh>
APP_SECRET_KEY=<chuỗi_ngẫu_nhiên_32_ký_tự>
ADMIN_PASSWORD=<mật_khẩu_admin>
```

### 3. Tạo SSL certificate (tự ký)
**Trên Linux/macOS/WSL2/Git Bash:**
```bash
chmod +x scripts/generate-ssl.sh
bash scripts/generate-ssl.sh
```

**Trên Windows (PowerShell — cần OpenSSL):**
```powershell
# Nếu có Git for Windows:
& "C:\Program Files\Git\usr\bin\openssl.exe" req -x509 -nodes -days 365 -newkey rsa:2048 `
    -keyout nginx/ssl/server.key `
    -out nginx/ssl/server.crt `
    -subj "/C=VN/ST=HCM/L=HCM/O=Portfolio/CN=localhost"
```

### 4. Build và khởi động
```bash
# Validate config
docker compose config

# Build và start toàn bộ
docker compose up -d --build

# Kiểm tra status
docker compose ps
```

### 5. Tạo tài khoản admin
```bash
# Đợi ~30s để MySQL khởi động xong, rồi chạy:
docker compose exec php php /var/www/html/scripts/create-admin.php
```

Hoặc chỉ định email/password:
```bash
docker compose exec php php /var/www/html/scripts/create-admin.php \
    --email=admin@example.com \
    --password=MyStrongPass123
```

### 6. Truy cập các URL

| URL | Dịch vụ |
|-----|---------|
| https://localhost | Website portfolio |
| https://localhost/admin/login | Trang đăng nhập admin |
| http://localhost:8080 | phpMyAdmin |
| http://localhost:3000 | Grafana |
| http://localhost:9090 | Prometheus |

> ⚠️ Trình duyệt sẽ cảnh báo SSL tự ký → nhấn "Advanced" → "Proceed to localhost"

---

## 🔧 Các lệnh vận hành

### Xem logs
```bash
docker compose logs -f              # Tất cả
docker compose logs -f nginx        # Chỉ Nginx
docker compose logs -f php          # Chỉ PHP
docker compose logs -f mysql        # Chỉ MySQL
```

### Restart service
```bash
docker compose restart nginx        # Restart Nginx
docker compose restart php          # Restart PHP
docker compose restart              # Restart tất cả
```

### Dừng hệ thống
```bash
# Dừng (giữ nguyên data)
docker compose stop

# Dừng và xóa containers (GIỮ volumes/data)
docker compose down

# ⚠️ XÓA TOÀN BỘ DATA — chỉ dùng khi reset hoàn toàn
docker compose down -v
```

### Cập nhật application
```bash
git pull
docker compose up -d --build php
```

---

## 📦 Backup & Restore

### Backup database
```bash
chmod +x scripts/backup-db.sh
./scripts/backup-db.sh
# File backup lưu tại: backups/portfolio_db_YYYYMMDD_HHMMSS.sql.gz
```

### Restore database
```bash
./scripts/restore-db.sh backups/portfolio_db_20251001_120000.sql.gz
```

---

## 📊 Prometheus & Grafana

### Kiểm tra targets
```
http://localhost:9090/targets
```
Tất cả targets phải ở trạng thái **UP**.

### PromQL mẫu

```promql
# CPU usage của tất cả containers (%)
sum(rate(container_cpu_usage_seconds_total{name=~"portfolio_.+"}[2m])) by (name) * 100

# Memory usage
container_memory_usage_bytes{name=~"portfolio_.+"}

# Nginx active connections
nginx_connections_active

# MySQL active threads
mysql_global_status_threads_connected

# Request rate qua Nginx
rate(nginx_http_requests_total[2m])

# MySQL query rate
rate(mysql_global_status_queries[2m])
```

---

## 📋 Loki & LogQL

### Truy vấn mẫu

**1. Xem log theo service:**
```logql
{service="nginx"} | json | line_format "{{.remote_addr}} {{.method}} {{.uri}} {{.status}}"
```

**2. Lọc HTTP errors (4xx, 5xx):**
```logql
{service="nginx"} | json | status >= 400
```

**3. Đếm requests theo khoảng thời gian:**
```logql
count_over_time({service="nginx"}[5m])
```

**4. Lọc lỗi từ PHP container:**
```logql
{container="portfolio_php"} |= "ERROR"
```

**5. Đếm lỗi 500 trong 10 phút:**
```logql
count_over_time({service="nginx"} | json | status = "500" [10m])
```

**Tạo traffic test:**
```bash
# Tạo một số request để có log
for i in $(seq 1 20); do
    curl -sk https://localhost/ > /dev/null
    curl -sk https://localhost/lien-he > /dev/null
    curl -sk https://localhost/khong-ton-tai > /dev/null
done
```

---

## 🔒 Hardening

| Biện pháp | Service | Cấu hình | Kiểm tra |
|-----------|---------|----------|---------|
| Non-root user | php | `USER appuser` (uid 1000) trong Dockerfile | `docker exec portfolio_php id` |
| no-new-privileges | Tất cả | `security_opt: - no-new-privileges:true` | `docker inspect portfolio_php \| grep NoNewPrivileges` |
| Network isolation | Tất cả | Mạng frontend/backend/monitoring riêng | `docker network ls` |
| DB không public | mysql | Không có ports mapping ra host | `docker compose ps mysql` |
| Security headers | nginx | X-Content-Type-Options, X-Frame-Options, CSP | `curl -I https://localhost` |
| Password hashing | php | bcrypt cost=12 | Xem `AdminUser::create()` |
| Prepared statements | php | PDO với `ATTR_EMULATE_PREPARES=false` | Xem `Core/Database.php` |
| Upload validation | php | MIME check thực tế (`finfo`), không tin header | Xem `AdminController.php` |
| CSRF protection | php | Token trong mọi form POST | Xem `Core/Csrf.php` |
| Rate limiting | nginx | login: 5r/m, contact: 3r/m, general: 30r/s | Xem `nginx/conf.d/default.conf` |
| Chặn .env/.git | nginx | `location ~ \.env` deny all | `curl -I https://localhost/.env` → 403 |
| Session hardening | php | httponly, samesite=Strict, secure cookie | Xem `Core/Session.php` |

### Kiểm chứng nhanh
```bash
# 1. PHP chạy non-root
docker exec portfolio_php id
# → uid=1000(appuser) gid=1000(appgroup)

# 2. Security headers
curl -sk -I https://localhost | grep -E "X-Content|X-Frame|CSP|Referrer"

# 3. .env bị chặn
curl -sk -I https://localhost/.env
# → HTTP/2 403

# 4. MySQL không public
docker compose ps mysql | grep "0.0.0.0:3306"
# → Không có output (không public)

# 5. no-new-privileges
docker inspect portfolio_php | python -c "import sys,json; d=json.load(sys.stdin)[0]; print(d['HostConfig']['SecurityOpt'])"
# → ['no-new-privileges:true']
```

---

## 🛠️ Troubleshooting

**MySQL chưa sẵn sàng:**
```bash
docker compose logs mysql | tail -20
# Đợi xuất hiện "ready for connections"
```

**PHP không kết nối được MySQL:**
```bash
docker compose exec php php -r "new PDO('mysql:host=mysql;dbname=portfolio_db', 'portfolio_user', 'AppPass@2025!');"
```

**Upload dir không ghi được:**
```bash
docker exec portfolio_php ls -la /var/www/html/uploads/
# Phải thuộc appuser:appgroup và có quyền 755
```

**Nginx lỗi SSL:**
```bash
docker compose logs nginx | grep error
ls nginx/ssl/  # Phải có server.crt và server.key
```

**Prometheus target DOWN:**
```bash
# Kiểm tra service có chạy không
docker compose ps
# Kiểm tra network
docker exec portfolio_prometheus wget -qO- http://mysql-exporter:9104/metrics | head -5
```

---

## 📂 Cấu trúc thư mục

```
├── app/                    # PHP Application
│   ├── Dockerfile
│   ├── php.ini
│   ├── php-fpm.conf
│   ├── public/             # Web root (index.php, CSS, JS)
│   ├── scripts/            # Utility scripts (create-admin.php)
│   ├── src/                # Source code
│   │   ├── bootstrap.php
│   │   ├── routes.php
│   │   ├── Core/           # Framework core (Database, Router, Auth...)
│   │   ├── controllers/    # HTTP Controllers
│   │   ├── models/         # Database models
│   │   └── views/          # PHP templates
│   └── uploads/            # User uploads (not in git)
├── nginx/
│   ├── nginx.conf          # Main Nginx config
│   ├── conf.d/default.conf # Virtual host
│   └── ssl/                # SSL certs (không commit .key)
├── mysql/
│   └── init/               # Init SQL scripts
│       ├── 01-schema.sql   # Schema + seed data
│       └── 02-create-exporter.sql
├── monitoring/
│   ├── prometheus/prometheus.yml
│   ├── grafana/
│   │   ├── provisioning/   # Auto-provision datasources & dashboards
│   │   └── dashboards/     # Dashboard JSON files
│   ├── loki/loki.yml
│   ├── promtail/promtail.yml
│   └── mysql-exporter.cnf
├── scripts/
│   ├── generate-ssl.sh
│   ├── backup-db.sh
│   ├── restore-db.sh
│   └── setup.bat           # Windows quick setup
├── .env.example
├── .gitignore
├── .dockerignore
└── docker-compose.yml
```

---

## 📄 Tài liệu tham khảo

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [PHP 8.2 Manual](https://www.php.net/manual/en/)
- [MySQL 8.0 Reference](https://dev.mysql.com/doc/refman/8.0/en/)
- [Prometheus Documentation](https://prometheus.io/docs/)
- [Grafana Documentation](https://grafana.com/docs/)
- [Loki Documentation](https://grafana.com/docs/loki/)
- [OWASP PHP Security](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
