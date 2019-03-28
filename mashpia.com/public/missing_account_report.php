<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Missing Account Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Missing Account Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $missing = array();
        
        foreach ( $schools as $id => $school ) {
			$users = array();
			$sql = "select first, last, class_grade, class_sub, school_name, dob, user_registered from users u 
					join schools s using (school_id) 
					join classes c on c.class_id = u.class_id 
					left join admin_auths aa on aa.id = u.user_id 
					where u.user_registered > 0 
					and u.school_id = " . $id . " 
					and aa.id is null 
					order by last, first";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$users[] = $row;
			}
            $missing[$id] = $users;
        }
        
        foreach ( $missing as $school => $users ) {
        	echo "<h2>" . $schools[$school] . "</h2>";
        	?>
        	<table>
				<tr>
					<td><strong>School</strong></th>
					<td><strong>Grade</strong></th>
					<td><strong>Student</strong></th>
					<td><strong>DOB</strong></td>
				</tr>
				<tr>
					<?php
					foreach ($users as $user) {
						$school = $user['school_name'];
						$grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
						$name = $user['first'] . ' ' . $user['last'];
						echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" .
						$name . "</td><td>" . $user['dob'] . "</td></tr>";
					}
					?>
				</tr>
			</table>
        <? } ?>
    </body>
</html>