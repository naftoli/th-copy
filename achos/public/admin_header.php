<?
$menuSection = array('admin','school','programs','reports','logout');
$school_id = gri('school_id', -1);

if (!isset($admin)) {
	include_once("camps/includes/classes/admin.php");
	$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$admin = new admin($row);
	$admin->get_school_id();
	$admin->get_auths();
}
?>
<script type="text/javascript" src="jquery-1.8.1.min.js"></script>
<SCRIPT type="text/javascript" src="scripts/jquery.tools.min.js"></SCRIPT>
<script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>

    <script src="scripts/scripts.js"></script>
	<script>
	 $(function() {
		// <?=isset($ui_type) ? array_search($ui_type, $menuSection) : "0" ?>;
		var curr_tab = <?=isset($ui_type) ? '$(".list_parent a").index($(".list_parent a[title='.$ui_type.']"));' : "0" ?>;
		$(".list_first:not(.list_small,.user_list)").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide', initialIndex: curr_tab});
		$('#nav li:has(ul)').addClass('submenu');

		$('.list_parent.link a').click(function(){
			window.location = $(this).attr('href');
		});
		
		$(".blog").click( function() {
		    document.blog.submit();
		});
	 });
	$(window).load(function() {
		correctHeight();
	});

	function correctHeight() {
		$('#nav').animate({height:$('#content').outerHeight()},1000);
	}
	</script>
	
	<div id="wrapper">

		<div id="nav">

			<div class="top">
				<img src="images/logo-achos-hatemimim.png" width="180" />
			</div>
			<!--<div class="col_title_bg"></div>

			<div class="col_title">Menu</div>-->

<? if (isset($admin_user['auth'])) : ?>
				<ul class="list_first">

					<li class="list_parent<?=isset($ui_type) && $ui_type == 'admin' ? ' current' : ''?>">
						<a href="admin.php" onclick="document.location.href=this.href"><div><span class="icon"><img height="28" width="28" alt="Home" src="images/icons/home.png"></span><?=T_('Home')?></div></a>
					</li>

					<ul class="list_second">
					</ul>

	<? if ($admin_user['auth'] == 'super' || !empty($admin_user['auths']['school'])) : ?>
		
		<? 	
		//$url_id = isset($school_id) ? "?school_id=$admin->school_id" : '';
		$url_id = "?admin_id=" . $admin->admin_id . "&school_id=" . $admin->school_id;
		$url_id2 = $url_id === '' ? '?' : $url_id . '&amp;'; 
		?>

					<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
						<a href="admin_school.php<?=$url_id?>"><div><span class="icon">
							<img height="28" width="28" alt="Base Management" src="images/icon_dashboard.png"></span><?=T_('Base Management')?></div>
						</a>
					</li>

					<ul class="list_second">
						<!--
						<li>
							<a href="#"><?=T_('Students (Soldiers)')?></a>

							<ul>
								<li><a href="#"><?=T_('View / Edit')?></a></li>
								<!--
								<li><a href="admin_user.php<?=$url_id2?>action=add"><?=T_('Add Individual')?></a></li>
								<li><a href="admin_school_file.php<?=$url_id?>"><?=T_('Upload School or Class List')?></a></li>
								<li><a href="admin_users_photo.php<?=$url_id?>"><?=T_("Upload Photos")?></a></li>
								<li><a href="https://mashpia.com/admin_users_register_new.php<?=$url_id?>"><?=T_("Registration")?></a></li> 
								<li><a href="admin_card_print.php<?=$url_id?>"><?=T_('Print Rank Cards')?></a></li>
								<li><a href="add_missions.php"><?=T_('Update Soldier\'s Missions')?></a></li>
                                <li><a href="add_medals.php"><?=T_('Update Soldier\'s Medals')?></a></li>
								<!--<li><a href="admin_users_subject.php<?=$url_id?>"><?=T_("Campaign Enrollment")?></a></li>-->
								<!--<li><a href="admin_user_kiosk.php<?=$url_id?>"><?=T_('Kiosk Mission Entry')?></a></li>
							</ul>

						</li>
						
						<li>
                            <a href="#"><?=T_('Classes (Platoons)')?></a>
                            <ul>
                                <li><a href="admin_class.php<?=$url_id?>"><?=T_('Manage')?></a></li>
                                <li><a href="admin_class.php<?=$url_id2?>action=add"><?=T_('Add New')?></a></li>
                                <li><a href="admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></a></li>
                            </ul>
                        </li>
                       -->
						<li>
							<a href="#"><?=T_('School (Base)')?></a>
							<ul>
								<!--<li><a href="admin_school.php<?=$url_id2?>action=edit"><?=T_('Edit School Profile')?></a></li>-->
                                <li><a href="admin_profile.php">Edit Admin Profile</a></li>
                                <!--<li><a href="students.php">Adjust Student Levels</a></li>-->
                                <li><a href="parent_list.php">Student Accounts</a></li>
								<li><a href="info_report.php">Add / Edit Students</a></li>
                                <!--<li><a href="points_report.php">Student Points Report</a></li>-->
							</ul>
						</li>
					</ul>
					
					<!--<li class="list_parent"><a href="date_tasks_print.php" onclick="document.location.href=this.href">Print Scoreboards</a></li>-->

                    <!--<li class="list_parent"><a href="editTasks.php" onclick="document.location.href=this.href"><?=T_('Edit Tasks')?></a></li>-->
                    <!--<li class="list_parent"><a href="achos_tasks.php" onclick="document.location.href=this.href"><?=T_('Setup Scoreboard')?></a></li>-->
                    <!--
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
						<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Campaigns" src="images/icon_admin_medal.png"></span><?=T_('Missions')?></div></a>
					</li>
					
					<ul class='list_second'>
						
						<li><a href="summer_report.php"><?=T_('Mark Summer/Tishrei Missions')?></a>
						
                        <li><a href="date_tasks_print.php"><?=T_('Print Missions')?></a></li>
                        <li><a href="date_tasks_report_new.php"><?=T_('Mark Missions')?></a></li>
                        <!--<li><a href="date_tasks_print_summer.php"><?=T_('Print Summer Missions')?></a></li>-->
                        <!--<li><a href="sefer_hamitzvos.php"><?=T_('Mark Yahadus')?></a>-->						    
						<!--<li><a href="date_tasks_print_two.php"><?=T_('Print Pesach Missions')?></a>-->
						<!--<li><a href="date_tasks_print_rh.php"><?=T_('Print Rosh Hashana Missions')?></a>-->
						<!--<li><a href="date_tasks_print_shavuos.php"><?=T_('Print Shavuos Missions')?></a>
					</ul>
					-->
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'reports' ? ' current' : ''?>">
						<a href="" title="missions"><div><span class="icon"><img height="28" width="28" alt="Reports" src="images/icon_report.png"></span><?=T_('Missions')?></div></a>
					</li>
					
					<ul class='list_second'>
						<li><a href="mark_missions.php"><?=T_('Mark Missions')?></a>
					</ul>
						
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'reports' ? ' current' : ''?>">
						<a href="admin_report_list.php<?=$url_id?>" title="reports"><div><span class="icon"><img height="28" width="28" alt="Reports" src="images/icon_report.png"></span><?=T_('Reports')?></div></a>
					</li>
					
					<ul class="list_second">
						<!--
						<li>
							<a href="#"><?=T_('Soldier Reports')?></a>
							<ul>
								<li><a href="admin_stats.php<?=$url_id2?>subjects=army&amp;start_date=0&amp;cols[s]=1&amp;cols[m]=1&amp;cols[r]=1&amp;order_by=.r&amp;registered_only=1&amp;report_type=rank&amp;view=1"><?=T_('Base Rank Report')?></a></li>
								<li><a href="admin_stats.php<?=$url_id2?>subjects=army&amp;start_date=0&amp;cols[s]=1&amp;cols[m]=1&amp;cols[r]=1&amp;order_by=cr&amp;report_type=rank_class&amp;view=1"><?=T_('Platoon Rank Report')?></a></li>
							</ul>
						</li>
												
						<li><a href="names_report.php">Birthday Report</a></li>
						
						<li><a href="find_birthdays_report.php">Birthdays By Date Range</a></li>
						
						<li><a href="hebrew_names.php">Update Birthdays and Hebrew Names</a></li>
						
						<li><a href="registered_report.php"><?=T_("Registered Report")?></a></li> 
                        
                        <!--<li><a href="barcodes_report.php"><?=T_("Barcodes Report")?></a></li>
						
						<li><a href="medal_rank_ceremony.php"><?=T_('Medal and Rank Ceremony')?></a></li>
						
						<!--<li><a href="tanya_enroll.php"><?=T_('TBP Hachloto Form')?></a></li>

						<li><a href="shabbos_mevorchim.php"><?=T_('Shabbos Mevorchim School & Class Report')?></a></li>
						
                        <li><a href="shabbos_mevorchim_summary.php"><?=T_('Shabbos Mevorchim Army Wide Report')?></a></li>

						<!--<li><a href="charge_card_report.php"><?=T_('Charge Card Report')?></a></li>
						
						<li><a href="promotion_report.php"><?=T_('Promotion Picture Report')?></a></li>
						
						<!--<li><a href="mission_sheets_checklist.php"><?=T_('Missions Checklist')?></a></li>-->
						
						<!--<li><a href="medal_report.php"><?=T_('Medal and Rank Statement')?></a></li>
						
						<li><a href="admin_print_pdf.php"><?=T_('Print Certificate')?></a></li>
						
						<li><a href="admin_received_stats.php<?=$url_id?>"><?=T_('Mark Ranks and Medals as Received')?></a></li>
						
                        <li><a href="miles.php"><?=T_('Miles Report')?></a></li>
                        
                        <li><a href="missions_report.php"><?=T_('Missions Report')?></a></li>
                        
                        <!--<li><a href="stickers_report.php"><?=T_('Stickers Report')?></a></li>-->
                        
                        <!--<li><a href="stickers_report_by_week.php"><?=T_('Stickers Report By Date')?></a></li>
                        
                        <li><a href="rank_report.php"><?=T_('Rank Report')?></a></li>   
                        
                        <!--<li><a href="teacherInfoReport.php">Teacher's Info</a></li>-->
						
						<!--
						<li>
							<a href="#"><?=T_('Mileage')?></a>
							<ul>
								<li><a href="admin_stats.php<?=$url_id2?>subjects=all&amp;start_date=<?=chaiElul()?>&amp;cols[p]=1&amp;cols[y]=1&amp;cols[u]=1&amp;cols[w]=1&amp;order_by=.y&amp;registered_only=1&amp;report_type=points&amp;view=1"><?=T_('Base Mileage Report')?></a></li>
								<li><a href="admin_stats.php<?=$url_id2?>subjects=all&amp;start_date=<?=chaiElul()?>&amp;cols[p]=1&amp;cols[y]=1&amp;cols[u]=1&amp;cols[w]=1&amp;order_by=cy&amp;report_type=points_class&amp;view=1"><?=T_('Platoon Mileage Report')?></a></li>
							</ul>
						</li>
						
						<li>
							<a href="#"><?=T_('Medal/Rank')?></a>
							<ul>
								<li><a href="admin_medal_report.php<?=$url_id?>"><?=T_('Medal Report')?></a></li>
								<li><a href="admin_rank_report.php<?=$url_id?>"><?=T_('Rank Report')?></a></li>
							</ul>
						</li>
                   
						<li>
							<a href="#"><?=T_('Create')?></a>
							<ul>
								<li><a href="admin_stats.php<?=$url_id?>"><?=T_('Create your own Stats Report')?></a></li>
							<li><a href="admin_mission_report.php<?=$url_id?>"><?=T_('Mission Report')?></a></li>
							</ul>
						</li>
                        -->
						<li><a href="class_report.php">Create your own General Report</a></li>
						<li><a href="weekly_report.php">Create your own Weekly Report</a></li>
					</ul>
				    <!--
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'info' ? ' current' : ''?>">
						<a href="#" title="info"><div><span class="icon"><img height="28" width="28" alt="Info" src="images/icon_info.png"></span><?=T_('About Us')?></div></a>
					</li>
				    
				    <ul class="list_second">
                        <li>
                            <a href="faq.php">Plan of Action</a>
                        </li>
                    </ul>
                    
                    <ul class="list_second">
                        <li>
                            <a href="faq.php">Campaigns</a>
                        </li>
                    </ul>
                    
                    <ul class="list_second">
                        <li>
                            <a href="faq.php">Updates</a>
                        </li>
                    </ul>
                    
                    <ul class="list_second">
                        <li>
                            <a href="faq.php">FAQ's</a>
                        </li>
                    </ul>
                -->
    <? endif;?>

	<? if (!empty($admin_user['auths']['user'])) : ?>
	        
	        <li>
                <a href="faq.php"><span class="icon"><img height="28" width="28" src="images/icons/about.jpeg"></span><?=T_('How it Works')?></a>
            </li>
            
	        <li>
                <a href="about.php"><span class="icon"><img height="28" width="28" src="images/icons/about.jpeg"></span><?=T_('About Us')?></a>
            </li>
			
            <li>
               <a href="personal.php"><span class="icon"><img height="28" width="28" src="images/icons/personal.jpeg"></span><?=T_('Personal Profile')?></a>
            </li>
            <!--   
			<li>
                <a href="student_tasks.php"><span class="icon"><img height="28" width="28" src="images/icon_admin_medal.png"></span><?=T_('Setup My Scoreboard')?></a>
            </li>
            -->
			<li>
                <a href="date_tasks_print.php"><span class="icon"><img height="28" width="28" src="images/icons/print.png"></span><?=T_('Print My Scoreboard')?></a>
            </li>
            
            <li>
                <a href="date_tasks_report_new.php"><span class="icon"><img height="28" width="28" src="images/icons/mark.jpeg"></span><?=T_('Mark My Scoreboard')?></a>
            </li>

	<? endif; ?>
	
    <li class="list_parent"><a href="logout.php" onclick="document.location.href=this.href"><span class="icon"><img height="28" width="28" alt="Logout" src="images/icons/logout.gif"></span><?=T_('Logout')?></a></li>

</ul>

<? endif;?>

</div>

<div id="content">
<div class="col_title_bg">
</div>
<div class="slider_container">
<div class="slider">
<div class="col_title">

</div>
<div class="col_content">
<DIV class="header header_<?=$align_start?>">

<?if(isset($admin_user['auth'])):?>

<DIV id="menu">
<A HREF="admin.php"><?=T_('Home')?></A>

<?if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['school'])):?>
<? $url_id = isset($school_id) ? "?school_id=$school_id" : ''; ?>

<A HREF="admin_school.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'school' ? 'class="selected"' : ''?>><?=T_('Base Management')?></A>
<A HREF="admin_school_subjects.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'programs' ? 'class="selected"' : ''?>><?=T_('Campaigns')?></A>
<A HREF="admin_report_list.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'reports' ? 'class="selected"' : ''?>><?=T_('Reports')?></A>
<?endif;?>

<?if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['user'])):?>
<? $url_id = isset($user_id) ? "?user_id=$user_id" : ''; ?>

<?endif;?>

<A HREF="logout.php"><?=T_('Logout')?></A>
</DIV>

<?endif;?>

</DIV>
