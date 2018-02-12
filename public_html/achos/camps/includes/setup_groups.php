<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$gt_div_groups = get_all_group_type_division_groups();

function get_all_group_type_division_groups() {
	global $camp_id;
	
	$group_types = array();
	$divisions = array();
	$groups = array();
	
	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "LEFT JOIN groups AS g USING (division_id) ";
	$sql = $sql . "WHERE gt.camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id ";
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$prev_group_type_id = "";
	$prev_division_id = "";
	
	$row_num = 0;
	$group_type_id = "";
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_group_type_id = $row['group_type_id'];
		$prev_division_id = $row['division_id'];
			
		if ($prev_division_id != $division_id && $division_id != "") {			
			$element = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $element);
			$groups = array();
		}
	
		if ($prev_group_type_id != $group_type_id && $group_type_id != "") {
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);
			$divisions = array();
		}
					
		$group_id = $row['group_id'];
		$group_name = $row['group_name'];
		
		if ($group_id > 0) {
			$element = compact('group_id', 'group_name');
			array_push($groups, $element);
		}
		
		$group_type_id = $prev_group_type_id;
		$group_type_name = $row['group_type_name'];	

		$division_id = $prev_division_id;
		$division_name = $row['division_name'];	
		
		if ($row_num == $num_rows) {
			$element = compact('division_id', 'division_name', 'groups');
			array_push($divisions, $element);		
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);		
		}
	}
	
	return $group_types;
}
?>
 			<script src="scripts/jquery.jeditable.min.js"></script>
			
 			<script>
				var new_groups = 0;
				var division_id = 0;
				var group_id = 0;
			
				$(function() {
				
                    $('.editable').editable( function(value, settings) 
                                             { 
												var info = $(this).attr("id").split("_");
												var group_id = info[1];
												save_group(this, group_id, value);
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
						division_id = $(this).attr("name");
						new_groups++;						
						var list_item_html = get_list_item_html(new_groups, division_id);
						
						$(this).parents('li').before(list_item_html);


						$(this).parents('li').prev().find('.editable').html('').editable(function  (value, settings) 
								{
									var li_id = $(this).parents('li').attr("id");																		
									var index_of = li_id.indexOf("new");
									if (index_of > -1) {
										if (value != "")
											save_new_group(this, division_id, value, li_id);
										else
											$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("fast");
									}
									else {
										var info = li_id.split("_");
										var old_group_id = info[1];
										save_group(this, old_group_id, value) 
										//alert("old_group_id:" + old_group_id);
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
						
						$(this).parents('li').find('.no_division').show();
						
						$(this).parents('li').prev().find('.editable').click();
					});
					
					$(".slider:last .remove a").live('click',function(event) {
						event.preventDefault();
						$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("slow", function(){$(this).remove()});
					});
					
					$(".slider:last .no_division a").live('click',function(event) {
						event.preventDefault();
						$(this).parents('li').prevAll().css({backgroundColor: "#ff0000"}).fadeOut("slow", function(){$(this).remove()});
					});
					
				});
				
				function save_group(editable, group_id, group_name) {
					var function_name = "save_group";
					var parameters = [group_id, group_name];
					var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {
						if (success == false) {
							alert("Could not add group. Please try again.");
						}
						else {
							editable.reset();
						}
					});				
				}
				
				function get_list_item_html(new_groups, division_id) {
					return "<li id='new_" + new_groups + "'>" + 
							"<span class='action'>" +
							"<span class='remove'><a href='#' title='Delete'>Delete</a></span>" +
							"<span class='edit'><a href='#' title='Edit'>Edit</a></span>" +
							"</span>" +
							"<span class='icon bullet'></span>" + 
							"<span id='division_" + division_id + "' class='label editable'>" + 
							"</span>" + 
							"</li>"; 				
				}
				
				function save_new_group(editableElement, old_division_id, group_name, li_id) {	
					var function_name = "add_new_group";
					var parameters = [old_division_id, group_name];
					var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {
						if (success == 0) {
							alert("Could not add group. Please try again.");
						}
						else {
							var group_id = success;
							division_id = 0;
							editableElement.reset();
							var new_id = "group_" + group_id;
							document.getElementById(li_id).id = new_id;
							var new_li = document.getElementById(new_id);
							$(new_li).find('.remove').html("<a href='#' onclick='remove_group(" + group_id + ");' title='Delete'>Delete</a>");
						}
					});
				}					
				
				function generate_groups(division_id, division_name) {
					var innerHTML = "";
					var div = document.getElementById(division_id);
					var inputs = div.getElementsByTagName("input");
					var format = "";
					var number_of_groups = 0;
					
					for (cntr = 0; cntr < inputs.length; cntr++) {
						if (inputs[cntr].getAttribute("type") == "radio") {
							if (inputs[cntr].checked == true)
								format = inputs[cntr].value;
						}
						else if (inputs[cntr].getAttribute("type") == "text") {
							number_of_groups = inputs[cntr].value;
							input_number = cntr;
						}
					}

					if (number_of_groups > 30) {
						alert("30 Groups maximum");
						inputs[input_number].focus();
					}
					else if (number_of_groups == 0) {
						alert("You must enter how many groups would you like to generate");
						inputs[input_number].focus();
					}
					else if (format == "") {
						alert("You must choose how would you like to name your groups");
					}
					else {
						var function_name = "generate_new_groups";
						var parameters = [division_id, division_name, number_of_groups, format];
						var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
						$.getJSON(url, function(error_code) {
						
							if (error_code == 1) {
								alert("Could not generate new groups. Please try again");
							}
							else {							
								// ***** If the generate inserts were succesful then the generate information ***** //
								// ***** needs to be replaced with the new groups that were generated         ***** //
								document.getElementById(division_id).innerHTML = "";
									
								var info1 = error_code.split("|");
											
								for (cntr1 = 0; cntr1 < info1.length; cntr1++) {
																				
									var info2 = info1[cntr1].split("~");
									var group_id = info2[0];
									var group_name = info2[1];
									var id = "group_" + group_id;	
										
									innerHTML = innerHTML + "<li id='" + id + "'>";
									innerHTML = innerHTML + "<span class='action'>";
									innerHTML = innerHTML + "<span class='remove'>";
									innerHTML = innerHTML + "<a onclick='delete_group_two(\"" + id +  "\");' title='Delete' href='#'>Delete</a>";
									innerHTML = innerHTML + "</span>";
									innerHTML = innerHTML + "<span class='edit'><a title='Edit' href='#'>Edit</a></span>";
									innerHTML = innerHTML + "</span>";
									innerHTML = innerHTML + "<span class='icon bullet'></span>";									
									innerHTML = innerHTML + "<span class='label editable'>" + group_name + "</span>";
									innerHTML = innerHTML + "</li>";
								}		

								$(document.getElementById(division_id)).html(innerHTML);
									
								$(document.getElementById(division_id)).children().find('.editable').editable( 
										function (value, settings) 
										{
											var li_id = $(this).parents('li').attr("id");										
											var info = li_id.split("_");
											var old_group_id = info[1];
											save_group(this, old_group_id, value) 
												
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
							
							}
							
						});

					}
					
				}
				
				function remove_group(group_id) {
					var function_name = "remove_group";
					var parameters = [group_id];
					var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(error_code) {
						if (error_code > 0) 
							alert("Could not remove group. Please try again.");					
					});					
				}
			</script>

			<div class="slider">
				<div class="col_title"><span>Getting Started</span><a class="slider_back">back</a></div>
				<div class="col_content">
                    <h1>Setup Groups</h1>
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>Setup the names of your groups.</p>
                        	<p>You can generate group names by selecting from the options below.</p>
                        </div>
                    </div>
					
					<? for ($gtdgno = 0; $gtdgno < count($gt_div_groups); $gtdgno++) : ?>
						<? $divisions = $gt_div_groups[$gtdgno]['divisions']; ?>
						<? //echo "# OF DIVISIONS:" . count($divisions) . "<br />"; ?>
						<? for ($dno = 0; $dno < count($divisions); $dno++) : ?>						
						<? $division = $divisions[$dno]; ?>
						<? $groups = $division['groups']; ?>
						<? //echo "# OF GROUPS:" . count($groups) . "<br />"; ?>
						<div class="module" id="module-info">
							
							<h1><?= $gt_div_groups[$gtdgno]['group_type_name'];?>-<?=$division['division_name'];?></h1>
							
							<div class="module_content">
							
								<div class="list">
									<ul>
									
										<? if (count($groups) == 0) : ?>
										<div id="<?=$division['division_id'];?>">
										<li>					
											<label>How many groups would you like to generate?</label>
											<input type="text">
										</li>
																				
										<li>
											<label>How would you like to name your groups?</label>
											<input type="radio" name="bunk_name_format" value="N">Alef,Beis,Gimmel</input>
											<input type="radio" name="bunk_name_format" value="A" checked>A,B,C</input>
											<input type="radio" name="bunk_name_format" value="1">1,2,3</input>
											<p><a class="button" onclick="generate_groups(<?=$division['division_id'];?>, '<?=$division['division_name'];?>');">Generate</a></p>
											<div class="clear"></div>
										</li>
										</div>
										<? endif; ?>
										
										<? for ($gno = 0; $gno < count($groups); $gno++) : ?>
										<? $group = $groups[$gno]; ?>
										<? if ($group['group_name'] != "") : ?>
										<li id="group_<?=$group['group_id'];?>">
											<span class="action">
												<span class="remove" onclick="remove_group(<?=$group['group_id'];?>);">
													<a href="#" title="Delete">Delete</a>
												</span>
												<span class="edit"><a href="#" title="Edit">Edit</a></span>
											</span>
											<span class="icon bullet"></span>
											<span id="g_<?=$group['group_id'];?>" class="title editable"><?=$group['group_name'];?></span>
											<div class="clear"></div>
										</li>
										<? endif; ?>
										<? endfor; ?>
										
										<li>
											<span class="icon add">
											</span>
											<span class="label">
												<a href="#" class="add_new_row" name="<?=$division['division_id'];?>">
													Add Group
												</a>
											</span>
											<div class="clear">
											</div>
										</li>
									</ul>
									
								</div>
								
							</div>
							
						</div>
						<? endfor; ?>
					<? endfor; ?>
					
                    <div class="wizard_nav">
                        <p><a class="button rfloat" href="content.php?output=gettingstarted4&group_task=0">Next</a></p>
                        <br class="clear" />
                    </div>
					
				</div>
				
			</div>