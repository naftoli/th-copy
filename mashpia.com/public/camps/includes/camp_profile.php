<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/camp.php");
include ("classes/group_type.php");
include ("classes/division.php");
include ("classes/group.php");

$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$camp = new camp($row);
$camp->get_group_types();
for ($gtno = 0; $gtno < count($camp->group_types); $gtno++) {	
	$camp->group_types[$gtno]->get_divisions();
	for ($dno = 0; $dno < count($camp->group_types[$gtno]->divisions); $dno++) {
		$camp->group_types[$gtno]->divisions[$dno]->get_groups();
	}
}

if ($camp->session_one_start > 0)
	$session_one_start = date('M j Y', strtotime(jdtogregorian($camp->session_one_start)));
else
	$session_one_start = "";
	
if ($camp->session_one_end > 0)	
	$session_one_end = date('M j Y', strtotime(jdtogregorian($camp->session_one_end)));
else 
	$session_one_end = "";
	
if ($camp->session_two_start > 0) 
	$session_two_start = date('M j Y', strtotime(jdtogregorian($camp->session_two_start)));
else
	$session_two_start = "";

if ($camp->session_two_end > 0) 
	$session_two_end = date('M j Y', strtotime(jdtogregorian($camp->session_two_end)));
else
	$session_two_end = "";
?>

			<script>
				$(document).ready(function() {
				
					var camp_id = <?=$camp_id;?>;
				
					$("a[name=make_text_editable]").click(
						function(event) {
							$(this).parents('ul').find(".save").click(
								function(event) {
									var field_name = $(this).parents('li').attr("id");
									var value = $(this).parents('li').find(".content").html();
									
									function_name = "update_camp";				
									parameters = [camp_id, field_name, value];
									var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
									$.getJSON(url, function(success) {
										if (success == false)  {
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
					<span>Camp Profile</span>
				</div>
				
				<div class="col_content camper">
									
                    <div class="two_col left">
                    	<div class="photo">
							<input type="hidden" name="CAMP LOGO ID" value="<?=$camp->camp_logo_id;?>">
							<? if ($camp->camp_logo_id > 0) : ?> 
							<img src="includes/file_view.php?id=<?=$camp->camp_logo_id;?>" height="150" />
							<? endif; ?>
						</div>
                    </div>
					
                    <div class="two_col right">
					
                        <div class="module lists editableText" id="lists-camper-profile">
						
                            <div class="module_content">
							
                                <ul>
								
                                    <li id="camp_name">
                                        <span class="title">Camp Name</span>
                                        <div class="content"><?=$camp->camp_name;?></div>
                                    </li>
									
                                    <li id="camp_address1">
                                        <span class="title">Address</span>
                                        <div class="content"><?=$camp->camp_address1;?></div>
                                    </li>
									
                                    <li id="camp_address2">
                                        <span class="title"></span>
                                        <div class="content"><?=$camp->camp_address2;?></div>
                                    </li>
									
                                    <li id="camp_phone">
                                        <span class="title">Phone</span>
                                        <div class="content"><?=$camp->camp_phone;?></div>
                                    </li>
									
                                    <li id="session_one_start">
                                        <span class="title">First Session Start</span>
                                        <div class="content"><?=$session_one_start;?></div>
                                    </li>
									
                                    <li id="session_one_end">
                                        <span class="title">First Session End</span>
                                        <div class="content"><?=$session_one_start;?></div>
                                    </li>
																		
                                    <li id="session_two_start">
                                        <span class="title">Second Session Start</span>
                                        <div class="content"><?=$session_two_start;?></div>
                                    </li>
									
                                    <li id="session_two_end">
                                        <span class="title">Second Session End</span>
                                        <div class="content"><?=$session_two_end;?></div>
                                    </li>									
									
                                    <li>
                                        <a class="inline_link makeTextEditable" name="make_text_editable" href="#"><div class="name">Edit Profile</div></a>				
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
					
                    <div class="clear"></div>					
					
                    <h1>Groups</h1>
					
                    <div class="module lists" id="lists-camper-groups">
                        <div class="module_content">
                            <ul>															
									<?
										$group_type_name = "";
										
										for ($gtno = 0; $gtno < count($camp->group_types); $gtno++) : 
											$group_type = $camp->group_types[$gtno];
											
											for ($dno = 0; $dno < count($group_type->divisions); $dno++) :
												$division = $group_type->divisions[$dno];
												
												for ($gno = 0; $gno < count($division->groups); $gno++) :
													$group = $division->groups[$gno]; ?>
													
													<li>
														<a class="link" href="content.php?output=group&group_type_id=<?=$group_type->group_type_id;?>&division_id=<?=$division->division_id;?>&group_id=<?=$group->group_id;?>&group_name=<?=urlencode($group->group_name);?>">
															<? if ($group_type->group_type_name != $group_type_name) : ?>
																<span class="title"><?=$group_type->group_type_name;?></span>
															<? else : ?>
																<span class="title">&nbsp;</span>
															<? endif; ?>
															<span class='content'><?=$group->group_name;?></span>
														</a>
													</li>
														
													<? $group_type_name = $group_type->group_type_name; ?>
														
												<? endfor;
												
											endfor;										
											
										endfor;
										
									?>

                            </ul>
							
                        </div>
						
                    </div>
										
				</div>
				
			</div>
