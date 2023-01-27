<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$only_flagged = isset($_GET['only_flagged']) && $_GET['only_flagged'];

$skus = [
    [21, 'sweater_mother', "xs", "Proud Chidon Mother"],
    [22, 'sweater_mother', "small", "Proud Chidon Mother"],
    [23, 'sweater_mother', "medium", "Proud Chidon Mother"],
    [24, 'sweater_mother', "large", "Proud Chidon Mother"],
    [25, 'sweater_mother', "xl", "Proud Chidon Mother"],
    [null, 'sweater_mother', "", "Proud Chidon Mother"],

    [15, 'sweater_father', "xs", "Proud Chidon Father"],
    [38, 'sweater_father', "small", "Proud Chidon Father"],
    [18, 'sweater_father', "medium", "Proud Chidon Father"],
    [19, 'sweater_father', "large", "Proud Chidon Father"],
    [20, 'sweater_father', "xl", "Proud Chidon Father"],
    [null, 'sweater_father', "", "Proud Chidon Father"],
    
    [26, 'sweater_bubby', "xs", "Proud Chidon Bubby"],
    [27, 'sweater_bubby', "small", "Proud Chidon Bubby"],
    [28, 'sweater_bubby', "medium", "Proud Chidon Bubby"],
    [29, 'sweater_bubby', "large", "Proud Chidon Bubby"],
    [30, 'sweater_bubby', "xl", "Proud Chidon Bubby"],
    [null, 'sweater_bubby', "", "Proud Chidon Bubby"],
    
    [31, 'sweater_zaidy', "xs", "Proud Chidon Zaidy"],
    [32, 'sweater_zaidy', "small", "Proud Chidon Zaidy"],
    [33, 'sweater_zaidy', "medium", "Proud Chidon Zaidy"],
    [34, 'sweater_zaidy', "large", "Proud Chidon Zaidy"],
    [35, 'sweater_zaidy', "xl", "Proud Chidon Zaidy"],
    [null, 'sweater_zaidy', "", "Proud Chidon Zaidy"],
];
$sweaters = [];

$sweater_sql = "SELECT * FROM chidon_sweaters
WHERE sweater_id in (" . implode(",", array_filter(
    array_column($skus, 0),
    function ($var) { return ($var !== NULL); }
)) . ")";
$sweater_result = mysql_query($sweater_sql);
while($sweater_row = mysql_fetch_assoc($sweater_result)) {
    $sweaters[$sweater_row["sweater_id"]] = $sweater_row;
}

$summary = [];
$purchases = [];
$purchase_keys = [];
$duplicate_purchase_keys = [];
$flagged_admins = [];
foreach($skus as $sku) {
    $sweater_id = $sku[0];
    $person_relation_key = $sku[1];
    $size = $sku[2];
    $sweater_name = $sku[3];

    // summamry
    $summary_sql = "SELECT COUNT(*) AS purchased FROM th_chidon_parent_purchases
        WHERE $person_relation_key = '$size'";
    $summary_result = mysql_query($summary_sql);
    $summary_row = mysql_fetch_assoc($summary_result);
    if ($sweater_id) {
        $sweater_row = $sweaters[$sweater_id];
    } else {
        $sweater_row = [
            'sweater_name' => $sweater_name,
            'size' => 'Unknown',
            'gender' => '',
            'quantity' => 0
        ];
    }
    $summary[] = array_merge($sweater_row, $summary_row);

    // purchases
    $purchases_sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
        a.admin_phone_mobile AS father_cell, a.admin_phone_mobile2 as mother_cell,
        {$person_relation_key}_ship_addr as ship_addr
        FROM th_chidon_parent_purchases tcpp 
        join admins a using (admin_id)
        WHERE $person_relation_key = '$size'
        order by a.last, a.first, a.admin_id";
    $purchases_result = mysql_query($purchases_sql);
    while($purchases_row = mysql_fetch_assoc($purchases_result)) {
        $purchase_key = $purchases_row['admin_id'].":".$person_relation_key;
        if (array_key_exists($purchase_key, $purchase_keys)) {
            $duplicate_purchase_keys[$purchase_key] = true;
            $flagged_admins[$purchases_row['admin_id']] = true;
        }
        $purchase_keys[$purchase_key] = true;

        if ($sweater_id) {
            $sweater_row = $sweaters[$sweater_id];
        } else {
            $sweater_row = [
                'sweater_name' => $sweater_name,
                'size' => 'Unknown',
            ];
        }
        // get oldest child registered for the chidon
        $child_sql = "SELECT s.school_name, u.first as user_first, u.last as user_last from users u
            join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
            join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
                c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
            ))
            left join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
            where aa.admin_id = {$purchases_row['admin_id']}
            and c.th_chidon_id is not null
            order by u.dob asc
            limit 1
        ";
        $child_result = mysql_query($child_sql);
        $child_row = mysql_fetch_assoc($child_result);
        $purchases[] = array_merge($purchases_row, $sweater_row, $child_row, ['purchase_key' => $purchase_key, 'person_relation_key' => $person_relation_key]);
    }
}

function array_sort_by_props(&$array, $props) {
    for($i = 0; $i < count($props); $i++) {
        if (is_array($props[$i])) {
            $props[$i][1] = !($props[$i][1] === "desc" || $props[$i][1] === -1);
        } else {
            $props[$i] = [$props[$i], false];
        }
    }
    usort($array, function($a, $b) use ($props) {
        foreach($props as $prop) {
            $name = $prop[0];
            $reversed = $prop[1];
            $diff = $reversed ? ($b[$name] <=> $a[$name]) : ($a[$name] <=> $b[$name]);
            if ($diff !== 0 ) {
                return $diff;
            }
        }
        return 0;
    });
}

array_sort_by_props($purchases, ['admin_last', 'admin_first', 'sweater_name']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Sweaters</title>
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
    <h1>Sweaters</h1>

    <? if(!$only_flagged) { ?>
        <h1>Inventory Summary</h1>
        <table>
            <tr>
                <th>Sweater Name</th>
                <th>Size</th>
                <th>Gender</th>
                <th>Quantity</th>
                <th>Purchased</th>
                <th>Amount Left</th>
            </tr>
            <? foreach ($summary as $sweater) { ?>
                <tr>
                    <td> <?= $sweater['sweater_name'] ?> </td>
                    <td> <?= $sweater['size'] ?> </td>
                    <td> <?= $sweater['gender'] ?> </td>
                    <td> <?= $sweater['quantity'] ?> </td>
                    <td> <?= $sweater['purchased'] ?> </td>
                    <td> <?= ($sweater['quantity'] - $sweater['purchased']) ?> </td>
                </tr>
            <? } ?>
        </table>
        <br />
        <br />
    <? } ?>

    <h1><?= $only_flagged  ? "Flagged " : ""?>Purchases</h1>
    <table>
        <tr>
            <th>Cancel</th>
            <th>Admin ID</th>
            <? if ($only_flagged) { ?>
                <th>Fathers cell</th>
                <th>Mothers cell</th>
            <? } ?>
            <th>Parent</th>
            <th>Child</th>
            <th>Sweater Name</th>
            <th>Size</th>
            <th>School</th>
            <th>authorize_desc</th>
            <th>authorize_trans_type</th>
            <th>Address</th>
        </tr>
        <? foreach ($purchases as $purchase) {
            $duplicate = array_key_exists($purchase['purchase_key'], $duplicate_purchase_keys);
            $missing_size = $purchase['size'] === "Unknown";
            $skipped_payment = $purchase['authorize_desc'] === "skipped credit card payment";
            $flagged_admin = isset($flagged_admins[$purchase['admin_id']]) && $flagged_admins[$purchase['admin_id']];
            $flagged = $flagged_admin || $duplicate || $missing_size || $skipped_payment;
            if ($only_flagged && !$flagged) {
                continue;
            };
        ?>
            <tr>
                <td>
                    <form class="cancel-sweater-form" method="post" action="./cancel_chidon_parent_purchase_item.php">
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="th_chidon_parent_purchase_id" value="<?= $purchase['th_chidon_parent_purchase_id'] ?>"/>
                        <input type="hidden" name="person_relation_key" value="<?= $purchase['person_relation_key'] ?>"/>
                        <input type="submit" value="Cancel"/>
                    </form>
                </td>
                <td class="<?= $duplicate ? " warning" : ""?>"> <?= $purchase['admin_id'] ?> </td>
                <? if ($only_flagged) { ?>
                    <td> <?= $purchase['father_cell'] ?> </td>
                    <td> <?= $purchase['mother_cell'] ?> </td>
                <? } ?>
                <td class="<?= $duplicate ? " warning" : ""?>"> <?= $purchase['admin_first'] ?> <?= $purchase['admin_last'] ?> </td>
                <td class="<?= $duplicate ? " warning" : ""?>"> <?= $purchase['user_first'] ?> <?= $purchase['user_last'] ?> </td>
                <td class="<?= $duplicate ? " warning" : ""?>"> <?= $purchase['sweater_name'] ?> </td>
                <td class="<?= $missing_size ? " warning" : ""?>"> <?= $purchase['size'] ?> </td>
                <td> <?= $purchase['ship_addr'] || !$purchase['school_name'] ? "No" : $purchase['school_name'] ?> </td>
                <td class="<?= $skipped_payment ? " warning" : ""?>"> <?= $purchase['authorize_desc'] ?> </td>
                <td> <?= $purchase['authorize_trans_type'] ?> </td>
                <td> <?= $purchase['ship_addr'] ?> </td>
            </tr>
        <? } ?>
    </table>
    <script>
        $(document).ready(function () {
            $(".cancel-sweater-form").submit(function (event) {
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
