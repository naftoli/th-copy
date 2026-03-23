<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No permission to access this page.";
    exit;
}

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$year = GlobalSettings::getCurrentYear();

$schoolList = implode(',', array_keys( $schools ));
$stmt = $MASHPIA_DB->query("
    SELECT 
        s.school_name, ls.*
    FROM
        lulav_settings ls 
    JOIN
        schools s using (school_id) 
    WHERE
        year = $year
");
$info = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mivtzoim Settings</title>
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
        <h1>Mivtza Lulav Settings</h1>
        <table>
            <tr>
                <th>School ID</th>
                <th>School</th>
                <th>Part of Mivtza</th>
                <th>Shipping Fee</th>
            </tr>
            <?php
            foreach ( $info as $row ) {
                echo "<tr><td>" . $row['school_id'] . "</td><td>" . $row['school_name'] . "</td><td>";
                if ( intval( $row['allow_lulav'] ) ) echo "yes";
                else echo "no";
                echo "</td><td>" . $row['lulav_shipping'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>