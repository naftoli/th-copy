<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$info = [];
$sql = 'select admin_email from admins where admin_id in (
        select parent_id from th_chidon where year = 5780 
        and can_enroll = 1)';
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}

foreach ( $info as $row ) {
    $to = $row['admin_email'];
    $subject = "Chidon Shabbaton Error";
    $msg = "A previous email about Shabbaton enrollment was sent out in error. Enrollment for this year has not yet begun. Please stay tuned for further updates. Thank you for your patience!";
    $headers = 'From: chidon@tzivoshashem.org' . "\r\n" .
                'Reply-To: chidon@tzivoshashem.org' . "\r\n";
    @mail( $to, $subject, $msg, $headers );
}
echo "sent.";