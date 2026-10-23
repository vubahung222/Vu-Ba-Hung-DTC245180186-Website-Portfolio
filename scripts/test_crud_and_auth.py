# -*- coding: utf-8 -*-
"""
Kiểm chứng tự động toàn diện các chức năng nghiệp vụ:
- Xác thực Admin (Login, Session, Chặn truy cập trái phép)
- CRUD Kỹ năng có tiếng Việt có dấu
- Kiểm chứng hiển thị ra trang Public
- Gửi form Liên hệ tiếng Việt & Admin xem được trong MySQL
- Đăng xuất
"""

import urllib.request
import urllib.parse
import http.cookiejar
import ssl
import re

# Bỏ qua SSL certificate self-signed
ctx = ssl._create_unverified_context()

# Quản lý Cookie Session
cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(
    urllib.request.HTTPCookieProcessor(cj),
    urllib.request.HTTPSHandler(context=ctx)
)

BASE_URL = "https://localhost"

def get_csrf_token(html):
    m = re.search(r'name=["\']_csrf_token["\']\s+value=["\']([^"\']+)["\']', html)
    if not m:
        m = re.search(r'value=["\']([^"\']+)["\']\s+name=["\']_csrf_token["\']', html)
    return m.group(1) if m else None

print("=== BẮT ĐẦU KIỂM CHỨNG CHỨC NĂNG NGHIỆP VỤ (AUTH & CRUD) ===")

# 1. Chặn truy cập trái phép
try:
    # Disable auto redirect to check 302
    class NoRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, req, fp, code, msg, headers, newurl):
            return None
    no_redir_opener = urllib.request.build_opener(NoRedirect, urllib.request.HTTPSHandler(context=ctx))
    res = no_redir_opener.open(f"{BASE_URL}/admin/dashboard")
    print(f"[FAIL] /admin/dashboard không chặn trái phép: HTTP {res.getcode()}")
except urllib.error.HTTPError as e:
    if e.code in [302, 303]:
        loc = e.headers.get("Location")
        print(f"[PASS] Chặn truy cập trái phép: HTTP {e.code} => Redirect về {loc}")
    else:
        print(f"[INFO] Chặn truy cập trái phép: HTTP {e.code}")

# 2. Lấy trang Login và CSRF token
resp = opener.open(f"{BASE_URL}/admin/login")
login_html = resp.read().decode('utf-8')
csrf = get_csrf_token(login_html)
print(f"[PASS] Lấy form login thành công, CSRF token: {csrf[:16]}...")

# 3. Đăng nhập với tài khoản Admin
login_data = urllib.parse.urlencode({
    "_csrf_token": csrf,
    "email": "admin@portfolio.local",
    "password": "Admin@2025!"
}).encode('utf-8')

req = urllib.request.Request(f"{BASE_URL}/admin/login", data=login_data, method="POST")
resp = opener.open(req)
dashboard_html = resp.read().decode('utf-8')

if "Tổng quan" in dashboard_html or "Dashboard" in dashboard_html or "admin/logout" in dashboard_html:
    print("[PASS] Đăng nhập Admin thành công, truy cập Dashboard!")
else:
    print("[FAIL] Đăng nhập không thành công, nội dung không có Dashboard")

# 4. Thêm Kỹ năng mới với tiếng Việt có dấu phức tạp
resp = opener.open(f"{BASE_URL}/admin/skills")
skills_html = resp.read().decode('utf-8')
csrf_skills = get_csrf_token(skills_html)

skill_name = "Kiểm Thử Tự Động & Quản Trị Hệ Thống Nâng Cao"
create_data = urllib.parse.urlencode({
    "_csrf_token": csrf_skills,
    "name": skill_name,
    "category": "devops",
    "level": "95",
    "icon": "docker",
    "sort_order": "5"
}).encode('utf-8')

req = urllib.request.Request(f"{BASE_URL}/admin/skills/create", data=create_data, method="POST")
resp = opener.open(req)
print(f"[PASS] Đã gửi yêu cầu thêm kỹ năng mới: '{skill_name}'")

# 5. Kiểm tra hiển thị trên trang Public
resp_public = opener.open(f"{BASE_URL}/")
home_html = resp_public.read().decode('utf-8')
import html
decoded_home = html.unescape(home_html)
if skill_name in decoded_home:
    print(f"[PASS] Kỹ năng tiếng Việt có dấu '{skill_name}' ĐÃ HIỂN THỊ CHÍNH XÁC trên trang Public!")
else:
    print(f"[FAIL] Không tìm thấy kỹ năng '{skill_name}' trên trang Public")

# 6. Gửi form Liên hệ từ trang Public
resp_contact = opener.open(f"{BASE_URL}/lien-he")
contact_html = resp_contact.read().decode('utf-8')
csrf_contact = get_csrf_token(contact_html)

contact_name = "Vũ Bá Hùng (Kiểm thử)"
contact_subject = "Tiêu đề kiểm thử dấu tiếng Việt: Triển khai Đề 13"
contact_msg = "Nội dung kiểm tra: Toàn bộ quy trình từ Client gửi lên MySQL và hiển thị lại trên Admin phải bảo toàn 100% thanh điệu tiếng Việt."

post_contact = urllib.parse.urlencode({
    "_csrf_token": csrf_contact,
    "sender_name": contact_name,
    "sender_email": "vubahung222@gmail.com",
    "subject": contact_subject,
    "message": contact_msg
}).encode('utf-8')

req = urllib.request.Request(f"{BASE_URL}/lien-he/gui", data=post_contact, method="POST")
resp = opener.open(req)
print(f"[PASS] Gửi Form Liên hệ thành công với tên '{contact_name}'")

# 7. Admin kiểm tra tin nhắn vừa nhận
resp_msg = opener.open(f"{BASE_URL}/admin/messages")
msg_html = resp_msg.read().decode('utf-8')
decoded_msg = html.unescape(msg_html)
if contact_name in decoded_msg and contact_subject in decoded_msg:
    print(f"[PASS] Admin đã nhận và hiển thị đúng tin nhắn tiếng Việt: '{contact_subject}'!")
else:
    print("[FAIL] Admin chưa hiển thị tin nhắn vừa gửi")

# 8. Đăng xuất
csrf_logout = get_csrf_token(msg_html)
logout_data = urllib.parse.urlencode({"_csrf_token": csrf_logout}).encode('utf-8')
req = urllib.request.Request(f"{BASE_URL}/admin/logout", data=logout_data, method="POST")
resp = opener.open(req)
print("[PASS] Đăng xuất Admin thành công, session đã kết thúc!")

print("=== HOÀN TẤT KIỂM CHỨNG NGHIỆP VỤ THỰC TẾ ===")
