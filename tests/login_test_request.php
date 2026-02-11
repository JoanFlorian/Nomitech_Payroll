<?php
$data = http_build_query(['correo' => 'test@nomitech.test', 'contrasena' => 'password123']);
$opts = ['http' => [
    'method' => 'POST',
    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
    'content' => $data,
    'ignore_errors' => true,
]];
$context = stream_context_create($opts);
$res = file_get_contents('http://127.0.0.1:8000/login', false, $context);
if (isset($http_response_header)) {
    foreach ($http_response_header as $h) {
        echo $h . "\n";
    }
}
echo "\n\n";
echo substr($res, 0, 3000);
