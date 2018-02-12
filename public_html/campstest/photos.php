<? 

$admin_auth = array('camp');
require('../header.php'); 
require_once('../file_save.php');

$action = gr('action', '');

if ($action != "") {

	switch($action) {
	
		case 'save':

			$logo_id = 'NULL';
			if (isset($_FILES['photo'])) 
				$logo_id = addFile($_FILES['photo'], $logo_id);
		
			$sql = "UPDATE default_group_types SET logo_id=" . $logo_id . " WHERE gt_id=" . gri('gt_id');
			mq($sql);
			
		break;

	}
	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<TITLE><?=T_('Manage Photos'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
  
  
	<FORM action="photos.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
		<INPUT type="hidden" name="action" id="action" value="save">
		
		<SELECT NAME="gt_id" id="gt_id">
			<? $query = mq("SELECT * FROM default_group_types"); ?>
			<? while ($row = mysql_fetch_assoc($query)) : ?>
			<option value="<?=$row['gt_id'];?>"><?=$row['group_type_name'];?></option>
			<? endwhile; ?>
		</SELECT>
		
		<INPUT type="file" name="photo" class="file">
		
		<INPUT type="submit" value="<?=T_('Save')?>"></TD>
	</FORM>
  
  
	</BODY>
	
</HTML>
