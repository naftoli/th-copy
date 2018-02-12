<?
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/staff_type.php");
$staff_types = array();
$sql = "SELECT * FROM staff_types";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$staff_type = new staff_type($row);
	array_push($staff_types, $staff_type);
}
?>

			<script>
				var new_staff_type_number = 0;
				
				 $(document).ready(function() {
				 
					$('.add_new_row').click(function() {
						new_staff_type_number++;
						
						var list_item = $(this).parents('li');
						var li_id = "new_staff_type_" + new_staff_type_number;
						var new_html = "<li name='" + li_id + "' id='" + li_id + "'>";
						new_html = new_html + "<span class='label editable' name='label'></span>";
						new_html = new_html + "</li>";
						
						$(list_item).before(new_html);
						
						$(list_item).prev().find('.editable').html('').editable(function(value, settings) 
							{
								var function_name = "add_staff_type";
								var parameters = [value];
								var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
								$.getJSON(url, function(success) {
									if (success > 0) {
										new_html = "<a class='link' href='content.php?output=staff_list&staff_type_id=" + success + "'><div class='icon'></div><div class='name'>" + value + "</div></a>";
										$("#" + li_id).html(new_html);
									}
									else {
										alert("Staff type not added. Please try again.");
									}									
								});
							},
							{
							indicator : '<img src="images/ajax-loader-sm.gif"/>',
							submit:'<img src="images/bullet_disk.png"/>',
							onblur:'ignore',
							tooltip: '',
							width:'1',
							height:'1'
							}
						);
						
						$(list_item).prev().find('.editable').click();							
					});
					
				});
			</script>
			
			<div class="slider">
			
				<div class="col_title">
					<span>Manage Staff</span>
				</div>
				
				<div class="col_content">
				
                    <div class="module" id="module-info">					
						<? include ("staff_header.php"); ?>						
                    </div>
					
					<div class="module lists" id="lists-staff-roles">
						<div class="module_content">
							<ul>
								<li>
									<a class="link" href="content.php?output=staff_list&staff_type_id=0">
										<div class="icon"></div>
										<div class="name">All Staff</div>
									</a>
								</li>
								
								<? for ($stno = 0; $stno < count($staff_types); $stno++) : ?>									
									<li>
										<a class="link" href="content.php?output=staff_list&staff_type_id=<?=$staff_types[$stno]->staff_types_id;?>">
											<div class="icon"></div>
											<div class="name"><?=$staff_types[$stno]->type_name;?></div>
										</a>
									</li>								
								<? endfor; ?>
																
								<li>
									<a href="#" class="add_new_row">
										<div class="icon"></div>
										<div class="name">Add New Staff Type</div>
									</a>
								</li>
								
								<? if ($unassigned_staff > 0) : ?>
									<li>
										<a class="link" href="content.php?output=staff_list&staff_type_id=-1">
											<div class="icon"></div>
											<div class="name">Unassigned</div>
										</a>
									</li>
								<? endif; ?>								
								
							</ul>
						</div>
					</div>
					<div class="module lists" id="lists-staff">
						<div class="module_content">
							<ul>
								<li>
									<a class="link" href="content.php?output=staffadd">
										<div class="icon"></div>
										<div class="name">Add New Staff Member</div>
									</a>
								</li>
								<li>
									<a class="link" href="content.php?output=staffroles">
										<div class="icon"></div>
										<div class="name">Staff Roles</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
<!-- ******************************************* DATABASE ******************************************* -->														
  				</div>
			</div>
