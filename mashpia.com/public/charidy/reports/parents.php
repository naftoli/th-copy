<?php
/*
 * Can you export all info that w have in the database with this information, including if possible old accounts?

    Parent account ID
    First and last names of all parents
    All phone numbers
    email addresses
    home addresses

This will be used to compare to donor accounts with missing contact data.

Thanks!
 */
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$admins = [];
$stmt = $MASHPIA_DB->query("
    SELECT * FROM admins
");
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Charidy Report</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>Admin ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Email Address</th>
                <th>Phone Numbers</th>
            </tr>
            <?php
            foreach ($admins as $admin) {
                $name = $admin['first'] . ' ' . $admin['last'];
                $address = $admin['admin_address1'] . ' ' . $admin['admin_address2'] . "<br />" . $admin['admin_city'] .
                    ', ' . $admin['admin_state'] . ' ' . $admin['admin_postal'] . "<br />" . $admin['admin_country'];
                $numbers = ($admin['admin_phone_work'] ? $admin['admin_phone_work'] . "<br /" : '') .
                    ($admin['admin_phone_home'] ? $admin['admin_phone_home'] . "<br />" : '') .
                    ($admin['admin_phone_mobile'] ? $admin['admin_phone_mobile'] . "<br />" : '') .
                    ($admin['admin_phone_mobile2'] ?: '');
                echo "<tr><td>" . $admin['admin_id'] . "</td><td>" . $name . "</td><td>" . $address . "</td><td>" .
                    $admin['admin_email'] . "</td><td>". $numbers . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>