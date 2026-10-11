param([string]$BaseUrl='http://localhost/Hospital/public')
# The old demo reset/account script is retired. SQL workflow tests run with
# disposable fixtures and rollback, while HTTP permissions use real accounts.
& php (Join-Path $PSScriptRoot 'emergency-smoke.php')
if($LASTEXITCODE -ne 0) {exit $LASTEXITCODE}
& php (Join-Path $PSScriptRoot 'database-smoke.php')
if($LASTEXITCODE -ne 0) {exit $LASTEXITCODE}
& (Join-Path $PSScriptRoot 'demo-page-access.ps1') -BaseUrl $BaseUrl
