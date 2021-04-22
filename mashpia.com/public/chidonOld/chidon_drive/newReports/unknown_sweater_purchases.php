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

// function get_celebration_box_purchases() {
//     global $year;
//     // purchases
//     $sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
//         a.admin_phone_mobile AS father_cell, a.admin_phone_mobile2 as mother_cell,
//         celeb_box_add_ship as ship,
//         celeb_box_add_addr as ship_addr
//         FROM th_chidon_parent_purchases tcpp 
//         join admins a using (admin_id)
//         WHERE celeb_box_add > 0
//         order by a.last, a.first, a.admin_id";
//     $result = mysql_query($sql);
//     $purchases = [];
//     while($row = mysql_fetch_assoc($result)) {
//         $admin_id = $row['admin_id'];
//         // get oldest child registered for the chidon
//         $child_sql = "SELECT s.school_id, s.school_name, u.first as user_first, u.last as user_last from users u
//             join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
//             join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
//                 c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
//             ))
//             left join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
//             where aa.admin_id = $admin_id
//             and c.th_chidon_id is not null
//             order by u.dob asc
//             limit 1
//         ";
//         $child_result = mysql_query($child_sql);
//         $child_row = mysql_fetch_assoc($child_result);
//         $purchases[] = array_merge($row, $child_row, [
//             'type' => 'celebration_box',
//             'purchase_key' => $admin_id . ':celebration_box'
//         ]);
//     }
//     return $purchases;
// }

function get_sweater_purchases() {
    global $year;
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
    
    $purchases = [];
    foreach($skus as $sku) {
        $sweater_id = $sku[0];
        $person_relation_key = $sku[1];
        $size = $sku[2];
        $sweater_name = $sku[3];

        $purchases_sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
            a.admin_phone_mobile AS father_cell, a.admin_phone_mobile2 as mother_cell,
            {$person_relation_key}_ship as ship,
            {$person_relation_key}_ship_addr as ship_addr
            FROM th_chidon_parent_purchases tcpp 
            join admins a using (admin_id)
            WHERE $person_relation_key = '$size'
            order by a.last, a.first, a.admin_id";
        $purchases_result = mysql_query($purchases_sql);
        while($purchases_row = mysql_fetch_assoc($purchases_result)) {
            $admin_id = $purchases_row['admin_id'];
            $purchase_key = $admin_id.":".$person_relation_key;

            if ($sweater_id) {
                $sweater_row = $sweaters[$sweater_id];
            } else {
                $sweater_row = [
                    'sweater_name' => $sweater_name,
                    'size' => 'Unknown',
                ];
            }
            // get oldest child registered for the chidon
            $child_sql = "SELECT s.school_id, s.school_name, u.first as user_first, u.last as user_last from users u
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
            $purchases[] = array_merge($purchases_row, $sweater_row, $child_row, [
                'type' => 'sweater',
                'purchase_key' => $purchase_key,
                'person_relation_key' => $person_relation_key
            ]);
        }
    }
    return $purchases;
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

// load and sort all purchases
$purchases = get_sweater_purchases();

array_sort_by_props($purchases, ['admin_last', 'admin_first', 'purchase_key']);

// flag iregular purchases
$admin_ids = [];  // pre-sorted
$flagged_admin_ids = [];
$prev_purchase_keys = [];
$duplicate_purchase_keys = [];
foreach ($purchases as $i => $purchase) {
    if (end($admin_ids) !== $purchase['admin_id']) {
        $admin_ids[] = $purchase['admin_id'];
    }
    
    // // if duplicate
    // if (array_key_exists($purchase['purchase_key'], $prev_purchase_keys)) {
    //     $flagged_admin_ids[$purchase['admin_id']] = true;
    //     $duplicate_purchase_keys[$purchase['purchase_key']] = true;
    // }
    // $prev_purchase_keys[$purchase['purchase_key']] = true;
    
    // if missing sweater size
    if ($purchase['type'] === 'sweater' && $purchase['size'] === 'Unknown') {
        $flagged_admin_ids[$purchase['admin_id']] = true;
        $purchases[$i]['missing_size'] = true;
    } else {
        $purchases[$i]['missing_size'] = false;
    }

    // // if missing payment
    // if ($purchase['authorize_desc'] === "skipped credit card payment") {
    //     $flagged_admin_ids[$purchase['admin_id']] = true;
    //     $purchases[$i]['skipped_payment'] = true;
    // } else {
    //     $purchases[$i]['skipped_payment'] = false;
    // }

    // // if missing shipping info
    // if ( $purchase['ship'] ? empty($purchase['ship_addr']) : !$purchase['school_id']) {
    //     $flagged_admin_ids[$purchase['admin_id']] = true;
    //     $purchases[$i]['shipping_issue'] = true;
    // } else {
    //     $purchases[$i]['shipping_issue'] = false;
    // }
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Unknown Sweaters Sizes</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
        }
        .warning {
            background-color: yellow;
        }
        .background-grey {
            background-color: #ddd;
        }
        .background-grey > td {
            border-right-color: #ddd;
        }
    </style>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
</head>
<body>
    <h1>Unknown Sweaters Sizes</h1>
    <table style="border-collapse: collapse">
        <tr>
            <th>Cancel</th>
            <th>Admin ID</th>
            <th>Fathers cell</th>
            <th>Mothers cell</th>
            <th>Parent</th>
            <th>Child</th>
            <th>Name</th>
            <th>Size</th>
            <th>School</th>
            <th>Amount</th>
            <th>authorize_desc</th>
            <th>authorize_trans_type</th>
            <th>Address</th>
        </tr>
        <?
        $prev_admin_id = "";
        $color_background = false;
        foreach($purchases as $purchase) {
            if (!array_key_exists($purchase['admin_id'], $flagged_admin_ids)) {
                continue;
            }
            if ($purchase['admin_id'] !== $prev_admin_id) {
                $color_background = !$color_background;
            }
            $prev_admin_id = $purchase['admin_id'];
            
            $duplicate = array_key_exists($purchase['purchase_key'], $duplicate_purchase_keys);
            if ($purchase['type'] === 'sweater') { ?>
                <tr class="<?= $color_background ? " background-grey" : "" ?>">
                    <td>
                        <form class="cancel-chidon-parent-purchase-item" method="post" action="./cancel_chidon_parent_purchase_item.php">
                            <input type="hidden" name="action" value="delete"/>
                            <input type="hidden" name="th_chidon_parent_purchase_id" value="<?= $purchase['th_chidon_parent_purchase_id'] ?>"/>
                            <input type="hidden" name="person_relation_key" value="<?= $purchase['person_relation_key'] ?>"/>
                            <input type="submit" value="Cancel"/>
                        </form>
                    </td>
                    <td> <?= $purchase['admin_id'] ?> </td>
                    <td> <?= $purchase['father_cell'] ?> </td>
                    <td> <?= $purchase['mother_cell'] ?> </td>
                    <td> <?= $purchase['admin_first'] ?> <?= $purchase['admin_last'] ?> </td>
                    <td> <?= $purchase['user_first'] ?> <?= $purchase['user_last'] ?> </td>
                    <td> <?= $purchase['sweater_name'] ?> </td>
                    <td class="<?= $purchase['missing_size'] ? " warning" : ""?>"> <?= $purchase['size'] ?> </td>
                    <td> <?= $purchase['school_name'] ?? "N/A" ?> </td>
                    <td> $<?= $purchase['amount'] ?> </td>
                    <td> <?= $purchase['authorize_desc'] ?> </td>
                    <td> <?= $purchase['authorize_trans_type'] ?> </td>
                    <td>
                        <?= $purchase['ship'] ? (empty($purchase['ship_addr']) ? "Missing address" : $purchase['ship_addr']) : ($purchase['school_id'] ? "" : "Missing (physical) school and shippping request") ?>
                    </td>
                </tr>
            <? } ?>
        <? } ?>
    </table>

    <script>
        $(document).ready(function () {
            $(".cancel-chidon-parent-purchase-item").submit(function (event) {
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
Unknown Sweaters Sizes