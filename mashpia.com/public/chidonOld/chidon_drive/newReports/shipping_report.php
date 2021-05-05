<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT']  . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT']  . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$totals = [];
$grandTotals = [];

// get oldest child for each admin
$oldestChild = [];
$sql = "select parent_id, user_id, first, last, class_grade, class_sub, dob from th_chidon 
        join users u using (user_id) 
        join classes c using (class_id) 
        where year = $year 
        order by parent_id, dob desc";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    // last one is oldest
    $oldestChild[$row['parent_id']] = $row['user_id'];
}

// get children
$children = [];
$parentChildren = [];
$sql = "select th_chidon_id, s.school_id, s.school_name, c.class_grade, c.class_sub, u.user_id, u.first, u.last, u.dob, date_paid, paid, yarmulka,  
            parent_id, size, edit_prizes, shabbaton_maven, shabbaton_pro, shabbaton_expert, shabbaton_trophy, award_type, khk_plaque, test_type  
        from th_chidon tc 
        join users u using (user_id) 
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        where tc.year = $year 
        and (shabbaton_maven = 1 or shabbaton_pro = 1 or shabbaton_expert = 1 or shabbaton_trophy = 1) 
        and s.school_id in (" . implode(',', array_keys($schools)) . ")
        order by s.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['school_id']][] = $row;
    $parentChildren[$row['parent_id']][] = $row;
}
// sort kids by age
foreach ($parentChildren as $parent => $more) {
    usort($parentChildren[$parent], function ($a, $b) {
        return $a['dob'] <=> $b['dob'];
    });
}

// find out which parents are getting extra celeb boxes
$extra = [];
$sql = "select parent_id, count(user_id) as total 
        from th_chidon 
        where year = $year  
        and parent_id not in (
            select admin_id from th_chidon_parent_purchases where celeb_box >= 1) 
        group by parent_id 
        order by parent_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $extra[$row['parent_id']] = $row['total'];
}

// get extra purchases
$purchases = [];
$sql = "select * from th_chidon_parent_purchases";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    // add extra celeb boxes where needed
    if (isset($extra[$row['admin_id']])) {
        $row['celeb_box'] = $extra[$row['admin_id']];
    }
    $purchases[$row['admin_id']][] = $row;
}

// figure out which kids are getting celeb boxes
$celebBoxes = [];
foreach ($purchases as $admin => $items) {
    foreach ($items as $purchase) {
        if (isset($purchase['celeb_box'])) {
            $boxes = $purchase['celeb_box'];
            if ($boxes > 0) {
                foreach ($parentChildren[$admin] as $child) {
                    if (intval($child['shabbaton_expert']) || intval($child['shabbaton_trophy'])) {
                        do {
                            $celebBoxes[$child['th_chidon_id']] = 1;
                            $boxes--;
                        } while ($boxes > 0);
                    }
                }
            }
        }
    }
}

$info = [];
$fields = ['celeb_box', 'celeb_box_add', 'celeb_box_add_ship', 'celeb_box_add_addr', 'sweater_mother', 'sweater_mother_ship',
    'sweater_mother_ship_addr', 'sweater_father', 'sweater_father_ship', 'sweater_father_ship_addr', 'sweater_bubby',
    'sweater_bubby_ship', 'sweater_bubby_ship_addr', 'sweater_zaidy', 'sweater_zaidy_ship', 'sweater_zaidy_ship_addr'];
foreach ($purchases as $admin => $details) {
    foreach ($details as $purchase) {
        foreach ($fields as $field) {
            if (!isset($info[$field])) $info[$admin][$field] = $purchase[$field];
            else {
                switch ($field) {
                    case 'celeb_box':
                    case 'celeb_box_add':
                    case 'celeb_box_add_ship':
                    case 'sweater_mother_ship':
                    case 'sweater_father_ship':
                    case 'sweater_bubby_ship':
                    case 'sweater_zaidy_ship':
                        if ($purchase[$field] > $info[$admin][$field]) $info[$admin][$field] = $purchase[$field];
                        break;
                    case 'sweater_mother':
                    case 'sweater_father':
                    case 'sweater_bubby':
                    case 'sweater_zaidy':
                    case 'celeb_box_add_addr':
                    case 'sweater_mother_ship_addr':
                    case 'sweater_father_ship_addr':
                    case 'sweater_bubby_ship_addr':
                    case 'sweater_zaidy_ship_addr':
                        if (!empty($purchase[$field])) $info[$admin][$field] = $purchase[$field];
                        break;
                }
            }
        }
    }
}

// check for extra celeb boxes
//$boxesTotal = 0;
//foreach ($info as $admin => $details) {
//    foreach ($details as $field => $total) {
//        if ($field == 'celeb_box_add') $boxesTotal += intval($total);
//    }
//}
//echo $boxesTotal;

$balances = [];
$sql = "select * from th_chidon_zelda";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $balances[$row['th_chidon_id']] = $row['balance'];
}

function checkForPurchases($child) {
    global $info, $oldestChild, $celebBoxes, $totals, $grandTotals;

    // celebration boxes
    if (isset($celebBoxes[$child['th_chidon_id']])) {
        echo "Celebration Box<br />";
        if (!isset($totals[$child['school_id']]['celeb_boxes'])) $totals[$child['school_id']]['celeb_boxes'] = 0;
        $totals[$child['school_id']]['celeb_boxes']++;
        if (!isset($grandTotals['celeb_boxes'])) $grandTotals['celeb_boxes'] = 0;
        $grandTotals['celeb_boxes']++;
    }

    $admin_id = $child['parent_id'];
    if (isset($info[$admin_id])) {
        // give extra purchases to oldest child
        if ($child['user_id'] == $oldestChild[$admin_id]) {
            // extra celebration boxes
            if (intval($info[$admin_id]['celeb_box_add']) && !intval($info[$admin_id]['celeb_box_add_ship'])) {
                echo "Extra Celebration Box<br />";
                if (!isset($totals[$child['school_id']]['extra_celeb_boxes'])) $totals[$child['school_id']]['extra_celeb_boxes'] = 0;
                $totals[$child['school_id']]['extra_celeb_boxes']++;
                if (!isset($grandTotals['extra_celeb_boxes'])) $grandTotals['extra_celeb_boxes'] = 0;
                $grandTotals['extra_celeb_boxes']++;
            }

            // sweaters
            $sweaters = ['mother', 'father', 'bubby', 'zaidy'];
            foreach ($sweaters as $sweater) {
                $ship = "sweater_" . $sweater . "_ship";
                if (isset($info[$admin_id]["sweater_$sweater"]) && !intval($info[$admin_id][$ship])) {
                    echo "Sweater $sweater, Size: " . $info[$admin_id]["sweater_$sweater"] . "<br />";
                    if (!isset($totals[$child['school_id']]["sweater_$sweater"][$info[$admin_id]["sweater_$sweater"]]))
                        $totals[$child['school_id']]["sweater_$sweater"][$info[$admin_id]["sweater_$sweater"]] = 0;
                    $totals[$child['school_id']]["sweater_$sweater"][$info[$admin_id]["sweater_$sweater"]]++;
                    if (!isset($grandTotals["sweater_$sweater"][$info[$admin_id]["sweater_$sweater"]]))
                        $grandTotals["sweater_$sweater"][$info[$admin_id]["sweater_$sweater"]] = 0;
                    $grandTotals["sweater_$sweater"][$info[$admin_id]["sweater_$sweater"]]++;
                }
            }
        }
    }
}
$fields = ['sweater_child', 'sweater_mother', 'sweater_father', 'sweater_bubby', 'sweater_zaidy'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Chidon Shipping Report</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 5px;
                border: 1px solid darkcyan;
            }
        </style>
    </head>
    <body>
        <h1>Chidon Shipping Report</h1>
        <?php foreach ($schools as $school_id => $school) : ?>
            <h2><?= $school ?></h2><hr />
            <table>
                <tr>
                    <th>Grade</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Eligibility</th>
                    <th>Registered</th>
                    <th>Registration Balance</th>
                    <th>To Ship</th>
                </tr>
                <?php
                if (isset($children[$school_id])) {
                    foreach ($children[$school_id] as $child) {
                        $award =  $child['award_type'];
                        if ($award == 'Medal') $award = "Plaque + Medal";
                        $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                        echo "<tr><td>" . $grade . "</td><td>" . $child['first'] . "</td><td>" . $child['last'] . "</td><td>";
                        // eligibility
                        if (intval($child['shabbaton_maven'])) echo "Sweater";
                        else if (intval($child['shabbaton_pro'])) echo "Sweater and Gifts";
                        else if (intval($child['shabbaton_expert'])) echo "Prizes and Trips";
                        else if (intval($child['shabbaton_trophy'])) echo "Prizes and Trips";
                        echo "</td><td>";
                        // registered
                        if (floatval($child['paid'])) echo "Registered";
                        else echo "<span style='color: red'>Not Registered</span>";
                        echo "</td><td>";
                        // balance
                        if (isset($balances[$child['th_chidon_id']])) {
                            if (floatval($balances[$child['th_chidon_id']]) > 0) echo "<span style='color: red'>" . $balances[$child['th_chidon_id']] . "</span>";
                            else echo $balances[$child['th_chidon_id']];
                        }
                        echo "</td><td>";
                        // stuff to ship
                        if (floatval($child['paid']) > 0 && (!isset($balances[$child['th_chidon_id']]) || floatval($balances[$child['th_chidon_id']]) == 0)) {
                            echo "Sweater Size: " . $child['size'] . "<br />";
                            if (!isset($totals[$child['school_id']]['sweater_child'][$child['size']])) $totals[$child['school_id']]['sweater_child'][$child['size']] = 0;
                            $totals[$child['school_id']]['sweater_child'][$child['size']]++;
                            if (!isset($grandTotals['sweater_child'][$child['size']])) $grandTotals['sweater_child'][$child['size']] = 0;
                            $grandTotals['sweater_child'][$child['size']]++;

                            if (floatval($child['paid']) > 25 && (intval($child['shabbaton_expert']) || intval($child['shabbaton_trophy']))) {
                                echo "Stress ball, table flag, ID card<br />";
                                if (!isset($totals[$child['school_id']]['stress_balls_flag_id_card'])) $totals[$child['school_id']]['stress_balls_flag_id_card'] = 0;
                                $totals[$child['school_id']]['stress_balls_flag_id_card']++;
                                if (!isset($grandTotals['stress_balls_flag_id_card'])) $grandTotals['stress_balls_flag_id_card'] = 0;
                                $grandTotals['stress_balls_flag_id_card']++;
                            }

                            echo "Award: " . $award . "<br />";
                            if (!isset($totals[$child['school_id']][$award])) $totals[$child['school_id']][$award] = 0;
                            $totals[$child['school_id']][$award]++;
                            if (!isset($grandTotals[$award])) $grandTotals[$award] = 0;
                            $grandTotals[$award]++;

                            if (intval($child['khk_plaque'])) {
                                echo "Kol Hatorah Kulah Plaque<br />";
                                if (!isset($totals[$child['school_id']]['khk_plaque'])) $totals[$child['school_id']]['khk_plaque'] = 0;
                                $totals[$child['school_id']]['khk_plaque']++;
                                if (!isset($grandTotals['khk_plaque'])) $grandTotals['khk_plaque'] = 0;
                                $grandTotals['khk_plaque']++;
                            }

                            // extra sweaters, celebration box
                            checkForPurchases($child);
                        }
                        echo "</td></tr>";
                    }
                }
                ?>
            </table>
            <div style="page-break-after: always;"></div>
            <h2>Totals for <?= $school ?></h2><hr />
            <?php
            if (isset($totals[$school_id])) {
                ksort($totals[$school_id]);
                foreach ($totals[$school_id] as $type => $total) {
                    if (is_array($total)) continue; // sweaters need to be done separately
                    echo $type . ": " . $total . "<br />";
                }
                foreach ($fields as $field) {
                    if (isset($totals[$school_id][$field])) {
                        ksort($totals[$school_id][$field]);
                        foreach ($totals[$school_id][$field] as $type => $total) {
                            echo $field . " - " . $type . ": " . $total . "<br />";
                        }
                    }
                }
            }
            ?>
            <div style="page-break-after: always;"></div>
        <?php endforeach; ?>
        <h2>Grand Totals</h2><hr />
        <?php
        ksort($grandTotals);
        foreach ($grandTotals as $type => $total) {
            if (is_array($total)) continue; // sweaters need to be done separately
            echo $type . ": " . $total . "<br />";
        }
        foreach ($fields as $field) {
            ksort($grandTotals[$field]);
            foreach ($totals[$school_id][$field] as $type => $total) {
                echo $field . " - " . $type . ": " . $total . "<br />";
            }
        }
        ?>
    </body>
</html>
