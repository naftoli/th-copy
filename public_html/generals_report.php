<?
$admin_auth = array('school'); 
require('header.php');

$users = array();
$sql = "select u.first, u.last, u.user_registered, s.school_name, c.class_grade, c.class_sub, r.rank_name 
		from users u 
		join schools s using (school_id) 
		left join classes c on (u.class_id = c.class_id) 
		join rank_marks rm using (user_id) 
		join ranks r using (rank_ord) 
		where rm.rank_ord >= 9
		order by rm.rank_ord, s.school_name, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Generals Report</title>
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
        <h1>Generals Report</h1>
        
        <table>
        	<tr>
        		<th>Rank</th>
        		<th>Name</th>
        		<th>School</th>
        		<th>Grade</th>
        		<th>Registered</th>
        	</tr>
        	<?
        	foreach ($users as $user) {
        		echo "<tr><td>" . $user['rank_name'] . "</td><td>" . $user['first'] . ' ' . $user['last'] . 
        			"</td><td>" . $user['school_name'] . "</td><td>" . $user['class_grade'] . '-' . 
        				$user['class_sub'] . "</td><td>" . $user['user_registered'] . "</td></tr>";
        	}
        	?>
        </table>
	</BODY>
</HTML>