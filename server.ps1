param(
    [int]$Port = 8080
)

$ErrorActionPreference = "Stop"
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$RootDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$RemoteBase = "https://vsho.vietsunlogistics.com"
$CookieFile = Join-Path $RootDir "tms_cookies.txt"

function Invoke-TmsLogin([string]$Username, [string]$Password) {
    $loginData = "username=$([uri]::EscapeDataString($Username))&password=$([uri]::EscapeDataString($Password))&login=ĐĂNG+NHẬP"
    $html = (& curl.exe -s -L -c $CookieFile -b $CookieFile -d $loginData "$RemoteBase/0login.php") -join "`n"
    $userString = "trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1."
    if ($html -match 'id="userString"\s+value="([^"]+)"') {
        $userString = $Matches[1]
    }
    return $userString
}

# Initial login with default account trucking14 / 1
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   VIETSUN TMS MOBILE SERVER & API GATEWAY" -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "Dang ket noi va dang nhap vao $RemoteBase ..." -ForegroundColor Gray
$initUserString = Invoke-TmsLogin "trucking14" "1"
Write-Host "Dang nhap thanh cong! Session: $initUserString" -ForegroundColor Green

$Listener = New-Object System.Net.HttpListener
$Listener.Prefixes.Add("http://localhost:$Port/")

# Try binding all interfaces if running as Admin, otherwise localhost
$BoundAll = $false
try {
    $Listener.Prefixes.Add("http://*:$Port/")
    $Listener.Start()
    $BoundAll = $true
} catch {
    $Listener = New-Object System.Net.HttpListener
    $Listener.Prefixes.Add("http://localhost:$Port/")
    $Listener.Start()
}

$LanIps = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" }).IPAddress

Write-Host ""
Write-Host "App dang chay tai:" -ForegroundColor Green
Write-Host "  -> Tren may tinh:   http://localhost:$Port" -ForegroundColor White
if ($BoundAll -and $LanIps) {
    foreach ($ip in $LanIps) {
        Write-Host "  -> Tren Android:    http://${ip}:$Port  (Cung mang Wi-Fi)" -ForegroundColor Yellow
    }
}
Write-Host ""
Write-Host "Nhan Ctrl+C de dung server." -ForegroundColor DarkGray

$MimeTypes = @{
    ".html" = "text/html; charset=utf-8"
    ".json" = "application/json; charset=utf-8"
    ".js"   = "application/javascript; charset=utf-8"
    ".css"  = "text/css; charset=utf-8"
    ".svg"  = "image/svg+xml"
    ".png"  = "image/png"
    ".ico"  = "image/x-icon"
}

while ($Listener.IsListening) {
    $Context = $Listener.GetContext()
    $Req = $Context.Request
    $Res = $Context.Response

    # Add CORS Headers
    $Res.Headers.Add("Access-Control-Allow-Origin", "*")
    $Res.Headers.Add("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
    $Res.Headers.Add("Access-Control-Allow-Headers", "Content-Type")

    if ($Req.HttpMethod -eq "OPTIONS") {
        $Res.StatusCode = 200
        $Res.Close()
        continue
    }

    $Path = $Req.Url.AbsolutePath
    $ApiQuery = $Req.QueryString["api"]

    try {
        if ($Path -eq "/api/return_json" -or $ApiQuery -eq "return_json") {
            $ngay = $Req.QueryString["ngay"]
            if (-not $ngay) { $ngay = (Get-Date -Format "yyyy-MM-dd") }
            $tmpOut = Join-Path $RootDir "tmp_return.json"
            & curl.exe -s "$RemoteBase/return_json.php?ngay=$([uri]::EscapeDataString($ngay))" -o $tmpOut
            $bytes = [System.IO.File]::ReadAllBytes($tmpOut)
            $Res.ContentType = "application/json; charset=utf-8"
            $Res.StatusCode = 200
            $Res.OutputStream.Write($bytes, 0, $bytes.Length)
            $Res.Close()
            continue
        }

        if (($Path -eq "/api/index-crud" -or $ApiQuery -eq "index_crud") -and $Req.HttpMethod -eq "POST") {
            $reader = New-Object System.IO.StreamReader($Req.InputStream, [System.Text.Encoding]::UTF8)
            $body = $reader.ReadToEnd()
            $reader.Close()

            $tmpPost = Join-Path $RootDir "tmp_post.txt"
            $tmpResp = Join-Path $RootDir "tmp_resp.html"
            [System.IO.File]::WriteAllText($tmpPost, $body, (New-Object System.Text.UTF8Encoding($false)))

            & curl.exe -s -b $CookieFile -c $CookieFile --data-binary "@$tmpPost" "$RemoteBase/index-crud.php" -o $tmpResp
            $bytes = [System.IO.File]::ReadAllBytes($tmpResp)
            $Res.ContentType = "text/html; charset=utf-8"
            $Res.StatusCode = 200
            $Res.OutputStream.Write($bytes, 0, $bytes.Length)
            $Res.Close()
            continue
        }

        if (($Path -eq "/api/login" -or $ApiQuery -eq "login") -and $Req.HttpMethod -eq "POST") {
            $reader = New-Object System.IO.StreamReader($Req.InputStream, [System.Text.Encoding]::UTF8)
            $jsonBody = $reader.ReadToEnd() | ConvertFrom-Json
            $reader.Close()

            $uStr = Invoke-TmsLogin $jsonBody.username $jsonBody.password
            $respObj = @{ ok = $true; username = $jsonBody.username; userString = $uStr } | ConvertTo-Json
            $bytes = [System.Text.Encoding]::UTF8.GetBytes($respObj)
            $Res.ContentType = "application/json; charset=utf-8"
            $Res.StatusCode = 200
            $Res.OutputStream.Write($bytes, 0, $bytes.Length)
            $Res.Close()
            continue
        }

        if ($Path -eq "/api/drivers" -or $ApiQuery -eq "drivers") {
            $vietsunList = @()
            $vendorList = @()
            try {
                $form = "userString=$([uri]::EscapeDataString($initUserString))&loai=26&query=0&cv=0&id=1"
                $html = (& curl.exe -s -b $CookieFile -c $CookieFile -d $form "$RemoteBase/ajax-gvt.php") -join "`n"
                $rowMatches = [regex]::Matches($html, '<tr id="tr\d+">([\s\S]*?)</tr>')
                foreach ($rm in $rowMatches) {
                    $m = [regex]::Match($rm.Groups[1].Value, '<th[^>]*>([^<]*)<\/th>\s*<td[^>]*>([^<]*)<\/td>\s*<td[^>]*>([^<]*)<\/td>')
                    if ($m.Success) {
                        $plate = ($m.Groups[1].Value -replace '[^A-Za-z0-9]', '').ToUpper()
                        if ($plate -and $plate -ne "DEPOT") {
                            $vietsunList += @{
                                vendor = "VIETSUN"
                                plate  = $plate
                                name   = $m.Groups[2].Value.Trim()
                                phone  = $m.Groups[3].Value.Trim()
                            }
                        }
                    }
                }
            } catch {}

            try {
                $sheetUrl = "https://docs.google.com/spreadsheets/d/13ndQsSmBsKlHjIRQR0S0MO5BPqqL6J4CECBAYfQi1oo/export?format=csv&gid=1046738475"
                $csvResp = Invoke-WebRequest -Uri $sheetUrl -UseBasicParsing
                $csvText = [System.Text.Encoding]::UTF8.GetString($csvResp.RawContentStream.ToArray())
                $lines = $csvText -split "\r?\n"
                for ($i = 1; $i -lt $lines.Count; $i++) {
                    $line = $lines[$i].Trim()
                    if (-not $line) { continue }
                    $cols = $line -split ","
                    if ($cols.Count -lt 2) { continue }
                    $plate = ($cols[1] -replace '[^A-Za-z0-9]', '').ToUpper()
                    if ($plate) {
                        $vendorList += @{
                            vendor = $cols[0].Trim().ToUpper()
                            plate  = $plate
                            name   = if ($cols.Count -gt 2) { $cols[2].Trim() } else { "" }
                            phone  = if ($cols.Count -gt 3) { $cols[3].Trim() } else { "" }
                            cccd   = if ($cols.Count -gt 4) { $cols[4].Trim() } else { "" }
                            gplx   = if ($cols.Count -gt 5) { $cols[5].Trim() } else { "" }
                        }
                    }
                }
            } catch {}

            $respJson = @{
                ok = $true
                vietsunCount = $vietsunList.Count
                vendorCount = $vendorList.Count
                vietsun = $vietsunList
                vendors = $vendorList
                updatedAt = (Get-Date -Format "o")
            } | ConvertTo-Json -Depth 4 -Compress
            $bytes = [System.Text.Encoding]::UTF8.GetBytes($respJson)
            $Res.ContentType = "application/json; charset=utf-8"
            $Res.StatusCode = 200
            $Res.OutputStream.Write($bytes, 0, $bytes.Length)
            $Res.Close()
            continue
        }

        # Static File Serving
        if ($Path -eq "/" -or $Path -eq "") { $Path = "/index.html" }
        $LocalPath = Join-Path $RootDir ($Path.TrimStart('/').Replace('/', '\'))

        if (Test-Path $LocalPath -PathType Leaf) {
            $ext = [System.IO.Path]::GetExtension($LocalPath).ToLower()
            $Res.ContentType = if ($MimeTypes.ContainsKey($ext)) { $MimeTypes[$ext] } else { "application/octet-stream" }
            $bytes = [System.IO.File]::ReadAllBytes($LocalPath)
            $Res.StatusCode = 200
            $Res.OutputStream.Write($bytes, 0, $bytes.Length)
        } else {
            $Res.StatusCode = 404
            $msg = [System.Text.Encoding]::UTF8.GetBytes("404 Not Found")
            $Res.OutputStream.Write($msg, 0, $msg.Length)
        }
    } catch {
        $Res.StatusCode = 500
        $errBytes = [System.Text.Encoding]::UTF8.GetBytes("Server Error: $($_.Exception.Message)")
        $Res.OutputStream.Write($errBytes, 0, $errBytes.Length)
    } finally {
        $Res.Close()
    }
}
