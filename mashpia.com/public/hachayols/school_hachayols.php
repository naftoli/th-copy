<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("
    select u.*, c.*, aa.admin_id from users u 
    join admin_auths aa on aa.id = u.user_id 
    join classes c using (class_id) 
    where u.school_id = :id
    order by class_grade, class_sub, last, first
");

$users = [];
foreach ($schools as $id => $name) {
    $stmt->execute(['id' => $id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $users[$id][$row['class_grade']][$row['class_sub']][] = $row;
    }
}

$stmt = $MASHPIA_DB->prepare("
    select u.* from users u 
    join admin_auths aa on aa.id = u.user_id 
    where u.hachayol = 1 
    and u.user_registered > 0 
    and aa.admin_id = :admin_id
");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Hachayol Report</title>
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            }
        </style>
    </head>
    <body>
    <?php
    foreach ($users as $school_id => $more) {
        foreach ($more as $class_grade => $other) {
            foreach ($other as $class_sub => $more) {
                $grade = $class_grade . ($class_sub ? '-' . $class_sub : '');
                echo "<h3>" . $schools[$school_id] . " - " . $grade . "</h3><hr />";
                ?>
                <table>
                    <tr>
                        <th>Hebrew Name</th>
                        <th>Student</th>
                        <th>Family ID</th>
                        <th>Receives Hachayol</th>
                        <th>Who gets Hachayol in Family</th>
                    </tr>
                <?php
                foreach ($more as $user) {
                    $children = [];
                    $receives_hachayol = intval($user['hachayol']) ? 'yes' : 'no';
                    if ($receives_hachayol == 'no') {
                      // find out which child(ren) do get it
                      $stmt->execute(['admin_id' => $user['admin_id']]);
                      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      foreach ($rows as $row) {
                        $children[] = $row['first'] . ' ' . $row['last'] . ' (' . $schools[$row['school_id']] . ')';
                      }
                    }
                    echo "<tr><td>" . $user['first_he'] . ' ' . $user['last_he'] . "</td><td>" . $user['first'] . ' ' . $user['last'] .
                        "</td><td>" . $user['admin_id'] . "</td><td>" . $receives_hachayol . "</td><td>" .
                        implode("<br />", $children) . "</td></tr>";
                }
            }
            echo "<div style='page-break-after:always;'></div>";
        }
        echo "</table>";
    }
    ?>
    </body>
</html>
