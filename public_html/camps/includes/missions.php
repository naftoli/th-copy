<? 	
include ("classes/camp_campaign.php");
include ("classes/camp_mission.php");
include ("get_camp_id.php");
$camp_id = get_camp_id();

$camp_campaigns = array();
$sql = "SELECT * FROM camp_campaigns WHERE camp_id=" . $camp_id . " AND active=1 AND group_task=0";
$query = mysql_query($sql);	
while ($row = mysql_fetch_assoc($query)) {
	$camp_campaign = new camp_campaign($row);
	$camp_campaign->get_missions();
	array_push($camp_campaigns, $camp_campaign);
}
?>

			<div class="slider">
			
				<div class="col_title">
					<span>Missions for <?=($group_task>0?"Groups":"Individuals")?></span>
				</div>
				
				<div class="col_content">
					
					<? for ($cno = 0; $cno < count($camp_campaigns); $cno++) : ?>
					
					<div id="module-info" class="module">
						<h1><?=$camp_campaigns[$cno]->campaign_name?></h1>
						
						<div class="module_content">
						
							<div class="list missions">
								<ul>
									<? for ($mno = 0; $mno < count($camp_campaigns[$cno]->camp_missions); $mno++) : ?>
									<li>
										<span class="action">
											<span class="tasks">
												<span>
													<a class="button overlay" title="Tasks" href="content.php?output=overlay_tasks&mission_name=<?=urlencode($camp_campaigns[$cno]->camp_missions[$mno]->mission_name);?>&camp_mission_id=<?=$camp_campaigns[$cno]->camp_missions[$mno]->camp_mission_id;?>&group_task=0">
														<span class="icon"></span>
														<?=$camp_campaigns[$cno]->camp_missions[$mno]->get_number_of_tasks();?> Tasks
													</a>
												</span>
											</span>																						
										</span>
									
										<span class="icon bullet"></span>
										<span class="label">
											<span class="label title">Mission </span>
											<?=$camp_campaigns[$cno]->camp_missions[$mno]->mission_name;?>
										</span>
										<div class="clear"></div>										
									</li>
									<? endfor; ?>
								</ul>
							</div>
							
						</div>
						
					</div>
					
					<? endfor; ?>
					
				</div> <!-- <div class="col_content"> -->
				
			</div> <!-- <div class="slider"> -->
