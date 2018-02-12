<?php
$admin_auth = array('camp'); 

require('header.php');
require_once('calendar.php');

if (count($admin_user['auths']['camp']) == 1) 
	$camp_id = $admin_user['auths']['camp'][0]; 
else
	$camp_id = gri('camp_id', -1);

$campaign_id = gri('campaign_id', -1);
$campaign_name = gr('campaign_name', '');
$template_id = gri('template_id', -1);

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			$sql = "INSERT INTO campaign_templates (campaign_id, template_name, points, max_times) VALUES (" . $campaign_id . ", '" . gr('template_name') . "', " . gri('points') . ", " . gri('max_times') . ")";
			mq($sql);
		break;
		
		case 'save':
			$sql = "UPDATE campaign_templates SET template_name='" .  gr('template_name') . "',  points=" . gri('points') . ", max_times=" . gr('max_times') . " WHERE template_id=" . $template_id;
			mq($sql);
			$action = "";
		break;
		
	}
	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Templates'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		
		<script type="text/javascript">
		</script>
	</HEAD>
	
	<BODY>
	
		<input type="hidden" name="camp_id" value="<?=$camp_id;?>">
		<input type="hidden" name="action" value="<?=$action;?>">
		<input type="hidden" name="campaign_id" value="<?=$campaign_id;?>">
		<input type="hidden" name="campaign_name" value="<?=$campaign_name;?>">
		
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			<DIV class="left_menu">
				<? include('admin_inc.php'); ?>
			</DIV>
			
			<H1>
				<?=T_('Templates')?>
			</H1>
			
			<? if (!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>
			
			<A HREF="admin_templates.php?action=add_new"><?=T_('Add new template')?></A>
			
			
<!-- **************************************** ADD NEW **************************************** -->			
<? if ($action == 'add_new') : ?>

	<? if ($admin_user['auth'] == 'super' || count($admin_user['auths']['camp']) > 1) : ?>
				
		<? $camp_result = mq('SELECT camp_id, camp_name FROM camps' . ($admin_user['auth'] != 'super' ? ' WHERE camp_id IN (' . implode(',', $admin_user['auths']['camp']) . ')' : '') . ' ORDER BY camp_name'); ?>
		
				<FORM action="admin_templates.php" method="get" accept-charset="UTF-8">					
					<P>
						<LABEL>
							<?=T_('Select Camp')?>: 
							<SELECT name="camp_id">
								<? while($camp_row = mysql_fetch_assoc($camp_result)) : ?>
								<OPTION value="<?=$camp_row['camp_id']?>" <?=$camp_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($camp_row['camp_name'])?></OPTION>
								<? endwhile; ?>
							</SELECT>
						</LABEL> 
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
					</P>
				</FORM>
				
				<HR>
	<? else : ?>
				
		<? if ($campaign_id == -1) : ?>
		
		<? 
			$camp_id = $admin_user['auths']['camp'][0]; 
			$sql = "SELECT camp_id, camp_name FROM camps WHERE camp_id=" . $camp_id;
			$query = mysql_query($sql);
			$camp = mysql_fetch_assoc($query);
			
			$campaigns_query = mq("SELECT campaign_id, campaign_name FROM campaigns WHERE camp_id=" . $camp_id);
		?>
			
				<FORM action="admin_templates.php" method="post" accept-charset="UTF-8">
					<input type="hidden" name="action" value="add_new">
					<P>
						<LABEL>
							<?=T_('Select Campaign')?>: 
							<SELECT name="campaign_id">
								<? while ($campaign = mysql_fetch_assoc($campaigns_query)) : ?>
									<? if ($campaign == $campaign['campaign_id']) : ?>
									<OPTION value="<?=$campaign['campaign_id']?>" selected><?=es($campaign['campaign_name'])?></OPTION>
									<? else : ?>
									<OPTION value="<?=$campaign['campaign_id']?>"><?=es($campaign['campaign_name'])?></OPTION>
									<? endif; ?>
								<? endwhile; ?>
							</SELECT>
						</LABEL> 
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
					</P>
				</FORM>	
		
		<? endif; ?> <!-- if ($term_id == -1) : -->
		
	<?endif;?> <!-- if ($admin_user['auth'] == 'super' || count($admin_user['auths']['camp']) > 1) : -->

	<? if ($campaign_id != -1) : ?>

				<? $campaigns_query = mq("SELECT campaign_id, campaign_name FROM campaigns WHERE camp_id=" . $camp_id); ?>
				
				<form action="admin_templates.php" method="post" accept-charset="UTF-8">
					<input type="hidden" name="action" value="add">
					<input type="hidden" name="campaign_id" value="<?=$campaign_id;?>">
					
					<?=T_('Select Campaign')?>: 
					<SELECT name="campaign_id">
						<? while ($campaign = mysql_fetch_assoc($campaigns_query)) : ?>
							<? if ($campaign == $campaign['campaign_id']) : ?>
							<OPTION value="<?=$campaign['campaign_id']?>" selected><?=es($campaign['campaign_name'])?></OPTION>
							<? else : ?>
							<OPTION value="<?=$campaign['campaign_id']?>"><?=es($campaign['campaign_name'])?></OPTION>
							<? endif; ?>
						<? endwhile; ?>
					</SELECT>
					
					<table>
						<tr><td>Name:</td><td><input type="text" name="template_name"></td></tr>
						<tr><td>Points:</td><td><input type="text" name="points"></td></tr>
						<tr><td>Max # of Times:</td><td><input type="text" name="max_times"></td></tr>
						<tr><td colspan="2"><input type="submit" value="<?=T_('Add');?>"></td></tr>
					</table>				
				</form>
	<? endif; ?> <!-- if ($campaign_id != -1) : -->


				
<? endif; ?> <!-- if ($action == 'add_new') : -->
<!-- **************************************** ADD NEW **************************************** -->			

<!-- **************************************** EDIT **************************************** -->			
<? if ($action == "edit") : ?>
	<? $template_query = mq("SELECT * FROM campaign_templates WHERE template_id=" . $template_id); ?>
	<? $row = mysql_fetch_assoc($template_query); ?>
	
				<form action="admin_templates.php" method="post" accept-charset="UTF-8">
					<input type="hidden" name="action" id="action" value="save">
					<input type="hidden" name="template_id" value="<?=$template_id;?>">
					<input type="hidden" name="campaign_id" value="<?=$campaign_id;?>">
					<input type="hidden" name="campaign_name" value="<?=$campaign_name;?>">
		
					<h1><?=$campaign_name;?></h1>
		
					<table>
						<tr><td><label><?=T_('Name');?></td><td><input type="text" value="<?=$row['template_name'];?>" maxlength="255" name="template_name"></label></td></tr>
						<tr><td><label><?=T_('Points');?></td><td><input type="text" value="<?=$row['points'];?>" maxlength="255" name="points"></label></td></tr>
						<tr><td><label><?=T_('Max # of Times');?></td><td><input type="text" value="<?=$row['max_times'];?>" maxlength="255" name="max_times"></label></td></tr>
					</table>
					
					<br />
					
					<input type="submit" value="Save">
					<input type="submit" value="Cancel" onclick="document.getElementById('action').value='';">
				</form>
<? endif; ?> <!-- if ($action == "edit") : -->
<!-- **************************************** EDIT **************************************** -->			

<? if ($action == "") : ?>

		<? if ($campaign_id == -1) : ?>

				<? $campaigns_query = mq("SELECT * FROM campaigns WHERE camp_id=" . $camp_id); ?>
				
				<form action="admin_templates.php" method="post" accept-charset="UTF-8">
					<?=T_('Select Campaign')?>: 
					<SELECT name="campaign_id" id="campaign_id">
						<? while ($campaign = mysql_fetch_assoc($campaigns_query)) : ?>
							<? if ($campaign == $campaign['campaign_id']) : ?>
							<OPTION value="<?=$campaign['campaign_id']?>" selected><?=es($campaign['campaign_name'])?></OPTION>
							<? else : ?>
							<OPTION value="<?=$campaign['campaign_id']?>"><?=es($campaign['campaign_name'])?></OPTION>
							<? endif; ?>
						<? endwhile; ?>
					</SELECT>
					<input type="hidden" name="campaign_name" id="campaign_name">
					
					<input class="submit" type="submit" value="<?=T_('Go')?>" onClick="document.getElementById('campaign_name').value = document.getElementById('campaign_id').options[document.getElementById('campaign_id').selectedIndex].text;">
				</form>
				
		<? else : ?> <!-- if ($campaign_id == -1) : -->
		
				<? $templates_query = mq("SELECT ct.*, c.campaign_name FROM campaign_templates AS ct JOIN campaigns AS c USING (campaign_id) WHERE c.camp_id=" . $camp_id); ?>
		
				<h1><?=$campaign_name;?></h1>
				
				<table class="list">
					<tr>
						<th><?=T_('Name');?></th>
						<th><?=T_('Points');?></th>
						<th><?=T_('Max # of Times');?></th>
						<th></th>
						<th></th>
					</tr>
					
					<? while($row = mysql_fetch_assoc($templates_query)) : ?>
					<tr>
						<td><?=$row['template_name'];?></td>
						<td><?=$row['points'];?></td>
						<td><?=$row['max_times'];?></td>
						<td><a href="admin_templates.php?action=edit&campaign_id=<?=$row['campaign_id'];?>&campaign_name=<?=$row['campaign_name'];?>&template_id=<?=$row['template_id'];?>">Edit Template</a></td>
						<td><a href="admin_templates.php?action=delete&campaign_id=<?=$row['campaign_id'];?>&campaign_name=<?=$row['campaign_name'];?>&template_id=<?=$row['template_id'];?>">Delete</a></td>
					</tr>
					<? endwhile; ?>
				</table>
		
		<? endif; ?> 
			
			
				
<? endif; ?> <!-- if ($action == "") : -->

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
