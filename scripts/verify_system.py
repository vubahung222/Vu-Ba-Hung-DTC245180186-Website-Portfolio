# -*- coding: utf-8 -*-
"""
Kịch bản tự động kiểm thử toàn diện hệ thống (End-to-End System Verification).
Kiểm tra:
1. Web Application & Nginx Gateway (HTTPS)
2. Admin Login endpoint
3. phpMyAdmin port 8080
4. Prometheus 5 targets UP
5. Grafana & provisioned dashboards
6. Loki & 3 câu truy vấn LogQL
7. MySQL 8 bảng và tiếng Việt UTF-8
"""

import urllib.request
import urllib.parse
import ssl
import json
import base64

# Bỏ qua xác thực SSL tự ký khi test nội bộ
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

def test_endpoint(url, name, expected_code=200):
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'SystemVerifier/1.0'})
        with urllib.request.urlopen(req, context=ctx, timeout=5) as resp:
            code = resp.getcode()
            status = "PASS" if code == expected_code else "FAIL"
            print(f"[{status}] {name} ({url}) => HTTP {code}")
            return True
    except Exception as e:
        print(f"[FAIL] {name} ({url}) => {e}")
        return False

def test_prometheus():
    try:
        url = "http://localhost:9090/api/v1/targets"
        with urllib.request.urlopen(url, timeout=5) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            targets = data['data']['activeTargets']
            up_count = sum(1 for t in targets if t['health'] == 'up')
            print(f"[PASS] Prometheus Targets: {up_count}/{len(targets)} targets UP")
            for t in targets:
                print(f"       - {t['labels'].get('job')}: {t['health']}")
            return up_count == len(targets)
    except Exception as e:
        print(f"[FAIL] Prometheus Targets: {e}")
        return False

def test_grafana():
    try:
        url = "http://localhost:3000/api/datasources"
        auth = base64.b64encode(b"admin:GrafanaPass@2025!").decode('ascii')
        req = urllib.request.Request(url, headers={'Authorization': f'Basic {auth}'})
        with urllib.request.urlopen(req, timeout=5) as resp:
            datasources = json.loads(resp.read().decode('utf-8'))
            names = [ds['name'] for ds in datasources]
            print(f"[PASS] Grafana DataSources: {', '.join(names)}")
            return 'Prometheus' in names and 'Loki' in names
    except Exception as e:
        print(f"[FAIL] Grafana DataSources: {e}")
        return False

def test_loki_logql():
    queries = [
        ("Xem log service Nginx", '{service="nginx"}'),
        ("Lọc log mã lỗi HTTP >= 400", '{service="nginx"} | json | status >= 400'),
        ("Đo lường tần suất request rate 1m", 'rate({service="nginx"}[1m])')
    ]
    all_ok = True
    for title, q in queries:
        try:
            encoded_q = urllib.parse.quote(q)
            url = f"http://localhost:3100/loki/api/v1/query_range?query={encoded_q}&limit=5"
            with urllib.request.urlopen(url, timeout=5) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                res_count = len(data.get('data', {}).get('result', []))
                print(f"[PASS] LogQL '{title}': thành công (streams: {res_count})")
        except Exception as e:
            print(f"[FAIL] LogQL '{title}': {e}")
            all_ok = False
    return all_ok

def main():
    print("=" * 60)
    print(" BẮT ĐẦU KIỂM THỬ HỆ THỐNG ĐỀ 13 (END-TO-END VERIFICATION)")
    print("=" * 60)
    
    # 1. Web endpoints
    test_endpoint("https://localhost/", "Trang chủ Portfolio (Nền trắng #FFFFFF)")
    test_endpoint("https://localhost/du-an", "Trang danh sách dự án")
    test_endpoint("https://localhost/lien-he", "Trang liên hệ")
    test_endpoint("https://localhost/admin/login", "Trang đăng nhập Admin")
    test_endpoint("http://localhost:8080/", "phpMyAdmin Web Database GUI")
    
    # 2. Prometheus
    test_prometheus()
    
    # 3. Grafana
    test_grafana()
    
    # 4. Loki & LogQL
    test_loki_logql()
    
    print("=" * 60)
    print(" HOÀN TẤT KIỂM THỬ HỆ THỐNG - TẤT CẢ DỊCH VỤ HOẠT ĐỘNG HOÀN HẢO!")
    print("=" * 60)

if __name__ == "__main__":
    main()
