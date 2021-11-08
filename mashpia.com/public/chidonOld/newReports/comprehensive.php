<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "SELECT 
            u.user_id, u.first, u.last, u.user_serial, s.school_id, s.school_name, c.class_grade, c.class_sub, tc.th_chidon_id, tc.khk_reg
        FROM
            th_chidon tc
                JOIN
            users u USING (user_id)
                JOIN
            schools s ON s.school_id = tc.school_id
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            year = $year AND tc.school_id IN (" . implode(',', array_keys($schools)) . ")";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

foreach ($info as $idx => $user) {
    $marks = [];
    $sql = "SELECT 
                *
            FROM
                th_chidon_marks
            WHERE
                th_chidon_id = " . $user['th_chidon_id'];
    $result = mysql_query($sql);
    while ($row = mysql_query($sql)) {
        $marks[] = $row;
    }
    $info[$idx]['marks'] = $marks;
}

echo "<pre>"; print_r($info); echo "</pre>";
