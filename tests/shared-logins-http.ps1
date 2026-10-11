param([string]$BaseUrl='http://localhost/Hospital/public')
$ErrorActionPreference='Stop'
$health=Invoke-RestMethod "$BaseUrl/api/mobile/health"
if ($health.storage -ne 'mysql' -or $health.mock_data) { throw 'Database API health failed' }
$accounts=@{
    'patient.test@careroute.test'='patient'
    'hospital.test@careroute.test'='hospital'
    'driver1.test@careroute.test'='driver'
    'driver2.test@careroute.test'='driver'
    'admin.test@careroute.test'='admin'
}
foreach ($email in $accounts.Keys) {
    $session=New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $page=Invoke-WebRequest "$BaseUrl/login" -WebSession $session -UseBasicParsing
    $match=[regex]::Match($page.Content,'name="_token" value="([^"]+)"')
    if (!$match.Success) { throw 'Login CSRF field missing' }
    $web=Invoke-WebRequest "$BaseUrl/login" -Method POST -WebSession $session -UseBasicParsing -Body @{_token=$match.Groups[1].Value;email=$email;password='TestCare@2026'}
    $logoutToken=[regex]::Match($web.Content,'name="_token" value="([^"]+)"').Groups[1].Value
    if ($web.Content -match 'These credentials do not match' -or $web.Content -match 'id="login-form"') { throw "Web login failed: $email" }
    $mobile=Invoke-RestMethod "$BaseUrl/api/mobile/login" -Method POST -ContentType 'application/json' -Body (@{email=$email;password='TestCare@2026'} | ConvertTo-Json)
    try {
        if ($mobile.profile.role -ne $accounts[$email]) { throw "Role mismatch for $email" }
        $headers=@{Authorization='Bearer '+$mobile.token;Accept='application/json'}
        $state=Invoke-RestMethod "$BaseUrl/api/mobile/state" -Headers $headers
        if ($state.profile.id -ne $mobile.profile.id) { throw "Profile mismatch for $email" }
        if ($accounts[$email] -eq 'driver') { $null=Invoke-RestMethod "$BaseUrl/api/mobile/sos/state" -Headers $headers }
        Write-Output "PASS: web and mobile login for $email"
    } finally {
        $null=Invoke-RestMethod "$BaseUrl/api/mobile/logout" -Method POST -Headers $headers -ContentType 'application/json' -Body '{}'
        $null=Invoke-WebRequest "$BaseUrl/logout" -Method POST -WebSession $session -UseBasicParsing -Body @{_token=$logoutToken}
    }
}
