<?php

declare(strict_types=1);

use DZunke\NovDoc\Web\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->loadEnv(__DIR__ . '/../.env');

$kernel   = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
