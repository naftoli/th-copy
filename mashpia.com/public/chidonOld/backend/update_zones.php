<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/chidon_drive/classes/WalkingZones.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM th_chidon WHERE year = :year AND (khk = 1 or school_rep = 1 or trophy_contestant = 1 or contestant = 1)
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll();

$qrys = [];
$errors = [];
foreach ( $rows as $row ) {
    if ( intval($row['in_zone']) ) {
        $w = new WalkingZones;
        $num = $row['host_street_num'];
        $street = $row['host_street'];
        $info = $w->getCrossStreets( $street, $num );
        if ( is_array( $info ) && $info['zone_5780'] != $row['walking_zone'] ) {
            $qrys[$row['th_chidon_id']] = $info['zone_5780'];
        } else if ( !is_array( $info ) ) {
            $errors[] = $row;
        }
    }
}

$stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon SET walking_zone = :zone WHERE th_chidon_id = :id
");
foreach ( $qrys as $id => $zone ) {
    $stmt->execute([':zone' => $zone]);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
        <table>
            <tr>
                <th>Admin ID</th>
                <th>User ID</th>
                <th>Host Street Number</th>
                <th>Host Street</th>
                <th>Walking Zone</th>
            </tr>
            <?php
            foreach ( $errors as $row ) {
                echo "<tr><td>" . $row['parent_id'] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['host_street_num'] . "</td><td>" . 
                    $row['host_street'] . "</td><td>" . $row['walking_zone'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>