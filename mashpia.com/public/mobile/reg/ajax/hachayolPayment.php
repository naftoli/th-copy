<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

function sendEmailConf($trans_id) {
    global $MASHPIA_DB, $admin_id, $amount, $first, $last;

    // get email address
    $stmt = $MASHPIA_DB->prepare("
        SELECT admin_email FROM admins WHERE admin_id = :id
    ");
    $stmt->execute([
        'id'    => $admin_id
    ]);
    $row = $stmt->fetch();
    $email = $row['admin_email'];

    $subject = "Hachayol Subscription";
    $msg = "Dear $first $last, your payment of $" . $amount . " has been received. Your transaction ID for your records is: " . $trans_id;
    $msg .= "<br />Your Hachayol subscription has been updated.<br /><br />Sincerely,<br />Tzivos Hashem Headquarters<br /><br/>";
    $msg .= "<div>
&copy; Tzivos Hashem 2020<br />
792 Eastern Pkwy<br />
Brooklyn, NY 11213
<a href='privacy.html'>Privacy Policy</a><br />
Click <a href='unsubscribe.html'>here</a> to unsubscribe
</div>";

    // To send HTML mail, the Content-type header must be set
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';

    // Additional headers
    $headers[] = 'From: Tzivos Hashem <accounting@tzivoshashem.org>';

    // Mail it
    @mail($email, $subject, $msg, implode("\r\n", $headers));
}

$admin_auth = ['user'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$info = $_POST['info'];
$user_ids = $_POST['list'];
$admin_id = encrypt_decrypt('decrypt', $info['admin']);
$year = GlobalSettings::getCurrentYear();

$amount = (float)$info['amount'];
$card_num = $info['cc']['num'];
$exp_date = $info['cc']['exp'];
$cvv = $info['cc']['cvv'];
$first_name = $info['cc']['first'];
$last_name = $info['cc']['last'];
$zip = $info['zip'];
$address = "";
$state = "";

$users = [];
$description = "";
// get serial numbers for description
$qry = "select user_id, user_serial, school_id from users where user_id in (" . implode(',', $user_ids) . ")";
$stmt = $MASHPIA_DB->query($qry);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $users[$row['user_id']] = $row['school_id'];
    $description .= "C" . $row['user_serial'] . ":HACH-20,";
}
// remove trailing comma
$description = substr($description, 0, strlen($description) - 1);

if ( $amount > 0 ) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/authorize.php';
    $trans_id = 0;

    if ($response_array[0] == 1) { // success
        $strResponse =  $response_array[3] . ':' .
        $response_array[4] . ':' .
        $response_array[6] . ':' .
        $response_array[9];

        // save to transactions table
        $stmt = $MASHPIA_DB->prepare("
                INSERT INTO transactions 
                SET trans_date = now(), 
                description = :description, 
                amount = :amount, 
                response = :response, 
                zip = :zip, 
                admin_id = :admin
        ");

        $res = $stmt->execute([
            'description'   => $description,
            'amount'        => $amount,
            'response'      => $strResponse,
            'zip'           => $zip,
            'admin'         => $admin_id
        ]);

        if ($res) $trans_id = $MASHPIA_DB->lastInsertId();
//        else $stmt->debugDumpParams();

        // save to registration charges table
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO registration_charges 
            SET trans_id = :trans_id, 
            user_id = :user, 
            school_id = :school, 
            type = :type, 
            amount = 20, 
            year = :year, 
            discount = 0
        ");
        foreach ($users as $user_id => $school_id) {
            $stmt->execute([
                'trans_id'  => $trans_id,
                'user'      => $user_id,
                'school'    => $school_id,
                'type'      => 'HACH',
                'year'      => $year
            ]);
        }

        // send email confirmation
        sendEmailConf($response_array[6]);

        echo json_encode([
            'success'   => true
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $response_array[3]
        ]);
    }
} else {
    echo json_encode([
        'success'   => false,
        'error'     => "You have not selected anything to purchase."
    ]);
}
