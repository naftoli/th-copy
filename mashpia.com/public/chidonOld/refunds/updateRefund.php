<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

$admin_id = $_POST['admin_id'];
$checked = intval($_POST['checked']);

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
if ($checked) {
    $stmt = $MASHPIA_DB->prepare("
        UPDATE admins SET show_chidon_refund = 1 WHERE admin_id = :admin
    ");
} else {
    $stmt = $MASHPIA_DB->prepare("
        UPDATE admins SET show_chidon_refund = 0, already_refunded = 0 WHERE admin_id = :admin
    ");
}
$res = $stmt->execute([
    ':admin'    =>  $admin_id
]);

if ($res && $checked) {
    // get email address
    $stmt = $MASHPIA_DB->prepare("
        SELECT first, last, admin_email FROM admins WHERE admin_id = :admin
    ");
    $stmt->execute([':admin' => $admin_id]);
    $row = $stmt->fetch();
    $to = $row['admin_email'];
    $name = $row['first'] . ' ' . $row['last'];
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        // send email to parent
        $from = "accounting@tzivoshashem.org";
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: ' . $from;
        $headers[] = 'Reply-to: ' . $from;
        $subject = "Chidon Shabbaton Refund Page";

        $msg = <<<MSG
Dear $name,
<br /><br />
Thank you for requesting a refund for your child(ren).
<br /><br />
Please log into your Tzivos Hashem parents account. 
<br /><br />
Go to http://TzivosHashem.com/mobile
<br /><br />
Enter your username and password.
<br /><br />
If you don't know your username and password click on forgot password and enter the email address associated 
with your parent account: ($to)
<br /><br />
In the top right corner you will see a button that says "request refund". 
<br /><br />
Click on that button and fill out the form.
<br /><br />
We wish you much continued Nachas from your child(ren).
<br /><br />
We look forward to the ultimate chidon with Moshiach Now.
<br /><br />
Kesiva Vachasima Tova 
<br /><br />
Shimmy Weinbaum <br />
Chidon Director 
MSG;

        if (!mail($to, $subject, $msg, implode("\r\n", $headers))) {
            // send mail to myself
            $subject = "Error sending chidon refund email";
            $to = 'naftoli@tzivoshashem.org';
            @mail($to, $subject, $msg, implode("\r\n", $headers));
        }
    }
}

echo json_encode([
    'success'   =>  $res
]);