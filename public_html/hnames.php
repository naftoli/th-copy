<?php
ini_set('display_errors', TRUE);
$admin_auth = array('school'); 
require('header.php'); 

$users = array();
$sql = "select user_id, first, last, first_he, last_he, he_name from users where he_name is not null and he_name != ''";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

$sql = array();
foreach ($users as $user) {
	$he_name = explode(' ', $user['he_name']);
	$num = count($he_name);
	$first = '';
	$last = '';
	for ($i = 0; $i < $num; $i++) {
		if ($i == ($num-1)) {
			$last = $he_name[$i];
		} else {
			$first .= $he_name[$i] . ' ';
		}
	}
	$first = trim($first);
	$last = trim($last);
	$sql[] = "update users set first_he = \"" . $first . "\", last_he = \"" . $last . "\" where user_id = " . $user['user_id'];
}

foreach ($sql as $qry) {
	mysql_query($qry);
}

$users = array();
$sql = "select user_id, first, last, first_he, last_he, he_name from users where he_name is not null and he_name != ''";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <style type="text/css">
            th, td {
            	padding: 5px;
            	font-size: 14px;
            }
        </style>
    </head>
    
    <body>
    	<table>
    		<tr>
    			<th>User ID</th>
    			<th>Name</th>
    			<th>Hebrew Name</th>
    			<th>Pushka Name</th>
    		</tr>
    		<?
    		foreach ($users as $user) {
    			echo "<tr><td>" . $user['user_id'] . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>" . 
    				$user['first_he'] . ' ' . $user['last_he'] . "</td><td>" . $user['he_name'] . "</td></tr>";
    		}
    		?>
    	</table>
	</body>
</html>