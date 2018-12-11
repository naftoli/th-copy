<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$group_type_divisions = get_all_group_type_divisions();

function get_all_group_type_divisions() {
	global $camp_id;
	
	$group_types = array();
	$divisions = array();
	
	$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "LEFT JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "WHERE gt.camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_id, d.division_id ";
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$prev_group_type_id = "";
	
	$row_num = 0;
	$group_type_id = "";
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		$prev_group_type_id = $row['group_type_id'];
		$division_id = $row['division_id'];
		$division_name = $row['division_name'];
	
		if ($prev_group_type_id != $group_type_id && $group_type_id != "") {
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);
			$divisions = array();
		}
				
		$element = compact('division_id', 'division_name');
		array_push($divisions, $element);
					
		$group_type_id = $prev_group_type_id;
		$group_type_name = $row['group_type_name'];	

		if ($row_num == $num_rows) {
			$element = compact('group_type_id', 'group_type_name', 'divisions');
			array_push($group_types, $element);		
		}
	}
	
	return $group_types;
}
?>
 			<script>
				var new_division = 0;
				
				$(function() {
				
                    $('.editable').editable( function(value, settings) 
                                             { 
												var info = $(this).attr("id").split("_");
												var division_id = info[1];
												save_division(this, division_id, value);
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
						group_type_id = $(this).attr("id");
						
						new_division++;
						var list_item_html = get_list_item_html(new_division);
						$(this).parents('li').before(list_item_html);
						
						$(this).parents('li').prev().find('.editable').html('').editable(function  (value, settings) 
								{
									var li_id = $(this).parents('li').attr("id");																		
									var index_of = li_id.indexOf("new");
									if (index_of > -1) {
										if (value != "")
											save_new_division(this, group_type_id, value, li_id);
										else
											$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("fast");
									}
									else {
										var info = li_id.split("_");
										var division_id = info[1];
										save_division(this, division_id, value) 
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
						$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("slow", function(){$(this).remove()});
					});
					
					$(".slider:last .no_division a").live('click',function(event) {
						var ul = $(this).parents("ul");
						var info = $(ul).attr("id").split("_");
						var group_type_id = info[1];
						var function_name = "remove_divisions";
						var parameters = [group_type_id];
						var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(success) {
							if (success == false) 
								alert("Could not remove divisions. Please try again.");
							else {
								for (lino = 0; lino < $(ul).find("li").size(); lino++) {
									var li = $(ul).find("li").get(lino);
									var id = $(li).attr("id");
									if (id.length > 0)
										$(li).css({backgroundColor: "#ff0000"}).fadeOut("slow", function(){$(this).remove()});	
								}
							}
						});										
					});
				})
				
				function save_new_division(edtbl, group_type_id, division_name, li_id) {	
					var function_name = "add_new_division";
					var parameters = [group_type_id, division_name];
					var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(division_id) {
						if (division_id == 0) {
							alert("Could not add new division. Please try again.");
						}
						else {
							edtbl.reset();
							var new_id = "d_" + division_id;
							document.getElementById(li_id).id = new_id;
							var new_li = document.getElementById(new_id);
							$(new_li).find('.remove').html("<a href='#' onclick='remove_division(" + division_id + ");' title='Delete'>Delete</a>");
						}
					});
				}					
				
				function get_list_item_html(new_division) {
					return "<li id='new_" + new_division + "'>" + 
							"<span class='action'>" +
							"<span class='remove'><a href='#' title='Delete'>Delete</a></span>" +
							"<span class='edit'><a href='#' title='Edit'>Edit</a></span>" +
							"</span>" +
							"<span class='icon bullet'></span>" + 
							"<span class='label editable'>" + 
							"</span>" + 							
							"</li>"; 				
				}				
				
				function save_division(editable, division_id, division_name) {
					var function_name = "edit_division";
					var parameters = [division_id, division_name];
					var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {
						if (success == false) {
							alert("Could not update division. Please try again.");
						}
						else {
							editable.reset();
						}
					});
				}

				function remove_division(division_id) {	
					var list_item = $(this).parents('li');
					var function_name = "delete_division";
					var parameters = [division_id];
					var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {
						if (success == false) 
							alert("Could not remove division. Please try again.");
						else
							$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow", function(){$(this).remove()});
					});					
				}
				
				//function no_divisions(group_type_id) {
				//	var function_name = "remove_divisions";
				//	var parameters = [group_type_id];
				//	var url = "include/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
				//	$.getJSON(url, function(success) {
				//		if (success == false) 
				//			alert("Could not remove divisions. Please try again.");
				//	});										
				//}
			</script>
			
			<div class="slider">
			
				<div class="col_title">
					<span>Getting Started</span>
					<a class="slider_back">back</a>
				</div>
				
				<div class="col_content">
				
                    <h1>Setup Divisions</h1>
					
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>In this step please tell us how you wish to divide the group types you selected.</p>
                        </div>
                    </div>
					
					<? for ($gtdno = 0; $gtdno < count($group_type_divisions); $gtdno++) : ?>
					<? $divisions = $group_type_divisions[$gtdno]['divisions']; ?>
                    <div class="module list_divisions" id="module-info">
					
                    	<h1><?=$group_type_divisions[$gtdno]['group_type_name'];?></h1>
						
                        <div class="module_content">
						
                            <div class="list">
							
                                <ul id="gt_<?=$group_type_divisions[$gtdno]['group_type_id'];?>">
								
									<? for ($dno = 0; $dno <  count($divisions); $dno++) : ?>
									<? $division = $divisions[$dno]; ?>
									<? if ($division['division_id'] > 0) : ?>
                                    <li id="d_<?=$division['division_id'];?>">
                                        <span class="action">
                                            <span class="remove">
												<a href="#" onclick="remove_division(<?=$division['division_id'];?>);" title="Delete">Delete</a>
											</span>
                                            <span class="edit"><a href="#" title="Edit">Edit</a></span>
                                        </span>
                                        <span class="icon bullet"></span>
										<span class="label editable" id="d_<?=$division['division_id'];?>"><?=$division['division_name'];?></span>
                                        <div class="clear"></div>
                                    </li>
									<? endif; ?>
									<? endfor; ?>
									
									<li>
										<span class="icon add">
										</span>
										
										<span class="label">
											<a href="#" class="add_new_row" id="<?=$group_type_divisions[$gtdno]['group_type_id'];?>">
												Add Division
											</a>
										</span>
										
										<? if ( (count($divisions) > 1) || (count($divisions) == 1 && $group_type_divisions[$gtdno]['group_type_name'] != "No Divisions") ) : ?>
										<span class="no_division">
                                            <span class="icon remove"></span>
                                            <a href="#" onclick="no_divisions(<?=$group_type_divisions[$gtdno]['group_type_id'];?>);">
												No divisions for this type
											</a>
                                        </span>
										<? endif; ?>
										
										<div class="clear">
										</div>
									</li>
									
                                </ul>
								
                            </div>
							
                        </div>
						
                    </div>
					<? endfor; ?>
					
                    <div class="wizard_nav">
                        <p><a class="button rfloat" href="content.php?output=gettingstarted3">Next</a></p>
                        <br class="clear" />
                    </div>
					
				</div>
				
			</div>
