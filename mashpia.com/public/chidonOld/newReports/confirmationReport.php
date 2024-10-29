<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$adminSchools = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $adminSchools->getSchools();

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        u.user_id,
        u.user_serial,
        u.first,
        u.last,
        c.class_grade,
        c.class_sub,
        s.school_name,
        tc.confirmed_info
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            JOIN
        schools s ON u.school_id = s.school_id
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        tc.year = :year AND u.school_id = :school 
    ORDER BY c.class_grade, c.class_sub, u.last, u.first
");

$info = [];
foreach ($schools as $school_id => $school_name) {
    $stmt->execute([':year' => $year, ':school' => $school_id]);
    $info[$school_name] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirmation Report</title>
    <style>
      table {
        font-family: Arial, sans-serif;
        font-size: 14px;
      }

      tr, th, td {
        border-bottom: #f0f0f0 1px solid;
        padding: 10px;
      }
    </style>
</head>
<body>
    <h1>Confirmation Report</h1>
    <?php
    foreach ($info as $school_name => $users) {
        echo "<h2>$school_name</h2>";
        echo "<table>";
        echo "<tr><th>Grade/Class</th><th>Serial</th><th>Name</th><th>Confirmed Info</th></tr>";
        foreach ($users as $user) {
            $grade = $user['class_grade'] . ($user['class_sub'] ? " - " . $user['class_sub'] : "");
            ?>
            <tr>
                <td><?= $grade ?></td>
                <td><?= $user['user_serial'] ?></td>
                <td><?= $user['first'] . " " . $user['last'] ?></td>
                <td><?= $user['confirmed_info'] ? 'Yes': 'No' ?></td>
            </tr>
        <?php } ?>
        </table>
    <?php } ?>
</body>
</html>
