<?php
// Exercise Apache authentication; remove only the tokens issued by this check.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$base = rtrim($argv[1] ?? 'http://localhost/Hospital/public/api/mobile', '/');
$tokens = [];
$accounts = [
    'patient.test@careroute.test' => 'patient',
    'hospital.test@careroute.test' => 'hospital',
    'driver1.test@careroute.test' => 'driver',
    'driver2.test@careroute.test' => 'driver',
    'admin.test@careroute.test' => 'admin',
];

function loginHttp($path, $body = null)
{
    global $base;
    $options = ['method' => $body === null ? 'GET' : 'POST',
        'header' => "Accept: application/json\r\nContent-Type: application/json\r\n",
        'timeout' => 15, 'ignore_errors' => true];
    if ($body !== null) $options['content'] = json_encode($body);
    $response = file_get_contents($base.'/'.$path, false, stream_context_create(['http' => $options]));
    if ($response === false) throw new RuntimeException('Apache API is unreachable.');
    $data = json_decode($response, true);
    if (!is_array($data)) throw new RuntimeException('Expected an API JSON response.');
    preg_match('/\s(\d{3})\s/', $http_response_header[0], $match);
    return [(int) ($match[1] ?? 0), $data];
}

try {
    [$status, $health] = loginHttp('health');
    if ($status !== 200 || ($health['storage'] ?? '') !== 'mysql') throw new RuntimeException('Database health failed.');
    foreach ($accounts as $email => $role) {
        [$status, $result] = loginHttp('login', ['email' => $email, 'password' => 'TestCare@2026']);
        if (isset($result['token'])) $tokens[] = $result['token'];
        if ($status !== 200 || !isset($result['token']) || ($result['profile']['role'] ?? '') !== $role) {
            throw new RuntimeException('Login failed for '.$email.' (HTTP '.$status.').');
        }
        echo 'PASS: Apache API login for '.$email."\n";
    }
} finally {
    foreach ($tokens as $token) {
        $issued = Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if ($issued) $issued->delete();
    }
}
