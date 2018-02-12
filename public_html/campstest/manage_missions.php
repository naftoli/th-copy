<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id');
?>
			<!--<link href="styles/new_styles.css" rel="stylesheet" type="text/css" />-->
			
			<script src="scripts/jquery.jeditable.min.js"></script>
			<script type="text/javascript" src="jquery.form.js"></script> 
				
			<script>
				var camp_id = "<?=$camp_id;?>";

				$(document).ready(function() {
				
					function showLoader() {
						$('#content .col_title_bg').append('<span class="loader">LOADING...</span>');
					}
					
					function hideLoader() {
						$('.loader').fadeOut('fast',function() {$(this).remove()});
					}

					$(".list .groups a, .list .tasks a").overlay({top: '20%', target: '#overlay', api:true, closeOnClick: false, close:'.close', mask: { color: '#fff', loadSpeed: 200, opacity: 0.5 },
						onBeforeLoad: function() {
								var wrap = this.getOverlay().find(".content");
								var self = this;
								showLoader();
								wrap.load(this.getTrigger().attr("href"),function() {
									hideLoader();
									$('.close', this).click(function(){self.close()});
							});
						}
					});
					
				});
			</script>

			<script type="text/javascript">	
			</script>

			<div class="slider">
			
				<div class="col_title">
					<span><?=T_("Manage Missions");?></span><a class="slider_back">back</a>
				</div>
				
				<div class="col_content">
				
                    <h1><?=T_("Setup Missions");?></h1>

					<div id="module-info" class="module">
                        <div class="module_content">
                        	<p><?=T_("In this step you will setup missions.");?></p>
                        	<p><?=T_("Please choose a camp type from the drop down to load up a selection of appropriate missions.");?></p>
                        	<p><?=T_("You will then be able to select certain missions and assign them to certain groups.");?></p>
                        </div>
                    </div> <!-- <div id="module-info" class="module"> -->				
							
					<div id="module-info" class="module">
                    	<h1><?=T_("Manage Missions");?></h1>
						
                        <div class="module_content">
						
                            <div class="list missions">
                                <ul>

									<? $query = mq("SELECT * FROM camp_campaigns AS cc JOIN camp_missions AS cm USING (camp_campaign_id) WHERE cc.camp_id=" . $camp_id); ?>
									<? while ($row = mysql_fetch_assoc($query)) : ?>
									
                                    <li>
                                        <span class="action">
                                            <span class="groups">
                                                <span>
													<a class="button" title="Groups" href="content.php?output=groupsoverlay">
														<span class="icon"></span>
														<?=T_("Entire Camp");?>
													</a>
												</span>
                                            </span>
                                            <span class="tasks">
                                                <span>
													<a class="button" title="Tasks" href="manage_tasks.php?camp_id=<?=$camp_id;?>&camp_mission_id=<?=$row['mission_id'];?>?output=tasksoverlay">
														<span class="icon"></span><?=T_("Tasks");?>
													</a>
												</span>
                                            </span>
                                        </span>
                                        <span class="icon bullet">
										</span>
										<span class="label">
											<span class="label title"><?=T_("Campaign");?> </span>
											<?=$row['campaign_name'];?>
										</span>
                                        <span class="label">
											<span class="label title">
												<?=T_("Mission");?> 
											</span>
											<?=$row['mission_name'];?>
										</span>
                                        <div class="clear"></div>
                                    </li>
									
									<? endwhile; ?>
									
                                </ul>
                            </div>
							
                        </div> <!-- <div class="module_content"> -->
						
                    </div> <!-- <div id="module-info" class="module"> -->
					
				</div> <!-- <div class="col_content"> -->
				
			</div> <!-- <div class="slider"> -->