param([string]$BaseUrl='http://localhost/Hospital/public/api/mobile')
$ErrorActionPreference='Stop'
function Call-Mobile($Path,$Body=$null,$Token=$null) {
    $headers=@{Accept='application/json'}
    if($Token){$headers.Authorization="Bearer $Token"}
    if($null -eq $Body){return Invoke-WebRequest "$BaseUrl/$Path" -Headers $headers -SkipHttpErrorCheck}
    Invoke-WebRequest "$BaseUrl/$Path" -Method Post -Headers $headers -ContentType application/json -Body ($Body|ConvertTo-Json -Compress) -SkipHttpErrorCheck
}
function Assert-Mobile($Response,$Expected,$Label) {
    if($Response.StatusCode -ne $Expected){throw "$Label expected $Expected, got $($Response.StatusCode): $($Response.Content.Substring(0,[Math]::Min(300,$Response.Content.Length)))"}
    Write-Output "PASS $Label"
}
function Login-Mobile($Email) {
    $response=Call-Mobile login @{email=$Email;password='CareRoute@2026'}
    Assert-Mobile $response 200 "Login $Email" | Out-Host
    ($response.Content|ConvertFrom-Json).token
}
$tokens=@()
try {
    Assert-Mobile (Call-Mobile health) 200 'Public API health'
    Assert-Mobile (Call-Mobile state) 401 'State requires login'
    Assert-Mobile (Call-Mobile login @{email='patient@careroute.test';password='wrong-password'}) 401 'Incorrect password denied'
    $patient=Login-Mobile 'patient@careroute.test'; $tokens+=$patient
    $operator=Login-Mobile 'hospital.c@careroute.test'; $tokens+=$operator
    $other=Login-Mobile 'hospital.b@careroute.test'; $tokens+=$other
    $admin=Login-Mobile 'admin@careroute.test'; $tokens+=$admin
    foreach($hospitalId in @('A','D','E')) {
        $hospitalToken=Login-Mobile "hospital.$($hospitalId.ToLower())@careroute.test"; $tokens+=$hospitalToken
        $hospitalResponse=Call-Mobile state $null $hospitalToken
        Assert-Mobile $hospitalResponse 200 "Hospital $hospitalId state"
        $hospitalState=$hospitalResponse.Content|ConvertFrom-Json
        if($hospitalState.profile.hospital -ne $hospitalId -or $hospitalState.hospitals.Count -ne 1 -or $hospitalState.hospitals[0].id -ne $hospitalId){throw "Wrong hospital scope for $hospitalId"}
        Write-Output "PASS Hospital $hospitalId sees only its own beds"
    }
    $state=Call-Mobile state $null $patient
    Assert-Mobile $state 200 'Patient token authenticates'
    $stateData=$state.Content|ConvertFrom-Json
    if($stateData.profile.role -ne 'patient'){throw 'Wrong patient role'}
    $marker="Mobile API Smoke $([DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds())"
    $created=Call-Mobile action @{action='create';patient=$marker;location='Accident Site';severity='Critical';care='General'} $patient
    Assert-Mobile $created 200 'Patient submits phone request'
    $id=($created.Content|ConvertFrom-Json).id
    $signupEmail="mobile-smoke-$([DateTimeOffset]::UtcNow.ToUnixTimeMilliseconds())@mobile-smoke.test"
    Assert-Mobile (Call-Mobile register @{name='Smoke patient';email=$signupEmail;password='TestPatient@2026';password_confirmation='mismatch'}) 422 'Signup requires matching password confirmation'
    $signup=Call-Mobile register @{name='Smoke patient';email=$signupEmail;password='TestPatient@2026';password_confirmation='TestPatient@2026'}
    Assert-Mobile $signup 201 'New patient registers'
    $newPatient=($signup.Content|ConvertFrom-Json).token; $tokens+=$newPatient
    $own=(Call-Mobile state $null $newPatient).Content|ConvertFrom-Json
    if($own.profile.role -ne 'patient' -or $own.requests.Count -ne 0){throw 'New patient has wrong role or can see another patient requests'}
    Write-Output 'PASS New patient sees only own requests'
    $webBase=$BaseUrl -replace '/api/mobile$',''
    $loginPage=Invoke-WebRequest "$webBase/login" -SessionVariable signupWebSession
    $csrf=[regex]::Match($loginPage.Content,'name="_token" value="([^"]+)"').Groups[1].Value
    $webSignedIn=Invoke-WebRequest "$webBase/login" -Method Post -WebSession $signupWebSession -Body @{_token=$csrf;email=$signupEmail;password='TestPatient@2026';college_id=1}
    if($webSignedIn.BaseResponse.RequestMessage.RequestUri.AbsolutePath -notmatch '/emergency$'){throw 'New phone patient cannot sign in to the website'}
    Write-Output 'PASS Registered phone patient also signs in to web dashboard'
    Assert-Mobile (Call-Mobile action @{action='request';id=$id;hospital_id='C'} $newPatient) 403 'Another patient cannot reserve this request'
    Assert-Mobile (Call-Mobile action @{action='verify';id=$id;severity='Critical'} $patient) 403 'Self-verification blocked'
    Assert-Mobile (Call-Mobile action @{action='create';patient='Forbidden operator';location='Site';severity='Stable';care='General'} $operator) 403 'Hospital cannot submit patient requests'
    Assert-Mobile (Call-Mobile action @{action='request';id=$id;hospital_id='C'} $patient) 200 'Patient requests compatible bed'
    Assert-Mobile (Call-Mobile action @{action='accept';id=$id} $other) 403 'Cross-hospital acceptance blocked'
    Assert-Mobile (Call-Mobile action @{action='verify';id=$id;severity='Critical'} $operator) 200 'Hospital verifies severity'
    Assert-Mobile (Call-Mobile action @{action='accept';id=$id} $operator) 200 'Hospital confirms bed'
    $visible=(Call-Mobile state $null $patient).Content|ConvertFrom-Json
    if(($visible.requests|Where-Object id -eq $id).status -ne 'Reserved'){throw 'Reservation not synchronized'}
    Write-Output 'PASS Phone state shows confirmed reservation'
    Assert-Mobile (Call-Mobile action @{action='arrive';id=$id} $operator) 200 'Hospital records arrival'
    Assert-Mobile (Call-Mobile logout @{} $patient) 200 'Logout revokes token'
    Assert-Mobile (Call-Mobile state $null $patient) 401 'Revoked token rejected'
} finally {
    foreach($token in $tokens){Call-Mobile logout @{} $token | Out-Null}
    # Remove only this test record under the service lock; preserve all user/demo requests.
    if($id){php tests/mobile-test-cleanup.php $id $signupEmail}
}
