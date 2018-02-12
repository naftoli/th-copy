<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');

function get_group_types() {
	global $camp_id;
	
	$echo_string = "\n";
	
	$sql = "";
	$sql = $sql . "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "WHERE camp_id=" . $camp_id;
	
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$row_num = 0;
	$prev_group_type_id = "";
	$prev_division_id = "";
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		if ($prev_division_id != $row['division_id'] && $prev_group_type_id != "") { 
			$echo_string = $echo_string . "<li id='add_group'>";
			$echo_string = $echo_string . "<span class='icon add'></span>";
			$echo_string = $echo_string . "<span class='label'>";
			$echo_string = $echo_string . "<a id='" . $prev_division_id . "' name='" . $prev_division_id . "' class='add_new_row' href='#'>" . T_("Add Group") . "</a>";
			$echo_string = $echo_string . "</span>";
			$echo_string = $echo_string . "<div class='clear'></div>";
			$echo_string = $echo_string . "</li>";			
		}		
		
		// ***** GROUP TYPES ***** //
		if ($prev_group_type_id	!= $row['group_type_id']) {
		
			if ($prev_group_type_id	!= "") {	
				$echo_string = $echo_string . "</ul>";
				$echo_string = $echo_string . "</div>";
			
				$echo_string = $echo_string . "</div>";
				$echo_string = $echo_string . "</div>";
			}
		
			$echo_string = $echo_string . "<div id='module-info' class='module'>";
			$echo_string = $echo_string . "<h1 style='color:darkgreen'>" . $row['group_type_name'] . "</h1>";
			$echo_string = $echo_string . "<div class='module_content'>";
			
			$echo_string = $echo_string . "<div class='list'>";
			$echo_string = $echo_string . "<ul>";
			
		}
		// ***** GROUP TYPES ***** //
		
		// ****** DIVISIONS ***** //
		if ($prev_division_id != $row['division_id']) {
			$echo_string = $echo_string . "<li>";
			$echo_string = $echo_string . "<span class='icon bullet'></span>";
			$echo_string = $echo_string . "<span class='label'><h2 style='color:darkgreen;'>" . $row['division_name'] . " " . T_("Division") . "</h2></span>";
			$echo_string = $echo_string . "<div class='clear'></div>";
			$echo_string = $echo_string . "</li>";
		}
		// ****** DIVISIONS ***** //

		// ***** GROUPS ***** //
		$groups = mq("SELECT * FROM groups WHERE division_id=" . $row['division_id']);
		$num_groups = mysql_num_rows($groups);
		if ($num_groups > 0)  {
			while ($group = mysql_fetch_assoc($groups)) {
				if ($group['group_name'] != "") {
					$echo_string = $echo_string . "<li id='group_" . $group['group_id'] . "'>";			
					$echo_string = $echo_string . "<span class='action'>";
					$echo_string = $echo_string . "<span class='remove'>";
					$echo_string = $echo_string . "<a onclick='delete_group(" . $group['group_id'] . ");' title='Delete' href='#'>Delete</a>";
					$echo_string = $echo_string . "</span>";
					$echo_string = $echo_string . "<span class='edit'>";
					$echo_string = $echo_string . "<a title='Edit' href='#'>Edit</a>";
					$echo_string = $echo_string . "</span>";
					$echo_string = $echo_string . "</span>";
					// ***** GROUP ID must be set in the ID for EDITING/SAVING purposes ***** //
					$echo_string = $echo_string . "<span id='group_" . $group['group_id'] . "' class='label editable'>" . $group['group_name'] . "</span>";
					// ***** GROUP ID must be set in the ID for EDITING/SAVING purposes ***** //
					$echo_string = $echo_string . "<div class='clear'></div>";
					$echo_string = $echo_string . "</li>";
				}
			}
		}
		else {
			$echo_string = $echo_string . "<div id='" . $row['division_id'] . "' name='" . $row['division_id'] . "'>";		
			$echo_string = $echo_string . "<li>";
			$echo_string = $echo_string . "<label>" . T_("How many groups would you like to generate?"). " </label>";
			$echo_string = $echo_string . "<input type='text' maxlength='2' onkeypress='return number_validation(event);'>";
			$echo_string = $echo_string . "<div class='clear'></div>";
			$echo_string = $echo_string . "</li>";
			
			$echo_string = $echo_string . "<li>";
			$echo_string = $echo_string . "<label>" . T_("How would you like to name your groups?") . "</label>";
			$echo_string = $echo_string . "<input type='radio' value='Alef' name='bunk_name_format'>" . T_("Alef,Beis,Gimmel");
			$echo_string = $echo_string . "<input type='radio' value='A' name='bunk_name_format'>" . T_("A,B,C");
			$echo_string = $echo_string . "<input type='radio' value='1' name='bunk_name_format'>" . T_("1,2,3");
			$echo_string = $echo_string . "<p><a class='button' onclick='generate_groups(" . $row['division_id'] . ", \"" . $row['division_name'] . "\")'>" . T_("Generate") . "</a></p>";
			$echo_string = $echo_string . "<div class='clear'></div>";
			$echo_string = $echo_string . "</li>";
			$echo_string = $echo_string . "</div>";			
		}
		// ***** GROUPS ***** //
		
		if ($row_num == $num_rows) {
			$echo_string = $echo_string . "<li id='add_group'>";
			$echo_string = $echo_string . "<span class='icon add'></span>";
			$echo_string = $echo_string . "<span class='label'>";
			$echo_string = $echo_string . "<a id='" . $row['division_id'] . "' name='" . $row['division_id'] . "' class='add_new_row' href='#'>" . T_("Add Group") . "</a>";
			$echo_string = $echo_string . "</span>";
			$echo_string = $echo_string . "<div class='clear'></div>";
			$echo_string = $echo_string . "</li>";			
		}
		
		$prev_group_type_id	= $row['group_type_id'];
		$prev_division_id = $row['division_id'];
	}
	
	echo $echo_string;
}
?>
 			<script src="scripts/jquery.jeditable.min.js"></script>
			
			<script>
				var new_groups = 0;
				var division_id = 0;
				var group_id = 0;
				
				$(function() {
					$('.edit a').live('click',function(){$(this).parents('li').find('.editable').click()});
					
					
					$('.bullet').click(function(event) {
					
				        $(this).parents('li').find('.editable').get(0).reset();
					});
					
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
						                     });
					
					
					$(".slider:last .remove a").click(function(event) {
						event.preventDefault();
                        $(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("fast");
					});
					
					$("a.add_new_row").click(function(event) {
						division_id = $(this).attr("name");
						
						new_groups++;
						
						var list_item_html = get_list_item_html(new_groups, division_id);
						
                        $(this).parents('li').before(list_item_html);
												
						$(this).parents('li').prev().find('.editable').html("").editable( 
								function(value, settings) 
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
										}
										
										return value;
									}, 
									{
										indicator : '<img src="images/ajax-loader-sm.gif"/>',
										submit:'<img src="images/bullet_disk.png"/>',
										onblur:'ignore',
										width:'143',
										height:'14' 
						});
						
						$(this).parents('li').prev().find('.editable').click();
						
						//$(this).parents('li').prev().find('.bullet').click(function(event) {					
				            //$(this).parents('li').find('.editable').get(0).reset();
				        
					    //});
					    
					}); // add_new_row
					
					
				})
				
				function get_list_item_html(new_groups, division_id) {
					return "<li id='new_" + new_groups + "'>" + 
							"<span class=\"action\">" +
							"<span class=\"remove\"><a href=\"#\" onclick='remove_group(this);' title=\"Delete\">Delete</a></span>" +
							"<span class=\"edit\"><a href=\"#\" title=\"Edit\">Edit</a></span>" +
							"</span>" +
							"<span id='division_" + division_id + "' class=\"label editable\">" + 
							"</span>" + 
							"</li>"; 				
				}
				
				function remove_group(obj) {
					var li_id = $(obj).parents('li').attr("id");
					
					var info = li_id.split("_");
					var group_id = info[1];
					
					var url = "save_group.php?action=remove&group_id=" + group_id;
					
					var http = getHTTPObject();
					http.open("GET", url, true);
					http.send(null);

					$("#" + li_id).css({backgroundColor: "#ff0000"}).fadeOut("fast");							
					
				}
				
				function edit_group(group_id) {
					$("#" + group_id).find('.editable').click();
				}
				
				function generate_groups(division_id, division_name) {
					var div = document.getElementById(division_id);
					var inputs = div.getElementsByTagName("input");
					var format = "";
					var number_of_groups = 0;
					var input_number = 0;
					var innerHTML = "";
					
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
						alert("<?=T_('30 Groups maximum');?>");
						inputs[input_number].focus();
					}
					else if (number_of_groups == 0) {
						alert("<?=T_('You must enter how many groups would you like to generate');?>");
						inputs[input_number].focus();
					}
					else if (format == "") {
						alert("<?=T_('You must choose how would you like to name your groups');?>");
					}
					else {
						var url = "save_group.php?action=generate&format=" + format + "&division_id=" + division_id + "&number_of_groups=" + number_of_groups + "&division_name=" + division_name;

						var http = getHTTPObject();
						http.open("GET", url, true);
						http.onreadystatechange = function() {
							if (http.readyState == 4 && http.status == 200) {
								if (http.responseText == "") {
									alert("Groups cold not be added");
								}
								else {
									
									// ***** If the generate inserts were succesful then the generate information ***** //
									// ***** needs to be replaced with the new groups that were generated         ***** //
									document.getElementById(division_id).innerHTML = "";
									
									var info1 = http.responseText.split("|");
																							
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
									// ***** If the generate inserts were succesful then the generate information ***** //
									// ***** needs to be replaced with the new groups that were generated         ***** //

								}
							}
						}						
						http.send(null);					
					}
				}
								
				function delete_group(group_id) {
					var url = "save_group.php?action=remove&group_id=" + group_id;
					var http = getHTTPObject();
					http.open("GET", url, true);
					http.send(null);				
				}
				
				function delete_group_two(group_id) {
					var index_of = group_id.indexOf("group");

					if (index_of == -1)				
						var url = "save_group.php?action=remove&group_id=" + group_id;
					else {
						var info = group_id.split("_");
						var url = "save_group.php?action=remove&group_id=" + info[1];
					}
					
					var http = getHTTPObject();
					http.open("GET", url, true);
					http.send(null);

					var li_id = "#" + group_id;
					$(li_id).css({backgroundColor: "#ff0000"}).fadeOut("fast");							
				}				
				
				function save_group(editableElement, new_group_id, group_name) {
					var url = "save_group.php?action=update&group_id=" + new_group_id + "&group_name=" + group_name; 
					
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) 						
							editableElement.reset();
					}
					http.send(null);					
				}
								
				function save_new_group(editableElement, old_division_id, group_name, li_id) {	
					var url = "save_group.php?action=insert&division_id=" + old_division_id + "&group_name=" + group_name; 
								
					var http = getHTTPObject();
					http.open("GET", url, true);
											
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
							group_id = http.responseText;							
							division_id = 0;
							editableElement.reset();
							document.getElementById(li_id).id = "group_" + group_id;
						}	
					}
					http.send(null);
				}					
				
				function getHTTPObject() {
					var xmlhttp;

					if (window.XMLHttpRequest) {
						xmlhttp = new XMLHttpRequest();
					}
					else if (window.ActiveXObject){ 
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
									
						if (!xmlhttp) {
							xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
						}
					}
								
					return xmlhttp; 
				}
			</script>

			<script type="text/javascript" src="jquery.form.js"></script> 

			<div class="slider">
			
				<div class="col_title">
					<span>Getting Started</span><a class="slider_back">back</a>
				</div>
				
				<div class="col_content">
                    <h1><?=T_("Setup Camp Profile");?></h1>
								
					<? get_group_types(); ?>																		
										
				</div> <!-- <div class="col_content"> -->

				<input type="button" onclick="alert(group_id);" value="TEST">
				
			</div> <!-- <div class="slider"> -->

			
			