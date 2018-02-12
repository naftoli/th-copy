<? 	
include ("get_camp_id.php");
$camp_id = get_camp_id();

$group_type_id = $_GET['group_type_id'];
$division_id = $_GET['division_id'];
$division_name = $_GET['division_name'];

include ("classes/group.php");
$groups = array();
$sql = "SELECT * FROM groups WHERE division_id=" . $division_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query) ) {
	$group = new group($row);
	$group->get_number_of_campers($camp_id);
	$group->get_group_points($camp_id);
	array_push($groups, $group);
}

$no_of_groups = count($groups);
if ($no_of_groups > 9)
	$no_of_columns = 3;
else
	$no_of_columns = ceil($no_of_groups / 3);
$no_of_rows = ceil($no_of_groups / $no_of_columns);
?>
			 <script>
				var new_group_number = 0;
				var group_type_id = "<?=$group_type_id;?>";
				var division_id = "<?=$division_id;?>";
				var division_name = "<?=$division_name;?>";
				
				$(document).ready(function() {
				
					$('.editable').editable(function(value, settings) {	
							var list_item = $(this).parents('li');
							var points = $(list_item).find("div[name=points]").html();
							var info = $(this).parents('li').attr("id").split("_");
							var group_id = info[1];
							var function_name = "edit_group";
							var parameters = [group_id, value];
							var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							$.getJSON(url, function(success) {
								if (success == false)
									alert("Group Not Updated. Please try again.");
								else {
									var new_html = get_new_html(group_id, value, points);
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
					
					$('.action a.delete ').click(function(){
						var list_item = $(this).parents('li');
						var group_name = $(list_item).find("span[name=label]").html();
						var confirm_delete = window.confirm("Are you sure you want to delete " + group_name + "?") 
						
						if (confirm_delete) {
							var info = $(this).parents('li').attr('id').split("_");
							var group_id = info[1];
							
							var function_name = "delete_group";
							var parameters = [group_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;				
							
							$.getJSON(url, function(success) {
								if (success == false) 
									alert("Could not delete. Please try again.");
								else
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");									
							});
						}
					});
					
					$('.g_add_new_row').click(function(){
						new_group_number++;
						
						var new_html = "<li id='new_group_" + new_group_number + "'>";
						new_html = new_html + "<span class='label editable' name='label'></span>";
						new_html = new_html + "</li>";
						
						$(this).parents('li').before(new_html);
						
						$(this).parents('li').prev().find('.editable').html('').editable(function(value, settings) {
								var list_item = $(this).parents('li');
								
								if (value.length > 0) {
									var function_name = "add_group";
									var parameters = [division_id, value];
									var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
									$.getJSON(url, function(success) {
										
										var points = "<div class='title' name='points'>Points: 0</div>";
										var new_html = get_new_html(success, value, points);
										$(list_item).html(new_html);
										$(list_item).attr("id", "g_" + success);
										var editable =  $(list_item).find('.editable').get(0);
										assign_editable_function(editable);
										assign_delete_function(list_item);											
									});
								}
								else {
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
								}
							}
							,{
								 indicator : '<img src="images/ajax-loader-sm.gif"/>',
								 submit:'<img src="images/bullet_disk.png"/>',
								 onblur:'ignore',
								 tooltip: '',
								 width:'1',
								 height:'1'
							}
						);
						
						$(this).parents('li').prev().find('.editable').click();	
					});
					
					
                });
				
				function assign_delete_function(list_item) {
					$(list_item).find('.action a.delete ').click(function(){
						var list_item = $(this).parents('li');
						var group_name = $(list_item).find("span[name=label]").html();
						var confirm_delete = window.confirm("Are you sure you want to delete " + group_name + "?") 
						
						if (confirm_delete) {
							var info = $(this).parents('li').attr('id').split("_");
							var group_id = info[1];
							
							var function_name = "delete_group";
							var parameters = [group_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;				
							
							$.getJSON(url, function(success) {
								if (success == false) 
									alert("Could not delete. Please try again.");
								else
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");									
							});
						}
					});
				}
				
				function assign_editable_function(editable) {
					$(editable).editable(function(value, settings) {
							var list_item = $(this).parents('li');
							var points = $(list_item).find("div[name=points]").html();
							var info = $(this).parents('li').attr("id").split("_");
							var group_id = info[1];
							var function_name = "edit_group";
							var parameters = [group_id, value];
							var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							$.getJSON(url, function(success) {
								if (success == false)
									alert("Group Not Updated. Please try again.");
								else {
									var new_html = get_new_html(group_id, value, points);
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
				}
				
				function get_new_html(group_id, value, points) {
					var new_html = "<a href='#'>";
					new_html = new_html + "<div class='title'>";
					new_html = new_html + "<span class='label editable' name='label'>" + value + "</span>";
					new_html = new_html + "</div>";
					new_html = new_html + "</a>";									
					new_html = new_html + "<a class='link' href='content.php?output=group&group_type_id=" + group_type_id + "&division_id=" + division_id + "&group_id=" + group_id + "&group_name=" + value + "'>";
					new_html = new_html + "<div class='icon'></div>";
					new_html = new_html + "<div class='name' name='points'>";
					new_html = new_html + "<div class='title'>" + points + "</div>";
					new_html = new_html + "</div>";
					new_html = new_html + "</a>";
					new_html = new_html + "<span class='action'>";
					new_html = new_html + "<a href='#' class='edit'>Edit</a>";
					new_html = new_html + "<a href='#' class='delete'>Delete</a>";
					new_html = new_html + "</span>";

					return new_html;
				}				
            </script>
			
			<div class="slider">
			
				<div class="col_title">
					<span>Groups</span>
				</div>
				
				<div class="col_content">
				
					<div class="module" id="module-info">
						<div class="module_content">
							<h1>Group Stats</h1>
							
							<? for ($cno = 0; $cno < $no_of_columns; $cno++) : ?>
								<? $start = $cno * $no_of_rows; $end = $start + $no_of_rows; ?>
								<ul class="stats">
									
									<? for ($gno = $start; $gno < $end; $gno++) : ?>
										<li><?=$groups[$gno]->group_name;?><span><?=$groups[$gno]->no_of_campers;?></span></li>
										<? if ($gno >= $no_of_groups) break; ?>
									<? endfor; ?>
								</ul>
							<? endfor; ?>

							<div class="clear"></div>
						</div>
					</div>
				
					<div class="sample_module">
					
						<div class="module lists" id="lists-grouptypes">
						
							<div class="module_content" id="">
							
								<ul class="clearfix">
									<li class="add_new">
										<a class="g_add_new_row" href="#">
											<div class="icon"></div>
											<div class="name">Add New Group</div>
										</a>
									</li>
								</ul>
								
							</div>
							
						</div>
						
					</div>
					
					<div class="module lists" id="lists-grouptypes">
						<div class="module_content" id="<?=$division_id;?>">
							<ul class="clearfix">
							
								<li>
									<h1><?=$division_name;?></h1>
								</li>

								<? for ($gno = 0; $gno < count($groups); $gno++) : ?>
								<? $group = $groups[$gno]; ?>
								
								<li id="g_<?=$group->group_id;?>">
								
									<a href="#">
										<div class="title">
											<span class='label editable' name='label'><?=$group->group_name;?></span>
										</div>
									</a>
									
									<a class="link" href="content.php?output=group&group_type_id=<?=$group_type_id;?>&division_id=<?=$division_id;?>&group_id=<?=$group->group_id;?>&group_name=<?=urlencode($group->group_name);?>">
										<div class="icon"></div>
										<div class="name" name="points">
											<? if ($group->points > 0) : ?>
												<!--<div class="title">Points: <?//=round(floatval(($group->points / $group->no_of_campers)), 2);?></div>-->
												<div class="title">Points: <?=$group->points;?></div>
											<? else: ?>
												<div class="title">Points: 0</div>
											<? endif; ?>
										</div>
									</a>
                                    <span class="action">
                                        <a href="#" class="edit">Edit</a>
                                    	<a href="#" class="delete">Delete</a>
                                    </span>
								</li>
								<? endfor; ?>
								
								<li name="action_list_item" class="add_new">
									<a class="g_add_new_row" href="#">
										<div class="icon"></div>
										<div class="name">Add New Group</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
				
				</div>
				
			</div>
