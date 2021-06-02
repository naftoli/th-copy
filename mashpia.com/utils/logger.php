<?php

// monolog usage docs: https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\UidProcessor;

if (!isset($logger)) {
    // create a logger instance
    $logger = new Logger("general");

    $logger->pushHandler(new StreamHandler(
        // Set the log output file to mashpia.com/tmp/.log
        __DIR__ . '/../tmp/.log',
        // Set the minimum log level DEBUG in development or when debug is true.
        (isset($development) && $development)
        || (isset($_GET['debug']) && $_GET['debug'])
        || (isset($_POST['debug']) && $_POST['debug'])
            ? Logger::DEBUG
            : Logger::INFO
    ));

    // add request method and url to all logs
    $logger->pushProcessor(function ($record) {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $record['extra']['method'] = $_SERVER['REQUEST_METHOD'];
        }
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
            $record['extra']['url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        return $record;
    });

    // add file, line, class and function/method to all logs
    $logger->pushProcessor(new IntrospectionProcessor());

    // add a unique request id to all logs
    $logger->pushProcessor(new UidProcessor());

    // register to log all thrown errors, exceptions and fatal errors.
    ErrorHandler::register($logger);

    $logger->info('request started');
}