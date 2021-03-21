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

$products = [
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

$info = [];
foreach($products as $product) {
    $sweater_id = $product[0];
    $field = $product[1];
    $size = $product[2];

    $purchaes_sql = "SELECT COUNT(*) AS purchased FROM th_chidon_parent_purchases
        WHERE $field = '$size'";
    $purchaes_result = mysql_query($purchaes_sql);
    $purchaes_row = mysql_fetch_assoc($purchaes_result);
    if ($sweater_id) {
        $sweater_sql = "SELECT * FROM chidon_sweaters
            WHERE sweater_id = $sweater_id";
        $sweater_result = mysql_query($sweater_sql);
        $sweater_row = mysql_fetch_assoc($sweater_result);
    } else {
        $sweater_row = [
            'sweater_name' => $field,
            'size' => 'Unknown',
            'gender' => '',
            'quantity' => 0
        ];
    }
    $info[] = array_merge($purchaes_row, $sweater_row);
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
        }
    </style>
</head>
<body>
    <h1>Sweaters inventory Report</h1>
    <table>
        <tr>
            <th>Sweater Name</th>
            <th>Size</th>
            <th>Gender</th>
            <th>Quantity</th>
            <th>Purchased</th>
            <th>Amount Left</th>
        </tr>
        <? foreach ($info as $sweater) { ?>
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
</body>
</html>
