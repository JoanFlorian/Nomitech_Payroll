<?php

function curl_get($url, &$cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

function curl_post($url, $postFields, &$cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

$cookieFile = sys_get_temp_dir() . '/nomitech_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// 1. GET login page to fetch CSRF token
list($loginHtml, $info) = curl_get('http://127.0.0.1:8000/login2', $cookieFile);
if (!$loginHtml) {
    echo "Failed to GET login page\n";
    exit(1);
}

// Extract CSRF token
if (preg_match('/name="_token" value="([^"]+)"/', $loginHtml, $m)) {
    $token = $m[1];
    echo "Found CSRF token: $token\n";
} else {
    echo "CSRF token not found in login page\n";
    // attempt to find meta tag
    if (preg_match('/meta name="csrf-token" content="([^"]+)"/', $loginHtml, $m2)) {
        $token = $m2[1];
        echo "Found CSRF token in meta: $token\n";
    } else {
        exit(1);
    }
}

// 2. POST login
$post = [
    '_token' => $token,
    'correo' => 'test@nomitech.test',
    'contrasena' => 'password123'
];

list($response, $info2) = curl_post('http://127.0.0.1:8000/login', $post, $cookieFile);

echo "HTTP status: " . $info2['http_code'] . "\n";

// print headers and first 800 chars of body
echo substr($response, 0, 2000) . "\n";

// cleanup
if (file_exists($cookieFile)) unlink($cookieFile);

