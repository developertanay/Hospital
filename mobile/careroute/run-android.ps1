param(
    [string]$ServerUrl='http://10.0.2.2/Hospital/public/api/mobile',
    [string]$DeviceId,
    [switch]$BuildApk
)
$ErrorActionPreference='Stop'
Set-Location -LiteralPath $PSScriptRoot
$flutter=Get-Command flutter -ErrorAction SilentlyContinue
$localSdk=Join-Path $PSScriptRoot '../../.tools/flutter/bin/flutter.bat'
if($flutter){$flutterPath=$flutter.Source}
elseif(Test-Path -LiteralPath $localSdk){$flutterPath=(Resolve-Path -LiteralPath $localSdk).Path}
else{throw 'Install Flutter stable and add its bin folder to PATH.'}
$parsed=$null
if(-not [Uri]::TryCreate($ServerUrl,[UriKind]::Absolute,[ref]$parsed) -or $parsed.Scheme -notin @('http','https') -or $parsed.UserInfo){throw 'Use a valid HTTP/HTTPS mobile API URL.'}
Write-Output 'Checking the local backend from this PC...'
$probeUrl=$ServerUrl.TrimEnd('/')+'/health'
if($parsed.Host -eq '10.0.2.2'){$probeUrl=$probeUrl.Replace('10.0.2.2','localhost')}
$health=Invoke-RestMethod $probeUrl -TimeoutSec 15
if($health.service -ne 'CareRoute'){throw 'The address is not a CareRoute mobile API.'}
& $flutterPath pub get
if($LASTEXITCODE -ne 0){throw 'Flutter dependencies could not be restored'}
if($BuildApk){& $flutterPath build apk --debug "--dart-define=API_BASE_URL=$ServerUrl"}
elseif($DeviceId){& $flutterPath run -d $DeviceId "--dart-define=API_BASE_URL=$ServerUrl"}
else{& $flutterPath run "--dart-define=API_BASE_URL=$ServerUrl"}
exit $LASTEXITCODE
