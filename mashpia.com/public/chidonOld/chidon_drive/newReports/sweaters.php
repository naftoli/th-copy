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

$skus = [
    [21, 'sweater_mother', "xs"],
    [22, 'sweater_mother', "small"],
    [23, 'sweater_mother', "medium"],
    [24, 'sweater_mother', "large"],
    [25, 'sweater_mother', "xl"],
    [null, 'sweater_mother', ""],

    [15, 'sweater_father', "xs"],
    [38, 'sweater_father', "small"],
    [18, 'sweater_father', "medium"],
    [19, 'sweater_father', "large"],
    [20, 'sweater_father', "xl"],
    [null, 'sweater_father', ""],
    
    [26, 'sweater_bubby', "xs"],
    [27, 'sweater_bubby', "small"],
    [28, 'sweater_bubby', "medium"],
    [29, 'sweater_bubby', "large"],
    [30, 'sweater_bubby', "xl"],
    [null, 'sweater_bubby', ""],
    
    [31, 'sweater_zaidy', "xs"],
    [32, 'sweater_zaidy', "small"],
    [33, 'sweater_zaidy', "medium"],
    [34, 'sweater_zaidy', "large"],
    [35, 'sweater_zaidy', "xl"],
    [null, 'sweater_zaidy', ""],
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
foreach($skus as $sku) {
    $sweater_id = $sku[0];
    $field = $sku[1];
    $size = $sku[2];

    // summamry
    $summary_sql = "SELECT COUNT(*) AS purchased FROM th_chidon_parent_purchases
        WHERE $field = '$size'";
    $summary_result = mysql_query($summary_sql);
    $summary_row = mysql_fetch_assoc($summary_result);
    if ($sweater_id) {
        $sweater_row = $sweaters[$sweater_id];
    } else {
        $sweater_row = [
            'sweater_name' => $field,
            'size' => 'Unknown',
            'gender' => '',
            'quantity' => 0
        ];
    }
    $summary[] = array_merge($summary_row, $sweater_row);

    // purchases
    $purchases_sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last, {$field}_ship_addr as ship_addr
        FROM th_chidon_parent_purchases tcpp 
        join admins a using (admin_id)
        WHERE $field = '$size'
        order by a.last, a.first, a.admin_id";
    $purchases_result = mysql_query($purchases_sql);
    while($purchases_row = mysql_fetch_assoc($purchases_result)) {
        $purchase_key = $purchases_row['admin_id'].":".$field;
        if (array_key_exists($purchase_key, $purchase_keys)) {
            $duplicate_purchase_keys[$purchase_key] = true;
        }
        $purchase_keys[$purchase_key] = true;

        if ($sweater_id) {
            $sweater_row = $sweaters[$sweater_id];
        } else {
            $sweater_row = [
                'sweater_name' => $field,
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
        $purchases[] = array_merge($purchases_row, $sweater_row, $child_row, ['purchase_key' => $purchase_key]);
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
</head>
<body>
    <h1>Sweaters</h1>

    <h2>Inventory Summary</h1>
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

    <h1>Purchases</h1>
    <table>
        <tr>
            <th>Canceled</th>
            <th>Admin ID</th>
            <th>Child Name</th>
            <th>Sweater Name</th>
            <th>Size</th>
            <th>school</th>
            <th>authorize_desc</th>
            <th>authorize_trans_type</th>
            <th>Adrress</th>
        </tr>
        <? foreach ($purchases as $purchase) {
            $duplicate = array_key_exists($purchase['purchase_key'], $duplicate_purchase_keys);
            $missing_size = $purchase['size'] === "Unknown";
            $skipped_payment = $purchase['authorize_desc'] === "skipped credit card payment";
            $warn = $duplicate || $missing_size || $skipped_payment;
        ?>
            <tr>
                <td><input type="checkbox" name="cancel"/></td>
                <td class="<?= $duplicate ? " warning" : ""?>"> <?= $purchase['admin_id'] ?> </td>
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
</body>
</html>
