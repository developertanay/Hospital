param([string]$BaseUrl='http://localhost/Hospital/public')
$ErrorActionPreference='Stop'
# Read-only HTTP checks; does not create requests or reset emergency data.
$rolePages=@{
    'admin@careroute.test'=@('dashboard')
    'patient@careroute.test'=@('dashboard','requests','reservations','history')
    'hospital.c@careroute.test'=@('dashboard','approvals','beds','history','inventory','blood','settings')
}
$checks=0
foreach($email in $rolePages.Keys) {
    $login=Invoke-WebRequest "$BaseUrl/login" -SessionVariable accountSession -UseBasicParsing
    $token=[regex]::Match($login.Content,'name="_token" value="([^"]+)"').Groups[1].Value
    $signedIn=Invoke-WebRequest "$BaseUrl/login" -Method Post -WebSession $accountSession -Body @{_token=$token;email=$email;password='CareRoute@2026';college_id=1} -UseBasicParsing
    foreach($page in @('dashboard','requests','approvals','beds','reservations','history','inventory','blood','settings')) {
        $path=if($page -eq 'dashboard') {'emergency'} else {"emergency/$page"}
        $expected=if($rolePages[$email] -contains $page) {200} else {403}
        foreach($url in @("$BaseUrl/$path","$BaseUrl/emergency/state?page=$page")) {
            $response=Invoke-WebRequest $url -WebSession $accountSession -SkipHttpErrorCheck -UseBasicParsing
            if($response.StatusCode -ne $expected) {throw "$email $url expected $expected, got $($response.StatusCode)"}
            $checks++
        }
        if($expected -eq 200) {
            $state=$response.Content | ConvertFrom-Json
            if($page -eq 'dashboard' -and ($state.requests.Count -or $state.hospitals.Count -or $state.events.Count)) {throw 'Dashboard received detailed records'}
            if($page -ne 'beds' -and $state.hospitals.Count) {throw 'Individual beds exposed on unrelated page'}
            if($page -ne 'history' -and $state.events.Count) {throw 'Decision history exposed on unrelated page'}
            if($email -eq 'hospital.c@careroute.test' -and @($state.hospitals | Where-Object id -ne 'C').Count) {throw 'Hospital C received another hospital beds'}
            foreach($record in $state.requests) {
                if($record.PSObject.Properties.Name -contains 'owner_id') {throw 'Internal ownership identifier exposed'}
                if($email -eq 'hospital.c@careroute.test' -and $record.status -ne 'New' -and $record.hospital_id -ne 'C') {throw 'Hospital C received another hospital request'}
                if($page -ne 'requests' -and $record.PSObject.Properties.Name -contains 'matches') {throw 'Recommendations exposed on unrelated page'}
            }
        }
    }
    Write-Output "PASS ${email}: pages and data permissions"
}
Write-Output "PASS: $checks page/data access checks"
