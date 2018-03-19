<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

require_once(dirname(__FILE__).'/../../shared/classes/Prize.php');
use raffles\weekly\Prize as Prize; // use the prizes for the prize form under the edit action

$prizes = Prize::loadAll();

echo json_encode($prizes);