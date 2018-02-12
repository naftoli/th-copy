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
else {
	$user_type = "camp";
}
// ***** Determine if the user is a camp director or a super user ***** //

if ($user_type == "camp") {
	$camp_id = $admin_user['auths']['camp'][0]; 
}	
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
$group_type_id = gri('group_type_id', -1);
$group_id = gri('group_id', -1);
$campaign_id = gri('campaign_id', -1);
$campaign_group_id = gri('campaign_group_id', -1);
$task_id = gri('task_id', -1);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Print Camp Achievement Cards'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<LINK href="card_printer.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<STYLE type="text/css">
			.fronts, .backs {
			  margin: auto;
			}

			.fronts, .backs {
			  page-break-after: always;
			}

			.fronts td, .backs td {
			  border: 1px dashed black;
			  vertical-align: middle;
			  height: 2.125in;
			}

			.fronts td td, .backs td td {
			  width: auto;
			  height: auto;
			  border: none;
			}

			@media print {
			  .fronts td, .backs td {
				border: none;
			  }

			  hr {
				display: none;
			  }
			}
		</STYLE>
		
		<SCRIPT type="text/javascript">
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var camp_name = "<?=$camp_name;?>";
			var group_type_id = "<?=$group_type_id;?>";
			var group_id = "<?=$group_id;?>";
			var campaign_id = "<?=$campaign_id;?>";
			var campaign_group_id = "<?=$campaign_group_id;?>";			
			var task_id = "<?=$task_id;?>";
			var divs_array = ["camps_div", "group_types_div", "groups_div", "campaigns_div", "campaign_groups_div", "tasks_div", "card_div"];
			var divisions = "";
			
			/*function get_divs(divs, form_name, select) {
				document.getElementById("cards_div").innerHTML = "";
				document.getElementById("card_div").innerHTML = "";
				
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
						
						if (select == "group_id") {
							url = url + "&campaign_id=-1";
						}
						else {
							try {
								url = url + "&campaign_id=" + document.getElementById("campaign_id").value;
							}
							catch(err) {
								url = url + "&campaign_id=" + campaign_id;
							}
						}
						
						if (select != "task_id") {
							url = url + "&task_id=-1";
						}
						else {
							try {
								url = url + "&task_id=" + document.getElementById("task_id").value;
							}
							catch(err) {
								url = url + "&task_id=" + task_id;
							}						
						}
						
					}
										
				}
				
				url = url + "&divs=" + divs + "&form_name=" + form_name;
				
				var http = getHTTPObject();
				http.open("GET", url, true);
				
				http.onreadystatechange = function() {
					if (http.readyState == 4 && http.status == 200) {
						if (http.responseText.substr(0, 7) == "[SPLIT]") 
							var innerHTML = http.responseText.substr(7, http.responseText.length - 7);
						else 
							var innerHTML = http.responseText;
								
						var divs = innerHTML.split("[SPLIT]");
						
						for (cntr = (divisions.length - 1); cntr > -1; cntr--) {
							var div_no = divisions.substr(cntr, 1);		
							document.getElementById(divs_array[div_no - 1]).innerHTML = divs[cntr];
						}
					}
				}
				http.send(null);
			}*/
			
			
			function print_cards() {
				try {
					camp_id = document.getElementById("camp_id").value;
				}
				catch(err) {
				}
			
				var task_id = document.getElementById("task_id").value;
				var miles = document.getElementById("miles").value;
				var left_circle = document.getElementById("left_circle").value;
				var right_circle = document.getElementById("right_circle").value;
				var number_of_cards = document.getElementById("number_of_cards").value;
				
				url = "ajax_print_cards.php?camp_id=" + camp_id + "&task_id=" + task_id + "&miles=" + miles + "&left_circle=" + left_circle + "&right_circle=" + right_circle + "&number_of_cards=" + number_of_cards;
				
				var http = getHTTPObject();
				http.open("GET", url, true);
				
				http.onreadystatechange = function() {
					if (http.readyState == 4 && http.status == 200) {
						document.getElementById("cards_div").innerHTML = http.responseText;
					}
				}
				http.send(null);												
			}			
		</SCRIPT>		
	</HEAD>

<? if ($action == "") : ?>	
	<body onload="get_divs('1234567', 'print_form', '');">
<? else : ?>
	<body>
<? endif; ?>		
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			
			<DIV class="left_menu">
				<? include('admin_inc.php'); ?>
			</DIV>
			
			<DIV class="noprint">
			
				<H1>
					<?=T_('Print Camp Achievement Cards')?>
				</H1>
				
				<form name="print_form" id="print_form" action="admin_print_camp_cards.php" method="post" accept-charset="UTF-8">
					<input type="hidden" name="action" id="action" value="">
		
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
					
					<div id="campaigns_div">
					</div>	
					
					<br />
					<br />
					
					<div id="campaign_groups_div">
					</div>	
					
					<br />
					<br />
					
					<div id="tasks_div">
					</div>
					
					<br />
					<br />
					
					<div id="card_div">
					</div>					
					
				</form>
				
			</DIV> <!-- noprint -->
			
			<div id="cards_div">
			</div>
			
		</DIV> <!-- body -->
		
		<DIV class="noprint">
			<? include('admin_footer.php'); ?>
		</DIV>
		
	</BODY>
	
</HTML>
