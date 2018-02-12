<?php
include "db.php";
function agr(&$in, $name, $empty = '') {
 if(isset($in[$name])) {
   return trim($in[$name]);
 } else {
   return $empty;
 }
}

if ($_POST['login_username'] && $_POST['login_password'])
{
	// Authenticate
	$sql = "SELECT admin_id from admins where username = '" . $_POST['login_username'] . "' and password = '" . $_POST["login_password"] . "'";
                $result = mysql_query($sql) or die('Query failed');
                if($row = mysql_fetch_assoc($result)) {
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
<? if(isset($_COOKIE['kiosk_machine']) && isset($admin_auth)) header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/kiosk.php'); ?>
<? $username = agr($_COOKIE, 'username_default'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--<meta http-equiv="X-UA-Compatible" content="chrome=1">-->
<meta http-equiv="X-UA-Compatible" content="IE=8" />
<title>Hachayal Kiosk - Admin</title>
<link rel="alternate" media="print" href="index.php">
<link href="http://mashpia.com/CampMotivationalSystem-legacy-june-20-2010/dev/presentation/styles/reset.css" rel="stylesheet" type="text/css" />
<link href="http://mashpia.com/CampMotivationalSystem-legacy-june-20-2010/dev/presentation/styles/styles.css" rel="stylesheet" type="text/css" />
<link href="http://mashpia.com/CampMotivationalSystem-legacy-june-20-2010/dev/presentation/styles/print.css" rel="stylesheet" type="text/css" media="print" />
<!--[if lt IE 8]>
<link href="http://mashpia.com/CampMotivationalSystem-legacy-june-20-2010/dev/presentation/styles/style_ie.css" rel="stylesheet" type="text/css" />
<![endif]-->
<?php //include("includes/jquery.php"); ?>
<script src="http://cdn.jquerytools.org/1.2.2/jquery.tools.min.js"></script>
<!--[if lt IE 9]>
<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
<![endif]-->
<script>
	$(function() {
		$("#contact_us").overlay({top: '40%', target: '#overlay', closeOnClick: true, mask: { color: '#fff', loadSpeed: 200, opacity: 0.5 }});
		$('#username').focus();
	});
</script>
</head>

<body>
	<div id="wrapper">
    	<div id="nav">
        	<div class="col_title_bg"></div>
            <div class="col_title">Menu</div>
            <ul class="list_first">
                <li class="list_parent"><a href="#dashboard"><img src="images/icon_dashboard.png" width="22" height="22" alt="Login" /> Login</a></li>
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
                            <noscript><p style="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</p></noscript> 
                        </div> 
                             
                        <div class="module" id="module-info">
                            <h1>Login</h1>
                            <div class="module_content">
                                <div class="list">
                                    <form action="" method="post" accept-charset="UTF-8" name="login"> 
                                        <ul>
                                            <li>
                                                <span class="icon bullet"></span>
                                                <span class="label"><label for="username">Username</label></span><input type="text" name="login_username" id="username" size=64 maxlength=64 value="">
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="icon bullet"></span>
                                                <span class="label"><label for="password">Password</label></span><input type="password" name="login_password" id="password" size=64 maxlength=64 value="">
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
                                        <p><a href="register_school.php">New schools: Register your school as a Tzivos Hashem Base</a></p>
                                        <p><a href="register_camp.php">Camps: Register your camp as a Tzivos Hashem Base</a></p>
                                        <p>Existing schools: Please login to register.</p>
                                        <!--<p><a href="school_brouchure.pdf">About Us</a></p>--> 
                                        <p><a href="#" id="contact_us">Contact Us</a></p>
                                        <p><a href="kiosk.php">Enter Kiosk Mode</a></p> 
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
            <div class="module" id="module-info">
                <div class="module_content">
                    <p>For more information, please contact:<br>
                    Rochie Pink<br>
                    at 718-907-8884<br>
                    or <a href='ma&#105;lto&#58;CT%48&#64;T%7&#65;ivosHas&#104;em&#46;org'>CTH&#64;Tzi&#118;osH&#97;she&#109;&#46;o&#114;g</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
