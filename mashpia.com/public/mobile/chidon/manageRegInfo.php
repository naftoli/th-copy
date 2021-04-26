<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$info = [];
$sql = "select * from th_chidon_zelda";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Manage Registration Info</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 5px;
                border: 1px solid darkcyan;
            }
        </style>
    </head>
    <body>
        <h1>Manage Registration Info</h1>
        <table>
            <tr>
                <th>Chidon ID</th>
                <th>Family ID</th>
                <th>Registration Fee</th>
                <th>Chidon Drive Total</th>
                <th>Chidon Drive Subsidy</th>
                <th>Coupon Code</th>
                <th>Coupon Reason</th>
                <th>Paid</th>
                <th>Balance</th>
            </tr>
            <?php
            foreach ($info as $row) {
                echo "<tr data-id='" . $row['th_chidon_id'] . "'>" .
                    "<td><input type='text' class='chidon_id' size='5' value='" . $row['th_chidon_id'] . "' />" .
                    "</td><td><input type='text' class='admin_id' size='5' value='" . $row['admin_id'] . "' />" .
                    "</td><td><input type='text' class='reg_fee' size='6' value='" . $row['reg_fee'] . "' />" .
                    "</td><td><input type='text' class='chidon_drive' size='7' value='" . $row['chidon_drive'] . "' />" .
                    "</td><td><input type='text' class='subsidy' size='6' value='" . $row['subsidy'] . "' />" .
                    "</td><td><input type='text' class='coupon' size='5' value='" . $row['coupon'] . "' />" .
                    "</td><td><textarea class='coupon_reason' cols='15' rows='2'>" . $row['coupon_reason'] . "</textarea>" .
                    "</td><td><input type='text' class='paid' size='6' value='". $row['paid'] . "' />" .
                    "</td><td><input type='text' class='balance' size='6' value='" . $row['balance'] . "' /></td></tr>";
            }
            ?>
        </table>
    </body>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script>
        function update(elem, field) {
            const id = $(elem).parent().parent().data('id')
            const value = $(elem).val()
            $.post('ajax/updateRegInfo.php', { chidon_id: id, field: field, val: value }, function (result) {
                if (parseInt(result) == 1) {
                    alert('Updated.')
                } else {
                    alert('Error updating.')
                }
            })
        }

        $(function () {
            $(".chidon_id").blur(function () {
                update(this, 'th_chidon_id')
            })
            $(".admin_id").blur(function () {
                update(this, 'admin_id')
            })
            $(".reg_fee").blur(function () {
                update(this, 'reg_fee')
            })
            $(".chidon_drive").blur(function () {
                update(this, 'chidon_drive')
            })
            $(".subsidy").blur(function () {
                update(this, 'subsidy')
            })
            $(".coupon").blur(function () {
                update(this, 'coupon')
            })
            $(".coupon_reason").blur(function () {
                update(this, 'coupon_reason')
            })
            $(".paid").blur(function () {
                update(this, 'paid')
            })
            $(".balance").blur(function () {
                update(this, 'balance')
            })
        })
    </script>
</html>
