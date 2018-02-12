<?php

include ("get_camp_id.php");
$camp_id = get_camp_id();

$admin_or_user = $_GET["show"];
$user_id = $_GET["user_id"];

$group_types = array();
$divisions = array();
$groups = array();

$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name ";
$sql = $sql . "FROM group_types AS gt ";
$sql = $sql . "JOIN divisions AS d USING (group_type_id) ";
$sql = $sql . "JOIN groups AS g USING (division_id) ";
$sql = $sql . "WHERE camp_id=" . $camp_id . " ";
$sql = $sql . "ORDER BY gt.group_type_id, d.division_id, g.group_id";
$query = mysql_query($sql);

$num_rows = mysql_num_rows($query);
$prev_group_type_id = "";
$prev_division_id = "";
$row_num = 0;
while ($row = mysql_fetch_assoc($query)) {
	$row_num++;
	
	$prev_group_type_id = $row['group_type_id'];
	$prev_division_id = $row['division_id'];
	
	$group_id = $row['group_id'];
	$group_name = $row['group_name'];
	
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
	
	$element = compact('group_id', 'group_name');
	array_push($groups, $element);
	
	$division_id = $row['division_id'];
	$division_name = $row['division_name'];
	
	$group_type_id = $row['group_type_id'];
	$group_type_name = $row['group_type_name'];
	
	if ($row_num == $num_rows) {
		$element = compact('division_id', 'division_name', 'groups');
		array_push($divisions, $element);
		$element = compact('group_type_id', 'group_type_name', 'divisions');
		array_push($group_types, $element);	
	}
}

json_encode($group_types);
?>
		 <script>
			var user_id = "<?=$user_id;?>";
			var camp_id = "<?=$camp_id;?>";
			
             $(document).ready(function() {
                $(".checklist input:checked").parent().addClass("selected");
                $(".checklist .checkbox-select").click(
                    function(event) {
                        event.preventDefault();
                        $(this).parents('.checklist').addClass("selected");
                        $(this).parents('.checklist').find(":checkbox").attr("checked","checked");
                    }
                );
				
                $(".checklist .checkbox-deselect").click(
                    function(event) {
                        event.preventDefault();
                        $(this).parents('.checklist').removeClass("selected");
                        $(this).parents('.checklist').find(":checkbox").removeAttr("checked");
                    }
                );
				
				/*$(".side_menu > ul").tabs(".side_menu > ul > ul, .side_main > ul", {effect:'slide',tabs:'> li'});
				$(".side_menu > ul > ul").tabs(".side_main > ul", {effect:'fade'});*/
				$(".side_menu > ul").tabs(".side_main ul > div", {effect:'fade'});
				
				//$("").tabs("> .module_content");
				/*$('.side_main > ul > li h1').click(function() {
						$(this).next().toggle('fast');
						return false;
				}).next().hide().first().show();*/
				
				//$(".side_main .group_type").tabs(".side_main .module", {tabs: 'h1', effect: 'slide', initialIndex: null});
				
				$('.check_all').click(function() {
					$(this).parent().parent().find('.checklist .checkbox-select').click()
				});
				
				$('.uncheck_all').click(function() {
					$(this).parent().parent().find('.checklist .checkbox-deselect').click()
				});
			
				/**************************-------In middle of programming....put on hold 7/22 - Hirshy --------*****************
				$('.checklist .checkbox-deselect').click(function(){
						if (admin_or_user == "admin") {
							var staff_group_id = info[1];
							var action = "remove_staff_member";
							var params = [staff_group_id];
							var message = "Could not remove staff member. Please try again.";
						}
						else if (admin_or_user == "user") {
							var member_group_id = info[1];
							var user_id = info[2];
							var action = "remove_member_group";
							var params = [member_group_id, group_id, user_id];
							var message = "Could not remove camper. Please try again.";
						}
						
						var url = "appInterface.php?action=" + action + "&params=" + params;
						$.getJSON(url, function(error_code) {
							if (error_code == 0) 
								$("#" + li_id).css({backgroundColor: "#ff0000"}).fadeOut("slow");
							else 
								alert(message);
						});												
				});
				$(".checklist .checkbox-select").click( 
					function(event) {
						var li = $(this).parents('li');
						var li_id = li.attr('id');
						var admin_or_user = <?=$admin_or_user?>;
						
						event.preventDefault();

						if (admin_or_user == 'admin') {
						

						$(this).parents('.checklist').addClass("selected");
						$(this).parents('.checklist').find(":checkbox").attr("checked","checked");
						$(this).parents('li').css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
						$(this).parents('li').find('.progress').show().delay(500).fadeOut(500);							
														
						var user_id = $(this).parents('li').attr("id");	
						var info = $(this).parents('li').attr("name").split(":");
						var name = info[0] + " " + info[1];
						var user_photo_id = info[2];
							
						function_name = "assign_member_group";				
						parameters = [camp_id, user_id, group_type_id, division_id, group_id];
						var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						$.getJSON(url, function(error_code) {
							//alert("error_code:" + error_code);
							if (error_code == 1) {
								alert("Could not place camper. Please try again.");
								$(li).find('.checklist').removeClass("selected");
							}
							else {
								var member_group_id = error_code;
								var li_id = "user_" + member_group_id;
								var new_html = 	"<li id='" + li_id + "'>" + 
												"<a class='link' href='content.php?output=camper&amp;profile=campers&amp;campers_id=" + user_id + "'>" + 
												"<div class='image'>" + 
												"<img src='includes/file_view.php?id=" + user_photo_id + "' height='32'>" + 
												"</div>" + 
												"<div class='name'>" + name + "</div>" +
												"</a>" + 
												"<span class='action'>" + 
												"<a href='#' class='remove' onclick='remove_member(" + member_group_id + ", " + user_id + ", " + group_id + ");'>Remove</a>" + 
												"</span>" + 
												"</li>";
								$("#assign_a_camper").before(new_html);	
																
							}
							
						}); // $.getJSON(url, function(error_code) {
						
				}); // $(".checklist .checkbox-select").click( 
				-----------------------------------***********************/

            });
			
			function check_groups(group_id) {				
				var info = group_id.split(":");
				var group_type_id = info[0];
				var division_id = info[1];
				var group_id = info[2];
				
				var action = "set_member_group";
				var params = [user_id, group_type_id, division_id, group_id, camp_id];
				var url = "appInterface.php?action=" + action + "&params=" + params;	
				
				$.getJSON(url, function(error_code) {				
					var division_div = document.getElementById("d_" + division_id);
					var no_of_groups = $(division_div).find("li").size();
					for (cntr = 0; cntr < no_of_groups; cntr++) {
						var li =  $(division_div).find("li").get(cntr);
						if (li.id != "g_" + group_id) {
							$(li).find('.checklist .checkbox-deselect').click();
						}
					}
				
				});


				
			}
			
			function uncheck_group(group_id) {
				var action = "delete_member_group";
				var params = [user_id, group_id];
				var url = "appInterface.php?action=" + action + "&params=" + params;	
				$.getJSON(url, function(error_code) {
				});

			}
        </script>
		
			<div class="slider">
			
				<div class="col_title">
					Choose Groups
				</div>
			
				<div class="side_tabs">
				
					<!-- ***** GROUP TYPES ***** -->
					<div class="side_menu">
						<ul>
							<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>
							<li><?=$group_types[$gtno]['group_type_name'];?></li>
								<!-- ***** GROUP TYPE - DIVISIONS ***** -->
								<!--<ul>								
									
										
									
										<!-- ***** DIVISIONS ***** -->
										<li class="group_type">All <?=$group_types[$gtno]['group_type_name'];?></li>
										<? for ($dvno = 0; $dvno < count($group_types[$gtno]['divisions']); $dvno++) : ?>
										<? $division = $group_types[$gtno]['divisions'][$dvno] ?>														
										<li class="group_type"><?=$division['division_name'];?></li>
										<? endfor; ?>
								</ul>-->
									
							<? endfor; ?>
						</ul>
					</div>
					<!-- ***** GROUP TYPES ***** -->
					
				</div>
				
				<div class="col_content">
				
					<div class="module">
					
						<div class="module_content side_tabs">
						
							<div class="list side_main">
								
								<!-- ***** GROUP TYPE - DIVISIONS ***** -->
								<ul>								
									<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>
									
									<div class="group_type">	
									
										<!-- ***** DIVISIONS ***** -->
										<? for ($dvno = 0; $dvno < count($group_types[$gtno]['divisions']); $dvno++) : ?>
										<? $division = $group_types[$gtno]['divisions'][$dvno] ?>														
										<h1><?=$division['division_name'];?></h1>	


											<!-- ***** GROUPS ***** -->
												 
													<div class="list" id="d_<?=$division['division_id'];?>">
															
															<? for ($gno = 0; $gno < count($division['groups']); $gno++) : ?>
															<? $group = $division['groups'][$gno]; ?>
															<li id="g_<?=$group['group_id'];?>">
															
																<span class="action">
																
																	<span class="checklist">
																	
																		<? $query = mysql_query("SELECT * FROM member_groups WHERE user_id=" . $user_id . " AND group_id=" . $group['group_id']); ?>
																		<? $num_rows = mysql_num_rows($query); ?>																		
																		<? if ($num_rows > 0) : ?>
																			<input type="checkbox" id="" class="checkbox"  checked="checked" />
																		<? else : ?>
																			<input type="checkbox" id="" class="checkbox" />
																		<? endif; ?>
																		<span class="checkbox_check">
																			<a on_click="check_groups('<?=$group_types[$gtno]['group_type_id'];?>:<?=$division['division_id'];?>:<?=$group['group_id'];?>');" href="#" title="Check" class="checkbox-select">
																				<span class="icon"></span>
																			</a>
																			<a on_click="uncheck_group('<?=$group['group_id'];?>');" href="#" title="Uncheck" class="checkbox-deselect">
																				<span class="icon"></span>
																			</a>
																		</span>
																		
																	</span>
																	
																</span>
																
																<span class="icon bullet"></span>
																
																<span class="label"><?=$group['group_name'];?></span>
																
																<div class="clear"></div>
															</li>
															<? endfor; ?>
															
													</div> <!-- <div class="list"> -->
													
											 <!-- ***** GROUPS ***** -->
											 
										<? endfor; ?>
										<!-- ***** DIVISIONS ***** -->
									
									</div>
									
									<? endfor; ?>									
								</ul>								
								<!-- ***** GROUP TYPE - DIVISIONS ***** -->
								
								</div>
								
							</div>
							
						</div> <!-- <div class="module_content side_tabs"> -->
						
				</div> <!-- <div class="col_content"> -->
     
			</div> <!-- <div class="slider"> -->
