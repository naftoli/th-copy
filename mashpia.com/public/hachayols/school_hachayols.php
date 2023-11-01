<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

require_once $_SERVER['DOCUMENT_ROOT'] . 'class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . 'class.schoolsUsers.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("
    select * from users u 
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
        echo "<h2>" . $schools[$school_id] . "</h2><hr />";
        ?>
        <table>
            <tr>
                <th>Grade</th>
                <th>Hebrew Name</th>
                <th>Student</th>
                <th>Family ID</th>
                <th>Receives Hachayol</th>
                <th>Who gets Hachayol in Family</th>
            </tr>
        <?php
        foreach ($more as $class_grade => $other) {
            foreach ($other as $class_sub => $more) {
                foreach ($more as $user) {
                    echo "<tr><td>" . $class_grade . "-" . $class_sub . "</td><td>" .
                        $user['first_he'] . ' ' . $user['last_he'] . "</td><td>" .
                        $user['first'] . ' ' . $user['last'] . "</td><td>" .
                        "</td><td>" . "</td><td>" . "</td></tr>";
                }
            }
        }
        echo "</table>";
    }
    ?>
    </body>
</html>
