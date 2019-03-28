<? 
$admin_auth = array('camp'); 

require('header.php');
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$admin_id = gri("admin_id");

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
	
$camp_name = "";	
if ($user_type == "camp" || $camp_id > -1) {
	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
}

$action = gr('action', '');
if ($action != "") {
}

function get_campaign_missions() {
	global $camp_id;
	
	$echo_string = "";
	
	$sql = "SELECT * FROM camp_campaigns WHERE camp_id=" . $camp_id . " ORDER BY campaign_name";
	$query = mq($sql);	
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows == 0) {
		$echo_string = "\n<table>\n<thead>\n<tr><th>" . T_('No campaigns found') . "</th></tr></thead>\n</table>";
		echo $echo_string;
	}
	else {
		while ($row = mysql_fetch_assoc($query)) {	
			$echo_string = "\n<table style='width:100%; border:solid 2px #B0ACA2;' id='campaign_" . $row['camp_campaign_id'] . "' name='campaign_" . $row['campaign_id'] . "'>\n";
			
			// ***** CAMPAIGN ***** //
			$echo_string = $echo_string . "\t\t<thead><!-- CAMPAIGN -->\n";
			$echo_string = $echo_string . "\t\t\t<tr>\n";
			$echo_string = $echo_string . "\t\t\t\t\t<th align='left' colspan='5' style='background-color:#FDF8EA;'>\n";
			$echo_string = $echo_string . "\t\t\t\t\t<label style='color:#996600;'>" . $row['campaign_name'] . "</label>\n";			
			$echo_string = $echo_string . "\t\t\t\t\t</th>\n";									
			$echo_string = $echo_string . "\t\t\t</tr>\n";			
			$echo_string = $echo_string . "\t\t</thead><!-- END CAMPAIGN -->\n";
			// ***** CAMPAIGN ***** //
			
			// ***** MISSIONS ***** //
			$sql2 = "SELECT * FROM camp_missions WHERE camp_campaign_id=" . $row['camp_campaign_id'];
			$query2 = mq($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$echo_string = $echo_string . "\t\t<tbody style='background-color:#E4EDD1;'><!-- MISSION -->\n";
				$echo_string = $echo_string . "\t\t\t<tr>\n";
				$echo_string = $echo_string . "\t\t\t\t\t<td colspan='5'>\n";
				$echo_string = $echo_string . "<label style='color:#336600;'><b>" . $row2['mission_name'] . "</b></label>\n";
				$echo_string = $echo_string . "\t\t\t\t\t</td>\n";
				$echo_string = $echo_string . "\t\t\t</tr>\n";
				$echo_string = $echo_string . "\t\t</tbody><!-- END MISSION -->\n";
				
				// ***** TASKS ***** //
				$sql3 = "SELECT * FROM camp_tasks WHERE camp_mission_id=" . $row2['camp_mission_id'];
				$query3 = mq($sql3);
				$task_number = 0;
				$echo_string = $echo_string . "\t\t<tbody><!-- TASKS -->\n";
				$number_of_tasks_per_row = 5;
				while ($row3 = mysql_fetch_assoc($query3)) {
					
					$remainder = $task_number % $number_of_tasks_per_row;
				
					if ($remainder == 0) 
						$echo_string = $echo_string . "\t\t\t<tr>\n";
					
					$echo_string = $echo_string . "\t\t\t\t\t<td>\n";
					$echo_string = $echo_string . "\t\t\t\t\t\t<label>" . $row3['task_name'] . "</label>\n";				
					$echo_string = $echo_string . "\t\t\t\t\t</td>\n";
									
					if ($remainder == ($number_of_tasks_per_row - 1)) 
						$echo_string = $echo_string . "\t\t\t</tr>\n";
					
					$task_number++;
				}
				$echo_string = $echo_string . "\t\t</tbody><!-- END TASKS -->\n";
				// ***** TASKS ***** //
			}
			// ***** MISSIONS ***** //
			
			$echo_string = $echo_string . "</table>\n<br />";
			
			echo $echo_string;
		}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Campaigns'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var camp_name = "<?=$camp_name;?>";
		</SCRIPT>		
	</HEAD>

	<body>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			
			<DIV class="left_menu">
				<? include('admin_inc.php'); ?>
			</DIV>
			
				<H1>
					<?=T_('Campaigns')?>
				</H1>
				
				<form name="camp_campaigns_form" id="camp_campaigns_form" action="admin_camp_campaigns.php" method="post" accept-charset="UTF-8">
					<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
					
					<br />
					<br />
					
					<? if ($user_type == "super") : ?>						
						<P>
							<LABEL>
								<?=T_('Select Camp')?>: 
								<SELECT name="camp_id" onchange="document.camp_campaigns_form.submit();">
									<? $camps_sql = mq("SELECT * FROM camps"); ?>
									<? while($camp = mysql_fetch_assoc($camps_sql)) : ?>
									<? if ($camp['camp_id'] == $camp_id) : ?>
									<OPTION value="<?=$camp['camp_id']?>" selected><?=es($camp['camp_name'])?></OPTION>
									<? else :?>
									<OPTION value="<?=$camp['camp_id']?>"><?=es($camp['camp_name'])?></OPTION>
									<? endif; ?>
									<? endwhile; ?>
								</SELECT>
							</LABEL>							
						</P>
					<? endif; ?>
					
					<div id="campaigns_div">
						<? get_campaign_missions(); ?>
					</div> <!-- ALL CAMPAIGNS DIV -->
									
				</form>
				
		</DIV> <!-- body -->
		
		<DIV class="noprint">
			<? include('admin_footer.php'); ?>
		</DIV>
		
	</BODY>
	
</HTML>
