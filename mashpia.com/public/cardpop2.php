<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$card = mysql_real_escape_string($_REQUEST['card']);
$result = Points::updateScanned($card);
if (isset($result['data'])) {
	// mail issue to myself
	$to = 'naftoli@tzivoshashem.org';
    $from = 'debug@tzivoshashem.org';
    $subject = 'Achievement Card Issue';
    $message = "These qrys failed: " . implode("<br />", $result['data']);
    $headers = "From: $from\r\n";
    $headers .= "Content-type: text/html\r\n";
    mail($to, $subject, $message, $headers);
}
?>