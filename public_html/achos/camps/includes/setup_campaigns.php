<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/camp_campaign.php");
	
$group_task = $_GET['group_task'];

$global_campaigns = get_member_global_campaigns();
$campaigns = $global_campaigns[0]['campaigns'];

function get_member_global_campaigns() {
	global $camp_id;
	global $group_task;
	
	$global_campaigns = array();
	$campaigns = array();
	
	$sql = "SELECT gc.campaign_id, gc.campaign_name, cc.camp_campaign_id, cc.active ";
	$sql = $sql . "FROM global_campaigns AS gc ";
	$sql = $sql . "LEFT JOIN camp_campaigns AS cc ON (gc.campaign_id=cc.campaign_id AND cc.camp_id=" . $camp_id. ") ";
	$sql = $sql . "WHERE gc.group_task=" . $group_task;
	$query = mysql_query($sql);

	while ($row = mysql_fetch_assoc($query)) {
		$campaign_id = $row['campaign_id'];
		$campaign_name = $row['campaign_name'];
		$camp_campaign_id = $row['camp_campaign_id'];
		$active = $row['active'];
		
		$element = compact('campaign_id', 'campaign_name', 'camp_campaign_id', 'active');
		array_push($campaigns, $element);
	}
	
	$element = compact('campaigns');
	array_push($global_campaigns, $element);
	
	return $global_campaigns;
}

?>

			<link href="styles/new_styles.css" rel="stylesheet" type="text/css" />
			
			<script src="scripts/jquery.jeditable.min.js"></script>
			<script type="text/javascript" src="jquery.form.js"></script> 
 			<script>
			</script>

			<script type="text/javascript">					
			</script>

			<script>
				var camp_id = <?=$camp_id;?>;
				var group_task = <?=$group_task;?>;
				
				$(document).ready(function() {
					$(".checklist input:checked").parent().addClass("selected");
					
					$(".checklist .checkbox-select").click(
						function(event) {
							var list_item = $(this).parents('li');
							var checklist = $(this).parents('.checklist');
							var campaign_id = $(this).attr("id");
							
							function_name = "install_campaign";
							parameters = [camp_id, campaign_id, group_task];					
							var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							$.getJSON(url, function(camp_campaign_id) {
								if (camp_campaign_id == 0) 
									alert("Could not install campaign. Please try again.");
								else {
									$(checklist).addClass("selected");
									$(checklist).find(":checkbox").attr("checked","checked");
									$(list_item).css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
									$(list_item).find('.progress').show().delay(500).fadeOut(500);	
									if (camp_campaign_id > 0)
										$(checklist).find("a[name=a_uninstall]").attr("id", camp_campaign_id);
								}
							});
						}
					);
					
					$(".checklist .checkbox-deselect").click(
						function(event) {
							var list_item = $(this).parents('li');
							var checklist = $(this).parents('.checklist');
							var camp_campaign_id = $(this).attr("id");
							
							var function_name = "deactivate_campaign";				
							parameters = [camp_campaign_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							$.getJSON(url, function(code) {
								if (code == false) 
									alert("Could not un-install campaign. Please try again.");
								else {
									$(checklist).removeClass("selected");
									$(checklist).find(":checkbox").removeAttr("checked");
									$(list_item).css({ backgroundColor: '#e19494' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
									$(list_item).find('.progress').show().delay(500).fadeOut(500);								
								}
							});			
						}
					);					
					
				});
					
				function insert_campaign(campaign_id, action) {
					var camp_type_id = document.getElementById("camp_type_id").value;
					
					var url = "insert_campaign.php?action=" + action + "&camp_id=" + camp_id + "&campaign_id=" + campaign_id + "&camp_type_id=" + camp_type_id;
					
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
						}
					}
					http.send(null);
				}				
			</script>
			
			<div class="slider">
			
				<div class="col_title">
					<? if ($group_task == 0) : ?>
					<span>Setup Member Campaigns</span><a class="slider_back">back</a>
					<? else : ?>
					<span>Setup Group Campaigns</span><a class="slider_back">back</a>
					<? endif; ?>
				</div>
				
				<div class="col_content">
				
					<? if ($group_task == 0) : ?>
                    <h1>Setup Member Campaigns</h1>
					<? else : ?>
					<h1>Setup Group Campaigns</h1>
					<? endif; ?>
					
                    <div id="module-info" class="module">
                        <div class="module_content">
                        	<p>In this step you will install campaigns.</p>
                        	<p>Please choose a camp type from the drop down to load up a selection of appropriate campaigns.</p>
                        	<p>"You will then be able to select certain missions and assign them to certain groups.</p>
                        </div>
                    </div>
					
                    <div id="module-info" class="module">
						<? if ($group_task == 0) : ?>
                    	<h1>Install Member Campaigns</h1>
						<? else : ?>
						<h1>Install Group Campaigns</h1>
						<? endif; ?>
						
                        <div class="module_content">
                            <div class="list campaigns">
                                <ul>
																
									<li>
										<span class="icon load"></span>
										<span>Load campaigns for:</span>
										<select onchange="loadMissions" id="camp_type_id" name="camp_type_id">
											<option value="0">All Camp Types</option>
											<? $query = mq("SELECT * FROM camp_types"); ?>
											<? while ($row = mysql_fetch_assoc($query)) : ?>												
												
												<option value="<?=$row['camp_type_id'];?>"><?=$row['camp_type'];?></option>
												
												<? endwhile; ?>
												
										</select>
										<div class="clear"></div>
									</li>
									
									<? //echo $sql . "<br />"; ?>
									
									<? for ($cno = 0; $cno < count($campaigns); $cno++) : ?>
									<li>
										<span class='action'>
										
											<? if ($campaigns[$cno]['camp_campaign_id'] > 0 && $campaigns[$cno]['active'] == 1) $selected = " selected "; else $selected = ""; ?>
											<? if ($campaigns[$cno]['camp_campaign_id'] > 0 && $campaigns[$cno]['active'] == 1) $checked = " checked "; else $checked = ""; ?>
											
											<span class='checklist<?=$selected;?>'>
												<input type='checkbox' class='checkbox<?=$checked;?>'>
												<span class='install'>
													<a class='button checkbox-select' href='#' id="<?=$campaigns[$cno]['campaign_id'];?>">
														<span class='icon'></span>Install
													</a>
												</span>
												
												<span class='uninstall'>													
													<a name="a_uninstall" class='button checkbox-deselect' title='Uninstall' href='#' id="<?=$campaigns[$cno]['camp_campaign_id'];?>">
														<span class='icon'></span>Uninstall
													</a>
												</span>
												
												<span class='progress'>Progress</span>
											</span>
										</span>
										
										<span class='icon bullet'></span>
										
										<span class='label'><span class='label title'>Campaign </span><?=$campaigns[$cno]['campaign_name'];?></span>
										
										<div class='clear'></div>
									</li>
									<? endfor; ?>
									
                                </ul>
                            </div>
                        </div>
						
                    </div> <!-- <div id="module-info" class="module"> -->
                    					
                    <div class="wizard_nav">
						<? if ($group_task == 0) : ?>
                        <p><a class="button rfloat" href="content.php?output=gettingstarted5&group_task=1">Next</a></p>
						<? else : ?>
						<p><a class="button rfloat" href="content.php?output=gettingstarted6">Next</a></p>
						<? endif; ?>
                        <br class="clear" />
                    </div>					
					
				</div> <!-- <div class="col_content"> -->	
				
				
				
				
				
				
				
				
				
				
				
			</div> <!-- <div class="slider"> -->

			
			