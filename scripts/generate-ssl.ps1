# =============================================
# generate-ssl.ps1 - Tạo SSL certificate tự ký cho Windows
# Đề 13: Website Portfolio - Vũ Bá Hùng DTC245180186
# Chạy: .\scripts\generate-ssl.ps1
# =============================================

$ErrorActionPreference = "Stop"

$SslDir = "nginx\ssl"
$CertFile = "$SslDir\server.crt"
$KeyFile = "$SslDir\server.key"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TẠO SSL CERTIFICATE TỰ KÝ (Self-Signed)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Tạo thư mục nếu chưa có
if (-not (Test-Path $SslDir)) {
    New-Item -ItemType Directory -Path $SslDir -Force | Out-Null
    Write-Host "[INFO] Đã tạo thư mục $SslDir" -ForegroundColor Yellow
}

# Kiểm tra đã có cert chưa
if ((Test-Path $CertFile) -and (Test-Path $KeyFile)) {
    Write-Host "[OK] SSL certificate đã tồn tại." -ForegroundColor Green
    Write-Host "  CRT: $CertFile" -ForegroundColor Gray
    Write-Host "  KEY: $KeyFile" -ForegroundColor Gray
    $choice = Read-Host "Tạo lại? (y/N)"
    if ($choice -notmatch "^[Yy]$") {
        Write-Host "Bỏ qua. Dùng certificate hiện tại." -ForegroundColor Yellow
        exit 0
    }
}

# --- Phương án 1: Dùng OpenSSL từ Git for Windows ---
$OpenSSLPaths = @(
    "C:\Program Files\Git\usr\bin\openssl.exe",
    "C:\Program Files (x86)\Git\usr\bin\openssl.exe",
    "$env:LOCALAPPDATA\Programs\Git\usr\bin\openssl.exe"
)

$OpenSSL = $null
foreach ($path in $OpenSSLPaths) {
    if (Test-Path $path) {
        $OpenSSL = $path
        break
    }
}

# Kiểm tra openssl trong PATH
if (-not $OpenSSL) {
    try {
        $null = & openssl version 2>&1
        $OpenSSL = "openssl"
    } catch { }
}

if ($OpenSSL) {
    Write-Host "[1/2] Tạo SSL certificate bằng OpenSSL..." -ForegroundColor Cyan
    Write-Host "  OpenSSL: $OpenSSL" -ForegroundColor Gray

    $SubjectArgs = "/C=VN/ST=ThaiBinh/L=ThaiBinh/O=Portfolio/OU=ICTU/CN=localhost"

    & $OpenSSL req -x509 -nodes -days 365 -newkey rsa:2048 `
        -keyout $KeyFile `
        -out $CertFile `
        -subj $SubjectArgs `
        -addext "subjectAltName=DNS:localhost,IP:127.0.0.1" 2>&1 | Out-Null

    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] SSL certificate tạo thành công bằng OpenSSL!" -ForegroundColor Green
    } else {
        Write-Host "[WARN] OpenSSL thất bại, thử phương án 2..." -ForegroundColor Yellow
        $OpenSSL = $null
    }
}

# --- Phương án 2: Dùng PowerShell New-SelfSignedCertificate (Windows built-in) ---
if (-not $OpenSSL) {
    Write-Host "[2/2] Tạo SSL certificate bằng PowerShell (New-SelfSignedCertificate)..." -ForegroundColor Cyan

    try {
        # Tạo cert trong Windows Certificate Store
        $cert = New-SelfSignedCertificate `
            -DnsName "localhost" `
            -CertStoreLocation "Cert:\CurrentUser\My" `
            -NotAfter (Get-Date).AddYears(1) `
            -KeyExportPolicy Exportable `
            -KeyAlgorithm RSA `
            -KeyLength 2048 `
            -HashAlgorithm SHA256 `
            -Subject "CN=localhost, O=Portfolio, C=VN"

        # Export certificate (.crt)
        $certBytes = $cert.Export([System.Security.Cryptography.X509Certificates.X509ContentType]::Cert)
        $certPem = "-----BEGIN CERTIFICATE-----`n" + [Convert]::ToBase64String($certBytes, [System.Base64FormattingOptions]::InsertLineBreaks) + "`n-----END CERTIFICATE-----"
        [System.IO.File]::WriteAllText((Resolve-Path ".").Path + "\$CertFile", $certPem)

        # Export private key (.key) - cần password tạm
        $tempPass = ConvertTo-SecureString -String "TempPass123" -Force -AsPlainText
        $pfxPath = "$env:TEMP\portfolio_temp.pfx"
        Export-PfxCertificate -Cert $cert -FilePath $pfxPath -Password $tempPass | Out-Null

        # Nếu có OpenSSL, convert PFX → PEM key
        $openSSLCheck = Get-Command openssl -ErrorAction SilentlyContinue
        if ($openSSLCheck) {
            & openssl pkcs12 -in $pfxPath -nocerts -nodes -out $KeyFile -passin pass:TempPass123 2>&1 | Out-Null
            Write-Host "[OK] Private key extracted." -ForegroundColor Green
        } else {
            # Tạo key file placeholder - sẽ cần OpenSSL sau
            Write-Host "[WARN] Không thể export private key mà không có OpenSSL." -ForegroundColor Yellow
            Write-Host "       Hãy cài OpenSSL hoặc dùng Git for Windows." -ForegroundColor Yellow
            "# Placeholder - cần OpenSSL để export key thực từ: $pfxPath" | Out-File $KeyFile
        }

        # Xóa PFX tạm
        Remove-Item $pfxPath -Force -ErrorAction SilentlyContinue

        # Xóa cert khỏi store
        Remove-Item "Cert:\CurrentUser\My\$($cert.Thumbprint)" -Force -ErrorAction SilentlyContinue

        Write-Host "[OK] SSL certificate tạo thành công bằng PowerShell!" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] Không thể tạo SSL certificate: $_" -ForegroundColor Red
        Write-Host ""
        Write-Host "Hãy cài đặt một trong các công cụ sau:" -ForegroundColor Yellow
        Write-Host "  1. Git for Windows: https://git-scm.com/download/win" -ForegroundColor Yellow
        Write-Host "  2. OpenSSL for Windows: https://slproweb.com/products/Win32OpenSSL.html" -ForegroundColor Yellow
        Write-Host "  3. WSL2 rồi chạy: bash scripts/generate-ssl.sh" -ForegroundColor Yellow
        exit 1
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  HOÀN TẤT!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Certificate: $CertFile" -ForegroundColor Gray
Write-Host "  Private Key: $KeyFile" -ForegroundColor Gray
Write-Host ""
Write-Host "⚠️  Lưu ý: Trình duyệt sẽ cảnh báo SSL tự ký." -ForegroundColor Yellow
Write-Host "   Nhấn 'Advanced' → 'Proceed to localhost' để tiếp tục." -ForegroundColor Yellow
Write-Host ""
