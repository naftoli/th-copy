<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>List of Parents</title>
<style>
  body, table {
    font-family: Arial, Helvetica, sans-serif;
  }
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
<h1>List of Parents</h1>
<table border="1" cellspacing="1" style="font-size:12px">
<tr>
<th>Parent Info</th>
<th>Address</th>
<th>Children</th>
<? if ($admin_user['auth'] == 'super' || $admin_user['admin_id'] == 60) : ?>
<th>School</th>
<? endif; ?>
</tr>
<?
require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();
$schoolIDs = array();
foreach ($schools as $id => $school) {
    $schoolIDs[] = $id;
}
        
//get list of parents
include_once('db.php');
$sql = "
SELECT DISTINCT a.admin_id, a.username, a.password, a.first, a.last, a.admin_address1, a.admin_city, a.admin_state, a.admin_postal, a.admin_country, 
                a.admin_email, a.admin_phone_mobile, a.admin_phone_mobile2 FROM admins AS a 
join admin_auths aa using (admin_id)  
WHERE aa.auth =  'user' 
and aa.id in (
select user_id from users where school_id in ( " . implode(',', $schoolIDs) . " )
)
order by a.last";
//echo $sql;
$result = mysql_query($sql) or die(mysql_error());
//echo mysql_num_rows($result);

$total = 0;
$number = 1;
while ($row = mysql_fetch_assoc($result)) {
	$total++;
	$address = $row['admin_address1'] . "<br />" . $row['admin_city'] . ", " . $row['admin_state'] . " " . $row['admin_postal'] . "<br />" . $row['admin_country'];
	echo "
	<tr><td>
	<strong>Parent Account ID: $row[admin_id]</strong><br />
	First: <strong>$row[first]</strong><br />
	Last: <strong>$row[last]</strong><br />
	Username: $row[username]<br />
	Password: $row[password]<br />
	Email: $row[admin_email]<br />
	Cell: $row[admin_phone_mobile]<br />
	Cell 2: $row[admin_phone_mobile2]
	</td><td>$address</td>";
	
	$sql2 = "select u.first, u.last, u.user_serial, s.school_name 
			from users u, admin_auths aa, schools s  
			where u.user_id = aa.id 
			and s.school_id = u.school_id 
			and admin_id = " . $row['admin_id'];
	//echo $sql2;
	$result2 = mysql_query($sql2);
	echo "<td>";
	while ($row2 = mysql_fetch_assoc($result2)) {
		//echo $row2['first'] . " (" . $row2['user_serial'] . "),<br />";
		echo $row2['first'] . ' ' . $row2['last'];
        if ($admin_user['admin_id'] == 60)
            echo ' (' . $row2['user_serial'] . ")";
        echo ",<br />";
	}
	if ($admin_user['auth'] == 'super' || $admin_user['admin_id'] == 60) {
	    echo "</td><td>";
    	if (mysql_num_rows($result2) > 0) 
    		mysql_data_seek($result2, 0);
    	while ($row2 = mysql_fetch_assoc($result2)) {
    		echo $row2['school_name'] . ",<br />";
    	}
    }
	echo "</td></tr>";	
}
?>
</table>
<?//=$total?>
</body>
</html>
