<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

// info on all schools
$sqlSchools = "select school_id, school_name from schools";
$stmtSchools = $MASHPIA_DB->query($sqlSchools);
$rows = $stmtSchools->fetchAll();
foreach ($rows as $row) {
    $all_schools[$row['school_id']] = $row['school_name'];
}

// first get all admins for all children in each school
$sqlAdmins = "select a.* from admins a 
                join admin_auths aa using (admin_id) 
                join users u on u.user_id = aa.id 
                where u.user_registered > 0 
                and u.school_id = :school 
                group by admin_id 
                order by a.last, a.first";
$stmtAdmins = $MASHPIA_DB->prepare($sqlAdmins);

// then get all users per admin
$sqlUsers = "select user_id, u.school_id, hachayol, first, c.class_grade, c.class_sub from users u 
            join classes c on c.class_id = u.class_id 
            join admin_auths aa on u.user_id = aa.id 
            where u.user_registered > 0 and aa.admin_id = :id 
            order by u.dob";
$stmtUsers = $MASHPIA_DB->prepare($sqlUsers);

// get users that don't have an admin account
$sqlMissing = "select user_id, u.school_id, hachayol, first, last, c.class_grade, c.class_sub from users u 
                join classes c on c.class_id = u.class_id 
                left join admin_auths aa on aa.id = u.user_id 
                where u.user_registered > 0 
                and aa.admin_id is null 
                and u.school_id = :school";
$stmtMissing = $MASHPIA_DB->prepare($sqlMissing);
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
            border-bottom: 1px solid grey;
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
                    // find out if hachayol child is in this school or not
                    $disable = false;
                    foreach ($children as $child) {
                        if ($child['hachayol'] && $child['school_id'] != $school_id) {
                            $disable = true;
                            break;
                        }
                    }

                    echo "<tr><td>" . $admin['admin_id'] . "</td><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>";
                    foreach ($children as $child) {
                        // find out child's school / grade
                        $school = $all_schools[$child['school_id']];
                        $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);

                        echo "<input type='radio' name='hachayol[" . $admin['admin_id'] . "]' class='hachayol' id='" . $child['user_id'] . "'";
                        if ($child['hachayol']) echo " checked";
                        if ($disable || $child['school_id'] != $school_id) echo " disabled";
                        echo " />";
                        echo $child['first'] . " (" . $school . ' : ' . $grade . ")<br />";
                    }
                    echo "</td></tr>";
                }
                // find kids with missing parent account
                $stmtMissing->execute(['school' => $school_id]);
                $missing = $stmtMissing->fetchAll();
                foreach ($missing as $idx => $child) {
                    $school = $all_schools[$child['school_id']];
                    $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);

                    echo "<tr><td colspan='2'></td><td>";
                    echo "<input type='radio' name='hachayol[" . ($idx + 1) . "]' class='hachayol' id='" . $child['user_id'] . "'";
                    if ($child['hachayol']) echo " checked";
                    echo " />";
                    echo $child['first'] . ' ' . $child['last'] . " (" . $school . ' : ' . $grade . ")</td></tr>";
                }
                ?>
            </table>
        <?php endforeach; ?>
    </body>
</html>