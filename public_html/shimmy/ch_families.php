<?php
ini_set('display_errors',1);
$admin_auth = array('school');
require '../header.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CH Families</title>
<style>
th, td {
	padding: 5px;
    font-size: 12px;
    font-family: Arial;
}
</style>
</head>

<body>

<h1>CH Families</h1>
<table>
    <tr>
        <th>Family Name</th>
        <th>Address</th>
        <th>Phone Number</th>
        <th>Email address</th>
        <th>Total number of kids in school</th>
    </tr>
<?php
$schoolIDs = array(54,30,7,33,63,194,9,471,255);
        
//get list of parents
//require('../db.php');
$sql = "
SELECT DISTINCT a.admin_id, a.username, a.password, a.first, a.last, a.admin_address1, a.admin_email, a.admin_phone_mobile, a.admin_phone_mobile2, 
a.admin_phone_work, a.admin_phone_home  
FROM admins AS a 
join admin_auths aa using (admin_id)  
WHERE a.last != '' 
and aa.auth =  'user' 
and aa.id in (
select user_id from users where school_id in ( " . implode(',', $schoolIDs) . " )
)
order by a.last";
$result = mysql_query($sql) or die(mysql_error());

$totals = array();
while ($row = mysql_fetch_assoc($result)) {
    echo "<tr><td>" . $row['last'] . "</td><td>" . $row['admin_address1'] . "</td><td>" . 
        ($row['admin_phone_mobile'] ? $row['admin_phone_mobile'] : ($row['admin_phone_mobile2'] ? $row['admin_phone_mobile2'] : 
        ($row['admin_phone_work'] ? $row['admin_phone_work'] : ($row['admin_phone_home'] ? $row['admin_phone_home'] : '')))) . "</td><td>" . $row['admin_email'] . "</td><td>";
    
    $sql2 = "select count(id) as total from admin_auths aa 
            join users u on u.user_id = aa.id 
            where aa.auth = 'user' 
            and u.school_id in ( " . implode(',', $schoolIDs) . " ) 
            and admin_id = " . $row['admin_id'];
    $result2 = mysql_query( $sql2 );
    $row2 = mysql_fetch_assoc( $result2 );
    $number_of_children = $row2['total'];

    if (isset( $totals[$number_of_children] )) $totals[$number_of_children]++;
    else $totals[$number_of_children] = 1;

    echo $number_of_children;
    echo "</td></tr>";
}
ksort( $totals );
?>
</table>
<h2>Totals</h2>
<table>
    <tr>
        <th>Number of Children</th>
        <th>Total</th>
    </tr>
    <?php
    foreach ($totals as $num => $total) {
        echo "<tr><td>" . $num . "</td><td>" . $total . "</td></tr>";
    }
    ?>
</table>
</body>
</html>