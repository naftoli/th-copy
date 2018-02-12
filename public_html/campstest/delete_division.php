<?php
$admin_auth = array('camp');
require('../header.php'); 

$division_id = $_GET['division_id'];

$sql = "DELETE FROM divisions WHERE division_id=" . $division_id;
mq($sql);
?>