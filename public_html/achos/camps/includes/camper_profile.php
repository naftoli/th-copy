<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();
$user_id = $_GET['user_id'];

include ("classes/user.php");
include ("classes/group_type.php");
include ("classes/division.php");
include ("classes/group.php");
$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$camper = new user($row);
$camper->get_user_groups();
$camper->get_user_total_points();
?>

			<script>
				$(document).ready(function() {
				
					var user_id = <?=$user_id;?>;
				
					$('#edit_image_form').ajaxForm({
						
						beforeSubmit: function(){
						},
						success: function(success){
							if (success != "0") {
								alert(success);
							}
							else {
								alert("Update did not work. Please try again.");
							}
						}
					});
				
					$("img[name=image_save]").click(function(event) {
						var photo = document.edit_image_form.elements[0].value
						if (photo.length > 0)
							$('#edit_image_form').submit();
					});
					
					$("a[name=make_text_editable]").click(
						function(event) {
							$(this).parents('ul').find(".save").click(
								function(event) {
									var field_name = $(this).parents('li').attr("id");
									var value = $(this).parents('li').find(".content").html();
									
									function_name = "update_user";				
									parameters = [user_id, field_name, value];
									var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
									$.getJSON(url, function(success) {
										if (success == false)  {
											alert("Could not update camper. Please try again");
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
					<?=$camper->first . " " . $camper->last;?>'s 
					<span> Profile</span>
				</div>
				
				<div class="col_content camper">
				
					<div class="module" id="lists-stats">
					
                        <div class="module_content clearfix">
                        	<h1>Stats</h1>
                            <ul class="stats">
                            	<li>Points<span><?=$camper->total_points;?></span></li>
                            </ul>
                            <ul class="stats">
                            	<li>Trips<span>10</span></li>
                            </ul>
                            <ul class="stats">
                            	<li>Pending Rewards<span>10</span></li>
                            </ul>							
                        </div>

					</div>
					
					<form name="edit_image_form" id="edit_image_form" action="includes/edit_image.php" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
						<input type="hidden" id="user_id" name="user_id" value="<?=$user_id;?>">
						<div class="two_col left">
							<div class="photo">
								<? if ($camper->user_photo_id > 0) : ?>	
									<img src="includes/file_view.php?id=<?=$camper->user_photo_id;?>" height="150" />
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
											<span class="input">
												<input type="file" id="photo" name="photo" />
											</span>
											<span class="tip">
												<img src="images/bullet_disk.png" id="image_save" name="image_save"> Maximum file size: 2MB. Minimum size: 180x225
											</span>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</form>
						
                    <div class="clear"></div>
					
					<!-- ********** MEMBER GROUPS ********** -->
                    <h1>Groups</h1>
					
                    <div class="module lists" id="lists-camper-groups">
					
                        <div class="module_content">
                            <ul>
								<? if (count($camper->groups) == 0) : ?>
									<li>
										<center>
											<span class="title"></span>
										</center>
									</li>								
								<? else : ?>
									<? for ($gno = 0; $gno < count($camper->groups); $gno++) : ?>
									<? $group = $camper->groups[$gno]; ?>
									<li>
										<a class="link" href="content.php?output=group&group_type_id=<?=$group->division->group_type->group_type_id;?>&division_id=<?=$group->division->division_name;?>&group_id=<?=$group->group_id;?>&group_name=<?=urlencode($group->group_name);?>">
											<span class="title"><?=$group->division->group_type->group_type_name;?></span>
											<div class="content"><?=$group->group_name;?></div>
										</a>
									</li>
									<? endfor; ?>
								<? endif; ?>
                            </ul>
                        </div>
						
                    </div>
					<!-- ********** MEMBER GROUPS ********** -->
					
                    <h1>Info</h1>

					
                    <div class="module lists editableText" id="lists-camper-info">
					
                        <div class="module_content">
                            <ul>
                                <li id="first">
                                    <span class="title">First Name</span>
                                    <div class="content"><?=$camper->first;?></div>									
                                </li>
								
                                <li id="last">
                                    <span class="title">Last Name</span>
                                    <div class="content" name="last"><?=$camper->last;?></div>									
                                </li>
										
								<li id="first">
                                    <span class="title">email</span>
                                    <div class="content"><?=$camper->email;?></div>									
                                </li>
							
								<? if ($camper->first_he) : ?>
                                <li id="first_he">
                                    <span class="title">Hebrew First Name</span>
                                    <div class="content" name="first_he"><?=$camper->first_he;?></div>
                                </li>
                                <li id="last_he">
                                    <span class="title">Hebrew Last Name</span>
                                    <div class="content" name="last_he"><?=$camper->last_he;?></div>
                                </li>
								<? endif; ?>
																
                                <li id="gender">
                                    <span class="title">Gender</span>
                                    <div class="content"><?=$camper->gender;?></div>
                                </li>
								
                                <li id="lang">
                                    <span class="title">Language</span>
                                    <div class="content" name="lang"><?=$camper->lang;?></div>
                                </li>
								
                                <li id="user_address1">
                                    <span class="title">Address</span>
                                    <div class="content" name="user_address1"><?=$camper->user_address1;?></div>
                                    <div class="clear"></div>
                                </li>
								
                                <li id="user_address2">
									<span class="title"></span>
                                    <div class="content" name="user_address2"><?=$camper->user_address2;?></div>
                                    <div class="clear"></div>
                                </li>
								
                                <li id="user_city">
                                    <span class="title">City</span>
                                    <div class="content"><?=$camper->user_city;?></div>
                                </li>
								
                                <li id="user_state">
                                    <span class="title">State</span>
                                    <div class="content" name="user_state"><?=$camper->user_state; ;?></div>
                                </li>
								
                                <li id="user_state">
                                    <span class="title">Zip</span>
                                    <div class="content" name="user_state"><?=$camper->user_postal;?></div>
                                </li>
								
                                <li id="user_country">
                                    <span class="title">Country</span>
                                    <div class="content" name="user_country"><?=$camper->user_country;?></div>
                                </li>
								
                                <li id="user_phone">
                                    <span class="title">Phone</span>
                                    <div class="content" name="user_phone"><?=$camper->user_phone;?></div>
                                </li>
								
                                <li>
                                    <a class="inline_link makeTextEditable" name="make_text_editable" href="content.php?output=camperadd">
										<div class="name">Edit Profile</div>
									</a>				
                                </li>
								
                            </ul>
                        </div>						
						
                    </div>
				</div>
			</div>
