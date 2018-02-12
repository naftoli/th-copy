<?php
session_start();

if (!isset($_GET['fromMobile'])) {
	require_once 'Mobile_Detect.php';
	$detect = new Mobile_Detect;
	if ( $detect->isMobile() || $detect->isTablet() ) {
	//if ( $detect->isMobile() ) {
		header("Location: /mobile/reg");
		exit;
	}
}

$admin_auth = array('school','user', 'class'); 
$school_and_camp = false;
require('header.php'); 

$school_registered = "true";
include("classes/admin.php");

$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
if (isset($_SESSION['admin'])) unset($_SESSION['admin']);
$_SESSION['admin_id'] = $admin_user['admin_id']; 
$admin->get_school_id();
$admin->get_auths();

//variables for blog
$uname = $admin->username;
$pass = $admin->password;

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
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

    <HEAD>
        <TITLE><?=T_('Admin Menu'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
        <STYLE type="text/css">
            .points {
              margin: 10px 0px;
            }

            .points tbody th {
              text-align: <?=$align_start?>;
            }

            .points tbody td {
              text-align: right;
            }
            
            .regInfo {
                float: right;
                width: 200px;
                border: 1px dashed red;
                height: 200px;
                padding: 5px;
                font-size: 12px;
            }
            
            .red {
                color: red;
                font-weight:bold;
            }
            
            .photo {
                float: right;
                text-align: center;
            }
            .photo img {
                border: 1px solid black;
            }
            .wall {
            	line-height: 1.25;
            }
            fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
        </STYLE>
        
        <script>
            var school_registered = "<?=$school_registered;?>";
            var admin_id = <?=$admin_user['admin_id'];?>;
            
            var user = <?="'" . $uname . "'";?>;
            var pass = <?="'" . $pass . "'";?>;
                        
            function change_class(class_id) {
                document.getElementById("class_id").value = class_id;
                document.forms["class_form"].submit();          
            }
        
            function change_school(school_id) {
                document.getElementById("school_id").value = school_id;
                document.forms["school_form"].submit();         
            }
            
            function change_child(child_id) {
                document.getElementById("child_id").value = child_id;
                document.forms["child_form"].submit();
            }
            
            function check_school_registered() {
                if (school_registered == "false" && admin_id != 2) {
                    document.forms["registration_form"].submit();
                }
            }
            
            // validate input   
            function validation(){
            
                var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                var address = document.getElementById('admin_email').value;
                
                if (document.getElementById('first').value == '') {
                    document.getElementById('first').focus();
                    alert("First Name is mandatory.");
                    return false;
                }   
                else if (document.getElementById('last').value == '') {
                    document.getElementById('last').focus();
                    alert("Last Name is mandatory.");
                    return false;
                }
                else if (document.getElementById('admin_phone_home').value == '') {
                    document.getElementById('admin_phone_home').focus();
                    alert("Home phone is mandatory.");
                    return false;
                }
                else if (document.getElementById('admin_email').value == '') {
                    document.getElementById('admin_email').focus();
                    alert("Email is mandatory.");
                    return false;
                }
                else if  (reg.test(address) != true) {                  
                    document.getElementById('admin_email').focus();
                    alert("Invalid email address.");
                    return false;               
                }
                else {
                    return true;            
                }
            }
            
            function popUp() {
            	alert("Please Note: If you have changed your username or password you will automatically be logged out and you will need to login with the new username / password that you entered.");
            }
        </script>
    </HEAD>
    
    <BODY onload="check_school_registered();">

        <? include('admin_header.php'); ?>
                
        <DIV CLASS="body">          
            
            <DIV class="admin">
            
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
					?>
					<script>
						location.href = "/v2/login/frommashpia/school_id/<?=$school_id?>/admin_id/<?=$admin_id?>/class_id/<?=$class_id?>";
					</script>
					<?
				}
				
				if ($admin->is_parent) {
					include 'parent_home.php';
					?>
					<script>
						//location.href = "mashpia.com/mobile";
					</script>
					<?
				}
				
				//add footer with links to about us, etc.
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
		    <CENTER>
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
		    </CENTER>
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
