<?php
session_start();

$admin_auth = array('user'); 

require('header.php'); 
require_once('file_save.php'); 
require_once('calendar.php'); 
require_once('card_printer.php');

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \camps\classes\admin($row);

$child_id = 0;
if (isset($_POST["child_id"])) {
	$child_id = $_POST["child_id"];
	$_SESSION["child_id"] = $child_id;
	$sql = "SELECT * FROM users WHERE user_id=" . $child_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$school_id = $row["school_id"];
	$_SESSION["school_id"] = $school_id;	
	$user_id = $child_id;
}

if (isset($_SESSION["child_id"])) {
	$child_id = $_SESSION["child_id"];
}

$admin_school_id = 0;
if (isset($_GET["school_id"])) {
	if (!isset($_SESSION))
		session_start();
	$_SESSION["school_id"] = $_GET["school_id"];
	$admin_school_id = $_GET["school_id"];
}

$admin_class_id = 0;
if (isset($_GET["class_id"])) {
	 $admin_class_id = $_GET["class_id"];
}

if ($admin_user['admin_id'] > 0) {

	if (!isset($_SESSION))
		session_start();
		
	$_SESSION["admin_id"] = $admin_user['admin_id'];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Admin Menu'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<STYLE type="text/css">
			.points {
			  margin: 10px 0px;
			}

			.points tbody th {
			  text-align: <?=$align_start?>;
			}

			.points tbody td {
			  text-align: right;
			}
		</STYLE>
		
		<script>
			function change_class(class_id) {
				document.getElementById("class_id").value = class_id;
				document.forms["class_form"].submit();			
			}
		
			function change_school(school_id) {
				document.getElementById("school_id").value = school_id;
				document.forms["school_form"].submit();			
			}
			
			function change_child(child_id) {
				document.getElementById("child_id").value = child_id;
				document.forms["child_form"].submit();
			}
		</script>
	</HEAD>
	
	<BODY>
		
		<? include('admin_header.php'); ?>

		
				
		<DIV CLASS="body">			
			
			<DIV class="admin">								

				<!-- ****************************** PARENT ****************************** -->
				<? if ($child_id == 0) : ?>
					<h1><?=T_('Welcome')?></h1>
					<!--<H2><?=T_('Welcome')?>, <?=$admin->first?> <?=$admin->last?></H2>-->

					<!--<ul class="tabs">
						<li><?//=T_('Personal Information')?></li>
						<li><?//=T_('Address')?></li>
					</ul>-->
					
					
					<!--<div class="panes">
						
						<div class="module">-->
							
							<h2><?=T_('Personal Information')?></h2>					
					
							<label style="font-weight:bold;"><?=T_('First Name')?>: </label><?=$admin->first;?>						
							<br />
					
							<label style="font-weight:bold;"><?=T_('Last Name')?>: </label><?=$admin->last;?>						
							<br />
							
							<label style="font-weight:bold;"><?=T_('Address')?>: </label><?=$admin->admin_address1;?>
							<?=$admin->admin_address2;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('City')?>: </label><?=$admin->admin_city;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('State')?>: </label><?=$admin->admin_state;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('Postal/Zip')?>: </label><?=$admin->admin_postal;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('Country')?>: </label><?=$admin->admin_country;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('Home Phone')?>: </label><?=$admin->admin_phone_work;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('Work Phone')?>: </label><?=$admin->admin_phone_home;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('Cell Phone')?>: </label><?=$admin->admin_phone_mobile;?>
							<br />
							
							<label style="font-weight:bold;"><?=T_('Email')?>: </label><?=$admin->admin_email;?>
							
							
						<!--</div>
						
					</div>-->
					
				<? endif; ?>
				<!-- ****************************** PARENT ****************************** -->

			</DIV>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
		

	</BODY>
	
</HTML>
