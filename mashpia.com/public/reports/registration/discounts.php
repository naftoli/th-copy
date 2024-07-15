<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$regYear = GlobalSettings::getRegistrationYear();

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, false);
$schools = $as->getSchools();

$msg = '';
require_once 'Discount.php';

if ( isset( $_POST['submit'] ) ) {
    $school_id = $_POST['school'];
    $amount = $_POST['amount'];
    $created_by = $_POST['created_by'];
    $reason = $_POST['reason'];
    $year = $_POST['year'];

    if ( !($school_id && $amount && $created_by && $reason && $year) ) {
        $msg .= "You must fill out all the fields in order to create a discount.<br />";
    }
    if ( !is_numeric( $amount ) ) {
        $msg .= "You can only enter numbers or decimals for the amount.<br />";
    }

    if ( empty( $msg ) ) {
        $discount = new Discount($year, $school_id, $amount, $reason, $created_by);
        $d = new DiscountManager($MASHPIA_DB);
        if ($d->createDiscount($discount)) {
            $msg = "Your discount has been created.";
        } else {
            $msg = "There was an error creating your discount.";
        }
    } else {
        $msg .= "<br />";
    }
}
$d = new DiscountManager($MASHPIA_DB);
$discounts = $d->getAllDiscounts();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Manage Discounts</title>
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
<h1>Discounts</h1>

<div style="color: red;">
    <?= empty($msg) ? '' : $msg; ?>
</div>

<form action="discounts.php" method="post">
    <p> Please fill in the following fields to generate a discount:</p>
    <table>
        <tr>
            <td>School:</td>
            <td>
                <select name="school">
                    <option value="0">Choose School</option>
                    <?php
                    foreach ($schools as $id => $school) {
                        echo "<option value='$id'>$school</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Discount Amount:</td>
            <td><input type="text" name="amount" size="6" /></td>
        </tr>
        <tr>
            <td>Created By:</td>
            <td><input type="text" name="created_by" /></td>
        </tr>
        <tr>
            <td>Reason for Discount:</td>
            <td><input type="text" name="reason" /></td>
        </tr>
        <tr>
            <td>For Registration Year:</td>
            <td>
                <select name="year">
                <?php
                for ($y = $regYear; $y < ($regYear + 5); $y++) {
                    echo "<option value='$y'>$y</option>";
                }
                ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center"><input type="submit" name="submit" value="create discount" /></td>
        </tr>
    </table>
</form>
<h2></h2>
<?php
if ( empty( $discounts ) ) {
    echo "No discounts in system.";
    exit;
}
?>
<table>
    <caption>Chayolei Registration Discounts</caption>
    <tr>
        <th>Year</th>
        <th>School</th>
        <th>Discount</th>
        <th>Created By</th>
        <th>Reason</th>
        <th>Date Created</th>
        <th>Date Used</th>
    </tr>
    <?php
    foreach ( $discounts as $discount ) {
        echo "<tr><td>" . $discount['year'] . "</td><td>" . $schools[$discount['school_id']] . "</td><td>" . $discount['amount'] .
            "</td><td>" . $discount['created_by'] . "</td><td>" . $discount['reason'] . "</td><td>" . $discount['created'] .
            "</td><td>" . $discount['used'] . "</td></tr>";
    }
    ?>
</table>
</body>
</html>