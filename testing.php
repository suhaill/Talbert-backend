<?php
require __DIR__."/vendor/autoload.php";

$client = new \GuzzleHttp\Client([
   'base_url'=>'http://localhost/backend/web/app_dev.php/',
    'defaults'=>[
        'exception'=>false
    ]
]);

$response  = $client->post('/api/programmers');
echo $response;
echo "\n\n";
