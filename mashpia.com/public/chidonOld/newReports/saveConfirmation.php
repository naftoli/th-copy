<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$admin_id = mysql_real_escape_string($_POST['id']);
$year = mysql_real_escape_string($_POST['year']);

// get all the schools connected to this admin
echo "<pre>"; print_r($admin_user); echo "</pre>";