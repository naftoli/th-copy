<?php
session_start();

$admin_id = isset($_SESSION["admin_id"]) ? $_SESSION["admin_id"] : 0;
$school_id = isset($_SESSION["school_id"]) ? $_SESSION["school_id"] : 0;

include("db.php");
$next_page = "false";

if (isset( $_REQUEST['admin_id'] )) {
	$admin_id = $_REQUEST['admin_id'];
	$_SESSION['admin_id'] = $admin_id;
} 
if (isset( $_REQUEST['school_id'] )) {
	$school_id = $_REQUEST['school_id'];
	$_SESSION['school_id'] = $school_id;
}

if (isset( $_GET['skip'] ) && $_GET['skip'] == 'shimmy') {
	// we will need to skip the cc part at the end
	$_SESSION['skipCC'] = 'yes';
}

if (isset($_POST["action"])) {
	$action = $_POST["action"];
	$message = "";
	
	foreach ($_POST as $k => $v) {
        if (!is_array($v) && $k != 'shliach')
		    $_POST[$k] = mysql_real_escape_string(trim($v));
    }

    $shliach = json_decode( $_POST['shliach'] );
    if ( !empty( $_POST['shliachEmail'] ) ) {
        $shliach->email = $_POST['shliachEmail'];
    }    

	if ($action == "update") {		
		$sql = "UPDATE admins SET title='" . $_POST['title']  ."', first='" . $_POST['first'] . "', last='" . $_POST['last'] . "', admin_phone_mobile='" . $_POST['admin_phone_mobile'] . "', admin_email='" . $_POST['admin_email'] . "', admin_phone_work='" . $_POST['admin_phone_work'] . "', admin_phone_home='" . $_POST['admin_phone_home'] . "' WHERE admin_id=" . $admin_id;
		$query = mysql_query($sql);
		if (!$query) {
			//echo $sql . mysql_error();
			$message = "<span style='color:red;'>Administrator not updated. Please try again.</span></a>";
		}
		
		// update principal info and school type / chidon info
		$sql = "update schools
				set principal = '" . mysql_real_escape_string($_POST['p_name']) . "',
				principal_number = '" . mysql_real_escape_string($_POST['p_number']) . "',
                principal_email = '" . mysql_real_escape_string($_POST['p_email']) . "'";
        $sql .= ", chayolei = 0, chidon = 0, tanya = 0, ckids = 1";
		$sql .= " where school_id = " . $school_id;

		if (!mysql_query( $sql )) {
			$message = "<span style='color:red;'>Principal Info not updated.</span></a>";
		}
	} else {
        // check for duplicate email address
        $sql = "SELECT * FROM admins 
                WHERE admin_email = '" . $_POST['admin_email'] . "'";
        $result = mysql_query( $sql );
        if ( mysql_num_rows( $result ) > 0 ) {
            $message = "This email is already associated with an existing account, please choose another one (or login to existing account).";
        } else {
            $sql = "INSERT INTO admins SET 
                    username='" . 			$_POST['username'] . "', 
                    password='" . 			$_POST['password'] . "', 
                    title='" . 				$_POST['title']  ."', 
                    first='" . 				$_POST['first'] . "', 
                    last='" . 				$_POST['last'] . "', 
                    admin_phone_mobile='" . $_POST['admin_phone_mobile'] . "', 
                    admin_email='" . 		$_POST['admin_email'] . "', 
                    admin_phone_work='" . 	$_POST['admin_phone_work'] . "', 
                    admin_phone_home='" . 	$_POST['admin_phone_home'] . "', 
                    chabad_org_shliach_id = " . $shliach->shliachID;
            //$_SESSION['qry'] = $sql;
            $query = mysql_query($sql);
            if ($query) {
                $admin_id = mysql_insert_id();
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['school_id'] = $school_id;
                $_SESSION['school_type']	= $_POST['school_type'];
                
                // save principal and shliach info to session
                $_SESSION['p_name'] 		= $_POST['p_name'];
                $_SESSION['p_number'] 		= $_POST['p_number'];
                $_SESSION['p_email'] 		= $_POST['p_email'];
                $_SESSION['shliach']        = json_encode( $shliach );
            } else {
                $message = "<span style='color:red;'>Error creating admin account. Please try again.</span></a>";
                //echo $sql . "<br />" . mysql_error();
            }	
        }		
	}

	if ($message=="") {
		$next_page = "true";
	}
		
}
// first time through
else {
	if ( $school_id > 0 ) {
		$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
		$query = mysql_query($sql);
		$school_info = mysql_fetch_assoc($query);
		$school_name = $school_info["school_name"];
		$admin_school_id = $school_id;
	} 
	
	include("classes/admin.php");
	$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$admin = new admin($row);	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="https://chabadorg.clhosting.org/scripts/js/api/baseapi.js.asp?474DBD09-F59F-433D-A755-5A97594FC4E1"></script>
		<script>
			var next_page = <?=$next_page;?>;
            var duplicateEmail = false;

			$(function() {
				$("#nav").height($("#content").height());
                
                $("#username").blur( function() { 
                    var username = $("#username").val().trim();
                    if ( username ) {
                        $.post('ajax/checkUsername.php', {user : username}, function(data) {
                            if (data == 1) {
                                alert('This username is already in use.\nPlease choose another one.');
                            }
                        });
                    }
                });

                $("#bcEmail").blur( function() {
                    checkDuplicateEmail();
                });
                
                $("#copyShliach").click( function() {
                    if ( $(this).is(":checked") ) {
                        $("#p_name").val( shliach.first + ' ' + shliach.last );
                        $("#p_number").val( shliach.phone );
                        $("#p_email").val( $("#shliachEmail").val().trim() );
                    }
                });

                $("#copyToBc").click( function() {
                    if ( $(this).is(":checked") ) {
                        $("#bcFirst").val( shliach.first );
                        $("#bcLast").val( shliach.last );
                        $("#bcEmail").val( $("#shliachEmail").val().trim() );
                        $("#bcPhone2").val( shliach.phone );
                        checkDuplicateEmail();
                    }
                });

                <?php if ( isset( $_SESSION['shliach'] ) ) : ?>
                    shliach = <?=$_SESSION['shliach']?>;
                    setShliachInfo();
                <?php endif; ?>
			});

			function check_next_page() {
				if (next_page) {		
					location.href = "registration_ckids2.php";
				}
			}
			
			function isAlphabetic(sText) {
				var ValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
				var IsAlphabetic=false;
				var Char;			
				for (i = 0; i < sText.length; i++){
					Char = sText.charAt(i);
					if (ValidChars.indexOf(Char) != -1){
						IsAlphabetic = true;
						break;
					}
				}
				return IsAlphabetic;
			}
			
			function checkTerms() {
				//check if terms was checked off
				var terms = ['responsible', 'designate', 'commited', 'agree'];
				for (t in terms) {
					var checkbox = '#' + terms[t];
					if (!$(checkbox).is(":checked")) {
						alert("You must accept all terms.");
						return false;
					}
				}				
			}

            function checkDuplicateEmail() {
                var email = $("#bcEmail").val().trim();
                if ( email ) {
                    $.post('ajax/checkDuplicateEmail.php', { email: email }, function( duplicate ) {
                        if ( duplicate == 1 ) {
                            alert('This email is already in use.\nPlease choose another one.');
                            duplicateEmail = true;
                        } else {
                            duplicateEmail = false;
                        }
                    });
                } else {
                    duplicateEmail = false;
                }
            }
			
			function validate() {
                var errors = [];
				var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,6})$/;

                if ( duplicateEmail ) {
                    errors.push('The email you are using for your base commander (admin) account already exists. Please choose a different one.');
                }

                if ( shliach ) {
                    // check that shliach email is filled in and is valid
                    var email = $("#shliachEmail").val().trim();
                    if (reg.test(email) !== true) {
                        errors.push('You must enter a valid shliach email.');
                        $("#shliachEmail").focus();
                    }
                }

                // make sure username and password fields are not empty
                var username = $("#username").val().trim();
                var password = $("#password").val().trim();
                var password2 = $("#password2").val().trim();
                if (username == '' || password == '') {
                    errors.push('You must enter a username and password.');
                }
                if (password !== password2) {
                    errors.push('Your passwords do not match.');
                }

				// check principal info
				var p_name = $("#p_name").val().trim();
				var p_number = $("#p_number").val().trim();
				var p_email = $("#p_email").val().trim();
				if (p_name == '' || p_number == '' || p_email == '') {
					errors.push("You must enter the principal's info.");
				}
				if (p_name.length < 3) {
					errors.push("Principal name must be at least 3 characters.");
				}
				if (p_number.length < 9 || isAlphabetic(p_number)) {
					errors.push("Principal number must be at least 9 digits and cannot contain alphabetic characters.");
				}
				if (reg.test(p_email) !== true) {
					errors.push("Invalid principal email address.");
				}

                // check base commander info
                var bc_first = $("#bcFirst").val().trim();
                var bc_last = $("#bcLast").val().trim();
                var bc_email = $("#bcEmail").val().trim();
                var bc_phone1 = $("#bcPhone1").val().trim();
                var bc_phone2 = $("#bcPhone2").val().trim();
                var bc_phone3 = $("#bcPhone3").val().trim();

                if (bc_first.length < 2 || bc_last.length < 3) {
                    errors.push('Base Commander first and last name are too short.');
                }

                if (bc_phone1.length < 9 || isAlphabetic(bc_phone1) || 
                    bc_phone2.length < 9 || isAlphabetic(bc_phone2) ||
                    (bc_phone3.length > 0 && (bc_phone3.length < 9 || isAlphabetic(bc_phone3)))) {
                        errors.push('Base Commander phone numbers must be at least 9 digits and cannot contain alphabetic characters.');
                }

                if (reg.test(bc_email) !== true) {
                    errors.push('Invalid base commander email.');
                }

                if ( errors.length ) {
                    alert( errors.join("\n") );
                    return false;
                }
			}
            
            var shliach; 
            MyChabadApi.Events.AddEventListener("statusUpdated", function (ev)
            {
                if (ev.response.Status)
                {
                    //console.log(ev.response);
                    var loaderNode = Co.Tools.GetElementsByClassName("loader", "div");
                    if (loaderNode.length > 0)
                        Co.Tools.Content.AppendClassName(loaderNode, "active");
                    //make call to the server.
                    var key = ev.response.Key;
                    $.post('chabad_org/getShliachInfo.php', { key: key }, function( shliachInfo ) {
                        //console.log( shliachInfo );
                        shliach = shliachInfo; // set access to shliach info from global scope
                        setShliachInfo();
                    });
                }
            });

            function setShliachInfo() {
                var name = shliach.title + ' ' + shliach.first + ' ' + shliach.last;
                var phone = shliach.phone;
                var address = shliach.address;
                if (shliach.address2) address += "<br />" + shliach.address2;
                address += "<br />" + shliach.city + ', ' + shliach.state + ' ' + shliach.zip + "<br />" + shliach.country;
                $("#shliachName").html( name );
                $("#shliachPhone").html( phone );
                $("#shliachAddress").html( address );
                $("#login").hide();
                $("#shliachInfo").show();
                $("#copyShliachInfo").show();
                $("#copyToBcInfo").show();
                $("#shliach").val( JSON.stringify( shliach ) );
            }
		</script>
		<style type="text/css">
		    label.error {
		        color: red;
		        font-weight: normal;
		        float: left;
		        font-size: 12px;
		    }
		    input.error {
		        border: 2px solid red;
		    }
			.new {
				font-size: 14px;
				color: red;
				font-weight: bold;
			}
			.school_type_info {
				font-size: 14px;
			}
		</style>
	</head>

	<body onload="check_next_page();">
		
		<NOSCRIPT>
			<P STYLE="color: red; font-size: larger;">Notice: You have javascript disabled. Some parts of the site will not function without javascript.</P>
		</NOSCRIPT>
		
		<div id="wrapper">
		
			<div id="nav" class="wizard">
			
				<div class="col_title_bg"></div>
				
				<div class="col_title">Menu</div>
				
				<? include("registration_menu.php"); ?>
								
			</div>
			
			<div id="content">
			
				<div class="col_title_bg"></div>
				
				<div class="slider_container">
				
					<div class="slider">
					
						<div class="col_title"></div>
						
						<div class="col_content">
						
							<h1>School Registration</h1>
							
							<? if (isset($message) && $message != "") : ?>
								<h1 style="color:red"><?=$message;?></h1>
							<? endif; ?>
											
							<div class="infobox">
								<div class="module_content" style="position:relative; left:50px;">
									  <span class="label">
										<? if ( isset( $school_name ) ) { ?>
										You need to register school <b><?=$school_name;?></b><br />
										<? } ?>
										Please note: You will need a valid credit card to complete registration
									</span>
								</div>
							</div>
	 
							<form method="post" name="registration_form" id="registration_form" action="registration_ckids.php" accept-charset="UTF-8"> 
								<? if ($admin_id > 0) : ?>
								<input type="hidden" name="action" value="update">
								<? else : ?>
								<input type="hidden" name="action" value="add">
                                <? endif; ?>
                                
                                <input type="hidden" name="school_type" value="ckids" />
                                <input type="hidden" name="shliach" id="shliach" value="" />
                                
                                <div id="login">
                                    <p>Login to chabad.org to retrieve your personal and mosad info.</p>
                                    <span class="mychabad" view="login" settings="viewStyle=button"></span>
                                    <div class="loader"></div>
                                    <br /><br />  
                                </div>

                                <p>* denotes mandatory field</p>

                                <div id="shliachInfo" style="display: none;">
                                    <h2>Shliach Info</h2>
                                    <div class="module">
                                        <div class="module_content">
                                            <div class="lists form">
                                                <ul>
                                                    <li>
                                                        <span class="label">Name</span>
                                                        <span class="input" id="shliachName"></span>
                                                    </li>
                                                    <li>
                                                        <span class="label">Phone</span>
                                                        <span class="input" id="shliachPhone"></span>
                                                    </li>
                                                    <li>
                                                        <span class="label">Address</span>
                                                        <span class="input" id="shliachAddress"></span>
                                                    </li>
                                                    <li>
                                                        <span class="label">*Email</span>
                                                        <span class="input"><input type="text" name="shliachEmail" id="shliachEmail" class="email" 
                                                        <?php if ( isset( $_POST['shliachEmail'] ) ) echo "value='" . $_POST['shliachEmail'] . "'" ?>
                                                        /></span>    
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <? if (!isset($admin_id) || $admin_id == 0) : ?>
                                    <h2>Create Login For Tzivos Hashem Account</h2> 
                                    <div class="module" id="module-info">
                                        <div class="module_content">
                                            <div class="lists form">
                                                <ul>
                                                    <li>
                                                        <span class="label"><label for="username">*Username</label></span>
                                                        <span class="input"><input class="required" name="username" id="username" type="text" 
                                                        <?php if ( isset( $_POST['username'] ) ) echo "value='" . $_POST['username'] . "'" ?>
                                                        required /></span>
                                                    </li>
                                                    <li>
                                                        <span class="label"><label for="password">*Password</label></span>
                                                        <span class="input"><input class="required" name="password" id="password" type="password" required /></span>
                                                    </li>
                                                    <li>
                                                        <span class="label"><label for="password2">*Re-enter Password</label></span>
                                                        <span class="input"><input class="required" name="password2" id="password2" type="password" required /></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <? endif; ?>

                                <h2>Principal's Info</h2>
                                <div id="copyShliachInfo" style="display:none">
                                    <input type="checkbox" id="copyShliach" /> Shliach is Principal
                                </div>
                                <div class="module" id="module-info">
                                
                                    <div class="module_content">
                                    
                                        <div class="lists form">
                                            
                                            <ul>
                                                <li>
                                                    <span class="label"><label for="p_name">*Name</label></span>
                                                    <span class="input"><input name="p_name" type="text" id="p_name" 
                                                    <?php if ( isset( $_POST['username'] ) ) echo "value='" . $_POST['username'] . "'" ?>
                                                    <?php if ( isset( $school_info['principal'] ) ) echo "value='" . $school_info['principal'] . "'" ?>
                                                    required /></span>
                                                
                                                </li>
                                                <li>
                                                    <span class="label"><label for="p_number">*Contact Number</label></span>
                                                    <span class="input"><input name="p_number" type="text" id="p_number" 
                                                    <?php if ( isset( $_POST['p_number'] ) ) echo "value='" . $_POST['p_number'] . "'" ?>
                                                    <?php if ( isset( $school_info['principal_number'] ) ) echo "value='" . $school_info['principal_number'] . "'" ?>
                                                    required /></span>
                                                </li>
                                                <li>
                                                    <span class="label"><label for="p_email">*Email</label></span>
                                                    <span class="input"><input name="p_email" type="text" id="p_email" class="email" 
                                                    <?php if ( isset( $_POST['p_email'] ) ) echo "value='" . $_POST['p_email'] . "'" ?>
                                                    <?php if ( isset( $school_info['principal_email'] ) ) echo "value='" . $school_info['principal_email'] . "'" ?>
                                                    required /></span>
                                                </li>
                                            </ul>
                                            
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                                
                                <h2>Base Commander's Info</h2> 
                                <div id="copyToBcInfo" style="display:none">
                                    <input type="checkbox" id="copyToBc" /> Shliach is Base Commander
                                </div>
                                <div class="module" id="module-info">
                                
                                    <div class="module_content">
                                    
                                        <div class="lists form">
                                            
                                            <ul>
                                                <li>
                                                    <span class="label">
                                                        <label for="title">Title</label>
                                                    </span>
                                                    
                                                    <span class="input">
                                                        <select name="title" class="select input">
                                                            <option value="0" disabled="disabled">Please Select</option>
                                                            <? if ($admin->title == "Rabbi") : ?>
                                                            <option value="Rabbi" selected>Rabbi</option>
                                                            <? else : ?>
                                                            <option value="Rabbi">Rabbi</option>
                                                            <? endif; ?>
                                                            
                                                            <? if ($admin->title == "Mr.") : ?>
                                                            <option value="Mr." selected>Mr.</option>
                                                            <? else : ?>
                                                            <option value="Mr.">Mr.</option>
                                                            <? endif; ?>
                                                            
                                                            <? if ($admin->title == "Mrs.") : ?>
                                                            <option value="Mrs." selected>Mrs.</option>
                                                            <? else : ?>
                                                            <option value="Mrs.">Mrs.</option>
                                                            <? endif; ?>
                                                            
                                                            <? if ($admin->title == "Ms.") : ?>															
                                                            <option value="Ms." selected>Ms.</option>
                                                            <? else : ?>
                                                            <option value="Ms.">Ms.</option>
                                                            <? endif; ?>															
                                                        </select>													
                                                    </span>
                                                </li>
                                                <li>
                                                    <span class="label"><label for="first">*First Name</label></span>
                                                    <span class="input"><input class="required" name="first" id="bcFirst" type="text" 
                                                    <?php if ( isset( $_POST['first'] ) ) echo "value='" . $_POST['first'] . "'" ?>
                                                    <?php if ( isset( $admin->first ) ) echo "value='" . $admin->first . "'" ?>
                                                    value="<?=isset($admin->first)?$admin->first:'';?>" required /></span>
                                                </li>
                                                <li>
                                                    <span class="label"><label for="last">*Last Name</label></span>
                                                    <span class="input"><input class="required" name="last" id="bcLast" type="text" 
                                                    <?php if ( isset( $_POST['last'] ) ) echo "value='" . $_POST['last'] . "'" ?>
                                                    <?php if ( isset( $admin->last ) ) echo "value='" . $admin->last . "'" ?>                                                    
                                                    required /></span>
                                                </li>
                                                <li>
                                                    <span class="label"><label for="mobile">*Mobile Phone</label></span>
                                                    <span class="input"><input class="required" name="admin_phone_mobile" id="bcPhone1" type="text" 
                                                    <?php if ( isset( $_POST['admin_phone_mobile'] ) ) echo "value='" . $_POST['admin_phone_mobile'] . "'" ?>
                                                    <?php if ( isset( $admin->admin_phone_mobile ) ) echo "value='" . $admin->admin_phone_mobile . "'" ?>    
                                                    required /></span>
                                                </li>
                                                <li>
                                                    <span class="label"><label for="email">*Email Address</label></span>
                                                    <span class="input"><input class="required" name="admin_email" id="bcEmail" type="text" 
                                                    <?php if ( isset( $_POST['admin_email'] ) ) echo "value='" . $_POST['admin_email'] . "'" ?>
                                                    <?php if ( isset( $admin->admin_email ) ) echo "value='" . $admin->admin_email . "'" ?>    
                                                    required /></span>
                                                </li>
                                                <li>
                                                    <span class="label"><label for="work">*Work Phone (+ext)</label></span>
                                                    <span class="input"><input class="required" name="admin_phone_work" id="bcPhone2" type="text" 
                                                    <?php if ( isset( $_POST['admin_phone_work'] ) ) echo "value='" . $_POST['admin_phone_work'] . "'" ?>
                                                    <?php if ( isset( $admin->admin_phone_work ) ) echo "value='" . $admin->admin_phone_work . "'" ?>    
                                                    required /></span>
                                                </li>
                                                <li>
                                                    <span class="label"><label for="home">Home Phone</label></span>
                                                    <span class="input"><input name="admin_phone_home" id="bcPhone3" type="text" 
                                                    <?php if ( isset( $_POST['admin_phone_home'] ) ) echo "value='" . $_POST['admin_phone_home'] . "'" ?>
                                                    <?php if ( isset( $admin->admin_phone_home ) ) echo "value='" . $admin->admin_phone_home . "'" ?>   
                                                    /></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div id="nonChayolei">
                                    <input id="Continue" type="submit" value="Continue" class="button" onclick="return validate()">
                                </div>
                                                                
							</form> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	<script>
		
	</script>
</html> 
