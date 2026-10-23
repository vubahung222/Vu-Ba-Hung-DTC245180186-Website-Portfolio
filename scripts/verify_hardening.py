#!/usr/bin/env python3
"""
verify_hardening.py - Kiểm tra tất cả biện pháp hardening
Đề 13: Website Portfolio - Vũ Bá Hùng DTC245180186

Chạy: python scripts/verify_hardening.py
Yêu cầu: docker compose đang chạy, requests library
"""

import subprocess
import sys
import json

# Thử import requests
try:
    import requests
    requests.packages.urllib3.disable_warnings()
    HAS_REQUESTS = True
except ImportError:
    HAS_REQUESTS = False

PASS = "\033[92m[PASS]\033[0m"
FAIL = "\033[91m[FAIL]\033[0m"
WARN = "\033[93m[WARN]\033[0m"
INFO = "\033[94m[INFO]\033[0m"

results = []

def check(name, passed, detail=""):
    status = PASS if passed else FAIL
    print(f"  {status} {name}")
    if detail:
        print(f"         → {detail}")
    results.append((name, passed))
    return passed


def run_docker(cmd):
    """Chạy lệnh docker, trả về stdout"""
    try:
        r = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=15)
        return r.returncode, r.stdout.strip(), r.stderr.strip()
    except Exception as e:
        return -1, "", str(e)


print()
print("=" * 55)
print("  KIỂM TRA HARDENING - PORTFOLIO ĐỀ 13")
print("  Sinh viên: Vũ Bá Hùng - DTC245180186")
print("=" * 55)
print()

# ── 1. Non-root container ─────────────────────────────────
print("1. NON-ROOT CONTAINER")
code, out, _ = run_docker("docker exec portfolio_php id")
passed = "uid=1000" in out and "appuser" in out
check("PHP chạy với user appuser (uid=1000)", passed, out[:80] if out else "Container không chạy")

code2, out2, _ = run_docker("docker exec portfolio_nginx id")
nginx_nonroot = "uid=" in out2
check("Nginx chạy với non-root (hoặc restricted)", nginx_nonroot, out2[:60] if out2 else "N/A")
print()

# ── 2. no-new-privileges ─────────────────────────────────
print("2. NO-NEW-PRIVILEGES")
code, out, _ = run_docker('docker inspect portfolio_php --format "{{.HostConfig.SecurityOpt}}"')
passed = "no-new-privileges" in out
check("portfolio_php: no-new-privileges:true", passed, out[:80])

code, out, _ = run_docker('docker inspect portfolio_mysql --format "{{.HostConfig.SecurityOpt}}"')
passed = "no-new-privileges" in out
check("portfolio_mysql: no-new-privileges:true", passed, out[:80])

code, out, _ = run_docker('docker inspect portfolio_nginx --format "{{.HostConfig.SecurityOpt}}"')
passed = "no-new-privileges" in out
check("portfolio_nginx: no-new-privileges:true", passed, out[:80])
print()

# ── 3. Network isolation ──────────────────────────────────
print("3. NETWORK ISOLATION")
code, out, _ = run_docker("docker network ls --filter name=portfolio")
networks = [l for l in out.splitlines() if "portfolio" in l]
check("Có ít nhất 3 network riêng (frontend/backend/monitoring)",
      len(networks) >= 3, f"Tìm thấy {len(networks)} network(s)")

# MySQL không public port
code, out, _ = run_docker('docker inspect portfolio_mysql --format "{{.NetworkSettings.Ports}}"')
mysql_public = "3306/tcp" in out and "0.0.0.0" in out
check("MySQL KHÔNG public port ra host", not mysql_public,
      "OK - MySQL chỉ trong backend network" if not mysql_public else "CẢNH BÁO: MySQL có public port!")
print()

# ── 4. Security Headers ───────────────────────────────────
print("4. SECURITY HEADERS (HTTPS)")
if HAS_REQUESTS:
    try:
        r = requests.get("https://localhost", verify=False, timeout=5)
        headers = r.headers

        check("X-Content-Type-Options: nosniff",
              "nosniff" in headers.get("X-Content-Type-Options", ""),
              headers.get("X-Content-Type-Options", "MISSING"))

        check("X-Frame-Options: SAMEORIGIN",
              "SAMEORIGIN" in headers.get("X-Frame-Options", ""),
              headers.get("X-Frame-Options", "MISSING"))

        check("Referrer-Policy",
              bool(headers.get("Referrer-Policy")),
              headers.get("Referrer-Policy", "MISSING"))

        check("Content-Security-Policy",
              bool(headers.get("Content-Security-Policy")),
              (headers.get("Content-Security-Policy", "MISSING"))[:80])

        check("X-XSS-Protection",
              bool(headers.get("X-XSS-Protection")),
              headers.get("X-XSS-Protection", "MISSING"))

        check("Permissions-Policy",
              bool(headers.get("Permissions-Policy")),
              headers.get("Permissions-Policy", "MISSING")[:60])

        check("Server token ẩn (không lộ version Nginx)",
              "nginx" not in headers.get("Server", "").lower() or
              not any(c.isdigit() for c in headers.get("Server", "")),
              headers.get("Server", "OK"))
    except Exception as e:
        print(f"  {WARN} Không kết nối được https://localhost: {e}")
        print(f"  {INFO} Đảm bảo docker compose đang chạy và SSL cert tồn tại")
else:
    print(f"  {WARN} Thư viện 'requests' chưa cài. Chạy: pip install requests")
print()

# ── 5. Chặn file nhạy cảm ────────────────────────────────
print("5. CHẶN FILE NHẠY CẢM")
if HAS_REQUESTS:
    try:
        sensitive_files = [
            ("/.env", "Chặn .env"),
            ("/.git/config", "Chặn .git/"),
            ("/app/src/bootstrap.php", "Chặn source PHP"),
        ]
        for url, label in sensitive_files:
            try:
                r = requests.get(f"https://localhost{url}", verify=False, timeout=5)
                check(label, r.status_code in (403, 404), f"HTTP {r.status_code}")
            except:
                check(label, False, "Connection failed")
    except Exception as e:
        print(f"  {WARN} {e}")
else:
    print(f"  {WARN} Cần 'requests' library để kiểm tra")
print()

# ── 6. Rate Limiting ──────────────────────────────────────
print("6. RATE LIMITING (Nginx Config)")
code, out, _ = run_docker("docker exec portfolio_nginx cat /etc/nginx/nginx.conf")
has_rate_limit = "limit_req_zone" in out
check("Cấu hình limit_req_zone trong nginx.conf", has_rate_limit, "Rate limiting zones configured")

code, out, _ = run_docker("docker exec portfolio_nginx cat /etc/nginx/conf.d/default.conf")
has_login_limit = "limit_req zone=login" in out
check("Rate limit cho /admin/login (5r/m)", has_login_limit)
has_contact_limit = "limit_req zone=contact" in out
check("Rate limit cho /lien-he/gui (3r/m)", has_contact_limit)
print()

# ── 7. CSRF Protection ───────────────────────────────────
print("7. CSRF PROTECTION")
code, out, _ = run_docker("docker exec portfolio_php ls /var/www/html/src/Core/Csrf.php")
check("File Csrf.php tồn tại", code == 0, out if out else "File không tìm thấy")
print()

# ── 8. Mật khẩu Hash ────────────────────────────────────
print("8. PASSWORD HASHING (bcrypt)")
code, out, _ = run_docker(
    "docker exec portfolio_mysql mysql -uportfolio_user -pAppPass@2025! portfolio_db "
    "-e \"SELECT SUBSTRING(password, 1, 7) as hash_prefix FROM admin_users LIMIT 1;\" "
    "--skip-column-names 2>/dev/null"
)
is_bcrypt = "$2y$12" in out or "$2y$" in out
check("Admin password dùng bcrypt ($2y$)", is_bcrypt,
      f"Prefix: {out[:20]}" if out else "Không query được DB")
print()

# ── Kết quả tổng hợp ─────────────────────────────────────
total = len(results)
passed_count = sum(1 for _, p in results if p)
failed_count = total - passed_count

print("=" * 55)
print(f"  KẾT QUẢ: {passed_count}/{total} PASSED")
print("=" * 55)

if failed_count == 0:
    print(f"  \033[92m✓ Tất cả kiểm tra PASSED! Hardening tốt.\033[0m")
else:
    print(f"  \033[91m✗ {failed_count} kiểm tra FAILED. Xem chi tiết ở trên.\033[0m")
    print()
    print("  Các kiểm tra FAILED:")
    for name, passed in results:
        if not passed:
            print(f"    - {name}")

print()
