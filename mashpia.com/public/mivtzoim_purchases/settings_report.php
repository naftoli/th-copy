<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimSetting.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$year = GlobalSettings::getChidonRegYear();
$schoolSettings = MivtzoimSetting::getSchools( $year, [2, 3] );
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mivtza Chanuka Settings</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mivtza Chanuka Settings</h1>
        <table>
            <tr>
                <th>School ID</th>
                <th>School</th>
                <th>Part of Mivtza</th>
                <th>Menorah Shipping Fee</th>
                <th>Brochure Shipping Fee</th>
            </tr>
            <?php
            foreach ( $schools as $id => $school ) {
                echo "<tr><td>" . $id . "</td><td>" . $school . "</td><td>";
                if ( isset( $schoolSettings[$id] ) ) {
                    if ( $schoolSettings[$id][2]['allow'] ) echo "yes";
                    else echo "no";
                    echo "</td><td>" . $schoolSettings[$id][2]['shipping'] . "</td><td>" . $schoolSettings[$id][3]['shipping'] . "</td>";
                }
                else echo "not yet</td><td></td><td></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </body>
</html>