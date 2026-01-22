<?php

$url = 'http://localhost:8000/api/auth/login';
$data = ['username' => 'superadmin', 'password' => 'password'];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Fetch content even on 4xx/5xx
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$headers = $http_response_header;

echo "Headers:\n";
print_r($headers);
echo "\nBody:\n";
echo $result;
