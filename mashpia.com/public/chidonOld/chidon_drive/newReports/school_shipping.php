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

$only_totals = isset($_GET['only_totals']) && $_GET['only_totals'];
$only_details = isset($_GET['only_details']) && $_GET['only_details'];
$show_details = true;
$show_totals = true;
if ($only_totals) { $show_details = false; }
if ($only_details) { $show_totals = false; }

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
    if (!array_key_exists($user['school_id'], $users_by_school)) { $users_by_school[$user['school_id']] = []; }
    $users_by_school[$user['school_id']][] = $user;
    if (!array_key_exists($user['admin_id'], $users_by_admin)) { $users_by_admin[$user['admin_id']] = []; }
    $users_by_admin[$user['admin_id']][] = $user;
}

function get_celebration_box_purchases() {
    $sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
        celeb_box as celeb_box_amount, 0 as ship, null as ship_addr
        FROM th_chidon_parent_purchases tcpp 
        join admins a using (admin_id)
        WHERE (celeb_box is not null and celeb_box > 0)";
    $result = mysql_query($sql);
    $purchases = [];
    while($row = mysql_fetch_assoc($result)) {
        $purchases[] = array_merge($row, ['type' => 'celebration_box', 'purchase_key' => $row['admin_id'] . ':celebration_box']);
    }
    return $purchases;
}

function get_extra_celebration_box_purchases() {
    $sql = "SELECT tcpp.*, a.admin_id, a.first as admin_first, a.last as admin_last,
        celeb_box_add as celeb_box_amount, celeb_box_add_ship as ship, celeb_box_add_addr as ship_addr
        FROM th_chidon_parent_purchases tcpp 
        join admins a using (admin_id)
        WHERE (celeb_box_add is not null and celeb_box_add > 0)";
    $result = mysql_query($sql);
    $purchases = [];
    while($row = mysql_fetch_assoc($result)) {
        $purchases[] = array_merge($row, ['type' => 'celebration_box', 'purchase_key' => $row['admin_id'] . ':celebration_box']);
    }
    return $purchases;
}

function get_sweater_purchases() {
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
            WHERE $person_relation_key = '$size'";
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

// *** filter out home shippments ***
// $all_purchases = array_filter($all_purchases, function($purchase) { return !$purchase['ship']; });

// *** group by admin_id ***
// $purchases_by_admin_id = array_group_by($all_purchases, function($purchase) { return $purchase['admin_id']; });
$purchases_by_admin_id = [];
foreach ($all_purchases as $purchase) {
    if (!array_key_exists($purchase['admin_id'], $purchases_by_admin_id)) { $purchases_by_admin_id[$purchase['admin_id']] = []; }
    $purchases_by_admin_id[$purchase['admin_id']][] = $purchase;
}

$school_purchases = [];
$all_schools_summaries = [];
$summaries_by_school = [];
$ak_ms_summaries = [];
$home_summaries = [];
$unknown_summaries = [];
$unknown_purchases = [];

$full_summaries = [];

// *** convert to product to string and group based on shipping categories***
// school_purchases by user > product > amount
// all_schools_summaries by school > product > amount
// summaries_by_school by product => amount
// ak_ms_summaries (anash kinder myshliach) by product > amount
// home_summaries (direct to home shipments) by product > amount
// unknown_summaries (no registered children found) by product > amount
// unknown_purchases by admin_id > product > amount

foreach($purchases_by_admin_id as $purchases) {
    $admin_id = $purchases[0]['admin_id'];
    $celeb_box_amount = 0;
    $children = array_key_exists($admin_id, $users_by_admin) ? $users_by_admin[$admin_id] : [];
    $children_in_real_school = array_filter($children, function ($child) { return !in_array($child['school_id'], [269, 61]); });
    $children_in_virtual_school = array_filter($children, function ($child) { return in_array($child['school_id'], [269, 61]); });
    usort($children_in_real_school, function ($a, $b) { return $a['dob'] <=> $b['dob']; });
    $child = count($children_in_real_school) ? $children_in_real_school[0] : false;
    $has_child_in_real_school = !!count($children_in_real_school);
    $has_child_in_virtual_school = !!count($children_in_virtual_school);

    foreach($purchases as $purchase) {
        $product_name = $purchase['type'] === "sweater" ?  "Sweater " . $purchase['sweater_name'] . " - " . $purchase['size'] : "Celebration Boxes";

        // school shipment
        if ($has_child_in_real_school && !$purchase['ship']) {
            if ($purchase['type'] === "sweater") {
                $school_id = array_key_exists('school_id', $child) ? $child['school_id'] : 0;
                $user_id = array_key_exists('user_id', $child) ? $child['user_id'] : 0;
                // update user purchases
                if (!array_key_exists($user_id, $school_purchases)) { $school_purchases[$user_id] = ['celebration_boxes' => 0, 'sweaters' => []]; }
                $school_purchases[$user_id]['sweaters'][] = $product_name;
                // update by school summary
                if (!array_key_exists($school_id, $summaries_by_school)) { $summaries_by_school[$school_id] = []; }
                if (!array_key_exists($product_name, $summaries_by_school[$school_id])) { $summaries_by_school[$school_id][$product_name] = 0; }
                $summaries_by_school[$school_id][$product_name] += 1;
                // update all schools summary
                if (!array_key_exists($product_name, $all_schools_summaries)) { $all_schools_summaries[$product_name] = 0; }
                $all_schools_summaries[$product_name] += 1;
                // update full summary
                if (!array_key_exists($product_name, $full_summaries)) { $full_summaries[$product_name] = 0; }
                $full_summaries[$product_name] += 1;
            } else {
                if (isset($purchase['celeb_box_amount'])) {
                    $celeb_box_amount += $purchase['celeb_box_amount'];
                    // update all schools summary
                    if (!array_key_exists($product_name, $all_schools_summaries)) { $all_schools_summaries[$product_name] = 0; }
                    $all_schools_summaries[$product_name] += 1;
                    // update full summary
                    if (!array_key_exists($product_name, $full_summaries)) { $full_summaries[$product_name] = 0; }
                    $full_summaries[$product_name] += 1;
                }
            }

        // for anash kinder / myshliach
        } else if ($has_child_in_virtual_school) {
            //  update ak/ms summary
            if (!array_key_exists($product_name, $ak_ms_summaries)) { $ak_ms_summaries[$product_name] = 0; }
            $ak_ms_summaries[$product_name] += 1;
            // update full summary
            if (!array_key_exists($product_name, $full_summaries)) { $full_summaries[$product_name] = 0; }
            $full_summaries[$product_name] += 1;

        // direct home shipment
        } else if ($purchase['ship']) {
            //  update home summary
            if (!array_key_exists($product_name, $home_summaries)) { $home_summaries[$product_name] = 0; }
            $home_summaries[$product_name] += 1;
            // update full summary
            if (!array_key_exists($product_name, $full_summaries)) { $full_summaries[$product_name] = 0; }
            $full_summaries[$product_name] += 1;

        // no registered children found
        } else {
            //  update unknown summary
            if (!array_key_exists($product_name, $unknown_summaries)) { $unknown_summaries[$product_name] = 0; }
            $unknown_summaries[$product_name] += 1;
            // update full summary
            if (!array_key_exists($product_name, $full_summaries)) { $full_summaries[$product_name] = 0; }
            $full_summaries[$product_name] += 1;
            
            if (!array_key_exists($admin_id, $unknown_purchases)) { $unknown_purchases[$admin_id] = ['celebration_boxes' => 0, 'sweaters' => []]; }
            if ($purchase['type'] === "sweater") {
                $unknown_purchases[$admin_id]['sweaters'][] = $product_name;
            } else {
                $unknown_purchases[$admin_id]['celebration_boxes'] += $purchase['celeb_box_amount'];
            }

        }
    }
    if ($has_child_in_real_school) {
        // distribute celebration boxes
        foreach($children_in_real_school as $i => $child) {
            if ($celeb_box_amount > $i) {
                if (!array_key_exists('school_id', $child)) { $logger->debug("missing school_id"); }
                if (!array_key_exists('user_id', $child)) { $logger->debug("missing user_id"); }
                $school_id = array_key_exists('school_id', $child) ? $child['school_id'] : 0;
                $user_id = array_key_exists('user_id', $child) ? $child['user_id'] : 0;
                // update user purchases
                if (!array_key_exists($user_id, $school_purchases)) { $school_purchases[$user_id] = ['celebration_boxes' => 0, 'sweaters' => []]; }
                $amount = $celeb_box_amount > count($children) + $i ? 2 : 1;
                $school_purchases[$user_id]['celebration_boxes'] += $amount;
                // update by school summary
                if (!array_key_exists($school_id, $summaries_by_school)) { $summaries_by_school[$school_id] = []; }
                if (!array_key_exists("Celebration Boxes", $summaries_by_school[$school_id])) { $summaries_by_school[$school_id]["Celebration Boxes"] = 0; }
                $summaries_by_school[$school_id]["Celebration Boxes"] += 1;
            }
        }
    }
}

$summaries = [
    "Full summary of all Sweaters and Celebration Boxes" => $full_summaries,
    "Summary of all school shipments" => $all_schools_summaries,
    "Summary of Anash Kinder and MyShliach shipments" => $ak_ms_summaries,
    "Summary of Direct to home shipments" => $home_summaries,
    "Summary of Unknown" => $unknown_summaries
];

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
    <? if ($show_totals) { ?>
        <? foreach ($summaries as $title => &$summary) { ?>
            <? if (!count($summary)) { continue; } ?>
            <br>
            <table>
                <tr>
                    <th colspan="2"><?= $title ?></th>
                </tr>
                <? ksort($summary); ?>
                <? foreach($summary as $product => $amount) { ?>
                    <tr>
                        <td> <?= $amount ?> </td>
                        <td> <?= $product ?> </td>
                    </tr>
                <? } ?>
            </table>
        <? } ?>
    <? } ?>

    <? foreach ($schools as $school_id => $school_name) { ?>
        <? if (!array_key_exists($school_id, $summaries_by_school)) { continue; } ?>
        <section class="page-break-defore">
            <h3><?= $school_name ?></h3>
            <? if ($show_totals) { ?>
                <br>
                <table>
                    <tr>
                        <th colspan="2">Totals</th>
                    </tr>
                    <? ksort($summaries_by_school[$school_id]); ?>
                    <? foreach($summaries_by_school[$school_id] as $product => $amount) { ?>
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
                        <th>School</th>
                        <th>Class</th>
                        <th>Chidon ID</th>
                        <th>Name</th>
                        <th>Purchases</th>
                    </tr>
                    <? foreach($users_by_school[$school_id] as $user) { ?>
                        <? $user_id = $user['user_id']; ?>
                        <? if (array_key_exists($user_id, $school_purchases)
                            && (count($school_purchases[$user_id]['sweaters']) > 0 || $school_purchases[$user_id]['celebration_boxes'] > 0 )
                        ) { ?>
                            <? $celebration_box_amount = $school_purchases[$user_id]['celebration_boxes'] ?>
                            <tr>
                                <td> <?= $schools[$user['school_id']] ?> </td>
                                <td> <?= $user['class_grade'] . ($user['class_sub'] ? ' - '.$user['class_sub'] : '') ?> </td>
                                <td> <?= $user['th_chidon_id'] ?> </td>
                                <td> <?= $user['first'] . " " . $user['last'] ?> </td>
                                <td>
                                    <?= implode('<br>', array_merge(
                                        $school_purchases[$user_id]['sweaters'],
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
