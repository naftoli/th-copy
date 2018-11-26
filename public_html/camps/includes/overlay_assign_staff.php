<?php	
include ("get_camp_id.php");
$camp_id = get_camp_id();

$staff_type_id = $_GET["staff_type_id"];
$type_name = $_GET["type_name"];

include ("classes/admin.php");
$admins = array();
$sql = "SELECT * FROM admins WHERE camp_id=" . $camp_id . " AND staff_type_id IS NULL";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$admin = new \camps\classes\admin($row);
	array_push($admins, $admin);
}
?>
		 <script>
			var staff_type_id = <?=$staff_type_id;?>;
			
			$(".checklist .checkbox-select").click(function(event) {
				var new_staff_member = $("#assign_a_staff_member");
				
				var list_item = $(this).parents("li");
				var info = $(list_item).attr("id").split("_");
				var admin_id = info[1];
				var first = info[2];
				var last = info[3];
				
				var function_name = "edit_staff_type";				
				var parameters = [admin_id, staff_type_id];
				var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
				$.getJSON(url, function(success) {
					if (success == false)
						alert("Could not assign staff member. Please try again.");
					else {
						var new_html = get_new_html(admin_id, first, last);
						$(new_staff_member).before(new_html);					
						$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
					}
				});
			});	
			
			function get_new_html(admin_id, first, last) {
				var new_html = "<li id='a_" + admin_id + "'><a href='content.php?output=staff_profile&admin_id=" + admin_id + "' class='link'><div class='name'>" + first + " " + last + "</div></a><span class='action'><a class='remove' href='#'>Remove</a></span></li>";
				return new_html;
			}													
        </script>
		
		<div class="slider">
				
			<div class="col_title">
				<span>Place Staff Member</span> in <?=$type_name;?>
			</div>
			
			<div class="col_content">
		
				<div class="module"> 
				
					<div class="module_content">
			
						<div class="list">
						
							<ul>
					
								<? for ($ano = 0; $ano < count($admins); $ano++) : ?>
								<li id="a_<?=$admins[$ano]->admin_id;?>_<?=$admins[$ano]->first;?>_<?=$admins[$ano]->last;?>">
											
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
											Staff Member
										</span>
										<div name="admin_name"><?=$admins[$ano]->first;?> <?=$admins[$ano]->last;?></div>
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
		
