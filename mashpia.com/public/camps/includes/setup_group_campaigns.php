<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

function get_campaigns() {
	global $camp_id;
	
	$echo_string = "\n";
	
	$sql = "SELECT * ";
	$sql = $sql . "FROM campaigns_group ";
	$sql = $sql . "WHERE camp_id=" . $camp_id;
	$query = mq($sql);
	
	while ($row = mysql_fetch_assoc($query)) {
	
		$echo_string = $echo_string . "<li id='ci_" . $row['campaign_group_id'] . "'>\n";
		$echo_string = $echo_string . "\t<span class='action'>\n";
		
		if ($row['installed'] > 0) 
			$echo_string = $echo_string . "\t\t<span class='checklist selected'>\n";
		else
			$echo_string = $echo_string . "\t\t<span class='checklist'>\n";
		
		if ($row['camp_exists'] > 0) 
			$echo_string = $echo_string . "\t\t\t<input type='checkbox' checked class='checkbox' id=''>\n";
		else 
			$echo_string = $echo_string . "\t\t\t<input type='checkbox' class='checkbox' id=''>\n";
		
		$echo_string = $echo_string . "\t\t\t<span class='install'>\n";
		$echo_string = $echo_string . "\t\t\t<a class='button checkbox-select' title='Install' href='#'>\n";				
		$echo_string = $echo_string . "\t\t\t<span class='icon'></span>Install</a>\n";
		$echo_string = $echo_string . "\t\t\t</span>\n";
		
		$echo_string = $echo_string . "\t\t\t<span class='uninstall'><a class='button checkbox-deselect' title='Uninstall' href='#'><span class='icon'></span>Uninstall</a></span>\n";		
		$echo_string = $echo_string . "\t\t\t<span class='progress'>Progress</span>\n";
		$echo_string = $echo_string . "\t\t\t</span>\n";
		$echo_string = $echo_string . "\t\t\t</span>\n";
		$echo_string = $echo_string . "\t\t<span class='icon bullet'></span>\n";
		$echo_string = $echo_string . "\t\t<span class='label'><span class='label title'>Campaign </span>" . $row['campaign_name'] . "</span>\n";
		
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
						
							var info = $(this).parents('li').attr("id").split("_");
							var campaign_group_id = info[1];
							
							action = "install_group_campaign";				
							params = [campaign_group_id, 1];
							var url = "../application/php/appInterface.php?action=" + action + "&params=" + params;
							$.getJSON(url, function(error_code) {
								if (error_code > 0) 
									alert("Could not install group campaign. Please try again.");
							});			
						}
					);
					
					$(".checklist .checkbox-deselect").click(
						function(event) {
							var info = $(this).parents('li').attr("id").split("_");
							var campaign_group_id = info[1];

							action = "install_group_campaign";				
							params = [campaign_group_id, 0];
							var url = "../application/php/appInterface.php?action=" + action + "&params=" + params;
							$.getJSON(url, function(error_code) {
								if (error_code > 0) 
									alert("Could not un-install group campaign. Please try again.");
							});			
							
							event.preventDefault();
							$(this).parents('.checklist').removeClass("selected");
							$(this).parents('.checklist').find(":checkbox").removeAttr("checked");
							$(this).parents('li').css({ backgroundColor: '#e19494' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
							$(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
						}
					);					
					
				});
					
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
					<span>Setup Campaigns</span><a class="slider_back">back</a>
				</div>
				

				
				
				
				<div class="col_content">
				
                    <h1>Setup Campaigns</h1>
                    <div id="module-info" class="module">
                        <div class="module_content">
                        	<p>In this step you will install campaigns.</p>
                        	<p>Please choose a camp type from the drop down to load up a selection of appropriate campaigns.</p>
                        	<p>"You will then be able to select certain missions and assign them to certain groups.</p>
                        </div>
                    </div>
					
                    <div id="module-info" class="module">
                    	<h1>Install Group Campaigns</h1>
						
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
																			
									<? get_campaigns(); ?>
									
                                </ul>
                            </div>
                        </div>
						
                    </div> <!-- <div id="module-info" class="module"> -->
                    
					
                    <div class="wizard_nav">
                        <p><a class="button rfloat" href="content.php?output=gettingstarted5">Next</a></p>
                        <br class="clear" />
                    </div>
					
				</div> <!-- <div class="col_content"> -->	
				
				
				
				
				
				
				
				
				
				
				
			</div> <!-- <div class="slider"> -->

			
			