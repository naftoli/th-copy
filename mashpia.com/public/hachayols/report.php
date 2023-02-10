<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

// first get all admins for all children in each school
$sqlAdmins = "select a.* from admins a 
                join admin_auths aa using (admin_id) 
                join users u on u.user_id = aa.id 
                where u.user_registered > 0 
                and u.school_id = :school";
$stmtAdmins = $MASHPIA_DB->prepare($sqlAdmins);

// then get all users per admin
$sqlUsers = "select user_id, school_id, hachayol, first from users u 
            join admin_auths aa on u.user_id = aa.id 
            where u.user_registered > 0 and aa.admin_id = :id 
            order by u.dob";
$stmtUsers = $MASHPIA_DB->prepare($sqlUsers);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Hachayol Report</title>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="../scripts/jquery-1.8.3.js"></script>
        <style>
          table {
            font-size: 12px;
          }
          th, td {
            padding: 3px 10px;
          }
        </style>
    </head>
    <body>
        <?php include('../admin_header.php'); ?>
        <h1>Hachayol Report</h1>
        <?php foreach ($schools as $school_id => $school_name) : ?>
            <?= "<h2>" . $school_name . "</h2>" ?>
            <table>
                <tr>
                    <th>Family ID</th>
                    <th>Family</th>
                    <th>Children/Hachayol</th>
                </tr>
                <?php
                $info = [];
                $stmtAdmins->execute(['school' => $school_id]);
                $admins = $stmtAdmins->fetchAll();
                foreach ($admins as $admin) {
                    $stmtUsers->execute(['id' => $admin['admin_id']]);
                    $children = $stmtUsers->fetchAll();
                    echo "<tr><td>" . $admin['admin_id'] . "</td><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>";
                    foreach ($children as $child) {
                        echo "<input type='checkbox' name='hachayol[" . $child['user_id'] . "]' class='hachayol' id='" . $child['user_id'] . "'";
                        if ($child['school_id'] != $school_id) echo " disabled";
                        echo " />";
                        echo $child['first'] . " (" . $schools[$child['school_id']] . ")<br />";
                    }
                    echo "</td></tr>";
                }
                ?>
            </table>
        <?php endforeach; ?>
    </body>
</html>