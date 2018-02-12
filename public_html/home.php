 <?php
session_start();

include "/home/mashpia/public_html/CampMotivationalSystem/dev/application/php/database.php";

if ($_POST['login_username'] && $_POST['login_password'])
{
	$sql = "SELECT admin_id from admins where username = '" . $_POST['login_username'] . "' and password = '" . $_POST["login_password"] . "'";
    $result = mysql_query($sql) or die('Query failed 0');
	
    if($row = mysql_fetch_assoc($result)) 
	{
		// Set the Cookie
		$auth = hash_hmac('ripemd128', strtolower($_POST['login_username']).$_POST["login_password"], '53fdc95857aac68970159dd07e7c3782');
		setcookie('admin_id', $row['admin_id'], 0, '/');
		setcookie('admin_auth', $auth, 0, '/');
		$page = '/index.php';
		header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $page);
	}
	else
	{
		$message = 'Wrong username or password';
		setcookie('admin_id', '', time() - 86400, '/');
		setcookie('admin_auth', '', time() - 86400, '/');
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<!--<meta http-equiv="X-UA-Compatible" content="chrome=1">-->
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Hachayal Kiosk - Admin</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="http://mashpia.com/CampMotivationalSystem/dev/presentation/styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="http://mashpia.com/CampMotivationalSystem/dev/presentation/styles/styles.css" rel="stylesheet" type="text/css" />
		<link href="http://mashpia.com/CampMotivationalSystem/dev/presentation/styles/print.css" rel="stylesheet" type="text/css" media="print" />
		<!--[if lt IE 8]>
		<link href="http://mashpia.com/CampMotivationalSystem/dev/presentation/styles/style_ie.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		<?php //include("includes/jquery.php"); ?>
		<!--<script src="http://cdn.jquerytools.org/1.2.3/full/jquery.tools.min.js"></script>-->
		<script src="http://mashpia.com/CampMotivationalSystem/dev/presentation/scripts/jquery.tools.min.js"></script>
		<script src="http://mashpia.com/CampMotivationalSystem/dev/presentation/scripts/jquery.color.min.js"></script>
		<script src="http://mashpia.com/CampMotivationalSystem/dev/presentation/scripts/jquery.styleselect.js"></script>
		<!--[if lt IE 9]>
		<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
		<![endif]-->
		<script>
			
			function showLoader() {
				$('#content .col_title_bg').append('<span class="loader">LOADING...</span>');
			}
			function hideLoader() {
				$('.loader').fadeOut('fast',function() {$(this).remove()});
			}
			
			function slideReplace(id) {
				var toLoad = $(id).attr('href');
				showLoader();
				$('.loader').fadeIn('normal',loadContent());
				function loadContent() {
					$.get(toLoad,'',
						function(data){
							$('.slider_container').fadeOut('fast',function(){
								$(this).empty().css({'margin-left':0}).append(data).fadeIn('fast',function(){
									$('.slider_container').data('url',toLoad);
									initialize();
								});
							});
							/*initialize();*/
							hideLoader();
								
						});
				}
			}
			function slideRefresh() {
				var toLoad = $('.slider_container').data('url');
				showLoader();
				$('.loader').fadeIn('normal',loadContent());
				function loadContent() {
					$.get(toLoad,'',
						function(data){
							$('.slider_container').fadeOut('fast',function(){
								$('.slider:last').remove();
								$(this).append(data).fadeIn('fast',function(){
									$('.slider_container').data('url',toLoad);
									initialize();
								});
							});
							hideLoader();
						});
				}
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
							$('.slider_container .slider:last .col_title').append('<a class="slider_back"></a>');
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
				$(".slider:last .slider_back").click(function(event) {
					//event.preventDefault();
					slide_width = 773;
					$(".slider_container").animate({'margin-left':parseInt($(".slider_container").css('margin-left')) + slide_width + 'px'},500);
					$(this).parent().parent().fadeOut(function() {$(this).remove()});
				});
				/*$(".slider:last .lists a, .slider:last .wizard_nav a").live('click',function(event) {
					event.preventDefault();
					slideForward(this);
				});*/
				/*$(".list_edit a, a.overlay").overlay({top: '20%', target: '#overlay', api:true, closeOnClick: false, mask: { color: '#000', loadSpeed: 200, opacity: 0.4 },
					onBeforeLoad: function() {
						var wrap = this.getOverlay().find(".content");
						var self = this;
						showLoader();
						wrap.load(this.getTrigger().attr("href"),function() {
							hideLoader();
							$('.close', this).click(function(){self.close()});
						});
					}
				});*/
				$('.slider:last select.select').sSelect();

			}
			
			
			$(function() {
				/*$('.side_main > ul > li h1').click(function() {
					$(this).next().toggle('fast');
					return false;
				}).next().hide().first().show();*/

				$(".list_first").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide'});
				
				$("#nav .list_second a").click(function(event) {
						event.preventDefault();
						slideReplace(this);
				});
				
				$(".slider .lists a, .slider .wizard_nav a").live('click',function(event) {
					event.preventDefault();
					slideForward(this);
				});
				$('.list_edit a, a.overlay').live('click', function(e){ 
					overlayUrl = $(this).attr("name");
					e.preventDefault(); 
					overlayObject.load(); 
				});
				var overlayObject = $('.overlayTrigger').overlay({top: '20%', target: '#overlay', api:true, closeOnClick: false, mask: { color: '#000', loadSpeed: 200, opacity: 0.4 },
					onBeforeLoad: function() {
						var wrap = this.getOverlay().find(".content");
						var self = this;
						showLoader();
						//wrap.load(this.getTrigger().attr("href"),function() {
						wrap.load(overlayUrl,function() {
							hideLoader();
							$('.close', this).click(function(){self.close()});
						});
					}
				}); 
				
				initialize();

			});
			
		</script>
	</head>

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
							<a href="content.php?output=marking">
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
							<a href="content.php?output=member_marking">
								<img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Achievements
							</a>
						</li>
						<li>
							<a href="#">
								<img src="images/icon_add.png" width="22" height="22" alt="Add" /> Print Cards
							</a>
						</li>
						<li>
							<a href="#">
								<img src="images/icon_add.png" width="22" height="22" alt="Add" /> Create Cards
							</a>
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
						<li><a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Mission Sheets</a></li>
						<li><a href="#"><img src="images/icon_add.png" width="22" height="22" alt="Add" /> Print Cards</a></li>
					</ul>
					
					<li class="list_parent">
						<a href="#control"><img src="images/icon_control.png" width="28" height="28" alt="Settings" /> Control Panel</a>
					</li>
					
					<ul class="list_second">
						<li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Camp Profile</a>
						</li>
						<li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Competitions</a>
						</li>
						<li>
							<a href="content.php?output=grouptypes"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Groups</a>
						</li>
						<li>
							<a href="content.php?output=camper"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Campers</a>
						</li>
						<li>
							<a href="content.php?output=staff"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Staff</a>
						</li>
						<li>
							<a href="#"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Trips</a>
						</li>
						<li>
							<a href="content.php?output=store"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Store</a>
						</li>
						<li>
							<a href="content.php?output=gettingstarted_setup_group_types"><img src="images/icon_settings.png" width="22" height="22" alt="Settings" /> Getting Started</a>
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
								</div>
							</div>
							<?php if (!$_COOKIE['admin_id']) { ?>
	 						<div id="login">
                                                                <h1>Login</h1>
								<div>
									<form name="login_form" id="login_form" method="post" action="" />
									<?php if ($message) { echo $message . "<br />"; } ?>
									<table>
										<tr><td>Username</td><td><input type="text" name="login_username" id="username" size="64" maxlength="64" value="<?php echo $_POST['login_username']; ?>" /></td></tr>
										<tr><td>Password</td><td><input type="password" name="login_password" id="password" size="64" maxlength="64" /></td></tr>
										<td colspan="2" style="text-align: center;">
  											<INPUT type="submit" value="Login">
										</td>
									</table>
									</form>
								</div>
                                                        </div>
							<?php } else { ?>
								Welcome back <?php echo $_COOKIE['admin_username_default']; ?>&nbsp;<a href="logout.php">Logout</a><br /><br />
							<?php } ?>
	
							<div class="lists" id="lists-campers">
								<h1>Campers</h1>
								<div class="module_content">
									<ul>
										<li>
											<a href="content.php?output=bunks">
												<div class="image">
													<img src="images/icon_bunk.png" width="32" height="32" alt="Bunks" />
												</div>
												<div class="name">
													Bunks
												</div>
											</a>
										</li>
										<li>
											<a href="content.php?output=learning_classes">
												<div class="image"><img src="images/icon_learn.png" width="32" height="32" alt="Learning Classes" /></div>
												<div class="name">Learning Classes</div>
											</a>
										</li>
										<li>
											<a href="content.php?output=leagues">
												<div class="image"><img src="images/icon_sports.png" width="32" height="32" alt="Leagues" /></div>
												<div class="name">Leagues</div>
											</a>
										</li>
										<li>
											<a href="content.php?output=add_group_type">
												<div class="image"><img src="images/icon_plus.png" width="32" height="32" alt="Add" /></div>
												<div class="name">Add Group Type</div>
											</a>
										</li>
									</ul>
								</div>
							</div>

						</div> <!-- <div class="col_content"> -->
					
					</div> <!-- <div class="slider"> -->
				
				</div> <!-- <div class="slider_container"> -->
				
			</div> <!-- <div id="content"> -->
		
		</div> <!-- <div id="wrapper"> -->	
		
		<div id="overlay">
			<div class="content"></div>
		</div>
	</body>
	
</html>
