param([string]$BaseUrl='http://localhost/Hospital/public/api/mobile')
$ErrorActionPreference='Stop'
$health=Invoke-WebRequest "$BaseUrl/health" -Headers @{Accept='application/json'}
if (($health.Content | ConvertFrom-Json).service -ne 'CareRoute') {throw 'Unexpected mobile API'}
Write-Output 'PASS Local mobile API health'
$anonymous=Invoke-WebRequest "$BaseUrl/state" -Headers @{Accept='application/json'} -SkipHttpErrorCheck
if ($anonymous.StatusCode -ne 401) {throw 'Anonymous state access must be denied'}
Write-Output 'PASS Local mobile API authentication required'
# All writes run in SQLite fixtures; installed SQL/JSON records remain untouched.
$taskRoot=Split-Path $PSScriptRoot -Parent
foreach ($taskTest in @('mobile-merge-smoke.php','sos-api-smoke.php','mobile-token-smoke.php')) {
    & php (Join-Path $PSScriptRoot $taskTest)
    if ($LASTEXITCODE -ne 0) {throw "Failed $taskTest"}
}
