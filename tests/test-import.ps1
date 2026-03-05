$body = @{
    data = @{
        vessels = @(
            @{imo = "VSL001"; name = "Ocean Queen"; flag = "LR"},
            @{imo = "VSL002"; name = "Sea Master"; flag = "PA"},
            @{imo = "VSL003"; name = "Global Voyager"; flag = "BS"}
        )
        ports = @(
            @{code = "PRT001"; name = "Rotterdam"; country = "NL"},
            @{code = "PRT002"; name = "New York"; country = "US"},
            @{code = "PRT003"; name = "Singapore"; country = "SG"}
        )
        companies = @(
            @{tax_id = "CMP001"; name = "Maersk Line"},
            @{tax_id = "CMP002"; name = "MSC"},
            @{tax_id = "CMP003"; name = "CMA CGM"}
        )
    }
} | ConvertTo-Json -Depth 3

Write-Host "Sending request with all data types..." -ForegroundColor Green
Write-Host "`nRequest body:" -ForegroundColor Yellow
Write-Host $body -ForegroundColor Gray

Write-Host "`nSending..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080/api/import" `
        -Method POST `
        -Headers @{"Content-Type" = "application/json"} `
        -Body $body `
        -UseBasicParsing

    Write-Host "`nResponse Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response Body: $($response.Content)" -ForegroundColor Cyan

    # Проверка после отправки
    Write-Host "`nWaiting for processing (3 seconds)..." -ForegroundColor Yellow
    Start-Sleep -Seconds 3

    Write-Host "`nChecking database:" -ForegroundColor Yellow
    Write-Host "`nVessels:" -ForegroundColor Cyan
    docker compose exec db psql -U postgres -d feip_db -c "SELECT * FROM vessels;"

    Write-Host "`nPorts:" -ForegroundColor Cyan
    docker compose exec db psql -U postgres -d feip_db -c "SELECT * FROM ports;"

    Write-Host "`nCompanies:" -ForegroundColor Cyan
    docker compose exec db psql -U postgres -d feip_db -c "SELECT * FROM companies;"
}
catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $stream = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response Body: $responseBody" -ForegroundColor Red
    }
}
