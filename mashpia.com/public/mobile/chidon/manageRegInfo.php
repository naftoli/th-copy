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
                <th>Action</th>
            </tr>
            <?php
            foreach ($info as $row) {
                echo "<tr data-id='" . $row['th_chidon_id'] . "'>" .
                    "<td>" . $row['th_chidon_id'] .
                    "</td><td><input type='text' class='admin_id' size='5' value='" . $row['admin_id'] . "' />" .
                    "</td><td><input type='text' class='reg_fee' size='6' value='" . $row['reg_fee'] . "' />" .
                    "</td><td><input type='text' class='chidon_drive' size='7' value='" . $row['chidon_drive'] . "' />" .
                    "</td><td><input type='text' class='subsidy' size='6' value='" . $row['subsidy'] . "' />" .
                    "</td><td><input type='text' class='coupon' size='5' value='" . $row['coupon'] . "' />" .
                    "</td><td><textarea class='coupon_reason' cols='15' rows='2'>" . $row['coupon_reason'] . "</textarea>" .
                    "</td><td><input type='text' class='paid' size='6' value='". $row['paid'] . "' />" .
                    "</td><td><input type='text' class='balance' size='6' value='" . $row['balance'] . "' />" .
                    "</td><td><button class='save'>Save</button></td></tr>";
            }
            ?>
        </table>
    </body>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script>
        $(".save").click( function () {
            const elem = $(this).parent().parent()
            const id = $(elem).data('id')
            const data = {}
            const fields = ['admin_id', 'reg_fee', 'chidon_drive', 'subsidy', 'coupon', 'coupon_reason', 'paid', 'balance']
            for (let field of fields) {
                let str = "." + field
                data[field] = $(elem).find(str).val()
            }
            console.log(data)
            $.post('ajax/updateRegInfo.php', { chidon_id: id, updates: data }, function (result) {
                if (parseInt(result) == 1) {
                    alert('Updated.')
                } else {
                    alert('Error updating.')
                }
            })
        })
    </script>
</html>
