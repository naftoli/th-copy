<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');
?>

			<script src="scripts/jquery.jeditable.min.js"></script>
 			<script>
				$(function() {
					$('.edit a').live('click',function(){$(this).parents('li').find('.editable').click()});
					
					$('.editable').editable('http://www.example.com/save.php',{
						 indicator : '<img src="images/ajax-loader-sm.gif"/>',
						 submit:'<img src="images/bullet_disk.png"/>',
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
					
					$(".slider:last .remove a").click(function(event) {
						event.preventDefault();
						$(this).parents('li').css({backgroundColor: "#ff0000"}).fadeOut("slow");
					});
				})
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
                    <div class="module list_group_types" id="module-info">
                    	<h1>Setup Group Types</h1>
                        <div class="module_content">
                            <div class="list">
                                <ul>
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
                                    <li>
                                        <span class="icon add"></span>
                                        <span class="label"><a href="#" class="add_new_row">Add Group Type</a></span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
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
				</div>
			</div>
