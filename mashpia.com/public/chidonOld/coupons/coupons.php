<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$msg = '';
require_once 'class.couponCode.php';
if ( isset( $_POST['submit'] ) ) {
    $length = $_POST['characters'];
    $value = $_POST['value'];
    $created_by = $_POST['created_by'];
    $reason = $_POST['reason'];

    if ( !($length && $value && $created_by && $reason) ) {
        $msg .= "You must fill out all the fields in order to generate a coupon code.<br />";
    }
    if ( !is_numeric( $value ) ) {
        $msg .= "You can only enter numbers or decimals for the dollar value.<br />";
    }
    
    if ( empty( $msg ) ) {
        $c = new CouponCode( $MASHPIA_DB, $year );
        $code = $c->getCouponCode( $length );
        if ( $code ) {
            if ( $c->saveCode( $value, $created_by, $reason ) ) {
                $msg .= "The following code has been generated and saved: " . $code . "<br />It can now be given out <strong>for one-time use.</strong><br /><br />";
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

        <form action="coupons.php" method="post">
            <p> Please fill in the following fields to generate a one-time-use coupon code:</p>
            <table>
                <tr>
                    <td>Code Length:</td>
                    <td><input type="text" name="characters" size="2" /></td>
                </tr>
                <tr>
                    <td>Dollar Value:</td>
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
                <th>Code</th>
                <th>Dollar Value</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Reason</th>
                <th>Year</th>
            </tr>
            <?php
            foreach ( $codes as $code ) {
                echo "<tr><td>" . $code['code'] . "</td><td>" . $code['value'] . "</td><td>";
                if ( intval( $code['used'] ) ) echo "Used";
                else echo "Not yet used";
                echo "</td><td>" . $code['created_by'] . "</td><td>" . $code['reason'] . "</td><td>" . $code['year'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>