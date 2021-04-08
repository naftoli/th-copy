<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$only_flagged = isset($_GET['only_flagged']) && $_GET['only_flagged'];

$boxes = [];

$summary = [];
$purchases = [];
$purchase_keys = [];
$flagged_admins = [];

// summamry
$total_sql = "SELECT sum(celeb_box_add) AS total FROM th_chidon_parent_purchases";
$total_result = mysql_query($total_sql);
$total_row = mysql_fetch_assoc($total_result);
$total = $total_row['total'];

// purchases
$sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
    a.admin_phone_mobile AS father_cell, a.admin_phone_mobile2 as mother_cell, celeb_box_add_addr as ship_addr
    FROM th_chidon_parent_purchases tcpp 
    join admins a using (admin_id)
    WHERE celeb_box_add > 0
    order by a.last, a.first, a.admin_id limit 300";
$result = mysql_query($sql);
while($row = mysql_fetch_assoc($result)) {
    $admin_id = $row['admin_id'];
    if (array_key_exists($admin_id, $purchase_keys)) {
        $flagged_admins[$admin_id] = true;
    }
    $purchase_keys[$admin_id] = true;

    // get oldest child registered for the chidon
    $child_sql = "SELECT s.school_name, u.first as user_first, u.last as user_last from users u
        join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
        join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
            c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
        ))
        left join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
        where aa.admin_id = $admin_id
        and c.th_chidon_id is not null
        order by u.dob asc
        limit 1
    ";
    $child_result = mysql_query($child_sql);
    $child_row = mysql_fetch_assoc($child_result);
    $purchases[] = array_merge($row, $child_row);
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Sweaters inventory Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
        .warning {
            background-color: yellow;
        }
    </style>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
</head>
<body>
    <h1>Extra Celebration Boxes</h1>
    <h3>Total boxes: <?= $total ?>
    <table>
        <tr>
            <th>Cancel</th>
            <th>Admin ID</th>
            <?// if ($only_flagged) { ?>
                <th>Fathers cell</th>
                <th>Mothers cell</th>
            <? //} ?>
            <th>Parent</th>
            <th>Child</th>
            <th>Amount</th>
            <th>school</th>
            <th>authorize_desc</th>
            <th>authorize_trans_type</th>
            <th>Adrress</th>
        </tr>
        <? foreach ($purchases as $purchase) {
            $skipped_payment = $purchase['authorize_desc'] === "skipped credit card payment";
            $flagged_admin = isset($flagged_admins[$purchase['admin_id']]) && $flagged_admins[$purchase['admin_id']];
            $flagged = $flagged_admin || $skipped_payment;
            if ($only_flagged && !$flagged) {
                continue;
            };
        ?>
            <tr>
                <td>
                    <form class="cancel-celeb-box-form" method="post" action="./cancel_chidon_parent_purchase_item.php">
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="th_chidon_parent_purchase_id" value="<?= $purchase['th_chidon_parent_purchase_id'] ?>"/>
                        <input type="hidden" name="person_relation_key" value="celeb_box_add"/>
                        <input type="submit" value="Cancel"/>
                    </form>
                </td>
                <td class="<?= $flagged_admin ? " warning" : ""?>"> <?= $purchase['admin_id'] ?> </td>
                <?// if ($only_flagged) { ?>
                    <td> <?= $purchase['father_cell'] ?> </td>
                    <td> <?= $purchase['mother_cell'] ?> </td>
                <?// } ?>
                <td class="<?= $flagged_admin ? " warning" : ""?>"> <?= $purchase['admin_first'] ?> <?= $purchase['admin_last'] ?> </td>
                <td class="<?= $flagged_admin ? " warning" : ""?>"> <?= $purchase['user_first'] ?> <?= $purchase['user_last'] ?> </td>
                <td class="<?= $flagged_admin ? " warning" : ""?>"> <?= $purchase['celeb_box_add'] ?></td>
                <td> <?= $purchase['ship_addr'] || !$purchase['school_name'] ? "No" : $purchase['school_name'] ?> </td>
                <td class="<?= $skipped_payment ? " warning" : ""?>"> <?= $purchase['authorize_desc'] ?> </td>
                <td> <?= $purchase['authorize_trans_type'] ?> </td>
                <td> <?= $purchase['ship_addr'] ?> </td>
            </tr>
        <? } ?>
    </table>
    <script>
        $(document).ready(function () {
            $(".cancel-celeb-box-form").submit(function (event) {
                event.preventDefault();
                var form = $(this)
                form.find(':submit').attr("disabled","disabled")
                $.ajax({
                    type: "POST",
                    url: form.attr("action"),
                    data: form.serialize(),
                    encode: true,
                }).done(function (data) {
                    if (data === "1") {
                        form.replaceWith("Canceled");
                    } else if (data === "0") {
                        form.find(':submit').removeAttr("disabled")
                    } 
                });

            });
        });
    </script>
</body>
</html>
