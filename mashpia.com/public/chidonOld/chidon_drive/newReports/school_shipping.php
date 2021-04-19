<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

set_time_limit(1000);
ini_set('max_execution_time',1000);

// *** load schools sorted ***
$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
$sql_limit = isset($_GET['limit']) ? (" limit ".(int)$_GET['limit']) : "";

$show_details = true;
$show_totals = true;
if (isset($_GET['only_totals'])) {
    $show_details = false;
}
if (isset($_GET['only_details'])) {
    $show_totals = false;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

// *** load users ***
$users_sql = "SELECT aa.admin_id, tc.th_chidon_id, u.school_id, c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.dob from users u
    join classes c on c.class_id = u.class_id
    join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
    join th_chidon tc on (tc.user_id = u.user_id and tc.year = $year)
    where u.school_id in (" . implode(',', array_keys($schools)) . ")
    ORDER BY class_grade, class_sub, last, first";
$users_query = mysql_query($users_sql);
$users_by_school = [];
$users_by_admin = [];
while($user = mysql_fetch_assoc($users_query)) {
    // var_dump($user);
    if (array_key_exists($user['school_id'], $users_by_school)){
        $users_by_school[$user['school_id']][] = $user;
    } else {
        $users_by_school[$user['school_id']] = [$user];
    }
    if (array_key_exists($user['admin_id'], $users_by_admin)){
        $users_by_admin[$user['admin_id']][] = $user;
    } else {
        $users_by_admin[$user['admin_id']] = [$user];
    }
}
// var_dump($schools);
// var_dump($users_by_school);
// var_dump(array_keys($schools));
// var_dump(array_values($schools));
// exit;
function get_celebration_box_purchases() {
    global $year, $sql_limit, $logger;
    // purchases
    $sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
        celeb_box as celeb_box_amount, 0 as ship, null as ship_addr
        FROM th_chidon_parent_purchases tcpp 
        join admins a using (admin_id)
        WHERE (celeb_box is not null and celeb_box > 0)
        $sql_limit";
    $result = mysql_query($sql);
    $purchases = [];
    while($row = mysql_fetch_assoc($result)) {
        $purchases[] = array_merge($row, ['type' => 'celebration_box', 'purchase_key' => $row['admin_id'] . ':celebration_box']);
    }
    return $purchases;
}

function get_extra_celebration_box_purchases() {
    global $year, $sql_limit,$logger;
    // purchases
    $sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
        celeb_box_add as celeb_box_amount, celeb_box_add_ship as ship, celeb_box_add_addr as ship_addr
        FROM th_chidon_parent_purchases tcpp 
        join admins a using (admin_id)
        WHERE (celeb_box_add is not null and celeb_box_add > 0)
        $sql_limit";
    $result = mysql_query($sql);
    $purchases = [];
    while($row = mysql_fetch_assoc($result)) {
        $purchases[] = array_merge($row, ['type' => 'celebration_box', 'purchase_key' => $row['admin_id'] . ':celebration_box']);
    }
    return $purchases;
}

function get_sweater_purchases() {
    global $year, $sql_limit, $logger;
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
    $sweater_sql = "SELECT * FROM chidon_sweaters";
    $sweater_result = mysql_query($sweater_sql);
    while($sweater_row = mysql_fetch_assoc($sweater_result)) {
        $sweaters[$sweater_row["sweater_id"]] = $sweater_row;
    }
    
    $purchases = [];
    foreach($skus as $sku) {
        $sweater_id = $sku[0];
        $person_relation_key = $sku[1];
        $size = $sku[2];

        $sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
            {$person_relation_key}_ship as ship,
            {$person_relation_key}_ship_addr as ship_addr
            FROM th_chidon_parent_purchases tcpp 
            join admins a using (admin_id)
            WHERE $person_relation_key = '$size'
            $sql_limit";
        $result = mysql_query($sql);
        while($row = mysql_fetch_assoc($result)) {
            $purchases[] = array_merge($row, $sweaters[$sweater_id],
                [
                    'type' => 'sweater',
                    'purchase_key' => $row['admin_id'].":".$person_relation_key,
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

// *** load purchases ***
$all_purchases = array_merge(get_celebration_box_purchases(), get_sweater_purchases(), get_extra_celebration_box_purchases());
// array_sort_by_props($all_purchases, ['school_name', 'admin_last', 'admin_id', 'sweater_name']);

// *** filter out home shippments ***
$all_purchases = array_filter($all_purchases, function($purchase) { return !$purchase['ship']; });

// *** group by admin_id ***
$purchases_by_admin_id = array_group_by($all_purchases, function($purchase) { return $purchase['admin_id']; });

$grouped_purchases = [];
$school_summaries = [];

// *** regroup purchases by school_id > user_id and convert to product to string ***
foreach($purchases_by_admin_id as $purchases) {
    $admin_id = $purchases[0]['admin_id'];
    $items = [];
    $celeb_box_amount = 0;
    $children = array_key_exists($admin_id, $users_by_admin) ? $users_by_admin[$admin_id] : [];
    if (!count($children)) {
        // $logger->debug("no Children found for admin $admin_id");
        continue;
    }
    $children = array_filter($children, function ($child) { return !in_array($child['school_id'], [269, 61, 6561]); });
    if (!count($children)) {
        // $logger->debug("no Children in shipable school found for admin $admin_id");
        continue;
    }
    usort($children, function ($a, $b) { return $a['dob'] <=> $b['dob']; });
    $child = $children[0];
    // $logger->debug('children', $children);
    foreach($purchases as $purchase) {
        if ($purchase['type'] === "sweater") {
            $school_id = array_key_exists('school_id', $child) ? $child['school_id'] : 0;
            $user_id = array_key_exists('user_id', $child) ? $child['user_id'] : 0;
            // update user purchases
            if (!array_key_exists($user_id, $grouped_purchases)) { $grouped_purchases[$user_id] = ['celebration_boxes' => 0, 'sweaters' => []]; }
            $product_name = "Sweater " . $purchase['sweater_name'] . " - " . $purchase['size'];
            $grouped_purchases[$user_id]['sweaters'][] = $product_name;
            // update school summary
            if (!array_key_exists($school_id, $school_summaries)) { $school_summaries[$school_id] = []; }
            if (!array_key_exists($product_name, $school_summaries[$school_id])) { $school_summaries[$school_id][$product_name] = 0; }
            $school_summaries[$school_id][$product_name] += 1;
        } else {
            if (isset($purchase['celeb_box_amount'])) {
                $celeb_box_amount += $purchase['celeb_box_amount'];
            }
        }
    }
    // distribute celebration boxes
    foreach($children as $i => $child) {
        if ($celeb_box_amount > $i) {
            $school_id = array_key_exists('school_id', $child) ? $child['school_id'] : 0;
            $user_id = array_key_exists('user_id', $child) ? $child['user_id'] : 0;
            // update user purchases
            if (!array_key_exists($user_id, $grouped_purchases)) { $grouped_purchases[$user_id] = ['celebration_boxes' => 0, 'sweaters' => []]; }
            $amount = $celeb_box_amount > count($children) + $i ? 2 : 1;
            $grouped_purchases[$user_id]['celebration_boxes'] += $amount;
            // update school summary
            if (!array_key_exists($school_id, $school_summaries)) { $school_summaries[$school_id] = []; }
            if (!array_key_exists("Celebration Boxes", $school_summaries[$school_id])) { $school_summaries[$school_id]["Celebration Boxes"] = 0; }
            $school_summaries[$school_id]["Celebration Boxes"] += 1;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon School Shipments</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
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
        .page-break-defore {
            page-break-before: always;
            margin-top: 40px;
        }
        .filter-links {
            display: inline-block;
            padding: 10px
        }
    </style>
    <!-- <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script> -->
</head>
<body>
    <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
    <h1>Chidon School Shipments</h1>
    <p style="text-align: center;">
        <a class="filter-links" href="?only_totals=1">Shipment Totals</a>
        <a class="filter-links" href="?only_details=1">Shipment Details</a>
        <a class="filter-links" href="?">All</a>
    </p>

    <? foreach ($schools as $school_id => $school_name) { ?>
        <? if (!array_key_exists($school_id, $school_summaries)) { continue; } ?>
        <section class="page-break-defore">
            <h3><?= $school_name ?></h3>
            <? if ($show_totals) { ?>
                <br>
                <table>
                    <tr>
                        <th colspan="2">Totals</th>
                    </tr>
                    <? ksort($school_summaries[$school_id]); ?>
                    <? foreach($school_summaries[$school_id] as $product => $amount) { ?>
                        <tr>
                            <td> <?= $amount ?> </td>
                            <td> <?= $product ?> </td>
                        </tr>
                    <? } ?>
                </table>
            <? } ?>

            <? if ($show_details) { ?>
                <br>
                <table>
                    <tr>
                        <th>Chidon ID</th>
                        <th>Class</th>
                        <th>Name</th>
                        <th>Purchases</th>
                    </tr>
                    <? foreach($users_by_school[$school_id] as $user) { ?>
                        <? $user_id = $user['user_id']; ?>
                        <? if (array_key_exists($user_id, $grouped_purchases)
                            && (count($grouped_purchases[$user_id]['sweaters']) > 0 || $grouped_purchases[$user_id]['celebration_boxes'] > 0 )
                        ) { ?>
                            <? $celebration_box_amount = $grouped_purchases[$user_id]['celebration_boxes'] ?>
                            <tr>
                                <td> <?= $user['th_chidon_id'] ?> </td>
                                <td> <?= $user['class_grade'] . ($user['class_sub'] ? ' - '.$user['class_sub'] : '') ?> </td>
                                <td> <?= $user['first'] . " " . $user['last'] ?> </td>
                                <td>
                                    <?= implode('<br>', array_merge(
                                        $grouped_purchases[$user_id]['sweaters'],
                                        ($celebration_box_amount ? ["$celebration_box_amount Celebration box" . ($celebration_box_amount > 1 ? "es" : "")] : [])
                                    )) ?>
                                </td>
                            </tr>
                        <? } ?>
                    <? } ?>
                </table>
            <? } ?>
        </section>
    <? } ?>

</body>
</html>
