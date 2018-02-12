<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link href="admin_styles.css" rel="stylesheet" type="text/css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>List of Students</title>
	<style>
		table {width: 100%;}
		tr {height: 22px;}
		th, td {vertical-align: text-top;padding: 0 5px;}
		input {margin-top: -5px;margin-bottom: 3px;}
		.newAdmin {border-top: 1px dashed black;padding-top: 10px;}
	
		/* The Modal (background) */
		.modal {
			display: none; /* Hidden by default */
			position: fixed; /* Stay in place */
			z-index: 1; /* Sit on top */
			left: 0;
			top: 0;
			width: 100%; /* Full width */
			height: 100%; /* Full height */
			overflow: auto; /* Enable scroll if needed */
			background-color: rgb(0,0,0); /* Fallback color */
			background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
			font-size: 12px;
		}

		.modal h2 {
			margin-top: 10px;
		}
		
		/* The Close Button */
		.close {
			color: #aaa;
			float: right;
			font-weight: bold;
		}
		
		.close:hover,
		.close:focus {
			color: black;
			text-decoration: none;
			cursor: pointer;
		}
		
		.modal-header, .modal-body, .modal-footer {
			padding: 2px 16px;
		}
		
		.modal-header {
			font-size: 22px;
		}
		
		.modal-footer {
			text-align: center;
		}
		
		/* Modal Content */
		.modal-content {
			position: relative;
			background-color: #fefefe;
			margin: auto;
			padding: 0;
			border: 1px solid #888;
			width: 300px;
			box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
			-webkit-animation-name: animatetop;
			-webkit-animation-duration: 0.4s;
			animation-name: animatetop;
			animation-duration: 0.4s
		}
		
		/* Add Animation */
		@-webkit-keyframes animatetop {
			from {top: -300px; opacity: 0} 
			to {top: 0; opacity: 1}
		}
		
		@keyframes animatetop {
			from {top: -300px; opacity: 0}
			to {top: 0; opacity: 1}
		}
	</style>
</head>
<body>
	<? include('admin_header.php');?>
	<h1>List of Students</h1>
	<?
	require_once 'class.adminSchools.php';       
	$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
	$schools = $as->getSchools();
	$schoolIDs = array();
	$originalSchools = array();
	foreach ($schools as $id => $school) {
		$schoolIDs[] = $id;
		$originalSchools[] = $id;
	}

	$group = array(
		'ny' 		=> array(7,9,30,33,54,255,264),
		'montreal' 	=> array(2,58),
		'toronto'	=> array(45,106),
		'miami'		=> array(19,42),
		'la'		=> array(4,162)
	);
	
	$groupBy = '';
	foreach ($group as $key => $arr) {
		foreach ($arr as $school) {
			if (in_array($school, $schoolIDs)) {
				$groupBy = $key;
				break;
			}
		}
	}
	if (!empty($groupBy)) {
		foreach ($group[$groupBy] as $school) {
			if (!in_array($school, $schoolIDs)) {
				$schoolIDs[] = $school;
			}
		}
	}
	$numSchools = count($schoolIDs);
	if ($numSchools == 1) {?>
	<table border="1" cellspacing="1" style="font-size:12px">
		<tr>
			<th>Child</th>
			<th>Parent Account ID</th>
			<th></th>
		</tr>
	<?} else {	?>
	<table border="1" cellspacing="1" style="font-size:12px">
		<tr>
			<th width="30%">School</th>
			<th>Child</th>
			<th>Email / Address</th>
			<th>Parent Account ID</th>
			<th></th>
			<th></th>
		</tr>
	<?}
	//get list of parents
	include_once('db.php');
	$sql = "SELECT * FROM users JOIN schools USING (school_id)
			WHERE school_id IN (" . implode(',', $schoolIDs) . ") ORDER BY last, first";
	$result = mysql_query($sql);
	$prev_admin = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$admin_id = 0;
		$newAdmin = false;
		$admin_auth_sql = "SELECT * FROM admin_auths WHERE id = " . $row['user_id'] . " AND auth = 'user'";
		$result2 = mysql_query($admin_auth_sql);
		if (mysql_num_rows($result2) > 0) {
			$row2 = mysql_fetch_assoc($result2);
			$admin_id = $row2['admin_id'];
		}
		if ($prev_admin != $admin_id) {
			$newAdmin = true;
			$prev_admin = $admin_id;
		}
		echo "<tr id=" . $row['user_id'] . "><td>";
		if ($numSchools == 1) {
			echo $row['first'] . ' ' . $row['last'] . "</td><td>";
		} else {
			echo $row['school_name'] . "</td><td>";
			//if ($newAdmin) echo " class='newAdmin'";
			$inArr = false;
			if (in_array($row['school_id'], $originalSchools)) {
				$inArr = true;
				echo "<a href='http://mashpia.com/admin_user.php?action=edit&user_id=" . $row['user_id'] . "&school_id=" . $row['school_id'] . "'>";
			}
			echo $row['first'] . ' ' . $row['last'];
			if ($inArr) echo "</a>";
			echo "</td><td>";
			echo ($row['email'] ? $row['email'] . "<br />" : '') . $row['user_address1'] . ($row['user_address2'] ? "<br />" . $row['user_address2'] : '') . "</td><td>";
		}
		echo "<input type='text' size='5' ";
		if ($admin_id) echo "value='" . $admin_id . "' ";
		echo "/></td><td><a class='update'>update</a></td><td>";
		if (!$admin_id) echo "<a class='createBtn'>create parent account</a>";
		echo "</td></tr>";
	}
	?>
	</table>
	
	<h2></h2>
	<?php
	$sql = "SELECT * FROM admins a JOIN school_parents s USING (admin_id) "
		."WHERE s.school_id in (" . implode(',', $schoolIDs) . ")";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		?>
		<table border="1" cellspacing="1" style="font-size:12px">
			<tr>
				<th>Parent Account ID</th>
				<th>Name</th>
				<th>Address</th>
			</tr>
			<?php
			while ($row = mysql_fetch_assoc($result)) {
				echo "<tr><td>" . $row['admin_id'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" .
					$row['admin_address1'] . "<br />" . $row['admin_city'] . ', ' . $row['admin_state'] . ' ' . $row['admin_postal'] . "</td></tr>";
			}
			?>
		</table>
	<? } ?>
	
	<!-- The Modal -->
	<div id="myModal" class="modal">
	
		<!-- Modal content -->
		<div class="modal-content">
		  <form id="createParent">
			<input type="hidden" id="childID" />
			<div class="modal-header">
			  <span class="close">×</span>
			  <h2>Create Parent Account</h2>
			</div>
			<div class="modal-body">
			  <table>
				  <tr>
					  <td>First Name</td>
					  <td><input type="text" id="fname" required /></td>
				  </tr>
				  <tr>
					<td>Last Name</td>
					<td><input type="text" id="lname" required /></td>
				  </tr>
				  <tr>
					<td>Address</td>
					<td><input type="text" id="address" required /></td>
				  </tr>
				  <tr>
					<td>City</td>
					<td><input type="text" id="city" required /></td>
				  </tr>
				  <tr>
					<td>State</td>
					<td>
						<input type="text" id="state" required />
					</td>
				  </tr>
				  <tr>
					<td>Zip</td>
					<td><input type="text" id="zip" required /></td>
				  </tr>
				  <tr>
					<td>Home Phone</td>
					<td><input type="text" id="hphone" required /></td>
				  </tr>
				  <tr>
					<td>Cell Phone</td>
					<td><input type="text" id="cphone" required /></td>
				  </tr>
				  <tr>
					<td>Email</td>
					<td><input type="email" id="email" required /></td>
				  </tr>
			  </table>
			</div>
			<div class="modal-footer">
			  <input type="submit" class="submit" value="create account" />
			  <br /><br />
			</div>
		  </form>
		</div>
	
	</div>
	
	</body>
	<script>
		// Get the modal
		var modal = document.getElementById('myModal');
		
		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
				$("#myModal input").not(".submit").val("");
			}
		}
	
		$(".createBtn").click( function() {
			var id = $(this).parent().parent().attr('id');
			$("#childID").val(id);
			$("#myModal").show();
		});
		
		$(".close").click( function() {
			$("#myModal").hide();
			$("#myModal input").not(".submit").val("");
		});
		
		$("#createParent").submit( function(e) {
			e.preventDefault();
			var fname = $("#fname").val();
			var lname = $("#lname").val();
			var address = $("#address").val();
			var city = $("#city").val();
			var state = $("#state").val();
			var zip = $("#zip").val();
			var hphone = $("#hphone").val();
			var cphone = $("#cphone").val();
			var email = $("#email").val();
			var childID = $("#childID").val();
			if (fname == '' || lname == '' || address == '' || city == '' || state == '' || zip == '' || email == '') {
				alert('You must fill out name, address, and email fields.');
				return false;
			}
			$.post('ajax/createParentAccount.php', {
				fname : fname,
				lname : lname,
				address : address,
				city : city,
				state : state,
				zip : zip,
				hphone : hphone,
				cphone : cphone,
				email : email,
				childID : childID
			}, function(success) {
				if (success != 0) {
					alert('account created.');
					var row = $("#" + childID);
					$(row).find("input").val(success);
					$(row).find("td").eq(4).text('');
					$("#myModal").hide();
					$("#myModal input").not(".submit").val("");
				} else {
					alert("Could not create account as it likely already exists.\n\nFor further assistance, please contact support and provide the info you attempted to submit.")
				}
			});
		});
	
		$(".update").click( function() {
			var user = $(this).parent().parent().attr('id');
			var admin = $(this).parent().parent().find('input').val();
			//alert(user + ' ' + admin);
			if (admin && user) {
				$.post('ajax/updateUserAdmin.php', { admin : admin, user : user }, function( success ) {
					if (success > 0) {
						// all is good
						alert('updated.');
						$(this).parent().parent().find('input').val(success);
						$("#" + user).find('td').eq(4).html("");
					} else {
						alert('There was an error.');
					}
				});
			} else if (user) {
				var conf = confirm('Are you sure you want to remove this child from this parent?');
				if (conf) {
					$.post('ajax/updateUserAdmin.php', { user : user }, function( success ) {
						if (success > 0) {
							// all is good
							alert('deleted.');
							//$("#" + user).find('td').eq(2).text('no');
							$("#" + user).find('td').eq(4).html("<a class='createBtn'>create parent account</a>");
						} else {
							alert('There was an error deleting.');
						}
					});
				}
			}
		});
		/*
		$(".remove").click( function() {
			var user = $(this).parent().parent().attr('id');
			var admin = $(this).parent().prev('td').text();
			if (admin && user) {
				$.post('ajax/updateUserAdmin.php', { admin : admin, user : user, action : 'delete' }, function( success ) {
					if (success > 0) {
						// all is good
						alert('deleted.');
						$("#" + user).find('td').eq(2).text('');
						$("#" + user).find('td').eq(3).text('');
					} else {
						alert('There was an error deleting.');
					}
				});
			}
		});
		*/
	</script>
</html>
