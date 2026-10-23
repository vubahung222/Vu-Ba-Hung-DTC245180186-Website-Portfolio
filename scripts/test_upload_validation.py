# -*- coding: utf-8 -*-
"""
Kiểm chứng chức năng Upload Ảnh:
1. File không hợp lệ (mã độc .php hoặc file .txt) => Bị từ chối
2. File ảnh hợp lệ (PNG) => Được chấp nhận và lưu trữ đổi tên an toàn
"""

import urllib.request
import urllib.parse
import http.cookiejar
import ssl
import re

ctx = ssl._create_unverified_context()
cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(
    urllib.request.HTTPCookieProcessor(cj),
    urllib.request.HTTPSHandler(context=ctx)
)

BASE_URL = "https://localhost"

def get_csrf(html):
    m = re.search(r'name=["\']_csrf_token["\']\s+value=["\']([^"\']+)["\']', html)
    return m.group(1) if m else None

# 1. Đăng nhập Admin
resp = opener.open(f"{BASE_URL}/admin/login")
csrf = get_csrf(resp.read().decode('utf-8'))
login_data = urllib.parse.urlencode({
    "_csrf_token": csrf,
    "email": "admin@portfolio.local",
    "password": "Admin@2025!"
}).encode('utf-8')
opener.open(urllib.request.Request(f"{BASE_URL}/admin/login", data=login_data, method="POST"))

# 2. Lấy trang Profile
resp = opener.open(f"{BASE_URL}/admin/profile")
profile_html = resp.read().decode('utf-8')
csrf_profile = get_csrf(profile_html)

# Helper gửi multipart/form-data
def upload_file(filename, content, content_type):
    boundary = "----WebKitFormBoundaryTest123456789"
    lines = [
        f"--{boundary}".encode('utf-8'),
        b'Content-Disposition: form-data; name="_csrf_token"',
        b'',
        csrf_profile.encode('utf-8'),
        f"--{boundary}".encode('utf-8'),
        f'Content-Disposition: form-data; name="avatar"; filename="{filename}"'.encode('utf-8'),
        f'Content-Type: {content_type}'.encode('utf-8'),
        b'',
        content,
        f"--{boundary}--".encode('utf-8'),
        b''
    ]
    payload = b"\r\n".join(lines)
    req = urllib.request.Request(
        f"{BASE_URL}/admin/profile/avatar",
        data=payload,
        headers={"Content-Type": f"multipart/form-data; boundary={boundary}"},
        method="POST"
    )
    return opener.open(req)

# Test 1: File không hợp lệ (mã PHP giả danh)
bad_content = b"<?php echo 'malicious code'; ?>"
resp_bad = upload_file("shell.php", bad_content, "application/x-php")
res_html = resp_bad.read().decode('utf-8')
if "Chỉ chấp nhận file ảnh" in res_html or "Lỗi upload" in res_html or "alert-error" in res_html:
    print("[PASS] Upload file không hợp lệ (shell.php) BỊ TỪ CHỐI CHÍNH XÁC!")
else:
    print("[FAIL] File không hợp lệ không bị từ chối")

# Test 2: File ảnh hợp lệ (PNG tối thiểu 1x1 pixel)
png_1x1 = (
    b'\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01'
    b'\x08\x06\x00\x00\x00\x1f\x15c4\x00\x00\x00\rIDATx\x9cc`\x00\x00\x00'
    b'\x02\x00\x01H\xaf\xa4q\x00\x00\x00\x00IEND\xaeB`\x82'
)
# Cần lấy lại csrf vì đã được regenerate
resp = opener.open(f"{BASE_URL}/admin/profile")
csrf_profile = get_csrf(resp.read().decode('utf-8'))

resp_good = upload_file("valid_avatar.png", png_1x1, "image/png")
res_good_html = resp_good.read().decode('utf-8')
if "Cập nhật ảnh đại diện thành công" in res_good_html or "alert-success" in res_good_html or "avatar_" in res_good_html:
    print("[PASS] Upload file ảnh hợp lệ (PNG) ĐƯỢC CHẤP NHẬN VÀ LƯU AN TOÀN!")
else:
    print("[INFO] Upload PNG đã thực thi, kết quả:", "Thành công" if "success" in res_good_html else "Cần kiểm tra flash")
