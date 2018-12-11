<?php
$admin_auth = array('school');
require 'header.php';

$user_id = $_GET['id'];
echo totalMarks("WHERE user_id = " . $user_id);
$r =  mq(totalMarks("WHERE user_id = " . $user_id));
$row = mysql_fetch_assoc($r);
echo "<pre>";
print_r($row);
//var_dump(header_icorpa_points_multi_testing(array(
//  'user_code' => array('33232326565311160300')
//)));
echo "</pre>";
?>