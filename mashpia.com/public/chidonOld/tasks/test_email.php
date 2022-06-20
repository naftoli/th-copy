<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

//require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/classes/recruits.php';
//$r = new Recruits(7745260);
//$error = $r->sendEmail('Michel Rapoport');
//if (intval($error) != 1) echo $error;

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/email.php';

$email = new Email();
$params = [
    'to'    => 'naftolir@gmail.com',
    'subject'   => 'Testing emailing',
    'msg'   => 'Hi There',
    'from'  => 'chidon@tzivoshashem.org'
];
$email->sendEmail($params);