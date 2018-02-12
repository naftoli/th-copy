<?php
$admin_auth = array('school');
require 'header.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Teachers</title>
<style>
table {
	width: 100%;
}
th, td {
	border: 1px solid black;
	vertical-align: text-top;
	padding: 6px;
    font-size: 12px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<h1>Teacher Logins</h1>
<?
$teachers = array();
require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
foreach ($schools as $id => $school) {
    $sql = "select class_id, class_grade, class_sub 
			from classes
			where school_id = " . $id . "
			and class_era = 0
			order by class_grade, class_sub";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $sql2 = "select a.* from admins a 
                join admin_auths aa using (admin_id)
                where aa.id = " . $row['class_id'] . " and aa.auth = 'class'";
        $result2 = mysql_query($sql2);
        $row2 = mysql_fetch_assoc($result2);
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $row2['email'] = $row2['admin_email'];
        $row2['cell'] = $row2['admin_phone_mobile'];
		$id = $row2['admin_id'];
		if (!$id) $id = 'class' . $row['class_id'];
        $teachers[$school][$id][$grade] = $row2;
    }    
}

foreach ($teachers as $school => $info) {
    ?>
    <table>
        <caption><?=$school?></caption>
        <tr>
            <th>Teacher</th>
            <th>Grade</th>
            <th>Username</th>
            <th>Password</th>
            <th>Email</th>
            <th>Cell Phone</th>
        </tr>
        <?
        foreach ($info as $class_id => $other) {
			foreach ($other as $grade => $row) {
				echo "<tr id=" . $class_id . "><td>" . $row['last'] . "</td><td>" .	$grade . "</td><td>" . $row['username'] . "</td><td>" .
                    $row['password'] . "</td><td>" . $row['email'] . "</td><td>" . $row['cell'] . "</td></tr>";
			}
		}
        ?>
    </table>
	<p></p>
<? } ?>
</body>
</html>

