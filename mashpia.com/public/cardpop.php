<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$school = mysql_real_escape_string($_REQUEST['school_id']);
$class = mysql_real_escape_string($_REQUEST['class_id']);
$user = mysql_real_escape_string($_REQUEST['user_id']);
$card = mysql_real_escape_string($_REQUEST['card']);

$msg = Points::scanMiles($school, $class, $user, $card);
echo $msg;

$message = json_decode($msg, true);
if ($message['success']) {
    $updated = Points::updateScanned($card);
    if (! $updated['success']) {
        // mail issue to myself
        $to = 'naftoli@tzivoshashem.org';
        $from = 'debug@tzivoshashem.org';
        $subject = 'Achievement Card Issue';
        $message = $updated['msg'];
        $headers = "From: $from\r\n";
        $headers .= "Content-type: text/html\r\n";
        mail($to, $subject, $message, $headers);
    }
}
?> 