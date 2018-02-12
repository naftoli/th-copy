<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Student Accounts</title>
<style>
table {
	width: 100%;
}
tr, th, td {
	vertical-align: text-top;
	border: 1px solid black;
	padding: 2px;
}
</style>
</head>

<body>
<? include('admin_header.php');?>

<h1>Student Accounts</h1>
<table border="1" cellspacing="1" style="font-size:12px">
<tr>
<th>First Name</th>
<th>Last Name</th>
<th>Username</th>
<th>Password</th>
<th>Email</th>
<th>Grade</th>
<th>Assigned To</th>
</tr>
<?

require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$schoolIDs = array();
foreach ($schools as $id => $school) {
    $schoolIDs[] = $id;
}
     
//get list of parents
include_once('db.php');
$sql = "
SELECT DISTINCT a.admin_id, a.username, a.password, a.first, a.last, a.admin_email, a.admin_address1, a.admin_city, a.admin_country, c.class_grade, c.class_sub, ut.subject_id    
FROM admins AS a 
join admin_auths aa using (admin_id)
join users u on aa.id = u.user_id
join classes c on u.class_id = c.class_id
join user_tracks ut on u.user_id = ut.user_id
join schools s on s.school_id = u.school_id 
WHERE aa.auth =  'user'
and u.school_id in (" . implode(',', $schoolIDs) . ") 
and u.heb_year = 5777 
order by a.last";
//echo $sql;
$result = mysql_query($sql) or die(mysql_error());

while ($row = mysql_fetch_assoc($result)) {
	switch ($row['subject_id']) {
		case 2:
			$type = "Hoo";
			break;
		case 3:
			$type = "FC";
			break;
		case 4:
			$type = "Personal";
			break;
	}
	echo "
	<tr><td>$row[first]</td>
	<td>$row[last]</td>
	<td>$row[username]</td>
	<td>$row[password]</td>
	<td>$row[admin_email]</td>
	<td>$row[class_grade]-$row[class_sub]</td>
	<td>$type</td>
	</tr>";	
}
?>
</table>
</body>
</html>
