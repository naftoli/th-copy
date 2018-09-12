<? 
$url = $_SERVER["REQUEST_URI"];

// redirect to the kiosk from login.php if the cookie is set and the $admin_auth is set in ?
if (isset($_COOKIE['kiosk_machine']) && isset($admin_auth)) 
{
	header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/kiosk.php'); 
}

$username = agr($_COOKIE, 'username_default'); 

if (!isset($login_query_string)) 
{
	$login_query_string = http_build_query($_GET, NULL, '&amp;');
	
	if (!is_null($login_query_string) && $login_query_string !== '') 
		$login_query_string = '?' . $login_query_string;
}
?>

<?php 
if (isset($admin_auth))  
{ 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<!--<meta http-equiv="X-UA-Compatible" content="chrome=1">-->
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>Hachayal Kiosk - Admin</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="/camps/styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="/camps/styles/styles.css" rel="stylesheet" type="text/css" />
		<!--[if lt IE 8]>
		<link href="/camps/styles/style_ie.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		<script src="/camps/scripts/jquery.tools.min.js"></script>
		<!--[if lt IE 9]>
		<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
		<![endif]-->
		<script>
			$(document).ready(function() {
				$("#contact_us").overlay({top: '40%', target: '#overlay', closeOnClick: true, mask: { color: '#000', loadSpeed: 200, opacity: 0.5 }});
				$('#username').focus();

				var curr_tab = 0 //$(".list_parent a").index($(".list_parent a[title=admin]"));;
				$(".list_first:not(.list_small,.user_list)").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide', initialIndex: curr_tab});
				$('#nav li:has(ul)').addClass('submenu');

				$('.list_parent.link a').click(function(){
					window.location = $(this).attr('href');
				});			
			 });
			$(window).load(function() {
				//document.docElement.requestFullscreen();
				correctHeight();
			});

			function correctHeight() {
				$('#nav').animate({height:$('#content').height()},1000);
			}
		</script>
		
		<script TYPE="text/javascript">
			function popup(mylink, windowname) {
				if (!window.focus){
					return true;
				}
				var href;
				if (typeof(mylink) == 'string'){
					href=mylink;
				}
				else {
					href = mylink.href ;
				}
				window.open(href, windowname, 'width=400,height=300,scrollbars=yes');
				return false;
			}
		</script>

		<style>
			#nav ul.list_first  li, .side_menu li  {
				background:url("/images/bg_gradientTrans.png") repeat-x scroll 0 25% #dadee6;
				border-top:1px solid #fff;
				border-bottom:1px solid #787e85;
			}
			#nav ul.list_first  li:hover, .side_menu li:hover  {
				background:url("/images/bg_gradientTrans.png") repeat-x scroll 0 25% #cfdbf3;
			}

			#nav ul.list_second ul, #nav .list_small .list_second   {
				background: #D5D8DE;
				position:absolute;
				margin-left:220px;
				z-index:1;
				width:250px;
				top:-1px;
				display:none;
				box-shadow:0px 0px 10px #666;	
				-moz-box-shadow:0px 0px 10px #666;	
				-webkit-box-shadow:0px 0px 10px #666;	
			}

			#nav ul.list_second ul li {
				background-color:#cbd4e6;
			}

			#nav ul.list_second li:hover ul   {
				display:block;
			}

			#nav li.submenu > a {
				background:url("/images/icon_control_right.png") no-repeat scroll 95% 50% transparent;
			}
			#nav ul.list_second  li a {
				line-height:1.6;
			}
			#nav ul.list_second > li, #nav ul.list_small > li {
				padding-left:30px;
				position:relative;
			}
			#nav ul.list_second:not(.list_small,.user_list) {
				display:none;
			}
		</style>
	</head>

	<body>
		
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
	<?
	if (isset($_GET['p']) and $_GET['p']) {
	?>
	<script type="text/javascript">
	alert('You have successfully created a parent account.\nPlease use your username and password that you setup, in order to login.');
	</script>
	<? } ?>
		
		<div id="wrapper">
		
			<div id="nav">
			
				<div class="col_title_bg"></div>
				
				<div class="col_title">Menu</div>
					<ul class="list_first">
						<li class="list_parent current"><a href="#"><img src="/images/icon_door_in.png" width="28" height="28" alt="Login" />Login</a></li>
						<ul></ul>
                        <!--
						<li class="list_parent">
						<a href="#" title="info"><div><span class="icon"><img height="28" width="28" alt="Info" src="images/icon_info.png"></span><?=T_('General Info')?></div></a>
						</li>
							<ul class="list_second">
								<li class="submenu">
									<a href="#"><?=T_('Rallies')?></a>

									<ul>
										<li><a href="rally_info.php"><?=T_('Rally Info')?></a></li>
										<li><a href="rally_pics.php"><?=T_('Promotion Pictures')?></a></li>
										<li><a href="rally_posuk.php"><?=T_('Posuk on Video')?></a></li>					
									</ul>
								</li>
						
								<li>
									<a href="cth_dates.php">CTH Dates 5772</a>
								</li>
						
								<li>
									<a href="faq.php">FAQ's</a>
								</li>
							</ul>
						-->
						<li>
						  <a href="/helpdesk" title="support"><div><span class="icon"><img height="28" width="28" alt="Support" src="/images/parentIcons/support icon.gif"></span><?=T_('Support')?></div></a>
						</li>
						
						<li>
						    <a href="https://www.mashpia.com/donate.php"><div><span class="icon"><img height="28" width="28" alt="Donate" src="/images/parentIcons/donate icon.gif"></span><?=T_('Donate')?></div></a>
						</li>
                        
					</ul>
				</div>
				
				<div id="content">
				
					<div class="col_title_bg"></div>
					
					<div class="slider_container">
					
						<div class="slider">
						
							<div class="col_title"><span>Welcome</span></div>
							
							<div class="col_content">

								<h1>Chayolei Tzivos Hashem School and Camp Login</h1> 
                                                  
								<div style="text-align: center;"> 								
									<noscript>
										<p style="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</p>
									</noscript> 
								</div> 
								
								<!--
								<div align="center">
								    Please Note: If you are having difficulty logging in, <br />
								    please click <b><a href="instuctions.html" onclick="return popup(this, 'Instuctions');">here</a></b>
								    for instructions.<br /><br />
								    <b>PLEASE NOTE: If you are having difficulty logging in with your regular username and password,<br />
								    please log in with your regular username but use the password "1234".<br />
								    Then update your profile including changing your password.<br />
								    Once you update your password, you will automatically be logged out. 
								    <br />Please login with your new password.<br /><br />
								</div>
								-->
								<div style="text-align: center; padding-bottom: 10px;">
									<a href="/mobile">Mobile Friendly Site</a><br />
								</div>
                                
								<? if (isset($admin_auth)) : ?>                            
								<div class="module" id="module-info">
								
									<h1>Login</h1>
									
									<div class="module_content">
									
										<div class="list form">
											
											<form action="" method="post" accept-charset="UTF-8" name="login"> 
												<input type="hidden" name="login_action" value="admin_login">
												
												<ul>
													<li>
														<div>
															<p><a href="#" id="forgot_password" name="forgot_password" onclick="show_table();">Forgot Username or Password</a></p>
															<!--
															<p>
																<a href="#" id="forgot_username" name="forgot_username" onclick="show_reminder();">Forgot Username or Password</a>
															</p>
															<div id="remind_username" style="display:none;">
																Please enter your email and you will be emailed your username and password.<br />
																<input type="text" name="username" id="rem_username" />
																<input type="submit" name="submit" value="submit" onclick="remind();" />
															</div>
															-->
														</div>
													</li>
													<li id="email_address_div" style="display:none;">
														<div>
															Please enter your email address and we will send you your username / password.<br />
															<input type="text" name="email_address" id="email_address" style="width:180px;"><br />
															<input type="button" value="Remind Me" class="submit" onclick="get_password();">
														</div>
													</li>
													
													<li>
														<span class="icon bullet"></span>
														<span class="label"><label for="username">Username</label></span>
														<span class="input"><input type="text" name="login_username" id="username" size=64 maxlength=64 value=""></span>
														<div class="clear"></div>
													</li>
													<li>
														<span class="icon bullet"></span>
														<span class="label"><label for="password">Password</label></span>
														<span class="input"><input type="password" name="login_password" id="password" size=64 maxlength=64 value=""></span>
														<div class="clear"></div>
													</li>
													<li>
														<input type="submit" value="Login" class="button"> 
														<input type="hidden" name="new_login">
														<input type="hidden" name="fromMobile" value="<?=isset($_GET['fromMobile']) ? 1 : 0;?>" >
														<div class="clear"></div>
													</li>
												</ul>
											</form> 
											
										</div>
									</div>
								</div>
								<? else : ?>
								<br /><br />
				
								<FORM action="" method="post" accept-charset="UTF-8" name="login">
									<LABEL for="user_code"><?=T_('Scan Rank Card')?>:</LABEL>
									<INPUT type="text" name="user_code" id="user_code" size=25 maxlength=20 autocomplete="off">
									<br /><br />
									<?= $login_message ?>
									<INPUT TYPE="hidden" NAME="new_login">
									<?= form_build_input(array_diff_key($_POST, array('login_username' => '', 'login_password' => '', 'user_code' => ''))) ?>				
								</FORM>
				
								<SCRIPT type="text/javascript">
									document.forms['login'].elements['<?=!isset($admin_auth) ? 'user_code' : (!$username ? 'login_username' : 'login_password') ?>'].focus();
									<?=!isset($admin_auth) ? 'setInterval("document.forms[\'login\'].elements[\'user_code\'].focus();", 1000);' : ''?>						
								</SCRIPT>
								<? endif; ?>

								<div class="module" id="module-info">
								
									<div class="module_content">
										<ol>
											<li>
												<script>
													
													function get_password() { 
														var function_name = "get_password";
														var parameters = [document.getElementById("email_address").value];
														if ( parameters == "" ) {
															alert("No email address was entered.\nPlease try again.");
															return;
														}
														var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
														$.getJSON(url, function(success) {
															if (success == 0) {
																alert("Email address does not exist. Please enter another one.");
															}
															else {
																document.getElementById("email_address_div").style.display = "none";
																alert("Your username and password were sent to " + document.getElementById("email_address").value);
															}
														});												
													}
													
										
													function show_table() {
														var e = document.getElementById("email_address_div");
														if (e.style.display == "none") 
															e.style.display = "block";
														else 
															e.style.display = "none";
													}
													
													function show_reminder() { 
														var e = document.getElementById("remind_username");
														if (e.style.display == "none")
															e.style.display = "block";
														else
															e.style.display = "none";
													}
													
													function remind() {
														var email = document.getElementById("rem_username").value;
														$.get('getUsername.php', {email : email}, function(data) {
															alert( data );
															alert("Your username / password was sent to " + email);
															//window.location = 'admin.php';															
														});
													}
																																							
												</script>
												
												<!--
												<p><a href="#" id="forgot_password" name="forgot_password" onclick="show_table();">Forgot Password</a></p>
												<p><a href="#" id="forgot_username" name="forgot_username" onclick="show_reminder();">Forgot Username</a></p>
												<div id="remind_username" style="display:none;">
													Please enter your email and you will be emailed your username.<br />
													<input type="text" name="username" id="rem_username" />
													<input type="submit" name="submit" value="submit" onclick="remind();" />
												</div>
												-->
												<p><a href="#" id="contact_us">Contact Us</a></p>
												<p><a href="statement.php">Enter Kiosk Mode</a></p>
												<p><a href="https://mashpia.com/registration.php">New Schools register here</a></p>
												<p><a href="register_parent.php">Create a Parent Account</a></p>
												<!--<p><a href="http://v2.mashpia.com">Teacher login here</a></p>-->
											</li>
										</ol>
									</div>
									
								</div> 
								
								<? require 'footer.php'; ?>
								
							</div>
							
						</div>
						
					</div>
					
				</div>
			
		</div>
						
			<div id="overlay" class="narrow">
			
				<div class="content">
				
					<div class="slider">
					
						<div class="col_title">
							<span>Contact Us</span>
						</div>

						<div class="col_content" style="min-height:0;">
							<div class="module" id="module-info">
								<div class="module_content">
									<p>For more information, please contact:<br>
									Mushkie Rosenblum<br>
									718-907-8884<br>
									<a href='ma&#105;lto&#58;CT%48&#64;T%7&#65;ivosHas&#104;em&#46;org'>CTH&#64;Tzi&#118;osH&#97;she&#109;&#46;o&#114;g</a></p>
								</div>
							</div>
						</div>
						
					</div>
					
				</div>
				
			</div>

	</body>

</html>

<?php 
} 
else 
{ 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
   
	<HTML DIR="LTR">
		<HEAD>
		<LINK href="../styles.css" rel="stylesheet" type="text/css">
		<TITLE>Login - Tzivos Hashem Management System</TITLE>
	</HEAD>

	<BODY class="kiosk">
	
		<DIV id="header">
		
			<DIV>
			</DIV>
				
		</DIV>

		<DIV STYLE="text-align: center;">
		
			<NOSCRIPT>
				<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
			</NOSCRIPT>
<!--
<FORM action="" method="post" accept-charset="UTF-8">
<DIV>
<INPUT type="submit" value="English">
<INPUT type="hidden" name="login_lang" value="en">
</DIV>
</FORM>
<FORM action="" method="post" accept-charset="UTF-8">
<DIV>
<INPUT type="submit" value="עברית">
<INPUT type="hidden" name="login_lang" value="he">
</DIV>
</FORM>
<FORM action="" method="post" accept-charset="UTF-8">
<DIV>
<INPUT type="submit" value="יידיש">
<INPUT type="hidden" name="login_lang" value="yi">
</DIV>
</FORM>
<BR>
-->

			<FORM action="" method="post" accept-charset="UTF-8" name="login">
			
				<? if ($url == "/ds/statement.php") : ?>
				<DIV id="body" class="dayschool">
				<? else : ?>
				<DIV id="body" class="login">
				<? endif; ?>
				
					<DIV>
					
						<INPUT type="text" name="user_code" id="user_code" title="<?=T_('Welcome! Please scan your rank card')?>" size=25 maxlength=20 autocomplete="off">
						<BR>
						<BR>
						<P>
						</P>
						
					</DIV>
					
				</DIV>
				
				<DIV>
					<INPUT TYPE="hidden" NAME="new_login">
				</DIV>
				
			</FORM>
			
			<SCRIPT type="text/javascript">
				document.forms['login'].elements['user_code'].focus();
				setInterval("document.forms['login'].elements['user_code'].focus();", 1000);
			</SCRIPT>
			
			<P style="text-align: center;">
<!--
  <P>
   <A HREF="register.php">Register for an account.</A>
  </P>
-->
			<DIV id="footer">&nbsp;</DIV>
			
		</DIV>
		
	</BODY>
	
</HTML>


<?php 
} 
?>
