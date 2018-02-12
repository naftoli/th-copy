<?
$admin_auth = array('school');
require 'header.php';
if ( isset( $_POST['submit'] ) ) {
	$from = $_POST['from'];
	$to = $_POST['to'];
	$msg = "";
	
	if ( $from != "" && $to != "" ) {
		$sql = "select user_id from users where user_serial = " . $from;
		$result = mysql_query( $sql );
		if ( mysql_num_rows($result) > 0 ) {
			$row = mysql_fetch_assoc($result);
			$from = $row['user_id'];
		} else {
			$msg .= "You have entered an incorrect serial number in the old account textbox.<br />";
		}
		
		$sql = "select user_id from users where user_serial = " . $to;
		$result = mysql_query( $sql );
		if ( mysql_num_rows($result) > 0 ) {
			$row = mysql_fetch_assoc($result);
			$to = $row['user_id'];
		} else {
			$msg .= "You have entered an incorrect serial number in the new account textbox.<br />";
		}
		
		if ( $msg == "" ) {		 
			$sql = "update date_tasks_marks set user_id = $to where user_id = $from";
			if ( $result = mysql_query( $sql ) ) {
				require_once('../classes/mission_marks_updater.php');
				require_once('../classes/medal_updater.php');
				require_once('../classes/rank_updater.php');
				
				$mmupdater = new mission_marks_updater();
				$mupdater = new medal_updater();
				$rupdater = new rank_updater();
				
				$user = $to;
				$mmupdater->mission_marks_update( $user );
				$mupdater->update_medal_two( $user );
				$rupdater->update_rank_two( $user );
				
				$msg .= "The two accounts have been merged<br />";
			} else {
				$msg .= "Error trying to merge the two accounts.<br />" . $sql . "<br />" . mysql_error() . "<br />";
			}
		}
	} else {
		$msg .= "You have not entered a correct value for the old and / or new account.<br />";
	}
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
	</head>

	<body>
		<? require 'admin_header.php'; ?>
		<h1>Merge Two Accounts</h1>
		<div class="infobox">
			<p>Please note that merging two accounts will cause the system to show that the child needs to get 
				medals / ranks in their new account even if he / she received it in their old account.</p>
		</div>
		
		<div style="color: red">
			<? 
			if ( isset( $msg ) ) {
				echo $msg . "Please try again!<br /><br />";
			}
			?>
		</div>
		
		<form action="merge_accounts.php" method="post">
			Old account serial number: <input type="text" name="from" /><br />
			New account serial number: <input type="text" name="to" /><br />
			<input type="submit" name="submit" value="Merge" />
		</form>
	</body>
</html>
