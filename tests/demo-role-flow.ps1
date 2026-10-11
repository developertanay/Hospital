param([string]$BaseUrl='http://localhost/Hospital/public')
$ErrorActionPreference='Stop'
function Login-Demo([string]$Email) {
    $page=Invoke-WebRequest "$BaseUrl/login" -SessionVariable accountSession -UseBasicParsing
    $token=[regex]::Match($page.Content,'name="_token" value="([^"]+)"').Groups[1].Value
    $page=Invoke-WebRequest "$BaseUrl/login" -Method Post -WebSession $accountSession -Body @{_token=$token;email=$Email;password='CareRoute@2026';college_id=1} -UseBasicParsing
    $token=[regex]::Match($page.Content,'name="csrf-token" content="([^"]+)"').Groups[1].Value
    if(-not $token){throw "Login failed: $Email"}
    return @{Session=$accountSession;Token=$token}
}
function Action-Demo($Account,$Payload) {
    Invoke-WebRequest "$BaseUrl/emergency/action" -Method Post -WebSession $Account.Session -Headers @{'X-CSRF-TOKEN'=$Account.Token;Accept='application/json'} -ContentType 'application/json' -Body ($Payload|ConvertTo-Json -Compress) -SkipHttpErrorCheck
}
function Assert-Status($Response,[int]$Expected,[string]$Label){if($Response.StatusCode -ne $Expected){throw "$Label expected $Expected, got $($Response.StatusCode): $($Response.Content.Substring(0,[Math]::Min(300,$Response.Content.Length)))"};Write-Output "PASS $Label ($Expected)"}
$admin=Login-Demo 'admin@careroute.test'
Assert-Status (Action-Demo $admin @{action='reset'}) 403 'Admin cannot alter emergency workflow'
$patient=Login-Demo 'patient@careroute.test'
$hospitalC=Login-Demo 'hospital.c@careroute.test'
$hospitalB=Login-Demo 'hospital.b@careroute.test'
foreach($path in @('Hospital','Bedtype','BloodType','BedInven','BloodInven','demo/modules','demo/users','ModuleMaster','ModuleAssigning','NewUser','Hospital/create','Bedtype/create','BloodType/create','BedInven/create','BloodInven/create','ModuleAssigning/create?college_id=1&role_id=2')) {
    $page=Invoke-WebRequest "$BaseUrl/$path" -WebSession $admin.Session -SkipHttpErrorCheck -UseBasicParsing
    Assert-Status $page 200 "Admin module $path"
}
Assert-Status (Invoke-WebRequest "$BaseUrl/Hospital" -WebSession $patient.Session -SkipHttpErrorCheck -UseBasicParsing) 403 'Patient denied masters'
$created=Action-Demo $patient @{action='create';patient="Role Flow Test $([DateTimeOffset]::UtcNow.ToUnixTimeSeconds())";location='Accident Site';severity='Critical';care='ICU'}
Assert-Status $created 200 'Patient creates emergency'
$id=($created.Content|ConvertFrom-Json).id
Assert-Status (Action-Demo $patient @{action='verify';id=$id;severity='Critical'}) 403 'Patient cannot self-verify'
Assert-Status (Action-Demo $patient @{action='login';pin='2468'}) 403 'PIN cannot elevate patient role'
Assert-Status (Action-Demo $patient @{action='request';id=$id;hospital_id='C'}) 200 'Patient requests C'
Assert-Status (Action-Demo $hospitalB @{action='accept';id=$id}) 403 'Hospital B cannot accept C request'
Assert-Status (Action-Demo $hospitalC @{action='verify';id=$id;severity='Critical'}) 200 'Hospital C verifies Critical'
Assert-Status (Action-Demo $hospitalC @{action='accept';id=$id}) 200 'Hospital C accepts reservation'
$state=Invoke-RestMethod "$BaseUrl/emergency/state?page=reservations" -WebSession $patient.Session
if(($state.requests|Where-Object id -eq $id).status -ne 'Reserved'){throw 'Patient cannot see Reserved status'}
Write-Output 'PASS Patient sees Reserved'
Assert-Status (Action-Demo $hospitalC @{action='arrive';id=$id}) 200 'Hospital C marks arrival'
Assert-Status (Action-Demo $hospitalC @{action='reset'}) 403 'Hospital cannot reset demo'
Assert-Status (Action-Demo $admin @{action='reset'}) 403 'Admin cannot reset care data'
Write-Output 'Role flow complete. Test requests remain in the demo store; run on a prepared disposable demo instance.'
