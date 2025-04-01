<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied!');
}

require_once 'Installments.php';
use \classes\authorize\Installments as Installments;

$info = Installments::getSubscriptions();
return json_encode($info);