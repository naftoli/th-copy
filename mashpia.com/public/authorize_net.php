<?php
include("db.php");

//check for spammers    
include 'check_for_spammers.php';

foreach ($_POST as $k => $v) {
	$_POST[$k] = mysql_real_escape_string(trim($v));
}

require_once '../includes/authorize_net.php';
?>