<?
$admin_auth = array('school'); 
require('header.php');

require 'class.myShliachHachayol.php';

$hachayol = new MyShliachHachayol(false, 269);
$admins = $hachayol->getAdmins();
$children = $hachayol->getChildren();

//echo "<pre>"; print_r($children); echo "</pre>";
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<title>Hachayol Report</title>
		<style>
			tr, th, td {
				padding: 3px;
				font-size: 12px;
				vertical-align: top;
			}
			input {
				width: 90px;
			}
			input.title {
				width: 40px;
			}
			input.email {
				width: 180px;
			}
			.line {
				border-top: 1px solid grey;
			}
			#loader {
				position: absolute;
				left: 90px;
				top: -20px;
			}
		</style>
	</head>
	
	<body>
	<? include('admin_header.php'); ?>
	<h1>Hachayol Report</h1>
		
		<div id='loader'>
			<img src="images/loadingNew.gif" />
		</div>
		
		<div align='center'>
			<button id='update'>Update</button>
		</div>
		<br />
		
		<table>
			<tr>
				<th>Title</th>
				<th>Parent First</th>
				<th>Parent Last</th>
				<th>Email</th>
				<th>Address</th>
				<th>Children</th>
			</tr>
		<? foreach ($admins as $id => $admin) : ?>
			<tr class='line' id='<?=$id?>'>
				<td><input type='text' class='title' value='<?=$admin['title']?>' /></td>
				<td><input type='text' class='fname' value='<?=$admin['afirst']?>' /></td>
				<td><input type='text' class='lname' value='<?=$admin['alast']?>' /></td>
				<td><input type='text' class='email' value='<?=$admin['admin_email']?>' /></td>
				<td><?=$admin['admin_address1'] . "<br />" . (empty($admin['admin_address2']) ? '' : 
						$admin['admin_address2'] . "<br />") . $admin['admin_city'] . ', ' 
						. $admin['admin_state'] . '  ' . $admin['admin_postal'] . "<br />" . 
						$admin['admin_country']?></td>
				<td>
					<table>
						<?
						foreach ($children[$id] as $child) {
							echo $child . "<br />";
						}
						?>
					</table>
				</td>
			</tr>
		<? endforeach; ?>
		</table>		
	</body>
	
	<script src="jquery-1.8.1.min.js"></script>
	<script>
		$( function() {
			$("#loader").hide();
			$("#update").click( function() {
				$("#loader").show();
				$(".line").each( function() {
					var admin = $(this).attr('id');
					var title = $(this).find('.title').val();
					var first = $(this).find('.fname').val();
					var last = $(this).find('.lname').val();
					var email = $(this).find('.email').val();
					$.post('ajax/updateAdmin.php', {
						title : title, 
						first : first, 
						last : last, 
						email : email, 
						admin : admin
					});
				});
				setTimeout( function() { 
					window.location = "anashKinderInfo.php";
				}, 5000);
			});
		});
	</script>
</html>