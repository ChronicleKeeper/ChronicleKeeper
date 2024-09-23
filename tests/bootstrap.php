<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

/**
 * Workaround for PHPUnit 11
 *
 * @see https://github.com/symfony/symfony/issues/53812
 */
ErrorHandler::register(null, false);
