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

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], false);
$schools = $as->getSchools();

$msg = '';
require_once 'Discount.php';

if ( isset( $_POST['submit'] ) ) {
    $user_id = $_POST['user'];
    $amount = $_POST['amount'];
    $created_by = $_POST['created_by'];
    $reason = $_POST['reason'];
    $year = $_POST['year'];

    if ( !($user_id && $amount && $created_by && $reason && $year) ) {
        $msg .= "You must fill out all the fields in order to create a discount.<br />";
    }
    if ( !is_numeric( $amount ) ) {
        $msg .= "You can only enter numbers or decimals for the amount.<br />";
    }

    if ( empty( $msg ) ) {
        $discount = new StudentDiscount($year, $user_id, $amount, $reason, $created_by);
        $d = new DiscountManager($MASHPIA_DB);
        if ($d->createStudentDiscount($discount)) {
            $msg = "Your discount has been created.";
        } else {
            $msg = "There was an error creating your discount.";
        }
    } else {
        $msg .= "<br />";
    }
}
$d = new DiscountManager($MASHPIA_DB);
$discounts = $d->getAllStudentDiscounts();
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

<form action="create_user_discount.php" method="post">
    <p> Please fill in the following fields to generate a discount:</p>
    <table>
        <tr>
            <td>School:</td>
            <td>
                <select name="school" id="school">
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
            <td>Grade:</td>
            <td>
                <select name="grade" id="grade"></select>
            </td>
        </tr>
        <tr>
            <td>Student:</td>
            <td>
                <select name="user" id="user"></select>
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
        <th>Soldier</th>
        <th>Discount</th>
        <th>Created By</th>
        <th>Reason</th>
        <th>Date Created</th>
        <th>Date Used</th>
    </tr>
    <?php
    foreach ( $discounts as $discount ) {
        echo "<tr><td>" . $discount['year'] . "</td><td>" . ($discount['first'] . ' ' . $discount['last']) . "</td><td>" . $discount['amount'] .
            "</td><td>" . $discount['created_by'] . "</td><td>" . $discount['reason'] . "</td><td>" . $discount['created'] .
            "</td><td>" . $discount['used'] . "</td></tr>";
    }
    ?>
</table>
</body>
<script>
    $(function () {
        $("#school").change( function () {
            let id = $(this).val()
            $.post('../../api/core/platoons?action=small', { school_id: id }, function( grades ) {
                console.log(grades)
                let html = '<option value="0">Choose Grade</option>'
                for (let grade of grades.data) {
                    html += `<option value="${grade.class_id}">${grade.name}</option>`
                }
                $("#grade").empty()
                $("#grade").append(html)
            })
        })
        $("#grade").change( function () {
            let id = $(this).val()
            $.get('../../ajax/getUsers.php?id=' + id, function( result ) {
                let users = JSON.parse(result)
                let html = '<option value="0">Choose Student</option>'
                for (let user_id in users) {
                    html += `<option value="${user_id}">${users[user_id]}</option>`
                }
                $("#user").empty()
                $("#user").append(html)
            })
        })
    })
</script>
</html>