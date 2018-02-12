<?php	
include ("get_camp_id.php");
include ("classes/admin.php");
$camp_id = get_camp_id();

$division_id = $_GET['division_id'];
$group_id = $_GET['group_id'];

$admins = array();
$sql = "SELECT a.admin_id, a.first, a.last ";
$sql = $sql . "FROM admins AS a ";
$sql = $sql . "JOIN admin_auths AS aa USING (admin_id) ";
$sql = $sql . "LEFT JOIN staff_groups AS sg ON (sg.admin_id=a.admin_id AND sg.group_id=" . $group_id . ") ";
//$sql = $sql . "LEFT JOIN groups AS g ON (sg.group_id=g.group_id AND g.division_id=" . $division_id . ") ";
$sql = $sql . "WHERE aa.auth='camp' AND aa.id=" . $camp_id . " AND sg.staff_group_id IS NULL ";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$admin = new admin($row);
	array_push($admins, $admin);
}
 ?>
		 <script>
			var division_id = <?=$division_id;?>;
			var group_id = <?=$group_id;?>;
			
             $(document).ready(function() {
			 
                $(".checklist input:checked").parent().addClass("selected");
								
                $(".checklist .checkbox-select").click(
                    function(event) {
						event.preventDefault();
						$(this).parents('.checklist').addClass("selected");
						$(this).parents('.checklist').find(":checkbox").attr("checked","checked");
						$(this).parents('li').css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
						$(this).parents('li').find('.progress').show().delay(500).fadeOut(500);							
								
						var admin_id = $(this).parents('li').attr("id");
						var info = $(this).parents('li').attr("name").split(":");
						var name = info[0] + " " + info[1];
						var user_photo_id = info[2];
						var staff_photo = "includes/file_view.php?id=" + user_photo_id;
						if (user_photo_id == "") {
							var staff_photo = "images/generic_user_small.png";
						} 
					
						function_name = "assign_staff_group";				
						parameters = [admin_id, group_id];
						var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(success) {
							if (success > 0) {
								var li_id = "admin_" + success;
								var new_html = 	"<li id='" + li_id + "'>" + 
												"<a href='content.php?output=profile&profile=staff&staff_id=" + admin_id + "' class='link'>" + 
												"<div class='image'>" + 
												"<img src='" + staff_photo + "' height='32'>" + 
												"</div>" + 
												"<div class='name'>" + name + "</div>" + 
												"</a>" + 
												"<span class='action'>" + 
												"<a class='remove' href='#'>Remove</a>" + 
												"</span>" + 
												"</li>";
												
								$("#assign_staff_member").before(new_html);
								
								var new_li = document.getElementById(li_id);
								$(new_li).children().find('a.remove').click(function() {
									var li_id = $(this).parents('li').attr('id');
									var info = li_id.split("_");
						
									var staff_group_id = info[1];
									var action = "remove_staff_member";
									var params = [staff_group_id];
									var message = "Could not remove staff member. Please try again.";
									
									var url = "includes/delete_function.php?action=" + action + "&params=" + params;
									$.getJSON(url, function(success) {
										if (success == true) 
											$("#" + li_id).css({backgroundColor: "#ff0000"}).fadeOut("slow");
										else 
											alert(message);
									});				
						
								});

								
								
							}
							else {
								alert("Could not assign staff member. Please try again.");
							}
						});
						
                    }
                );
				
                $(".checklist .checkbox-deselect").click(
                    function(event) {
                        event.preventDefault();
                        $(this).parents('.checklist').removeClass("selected");
                        $(this).parents('.checklist').find(":checkbox").removeAttr("checked");
                        $(this).parents('li').css({ backgroundColor: '#e19494' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
                        $(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
						
						var admin_id = $(this).parents('li').attr("id");
						
						function_name = "deassign_staff_group";				
						parameters = [admin_id, group_id];
						var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(success) {
							if (parameters == false)
								alert("Could not de-assign staff member. Please try again.");
						});
						
                    }
                );
				
				$('select.select').sSelect();
				
            });			
        </script>
		
 			<div class="slider">
			
				<div class="col_title">
					<span>Assign Staff to <?=$group_name;?></span>
				</div>
				
				<div class="col_content">
				
					<div class="module"> 
			
						<div class="module_content">
			
							<div class="list">
								<ul>
					
						<? for ($ano = 0; $ano < count($admins); $ano++) : ?>
						<? $admin = $admins[$ano]; ?>
                        <li id="<?=$admin->admin_id;?>" name="<?=$admin->first;?>:<?=$admin->last;?>:<?=$admin->user_photo_id;?>">
											
							<span class="action">
								<span class="checklist">
									<input type="checkbox" class="checkbox" />
									
									<span class="activate"><a href="#" title="Activate" class="buttonHover checkbox-select"><span class="icon activate"></span>Activate</a></span>
									<span class="deactivate"><a href="#" title="Deactivate" class="buttonHover checkbox-deselect"><span class="icon deactivate"></span>Deactivate</a></span>
									<span class="progress">Progress</span>
								</span>
							</span>

                            <span class="icon bullet">
							</span>
							
                            <span class="label">
								<span class="label title">Staff Member</span>
								<?=$admin->first;?> <?=$admin->last;?>
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
