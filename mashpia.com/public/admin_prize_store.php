<? 
$admin_auth = array('school'); 

require('header.php'); 
require_once('file_save.php');

$action = gr('action');
assure_id_school('school_id');
$edit_row = false;

$school_id = gri('school_id', -1);
$prize_id = gri('prize_id', -1);

$prize_description = gr('prize_description', '');

echo "<input type='hidden' name='school_id' value='" . $school_id . "'>\n";

if (!empty($action)) 

	switch($action) {
		case 'add':
			$result = mq("SELECT -1 prize_id, NULL school_id, '' prize_name, '' prize_description, 0 prize_points, NULL prize_available, NULL prize_image_id");
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'add2':
			$prize_name = gr('prize_name');
			$school_id = nullif(gri('school_id', -1), -1);
			$prize_points = max(1, min(gri('prize_points', 1), 4294967295));
			$prize_available = nullif_max(gr('prize_available'), 65535);

			$result = mq('SELECT 1 FROM prizes_store WHERE prize_name = ' . ms($prize_name) . " AND school_id <=> $school_id");

			if (mysql_num_rows($result)) {
				$message = T_('Unable to add new prize, this name is already used.');
				$result = mq("SELECT -1 prize_id, $school_id school_id, " . ms($prize_name) . ' prize_name, ' . ms($prize_description) . " prize_description, $prize_points prize_points, $prize_available prize_available");
				$edit_row = mysql_fetch_assoc($result);
				$action = 'add';
			} 
			else {
				$prize_image_id = 'NULL';
				
				if (isset($_FILES['image'])) {
				
					foreach ($_FILES as $file) { 
					
						if ($file['tmp_name'] > '') { 
						
							$random_number = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
							$file_name = 'imagecache/' . $random_number . '.jpg';
								
							include('SimpleImage.php');
							$image = new SimpleImage();
							$image->load($file['tmp_name']);
							$image->resize(200,200);
							$image->save($file_name);					

							if(mysql_result(mq("SELECT GET_LOCK('files', 30)"),0) != 1) 
								trigger_error('could not get lock', E_USER_ERROR);
								
							$count = 0;
							do {
								if ($count++ > 100000) 
									trigger_error('could not get ID', E_USER_ERROR);
									
								$id = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
							} while (mysql_result(mq("SELECT COUNT(*) FROM files WHERE file_id = $id"),0) != 0);
							
							echo "<input type='hidden' name='FILE ID' value='" . $id . "'>\n";
							
							mq("INSERT INTO files (file_id, file_name, file_content_type, file_data) VALUES ($id, " . ms($file['name']) . ', ' . ms(mime_content_type($file['tmp_name'])) . ', ' . ms(file_get_contents($file_name)) . ')');
							mq("SELECT RELEASE_LOCK('files')");
							
							unlink($file_name);

							mq('INSERT INTO prizes_store SET prize_name = ' . ms($prize_name) . ", school_id = $school_id, prize_points = $prize_points, prize_available = $prize_available, prize_image_id = $id");
				
							$message = T_('Prize added');
							
							break;
					
						}
						
					}
					
				}
				
			}
		break;

		case 'delete':
			mq('DELETE FROM files USING files JOIN prizes_auction ON (files.file_id = prizes_auction.prize_image_id) WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
			mq('DELETE FROM prizes_store WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
			$message = T_('Prize deleted');
		break;

		case 'edit':
			if ($school_id == -1) 
				$sql = 'SELECT prize_id, school_id, prize_name, prize_description, prize_points, prize_available, prize_image_id FROM prizes_store WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '');
			else
				$sql = 'SELECT prize_id, school_id, prize_name, prize_description, prize_points, prize_available, prize_image_id FROM prizes_store WHERE school_id=' . $school_id . ' AND prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '');
				
			echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n";
			
			$result = mq($sql);
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'edit2':
			$prize_id = gri('prize_id', -1);

			// ********** Save new image in the files table ********** //
			if (isset($_FILES['image'])) {
				$file_id = mysql_fetch_assoc(mq("SELECT prize_image_id FROM prizes_store WHERE prize_id=" . $prize_id));
				
				foreach ($_FILES as $file) {
					
					if ($file['tmp_name'] > '') { 
						
						$random_number = mysql_result(mq('SELECT FLOOR(RAND() * 4294967295)'),0);
						$file_name = 'imagecache/' . $random_number . '.jpg';
						
						include('SimpleImage.php');
						$image = new SimpleImage();
						$image->load($file['tmp_name']);
						$image->resize(200,200);
						$image->save($file_name);					
						mq("UPDATE files SET file_data=" . ms(file_get_contents($file_name)) . " WHERE file_id=" . $file_id['prize_image_id']);					
						unlink($file_name);
						
					}
						
				}
				
			}
			// ********** Save new image in the files table ********** //
			
			if ($admin_user['auth'] == 'super' || mysql_num_rows(mq("SELECT prize_id FROM prizes_store WHERE prize_id = $prize_id AND school_id IN (" . implode(',', $admin_user['auths']['school']) . ')'))) {
				$prize_name = gr('prize_name');
				$school_id = nullif(gri('school_id', -1), -1);
				$prize_points = max(1, min(gri('prize_points', 1), 4294967295));
				$prize_available = nullif_max(gr('prize_available'), 65535);

				$result = mq('SELECT 1 FROM prizes_store WHERE prize_name = ' . ms($prize_name) . " AND school_id <=> $school_id AND prize_id != $prize_id");

				if(mysql_num_rows($result)) {
					$message = T_('Unable to edit prize, this name is already used.');
					$result = mq("SELECT $prize_id prize_id, $school_id school_id, " . ms($prize_name) . ' prize_name, ' . ms(gr($prize_description)) . " prize_description, $prize_points prize_points, $prize_available prize_available");
					$edit_row = mysql_fetch_assoc($result);
					$action = 'edit';
				} 
				else {
					//$prize_image_id = gri('image_delete', 0) ? 'NULL' : 'prize_image_id';
					
					//if (isset($_FILES['image'])) 
					//	$prize_image_id = addFile($_FILES['image'], $prize_image_id);

					//if ($prize_image_id !== 'prize_image_id') 
					//	mq('DELETE FROM files USING files JOIN prizes_auction ON (files.file_id = prizes_auction.prize_image_id) WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));

					mq('UPDATE prizes_store SET prize_name = ' . ms($prize_name) . ', prize_description = ' . ms(gr('prize_description')) . ", school_id = $school_id, prize_points = $prize_points, prize_available = $prize_available WHERE prize_id = $prize_id");
					$message = T_('Prize edited');
				}
			}
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Store Prizes'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<H1>
				<?=T_('Store Prizes')?>				
			</H1>
			
			
			<? if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1): ?>
				
				<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

				<FORM action="admin_prize_store.php" method="post" accept-charset="UTF-8">
					<P>
						<?=T_('Select Institution')?>: 
							
						<SELECT name="school_id">
							<option value="-1">All Schools</option>
							<? while($school_row = mysql_fetch_assoc($school_result)): ?>
									<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>								
							<?endwhile;?>
						</SELECT> 
							
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
					</P>
				</FORM>
									
			<?endif;?>

				
			
			<?if(!empty($message)):?>
				<H2>
					<?=$message?>
				</H2>
			<?endif;?>
			
			<?if($edit_row):?>
			
				<FORM action="admin_prize_store.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
				
					<P>
						<INPUT type="hidden" name="action" value="<?=$action?>2">						
						<INPUT type="hidden" name="prize_id" value="<?=$edit_row['prize_id']?>">
						
						<LABEL><?=T_('Name')?>
							<BR><INPUT type="text" name="prize_name" maxlength=255 value="<?=es($edit_row['prize_name'])?>">
						</LABEL>
						
						<BR>
						
						<LABEL>
							<?=T_('Description')?> (<?=T_('Note: Output as is, including any HTML.')?>)
							<BR>
							<TEXTAREA ROWS="3" COLS="70" name="prize_description">
								<?=es($edit_row['prize_description'])?>
							</TEXTAREA>
						</LABEL>
						
						<BR>
						
						<LABEL>
							<?=T_('Points')?>
							<BR>
							<INPUT type="text" name="prize_points" maxlength=10 value="<?=$edit_row['prize_points']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 4294967295));">
						</LABEL>
						
						<BR>
						
						<LABEL>
							<?=T_('Prizes available')?>
							<BR>
							<INPUT type="text" name="prize_available" maxlength=5 value="<?=$edit_row['prize_available']?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));">
						</LABEL> 
						
						<?=T_('Leave blank for unlimited')?>
						
						<BR>
						
						<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
							<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
							<LABEL><?=T_('Institution')?>:<BR><SELECT name="school_id">
								<?if($admin_user['auth'] == 'super'):?>
								<OPTION value="-1">&lt;<?=T_('Multi institution prize')?>&gt;</OPTION>
								<?endif;?>
							<?while($school_row = mysql_fetch_assoc($school_result)):?>
								<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $edit_row['school_id'] ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
							<?endwhile;?>
							</SELECT></LABEL><BR>
						<?endif;?>
						
						<LABEL>
							<?=T_('Image')?>
							<BR>
							<INPUT type="file" name="image" class="file">
						</LABEL> 
						
						<?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B
						
						<BR>
						
						<?if(!is_null($edit_row['prize_image_id'])):?>
							<?=T_('Uploading a new image will replace the old.')?>
							<BR>
							<LABEL>
								<?=T_('Delete current image')?> 
								<INPUT type="checkbox" name="image_delete" class="checkbox" value="1">
								<BR>
								<?=linkImgFile($edit_row['prize_image_id'])?><BR>
							</LABEL>
						<?endif?>
						
						<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
						
					</P>
				</FORM>

				<A HREF="admin_prize_store.php"><?=T_('Cancel')?></A>
				
			<?else:?>
				
				<?
					if ($school_id == -1)
						$result = mq('SELECT prize_id, prize_name, prize_points, prize_available, school_name, prizes_store.school_id FROM prizes_store LEFT JOIN schools USING (school_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IS NULL OR school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY prize_name, prize_id');
					else
						$result = mq('SELECT prize_id, prize_name, prize_points, prize_available, school_name, prizes_store.school_id FROM prizes_store LEFT JOIN schools USING (school_id) WHERE school_id=' . $school_id . '  ORDER BY prize_name, prize_id');
				?>

				<A HREF="admin_prize_store.php?action=add"><?=T_('Add new Prize')?></A>
				&nbsp;&nbsp;&nbsp;
				<A HREF="admin_store_prizes_transfer.php"><?=T_('Transfer Prizes')?></A>
				
				<TABLE CLASS="list">
					<TR>
						<TH><?=T_('Name')?></TH>
						<TH><?=T_('Points')?></TH>
						<TH><?=T_('Prizes available')?></TH>
						<TH><?=T_('Institution')?></TH>
						<TH></TH>
						<TH></TH>
					</TR>
					<? while($row = mysql_fetch_assoc($result)): ?>
						<TR>
							<TD><?=es($row['prize_name'])?></TD>
							<TD><?=es($row['prize_points'])?></TD>
							<TD><?=es($row['prize_available'])?></TD>
							<TD><?=es($row['school_name'])?></TD>
							<TD><?if($admin_user['auth'] == 'super' || !is_null($row['school_id'])):?><A HREF="admin_prize_store.php?action=edit&amp;prize_id=<?=$row['prize_id']?>"><?=T_('Edit Prize')?></A><?endif;?></TD>
							<TD><?if($admin_user['auth'] == 'super' || !is_null($row['school_id'])):?><A HREF="admin_prize_store.php?action=delete&amp;prize_id=<?=$row['prize_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Prize')?></A><?endif;?></TD>
						</TR>
					<? endwhile; ?>
				</TABLE>
				
			<? endif; ?>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
