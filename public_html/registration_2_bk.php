<?php
ini_set('display_errors',1);
session_start();
//print_r( $_SESSION );
if ( !isset( $_SESSION['hschool'] ) ) 
    header( "Location: admin.php" );
$h_school = $_SESSION['hschool'];

include("db.php");
include("check_admin_id.php");

// assume that this is a first time registration unless proved otherwise
$_SESSION["new_school_registration"] = 'true';
$next_page = "false";

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$school_id = 0;
$admin->get_school_id();
if ($admin->school_id > 0)
	$school_id = $admin->school_id;

$message = "";
if (isset($_POST["action"])) {
	$action = $_POST["action"];
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}

	if ($action == "update") {
		// ****************************** SCHOOL LOGO ID ****************************** //
		$school_logo_id = 0;	
		if (isset($_FILES['school_logo_id'])) {
			include_once ("file_save.php");
			//$school_logo_id = addLogoNew($_FILES['school_logo_id'], $school_logo_id);
			//echo $school_logo_id; exit;
		}
		// ****************************** SCHOOL LOGO ID ****************************** //
			
		// ****************************** SCHOOL UPDATE ****************************** //
		$sql = "UPDATE schools ";
		$sql = $sql . "SET school_name='" 	. mysql_real_escape_string($_POST["school_name"]) . "', ";
		$sql = $sql . "school_name_he='" 	. mysql_real_escape_string($_POST["school_name_he"]) . "', ";
                if ($h_school)
                    $sql = $sql . "inst_id=4, ";
                else 
                    $sql = $sql . "inst_id=2, ";
		if ( isset( $_POST['school_gender'] ) )
                $sql = $sql . "school_gender='" 	. mysql_real_escape_string($_POST["school_gender"]) . "', ";
                $sql = $sql . "school_address1='" 	. mysql_real_escape_string($_POST["school_address1"]) . "', ";
                $sql = $sql . "school_address2='" 	. mysql_real_escape_string($_POST["school_address2"]) . "', ";
                $sql = $sql . "school_city='" 		. mysql_real_escape_string($_POST["school_city"]) . "', ";
                $sql = $sql . "school_state='" 		. mysql_real_escape_string($_POST["school_state"]) . "', ";
                $sql = $sql . "school_postal='" 	. mysql_real_escape_string($_POST["school_postal"]) . "', ";
		$sql = $sql . "school_country='" 	. mysql_real_escape_string($_POST["school_country"]) . "', ";
		$sql = $sql . "school_phone='" 		. mysql_real_escape_string($_POST["school_phone"]) . "', ";
		if ($school_logo_id > 0)
			$sql = $sql . "school_logo_id=" . 'logos/' . $school_logo_id;
		else 
			$sql = substr($sql, 0, strlen($sql) - 2);
			
		// add number of students
		if (intval($_POST['total_children']) > 0) {
			$sql .= ", num_students = " . intval($_POST['total_children']);
		}
			
		$sql = $sql . " WHERE school_id=" . $_POST["school_id"];
		$query = mysql_query($sql);
		// ****************************** SCHOOL UPDATE ****************************** //
		
		if (!$query) {
			//echo $sql;
			$message = "School not updated. Please try again.";
		}
		else {			
			$sql = "DELETE FROM school_child_types WHERE school_id=" . $admin->school_id;
			$query = mysql_query($sql);						
            /*
            if ( isset( $_POST['child_type_id'] ) )
                $child_type = $_POST['child_type_id'];
            else
                $child_type = null; 
            switch ($child_type) {
                case 1:
                    $set_default = 1;
                    break;
                case 2:
                    $set_default = 2;
                    break;
                case 3:
                    $set_default = 3;
                    break;                  
            }
            if ( $child_type ) {
                $sql = "INSERT INTO school_child_types values(null, $school_id, $child_type, $set_default)";
                $query = mysql_query($sql);
            }
             * 
             */			
		}
	
	}
	else {
 
		$sql = "SELECT school_number FROM schools ORDER BY school_number DESC LIMIT 1";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$school_number = $row["school_number"] + 1;
		
		$sql = "INSERT INTO schools ";
		$sql = $sql . "SET school_name='" . mysql_real_escape_string($_POST["school_name"]) . "', ";
		$sql = $sql . "school_name_he='" . mysql_real_escape_string($_POST["school_name_he"]) . "', ";
                if ($h_school)
                    $sql = $sql . "inst_id=4, ";
                else 
                    $sql = $sql . "inst_id=2, ";
                if ( isset( $_POST['school_gender'] ) )
    		$sql = $sql . "school_gender='" . mysql_real_escape_string($_POST["school_gender"]) . "', ";
		$sql = $sql . "school_number=" . $school_number . ", ";
		$sql = $sql . "school_address1='" . mysql_real_escape_string($_POST["school_address1"]) . "', ";
		$sql = $sql . "school_address2='" . mysql_real_escape_string($_POST["school_address2"]) . "', ";
		$sql = $sql . "school_city='" . mysql_real_escape_string($_POST["school_city"]) . "', ";
		$sql = $sql . "school_state='" . mysql_real_escape_string($_POST["school_state"]) . "', ";
		$sql = $sql . "school_postal='" . mysql_real_escape_string($_POST["school_postal"]) . "', ";
		$sql = $sql . "school_country='" . mysql_real_escape_string($_POST["school_country"]) . "', ";
		$sql = $sql . "school_phone='" . mysql_real_escape_string($_POST["school_phone"]) . "', ";
		$sql = $sql . "school_era=1, ";
		$sql = $sql . "chayolei=1, ";
		
		if (isset($school_logo_id) && $school_logo_id > 0)
			$sql = $sql . "school_logo_id=" . $school_logo_id;
		else 
			$sql = substr($sql, 0, strlen($sql) - 2);
			
		// add principal info 
		$sql .= ", principal = '" . mysql_real_escape_string($_SESSION['p_name']) . "',
				principal_number = '" . mysql_real_escape_string($_SESSION['p_number']) . "',
				principal_email = '" . mysql_real_escape_string($_SESSION['p_email']) . "', ";
		
		// add chidon info
		if (intval($_SESSION['chidon']) == 1) {
			$sql .= "chidon = 1,
					chidon_name = '" . mysql_real_escape_string($_SESSION['chidon_name']) . "',
					chidon_number = '" . mysql_real_escape_string($_SESSION['chidon_number']) . "',
					chidon_email = '" . mysql_real_escape_string($_SESSION['chidon_email']) . "'";
		} else {
			$sql .= "chidon = 0";
		}
			
		$query = mysql_query($sql);
		if (!$query) {
			$message = "School not added. Please try again.";
		}
		else {
			$school_id = mysql_insert_id();
			$sql = "INSERT INTO admin_auths SET id=" . $school_id . ", admin_id=" . $admin_id . ", auth='school'";
			$query = mysql_query($sql);
            /*
            if ( isset( $_POST['child_type_id'] ) )
                $child_type = $_POST['child_type_id'];
            else
                $child_type = null; 
            switch ($child_type) {
                case 1:
                    $set_default = 1;
                    break;
                case 2:
                    $set_default = 2;
                    break;
                case 3:
                    $set_default = 3;
                    break;                  
            }
            if ( $child_type ) {
    			$sql = "INSERT INTO school_child_types values(null, $school_id, $child_type, $set_default)";
    			$query = mysql_query($sql);
            }
             * 
             */				
		}
	}
	
	if ($message == "") {
		//header("Location: https://www.mashpia.com/registration_3.php");
		$next_page = "true";
	}
}

$school_logo_id = 0;
if ($admin->school_id > 0) {
	include ("classes/school.php");
	$sql = "SELECT * FROM schools WHERE school_id=" . $admin->school_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	// not first time registration
	if($row){
		$_SESSION["new_school_registration"] = 'false';					
	}
	$school = new \classes\school($row);
}

if ( isset( $school->school_logo_id ) && $school->school_logo_id > 0 ) 
	$school_logo_id = $school->school_logo_id;
	
include ("camps/includes/classes/child_type.php");
$child_types = array();
$sql = "SELECT * FROM child_types order by child_type_id";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$child_type = new child_type($row);
	if ($admin->school_id > 0) 
		$child_type->get_school_child_type_id($admin->school_id);
	array_push($child_types, $child_type);
}		
	
include ("camps/includes/classes/institution.php");
$institutions = array();
$sql = "SELECT * FROM institutions";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$institution = new institution($row);
	array_push($institutions, $institution);
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
		<link rel="stylesheet" type="text/css" href="mobile/reg/css/keyboard.css">
                <!--
		<script src="camps/scripts/jquery.tools.min.js"></script>
                <script src="scripts/jquery.placeholder.js"></script>
		<script src="scripts/vk/vk_loader.js?vk_layout=IL%20Hebrew&vk_skin=flat_gray" type="text/javascript"></script>
                -->
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript" src="mobile/reg/js/keyboard.js" charset="UTF-8"></script>
		<script type="text/javascript">
		    var next_page = "<?=$next_page;?>";
            var admin_id = <?=$admin_id;?>;
            var hschool = <?=$h_school?>;
                    
            var chckbx_count = 0;
            var default_picked = false;
            var school_id = <?=$school_id;?>;
            var school_logo_id = <?=$school_logo_id;?>;
            /*
            var W3CDOM = (document.createElement && document.getElementsByTagName);
            
            function initFileUploads() {
                if (!W3CDOM) return;
                var fakeFileUpload = document.createElement('div');
                fakeFileUpload.className = 'fakefile';
                var newInput = document.createElement('input');
                newInput.setAttribute('type','text');
                fakeFileUpload.appendChild(newInput);
                var image = document.createElement('a');
                image.className='button';
                image.innerHTML='Browse';
                fakeFileUpload.appendChild(image);
                var x = document.getElementsByTagName('input');
                for (var i=0;i<x.length;i++) {
                    
                    var name = x[i].name;
                    var index_of = name.indexOf("no_of_children_");
                    if (index_of > -1) {
                        var input_id = name.substr(15, name.length - index_of);
                        document.getElementById(input_id).value = x[i].value;
                    }
                    
                    if (x[i].type != 'file') continue;
                    if (x[i].parentNode.className != 'fileinputs') continue;
                    x[i].className = 'file hidden';
                    var clone = fakeFileUpload.cloneNode(true);
                    x[i].parentNode.appendChild(clone);
                    x[i].relatedElement = clone.getElementsByTagName('input')[0];
                    x[i].onchange = x[i].onmouseout = function () {
                        this.relatedElement.value = this.value;
                    }
                }
            }
            
            initFileUploads();
            */
                $(function() {
                    $("input[name='school_name']").focus();
                    /*
                    if ( hschool ) {
                        $('.forYeshiva').hide();
                    }

                    $('input[name="inst_id"]').click( function() {
                    if ( $(this).val() == 2 ) {
                        $('.forYeshiva').show();
                    } else {
                        $('.forYeshiva').hide();
                    }
                    */
                    
                    
                    $("#registration_2").submit( function() {
                        //check that all mandatory fields are filled in
                        var errors = "";
                        if ( $('input[name="school_name"]').val() == "" ) {
                            errors += "Please enter the School Name.";
                            $('input[name="school_name"]').focus();
                        }
						/*
                        if ( !hschool ) {
                            if ( !$('input[name="inst_id"]:checked').val() ) {
                                errors += "\nPlease choose the Institution Type.";
                            }
                        }
                        */
						/*
                        if ( $("#school_logo_id_exists").val() != "on" && $("#school_logo_id").val() == "" && !$("#no_school_logo_id").is(":checked") ) {
                            errors += "\nPlease upload school logo or indicate that there is no logo.";
                        }
                        */
                        /*
                        if ( !hschool ) {
                            if ( $('input[name="inst_id"]:checked').val() == 2 && !$('input[name="child_type_id"]:checked').val() ) {
                                errors += "\nPlease choose the Child Type.";
                            }
                        }
                        */
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

                });
                
                $("#nav").height($("#content").height());
                
                //$('input').placeholder(); 
                
                /*
                $('#input').focus(function(){
                    $('#vk').slideDown('fast'); 
                });
                $('#input').blur(function(){
                    $('#vk').slideUp('fast');   
                });
                
                if ( !hschool ) {
                    $('input[name="child_type"]').change(function(){
                            $(this).parents('li').find('.toggle').hide();
                            $(this).filter(':checked').parents('li').find('.toggle').show();
                            $('.default').hide();
                            if ($('input[name="child_type"]:checked').length > 1 ) {
                                $('.default').show();
                            }
                    });
                    $('input[name="child_type"]').change();
                }
                */
			
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
				
				//else if (chckbx_count == 0) 
					//message = "You must choose a child type.";
				
				if (message == "") {
					return true;
				}
				else {
					alert(message);
					return false;
				}
			}
			/*
			function update_child_type_count(chckbx, txtbxnmbr) {
				var list_item = $(chckbx).parents().find("li");
				var radio_button = $(list_item).find("input[name=is_default]");				
				var input = $(list_item).find("input[name=no_of_children_" + txtbxnmbr + "]");
								
				if (chckbx.checked == true) {
					chckbx_count++;
				}
				else {
					if (radio_button[txtbxnmbr - 1].checked == true) {
						chckbx.checked = true;
						alert("You must choose a default child type before removing this child type.");						
					}
					else {
						chckbx_count--;
						$(input).val("");
					}
				}
			}
                        */
			
			function update_is_default_count() {
				default_picked = true;
			}
			
			//function increment_chckbx_count() {				
			//	chckbx_count++;
			//}
			
			function set_default_type_to_true() {
				default_picked = true;
			}
			
			function check_school_logo_id() {
				if (school_id > 0 && school_logo_id == 0) {
					if ( hschool ) 
                       alert("Please note:Your ID cards will be printed without a logo.");
					else 
					   alert("Please note:Your ID cards will be printed without a logo.");
				}
			}

			function check_next_page() {
				if (next_page == "true") {
					//check_school_logo_id();
					var registration_form_three = document.forms["registration_form_three"];
					registration_form_three.elements["admin_id"].value = admin_id;
                    registration_form_three.elements["school_id"].value = school_id;
					registration_form_three.submit();
				}
			}
		</script>
	</head>

	<body onload="check_next_page();">
	    
	    <? $action = "registration_4.php"; ?>
		<FORM name="registration_form_three" method="post" action="<?=$action?>">
			<input type="hidden" name="admin_id" value="">
			<input type="hidden" name="school_id" value="">
			<input type="hidden" name="hschool" value="">
			
		</FORM>

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
	 
							<form name="registration_2" id="registration_2" action="registration_2_bk.php" method="post" enctype="multipart/form-data" accept-charset="UTF-8"> 
								<? if ($admin->school_id > 0) : ?>
								<input type="hidden" name="action" value="update">
								<? else : ?>
								<input type="hidden" name="action" value="add">
								<? endif; ?>
								
								<input type="hidden" name="school_id" value="<?=$admin->school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
							
								<? if ($message != "") : ?>
									<?=$message;?>
								<? endif; ?>
								
								<h2>School Info</h2> 
								
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
											<ul>
												<li>
													<span class="label"><label for="name">School Name</label></span>
													<span class="input"><input name="school_name" type="text" value="<?=isset($school)?$school->school_name:'';?>" required /></span>
												</li>
												<li>
													<span class="label"><label for="school_name_he">School Name in Hebrew</label></span>
													<span class="input"><input class="keyboardInput" name="school_name_he" type="text" id="school_name_he" value="<?=isset($school)?$school->school_name_he:'';?>" /></span>
													<div class="clear"></div>
													<!--<div id="vk"></div>-->
												</li>
												<? if ( !$h_school && false ) { ?>
												<li>
													<span class="label"><label for="type">Institution Type</label></span>
													<span class="input">
														<? for ($ino = 0; $ino < count($institutions); $ino++) : ?>
														    <? if ( $institutions[$ino]->inst_id == 4 ) continue; ?>                                                           													
															<label><input name="inst_id" type="radio"  
															    <? 
                                                                echo " value=" . $institutions[$ino]->inst_id;
                                                                if ( isset( $school ) ) {
                                                                    echo ($school->inst_id == $institutions[$ino]->inst_id)?' checked="checked"':'';
                                                                }
                                                                echo " />";
                                                                echo $institutions[$ino]->inst_name;
                                                                ?>
															    </label>
														<? endfor; ?>
												    </span>
												</li>
												<? } else { ?>
												    <input type="hidden" name="inst_id" value="4" />
												<? } ?>    
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
													<span class="input"><input name="school_address2" type="text" value="<?=isset($school)?$school->school_address2:'';?>" /></span>
													
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
													<span class="input"><input name="school_country" type="text" value="<?=isset($school)?$school->school_country:'';?>"  /></span>
												</li>
												<li>
													<span class="label"><label for="phone">Phone</label></span>
													<span class="input"><input id="phone" name="school_phone" type="text" value="<?=isset($school)?$school->school_phone:'';?>" required /></span>
												</li>
												<!--
												<script>
												    var hschool = <?=$h_school?>;
                                                                                                    function no_school_logo(chckbx) {
                                                                                                        if (chckbx.checked == true) {
                                                                                                            if ( hschool ) 
                                                                                                               alert("Please note:Your ID cards will be printed without a logo.");
                                                                                                            else 
                                                                                                               alert("Please note:Your school billboard and ID cards will be printed without a logo.");
                                                                                                           
                                                                                                            //delete logo if there is one and refresh page
                                                                                                            <? if (isset($school->school_logo_id) && $school->school_logo_id) : ?>
                                                                                                            $.post('ajax/deleteLogo.php', {id : school_id}, function(data) {
                                                                                                                //alert(data);
                                                                                                                //history.go(0);
                                                                                                            });
                                                                                                            <? endif; ?>
                                                                                                        }
                                                                                                    }
												</script>
												
												<li>
													<span class="label">
														<label for="logo">School Logo</label>
													</span>
													<span class="input">
														<span class="fileinputs">
															<table  width='100%'><tr><td>
															<input name="school_logo_id"  id="school_logo_id" type="file" /><br />
															<input name="no_school_logo_id"  id="no_school_logo_id" type="checkbox" onclick="no_school_logo(this);"/>Our school does not have a logo
															</td><td width='30%'></td><td  width='40%'>
															<br>
															<? if ( isset( $school->school_logo_id ) ) { ?>
															    <img height="100" alt="<?=$school->school_logo_id?>" src="/file_view.php?id=<?=$school->school_logo_id?>" />
                                                                                                                            <input type="hidden" id="school_logo_id_exists" value="on" />
															<? } ?>
															</td></tr></table>
														</span>
													</span>
												</li>
												-->
												<li>
													<p style="font-size: 16px; font-weight: bold;">
														How many students does your school have (roughly)?<br />
														<span class="input"><input type="text" name="total_children" style="width: 100px" /></span>
													</p>
												</li>
											</ul>
										</div>
									</div>
								</div>
								<!--
								<div class="forYeshiva">
    								<h2>Mission Type</h2> 
    								<div class="infobox">
    									<div class="module_content">
    										  <span class="label"><p>This program offers customized missions based on the background of your students.</p>
    										  <p>
    											1. Chabad Campaigns and Missions are based on halocha and Lubvaitcher minhogim and are geared towards children brought up in a Lubavitcher home. (Example of campaigns: Chassidishe Yomei Depagra, Hiskashrus & Tanya Baal Peh)</p>
    										    2. Frum Campaigns and Missions are based on halocha and are geared towards children who don't yet want to affiliate themselves with Lubavitcher minhogim. (Example of campaigns: General Yomim Tovim)
    										 </p></span>
    									</div>
    								</div>
    							</div>
								
								<!-- ********** CHILD TYPES ********** -->
                                                                <!--
								<div class="module" id="module-info">
								
									<div class="module_content">
									
										<div class="lists form">
										
											<ul name="child_types_ul">	
											    <div class="forYeshiva">
												<? for ($ctno = 0; $ctno < count($child_types); $ctno++) : ?>
												<? $child_type = $child_types[$ctno]; ?>
												<li>
												
													<!-- CHILD TYPE -->
                                                                                                        <!--
													<span class="label">
														<? if ($child_type->school_child_type_id == ($ctno+1)) : ?>
														<script>
															increment_chckbx_count();
														</script>
														<input checked=checked name="child_type_id" type="radio" value="<?=$ctno+1?>" onclick="update_child_type_count(this, <?=$child_type->child_type_id;?>);"  /><?=$child_type->child_type_name;?>
														<? else : ?>
														<input name="child_type_id" type="radio" value="<?=$ctno+1?>" onclick="update_child_type_count(this, <?=$child_type->child_type_id;?>);" /><?=$child_type->child_type_name;?>
														<? endif; ?>
													</span>
													<!-- CHILD TYPE -->
													<!--
													<span class="toggle">
																											
														<!-- IS DEFAULT -->
													<!--<span class="label default">
															<? if ($child_type->is_default == true) : ?>
															<script>
																set_default_type_to_true();
															</script>
															<input checked=checked name="is_default" id="is_default" type="radio" onclick="update_is_default_count();" value="<?=$child_type->child_type_id;?>" />Default
															<? else : ?>
															<input name="is_default" id="is_default" type="radio" onclick="update_is_default_count();" value="<?=$child_type->child_type_id;?>" />Default															
															<? endif; ?>
														</span>
														<!-- IS DEFAULT -->
														
													<!--</span>-->
                                                                                                        <!--
												</li>
												<? endfor; ?>
								                </div>
												<li>
													<input type="submit" value="Continue" class="button"> 
												</li>
											</ul>
											
										</div>
									</div>
								</div>
                                                                -->
								<!-- ********** CHILD TYPES ********** -->
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
                  <!--
		  <script type="text/javascript"> 
	 
			 /**
			  *  Initializes filter
			  */
			 function initFilter (filter) {
				 /*
				 *  add some filtering...
				 */
				 var all = !filter || !filter.length;
	 
				 $('#layoutsContainer input').each(
					 function(num, cbx){
						 if (all) {
							 cbx.checked=true;
						 } else if (filter.match(new RegExp("(^|,)"+cbx.value+"(,|$)","i"))) {
							 cbx.checked=true;
						 }
						 cbx.onclick = setFilter;
					 });
			 }
			 /**
			  *  Collects all selected filters and changes available layouts
			  */
			 function setFilter (event) {
				 var t = this
					,filter = []
					,allChecked = true
					,allUnchecked = true
	 
				 $('#layoutsContainer input').each(
					 function(num, cbx){
						 var block = cbx.parentNode.parentNode;
						 if (cbx.checked) {
							 allUnchecked = false;
							 filter.push(cbx.value);
							 DOM.CSS(block).addClass('group_block_active');
						 } else {
							 allChecked = false;
							 DOM.CSS(block).removeClass('group_block_active');
						 }
					 });
				 VirtualKeyboard.setVisibleLayoutCodes(filter);
	 
				 // update the url
				/* if (allChecked || allUnchecked) {
					 document.location.hash = "layouts="
				 } else {
					 document.location.hash = "layouts="+filter
				 }*/
			 }
                         
			 $(function(){
	 
	 
	 
				 /*
				 *  open the keyboard
				 */
				 VirtualKeyboard.toggle('input','vk');
					$('#vk').hide();	
	 
				 /*
				 *  fill in layouts list
				 */
				 var lt = VirtualKeyboard.getLayouts()
					,dl = window.location.href.replace(/[?#].+/,"")
					,group = ""
				 for (var i=0,lL=lt.length; i<lL; i++) {
					 var cl = lt[i];
					 if (group != cl[0]) {
						 lt[i] = "";
						 if (group) {
							 lt[i]+= "</div>";
						 }
						 group = cl[0];
	 
						 lt[i]+= "<div class='group_block'>";
						 lt[i]+= "<span class='group'>"
								+"<input id='cbx_"+group+"' type='checkbox' value='"+group+"' />"
								+group+"</span>";
					 } else {
						 lt[i] = "";
					 }
					 lt[i] += "<a href=\""+dl+"?vk_layout="+cl[0]+" "+cl[1]+"\" onclick=\"VirtualKeyboard.switchLayout(this.title);return false;\" title=\""+cl[0]+" "+cl[1]+"\" >"+cl[1]+"</a>"
				 }
				 lt[i] = "</div>";
	 
				 $('#layouts').html(lt.join(""));
	 
				 initFilter((parseQuery(document.location.hash.replace(/^[#?]+/,""))['layouts'] || ""));
				 setFilter();
	 
				 /**
				  *  Toggles filtering mode
				  */
				 $('#layouts_customize div.toggle_block').mousedown(
					 function(lt){
						 var p = this.parentNode.parentNode;
						 if (DOM.CSS(p).hasClass("filtering")) {
							 DOM.CSS(p).removeClass("filtering");
						 } else {
							 DOM.CSS(p).addClass("filtering");
						 }
						 return false;
					 });
				 $('#layouts_customize div.selector_block span.select_all_layouts').mousedown(
					 function () {
						 $('#layoutsContainer input').each(
							 function(num, cbx){
								 cbx.checked=true;
							 });
						 setFilter();
					 });
				 $('#layouts_customize div.selector_block span.select_none_layouts').mousedown(
					 function () {
						 $('#layoutsContainer input').each(
							 function(num, cbx){
								 cbx.checked=false;
							 });
						 setFilter();
					 });
	 
			 });
                         
		  </script> 
                  -->
	</body>
	
</html>
