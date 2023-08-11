<? 
$admin_auth = array('school');  

require('header.php'); 
require_once('file_save.php');

$action = gr('action');
assure_id_school('school_id');

$edit_row = false;

if (!empty($action)) {

	switch($action) {
	
		case 'add':
			$result = mq("SELECT -1 prize_id, NULL school_id, NULL prize_number, '' prize_name, '' prize_description, 1 prize_points, 1 prize_ratio, NULL prize_image_id, NULL min_grade, NULL max_grade, NULL in_stock");
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'add2':
			$prize_name = gr('prize_name');
			$prize_number = nullif_max(gr('prize_number'), 16777215);
			$school_id = nullif(gri('school_id', -1), -1);
			$prize_points = max(1, min(gri('prize_points', 1), 4294967295));
			$prize_ratio = max(1, min(gri('prize_ratio', 1), 65535));
			$in_stock = gri('in_stock', 0);

			if ($admin_user['auth']=='super' && mysql_num_rows(mq("SELECT 1 FROM prizes_auction WHERE prize_number = $prize_number"))) {
				$message = T_('Unable to add new prize, this number is already used.');
				$result = mq("SELECT -1 prize_id, $school_id school_id, $prize_number prize_number, " . ms($prize_name) . ' prize_name, ' . nullif_ms(gr('min_grade'), '') . ' min_grade, ' . nullif_ms(gr('max_grade'), '') . ' max_grade, ' . ms(gr('prize_description')) . " prize_description, $prize_points prize_points, $prize_ratio prize_ratio, NULL prize_image_id");
				$edit_row = mysql_fetch_assoc($result);
				$action = 'add';
			} 
			elseif(mysql_num_rows(mq('SELECT 1 FROM prizes_auction WHERE prize_name = ' . ms($prize_name) . " AND school_id <=> $school_id"))) {
				$message = T_('Unable to add new prize, this name is already used.');
				$result = mq("SELECT -1 prize_id, $school_id school_id, $prize_number prize_number, " . ms($prize_name) . ' prize_name, ' . nullif_ms(gr('min_grade'), '') . ' min_grade, ' . nullif_ms(gr('max_grade'), '') . ' max_grade, ' . ms(gr('prize_description')) . " prize_description, $prize_points prize_points, $prize_ratio prize_ratio, NULL prize_image_id");
				$edit_row = mysql_fetch_assoc($result);
				$action = 'add';
			} 
			else {
				$prize_image_id = 'NULL';
				
				if (isset($_FILES['image'])) 
					$prize_image_id = addFile($_FILES['image'], $prize_image_id);
					
				mq('INSERT INTO prizes_auction SET in_stock=' . $in_stock . ', prize_name = ' . ms($prize_name) . ($admin_user['auth'] == 'super' ? ", prize_number = $prize_number" : '') . ', prize_description = ' . ms(gr('prize_description')) . ', min_grade = ' . nullif_ms(gr('min_grade'), '') . ', max_grade = ' . nullif_ms(gr('max_grade'), '') . ", school_id = $school_id, prize_points = $prize_points, prize_ratio = $prize_ratio, prize_image_id = $prize_image_id");
				
				$message = T_('Prize added');
			}
		break;

		case 'delete':
			mq('DELETE FROM files USING files JOIN prizes_auction ON (files.file_id = prizes_auction.prize_image_id) WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
			mq('DELETE FROM prizes_auction WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
			$message = T_('Prize deleted');
		break;
		
		case 'edit':
			$result = mq('SELECT prize_id, school_id, prize_name, prize_number, prize_description, prize_points, prize_ratio, prize_image_id, min_grade, max_grade, in_stock FROM prizes_auction WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'edit2':
			$prize_id = gri('prize_id', -1);

			if ($admin_user['auth'] == 'super' || mysql_num_rows(mq("SELECT prize_id FROM prizes_auction WHERE prize_id = $prize_id AND school_id IN (" . implode(',', $admin_user['auths']['school']) . ')'))) {
				$prize_name = gr('prize_name');
				$prize_number = nullif_max(gr('prize_number'), 16777215);
				$school_id = nullif(gri('school_id', -1), -1);
				$prize_points = max(1, min(gri('prize_points', 1), 4294967295));
				$prize_ratio = max(1, min(gri('prize_ratio', 1), 65535));
				$in_stock = gri('in_stock', 0);
				
				if ($admin_user['auth']=='super' && mysql_num_rows(mq("SELECT 1 FROM prizes_auction WHERE prize_number = $prize_number AND prize_id != $prize_id"))) {
					$message = T_('Unable to edit prize, this number is already used.');
					$result = mq("SELECT $prize_id prize_id, $school_id school_id, $prize_number prize_number, " . ms($prize_name) . ' prize_name, ' . nullif_ms(gr('min_grade'), '') . ' min_grade, ' . nullif_ms(gr('max_grade'), '') . ' max_grade, ' . ms(gr('prize_description')) . " prize_description, $prize_points prize_points, $prize_ratio prize_ratio, prize_image_id, in_stock FROM prizes_auction WHERE prize_id = $prize_id");
					$edit_row = mysql_fetch_assoc($result);
					$action = 'edit';
				} 
				elseif(mysql_num_rows(mq('SELECT 1 FROM prizes_auction WHERE prize_name = ' . ms($prize_name) . " AND school_id <=> $school_id AND prize_id != $prize_id"))) {
					$message = T_('Unable to edit prize, this name is already used.');
					$result = mq("SELECT $prize_id prize_id, $school_id school_id, $prize_number prize_number, " . ms($prize_name) . ' prize_name, ' . nullif_ms(gr('min_grade'), '') . ' min_grade, ' . nullif_ms(gr('max_grade'), '') . ' max_grade, ' . ms(gr('prize_description')) . " prize_description, $prize_points prize_points, $prize_ratio prize_ratio, prize_image_id, in_stock FROM prizes_auction WHERE prize_id = $prize_id");
					$edit_row = mysql_fetch_assoc($result);
					$action = 'edit';
				} 
				else {
					$prize_image_id = gri('image_delete', 0) ? 'NULL' : 'prize_image_id';
					
					if (isset($_FILES['image'])) 
						$prize_image_id = addFile($_FILES['image'], $prize_image_id);

					if ($prize_image_id !== 'prize_image_id') 
						mq('DELETE FROM files USING files JOIN prizes_auction ON (files.file_id = prizes_auction.prize_image_id) WHERE prize_id = ' . gri('prize_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
						
					mq('UPDATE prizes_auction SET prize_name = ' . ms($prize_name) . ($admin_user['auth'] == 'super' ? ", prize_number = $prize_number" : '') . ', prize_description = ' . ms(gr('prize_description')) . ', min_grade = ' . nullif_ms(gr('min_grade'), '') . ', max_grade = ' . nullif_ms(gr('max_grade'), '') . ", school_id = $school_id, prize_points = $prize_points, prize_ratio = $prize_ratio, prize_image_id = $prize_image_id, in_stock=$in_stock WHERE prize_id = $prize_id");
					$message = T_('Prize edited');
				}
			}
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
		
	}
	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Chinese Auction Prizes'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">
			
			<H1><?=T_('Chinese Auction Prizes')?></H1>
			
			<? if(!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
			
<? if ($edit_row) : ?>

			<FORM action="admin_prize_auction.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
				<P>
					<INPUT type="hidden" name="action" value="<?=$action?>2">
					<INPUT type="hidden" name="prize_id" value="<?=$edit_row['prize_id']?>">
					
					<LABEL>
						<?=T_('Name')?>
						<BR>
						<INPUT type="text" name="prize_name" maxlength=255 value="<?=es($edit_row['prize_name'])?>">
					</LABEL>
					
					<BR>
					
				<? if($admin_user['auth'] == 'super') : ?>
				
					<LABEL>
						<?=T_('Number')?>
						<BR>
						<INPUT type="text" name="prize_number" maxlength=8 value="<?=$edit_row['prize_number']?>" onChange="if(this.value != '') this.value = Math.max(1, Math.min(parseInt('0'+this.value, 10), 16777215));">
					</LABEL>
					
					<BR>					
				<? endif; ?> <!-- if($admin_user['auth'] == 'super') : -->
				
					<LABEL>
						<?=T_('Description')?> (<?=T_('Note: Output as is, including any HTML.')?>)
						<BR>
						<TEXTAREA ROWS="3" COLS="70" name="prize_description"><?=es($edit_row['prize_description'])?></TEXTAREA>
					</LABEL>
					
					<BR>
					
					<LABEL>
						<?=T_('In Stock')?>
						<BR>
						<INPUT type="text" name="in_stock" maxlength=10 value="<?=$edit_row['in_stock']?>">
					</LABEL>

					<BR>
					
					<LABEL>
						<?=T_('Points')?>
						<BR>
						<INPUT type="text" name="prize_points" maxlength=10 value="<?=$edit_row['prize_points']?>" onChange="this.value = Math.max(1, Math.min(parseInt('0'+this.value, 10), 4294967295));">
					</LABEL>
					
					<BR>
					
					<LABEL>
						<?=T_('Ratio')?>
						<BR>
						<INPUT type="text" name="prize_ratio" maxlength=5 value="<?=$edit_row['prize_ratio']?>" onChange="this.value = Math.max(1, Math.min(parseInt('0'+this.value, 10), 65535));">
					</LABEL>
					
					<BR>
					
				<? if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : ?>
				
					<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
					
					
					<LABEL>
						<?=T_('Min Grade')?>
						<BR>
						<SELECT name="min_grade">
							<OPTION value="">&lt;N/A&gt;</option>
							<? //foreach(mysql_enum_values('prizes_auction', 'min_grade') as $grade) : ?>
							<!-- <OPTION <?=$grade == $edit_row['min_grade'] ? 'SELECTED' : ''?>><?=es($grade)?></OPTION> -->
							<? //endforeach; ?>
						</SELECT>
					</LABEL>
					<BR>
					
					<LABEL>
						<?=T_('Max Grade')?>
						<BR>
						<SELECT name="max_grade">
							<OPTION value="">&lt;N/A&gt;
							<? //foreach(mysql_enum_values('prizes_auction', 'max_grade') as $grade) : ?>
							<!-- <OPTION <?=$grade == $edit_row['max_grade'] ? 'SELECTED' : ''?>><?=es($grade)?></OPTION> -->
							<? //endforeach; ?>
						</SELECT>
					</LABEL>
					
					<BR>
					
					<LABEL>
						<?=T_('Institution')?>:
						<BR>
						<SELECT name="school_id">
							<OPTION value="-1">&lt;<?=T_('Multi institution prize')?>&gt;</OPTION>
							<? while($school_row = mysql_fetch_assoc($school_result)) : ?>
							<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $edit_row['school_id'] ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
							<? endwhile; ?>
						</SELECT>
					</LABEL>
					
					<BR>
					
				<?endif;?> <!-- if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : -->
				
					<LABEL>
						<?=T_('Image')?>
						<BR>
						<INPUT type="file" name="image" class="file">
					</LABEL> 
					
					<?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B
					
					<BR>
					
				<? if (!is_null($edit_row['prize_image_id'])) : ?>
				
					<?=T_('Uploading a new image will replace the old.')?>
					<BR>
					<LABEL>
						<?=T_('Delete current image')?> 
						<INPUT type="checkbox" name="image_delete" class="checkbox" value="1">
						<BR>
						<?=linkImgFile($edit_row['prize_image_id'])?>
						<BR>
					</LABEL>
					
				<? endif; ?> <!-- if (!is_null($edit_row['prize_image_id'])) :  -->
				
					<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
					
				</P>
				
			</FORM>

			<A HREF="admin_prize_auction.php"><?=T_('Cancel')?></A>
			
<?else:?>

			<?//$result = mq('SELECT prize_id, prize_name, prize_number, prize_points, prize_ratio, prize_image_id, min_grade, max_grade, school_name, prizes_auction.school_id, in_stock FROM prizes_auction LEFT JOIN schools USING (school_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IS NULL OR school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY prize_points, prize_number, prize_name, prize_id');?>
			<?$result = mq('SELECT prize_id, prize_name, prize_number, prize_points, prize_ratio, prize_image_id, min_grade, max_grade, in_stock, archived FROM prizes_auction ORDER BY archived, prize_id');?>

			<A HREF="admin_prize_auction.php?action=add">
				<?=T_('Add new Prize')?>
			</A>

			<TABLE CLASS="list">
				<TR>
					<th>Prize ID</th>
					<TH><?=T_('Points')?></TH>
					<TH><?=T_('In Stock')?></TH>
					<TH width="100"><?=T_('Name')?></TH>
					<TH><?=T_('Image')?></TH>
					<th>Image Width</th>
					<th>Image Height</th>
					<!--
					<TH><?=T_('Min Grade')?></TH>
					<TH><?=T_('Max Grade')?></TH>
					-->
					<TH></TH>
					<TH></TH>
					<th>Archived</th>
				</TR>

			<? while($row = mysql_fetch_assoc($result)) : ?>
				<TR>
					<td><?=$row['prize_id']?></td>
					<TD><?=$row['prize_points']?></TD>
					<TD><?=$row['in_stock']?></TD>
					<TD><?=es($row['prize_name'])?></TD>
					<TD><?=!is_null($row['prize_image_id']) ? linkImgFile($row['prize_image_id'], 50) : ''?>
					<?php
					list($width, $height, $type, $attr) = @getimagesize("http://mashpia.com/file_view.php?id=" . $row['prize_image_id']);
					echo "<td>" . $width . "</td><td>" . $height . "</td>";
					?>
					<!--
					<TD><?=es($row['min_grade'])?></TD>
					<TD><?=es($row['max_grade'])?></TD>
					-->
					<td></td>
					<TD><?if($admin_user['auth'] == 'super' || !is_null($row['school_id'])):?><A HREF="admin_prize_auction.php?action=edit&amp;prize_id=<?=$row['prize_id']?>"><?=T_('Edit Prize')?></A><?endif;?></TD>
					<TD><?if($admin_user['auth'] == 'super' || !is_null($row['school_id'])):?><A HREF="admin_prize_auction.php?action=delete&amp;prize_id=<?=$row['prize_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Prize')?></A><?endif;?></TD>
					<td>
            <?if($admin_user['auth'] == 'super' || !is_null($row['school_id'])):?>
						  <input type="checkbox" name="archived" id="<?=$row['prize_id']?>" class="archived"
							<? if ($row['archived']) echo "checked ";?>
              />
            <?endif;?>
					</td>
				</TR>
			<? endwhile; ?>
			
			</TABLE>
			
<? endif; ?> <!-- if ($edit_row) : -->

		</DIV> <!-- body -->
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	<script>
		$(".archived").click( function() {
			var id = $(this).attr('id');
			var checked = $(this).is(":checked");
			$.post('ajax/archivePrize.php', { prize_id : id, checked : checked });
		});
	</script>
</HTML>
