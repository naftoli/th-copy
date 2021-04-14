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

function get_celebration_box_purchases() {
    global $year;
    // purchases
    $sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
        celeb_box_add_ship as ship, celeb_box_add_addr as ship_addr
        FROM th_chidon_parent_purchases tcpp 
        join admins a using (admin_id)
        WHERE (celeb_box_add is not null and celeb_box_add > 0) || (celeb_box is not null and celeb_box > 0)
        order by a.last, a.first, a.admin_id";
    $result = mysql_query($sql);
    $purchases = [];
    while($row = mysql_fetch_assoc($result)) {
        $admin_id = $row['admin_id'];
        // // get oldest child registered for the chidon
        // $child_sql = "SELECT s.school_name, u.first as user_first, u.last as user_last from users u
        //     join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
        //     join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
        //         c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
        //     ))
        //     left join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
        //     where aa.admin_id = $admin_id
        //     and c.th_chidon_id is not null
        //     order by u.dob asc
        //     limit 1
        // ";
        // $child_result = mysql_query($child_sql);
        // $child_row = mysql_fetch_assoc($child_result);
        $purchases[] = array_merge($row,
            // $child_row,
            [
                'type' => 'celebration_box',
                'purchase_key' => $admin_id . ':celebration_box'
            ]
        );
    }
    return $purchases;
}

function get_sweater_purchases() {
    global $year;
    $skus = [
        [21, 'sweater_mother', "xs"],
        [22, 'sweater_mother', "small"],
        [23, 'sweater_mother', "medium"],
        [24, 'sweater_mother', "large"],
        [25, 'sweater_mother', "xl"],
        [15, 'sweater_father', "xs"],
        [38, 'sweater_father', "small"],
        [18, 'sweater_father', "medium"],
        [19, 'sweater_father', "large"],
        [20, 'sweater_father', "xl"], 
        [26, 'sweater_bubby', "xs"],
        [27, 'sweater_bubby', "small"],
        [28, 'sweater_bubby', "medium"],
        [29, 'sweater_bubby', "large"],
        [30, 'sweater_bubby', "xl"],
        [31, 'sweater_zaidy', "xs"],
        [32, 'sweater_zaidy', "small"],
        [33, 'sweater_zaidy', "medium"],
        [34, 'sweater_zaidy', "large"],
        [35, 'sweater_zaidy', "xl"],
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

        $purchases_sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
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
            }
            // // get oldest child registered for the chidon
            // $child_sql = "SELECT s.school_name, u.first as user_first, u.last as user_last from users u
            //     join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
            //     join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
            //         c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
            //     ))
            //     left join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
            //     where aa.admin_id = {$purchases_row['admin_id']}
            //     and c.th_chidon_id is not null
            //     order by u.dob asc
            //     limit 1
            // ";
            // $child_result = mysql_query($child_sql);
            // $child_row = mysql_fetch_assoc($child_result);
            $purchases[] = array_merge($purchases_row, $sweater_row,
                // $child_row,
                [
                    'type' => 'sweater',
                    'purchase_key' => $purchase_key,
                    'person_relation_key' => $person_relation_key
                ]
            );
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
            $desc = $prop[1];
            // allow missing keys (always gows to end)
            if (!array_key_exists($name, $a) && !array_key_exists($name, $a)) { continue; }
            elseif (!array_key_exists($name, $a)) { return 1; }
            elseif (!array_key_exists($name, $b)) { return -1; }
            $diff = $desc ? ($b[$name] <=> $a[$name]) : ($a[$name] <=> $b[$name]);
            if ($diff !== 0 ) {
                return $diff;
            }
        }
        return 0;
    });
}

// $func returns the key used for grouping the items
function array_group_by($array, $func) {
    $groups = [];
    foreach($array as $item) {
        $key = $func($item);
        array_key_exists($key, $groups) ? $groups[$key][] = $item : $groups[$key] = [$item];
    }
    return $groups;
}

$all_purchases = array_merge(get_celebration_box_purchases(), get_sweater_purchases());
array_sort_by_props($all_purchases, ['admin_first', 'admin_last', 'admin_id', 'sweater_name']);

// filter home shippments
$all_purchases = array_filter($all_purchases, function($purchase) { return $purchase['ship'] && !empty($purchase['ship_addr']); });

// group by admin/address
$grouped_purchases = array_group_by($all_purchases, function($purchase) { return $purchase['admin_id'].':'.$purchase['ship_addr']; })


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Direct to Home Shipments</title>
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
    <h1>Chidon Direct to Home Shipments</h1>
    <? foreach($grouped_purchases as $purchases) {
        $items = [];
        $celeb_box_amount = 0;
        foreach($purchases as $purchase) {
            if ($purchase['type'] === "sweater") {
                $items[] = "Sweater " . $purchase['sweater_name'] . " - " . $purchase['size'];
            } else {
                // ?? add base celebration boxes for anash kinder / my shliach ??
                // if (isset($purchase['celeb_box']) && !$purchase['school_id']) {
                //     $celeb_box_amount += $purchase['celeb_box'];
                // }
                if (isset($purchase['celeb_box_add'])) {
                    $celeb_box_amount += $purchase['celeb_box_add'];
                }
            }
        }
        if ($celeb_box_amount > 0) {
            $items[] = $celeb_box_amount === 1 ? "Celebration Box"  : $celeb_box_amount . " Celebration Boxes";
        }
        echo('<p>(admin_id:' . $purchases[0]['admin_id'] . ')</p>');
        echo('<p>' . implode('<br>', $items) . '</p>');
        echo('<p style="margin: 25px;"><b>' . $purchases[0]['admin_last'] . ' Family </b><br>');
        echo($purchases[0]['ship_addr'] . '</p>');
        echo('<hr>');
    } ?>

    
</body>
</html>
