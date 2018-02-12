<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('file_save.php');
$ui_type = 'school';
require_once('admin_ui.php');

$selects = array("school_id", "class_id");
include("admin_schools.php");


// contains email addresses
require_once('constant_file.php');

//$action = gr('action');
//assure_id_school('school_id');

//$school_id = gri('school_id', -1);
//$class_id = gri('class_id', -1);

//--------------------------
// function send_mail to HQ
//--------------------------
//function send_emailto_HQ($school_name){
	// email 1 
//	global $headquarters;
//	$to = $headquarters;  // see constant_file.php
//	$subject= $school_name . ' has uploaded pictures';
//	$body = "<br>" . $school_name." has uploaded photos. Please print rank cards.<br>";
//	send_email($to, $subject, $body, $type = 'html');			  
//}

//--------------------------
// function send_mail
//--------------------------
//function send_email($email, $subject, $body, $type = 'html'){
//	mail($email, $subject, $body, "From: No Reply <noreply@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . ">\r\nX-Mailer: PHP/" . phpversion() . "\r\nErrors-To: errors@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . "\r\nMIME-Version: 1.0\r\nContent-Type: text/" . $type . "; charset=utf-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n");
//}

//$school_name = "";

//if (!empty($action)) {

//	echo "ACTION:$action<br />";

//	switch($action) {
	
//		case 'delete':
//			include("camps/includes/classes/user.php");	
//			$user_id = $_POST['hidden_user_id'];
//			$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
//			$query = mysql_query($sql);
//			$row = mysql_fetch_assoc($query);
//			$user = new user($row);
			
//			$sql = "DELETE FROM files WHERE file_id=" . $user->user_photo_id;
//			$query = mysql_query($sql);
			
//			$sql = "UPDATE users SET user_photo_id=NULL WHERE user_id=" . $user_id;
//			$query = mysql_query($sql);
			
			// send email
//			send_emailto_HQ($school_name);			
//		break;
		
//		default:
//			user_error('unknown action', E_USER_ERROR);
//		break;
//		
//	}
//}

//$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");

//$edit_result = mq("SELECT users.user_id, users.first, users.last, users.username, class_grade, class_sub, class_teacher, users.user_photo_id FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ' ORDER BY users.last, users.first, users.username');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Manage Photos'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript">
			function delete_photo(checkbox, user_id) {
				if (checkbox.checked) {
					var answer = confirm ("Are you sure that you want to delete this photo?");
					if (answer) {
						document.getElementById('hidden_user_id').value = user_id;
						document.forms['delete_form'].submit();
					}
				}
			}
		</SCRIPT>
	</HEAD>

	<BODY>
	
		<? include('admin_header.php'); ?>
		
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">
					<? if(!empty($message)) : ?>
					<H2><?=$message?></H2>
					<? endif; ?>
				</DIV>
				
				<H1><?=T_('Base Management')?></H1>
				
				<script>
					<? include('admin_schools_select.js'); ?>
					
					function get_data()
					{
						alert("SCHOOL ID:" + school_id);
					}
				</script>

				
				<DIV class="infobox2 marking_list clearfix">
					<? include('admin_schools_select.php'); ?>
				</DIV>
				
				<DIV class="ui_body">
				
					<DIV class="ui_menu">
						<?ui_menu();?>
					</DIV>
					
					<DIV class="content">
					
						<H2><?=T_('Manage Photos')?></H2>
						
						<DIV class="infobox">
							<P>
								<?=sprintf(T_('Please upload one headshot photo for each new Soldier. (Soldiers who are previously enrolled do not receive new rank cards until they go up in rank.) You may %sprint temporary rank cards%s until your permanent cards arrive.'), '<A HREF="admin_card_print.php?school_id=' . $school_id . '">', '</A>')?>
							</P>
							<?=T_('Minimum size')?>: 180x225 (<?=sprintf(T_('Larger is OK, the desired aspect ratio is: %s times as high, as it is wide'), 1.25)?>)<BR><?=T_('Maximum individual file size')?>: <?=bytes2units(maxFileSize())?><BR><?=T_('Maximum total combined file size')?>: <?=bytes2units(units2bytes(ini_get('post_max_size')))?>
						</DIV>
						
						<!--
						<DIV class="infobox2">
							<FORM action="admin_users_photo.php" method="get" accept-charset="UTF-8">
							</FORM>
						</DIV>
						-->
						
						<? //if ($edit_result) : ?>
						<!--
						<FORM name="delete_form" id="delete_form" action="admin_users_photo_new.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
							<DIV>
								<INPUT type="hidden" name="action" id="action" value="delete">
								<INPUT type="hidden" name="school_id" id="school_id" value="<?//=$school_id?>">
								<INPUT type="hidden" name="hidden_user_id" id="hidden_user_id" value="">
								<INPUT type="hidden" name="class_id" id="class_id" value="<?//=$class_id?>">
			
								<TABLE class="list list_<?//=$align_start?>">
									<THEAD>
										<TR>
											<TH><?//=T_('Soldier')?><BR><?//=T_('Platoon')?></TH>
											<TH><?//=T_('New Photo')?></TH>
											<TH><?//=T_('Existing Photo')?><BR><?//=T_('Uploading a new photo will replace the old.')?></TH>
										</TR>				
									</THEAD>
					
									<? //$toggle = 0; ?>
									<? //while($row = mysql_fetch_assoc($edit_result)) : ?>
									<TR class="<?//=($toggle ^= 1) ? 'odd' : 'even'?>">
										<TD>
											<SPAN style="font-size: 115%; font-weight: bold;">
												<?//=es($row['last']), ', ', es($row['first'])?>
											</SPAN>
											<BR>
											<BR>
											<?//=T_('Platoon'), ' ', $row['class_grade'], '-', es($row['class_sub']), ' ', es($row['class_teacher'])?>
										</TD>
					
										<TD>
											<a href="admin_user_photo_new.php?school_id=<?//=$school_id;?>&user_id=<?//=$row['user_id'];?>" class="button">New Photo</a>
											<INPUT type="submit" value="<?//=T_('Save')?>">
										</TD>
					
										<TD>
											<? //if(!is_null($row['user_photo_id'])) : ?>
											<LABEL>
												<?//=T_('Delete current photo')?> 
												<INPUT onclick="delete_photo(this, <?//=$row['user_id'];?>);" type="checkbox" name="photo_delete_<?//=$row['user_id']?>" class="checkbox" value="1">
											</LABEL>
											<BR>
											<?//=linkImgFile($row['user_photo_id'], NULL, 80)?>
											<?//endif?>
										</TD>				
									</TR>
									<?//endwhile;?>
								</TABLE>
			
							</DIV>
		
						</FORM>
						-->
						<? //endif; ?>

						<BR style="clear: both;">
						
					</DIV>
					
				</DIV>
				
			</DIV>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
		</BODY>
</HTML>
