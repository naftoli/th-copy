<?php

require('db.php');
$intUser = intval($_GET["user_id"]);
$boolEnrolled = @$_GET["enrolled"] == "0" ? "0" : "1";
if ($intUser < 1)
{
	exit;
}
// Check what the users top user_tracks level is
$strSql = "
	SELECT
		level
	FROM
		user_tracks
	WHERE
		user_id = " . $intUser . "
	ORDER BY
		level DESC
	LIMIT
		1
";
$refResult = mysql_query($strSql) or die(mysql_error());
$intLevel = mysql_result($refResult, "level");
$intLevel = $intLevel ? $intLevel : 1;

// Check if an update or an insert should be done
$strSql = "
	SELECT
		*
	FROM
		user_tracks
	WHERE
		user_id = " . $intUser . "
		AND subject_id = 91
";
$refResult = mysql_query($strSql) or die(mysql_error());
if (mysql_num_rows($refResult))
{
	$strSql = "
		UPDATE
			user_tracks
		SET
			enrolled = " . $boolEnrolled . ",
			level = " . $intLevel . "
		WHERE
			user_id = " . $intUser . "
			AND subject_id = 91
		ORDER BY
			level DESC
		LIMIT
			1
	";
	mysql_query($strSql) or die(mysql_error());
}
else
{
	$strSql = "
		insert into
			user_tracks
			(user_id, subject_id, track_id, level, enrolled)
		values
			(" . $intUser . ", 91, 1, " . $intLevel . ", " . $boolEnrolled . ")
	";
	mysql_query($strSql) or die(mysql_error());
}



?>