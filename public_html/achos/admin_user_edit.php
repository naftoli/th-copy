<? 			
if (isset($_POST['action'])) 
{

	$action = $_POST['action'];
	$user_id = $_POST['user_id'];
	
	$_GETPOST = $_POST + $_GET;
	
	include("file_save.php");
	function gr($name, $empty = '') 
	{
		global $_GETPOST;
		
		return agr($_GETPOST, $name, $empty);
	}

	function gri($name, $empty = NULL) 
	{
		global $_GETPOST;
		
		return agri($_GETPOST, $name, $empty);
	}

	function agri(&$in, $name, $empty = NULL) 
	{
		if (isset($in[$name])) {
			return intval($in[$name]);
		} 
		else {
			return $empty;
		}
	}
	
	function agr(&$in, $name, $empty = '') 
	{
		if (isset($in[$name])) {
			return trim($in[$name]);
		} 
		else {
			return $empty;
		}
	}	
	
	include("db.php");
	
	switch ($action) 
	{	
		case 'save':
		
			$user_photo_id = gri('photo_delete', 0) ? 'NULL' : 'user_photo_id';
			if (isset($_FILES['photo'])) 
				$user_photo_id = addFile($_FILES['photo'], $user_photo_id);

			if ($user_photo_id !== 'user_photo_id') 
				mq('DELETE FROM files USING files JOIN users ON (files.file_id = users.user_photo_id) WHERE user_id = ' . gri('user_id', -1));

			$reg = '';
			if (gri('user_registered', 0)) 
				$reg = ', user_registered = NOW(), user_start_date = IFNULL(user_start_date, ' . unixtojd() .')';
			elseif ($admin_user['auth'] == 'super' && gri('user_registered_not', 0)) 
				$reg = ', user_registered = NULL, user_start_date = IF(user_start_date > ' . (unixtojd()-10) . ', NULL, user_start_date)';

			mq('UPDATE users SET parent_marking=' . gri('parent_marking') . ', email = ' . ms(gr('email')) . ', first = ' . ms(gr('first')) . ', last = ' . ms(gr('last')) . ', first_he = ' . ms(gr('first_he')) . ', last_he = ' . ms(gr('last_he')) . ', lang = ' . ms(gr('lang')) . ', user_address1 = ' . ms(gr('address1')) . ', user_address2 = ' . ms(gr('address2')) . ', user_city = ' . ms(gr('city')) . ', user_state = ' . ms(gr('state')) . ', user_postal = ' . ms(gr('postal')) . ', user_country = ' . ms(gr('country')) . ', user_phone = ' . ms(gr('phone')) . ', kiosk_edit = ' . ms(gr('kiosk_edit')) . ', class_id = ' . nullif(gri('class_id', -1), -1) . ', child_type_id = ' . gri('child_type_id', -1) . ', team_id = ' . nullif(gri('team_id', -1), -1) . $reg . ', dob = ' . nullif_ms(gr('dob'), '') . ', gender = ' . nullif_ms((gr('gender') != 'M' && gr('gender') != 'F' ? 'NULL' : gr('gender')), 'NULL') . ", user_photo_id=" . $user_photo_id . " WHERE user_id=" . $user_id);
			
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
	
	
	}
	
	header("Location: http://mashpia.com/admin_user_two.php?school_id=" . $_POST['school_id']);
	
}



$admin_auth = array('school'); 
require('header.php');
require_once('file_save.php');
require_once('calendar.php');
$ui_type = 'school';
require_once('admin_ui.php');

$action = "edit";

assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$edit_row = false;

$search_user_serial = gr('search_user_serial');
$search_first = gr('search_first');
$search_last = gr('search_last');
$search_class_id = gri('search_class_id', -1);
$search_user_registered = gri('search_user_registered', 0);

include("camps/includes/classes/child_type.php");
$child_types = array();
$sql = "SELECT * FROM child_types ORDER BY child_type_name";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{
	$child_type = new child_type($row);
	array_push($child_types, $child_type);
}


if ($action == "edit")
{
	$user_id = $_GET['user_id'];
	$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
	$result = mq($sql);
	$edit_row = mysql_fetch_assoc($result);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE>
			Edit Soldier
		</TITLE>
		
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
				
		<script>
			function submit_form(user_code)
			{
				document.kiosk_form.elements["user_code"].value = "3" + user_code;
				document.kiosk_form.submit();
			}
			
			function check_action(action)
			{
				if (action == "save")
				{
					window.location = "http://mashpia.com/admin_user_two.php";
				}
			}
		</script>											
	</HEAD>
	
	<BODY onload="check_action('<?=$action;?>');">
	
		<?include('admin_header.php');?>
		
		<script>
			$(function(){
				$("ul.tabs").tabs("div.module");
			});
		</script>		
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">				
					<? if (!empty($message)):?>
						<H2><?=$message?></H2>
					<?endif;?>				
				</DIV>
				
				<H1><?=T_('Base Management')?></H1>
				
				<DIV class="ui_body">
					
					<DIV class="ui_menu">
						<?ui_menu();?>
					</DIV>
						
					<DIV class="content">
						
						<H1>
							Edit Soldier
						</H1>
							
						<FORM action="admin_user_edit.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data" onSubmit="//<?/*=!$edit_row ? "if(this.elements['password'].value == '') { alert('" . esq(T_('Please enter a password for this user.')) . "'); } else " : ''*/?> { if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); } else { return true; } } this.elements['password'].focus(); return false;">
							<P CLASS="rows">
								<INPUT type="hidden" name="action" value="save">
								<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
								<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
								<INPUT type="hidden" name="user_id" value="<?=$user_id;?>">
								
								<ul class="tabs">
									<li><?=T_('Personal')?></li>
									<li><?=T_('Address')?></li>
									<li><?=T_('Registration')?></li>
									<li><?=T_('Campaigns')?></li>
									<li><?=T_('Rank')?></li>
									<li><?=T_('Groups')?></li>
									<li><?=T_('Settings')?></li>
								</ul>
								
								<div class="panes">
								
									<div class="module">

										<h2>
											<?=T_('Personal Information')?>
										</h2>
											
										<div class="photo">									
											<?=linkImgFile($edit_row['user_photo_id'], 200)?>
										</div>
											
			
										<?=T_('Serial #')?><BR><SPAN style="font-size: 125%; font-weight: bold;"><?=es($edit_row['user_serial'])?></SPAN><BR>
										<?=T_('Barcode #')?><BR><SPAN style="font-size: 125%; font-weight: bold;">3<?=es($edit_row['user_code'])?></SPAN><BR>
											
										<LABEL><?=T_('First Name')?><BR><INPUT TYPE="text" NAME="first" VALUE="<?=es($edit_row['first'])?>" MAXLENGTH="128"></LABEL><BR>
										<LABEL><?=T_('Last Name')?><BR><INPUT TYPE="text" NAME="last" VALUE="<?=es($edit_row['last'])?>" MAXLENGTH="128"></LABEL><BR>
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
											<INPUT TYPE="text" NAME="email" VALUE="<?=es($edit_row['email'])?>" MAXLENGTH="255">
										</LABEL>
											
										<BR>
										<?=T_('Gender')?>
										<BR>
											
										<LABEL><INPUT type="radio" name="gender" value="NULL" <?= is_null($edit_row['gender']) ? 'CHECKED' : ''?> style="width: auto;"> <?=T_('Unknown')?></LABEL>
										<LABEL><INPUT type="radio" name="gender" value="M" <?= $edit_row['gender'] == 'M' ? 'CHECKED' : ''?> style="width: auto;"> <?=T_('Male')?></LABEL>
										<LABEL><INPUT type="radio" name="gender" value="F" <?= $edit_row['gender'] == 'F' ? 'CHECKED' : ''?> style="width: auto;"> <?=T_('Female')?></LABEL>
											
										<BR>
											
										<LABEL>
											<?=T_('Date of birth')?>
											<BR>
											<INPUT TYPE="text" NAME="dob" VALUE="<?=$edit_row['dob']?>" MAXLENGTH="10" onChange="if(this.value != '') {var str = this.value.replace(/\D/g, '')+'00000000'; this.value = str.substring(0, 4) + '-' + str.substring(4, 6) + '-' +  str.substring(6, 8);}"> (YYYY-MM-DD)
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
										<h2><?=T_('Registration')?></h2>
										<? if ($action=='edit') : ?>
										<?=T_('Member Since')?><BR><SPAN style="font-size: 125%; font-weight: bold;"><?=dateToHebrew($edit_row['user_start_date'])?></SPAN><BR>
										<? endif; ?>

										<? if(is_null($edit_row['user_registered'])):?>
										<!-- <LABEL><?=T_('Register?')?><BR><INPUT type="checkbox" name="user_registered" value="1"></LABEL><BR> -->
										<? else: ?>
											<?=T_('Registered')?><BR><SPAN style="font-size: 125%; font-weight: bold;"><?=es($edit_row['user_registered'])?></SPAN><BR>
											<? if($admin_user['auth'] == 'super'): ?>
											<LABEL><?=T_('Un-Register?')?><BR><INPUT type="checkbox" name="user_registered_not" value="1"></LABEL><BR>
											<? endif; ?>
										<? endif; ?>
									</div>
									
									<div class="module">
										<h2><?=T_('Campaigns')?></h2>
										<BR />
											
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
									
									<div class="module">										
										<h2><?=T_('Groups')?></h2>
											
										<LABEL>
											<?=T_('Platoon')?>
											<BR>
											<? $result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub"); ?>
											<SELECT name="class_id">
												<OPTION VALUE="-1">&lt;<?=T_('N/A')?>&gt;</OPTION>
												<? while ($row = mysql_fetch_assoc($result)) : ?>
												<OPTION VALUE="<?=$row['class_id']?>" <?=$row['class_id'] == $edit_row['class_id'] ? 'SELECTED' : '' ?>><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></OPTION>
												<? endwhile; ?>
											</SELECT>
										</LABEL>
											
										<BR>
											
										<LABEL>
											<?=T_('Squad')?>
											<BR>
											<? $result = mq("SELECT team_id, team_name FROM teams WHERE school_id = $school_id ORDER BY team_name"); ?>
											<SELECT name="team_id">
												<OPTION VALUE="-1">&lt;<?=T_('N/A')?>&gt;</OPTION>
												<? while($row = mysql_fetch_assoc($result)): ?>
												<OPTION VALUE="<?=$row['team_id']?>" <?=$row['team_id'] == $edit_row['team_id'] ? 'SELECTED' : '' ?>><?=es($row['team_name'])?></OPTION>
												<? endwhile; ?>
											</SELECT>
										</LABEL>
											
										<BR>
									</div>
									
									<div class="module">
										<h2><?=T_('Settings')?></h2>
											
										<?=es(T_('Kiosk Mission & Task entry'))?>
											
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
											
										<!--<LABEL>
											<?//=T_('Tzivos Hashem Type')?>
											<BR>
											<? //$result = mq('SELECT school_type_id, school_type_name FROM school_types ORDER BY school_type_name'); ?>
											<SELECT name="school_type_id">
												<? //while($row = mysql_fetch_assoc($result)): ?>
												<OPTION VALUE="<?//=$row['school_type_id']?>" <?//=$row['school_type_id'] == $edit_row['school_type_id'] ? 'SELECTED' : '' ?>><?//=es($row['school_type_name'])?></OPTION>
												<? //endwhile; ?>
											</SELECT>
										</LABEL>-->
											
										<input type="hidden" name="CHILD TYPE ID" value="<?=$edit_row["child_type_id"];?>">
											
										<BR>
											
										<LABEL>
											<?=T_('Tzivos Hashem Type')?>
											<BR>
											<SELECT name="child_type_id">
												<? for ($ctno = 0; $ctno < count($child_types); $ctno++) : ?>
												
												<? if ($edit_row["child_type_id"] == $child_types[$ctno]->child_type_id) : ?>
												<OPTION selected VALUE="<?=$child_types[$ctno]->child_type_id;?>"><?=$child_types[$ctno]->child_type_name;?></OPTION>
												<? else : ?>
												<OPTION VALUE="<?=$child_types[$ctno]->child_type_id;?>"><?=$child_types[$ctno]->child_type_name;?></OPTION>
												<? endif; ?>
														
												<? endfor; ?>
											</SELECT>
										</LABEL>										
											
										<BR>
										<BR>
											
										<LABEL>
											<?=T_('Language')?>
											<BR>
											<SELECT NAME="lang">
												<?
												foreach($langs as $lang_id => $lang_name) {
													echo "<OPTION value='$lang_id'" . ($lang_id == $edit_row['lang'] ? ' SELECTED' : '') . ">" . es($lang_name);
												}
												?>
											</SELECT>
										</LABEL>
											
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
									</div>
									
								</div>
								
								<INPUT class="submit" type="submit" value="Save">
								
							</P>
						</FORM>
													
						<BR style="clear: both;">
							
					</DIV>
						
				</DIV>
					
			</DIV>
				
		</DIV>
			
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
