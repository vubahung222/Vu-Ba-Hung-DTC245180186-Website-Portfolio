# -*- coding: utf-8 -*-
"""
Chụp ảnh màn hình thực tế (Real Screenshots) từ hệ thống đang chạy:
1. Mobile 375px (Responsive mobile view)
2. Tablet 768px (Responsive tablet view)
3. Desktop 1440px (Full desktop view)
4. Admin Dashboard (Trang quản trị sau đăng nhập)
5. Grafana System Overview Dashboard
"""

import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

os.makedirs("screenshots", exist_ok=True)

opts = Options()
opts.add_argument('--headless')
opts.add_argument('--ignore-certificate-errors')
opts.add_argument('--allow-insecure-localhost')
opts.add_argument('--disable-gpu')
opts.add_argument('--no-sandbox')

driver = webdriver.Chrome(options=opts)

try:
    # 1. Desktop 1440px
    driver.set_window_size(1440, 900)
    driver.get("https://localhost/")
    time.sleep(2)
    driver.save_screenshot("screenshots/responsive_1440px.png")
    print("[PASS] Đã chụp screenshot Desktop (1440px) => screenshots/responsive_1440px.png")

    # 2. Tablet 768px
    driver.set_window_size(768, 1024)
    driver.get("https://localhost/")
    time.sleep(1)
    driver.save_screenshot("screenshots/responsive_768px.png")
    print("[PASS] Đã chụp screenshot Tablet (768px) => screenshots/responsive_768px.png")

    # 3. Mobile 375px
    driver.set_window_size(375, 812)
    driver.get("https://localhost/")
    time.sleep(1)
    driver.save_screenshot("screenshots/responsive_375px.png")
    print("[PASS] Đã chụp screenshot Mobile (375px) => screenshots/responsive_375px.png")

    # 4. Admin Dashboard sau đăng nhập
    driver.set_window_size(1440, 900)
    driver.get("https://localhost/admin/login")
    time.sleep(1)
    driver.find_element(By.ID, "email").send_keys("admin@portfolio.local")
    driver.find_element(By.ID, "password").send_keys("Admin@2025!")
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    time.sleep(2)
    driver.save_screenshot("screenshots/admin_dashboard.png")
    print("[PASS] Đã chụp screenshot Admin Dashboard => screenshots/admin_dashboard.png")

    # 5. Grafana Dashboard
    driver.set_window_size(1600, 1000)
    driver.get("http://localhost:3000/d/portfolio-overview/portfolio-system-overview?orgId=1&kiosk")
    time.sleep(3)
    # Login nếu hiện màn hình login
    if "login" in driver.current_url.lower():
        try:
            driver.find_element(By.NAME, "user").send_keys("admin")
            driver.find_element(By.NAME, "password").send_keys("GrafanaPass@2025!")
            driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
            time.sleep(3)
            driver.get("http://localhost:3000/d/portfolio-overview/portfolio-system-overview?orgId=1&kiosk")
            time.sleep(3)
        except Exception as e:
            print("Grafana login form note:", e)
    driver.save_screenshot("screenshots/grafana_dashboard.png")
    print("[PASS] Đã chụp screenshot Grafana Overview => screenshots/grafana_dashboard.png")

finally:
    driver.quit()

print("=== HOÀN TẤT CHỤP TẤT CẢ SCREENSHOT MINH CHỨNG THỰC TẾ ===")
