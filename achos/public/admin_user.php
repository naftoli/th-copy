<? 
$admin_auth = array('school'); 
require('header.php');
require_once('file_save.php');
require_once('calendar.php');
$ui_type = 'school';
require_once('admin_ui.php');

$action = gr('action');
//assure_id_school('school_id');
//$school_id = gri('school_id', -1);
$school_id = 1;
$class_id = gri('class_id', -1);
$edit_row = false;

$search_user_serial = gr('search_user_serial');
$search_first = gr('search_first'); 
$search_last = gr('search_last');
$search_class_id = gri('search_class_id', -1);
$search_user_registered = gri('search_user_registered', 0);
$search_user_unregistered = gri('search_user_unregistered', 0);

if(!empty($action)) {

	switch($action) {
	
		case 'add':
			$result = mq("SELECT -1 user_id, '' email, '' child_type_id, '' first, '' last, '' first_he, '' last_he, '' lang, $class_id class_id, NULL school_type_id, NULL team_id, '' user_address1, '' user_address2, '' user_city, '' user_state, '' user_postal, '' user_country, '' user_phone, NULL gender, NULL dob, '' kiosk_edit, NULL user_photo_id, NULL user_registered, 0 parent_marking");
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'add2':
			$username = mb_strtolower(mb_substr(gr('first'),0,1)) . preg_replace('/\P{L}/u', '', mb_strtolower(gr('last')));
			$count = '';
			
			while (mysql_num_rows(mq('SELECT username FROM users WHERE username = ' . ms($username.$count)))) 
				$count++;
			
			$username .= $count;

			$user_photo_id = 'NULL';
			
			if (isset($_FILES['photo'])) 
				$user_photo_id = addFile($_FILES['photo'], $user_photo_id);

			if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
				trigger_error('could not get lock', E_USER_ERROR);
				
			$count = 0;
			do {
				if ($count++ > 100000) 
					trigger_error('could not get ID', E_USER_ERROR);
					
				$user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
				
			} while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
            
			$school_type = 1;
			$sql = "INSERT INTO users SET user_code = $user_code, school_id = $school_id, username = " . ms($username) . ', email = ' . ms(gr('email')) . ',child_type_id = ' . ms(gr('child_type')) . ', first = ' . ms(gr('first')) . ', last = ' . ms(gr('last')) . ', first_he = ' . ms(gr('first_he')) . ', last_he = ' . ms(gr('last_he')) . ', lang = ' . ms(gr('lang')) . ', user_serial = ' . mysql_result(mq("(SELECT IFNULL(MAX(user_serial), 0)+1 FROM users users_max)"), 0) . ', user_address1 = ' . ms(gr('address1')) . ', user_address2 = ' . ms(gr('address2')) . ', user_city = ' . ms(gr('city')) . ', user_state = ' . ms(gr('state')) . ', user_postal = ' . ms(gr('postal')) . ', user_country = ' . ms(gr('country')) . ', user_phone = ' . ms(gr('phone')) . ', kiosk_edit = ' . ms(gr('kiosk_edit')) . ', class_id = ' . nullif(gri('class_id', -1), -1) . ', school_type_id = ' . $school_type . ', team_id = ' . nullif(gri('team_id', -1), -1) . (gri('user_registered', 0) ? ', user_registered = NOW(), user_start_date = ' . unixtojd() : '') . ', gender = ' . nullif_ms((gr('gender') != 'M' && gr('gender') != 'F' ? 'NULL' : gr('gender')), 'NULL') . ', dob = ' . nullif_ms(gr('dob'), '') . ", user_photo_id = $user_photo_id" . (gr('password') ? ', password = ' . ms(gr('password')) : '');
			mq( $sql ) or die( $sql . mysql_error() );
			$new_user_id = mysql_insert_id();
			header_update_icorpa_student(array(
				"legacy_user_id" => $new_user_id
			));
		
			mq("SELECT RELEASE_LOCK('users')");

			mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, $new_user_id user_id, " . unixtojd() . ' date_promoted FROM ranks WHERE medals_required <= 0');
			
			$ins = "insert into user_tracks values ($new_user_id, 1, 1, 1, 0)";
			mysql_query($ins) or die( $ins . mysql_error() );              

			$message = T_('Soldier added');
		break;

		case 'delete':
			$user_id = gri('user_id', -1);
			/*
			if (mysql_result(mq(totalMarks("WHERE user_id = $user_id")), 0) == 0) {
				mq("DELETE FROM files USING files JOIN users ON (files.file_id = users.user_photo_id) WHERE user_id = $user_id AND school_id = $school_id");
				mq("DELETE FROM users WHERE user_id = $user_id AND school_id = $school_id");
				mq("DELETE FROM user_tracks USING user_tracks JOIN users USING (user_id) WHERE user_id = $user_id AND school_id = $school_id");
				mq("DELETE FROM admin_auths USING admin_auths JOIN users ON (id = user_id) WHERE auth = 'user' AND id = $user_id AND school_id = $school_id");
				mq("DELETE FROM medal_marks USING medal_marks JOIN users USING (user_id) WHERE user_id = $user_id AND school_id = $school_id");
				mq("DELETE FROM rank_marks USING rank_marks JOIN users USING (user_id) WHERE user_id = $user_id AND school_id = $school_id");
				$message = T_('Soldier deleted');
			} 
			else {
				$message = T_("Can't delete, Soldier has points");
			}
			*/
			$adminSql = "select a.admin_id from admins a 
						 join admin_auths aa using (admin_id) 
						 where aa.id = " . $user_id;
			$adminRes = mysql_query($adminSql);
			$admin_Row = mysql_fetch_assoc($adminRes);
			$admin_id = $admin_Row['admin_id'];
			//delete user points, user account, admin account, admin-user relationship
			mysql_query("set autocommit=0");
			mysql_query("begin");
			$success = false;
//			if (mysql_query("delete from date_tasks_mission_marks where user_id = " . $user_id)) {
//				if (mysql_query("delete from date_tasks_marks where user_id = " . $user_id)) {
//					if (mysql_query("delete from user_tracks where user_id = " . $user_id)) {
//						if (mysql_query("delete from users where user_id = " . $user_id)) {
//							if (mysql_query("delete from admins where admin_id = " . $admin_id)) {
//								if (mysql_query("delete from admin_auths where admin_id = " . $admin_id . " and id = " . $user_id)) {
//									mysql_query("commit");
//									$message = T_('Soldier deleted.');
//									$success = true;
//								}
//							}
//						}
//					}
//				}
//			}
			if (!$success) {
				mysql_query("rollback");
				$message = T_("Soldier could not be deleted.");
			}
			mysql_query("set autocommit=1");
		break;

		case 'remove':
			$user_id = gri('user_id', -1);
			mq("UPDATE users SET school_id = NULL, class_id = NULL, team_id = NULL WHERE user_id = $user_id AND school_id = $school_id");
			$message = T_('Soldier removed from school');
		break;

		case 'edit':
			$result = mq('SELECT user_id, user_code, username, email, first, last, first_he, last_he, lang, class_id, school_type_id, team_id, user_serial, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, kiosk_edit, dob, gender, user_photo_id, user_registered, user_start_date, child_type_id, parent_marking FROM users WHERE user_id = ' . gri('user_id', -1) . " AND school_id = $school_id");
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'edit2':
			$user_photo_id = gri('photo_delete', 0) ? 'NULL' : 'user_photo_id';
			if (isset($_FILES['photo'])) 
				$user_photo_id = addFile($_FILES['photo'], $user_photo_id);

			if ($user_photo_id !== 'user_photo_id') 
				mq('DELETE FROM files USING files JOIN users ON (files.file_id = users.user_photo_id) WHERE user_id = ' . gri('user_id', -1));

			$reg = '';
			if (gri('user_registered', 0)) {
				$reg = ', user_registered = NOW(), user_start_date = IFNULL(user_start_date, ' . unixtojd() .')';
			} 
			elseif ($admin_user['auth'] == 'super' && gri('user_registered_not', 0)) {
				$reg = ', user_registered = NULL, user_start_date = IF(user_start_date > ' . (unixtojd()-10) . ', NULL, user_start_date)';
			}
			
            //if ( !$h_school ) { 
    			//insert school type
    			$school_type = null;
    			$type = $_POST['child_type'];
    			$gender = $_POST['gender'];
    			switch ($type) {
    				case 1:
    					if ($gender == 'M') $school_type = 2;
    					else $school_type = 3;
    					break;
    				case 2:
    					if ($gender == 'M') $school_type = 12;
    					else $school_type = 13;
    					break;
    			}
    			//////////mq('UPDATE users SET email = ' . ms(gr('email')) . ', first = ' . ms(gr('first')) . ', last = ' . ms(gr('last')) . ', first_he = ' . ms(gr('first_he')) . ', last_he = ' . ms(gr('last_he')) . ', lang = ' . ms(gr('lang')) . ', user_address1 = ' . ms(gr('address1')) . ', user_address2 = ' . ms(gr('address2')) . ', user_city = ' . ms(gr('city')) . ', user_state = ' . ms(gr('state')) . ', user_postal = ' . ms(gr('postal')) . ', user_country = ' . ms(gr('country')) . ', user_phone = ' . ms(gr('phone')) . ', kiosk_edit = ' . ms(gr('kiosk_edit')) . ', class_id = ' . nullif(gri('class_id', -1), -1) . ', school_type_id = ' . gri('school_type_id', -1) . ', team_id = ' . nullif(gri('team_id', -1), -1) . $reg . ', dob = ' . nullif_ms(gr('dob'), '') . ', gender = ' . nullif_ms((gr('gender') != 'M' && gr('gender') != 'F' ? 'NULL' : gr('gender')), 'NULL') . ", user_photo_id = $user_photo_id" . (gr('password') ? ', password = ' . ms(gr('password')) : '') . ' WHERE user_id = ' . gri('user_id', -1) . " AND school_id = $school_id");
    			mq('UPDATE users SET school_type_id = ' . $school_type . ', email = ' . ms(gr('email')) . ', first = ' . ms(gr('first')) . ', last = ' . ms(gr('last')) . ', first_he = ' . ms(gr('first_he')) . ', last_he = ' . ms(gr('last_he')) . ', lang = ' . ms(gr('lang')) . ', user_address1 = ' . ms(gr('address1')) . ', user_address2 = ' . ms(gr('address2')) . ', user_city = ' . ms(gr('city')) . ', user_state = ' . ms(gr('state')) . ', user_postal = ' . ms(gr('postal')) . ', user_country = ' . ms(gr('country')) . ', user_phone = ' . ms(gr('phone')) . ', kiosk_edit = ' . ms(gr('kiosk_edit')) . ', class_id = ' . nullif(gri('class_id', -1), -1) . ', child_type_id = ' . $_POST['child_type'] . ', team_id = ' . nullif(gri('team_id', -1), -1) . $reg . ', dob = ' . nullif_ms(gr('dob'), '') . ', gender = ' . nullif_ms((gr('gender') != 'M' && gr('gender') != 'F' ? 'NULL' : gr('gender')), 'NULL') . ", user_photo_id = $user_photo_id" . (gr('password') ? ', password = ' . ms(gr('password')) : '') . ' WHERE user_id = ' . gri('user_id', -1) . " AND school_id = $school_id");
                
                //add birthday mission/task if not exists
                //require_once 'class.birthday.php';
                //$b = new Birthday( gri('user_id', -1) );
                //$b->setBirthday();
                
            //} else {
            //    mq('UPDATE users SET email = ' . ms(gr('email')) . ', first = ' . ms(gr('first')) . ', last = ' . ms(gr('last')) . ', first_he = ' . ms(gr('first_he')) . ', last_he = ' . ms(gr('last_he')) . ', lang = ' . ms(gr('lang')) . ', user_address1 = ' . ms(gr('address1')) . ', user_address2 = ' . ms(gr('address2')) . ', user_city = ' . ms(gr('city')) . ', user_state = ' . ms(gr('state')) . ', user_postal = ' . ms(gr('postal')) . ', user_country = ' . ms(gr('country')) . ', user_phone = ' . ms(gr('phone')) . ', class_id = ' . nullif(gri('class_id', -1), -1) . ', team_id = ' . nullif(gri('team_id', -1), -1) . $reg . ', dob = ' . nullif_ms(gr('dob'), '') . ', gender = ' . nullif_ms((gr('gender') != 'M' && gr('gender') != 'F' ? 'NULL' : gr('gender')), 'NULL') . ", user_photo_id = $user_photo_id" . (gr('password') ? ', password = ' . ms(gr('password')) : '') . ' WHERE user_id = ' . gri('user_id', -1) . " AND school_id = $school_id");                
            //}
            
            //header_update_icorpa_student(array(
            //    "legacy_user_id" => gri('user_id', -1)
            //));
			$message = T_('Soldier edited');
		break;

		case 'export_users':
			require_once('export.php');
			$qry = "SELECT class_grade, class_sub, class_teacher, username, email, 
				first, last, first_he, last_he, user_serial, user_address1, user_address2, user_city, user_state, user_postal, user_country, 
				user_phone, gender, user_start_date, user_registered as date_registered, dob, rank_name as rank 
				FROM users LEFT JOIN classes USING (class_id, school_id) 
				join rank_marks using (user_id) 
				join ranks using (rank_ord) 
				WHERE school_id = $school_id" . ($search_first !== '' ? ' AND first LIKE ' . ms("$search_first%") : '') . 
				($search_user_serial !== '' ? ' AND user_serial = ' . intval($search_user_serial) : '') . 
				($search_last !== '' ? ' AND last LIKE ' . ms("$search_last%") : '') . 
				($search_class_id != -1 ? " AND class_id = $search_class_id" : '') . 
				($search_user_registered ? ' AND (user_registered IS NOT NULL and user_registered > 0)' : '') . 
				($search_user_unregistered ? ' AND (user_registered IS NULL or user_registered = 0)' : '') . " 
				ORDER BY class_grade, class_sub, last, first, username, user_id";
			export($qry, 'soldiers');
			exit;
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
	
	}
	
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=$action == 'add' ? T_('Add Soldier') : ($action == 'edit' ? T_('Edit Soldier') : T_('View Soldiers')), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
		<!--
		.photo {float:right;}
		-->
		</style>
		
		<script>
			function submit_form(user_code)
			{
				document.kiosk_form.elements["user_code"].value = "3" + user_code;
				document.kiosk_form.submit();
			}
		</script>

		<script>
			function validateForm() { 
				var fname = document.form1.first.value;
				var lname = document.form1.last.value;
				var gender1 = document.form1.gender[0].checked;
				var gender2 = document.form1.gender[1].checked;
				var dob = document.form1.dob.value;
				var cls = document.form1.class_id.value;
				
				var type = document.form1.child_type.value;
								    
				if (fname == null || fname == "" || lname == null || lname == "" || dob == null || dob == "" || cls == "" || (!gender1 && !gender2) || type == "") {
					alert('You must fill out first name, last name, gender, dob, grade (under Platoon Tab), and mission type (under Settings Tab)');
					return false;
				} else
					return true;
					
			}
		</script>
		<!--
		<script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
		<script type="text/javascript">
			$( function() {
				$(".submit").click( function() { 
					var errors = new Array();
					if ( $("#fname").val() == "" ) {
						errors.push("First Name is Mandatory.\n");
					}
					if ( $("#lname").val() == "" ) {
						errors.push("Last Name is Mandatory.\n");
					}
					alert( $(".gender").val() );
					if ( $("#dob").val() == "" ) {
						errors.push("Gender is Mandatory.\n");
					}
					if ( $("#platoon").val() == "" ) {
						errors.push("please choose a Platoon.\n");
					}
					if ( $("#type").val() == "" ) {
						errors.push("please choose the child type.");
					}
					alert( errors );
				});
			});
		</script>								
		-->
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		<script>
			$(function(){
				$("ul.tabs").tabs("div.module");

			});
		</script>		
		
		<form method="post" action="statement.php" name="kiosk_form" id="kiosk_form">
			<input type="hidden" name="new_login" />
			<input type="hidden" name="user_code" id="user_code" value="" />
		</form>	
											
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">				
					<? if (!empty($message)):?>
						<H2><?=$message?></H2>
					<?endif;?>				
				</DIV>
				
				<H1><?=T_('Base Management')?></H1>
				<!--
				<? if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : ?>
					<? $sql = 'SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'; ?>
					<? $school_result = mq($sql); ?>
					<FORM action="admin_user.php" method="get" accept-charset="UTF-8">
						<P>
							<LABEL>
								<?=T_('Select Institution')?>: 
								<SELECT name="school_id">
									<? while($school_row = mysql_fetch_assoc($school_result)) : ?>
									<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
									<?endwhile;?>
								</SELECT>
							</LABEL>
							<INPUT type="hidden" name="action" value="<?=$action?>">
							<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
						</P>
					</FORM>
				<?endif;?>
				-->
				<? if ($school_id == -1) : ?>
					<?=T_('Please select an Institution.')?>
				<? else : ?>
					<DIV class="ui_body">
					
						<DIV class="ui_menu">
							<?ui_menu();?>
						</DIV>
						
						<DIV class="content">
						
							<H1>
								<?=$action == 'add' ? T_('Add Soldier') : ($action == 'edit' ? T_('Edit Soldier') : T_('View Soldiers'))?>
							</H1>
							
							<? if ($edit_row) : ?>
								<?=$action == 'add' ? "<DIV class='infobox'>" . T_("NOTE: Adding a Soldier's name is for your own records only and does not register him/her in TH. (To register a child, please choose Students->Registration from the menu.)") . "</DIV>" : ''?>

								<FORM name='form1' action="admin_user.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data" 
								<? if ($school_id != 79) { ?>onSubmit="return validateForm();<?}?>//<?/*=!$edit_row ? "if(this.elements['password'].value == '') { alert('" . esq(T_('Please enter a password for this user.')) . "'); } else " : ''*/?> { if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); } else { return true; } } this.elements['password'].focus(); return false;">
									<P CLASS="rows">
										<INPUT type="hidden" name="action" value="<?=$action?>2">
										<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
										<!--<INPUT type="hidden" name="class_id" value="<?=$class_id?>">-->
										<INPUT type="hidden" name="user_id" value="<?=$edit_row['user_id']?>">
						<ul class="tabs">
							<li><b><?=T_('Personal')?></b></li>
							<li><b><?=T_('Address')?></b></li>
							<li><b><?=T_('Platoon')?></b></li>
							<? if ( !$h_school ) { ?>
							<li><b><?=T_('Settings')?></b></li>
							<? } ?>
							<li><b><?=T_('Registration')?></b></li>
							<? if ( !$h_school && $action != 'add' ) { ?> 
							<li><b><?=T_('Campaigns')?></b></li>
							<li><b><?=T_('Rank')?></b></li>
							<? } ?>
						</ul>
						<div class="panes">
						<div class="module">

										<h2>
											<?=T_('Personal Information')?>
										</h2>
										
										<div class="photo">
											<?=linkImgFile($edit_row['user_photo_id'], 200)?>
										</div>
										
		
										<? if ($action=='edit') : ?>
										<?=T_('Serial #')?><BR><SPAN style="font-size: 125%; font-weight: bold;"><?=es($edit_row['user_serial'])?></SPAN><BR>
										<?=T_('Barcode #')?><BR><SPAN style="font-size: 125%; font-weight: bold;">3<?=es($edit_row['user_code'])?></SPAN><BR>
										<? endif; ?>
										
										<LABEL>*<?=T_('First Name')?><BR><INPUT TYPE="text" NAME="first" id="fname" VALUE="<?=es($edit_row['first'])?>" MAXLENGTH="128"></LABEL><BR>
										<LABEL>*<?=T_('Last Name')?><BR><INPUT TYPE="text" NAME="last" id="lname" VALUE="<?=es($edit_row['last'])?>" MAXLENGTH="128"></LABEL><BR>
										<LABEL><?=T_('Hebrew First Name')?><BR><INPUT TYPE="text" NAME="first_he" VALUE="<?=es($edit_row['first_he'])?>" MAXLENGTH="128"></LABEL><BR>
										<LABEL><?=T_('Hebrew Last Name')?><BR><INPUT TYPE="text" NAME="last_he" VALUE="<?=es($edit_row['last_he'])?>" MAXLENGTH="128"></LABEL><BR>
										<LABEL><?=T_('Photo')?><BR><?=T_('Minimum size')?>: 180x225 (<?=sprintf(T_('Larger is OK, the desired aspect ratio is: %s times as high, as it is wide'), 1.25)?>) <BR><INPUT type="file" name="photo" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
										
										<? if (!is_null($edit_row['user_photo_id'])) : ?>
										<?=T_('Uploading a new photo will replace the old.')?><BR>
										<LABEL>
											<?=T_('Delete current photo')?> 
											<INPUT type="checkbox" name="photo_delete" class="checkbox" value="1">
											<BR>
										</LABEL>
										<? endif; ?>
										
										<LABEL>
											<?=T_('Parent Email')?>
											<BR>
											<INPUT TYPE="text" NAME="email" VALUE="<?=es($edit_row['email'])?>" MAXLENGTH="255" id="email">
										</LABEL>
										
										<BR>
										*<?=T_('Gender')?>
										<BR>
										
										<!--<LABEL><INPUT type="radio" name="gender" value="NULL" <?= is_null($edit_row['gender']) ? 'CHECKED' : ''?> style="width: auto;"> <?=T_('Unknown')?></LABEL>-->
										<LABEL><INPUT type="radio" name="gender" class="gender" value="M" <?= $edit_row['gender'] == 'M' ? 'CHECKED' : ''?> style="width: auto;"> <?=T_('Male')?></LABEL>
										<LABEL><INPUT type="radio" name="gender" class="gender" value="F" <?= $edit_row['gender'] == 'F' ? 'CHECKED' : ''?> style="width: auto;"> <?=T_('Female')?></LABEL>
										<BR>
										
										<LABEL>
											*<?=T_('Date of birth')?>
											<BR>
											<INPUT TYPE="text" NAME="dob" id="dob" VALUE="<?=$edit_row['dob']?>" MAXLENGTH="10" onChange="if(this.value != '') {var str = this.value.replace(/\D/g, '')+'00000000'; this.value = str.substring(0, 4) + '-' + str.substring(4, 6) + '-' +  str.substring(6, 8);}"> (YYYY-MM-DD)
										</LABEL>
										
										<BR>
										
						</div>
						<div class="module">

										<h2><?=T_('Address')?></h2>
										<LABEL><?=T_('Address 1')?><BR><INPUT type="text" name="address1" value="<?=es($edit_row['user_address1'])?>" maxlength=255></LABEL><BR>
										<LABEL><?=T_('Address 2')?><BR><INPUT type="text" name="address2" value="<?=es($edit_row['user_address2'])?>" maxlength=255></LABEL><BR>
										<LABEL><?=T_('City')?><BR><INPUT type="text" name="city" value="<?=es($edit_row['user_city'])?>" maxlength=255></LABEL><BR>
										<LABEL><?=T_('State/Province')?><BR><INPUT type="text" name="state" value="<?=es($edit_row['user_state'])?>" maxlength=255></LABEL><BR>
										<LABEL><?=T_('Zip/Postal code')?><BR><INPUT type="text" name="postal" value="<?=es($edit_row['user_postal'])?>" maxlength=255></LABEL><BR>
										<LABEL><?=T_('Country')?><BR><INPUT type="text" name="country" value="<?=es($edit_row['user_country'])?>" maxlength=255></LABEL><BR>
										<LABEL><?=T_('Phone')?><BR><INPUT type="text" name="phone" value="<?=es($edit_row['user_phone'])?>" maxlength=255></LABEL><BR>

						</div>
						
						<div class="module">
										
										<h2><?=T_('Platoon')?></h2>
										
										<LABEL>
											*<?=T_('Platoon')?>
											<BR>
											<? $result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub"); ?>
											<SELECT name="class_id" id="platoon">
												<OPTION VALUE="">&lt;<?=T_('N/A')?>&gt;</OPTION>
												<? while ($row = mysql_fetch_assoc($result)) : ?>
												<OPTION VALUE="<?=$row['class_id']?>" <?=$row['class_id'] == $edit_row['class_id'] ? 'SELECTED' : '' ?>><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></OPTION>
												<? endwhile; ?>
											</SELECT>
										</LABEL>
										
										<BR>
										
										<!--
										<LABEL>
											<?//=T_('Squad')?>
											<BR>
											<? //$result = mq("SELECT team_id, team_name FROM teams WHERE school_id = $school_id ORDER BY team_name"); ?>
											<SELECT name="team_id">
												<OPTION VALUE="-1">&lt;<?//=T_('N/A')?>&gt;</OPTION>
												<?// while($row = mysql_fetch_assoc($result)): ?>
												<OPTION VALUE="<?//=$row['team_id']?>" <?//=$row['team_id'] == $edit_row['team_id'] ? 'SELECTED' : '' ?>><?//=es($row['team_name'])?></OPTION>
												<? //endwhile; ?>
											</SELECT>
										</LABEL>
										-->
										
										<BR>
						</div>

						<div class="module">

										<h2><?=T_('Settings')?></h2>
										
										<!--
										<?//=es(T_('Kiosk Mission & Task entry'))?>
										
										<BR>
										
										<LABEL>
											<INPUT type="radio" name="kiosk_edit" value="" <?=$edit_row['kiosk_edit'] === '' ? 'CHECKED' : ''?>><?=T_('Enabled')?>
										</LABEL>
										
										<LABEL>
											<INPUT type="radio" name="kiosk_edit" value="off" <?=$edit_row['kiosk_edit'] === 'off' ? 'CHECKED' : ''?>><?=T_('Disabled')?>
										</LABEL>
										
										<LABEL>
											<INPUT type="radio" name="kiosk_edit" value="frozen" <?=$edit_row['kiosk_edit'] === 'frozen' ? 'CHECKED' : ''?>><?=T_('Frozen')?>
										</LABEL>
										
										<BR>
										<BR>
-->										
										<label>
											*<?=T_('Mission Type')?>
											<br />
											<select name='child_type' id="type">
											<?
											if ($action == 'add') echo "<option value=''>Please choose</option>";
											$child_type = $edit_row['child_type_id'];
											$child_types = array();
											$sql = "select * from child_types order by child_type_id";
											$result = mysql_query($sql);
											while ($row = mysql_fetch_assoc($result)) {
												if ($row['child_type_id'] == 3) break;
												$child_types[$row['child_type_id']] = $row['child_type_name'];
											}											
											foreach ($child_types as $k => $v) {
												if ($k == $child_type)
													echo "<option value='$k' selected='selected'>$v</option>";
												else 
													echo "<option value='$k'>$v</option>";
											}
											?>
											</select>
										</label>
										<br />
										<br />
											
										<LABEL>
											<?=T_('Language')?>
											<BR>
											<SELECT NAME="lang">
											<?php
												$langs = array();
												$langSql = "select * from languages";
												$langRes = mysql_query($langSql);
												while ($langRow = mysql_fetch_assoc($langRes)) {
													$langs[$langRow['lang_id']] = $langRow['language'];
												}
												foreach($langs as $lang_id => $lang_name) {
													echo "<OPTION value='$lang_id'" . ($lang_id == $edit_row['lang'] ? ' SELECTED' : '') . ">" . es($lang_name);
												}
											?>
											</SELECT>
										</LABEL>
										<!--
										<BR>
										<BR>
										
										<LABEL>
											Allow Parent(s) to Mark
											<? if ($edit_row["parent_marking"] == 0) : ?>
											<INPUT type="radio" name="parent_marking" value="0" CHECKED>No
											<? else : ?>
											<INPUT type="radio" name="parent_marking" value="0">No
											<? endif; ?>
											
											<? if ($edit_row["parent_marking"] == 1) : ?>
											<INPUT type="radio" name="parent_marking" value="1" CHECKED>Yes
											<? else : ?>
											<INPUT type="radio" name="parent_marking" value="1">Yes
											<? endif; ?>
										</LABEL>
										-->
						</div>
						
						<div class="module">
						
							<h2><?=T_('Registration')?></h2>
							
							<input type="hidden" name="ACTION" value="<?=$action;?>">
							
							<? if ($action == 'edit') : ?>
								<?=T_('Member Since')?>
								<BR>
								<SPAN style="font-size: 125%; font-weight: bold;">
									<?=dateToHebrew($edit_row['user_start_date'])?>
								</SPAN>
								<BR>
							<? endif; ?>

							<input type="hidden" name="USER REGISTERED" value="<?=$edit_row['user_registered'];?>">
							
							<? if (is_null($edit_row['user_registered'])) : ?>
								<!-- <LABEL><?=T_('Register?')?><BR><INPUT type="checkbox" name="user_registered" value="1"></LABEL><BR> -->
							<? else: ?>
								<?=T_('Registered')?>
								<BR>
								<SPAN style="font-size: 125%; font-weight: bold;">
									<?=es($edit_row['user_registered'])?>
								</SPAN>
								<BR>
								<? if($admin_user['auth'] == 'super'): ?>
									<LABEL>
										<?=T_('Un-Register?')?>
										<BR>
										<INPUT type="checkbox" name="user_registered_not" value="1">
									</LABEL>
									<BR>
								<? endif; ?>
							<? endif; ?>

						</div>
						
						<? if ( $action != 'add' ) { ?>
						<div class="module">
										<h2><?=T_('Campaigns')?></h2>
										<BR />
										<a href="admin_user_track.php?user_id=<?=$edit_row['user_id']?>&amp;school_id=<?=$school_id?>">View Campaigns</a>

										<? 
											$rank_sql = "SELECT * FROM rank_marks JOIN ranks AS r USING(rank_ord) WHERE user_id=" . $edit_row['user_id'] . " ORDER BY rank_ord"; 
											$rank_query = mysql_query($rank_sql);											
										?>										
						</div>
						<div class="module">
										<h2><?=T_('Rank')?></h2>
										<? while ($rank_row = mysql_fetch_assoc($rank_query)) : ?>
											<LABEL>
												<TABLE WIDTH="100%">
													<TR>
														<TD>
															<span style="color:<?=$rank_row["rank_color"];?>;"><?=$rank_row["rank_name"];?></span>
														</TD>
														<TD>
															<span style="color:<?=$rank_row["rank_color"];?>;"><?=jdtogregorian($rank_row["date_promoted"]);?></span>
														</TD>
													</TR>
												</TABLE>
											</LABEL>
										<? endwhile; ?>
										<BR />
						</div>
						<? } ?>
						</div>
										<INPUT class="submit" type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Submit')?>">
									</P>
								</FORM>
							<? else : ?>							
								<DIV class="infobox">
									<?=T_("Click a Soldier's name to view and edit profile details and ID photo.")?>
								</DIV>
								
								<DIV class="infobox2">
									<FORM action="admin_user.php" method="get" accept-charset="UTF-8">
										<H3><?=T_('Search by')?>:</H3>
										<P>
											<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
											<LABEL style="white-space: nowrap;">
												<?=T_('Serial #')?>: 
												<INPUT type="text" name="search_user_serial" value="<?=es($search_user_serial)?>">
											</LABEL>
											
											<LABEL style="white-space: nowrap;"><?=T_('First name')?>: <INPUT type="text" name="search_first" value="<?=es($search_first)?>"></LABEL>
											<LABEL style="white-space: nowrap;"><?=T_('Last name')?>: <INPUT type="text" name="search_last" value="<?=es($search_last)?>"></LABEL>
											
											<LABEL style="white-space: nowrap;">
												<?=T_('Platoon')?>: 
												<SELECT name="search_class_id">
													<OPTION value="-1">&lt;<?=T_('All')?>&gt;
													<?while($class_row = mysql_fetch_assoc($class_result)):?>
													<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $search_class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
													<?endwhile;?>
												</SELECT>
											</LABEL>
											
											<LABEL><?=T_('Show only registered users')?>: <INPUT type="checkbox" name="search_user_registered" value="1" <?=$search_user_registered ? 'CHECKED': ''?>></LABEL>
											<LABEL><?=T_('Show only unregistered users')?>: <INPUT type="checkbox" name="search_user_unregistered" value="1" <?=$search_user_unregistered ? 'CHECKED': ''?>></LABEL>
											<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
										</P>
									</FORM>
								</DIV>
								

								<? $result = mq("
									SELECT class_grade, class_sub, user_id, user_code, username, first, last, user_serial, team_name, dob 
									FROM users 
									LEFT JOIN classes USING (class_id, school_id) 
									LEFT JOIN teams USING (team_id, school_id) 
									WHERE school_id = $school_id" . ($search_first !== '' ? ' 
									AND first LIKE ' . ms("$search_first%") : '') . 
									($search_user_serial !== '' ? ' AND user_serial = ' . intval($search_user_serial) : '') . 
									($search_last !== '' ? ' AND last LIKE ' . ms("$search_last%") : '') . 
									($search_class_id != -1 ? " AND class_id = $search_class_id" : '') . 
									($search_user_registered ? ' AND user_registered IS NOT NULL' : '') . 
									($search_user_unregistered ? ' AND user_registered IS NULL' : '') . 
									" ORDER BY class_grade, class_sub, last, first, username");?>
								<TABLE CLASS="list list_<?=$align_start?>" style="font-size:12px">
									<THEAD>
										<TR>
											<!--   <TH><?//=T_('Username')?></TH> -->
											<TH><?=T_('Last')?></TH>
											<TH><?=T_('First')?></TH>
											<TH><?=T_('Platoon')?></TH>
											<!--   <TH><?//=T_('Points')?></TH> -->											
											<TH><?=T_('Birthdate')?></TH>
											<!--<TH><?=T_('Squad')?></TH>-->
											<!--   <TH></TH> -->
											<!--   <TH></TH> -->
											<TH></TH>
											<!--   <TH></TH> -->
										</TR>
									</THEAD>
									
									<? $toggle = 0; ?>
									<? while($row = mysql_fetch_assoc($result)): ?>
										<TR class="<?=($toggle ^= 1) ? 'odd' : 'even'?>">
											<!--     <TD><?//=es($row['username'])?></TD> -->
											<TD><A HREF="admin_user.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=es($row['last'])?></A></TD>
											<TD><A HREF="admin_user.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=es($row['first'])?></A></TD>
											<TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
											

											<? $points = mysql_result(mq(totalMarks("WHERE user_id = {$row['user_id']}")), 0); ?>
											
											<TD><?=$row['dob']?></TD>
											<TD><?=es($row['team_name'])?></TD>
											
											<TD>
											
											<? //if ($points == 0) : ?>
												<A HREF="admin_user.php?action=delete&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Soldier')?></A>
											<?//else:?>
												<?//=T_("Can't delete, has points")?>
											<? //endif; ?>
											<!--
											<BR>
											<A HREF="admin_user.php?action=remove&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>" onClick="return confirm('<?=es(T_('Are you sure?\n\n(This will not delete the soldier, only remove him from the school.)'))?>')"><?=T_('Remove Soldier from school')?></A>
																			
									<!--		<br />
											
											
											<a href="javascript: submit_form(<?=$row['user_code'];?>)" class="kiosk_link"><?=T_('Go to Kiosk')?></a>
									-->
											</TD>
										</TR>
									<? endwhile; ?>
									
								</TABLE>
								
								<br />
								<A HREF="admin_user.php?action=export_users&amp;school_id=<?=$school_id?>&amp;<?=http_build_query(array_intersect_key($_GETPOST, array_fill_keys(array('search_first', 'search_last', 'search_user_serial', 'search_class_id', 'search_user_registered'), 0)), NULL, '&amp;')?>"><?=T_('Export Soldiers')?></A><BR>

							<? endif; ?>
							
							<BR style="clear: both;">
							
						</DIV>
						
					</DIV>
					<? endif; ?>
					
				</DIV>
				
			</DIV>
			
			<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
