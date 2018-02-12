<?
$admin_auth = array('camp');

require('header.php');
require_once('calendar.php');

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

$group_type_id = gri('group_type_id', -1);
$group_id = gri('group_id', -1);

$action = gr("action", "");
if ($action == "save") {
	$sql = "UPDATE camp_groups SET points=" . gri('points') . " WHERE group_id=" . $group_id;
	mq($sql);
	$action = "";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<TITLE><?=T_('Assign Group Points'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var group_type_id = "<?=$group_type_id;?>";
			var group_id = "<?=$group_id;?>";

			var divs_array = ["camps_div", "group_types_div", "groups_div", "campaigns_div"];
			var divisions = "";
			
			function get_divs(divs, form_name, select) {
				divisions = divs;
				
				try {
					var url = "ajax_get_camps_info.php?admin_id=" + admin_id + "&user_type=" + user_type + "&camp_id=" + document.getElementById("camp_id").value;
				}
				catch(err) {
					var url = "ajax_get_camps_info.php?admin_id=" + admin_id + "&user_type=" + user_type + "&camp_id=" + camp_id;
				}
				
				if (select == "camp_id") {
					url = url + "&group_type_id=-1&group_id=-1";
				}
				else {
					try {
						url = url + "&group_type_id=" + document.getElementById("group_type_id").value;
					}
					catch(err) {
						url = url + "&group_type_id=" + group_type_id;
					}
					
					if (select == "group_type_id") {
						url = url + "&group_id=-1&campaign_id=-1";
					}
					else {
						try {
							url = url + "&group_id=" + document.getElementById("group_id").value;
						}
						catch(err) {
							url = url + "&group_id=" + group_id;
						}
												
					}
										
				}
				
				url = url + "&divs=" + divs + "&form_name=" + form_name;
				
				var http = getHTTPObject();
				http.open("GET", url, true);
				
				http.onreadystatechange = function() {
				
					if (http.readyState == 4 && http.status == 200) {
						
						if (http.responseText.substr(0, 7) == "[SPLIT]") 
							var innerHTML = http.responseText.substr(7, http.responseText.length - 8);
						else 
							var innerHTML = http.responseText;
								
						var divs = innerHTML.split("[SPLIT]");
						
						for (cntr = (divisions.length - 1); cntr > -1; cntr--) {							
							var div_no = divisions.substr(cntr, 1);
							
							if (divs_array[div_no - 1] != "campaigns_div") {
								document.getElementById(divs_array[div_no - 1]).innerHTML = divs[cntr];
							}
						}

						url2 = "ajax_get_group_points.php?group_id=" + document.getElementById("group_id").value;
						var http2 = getHTTPObject();
						http2.open("GET", url2, true);
						http2.onreadystatechange = function() {
							if (http2.readyState == 4 && http2.status == 200) {
								document.getElementById("points").value = http2.responseText;
							}
						}
						http2.send(null);
						
						
					}
					
				}
				http.send(null);
			}
		</SCRIPT>
	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_divs('1234', 'group_points_form', '');">
<? else : ?>
	<body>
<? endif; ?>		

	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">		
			<DIV class="left_menu">
				<?include('admin_inc.php');?>
			</DIV>
			
			<H1>
				<?=T_('Assign Group Points')?>
			</H1>
			
			<form name="group_points_form" id="group_points_form" action="admin_assign_group_points.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
				<input type="hidden" name="action" id="action" value="<?=$action;?>">
				
				<br />
				<br />
				
				<div id="camps_div">
				</div>		
				
				<br />
				<br />
				
				<div id="group_types_div">
				</div>	
				
				<br />
				<br />
				
				<div id="groups_div">
				</div>	
				
				<br />
				<br />

				<label>
					<?=T_("Points");?>:
					<input type="text" name="points" id="points" onkeypress="return number_validation(event);">
				</label>
				
				<br />
				<br />
				
				<input type="submit" value="<?=T_('SAVE');?>" onclick="document.getElementById('action').value='save';">
				
				<div id="campaigns_div">
				</div>	
		
				<br />
				<br />

				
			</form>
			
		</DIV> <!-- BODY -->
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
