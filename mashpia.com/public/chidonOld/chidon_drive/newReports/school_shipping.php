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
// $logger->debug($sql_limit);
// $logger->debug("GET", $_GET);

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

// *** load users sorted (scoped by school) ***
$users_sql = "SELECT c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.school_id, u.user_serial from users u
    join classes c on c.class_id = u.class_id
    where u.school_id in (" . implode(',', array_keys($schools)) . ")
    ORDER BY class_grade, class_sub, last, first";
$users_query = mysql_query($users_sql);
$users_by_school = [];
while($user = mysql_fetch_assoc($users_query)) {
    // var_dump($user);
    if (array_key_exists($user['school_id'], $users_by_school)){
        $users_by_school[$user['school_id']][] = $user;
    } else {
        $users_by_school[$user['school_id']] = [$user];
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
        $admin_id = $row['admin_id'];
        // get registered children chidon
        $children_sql = "SELECT s.school_id, s.school_name, u.*, u.first as user_first, u.last as user_last from users u
            join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
            join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
                c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
            ))
            inner join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
            where aa.admin_id = $admin_id
            and c.th_chidon_id is not null
            order by u.dob asc";
        $children_result = mysql_query($children_sql);
        $children = [];
        while ($child = mysql_fetch_assoc($children_result)) {
            $children[] = $child;
        }
        // if (count($children) === 0) { $logger->debug(__LINE__.' '.$children_sql); }
        if (count($children) > 0) {
            $purchases[] = array_merge($row,
                [
                    'type' => 'celebration_box',
                    'purchase_key' => $admin_id . ':celebration_box',
                    'children' => $children
                ]
            );
        }
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
        $admin_id = $row['admin_id'];
        // get registered children chidon
        $children_sql = "SELECT s.school_id, s.school_name, u.*, u.first as user_first, u.last as user_last from users u
            join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
            join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
                c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
            ))
            inner join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
            where aa.admin_id = $admin_id
            and c.th_chidon_id is not null
            order by u.dob asc";
        $children_result = mysql_query($children_sql);
        $children = [];
        while ($child = mysql_fetch_assoc($children_result)) {
            $children[] = $child;
        }
        // if (count($children) === 0) { $logger->debug(__LINE__.' '.$children_sql); }
        if (count($children) > 0) {
            $purchases[] = array_merge($row,
                [
                    'type' => 'celebration_box',
                    'purchase_key' => $admin_id . ':celebration_box',
                    'children' => $children
                ]
            );
        }
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
            $sql_limit";
        $purchases_result = mysql_query($purchases_sql);
        while($purchases_row = mysql_fetch_assoc($purchases_result)) {
            $admin_id = $purchases_row['admin_id'];
            $purchase_key = $admin_id.":".$person_relation_key;

            if ($sweater_id) {
                $sweater_row = $sweaters[$sweater_id];
            }
            // get oldest child registered for the chidon
            $child_sql = "SELECT s.school_id, s.school_name, u.*, u.first as user_first, u.last as user_last from users u
                join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
                join th_chidon c on (c.user_id = u.user_id and c.year = $year and (
                    c.shabbaton_maven = 1 or c.shabbaton_pro = 1 or c.shabbaton_expert = 1 or c.shabbaton_trophy = 1
                ))
                inner join schools s on (s.school_id = u.school_id and s.school_id not in (269, 61))
                where aa.admin_id = {$purchases_row['admin_id']}
                and c.th_chidon_id is not null
                order by u.dob asc
                limit 1";
            $child_result = mysql_query($child_sql);
            $child = mysql_fetch_assoc($child_result);
            // if (!$child) { $logger->debug(__LINE__.' '.$child_sql); }
            if ($child) {

                $purchases[] = array_merge($purchases_row, $sweater_row,
                    [
                        'type' => 'sweater',
                        'purchase_key' => $purchase_key,
                        'person_relation_key' => $person_relation_key,
                        'child' => $child
                    ]
                );
            }
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
$all_purchases = array_filter($all_purchases, function($purchase) { return $purchase['ship'] && !empty($purchase['ship_addr']); });

// *** group by admin_id ***
$purchases_by_admin_id = array_group_by($all_purchases, function($purchase) { return $purchase['admin_id']; });

$grouped_purchases = [];

// *** regroup purchases by school_id > user_id and convert to product to string ***
foreach($purchases_by_admin_id as $purchases) {
    $items = [];
    $celeb_box_amount = 0;
    $children = [];
    foreach($purchases as $purchase) {
        if ($purchase['type'] === "sweater") {
            $child = $purchase['child'];
            // if (!$child) { $logger->warning(__LINE__." missing child for purchase", [$child, $purchase]); continue; }
            // if (!array_key_exists('user_id', $child)) { $logger->warning(__LINE__." missing user_id for child", [$child, $purchase]); continue; }
            // if (!array_key_exists('school_id', $child)) { $logger->warning(__LINE__." missing school_id for child", $purchase); continue; }
            $school_id = array_key_exists('school_id', $child) ? $child['school_id'] : 0;
            $user_id = array_key_exists('user_id', $child) ? $child['user_id'] : 0;
            if (!array_key_exists($user_id, $grouped_purchases)) { $grouped_purchases[$user_id] = ['celebration_boxes' => 0, 'sweaters' => []]; }
            $grouped_purchases[$user_id]['sweaters'][] = "Sweater " . $purchase['sweater_name'] . " - " . $purchase['size'];
        } else {
            if (isset($purchase['celeb_box_amount'])) {
                $celeb_box_amount += $purchase['celeb_box_amount'];
                $children = $purchase['children'];
            }
        }
    }
    // distribute celebration boxes
    foreach($children as $i => $child) {
        // if (!$child) { $logger->warning(__LINE__." missing child for purchase", [$child, $purchase]); continue; }
        // if (!array_key_exists('user_id', $child)) { $logger->warning(__LINE__." missing user_id for child", [$child, $purchase]); continue; }
        // if (!array_key_exists('school_id', $child)) { $logger->warning(__LINE__." missing school_id for child", $purchase); continue; }

        if ($celeb_box_amount > $i) {
            $school_id = array_key_exists('school_id', $child) ? $child['school_id'] : 0;
            $user_id = array_key_exists('user_id', $child) ? $child['user_id'] : 0;
            if (!array_key_exists($user_id, $grouped_purchases)) { $grouped_purchases[$user_id] = ['celebration_boxes' => 0, 'sweaters' => []]; }
            $amount = $celeb_box_amount > count($children) + $i ? 2 : 1;
            $grouped_purchases[$user_id]['celebration_boxes'] += $amount;
        }
    }
}

// var_dump($grouped_purchases);
// exit;

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
    </style>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
</head>
<body>
    <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
    <h1>Chidon School Shipments</h1>
    
    <? //var_dump($grouped_purchases) ?>

    <? foreach ($schools as $school_id => $school_name) { ?>
        <h3><?= $school_name ?></h3>
        <table>
            <tr>
                <th>Class</th>
                <th>Serial</th>
                <th>Name</th>
                <th>Purchases</th>
            </tr>
            <?// $logger->debug("table for " . $school_name) ?>
            <?// $logger->debug("user_ids", array_keys($grouped_purchases[$school_id])) ?>
            <? if (array_key_exists($school_id, $users_by_school)) { ?>
                <? foreach($users_by_school[$school_id] as $user) { ?>
                    <? $user_id = $user['user_id']; ?>
                    <? if (array_key_exists($user_id, $grouped_purchases)
                        && (count($grouped_purchases[$user_id]['sweaters']) > 0 || $grouped_purchases[$user_id]['celebration_boxes'] > 0 )
                    ) { ?>
                        <? $celebration_box_amount = $grouped_purchases[$user_id]['celebration_boxes'] ?>
                        <tr>
                            <td> <?= $user_id ?> </td>
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
            <? } ?>
        </table>
        <br>
        <br>
    <? } ?>

</body>
</html>
