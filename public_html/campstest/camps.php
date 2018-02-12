<?php
$admin_auth = array('camp');
require('../header.php');

// ***** Determine if the user is a camp director or a super user ***** //
if ($admin_user['auth'] == "super")
	$user_type = "super";
else
	$user_type = "camp";
// ***** Determine if the user is a camp director or a super user ***** //

if ($user_type == "camp") 
	$camp_id = $admin_user['auths']['camp'][0]; 
else {
	$camp_id = gri('camp_id', -1);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Camps'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<link href="styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="styles/styles.css" rel="stylesheet" type="text/css" />
		<link href="styles/print.css" rel="stylesheet" type="text/css" media="print" />

		<script src="http://cdn.jquerytools.org/1.2.2/jquery.tools.min.js"></script>
		<script type="text/javascript" src="scripts/jquery.checkbox.min.js"></script>

		<link href="styles/jquery.checkbox.css" rel="stylesheet" type="text/css" />
		<LINK href="styles/card_printer.css" rel="stylesheet" type="text/css">
		
		<script>
        $(function() {
				$(".list_first").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide', history: true});
				
				$("#nav .list_second a").click(function(event) {
						event.preventDefault();
						slideForward(this);
				});
				
				function showLoader() {
					$('#content .col_title_bg').append('<span class="loader">LOADING...</span>');
				}
				function hideLoader() {
					$('.loader').fadeOut('fast',function() {$(this).remove()});
				}
				
				function slideForward(id) {
					currentTitle = $('.slider:last .col_title span').html();
					//currentTitle = $(this).parents('.slider').children('.col_title').html();

					var toLoad = $(id).attr('href');
					showLoader();
					$('.loader').fadeIn('normal',loadContent());
					//addressBar = $(this).attr('href');
					function loadContent() {
						$.get(toLoad,'',
							function(data){
								$('.slider_container').append(data);
								$('.slider_container .slider:last .col_title a').html(currentTitle);
								showNewSlide();
                            });
					}
					function showNewSlide() {
						hideLoader();
						initialize();
						slide_width = 773;
						$(".slider_container").animate({'margin-left':parseInt($(".slider_container").css('margin-left')) - slide_width + 'px'},500, hideLoader());
						//window.location.hash = $(this).attr('href').substr(0,$(this).attr('href').length-5);
						//window.location.hash = addressBar;
					}
				}
				function initialize() {
				
					$(".slider:last a.dismiss").click(function(event) {
						event.preventDefault();
						$(this).parent().css({backgroundColor: "#ff0000"}).fadeOut("slow");
					});
					$(".slider:last .lists a, .slider:last .wizard_nav a").click(function(event) {
						event.preventDefault();
						slideForward(this);
					});
					$(".slider:last .slider_back").click(function(event) {
						//event.preventDefault();
						slide_width = 773;
						$(".slider_container").animate({'margin-left':parseInt($(".slider_container").css('margin-left')) + slide_width + 'px'},500);
						$(this).parent().parent().fadeOut(function() {$(this).remove()});
					});
					$(".list_edit a").overlay({top: '20%', target: '#overlay', api:true, closeOnClick: false, close:'.close', mask: { color: '#fff', loadSpeed: 200, opacity: 0.5 },
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
					$('input[type=checkbox]').checkbox();
				}
				initialize();
				
			});
            </script>
		
	</HEAD>

	<body>
	
		<div id="wrapper">
		
			<div id="nav">
				
				<div class="col_title_bg">
				</div>
				
				<div class="col_title">
					Menu
				</div>
				
				<ul class="list_first">
					<li class="list_parent">
						<a href="#dashboard">
							<img src="images/icon_dashboard.png" width="22" height="22" alt="Dashboard" /> Dashboard
						</a>
					</li>

                    <ul class="list_second">
                        <li>
							<a href="mark_campaign.php?camp_id=<?=$camp_id;?>">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> My Profile
							</a>
						</li>
                    </ul>
					
					<li class="list_parent">
						<a href="#points">
							<img src="images/icon_points.png" width="28" height="28" alt="Points" /> Points
						</a>
					</li>
					
                    <ul class="list_second">
                        <li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Achievements</a>
						</li>

                        <li>
							<a href="#"><img src="images/icon_add.png" width="22" height="22" alt="Add" /> Print Cards</a>
						</li>
						
                        <li>
							<a href="#"><img src="images/icon_add.png" width="22" height="22" alt="Add" /> Create Cards</a>
						</li>
                    </ul>
					
					<li class="list_parent">
						<a href="#awards"><img src="images/icon_award.png" width="28" height="28" alt="Awards" /> Awards</a>
					</li>
										
                    <ul class="list_second">
					</ul>

					<li class="list_parent">
						<a href="#print"><img src="images/icon_print.png" width="28" height="28" alt="Print Center" /> Print Center</a>
					</li>
					
                    <ul class="list_second">
                        <li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Print Mission Sheets</a>
						</li>
                        <li>
							<a href="print_cards.php?camp_id=<?=$camp_id;?>"><img src="images/icon_add.png" width="22" height="22" alt="Add" /> Print Cards</a>
						</li>
                    </ul>
					
					<li class="list_parent">
						<a href="#control"><img src="images/icon_control.png" width="28" height="28" alt="Settings" /> Control Panel</a>
					</li>
					
                    <ul class="list_second">
                        <li>
							<a href="install_campaigns.php?camp_id=<?=$camp_id;?>">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" />
								<?=T_("Install Campaigns");?>
							</a>
						</li>
						
                        <li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /><?=T_("Competitions");?></a>
						</li>

                        <li>
							<a href="manage_group_types.php?camp_id=<?=$camp_id;?>">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" />
								<?=T_("Groups Types");?>
							</a>
						</li>
						
                        <li>
							<a href="manage_divisions.php?camp_id=<?=$camp_id;?>">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" />
								<?=T_("Divisions");?>
							</a>
						</li>						
						
                        <li>
							<a href="manage_groups.php?camp_id=<?=$camp_id;?>">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" />
								<?=T_("Groups");?>
							</a>
						</li>												
						
                        <li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /><?=T_("Campers");?></a>
						</li>
												
                        <li>
							<a href="manage_missions.php?camp_id=<?=$camp_id;?>">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" />
								<?=T_("Manage Missions");?>
							</a>
						</li>
						
                        <li>
							<a href="getting_started_three.php?camp_id=4">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" />
								<?=T_("Manage Staff");?>
							</a>
						</li>
						
                        <li>
							<a href="getting_started_two.php?camp_id=4"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Manage Trips</a>
						</li>
                        <li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Manage Store</a>
						</li>
                        <li>
							<a href="getting_started.php?camp_id=<?=$camp_id;?>"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Getting Started</a>
						</li>
					</ul>

					<li class="list_parent">
						<a href="#shop"><img src="images/icon_shop.png" width="28" height="28" alt="Shop TH" /> Shop TH</a>
					</li>
                    
					<ul class="list_second">
					</ul>
					
				</ul> <!-- <ul class="list_first"> -->
				
			</div> <!-- <div id="nav"> -->
			
			<div id="content">
			
				<div class="col_title_bg">
				</div>
				
				<div class="slider_container">

					<div class="slider">
					
						<div class="col_title">
							<span>Dashboard</span>
						</div>
						
						<div class="col_content">
						
							<p>Welcome to the Hachayol Admin Dashboard.</p>
							<p>Please select an option to begin.</p>
							
							<div class="module" id="module-alerts">
								<h1>Alerts</h1>

								<div class="module_content">
									<ul>
										<li>
											<a class="dismiss" href="#" title="Dismiss Alert">x</a>
											<span class="date">May 25</span>
											PLEASE NOTE: This page is best viewed in Firefox or Chrome.
										</li>
										<li>
											<a class="dismiss" href="#" title="Dismiss Alert">x</a>
											<span class="date">May 25</span>
											<a href="#">5 Staff Members</a> do not have access privileges.
										</li>
										<li>
											<a class="dismiss" href="#" title="Dismiss Alert">x</a>
											<span class="date">May 25</span>
											<a href="#">4 Campers</a> have not been placed in bunks.
										</li>
									</ul>
								</div> <!-- <div class="module_content"> -->
								
							</div> <!-- <div class="module" id="module-alerts"> -->
							
							<div class="lists" id="lists-campers">
							
								<label style="font-size:15px; font-weight:bold; color:#005A8C;"><?=T_('Group Types');?></label>
								
								<br />
								<br />
								
								<div class="module_content">
									<ul>
										<? $query = mq("SELECT * FROM group_types WHERE camp_id=" . $camp_id); ?>
										<? while ($row = mysql_fetch_assoc($query)) : ?>
										<li>
											<a href="groups.php?group_type_id=<?=$row['group_type_id'];?>&group_type_name=<?=$row['group_type_name'];?>">
												<div class="image">
													<img src="images/<?=$row['group_type_name'];?>.png" width="32" height="32" alt="<?=$row['group_type_name'];?>" />
												</div>
													
												<div class="name">
													<?=$row['group_type_name'];?>
												</div>											
											</a>
										</li>
										<? endwhile; ?>
										
										<li>
											<a href="groups.php?group_type_id=<?=$row['group_type_id'];?>&group_type_name=<?=$row['group_type_name'];?>">
												<div class="image">
													<img src="images/icon_plus.png" width="32" height="32" alt="<?=T_('Add');?>" />
												</div>
													
												<div class="name">
													<?=T_('Add Group Type');?>
												</div>											
											</a>
										</li>
										
									</ul>
								</div> <!-- <div class="module_content"> -->
								
							</div> <!-- <div class="lists" id="lists-campers"> -->
							
						</div> <!-- <div class="col_content"> -->
						
					</div> <!-- <div class="slider"> -->
					
				</div> <!-- <div class="slider_container"> -->
				
			</div> <!-- <div id="content"> -->


		</div> <!-- <div id="wrapper"> -->

		<div id="overlay">
			<div class="content"></div>
		</div>
		
	</body>
	
</HTML>
