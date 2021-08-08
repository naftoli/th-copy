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

$school_shipping_sql = "select school_id, shipping_address1, shipping_address2, shipping_city, shipping_state, shipping_postal, shipping_country from schools
where school_id in (" . implode(',', array_keys($schools)) . ")";
$school_shipping_query = mysql_query($school_shipping_sql);
$school_shipping = [];
while($school = mysql_fetch_assoc($school_shipping_query)) {
    $school_shipping[$school['school_id']] = $school;
}

// *** load users ***
$users_sql = "SELECT aa.admin_id, tc.th_chidon_id, u.school_id, c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.dob from users u
    join classes c on c.class_id = u.class_id
    join admin_auths aa on (aa.id = u.user_id and aa.auth = 'user')
    join th_chidon tc on (tc.user_id = u.user_id and tc.year = $year)
    where u.user_id in (
        select user_id from th_chidon
            where year = 5781
            and (shabbaton_expert = 1 or shabbaton_trophy = 1)
            and parent_id not in (
                select admin_id from th_chidon_parent_purchases where celeb_box >= 1
            )
            order by parent_id
    )
    and u.school_id in (" . implode(',', array_keys($schools)) . ")
    ORDER BY class_grade, class_sub, last, first";
$users_query = mysql_query($users_sql);
$users = [];
$users_by_school = [];
while($user = mysql_fetch_assoc($users_query)) {
    $users[] = $user;    
    if (!array_key_exists($user['school_id'], $users_by_school)) { $users_by_school[$user['school_id']] = []; }
    $users_by_school[$user['school_id']][] = $user;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon School additional Shipments</title>
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
    <h1>Chidon School additional Shipments</h1>
    <p style="text-align: center;">
        <a class="filter-links" href="?only_totals=1">Shipment Totals</a>
        <a class="filter-links" href="?only_details=1">Shipment Details</a>
        <a class="filter-links" href="?">All</a>
    </p>
    <br>
    <table>
        <tr>
            <th colspan="2"> Total additional Celebration Boxes</th>
        </tr>
        <? $summary = ["Celebration Boxes" => count($users)] ?>
        <? foreach($summary as $product => $amount) { ?>
            <tr>
                <td> <?= $amount ?> </td>
                <td> <?= $product ?> </td>
            </tr>
        <? } ?>
    </table>

    <? foreach ($schools as $school_id => $school_name) { ?>
        <? if (!array_key_exists($school_id, $users_by_school) || in_array($school_id, [269, 61, 612])) { continue; } ?>
        <? $address = $school_shipping[$school_id] ?>
        <section class="page-break-defore">
            <h3><?= $school_name ?></h3>
            <p>
                <?=$address['shipping_address1']?>, <?=$address['shipping_address2']?><br/>
                <?=$address['shipping_city']?>, <?=$address['shipping_state']?> <?=$address['shipping_postal']?>, <?=$address['shipping_country']?>
            </p>
            <? if ($show_totals) { ?>
                <br>
                <table>
                    <tr>
                        <th colspan="2">Totals</th>
                    </tr>
                    <? $summary = ["Celebration Boxes" => count($users_by_school[$school_id])]; ?>
                    <? foreach($summary as $product => $amount) { ?>
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
                        <tr>
                            <td> <?= $schools[$user['school_id']] ?> </td>
                            <td> <?= $user['class_grade'] . ($user['class_sub'] ? ' - '.$user['class_sub'] : '') ?> </td>
                            <td> <?= $user['th_chidon_id'] ?> </td>
                            <td> <?= $user['first'] . " " . $user['last'] ?> </td>
                            <td> 1 Celebration Box</td>
                        </tr>
                    <? } ?>
                </table>
            <? } ?>
        </section>
    <? } ?>

</body>
</html>
