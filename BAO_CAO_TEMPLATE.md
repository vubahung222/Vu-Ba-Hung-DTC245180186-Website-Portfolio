# Hướng dẫn tạo báo cáo Word/PDF

## Cấu trúc báo cáo (≥ 10 trang)

---

### TRANG BÌA
```
[TRƯỜNG/KHOA]
KHOA CÔNG NGHỆ THÔNG TIN

BÁO CÁO ĐỒ ÁN MÔN HỌC
TRIỂN KHAI VÀ QUẢN TRỊ HỆ THỐNG PHẦN MỀM

Đề tài: WEBSITE PORTFOLIO / GIỚI THIỆU CÁ NHÂN
(Đề 13)

Sinh viên:  [HỌ TÊN]
MSSV:       [MSSV]
Lớp:        [LỚP]
Giảng viên: [GIẢNG VIÊN]

TP. Hồ Chí Minh, Tháng 10/2025
```

---

### CHƯƠNG 1: GIỚI THIỆU VÀ MỤC TIÊU (Trang 2-3)

**1.1 Mục tiêu đề tài**
- Xây dựng website portfolio cá nhân với giao diện hiện đại, nền trắng, hỗ trợ tiếng Việt đầy đủ.
- Triển khai toàn bộ hệ thống bằng Docker Compose gồm: PHP-FPM, MySQL, Nginx, phpMyAdmin.
- Thiết lập giám sát toàn diện: Prometheus, Grafana, Loki, Promtail, các exporter.
- Áp dụng hardening bảo mật: non-root container, network isolation, security headers, CSRF, bcrypt.

**1.2 Phạm vi**
- Website public: trang chủ, giới thiệu, kỹ năng, dự án, liên hệ.
- Trang admin: đăng nhập, CRUD nội dung, quản lý tin nhắn, đổi mật khẩu.
- Hạ tầng: 11 services Docker Compose.
- Bảo mật: 10+ biện pháp hardening có minh chứng.

---

### CHƯƠNG 2: PHÂN TÍCH YÊU CẦU (Trang 4)

**2.1 Yêu cầu chức năng**

| STT | Chức năng | Ưu tiên |
|-----|-----------|---------|
| 1 | Hiển thị portfolio với dữ liệu từ MySQL | Cao |
| 2 | Form liên hệ lưu vào DB | Cao |
| 3 | Admin CRUD: kỹ năng, dự án, học vấn, kinh nghiệm | Cao |
| 4 | Giám sát container/Nginx/MySQL bằng Prometheus+Grafana | Cao |
| 5 | Thu thập log tập trung với Loki+Promtail | Cao |

**2.2 Yêu cầu phi chức năng**
- Giao diện nền trắng #FFFFFF, hỗ trợ tiếng Việt UTF-8.
- Responsive: 375px, 768px, 1440px.
- Bảo mật: prepared statements, CSRF, bcrypt, session hardening.
- Khả dụng: healthcheck, retry kết nối DB, không mất data khi restart.

---

### CHƯƠNG 3: KIẾN TRÚC HỆ THỐNG (Trang 5)

**3.1 Sơ đồ kiến trúc**

```
┌─────────────────────────────────────────────────────────┐
│                    Docker Host                          │
│                                                         │
│  ┌──────────────┐        ┌─────────────────────────┐   │
│  │   Frontend   │        │       Backend           │   │
│  │  Network     │        │       Network           │   │
│  │              │        │                         │   │
│  │  [Nginx]─────┼────────┼─→[PHP-FPM]─→[MySQL]    │   │
│  │  :80/:443    │        │              :3306       │   │
│  │              │        │  [phpMyAdmin]→↑          │   │
│  └──────────────┘        └─────────────────────────┘   │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │              Monitoring Network                  │   │
│  │                                                  │   │
│  │  [Prometheus]←[cAdvisor, nginx-exporter,        │   │
│  │               mysql-exporter, loki]              │   │
│  │  [Grafana]→{Prometheus, Loki}                   │   │
│  │  [Loki]←[Promtail]←{Nginx logs, Docker logs}   │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

**3.2 Luồng xử lý request**
1. User truy cập https://localhost
2. Nginx nhận request, kiểm tra SSL, áp dụng rate limit và security headers
3. Nginx forward request qua FastCGI tới PHP-FPM (:9000)
4. PHP-FPM xử lý, truy vấn MySQL (UTF-8mb4)
5. Response trả về qua Nginx
6. Promtail đọc access log JSON của Nginx → push vào Loki
7. Prometheus scrape metrics từ cAdvisor, nginx-exporter, mysql-exporter

---

### CHƯƠNG 4: WEBSITE, ADMIN, MYSQL, PHPMYADMIN (Trang 6)

**4.1 Stack ứng dụng**
- PHP 8.2 FPM Alpine (non-root, image pin)
- Custom Router, MVC pattern thuần PHP
- PDO + prepared statements, UTF-8mb4

**4.2 Bảo mật ứng dụng**
- CSRF: token trong mọi form POST, rotate sau mỗi submit
- Session: httponly, samesite=Strict, secure cookie, regenerate sau login
- Password: bcrypt cost=12 (AdminUser::create)
- Upload: MIME check với finfo (không tin header), dung lượng giới hạn 5MB, tên file server-side
- Brute force: khóa tài khoản 15 phút sau 5 lần thất bại

**4.3 MySQL**
- Tài khoản `portfolio_user` chỉ có quyền trên `portfolio_db`
- Tài khoản `exporter` chỉ có PROCESS, REPLICATION CLIENT, SELECT
- Không public port 3306 ra ngoài host
- Charset: utf8mb4, collation: utf8mb4_unicode_ci

[CHÈN ẢNH: Screenshot website trang chủ]
[CHÈN ẢNH: Screenshot admin dashboard]
[CHÈN ẢNH: Screenshot phpMyAdmin]

---

### CHƯƠNG 5: DOCKER COMPOSE VÀ NGINX (Trang 7)

**5.1 Services**
[Xem bảng services trong README]

**5.2 Nginx cấu hình quan trọng**
- HTTP → HTTPS redirect
- FastCGI proxy tới PHP-FPM (không proxy HTTP trực tiếp)
- Chặn .env, .git, file cấu hình nhạy cảm
- Rate limiting: login 5r/m, contact 3r/m, general 30r/s
- Security headers: X-Content-Type-Options, X-Frame-Options, CSP, Referrer-Policy
- Access log JSON (cho Promtail/Loki)
- stub_status cho nginx-exporter

```nginx
# Kiểm tra security headers:
curl -sk -I https://localhost | grep -E "X-Content|X-Frame|Content-Security"
```

[CHÈN ẢNH: docker compose ps output]
[CHÈN ẢNH: curl -I https://localhost]

---

### CHƯƠNG 6: PROMETHEUS VÀ GRAFANA (Trang 8)

**6.1 Targets scrape**

| Target | Metric | Scrape interval |
|--------|--------|----------------|
| cAdvisor | Container CPU, RAM, Network | 15s |
| nginx-exporter | Connections, requests | 15s |
| mysql-exporter | Connections, queries, uptime | 15s |
| prometheus | Self metrics | 15s |
| loki | Loki internal metrics | 15s |

**6.2 PromQL mẫu đã chạy**

```promql
# Container CPU
sum(rate(container_cpu_usage_seconds_total{name=~"portfolio_.+"}[2m])) by (name) * 100

# Nginx connections
nginx_connections_active

# MySQL query rate
rate(mysql_global_status_queries[2m])
```

**6.3 Grafana Dashboard**
- File: `monitoring/grafana/dashboards/portfolio-overview.json`
- Auto-provision qua `monitoring/grafana/provisioning/`
- Panels: Container CPU/RAM/Network, Nginx Connections/Requests, MySQL Connections/Queries/Uptime

[CHÈN ẢNH: Prometheus /targets - tất cả UP]
[CHÈN ẢNH: Grafana dashboard tổng quan]
[CHÈN ẢNH: Panel container CPU usage]

---

### CHƯƠNG 7: LOKI, PROMTAIL, LOGQL (Trang 9)

**7.1 Kiến trúc logging**
- Promtail chạy trong container, mount `/var/log/nginx` (read-only) và `/var/run/docker.sock`
- Nginx ghi log định dạng JSON → dễ parse bởi Promtail
- Docker container logs thu thập qua docker_sd_configs
- Labels: `service`, `container`, `log_type`

**7.2 LogQL queries được kiểm chứng**

**Query 1: Xem log theo service**
```logql
{service="nginx"} | json | line_format "{{.remote_addr}} {{.method}} {{.uri}} → {{.status}}"
```
*Giải thích: Lấy tất cả log từ service nginx, parse JSON, format theo dạng dễ đọc.*

**Query 2: Lọc lỗi HTTP**
```logql
{service="nginx"} | json | status >= 400
```
*Giải thích: Chỉ hiển thị request có HTTP status ≥ 400 (client/server errors).*

**Query 3: Đếm requests trong khoảng thời gian**
```logql
count_over_time({service="nginx"}[5m])
```
*Giải thích: Đếm số log entries (= số request) mỗi 5 phút.*

**Kiểm chứng - Tạo traffic test:**
```bash
for i in $(seq 1 30); do
    curl -sk https://localhost/ > /dev/null
    curl -sk https://localhost/khong-ton-tai > /dev/null
done
```

[CHÈN ẢNH: Grafana Explore → Loki query 1 với kết quả]
[CHÈN ẢNH: Grafana Explore → Loki query 2 lọc errors]
[CHÈN ẢNH: Grafana Explore → Loki query 3 đếm requests]

---

### CHƯƠNG 8: HARDENING (Trang 10)

| STT | Biện pháp | Service | Cấu hình | Kiểm tra | Kết quả |
|-----|-----------|---------|----------|---------|---------|
| 1 | Non-root user | php | `USER appuser` (uid 1000) trong Dockerfile | `docker exec portfolio_php id` | uid=1000(appuser) |
| 2 | no-new-privileges | Tất cả | `security_opt: [no-new-privileges:true]` | `docker inspect \| grep NoNewPrivileges` | true |
| 3 | Network isolation | Tất cả | 3 networks: frontend/backend/monitoring | `docker network ls` | 3 networks riêng |
| 4 | DB không public | mysql | Không có ports mapping | `docker compose ps mysql` | Không có 0.0.0.0:3306 |
| 5 | Security headers | nginx | X-Content-Type-Options, X-Frame, CSP | `curl -I https://localhost` | Headers có mặt |
| 6 | bcrypt password | php | bcrypt cost=12 | Xem AdminUser.php | Hash bắt đầu $2y$ |
| 7 | Prepared statements | php | PDO EMULATE_PREPARES=false | Xem Database.php | SQL injection proof |
| 8 | Upload MIME check | php | finfo::FILEINFO_MIME_TYPE | Upload .php file | Từ chối 403 |
| 9 | CSRF protection | php | Token trong mọi POST | Intercept request | Token verify |
| 10 | Rate limiting | nginx | 5r/m login, 3r/m contact | Thử 10 lần liên tiếp | 429 Too Many Requests |
| 11 | Chặn .env | nginx | deny all cho *.env* | curl .env | 403 Forbidden |
| 12 | Quyền tối thiểu DB | mysql | portfolio_user: chỉ quyền cần thiết | SHOW GRANTS | Giới hạn |
| 13 | Read-only mount | promtail | `/var/log/nginx:ro` | docker inspect | ro mode |
| 14 | tmpfs /tmp | php | `tmpfs: /tmp:size=64m` | docker inspect | tmpfs |

---

### CHƯƠNG 9: KIỂM THỬ VÀ KẾT QUẢ (Trang 11)

**9.1 Giao diện**

| Kiểm tra | Kết quả |
|---------|--------|
| Nền trắng #FFFFFF | ✅ Đạt |
| Tiếng Việt chuẩn UTF-8 | ✅ Không lỗi ký tự |
| Responsive 375px | ✅ Không tràn ngang |
| Responsive 768px | ✅ Đúng |
| Responsive 1440px | ✅ Đúng |
| Menu mobile | ✅ Mở/đóng đúng |

**9.2 Chức năng**

| Kiểm tra | Kết quả |
|---------|--------|
| Đăng nhập đúng → vào dashboard | ✅ |
| Đăng nhập sai → thông báo lỗi | ✅ |
| Đăng xuất | ✅ |
| CRUD kỹ năng | ✅ Lưu DB |
| CRUD dự án | ✅ Lưu DB |
| Dữ liệu admin cập nhật → public | ✅ |
| Form liên hệ → lưu vào messages | ✅ |
| Upload ảnh hợp lệ | ✅ |
| Upload file .php → từ chối | ✅ |
| Nhập tiếng Việt → đọc lại đúng | ✅ |

**9.3 Hạ tầng**

| Kiểm tra | Kết quả |
|---------|--------|
| docker compose config | ✅ Hợp lệ |
| Build và start | ✅ Thành công |
| Website qua Nginx HTTPS | ✅ |
| phpMyAdmin kết nối MySQL | ✅ |
| Prometheus targets UP | ✅ cadvisor, nginx, mysql |
| Grafana hiển thị metrics | ✅ |
| Loki nhận log | ✅ |
| 3 LogQL chạy được | ✅ |
| Restart giữ dữ liệu | ✅ Volume persistent |

[CHÈN ẢNH minh chứng cho từng mục]

---

### CHƯƠNG 10: HẠN CHẾ VÀ HƯỚNG PHÁT TRIỂN (Trang 12)

**10.1 Hạn chế**
- SSL tự ký: trình duyệt sẽ cảnh báo khi demo. Cần Let's Encrypt để production.
- cAdvisor trên WSL2/Docker Desktop: một số metrics container có thể bị thiếu do giới hạn cgroups.
- Promtail docker_sd_configs cần mount docker.sock (quyền đặc biệt, đã giải thích).
- Chưa có email SMTP thực để gửi phản hồi tin nhắn liên hệ.
- Không có CDN cho assets tĩnh.

**10.2 Hướng phát triển**
- Tích hợp Let's Encrypt (Certbot) để HTTPS tự động gia hạn.
- Thêm CI/CD pipeline với GitHub Actions.
- Caching layer với Redis.
- Full-text search với Elasticsearch.
- Gửi email thông báo khi có tin nhắn mới (SMTP/SendGrid).
- Thêm Analytics (Matomo self-hosted).
- Multi-language support (i18n).

---

### TÀI LIỆU THAM KHẢO

1. Docker Documentation. (2024). https://docs.docker.com/
2. Nginx Official Documentation. (2024). https://nginx.org/en/docs/
3. PHP 8.2 Manual. (2024). https://www.php.net/manual/en/
4. MySQL 8.0 Reference Manual. (2024). https://dev.mysql.com/doc/refman/8.0/en/
5. Prometheus Documentation. (2024). https://prometheus.io/docs/
6. Grafana Documentation. (2024). https://grafana.com/docs/
7. Grafana Loki Documentation. (2024). https://grafana.com/docs/loki/
8. OWASP PHP Security Cheat Sheet. (2024). https://cheatsheetseries.owasp.org/
9. CIS Docker Benchmark. (2024). https://www.cisecurity.org/
10. Mozilla Web Security Guidelines. (2024). https://infosec.mozilla.org/guidelines/web_security
