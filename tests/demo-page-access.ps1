param([string]$BaseUrl='http://localhost/Hospital/public')
$ErrorActionPreference='Stop'
# Supply real test credentials in CARE_ADMIN_EMAIL/PASSWORD,
# CARE_PATIENT_EMAIL/PASSWORD and CARE_HOSPITAL_EMAIL/PASSWORD.
# This check reads existing records; it never resets or creates business data.
$checks=0
foreach($role in @('admin','patient','hospital')) {
    $prefix='CARE_'+$role.ToUpper()
    $email=[Environment]::GetEnvironmentVariable($prefix+'_EMAIL')
    $password=[Environment]::GetEnvironmentVariable($prefix+'_PASSWORD')
    if(-not $email -or -not $password) {throw "Set ${prefix}_EMAIL and ${prefix}_PASSWORD before running."}
    $login=Invoke-WebRequest "$BaseUrl/login" -SessionVariable accountSession -UseBasicParsing
    $token=[regex]::Match($login.Content,'name="_token" value="([^"]+)"').Groups[1].Value
    $signedIn=Invoke-WebRequest "$BaseUrl/login" -Method Post -WebSession $accountSession -Body @{_token=$token;email=$email;password=$password} -UseBasicParsing
    $summary=Invoke-RestMethod "$BaseUrl/emergency/state" -WebSession $accountSession
    if($summary.profile.role -ne $role) {throw "Configured account is not $role."}
    $allowed=@('dashboard')
    if($role -eq 'patient') {$allowed+=@('requests','reservations','history')}
    if($role -eq 'hospital' -and $summary.profile.hospital) {$allowed+=@('approvals','beds','history','inventory','blood','settings')}
    foreach($page in @('dashboard','requests','approvals','beds','reservations','history','inventory','blood','settings')) {
        $path=if($page -eq 'dashboard') {'emergency'} else {"emergency/$page"}
        $expected=if($allowed -contains $page) {200} else {403}
        foreach($url in @("$BaseUrl/$path","$BaseUrl/emergency/state?page=$page")) {
            $response=Invoke-WebRequest $url -WebSession $accountSession -SkipHttpErrorCheck -UseBasicParsing
            if($response.StatusCode -ne $expected) {throw "$role $path expected $expected, got $($response.StatusCode)"}
            $checks++
        }
        if($expected -eq 200) {
            $state=$response.Content|ConvertFrom-Json
            if($page -eq 'dashboard' -and ($state.requests.Count -or $state.hospitals.Count -or $state.events.Count)) {throw 'Dashboard exposed detail records'}
            if($page -ne 'beds' -and $state.hospitals.Count) {throw 'Beds exposed on unrelated page'}
            if($role -eq 'hospital' -and @($state.hospitals|Where-Object {([string]$_.id) -ne $summary.profile.hospital}).Count) {throw 'Other hospital data exposed'}
        }
    }
    Write-Output "PASS $role account page and data permissions"
}
Write-Output "PASS: $checks read-only access checks"
