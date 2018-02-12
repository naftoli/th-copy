<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = $_GET['camp_id'];

?>

			<script src="scripts/jquery.jeditable.min.js"></script>
 			<script>
				$(function() {
				
				})
				
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

			<div class="slider">
				<div class="col_title"><span>Points</span><a class="slider_back">back</a></div>
				<div class="col_content">
                    <h1>Achievements</h1>

                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>Welcome to the Camp Marking System.</p>
							<p>Please select a campaign to mark.</p>
                        </div>
                    </div>
                        
                    <div class="lists" id="lists-campers">
                    
                        <label style="font-size:15px; font-weight:bold; color:#005A8C;"><?=T_('Campaigns');?></label>
								
						<br />
						<br />    
                
                        <div class="module_content">
                            <ul>
        						<? $campaign_query = mq("SELECT * FROM camp_campaigns WHERE camp_id=" . $camp_id); ?>
        						<? while ($campaign_row = mysql_fetch_assoc($campaign_query)) : ?>
        						<li>
        							<a href="mark_campaign.php?camp_id=<?=$camp_id;?>&campaign=<?=$campaign_row['campaign_id'];?">
        								<div class="image">
        									<img src="images/icon_award.png" width="32" height="32" alt="<?=$campaign_row['campaign_name'];?>" />
        								</div>
        								<div class="name">
        									<?=$campaign_row['campaign_name'];?>
        								</div>											
        							</a>
        						</li>
        						<? endwhile; ?>		
        					</ul>
                        </div>
                    </div>
				</div>
			</div>


