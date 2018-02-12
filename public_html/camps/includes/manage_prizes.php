<?php 
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/camp_prize.php");

$prizes = array();
$sql = "SELECT * FROM prizes_camp WHERE camp_id=" .  $camp_id . " and installed=1";
$query = mq($sql);
while ($row = mysql_fetch_assoc($query)) {
	$prize = new camp_prize();
	$prize->new_camp_prize($row);	
	array_push($prizes, $prize);
}
?>

	<script type="text/javascript" src="includes/functions.js"></script>
	
	<script>
		$(document).ready(function() {
			
			var camp_id = <?=$camp_id;?>;
									
			$(".edit a, .edit_row input[type='text']").die().live('click',
				function(event) {
					event.preventDefault();
					$(this).parents('li').addClass("editing");
				}
			);
			
			$(".save a").die().live('click', 
				function(event) {
					$(this).parents('li').removeClass("editing");
				 
					var info = $(this).parents("li").attr("id").split("_");
					var prize_id = info[1];
					var prize_name = $(this).parents("li").find("#prize_name").val();
					var prize_description = $(this).parents("li").find("#prize_description").val();
					var prize_points = $(this).parents("li").find("#prize_points").val();
					var prize_available = $(this).parents("li").find("#prize_available").val();
				
					function_name = "update_prize";				
					parameters = [prize_id, prize_name, prize_description, prize_points, prize_available];
					var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(error_code) {
						if (error_code == false) 
							alert("Could not update prize. Please try again.");
					});			
				}
			);
		
			$(".remove a").die().live('click', 
				function(event) {
					var list_item = $(this).parents("li");
					var info = $(list_item).attr("id").split("_");
					var prize_id = info[1];
					
					function_name = "delete_prize";				
					parameters = [prize_id];
					var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(deletion) {
						if (deletion == false) 
							alert("Could not delete prize. Please try again.");
						else
							$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");	
					});			
				}
			);
						
		});	
	</script>

	<div class="slider">
	
		<div class="col_title">
			<span>Manage Store</span>
		</div>
		
		<div class="col_content">																							
			<h1>Manage Prizes</h1>
									
			<div id="module-info" class="module list_prizes">	
						
				<div class="module_content">
					
					<div class="list">
						
						<ul>
						
							<li><h1>Prizes</h1></li>
							
							<? for ($pno = 0; $pno < count($prizes); $pno++) : ?>
							<? $prize = $prizes[$pno]; ?>
							<li id="cp_<?=$prize->prize_id;?>" class="edit_row">								
									
								<form>									
									<input type="hidden" name="prize_id" id="prize_id" value="<?=$prize->prize_id;?>">
											
									<span class="label" style="width:45px;">
										<a class="overlay" href="content.php?output=overlay_add_prize&edit_prize=<?=$prize->prize_id;?>"><img src="<?=isset($prize->prize_image_id)? "includes/file_view.php?id=" . $prize->prize_image_id:"images/generic_prize.png"?>" width="32" height="32" />
										</a>
									</span>
												
									<span class="label">
										<span class="label title">Prize</span>
										<input type="text" style="width:145px;"name="prize_name" id="prize_name" value="<?=$prize->prize_name;?>" />
									</span>
											
									<span class="label">
										<span class="label title">Description</span>
										<input type="text" style="width:145px;"name="prize_description" id="prize_description" value="<?=$prize->prize_description;?>" />
									</span>
																										
									<span class="label">
										<span class="label title">Points</span>
										<input type="text" onkeypress='return number_validation(event);' style="width:145px;"name="prize_points" id="prize_points" value="<?=$prize->prize_points;?>" />
									</span>
											
									<span class="label">
										<span class="label title">Available</span>
										<input type="text" onkeypress='return number_validation(event);' style="width:145px;"name="prize_available" id="prize_available" value="<?=$prize->prize_available;?>" />
									</span>									
											
									<span class="action">
										<span class="edit">
											<a title="Edit" href="#"><span class="icon"></span>Edit</a>
										</span>
												
										<span class="save">
											<a title="Save" href="#"><span class="icon"></span>Save</a>
										</span>											
									</span>
												
									<span class="action">
										<span class="remove">
											<a title="Delete" href="#">Delete</a>
										</span>
									</span>
									
									<div class="clear"></div>
								</form>																																		
									
							</li>
							<? endfor; ?>								
								
							<li>
								<span class="icon add"></span>
								<span class="label">																
									<a id="add_new_prize" href="content.php?output=overlay_add_prize" class="overlay">Add Prize</a>
								</span>
								<div class="clear"></div>
							</li>								
								
						</ul>
							
					</div> <!-- <div class="list"> -->
						
				</div> <!-- <div class="module_content"> -->
		
			</div> <!-- <div id="module-info" class="module list_prizes"> -->
			
		
			</div> <!-- <div class="col_content"> -->

		</div> <!-- <div class="slider"> -->