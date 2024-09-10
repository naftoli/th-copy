<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$info = $_POST['info'];
$users = $_POST['list'];
$admin_id = encrypt_decrypt('decrypt', $info['admin']);
$year = GlobalSettings::getRegistrationYear();

$amount = (float)$info['amount'];
$card_num = $info['cc']['num'];
$exp_date = $info['cc']['exp'];
$cvv = $info['cc']['cvv'];
$first_name = $info['cc']['first'];
$last_name = $info['cc']['last'];
$zip = $info['zip'];
$address = "";
$state = "";

// figure out how many children are being charged
$num_children = intval($amount / 20);

// figure out which child(ren) is getting charged for hachayol
$getting_charged = 0;
$user_ids = [];
foreach ($users as $user) {
    if (intval($user['checked']) && !intval($user['paid'])) {
        $user_ids[] = $user['user_id'];
        $getting_charged++;
        if ($getting_charged == $num_children) {
            break;
        }
    }
}

$description = "";
// get serial numbers for description
$user_info = [];
$qry = "select user_id, user_serial, school_id from users where user_id in (" . implode(',', $user_ids) . ")";
$stmt = $MASHPIA_DB->query($qry);
$rows = $stmt->fetchAll();
if (empty($rows)) {
    echo json_encode([
        'success'   => false,
        'error'     => "No users selected."
    ]);
    exit;
}
foreach ($rows as $row) {
    $user_info[$row['user_id']] = $row['school_id'];
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

        $error_msg = "There was an error saving your info, however your credit card WAS CHARGED. Please contact us by sending an email to 'support@tzivoshashem.org'. Your transaction ID is: " . $response_array[6];

        $MASHPIA_DB->beginTransaction();
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

        if (! $res) {
            $MASHPIA_DB->rollBack();
            echo json_encode([
                'success'   => false,
                'error'     => $error_msg
            ]);
            exit;
        }

        $trans_id = $MASHPIA_DB->lastInsertId();

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
        // update users table
        $stmt2 = $MASHPIA_DB->prepare("update users set hachayol = 1 where user_id = :user");

        foreach ($users as $user) {
            if (!in_array($user['user_id'], $user_ids)) {
                continue;
            }
            $res = $stmt->execute([
                'trans_id'  => $trans_id,
                'user'      => $user['user_id'],
                'school'    => $user_info[$user['user_id']],
                'type'      => 'HACH',
                'year'      => $year
            ]);
//            $stmt->debugDumpParams();
            $res2 = $stmt2->execute([
                'user'  => $user['user_id']
            ]);
            if (!$res || !$res2) {
                $MASHPIA_DB->rollBack();
//                if (!$res) $details = $stmt->debugDumpParams();
//                if (!$res2) $details = $stmt2->debugDumpParams();
                echo json_encode([
                    'success'   => false,
                    'error'     => $error_msg,
                    'details'   => $stmt->errorCode() > 0 ? $stmt->errorInfo() : $stmt2->errorInfo()
                ]);
                exit;
            }
        }
        // commit transaction
        $MASHPIA_DB->commit();
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

function sendEmailConf($trans_id) {
    global $MASHPIA_DB, $admin_id, $amount;

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
    $msg = "Your payment of $" . $amount . " has been received. Your transaction ID for your records is: " . $trans_id;
    $msg .= "<br /><br />Sincerely,<br />Tzivos Hashem Headquarters<br /><br/>";
    $msg .= "<div>
&copy; Tzivos Hashem 2024<br />
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