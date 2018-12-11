<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$message = "";

function get_todays_julian_date() {
	$todays_day = date("j"); 
	$todays_month = date("n"); 
	$todays_year = date("Y"); 
	$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);

	return $today_jd;
}


if (isset($_POST["action"])) {
	include_once ("db.php");
	include_once ("file_save.php");
	
	$user_id = 0;
	
	$user_photo_id = "NULL";
	if (isset($_FILES['photo'])) 
		$user_photo_id = addFile($_FILES['photo'], $user_photo_id);
	 
	$counter = 0;
	$username = mysql_real_escape_string($_POST['first']) . mysql_real_escape_string($_POST['last']);
	do { 
		$counter++;
		$sql = "SELECT COUNT(*) AS number_of_usernames FROM users WHERE username='" . mysql_real_escape_string($username) . "'";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$number_of_usernames = $row['number_of_usernames'];
		if ($number_of_usernames > 0)
			$username = $username . $counter;
	} while ($number_of_usernames > 0);
	
    if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) trigger_error('could not get lock', E_USER_ERROR);
    $count = 0;
    do {
		if ($count++ > 100000) 
			trigger_error('could not get ID', E_USER_ERROR);
			
		$user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
		
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
	
	$sql = "INSERT INTO users SET ";
	$sql = $sql . " first='" . mysql_real_escape_string($_POST['first']) . "', ";
	$sql = $sql . " last='" . mysql_real_escape_string($_POST['last']) . "', ";
	$sql = $sql . " username='" . $username . "', ";
	$sql = $sql . " first_he='" . mysql_real_escape_string($_POST['first_he']) . "', "; 
	$sql = $sql . " last_he='" . mysql_real_escape_string($_POST['last_he']) . "', "; 
	$sql = $sql . " email='" . mysql_real_escape_string($_POST['email']) . "', "; 
	$sql = $sql . " gender='" . mysql_real_escape_string($_POST['gender']) . "', "; 
	$sql = $sql . " lang='" . mysql_real_escape_string($_POST['lang']) . "', "; 
	$sql = $sql . " user_address1='" . mysql_real_escape_string($_POST['address1']) . "', "; 
	$sql = $sql . " user_address2='" . mysql_real_escape_string($_POST['address2']) . "', "; 
	$sql = $sql . " user_city='" . mysql_real_escape_string($_POST['city']) . "', "; 
	$sql = $sql . " user_state='" . mysql_real_escape_string($_POST['state']) . "', "; 
	$sql = $sql . " user_postal='" . mysql_real_escape_string($_POST['postal']) . "', "; 
	$sql = $sql . " user_country='" . mysql_real_escape_string($_POST['country']) . "', "; 	
	$sql = $sql . " user_phone='" . mysql_real_escape_string($_POST['phone']) . "', "; 
	$sql = $sql . " user_serial='" . mysql_result(mysql_query("(SELECT IFNULL(MAX(user_serial), 0)+1 FROM users users_max)"), 0) . "', ";
	$sql = $sql . " user_code='" . $user_code . "', "; 
	$sql = $sql . " user_start_date=" . get_todays_julian_date() . ", "; 
	$sql = $sql . " camp_id='" . $camp_id . "' "; 
	if ($user_photo_id != "") 
		$sql = $sql . ", user_photo_id='" . $user_photo_id . "'"; 
	
	//$message = $_POST['first'] . " " . $_POST['last'] . "has been added." ;
	$query = mysql_query($sql);	
	
	if (!$query) {
		$message = '\n\n' . mysql_error() . "\n\n" . $sql . "\n\nCamper could not be added. Try again.";
	}
	else {
		$message = mysql_insert_id();
		$todays_date = get_todays_julian_date();
		$sql1 = "INSERT INTO rank_marks SET rank_ord=1, user_id=" . $user_id . ", date_promoted=" . $todays_date;
		$query1 = mysql_query($sql1);
	}
	
	echo $message;
} 
else {
?>
<script src="scripts/jquery.blockui.js"></script>

			<script>
				var action = '';
				
				$(function() {
					$.blockUI.defaults.css = {};
					$.blockUI.defaults.overlayCSS = {}; 
					
					$('#add_camper_form').ajaxForm({
						
						beforeSubmit: function(){
							if ($('#photo').value!=null) {
								alert($('#photo').value);
								$.blockUI({ message: '<h1><img src="busy.gif" /> Uploading camper photo...</h1>' });
							}
						},
						success: function(data){
							if (isNaN(data) == false) 
								$.blockUI({ message: $('#question')}); 
							else 
								$.blockUI({ message: '<h1><img src="busy.gif" /> An error occurred, please try again.</h1>',timeout: 2000 });
						}
					});
					
					$('#question #another').click(function(e) { 
						e.preventDefault();
						$.unblockUI();
						$('#add_camper_form').resetForm();
					}); 
					
					$('#question #register').click(function(e) { 
						e.preventDefault();
						passedUrl = this;
						$.unblockUI({
							onUnblock: function(){
								 slideForward(passedUrl);
							}
						});
					}); 
					
					$('form a.submit').click( function(e) {
						e.preventDefault();
						$('form').submit();			
					});
					
				});
			</script>
						
			<div class="slider">
				<div class="col_title"><span>Add a Camper</span></div>
				<div class="col_content">
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>Use this form to add individual campers to the system. <a href="content.php?output=camperbulk" class="link">Add bulk campers</a></p>
                        	<p>Once added to the system you will be able to register the camper in the program.</p>
                        </div>
                    </div>
                    								
					
                    <!--<form name="add_camper_form" id="add_camper_form" action="content.php?output=camperadd" method="post" enctype="multipart/form-data" accept-charset="UTF-8">-->
					<form name="add_camper_form" id="add_camper_form" action="includes/camper_add.php" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
					    <input type="hidden" name="action" id="action" value="add">
			
                        <div class="module form" id="module-info">
                            <h1>Add Camper</h1>
                            <div class="module_content list">
                            	<ul>
                                	<li>
                                    	<span class="label">First Name</span>
                                        <span class="input"><input type="text" name="first" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Last Name</span>
                                        <span class="input"><input type="text" name="last" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Hebrew First Name</span>
                                        <span class="input"><input type="text" name="first_he" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Hebrew Last Name</span>
                                        <span class="input"><input type="text" name="last_he" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">
											Photo
										</span>
                                        <span class="input">
											<input type="file" id="photo" name="photo" />
										</span>
                                    	<span class="tip">
											Maximum file size: 2MB. Minimum size: 180x225 (Larger is OK, the desired aspect ratio is: 1.25 times as high, as it is wide)
										</span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Email</span>
                                        <span class="input"><input type="text" name="email" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Gender</span>
                                        <span class="input">
                                        	<input type="radio" name="gender" value="NULL"> Unknown
                                            <input type="radio" name="gender" value="M" style="width: auto;" > Male
                                            <input type="radio" name="gender" value="F" style="width: auto;" > Female
                                        </span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Language</span>
                                        <span class="input">
                                        	<select name="lang" class="select">
                                            	<option>English</option>
                                                <option>עברית</option>
                                                <option>יידיש</option>	
                                            </select>
                                        </span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Address</span>
                                        <span class="input"><input type="text" name="address1" /><input type="text" name="address2" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">City</span>
                                        <span class="input"><input type="text" name="city" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">State/Province</span>
                                        <span class="input"><input type="text" name="state" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Zip/Postal code</span>
                                        <span class="input"><input type="text" name="postal" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Country</span>
                                        <span class="input"><input type="text" name="country" /></span>
                                        <div class="clear"></div>
                                    </li>
                                	<li>
                                    	<span class="label">Phone</span>
                                        <span class="input"><input type="text" name="phone" /></span>
                                        <div class="clear"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
						
                        <a href="#" title="Save" class="button submit">Save</a>
                    </form>	
					
                    <div id="question">
                    	<p>Camper successfully added.<?=$message;?></p>
                        <p><a id="another" class="button" href="#">Add Another Camper</a></p>
                        <p><a id="register" class="button" href="content.php?output=campers_register">Go to Register Campers</a></p>
                    </div>
				</div>
			</div>
			
<? } ?>
