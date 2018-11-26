<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();
$admin_id = $_GET['admin_id'];

include ("classes/admin.php");
include ("classes/group_type.php");
include ("classes/division.php");
include ("classes/group.php");

$sql = "SELECT * FROM admins WHERE admin_id = " . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \camps\classes\admin($row);
$admin->get_admin_groups();
?>

			<script>
				$(document).ready(function() {
				
					var admin_id = <?=$admin_id;?>;
				
					$("a[name=make_text_editable]").click(
						function(event) {
							$(this).parents('ul').find(".save").click(
								function(event) {
									var field_name = $(this).parents('li').attr("id");
									var value = $(this).parents('li').find(".content").html();
									
									function_name = "update_admin";				
									parameters = [admin_id, field_name, value];
									var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
									$.getJSON(url, function(update) {
										if (update == false)  {
											alert("Could not update staff member. Please try again");
										}
									});
								}
							);
						}
					);
					
				});
			</script>

			<div class="slider">
			
				<div class="col_title">
					<?=$admin->first . " " . $admin->last;?>'s 
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
							<? if ($admin->staff_photo_id > 0) : ?>	
								<img src="includes/file_view.php?id=<?=$admin->staff_photo_id;?>" height="150" />
							<? else : ?>
								<img src="images/generic_user.jpg" height="150"  />
							<? endif; ?>
						</div>
                    </div>
					
                    <div class="two_col right">
                        <div class="module lists editableText" id="lists-camper-profile">
                            <div class="module_content">
                                <ul>
                                    <li id="username">
                                        <span class="title">Username</span>
                                        <div class="content"><?=$admin->username; ?></div>
                                    </li>
                                    <li id="password">
                                        <span class="title">Password</span>
                                        <div class="content"><?=$admin->password; ?></div>
                                    </li>
                             	 <li>
                             	       <a class="inline_link makeTextEditable" name="make_text_editable" href="#">
							<div class="name">Edit</div>
						</a>
                            	 </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
									
					<!-- ********** STAFF GROUPS ********** -->
                    <h1>Groups</h1>
					
                    <div class="module lists" id="lists-camper-groups">
					
                        <div class="module_content">
                            <ul>
								<? for ($gno = 0; $gno < count($admin->groups); $gno++) : ?>
								<? $group = $admin->groups[$gno]; ?>
                                <li>
									<a class="link" href="content.php?output=group&group_type_id=<?=$group->division->group_type->group_type_id;?>&division_id=<?=$group->division->division_name;?>&group_id=<?=$group->group_id;?>&group_name=<?=urlencode($group->group_name);?>">
                                        <span class="title"><?=$group->division->group_type->group_type_name;?></span>
                                        <div class="content"><?=$group->group_name;?></div>
                                    </a>
                                </li>
								<? endfor; ?>
                            </ul>
                        </div>
						
                    </div>
					<!-- ********** STAFF GROUPS ********** -->
									
                    <h1>Info</h1>
                    <div class="module lists editableText" id="lists-camper-info">
                        <div class="module_content">
                            <ul>
                                <li id="first">
                                    <span class="title">First Name</span>
                                    <div class="content"><?=$admin->first;?></div>
                                </li>
								
                                <li id="last">
                                    <span class="title">Last Name</span>
                                    <div class="content"><?=$admin->last;?></div>
                                </li>
								
								<? if ($admin->first_he) : ?>
                                <li id="first_he">
                                    <span class="title">Hebrew First Name</span>
                                    <div class="content"><?=$admin->first_he;?></div>
                                </li>
								
                                <li id="last_he">
                                    <span class="title">Hebrew Last Name</span>
                                    <div class="content"><?=$admin->last_he;?></div>
                                </li>
								<? endif; ?>
								
                                <li id="admin_email">
                                    <span class="title">Email</span>
                                    <div class="content"><?=$admin->admin_email;?></div>
                                </li>

                                <li id="lang">
                                    <span class="title">Language</span>
                                    <div class="content"><?=$admin->lang;?></div>
                                </li>
								
                                <li id="admin_address1">
                                    <span class="title">Address</span>
                                    <div class="content"><?=$admin->admin_address1;?></div>
                                    <div class="clear"></div>
                                </li>
								
                                <li id="admin_address2">
                                    <span class="title"></span>
                                    <div class="content"><?=$admin->admin_address2;?></div>
                                    <div class="clear"></div>
                                </li>
								
                                <li id="admin_city">
                                    <span class="title">City</span>
                                    <div class="content"><?=$admin->admin_city;?> 
									</div>
                                </li>
								
                                <li id="admin_state">
                                    <span class="title">State</span>
                                    <div class="content"><?=$admin->admin_state;?> </div>
                                </li>
								
                                <li id="admin_postal">
                                    <span class="title">Zip</span>
                                    <div class="content"><?=$admin->admin_postal;?></div>
                                </li>
								
                                <li id="admin_country">
                                    <span class="title">Country</span>
                                    <div class="content"><?=$admin->admin_country; ?></div>
                                </li>
								
                                <li id="admin_phone_work">
                                    <span class="title">Phone</span>
                                    <div class="content"><?=$admin->admin_phone_work; ?></div>
                                </li>
								
                                <li>
                                    <a class="inline_link makeTextEditable" name="make_text_editable" href="#">
										<div class="name">Edit Profile</div>
									</a>
                                </li>
								
								<li>
									<a href="includes/classes/copy_school.php?school_id=5">COPY</a>
								</li>
								
                            </ul>
                        </div>
                    </div>
				</div>
			</div>
