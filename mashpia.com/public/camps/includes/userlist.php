<?
include ("get_camp_id.php");
$camp_id = get_camp_id();

if (isset($_GET["list"])) {
	$show = $_GET["list"];
	$list = $_GET["list"];
}

if ($list == "campers") {
	$action = "get_camp_members";
	$params = $camp_id;
}
else {
	$action = "get_staff_types";
	$params = "";
	$staff_types = getJson($action, $params);
	
	$action = "get_staff_assignments";
	$params = $camp_id;
	$staff_assignments = getJson($action, $params);	

	$sql = "SELECT count(*) AS unassigned_staff FROM admins WHERE camp_id=" . $camp_id . " AND staff_type_id IS NULL";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$unassigned_staff = $row['unassigned_staff'];

	$total_staff = count($staff_assignments) + $unassigned_staff;	
}
	
$staff_types_id = "";	
if (isset($_GET["staff_types_id"])) {
	$staff_types_id = $_GET["staff_types_id"]; 
	$show = "staff"; 
}
	
if (isset($_GET["view"])) 
	$view = $_GET["view"];
else
	$view = "";

	//If displaying "All Staff" or "All Campers" use this as the page title.
	//If displaying "All Staff" then split them into groups of staff types with a title, as done on this page.
	//Otherwise, the page title should be the name of the staff type and just display that staff typew w/o a title.


switch ($show) {
	case "staff":
		if ($staff_types_id=='0') {
			$page_title="All Staff";
		} 
		else if ($staff_types_id=='-1') {
			$page_title="Unassigned";
		} 
		else {
			$page_title="Counselors"; //get_staff_type_name
		}
	break;
		
	case "campers": 
		if ($view == 'unassigned') {
			$page_title = "Unassigned Campers";
		} 
		else if ($view == 'register') {
			$page_title = "Register Campers";
		} 
		else {
			$page_title = "All Campers";
		}
	break;
} 
?>
			 <script>
                 $(document).ready(function() {
                    $(".register .button").click(
                        function(event) {
							
$.get('./includes/registerCamper.php',{id:$(this).attr('title')}, function(data){ alert(data); });
							event.preventDefault();
							$(this).parents('li').addClass("selected");
							$(this).parents('li').find('.action').append('<span class="progress">Progress</span>').find('.progress').show().delay(500).fadeOut(500,function(){
					

$(this).parents('li').slideUp('fast',function(){
									$(this).remove();
								})
							});
                        }
                    );
                });
            </script>

			<div class="slider">
                <div class="col_title">
					<span><?=$page_title?></span>
				</div>
				
				<div class="col_content">
				
                    <div class="module" id="module-info">
					
                        <div class="module_content">
						
							<? if ($show == 'campers') : ?>
                        	<h1>Camper Stats</h1>
							<? else : ?>
							<h1>Staff Stats</h1>
							<? endif; ?>
							
							<? if ($show == 'campers') : ?>
                            <ul class="stats">
                            	<li>Camper<span>53</span></li>
                            	<li>Camper - Assigned<span>41</span></li>
                            	<li>Camper - Non-Assigned<span>12</span></li>
                            </ul>
                            <ul class="stats">
                            	<li>Counselors<span>53</span></li>
                            	<li>Learning Teachers<span>53</span></li>
                            	<li>Waiters<span>53</span></li>
                            </ul>
                            <ul class="stats">
                            	<li>Lifeguards<span>53</span></li>
                            	<li>General Staff<span>53</span></li>
                            	<li>Head Staff<span>53</span></li>
                            </ul>
							<? else : ?>
								<ul class="stats">
									<li>Staff<span><?=$total_staff;?></span></li>
									<li>Staff - Assigned<span><?=count($staff_assignments);?></span></li>
									<li>Staff - Non-Assigned<span><?=$unassigned_staff;?></span></li>
								</ul>
								
								<? for ($cntr = 0; $cntr < count($staff_assignments); $cntr++) : ?>
									<? $remainder = $cntr % 3; ?>
										
									<? if ($remainder == 0) : ?>
									<ul class="stats">
									<? endif; ?>
										
											<li>
												<?=$staff_assignments[$cntr]["type_name"];?>
												<span>
													<?=$staff_assignments[$cntr]["number_of_staff"];?>
												</span>
											</li>
											
									<? if ($remainder == 2 || $cntr == (count($staff_assignments) - 1)) : ?>
									</ul>
									<? endif; ?>
								<? endfor; ?>							
							<? endif; ?>
							
                            <div class="clear"></div>
                        </div>
						
                    </div>
                    <? if ($staff_types_id==0) {
					
				
				if ($show == 'campers') {
					$params = $camp_id;
					if ($view == 'register') {	
						$action = "get_register_camp_members"; 
					}
					elseif ($view == 'unassigned') {
						$action = "get_unassigned_camp_members"; 
					}
					else {
						$action = "get_camp_members";	
					}
				}
				elseif ($show == 'staff') {
					if ($staff_types_id = "")
						$action = "get_all_staff";
					else 
						$action = "get_staff_type";
						$params = $camp_id . "," . $staff_types_id;
				}

				if ($show != 'campers') {
					if ($view == 'register') {
						$params = 'register';
					}
					else {
						$params = $camp_id;
					}
				}

				//$params = $camp_id;
				//echo "2) ACTION:" . $action . " PARAMS:" . $params . "<br />";
				$all_staff = getJson($action, $params);
				// if displaying all staff, loop thru staff type names and display as title
                    ?>
                    <!--<h1>Counselors</h1>-->
                    <? } ?>
					<div class="module lists" id="lists-userlist">
						<div class="module_content">
							<ul>
								
<? // loop thru staff (all {split by staff types} OR specific staff type OR unassigned staff) OR
	// loop campers (all OR unassigned OR register)
	for ($i = 0; $i < count($all_staff); $i++) : 
?>
									
								<li id="CamperID">
									<a class="link" href="content.php?output=profile&profile=<?=$show?>&<?=$show?>_id=<?php if ($show == 'campers') { echo $all_staff[$i]['user_id']; } else { echo $all_staff[$i]['admin_id']; } ?>">
									
										<div class="image">
										<? if ($list == "campers" && $all_staff[$i]['user_photo_id'] > 0) : ?>
											<img src="includes/file_view.php?id=<?=$all_staff[0]['user_photo_id'];?>" height="32" />
										<? else :?>
											<img src="images/generic_user_small.png" height="32" />
										<? endif; ?>
										</div>
										
										<div class="name"><?php echo $all_staff[$i]['first'] . " " . $all_staff[$i]['last']; ?></div>
										<div class="dropdowns"><? //display dropdowns for staff (staff type) OR Camper (bunk AND Learning Class AND League ---Not yet ready to implement ?></div>
									</a>
									<span class="action">
										<? if ($list == "campers") : ?>
										<!--<span class="assign"><a href="includes/overlay_groups_two.php?camp_id=<?=$camp_id;?>&user_id=<?=$all_staff[$i]['user_id'];?>" title="Assign Groups" class="button overlay"><span class="icon"></span>Assign Groups</a></span>-->
										<? else : ?>
										<span class="assign"><a href="includes/overlay_staff_types.php?admin_id=<?=$all_staff[$i]['admin_id'];?>" title="Assign" class="button overlay"><span class="icon"></span>Assign Staff Type</a></span>
										<? endif; ?>
                                    </span>
                                    <? if ($view=='register') {?>
									<span class="action">
                                            <span class="register"><a href="#" title="<?php echo $all_staff[$i]['user_id']; ?>" class="button"><span class="icon"></span>Register</a></span>
                                    </span>
                                    <? } ?>
								</li>
								
<? endfor;
	//do not show "add new row" for "register campers" page
 ?>
								
								
								<li>
									<? if ($list == "campers") : ?>
									<a class="link" href="content.php?output=camperadd">
									<? else : ?>
									<a class="link" href="content.php?output=staffadd">
									<? endif; ?>
									
										<div class="icon"></div>
										<? if ($list == "campers") : ?>
										<div class="name">Add New Camper</div>
										<? else : ?>
										<div class="name">Add New Staff Member</div>
										<? endif; ?>
									</a>
								</li>
							</ul>
						</div>
					</div>
  				</div>
			</div>
