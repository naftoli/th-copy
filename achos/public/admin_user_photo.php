<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('file_save.php');
$ui_type = 'school';
require_once('admin_ui.php');


//--------------------------
// function send_mail to HQ
//--------------------------
function send_emailto_HQ($school_name){
	require_once('constant_file.php');
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
				
		$new_user_photo_id = addFile($_FILES['user_photo'], $user_photo_id);		
		$sql = "UPDATE users SET user_photo_id=" . $new_user_photo_id . " WHERE user_id=" . $user_id;
		$query = mysql_query($sql);
		
		send_emailto_HQ($school_name);	
		
		header("Location: http://mashpia.com/admin_users_photo.php?school_id=" . $school_id);
		
	}
	
}

$school_id = $_GET['school_id'];
include("camps/includes/classes/user.php");
$user_id = $_GET['user_id'];
$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);
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
			
				<FORM action="admin_user_photo.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
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
