<?php
//echo "<pre>";
//print_r( $_POST );
//echo "</pre>";
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

//$ip = $_SERVER['SERVER_ADDR'];
//if ($ip == '39.53.201.236') {
//    $msg = 'Go Away!';
//    header("Location: https://mashpia.com/donate/payment.php?error=" . $msg);
//    exit;
//}
const SITE_URL = 'https://mashpia.com/payment/index.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
foreach ($_POST as $k => $v) {
    $_POST[$k] = mysql_real_escape_string(trim($v));
}

// check captcha
/*
$privatekey = '6LcPSR0UAAAAAMMBnZpu9a4Ru5sNmrfgeEYVmWPw';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'secret'   => $privatekey,
    'response' => $_POST['g-recaptcha-response'],
    'remoteip' => $_SERVER['REMOTE_ADDR']
]);

$resp = json_decode(curl_exec($ch));
curl_close($ch);

if (!$resp->success) {
    $error = "Please click on the reCAPTCHA box.";
    header("Location: " . SITE_URL . "?error=" . urlencode($error));
    exit;
}
*/
$amount = (int)$_POST['amount'];
if ($amount <= 0) {
    $error = "You have not entered a valid amount!";
    header("Location: " . SITE_URL . "?error=" . urlencode($error));
    exit;
}

foreach ( $_POST as $k => $v ) {
    $_POST[$k] = trim($v);
}

$card_num 	= $_POST['ccnum'];
$exp_date 	= $_POST['ccexp'];
$first_name = $_POST['ccfname'];
$last_name	= $_POST['cclname'];
$invoice    = $_POST['invoice'];
$description = "Payment from " . ucwords($first_name) . ' ' . ucwords($last_name) . " for Invoice #" . $invoice . " (" . $_POST['desc'] . ")";
$address	= $_POST['ccaddress'] . ' ' . $_POST['ccaddress2'];
$city		= $_POST['cccity'];
$state		= $_POST['ccstate'];
$zip		= $_POST['cczip'];
$email 		= $_POST['email'];
$phone		= $_POST['phone'];
$cvv		= $_POST['cccvv'];
$country    = $_POST['cccountry'] ?? '';

if (! ($card_num && $exp_date && $first_name && $last_name && $address && $city && $state && $zip && $email && $phone && $cvv) ) {
    $error = "All fields are mandatory, please try again.";
    header("Location: " . SITE_URL . "?error=" . urlencode( $error ));
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/authorize.php';
$charged = false;
$response = '';
if ($response_array) {
    if ($response_array[0] == 1) {
        $response .= $response_array[0] . ":";
        $response .= $response_array[3] . ":";
        $response .= $response_array[4] . ":";
        $response .= $response_array[6] . ":";
        $response .= $response_array[9];
        $charged = true;
    } else {
        $response .= $response_array[3] . "\n";
    }
}

if ($charged) {
    // send confirmation email
    include_once("../classes/send_mail.php");
    include_once("../constant_file.php");

    // Format amount consistently
    $formattedAmount = number_format($amount, 2);
    
    // Email notification
    $mail_parms = [
        'to' => $email, 
        'cc' => "cthshipping@gmail.com", // dovie
        'subject' => "Confirmation of Credit Card Transaction",
        'message' => "Thank you for your payment to Tzivos Hashem. Your credit card has been charged $" . $formattedAmount .
            ". Your authorization ID is: " . $response_array[4],
        'headers' => "From: cth@mashpia.com\r\n"
    ];

    $send_mail = new MailClass();
    $send_mail->send_mail($mail_parms);

    try {
        $pdo = $MASHPIA_DB;
        $stmt = $pdo->prepare("INSERT INTO payments (email, phone, amount, response, name, address, invoice) 
                              VALUES (:email, :phone, :amount, :response, :name, :address, :invoice)");

        $fullName = $first_name . ' ' . $last_name;
        $fullAddress = $address . ' ' . $city . ',' . $state . ' ' . $zip . ' ' . $country;

        $stmt->execute([
            ':email' => $email,
            ':phone' => $phone,
            ':amount' => $amount,
            ':response' => $response,
            ':name' => $fullName,
            ':address' => $fullAddress,
            ':invoice' => $invoice
        ]);
    } catch (PDOException $e) {
//        error_log("Payment Database Error: " . $e->getMessage());
        // Send admin notification
        $adminMail = new MailClass();
        $adminMail->send_mail([
            'to' => "naftolir@gmail.com",
            'subject' => "Payment Database Error",
            'message' => $e->getMessage(),
            'headers' => "From: cth@mashpia.com\r\n"
        ]);
    }

    $successMessage = "Thank you for your payment of $" . $formattedAmount . ". You should receive an email confirmation shortly.";
    header("Location: " . SITE_URL . "?msg=" . urlencode($successMessage));
    exit;
} else {
//    header("Location: " . SITE_URL . "?error=" . urlencode($response));
    echo "Error: " . $response;
    exit;
}
?>