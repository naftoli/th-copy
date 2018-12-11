<? 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<TITLE>Platoons Tzivos Hashem Management System</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">
					<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
				</DIV>
			
				<H1>
					<?=T_('Base Management')?>
				</H1>
				
				<? if ($admin_user['auth'] == 'super' || $auth_mode == 'school' && count($admin_user['auths']['school']) > 1) : ?>
					<FORM action="admin_class.php" method="get" accept-charset="UTF-8">
						<P>
							<?=T_('Select Institution')?>: 
							<SELECT name="school_id">
							<?while($school_row = mysql_fetch_assoc($school_result)):?>
								<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
							<?endwhile;?>
							</SELECT> 
							<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
						</P>
					</FORM>
				<? endif; ?>
				
				<? if ($school_id == -1) : ?>
					<?=T_('Please select an Institution.')?>
				<?else:?>
					<DIV class="ui_body">
					
						<DIV class="ui_menu">
							<?ui_menu();?>
						</DIV>
						
						<DIV class="content">
						
							<H1><?=T_('Platoons')?></H1>
							
							<DIV class="infobox">
							Here you will see information about your platoons
							</DIV>
							
							<BR>
							<BR>
							
							<?if($edit_row):?>
							
								<?if($auth_mode != 'class'):?>
									<P><A HREF="admin_class.php?school_id=<?=$school_id?>"><?=T_('Cancel')?></A></P>
								<?endif;?>

								<FORM action="admin_class.php" method="post" accept-charset="UTF-8">
									<P CLASS="rows">
										<INPUT type="hidden" name="action" value="<?=$action?>2">
										<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
										<INPUT type="hidden" name="class_id" value="<?=$edit_row['class_id']?>">
										<LABEL>
										<?=T_('Grade')?>
										<BR>
											<SELECT name="class_grade">
												<?foreach(mysql_enum_values('classes', 'class_grade') as $grade):?>
												<OPTION <?=$grade == $edit_row['class_grade'] ? 'SELECTED' : ''?>><?=es($grade)?></OPTION>
												<?endforeach;?>
											</SELECT>
										</LABEL>
										<BR>
										
										<LABEL><?=T_('Sub')?><BR><INPUT type="text" name="class_sub" maxlength=255 value="<?=es($edit_row['class_sub'])?>"></LABEL><BR>
										<LABEL><?=T_('Teacher')?><BR><INPUT type="text" name="class_teacher" maxlength=255 value="<?=es($edit_row['class_teacher'])?>"></LABEL><BR>
										<LABEL><?=T_('Default Year')?><BR><INPUT type="text" name="default_level" maxlength=2 value="<?=es($edit_row['default_level'])?>" onChange="this.value = Math.max(3, Math.min(parseInt('0'+this.value, 10), 14));"> (3 - 14)</LABEL><BR>
										<INPUT class="submit" type="submit" value="<?=$action == 'edit' ? T_('Save') : T_('Add new')?>">
									</P>
								</FORM>

							<?else:?>

								<?$result = mq("SELECT class_id, class_era, class_grade, class_sub, class_teacher, default_level, (SELECT COUNT(*) FROM users WHERE users.school_id = classes.school_id AND users.class_id = classes.class_id) students FROM classes WHERE school_id = $school_id ORDER BY class_era, class_grade, class_sub");?>

								<TABLE CLASS="list" style="font-size:12px;">
									<THEAD>
										<TR>
											<TH><?=T_('Grade')?></TH>
											<TH><?=T_('Sub')?></TH>	
											<TH><?=T_('Teacher')?></TH>
											<TH><?=T_('Default Year')?></TH>
											<TH><?=T_('# Soldiers')?></TH>
											<TH><?=T_('Points')?></TH>
											<TH></TH>
											<TH></TH>
											<TH></TH>
										</TR>
									</THEAD>
									<? while($row = mysql_fetch_assoc($result)): ?>
									<TR>
										<TD><?=es($row['class_grade'])?></TD>
										<TD><?=es($row['class_sub'])?></TD>
										<TD><?=es($row['class_teacher'])?></TD>
										<TD><?=$row['default_level']?></TD>
										<TD><?=$row['students']?></TD>
										<TD><?=number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$row['class_id']}")), 0), 0)?></TD>
										<TD><?if($row['class_era'] == 0):?><A HREF="admin_class.php?action=edit&amp;class_id=<?=$row['class_id']?>&amp;school_id=<?=$school_id?>"><?=T_('Edit Platoon Info')?></A><?else:?><?=sprintf(T_('This class is from %s. Please move all the soldiers to current classes, or remove them from your school, and then delete this class.'), $row['class_era'])?><?endif;?></TD>
										<TD><?if(!$row['students']):?><A HREF="admin_class.php?action=delete&amp;class_id=<?=$row['class_id']?>&amp;school_id=<?=$school_id?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Platoon')?></A><?else:?><?=T_("Can't delete, has soldiers")?><?endif;?></TD>
										<TD><A HREF="admin_user.php?search_class_id=<?=$row['class_id']?>&amp;school_id=<?=$school_id?>"><?=T_('Manage Soldiers')?></A></TD>
									</TR>
									<? endwhile; ?>
								</TABLE>
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
