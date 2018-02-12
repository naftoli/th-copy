<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');

function get_global_campaigns() {
	global $camp_id;
	
	$echo_string = "\n";
	
	$sql = "SELECT gc.*, cc.campaign_id AS camp_exists FROM global_campaigns AS gc LEFT JOIN camp_campaigns AS cc ON (cc.campaign_id=gc.campaign_id AND cc.camp_id=" . $camp_id . ")";
	$query = mq($sql);
	
	while ($row = mysql_fetch_assoc($query)) {
	
		$echo_string = $echo_string . "<li>\n";
		$echo_string = $echo_string . "\t<span class='action'>\n";
		$echo_string = $echo_string . "\t\t<span class='checklist'>\n";
		
		if ($row['camp_exists'] > 0) 
			$echo_string = $echo_string . "\t\t\t<input type='checkbox' checked class='checkbox' id=''>\n";
		else 
			$echo_string = $echo_string . "\t\t\t<input type='checkbox' class='checkbox' id=''>\n";
		
		$echo_string = $echo_string . "\t\t\t<span class='install'>\n";
		$echo_string = $echo_string . "\t\t\t<a onclick='insert_campaign(" . $row['campaign_id'] . ", \"insert\")' class='button checkbox-select' title='Install' href='#'>\n";		
		$echo_string = $echo_string . "\t\t\t<span class='icon'></span>Install</a>\n";
		$echo_string = $echo_string . "\t\t\t</span>\n";
		
		$echo_string = $echo_string . "\t\t\t<span class='uninstall'><a onclick='insert_campaign(" . $row['campaign_id'] . ", \"remove\")' class='button checkbox-deselect' title='Uninstall' href='#'><span class='icon'></span>Uninstall</a></span>\n";
		$echo_string = $echo_string . "\t\t\t<span class='progress'>Progress</span>\n";
		$echo_string = $echo_string . "\t\t\t</span>\n";
		$echo_string = $echo_string . "\t\t\t</span>\n";
		$echo_string = $echo_string . "\t\t<span class='icon bullet'></span>\n";
		$echo_string = $echo_string . "\t\t<span class='label'><span class='label title'>Campaign </span>" . $row['campaign_name'] . "</span>\n";
		$echo_string = $echo_string . "\t\t<span class='label'><span class='label title'>Includes </span><span class='label small'>Line Up, Clean Up, Breakfast</span></span>\n";
		$echo_string = $echo_string . "\t\t<div class='clear'></div>\n";
		$echo_string = $echo_string . "\t</li>\n";

	}
	
	echo $echo_string;
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
				var camp_id = "<?=$camp_id;?>";
				
				$(document).ready(function() {
					$(".checklist input:checked").parent().addClass("selected");
					
					$(".checklist .checkbox-select").click(
						function(event) {
							event.preventDefault();
							$(this).parents('.checklist').addClass("selected");
							$(this).parents('.checklist').find(":checkbox").attr("checked","checked");
							$(this).parents('li').css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
							$(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
						}
					);
					
					$(".checklist .checkbox-deselect").click(
						function(event) {
							event.preventDefault();
							$(this).parents('.checklist').removeClass("selected");
							$(this).parents('.checklist').find(":checkbox").removeAttr("checked");
							$(this).parents('li').css({ backgroundColor: '#e19494' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
							$(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
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
			
			<div class="slider">
			
				<div class="col_title">
					<span>Getting Started</span><a class="slider_back">back</a>
				</div>
				

				
				
				
				<div class="col_content">
				
                    <h1><?=T_("Setup Campains");?></h1>
                    <div id="module-info" class="module">
                        <div class="module_content">
                        	<p><?=T_("In this step you will install campaigns.");?></p>
                        	<p><?=T_("Please choose a camp type from the drop down to load up a selection of appropriate campaigns.");?></p>
                        	<p><?=T_("You will then be able to select certain missions and assign them to certain groups.");?></p>
                        </div>
                    </div>
					
                    <div id="module-info" class="module">
                    	<h1><?=T_("Install Campaigns");?></h1>
						
                        <div class="module_content">
                            <div class="list campaigns">
                                <ul>
																
									<li>
										<span class="icon load"></span>
										<span>Load campaigns for:</span>
										<select onchange="loadMissions" id="camp_type_id" name="camp_type_id">
											<option value="0"><?=T_("All Camp Types");?></option>
											<? $query = mq("SELECT * FROM camp_types"); ?>
											<? while ($row = mysql_fetch_assoc($query)) : ?>												
												
												<option value="<?=$row['camp_type_id'];?>"><?=$row['camp_type'];?></option>
												
												<? endwhile; ?>
												
										</select>
										<div class="clear"></div>
									</li>
																			
									<? get_global_campaigns(); ?>
									
                                </ul>
                            </div>
                        </div>
						
                    </div> <!-- <div id="module-info" class="module"> -->
                    
				</div> <!-- <div class="col_content"> -->	
				
				
				
				
				
				
				
				
				
				
				
			</div> <!-- <div class="slider"> -->

			
			