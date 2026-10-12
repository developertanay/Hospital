param([string]$ApiBaseUrl='http://10.9.4.210/Hospital/public/api/mobile')
$ErrorActionPreference='Stop'
$projectRoot=Resolve-Path (Join-Path $PSScriptRoot '../..')
$flutterCommand=Join-Path $projectRoot '.tools/flutter/bin/flutter.bat'
if (!(Test-Path $flutterCommand)) { throw 'Install Flutter stable in .tools/flutter or update this script to your Flutter SDK.' }
$env:PUB_CACHE=Join-Path $projectRoot '.tools/pub-cache'
$env:GRADLE_USER_HOME=Join-Path $projectRoot '.tools/gradle-cache'
$env:ANDROID_HOME=Join-Path $env:LOCALAPPDATA 'Android/Sdk'
$env:JAVA_HOME='C:\Program Files\Eclipse Adoptium\jdk-17.0.18.8-hotspot'
Push-Location $PSScriptRoot
try {
    & $flutterCommand build apk --debug "--dart-define=API_BASE_URL=$ApiBaseUrl"
    if ($LASTEXITCODE -ne 0) { throw 'APK build failed.' }
    $outputFolder=Join-Path $projectRoot 'deliverables'
    New-Item -ItemType Directory -Path $outputFolder -Force | Out-Null
    Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'build/app/outputs/flutter-apk/app-debug.apk') -Destination (Join-Path $outputFolder 'Hospital-Test.apk')
} finally { Pop-Location }
