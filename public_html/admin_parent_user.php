<?
if (!isset($_POST["child_id"]) || !isset($_POST["school_id"])) {
	header("Location: admin.php");
}

$user_id = $_POST["child_id"];
$school_id = $_POST["school_id"];

$admin_auth = array('user'); 
$ui_type = 'child';

require('header.php');
require('file_save.php');
require('calendar.php');

function clean(&$value) {
	if (is_array($value)) {
		foreach ($value as $k => &$v) {
			clean($v);
		}
	} else {
		$value = mysql_real_escape_string($value);
	}
}
clean($_POST);

$message = "";
if (isset($_POST["action"])) {
	$action = $_POST["action"];
	
	if ($action == "update") {	
		if (strlen($_POST["dob_month"]) == 1)
			$dob_month = "0" . ($_POST["dob_month"] + 1);
		else 
			$dob_month = ($_POST["dob_month"] + 1);
			
		if (strlen($_POST["dob_day"]) == 1)
			$dob_day = "0" . $_POST["dob_day"];
		else 
			$dob_day = $_POST["dob_day"];
		
		$dob = $_POST["dob_year"] . "-" . $dob_month . "-" . $dob_day;

		$sql = "UPDATE users SET first='" . mysql_real_escape_string($_POST['first'])  . 
				"', last='" . mysql_real_escape_string($_POST['last'])  . 
				"', email='" . mysql_real_escape_string($_POST['email'])  . 
				"', first_he='" . mysql_real_escape_string($_POST['first_he'])  . 
				"', last_he='" . mysql_real_escape_string($_POST['last_he'])  . 
				"', user_address1='" . mysql_real_escape_string($_POST['user_address1'])  . 
				"', user_address2='" . mysql_real_escape_string($_POST['user_address2'])  . 
				"', user_city='" . mysql_real_escape_string($_POST['user_city'])  . 
				"', user_state='" . mysql_real_escape_string($_POST['user_state']) . 
				"', user_postal='" . mysql_real_escape_string($_POST['user_postal']) . 
				"', user_country='" . mysql_real_escape_string($_POST['user_country']) . 
				"', user_phone='" . mysql_real_escape_string($_POST['user_phone']) . 
				"', dob='" . mysql_real_escape_string($dob) . "' WHERE user_id=" . $user_id;
		$query = mysql_query($sql);
		if (!$query)
			$message = "<span style='color:red;'>Child was not updated. Please try again.</span>";
		
		echo "<pre>"; print_r($_FILES); echo "</pre>"; exit;	
		if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
			$inFile = $_FILES['photo']['tmp_name'];
			$outFile = $_FILES['photo']['name'];
			$image = new Imagick($inFile);
			$image->thumbnailImage(250,200);
			$image->writeImage($outFile);
			addFile($file);
		}
	}
}


include("camps/includes/classes/user.php");
include("camps/includes/classes/school.php");
include("camps/includes/classes/school_class.php");
$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);
$user->get_school_class();

if (!empty($school_id)) {
	$classes = array();
	$sql = "SELECT * FROM classes WHERE school_id=" . $school_id;
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$class = new school_class($row);
		array_push($classes, $class);
	}
}

$months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
$this_year = date("Y");

if ($user->dob != "") {
	$dob_info = explode("-", $user->dob);
	$dob_year = $dob_info[0];
	$dob_month = $dob_info[1];
	$dob_day = $dob_info[2];
}
else {
	$message = "<span style='color:red;'>Your child has no date of birth on file. Please choose one for him/her.</span>";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=$user->first.' '.$user->last.'\'s '?><?=T_('Profile')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
			var user_id = <?=$user->user_id;?>;
			var month_days = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			
			function set_month_days(mnth_slctbx) {
				var max_days = month_days[mnth_slctbx.selectedIndex];
				var day_selectbox = document.getElementById("dob_day");
				
				if (max_days > day_selectbox.length) {
					for (dno = day_selectbox.length; dno < max_days; dno++) {
						day_selectbox.options[day_selectbox.length] = new Option((dno + 1), (dno + 1));
					}
				}
				else if (max_days < day_selectbox.length) {
					var difference = max_days - day_selectbox.length;
					for (dno = difference; dno < 0; dno++) {
						var index_number = day_selectbox.length - 1;
						day_selectbox.remove(index_number);
					}
				}
			}

			function update_user() {
				// ----- TAB ONE ----- //
				var form = document.forms["admin_parent_user_form"];  
				var first = form.elements["first"].value; 
				var last = form.elements["last"].value; 				
				var first_he = form.elements["first_he"].value; 
				var last_he = form.elements["last_he"].value; 
				var email = form.elements["email"].value; 
				var dob_year = form.elements["dob_year"].value; 
				var dob_month = (parseInt(form.elements["dob_month"].value) + 1) + ""; 
				var dob_day = form.elements["dob_day"].value; 
				if (dob_month.length == 1)
					dob_month = "0" + dob_month;				
				var dob = dob_year + "-" + dob_month + "-" + dob_day;
				
				// ----- TAB TWO ----- //
				var user_address1 = encodeURIComponent(form.elements["user_address1"].value); 
				var user_address2 = form.elements["user_address2"].value; 
				var user_city = form.elements["user_city"].value; 
				var user_state = form.elements["user_state"].value; 
				var user_postal = form.elements["user_postal"].value; 
				var user_country = form.elements["user_country"].value; 
				var user_phone = form.elements["user_phone"].value; 
				
				// ----- TAB THREE ----- //
				var class_id = form.elements["class_id"].value; 
				
				var function_name = "update_user";
				var parameters = [user_id, first, last, first_he, last_he, email, dob, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, class_id];
				var url = "edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;						
				
				$.getJSON(url, function(success) {	
					if (success == 1) {
						alert("Update successfull.");
					}
					else {
						alert("Update not performed.");
					}
				});
			}			
		</script>		
	</HEAD>
	
	<BODY>
		<? include("admin_header.php"); ?>
		<script type="text/javascript">
			$( function() {
				$("ul.tabs").tabs("div.module");
			});
			
			$('.kiosk_link').click( function(e) {
				$("#kiosk_form").submit();
			});			
		</script>		
		
		<DIV>
		
			<DIV class="body">
			
				<H1><?=$user->first.' '.$user->last.'\'s '?><?=T_('Profile')?></H1>
				
				<div class="content">
					
					
					<? if ($message != ""):?>
						<h1><?=$message;?></h1>
					<?endif;?>

					<div id="info">
					</div>
					
					<a href="#" class="kiosk_link"><?=T_('Go to Kiosk')?></a>
					<form method="post" target="_blank" id="kiosk_form" action="statement.php">
						<input type="hidden" name="new_login" />
						<input type="hidden" name="user_code" value="3<?=$user->user_code;?>" />
					</form>
					<br />
					
					<FORM name="admin_parent_user_form" id="admin_parent_user_form" action="admin_parent_user.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
						<input type="hidden" name="action" value="update">
						
						<ul class="tabs" id="infoTabs">
							<li><b><?=T_('Personal Information')?></b></li>
							<li><b><?=T_('Address')?></b></li>
							<li><b><?=T_('Activity')?></b></li>
							<!--<li><b><?=T_('Campaigns')?></b></li>-->
							<li><b><?=T_('Rank')?></b></li>
						</ul>

						<div class="panes">
						
							<div class="module">
							
								<h2><?=T_('Personal Information')?></h2>
								
								<div style="float: right; width: 200px;">
									<? if ($user->user_photo_id > 0) : ?>
									<? $str = "Change"; ?>
									<div class="photo">									
										<img width="200" alt="" src="file_view.php?id=<?=$user->user_photo_id;?>">
									</div>	
									<? else : ?>
									<? $str = "Upload"; ?>							
									<? endif; ?>
									<!--
									<p style="font-size: 14px; line-height: 1.4">
										If you would like to upload a picture or change the existing picture,<br />
										please upload a picture with the following dimensions:<br />
										250px height x 200px width.<br />
										The system will automatically crop your image to this size.<br />
										You can only upload the following file types:<br />
										'GIF', 'JPG', 'JPEG', 'PNG'<br /><br />
									</p>
									
									<?=$str?> photo:<br />
									<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
									<input type="file" name="photo" /><br />
									-->
								</div>
								
								<div>
									Barcode #<br />
									<span style="font-size: 125%; font-weight: bold;">3<?=$user->user_code;?></span>
									<br />
												
									Serial #
									<br />
									<span style="font-size: 125%; font-weight: bold;"><?=$user->user_serial;?></span>
									<br />
							
									<label>
										<?=T_('First Name')?>
										<br>
										<input type="text" maxlength="128" value="<?=$user->first;?>" name="first">
									</label>						
									<br />
						
									<label>
										<?=T_('Last Name')?>
										<br>
										<input type="text" maxlength="128" value="<?=$user->last;?>" name="last">
									</label>						
									<br />
								
									<label>
										<?=T_('Hebrew First Name')?>
										<br>
										<input type="text" maxlength="128" value="<?=$user->first_he;?>" name="first_he">
									</label>						
									<br />
								
									<label>
										<?=T_('Hebrew Last Name')?>
										<br>
										<input type="text" maxlength="128" value="<?=$user->last_he;?>" name="last_he">
									</label>						
									<br />
								
									<label>
										<?=T_('Email')?>									
										<br>
										<input type="text" maxlength="255" value="<?=$user->email;?>" name="email">
									</label>					
									<br />
									
									<?=T_('Date of birth')?>											
									<input type="hidden" name="dob" value="<?=$user->dob;?>">
									<br>
									<SELECT name="dob_month" onchange="set_month_days(this);">
										<? for ($mno = 0; $mno < count($months); $mno++) : ?>
										<? if ($dob_month == ($mno + 1)) $selected = " selected "; else $selected = ""; ?>
										<OPTION <?=$selected;?> value="<?=$mno;?>"><?=$months[$mno];?></OPTION>
										<? endfor; ?>
									</SELECT>
										
									<SELECT name="dob_day" id="dob_day">
										<? for ($dno = 1; $dno < 32; $dno++) : ?>
										<? if ($dob_day == $dno) $selected = " selected "; else $selected = ""; ?>
										<OPTION <?=$selected;?> value="<?=$dno;?>"><?=$dno;?></OPTION>
										<? endfor; ?>
									</SELECT>							
										
									<SELECT name="dob_year">
										<? for ($yno = ($this_year - 14); $yno < ($this_year + 14); $yno++) : ?>
										<? if ($dob_year == $yno) $selected = " selected "; else $selected = ""; ?>
										<OPTION <?=$selected;?> value="<?=$yno;?>"><?=$yno;?></OPTION>
										<? endfor; ?>							
									</SELECT>
								</div>
						</div>
						

						<div class="module">
						<h2><?=T_('Address')?></h2>
						<label>
							<?=T_('Address 1')?>
							<br>
							<input type="text" maxlength="255" value="<?=$user->user_address1;?>" name="user_address1">
						</label>
						<br />
						
						<label>
							<?=T_('Address 2')?>
							<br>
							<input type="text" maxlength="255" value="<?=$user->user_address2;?>" name="user_address2">
						</label>
						<br />
						
						<label>
							<?=T_('City')?>
							<br>
							<input type="text" maxlength="255" value="<?=$user->user_city;?>" name="user_city">
						</label>
						<br />
						
						<label>
							<?=T_('State/Province')?>
							<br>
							<input type="text" maxlength="255" value="<?=$user->user_state;?>" name="user_state">
						</label>
						<br />
						
						<label>
							<?=T_('Zip/Postal code')?>
							<br>
							<input type="text" maxlength="255" value="<?=$user->user_postal;?>" name="user_postal">
						</label>
						<br />
						
						<label>
							<?=T_('Country')?>
							<br>
							<input type="text" maxlength="255" value="<?=$user->user_country;?>" name="user_country">
						</label>
						<br />
						
						<label>
							<?=T_('Phone')?>
							<br>
							<input type="text" maxlength="255" value="<?=$user->user_phone;?>" name="user_phone">
						</label>
						</div>


						<div class="module">						
						<h2><?=T_('Activity')?><h2>
						
						<div align='left'>
							<?=T_('Member Since')?>
							<br />
							<span style="font-size: 125%; font-weight: bold;">
								<?=dateToHebrew($user->user_start_date);?>
							</span>
							<br /><br />
							
							<? if ($user->user_registered > 0) : ?>
								Registered
								<BR>
								<SPAN style="font-size: 125%; font-weight: bold;">
									<?=$user->user_registered;?>
								</SPAN>
								<BR><br />
							<? else : ?>
								Not currently registered
								<br /><br />
							<? endif; ?>
							
							<LABEL>
								Platoon
								<BR>
	
								<SELECT name="class_id">
									<? for ($cno = 0; $cno < count($classes); $cno++) : ?>
										<? if ($user->school_class->class_id == $classes[$cno]->class_id) $selected = " selected "; else $selected = ""; ?>
										<? if ($classes[$cno]->class_sub != "") : ?>
										<OPTION <?=$selected;?> value="<?=$classes[$cno]->class_id;?>"><?=$classes[$cno]->class_grade;?>-<?=$classes[$cno]->class_sub;?></OPTION>								
										<? else : ?>
										<OPTION <?=$selected;?> value="<?=$classes[$cno]->class_id;?>"><?=$classes[$cno]->class_grade;?></OPTION>
										<? endif; ?>
									<? endfor; ?>
								</SELECT>
							</LABEL>
							
						</div>										
						</div>
						<!--
						<div class="module">
							<h2><?=T_('Campaigns')?></h2>
							<BR />
							<a href="admin_user_track.php?user_id=<?=$user->user_id?>">View Campaigns</a>										
						</div>
						-->
						<div class="module">
							<? 
							$rank_sql = "SELECT * FROM rank_marks JOIN ranks AS r USING(rank_ord) WHERE user_id=" . $user->user_id . " ORDER BY rank_ord"; 
							$rank_query = mysql_query($rank_sql);											
							?>
							<h2><?=T_('Rank')?></h2>
							<TABLE WIDTH="50%">
							<? while ($rank_row = mysql_fetch_assoc($rank_query)) : ?>
								<TR>
									<TD align="left">
										<span style="color:<?=$rank_row["rank_color"];?>;"><?=$rank_row["rank_name"];?></span>
									</TD>
									<TD align="right">
										<span style="color:<?=$rank_row["rank_color"];?>;"><?=jdtogregorian($rank_row["date_promoted"]);?></span>
									</TD>
								</TR>
							<? endwhile; ?>
							</TABLE>
						</div>
						
						<!--<input type="submit" value="UPDATE">-->
						<input type="button" value="UPDATE" onclick="update_user();">
						
					</FORM>

				</div>
			
			</DIV>
			
		</DIV>
		
		<? include("admin_footer.php"); ?>
	</BODY>
	
</HTML>
