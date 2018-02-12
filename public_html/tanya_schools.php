<?php
require_once(dirname(__FILE__).'/db.php');

require 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get campaigns for current year
$line_campaigns = mysql_query( "SELECT * FROM line_campaigns WHERE year = " . $year );
while ($row = mysql_fetch_assoc( $line_campaigns )) {
	if (strtolower($row['type']) == 'tanya') $tanyaCampaign = $row['id'];
	else if (strtolower($row['type']) == 'mishna') $mishnaCampaign = $row['id'];
}

// if we are creating a new school
if (isset($_POST['newSchool'])) {
	// use do -> while so that we can break out at any time...
	do {
		$name = mysql_real_escape_string($_POST['newSchool']);
		$school_number_query = mysql_query("SELECT school_number FROM schools ORDER BY school_number DESC LIMIT 1");
		$number = mysql_fetch_assoc($school_number_query)['school_number'];
		
		if(empty($name)){
			$msg = "School Name cannot be blank."; break; // set the message and break from the while loop
		}
		
		$create_school_query = mysql_query(
			"INSERT INTO schools SET "
			."school_name = '" . $name . "', "
			."school_number = " . ++$number . ", "
			."tanya = 1"
		);
		
		if(!$create_school_query) {
			$msg = "Error creating school."; break;
		}
		
		$school_id = mysql_insert_id(); // get the ID of the school
		// create admin and connect with school
		$username = mysql_real_escape_string( $_POST['username'] );
		$password = mysql_real_escape_string( $_POST['password'] );
		
		if (empty($username) || empty($password)) {
			$msg = "Please provide a username and password"; break;
		}
		
		$create_admin_query = mysql_query( "INSERT INTO admins SET username = '" . $username . "', password = '" . $password . "'" );
		
		if(!$create_admin_query) {
			$msg = "School created however there was an error creating the admin account."; break;
		}
		
		$admin_id = mysql_insert_id();
		
		$create_admin_auth_query = mysql_query(
			"INSERT INTO admin_auths SET admin_id = " . $admin_id . ", "
			."id = " . $school_id . ", auth = 'school', role_id = 18"
		);
		
		if(!$create_admin_auth_query) {
			$msg = "School created however there was an error setting up the admin account.";break;
		}
		
		$msg = $name . " School has been successfully created.";
		
	} while (false); // false so we only do one round
}

// load all the schools
$schools = array();
$schools_query = mysql_query("SELECT * FROM schools WHERE tanya = 1 AND chayolei = 0 ORDER BY school_name");
while ($row = mysql_fetch_assoc($schools_query)) {
	$schools[$row['school_id']] = $row['school_name'];
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>BP School Report</title>
		<style>
			body, table {
				font-family: sans-serif;
				font-size: 12px;
			}
			th, td {
				padding: 3px;
			}
			#bpTable input {
				width: 50px;
			}
		</style>
	</head>
	
	<body>
		<h2>BP School Report</h2>
		
		<?php if (isset($msg)) : ?>
		<p style="color: red">
			<?=$msg?>
		</p>
		<?php endif; ?>
		
		<hr />
		<form action="tanya_schools.php" method="post" id="newSchool">
			<table>
				<tr>
					<td>New School Name:</td>
					<td><input type="text" name="newSchool" required /></td>
				</tr>
				<tr>
					<td>Username:</td>
					<td><input type="text" name="username" id="username" required /></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="password" required /></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="createSchool" value="Create" id="submitForm" onclick="checkForm(); return false;" /></td>
				</tr>
			</table>
		</form>
		
		<hr />
		<table id="bpTable">
			<tr>
				<th>School</th>
				<th>Tanya Lines</th>
				<th>Mishna Lines</th>
			</tr>
			<?php
			foreach ($schools as $id => $name) {
				$tanya = 0;
				$mishna = 0;
				$sql = "SELECT * FROM bp_school_summary WHERE campaign_id IN ($tanyaCampaign, $mishnaCampaign) AND school_id = " . $id;
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_assoc($result)) {
						if ($row['campaign_id'] == $tanyaCampaign) {
							$tanya = $row['num_lines'];
						} else if ($row['campaign_id'] == $mishnaCampaign) {
							$mishna = $row['num_lines'];
						}
					}
				}
				?>
				<tr id="id">
					<td><?=$name?></td>
					<td>
						<input type="text" class="tanya" value="<?=$tanya?>" />
					</td>
					<td>
						<input type="text" class="mishna" value="<?=$mishna?>" />
					</td>
				</tr>
			<? } // end foreach school ?>
		</table>
	</body>
	<script src="scripts/jquery-1.8.3.js"></script>
	<script>
		function checkForm() {
			var username = $("#username").val().trim();
			$.post('ajax/checkUsername.php', { user : username }, function( error ) {
				if (error == 1) {
					alert("Username already exists. Please choose a different username.");
					$("#username").focus().select();
				} else {
					$("form")[0].submit();
				}
			});
		}
		
		var tanya = <?=$tanyaCampaign?>;
		var mishna = <?=$mishnaCampaign?>;
		
		$(function() {
			$(".tanya").keyup( function() {
				var val = $(this).val();
				var id = $(this).parent().parent().attr('id');
				$.post('ajax/updateBalPehCampaign.php', {
					id: tanya,
					val: val,
					school : id,
					table : 'lines_learned',
					updateSummary : 1
				});
			});
			$(".mishna").keyup( function() {
				var val = $(this).val();
				var id = $(this).parent().parent().attr('id');
				$.post('ajax/updateBalPehCampaign.php', {
					id: mishna,
					val: val,
					school : id,
					table : 'lines_learned',
					updateSummary : 1
				});
			});
		});
	</script>
</html>