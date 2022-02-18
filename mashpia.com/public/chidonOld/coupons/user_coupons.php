<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ( $admin_user['auth'] != 'super' && !in_array($admin_user['auths']['school'][0], [61, 269]) ) {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$msg = '';
require_once 'class.couponCode.php';
if ( isset( $_POST['submit'] ) ) {
    $user_serial = $_POST['user'];
    $value = $_POST['value'];
    $created_by = $_POST['created_by'];
    $reason = $_POST['reason'];

    if ( !($user_serial && $value && $created_by && $reason) ) {
        $msg .= "You must fill out all the fields in order to generate a coupon code.<br />";
    }
    if ( !is_numeric( $value ) ) {
        $msg .= "You can only enter numbers or decimals for the coupon amount.<br />";
    }

    if ( empty( $msg ) ) {
        $c = new CouponCode( $MASHPIA_DB, $year );
        $code = $c->getCouponCode( 5 );
        if ( $code ) {
            $saved = $c->saveUserCode( $value, $created_by, $reason, $user_serial );
            if ( $saved == 1 ) {
                $msg .= "The following code has been generated and saved: " . $code . "<br />
                        It will be used during Chidon Experiance Enrollment.</strong><br /><br />";
            } else if ($saved == 0) {
                $msg .= "There was an error saving your coupon.";
            } else if ($saved == -1) {
                $msg .= "We couldn't find any users with that serial number.";
            }
        }
    }
}
$codes = CouponCode::getlistOfCodes( $MASHPIA_DB, $year );
?>
    <!DOCTYPE html>
    <html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Manage Coupon Codes</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 14px;
            padding: 6px;
        }
        body {
            line-height: 1.2;
        }
    </style>
</head>
<body>
<?php require('../../admin_header.php'); ?>
<h1>Coupon Codes</h1>

<div style="color: red;">
    <?= empty($msg) ? '' : $msg; ?>
</div>

<form action="user_coupons.php" method="post">
    <p> Please fill in the following fields to generate a one-time-use coupon code:</p>
    <table>
        <tr>
            <td>User Serial:</td>
            <td><input type="text" name="user" size="6" /></td>
        </tr>
        <tr>
            <td>Coupon Amount:</td>
            <td><input type="text" name="value" size="6" /></td>
        </tr>
        <tr>
            <td>Created By:</td>
            <td><input type="text" name="created_by" /></td>
        </tr>
        <tr>
            <td>Reason for Creation:</td>
            <td><input type="text" name="reason" /></td>
        </tr>
        <tr>
            <td colspan="2" align="center"><input type="submit" name="submit" value="generate code" /></td>
        </tr>
    </table>
</form>
<h2></h2>
<?php
if ( empty( $codes ) ) {
    echo "No codes in system.";
    exit;
}
?>
<table>
    <caption>Current Chidon Codes in System</caption>
    <tr>
        <th>User Serial</th>
        <th>Coupon Amount</th>
        <th>Status</th>
        <th>Created By</th>
        <th>Reason</th>
        <th>Date Created</th>
        <th>Date Redeemed</th>
    </tr>
    <?php
    foreach ( $codes as $code ) {
        echo "<tr><td>" . $code['serial_num'] . "</td><td>" . $code['value'] . "</td><td>";
        if ( intval( $code['used'] ) ) echo "Redeemed";
        else echo "Not yet used";
        echo "</td><td>" . $code['created_by'] . "</td><td>" . $code['reason'] . "</td><td>" . $code['date_created'] . "</td><td>" .
            $code['date_redeemed'] . "</td></tr>";
    }
    ?>
</table>
</body>
</html>
