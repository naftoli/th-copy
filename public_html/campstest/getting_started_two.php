<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');
?>

			<script src="scripts/jquery.jeditable.min.js"></script>
			<script type="text/javascript" src="jquery.form.js"></script> 
 			<script>
				var camp_id = "<?=$camp_id;?>";
				var new_group_type_number = 0;
				var new_group_type_name = "";
				
				$(function() {					
					$('.edit a').live('click',function(){$(this).parents('li').find('.editable').click()});
					
					//$('.editable').editable('http://mashpia.com/campstest/save_group_type.php?group_type_name=NewGroupType',{
					//	 indicator : '<img src="images/ajax-loader-sm.gif"/>',
					//	 submit:'<img src="images/bullet_disk.png"/>',
					//	 onblur:'ignore',
					//	 width:'143',
					//	 height:'14'
					//});
					
					$("a.add_new_row").click(function(event) {
						//event.preventDefault();
						//var html = '<li><span class="action"><span class="save"><a href="#" title="Save">Save</a></span></span><span class="icon bullet"></span><span class="label"><input type="text" /></span></li>';
						
						new_group_type_number++;
						
						var div_name = "new_group_type_" + new_group_type_number;
						
						html = '<div id="' + div_name + '"><li>' + $(this).parents('li').prev().html() + '</li></div>';
						$(this).parents('li').before(html);
						$(this).parents('li').prev().find('.editable').html('').editable('http://www.example.com/save.php',{
							 indicator : '<img src="images/ajax-loader-sm.gif"/>',
							 submit:'<img onclick="save_group_type();" src="images/bullet_disk.png"/>',
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
				
				$('#target').click(function() { alert('Handler for .click() called.'); });
				
				function delete_group_type(group_type_id) {
					var id_name = "#li" + group_type_id;
					var url = "delete_group_type.php?group_type_id=" + group_type_id;
								
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
							//$(id_name).fadeOut("slow");
							$(id_name).css({backgroundColor: "#ff0000"}).fadeOut("slow");							
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
				
				function save_group_type() {	
					var div_name = "new_group_type_" + new_group_type_number;
					var new_group_type_div = document.getElementById(div_name);					
					var inputs = new_group_type_div.getElementsByTagName("input");
					var group_type_name = inputs[0].value;
					
					var url = "save_group_type.php?camp_id=" + camp_id + "&group_type_name=" + group_type_name;
								
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
							if (http.responseText != "0") 
								setTimeout("set_group_type_name('" + div_name + "', '" + http.responseText + "')", 250);
							else
								setTimeout("set_error_message('" + div_name + "', '<label style=\"color:red;\">Group Type Already Exists</label>')", 250);
						}
					}
					http.send(null);					
				}
				
				function set_group_type_name(div_name, innerHTML) {
					var info = innerHTML.split(":");
					var group_type_id = info[0];
					var group_type_name = info[1];
					
					var new_innerHTML = "<li id='li" + group_type_id + "'>";										
					new_innerHTML = new_innerHTML + "<span name='action' class='action'>";
					new_innerHTML = new_innerHTML + "<span name='remove' class='remove'><a title='Delete' href='#' onclick='delete_group_type(" + group_type_id + ");'>Delete</a></span>";
					new_innerHTML = new_innerHTML + "<span name='edit' class='edit'><a href='#' title='Edit'>Edit</a></span>";
					new_innerHTML = new_innerHTML + "</span>";
					new_innerHTML = new_innerHTML + "<span name='bullet' class='icon bullet'></span>";
					new_innerHTML = new_innerHTML + "<span name='label' class='label editable'>" + group_type_name+ "</span>";
					new_innerHTML = new_innerHTML + "</li>";
					
					document.getElementById(div_name).innerHTML = new_innerHTML;
				}
								
				function set_error_message(div_name, innerHTML) {
					var new_group_type_div = document.getElementById(div_name);													
					var spans = new_group_type_div.getElementsByTagName("span");
					for (cntr = 0; cntr < spans.length; cntr++) {
						var span_name = spans[cntr].getAttribute("name");
						if (span_name == "label") {
							spans[cntr].innerHTML = innerHTML;
						}
					}
				}
			</script>

			<script type="text/javascript">					
			</script>

			<div class="slider">
				<div class="col_title"><span>Getting Started</span><a class="slider_back">back</a></div>
				<div class="col_content">
                    <h1>Setup Camp Profile</h1>

                    <div class="module list_group_types" id="module-info">
                    	<h1>Setup Group Types</h1>
                        <div class="module_content">
                            <div class="list">
                                <ul>
									<? $query = mq("SELECT * FROM group_types WHERE camp_id=" . $camp_id); ?>
									<? while ($row = mysql_fetch_assoc($query)) : ?>
									<li id="li<?=$row['group_type_id'];?>">										
                                        <span class="action" name="action">
                                            <span class="remove" name="remove"><a title="Delete" href="#" onclick="delete_group_type(<?=$row['group_type_id'];?>);">Delete</a></span>
                                            <span class="edit" name="edit"><a title="Edit" href="#">Edit</a></span>
                                        </span>
                                        <span class="icon bullet" name="bullet"></span>
                                        <span class="label editable" name="label"><?=$row['group_type_name'];?></span>
                                    </li>									
									<? endwhile; ?>
									
                                    <li>
                                        <span class="icon add"></span>
                                        <span class="label">
											<a href="#" class="add_new_row">Add Group Type</a>
										</span>
                                    </li>
									
                                </ul>
                            </div>
                        </div>

                    </div>
					<br />
										
                    <div class="wizard_nav">
                        <p><a class="button rfloat" href="setup_divisions.php">Next</a></p>
                        <br class="clear" />
                    </div>
				</div>
			</div>

			
			