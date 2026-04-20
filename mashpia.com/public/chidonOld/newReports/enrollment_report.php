<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$chidonYr = GlobalSettings::getChidonRegYear();
$year = intval($_REQUEST['year'] ?? $chidonYr);

$startGrade = 3;
$endGrade = 8;
if ($chidonYr > $year) {
    $startGrade--;
    $endGrade--;
}

$gradeList = [];
for ($grade = $startGrade; $grade <= $endGrade; $grade++) {
    $gradeList[] = '"' . $grade . '"';
}

$enrollmentBySchool = [];
if (!empty($schools)) {
    $sql = "
        SELECT
            u.first, 
            u.last,
            u.user_serial,
            u.school_id,
            c.class_grade,
            c.class_sub,
            tc.reg_date, 
            aa.admin_id 
        FROM th_chidon tc
            JOIN users u ON u.user_id = tc.user_id
            JOIN classes c ON c.class_id = u.class_id
            JOIN admin_auths aa ON aa.id = u.user_id 
        WHERE
            tc.year = " . intval($year) . "
            AND c.class_grade in (" . implode(',', $gradeList) . ")
            AND u.school_id in (" . implode(',', array_keys($schools)) . ")
        ORDER BY u.school_id, c.class_grade, c.class_sub, u.last, u.first
    ";

    // echo $sql; exit;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $enrollmentBySchool[$row['school_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Enrollment Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Enrollment Report</h1>
<select name="year" id="year">
    <?php
    for ($y = $chidonYr; $y >= 5782; $y--) {
        echo "<option value='$y'" . ($y == $year ? ' selected' : '') . ">$y</option>";
    }
    ?>
</select>
<br/><br/>
<div id="reg">
    <?php foreach ($enrollmentBySchool as $schoolId => $rows) { ?>
        <h2><?= $schools[$schoolId] ?></h2>
        <table>
            <tr>
                <th>Family ID</th>
                <th>Grade</th>
                <th>Student</th>
                <th>Serial #</th>
                <th>Date Enrolled</th>
            </tr>
            <?php foreach ($rows as $user) {
                $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
                ?>
                <tr>
                    <td><?= $user['admin_id'] ?></td>
                    <td><?= $grade ?></td>
                    <td><?= $user['first'] . ' ' . $user['last'] ?></td>
                    <td><?= $user['user_serial'] ?></td>
                    <td><?= $user['reg_date'] ?></td>
                </tr>
            <?php } ?>
        </table>
        <div class="page-break"></div>
    <?php } ?>
</div>
</body>
<script>
    document.getElementById('year').addEventListener('change', function () {
        window.location.href = 'enrollment_report.php?year=' + this.value;
    });
</script>
</html>