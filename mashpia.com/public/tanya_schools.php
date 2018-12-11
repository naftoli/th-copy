<?php
$admin_auth = array('school'); 
require_once(dirname(__FILE__).'/header.php');

// only superusers can use this page
if($admin_user['auth'] != 'super') {
	header("Location: /admin.php");
}

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
			body{ margin: 0px; }
			h2.header{text-align: center;background: #03A9F4;margin: 0px;padding: 15px;color: #fff;font-size: 2em;margin-bottom: 15px;}
			body, table {
				font-family: sans-serif;
				font-size: 12px;
				width: 100%;
			}
			table{
				max-width: 1200px;
				margin: 0 auto;
			}
			table#newSchool {
				width: 300px;
			}
			th, td {
				padding: 3px;
			}
			#bpTable {
				border-spacing: 0px;
			}
			#bpTable input {
				width: 50px;
				margin: 0 auto;
				display: block;
				background: none;
				border: none;
				border-bottom: 1px solid #000;
				text-align: center;
			}
			#bpTable tr:nth-child(even) {background: #EEE}
			#bpTable tr:nth-child(odd) {background: #FFF}
			
			button.save_row, button.delete_row {
				background: #03A9F4;
				border: 1px solid #2196F3;
				border-radius: 5px;
				padding: 3px 8px;
				color: #fff;
				transition: .1s ease-in-out;
			}
			button.save_row:hover, button.delete_row:hover {
				transform: scale(1.1);
				cursor: pointer;
			}
			button.delete_row {
				background: #F44336;
				border-color: #D32F2F;
			}
			@media (min-width: 1015px) {
				button.delete_row { float: right; }
			}
			td.actions { max-width: 80px; }
		</style>
	</head>
	
	<body>
		<h2 class="header">BP School Report</h2>
		
		<?php if (isset($msg)) : ?>
		<p style="color: red; text-align: center;">
			<?=$msg?>
		</p>
		<?php endif; ?>

		<form action="tanya_schools.php" method="post" id="newSchool">
			<table id="newSchool">
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
				<th>School ID</th>
				<th>School</th>
				<th>Tanya Lines</th>
				<th>Mishna Lines</th>
				<th>Number Of Students</th>
				<th>Actions</th>
			</tr>
			<?php
			foreach ($schools as $id => $name) {
				$tanya = 0;
				$mishna = 0;
				$child_count = 0;
				
				$result = mysql_query(
					" SELECT bpu.campaign_id, bpu.num_lines, bpu.child_count " // select the campaign and the number of lines
					." FROM users u JOIN bp_user_summary bpu USING (user_id) " // from the bp_user_summary table (joined from users...)
					." WHERE bpu.campaign_id IN ($tanyaCampaign, $mishnaCampaign) " // where the campaign is in the current campaigns
					." AND u.school_id = '$id' AND u.class_id IS NULL " // and they are in the same school with NO GRADE!
					." AND u.first = 'Tanya'" // and the first name is tanya.... (perhaps we should say the child count is greater then 1?)
				);
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_assoc($result)) {
						$child_count = $row['child_count']; // just keep updating this...
						if ($row['campaign_id'] == $tanyaCampaign) {
							$tanya = $row['num_lines'];
						} else if ($row['campaign_id'] == $mishnaCampaign) {
							$mishna = $row['num_lines'];
						}
					}
				}
				?>
				<tr id="<?=$id?>">
					<td><?=$id?></td>
					<td><?=$name?></td>
					<td>
						<input type="number" class="tanya" value="<?=$tanya?>" />
					</td>
					<td>
						<input type="number" class="mishna" value="<?=$mishna?>" />
					</td>
					<td>
						<input type="number" class="child_count" value="<?=$child_count?>" />
					</td>
					<td class="actions">
						<button class="save_row">Save</button>
						<button class="delete_row">Delete</button>
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
			$("button.save_row").click(function(event) {
				var row = $(event.target).parent().parent(); // get the row that we are in.
				// get the info from the row
				
				var data = {
					school_id:		row.attr("id"),
					tanya:			row.find(".tanya").val(),
					mishna: 		row.find(".mishna").val(),
					tanya_campaign:	<?=$tanyaCampaign?>,
					mishna_campaign:<?=$mishnaCampaign?>,
					child_count: 	row.find(".child_count").val()
				};
				
				$.post("ajax/updateBpSchoolMarks.php", data, function( response ){
					response = JSON.parse( response );
					if ( !response.success ) { 
						alert( response.error );
					}
				});
				
			});

			$("button.delete_row").click( function( event ) {
				// get the text from the first two columns in the table
				var school_id =  $( $(event.target).parent().siblings()[0]).text();
				var school_name = $( $(event.target).parent().siblings()[1]).text();
				// make sure that they know what they are doing
				if ( !confirm( "Please confirm that you want to delete " + school_name + " (#" + school_id + ")?" ) )
					return false;
				// delete the school
				$.post("/ajax/tanya/deleteTanyaSchool.php", { school_id: school_id }, function( response ) {
					response = JSON.parse( response );
					if ( response.success ) {
						$( event.target ).parent().parent().remove(); // delete the row from the table
					} else if( response.error ) {
						alert( response.error);
					} else {
						alert( "Unknown Error. Please refresh the page" );
					}
				});
			});
		});
	</script>
</html>