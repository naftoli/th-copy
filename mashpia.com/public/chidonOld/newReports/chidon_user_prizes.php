<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$chidon_prizes = [];
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.chidonShipping.php';
$cs = new ChidonShipping();
foreach ($schools as $id => $name) {
    $chidon_prizes[$id] = $cs->getPrizes('M', $id);
}
echo "<pre>"; print_r($chidon_prizes); echo "</pre>"; exit;
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
                foreach ($prizes as $prize) {
                    echo "<tr><td>" . $prize['serial'] . "</td><td>" . $schools[$school] . "</td><td>" . $prize['grade'] .
                        "</td><td>" . $prize['first'] . "</td><td>" . $prize['last'] . "</td><td>" . $prize['track'] .
                        "</td><td>" . $prize['item'] . ' ' . ($prize['size'] ?? '') . ' ' . ($prize['color'] ?? '') . "</td></tr>";
                }
            }
        }
        echo "</table>";
        ?>
    </body>
</html>