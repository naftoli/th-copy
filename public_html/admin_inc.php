<?

$role_auth = "";

if (!isset($menu_type)) 
{
	if (!empty($admin_user) && $admin_user['auth'] == 'super') 
	{
		$menu_type = 'super';
	} 
	elseif (!empty($admin_user['auths']['school'])) 
	{
		$menu_type = 'school';
	}
	elseif (!empty($admin_user['auths']['camp'])) 
	{
		$menu_type = 'camp';
		$sql = "SELECT aa.id, aa.auth, r.role_auth FROM admin_auths AS aa JOIN roles AS r USING (role_id) WHERE admin_id=" . $admin_user['admin_id'];
		$query = mysql_query($sql);
		$role = mysql_fetch_assoc($query);
		$auth = $role['auth'];
		
		if ($auth == "camp") {
			//session_start();
			$_SESSION['camp_id'] = $role['id'];
		}
	}
	
}
?>

<? if (!empty($admin_user) && isset($menu_type)) : ?>

	<!-- if (basename($_SERVER['PHP_SELF']) != 'admin.php') : -->
	<? if (basename($_SERVER['PHP_SELF']) != 'admin.php') : ?>
	<DIV>
		<A HREF="admin.php" onClick="var menu=document.getElementById('left_menu_overlay'); if(menu.style.display == '') menu.style.display = 'none'; else menu.style.display = ''; return false;"><?=T_('Menu')?>
		</A>
	</DIV>
	
	<DIV class="left_menu_overlay" id="left_menu_overlay">
	<? endif; ?> 
	<!-- if (basename($_SERVER['PHP_SELF']) != 'admin.php') : -->

		<UL class="admin">

	<? echo "<input type='hidden' name='MENU TYPE' value='" . $menu_type . "'>\n"; ?>
	
	<!-- if ($menu_type == 'super') : -->
	<? if ($menu_type == 'super') : ?>
		<? $url_id = ''; ?>
			<!-- <li><a href="school_reg_types.php">School Registration & Types</a></li> -->
			<li><a href="shabbo_mevorchim_hq.php">Shabbos Mevorchim HQ Report</a></li>
			<li><a href="school_poster_brochure_orders.php">School Brochure / Poster Orders Report</a></li>
			<li><a href="chidon_report.php">Chidon Registered Report</a></li>
			<li><a href="communicate_files.php">Manage files for Communicating with Parents</a></li>
			<li><a href="admin_lines_report.php">Yud Alef Nissan Report</a></li>
			<li><a href="admin_print_pdf.php"><?=T_('Print Certificate')?></a></li>
            <li><a href="admin_shipping_report_new.php"><?=T_('Shipping Report')?></A>
            <li></li><br />
            <LI><A HREF="medals_labels.php"><?=T_('Medals Shipping Labels')?></A> 
            <li><a href="myShliachShipLabels.php">MyShliach Medals Shipping Labels</a></li> 
            <li><a href="anashShipLabels.php">Anash Kinder Medals Shipping Labels</a></li>    
            <li></li><br />   
            <li><a href="isserRanks.php">Isser's Rank Summary Report</a></li>
            <LI><A HREF="medals_summary_report.php"><?=T_('Medals Packing List')?></A>
			<li><a href="shimmy_rank_report.php">Rank Report by Grade</a></li>
            <li></li><br />      
            <li><a href="hachayol_report.php"><?=T_('Hachayol Report')?></A>
            <li><a href="hachayol_report_details.php">Hachayol Report Details</a></li>
            <li><a href="myShliachHachayolReport.php">MyShliach Hachayol Report</a></li>       
            <li><a href="myShliachHachayolLabels.php">MyShliach Hachayol Labels</a></li>
			<li><a href="/raffles/shared/forms/winners_hachayol_form.php">Hachayol Raffle Winners</a></li> 
            <li></li><br />      
            <li><a href="admin_received_stats_all.php"><?=T_("Medal/Rank Report All Schools")?></a></li>
            <li><a href="school_labels.php">School Labels</a></li>
            <li><a href="school_bday_labels.php">School Birthday Labels</a></li>
            <li><a href="age_report.php">Current Age Report</a></li>
            <li><a href="age_report2.php">Future Age Report</a></li>            
            <li></li><br />
            <li><a href="add_ons_report.php">Add-ons Report</a></li>
            <li><a href="new_th_users.php">Binders & Sticker Boards Report</a></li>
            <li><a href="newly_registered.php">Charge Cards & Sticker Books Report</a></li>  
            <li><a href="ranks_shipping.php">Ranks Shipping Report</a></li> 
            <li><a href="medals_shipping.php">Medals Shipping Report</a></li> 
            <li><a href="school_labels.php">School Shipping Labels</a></li>        
            <li><br />
            <li><a href="5774_orders_report.php">Poster and Registration Brochure Report 5774</a></li>
            <!--<li><a href="registration_brochures.php">Registration Brochures Report 5774</a></li>-->
            <li><a href="siddurim_report.php">Siddurim Report</a></li>
            <li><a href="registered_report.php"><?=T_("Registered Report")?></a></li> 
            <li><a href="barcodes_report.php"><?=T_("Barcodes Report")?></a></li>
            <li><a href="promotions2.php">Rally promotion list for Choni</a></li>           
            <li></li><br /> 
            <li><a href="ajax/delParentAccounts.php">Delete Parent accounts with no children associations</a></li>
            <li><a href='missionTaskReport.php'>Mission / Task Report</a></li>
            <li><a href="admin_card_hschool.php?school_id=-2"><?=T_("Print ID Cards for Hebrew Schools")?></a></li>
            <li><a href="create_tasks.php">Bulk Add/Delete New Tasks</a></li>
            <li></li><br />  
            
            <li><a href="school_birthdays.php"><?=T_("Choose Multiple Schools Birthdays")?></a></li>
            <li><a href="haggadas_report.php"><?=T_("Haggadas Report")?></a></li>
            <LI><A HREF="admin_reports.php"><?=T_('Manage Mission Sheet Parshos')?></A>
            <li></li><br />       
		<!--
			<LI><A HREF="admin_campaigns.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Global Campaigns')?></A>
			<LI><A HREF="admin_missions.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Global Campaign Missions')?></A>
			<LI><A HREF="admin_group_missions.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Global Campaign Group Missions')?></A>								<LI><A HREF="admin_tasks.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Global Campaign Missions Tasks')?></A>				
				
			<LI><A HREF="admin_school_type.php"><?=T_('Manage Tzivos Hashem Types')?></A>
			<LI><A HREF="admin_school_makeup.php"><?=T_('Manage School Types')?></A>-->
			<LI><A HREF="admin_admin.php"><?=T_('Manage Admins')?></A>
			<LI><A HREF="admin_yearly.php"><?=T_('End of year tasks')?></A>
			<LI><A HREF="admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></A>
			<LI><BR>
	   <!--
			<LI><A HREF="admin_inst.php"><?=T_('Manage Institution types')?></A>
			<LI><A HREF="admin_school.php"><?=T_('Manage Bases')?></A>
			<LI><A HREF="admin_class.php<?=$url_id?>"><?=T_('Manage Platoons')?></A>
			<LI><A HREF="admin_user.php<?=$url_id?>"><?=T_('Manage Soldiers')?></A>-->
			<LI><A HREF="admin_user_noschool.php<?=$url_id?>"><?=T_('Manage No-School Soldiers')?></A>
			<LI><BR>
	   <!--
			<LI><A HREF="admin_invoice_summary.php"><?=T_('Invoice Summary')?></A>			
			<LI><A HREF="admin_invoice_report.php"><?=T_('Invoices by Date/Time')?></A> <span><font size='1' color='red'>*NEW*</font></span>-->
			<LI><A HREF="admin_shipping_report_02.php"><?=T_('Registrations by Date/Time')?></A> <span><font size='1' color='red'>*NEW*</font></span>
			<LI><A HREF="admin_credit_card_report.php"><?=T_('Credit Card Transactions by Date')?></A> <span><font size='1' color='red'>*NEW*</font></span>
			<LI><A HREF="admin_school_register_report.php"><?=T_('Registered Schools and Registered Students')?></A> <span><font size='1' color='red'>*NEW*</font></span>
			<LI><A HREF="admin_school_teacher_report.php"><?=T_('List of Unique teacher names for current year')?></A> <span><font size='1' color='red'>*NEW*</font></span>
			
			<LI><BR>
			<LI><A HREF="admin_subject.php"><?=T_('Manage Subjects')?></A>
			<LI><A HREF="admin_subject_slide.php"><?=T_("Manage Subjects' Slides")?></A>
			<LI><A HREF="admin_track.php"><?=T_('Manage Ladders')?></A>
			<LI><A HREF="admin_label.php"><?=T_('Manage Labels')?></A>
			<LI><A HREF="admin_medal.php"><?=T_('Manage Medals')?></A>
			<LI><A HREF="school_possible_medals.php"><?=T_('Possible Medals')?></A>			
			<LI><A HREF="admin_rank.php"><?=T_('Manage Ranks')?></A>
			<LI><A HREF="admin_date_tasks.php" target="_blank"><?=T_('Manage Date Tasks')?></A>
			<li><a href="medals_earned_summary.php">Summary of Medals Earned Per Year</a></li>
			<li><a href="ranks_earned_summary.php">Summary of Ranks Earned Per Year</a></li>
			<LI><BR>
        <!--<LI><A HREF="admin_role.php"><?=T_('Manage Roles')?></A>
			<LI><A HREF="admin_message.php"><?=T_('Manage Messages')?></A>
            <LI><A HREF="admin_translate.php"><?=T_('Manage Translation')?></A>-->
			<LI><BR>
		    <LI><A HREF="admin_todolist.php<?=$url_id?>"><?=T_('Manage Todo List')?></A>
			<LI><A HREF="admin_todo_category.php"><?=T_('Manage Todo Categories')?></A>
			<LI><BR>
		<!--<LI><A HREF="admin_tanya_lines.php"><?=T_('Enter Lines of Tanya')?></A>
			<LI><A HREF="admin_tanya_goals.php"><?=T_('Define Goals for Tanya Tracks')?></A>
			<LI><BR>-->
			<LI><A HREF="admin_auction.php<?=$url_id?>"><?=T_('Manage Chinese Auctions')?></A>
			<LI><A HREF="admin_prize_auction.php<?=$url_id?>"><?=T_('Manage Chinese Auction Prizes')?></A>
			<LI><A HREF="auction/auction_run.php"><?=T_('Run Chinese Auction')?></A>
			<LI><A HREF="admin_auction_winners.php<?=$url_id?>"><?=T_('View Chinese Auction winners')?></A>
			<LI><A HREF="admin_user_auction_bulk.php<?=$url_id?>"><?=T_('Assign Chinese Auction prizes in bulk')?></A>
			<LI><A HREF="admin_prize_store.php<?=$url_id?>"><?=T_('Manage Store Prizes')?></A>
			<LI><A HREF="admin_store.php<?=$url_id?>"><?=T_('Manage Store Purchases')?></A>
			<LI><BR>
			<LI><A HREF="admin_auction_revision.php<?=$url_id?>"><?=T_('Manage Auctions')?></A>
			<LI><BR>  
			<LI><A HREF="admin_points_print.php<?=$url_id?>"><?=T_('Print Achievement Cards')?></A>
			<LI><A HREF="admin_points_templates.php<?=$url_id?>"><?=T_('Manage Achievement Card Templates')?></A>
			<LI><A HREF="admin_card_print.php<?=$url_id?>"><?=T_('Print Rank Cards')?></A>
			<LI><A HREF="admin_print_pdf.php<?=$url_id?>"><?=T_("Print Soldier's Files")?></A>
			<LI><A HREF="admin_stats.php<?=$url_id?>"><?=T_("View Soldier's Stats")?></A>
			<LI><A HREF="admin_mission_report.php<?=$url_id?>"><?=T_("View Mission Report/Stats")?></A>
			<LI><A HREF="admin_rank_report.php<?=$url_id?>"><?=T_("View Rank Report")?></A>
			<LI><A HREF="student_rank_report.php"><?=T_('Student Rank promotions - by date range')?></A> <span><font size='1' color='red'>*NEW*</font></span>
			<LI><A HREF="admin_medal_report.php<?=$url_id?>"><?=T_("View Medal Report")?></A>
			<LI><A HREF="admin_invite.php<?=$url_id?>"><?=T_('Invite Parents')?></A>
			<LI><BR>
			<LI><A HREF="admin_users_track.php<?=$url_id?>"><?=T_("Manage Soldier's Ladders/Years")?></A>
			<LI><A HREF="admin_users_tanya.php<?=$url_id?>"><?=T_("Manage Soldier's Tanya Setup")?></A>
			<LI><A HREF="admin_users_photo.php<?=$url_id?>"><?=T_("Manage Multiple Soldiers' photos")?></A>
			<LI><A HREF="admin_user_auction.php<?=$url_id?>"><?=T_("Manage Soldier's Auction Prizes")?></A>
			<LI><A HREF="admin_received_stats.php<?=$url_id?>"><?=T_('Mark Ranks and Medals as Received')?></A>
			<LI><A HREF="admin_users_register.php<?=$url_id?>"><?=T_("Soldiers' Registration")?></A>
			<LI><A HREF="admin_users_subject.php<?=$url_id?>"><?=T_("Soldiers' Program Enrollment")?></A>
			<LI><BR>
		<!--<LI><A HREF="admin_report_hakhel.php<?=$url_id?>"><?=T_("Soldiers Hakhel Report")?></A>
			<LI><A HREF="admin_report_wwtc.php<?=$url_id?>"><?=T_("Soldiers WWTC Report")?></A>
			<LI><A HREF="admin_report_auction.php<?=$url_id?>"><?=T_("Soldiers Auction Report")?></A>
			<LI><BR>
			<LI><A HREF="admin_date_tasks_marks.php<?=$url_id?>"><?=T_("Date Tasks Marks")?></A>
			<LI><A HREF="admin_marks_hakhel.php<?=$url_id?>"><?=T_("Soldiers Hakhel Marks")?></A>
			<li><br />-->
			<LI><A HREF="school_types.php"><?=T_("School Types")?></A>
			<LI><A HREF="edit_school_type.php"><?=T_("Edit School Types")?></A>
			<LI><A HREF="school_list.php"><?=T_("School List")?></A>
            <LI><A HREF="parent_list.php"><?=T_("Parent List")?></A>
            <LI><A HREF="soldiers.php"><?=T_("Student List")?></A>			    
			<LI><A HREF="school_possible_medals.php"><?=T_("School Possible Medals")?></A>
			<LI><A HREF="school_possible_boards.php"><?=T_("School Possible Boards")?></A>
			
	<? endif; ?> 
	<!-- if ($menu_type == 'super') : -->


	<!-- if ($menu_type == 'school') : -->
	<? if ($menu_type == 'school') : ?>
		<? $url_id = isset($school_id) ? "?school_id=$admin->school_id" : ''; ?>
    	<? if (false) : ?>
			<LI><A HREF="admin_school.php<?=$url_id?>&amp;action=edit"><?=T_('Manage My Base')?></A>
			<LI><A HREF="admin_class.php<?=$url_id?>"><?=T_('Manage My Platoons')?></A>
			
			<LI><A HREF="admin_user.php<?=$url_id?>"><?=T_('Manage My Soldiers')?></A>
			<LI><A HREF="admin_admin.php<?=$url_id?>"><?=T_('Manage Admins')?></A>			
			<LI><BR>
			
			<LI><A HREF="admin_prize_store.php<?=$url_id?>"><?=T_('Manage Store Prizes')?></A>
			<LI><A HREF="admin_store.php<?=$url_id?>"><?=T_('Manage Store Purchases')?></A>
			<LI><BR>
			<LI><A HREF="admin_auction_revision.php<?=$url_id?>"><?=T_('Manage Auctions')?></A>
			<LI><BR>
			<LI><A HREF="admin_points_print.php<?=$url_id?>"><?=T_('Print Achievement Cards')?></A>
			<LI><A HREF="admin_points_templates.php<?=$url_id?>"><?=T_('Manage Achievement Card Templates')?></A>
			<LI><A HREF="admin_card_print.php<?=$url_id?>"><?=T_('Print Rank Cards')?></A>
			<LI><A HREF="admin_received_stats.php<?=$url_id?>"><?=T_('Mark Ranks and Medals as Received')?></A>
			<LI><A HREF="admin_print_pdf.php<?=$url_id?>"><?=T_("Print Soldier's Files")?></A>
			<LI><A HREF="admin_stats.php<?=$url_id?>"><?=T_("View Soldier's Stats")?></A>
			<LI><A HREF="admin_mission_report.php<?=$url_id?>"><?=T_("View Mission Report/Stats")?></A>
			<LI><A HREF="admin_rank_report.php<?=$url_id?>"><?=T_("View Rank Report")?></A>
			<LI><A HREF="admin_medal_report.php<?=$url_id?>"><?=T_("View Medal Report")?></A>
			<LI><A HREF="admin_invite.php<?=$url_id?>"><?=T_('Invite Parents')?></A>
			<LI><BR>
			<LI><A HREF="admin_users_track.php<?=$url_id?>"><?=T_("Manage My Soldier's Ladders/Years")?></A>
			<LI><A HREF="admin_users_tanya.php<?=$url_id?>"><?=T_("Manage My Soldier's Tanya Setup")?></A>
			<LI><A HREF="admin_users_photo.php<?=$url_id?>"><?=T_("Manage Multiple Soldiers' Photos")?></A>
			<LI><A HREF="admin_user_auction.php<?=$url_id?>"><?=T_("Manage Soldier's Auction Prizes")?></A>
			<LI><A HREF="admin_users_register.php<?=$url_id?>"><?=T_("Soldiers' Registration")?></A>
			<LI><A HREF="admin_users_subject.php<?=$url_id?>"><?=T_("Soldiers' Program Enrollment")?></A>
			<LI><BR>
			<LI><A HREF="admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></A>
        <? endif; ?>
	<? endif; ?> 
	<!-- if ($menu_type == 'school') : -->

	<!-- if ($menu_type == 'class') : -->
	<? if ($menu_type == 'class' && 1 == 2) : ?>
		<? if (isset($_GET['class_id'])) $class_id = $_GET['class_id']; ?>
		<? if (isset($_GET['school_id'])) $school_id = $_GET['school_id'] ?> 
		<? $url_id = isset($class_id) ? "?class_id=$class_id" : "?school_id=$school_id"; ?>
		<LI><A HREF="admin_class.php<?=$url_id?>"><?=T_('Manage My Platoon')?></A>
		<LI><A HREF="admin_user.php<?=$url_id?>"><?=T_('Manage My Soldiers')?></A>
	<? endif; ?> 
	<!-- if ($menu_type == 'class') : -->

	<!-- if ($menu_type == 'team') : -->
	<? if ($menu_type == 'team') : ?>
		<? $url_id = "?team_id=$team_id"; ?>
		<LI><A HREF="admin_team.php<?=$url_id?>"><?=T_('Manage My Squad')?></A>
		<LI><A HREF="admin_user.php<?=$url_id?>"><?=T_('Manage My Soldiers')?></A>
	<? endif; ?> 
	<!-- if ($menu_type == 'team') : -->

	<!-- ***** PARENT ***** -->
	<? if ($menu_type == 'user') : ?>
		<!--<LI>
			<? //$form_name = "prnt_sr_" . $user_row['user_id']; ?>
			<form method="post" name="<?//=$form_name;?>" action="admin_parent_user.php">
				<input type="hidden" name="child_id" value="<?//=$user_row['user_id'];?>">
				<input type="hidden" name="school_id" value="<?//=$user_row['school_id'];?>">
				<A HREF="#" onclick="document.forms['<?//=$form_name;?>'].submit();"><?//=T_('Manage My Soldier')?></A>
			</form>-->
	<? endif; ?> 
	<!-- ***** PARENT ***** -->
	
	<!-- ***** CAMP ***** -->
	<? if ($menu_type == 'camp') : ?>
			<? 
				if ($admin_user['auth']['camp']) {
					header("Location:http://www.mashpia.com/camps/index.php"); 
				}
				else {
					header("Location: http://www.mashpia.com");
				}
			?>
	
			<li>
				<br />
			</li>
			
				<? //$_SESSION['admin_id'] = $admin_user['admin_id']; ?>
				
		
				<LI><A HREF="admin_global_campaigns.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Global Campaigns')?></A>
				<LI><A HREF="admin_camp_campaigns.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Camp Campaigns')?></A>
				<!--<LI><A HREF="admin_camp_divisions.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Manage Camp Divisions')?></A>
				<LI><A HREF="admin_group_types.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Manage Group Types')?></A>
				<LI><A HREF="admin_groups.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Manage Groups')?></A>
				<LI><A HREF="admin_campaigns.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Manage Campaigns')?></A>
				<LI><A HREF="admin_campaign_groups.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Manage Missions')?></A>
				<LI><A HREF="admin_campaign_tasks.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Manage Tasks')?></A>
				<LI><A HREF="admin_print_camp_cards.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Print Camp Achievement Cards')?></A>
				<LI><A HREF="admin_global_tasks.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Global Tasks')?></A>
				<LI></LI>
				<LI><A HREF="admin_camp_members.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Camp Members')?></A>
				<LI><A HREF="admin_camp_employees.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Employees')?></A>
				<LI></LI>
				<LI><A HREF="admin_assign_group_points.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Assign Group Points')?></A>				
				<LI><A HREF="admin_assign_member_points.php?admin_id=<?=$admin_user['admin_id'];?>"><?//=T_('Assign Member Points')?></A>-->				
				<LI><A HREF="admin_print_camp_cards.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Print Camp Achievement Cards')?></A>	
				<LI><A HREF="admin_assign_group_points.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Assign Group Points')?></A>
				<LI><A HREF="admin_assign_member_points.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Assign Member Points')?></A>
				
				<!-- MT Working with backend functions -->
				<!--<LI><A HREF="CampMotivationalSystem/dev/presentation/html/campAdmin.html">?=T_('NEW CAMP SYTEM');?></A>-->
				<!--<LI><A HREF="campstest/camps.php?camp_id=4">?//=T_('CAMPS');?></A>-->
				
				<LI>--------------------------------------------------------------------------------</LI>
				<LI><A HREF="admin_print_camp_cards.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Print Camp Achievement Cards')?></A>	
				<LI>--------------------------------------------------------------------------------</LI>
				
				<LI>--------------------------------------------------------------------------------</LI>
				<LI><A HREF="admin_print_camp_cards.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Print Camp Achievement Cards')?></A>	
				<LI>--------------------------------------------------------------------------------</LI>
				
				<LI>--------------------------------------------------------------------------------</LI>
				<LI><A HREF="admin_camp_members.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Members')?></A>
				<LI><A HREF="admin_assign_group_tasks.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Assign Tasks')?></A>				
				<LI>--------------------------------------------------------------------------------</LI>
				<LI><A HREF="admin_staff.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Staff')?></A>
				<LI><A HREF="admin_employee_roles.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Staff Roles')?></A>				
				<LI>--------------------------------------------------------------------------------</LI>
				<LI><A HREF="admin_group_types.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Group Types')?></A>
				<LI><A HREF="admin_divisions.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Divisions')?></A>
				<LI><A HREF="admin_groups.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Groups')?></A>				
				<LI>--------------------------------------------------------------------------------</LI>
				<LI><A HREF="admin_campaigns.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Global Campaigns')?></A>
				<LI><A HREF="admin_missions.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Global Campaign Missions')?></A>
				<LI><A HREF="admin_tasks.php?admin_id=<?=$admin_user['admin_id'];?>"><?=T_('Manage Global Campaign Missions Tasks')?></A>				
				<LI>--------------------------------------------------------------------------------</LI>				
				
				<LI><A HREF="CampMotivationalSystem/dev/presentation/"><?=T_('NEW CAMP SYTEM');?></A>
				<LI>--------------------------------------------------------------------------------</LI>			
			<!--<LI><A HREF="admin_terms.php"><?//=T_('Manage Terms')?></A>
			<LI><A HREF="admin_campaigns.php"><?//=T_('Manage Campaigns')?></A>
			<LI><A HREF="admin_campaign_tasks.php"><?//=T_('Manage Campaign Tasks')?></A>
			<LI><A HREF="admin_camp_members.php"><?//=T_('Manage Camp Members')?></A>-->
			
	<? endif; ?> <!-- if ($menu_type == 'camp') : -->
	<!-- ***** CAMP ***** -->
	
	<? if ($menu_type != 'camp' && $menu_type != 'school' && $menu_type != 'user' && 1 == 2) : //stuff for everybody, that does not need an ID ?>
			<LI><BR>
			<!-- <LI><A HREF="admin_view_task.php"><?//=T_('View Missions and Tasks')?></A> -->
			<!-- <LI><A HREF="admin_view_chains.php"><?//=T_('View Chains')?></A> -->
			<!-- <LI><A HREF="admin_view_date_tasks.php"><?//=T_('View Growth Plans')?></A> -->
			<LI><A HREF="admin_auction_print.php"><?=T_('Print Chinese Auction Prize Cards')?></A>
			<LI><A HREF="admin_tanya_lines_print.php"><?=T_('Print Tanya log sheet/bar-codes')?></A>
			

	<? endif; // ?>

		</UL>
		
	<? if (basename($_SERVER['PHP_SELF']) != 'admin.php') : ?>
		<SCRIPT type="text/javascript">
			document.getElementById('left_menu_overlay').style.display = 'none';
		</SCRIPT>
		
	</DIV>
	<? endif; ?> <!-- if (basename($_SERVER['PHP_SELF']) != 'admin.php') : -->
	
<? endif; ?> <!-- if (!empty($admin_user) && isset($menu_type)) : -->
