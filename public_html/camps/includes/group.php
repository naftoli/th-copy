<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$group_type_id = $_GET['group_type_id'];	
$division_id = $_GET['division_id'];	
$group_id = $_GET['group_id'];	
$group_name = $_GET['group_name'];	

include ("classes/admin.php");
include ("classes/group.php");
include ("classes/user.php");
$sql = "SELECT * FROM groups WHERE group_id=" . $group_id;
$query = mysql_query($sql);	
$row = mysql_fetch_assoc($query);
$group = new group($row);
$group->get_members();
$group->get_staff();
$group->get_group_points($camp_id);
				
?>
			 <script>
				var group_id = <?=$group_id;?>;
				var group_name = "<?=$group_name;?>";
				
				var window_name = 0;
				
				$(document).ready(function() {
				
					$('.action a.remove ').click(function() {
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
                });
				
				function generate_report() {
					window_name++;			
					url = "http://www.mashpia.com/camps/includes/group_points_report.php?group_id=" + group_id + "&group_name=" + group_name;
					window.open(url, window_name, 'height=760,width=760,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=no,status=no');
				}
            </script>
			
			<div class="slider">
			
				<div class="col_title">
					<span><?=$group_name;?></span>
				</div>
				
				
				<div class="col_content">
				
					<!-- ****************************** GROUP STATS ****************************** --> 
					<div class="module" id="lists-stats">
					
                        <div class="module_content clearfix">
						
                        	<h1>Group Stats</h1>
							
                            <ul class="stats">
                            	<li>Campers<span><?=count($group->members);?></span></li>
                            	<li>Staff<span><?=count($group->admins);?></span></li>
                            </ul>
							
                            <ul class="stats">
                            	<li>Group Points<span><?=$group->points;?></span></li>
								<li>Points per member<span><?=$group->average_points;?></span></li>
                            </ul>
							
                            <ul class="stats">
                            </ul>
							
                        </div>
						
					</div>
					<!-- ****************************** GROUP STATS ****************************** --> 
					
					<!-- ****************************** GROUP POINTS REPORT  ****************************** -->
					<div class="module lists" id="lists-group-staff">
						<div class="module_content">							
							<ul>								
								<h1>Group Points</h1>

								<li>								
									<a href="#" onclick="generate_report();">
										<h1>Group Points Report</h1>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<!-- ****************************** GROUP POINTS REPORT  ****************************** -->
					
					<!-- ****************************** STAFF ****************************** -->                    
					<div class="module lists" id="lists-group-staff">
						<div class="module_content">
							
							<ul>
								
								<h1>Staff</h1>

								<? for ($sno = 0; $sno < count($group->admins); $sno++) : ?>
									<? $admin = $group->admins[$ano]; ?>
									<li id="admin_<?=$staff[$sno]['staff_group_id'];?>">
									
										<a class="link" href="content.php?output=staff_profile&admin_id=<?=$staff[$sno]['admin_id'];?>">
											<div class="image"><img src="<?=($admin->user_photo_id > 0 ? "includes/file_view.php?id=" . $admin->user_photo_id : "images/generic_user_small.png") ?>" height="32" /></div>
											<div class="name"><span class="title"><?=$admin->first;?> <?=$staff[$sno]['last'];?></span><span class="title"><?//=$staff_type_name;?></span></div>
										</a>
										<span class="action">
											<a href="#" class="remove">Remove</a>
										</span>
									</li>
								<? endfor; ?>
								
									<li id="assign_staff_member" name="assign_staff_member">
										<a class="overlay" href="content.php?output=staff_overlay&division_id=<?=$division_id;?>&group_id=<?=$group_id;?>&group_name=<?=urlencode($group_name);?>">										
											<div class="icon"></div>
											<div class="name">Assign a Staff Member to this Group</div>
										</a>
									</li>								
							</ul>
						</div>
					</div>
					<!-- ****************************** STAFF ****************************** -->
					
									
					<!-- ****************************** CAMPERS ****************************** -->
					<input type="hidden" name="new_user_id" id="new_user_id" value="">
					
					<div class="module lists" id="lists-grouptypes">
						<div class="module_content">
							<ul>
								
								<h1>Campers</h1>
							
								<? for ($uno = 0; $uno < count($group->members); $uno++) : ?>
								<? $user = $group->members[$uno]; ?>
								<? $member_group_id = $group->member_group_id; ?>
								
								<li id="user_<?=$member_group_id;?>_<?=$user->user_id;?>" >
									<a class="link" href="content.php?output=camper_profile&user_id=<?=$user->user_id;?>">
									
										<div class="image">
											<? if ($user->user_photo_id > 0) : ?> 
												<img src="includes/file_view.php?id=<?=$user->user_photo_id;?>" height="32" />
											<? else : ?>
												<img src="includes/images/generic_user_small.png" height="32" />
											<? endif; ?>
										</div>
										
										<div class="name">
											<div class="title">
												<?=$user->first;?> <?=$user->last;?>
											</div>
											<div class="title">Points: <?=$user->task_points;?></div>
										</div>
									</a>
                                    <span class="action">
                                    	<a href="#" class="remove">Remove</a>
                                    </span>
								</li>
								<? endfor; ?>
								
								<li id="assign_a_camper" name="assign_a_camper">
									<a class="overlay" href="content.php?output=overlay_assign_campers&group_type_id=<?=$group_type_id;?>&division_id=<?=$division_id;?>&group_id=<?=$group_id;?>&group_name=<?=urlencode($group_name);?>">
										<div class="icon"></div>
										<div class="name">Place a Camper in this Group</div>
									</a>								
								</li>								
								
							</ul>
						</div>
					</div>
					<!-- ****************************** CAMPERS ****************************** -->
					
				</div> <!-- <div class="col_content"> -->
				
			</div> <!-- <div class="slider"> -->
