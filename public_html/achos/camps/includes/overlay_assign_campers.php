<?php	
include ("get_camp_id.php");
$camp_id = get_camp_id();

$group_type_id = $_GET["group_type_id"];
$division_id = $_GET["division_id"];
$group_id = $_GET["group_id"];
$group_name = $_GET["group_name"];

$members = array();
$sql = "SELECT u.user_id, u.first, u.last, u.user_photo_id ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "LEFT JOIN member_groups AS mg ON (mg.user_id=u.user_id AND mg.group_type_id=" . $group_type_id . " AND mg.end_date=0) ";
$sql = $sql . "WHERE u.camp_id=" . $camp_id . " ";
$sql = $sql . "AND u.camp_registered IS NOT NULL ";
$sql = $sql . "AND mg.member_group_id IS NULL";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$user_id = $row['user_id'];
	$user_photo_id = $row['user_photo_id'];
	$first = $row['first'];
	$last = $row['last'];
		
	$element = compact('user_id', 'user_photo_id', 'first', 'last');
	array_push($members, $element);	
}
?>
		 <script>
			var camp_id = <?=$camp_id;?>;
			var group_type_id = <?=$group_type_id;?>;
			var division_id = <?=$division_id;?>;
			var group_id = <?=$group_id;?>;
						
			$(document).ready(function() {
								
				$(".checklist .checkbox-select").click( 
					function(event) {
						var li = $(this).parents('li');
						
						event.preventDefault();
						$(this).parents('.checklist').addClass("selected");
						$(this).parents('.checklist').find(":checkbox").attr("checked","checked");
						$(this).parents('li').css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
						$(this).parents('li').find('.progress').show().delay(500).fadeOut(500);							
														
						var user_id = $(this).parents('li').attr("id");	
						var info = $(this).parents('li').attr("name").split(":");
						var name = info[0] + " " + info[1];
						var user_photo_id = info[2];
							
						function_name = "assign_member_group";				
						parameters = [camp_id, user_id, group_type_id, division_id, group_id];
						var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(success) {
							if (success == 0) {
								alert("Could not place camper. Please try again.");
								$(li).find('.checklist').removeClass("selected");
							}
							else {
								var member_group_id = success;
								var li_id = "user_" + member_group_id + "_" + user_id;
								var new_html = 	"<li id='" + li_id + "' name='" + li_id + "'>" + 
												"<a class='link' href='content.php?output=camper_profile&user_id=" + user_id + "'>" + 
												"<div class='image'>" + 
												"<img src='includes/file_view.php?id=" + user_photo_id + "' height='32'>" + 
												"</div>" + 
												"<div class='name'>" + name + "</div>" +
												"</a>" + 
												"<span class='action'>" + 
												"<a href='#' class='remove'>Remove</a>" + 
												"</span>" + 
												"</li>";

								$("#assign_a_camper").before(new_html);	
								
								$("li[name=" + li_id + "]").find('.action a.remove ').click(function() {
									var list_item = $(this).parents('li');
									var info = $(list_item).attr('id').split("_");
									var admin_or_user = info[0];
							
									if (admin_or_user == "admin") {
										var staff_group_id = info[1];
										var function_name = "remove_staff_member";
										var parameters = [staff_group_id];
										var message = "Could not remove staff member. Please try again.";
									}
									else if (admin_or_user == "user") {
										var member_group_id = info[1];
										var user_id = info[2];
										var function_name = "remove_member_group";
										var parameters = [member_group_id, group_id, user_id];
										var message = "Could not remove camper. Please try again.";
									}
						
									var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
									$.getJSON(url, function(success) {
										if (success == true) 
											$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
										else 
											alert(message);
									});												
								});
																
							}
							
						}); // $.getJSON(url, function(error_code) {
						
				}); // $(".checklist .checkbox-select").click( 
			
			}); // READY 
        </script>
		
		<div class="slider">
				
			<div class="col_title">
				<span>Place Campers</span> in <?=$group_name;?>
			</div>
			
			<div class="col_content">
		
				<div class="module"> 
				
					<div class="module_content">
			
						<div class="list">
						
							<ul>
					
								<? for ($mbrno = 0; $mbrno < count($members); $mbrno++) : ?>
								<li id="<?=$members[$mbrno]['user_id'];?>" name="<?=$members[$mbrno]['first'];?>:<?=$members[$mbrno]['last'];?>:<?=$members[$mbrno]['user_photo_id'];?>">
											
									<span class="action">
									
										<span class="checklist">
											<input type="checkbox" class="checkbox" />
									
											<span class="activate">
												<a href="#" title="Activate" class="buttonHover checkbox-select">
													<span class="icon activate"></span>
													Activate
												</a>
											</span>
											
											<span class="deactivate">
												<a href="#" title="Deactivate" class="buttonHover checkbox-deselect">
													<span class="icon deactivate"></span>
													Deactivate
												</a>
											</span>
											
											<span class="progress">
												Progress
											</span>
											
										</span>
										
									</span>

									<span class="icon bullet">
									</span>
							
									<span class="label">
										<span class="label title">
											Camp Member
										</span>
										<?=$members[$mbrno]['first'];?> <?=$members[$mbrno]['last'];?>
									</span>
							
									<div class="clear"></div>
									
								</li>
								<? endfor; ?>
						
							</ul>
							
						</div>
				
					</div>
     
				</div> 
				
            </div>
     
        </div> 
		
