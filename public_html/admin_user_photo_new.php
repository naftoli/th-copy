<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('file_save.php');
$ui_type = 'school';
require_once('admin_ui.php');
require_once('constant_file.php');

//--------------------------
// function send_mail to HQ
//--------------------------
function send_emailto_HQ($school_name){
	// email 1 
	global $headquarters;
	$to = $headquarters;  // see constant_file.php
	$subject= $school_name . ' has uploaded pictures';
	$body = "<br>" . $school_name." has uploaded photos. Please print rank cards.<br>";
	send_email($to, $subject, $body, $type = 'html');			  
}

//--------------------------
// function send_mail
//--------------------------
function send_email($email, $subject, $body, $type = 'html'){
	mail($email, $subject, $body, "From: No Reply <noreply@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . ">\r\nX-Mailer: PHP/" . phpversion() . "\r\nErrors-To: errors@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . "\r\nMIME-Version: 1.0\r\nContent-Type: text/" . $type . "; charset=utf-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n");
}

$school_name = "";

if (isset($_POST['action'])) {

	$school_id = $_POST['school_id'];
	$user_id = $_POST['user_id'];
	$user_photo_id = $_POST['user_photo_id'];
	
	if (isset($_FILES['user_photo']))  {
	
		if ($user_photo_id > 0) {		
			$sql = "DELETE FROM files WHERE file_id=" . $user_photo_id;
			$query = mysql_query($sql);
		}
		
		if ($mobile_pic = addFileNew($_FILES['user_photo'], $user_photo_id)) {
			$sql = "update users set mobile_pic = '" . $mobile_pic . "', user_photo_id = null where user_id = " . $user_id;
			mysql_query($sql);
		} else {
			$new_user_photo_id = addFile($_FILES['user_photo'], $user_photo_id);		
			$sql = "UPDATE users SET user_photo_id=" . $new_user_photo_id . " WHERE user_id=" . $user_id;
			$query = mysql_query($sql);
		}
		
		send_emailto_HQ($school_name);	
		
		header("Location: http://mashpia.com/admin_users_photo_new.php?school_id=" . $school_id);
		
	}
	
}

$school_id = $_GET['school_id'];
include("camps/includes/classes/user.php");
$user_id = $_GET['user_id'];
$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);

// contains email addresses
/*require_once('constant_file.php');

$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);

$school_name = "";
if(!empty($action)) switch($action) {
  
  case 'save':
    $result = mq("SELECT u.user_id, s.school_name 
		FROM users u
		LEFT JOIN classes on u.class_id = classes.class_id
		LEFT JOIN schools s on s.school_id = u.school_id
		WHERE u.school_id = $school_id" . ($class_id != -1 ? " AND u.class_id = $class_id" : '') . ' 
		ORDER BY u.username');
		
    while($row = mysql_fetch_assoc($result)) {
      $user_photo_id = gri("photo_delete_{$row['user_id']}", 0) ? 'NULL' : 'user_photo_id';
      if(isset($_FILES["photo_{$row['user_id']}"])) 
		$user_photo_id = addFile($_FILES["photo_{$row['user_id']}"], $user_photo_id);
      if($user_photo_id !== 'user_photo_id') 
		mq("DELETE FROM files USING files JOIN users ON (files.file_id = users.user_photo_id) WHERE user_id = {$row['user_id']}");
      mq("UPDATE users SET user_photo_id = $user_photo_id WHERE user_id = {$row['user_id']}");
	  $school_name = $row['school_name'];
    }
	// send email
	send_emailto_HQ($school_name);	
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");

$edit_result = mq("SELECT users.user_id, users.first, users.last, users.username, class_grade, class_sub, class_teacher, users.user_photo_id FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ' ORDER BY users.last, users.first, users.username');
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Manage Photos'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">
					<? if(!empty($message)) : ?>
						<H2><?=$message?></H2>
					<?endif;?>				
				</DIV>
				
				<H1>
					<?=T_('Base Management')?>
				</H1>
			
				<FORM action="admin_user_photo_new.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
					<input type="hidden" name="action" value="save">
					<input type="hidden" name="school_id" value="<?=$school_id;?>">
					<input type="hidden" name="user_id" value="<?=$user_id;?>">
					<input type="hidden" name="user_photo_id" value="<?=$user->user_photo_id;?>">
					
					<TABLE class="list list_<?=$align_start?>">
						<THEAD>
							<TR>
								<TH><?=T_('Soldier')?><BR><?=T_('Platoon')?></TH>
								<TH><?=T_('New Photo')?></TH>
								<TH><?=T_('Existing Photo')?><BR><?=T_('Uploading a new photo will replace the old.')?></TH>
							</TR>				
						</THEAD>
						
						<TR class="odd">
							<TD>
								<SPAN style="font-size: 115%; font-weight: bold;">
									<?=$user->last;?>, <?=$user->first;?>
								</SPAN>
							</TD>
							
							<TD>
								<INPUT type="file" name="user_photo"> 
								<INPUT type="submit" value="<?=T_('Save')?>">
							</TD>	

							<TD>
								<? if ($user->user_photo_id > 0) : ?>
								<LABEL>
									<?=T_('Delete current photo')?> 
									<INPUT type="checkbox" name="photo_delete_<?=$row['user_id']?>" class="checkbox" value="1">
								</LABEL>
								<BR>
								<?=linkImgFile($user->user_photo_id, NULL, 80)?>
								<? endif; ?>
							</TD>				
						</TR>
						
					</TABLE>
				
				</FORM>
				
			</DIV>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>

	</BODY>
	
</HTML>
