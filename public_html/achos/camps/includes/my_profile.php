<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$admin_id = $_GET['admin_id'];

$action = "get_staff_details";
$params = $admin_id;
$details = getJson($action,$params);
?>
			<div class="slider">
			
				<div class="col_title">
					<?php echo $details[0]['first'] . " " . $details[0]['last']; ?>'s 
					<span> Profile</span>
				</div>
				
				<div class="col_content camper">
									
                    <div class="two_col left">
                    	<div class="photo">
							<img src="images/generic_user.jpg" height="150"  />
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
				
					<br />
					
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
                                    <span class="content"><?=$details[0]['email'];?></span>
                                </li>
                                <!--<li>
                                    <span class="title">Gender</span>
                                    <span class="content"><?=$details[0]['gender'];?></span>
                                </li>-->
                                <li>
                                    <span class="title">Language</span>
                                    <span class="content"><?=$details[0]['lang'];?></span>
                                </li>
								
                                <li>
                                    <span class="title">Address</span>
                                    <span class="content">
										<?=$details[0]['admin_address1'] . " " . $details[0]['admin_address2'];?>
									</span>
                                    <div class="clear"></div>
                                </li>
								
                                <li>
                                    <span class="title">City</span>
                                    <span class="content"><?=$details[0]['admin_city'];?></span>
                                </li>
                                <li>
                                    <span class="title">State</span>
                                    <span class="content">
										<?=$details[0]['admin_state'];?>
									</span>
                                </li>
                                <li>
                                    <span class="title">Zip</span>
                                    <span class="content">
										<?=$details[0]['admin_postal']; ?>
									</span>
                                </li>
                                <li>
                                    <span class="title">Country</span>
                                    <span class="content">
										<?=$details[0]['admin_country'];?>
									</span>
                                </li>
                                <li>
                                    <span class="title">Phone</span>
                                    <span class="content">
										<?=$details[0]['admin_phone_work'];?>
									</span>
                                </li>
                            </ul>
                        </div>
                    </div>
				</div>
			</div>
