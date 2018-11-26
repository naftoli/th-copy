<?php
ini_set('display_errors',1);
session_start();
//echo "<pre>"; print_r( $_SESSION ); echo "</pre>";
if ( !isset( $_SESSION['admin_id'] ) ) {
	header( "Location: registration_ckids.php" );
	exit;
}

include("db.php");
$admin_id = $_SESSION['admin_id'];
$school_id = $_SESSION['school_id'];
$next_page = "false";

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);

// if school id is 0 then this is a first time registration
$_SESSION["new_school_registration"] = $school_id > 0 ? 0 : 1;

$message = "";
if (isset($_POST["action"])) {
	$action = $_POST["action"];
	
	foreach ($_POST as $k => $v) {
        if (!is_array($v))
		    $_POST[$k] = mysql_real_escape_string(trim($v));
    }
    
    //echo "<pre>"; print_r( $_POST ); echo "</pre>"; 

	if ($action == "update") {
		// ****************************** SCHOOL LOGO ID ****************************** //
		$school_logo_id = 0;	
		if (isset($_FILES['school_logo_id'])) {
			include_once ("file_save.php");
			$school_logo_id = addFile($_FILES['school_logo_id'], $school_logo_id);
		}
		// ****************************** SCHOOL LOGO ID ****************************** //
			
		// ****************************** SCHOOL UPDATE ****************************** //
		$sql = "UPDATE schools ";
		$sql = $sql . "SET school_name='" 	. mysql_real_escape_string($_POST["school_name"]) . "', ";
		$sql = $sql . "school_name_he='" 	. mysql_real_escape_string($_POST["school_name_he"]) . "', ";
		$sql = $sql . "inst_id = 10, ";                 
		if ( isset( $_POST['school_gender'] ) ) $sql = $sql . "school_gender='" 	. mysql_real_escape_string($_POST["school_gender"]) . "', ";
		$sql = $sql . "school_address1='" 	. mysql_real_escape_string($_POST["school_address1"]) . "', ";
		$sql = $sql . "school_address2='" 	. mysql_real_escape_string($_POST["school_address2"]) . "', ";
		$sql = $sql . "school_city='" 		. mysql_real_escape_string($_POST["school_city"]) . "', ";
		$sql = $sql . "school_state='" 		. mysql_real_escape_string($_POST["school_state"]) . "', ";
		$sql = $sql . "school_postal='" 	. mysql_real_escape_string($_POST["school_postal"]) . "', ";
		$sql = $sql . "school_country='" 	. mysql_real_escape_string($_POST["school_country"]) . "', ";
		$sql = $sql . "school_phone='" 		. mysql_real_escape_string($_POST["school_phone"]) . "', ";
		if ($school_logo_id > 0)
			$sql = $sql . "school_logo_id=" . $school_logo_id;
		else 
			$sql = substr($sql, 0, strlen($sql) - 2);
		
		$sql = $sql . " WHERE school_id=" . $school_id;
		$query = mysql_query($sql);
		// ****************************** SCHOOL UPDATE ****************************** //
		
		if (!$query) {
			//echo $sql;
			$message = "School not updated. Please try again.";
		}
	}
	else {
		// create new school
		$sql = "SELECT school_number FROM schools ORDER BY school_number DESC LIMIT 1";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$school_number = $row["school_number"] + 1;
		
		$sql = "INSERT INTO schools ";
		$sql = $sql . "SET school_name='" . mysql_real_escape_string($_POST["school_name"]) . "', ";
		$sql = $sql . "school_name_he='" . mysql_real_escape_string($_POST["school_name_he"]) . "', ";
		$sql = $sql . "inst_id = 10 ";
        $sql .= ", chayolei = 1, chidon = 0, tanya = 0, rewards = 1 ";
		if ( isset( $_POST['school_gender'] ) )	$sql = $sql . ", school_gender='" . mysql_real_escape_string($_POST["school_gender"]) . "'";
		$sql = $sql . ", school_number=" . $school_number . ", ";
		$sql = $sql . "school_address1='" . mysql_real_escape_string($_POST["school_address1"]) . "', ";
		$sql = $sql . "school_address2='" . mysql_real_escape_string($_POST["school_address2"]) . "', ";
		$sql = $sql . "school_city='" . mysql_real_escape_string($_POST["school_city"]) . "', ";
		$sql = $sql . "school_state='" . mysql_real_escape_string($_POST["school_state"]) . "', ";
		$sql = $sql . "school_postal='" . mysql_real_escape_string($_POST["school_postal"]) . "', ";
		$sql = $sql . "school_country='" . mysql_real_escape_string($_POST["school_country"]) . "', ";
		$sql = $sql . "school_phone='" . mysql_real_escape_string($_POST["school_phone"]) . "', ";
		$sql = $sql . "school_era=1, reg_type=3, ";
		
		if (isset($school_logo_id) && $school_logo_id > 0)
			$sql = $sql . "school_logo_id=" . $school_logo_id;
		else 
			$sql = substr($sql, 0, strlen($sql) - 2);
			
		// add principal info 
		$sql .= ", principal = '" . mysql_real_escape_string($_SESSION['p_name']) . "',
				principal_number = '" . mysql_real_escape_string($_SESSION['p_number']) . "',
				principal_email = '" . mysql_real_escape_string($_SESSION['p_email']) . "'";
		//echo $sql . "<br />";
		$query = mysql_query($sql);
		if (!$query) {
			$message = "School not added. Please try again. <span style='font-size: 12px;'>(" . mysql_error() . ")</span>";
			//echo $sql . "<br />" . mysql_error();
		}
		else {
            $school_id = mysql_insert_id();
			$_SESSION['school_id'] = $school_id;
			$sql = "INSERT INTO admin_auths SET id=" . $school_id . ", admin_id=" . $admin_id . ", auth='school', role_id = 18";
			//echo $sql; exit;
			$query = mysql_query($sql);
			
			// get shliach id 
			$shliach = json_decode( $_SESSION['shliach'] );
			$shliach_id = $shliach->shliachID;

			// delete any previous info in shliach / mosad rel table
			$sql = "DELETE FROM shliach_mosad_rel 
					WHERE shliach_id = " . $shliach_id;
			mysql_query( $sql );

			// delete any previous school / mosad info
			$sql = "DELETE FROM school_mosad_rel 
					WHERE school_id = " . $school_id;
			mysql_query( $sql );
            
			// add mosdos info to db
			if ( isset( $_POST['mosdosInfo'] ) ) {
				foreach ($_POST['mosdosInfo'] as $mosadInfo) {
					$mosad = json_decode( $mosadInfo );
					$mosad_id = $mosad->id;
					$sql = "INSERT IGNORE INTO chabad_mosad_info SET 
							mosad_id = " . mysql_real_escape_string( $mosad_id ) . ", 
							json_info = '" . $mosadInfo . "'";
					//echo $sql . "<br />";
					@mysql_query( $sql );

					// add shliach / mosad relationship to db                
					$sql = "INSERT INTO shliach_mosad_rel SET 
							shliach_id = " . mysql_real_escape_string( $shliach_id ) . ", 
							mosad_id = " . mysql_real_escape_string( $mosad_id );
					//echo $sql . "<br />";
					@mysql_query( $sql );
				}
			}

			// add school / mosad relationship to db
			if ( isset( $_POST['mosdos'] ) ) {
				foreach ($_POST['mosdos'] as $mosad_id) {
					$sql = "INSERT INTO school_mosad_rel SET 
							school_id = " . $school_id . ", 
							mosad_id = " . mysql_real_escape_string( $mosad_id );
					//echo $sql . "<br />";
					@mysql_query( $sql );
				} 
			}           
		}
    }

	if ($message == "") {
		$next_page = "true";
	}
}

$school_logo_id = 0;
if ($school_id > 0) {
	include ("classes/school.php");
	$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$school = new \classes\school($row);
}

if ( isset( $school->school_logo_id ) && $school->school_logo_id > 0 ) 
	$school_logo_id = $school->school_logo_id;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>School Registration</title>
		<link rel="alternate" media="print" href="index.php">
		<link href="admin_styles.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" type="text/css" href="mobile/reg/css/keyboard.css">
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript" src="mobile/reg/js/keyboard.js" charset="UTF-8"></script>
		<script type="text/javascript">
			var next_page = "<?=$next_page;?>";    
            var chckbx_count = 0;
            var default_picked = false;
            var school_logo_id = <?=$school_logo_id;?>;
            
			$(function() {
				$("input[name='school_name']").focus();
				
				$("#registration_2").submit( function() {
					//check that all mandatory fields are filled in
					var errors = "";
					if ( $('input[name="school_name"]').val() == "" ) {
						errors += "Please enter the School Name.";
						$('input[name="school_name"]').focus();
					}

					if ( $("#school_logo_id_exists").val() != "on" && $("#school_logo_id").val() == "" && !$("#no_school_logo_id").is(":checked") ) {
						errors += "\nPlease upload school logo or indicate that there is no logo.";
					}

					//make sure address is filled out
					var address = $("#address").val();
					var city = $("#city").val();
					var state = $("#state").val();
					var zip = $("#zip").val();
					var phone = $("#phone").val();
					
					if (address == '' || city == '' || state == '' || zip == '' || phone == '') {
						alert("Address, City, State, Zip, and Phone are mandatory.");
						return false;
					}
					
					var str = $("#school_name_he").val();
					var he = encodeURI(str);
					if (he.indexOf('%') == -1) {
						alert("Hebrew School Name must be in hebrew letters.");
						return false;
					}
					
					if ( errors != "" ) { 
						alert( errors );
						return false;
					} else {
						return true;
					}
				});

                var shliach = <?=isset($_SESSION['shliach']) ? $_SESSION['shliach'] : 0?>;
                if (shliach && shliach.mosdos) {
                    var mosdos = shliach.mosdos;
                    var html = '';
					for (var m in mosdos) {
						var mosad = mosdos[m];
						var mosadName = mosad.name;
						var mosadAddress = mosad.address;
						if (mosad.address2) {
							mosadAddress += "<br />" + mosad.address2;
						}
						mosadAddress += "<br />" + mosad.city + ', ' + mosad.state + ' ' + mosad.zip + "<br />" + mosad.country;
						var types = mosad.types;
						html+= "<li><input type='hidden' name='mosdosInfo[]' value='" + JSON.stringify( mosad ) + "' />";
						html += "<input type='checkbox' class='mosdos' name='mosdos[]' value='" + mosad.id + "' /> <div class='mosadInfo'>" + mosadName + ' (';
						for (var t in types) {
							html += types[t] + ', ';
						}
						// remove trailing comma
						html = html.substring(0, html.length-2);
						html += ")<br />" + mosadAddress + "</div></li>";
					}
                    if (html != '') {
                        $("#chooseMosdos").html( html );
                        $("#mosdosInfo").show();
                        $("#copyFromMosad").show();
                    } 
                }

                $("#copyMosad").click( function() {
                    var id = $("#mosadToCopy").val();
                    var mosdos = shliach.mosdos;
                    for (var m in mosdos) {
                        var mosad = mosdos[m];
                        if (mosad.id == id) {
                            $("#school_name").val( mosad.name );
                            $("#address").val( mosad.address );
                            $("#address2").val( mosad.address2 );
                            $("#zip").val( mosad.zip );
                            $("#state").val( mosad.state );
                            $("#city").val( mosad.city );
                            $("#country").val( mosad.country );
                            $("#phone").val( mosad.phone );
                        }
                        break;
                    }
                });
			});
                
			$("#nav").height($("#content").height());
			
			// perform validation
			function check_checkboxes() {
				var message = "";
				
				var logo = document.getElementById('school_logo_id');
				var nologo = document.getElementById('no_school_logo_id');

				// if no logo entered and "no logo" not checked and brand new registration => then error
				if (logo.value==='' && nologo.checked===false && <?=$_SESSION["new_school_registration"]?> == true) 
					message = "New school: Please select school logo file or check school does not have logo.";
				
				// if no logo entered and "no logo" not checked and existing school and not logo on file
				else if (logo.value==='' && nologo.checked===false && <?=$_SESSION["new_school_registration"]?> == false && '<?=isset($school->school_logo_id)?>' == '0') 
					message = "Existing school:  Please select school logo file or check school does not have logo.";
				
				else if (logo.value !== '' && nologo.checked===true)
					message = "Cannot have both logo file and check school does not have logo.";
				
				if (message == "") {
					return true;
				}
				else {
					alert(message);
					return false;
				}
			}
			
			function update_is_default_count() {
				default_picked = true;
			}
			
			function set_default_type_to_true() {
				default_picked = true;
			}
			
			function check_school_logo_id() {
				if (school_id > 0 && school_logo_id == 0) {
					alert("Please note:Your ID cards will be printed without a logo.");
				}
			}

			function check_next_page() {
				if (next_page == "true") {
					location.href = "registration_ckids3.php";
				}
			}
        </script>
        <style>
            .mosdos {
                float: left;
                height: 40px;
                padding: 10px;
            }
            .mosadInfo {
                padding-left: 40px;
                padding-top: 10px;
                padding-bottom: 10px;
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
						<div class="col_content left">
						<h1>School Registration</h1>
	 
							<form name="registration_2" id="registration_2" action="registration_ckids2.php" method="post" enctype="multipart/form-data" accept-charset="UTF-8"> 
								<? if ($school_id > 0) : ?>
								<input type="hidden" name="action" value="update">
								<? else : ?>
								<input type="hidden" name="action" value="add">
								<? endif; ?>
							
								<? if ($message != "") : ?>
									<div style="color:red"><?=$message;?></div>
                                <? endif; ?>
                                
                                <div id="mosdosInfo" style="display: none;">                               
                                    <h2>Your Mosdos</h2>
                                    <p>Please choose which mosdos you would like to associate with your Tzivos Hashem account.
                                    <div class="module">
                                        <div class="module_content">
                                            <div class="lists form">
                                                <ul id="chooseMosdos"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								
								<h2>Your New Tzivos Hashem School Account Info</h2> 
								<div id="copyFromMosad" style="display: none">
                                    Copy info from following mosad: 
                                    <select id="mosadToCopy">
                                        <?php
                                        $shliach = json_decode( $_SESSION['shliach'] );
                                        $mosdos = $shliach->mosdos;
                                        foreach ($mosdos as $mosad) {
                                            echo "<option value='" . $mosad->id . "'>" . $mosad->name . "</option>";
                                        }
                                        ?>
                                    </select> 
                                    <input type="button" id="copyMosad" value="Copy" />
                                </div>          
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
											<ul>
												<li>
													<span class="label"><label for="name">School Name</label></span>
													<span class="input"><input name="school_name" id="school_name" type="text" value="<?=isset($school)?$school->school_name:'';?>" required /></span>
												</li>
												<li>
													<span class="label"><label for="school_name_he">School Name in Hebrew</label></span>
													<span class="input"><input class="keyboardInput" name="school_name_he" type="text" id="school_name_he" value="<?=isset($school)?$school->school_name_he:'';?>" /></span>
													<div class="clear"></div>
													<!--<div id="vk"></div>-->
												</li>
												    
												<li>
													<span class="label">
														<label for="gender">Gender</label>
													</span>
													
													<span class="input">													
														<label><input name="school_gender" type="radio" value="M" <?=(isset($school) && $school->school_gender == "M")?'checked="checked"':''?> />Male</label>
														<label><input name="school_gender" type="radio" value="F" <?=(isset($school) && $school->school_gender == "F")?'checked="checked"':''?> />Female</label>
														<label><input name="school_gender" type="radio" value="B" <?=(isset($school) && $school->school_gender == "B")?'checked="checked"':''?> />Both</label>
													</span>
												</li>
												
												<li>
													<span class="label"><label for="address">Address</label></span>
													<span class="input"><input id="address" name="school_address1" type="text" value="<?=isset($school)?$school->school_address1:'';?>" required /></span>
													
													<div class="clear"></div>
													<span class="label">Address2</span>													
													<span class="input"><input name="school_address2" id="address2" type="text" value="<?=isset($school)?$school->school_address2:'';?>" /></span>
													
													<div class="clear"></div>
													
													<span class="label">City/State/Zip</span>
													
													<? if (isset($school) && $school->school_city != "") : ?>
														<span class="input city"><input id="city" name="school_city" type="text" value="<?=isset($school)?$school->school_city:'';?>" required /></span>
													<? else : ?>
														<span class="input city"><input id="city" name="school_city" type="text" placeholder="City" required /></span>
													<? endif; ?>

													<? if (isset($school) && $school->school_state != "") : ?>
													<span class="input state"><input id="state" name="school_state" type="text" value="<?=isset($school)?$school->school_state:'';?>" required /></span>												
													<? else : ?>
													<span class="input state"><input id="state" name="school_state" type="text" placeholder="State" value="<?=isset($school)?$school->school_state:'';?>" required /></span>
													<? endif; ?>
													
													<? if (isset($school) && $school->school_postal != "") : ?>												
													<span class="input zip"><input id="zip" name="school_postal" type="text" value="<?=isset($school)?$school->school_postal:'';?>" required /></span>
													<? else : ?>
													<span class="input zip"><input id="zip" name="school_postal" type="text" placeholder="Zip" value="<?=isset($school)?$school->school_postal:'';?>" required /></span>
													<? endif; ?>
												</li>
												
												<li>
													<span class="label"><label for="country">Country</label></span>
													<span class="input"><input name="school_country" id="country" type="text" value="<?=isset($school)?$school->school_country:'';?>"  /></span>
												</li>
												<li>
													<span class="label"><label for="phone">Phone</label></span>
													<span class="input"><input id="phone" name="school_phone" type="text" value="<?=isset($school)?$school->school_phone:'';?>" required /></span>
												</li>
											</ul>
										</div>
									</div>
                                </div>
								
								<ul>
									<li>
                                        <input type="submit" value="Continue" class="button"> 
									</li>
								</ul>
							</form> 
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	
</html>
