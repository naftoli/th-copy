<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>List of Parents</title>
<style>
table {
	width: 100%;
}
th, td {
	border: 1px solid black;
	vertical-align: text-top;
	padding: 6px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>

<h1>List of Parents</h1>
<table border="1" cellspacing="1" style="font-size:12px">
<tr>
<th>Parent Info</th>
<th>Type of Account</th>
<th>Children</th>
<? if ($admin_user['auth'] == 'super' || $admin_user['admin_id'] == 60) : ?>
<th>School</th>
<? endif; ?>
</tr>
<?        
//get list of parents
include_once('db.php');
$sql = "
SELECT DISTINCT a.admin_id, a.username, a.password, a.first, a.last, a.admin_address1, a.admin_city, a.admin_country, a.admin_email, a.admin_phone_mobile,
a.admin_phone_mobile2, aa.auth FROM admins AS a 
left join admin_auths aa using (admin_id)  
order by a.last";
//echo $sql;
$result = mysql_query($sql) or die(mysql_error());
//echo mysql_num_rows($result);

$total = 0;
while ($row = mysql_fetch_assoc($result)) {
	$total++;
	echo "
	<tr><td>
	<strong>Admin/Parent Account ID: $row[admin_id]</strong><br />
	First: <strong>$row[first]</strong><br />
	Last: <strong>$row[last]</strong><br />
	Username: $row[username]<br />
	Password: $row[password]<br />
	Email: $row[admin_email]<br />
	Cell: $row[admin_phone_mobile]<br />
	Cell 2: $row[admin_phone_mobile2]</td>";
	
    if ($row['auth'] == 'user') {
        echo "<td>Parent Account</td>";
        $sql2 = "select u.first, u.last, u.user_serial, s.school_name 
                from users u 
                join admin_auths aa on aa.id = u.user_id 
                left join schools s using (school_id) 
                where aa.admin_id = " . $row['admin_id'];
        //echo $sql2;
        $result2 = mysql_query($sql2);
        echo "<td>";
		if (mysql_num_rows($result2) == 0) echo "</td><td>";
        while ($row2 = mysql_fetch_assoc($result2)) {
            //echo $row2['first'] . " (" . $row2['user_serial'] . "),<br />";
            echo $row2['first'] . ' ' . $row2['last'];
            echo ",<br />";
            echo "</td><td>";
            if (mysql_num_rows($result2) > 0) 
                mysql_data_seek($result2, 0);
            while ($row2 = mysql_fetch_assoc($result2)) {
                echo $row2['school_name'] . ",<br />";
            }
        }
    } else if ($row['auth'] == 'class') {
        echo "<td>Teacher Account</td><td></td><td>";
    } else if ($row['auth'] == 'school') {
        echo "<td>School Staff</td><td></td><td>";
    } else {
        echo "<td></td><td></td><td>";
    }
	echo "</td></tr>";	
}
?>
</table>
<?//=$total?>
</body>
</html>
