<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$super = $admin_user['auth'] == 'super';

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
            join user_registration ur on ur.user_id = u.user_id 
            where u.user_registered > 0 
            and u.school_id = :school 
            and ur.year = :year 
            group by admin_id 
            order by a.last, a.first";
$stmtAdmins = $MASHPIA_DB->prepare($sqlAdmins);

if ($year < 5786) {
    // then get all users per admin
    $sqlUsers = "select u.user_id, u.school_id, hachayol, first, c.class_grade, c.class_sub, ur.reg_date from users u 
                join classes c on c.class_id = u.class_id 
                join admin_auths aa on u.user_id = aa.id 
                left join user_registration ur on ur.user_id = u.user_id 
                where u.user_registered > 0 and aa.admin_id = :id 
                and ur.year = :year 
                order by u.dob";
    $stmtUsers = $MASHPIA_DB->prepare($sqlUsers);

    // get users that don't have an admin account
    $sqlMissing = "select u.user_id, u.school_id, hachayol, first, last, c.class_grade, c.class_sub, ur.reg_date from users u 
                    join classes c on c.class_id = u.class_id 
                    left join admin_auths aa on aa.id = u.user_id 
                    left join user_registration ur on ur.user_id = u.user_id 
                    where u.user_registered > 0 
                    and aa.admin_id is null 
                    and u.school_id = :school 
                    and ur.year = :year";
    $stmtMissing = $MASHPIA_DB->prepare($sqlMissing);
} else {
    // then get all users per admin
    $sqlUsers = "select u.user_id, u.school_id, first, c.class_grade, c.class_sub, ur.reg_date, u.hachayol as hachayol_status,  
                  IF(htg.user_id IS NOT NULL, 1, 0) as hachayol 
                from users u 
                join classes c on c.class_id = u.class_id 
                join admin_auths aa on u.user_id = aa.id 
                left join user_registration ur on ur.user_id = u.user_id 
                left join hachayols_to_give htg on htg.user_id = u.user_id and htg.year = ur.year 
                where u.user_registered > 0 and aa.admin_id = :id 
                and ur.year = :year 
                order by u.dob";
    $stmtUsers = $MASHPIA_DB->prepare($sqlUsers);

    // get users that don't have an admin account
    $sqlMissing = "select u.user_id, u.school_id, first, last, c.class_grade, c.class_sub, ur.reg_date, u.hachayol as hachayol_status,  
                      IF(htg.user_id IS NOT NULL, 1, 0) as hachayol
                    from users u 
                    join classes c on c.class_id = u.class_id 
                    left join admin_auths aa on aa.id = u.user_id 
                    left join user_registration ur on ur.user_id = u.user_id 
                    left join hachayols_to_give htg on htg.user_id = u.user_id and htg.year = ur.year 
                    where u.user_registered > 0 
                    and aa.admin_id is null 
                    and u.school_id = :school 
                    and ur.year = :year";
    $stmtMissing = $MASHPIA_DB->prepare($sqlMissing);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hachayol Family Report</title>
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
<h1>Hachayol Family Report</h1>
<?php $j = []; // for id in input of children ?>
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
        $stmtAdmins->execute([
            'school' => $school_id,
            'year' => $year
        ]);
        $admins = $stmtAdmins->fetchAll();
        foreach ($admins as $admin) {
            if (isset($j[$admin['admin_id']])) $j[$admin['admin_id']]++;
            else $j[$admin['admin_id']] = 1;
            $stmtUsers->execute([
                'id' => $admin['admin_id'],
                'year' => $year
            ]);
            $children = $stmtUsers->fetchAll();
//          if (!$children) $stmtUsers->debugDumpParams();
            // find out if hachayol child is in this school or not
            $disable = false;
            foreach ($children as $child) {
                if (!$super && $child['hachayol'] == 1 && $child['school_id'] != $school_id) {
                    $disable = true;
                    break;
                }
            }

            echo "<tr><td>" . $admin['admin_id'] . "</td><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>";
            if (!$children) {
                echo "No children registered for this year";
            }
            foreach ($children as $i => $child) {
                // find out child's school / grade
                $school = $all_schools[$child['school_id']];
                $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                $id = $admin['admin_id'] . ':' . $j[$admin['admin_id']];
                echo "<input type='checkbox' name='hachayol[$id]' class='hachayol' id='" . $child['user_id'] . "'";
                if ($child['hachayol'] == 1 && $child['reg_date']) echo " checked";
                echo " disabled";
                echo " />";
                echo $child['first'] . " (" . $school . ' : ' . $grade . ")<br />";
            }
            echo "</td></tr>";
        }
        // find kids with missing parent account
        $stmtMissing->execute([
            'school' => $school_id,
            'year' => $year
        ]);
        $missing = $stmtMissing->fetchAll();
        foreach ($missing as $idx => $child) {
            $school = $all_schools[$child['school_id']];
            $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
            $id = $idx + 1;
            echo "<tr><td colspan='2'>No Parent Account</td><td>";
            echo "<input type='checkbox' name='hachayol[$id]' class='hachayol' id='" . $child['user_id'] . "'";
            if ($child['hachayol'] == 1 && $child['reg_date']) echo " checked";
            echo " />";
            echo $child['first'] . ' ' . $child['last'] . " (" . $school . ' : ' . $grade . ")</td></tr>";
        }
        ?>
    </table>
<?php endforeach; ?>
</body>
</html>