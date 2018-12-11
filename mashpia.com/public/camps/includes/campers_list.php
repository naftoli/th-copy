<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/user.php");

$view = "";
$campers = array();
if (isset($_GET['view'])) {
	$view = $_GET['view'];
	
	if ($view == "unassigned") {
		$sql = "SELECT u.* FROM users AS u ";
		$sql = $sql . "LEFT JOIN member_groups AS mg USING (user_id) ";
		$sql = $sql . "WHERE u.camp_id=" . $camp_id . " AND user_start_date IS NOT NULL AND mg.member_group_id IS NULL ";
		$sql = $sql . "ORDER BY u.first, u.last";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) {
			$camper = new user($row);
			array_push($campers, $camper);	
		}		
	}	
}
else {
	$sql = "SELECT * FROM users WHERE camp_id=" . $camp_id . " AND user_start_date IS NOT NULL AND camp_registered IS NOT NULL ORDER BY first, last";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$camper = new user($row);
		array_push($campers, $camper);	
	}
}
?>
			<script>
				$(document).ready(function() {
				
					$('.action a.remove ').click(function() {
						// ***** Change function to update status when camp GAN is done revieing ***** //
						var list_item = $(this).parents('li');
						var full_name = $(list_item).find("div[name=full_name]").html();
						var confirm_delete = window.confirm("Are you sure you want to delete " + full_name + "?") 
						if (confirm_delete) {
							var info = $(list_item).attr("id").split("_");
							var user_id = info[1];						
							var function_name = "delete_user";
							var parameters = [user_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;						
							$.getJSON(url, function(success) {
								if (success == false) 
									alert("Could not delete user. Please try again.")
								else
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
							});						
						}											
					});
					
				});
			</script>
			
			<div class="slider">
			
				<div class="col_title">
					<? if ($view == "unassigned") : ?>
					<span>Unassigned Campers</span>
					<? else : ?>
					<span>Campers</span>
					<? endif; ?>					
				</div>
				
				<div class="col_content">
				
					<? include ("campers_header.php"); ?>
				
					<div class="module lists" id="lists-grouptypes">
					
						<div class="module_content">
						
							<ul>
							
								<? for ($cmpr = 0; $cmpr < count($campers); $cmpr++) : ?>	
								<? $camper = $campers[$cmpr]; ?>
								
								<li id="u_<?=$camper->user_id?>">
									<a class="link" href="content.php?output=camper_profile&user_id=<?=$camper->user_id;?>">
																	
										<div class="image">
											<? if ($camper->user_photo_id > 0) : ?>
											<img src="includes/file_view.php?id=<?=$camper->user_photo_id;?>" height="32" width="32" />
											<? //else: ?>
											<!--<img height="32" width="32" src="images/generic_user_small.png">-->
											<? endif; ?>
										</div>
									
										<div class="name">
											<div class="title" name="full_name"><?=$camper->first . " " . $camper->last; ?></div>
											<div class="title">
												Points
											</div>
										</div>
										
									</a>
									
									<span class="action">
										<a class="remove" href="#">Remove</a>
									</span>									
									
								</li>
								<? endfor; ?>
								
							</ul>
							
						</div> <!-- <div class="module_content"> -->
						
					</div> <!-- <div class="module lists" id="lists-grouptypes"> -->
					
				</div> <!-- <div class="col_content"> -->
				
			</div> <!-- <div class="slider"> -->
