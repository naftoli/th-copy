<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();
$year = GlobalSettings::getChidonYear();

$info = [];
foreach ($schools as $school_id => $name) {
    $sql = "select u.user_serial, u.first, u.last, c.class_grade, c.class_sub, tc.test_type, tc.reward_type 
            from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            join th_chidon tc using (user_id) 
            where tc.year = $year  
            and u.school_id = " . $school_id;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $info[$school_id][$grade][] = $row; 
    }
}

$fields = [
    'Serial'                    => 'user_serial', 
    'Name'                      => ['first', 'last'], 
    'Class'                     => ['class_grade', 'class_sub'], 
    'Track Chosen'              => 'test_type', 
    'Track Passed'              => 'calc-tpassed', 
    'Learning Days Passed'      => 'calc-lpassed', 
    'Days Logged'               => 'calc-dlogged', 
    'Learning Minutes Required' => 'calc-mrequired', 
    'Learning Minutes Logged'   => 'calc-mlogged', 
    'Minutes Behind'            => 'calc-behind', 
    'Minutes Ahead'             => 'calc-ahead'
]
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Limmud Report</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 6px;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Limmud Report</h1>
        <?php foreach ($schools as $school_id => $name) : ?>
            <?= "<h2>" . $name . "</h2>"; ?>
            <table>
                <tr>
                    <?php foreach ($fields as $desc => $field) echo "<th>" . $desc . "</th>"; ?>
                </tr>
                <?php
                if (isset($info[$school_id])) {
                    foreach ($info[$school_id] as $more) {
                        foreach ($more as $grade => $rows) {
                            foreach ($rows as $row) {
                                foreach ($fields as $field) {

                                }
                            }
                        }
                    }
                }
                ?>
            </table>
        <?php endforeach; ?>
    </body>
</html>