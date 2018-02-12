<? 	
include ("get_camp_id.php");
$camp_id = get_camp_id();

$group_type_id = $_GET['group_type_id'];
$group_type_name = $_GET['group_type_name'];

include ("classes/division.php");
$divisions = array();
$sql = "SELECT * FROM divisions WHERE group_type_id=" . $group_type_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query) ) {
	$division = new division($row);
	$division->get_number_of_campers($camp_id);
	$division->get_division_points($camp_id);
	array_push($divisions, $division);
}			
?>
			 <script>
				var group_type_id = "<?=$group_type_id;?>";
				var new_division_number = 0;
				
                 $(document).ready(function() {
								
					$('.d_add_new_row').click(function(){
						new_division_number++;
						
						var new_html = "<li id='new_division_" + new_division_number + "'>";
						new_html = new_html + "<span class='label editable' name='label'></span>";
						new_html = new_html + "</li>";
						
						$(this).parents('li').before(new_html);
						
						$(this).parents('li').prev().find('.editable').html('').editable(function(value, settings) {
								var list_item = $(this).parents('li');

								if (value.length > 0) {									
									var function_name = "add_division";
									var parameters = [group_type_id, value];
									var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
									$.getJSON(url, function(success) {
										if (success > 0) {
											var points = "<div class='title' name='points'>Points: 0</div>";
											var new_html = get_new_html(success, value, points);
											$(list_item).html(new_html);
											$(list_item).attr("id", "d_" + success);
											var editable =  $(list_item).find('.editable').get(0);
											assign_editable_function(editable);	
											assign_delete_function(list_item);	
										}
										else {
											alert("Could not add new division. Please try again.");
										}
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
								
					$('.action a.delete ').click(function(){
						var list_item = $(this).parents('li');
						var division_name = $(list_item).find("span[name=label]").html();
						var confirm_delete = window.confirm("Are you sure you want to delete " + division_name + "?") 
						
						if (confirm_delete) {
							$(this).parents('li').find('.action').append('<span class="progress">Progress</span>').find('.progress').show();
							var info = $(this).parents('li').attr('id').split("_");
							var division_id = info[1];

							var function_name = "delete_division";
							var parameters = [division_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" +parameters;	
								
							$.getJSON(url, function(success) {
								if (success == false) 
									alert("Could not remove division. Please try again.");
								else
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
							});						
						}
						
					});
					
					$('.editable').editable(function(value, settings) {		
							if (value.length > 0) {
								var list_item = $(this).parents('li');
								var points = $(list_item).find("div[name=points]").html();
								var info = $(this).parents('li').attr("id").split("_");
								var division_id = info[1];
								var function_name = "edit_division";
								var parameters = [division_id, value];
								var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
								$.getJSON(url, function(success) {
									if (success == false)
										alert("Group Not Updated. Please try again.");
									else {
										var new_html = get_new_html(division_id, value, points);
										$(list_item).html(new_html);
										var editable =  $(list_item).find('.editable').get(0);
										assign_editable_function(editable);
										assign_delete_function(list_item);	
									}
								});	
							}
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

				function assign_editable_function(editable) {
					$(editable).editable(function(value, settings) {
							var list_item = $(this).parents('li');
							var points = $(list_item).find("div[name=points]").html();
							
							var info = $(this).parents('li').attr("id").split("_");
							var division_id = info[1];
							var function_name = "edit_division";
							var parameters = [division_id, value];
							var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							$.getJSON(url, function(success) {
								if (success == false)
									alert("Group Not Updated. Please try again.");
								else {
									var new_html = get_new_html(division_id, value, points);
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
				
				function assign_delete_function(list_item) {
					$(list_item).find('.action a.delete ').click(function(){
						var list_item = $(this).parents('li');
						var division_name = $(list_item).find("span[name=label]").html();
						var confirm_delete = window.confirm("Are you sure you want to delete " + division_name + "?") 
						
						if (confirm_delete) {
							$(this).parents('li').find('.action').append('<span class="progress">Progress</span>').find('.progress').show();
							var info = $(this).parents('li').attr('id').split("_");
							var division_id = info[1];

							var function_name = "delete_division";
							var parameters = [division_id];
							var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" +parameters;	
								
							$.getJSON(url, function(success) {
								if (success == false) 
									alert("Could not remove division. Please try again.");
								else
									$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");
							});						
						}
						
					});
				}
				
				
				function get_new_html(division_id, value, points) {	
				
					var new_html = "<a href='#'><div class='title'>";
					new_html = new_html + "<span name='label' class='label editable'>" + value + "</span>";
					new_html = new_html + "</div></a>";
					new_html = new_html + "<a href='content.php?output=groups&group_type_id=" + group_type_id + "&division_id=" + division_id + "&division_name=" + value + "' class='link' name='link'>";
					new_html = new_html + "<div class='icon'>";
					new_html = new_html + "</div>";
					
					new_html = new_html + "<div class='name' id='points'>";
					new_html = new_html + "<div name='points' class='title'>" + points + "</div>";
					new_html = new_html + "</div>";
					
					new_html = new_html + "</a>";
					new_html = new_html + "<span class='action'>";
					new_html = new_html + "<a class='edit' href='#'>Edit</a>";
					new_html = new_html + "<a class='delete' href='#'>Delete</a>";
					new_html = new_html + "</span>";
					
					return new_html;
				}
            </script>
			
			<div class="slider">
			
				<div class="col_title">
					<span>Divisions</span>
				</div>
				
				<div class="col_content">
				
					<div class="module" id="module-info">
						<div class="module_content">
							<h1>Division Stats</h1>
							
							<? for ($dno = 0; $dno < count($divisions); $dno++) : ?>							
							<? $remainder = $dno % 2;?>
								<? if ($remainder == 0) : ?>
								<ul class="stats">
								<? endif; ?>
								
								<li><?=$divisions[$dno]->division_name;?><span><?=$divisions[$dno]->no_of_campers;?></span></li>							
								
								<? if ($remainder == 1 || $dno == (count($divisions) -1)) : ?>
								</ul>
								<? endif; ?>
							<? endfor; ?>
							
							<div class="clear"></div>
						</div>
					</div>
				
					<div class="sample_module">
					
						<div class="module lists" id="lists-divisions">
						
							<div class="module_content" id="">
							
								<ul class="clearfix">
									<li class="add_new">
										<a class="d_add_new_row" href="#">
											<div class="icon"></div>
											<div class="name">Add New Division</div>
										</a>
									</li>
								</ul>
								
							</div>
							
						</div>
						
					</div>
					
					<div class="module lists" id="lists-divisions">
						<div class="module_content">
							<ul class="clearfix">
							
								<li>
									<h1><?=$group_type_name;?></h1>
								</li>

								<? for ($dno = 0; $dno < count($divisions); $dno++) : ?>
								<? $division = $divisions[$dno]; ?>
								
								<li id="d_<?=$division->division_id;?>">
								
									<a href="#">
										<div class="title">
											<span class='label editable' name='label'><?=$division->division_name;?></span>
										</div>
									</a>
									
									<a class="link" href="content.php?output=groups&group_type_id=<?=$group_type_id;?>&division_id=<?=$division->division_id;?>&division_name=<?=$division->division_name;?>">
										<div class="icon"></div>
										<div class="name">
											<? if ($division->points > 0) : ?>
												<!--<div class="title" name="points">Points: <?//=round(floatval(($division->points / $division->no_of_campers)), 2);?></div>-->
												<div class="title" name="points">Points: <?=$division->points;?></div>
											<? else: ?>
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
								
								<li name="action_list_item" class="add_new">
									<a class="d_add_new_row" href="#">
										<div class="icon"></div>
										<div class="name">Add New Division</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
				
				</div>
				
			</div>
