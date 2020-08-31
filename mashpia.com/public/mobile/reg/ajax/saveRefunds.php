<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';

$total = $_POST['total'];
$donation = $_POST['donation'];
$donation50 = implode(',', $_POST['donation50']);
$num_donation_50 = $_POST['num_donation_50'];
$num_children = $_POST['num_children'];
$admin = encrypt_decrypt('decrypt', $_COOKIE['admin']);
$year = 5780;

// first delete any existing refund requests
$stmtDelete = $MASHPIA_DB->prepare("
    DELETE FROM chidon_refunds 
    WHERE year = :year AND admin_id = :admin
");
$resDelete = $stmtDelete->execute([
    ':year' => $year,
    ':admin'=> $admin
]);

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO chidon_refunds 
    SET 
        year = :year, 
        admin_id = :admin, 
        donation = :donation, 
        refund = :total, 
        donation_50 = :donation50,
        num_donation_50 = :num50,
        num_children = :num_children
");
$res = $stmt->execute([
    ':year'     => $year,
    ':admin'    => $admin,
    ':donation' => $donation,
    ':total'    => $total,
    ':donation50'=> $donation50,
    ':num50'    => $num_donation_50,
    ':num_children' => $num_children
]);
if ($res) {
    $stmt = $MASHPIA_DB->prepare("
        UPDATE admins SET already_refunded = 1 WHERE admin_id = :admin
    ");
    $stmt->execute([':admin' => $admin]);

    // send email to parent
    // get email address
    $stmt = $MASHPIA_DB->prepare("
        SELECT first, last, admin_email FROM admins WHERE admin_id = :admin
    ");
    $stmt->execute([':admin' => $admin]);
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
        $subject = "Chidon Shabbaton Refund Request";

        $msg = <<<MSG
Dear $name,
<br /><br />
Thank you for requesting a refund for your child(ren).
<br /><br />
We will be submitting all refunds requests on Monday the 25th of Elul, September 14. Please allow 7 days after that for your card to be refunded.
<br /><br />
We wish you much continued Nachas from your child(ren).
<br /><br />
We look forward to the ultimate Chidon with Moshiach Now.
<br /><br />
Kesiva Vachasima Tova <br />
Shimmy Weinbaum <br />
Chidon Director
MSG;

        if (!mail($to, $subject, $msg, implode("\r\n", $headers))) {
            // send mail to myself
            $subject = "Error sending chidon refund request email";
            $to = 'naftoli@tzivoshashem.org';
            @mail($to, $subject, $msg, implode("\r\n", $headers));
        }
    }

    echo json_encode([
        'success'   => true
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'There was an error processing your request'
    ]);
}