<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/group_type.php");
$group_types = array();
$sql = "SELECT * FROM group_types WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$group_type = new group_type($row);
	array_push($group_types, $group_type);
}
//$action = "get_all_group_types";
//$params = $camp_id;
//$group_types = getJson($action, $params);
?> 			
			<script src="scripts/jquery.jeditable.min.js"></script>
			
			<script>
				var camp_id = <?=$camp_id;?>;
				var new_group_type = 0;
				
				$(function() {
				
                    $('.editable').editable( function(value, settings) 
                                             { 
												var info = $(this).attr("id").split("_");
												var group_type_id = info[1];
												save_group_type(this, group_type_id, value);
												return value;
                                             }, 
                                             {
												  indicator : '<img src="images/ajax-loader-sm.gif"/>',
												  submit:'<img src="images/bullet_disk.png"/>',
												  onblur:'ignore',
												  width:'143',
												  height:'14' 
						                     }
					);
				
					$("a.add_new_row").click(function(event) {
						new_group_type++;
						var list_item_html = get_list_item_html(new_group_type);
						$(this).parents('li').before(list_item_html);
						
						$(this).parents('li').prev().find('.editable').html('').editable(function  (value, settings) 
								{
									var li_id = $(this).parents('li').attr("id");																		
									var index_of = li_id.indexOf("new");
									if (index_of > -1) {
										if (value != "")
											save_new_group_type(this, value, li_id);
										else
											$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("fast");
									}
									else {
										var info = li_id.split("_");
										var group_type_id = info[1];
										save_group_type(this, group_type_id, value) 
									}
								
									return value;
								},
								{
									 indicator : '<img src="images/ajax-loader-sm.gif"/>',
									 submit:'<img src="images/bullet_disk.png"/>',
									 onblur:'ignore',
									 width:'1',
									 height:'1'
								}
						);

						$(this).parents('li').prev().find('.editable').click();
						
					});
					
					$(".slider:last .remove a").live('click',function(event) {
						event.preventDefault();
						
						var list_item = $(this).parents('li');
						var info = $(list_item).attr("id").split("_");
						var group_type_id = info[1];
						var function_name = "remove_group_type";
						var parameters = [group_type_id];
						var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(success) {
							if (success == false) 
								alert("Could not remove group type. Please try again.");
							else
								$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow", function(){$(this).remove()});
						});					
						
						
					});
					
				})
				
				function save_new_group_type(edtbl, value, li_id) {
					var function_name = "add_new_group_type";
					var parameters = [camp_id, value];
					var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {
						if (success == 0) {
							alert("Could not add new group type. Please try again.");
						}
						else {
							var group_type_id = success;
							edtbl.reset();
							var new_id = "gt_" + group_type_id;
							document.getElementById(li_id).id = new_id;
							var new_li = document.getElementById(new_id);
							$(new_li).find('.remove').html("<a href='#' onclick='remove_group_type(" + group_type_id + ");' title='Delete'>Delete</a>");
						}
					});
					
				}
				
				function save_group_type(editable, group_type_id, group_type_name) {
					var function_name = "save_group_type";
					var parameters = [group_type_id, group_type_name];
					var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {
						if (success == false) {
							alert("Could not update group type. Please try again.");
						}
						else {
							editable.reset();
						}
					});
				}

				/*function remove_group_type(group_type_id) {
					var function_name = "remove_group_type";
					var parameters = [group_type_id];
					var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(error_code) {
						if (error_code > 0) 
							alert("Could not remove group type. Please try again.");
					});					
				}*/
				
				function get_list_item_html(new_group_type) {
					return "<li id='new_" + new_group_type + "'>" + 
							"<span class='action'>" +
							"<span class='remove'><a href='#' title='Delete'>Delete</a></span>" +
							"<span class='edit'><a href='#' title='Edit'>Edit</a></span>" +
							"</span>" +
							"<span class='icon bullet'></span>" + 
							"<span class='label editable'>" + 
							"</span>" + 							
							"</li>"; 				
				}				
			</script>
			
			<div class="slider">
			
				<div class="col_title">
					<span>Getting Started</span>
				</div>
				
				<div class="col_content">
				
                    <h1>Setup Camp Profile</h1>
					
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>This guide will walk you through all the necessary steps to get you up and running in no time.</p>
                        	<p>To minimize setup time many fields have been pre-filled or selected.</p>
                        	<p>You can always edit these options later in the control panel.</p>
                        </div>
                    </div>
					
                    <div class="module list_sessions" id="module-info">
                    	<h1>Setup Sessions</h1>
                        <div class="module_content">
                            <div class="list">
                                <ul>
                                    <li>
                                        <span class="action">
                                            <span class="remove"><a href="#" title="Delete">Delete</a></span>
                                            <span class="edit"><a href="#" title="Edit">Edit</a></span>
                                        </span>
                                        <span class="icon bullet"></span>
                                        <span class="label editable">First Month</span>
                                        <span class="label small editable">June 25 - July 25</span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="action">
                                            <span class="remove"><a href="#" title="Delete">Delete</a></span>
                                            <span class="edit"><a href="#" title="Edit">Edit</a></span>
                                        </span>
                                        <span class="icon bullet"></span>
                                        <span class="label editable">Second Month</span>
                                        <span class="label small editable">July 25 - August 25</span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="icon add"></span>
                                        <span class="label"><a href="#" class="add_new_row">Add Session</a></span>
                                        <div class="clear"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
					
                    <div class="module list_group_types" id="module-info">
					
                    	<h1>Setup Group Types</h1>
						
                        <div class="module_content">
                            <div class="list">
                                <ul>
									<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>
										<? $group_type = $group_types[$gtno]; ?>
										<li id="gt_<?=$group_type->group_type_id;?>">
											<span class="action">
												<span class="remove">
													<a href="#" title="Delete">Delete</a>
												</span>
												<span class="edit">
													<a href="#" title="Edit">Edit</a>
												</span>
											</span>
											<span class="icon bullet"></span>
											<span class="label editable" id="gt_<?=$group_type->group_type_id;?>"><?=$group_type->group_type_name;?></span>
											<div class="clear"></div>
										</li>
									<? endfor; ?>
									
									<li>
										<span class="icon add">
										</span>
										
										<span class="label">
											<a href="#" class="add_new_row">
												Add Group Type
											</a>
										</span>
										
										<div class="clear">
										</div>
									</li>
									
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wizard_nav">
                        <p><a class="button rfloat" href="content.php?output=gettingstarted2">Next</a></p>
                        <br class="clear" />
                    </div>
					
				</div>
				
			</div>
