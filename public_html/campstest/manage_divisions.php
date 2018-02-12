<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');

function get_divisions() {
	global $camp_id;
	
	$echo_string = "";
	
	$query = mq("SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name FROM group_types AS gt JOIN divisions AS d USING (group_type_id) WHERE gt.camp_id=" . $camp_id); 
	
	$prev_group_type_id = "";
	$prev_division_id = "";
	while ($row = mysql_fetch_assoc($query)) {
	
		if ($prev_group_type_id != $row['group_type_id']) {
		
			if ($prev_group_type_id != "") {
			
			$echo_string = $echo_string . "<li>";
			$echo_string = $echo_string . "<span class='icon add'></span>";
			$echo_string = $echo_string . "<span class='label'><a class='add_new_row' href='#'>Add Division</a></span>";
			$echo_string = $echo_string . "<span class='no_division'>";
			$echo_string = $echo_string . "<span class='icon remove'></span>";
			$echo_string = $echo_string . "<a href='#'>No divisions for this type</a>";
			$echo_string = $echo_string . "</span>";
			$echo_string = $echo_string . "<div class='clear'></div>";
			$echo_string = $echo_string . "</li>";
			
				$echo_string = $echo_string . "\t\t\t</ul>\n";
				$echo_string = $echo_string . "\t\t</div>\n";
				$echo_string = $echo_string . "\t</div>\n";			
				$echo_string = $echo_string . "</div>\n";
			}
			
			$echo_string = $echo_string . "\t<div id='module-info' class='module list_divisions'>\n";
			$echo_string = $echo_string . "\t\t<h1>" . $row['group_type_name'] . "</h1>\n";
			$echo_string = $echo_string . "\t\t<div class='module_content'>\n";
			$echo_string = $echo_string . "\t\t<div class='list'>\n";
			$echo_string = $echo_string . "\t\t\t<ul>\n";
			
		}
		
		
		
		$echo_string = $echo_string . "\t\t\t<li>\n";
		$echo_string = $echo_string . "<span class='action'>";
		$echo_string = $echo_string . "<span class='remove'><a title='Delete' href='#'>Delete</a></span>";
		$echo_string = $echo_string . "<span class='edit'><a title='Edit' href='#'>Edit</a></span>";
		$echo_string = $echo_string . "</span>";
		$echo_string = $echo_string . "<span class='icon bullet'></span>";
		$echo_string = $echo_string . "<span class='label'>" . $row['division_name'] . "</span>";
		$echo_string = $echo_string . "<div class='clear'></div>";
		$echo_string = $echo_string . "</li>";
		
		//if ($prev_division_id != $row['division_id'] && $prev_division_id != "") {
		//}
		
		
		$prev_group_type_id = $row['group_type_id'];
		$prev_division_id = $row['division_id'];
		
	}
	
	
	
	echo $echo_string;
}
?>

			<script src="scripts/jquery.jeditable.min.js"></script>
			<script type="text/javascript" src="jquery.form.js"></script> 
 			<script>
				var camp_id = "<?=$camp_id;?>";
				var new_division_number = 0;
				
				$(function() {
								
					$("a.add_new_row").click(function(event) {
						var group_type_id = $(this).attr("name");
						
						new_division_number++;
						
						var div_name = "new_division_" + new_division_number;
						
						var new_html = "<span class='action' name='action'>";
						new_html = new_html + "<span class='remove' name='remove'><a title='Delete' href='#' onclick='delete_new_group_type(\"#" + div_name + "\");'>Delete</a></span>";
						new_html = new_html + "<span class='edit' name='edit'><a title='Edit' href='#'>Edit</a></span>";
						new_html = new_html + "</span>";
						new_html = new_html + "<span class='icon bullet' name='bullet'></span>";
						new_html = new_html + "<span class='label editable' name='label'></span>";

						html = '<div id="' + div_name + '"><li>' + new_html + '</li></div>';
						
						$(this).parents('li').before(html);
						$(this).parents('li').prev().find('.editable').html('').editable('http://www.example.com/save.php',{
							 indicator : '<img src="images/ajax-loader-sm.gif"/>',
							 submit:'<img onclick="save_division(' + group_type_id + ');" src="images/bullet_disk.png"/>',
							 onblur:'ignore',
							 width:'143',
							 height:'14'
						});
						
						$(this).parents('li').prev().find('.editable').click();											
						
					});
					
					$(".slider:last .remove a").click(function(event) {
						//event.preventDefault();
						//$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("slow");
					});
				
				
				})
				
				function save_division(group_type_id) {
					var div_name = "new_division_" + new_division_number;
					var new_group_type_div = document.getElementById(div_name);					
					var inputs = new_group_type_div.getElementsByTagName("input");
					var division_name = inputs[0].value;
				
					var url = "insert_division.php?group_type_id=" + group_type_id + "&division_name=" + division_name;
					
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
							setTimeout("set_division_name('" + division_name + "', '" + div_name + "', '" + http.responseText + "')", 250);
						}
					}
					http.send(null);					

				}
				
				function set_division_name(division_name, div_name, division_id) {
					var new_innerHTML = "<li id='li" + division_id + "'>";										
					new_innerHTML = new_innerHTML + "<span name='action' class='action'>";
					new_innerHTML = new_innerHTML + "<span name='remove' class='remove'><a title='Delete' href='#' onclick='delete_group_type(" + division_id + ");'>Delete</a></span>";
					new_innerHTML = new_innerHTML + "<span name='edit' class='edit'><a href='#' title='Edit'>Edit</a></span>";
					new_innerHTML = new_innerHTML + "</span>";
					new_innerHTML = new_innerHTML + "<span name='bullet' class='icon bullet'></span>";
					new_innerHTML = new_innerHTML + "<span name='label' class='label editable'>" + division_name + "</span>";
					new_innerHTML = new_innerHTML + "</li>";
					
					document.getElementById(div_name).innerHTML = new_innerHTML;
				}
				
				
				function delete_division(division_id) {
					var division_name = "#" + division_id;
					$(division_name).css({backgroundColor: "#ff0000"}).fadeOut("slow");							
				
					var url = "delete_division.php?division_id=" + division_id;
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
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

			<script type="text/javascript">	
			</script>

			<div class="slider">
			
				<div class="col_title">
					<span>Getting Started</span>
					<a class="slider_back">back</a>
				</div> <!-- <div class="col_title"> -->
				
				<div class="col_content">
                    <!--<h1><?//=T_("Setup Camp Profile");?></h1>-->

						
						<div id="module-info" class="module">
							<div class="module_content">
								<p>In this step please tell us how you wish to divide the group types you selected.</p>
							</div>
						</div>
						
						<? get_divisions(); ?>
						
				</div> <!-- <div class="col_content"> -->
				
			</div> <!-- <div class="slider"> -->

			
			