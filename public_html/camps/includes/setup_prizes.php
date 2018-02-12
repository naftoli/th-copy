<?php 
include ("get_camp_id.php");
$camp_id = get_camp_id();

$sql = "SELECT gp.* ";
$sql = $sql . "FROM global_prizes AS gp ";
$sql = $sql . "LEFT JOIN prizes_camp AS pc ON (gp.prize_id=pc.global_prize_id AND pc.camp_id=" . $camp_id. ") ";
$sql = $sql . "WHERE pc.prize_id IS NULL";
$query = mysql_query($sql);
?>

	<script>
		$(document).ready(function() {
			
			var camp_id = <?=$camp_id;?>;
						
            $(".checklist .checkbox-select").click(
				function(event) {	
					var list_item = $(this).parents('li');
					var prize_id = $(this).parents('li').attr("id");
					
					function_name = "install_prize";				
					parameters = [camp_id, prize_id];
					var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(install) {
						if (install == false) 
							alert("Could not install prize. Please try again.");
						else 
							$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
					});
                }       
            );		
		});				
	</script>

	<div class="slider">
	
		<div class="col_title">
			<span>Install Prizes</span>
		</div>
		
		<div class="col_content">
		
			<div id="module-info" class="module list_prizes">
					
				<h1>Install Prizes</h1>
						
					<div class="module_content">
					
						<div class="list">
						
							<ul>
							
								<? while ($row = mysql_fetch_assoc($query)) : ?>
								<li id="<?=$row['prize_id'];?>">
								
									<span class="action">
									
										<span class="checklist">
										
											<input type="checkbox" class="checkbox">
											
											<span class="install">
												<a href="#" title="Install" class="button checkbox-select">
													<span class="icon">
													</span>
													Install
												</a>
											</span>
											
											<span class="uninstall">
												<a href="#" title="Uninstall" class="button checkbox-deselect">
													<span class="icon"></span>
													Uninstall
												</a>
											</span>											
											
										</span>
											
									</span>
										
										
									<span class="label" style="width:45px;">
										<img src="includes/file_view.php?id=<?=$row['prize_image_id'];?>" width="32" height="32" />
									</span>
										
									<span class="label" style="width:125px;">
										<span class="label title">
											Prize 
										</span>
										<span id="prize_name_<?=$row['prize_id'];?>"><?=$row['prize_name'];?></span>
									</span>
												
									<span class="label" style="width:125px;">
										<span class="label title">
											Description 
										</span>
										<span id="prize_description_<?=$row['prize_id'];?>"><?=$row['prize_description'];?></span>
									</span>

									<span class="label" style="width:125px;" id="points_span_<?=$row['prize_id'];?>">
										<span class="label title">
											Points
										</span>
										<span id="prize_points_<?=$row['prize_id'];?>"><?=$row['prize_points'];?></span>
									</span>

									<div class="clear">
									</div>
								</li>								
								<? endwhile; ?>
								
								
							</ul>
							
						</div>
						
					</div>
					
				</div>		
		
			</div>

		</div>