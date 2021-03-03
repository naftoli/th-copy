<?php

// monolog usage docs: https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (!isset($logger)) {
    // create a logger instance
    $logger = new Logger("general");

    // Set the log output file to mashpia.com/tmp/.log
    // and set the minimum log level DEBUG to log all log levels.
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../tmp/.log', Logger::DEBUG));

    // register to also log all errors, exceptions and fatal errors
    ErrorHandler::register($logger);

    if (isset($_SERVER['REQUEST_METHOD']) && isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
        $logger->info('request started', [
            "method" => $_SERVER['REQUEST_METHOD'],
            "url" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        ]);
    }
}