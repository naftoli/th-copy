<?php
$admin_auth = array('camp'); 

require('header.php');
require_once('calendar.php');

// ***** Determine if the user is a camp director or a super user ***** //
if ($admin_user['auth'] == "super")
	$user_type = "super";
else
	$user_type = "camp";
// ***** Determine if the user is a camp director or a super user ***** //

if ($user_type == "camp") 
	$camp_id = $admin_user['auths']['camp'][0]; 
else {
	$camp_id = gri('camp_id', -1);
	if ($camp_id == -1)
		$camp_id = 1;
}

if ($user_type == "camp") {
	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
}

$term_id = gri('term_id', -1);

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			if ($user_type == "super" && $camp_id == -1)
				$sql = "INSERT INTO terms (camp_id, term_name, term_days) VALUES (NULL, '" . gr('term_name') . "', " . gr('term_days') . ")";
			else
				$sql = "INSERT INTO terms (camp_id, term_name, term_days) VALUES (" . $camp_id . ", '" . gr('term_name') . "', " . gr('term_days') . ")";
			mq($sql);
			$action = "";
			$term_id = -1;	
		break;
		
		case 'save':
			$sql = "UPDATE terms SET term_name='" . gr('term_name') . "', term_days=" . gri('term_days') . " WHERE term_id=" . $term_id;
			mq($sql);
			$action = "";
			$term_id = -1;	
		break;
		
		case 'delete':
			$sql = "DELETE FROM terms WHERE term_id=" . $term_id;
			mq($sql);
			$action = "";
			$term_id = -1;	
		break;		
		
	}

}

$delete_message = T_('Are you sure that you want to delete this term?');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Terms'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		
		<script type="text/javascript">
			function get_terms() {
				var camp_id = document.getElementById("camp_id").value;
				
				url = "ajax_get_terms.php?camp_id=" + camp_id;
				
				var http = getHTTPObject();
				http.open("GET", url, true);
				
				http.onreadystatechange = function() {
					if (http.readyState == 4 && http.status == 200) {	
						document.getElementById("terms_div").innerHTML = http.responseText;
					}
				}
				http.send(null);
			}
			
			function getHTTPObject() {
				var xmlhttp;

				if (window.XMLHttpRequest) {
					xmlhttp = new XMLHttpRequest();
				}
				else if (window.ActiveXObject){ 
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					
					if (!xmlhttp) {
						xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
					}
				}
				
				return xmlhttp; 
			}			
		</script>
	</HEAD>
	
	<BODY>
		
		<? include('admin_header.php'); ?>
		
		<input type="hidden" name="action 1" value="<?=$action;?>">
		
		<DIV CLASS="body">
		
			<DIV class="left_menu">
				<? include('admin_inc.php'); ?>
			</DIV>
			
			<H1>
				<?=T_('Terms')?>
			</H1>
			
			<? if (!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>
			
			
<!-- **************************************** ADD NEW **************************************** -->			
<? if ($action == 'add_new') : ?>

	<form action="admin_terms.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="add">
		
		<? if ($user_type == "camp") : ?>
		<h2>Camp:<?=$camp_name;?></h2>
		<? endif; ?>
		
		<br />
		
		<? if ($user_type == "super") : ?>
			<? $camps_query = mq("SELECT * FROM camps"); ?>
			<?=T_('Select a Camp')?>: 
			<label>
				<select name="camp_id">
					<? while ($camp = mysql_fetch_assoc($camps_query)) : ?>
						<? if ($camp_id == $camp['camp_id']) : ?>
						<option value="<?=$camp['camp_id']?>" selected><?=es($camp['camp_name'])?></option>
						<? else : ?>
						<option value="<?=$camp['camp_id']?>"><?=es($camp['camp_name'])?></option>
						<? endif; ?>
					<? endwhile; ?>
				</select>
			</label>							
		<? endif; ?> <!-- if ($user_type == "super") : -->
		
		<table>
			<tr>
				<td><?=T_('Name');?></td>
				<td><input type="text" name="term_name" id="term_name"></td>
			</tr>
			<tr>
				<td><?=T_('Days');?></td>
				<td><input type="text" name="term_days" id="term_days" maxlength="2"></td>
			</tr>
		</table>
		
		<br />
		
		<input type="submit" value="<?=T_('ADD');?>">
		<input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value=''; ">
	</form>
	
<? endif; ?> <!-- if ($action == 'add_new') : -->
<!-- **************************************** ADD NEW **************************************** -->			


<!-- **************************************** EDIT **************************************** -->			
<? if ($action == "edit") : ?>
	<? $term_query = mq("SELECT * FROM terms WHERE term_id=" . $term_id); ?>
	<? $term = mysql_fetch_assoc($term_query); ?>
	
	<form action="admin_terms.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="save">
		<input type="hidden" name="term_id" id="term_id" value="<?=$term_id;?>">
		
		<table>
			<tr>
				<td><?=T_('Name');?></td>
				<td><input type="text" name="term_name" id="term_name" value="<?=$term['term_name'];?>"></td>
			</tr>
			<tr>
				<td><?=T_('Days');?></td>
				<td><input type="text" name="term_days" id="term_days" maxlength="2" value="<?=$term['term_days'];?>"></td>
			</tr>
		</table>
		
		<br />
		
		<input type="submit" value="<?=T_('SAVE');?>">
		<input type="submit" value="<?=T_('CANCEL');?>">
	</form>
	
<? endif; ?> <!-- if ($action == "edit") : -->
<!-- **************************************** EDIT **************************************** -->			


<!-- **************************************** NO ACTION **************************************** -->			
<? if ($action == "") : ?>

	<form name="terms_form" id="terms_form" action="admin_terms.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">
		<input type="hidden" name="term_id" id="term_id" value="">
		
		<a href="#" onclick="document.getElementById('action').value='add_new'; document.forms['terms_form'].submit();"><?=T_('Add new term')?></a>
			
		<br />
		
		<? if ($user_type == "super") : ?>
			<? $camps_query = mq("SELECT * FROM camps"); ?>
			<?=T_('Select a Camp')?>: 
			<label>
				<select name="camp_id" id="camp_id" onchange="get_terms();">
					<? while ($camp = mysql_fetch_assoc($camps_query)) : ?>
						<? if ($camp_id == $camp['camp_id']) : ?>
						<option value="<?=$camp['camp_id']?>" selected><?=es($camp['camp_name'])?></option>
						<? else : ?>
						<option value="<?=$camp['camp_id']?>"><?=es($camp['camp_name'])?></option>
						<? endif; ?>
					<? endwhile; ?>
				</select>
			</label>					
		<? else : ?> <!-- if ($user_type == "super") : -->
			<h2>Camp:<?=$camp_name;?></h2>
		<? endif; ?> <!-- if ($user_type == "super") : -->
		
		<? $terms_query = mq("SELECT t.*, c.camp_name FROM terms AS t JOIN camps AS c USING (camp_id) WHERE camp_id=" . $camp_id . " ORDER BY term_days"); ?>
		<? $num_rows = mysql_num_rows($terms_query); ?>
		
		<br />
		
		<div id="terms_div">
			<table class="pretty_grid">
				<th><?=T_('Name');?></th>
				<th><?=T_('Days');?></th>
				<th></th>
				<th></th>
				
				<? if ($num_rows > 0) : ?>
					<? while ($term = mysql_fetch_assoc($terms_query)) : ?>
						<tr>
							<td><?=$term['term_name'];?></td>
							<td><?=$term['term_days'];?></td>
							<td><a href="#" onclick="document.getElementById('action').value='edit'; document.getElementById('term_id').value='<?=$term['term_id'];?>'; document.forms['terms_form'].submit();"><?=T_('Edit Term');?></a></td>		
							<td><a href="#" onclick="document.getElementById('action').value='delete'; document.getElementById('term_id').value='<?=$term['term_id'];?>'; var dlt = confirm ('<?=$delete_message;?>'); if (dlt == true) document.forms['terms_form'].submit();"><?=T_('Delete Term');?></a></td>
						</tr>
					<? endwhile; ?>
				<? else : ?>
					<tr>
						<td colspan="4"><?=T_('No terms found');?></td>
					</tr>				
				<? endif; ?>
			</table>
		</div>
		
		<br />
			
	</form>
	
<? endif; ?> <!-- if ($action == "") : -->
<!-- **************************************** NO ACTION **************************************** -->			

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
