<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// report for next year eligibility including serial number, name, school
// only show kids that are eligible
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$year = 5784;
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$children = [];
$sql = "select user_id, user_serial, first, last, u.school_id, school_name, class_grade, class_sub  
        from users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        where u.user_registered > 0 
        and c.class_grade = '7'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['user_id']] = $row;
}

$eligible = ChidonTests::isEligible(array_keys($children), $year, 2);
echo "<pre>"; print_r($eligible); echo "</pre>"; exit;