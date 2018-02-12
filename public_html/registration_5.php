<?php 
session_start();
if ( !isset( $_SESSION['hschool'] ) ) 
    header( "Location: admin.php" );
$h_school = $_SESSION['hschool'];

include("check_admin_id.php");
$next_page = "false";

include("db.php");
include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id();
$school_id = $admin->school_id;

$kiosks_added = "false";
$message = "";
if (isset($_POST["action"])) {
	$action = $_POST["action"];
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}

	if ($action == "add_kiosks") {
            
        $qty = trim($_POST['qty']);
        $year = 5774;
        
        $sql = "select * from school_accessories where school_id = " . $school_id . " and year = " . $year;
        $result = mysql_query( $sql );
        if ( mysql_num_rows($result) > 0 ) {
            $sql = "update school_accessories set scanners = " . $qty . " where school_id = " . $school_id . " and year = " . $year;
        } else {
            $sql = "insert into school_accessories values ('', $school_id, $year, $qty)";
        }
        //echo $sql;
        //exit;
        mysql_query( $sql );

	    /*
		$info1 = explode(":", $_POST["kiosks_info"]);
		
		$sql = "DELETE FROM school_kiosks WHERE school_id=" . $school_id;
		$query = mysql_query($sql);
		
		for ($no1 = 0; $no1 < count($info1); $no1++) {
			$info2 = explode(";", $info1[$no1]);
			$kiosk_type_id = $info2[0];
			$quantity = $info2[1];
			$with_dedication = $info2[2];
			
			$sql = "INSERT INTO school_kiosks SET school_id=" . $school_id . ", kiosk_type_id=" . $kiosk_type_id . ", with_dedication=" . $with_dedication . ", quantity=" . $quantity;			
			$query = mysql_query($sql);
			if (!$query) 
				$message = "<span style='color:red'>Kiosks not added. Please try again.</span>";
		}
         * 
         */
		
		if ($message == "") {
			$kiosks_added = "true";
			$next_page = "true";
		}
	}
		
}
else {
	header("https://www.mashpia.com/registration.php");
}

include("classes/kiosk_type.php");
$kiosk_types = array();
$sql = "SELECT * FROM kiosk_types";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$kiosk_type = new kiosk_type($row);
	$kiosk_type->get_school_quantity($school_id);
	array_push($kiosk_types, $kiosk_type);
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
		<script src="camps/scripts/jquery.tools.min.js"></script>
		
		<script>
			var next_page = "<?=$next_page;?>";
			var admin_id = <?=$admin_id;?>;
			var school_id = <?=$school_id;?>;
		
			var kiosk_types = new Array();
			<? for ($ktno = 0; $ktno < count($kiosk_types); $ktno++) : ?>
				kiosk_types[<?=$ktno;?>] = <?=$kiosk_types[$ktno]->kiosk_type_id;?>;
			<? endfor; ?>
			
			var kiosks_added = "<?=$kiosks_added;?>";
			
			$(function() {
				$("#nav").height($("#content").height());
			});

			function check_kiosks_added() {
				if (kiosks_added == "true") {
					window.location = "https://mashpia.com/registration_6.php";
				}
			}
			
			function get_kiosks_info() {
				var return_flag = true;
				var kiosks_info = "";
				
				for (ktno = 0; ktno < kiosk_types.length; ktno++) {
					var checkbox_1 = document.getElementById("checkbox_" + kiosk_types[ktno] + "_0");
					var checkbox_2 = document.getElementById("checkbox_" + kiosk_types[ktno] + "_1");
					var qty = document.getElementById("kiosk_" + kiosk_types[ktno] + "_qty");
					
					if (checkbox_1.checked == true) {
						if (qty.value == "") {
							return_flag = false;
							qty.focus();
							alert("You must enter a quantity");
							break;
						}
						else {
							kiosks_info = kiosks_info + kiosk_types[ktno] + ";" + qty.value + ";0:"
						}						
					}					
					else if (checkbox_2.checked == true) {
						if (qty.value == "") {
							return_flag = false;
							qty.focus();
							alert("You must enter a quantity");						
							break;
						}
						else {
							kiosks_info = kiosks_info + kiosk_types[ktno] + ";" + qty.value + ";1:"
						}
					}
										
				}
				
				if (kiosks_info.length > 0) {
					kiosks_info = kiosks_info.substr(0, (kiosks_info.length - 1));
					document.getElementById("kiosks_info").value = kiosks_info;
				}
				
				return return_flag;
			}
						
			function set_kiosks_info(checkbox_id, dedication) {
				if (dedication == 0) {
					document.getElementById("checkbox_" + checkbox_id + "_1").checked = false;
				}
				else {
					document.getElementById("checkbox_" + checkbox_id + "_0").checked = false;
				}
				
			}			
			
			function number_validation(e) {
				var unicode = e.charCode ? e.charCode : e.keyCode
							
				if  (unicode != 8 && unicode != 9) {
					if (unicode < 48 || unicode > 57) 
						return false;
				}			
			}	

			function check_next_page() {
				if (next_page == "true") {
					var registration_form_six = document.forms["registration_form_six"];
					registration_form_six.elements["admin_id"].value = admin_id;
					registration_form_six.elements["school_id"].value = school_id;
					registration_form_six.submit();
				}
			}						
		</script>
	</head>

	<body onload="check_kiosks_added();">
	
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
	 
						<? if ($message != "") : ?>
						<h1><?=$message;?></h1>
						<? endif; ?>
						
							<form action="https://mashpia.com/registration_5.php" name="kiosks_form" method="post" accept-charset="UTF-8" name="login" onsubmit="return get_kiosks_info();"> 
								<input type="hidden" name="action" value="add_kiosks">
								<input type="hidden" name="kiosks_info" id="kiosks_info" value="">
								<input type="hidden" name="school_id" value="<?=$school_id;?>">
								<input type="hidden" name="admin_id" value="<?=$admin_id;?>">
							
								<div class="infobox">
									<div class="module_content">
									      <p>In order to utilize our program you will need to purchase a scanner for each 
									      computer that you wish to setup.</p>
									      <p>Each scanner costs $50.</p>
									      <!--
										  <p>We encourage you to purchase a kiosk for your school.</p>
										  <p>We have done extensive research to be able to purchase the best quality kiosks.</p>
										  <p>We sell refurbished kiosks ranging from $770 - $1800 (plus shipping).</p>
										  <p>If you prefer you can purchase a new kiosk on your own.</p>
										  <p>We will program, setup and add signage to your new kiosk for $500.</p>
										  -->
									</div>
								</div>
								<h2>Accessories</h2> 
								
								<div class="module" id="module-info">
                                    <div class="module_content">
                                        <div class="lists form">
                                            <ul>
                                                <li>
                                                    <div class="box">
                                                        <h4>Scanners</h4>
                                                        <p>
                                                            <span class="label">Quantity:</span>
                                                            <span class="input small"><input type="text" name="qty" id="qty"></span>
                                                        </p>
                                                    </div>   
                                                </li>
                                                <li>
                                                    <input type="submit" value="Continue" class="button"> 
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!--
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul>
												<li>
													<div class="box">
														<h4>New Kiosks</h4>
														<p><a href="#">View new kiosks that are compatible with our system.</a></p>
													</div>   
												</li>
											</ul>
										</div>
									</div>
								</div>								
								
								<!-- ***** ADD KIOSKS ***** -->
								<!--
								<div class="module" id="module-info">
									<div class="module_content">
										<div class="lists form">
											<ul id="kiosk_types_ul" name="kiosk_types_ul">
												<? for ($ktno = 0; $ktno < count($kiosk_types); $ktno++) : ?>
												<li id="list_item_<?=$kiosk_types[$ktno]->kiosk_type_id;?>" name="list_item_<?=$kiosk_types[$ktno]->kiosk_type_id;?>">
													<div class="box">
														<h4><?=$kiosk_types[$ktno]->kiosk_name;?></h4>
														<p>Description.</p>
														
														<? if ($kiosk_types[$ktno]->with_dedication == 0) : ?>
															<p><input type="checkbox" checked name="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_0" id="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_0" value="0" onclick="set_kiosks_info(<?=$kiosk_types[$ktno]->kiosk_type_id;?>, 0);" />Price without dedication: $<?=$kiosk_types[$ktno]->non_ded_price;?></p>
														<? else : ?>
															<p><input type="checkbox" name="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_0" id="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_0" value="0" onclick="set_kiosks_info(<?=$kiosk_types[$ktno]->kiosk_type_id;?>, 0);" />Price without dedication: $<?=$kiosk_types[$ktno]->non_ded_price;?></p>
														<? endif; ?>
														
														<? if ($kiosk_types[$ktno]->with_dedication == 1) : ?>
															<p><input type="checkbox" checked name="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_1" id="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_1" value="1" onclick="set_kiosks_info(<?=$kiosk_types[$ktno]->kiosk_type_id;?>, 1);" />Price with dedication: $<?=$kiosk_types[$ktno]->price;?></p>
														<? else : ?>
															<p><input type="checkbox" name="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_1" id="checkbox_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_1" value="1" onclick="set_kiosks_info(<?=$kiosk_types[$ktno]->kiosk_type_id;?>, 1);" />Price with dedication: $<?=$kiosk_types[$ktno]->price;?></p>
														<? endif; ?>
														
														<span class="label"><label for="kiosk_27_qty">Quantity</label></span>
														<span class="input small"><input maxlength="1" type="text" name="kiosk_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_qty" id="kiosk_<?=$kiosk_types[$ktno]->kiosk_type_id;?>_qty" onkeypress="return number_validation(event);" value="<?=$kiosk_types[$ktno]->quantity;?>" /></span>
													</div>   
												</li>												
												<? endfor; ?>
												<li>
													<input type="submit" value="Continue" class="button"> 
												</li>
											</ul>
										</div>
									</div>
								</div>
								<!-- ***** ADD KIOSKS ***** -->
								
							</form> 
							
						</div>
					</div>
				</div>
			</div>
		</div>

	</body>
	
</html>
