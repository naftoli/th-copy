<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';
$cs = new ChidonShipping($year);

$gender = $_REQUEST['type'];
require 'functions.php';

$prizesInfo = getPrizesInfo();
$prizes = getUserPrizes();
$marks = getMarks();
$final_marks = getFinalMarks();

$sheets = [];
foreach ($schools as $school_id => $school) {
    $children = getChildren($school_id, $gender);
    if (! empty($children)) {
        $sheets[$school_id] = createSpreadSheet($children);
    }
}

$awards = [
    'yesod'   => 'certificate',
    'yediah'  => 'plaque',
    'havonah' => 'medal / plaque',
    'iyun'    => 'trophy / medal / blue plaque',
];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
<!--        <link href="../../admin_styles.css" rel="stylesheet" type="text/css"/>-->
        <style>
            tr, th, td {
              padding: 10px;
              font-size: 14px;
              border-bottom: 1px solid grey;
              font-family: Arial, Helvetica, sans-serif;
            }
            caption {
              font-size: 24px;
            }
        </style>
    </head>
    <body>
<!--        --><?php //include('../../admin_header.php'); ?>
        <?php foreach ($sheets as $school => $sheet) : ?>
            <table>
                <caption><?= $schools[$school] ?></caption>
                <tr>
                    <th>Highest Track</th>
                    <th>Name</th>
                    <th>Serial</th>
                    <th>Grade</th>
                    <th>Award</th>
                    <th>Prize 1</th>
                    <th>Prize 2</th>
                    <th>Prize 3</th>
                    <th>Prize 4</th>
                    <th>Prize 5</th>
                    <th>Prize 6</th>
                    <th>Total Prizes</th>
                </tr>
                <?php
                foreach ($sheet as $idx => $row) {
                    if ($idx == 0) continue; // skip first row
                    if (empty($row[1])) continue; // only show rows with names of children
                    $track = $row[0];
                    $name = $row[1];
                    $serial = substr($row[2], 0, strpos($row[2], '.png'));
                    $grade = $row[3];
                    $award = $row[7] ? $awards[$row[7]] : '';
                    $prizes = [$row[9], $row[10], $row[11], $row[12], $row[13], $row[14]];
                    $total_prizes = $row[15];
                    echo "<tr><td>" . $track . "</td><td>" . $name . "</td><td>" . $serial . "</td><td>" . $grade .
                        "</td><td>" . $award . "</td>";
                    if ($track == 'yesod') {
                        echo "<td colspan='7'></td></tr>";
                        continue;
                    } else {
                        foreach ($prizes as $prize) {
                            $desc = '';
                            if ($prize) {
                                $prize_id_desc = substr($prize, strpos($prize, '_') + 1);
                                $prize_id = substr($prize_id_desc, 0, strpos($prize_id_desc, '.png'));
                                $prize_info = $prizesInfo[$prize_id];
                                $desc = $prize_info['prize_name'] . (empty($prize_info['size']) ? '' : ' ' . $prize_info['size']) .
                                    (empty($prize_info['color']) ? '' : ' ' . $prize_info['color']);
                            }
                            echo "<td>" . $desc . "</td>";
                        }
                        echo "<td>" . $total_prizes . "</td></tr>";
                    }
                }
                ?>
            </table>
            <p></p>
        <?php endforeach; ?>
    </body>
</html>