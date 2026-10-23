# 🌐 Vũ Bá Hùng DTC245180186 — Website Portfolio Đề 13

**Môn:** Triển khai và Quản trị Hệ thống Phần mềm  
**Sinh viên:** Vũ Bá Hùng — MSSV: DTC245180186 — Lớp: KTPM K23C  
**Giảng viên:** Hoàng Văn Tâm  
**Trường:** Trường Đại học Công nghệ Thông tin và Truyền thông - Đại học Thái Nguyên  
**GitHub:** https://github.com/vubahung222/Vu-Ba-Hung-DTC245180186-Website-Portfolio

---

## 📋 Mục lục

- [Kiến trúc hệ thống](#-kiến-trúc-hệ-thống)
- [Services & Ports](#-services--ports)
- [Yêu cầu môi trường](#-yêu-cầu-môi-trường)
- [Triển khai lần đầu](#-triển-khai-lần-đầu)
- [Các URL truy cập](#-các-url-truy-cập)
- [Lệnh vận hành](#-lệnh-vận-hành)
- [Prometheus & Grafana](#-prometheus--grafana)
- [Loki & LogQL](#-loki--logql)
- [Hardening](#-hardening)
- [Commit History](#-commit-history)
- [Troubleshooting](#️-troubleshooting)

---

## 🏗️ Kiến trúc hệ thống

```
Internet
    │
    ▼
┌─────────────────────────────────────────────────────────┐
│                    Docker Host                          │
│                                                         │
│  ┌─────────────┐   ┌──────────────────────────────┐    │
│  │  FRONTEND   │   │         BACKEND              │    │
│  │  Network    │   │         Network              │    │
│  │             │   │                              │    │
│  │[Nginx :443] ├───┤→ [PHP-FPM :9000]→[MySQL]    │    │
│  │  HTTP→HTTPS │   │   [phpMyAdmin :8080]         │    │
│  └─────────────┘   └──────────────────────────────┘    │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │              MONITORING Network                  │   │
│  │                                                  │   │
│  │  [Prometheus :9090]                              │   │
│  │    ← scrape ← [cAdvisor]                        │   │
│  │    ← scrape ← [nginx-exporter :9113]            │   │
│  │    ← scrape ← [mysql-exporter :9104]            │   │
│  │    ← scrape ← [loki :3100]                      │   │
│  │  [Grafana :3000] → query → {Prometheus, Loki}   │   │
│  │  [Loki :3100] ← push ← [Promtail]              │   │
│  │  [Promtail] ← tail ← {Nginx logs, Docker logs} │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

---

## 📦 Services & Ports

| Service | Image | Port (host) | Mục đích |
|---------|-------|-------------|----------|
| `nginx` | nginx:1.25.4-alpine | **80**, **443** | Reverse proxy, HTTPS, Rate limit |
| `php` | php:8.2.18-fpm-alpine | internal :9000 | PHP-FPM application |
| `mysql` | mysql:8.0.36 | **internal only** | Database (không public) |
| `phpmyadmin` | phpmyadmin:5.2.1 | 127.0.0.1:**8080** | DB admin UI |
| `prometheus` | prom/prometheus:v2.50.1 | 127.0.0.1:**9090** | Metrics collection |
| `grafana` | grafana/grafana:10.3.3 | 127.0.0.1:**3000** | Dashboard visualization |
| `loki` | grafana/loki:2.9.4 | 127.0.0.1:**3100** | Log aggregation |
| `promtail` | grafana/promtail:2.9.4 | internal | Log shipping to Loki |
| `cadvisor` | gcr.io/cadvisor/cadvisor:v0.47.2 | internal | Container metrics |
| `nginx-exporter` | nginx/nginx-prometheus-exporter:1.1.0 | internal :9113 | Nginx metrics |
| `mysql-exporter` | prom/mysqld-exporter:v0.15.1 | internal :9104 | MySQL metrics |

## 🌐 Networks (Network Isolation)

| Network | Services | Mục đích |
|---------|----------|----------|
| `portfolio_frontend` | nginx | Traffic từ internet |
| `portfolio_backend` | nginx, php, mysql, phpmyadmin, prometheus, mysql-exporter | Ứng dụng nội bộ |
| `portfolio_monitoring` | nginx, prometheus, grafana, loki, promtail, cadvisor, nginx-exporter, mysql-exporter | Giám sát tách biệt |

---

## ⚡ Yêu cầu môi trường

- **Docker Engine** ≥ 24.0
- **Docker Compose** v2 plugin (lệnh `docker compose`, KHÔNG dùng `docker-compose` cũ)
- **Windows** + Docker Desktop (WSL2 backend) **hoặc** Linux/macOS
- **OpenSSL** để tạo SSL certificate (có sẵn trong Git for Windows)
- **RAM** tối thiểu 4GB khả dụng

---

## 🚀 Triển khai lần đầu

### Bước 1: Clone repository

```bash
git clone https://github.com/vubahung222/Vu-Ba-Hung-DTC245180186-Website-Portfolio.git
cd Vu-Ba-Hung-DTC245180186-Website-Portfolio
```

### Bước 2: Tạo file `.env`

```bash
# Linux/macOS:
cp .env.example .env

# Windows PowerShell:
Copy-Item .env.example .env
```

> ⚠️ **BẮT BUỘC** chỉnh sửa `.env` — thay đổi TẤT CẢ mật khẩu mặc định:

```env
MYSQL_ROOT_PASSWORD=<mật_khẩu_mạnh_của_bạn>
MYSQL_PASSWORD=<mật_khẩu_mạnh_của_bạn>
GF_SECURITY_ADMIN_PASSWORD=<mật_khẩu_grafana>
APP_SECRET_KEY=<chuỗi_ngẫu_nhiên_64_ký_tự>
ADMIN_PASSWORD=<mật_khẩu_admin_portfolio>
```

### Bước 3: Tạo SSL certificate (tự ký)

**Windows PowerShell (cách nhanh nhất):**
```powershell
.\scripts\generate-ssl.ps1
```

**Git Bash / WSL2 / Linux / macOS:**
```bash
bash scripts/generate-ssl.sh
```

**PowerShell thủ công (nếu có Git for Windows):**
```powershell
& "C:\Program Files\Git\usr\bin\openssl.exe" req -x509 -nodes -days 365 -newkey rsa:2048 `
    -keyout nginx/ssl/server.key `
    -out nginx/ssl/server.crt `
    -subj "/C=VN/ST=ThaiBinh/L=ThaiBinh/O=Portfolio/CN=localhost"
```

### Bước 4: Build và khởi động

```bash
# Validate config trước khi build
docker compose config

# Build và start toàn bộ 11 services
docker compose up -d --build

# Kiểm tra tất cả services đang chạy
docker compose ps
```

### Bước 5: Tạo tài khoản admin

```bash
# Đợi ~30 giây để MySQL khởi động xong
docker compose exec php php /var/www/html/scripts/create-admin.php

# Hoặc chỉ định email/password cụ thể:
docker compose exec php php /var/www/html/scripts/create-admin.php \
    --email=admin@portfolio.local \
    --password=Admin@2025!
```

### Bước 6: Phục hồi dữ liệu Tiếng Việt (nếu cần)

```bash
# Chạy script fix encoding tiếng Việt trong container
docker compose exec php php /var/www/html/scripts/fix_encoding.php
```

---

## 🌐 Các URL truy cập

| URL | Dịch vụ | Ghi chú |
|-----|---------|---------|
| https://localhost | Website Portfolio | SSL tự ký, nhấn "Advanced → Proceed" |
| https://localhost/admin/login | Trang Admin | Đăng nhập quản lý nội dung |
| http://localhost:8080 | phpMyAdmin | Quản lý database MySQL |
| http://localhost:3000 | Grafana | Dashboard giám sát (admin/GrafanaPass@2025!) |
| http://localhost:9090 | Prometheus | Metrics & targets status |
| http://localhost:3100/ready | Loki | Kiểm tra Loki sẵn sàng |

> ⚠️ Trình duyệt sẽ cảnh báo SSL tự ký → nhấn **"Advanced"** → **"Proceed to localhost"**

---

## 🔧 Lệnh vận hành

### Xem logs

```bash
docker compose logs -f              # Tất cả services
docker compose logs -f nginx        # Chỉ Nginx
docker compose logs -f php          # Chỉ PHP
docker compose logs -f mysql        # Chỉ MySQL
docker compose logs -f grafana      # Chỉ Grafana
docker compose logs -f loki         # Chỉ Loki
```

### Quản lý services

```bash
docker compose restart nginx        # Restart Nginx
docker compose restart php          # Restart PHP
docker compose restart              # Restart tất cả

# Dừng (giữ nguyên data)
docker compose stop

# Dừng và xóa containers (GIỮ volumes/data)
docker compose down

# ⚠️ XÓA TOÀN BỘ DATA — chỉ dùng khi reset hoàn toàn
docker compose down -v
```

### Cập nhật ứng dụng

```bash
git pull
docker compose up -d --build php
```

---

## 📦 Backup & Restore

### Backup database

```bash
# Linux/macOS:
bash scripts/backup-db.sh
# File lưu tại: backups/portfolio_db_YYYYMMDD_HHMMSS.sql.gz

# Windows (PowerShell):
docker compose exec mysql mysqldump -uroot -p"$env:MYSQL_ROOT_PASSWORD" portfolio_db | `
    gzip > "backups/portfolio_db_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql.gz"
```

### Restore database

```bash
bash scripts/restore-db.sh backups/portfolio_db_20251001_120000.sql.gz
```

---

## 📊 Prometheus & Grafana

### Kiểm tra targets (phải tất cả UP)

```
http://localhost:9090/targets
```

→ Tất cả 5 targets phải ở trạng thái **UP**: prometheus, cadvisor, nginx, mysql, loki

### PromQL mẫu

```promql
# CPU usage của tất cả portfolio containers (%)
sum(rate(container_cpu_usage_seconds_total{name=~"portfolio_.+"}[2m])) by (name) * 100

# Memory usage theo container
container_memory_usage_bytes{name=~"portfolio_.+"}

# Nginx active connections
nginx_connections_active

# Nginx request rate (req/s)
rate(nginx_http_requests_total[2m])

# MySQL threads đang kết nối
mysql_global_status_threads_connected

# MySQL query rate (queries/s)
rate(mysql_global_status_queries[2m])

# MySQL SELECT rate
rate(mysql_global_status_com_select[2m])
```

### Grafana Dashboard

Grafana tự động load dashboard "**Portfolio - System Overview (Đề 13)**" khi khởi động.

Dashboard bao gồm:
- 🐳 **Container Metrics**: CPU, RAM, Network I/O (via cAdvisor)
- 🌐 **Nginx Metrics**: Connections, Request rate, Accepted
- 🗄️ **MySQL Metrics**: Connections, Query rate, Network I/O, Uptime
- 📋 **Loki Logs**: Nginx access logs real-time, HTTP error rate, PHP errors
- 📊 **LogQL Queries**: 3 mẫu query hiển thị trực tiếp trong Grafana

---

## 📋 Loki & LogQL

### Kiểm tra Loki hoạt động

```bash
# Health check
curl http://localhost:3100/ready

# Xem labels có sẵn
curl http://localhost:3100/loki/api/v1/labels

# Chạy script test đầy đủ 5 queries:
python scripts/test_logql_detailed.py
```

### 5 LogQL Queries mẫu

**1. Xem tất cả log Nginx (kèm parse JSON):**
```logql
{service="nginx"} | json | line_format "{{.remote_addr}} [{{.status}}] {{.method}} {{.uri}}"
```

**2. Lọc HTTP errors (4xx & 5xx):**
```logql
{service="nginx", log_type="access"} | json | status >= 400
```

**3. Đếm requests trong 5 phút:**
```logql
count_over_time({service="nginx"}[5m])
```

**4. Lọc ERROR từ PHP container:**
```logql
{container="portfolio_php"} |= "ERROR"
```

**5. Rate request/giây:**
```logql
rate({service="nginx"}[1m])
```

### Tạo traffic để có log

```bash
# Tạo 20 requests để có log trong Loki
for i in $(seq 1 20); do
    curl -sk https://localhost/ > /dev/null
    curl -sk https://localhost/du-an > /dev/null
    curl -sk https://localhost/lien-he > /dev/null
    curl -sk https://localhost/khong-ton-tai > /dev/null  # → 404 error
done
```

---

## 🔒 Hardening

| # | Biện pháp | Service | Cấu hình | Kiểm tra |
|---|-----------|---------|----------|---------|
| 1 | **Non-root user** | php | `USER appuser` (uid=1000) trong Dockerfile | `docker exec portfolio_php id` |
| 2 | **no-new-privileges** | Tất cả | `security_opt: - no-new-privileges:true` | `docker inspect portfolio_php \| grep -i NoNewPrivileges` |
| 3 | **Network isolation** | Tất cả | 3 network riêng: frontend/backend/monitoring | `docker network ls \| grep portfolio` |
| 4 | **MySQL không public** | mysql | Không có `ports:` mapping | `docker compose ps mysql` |
| 5 | **Security Headers** | nginx | X-Content-Type, X-Frame, CSP, Referrer-Policy, Permissions-Policy | `curl -sk -I https://localhost` |
| 6 | **Rate limiting** | nginx | login: 5r/m, contact: 3r/m, general: 30r/s | Nginx config |
| 7 | **Chặn file nhạy cảm** | nginx | Deny `.env`, `.git`, `.sql`, `.sh` | `curl -sk -I https://localhost/.env` → 403 |
| 8 | **bcrypt password** | php | bcrypt cost=12 via `password_hash()` | Xem `AdminUser.php` |
| 9 | **Prepared statements** | php | PDO với `ATTR_EMULATE_PREPARES=false` | Xem `Database.php` |
| 10 | **CSRF protection** | php | Token trong mọi form POST | Xem `Core/Csrf.php` |
| 11 | **Session hardening** | php | httponly, samesite=Strict, secure cookie | Xem `Core/Session.php` |
| 12 | **Upload validation** | php | MIME check thực tế (`finfo`), giới hạn 5MB | Xem `AdminController.php` |

### Kiểm chứng nhanh

```bash
# 1. PHP chạy non-root
docker exec portfolio_php id
# → uid=1000(appuser) gid=1000(appgroup) groups=1000(appgroup)

# 2. Security headers
curl -sk -I https://localhost | grep -E "X-Content|X-Frame|Content-Security|Referrer|Permissions"

# 3. .env bị chặn (phải trả về 403)
curl -sk -I https://localhost/.env

# 4. MySQL không public ra host
docker compose ps mysql | grep "0.0.0.0:3306"
# → Không có output

# 5. no-new-privileges
docker inspect portfolio_php | python -c "import sys,json; d=json.load(sys.stdin)[0]; print(d['HostConfig']['SecurityOpt'])"
# → ['no-new-privileges:true']

# 6. Chạy toàn bộ kiểm tra hardening tự động:
python scripts/verify_hardening.py
```

---

## 📌 Commit History (theo yêu cầu đề)

```
Commit 1: Triển khai ứng dụng + Database + Nginx Reverse Proxy
├── PHP portfolio app (MVC, CRUD admin)
├── MySQL + phpMyAdmin
├── Nginx: HTTPS tự ký, HTTP→HTTPS redirect
└── Security headers, rate limiting, hardening cơ bản

Commit 2: Tích hợp Prometheus + Grafana giám sát
├── Prometheus với 4 exporters (cAdvisor, nginx, mysql, loki)
├── Grafana với dashboard auto-provisioned
└── Alerting rules cơ bản

Commit 3: Triển khai Loki + Promtail log tập trung
├── Loki log aggregation
├── Promtail: scrape Nginx logs (JSON) + Docker container logs
└── 5 LogQL queries mẫu
```

---

## 📂 Cấu trúc thư mục

```
├── app/                        # PHP Application
│   ├── Dockerfile              # PHP 8.2-FPM Alpine, non-root
│   ├── php.ini / php-fpm.conf
│   ├── public/                 # Web root (index.php, CSS, JS)
│   ├── scripts/                # create-admin.php, fix_encoding.php
│   ├── src/
│   │   ├── Core/               # Database, Router, Auth, Csrf, Session...
│   │   ├── controllers/        # PublicController, AdminController, AuthController
│   │   ├── models/             # Profile, Skill, Project, Education, Experience...
│   │   └── views/              # PHP templates (layouts, public, admin, errors)
│   └── uploads/                # User uploads (gitignored)
├── nginx/
│   ├── nginx.conf              # Main config (gzip, log format JSON, rate zones)
│   ├── conf.d/default.conf     # Virtual host (HTTPS, security headers, FastCGI)
│   └── ssl/
│       ├── server.crt          # SSL certificate (committed)
│       └── server.key          # Private key (gitignored ✓)
├── mysql/
│   └── init/
│       ├── 01-schema.sql       # Schema + seed data
│       └── 02-create-exporter.sql # User exporter cho Prometheus
├── monitoring/
│   ├── prometheus/
│   │   ├── prometheus.yml      # Scrape configs (5 targets)
│   │   └── rules/              # Alerting rules
│   ├── grafana/
│   │   ├── provisioning/
│   │   │   ├── datasources/datasources.yml   # Prometheus + Loki
│   │   │   └── dashboards/dashboards.yml     # Auto-load dashboards
│   │   └── dashboards/
│   │       └── portfolio-overview.json       # Dashboard: Container+Nginx+MySQL+Loki
│   ├── loki/loki.yml           # Loki config (TSDB, filesystem storage)
│   ├── promtail/promtail.yml   # Scrape Nginx logs + Docker container logs
│   └── mysql-exporter.cnf      # MySQL exporter credentials
├── scripts/
│   ├── generate-ssl.sh         # Linux/macOS SSL generator
│   ├── generate-ssl.ps1        # Windows PowerShell SSL generator
│   ├── setup.bat               # Windows quick setup
│   ├── backup-db.sh / restore-db.sh
│   ├── verify_hardening.py     # Kiểm tra tất cả biện pháp bảo mật
│   └── test_logql_detailed.py  # Test 5 LogQL queries với Loki
├── backups/                    # DB backups (gitignored)
├── .env                        # 🔐 KHÔNG commit! (gitignored)
├── .env.example                # Mẫu cấu hình
├── .gitignore
├── .dockerignore
└── docker-compose.yml          # 11 services
```

---

## 🛠️ Troubleshooting

### MySQL chưa sẵn sàng

```bash
docker compose logs mysql | tail -30
# Đợi xuất hiện: "ready for connections"
# Hoặc dùng healthcheck: docker compose ps
```

### PHP không kết nối được MySQL

```bash
docker compose exec php php -r "
new PDO('mysql:host=mysql;dbname=portfolio_db', 'portfolio_user', 'AppPass@2025!');
echo 'OK';
"
```

### Nginx lỗi SSL / 502 Bad Gateway

```bash
# Kiểm tra SSL cert tồn tại
ls nginx/ssl/
# Phải có: server.crt và server.key

# Nếu thiếu, tạo SSL:
.\scripts\generate-ssl.ps1   # Windows
bash scripts/generate-ssl.sh # Linux/macOS

# Xem log Nginx
docker compose logs nginx | tail -30
```

### Prometheus target DOWN

```bash
# Kiểm tra từng exporter
docker exec portfolio_prometheus wget -qO- http://nginx-exporter:9113/metrics | head -5
docker exec portfolio_prometheus wget -qO- http://mysql-exporter:9104/metrics | head -5
docker exec portfolio_prometheus wget -qO- http://cadvisor:8080/metrics | head -5

# Reload Prometheus config
curl -X POST http://localhost:9090/-/reload
```

### Loki không nhận logs

```bash
# Kiểm tra Loki health
curl http://localhost:3100/ready

# Kiểm tra Promtail kết nối Loki
docker compose logs promtail | tail -20

# Tạo traffic để có logs
curl -sk https://localhost/ > /dev/null
```

### Grafana không thấy dashboard

```bash
# Restart Grafana để re-provision
docker compose restart grafana
docker compose logs grafana | grep -i "provision\|dashboard"
```

### Kiểm tra tổng thể hệ thống

```bash
# Tất cả services healthy
docker compose ps

# Verify hardening
python scripts/verify_hardening.py

# Test LogQL
python scripts/test_logql_detailed.py

# Verify system
python scripts/verify_system.py
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
- [Promtail Documentation](https://grafana.com/docs/loki/latest/clients/promtail/)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
