<? 
$url = $_SERVER["REQUEST_URI"];

if (isset($_POST['action']))
{
	include("db.php");
	
	$sql = "SELECT * FROM admins WHERE username='" . $_POST['username'] . "' AND password='" . $_POST['password'] . "'";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	if ($row)
	{
		$auth = hash_hmac('ripemd128', strtolower($row['username']) . $row['password'], '53fdc95857aac68970159dd07e7c3782');
		
		session_start();
		$_SESSION['admin_id'] = $row['admin_id'];
		$_SESSION['admin_auth'] = $auth;
		header("Location:http://mashpia.com/admin.php"); 
	}
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		
		<title>Hachayal Kiosk - Admin</title>
		
		<link href="camps/styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="camps/styles/styles.css" rel="stylesheet" type="text/css" />
		
		<script src="camps/scripts/jquery.tools.min.js">
		</script>
		
		<script>
			$(function() {
				$("#contact_us").overlay({top: '40%', target: '#overlay', closeOnClick: true, mask: { color: '#000', loadSpeed: 200, opacity: 0.5 }});
				$('#username').focus();
			});
		</script>
		<!--Copyright Ariel Shkedi 2007-2010-->
	</head>

	<body>
		
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
				
		<div id="wrapper">
		
			<div id="nav">
			
				<div class="col_title_bg"></div>
				
				<div class="col_title">Menu</div>
					<ul class="list_first">
						<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Login</a></li>
					</ul>
				</div>
				
				<div id="content">
				
					<div class="col_title_bg"></div>
					
					<div class="slider_container">
					
						<div class="slider">
						
							<div class="col_title"><span>Welcome</span></div>
							
							<div class="col_content">

								<p>This website will only function properly using Firefox 3.0 and higher. Click <a href="http://www.getfirefox.com/">here</a> to download Firefox.</p> 
								<h1>Chayolei Tzivos Hashem School and Camp Login</h1> 
                                                  
								<div style="text-align: center;"> 								
									<noscript>
										<p style="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</p>
									</noscript> 
								</div> 
 
								<div class="module" id="module-info">
								
									<h1>Login</h1>
									
									<div class="module_content">
									
										<div class="list form">
										
											<form action="admin_login.php" method="post" accept-charset="UTF-8" name="login"> 
												<input type="hidden" name="action" value="login">
												
												<ul>
													<li>
														<span class="icon bullet"></span>
														<span class="label"><label for="username">Username</label></span>
														<span class="input"><input type="text" name="username" id="username" size=64 maxlength=64 value=""></span>
														<div class="clear"></div>
													</li>
													<li>
														<span class="icon bullet"></span>
														<span class="label"><label for="password">Password</label></span>
														<span class="input"><input type="password" name="password" id="password" size=64 maxlength=64 value=""></span>
														<div class="clear"></div>
													</li>
													<li>
														<input type="submit" value="Login" class="button"> 
														<input type="hidden" name="new_login"> 
														<div class="clear"></div>
													</li>
												</ul>
											</form> 
											
										</div>
										
									</div>
									
								</div>

								<div class="module" id="module-info">
								
									<div class="module_content">
										<ol>
											<li>
												<script>
													function get_password() 
													{
														var function_name = "get_password";
														var parameters = [document.getElementById("email_address").value];
														var url = "camps/includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
														$.getJSON(url, function(success) {
															if (success == 0) {
																alert("Email address does not exist. Please enter another one.");
															}
															else {
																document.getElementById("email_address_div").style.display = "none";
																alert("Your password was sent to " + document.getElementById("email_address").value);
															}
														});												
													}
										
													function show_table() 
													{
														document.getElementById("email_address_div").style.display = "block";
													}
												</script>
												
												<table id="email_address_div" style="display:none;"><tr><td><a href="#">Email Address:</a></td><td><input type="text" name="email_address" id="email_address" style="width:240px;"></td><td><a href="#" class="submit" onclick="get_password();">GET PASSWORD</a></td></tr>
												</table>
												
												<p><a href="#" id="forgot_password" name="forgot_password" onclick="show_table();">Forgot Password</a></p>
												<p><a href="#" id="contact_us">Contact Us</a></p>
												<p><a href="statement.php">Enter Kiosk Mode</a></p>
												<p><a href="registration.php">New schools register here</a></p>
											</li>
										</ol>
									</div>
									
								</div>
								
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
									Chanie Mogilevsky<br>
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
