# -*- coding: utf-8 -*-
"""
Kiểm chứng chi tiết 3 câu truy vấn LogQL với Loki
"""
import urllib.request
import urllib.parse
import json

base_url = "http://localhost:3100/loki/api/v1/query_range"

queries = [
    {
        "id": "LogQL-1",
        "name": "Xem toàn bộ luồng log của Nginx",
        "query": '{service="nginx"}',
        "type": "stream"
    },
    {
        "id": "LogQL-2",
        "name": "Lọc các bản ghi log có HTTP status code >= 400 (lỗi client/server)",
        "query": '{service="nginx"} | json | status >= 400',
        "type": "filter"
    },
    {
        "id": "LogQL-3",
        "name": "Đo lường tần suất request trên giây trong cửa sổ 1 phút (Requests per Second)",
        "query": 'rate({service="nginx"}[1m])',
        "type": "metric"
    }
]

print("=== BẮT ĐẦU KIỂM CHỨNG 3 CÂU TRUY VẤN LOGQL VỚI LOKI ===")
for q in queries:
    encoded = urllib.parse.quote(q["query"])
    url = f"{base_url}?query={encoded}&limit=3"
    try:
        req = urllib.request.Request(url)
        with urllib.request.urlopen(req, timeout=5) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            result = data.get("data", {}).get("result", [])
            print(f"\n[{q['id']}] {q['name']}")
            print(f"Câu lệnh LogQL: {q['query']}")
            print(f"Số lượng streams/vectors trả về: {len(result)}")
            if result:
                if q["type"] in ["stream", "filter"]:
                    for stream in result[:2]:
                        labels = stream.get("stream", {})
                        print(f"  - Labels: {labels}")
                        for ts, val in stream.get("values", [])[:2]:
                            print(f"  - Log entry ({ts}): {val[:130]}...")
                elif q["type"] == "metric":
                    for vector in result[:2]:
                        metric = vector.get("metric", {})
                        values = vector.get("values", [])
                        last_val = values[-1] if values else "N/A"
                        print(f"  - Metric labels: {metric}")
                        print(f"  - Rate giá trị đo lường: {last_val}")
    except Exception as e:
        print(f"[{q['id']}] LỖI: {e}")

print("\n=== HOÀN TẤT KIỂM CHỨNG LOGQL ===")
