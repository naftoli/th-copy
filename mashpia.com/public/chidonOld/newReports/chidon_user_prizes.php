<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$chidon_prizes = [];
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/shipping/class.chidonShipping.php';
$cs = new ChidonShipping();
foreach ($schools as $id => $name) {
    $chidon_prizes[$id] = $cs->getPrizes('M', $id);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Chidon Student Prizes</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
            td:not(.type) {
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Chidon Student Prizes</h1>
        <?php
        echo "<table><tr><th>Serial Number</th><th>School</th></th><th>Grade</th><th>First Name</th><th>Last Name</th>
            <th>Highest Track</th><th>Prize(s) Selected</th></tr>";
        foreach ($chidon_prizes as $school => $more) {
            foreach ($more as $user_id => $prizes) {
                $info = $prizes[0];
                echo "<tr><td>" . $info['serial'] . "</td><td>" . $schools[$school] . "</td><td>" . $info['grade'] .
                    "</td><td>" . $info['first'] . "</td><td>" . $info['last'] . "</td><td>" . $info['track'] . "</td><td>";
                foreach ($prizes as $prize) {
                    echo $prize['item'] . ' ' . ($prize['size'] ?? '') . ' ' . ($prize['color'] ?? '') . ", ";
                }
                echo "</td></tr>";
            }
        }
        echo "</table>";
        ?>
    </body>
</html>