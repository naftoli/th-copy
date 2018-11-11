<?
// types of menues.
$menuSection = array('admin','school','programs','reports','logout');

// get the school_id of the request if variable not already set.
if ( !isset($school_id) )
	$school_id = gri('school_id', -1); 

/** Set the admin if not loaded yet. */
if (!isset($admin)) {
	include_once("camps/includes/classes/admin.php"); // load up the admin class.
	$query = mysql_query(
		"SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id']
	);
	$row = mysql_fetch_assoc($query);
	$admin = new admin($row);
	$admin->get_school_id();
	$admin->get_auths();
}

/** Make sure we know if we are dealing with a hebrew school or not */
if ( !isset( $h_school ) ) {
    $h_school = false; // default to false
    if ( isset( $admin->school_id ) ) {
        //check for hebrew schools
        $school_type_query = mysql_query( 
			"SELECT inst_id FROM schools WHERE school_id = " . $admin->school_id
		);
        $row = mysql_fetch_assoc( $school_type_query );
        $inst_id = $row['inst_id'];
        if ( $inst_id == 4 ) { // School type of 4 is hebrew schools.
            $h_school = true;
        }
    } // end if admin->school_id is set at all
}

/** Fetch the school store */
$school_store = 0;
if (isset($admin_user['auths']['school'])) {
	// make sure that there is something at the 0th index of the array
	if (isset($admin_user['auths']['school'][0])) {
		$query = mysql_query(
			"SELECT school_store FROM schools WHERE school_id=" . $admin_user['auths']['school'][0]
		);
		$row = mysql_fetch_assoc($query);
		$school_store = $row['school_store'];
	}
}

/** Get all the Tanya Only schools */
$tanya_only_query = mysql_query(
	"SELECT school_id FROM schools WHERE tanya = 1 AND chayolei = 0"
);
$tanyaOnlySchools = array();
while ($tanya_only = mysql_fetch_assoc($tanya_only_query)) {
	$tanyaOnlySchools[] = $tanya_only['school_id'];
}

/** Get all the Chidon Only schools */
$chidonSchools = array();
$chidon_school_query = mysql_query(
	"SELECT school_id FROM schools WHERE chidon = 1 AND chayolei = 0"
);
while ($rw = mysql_fetch_assoc($chidon_school_query)) {
	$chidonSchools[] = $rw['school_id'];
}
// hardcode the ballpeh only schools
$bpOnly = [ 82 ];
?>

<? if ( !isset($_SESSION['program_name']) || $_SESSION['program_name'] != 'children_tasks' ) { ?>
	<script src="/scripts/jquery-1.8.3.js"></script>
	<script type="text/javascript" src="/scripts/jquery.tools.min.js"></script>
	<script type="text/javascript" src="/scripts/jquery.styleselect.js"></script>
	<script type="text/javascript" src="/scripts/bug_report/bug_report.js"></script>
	<script type="text/javascript" src="/jquery-ui.js"></script>
    <script src="/scripts/scripts.js"></script>
	<script>
	$(function() {
		// get the current tab from the server
		var curr_tab = <?= isset($ui_type) ? '$(".list_parent a").index($(".list_parent a[title='.$ui_type.']"));' : "0" ?>;
		// 
		$( ".list_first:not(.list_small,.user_list)" ).tabs( 
			".list_first > ul", 
			{ tabs: '.list_parent', effect: 'slide', initialIndex: curr_tab }
		);
		// add submenus to the UI
		$( '#nav li:has(ul)' ).addClass( 'submenu' );
		// get the admin_id from the server
		var admin_id = <?=$admin_user['admin_id']?>;
		// special UI for the following admins
		if ( admin_id == 3 || admin_id == 237 || admin_id == 7 ) {
			var elem = $(".list_parent").has("a .icon");
			elem.click( function() {
				var val = $.trim( $( this ).text() );
				if ( val == 'Child Management' ) {
					$( this ).next( "ul" ).attr( 'style', 'display:block' );
				}    
			});
		}
		// fix links and make them work
		$('.list_parent.link a').click( function(){
			window.location = $( this ).attr( 'href' );
		});
		
		$( ".blog" ).click( function() {
			document.blog.submit();
		});
	});
	// run correctHeight when the page loads
	$( window ).load( function() {
		correctHeight();
	});
	// fix the hight of the sidebar
	function correctHeight() {
		$('#nav').animate({height:$('#content').height()},1000);
	}
	</script>
<? } // end if the program name is not set (or if set not equal to "children_tasks") ?>
	
<div id="wrapper">
	<div id="nav">
		<div class="col_title_bg"></div>
		<div class="col_title">Menu</div>

		<?php /** Get the Role name and ID */
		$query = mysql_query(
			"SELECT aa.role_id, r.role_name, u.school_id 
			FROM admin_auths AS aa JOIN roles AS r USING (role_id) 
			JOIN users AS u ON (aa.id=u.user_id) 
			WHERE aa.admin_id=" . $admin_user['admin_id'] . " GROUP BY aa.admin_id, aa.auth"
		);
		$row = mysql_fetch_assoc($query);
		$role_name = $row["role_name"];
		$role_id = $row["role_id"];
		
		if ( isset( $admin_user['auth'] ) ) { ?>
			<ul class="list_first">
				<li class="list_parent<?=isset($ui_type) && $ui_type == 'admin' ? ' current' : ''?>">
					<a href="/admin.php" onclick="document.location.href=this.href" title="admin"><div><span class="icon"><img height="28" width="28" alt="Home" src="/images/icon_admin_home.png"></span><?=T_('Home')?></div></a>
				</li>
				<ul class="list_second"></ul>

		<?php // if the user is a superuser or they have a school
			if ( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['school'] ) ) {
				/** CHIDON / TANYA ONLY SCHOOLS */
				if ( in_array( $admin->school_id, $tanyaOnlySchools ) || in_array( $admin->school_id, $chidonSchools ) ) { 
					$url_id = "?admin_id=" . $admin->admin_id . "&school_id=" . $admin->school_id;
					$url_id2 = $url_id === '' ? '?' : $url_id . '&amp;'; 
					?>
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
						<a href="/admin_school2.php<?=$url_id?>" title="school">
							<div>
								<span class="icon"><img height="28" width="28" alt="Base Management" src="/images/icon_dashboard.png"></span>
								<?=T_('Base Management')?>
							</div>
						</a>
					</li>

					<?php if ( $admin_user["beta"] ) { ?>
						<ul class="list_second">
							<li>
								<a href="#"><?=T_('Soldiers')?></a>
								<ul>
									<li><a href="/beta/bm/soldiers"><?=T_('View / Edit')?></a></li>
									<li><a href="/beta/bm/soldiers/cards"><?=T_('Rank Cards')?></a></li>
								</ul>
							</li>
							<li>
								<a href="/beta/bm/platoons"><?=T_('Platoons')?></a>
							</li>
							<li>
								<a href="/beta/bm/parents"><?=T_('Parents')?></a>
							</li>
							<li>
								<a href="/beta/bm/staff"><?=T_('Staff')?></a>
							</li>
							<li>
								<a href="/beta/bm/base"><?=T_('Base')?></a>
							</li>
						</ul>

						<li class="list_parent<?=isset( $ui_type ) && $ui_type == 'programs' ? ' current' : '' ?>">
							<a href="#" title="programs"><div>
								<span class="icon">
									<img height="28" width="28" alt="cart" src="/images/iconl_cart.png">
								</span><?=T_('Rewards Program')?>
							</div></a>
						</li>
							
						<ul class='list_second'>
							<li><a href="/beta/rewards/cards"><?=T_('Achievement Cards')?></a></li>
							<li><a href="/beta/rewards/tasks"><?=T_('Tasks')?></a></li>
							<li><a href="/beta/rewards/prizes"><?=T_('Prizes')?></a></li>
							<li><a href="/beta/rewards/orders"><?=T_('Orders')?></a></li>
							<li><a href="/beta/rewards/miles"><?=T_('Add / Subtract Miles')?></a></li>
						</ul>
					<?php  } else { ?>
						<ul class="list_second">
							<li>
								<a href="#"><?=T_('Students (Soldiers)')?></a>
								<ul>
									<li><a href="/admin_user.php<?=$url_id?>"><?=T_('View / Edit')?></a></li>
									<li><a href="/admin_user.php<?=$url_id2?>action=add"><?=T_('Add Individual')?></a></li>
									<li><a href="/admin_school_file.php<?=$url_id?>"><?=T_('Upload School or Class List')?></a></li>
									<li><a href="/admin_users_photo.php<?=$url_id?>"><?=T_("Upload Photos")?></a></li>
								</ul>
							</li>
							<li>
								<a href="#"><?=T_('Classes (Platoons)')?></a>
								<ul>
									<li><a href="/admin_class.php<?=$url_id?>"><?=T_('Manage')?></a></li>
									<li><a href="/admin_class.php<?=$url_id2?>action=add"><?=T_('Add New')?></a></li>
									<li><a href="/admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></a></li>
								</ul>
							</li>
							<?php if ( in_array($admin->school_id, $chidonSchools ) ) { ?>
								<li>
									<a href="#"><?=T_('Parents')?></a>
									<ul>
										<li><a href="/parent_list.php">Parent Accounts</a></li>
										<li><a href="/child_list.php">Parent / Children Accounts</a></li>
									</ul>
								</li>
							<?php } // end if school is a chidon school ?>
						</ul>	
					<? } ?>
				
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
						<a href="#" title="programs">
							<div>
								<span class="icon"><img height="28" width="28" alt="Chidon" src="/images/chidon.png"></span>
								<?=T_('Chidon')?>
							</div>
						</a>
					</li>
					
					<ul class='list_second'>
						<li><a href="/reports/chidon/chidon_enrollment.php">Registered for Chidon</a></li>
						<li><a href="/reports/chidon/shabbaton_enrollment.php">Shabbaton Enrolled Report</a></li>
						<li><a href="/reports/chidon/booklet_report.php">Study Guides</a></li>
						<li><a href="/reports/chidon/yahadus.php">Yahadus Books</a></li>
						<li><a href="/chidon_tests.php">Enter Chidon Test Marks</a></li>
						<li><a href="/chidon_school_reg.php">Enroll Chaperones</a></li>

						<?php if ($admin_user['auth'] == 'super') { ?>
							<li><a href="/enrollment_hq.php">Activate Enrollment HQ</a></li>
						<?php } else { ?>
							<li><a href="/enrollment.php">Activate Enrollment</a></li>
						<?php } // end superuser only ?>

						<li><a href="/review_enrollment.php">Review Enrollment</a></li>
						<li><a href="/chidon_review.php">Print Enrollment Info</a></li>
						
						<?php if ($admin_user['auth'] == 'super') { ?>
							<li><a href="/chidon/IDcards/">	Generate ID Cards	</a></li>
							<li><a href="/chidon/upload/">	Upload Spreadsheets	</a></li>
						<?php } ?>
					</ul>
				
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'info' ? ' current' : ''?>">
						<a href="#" title="campaigns">
							<div>
								<span class="icon"><img height="28" width="28" alt="campaigns" src="/images/parentIcons/Campaigns.gif"></span>
								<?=T_('Campaigns')?>
							</div>
						</a>
					</li>
				
					<ul class="list_second">
						<li>
							<a href="#">Tanya</a>
							<ul>
								<li><a href="/editSoldierLines2.php">Individual Marking</a></li>
								<li><a href="/yud_alef_nissan_choose.php">Yud Alef Nissan Reports</a></li>
							</ul>
						</li>
					</ul>

		<?php 	} else { // Regular Schools get the following menu
					$url_id = "?admin_id=" . $admin->admin_id . "&school_id=" . $admin->school_id;
					$url_id2 = $url_id === '' ? '?' : $url_id . '&amp;'; 
					// if they are a hebrew school...
					if ( $h_school ) { ?>
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
							<a href="/admin_school2.php<?=$url_id?>" title="school">
								<div>
									<span class="icon"><img height="28" width="28" alt="Base Management" src="/images/icon_dashboard.png"></span>
									<?=T_('Base Management')?>
								</div>
							</a>
						</li>

						<ul class="list_second">
							<li>
								<a href="#"><?=T_('Students (Soldiers)')?></a>
								<ul>
									<li><a href="/admin_user.php<?=$url_id?>"><?=T_('View / Edit')?></a></li>
									<li><a href="/admin_user.php<?=$url_id2?>action=add"><?=T_('Add Individual')?></a></li>
									<li><a href="/admin_school_file.php<?=$url_id?>"><?=T_('Upload School / Class')?></a></li>
									<li><a href="/admin_users_photo.php<?=$url_id?>"><?=T_("Upload Photos")?></a></li>
									<li><a href="/admin_users_register.php<?=$url_id?>"><?=T_("Registration")?></a></li> 
									<li><a href="/admin_card_hschool.php<?=$url_id?>"><?=T_('Print ID Cards')?></a></li>
								</ul>
							</li>
							<li>
								<a href="#"><?=T_('School (Base)')?></a>
								<ul>
									<li>
										<a href="/admin_school2.php<?=$url_id2?>action=edit"><?=T_('Edit Profile')?></a>
									</li>
								</ul>
							</li>
							<li>
								<a href="#"><?=T_('Classes (Platoons)')?></a>
								<ul>
									<li><a href="/admin_class.php<?=$url_id?>"><?=T_('Manage')?></a></li>
									<li><a href="/admin_class.php<?=$url_id2?>action=add"><?=T_('Add New')?></a></li>
								</ul>
							</li>
						</ul>
		
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'reports' ? ' current' : ''?>">
							<a href="/admin_report_list.php<?=$url_id?>" title="reports">
								<div>
									<span class="icon"><img height="28" width="28" alt="Reports" src="/images/icon_report.png"></span>
									<?=T_('Reports')?>
								</div>
							</a>
						</li>
		
						<ul class="list_second">					
							<li><a href="/registered_report.php"><?=T_("Registered Report")?></a></li> 
							<li><a href="/barcodes_report.php"><?=T_("Barcodes Report")?></a></li>
						</ul>

						<li>
							<a href="/reports/shipping/">
								<span class="icon">
									<img src="/images/icon_report.png" width="28" height="28" />Shipping Reports
								</span>
							</a>
						</li>
											
						<li class="list_parent link">
							<form name="setup_guide" method="post" action="admin_setup_guide_hschool.php">
								<input type="hidden" name="admin_id" value="<?=$admin_user['admin_id'];?>">
								<a href="#" onclick="document.forms['setup_guide'].submit();">
									<span class="icon">
										<img src="/images/icon_wizard.png" width="28" height="28" alt="Dashboard" />
									</span>
									Setup Guide
								</a>
							</form>
						</li>
						<?php if ( !$admin_user["beta"] ) { ?>
							<li>
								<a href="//mashpia.com/v2/login/frommashpia/school_id/<?=$admin->school_id ? $admin->school_id : $admin_user['auths']['school'][0]?>/admin_id/<?=$admin_user['admin_id']?>">
									<span class="icon">
										<img src="/images/icon_auction.png" width="28" height="28" alt="Dashboard" />Mileage Program
									</span>
								</a>
							</li>
						<?php } ?>
						<li>
							<a href="/helpdesk/?p=open" id="helpdesk_link" title="support">
								<div>
									<span class="icon"><img height="28" width="28" alt="Support" src="/images/icon_info.png"></span>
									<?=T_('Support')?>
								</div>
							</a>
						</li>
							
						<?php if ( isset($_GET['showBlog']) ) { ?>
							<li>
								<form name="blog" action="blog/wp-login.php" method="post">
									<a href="#" title="blog" class="blog">
										<div><span class="icon">
											<img height="28" width="28" alt="Blog" src="/images/icon_info.png">
										</span><?=T_('Blog')?></div>
									</a>
									<input type="hidden" name="log" value="<?=$uname?>" />
									<input type="hidden" name="pwd" value="<?=$pass?>" />
									<input type="hidden" name="mashpia" value="1" />
								</form>
							</li>
						<?php } // end if showBlog is set in the Get Headers
					} else { // non hebrew schools ?>     
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
							<a href="/admin_school2.php<?=$url_id?>" title="school">
								<div>
									<span class="icon"><img height="28" width="28" alt="Base Management" src="/images/icon_dashboard.png"></span>
									<?=T_('Base Management')?>
								</div>
							</a>
						</li>
						<?php if ( $admin_user["beta"] ) { ?>
							<ul class="list_second">
								<li>
									<a href="#"><?=T_('Soldiers')?></a>
									<ul>
										<li><a href="/beta/bm/soldiers"><?=T_('View / Edit')?></a></li>
										<?php if ($admin_user['auth'] == 'super') { ?>
											<li><a href="/admin_users_register.php<?=$url_id?>"><?=T_("Registration")?></a></li> 
										<?php } else { ?>
											<li><a href="/beta/bm/soldiers/registration"><?=T_("Registration")?></a></li> 
										<?php } ?>
										<li><a href="/beta/bm/soldiers/cards"><?=T_('Rank Cards')?></a></li>
									</ul>
								</li>
								<li>
									<a href="/beta/bm/platoons"><?=T_('Platoons')?></a>
								</li>
								<li>
									<a href="/beta/bm/parents"><?=T_('Parents')?></a>
								</li>
								<li>
									<a href="/beta/bm/staff"><?=T_('Staff')?></a>
								</li>
								<li>
									<a href="/beta/bm/base"><?=T_('Base')?></a>
								</li>
							</ul>

							<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
								<a href="#" title="programs">
									<div>
										<span class="icon">
											<img height="28" width="28" alt="Campaigns" src="/images/icon_admin_medal.png">
										</span>
										<?=T_('Missions')?>
									</div>
								</a>
							</li>

							<ul class='list_second'>
								<li><a href="/beta/missions/print"><?=T_('Print')?></a></li>
								<li><a href="/beta/missions/mark"><?=T_('Mark')?></a></li>
								<li><a href="/beta/missions/personalize"><?=T_('Personalize')?></a></li>
								<li><a href="/beta/missions/tasks"><?=T_('Add Task')?></a></li>
								<li><a href="/mission_sheets_checklist.php"><?=T_('Mission Checklist')?></a></li>
								<li><a href="/missions_report.php"><?=T_('Mission Report')?></a></li>
							</ul>

							<li class="list_parent<?=isset( $ui_type ) && $ui_type == 'programs' ? ' current' : '' ?>">
								<a href="#" title="programs"><div>
									<span class="icon">
										<img height="28" width="28" alt="cart" src="/images/iconl_cart.png">
									</span><?=T_('Rewards Program')?>
								</div></a>
							</li>
								
							<ul class='list_second'>
								<li><a href="/beta/rewards/cards"><?=T_('Achievement Cards')?></a></li>
								<li><a href="/beta/rewards/tasks"><?=T_('Tasks')?></a></li>
								<li><a href="/beta/rewards/prizes"><?=T_('Prizes')?></a></li>
								<li><a href="/beta/rewards/orders"><?=T_('Orders')?></a></li>
								<li><a href="/beta/rewards/miles"><?=T_('Add / Subtract Miles')?></a></li>
							</ul>
						<?php } else { ?>
							<ul class="list_second">
								<li><a href="/yearly_prize/forms/staff_info.php">Staff Management</a></li>
								<li>
									<a href="#"><?=T_('Teachers')?></a>
									<ul>
										<li><a href="/teacher_information.php">Teacher Information</a></li>
										<li><a href="/teacher_list.php">Teacher Logins</a></li>
										<li><a href="/teacher_letter.php">Teacher Letters</a></li>
										<li><a href="/teacher_settings.php">Teacher Settings</a></li>
									</ul>
								</li>
								<li>
									<a href="#"><?=T_('Students (Soldiers)')?></a>
									<ul>
										<li><a href="/admin_user.php<?=$url_id?>"><?=T_('View / Edit')?></a></li>
										<li><a href="/admin_user.php<?=$url_id2?>action=add"><?=T_('Add Individual')?></a></li>
										<li><a href="/admin_school_file.php<?=$url_id?>"><?=T_('Upload School or Class List')?></a></li>
										<li><a href="/admin_users_photo.php<?=$url_id?>"><?=T_("Upload Photos")?></a></li>
										<li><a href="/admin_users_register.php<?=$url_id?>"><?=T_("Registration")?></a></li> 
										<li><a href="/admin_card_print.php<?=$url_id?>"><?=T_('Print Rank Cards')?></a></li>
										<li><a href="/reports/users/student_info.php"><?=T_('Search By Serial / Barcode')?></a></li>
										<li><a href="/add_missions.php"><?=T_('Update Soldier\'s Missions')?></a></li>
										<li><a href="/add_medals.php"><?=T_('Update Soldier\'s Medals')?></a></li>
									</ul>
								</li>						
								<li>
									<a href="#"><?=T_('Classes (Platoons)')?></a>
									<ul>
										<li><a href="/admin_class.php<?=$url_id?>"><?=T_('Manage')?></a></li>
										<li><a href="/admin_class.php<?=$url_id2?>action=add"><?=T_('Add New')?></a></li>
										<li><a href="/admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></a></li>
									</ul>
								</li>
								
								<li>
									<a href="#"><?=T_('School (Base)')?></a>
									<ul>
										<li><a href="/admin_school2.php<?=$url_id2?>action=edit"><?=T_('Edit School Profile')?></a></li>
										<li><a href="/admin_profile.php">Edit Admin Profile</a></li>
										<li><a href="/settings.php<?=$url_id2?>">Settings</a></li>
										<li><a href="/admin_invoice_items.php<?=$url_id?>"><?=T_('Transaction History')?></a></li>
										<?php if( $admin_user['auth'] === "super" ) { ?>
											<li><a href="/admin_school_logos.php"><?=T_('Edit School Logos')?></a></li>
										<?php } ?>
									</ul>
								</li>
								
								<li>
									<a href="#"><?=T_('Parents')?></a>
									<ul>
										<li><a href="/parent_list.php">Parent Accounts</a></li>
										<li><a href="/child_list.php">Parent / Children Accounts</a></li>
									</ul>
								</li>		
							</ul>

							<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
								<a href="#" title="programs">
									<div>
										<span class="icon"><img height="28" width="28" alt="Campaigns" src="/images/icon_admin_medal.png"></span>
										<?=T_('Missions')?>
									</div>
								</a>
							</li>

							<ul class='list_second'>
								<!--<li><a href="summer_report.php"><?=T_('Mark Summer/Tishrei Missions')?></a>-->
								<li><a href="/print_missions2.php"><?=T_('Print Missions')?></a>
								<li><a href="/print_missionsYT.php"><?=T_('Print Succos Missions')?></a>
								<!--<li><a href="/print_missionsYT2.php"><?=T_('Print Yom Kippur / Succos Missions')?></a>-->
								<!-- <li><a href="/print_missions_summer.php"><?=T_('Print Summer Missions')?></a> -->
								<li><a href="/mark_missions2.php"><?=T_('Mark Missions')?></a>
								<li><a href="/sefer_hamitzvos.php"><?=T_('Mark Yahadus')?></a>
								<li><a href="/task_customization.php">Personalize Your Missions</a></li>
								<li><a href="/newTask.php">Add Tasks</a></li>
								<li><a href="/mission_sheets_checklist.php"><?=T_("Teacher's Missions Checklist")?></a></li>
								<li><a href="/missions_report.php"><?=T_('Missions Accomplished Report')?></a></li>
							</ul>
							<li class="list_parent<?=isset( $ui_type ) && $ui_type == 'programs' ? ' current' : '' ?>">
								<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Campaigns" src="/images/icon_auction.png"></span><?=T_('Achievement Cards')?></div></a>
							</li>
								
							<ul class='list_second'>
								<li><a href="/newAchievementTasks.php">Add Achievement Task</a></li>
								<li><a href="/manual_points.php">Add / Subtract Points</a></li>
								<li><a href="/remove_old_achievement_cards.php">Delete Old Achievement Cards</a></li>
							</ul>
						<?php } ?>
						
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
							<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Chidon" src="/images/chidon.png"></span><?=T_('Chidon')?></div></a>
						</li>
							
						<ul class='list_second'>
							<!--<li><a href="/uploadChidonFile.php">Upload File for Chidon</a></li>-->
							<!--<li><a href="/chidon_report.php">Registered for Chidon</a></li>-->
							<li><a href="/reports/chidon/chidon_enrollment.php">Registered Report</a></li>
							<li><a href="/reports/chidon/snapshot.php">Registered / Unregistered Report</a></li>
							<li><a href="/reports/chidon/booklet_report.php">Study Guides</a></li>
							<li><a href="/reports/chidon/yahadus.php">Yahadus Books</a></li>
							<li><a href="/reports/chidon/shabbaton_enrollment.php">Shabbaton Enrolled Report</a></li>
							<li><a href="/reports/chidon/walking_groups.php">Shabbaton Walking Report</a></li>
							<li><a href="/chidon_tests.php">Enter Chidon Test Marks</a></li>
							<li><a href="/chidon_school_reg.php">Enroll Chaperones</a></li>

							<?php if ($admin_user['auth'] == 'super') { ?>
								<li><a href="/enrollment_hq.php">Activate Enrollment HQ</a></li>
							<?php } else { ?>
								<li><a href="/enrollment.php">Activate Enrollment</a></li>
							<?php } // end if superuser or not (for enrollment links) ?>

							<li><a href="/review_enrollment.php">Review Enrollment</a></li>
							<li><a href="/chidon_review.php">Print Enrollment Info</a></li>

							<?php if ($admin_user['auth'] == 'super') { ?>
								<li><a href="/chidon/IDcards/">	Generate ID Cards	</a></li>
								<li><a href="/chidon/upload/">	Upload Spreadsheets	</a></li>
								<li><a href="/reports/chidon/"> Chidon Office Reports </a></li>
							<?php } // end special admin links ?>
						</ul>

						<li class="list_parent<?=isset($ui_type) && $ui_type == 'reports' ? ' current' : ''?>">
							<a href="/admin_report_list.php<?=$url_id?>" title="reports">
								<div>
									<span class="icon"><img height="28" width="28" alt="Reports" src="/images/icon_report.png"></span>
									<?=T_('Reports')?>
								</div>
							</a>
						</li>

						<ul class="list_second">
							<?php if ($admin_user['auth'] == 'super') { ?>
								<li><a href="/reports/"><?=T_("Office Reports")?></a></li>
							<?php } // end links to new reporitng system for upstairs ?>

							<?php if ($admin_user['auths']['school'][0] == 162) { ?>
								<li><a href="/admin_print_pdf.php">Print Certificates</a></li>
							<?php } // end special links for school #162 ?>
							
							<li><a href="/registered_report.php"><?=T_("Registered Report")?></a></li>
							<li><a href="/parent_report.php">Parents Report</a></li>
							<li><a href="/non_registered_report.php"><?=T_("Not Yet Registered Report")?></a></li> 
							<li><a href="/barcodes_report.php"><?=T_("Barcodes Report")?></a></li>              
							<li><a href="/miles.php"><?=T_('Miles Report')?></a></li>
							<li><a href="/auctionMiles.php">Auction Miles Report</a></li>
							<li><a href="/missions_report.php"><?=T_('Missions Done Report')?></a></li>
							<li>
								<a href="#">Stickers</a>
								<ul>
									<li><a href="/stickers_report.php"><?=T_('Total Stickers Earned')?></a></li>
									<li><a href="/stickers_report_by_week.php"><?=T_('Total Stickers Earned By Date')?></a></li>
									<li><a href="/stickers_report_by_child.php">Total Stickers Earned By Child</a></li>
								</ul>
							</li>
						
							<li>
								<a href="#">Birthdays</a>
								<ul>
									<li><a href="/names_report.php">Birthday Report</a></li>
									<li><a href="/find_birthdays_report.php">Birthdays By Date Range</a></li>
									<li><a href="/hebrew_names.php">Update Birthdays and Hebrew Names</a></li>
								</ul>
							</li>
							
							<li>
								<a href="#">Ranks / Medals</a>
								<ul>
									<li><a href="/rank_report.php"><?=T_('Rank Report')?></a></li>
									<li><a href="/admin_received_stats.php<?=$url_id?>"><?=T_('Mark Ranks and Medals as Received')?></a></li>
								</ul>
							</li>
						</ul>

						<li>
							<a href="/reports/shipping/">
								<span class="icon">
									<img src="/images/icon_report.png" width="28" height="28" />Shipping Reports
								</span>
							</a>
						</li>
						
						<li class="list_parent<?= isset($ui_type) && $ui_type == 'info' ? ' current' : '' ?>">
							<a href="#" title="campaigns">
								<div><span class="icon">
									<img height="28" width="28" alt="campaigns" src="/images/parentIcons/Campaigns.gif"></span>
									<?=T_('Campaigns')?>
								</div>
							</a>
						</li>
						
						<ul class="list_second">
							<li>
								<a href="#">Tanya</a>
								<ul>
									<li><a href="/editSoldierLines2.php">Individual Marking</a></li>
									<li><a href="/yud_alef_nissan_choose.php">Yud Alef Nissan Reports</a></li>
								</ul>
							</li>
							<li>
								<a href="#">Tehillim</a>
								<ul>
									<li><a href="/mark_tehillim2.php">Mark Shabbos Mevorchim Tehillim</a></li>
									<li><a href="/choose_sm_report.php"><?=T_('Shabbos Mevorchim Report')?></a></li>
									<li><a href="/tehillim_quotas.php">Check Your Tehillim Quotas</a></li>
									<li><a href="/admin_users_track.php">Change Tehillim Ladder/Quota</a></li>
									<li><a href="https://vimeo.com/195384916">Shabbos Mevorchim Tutorial Video</a></li>
									<li><a href="quota_cards.php">Quota Cards</a></li>
								</ul>
							</li>
						</ul>
						
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'info' ? ' current' : ''?>">
							<a href="#" title="rally"><div><span class="icon"><img height="28" width="28" alt="rally" src="/images/parentIcons/Rally.gif"></span><?=T_('Rally')?></div></a>
						</li>
						
						<ul class="list_second">
							<li><a href="/promotion_report.php"><?=T_('Promotion Picture Report')?></a></li>
							<li><a href="/medal_rank_ceremony3.php"><?=T_("Teacher's Medal Ceremony Report")?></a></li>
							<li><a href="/raffles/shared/forms/winners_form.php"><?=T_("Raffle Winners")?></a></li>
							<?php if ($admin_user['auth'] == 'super') { ?>
								<li><a href="/missing_medals.php">Missing Medals</a></li>
							<?php } // end cuetom super user links ?>
						</ul>
						
						<li>
							<a href="/raffles/">
								<span class="icon">
									<img src="/images/icon_auction.png" width="28" height="28" />Raffles
								</span>
							</a>
						</li>
						
						<li>
							<a href="/yearly_prize/reports/">
								<span class="icon">
									<img src="/images/icon_auction.png" width="28" height="28" />Yearly Prize
								</span>
							</a>
						</li>

						<li>
							<a href="/auction/winners3.php">
								<span class="icon">
									<img src="/images/icon_auction.png" width="28" height="28" />Auction Winners
								</span>
							</a>
						</li>
						
						<!--****************************************************-->
						<!-- ***** If the school is using the camp system ***** -->
						<!--****************************************************-->
						<?php if ($school_and_camp == true && false) { // disabled for now ?>

							<li class="list_parent slide">
								<a href="/camps/content.php?output=staff_profile&admin_id=<?=$admin_id;?>">
									<span class="icon"><img src="/images/icon_dashboard.png" width="28" height="28" alt="Dashboard" /></span>
									My Profile
								</a>
							</li>

							<ul class="list_second slide">
							</ul>

							<li class="list_parent slide">
								<a href="/camps/content.php?output=points">
									<span class="icon"><img src="/images/icon_points.png" width="28" height="28" alt="Points" /></span>
									Points
								</a>
							</li>

							<ul class="list_second slide"></ul>

							<li class="list_parent slide">
								<a href="#print">
									<span class="icon"><img src="/images/icon_print.png" width="28" height="28" alt="Print Center" /></span>
									Print Center
								</a>
							</li>

							<ul class="list_second slide">
								<li>
									<a href="/camps/content.php?output=rankcards">
										<span class="icon"><img src="/images/icon_rank_card.png" width="28" height="28" alt="Settings" /></span>
										Rank Cards
									</a>
								</li>
								<li>
									<a href="/camps/content.php?output=mission_sheets">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Mission Sheets
									</a>
								</li>
								<li>
									<a href="#">
										<span class="icon"><img src="/images/icon_add.png" width="22" height="22" alt="Add" /></span>
										Print Cards
									</a>
								</li>
							</ul>

							<li class="list_parent slide">
								<a href="#control">
									<span class="icon"><img src="/images/icon_control.png" width="28" height="28" alt="Settings" /></span>
									Control Panel
								</a>
							</li>
							<ul class="list_second slide">
								<li>
									<a href="/camps/content.php?output=campprofile">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Camp Profile
									</a>
								</li>
								<li>
									<a href="/camps/content.php?output=grouptypes">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Groups
									</a>
								</li>
								<li>
									<a href="/camps/content.php?output=missions_dash">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Missions
									</a>
								</li>
								<li>
									<a href="/camps/content.php?output=campers">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Campers
									</a>
								</li>
								<li>
									<a href="/camps/content.php?output=staff">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Staff
									</a>
								</li>
								<li>
									<a href="/camps/content.php?output=store">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Store
									</a>
								</li>
								<li>
									<a href="/camps/content.php?output=gettingstarted">
										<span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>
										Getting Started
									</a>
								</li>
							</ul>
						<?php } // end camp system ?> <!--  if ($school_and_camp == true) : -->
						<!--****************************************************-->
						<!-- ***** If the school is using the camp system ***** -->
						<!--****************************************************-->

						<?php if ($school_store > 0 && false) { ?>
							<li class="list_parent">
								<a href="//v2.mashpia.com/login/auto/user_id/<?=$admin->admin_id;?>/email/<?=$admin->admin_email;?>/password/<?=MD5($admin->password);?>">
									<span class="icon">
										<img src="/images/icon_dashboard.png" width="28" height="28" alt="Dashboard" />
									</span>
									Store
								</a>
							</li>
						<? } ?>
						
						<li class="list_parent link">
							<form name="setup_guide" method="post" action="/admin_setup_guide.php">
								<input type="hidden" name="admin_id" value="<?=$admin_user['admin_id'];?>">
								<a href="#" onclick="document.forms['setup_guide'].submit();">
									<span class="icon">
										<img src="/images/icon_wizard.png" width="28" height="28" alt="Dashboard" />
									</span>
									Setup Guide
								</a>
							</form>
						</li>
						<?php if ( !$admin_user["beta"] ) { ?>
							<li>
								<a href="//mashpia.com/v2/login/frommashpia/school_id/<?=$admin->school_id ? $admin->school_id : $admin_user['auths']['school'][0]?>/admin_id/<?=$admin_user['admin_id']?>">
									<span class="icon">
										<img src="/images/icon_auction.png" width="28" height="28" alt="Dashboard" />
										Mileage Program
									</span>
								</a>
							</li>
						<?php } ?>
						<li>
							<a href="/helpdesk/?p=open" id="helpdesk_link" title="support"><div><span class="icon"><img height="28" width="28" alt="Support" src="/images/parentIcons/support icon.gif"></span><?=T_('Support')?></div></a>
						</li>
						
						<?php if (isset($_GET['showBlog'])) { ?>
							<li>
								<form name="blog" action="blog/wp-login.php" method="post">
									<a href="#" title="blog" class="blog">
										<div><span class="icon">
											<img height="28" width="28" alt="Blog" src="/images/icon_info.png">
										</span><?=T_('Blog')?></div>
									</a>
									<input type="hidden" name="log" value="<?=$uname?>" />
									<input type="hidden" name="pwd" value="<?=$pass?>" />
									<input type="hidden" name="mashpia" value="1" />
								</form>
							</li>
						<? }
					} // plain non-hebrew schools
				} // end non tanya/chidon schools
			} // end ?>

		<?php  // reset $url_id

			if( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['class'] ) ) {
				$url_id = isset($class_id) ? "?class_id=$class_id" : '';
			}

			if( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['team'] ) ) {
				$url_id = isset($team_id) ? "?team_id=$team_id" : '';
			}
			
			if( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['user'] ) ) {
				$url_id = isset($user_id) ? "?user_id=$user_id" : '';
			}?>
	
			<li class="list_parent">
				<a href="/logout.php" onclick="document.location.href=this.href">
					<span class="icon"><img height="28" width="28" alt="Logout" src="/images/parentIcons/logout.gif"></span>
					<?=T_('Logout')?>
				</a>
			</li>
		</ul>

	<?php
		} else { 
			//dual_auth
		}
	?>

	</div>
	<div id="content">
		<div class="col_title_bg"></div>
		<div class="slider_container">
			<div class="slider">
				<div class="col_title"></div>
				<div class="col_content">
					<div class="header header_<?=$align_start?>">
					<h1>
						<img src="/images/header.gif" width="30" height="30" alt="World Wide Tehillim Club"> Tzivos Hashem
						<?php if ( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['school'] ) ) { ?>
						<!-- BEGIN Comm100 Live Chat Button Code -->
							<div>
								<div id="comm100_LiveChatDiv"></div>
								<div id="comm100_track" style="z-index:99;">
									<span style="font-size:10px; font-family:Arial, Helvetica, sans-serif;color:#555"></span>
								</div>
							</div>
						<!--</center>-->
						<!-- End Comm100 Live Chat Button Code -->
						<?php } else if ( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['user'] ) ) { ?>
							<!-- BEGIN Comm100 Live Chat Button Code -->
							<!--<center>-->
							<div>
								<div id="comm100_LiveChatDiv"></div>
								<div id="comm100_track" style="z-index:99;"></div>
							</div>
						<?php 	} ?>
					</h1>
					<?php if( isset( $admin_user['auth'] ) ) { ?>
						<div id="menu">
							<a href="/admin.php"><?=T_('Home')?></a>
							<?php if( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['school'] ) ) {
								$url_id = isset($school_id) ? "?school_id=$school_id" : ''; ?>
								
								<a href="/admin_school2.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'school' ? 'class="selected"' : ''?>>
									<?=T_('Base Management')?>
								</a>
								<a href="/admin_school_subjects.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'programs' ? 'class="selected"' : ''?>>
									<?=T_('Campaigns')?>
								</a>
								<a href="/admin_report_list.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'reports' ? 'class="selected"' : ''?>>
									<?=T_('Reports')?>
								</a>
							<?php }
							
							if( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['class'] ) ) {
								$url_id = isset($class_id) ? "?class_id=$class_id" : '';
							}

							if( $admin_user['auth'] == 'super' || !empty( $admin_user['auths']['team'] ) ) {
								$url_id = isset($team_id) ? "?team_id=$team_id" : '';
							}
							
							if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['user'])){
								$url_id = isset($user_id) ? "?user_id=$user_id" : '';
							} ?>

							<a href="/logout.php"><?=T_('Logout')?></a>
						</div>

					<? } else { 
						//dual_auth
					}?>
				</div>
			<!-- close .slider in footer -->
		<!-- close .slider_container in footer -->
	<!-- close #content in footer -->
<!-- close wrapper in footer -->
