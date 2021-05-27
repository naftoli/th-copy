<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$super = $admin_user['auth'] == 'super';

$year = GlobalSettings::getChidonYear();
$s = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $s->getSchools();

$shipment_number = isset($_GET['num']) ? intval($_GET['num']) : 1;

// maps shipment number to prize id's
$shipments = [
    1   =>  [
        7, 8, 9, 10, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26,
        38, 40, 44, 45, 48, 50, 53, 54, 59, 60, 62, 63
    ],
    2   =>  [
        8, 27, 28, 29, 30, 31, 32, 34, 35, 39
    ],
    3   =>  [
        29, 36, 37
    ]
];

// limits prize id's to school id's for certain shipments
// array of prize id, shipment number, school id's
$limits = [
    7   =>  [
        1   =>  [81, 7, 54, 255, 13, 33, 63, 49, 192, 60, 613, 21, 37, 105, 577, 9, 471, 263, 84, 80, 50, 5, 45, 106, 58, 2, 176]
    ],
    8   =>  [
        1   =>  [517, 4, 162, 84, 470, 39, 80, 50, 5, 45, 106, 58, 2, 176, 615, 19, 42, 185, 472, 263, 483, 81]
    ],
    29  =>  [
        3   =>  [7, 255]
    ]
];

// list of schools that need break down of totals by classes
$schools_by_classes = [7, 9, 30, 54, 255];

$children = [];
$children_parents = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        join classes c on c.class_id = u.class_id 
        where tc.paid > 0 
        and tc.year = $year 
        and u.school_id in (" . implode(',', array_keys($schools)) . ") 
        order by u.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['school_id']][] = $row;
    $children_parents[$row['parent_id']][] = $row;
}
$grandTotals = [];
$prizeInfo = [];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8">
        <title>Prizes Shipping Report</title>
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
    </head>
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        <h1>Prizes Shipping Report</h1>
        <?php
        foreach ($schools as $id => $school) {
            if (!isset($children[$id])) continue;
            ?>
            <h2><?= $school ?></h2>
            <table>
                <tr>
                    <th>Grade</th>
                    <th>Chidon ID</th>
                    <th>Name</th>
                    <th>Gifts / Prizes</th>
                </tr>
                <?php
                $totals = [];
                $classTotals = [];
                foreach ($children[$id] as $child) {
                    $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                    echo "<tr><td>" . $grade . "</td><td>" . $child['th_chidon_id'] . "</td><td>" .
                        $child['first'] . ' ' . $child['last'] . "</td><td>";
                    // get yarmulka / bracelet
                    if (!intval($child['shabbaton_maven'])) {
                        if ($shipment_number == 1) {
                            if ($child['gender'] == 'M') {
                                $yarmulka = intval($child['yarmulka']);
                                if (!$yarmulka) {
                                    $yarmulka = in_array($child['yarmulka'], [4, 5]) ? 4 : 5;
                                }
                                echo "Yarmulka Size: " . $yarmulka . "<br />";
                                // totals
                                if (!isset($totals['yarmulka'][$yarmulka])) $totals['yarmulka'][$yarmulka] = 0;
                                $totals['yarmulka'][$yarmulka]++;
                                if (!isset($grandTotals['yarmulka'][$yarmulka])) $grandTotals['yarmulka'][$yarmulka] = 0;
                                $grandTotals['yarmulka'][$yarmulka]++;
                                if (!isset($classTotals[$grade]['yarmulka'][$yarmulka])) $classTotals[$grade]['yarmulka'][$yarmulka] = 0;
                                $classTotals[$grade]['yarmulka'][$yarmulka]++;
                            } else {
                                echo "Chidon Bracelet<br />";
                                // totals
                                if (!isset($totals['bracelet'])) $totals['bracelet'] = 0;
                                $totals['bracelet']++;
                                if (!isset($grandTotals['bracelet'])) $grandTotals['bracelet'] = 0;
                                $grandTotals['bracelet']++;
                                if (!isset($classTotals[$grade]['bracelet'])) $classTotals[$grade]['bracelet'] = 0;
                                $classTotals[$grade]['bracelet']++;
                            }
                        }
                        // get prizes
                        if (intval($child['shabbaton_expert']) || intval($child['shabbaton_trophy'])) {
                            $prizes = [];
                            $sql = "select * from chidon_user_prizes cup 
                                    join chidon_prizes cp using (prize_id) 
                                    where cup.user_id = " . $child['user_id'];
                            $result = mysql_query($sql);
                            while ($row = mysql_fetch_assoc($result)) {
                                $prizes[] = $row;
                            }
                            foreach ($prizes as $prize) {
                                if (in_array($prize['prize_id'], $shipments[$shipment_number])) {
                                    // for certain prizes make sure it only goes to children in certain schools
                                    if (in_array($prize['prize_id'], array_keys($limits))) {
                                        if (in_array($shipment_number, array_keys($limits[$prize['prize_id']]))) {
                                            if (!in_array($child['school_id'], $limits[$prize['prize_id']][$shipment_number])) continue;
                                        } else {
                                            if (
                                                isset($limits[$prize['prize_id']][$shipment_number])
                                                &&
                                                in_array($child['school_id'], $limits[$prize['prize_id']][$shipment_number])
                                            ) continue;
                                        }
                                    }
                                    $he_name = false;
                                    if ($prize['prize_id'] == 36 || ($prize['prize_id'] >= 44 && $prize['prize_id'] <= 60)) {
                                        $he_name = true;
                                        echo "<span class='warning'>";
                                    }
                                    echo $prize['prize_name'];
                                    if ($prize['color']) echo " - Color: " . $prize['color'];
                                    if ($prize['size']) echo "; Size: " . $prize['size'];
                                    if ($he_name) echo "; He Name: " . $prize['he_name'] . "</span>";
                                    echo "<br />";

                                    // totals
                                    if (!isset($totals['prizes'][$prize['prize_id']])) $totals['prizes'][$prize['prize_id']] = 0;
                                    $totals['prizes'][$prize['prize_id']]++;
                                    if (!isset($grandTotals['prizes'][$prize['prize_id']])) $grandTotals['prizes'][$prize['prize_id']] = 0;
                                    $grandTotals['prizes'][$prize['prize_id']]++;
                                    if (!isset($classTotals[$grade]['prizes'][$prize['prize_id']])) $classTotals[$grade]['prizes'][$prize['prize_id']] = 0;
                                    $classTotals[$grade]['prizes'][$prize['prize_id']]++;

                                    // set prize information
                                    if (!isset($prizeInfo[$prize['prize_id']])) {
                                        $prizeInfo[$prize['prize_id']] = [
                                            'name' => $prize['prize_name'],
                                            'color' => $prize['color'],
                                            'size' => $prize['size']
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    echo "</td></tr>";
                }
                ?>
            </table>
            <div style="page-break-after: always"></div>
            <h2>Totals for <?= $school ?></h2>
            <?php
            if (!in_array($id, $schools_by_classes)) {
                ?>
                <table>
                    <tr>
                        <th>Item</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Total</th>
                    </tr>
                    <?php
                    if (isset($totals['bracelet'])) {
                        echo "<tr><td>Chidon Bracelet</td><td></td><td></td><td>" . $totals['bracelet'] . "</td></tr>";
                    }
                    if (isset($totals['yarmulka'])) {
                        foreach ($totals['yarmulka'] as $size => $total) {
                            echo "<tr><td>Yarmulka</td><td></td><td>$size</td><td>$total</td></tr>";
                        }
                    }
                    if (isset($totals['prizes'])) {
                        ksort($totals['prizes']);
                        foreach ($totals['prizes'] as $prize_id => $total) {
                            echo "<tr><td>" . $prizeInfo[$prize_id]['name'] . "</td><td>" . $prizeInfo[$prize_id]['color']
                                . "</td><td>" . $prizeInfo[$prize_id]['size'] . "</td><td>" . $total . "</td></tr>";
                        }
                    }
                    ?>
                </table>
                <div style="page-break-after: always"></div>
            <?php
            } else {
                $grades = array_keys($classTotals);
                sort($grades);
                ?>
                <table>
                    <tr>
                        <th>Grade</th>
                        <th>Item</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Total</th>
                    </tr>
                    <?php
                    foreach ($grades as $grade) {
                        if (isset($classTotals[$grade]['bracelet'])) {
                            echo "<tr><td>" . $grade . "</td><td>Chidon Bracelet</td><td></td><td></td><td>" . $classTotals[$grade]['bracelet'] . "</td></tr>";
                        }
                        if (isset($classTotals[$grade]['yarmulka'])) {
                            foreach ($classTotals[$grade]['yarmulka'] as $size => $total) {
                                echo "<tr><td>" . $grade . "</td><td>Yarmulka</td><td></td><td>$size</td><td>$total</td></tr>";
                            }
                        }
                        if (isset($classTotals[$grade]['prizes'])) {
                            ksort($classTotals[$grade]['prizes']);
                            foreach ($classTotals[$grade]['prizes'] as $prize_id => $total) {
                                echo "<tr><td>" . $grade . "</td><td>" . $prizeInfo[$prize_id]['name'] . "</td><td>" . $prizeInfo[$prize_id]['color']
                                    . "</td><td>" . $prizeInfo[$prize_id]['size'] . "</td><td>" . $total . "</td></tr>";
                            }
                        }
                    }
                    ?>
                </table>
                <div style="page-break-after: always"></div>
                <?php
            }
        }
        if ($super) {
            ?>
            <h2>Grand Totals</h2>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Total</th>
                </tr>
                <?php
                echo "<tr><td>Chidon Bracelet</td><td></td><td></td><td>" . $grandTotals['bracelet'] . "</td></tr>";
                foreach ($grandTotals['yarmulka'] as $size => $total) {
                    echo "<tr><td>Yarmulka</td><td></td><td>$size</td><td>$total</td></tr>";
                }
                ksort($grandTotals['prizes']);
                foreach ($grandTotals['prizes'] as $prize_id => $total) {
                    echo "<tr><td>" . $prizeInfo[$prize_id]['name'] . "</td><td>" . $prizeInfo[$prize_id]['color']
                        . "</td><td>" . $prizeInfo[$prize_id]['size'] . "</td><td>" . $total . "</td></tr>";
                }
                ?>
            </table>
        <?php } ?>
    </body>
</html>

