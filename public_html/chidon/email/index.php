<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

// Additional headers
$headers[] = 'From: Chidon Committee <chidon@tzivoshashem.org>';
//$headers[] = 'Cc: naftoli@tzivoshashem.org';

// Mail it
$to = 'mushka@tzivoshashem.org';
$subject = 'Watch the Chidon Hamitzvos Live!';
$message = "<html><head></head><body><div style='font-size: 18px;'>Tickets still available for purchase at <a href='chidon613.com'>chidon613.com</a></div>";
$message .= "<img src='http://mashpia.com/chidon/email/image1.jpeg' /><br /><br /></body></html>";

// get emails to send to
$emails = array();
$sql = "select admin_email from admins a 
        join admin_auths aa using (admin_id) 
        join users u on u.user_id = aa.id 
        where aa.auth = 'user' 
        and u.user_registered > 0 
        group by admin_email";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $email = $row['admin_email'];
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) { // if valid email address add to array or email addresses
        $emails[] = $email;
    }
}
echo "<pre>"; print_r( $emails ); echo "</pre>"; exit;

$sent = 0;
$startEmailNum = 990; // first 989 already sent
$total = count( $emails );
for ($i = $startEmailNum; $i < $total; $i++) {
    $to = $emails[$i];
    if (@mail($to, $subject, $message, implode("\r\n", $headers))) {
        $sent++;
    }
    if ($i % 250 == 0) {
        echo "250 emails sent.<br />";
        sleep(600);
    }
}
echo 'Emails sent: ' . $sent;
?>