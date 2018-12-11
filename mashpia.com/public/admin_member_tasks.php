<? 
$admin_auth = array('camp');
require('header.php'); 
require_once('file_save.php');
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
}

if ($user_type == "camp") {
	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
}

$members_query = mq("SELECT * FROM users WHERE camp_id=" . $camp_id);

$user_id = gri('user_id', -1);
$user = mysql_fetch_assoc(mq("SELECT * FROM users WHERE user_id=" . $user_id));

$division_id = gri('division_id', -1);

$ui_type = 'camp';
require_once('admin_ui.php');

function get_tasks() {
	global $user_id;
	
	$echo_string = "\n<table class='pretty_grid'>\n";
	$echo_string = $echo_string . "\t<thead>\n";
	$echo_string = $echo_string . "\t\t<tr>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Group Type') . "</th>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Division') . "</th>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Group') . "</th>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Task') . "</th>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Period') . "</th>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Points') . "</th>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Max Times') . "</th>\n";
	$echo_string = $echo_string . "\t\t</tr>\n";	
	$echo_string = $echo_string . "\t</thead>\n";

	$sql = "SELECT gt.group_type_name, g.group_name, d.division_name, ct.*, p.period_name FROM member_groups AS mg JOIN group_types AS gt USING (group_type_id) JOIN divisions as d USING (division_id) JOIN groups AS g ON (mg.group_id=g.group_id) JOIN group_tasks AS gt2 ON (mg.group_id=gt2.group_id) JOIN camp_tasks AS ct USING (camp_task_id) JOIN periods AS p USING (period_id) WHERE user_id=" . $user_id;
	$query = mq($sql);
	$prev_group_type_name = "";
	$prev_group_name = "";
	$prev_division_name = "";
	while ($row = mysql_fetch_assoc($query)) {
		$echo_string = $echo_string . "\t\t<tr>\n";
		
		if ($prev_group_type_name != $row['group_type_name'])
			$echo_string = $echo_string . "\t\t\t<td>" . $row['group_type_name'] . "</td>\n";
		else
			$echo_string = $echo_string . "\t\t\t<td>&nbsp;</td>\n";
			
		if ($prev_group_name != $row['group_name'])
			$echo_string = $echo_string . "\t\t\t<td>" . $row['group_name'] . "</td>\n";
		else
			$echo_string = $echo_string . "\t\t\t<td>&nbsp;</td>\n";
			
		if ($prev_division_name != $row['division_name'])
			$echo_string = $echo_string . "\t\t\t<td>" . $row['division_name'] . "</td>\n";
		else
			$echo_string = $echo_string . "\t\t\t<td>&nbsp;</td>\n";
		
		$echo_string = $echo_string . "\t\t\t<td>" . $row['task_name'] . "</td>\n";
		$echo_string = $echo_string . "\t\t\t<td>" . $row['period_name'] . "</td>\n";
		$echo_string = $echo_string . "\t\t\t<td>" . $row['points'] . "</td>\n";
		$echo_string = $echo_string . "\t\t\t<td>" . $row['max_times'] . "</td>\n";
		
		$echo_string = $echo_string . "\t\t</tr>\n";

		$prev_group_type_name = $row['group_type_name'];
		$prev_group_name = $row['group_name'];
		$prev_division_name = $row['division_name'];
	}
	
	$echo_string = $echo_string. "</table>\n";
	
	echo $echo_string;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Member Tasks');?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript">
			var user_type = "<?=$user_type;?>";
		</script>
	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
		
		<DIV class="ui_school left">
		
			<DIV class="body">
		
				<DIV class="sub_menu">				
					<? if(!empty($message)) : ?>
						<H2><?=$message?></H2>
					<? endif; ?>
				</DIV> <!-- sub_menu -->
				
				<H1><?=T_('Camp Management')?></H1>
				
				
				<? if ($user_type == "super") : ?>
					<? $camps_sql = mq("SELECT * FROM camps"); ?>
					<FORM name="camps_form" id="camps_form" action="admin_camp_members.php" method="post" accept-charset="UTF-8">
						<INPUT type="hidden" name="action" id="action" value="<?=$action?>">
						<INPUT type="hidden" name="camp_id" id="camp_id" value="">
						<P>
							<LABEL>
								<?=T_('Select Camp')?>: 
								<SELECT name="camp_id">
									<? while($camp = mysql_fetch_assoc($camps_sql)) : ?>
									<? if ($camp['camp_id'] == $camp_id) : ?>
									<OPTION value="<?=$camp['camp_id']?>" selected><?=es($camp['camp_name'])?></OPTION>
									<? else :?>
									<OPTION value="<?=$camp['camp_id']?>"><?=es($camp['camp_name'])?></OPTION>
									<? endif; ?>
									<? endwhile; ?>
								</SELECT>
							</LABEL>
							
							<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
						</P>
					</FORM>
				<? else : ?>
					<h2>
						<?=$camp_name;?>
					</h2>
				<? endif; ?>
		
				<div class="ui_body">
				
					<DIV class="ui_menu">
						<?ui_menu();?>
					</DIV> <!-- ui_menu -->
					
					<DIV class="content">
						<H2>
							<?=T_('Member Tasks');?>
						</H2>							
							
						<h3><?=T_('Member');?>: <label style='color:blue'><?=$user['first'];?> <?=$user['last'];?></label></h3>
						
						<? get_tasks(); ?>
						
					</div> <!-- content -->
					
				</div> <!-- ui_body -->
				
			</div> <!-- body -->
			
		</DIV> <!-- ui_school left -->
		
		<? include('admin_footer.php'); ?>

	</BODY>
	
</HTML>
