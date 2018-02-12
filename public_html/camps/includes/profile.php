<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();
$user_id = $_GET['campers_id'];

$show =  $_GET['profile'];

if ($show == 'campers') {	
	$action = "get_camper_details";	
	$params = $_GET['campers_id'];
	$details = getJson($action,$params);
	
	$action = "get_member_groups";	
	$params = $user_id;
	$member_groups = getJson($action,$params);	
}	
elseif ($show == 'staff') {
	$action = "get_staff_details";
	$params = $_GET['staff_id'];
	$details = getJson($action,$params);
}



  /* include('includes/SimpleImage.php');
   $image = new SimpleImage();
   $image->load('includes/file_view.php?id='. $details[0]['user_photo_id']);
   $image->resize(150,150);
   //$image->crop('1:1');
   $image->output();*/



?>
			<div class="slider">
			
				<div class="col_title">
					<?php echo $details[0]['first'] . " " . $details[0]['last']; ?>'s 
					<span> Profile</span>
				</div>
				
				<div class="col_content camper">
				
					<div class="module" id="lists-stats">
					
                        <div class="module_content clearfix">
                        	<h1>Stats</h1>
                            <ul class="stats">
                            	<li>Points<span>10</span></li>
                            	<li>Trips<span>10</span></li>
                            	<li>Pending Rewards<span>10</span></li>
                            </ul>
                            <ul class="stats">
                            	<li>Points<span>10</span></li>
                            	<li>Rewards<span>41</span></li>
                            	<li>Medals<span>12</span></li>
                            </ul>
                            <ul class="stats">
                            	<li>Points<span>10</span></li>
                            	<li>Trips<span>10</span></li>
                            	<li>Pending Rewards<span>10</span></li>
                            </ul>
                        </div>

					</div>
					
                    <div class="two_col left">
                    	<div class="photo">
							<? if ($show == 'campers' && $details[0]['user_photo_id'] > 0) : ?>	
                            	<!--<img src="includes/image.php/image.jpg?width=100&amp;height=100&amp;cropratio=1:1&amp;image=/includes/file_view.php?id=<?=$details[0]['user_photo_id'];?>" alt="Don't forget your alt text" />-->							
								<img src="includes/file_view.php?id=<?=$details[0]['user_photo_id'];?>" height="150" />
							<? else : ?>
								<img src="images/generic_user.jpg" height="150"  />
							<? endif; ?>
						</div>
                    </div>
					
                    <div class="two_col right">
                        <div class="module lists" id="lists-camper-profile">
                            <div class="module_content">
                                <ul>
                                    <li>
                                        <span class="title">Email</span>
                                        <span class="content"><?php echo $details[0]['email']; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
					
					<!-- ********** MEMBER GROUPS ********** -->
					<? if ($show == 'campers') : ?>
                    <h1>Groups</h1>
					<? endif; ?>
					
                    <div class="module lists" id="lists-camper-groups">
					
                        <div class="module_content">
                            <ul>
								<? for ($mgno = 0; $mgno < count($member_groups); $mgno++) : ?>
								<? $member_group = $member_groups[$mgno]; ?>
                                <li>
									<a class="link" href="content.php?output=group&gid=">
                                        <span class="title"><?=$member_group['group_type_name'];?></span>
                                        <span class="content"><?=$member_group['group_name'];?></span>
                                    </a>
                                </li>
								<? endfor; ?>
                                <!--<li>
									<a href="#">
                                        <span class="title">Bog War</span>
                                        <span class="content unassigned">Unassigned</span>
                                    </a>
                                </li>-->
                            </ul>
                        </div>
						
                    </div>
					<!-- ********** MEMBER GROUPS ********** -->
					
                    <h1>Info</h1>
                    <div class="module lists" id="lists-camper-info">
                        <div class="module_content">
                            <ul>
                                <li>
                                    <span class="title">First Name</span>
                                    <span class="content"><?=$details[0]['first'];?></span>
                                </li>
                                <li>
                                    <span class="title">Last Name</span>
                                    <span class="content"><?=$details[0]['last'];?></span>
                                </li>
								<? if ($details[0]['first_he']) : ?>
                                <li>
                                    <span class="title">Hebrew First Name</span>
                                    <span class="content"><?=$details[0]['first_he'];?></span>
                                </li>
                                <li>
                                    <span class="title">Hebrew Last Name</span>
                                    <span class="content"><?=$details[0]['last_he'];?></span>
                                </li>
								<? endif; ?>
                                <li>
                                    <span class="title">Email</span>
                                    <span class="content"><?$details[0]['email'];?></span>
                                </li>
                                <li>
                                    <span class="title">Gender</span>
                                    <span class="content"><?=$details[0]['gender'];?></span>
                                </li>
                                <li>
                                    <span class="title">Language</span>
                                    <span class="content"><?=$details[0]['lang'];?></span>
                                </li>
								
                                <li>
                                    <span class="title">Address</span>
                                    <span class="content">
									<? 
									if ($details[0]['user_address1']) 
										echo $details[0]['user_address1'] . " " . $details[0]['user_address2']; 
									else
										echo $details[0]['admin_address1'] . " " . $details[0]['admin_address2'];
									?>
									</span>
                                    <div class="clear"></div>
                                </li>
								
                                <li>
                                    <span class="title">City</span>
                                    <span class="content">
									<?
										if ($details[0]['user_city']) 
											echo $details[0]['user_city'];
										else 
											echo($details[0]['admin_city']); 
									?>
									</span>
                                </li>
                                <li>
                                    <span class="title">State</span>
                                    <span class="content">
									<? 
										if ($details[0]['user_state']) 
											echo $details[0]['user_state']; 
										else 
											echo $details[0]['admin_state']; 
									?>
									</span>
                                </li>
                                <li>
                                    <span class="title">Zip</span>
                                    <span class="content">
										<? 
											if ($details[0]['user_postal']) 
												echo $details[0]['user_postal']; 
											else 
												echo $details[0]['admin_postal']; 
										?>
									</span>
                                </li>
                                <li>
                                    <span class="title">Country</span>
                                    <span class="content">
										<?
											if ($details[0]['user_country']) 
												echo $details[0]['user_country']; 
											else 
												echo $details[0]['admin_country']; 
										?>
									</span>
                                </li>
                                <li>
                                    <span class="title">Phone</span>
                                    <span class="content">
										<?
											if ($details[0]['user_phone']) 
												echo $details[0]['user_phone'];
											else 
												echo $details[0]['admin_phone_work']; 
										?>
									</span>
                                </li>
                            </ul>
                        </div>
                    </div>
				</div>
			</div>
