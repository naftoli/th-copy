<?php
require_once '../db.php';
$school_id = mysql_real_escape_string($_POST['school']);

$sql = "select school_name, reg_type from schools where school_id = " . $school_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

$to = "cth@tzivoshashem.org";
//$to = "naftolir@gmail.com";
$subject = "School Registration 5778";
$msg = $row['school_name'] . " has just registered their school for 5778.";
$headers = 'From: admin@mashpia.com' . "\r\n";
$headers .= 'Reply-to: cth@tzivoshashem.org' . "\r\n";

if ($row['reg_type'] == 1) $msg .= " They have indicated that they are a tuition school that pays for all their students.";
if ($row['reg_type'] == 2) $msg .= " They have indicated that they are a tuition school where the parents pay and they only pay for any unregistered children.";
if ($row['reg_type'] == 1) $msg .= " They have indicated that they are not a tuition school and parents need to pay complete fee.";
mail($to, $subject, $msg, $headers);