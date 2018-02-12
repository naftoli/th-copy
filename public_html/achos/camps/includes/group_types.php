<?php 
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/group_type.php");
$group_types = array();
$sql = "SELECT * FROM group_types WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$group_type = new group_type($row);
	$group_type->get_number_of_campers($camp_id);
	$group_type->get_group_type_points($camp_id);	
	array_push($group_types, $group_type);
}
?>
			 <script>
				var new_group_type_number = 0;
				var camp_id = <?=$camp_id;?>;
				
				$(document).ready(function() {
				
					$('.action a.delete ').click(function() {
						var list_item = $(this).parents('li');
						var group_type_name = $(list_item).find("span[name=label]").html();
						var confirm_delete = window.confirm("Are you sure you want to delete " + group_type_name + "?") 
						
						if (confirm_delete) {
							var info = $(list_item).attr("id").split("_");
							var group_type_id = info[1];
							
							var function_name = "delete_group_type";
							var parameters = [group_type_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							
							$.getJSON(url, function(success) {
								if (success == false) {
									alert("Could not delete group type. Please try again.")
								}
								else {
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
								}
							});
						}
					});
				
					$('.gt_add_new_row').click(function(){
						new_group_type_number++;
						
						var new_html = "<li id='new_group_type_" + new_group_type_number + "'>";
						new_html = new_html + "<span class='label editable' name='label'></span>";
						new_html = new_html + "</li>";						
						$(this).parents('li').before(new_html);
						
						$(this).parents('li').prev().find('.editable').html('').editable('http://www.example.com/save.php',{
							 indicator : '<img src="images/ajax-loader-sm.gif"/>',
							 submit:'<img onclick="save_new_group_type(' + new_group_type_number + ');" src="images/bullet_disk.png"/>',
							 onblur:'ignore',
							 tooltip: '',
							 width:'1',
							 height:'1'
						});
						
						$(this).parents('li').prev().find('.editable').click();	
						
					});
										
                
					$('.editable').editable(function(value, settings) {	
							var list_item = $(this).parents('li');
							var points = $(list_item).find("div[name=points]").html();
							var info = $(this).parents('li').attr("id").split("_");
							var group_type_id = info[1];
							var function_name = "edit_group_type";
							var parameters = [group_type_id, value];
							var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							$.getJSON(url, function(success) {
								if (success == false)
									alert("Group Not Updated. Please try again.");
								else {
									var new_html = get_new_html(group_type_id, value, points);
									$(list_item).html(new_html);
									var editable =  $(list_item).find('.editable').get(0);
									assign_editable_function(editable);
									assign_delete_function(list_item);
								}
							});																								
						},
						{
							indicator : '<img src="images/ajax-loader-sm.gif"/>',
							submit:'<img src="images/bullet_disk.png"/>',
							onblur:'ignore',
							width:'1',
							height:'1'
						}					
					);

				});
				
				function assign_delete_function(list_item) {
					$(list_item).find('.action a.delete ').click(function() {
						var list_item = $(this).parents('li');
						var group_type_name = $(list_item).find("span[name=label]").html();
						var confirm_delete = window.confirm("Are you sure you want to delete " + group_type_name + "?") 
						
						if (confirm_delete) {
							var info = $(list_item).attr("id").split("_");
							var group_type_id = info[1];
							
							var function_name = "delete_group_type";
							var parameters = [group_type_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							
							$.getJSON(url, function(success) {
								if (success == false) {
									alert("Could not delete group type. Please try again.")
								}
								else {
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
								}
							});
						}
					});
				}
				
				function assign_editable_function(editable) {
					$('.editable').editable(function(value, settings) {	
						var list_item = $(this).parents('li');
						var points = $(list_item).find("div[name=points]").html();
						var info = $(this).parents('li').attr("id").split("_");
						var group_type_id = info[1];
						var function_name = "edit_group_type";
						var parameters = [group_type_id, value];
						var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(success) {
							if (success == false)
								alert("Group Not Updated. Please try again.");
							else {
								var new_html = get_new_html(group_type_id, value, points);
								$(list_item).html(new_html);
								var editable = $(list_item).find('.editable').get(0);
								assign_editable_function(editable);
								assign_delete_function(list_item);
							}
						});																								
						},
						{
							indicator : '<img src="images/ajax-loader-sm.gif"/>',
							submit:'<img src="images/bullet_disk.png"/>',
							onblur:'ignore',
							width:'1',
							height:'1'
						}					
					);
				}
				
				function get_new_html(group_type_id, value, points) {
					var new_html = "<a href='#'>";
					new_html = new_html + "<div class='title'>";
					new_html = new_html + "<span class='label editable' name='label'>" + value + "</span>";
					new_html = new_html + "</div>";
					new_html = new_html + "</a>";									
					new_html = new_html + "<a class='link' name='link' href='content.php?output=divisions&group_type_id=" + group_type_id + "&group_type_name=" + value + "'>";
					new_html = new_html + "<div class='icon'></div>";
					new_html = new_html + "<div class='name' name='points'>";
					new_html = new_html + "<div class='title' name='points'>" + points + "</div>";
					new_html = new_html + "</div>";
					new_html = new_html + "</a>";										
					new_html = new_html + "<span class='action'>";
					new_html = new_html + "<a href='#' class='edit'>Edit</a>";
					new_html = new_html + "<a href='#' class='delete'>Delete</a>";
					new_html = new_html + "</span>";
					return new_html;					
				}
				
				function save_new_group_type(new_group_type_number) {
					var element_id = "new_group_type_" + new_group_type_number;
					var li = document.getElementById(element_id);
					var inputs = li.getElementsByTagName("input");					
					var group_type_name = inputs[0].value;
					
					if (group_type_name.length > 0) {
						var function_name = "add_group_type";
						var parameters = [camp_id, group_type_name];
						var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
						$.getJSON(url, function(success) {
							if (success == 0) {
								alert("Group Type Not Added. Please try again");
							}
							else {
								var points = "<div class='title' name='points'>Points: 0</div>";
								var inner_html = get_new_html(success, group_type_name, points);
								$(li).html(inner_html);
								new_id = "gt_" + success; 
								li.id = new_id;
								new_id = '"' + new_id + '"';
								var editable = $(li
								).find('.editable').get(0);
								assign_editable_function(editable);
								assign_delete_function(li);								
							}
						});	
					}
					else
						$("#" + element_id).css({backgroundColor: "#ff0000"}).fadeOut("slow");										
				}				
            </script>
			
			<div class="slider">
			
				<div class="col_title">
					<span>Group Types</span>
				</div>
				
				<div class="col_content">
				
					<div class="module" id="module-info">
						<div class="module_content">
							<h1>Group Type Stats</h1>
							
							<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>							
							<? $remainder = $gtno % 2;?>
								<? if ($remainder == 0) : ?>
								<ul class="stats">
								<? endif; ?>
								
								<li><?=$group_types[$gtno]->group_type_name;?><span><?=$group_types[$gtno]->no_of_campers;?></span></li>							
								
								<? if ($remainder == 1 || $gtno == (count($group_types) -1)) : ?>
								</ul>
								<? endif; ?>
							<? endfor; ?>
							
							<div class="clear"></div>
						</div>
					</div>
					
					<div class="module lists" id="lists-grouptypes">
					
						<div class="module_content">
						
							<ul>
							
							
								<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>
									<? $group_type = $group_types[$gtno]; ?>
								
									<li id="gt_<?=$group_type->group_type_id?>">
																
										<a href='#'>
											<div class="title">
												<span class='label editable' name='label'><?=$group_type->group_type_name;?></span>
											</div>
										</a>
									
										<a class="link" name="link" href="content.php?output=divisions&group_type_id=<?=$group_type->group_type_id;?>&group_type_name=<?=$group_type->group_type_name;?>">
											<div class="icon"></div>
											<div class="name">
												
												<? if ($group_type->points > 0) : ?>
													<!--<div class="title" name="points">Points: <?//=round(floatval(($group_type->points / $group_type->no_of_campers)), 2);?></div>-->
													<div class="title" name="points">Points: <?=$group_type->points;?></div>
												<? else : ?>
													<div class="title" name="points">Points: 0</div>
												<? endif; ?>
												
											</div>
										</a>
										
										<span class="action">
											<a href="#" class="edit">Edit</a>
											<a href="#" class="delete">Delete</a>
										</span>
										
									</li>
																		
                                <? endfor; ?>
									
								<li class="add_new">
									<a class="gt_add_new_row" href="#">
										<div class="icon"></div>
										<div class="name">Add New Group Type</div>
									</a>
								</li>

							</ul>
							
						</div> <!-- <div class="module_content"> -->
						
					</div> <!-- <div class="module lists" id="lists-grouptypes"> -->
					
				</div> <!-- <div class="col_content"> -->
				
			</div> <!-- <div class="slider"> -->
