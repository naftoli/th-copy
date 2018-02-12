<?
$admin_auth = array('school'); 
require('header.php');

$info = array();
$admins = array();
$users = array();
$sql = "select ml.*, a.first, a.last, u.first as ufirst, u.last as ulast, s.school_name  
		from mobile_logins ml 
		join admins a using (admin_id) 
		join users u using (user_id) 
		join schools s using (school_id) 
		order by school_name, a.last, a.first, u.last, u.first, ml.date";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['school_name']][$row['admin_id']][$row['user_id']][] = $row['date'];
	$admins[$row['admin_id']] = $row['first'] . ' ' . $row['last'];
	$users[$row['user_id']] = $row['ufirst'] . ' ' . $row['ulast'];
}
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mobile Usage Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 10px;
                vertical-align: top;
                border-right: 1px solid blue;
            }
            tr:nth-child(even) {
            	background: #F0F0F0;
            }
            tr:first-child {
            	border-bottom: 1px solid blue;
            	border-top: 1px solid blue;
            }
            th:first-child, td:first-child {
            	border-left: 1px solid blue;
            }
            tr:last-child {
            	border-bottom: 1px solid blue;
            }
            td.date {
            	width: 120px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Mobile Usage Report</h1>
        
        <table>
        	<tr>
        		<th>School</th>
        		<th>Parent</th>
        		<th>Chayol</th>
        		<th>Time(s)</th>
        	</tr>
        	<?
        	foreach ($info as $school => $other) {
	        	foreach ($other as $admin => $more) {
	        		foreach ($more as $user => $rest) {
	        			echo "<tr><td>" . $school . "</td><td>" . $admins[$admin] . "</td><td>" . $users[$user] . 
	        				"</td><td class='date'>";
	        			foreach ($rest as $date) {
	        				echo $date . "<br />";
	        			}
						echo "</td></tr>";
	        		}
	        	}
	        }
        	?>
        </table>
        <?
        
        
