<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../src/Kernel.php';

$kernel = new \App\Kernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);