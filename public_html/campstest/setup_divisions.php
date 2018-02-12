<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = $_GET['camp_id'];

?>

			<script src="scripts/jquery.jeditable.min.js"></script>
 			<script>
 			    var camp_id = "<?=$camp_id;?>";
 			
				$(function() {
					
                    $('.edit a').live('click',function(){$(this).parents('li').find('.editable').click()});
					
					$('.bullet').click(function(event) {
				
				        $(this).parents('li').find('.editable').get(0).reset();
					});
					
                    $('.editable').editable( function(value, settings) 
                                             {
                                                if ( validateForm(value, this) == true )
                                                {
                                                    processUpdateDivision(value, this);
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
					
					$("a.add_new_row").click(function(event) {
					
						
				        // Check if there is already a parent html element
						var list_item_html = $(this).parents('li').prev().html();
						
						// If there is no parent, then we need to create a new element ourselves
						if (list_item_html == null)
						{
                            list_item_html = "<span class=\"action\">"+
                                             "<span class=\"remove\"><a href=\"#\" title=\"Delete\">Delete</a></span>"+
                                             "<span class=\"edit\"><a href=\"#\" title=\"Edit\">Edit</a></span>"+
                                             "</span>"+
                                             "<span class=\"icon bullet\"></span>"+
                                             "<span class=\"label editable\"></span>";
                        }
						
                        html = '<li>' + list_item_html + '</li>';
                        $(this).parents('li').before(html);
						$(this).parents('li').prev().find('.editable').html("").
                            editable( function(value, settings) 
                                             { 
                                                if ( validateForm(value, this) == true )
                                                {
                                                    processSaveNewDivision(value, this);
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
						
						//$(this).parents('li').prev().css({'background':'green'}).animate({'background':'none'},500).css({'background':'none'});
						$(this).parents('li').prev().find('.editable').click();
						$(this).parents('li').prev().find('.bullet').click(function(event) {
					
				            $(this).parents('li').find('.editable').get(0).reset();			        
					    });
		    
					});
					
					$(".slider:last .remove a").click(function(event) {
						//event.preventDefault();
                        //$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("slow");
					});
					
				})
				
				function validateForm(value, calling_element)
				{
				    var errorMessage = "";
				    
				    // If the field has a value, then check for
				    // redudant entries
                    if (value != "") {
                    
                        var elementArr = $(calling_element).parents('ul').find('.editable');
                        
                        // In order to validate the fields we need to assemble the list of strings
                        // in each label plus the one in value for the current editable field (i.e 
                        // the test value has not been applied to the DOM element so we have to put it 
                        // in manually)
                        
                        // Create and array of innerHTML strings for each label                        
                        var innerHTMLArr = [];
                        for (var elemArrIndex = 0; elemArrIndex < elementArr.length; elemArrIndex += 1) {
                            
                            innerHTMLArr[elemArrIndex] = elementArr[elemArrIndex].innerHTML;
                        }
                        
                        // Add the new value from the edit box that has not yet been applied
                        // the editable field
                        innerHTMLArr[elementArr.length] = value;
                            
                        // Now sort innerHTMLArr   
                        sortedArr = innerHTMLArr.sort();
                        for (var sortedArrIndex = 0; sortedArrIndex < sortedArr.length - 1; sortedArrIndex += 1) {
                        
                            // If the current element is the same as the next element,
                            // then we know there are redundant fields
                            //alert(sortedArr[sortedArrIndex + 1]);
                            //alert(sortedArr[sortedArrIndex]);
                            
                            if (sortedArr[sortedArrIndex + 1] == sortedArr[sortedArrIndex]) {
                                    errorMessage = "Can't create two groups with the same name!!!\n" +
                                            "Please enter a different name";
                            }
                        }                    
                    }
                    else {
                        errorMessage = "Please enter a name.";
                    }
                    
                    // Display error message, if any
                    if (errorMessage != "") {
                        alert(errorMessage);
                        return false;
                    }
                    else {
                        return true;
                    }
                }
                
				function processUpdateDivision(value, calling_element)
				{
                    var divisionID = $(calling_element).parents('li').get(0).id;
                    
                    // Get the editable elements
                    var editableElement = $(calling_element).parents('li').find('.editable').get(0);
                    
                    // Udpate the division type
                    updateDivision(divisionID, value, calling_element);
				}
				
				
				function processSaveNewDivision(value, calling_element)
				{
				    var groupTypeId = $(calling_element).parents('div').find('h1').get(0).id;
                    
                    saveNewDivision(value, groupTypeId, calling_element);
				}
				
				function deleteDivision(divisionId) {
				
				    var id_name = "#"+ divisionId;
					var url = "delete_division.php?division_id=" + divisionId;
											
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
							
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
				
				function updateDivision(divisionId, divisionName, editableElement) {

                    var url = "update_division.php?divisionId=" +
                                divisionId +
                                "&divisionName=" +
                                divisionName;
								
					var http = getHTTPObject();
					http.open("GET", url, true);
					
		            http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
                            if (http.responseText != "") {
							
                                editableElement.reset();
                            }
                       }     
					}
					http.send(null);
                }				
				function saveNewDivision(divisionName, groupTypeId, editableElement) {
					
					var url = "save_division.php?&groupTypeId=" +
                               groupTypeId +
                               "&divisionName=" +
                               divisionName;
                               			
					var http = getHTTPObject();
					http.open("GET", url, true);
					
					http.onreadystatechange = function() {
						if (http.readyState == 4 && http.status == 200) {
						
							editableElement.reset();
                        }
					}
					http.send(null);
       					
				}
			</script>

			<div class="slider">
				<div class="col_title"><span>Getting Started</span><a class="slider_back">back</a></div>
				<div class="col_content">
                    <h1>Setup Camp Profile</h1>

                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>This guide will walk you through all the necessary steps to get you up and running in no time.</p>
                        	<p>To minimize setup time many fields have been pre-filled or selected.</p>
                        	<p>You can always edit these options later in the control panel.</p>
                        </div>
                    </div>
                    
                    <div class="module list_divisions" id="module-info">
                    	<h1>Setup Divisions</h1>
                        <div class="module_content">
                            <div class="list">
                            </div>
                        </div>
                    </div>
                    
                    <? $group_type_query = mq("SELECT * FROM group_types WHERE camp_id=".$camp_id); ?>
					<? while ($group_type_row = mysql_fetch_assoc($group_type_query)) : ?>
                    <div class="module list_group_types" id="module-info">
                    	<h1 id="<?=$group_type_row['group_type_id'];?>"><?=$group_type_row['group_type_name'];?></h1>
                        <div class="module_content">
                            <div class="list">
                               <ul>
                                    <? $division_query = mq("SELECT * FROM divisions WHERE group_type_id=".$group_type_row['group_type_id']); ?>
    								<? while ($division_row = mysql_fetch_assoc($division_query)) : ?>
    								<li id="<?=$division_row['division_id'];?>">	
    								    <span class="action" name="action">
                                            <span class="remove" name="remove"><a title="Delete" href="#" onclick="deleteDivision(<?=$division_row['division_id'];?>, this);"><?=T_("Delete")?></a></span>
                                            <span class="edit" name="edit"><a title="Edit" href="#"><?=T_("Edit")?></a></span>
                                        </span>
                                        <span class="icon bullet" name="bullet"></span>
                                        <span class="label editable" name="label">
                                            <?=$division_row['division_name'];?>
                                        </span>
                                    </li>
                                    <? endwhile; ?>
                                    <li>
                                        <span class="icon add"></span>
                                        <span class="label">
											<a href="#" class="add_new_row">Add Division</a>
										</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <? endwhile; ?>
                    
                    <!--<div class="wizard_nav">
                        <p><a class="button rfloat" href="#">Next</a></p>
                        <br class="clear" />
                    </div>-->
				</div>
			</div>
