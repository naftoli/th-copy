<!--
<li class="list_parent">
	<a href="#" title="child">
		<span class="icon">
			<img height="28" width="28" alt="Child Management" src="images/parentIcons/Child Management.png">
		</span> 
		<?=T_('Child Management')?>
	</a>
</li>	
						
<ul class="list_second">
 
	<?
	$cur_admin_id = $admin_user['admin_id'];
	$user_auths = mq("SELECT school_id, school_name, first, last, user_id, admin_auths.role_id, role_name, user_code FROM admin_auths LEFT JOIN users ON (admin_auths.id = users.user_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'user' AND admin_id = $cur_admin_id" . ($admin_user['auth'] == 'super' ? '' : "") . ' ORDER BY last, first'); //  AND school_id IN ($school_ids)
	$myshliach = false;
	?>
	<? while($row = mysql_fetch_assoc($user_auths)): ?>
	<? if (in_array($row['school_id'], array(61,269))) $myshliach = true; ?>
			<li class="user">
				<? $form_name = "parent_user_" . $row['user_id']; ?>
				<form method="post" action="admin_parent_user.php" name="<?=$form_name?>">
					<input type="hidden" name="child_id" value="<?=$row['user_id'];?>">
					<input type="hidden" name="school_id" value="<?=$row['school_id'];?>">
					<a href="#" onclick="document.forms['<?=$form_name;?>'].submit()">
				    <div class="title"><?=es($row['first'])?> <?=es($row['last'])?></div>
					</a>
				</form>
			</li>								
	<? endwhile; ?>
	<!--		
	<li>
		<a href="associate_children.php">Add Children</a>
	</li>
	
	<li>
		<FORM name="register_parent_form" method="post" action="https://mashpia.com/register_children.php">
			<input type="hidden" name="admin_id" value="<?=$admin->admin_id;?>">
			<input type="hidden" name="register" value="children" />
				<a href="#" title='child' onclick="document.forms['register_parent_form'].elements['admin_id'].value=<?=$admin->admin_id;?>; document.forms['register_parent_form'].submit();">
				<?=T_('Register Children')?>
			</a>
		</FORM>
	</li>
	-->
	<!--
	<script>
		$(function(){
			$('.kiosk_link').click(function(e){
				e.preventDefault();
				$(this).siblings('form').submit();
			});
		});
	</script>
	<!--
	<li>
		<FORM name="children_add_ons_form" method="post" action="https://mashpia.com/admin_children_add_ons.php">
			<input type="hidden" name="admin_id" value="<?=$admin->admin_id;?>">
				<a href="#" onclick="document.forms['children_add_ons_form'].elements['admin_id'].value=<?=$admin->admin_id;?>; document.forms['children_add_ons_form'].submit();">
				<?=T_('Children Purchases')?>
			</a>
		</FORM>
	</li>
	-->
	<!--
</ul>
-->
<li class="list_parent">
	<a href="#" title="child">
		<span class="icon">
			<img height="28" width="28" alt="Mission Management" src="images/parentIcons/New Tasks.gif">
		</span>
		<?=T_('Mission Management')?>
	</a>
</li>

<ul class="list_second">
	
	<li>
		<a href="mission_report/newParentPrint.php">Print Missions</a>
	</li>
	<!--
	<li>
		<a href="mission_report/newParentPrintYT.php">Print Pesach Missions</a>
	</li>
	-->
	<li>
		<a href="mission_report/newParentMark.php">Mark Missions</a>
	</li>
	
	<li>
		<a href="parentSettings.php">Mission Settings</a>
	</li>
	<!--
	<li>
		<a href="childrens_tasks.php">Print Multiple Missions</a>
	</li>
	<li>
		<a href="parents_print_report.php">Print Single Missions</a>
	</li>
	<li>
		<a href="parents_print_pesach_report.php">Print Pesach Missions</a>
	</li>
	<li>
		<a href="parents_date_tasks_report_new.php">Mark Missions</a>
	</li>
	-->
	<? if ($myshliach) : ?>
		<li>
			<a href="parent_task_customization.php">Customize Missions</a>
		</li>
		<li>
			<a href="newParentTask.php">Add New Tasks</a>
		</li>
	<? endif; ?>
</ul>

<li class="list_parent">
	<a href="#" title="programs">
		<span class="icon">
			<img height="28" width="28" alt="Report" src="images/parentIcons/Reports.gif">
		</span>
		<?=T_('Reports')?>
	</a>
</li>

<ul class="list_second">
	<li>
		<a href="parentPersonalizedReport.php">Weekly Report</a>
	</li>
	<li>
		<a href="children_stickers.php">Sticker Report</a>
	</li>
	<li>
		<a href="new_tanya_report.php">Tanya Report</a>
	</li>
</ul>
<!--
<li class="list_parent">
	<a href="#" title="child" onclick="window.location.href='parent_profile.php'">
		<span class="icon">
			<img height="28" width="28" alt="Profile" src="images/parentIcons/profile icon.gif">
		</span>
		<?=T_('Profile')?>
	</a>
</li>
-->