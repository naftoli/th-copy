<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php
include ("db.php");

$sql = "
	SELECT * 
	FROM user_tracks
	WHERE user_id NOT 
	IN (
		SELECT user_id
		FROM tanya_users
	)
	AND subject_id =27
	GROUP BY user_id";
	
$result = mysql_query($sql);

$num = 0;
$errors = array();
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['user_id'];
	$track = $row['track_id'];
	$level = $row['level'];
	
	$sql2 = "insert into tanya_users (user_id, track, year) values ($id, $track, $level)";
	if (@mysql_query($sql2))
		$num++;
	else 
		$errors[$id] = mysql_error();	
}
echo $num . " records inserted.<br />";
if (count($errors)) {
	foreach ($errors as $key => $error)
		echo "Error for user_id:" . $key . " - " . $error . "<br />";
}	
?>
</body>
</html>