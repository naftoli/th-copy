<?
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/admin.php");
include ("classes/staff_type.php");

$staff_type_id = $_GET['staff_type_id'];
$admins = array();
$sql = "SELECT * FROM admins WHERE camp_id=" . $camp_id;
if ($staff_type_id > 0)
	$sql = $sql . " AND staff_type_id=" . $staff_type_id;
elseif ($staff_type_id == -1)
	$sql = $sql . " AND staff_type_id IS NULL";
$sql = $sql . " ORDER BY first, last";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$admin = new admin($row);
	array_push($admins, $admin);
}

$type_name = "";
if ($staff_type_id > 0) {
	$sql = "SELECT * FROM staff_types WHERE staff_types_id=" . $staff_type_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$type_name = $row['type_name'];
}
?>
			 <script>
                 $(document).ready(function() {
					$('.action a.remove ').click(function() {
						var list_item = $(this).parents('li');
						var info = $(list_item).attr("id").split("_");
						var admin_id = info[1];
						var function_name = "remove_staff_type";
						var parameters = [admin_id];
						var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(update) {
							if (update == false) 
								alert("Could not remove staff member. Please try again");
							else 
								$(list_item).css({backgroundColor: "#ff0000"}).fadeOut("slow");	
						});						
					});
					
                    $(".register .button").click(
                        function(event) {
							
							$.get('../includes/registerCamper.php',{id:$(this).attr('title')}, function(data){ alert(data); });
							event.preventDefault();
							$(this).parents('li').addClass("selected");
							$(this).parents('li').find('.action').append('<span class="progress">Progress</span>').find('.progress').show().delay(500).fadeOut(500,function(){
					

							$(this).parents('li').slideUp('fast',function(){
									$(this).remove();
								})
							});
                        }
                    );
                });
            </script>

			<div class="slider">
			
                <div class="col_title">
					<? if ($type_name != "") : ?>
						<span><?=$type_name;?></span>
					<? else : ?>
						<span>All Staff</span>
					<? endif; ?>
					<a class="slider_back">Manage Staff</a>
				</div>
				
				<div class="col_content">
				
                    <div id="module-info" class="module">					
						<? include ("staff_header.php"); ?>						
                    </div>
					                  
                    <div id="lists-userlist" class="module lists">
						<div class="module_content">
							<ul>	
								<? for ($ano = 0; $ano < count($admins); $ano++) : ?>
								<li id="a_<?=$admins[$ano]->admin_id;?>">
									<a class="link" href="content.php?output=staff_profile&admin_id=<?=$admins[$ano]->admin_id;?>">									
										<div class="name"><?=$admins[$ano]->first;?> <?=$admins[$ano]->last;?></div>
									</a>
									<? if ($staff_type_id > 0) : ?>
                                    <span class="action">
                                    	<a href="#" class="remove">Remove</a>
                                    </span>
									<? endif; ?>
								</li>								
								<? endfor; ?>

								<? if ($staff_type_id > 0) : ?>
								<li name="assign_a_staff_member" id="assign_a_staff_member">
									<a href="content.php?output=overlay_assign_staff&staff_type_id=<?=$staff_type_id;?>&type_name=<?=urlencode($type_name);?>" class="overlay">
										<div class="icon"></div>
										<div class="name">Place a Staff Member in Staff Type</div>
									</a>								
								</li>
								<? endif; ?>
								
							</ul>
						</div>
					</div>
					
  				</div>
				
			</div> 