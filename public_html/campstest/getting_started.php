<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');
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
                                                    processUpdateGroupType(value, this);
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
                                                    processSaveNewGroupType(value, this);
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
                
				
				function processUpdateGroupType(value, calling_element)
				{
				    // Get the current group type id associated
                    // with the element so that we can
                    // associate it properly
                    var group_type_id = $(calling_element).parents('li').get(0).id;
                    var editableElement = $(calling_element).parents('li').find('.editable').get(0);
                    
                    update_group_type(value, group_type_id, editableElement);
				}
				
				
				function processSaveNewGroupType(value, calling_element)
				{
				    // Get the current group type id associated
                    // with the element so that we can
                    // associate it properly
                    var group_type_id = $(calling_element).parents('li').get(0).id;
                    var editableElement = $(calling_element).parents('li').find('.editable').get(0);
                    
                    save_new_group_type(value, group_type_id, editableElement);
				}
				
				function delete_group_type(group_type_id) {
					
                    var url = "delete_group_type.php?group_type_id=" + group_type_id;
					var id_name = "#" + group_type_id;
                    			
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
				
				function update_group_type(updated_group_type_name, group_type_id, editableElement) {

                    var url = "update_group_type.php?camp_id=" + 
                                camp_id + 
                                "&group_type_name=" +
                                updated_group_type_name + 
                                "&group_type_id=" +
                                group_type_id;
								
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
                				
				function save_new_group_type(new_group_type_name, group_type_id, editableElement) {
					
					var url = "save_group_type_june9.php?camp_id=" + 
                              camp_id + 
                              "&group_type_name=" + 
                              new_group_type_name;
								
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
                    <div class="module list_group_types" id="module-info">
                    	<h1>Setup Group Types</h1>
                        <div class="module_content">
                            <div class="list">
                                <ul>
                                    <? $query = mq("SELECT * FROM group_types WHERE camp_id=" . $camp_id); ?>
									<? while ($row = mysql_fetch_assoc($query)) : ?>
                                    <li id="<?=$row['group_type_id'];?>">										
                                        <span class="action" name="action">
                                            <span class="remove" name="remove"><a title="Delete" href="#" onclick="delete_group_type(<?=$row['group_type_id'];?>);"><?=T_("Delete")?></a></span>
                                            <span class="edit" name="edit"><a title="Edit" href="#"><?=T_("Edit")?></a></span>
                                        </span>
                                        <span class="icon bullet" name="bullet"></span>
                                        <span class="label editable" name="label"><?=$row['group_type_name'];?></span>
                                    </li>									
									<? endwhile; ?>
                                    <li>
                                        <span class="icon add"></span>
                                        <span class="label"><a href="#" class="add_new_row">Add Group Type</a></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!---->
                    <div class="wizard_nav">
                        <p><a class="button rfloat" href="setup_divisions.php?camp_id=<?=$camp_id;?>">Next</a></p>
                        <br class="clear" />
                    </div>
				</div>
			</div>


<!--

$('.editable').editable('http://www.example.com/save.php',{
						 indicator : '<img src="images/ajax-loader-sm.gif"/>',
						 submit: '<img onclick="save_group_type();" src="images/bullet_disk.png"/>',
						 onblur:'ignore',
						 width:'143',
						 height:'14'
                         });
					
$('.editable')..editable('http://www.example.com/save.php', {
							             indicator : '<img src="images/ajax-loader-sm.gif"/>',
							             submit:'<img onclick="save_group_type();" src="images/bullet_disk.png"/>',
							             onblur:'ignore',
							             width:'143',
							             height:'14'
						            });
					
$("a.add_new_row").click(function(event) {
						//event.preventDefault();
						//var html = '<li><span class="action"><span class="save"><a href="#" title="Save">Save</a></span></span><span class="icon bullet"></span><span class="label"><input type="text" /></span></li>';
						html = '<li>' + $(this).parents('li').prev().html() + '</li>';
						$(this).parents('li').before(html);
						$(this).parents('li').prev().find('.editable').html('').editable('http://www.example.com/save.php',{
							 indicator : '<img src="images/ajax-loader-sm.gif"/>',
							 submit:'<img src="images/bullet_disk.png"/>',
							 onblur:'ignore',
							 width:'143',
							 height:'14'
						});
						//$(this).parents('li').prev().css({'background':'green'}).animate({'background':'none'},500).css({'background':'none'});
						
					});


$(this).parents('li').prev().find('.editable').html("").editable('http://www.example.com/save.php',{
	 indicator : '<img src="images/ajax-loader-sm.gif"/>',
	 submit:'<img src="images/bullet_disk.png"/>',
	 onblur:'ignore',
	 width:'143',
	 height:'14'
});


$(this).parents('li').prev().find('.editable').html('').editable('http://www.example.com/save.php',{
	 indicator : '<img src="images/ajax-loader-sm.gif"/>',
	 submit:'<img onclick="save_group_type();" src="images/bullet_disk.png"/>',
	 onblur:'ignore',
	 width:'143',
	 height:'14'
});


<div class="module list_sessions" id="module-info">
	<h1>Setup Sessions</h1>
    <div class="module_content">
        <div class="list">
            <ul>
                <li>
                    <span class="action">
                        <span class="remove"><a href="#" title="Delete">Delete</a></span>

                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label editable">First Month</span>
                    <span class="label small editable">June 25 - July 25</span>
                </li>
                <li>

                    <span class="action">
                        <span class="remove"><a href="#" title="Delete">Delete</a></span>
                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label editable">Second Month</span>
                    <span class="label small editable">July 25 - August 25</span>

                </li>
                <li>
                    <span class="icon add"></span>
                    <span class="label"><a href="#" class="add_new_row">Add Session</a></span>
                </li>
            </ul>
        </div>
    </div>
</div>

<li>
    <span class="action">

        <span class="remove"><a href="#" title="Delete">Delete</a></span>
        <span class="edit"><a href="#" title="Edit">Edit</a></span>
    </span>
    <span class="icon bullet"></span>
    <span class="label editable">Bunks</span>
</li>
<li>

    <span class="action">
        <span class="remove"><a href="#" title="Delete">Delete</a></span>
        <span class="edit"><a href="#" title="Edit">Edit</a></span>
    </span>
    <span class="icon bullet"></span>
    <span class="label editable">Learning Classes</span>
</li>

<li>
    <span class="action">
        <span class="remove"><a href="#" title="Delete">Delete</a></span>
        <span class="edit"><a href="#" title="Edit">Edit</a></span>
    </span>
    <span class="icon bullet"></span>
    <span class="label editable">Leagues</span>

</li>
<li>
    <span class="action">
        <span class="remove"><a href="#" title="Delete">Delete</a></span>
        <span class="edit"><a href="#" title="Edit">Edit</a></span>
    </span>
    <span class="icon bullet"></span>
    <span class="label editable">Bog War</span>

</li>
<li>
    <span class="action">
        <span class="remove"><a href="#" title="Delete">Delete</a></span>
        <span class="edit"><a href="#" title="Edit">Edit</a></span>
    </span>
    <span class="icon bullet"></span>
    <span class="label editable">Color War</span>
</li>

<div class="module list_divisions" id="module-info">
	<h1>Setup Divisions</h1>
    <div class="module_content">
        <div class="list">
            <ul>
                <li>
                    <span class="action">

                        <span class="remove"><a href="#" title="Delete">Delete</a></span>
                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label">Junior Division</span>
                </li>
                <li>

                    <span class="action">
                        <span class="remove"><a href="#" title="Delete">Delete</a></span>
                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label">Main Camp</span>
                </li>

                <li>
                    <span class="action">
                        <span class="remove"><a href="#" title="Delete">Delete</a></span>
                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label">Section C</span>

                </li>
                <li>
                    <span class="action">
                        <span class="remove"><a href="#" title="Delete">Delete</a></span>
                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label">Bar Mitzvah Division</span>

                </li>
                <li>
                    <span class="action">
                        <span class="remove"><a href="#" title="Delete">Delete</a></span>
                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label">Masmidim</span>

                </li>
                <li>
                    <span class="action">
                        <span class="remove"><a href="#" title="Delete">Delete</a></span>
                        <span class="edit"><a href="#" title="Edit">Edit</a></span>
                    </span>
                    <span class="icon bullet"></span>
                    <span class="label">Yeshivas Kayitz</span>

                </li>
                <li>
                    <span class="icon add"></span>
                    <span class="label"><a href="#" class="add_new_row">Add Division</a></span>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="wizard_nav">
    <p><a class="button rfloat" href="content.php?output=gettingstarted2">Next</a></p>
    <br class="clear" />
</div>
					
-->