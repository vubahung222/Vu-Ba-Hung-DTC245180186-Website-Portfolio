# -*- coding: utf-8 -*-
"""
test_logql_detailed.py - Kiểm chứng ít nhất 5 câu truy vấn LogQL với Loki
Đề 13: Website Portfolio - Vũ Bá Hùng DTC245180186

Chạy: python scripts/test_logql_detailed.py
Yêu cầu: Loki đang chạy tại http://localhost:3100
"""
import urllib.request
import urllib.parse
import json
import time
from datetime import datetime, timezone

BASE_URL = "http://localhost:3100/loki/api/v1"
NOW_NS = str(int(time.time() * 1e9))
START_NS = str(int((time.time() - 3600) * 1e9))  # 1 giờ trước

PASS = "\033[92m[PASS]\033[0m"
FAIL = "\033[91m[FAIL]\033[0m"
INFO = "\033[94m[INFO]\033[0m"

# ── 5 Câu truy vấn LogQL bắt buộc ──────────────────────────────────
QUERIES = [
    {
        "id": "LogQL-1",
        "name": "Xem toàn bộ stream log của Nginx (access + error)",
        "query": '{service="nginx"}',
        "endpoint": "query_range",
        "type": "stream",
        "purpose": "Liệt kê tất cả log từ service Nginx, bao gồm access và error logs"
    },
    {
        "id": "LogQL-2",
        "name": "Lọc HTTP errors (status >= 400) từ Nginx",
        "query": '{service="nginx", log_type="access"} | json | status >= 400',
        "endpoint": "query_range",
        "type": "filter",
        "purpose": "Phát hiện các request bị lỗi 4xx (client error) và 5xx (server error)"
    },
    {
        "id": "LogQL-3",
        "name": "Đếm số request Nginx trong mỗi 5 phút (count_over_time)",
        "query": 'count_over_time({service="nginx"}[5m])',
        "endpoint": "query_range",
        "type": "metric",
        "purpose": "Đo lường tổng số log entries Nginx trong cửa sổ 5 phút"
    },
    {
        "id": "LogQL-4",
        "name": "Lọc ERROR từ PHP container",
        "query": '{container="portfolio_php"} |= "ERROR"',
        "endpoint": "query_range",
        "type": "filter",
        "purpose": "Phát hiện lỗi PHP trong container portfolio_php"
    },
    {
        "id": "LogQL-5",
        "name": "Tính rate request/giây của Nginx trong 1 phút",
        "query": 'rate({service="nginx"}[1m])',
        "endpoint": "query_range",
        "type": "metric",
        "purpose": "Đo tốc độ log/giây để phát hiện traffic spike"
    },
]

def call_loki(query, endpoint="query_range", limit=5):
    """Gọi Loki API và trả về (success, data, error)"""
    params = {
        "query": query,
        "limit": str(limit),
        "start": START_NS,
        "end": NOW_NS,
        "step": "60"
    }
    url = f"{BASE_URL}/{endpoint}?" + urllib.parse.urlencode(params)
    try:
        req = urllib.request.Request(url, headers={"Accept": "application/json"})
        with urllib.request.urlopen(req, timeout=10) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            if data.get("status") == "success":
                return True, data.get("data", {}), None
            return False, {}, data.get("error", "Unknown error")
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", errors="replace")
        return False, {}, f"HTTP {e.code}: {body[:200]}"
    except Exception as e:
        return False, {}, str(e)


def print_results(query_info, result_type, result_data):
    """In kết quả đẹp"""
    results = result_data.get("result", [])
    result_type_api = result_data.get("resultType", "unknown")

    print(f"    Result type : {result_type_api}")
    print(f"    Streams/vecs: {len(results)}")

    if results:
        if result_type in ("stream", "filter"):
            # Stream logs
            for i, stream in enumerate(results[:2]):
                labels = stream.get("stream", {})
                values = stream.get("values", [])
                print(f"    Stream[{i}] labels: {labels}")
                for ts_ns, log_line in values[:2]:
                    ts = datetime.fromtimestamp(int(ts_ns) / 1e9, tz=timezone.utc).strftime("%H:%M:%S UTC")
                    print(f"      [{ts}] {log_line[:120]}{'...' if len(log_line) > 120 else ''}")
        elif result_type == "metric":
            # Metric values
            for i, vector in enumerate(results[:3]):
                metric = vector.get("metric", {})
                values = vector.get("values", [])
                if values:
                    last_ts, last_val = values[-1]
                    ts = datetime.fromtimestamp(int(last_ts), tz=timezone.utc).strftime("%H:%M:%S UTC")
                    print(f"    Metric[{i}] {metric} = {float(last_val):.4f} (at {ts})")
    else:
        print(f"    (Không có data - Hãy tạo một số traffic trước)")
        print(f"    Lệnh tạo traffic: for i in $(seq 1 10); do curl -sk https://localhost/ > /dev/null; done")


# ── Main ─────────────────────────────────────────────────────────────
print()
print("=" * 62)
print("  KIỂM CHỨNG LOGQL - PORTFOLIO ĐỀ 13")
print("  Sinh viên: Vũ Bá Hùng - MSSV: DTC245180186")
print(f"  Thời gian: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
print("=" * 62)
print()

# Kiểm tra Loki health trước
print(f"{INFO} Kiểm tra kết nối Loki tại http://localhost:3100...")
try:
    with urllib.request.urlopen("http://localhost:3100/ready", timeout=5) as resp:
        if resp.status == 200:
            print(f"{PASS} Loki đang chạy và sẵn sàng!\n")
        else:
            print(f"{FAIL} Loki trả về HTTP {resp.status}")
            sys.exit(1)
except Exception as e:
    print(f"{FAIL} Không kết nối được Loki: {e}")
    print("       Hãy đảm bảo docker compose đang chạy: docker compose ps")
    import sys; sys.exit(1)

# Chạy từng query
passed = 0
failed = 0

for q in QUERIES:
    print(f"┌─ {q['id']}: {q['name']}")
    print(f"│  Mục đích : {q['purpose']}")
    print(f"│  LogQL    : {q['query']}")
    print("│  Kết quả:")

    success, data, error = call_loki(q["query"], q["endpoint"])

    if success:
        print_results(q, q["type"], data)
        print(f"│  {PASS} Query thực thi thành công")
        passed += 1
    else:
        print(f"│  {FAIL} Lỗi: {error}")
        failed += 1

    print("└" + "─" * 60)
    print()

# Tổng kết
print("=" * 62)
print(f"  KẾT QUẢ: {passed}/{len(QUERIES)} queries PASSED, {failed} FAILED")
print("=" * 62)
if failed == 0:
    print("  \033[92m✓ Tất cả LogQL queries hoạt động tốt!\033[0m")
    print("  \033[92m✓ Đáp ứng yêu cầu Commit 3 (Loki + Promtail)\033[0m")
else:
    print("  \033[93m! Một số query có thể không có data (chưa có traffic).\033[0m")
    print("  \033[93m! Tạo traffic: curl -sk https://localhost/ -o /dev/null\033[0m")
print()

# In mẫu LogQL cho báo cáo
print("─" * 62)
print("  BẢNG THAM KHẢO LOGQL CHO BÁO CÁO:")
print("─" * 62)
logql_samples = [
    ("1. Tất cả Nginx logs",
     '{service="nginx"} | json | line_format "{{.remote_addr}} [{{.status}}] {{.method}} {{.uri}}"'),
    ("2. HTTP errors (>=400)",
     '{service="nginx", log_type="access"} | json | status >= 400'),
    ("3. Đếm request / 5 phút",
     'count_over_time({service="nginx"}[5m])'),
    ("4. PHP ERROR logs",
     '{container="portfolio_php"} |= "ERROR"'),
    ("5. Rate request/giây",
     'rate({service="nginx"}[1m])'),
]
for label, q in logql_samples:
    print(f"  {label}:")
    print(f"    {q}")
    print()
