<?php
// if they have the mobile login but not the legacy/react login...
if ( isset( $_COOKIE['admin'] ) && !isset( $_COOKIE['admin_auth'] ) ) {
	header( "Location: /mobile/" ); // send them to the mobile site
}

// redirect to https if using http
// if (!isset($_SERVER['HTTPS'])) {
// 	$url = "https://". $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?" . $_SERVER['argv'][0];
// 	header("Location: $url");
// }
// start the session for something
session_start();

// redirect to mobile version if fromMobile is set
if ( !isset( $_GET['fromMobile'] ) && !isset( $_POST['fromMobile'] ) ) {
	require_once( __DIR__ . '/Mobile_Detect.php' );
	$detect = new Mobile_Detect;
	if ( $detect->isMobile() || $detect->isTablet() ) {
		header( "Location: /mobile/" );
		exit;
	}
}

// send them to the react app for the new homepage/login page.
if ( !isset( $_GET['oldsite'] ) ) {
	header( "Location: /new/" );
	die();
} 

// set the $admin_auth for use in admin_auth.php (header.php will only require it if this variable is set)
$admin_auth = array( 'school','user', 'class' );
$school_and_camp = false; // this is commented out on line 64 of admin_auth.php - Remove?
require('header.php'); 

// load the admin class
$school_registered = "true";
include("classes/admin.php");

// $admin_user is defined in the following chain: admin.php -> header.php -> db.php -> admin_auth.php on line #2
// is filled if the hased userpassword combo is correctly set in the cookie from the auth test
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
if (isset($_SESSION['admin'])) unset($_SESSION['admin']);
$_SESSION['admin_id'] = $admin_user['admin_id'];

// redirect to mobile page if user is a parent
// base commanders that are parents are an exception
$exceptions = array();
$exceptions[] = 3;
$sqlBC = "select admin_id from admin_auths where auth = 'user' and admin_id in (select admin_id from admin_auths where auth = 'school') group by admin_id";
$resultBC = mysql_query($sqlBC);
while ($rowBC = mysql_fetch_assoc($resultBC)) {
	$exceptions[] = $rowBC['admin_id'];
}
if ($admin->is_parent && !in_array($admin_user['admin_id'],$exceptions)) {
	require 'mobile/reg/ajax/encrypt.php';
	$value = encrypt_decrypt('encrypt', $admin_user['admin_id']);
	setcookie('admin', $value);
	header("Location: /mobile/reg/parent_detail.html");
	exit;
}

$admin->get_school_id();
$admin->get_auths();

//variables for blog
$uname = $admin->username;
$pass = $admin->password; // Plain text passwords?

$schl_id = $admin->school_id;
if ($admin_user['auths']['school']) {

	// ***** CHECK FOR ALL SCHOOLS ***** //
	$_SESSION["admin_id"] = $admin_user["admin_id"];
	$_SESSION["school_id"] = $schl_id;

	$sql = "SELECT school_id FROM admin_auths AS aa 
			JOIN schools AS s ON (aa.id=s.school_id AND s.school_era > 0) 
			WHERE aa.admin_id=" . $admin_user["admin_id"] . " 
			AND auth='school' 
			AND (aa.role_id=16 OR aa.role_id=18 or aa.role_id=34 or aa.role_id is null)";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$unregistered_school_id = $row["school_id"];
	if ($unregistered_school_id > 0) {
		$school_registered = "false";
		$schl_id = $unregistered_school_id;
		$_SESSION["school_id"] = $schl_id;
	}
	// ***** CHECK FOR ALL SCHOOLS ***** //

} else {
	if ($admin_user['auth'] != 'super') {
		//header("Location: /mobile/reg/parent_detail.html");
		//exit;
	}
}

$school_id = gri('school_id', -1);
$ui_type = 'admin';

if ($admin_user['admin_id'] > 0) {        
    $_SESSION["admin_id"] = $admin_user['admin_id'];
}

$admin->check_ckids_school();
// if ( $admin->beta || $admin->ckids ) {
// 	header( 'Location: /new' );
// }
// Note that T_() in the following html transalates the text if needed.
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Admin Menu'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<LINK rel="stylesheet" type="text/css" href="/styles/admin/admin.php.css"/> <!--chrome says this is not used by the page-->
		<STYLE type="text/css">
			.points tbody th {
				text-align: <?=$align_start?>;
			}
		</STYLE>
		
		<script>
			var school_registered = "<?=$school_registered;?>";
			var admin_id = <?=$admin_user['admin_id'];?>;
			// cannot find location of these variables being used.
			var user = <?="'" . $uname . "'";?>;
			var pass = <?="'" . $pass . "'";?>; // plain text password put in js!?!?!?!?
						
			// moved to js/admin/admin.php.js
		</script>
		<script src="js/admin/admin.php.js"></script>
		
<?php if (isset($_COOKIE['naftoli'])) : ?>
<!--<script src="https://global.localizecdn.com/localize.js"></script>-->
<!---->
<!--<script>!function(a){if(!a.Localize){a.Localize={};for(var e=["translate","untranslate","phrase","initialize","translatePage","setLanguage","getLanguage","detectLanguage","getAvailableLanguages","untranslatePage","bootstrap","prefetch","on","off"],t=0;t<e.length;t++)a.Localize[e[t]]=function(){}}}(window);</script>-->
<!---->
<!--<script>-->
<!--// Localize.initialize({ key: 'seqkv59qMeLU8', rememberLanguage: true });-->
<!--</script>-->
<?php endif; ?>
	</HEAD>

	<BODY onload="check_school_registered();">

		<? include('admin_header.php'); ?>

		<DIV CLASS="body">

			<DIV class="admin">
				<?php //this form is submitted with the code in ./js/admin/admin.php.js on line ~16 ?>
				<FORM name="registration_form" method="post" action="registration.php">
					<input type="hidden" name="admin_id" value="<?=$admin_user["admin_id"];?>">
					<input type="hidden" name="school_id" value="<?=$schl_id;?>">
				</FORM>

				<?
				//print_r($admin_user['auths']); exit;
				if (isset($admin_user['auths']['school'][0])) {
					$school_id = $admin_user['auths']['school'][0];
					$row = mysql_fetch_assoc(mq("SELECT logo, school_name, inst_name, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, school_logo_id, school_era FROM schools LEFT JOIN institutions USING (inst_id) WHERE school_id=" . $school_id . " LIMIT 1"));
					
					echo "<h1>Home Page</h1>";
					if (!empty($message)) {
						echo '<H2>' . $message . '</H2>';
					}
					include 'main_home.php';
				} else if (isset($admin_user['auths']['class']) && !empty($admin_user['auths']['class'])) {
					$admin_id = $admin_user['admin_id'];
					$class_id = $admin_user['auths']['class'][0];
					$sql = "select school_id from classes where class_id = " . $class_id;
					$result = mysql_query($sql);
					$row = mysql_fetch_assoc($result);
					$school_id = $row['school_id'];
					$sql = "select achievement_cards, store from admins where admin_id = " . $admin_id;
					$result = mysql_query($sql);
					$row = mysql_fetch_assoc($result);
					$printing = 0;
					if ($row['achievement_cards']) $printing = 1;
					$store = 0;
					if ($row['store']) $store = 1;
					?>
					<script>
						location.href = "/new/";
					</script>
					<?
				}
				
				if ($admin->is_parent) {
					//include 'parent_home.php';
					?>
					<script>
						//location.href = "/mobile";
					</script>
					<?
				}
				
				// add footer with links to about us, etc.
				include 'footer.php';
								
				if ($admin->auth == 'super') {
					echo "<H2>" . T_('Admin Menu') . "</H2>";
					$menu_type = 'super';
					include('admin_inc.php');
				}
				?>

			</DIV>

		</DIV>

        
		<!-- ***** If a parent logs in but has not registered any children yet ***** -->
		<? 
		if ($admin_user["is_parent"] == 1 && $admin_user["no_of_children"] == 0) : ?>
			<div style='text-align:center'>
				<br />
				<h1 style="color:red;">You have no children associated with your account.</h1>
				<ul>
					<li>
						<FORM name="register_parent_form" method="post" action="associate_children.php">
								<input type="hidden" name="admin_id" value="<?=$admin->admin_id;?>">
									<a href="#" onclick="document.forms['register_parent_form'].elements['admin_id'].value=<?=$admin->admin_id;?>; document.forms['register_parent_form'].submit();">  
									<?=T_('Add Children to Account')?>
								</a>
						</FORM>
					</li>
				</ul>
			</div>
		<? endif; ?>
		<!-- ***** If a parent logs in but has not registered any children yet ***** -->

        <? include('admin_footer.php'); ?>      
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		  ga('create', 'UA-71974937-1', 'auto');
		  ga('send', 'pageview');
		</script>
    </BODY>
    
</HTML>
