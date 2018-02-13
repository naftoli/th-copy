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

if ( !isset( $h_school ) ) {
    $h_school = false;
    if ( isset( $admin->school_id ) ) {
        //check for hebrew schools
        $sql = "select inst_id from schools where school_id = " . $admin->school_id;
        $res = mysql_query( $sql );
        $row = mysql_fetch_assoc( $res );
        $inst_id = $row['inst_id'];
        if ( $inst_id == 4 ) {
            $h_school = true;
        }
    }
}

$school_store = 0;
if (isset($admin_user['auths']['school'])) {

	if (isset($admin_user['auths']['school'][0])) {
		$sql = "SELECT school_store FROM schools WHERE school_id=" . $admin_user['auths']['school'][0];
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$school_store = $row['school_store'];
	}
}
//print_r($admin_user);
$s = "select school_id from schools where tanya = 1 and chayolei = 0";
$r = mysql_query($s);
$tanyaOnlySchools = array();
while ($rw = mysql_fetch_assoc($r)) {
	$tanyaOnlySchools[] = $rw['school_id'];
}

$chidonSchools = array();
$s = "select school_id from schools where chidon = 1 and chayolei = 0";
$r = mysql_query($s);
while ($rw = mysql_fetch_assoc($r)) {
	$chidonSchools[] = $rw['school_id'];
}

$bpOnly = array(82);
?>

<? if (!isset($_SESSION['program_name']) || $_SESSION['program_name'] != 'children_tasks') : ?>

<? //if ($_SERVER['REQUEST_URI'] == '/print_missions.dev.php') { ?>
	<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>-->
<? //} else { ?>
	<script src="/scripts/jquery-1.8.3.js"></script>
<? //}?>

<!--
<? if ($_SERVER['REQUEST_URI'] == '/sefer_hamitzvos.php' || 
            $_SERVER['REQUEST_URI'] == '/missions_report.php') : ?>
<script type="text/javascript" src="jquery-1.8.1.min.js"></script>
<? else : ?>
	<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
<? endif; ?>
-->
<SCRIPT type="text/javascript" src="/scripts/jquery.tools.min.js"></SCRIPT>
<script type="text/javascript" src="/scripts/jquery.styleselect.js"></script>
<script type="text/javascript" src="/scripts/bug_report/bug_report.js"></script>
<SCRIPT type="text/javascript" src="/jquery-ui.js"></SCRIPT>

    <script src="/scripts/scripts.js"></script>
	<script>
	 $(function() {
		// <?=isset($ui_type) ? array_search($ui_type, $menuSection) : "0" ?>;
		var curr_tab = <?=isset($ui_type) ? '$(".list_parent a").index($(".list_parent a[title='.$ui_type.']"));' : "0" ?>;
		$(".list_first:not(.list_small,.user_list)").tabs(".list_first > ul", {tabs: '.list_parent', effect: 'slide', initialIndex: curr_tab});
		$('#nav li:has(ul)').addClass('submenu');
		
		var admin_id = <?=$admin_user['admin_id']?>;
		if (admin_id == 3 || admin_id == 237 || admin_id == 7) {
		    var elem = $(".list_parent").has("a .icon");
		    elem.click( function() {
		       var val = $.trim($(this).text());
		       if (val == 'Child Management') {
		           $(this).next("ul").attr('style', 'display:block');
		       }    
		    });
		}

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
		$('#nav').animate({height:$('#content').height()},1000);
	}
	</script>
<? endif; ?>
	
	<div id="wrapper">

		<div id="nav">

			<div class="col_title_bg"></div>

			<div class="col_title">Menu</div>

<?
$sql = "SELECT aa.role_id, r.role_name, u.school_id FROM admin_auths AS aa JOIN roles AS r USING (role_id) JOIN users AS u ON (aa.id=u.user_id) WHERE aa.admin_id=" . $admin_user['admin_id'] . " GROUP BY aa.admin_id, aa.auth";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$role_name = $row["role_name"];
$role_id = $row["role_id"];
?>

<? if ((isset($admin_user) || isset($user))) : ?>

	<? if (isset($admin_user['admin_id']) && 1 == 2) : ?>

				<ul class="list_first list_second user_list">

					<li class="school">
						<a href="#">
							<div class="icon">
								<div class="title"><?=$admin_user['first'];?> <?=$admin_user['last'];?></div>
								<div class="role"><?=$role_name;?></div>
							</div>
						</a>


						<?
						$cur_admin_id = $admin_user['admin_id'];
						$school_auths = mq("SELECT school_name, school_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN schools ON (admin_auths.id = schools.school_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) WHERE auth = 'school' AND admin_id = $cur_admin_id" . ($admin_user['auth'] == 'super' ? '' : "") . ' ORDER BY school_name');
						$class_auths = mq("SELECT school_name, class_grade, class_sub, class_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN classes ON (admin_auths.id = classes.class_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'class' AND admin_id = $cur_admin_id" . ($admin_user['auth'] == 'super' ? '' : "") . ' ORDER BY class_grade, class_sub');
						$team_auths = mq("SELECT school_name, team_name, team_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN teams ON (admin_auths.id = teams.team_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'team' AND admin_id = $cur_admin_id" . ($admin_user['auth'] == 'super' ? '' : "") . ' ORDER BY team_name');
						$user_auths = mq("SELECT school_name, first, last, user_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN users ON (admin_auths.id = users.user_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'user' AND admin_id = $cur_admin_id" . ($admin_user['auth'] == 'super' ? '' : "") . ' ORDER BY last, first'); //  AND school_id IN ($school_ids)
						?>

						<ul class="list_first user_list user_menu">

							<!-- ***** SCHOOLS ***** -->
							<div class="list_section">
								<? while($row = mysql_fetch_assoc($school_auths)): ?>
									<li class="school current">
										<a href="/admin.php?school_id=<?=$row['school_id'];?>">
											<div class="title"><?=es($row['school_name'])?></div>
											<div class="role"><?=is_null($row['role_id']) ? T_('Other') : es($row['role_name'])?></div>
										</a>
									</li>

								<? endwhile; ?>
							</div>
							<!-- ***** SCHOOLS ***** -->

							<!-- ***** CLASSES ***** -->
							<div class="list_section">
								<? while($row = mysql_fetch_assoc($class_auths)): ?>
									<li class="class">
										<a href="/admin.php?class_id=<?=$row['class_id'];?>">
											<div class="title"><?=es($row['class_grade'])?> - <?=es($row['class_sub'])?></div>
											<div class="role"><?=is_null($row['role_id']) ? T_('Other') : es($row['role_name'])?></div>
											<div class="info"><?=es($row['school_name'])?></div>
										</a>
									</li>
								<? endwhile; ?>
							</div>
							<!-- ***** CLASSES ***** -->


						</ul> <!-- <ul class="list_first user_list user_menu"> -->

	<? endif; ?>

<? endif; ?>

					</li> <!-- <li class="school"> -->

				</ul> <!-- <ul class="list_first list_second user_list"> -->


<? if (isset($admin_user['auth'])) : ?>
				<ul class="list_first">

					<li class="list_parent<?=isset($ui_type) && $ui_type == 'admin' ? ' current' : ''?>">
						<a href="/admin.php" onclick="document.location.href=this.href" title="admin"><div><span class="icon"><img height="28" width="28" alt="Home" src="/images/icon_admin_home.png"></span><?=T_('Home')?></div></a>
					</li>

					<ul class="list_second">
					</ul>

	<? if ($admin_user['auth'] == 'super' || !empty($admin_user['auths']['school'])) : ?>
	
	<? if (in_array($admin->school_id, $tanyaOnlySchools) || in_array($admin->school_id, $chidonSchools)) : ?>
	
		<? 	
		//$url_id = isset($school_id) ? "?school_id=$admin->school_id" : '';
		$url_id = "?admin_id=" . $admin->admin_id . "&school_id=" . $admin->school_id;
		$url_id2 = $url_id === '' ? '?' : $url_id . '&amp;'; 
		?>
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
						<a href="/admin_school.php<?=$url_id?>" title="school"><div><span class="icon">
							<img height="28" width="28" alt="Base Management" src="/images/icon_dashboard.png"></span><?=T_('Base Management')?></div>
						</a>
					</li>
					

					<ul class="list_second">
						<li>
							<a href="#"><?=T_('Students (Soldiers)')?></a>
							<ul>
								<li><a href="/admin_user.php<?=$url_id?>"><?=T_('View / Edit')?></a></li>
								<li><a href="/admin_user.php<?=$url_id2?>action=add"><?=T_('Add Individual')?></a></li>
								<li><a href="/admin_school_file.php<?=$url_id?>"><?=T_('Upload School or Class List')?></a></li>
								<li><a href="/admin_users_photo.php<?=$url_id?>"><?=T_("Upload Photos")?></a></li>
								<!--
								<li><a href="https://mashpia.com/admin_users_register_new.php<?=$url_id?>"><?=T_("Registration")?></a></li> 
								<li><a href="/admin_card_print.php<?=$url_id?>"><?=T_('Print Rank Cards')?></a></li>
								<li><a href="/add_missions.php"><?=T_('Update Soldier\'s Missions')?></a></li>
                                <li><a href="/add_medals.php"><?=T_('Update Soldier\'s Medals')?></a></li>
								<!--<li><a href="/admin_users_subject.php<?=$url_id?>"><?=T_("Campaign Enrollment")?></a></li>-->
								<!--<li><a href="/admin_user_kiosk.php<?=$url_id?>"><?=T_('Kiosk Mission Entry')?></a></li>-->
							</ul>
						</li>
						<!--
						<li>
							<a href="/admin_admin.php<?=$url_id?>"><?=T_('Manage Admins')?></a>
						</li>
						-->
						
						<li>
                            <a href="#"><?=T_('Classes (Platoons)')?></a>
                            <ul>
                                <li><a href="/admin_class.php<?=$url_id?>"><?=T_('Manage')?></a></li>
                                <li><a href="/admin_class.php<?=$url_id2?>action=add"><?=T_('Add New')?></a></li>
                                <li><a href="/admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></a></li>
                            </ul>
                        </li>

                        <!--
						<li>
							<a href="#"><?=T_('School (Base)')?></a>
							<ul>
								<li><a href="/admin_school.php<?=$url_id2?>action=edit"><?=T_('Edit School Profile')?></a></li>
                                <li><a href="/admin_profile.php">Edit Admin Profile</a></li>
                                <li><a href="/parent_list.php">Parent Accounts</a></li>
								<li><a href="/settings.php<?=$url_id2?>">Settings</a></li>
								<li><a href="/admin_invoice_items.php<?=$url_id?>"><?=T_('Transaction History')?></a></li>
							</ul>
						</li>
						-->
						<? if (in_array($admin->school_id, $chidonSchools)) { ?>
							<li>
								<a href="#"><?=T_('Parents')?></a>
								<ul>
									<li><a href="/parent_list.php">Parent Accounts</a></li>
									<li><a href="/child_list.php">Parent / Children Accounts</a></li>
								</ul>
							</li>
						<? } ?>
					</ul>
					<? if (in_array($admin->school_id, $chidonSchools)) { ?>
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
							<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Chidon" src="/images/icon_admin_medal.png"></span><?=T_('Chidon')?></div></a>
						</li>
							
						<ul class='list_second'>
							<li><a href="/chidon_report.php">Registered for Chidon</a></li>
							<li><a href="/chidon_tests.php">Enter Chidon Test Marks</a></li>
						</ul>
					<? } ?>
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'info' ? ' current' : ''?>">
						<a href="#" title="campaigns"><div><span class="icon"><img height="28" width="28" alt="campaigns" src="/images/parentIcons/Campaigns.gif"></span><?=T_('Campaigns')?></div></a>
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
							<a href="#">Mishna Bal Peh</a>
							<ul>
								<li><a href="/assign_mishnos.php">Assign Mesechtos</a></li>
								<li><a href="/mark_mishnos.php">Group Marking</a></li>
								<li><a href="/mark_mishnos_single.php">Individual Marking</a></li>
								<li><a href="/mishna_report.php">Reports</a></li>
							</ul>
						</li>
					</ul>
	
	<? else : ?>
		
		<? 	
		//$url_id = isset($school_id) ? "?school_id=$admin->school_id" : '';
		$url_id = "?admin_id=" . $admin->admin_id . "&school_id=" . $admin->school_id;
		$url_id2 = $url_id === '' ? '?' : $url_id . '&amp;'; 
		?>

					<? if ( $h_school ) { ?>
					
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
							<a href="/admin_school.php<?=$url_id?>" title="school"><div><span class="icon">
								<img height="28" width="28" alt="Base Management" src="/images/icon_dashboard.png"></span><?=T_('Base Management')?></div>
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
									<li><a href="https://www.mashpia.com/admin_users_register.php<?=$url_id?>"><?=T_("Registration")?></a></li> 
									<li><a href="/admin_card_hschool.php<?=$url_id?>"><?=T_('Print ID Cards')?></a></li>
								</ul>
	
							</li>
	
							<li>
								<a href="#"><?=T_('School (Base)')?></a>
								<ul>
									<li><a href="/admin_school2.php<?=$url_id2?>action=edit"><?=T_('Edit Profile')?></a></li>
								</ul>
							</li>
	
							<li>
								<a href="#"><?=T_('Classes (Platoons)')?></a>
								<ul>
									<li><a href="/admin_class.php<?=$url_id?>"><?=T_('Manage')?></a></li>
									<li><a href="/admin_class.php<?=$url_id2?>action=add"><?=T_('Add New')?></a></li>
									<!--<li><a href="/admin_class_transition.php<?=$url_id?>"><?=T_('Platoon Transition')?></a></li>-->
								</ul>
							</li>
						</ul>
						
							<!--
							<li><a href="/admin_invoice_items.php<?=$url_id?>"><?=T_('Transaction History')?></a></li>
							<li><a href="/admin_scan.php<?=$url_id?>"><?=T_('Scan Vouchers')?></a></li>
							<li><a href="/admin_withdraw.php<?=$url_id?>"><?=T_("Show/Cash Existing Soldiers' Vouchers")?></a></li>
							
							<li>
								<a href="/admin_user_withdraw.php<?=$url_id?>">Create Soldiers' Vouchers</a>
							</li>
						</ul>
	
						<ul class="list_second">
							
							<? $result = mq("SELECT subjects.subject_id, subject_name FROM subjects JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE subject_type NOT IN ('school_points', 'home_points') ORDER BY subject_name"); ?>
							
							<!--
							<?
							if (in_array($admin->school_id, array(55, 66, 112, 79, 61))) { 
							?>
							<li>
								<a href="/admin_points_print.php<?=$url_id?>"><?=T_('Print Achievement Cards')?></a>
							</li>
							<? } ?>
							
						</ul> <!-- <ul class="list_second"> -->
	
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'reports' ? ' current' : ''?>">
							<a href="/admin_report_list.php<?=$url_id?>" title="reports"><div><span class="icon"><img height="28" width="28" alt="Reports" src="/images/icon_report.png"></span><?=T_('Reports')?></div></a>
						</li>
	
						<ul class="list_second">					
							
							<li><a href="/registered_report.php"><?=T_("Registered Report")?></a></li> 
							
							<li><a href="/barcodes_report.php"><?=T_("Barcodes Report")?></a></li>
													
							<!--<li><a href="/IDcardReport.php"><?=T_("ID Cards Report")?></a></li>-->
							
							<!--<li><a href="/medal_rank_ceremony.php"><?=T_('Medal and Rank Ceremony')?></a></li>
							
							<li><a href="/tanya_enroll.php"><?=T_('TBP Hachloto Form')?></a></li>
	
							<li><a href="/shabbos_mevorchim.php"><?=T_('Shabbos Mevorchim School & Class Report')?></a></li>
							
							<li><a href="/shabbos_mevorchim_summary.php"><?=T_('Shabbos Mevorchim Army Wide Report')?></a></li>
	
							<li><a href="/charge_card_report.php"><?=T_('Charge Card Report')?></a></li>
							
							<li><a href="/promotion_report.php"><?=T_('Promotion Picture Report')?></a></li>
							
							<li><a href="/mission_sheets_checklist.php"><?=T_('Missions Checklist')?></a></li>
													
							<li><a href="/admin_print_pdf.php"><?=T_('Print Certificate')?></a></li>
							
							<li><a href="/admin_received_stats.php<?=$url_id?>"><?=T_('Mark Ranks and Medals as Received')?></a></li>
							
							<li><a href="/miles.php"><?=T_('Miles Report')?></a></li>
							
							<li><a href="/missions_report.php"><?=T_('Missions Report')?></a></li>
							
							<li><a href="/rank_report.php"><?=T_('Rank Report')?></a></li>-->                        
							
						</ul>
						
						<li>
							<a href="/reports/shipping/">
								<span class="icon">
									<img src="/images/icon_report.png" width="28" height="28" />Shipping Reports
								</span>
							</a>
						</li>
                                        
                        <li class="list_parent link">
                            <FORM name="setup_guide" method="post" action="admin_setup_guide_hschool.php">
                                <input type="hidden" name="admin_id" value="<?=$admin_user['admin_id'];?>">
                                <a href="#" onclick="document.forms['setup_guide'].submit();">
                                    <span class="icon">
                                        <img src="/images/icon_wizard.png" width="28" height="28" alt="Dashboard" />
                                    </span>
                                    Setup Guide
                                </a>
                            </FORM>
                        </li>
                        <li>
                            <a href="http://mashpia.com/v2/login/frommashpia/school_id/<?=$admin->school_id ? $admin->school_id : $admin_user['auths']['school'][0]?>/admin_id/<?=$admin_user['admin_id']?>">
                                <span class="icon">
                                    <img src="/images/icon_auction.png" width="28" height="28" alt="Dashboard" />Mileage Program
                                </span>
                            </a>
                        </li>

                        <li>
                          <a href="/helpdesk/?p=open" id="helpdesk_link" title="support"><div><span class="icon"><img height="28" width="28" alt="Support" src="/images/icon_info.png"></span><?=T_('Support')?></div></a>
                        </li>
                        
                        <? if (isset($_GET['showBlog'])) { ?>
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
                        <? } ?>
                        
                    <? } else { ?>     
					
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
						<a href="/admin_school.php<?=$url_id?>" title="school"><div><span class="icon">
							<img height="28" width="28" alt="Base Management" src="/images/icon_dashboard.png"></span><?=T_('Base Management')?></div>
						</a>
					</li>

					<ul class="list_second">
					
						<li><a href="/yearly_prize/forms/staff_info.php">Staff Management</a></li>
						<li>
							<a href="#"><?=T_('Teachers')?></a>

							<ul>
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
								<li><a href="https://mashpia.com/admin_users_register.php<?=$url_id?>"><?=T_("Registration")?></a></li> 
								<li><a href="/admin_card_print.php<?=$url_id?>"><?=T_('Print Rank Cards')?></a></li>
								<li><a href="/add_missions.php"><?=T_('Update Soldier\'s Missions')?></a></li>
                                <li><a href="/add_medals.php"><?=T_('Update Soldier\'s Medals')?></a></li>
								<!--<li><a href="/admin_users_subject.php<?=$url_id?>"><?=T_("Campaign Enrollment")?></a></li>-->
								<!--<li><a href="/admin_user_kiosk.php<?=$url_id?>"><?=T_('Kiosk Mission Entry')?></a></li>-->
							</ul>

						</li>
						<!--
						<li>
							<a href="admin_admin.php<?=$url_id?>"><?=T_('Manage Admins')?></a>
						</li>
						-->
						
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

                                        <!--
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
						<a href="admin_school_subjects.php<?=$url_id?>" title="programs">
							<div>
								<span class="icon">
									<img height="28" width="28" alt="Campaigns" src="/images/icon_admin_medal.png">
								</span><?=T_('My Campaigns')?>
							</div>
						</a>
					</li>

					<ul class="list_second">
						
						<?
						  //find out whether school is chabad, frum, or both
						  $sql2 = "select child_type_id from school_child_types where school_id = $school_id";
						  $result2 = mq($sql2);
						  $row2 = mysql_fetch_row($result2);
						  $type = $row2[0];
						  $sql3 = false;
						  switch ($type) {
							case 1:
								$sql3 = "select distinct subject_id from school_type_subjects where school_type_id in (2,3)";
								break;
							case 2:
								$sql3 = "select distinct subject_id from school_type_subjects where school_type_id in (12,13)";
								break;
							case 3:
								break;
						  }
						  $subjects = array();
						  if ($sql3) {
							$result3 = mq($sql3);
							while ($row3 = mysql_fetch_row($result3)) {
								$subjects[] = $row3[0];
							}
						  } 
						?>
						<? $result = mq("SELECT subjects.subject_id, subject_name FROM subjects JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE subject_type NOT IN ('school_points', 'home_points') ORDER BY subject_name"); ?>
						<? if (mysql_num_rows($result)) : ?>
						
						<li>
							<a href="#"><?=T_('My Campaigns')?></a>
							<ul>
							
								<? while($row = mysql_fetch_assoc($result)): ?>
								<? if ($row['subject_name'] == "Hakhel" || $row['subject_id'] == 91) continue; ?>
								<?
								   //check that subject is according to mission type
								   if (count($subjects) > 0 && !in_array($row['subject_id'], $subjects)) continue;							   
								?>
								<li><a href="/admin_todo_subject.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></a></li>
								<? endwhile; ?>
							
							</ul>
						</li>
						
						<? endif; ?> 
						<!-- (mysql_num_rows($result)) : -->
                                                <!--
					<? $result = mq("SELECT subjects.subject_id, subject_name, subject_type FROM subjects JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE subject_type IN ('school_points', 'home_points') ORDER BY subject_name"); ?>
						<? if (mysql_num_rows($result)) : ?>
						<li>
							<a href="#"><?=T_('My Campaigns - base campaigns')?></a>
							<ul>
								<? while($row = mysql_fetch_assoc($result)): ?>
								<li><a href="/admin_todo_subject.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></a></li>
								<? endwhile; ?>
							</ul>
						</li>
						<? endif; ?> -->
						<!-- if (mysql_num_rows($result)) : -->
                                                <!--
						<? $result = mq("SELECT subjects.subject_id, subject_name FROM subjects LEFT JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE school_subjects.subject_id IS NULL AND subject_type NOT IN ('school_points', 'home_points') ORDER BY subject_name"); ?>
						<? if (mysql_num_rows($result) > 1) : ?>
						<li>
							<a href="#"><?=es(T_('View & Enroll'))?></a>
							<ul>
								<? while($row = mysql_fetch_assoc($result)): ?>
								<? if ($row['subject_name'] == "Hakhel" || $row['subject_id'] == 91) continue; ?>
								<li><a href="/admin_school_subjects.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></a></li>
								<? endwhile; ?>
							</ul>
						</li>
						<? endif; ?> 
						<!-- if (mysql_num_rows($result)) : -->
                                                <!--
					<? $sql = "SELECT subjects.subject_id, subject_name FROM subjects LEFT JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE school_subjects.subject_id IS NULL AND subject_type IN ('school_points', 'home_points') ORDER BY subject_name"; ?>
						<? echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n"; ?>
				
				
						<? $result = mq("SELECT subjects.subject_id, subject_name FROM subjects LEFT JOIN school_subjects ON (subjects.subject_id = school_subjects.subject_id AND school_id = $school_id) WHERE school_subjects.subject_id IS NULL AND subject_type IN ('school_points', 'home_points') ORDER BY subject_name"); ?>
						<? if(mysql_num_rows($result)): ?>
						<li>
							<a href="#"><?=es(T_('View & Enroll - base campaigns'))?></a>
							<a href="#" onclick="document.getElementById('local_list').style.display = ''; document.getElementById('local_show').style.display = 'none'; return false;" id="local_show" style="padding-<?=$align_start?>: 2em;"><?=T_('Show')?></a>
							<ul id="local_list" style="display: none;">
							<ul>
								<? while($row = mysql_fetch_assoc($result)): ?>
								<li><a href="/admin_school_subjects.php<?=$url_id2?>subject_id=<?=$row['subject_id']?>"><?=es($row['subject_name'])?></a></li>
								<? endwhile; ?>
							</ul>
						<? endif; ?>

						<?
						if (in_array($admin->school_id, array(55, 66, 112, 79, 61))) { 
						?>
						<li>
							<a href="/admin_points_print.php<?=$url_id?>"><?=T_('Print Achievement Cards')?></a>
						</li>
						<? } ?>
					
					</ul> <!-- <ul class="list_second"> -->
<!--</li>-->

					<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
						<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Campaigns" src="/images/icon_admin_medal.png"></span><?=T_('Missions')?></div></a>
					</li>

					<ul class='list_second'>
						<!--
						<li><a href="summer_report.php"><?=T_('Mark Summer/Tishrei Missions')?></a>
						-->
                        <li><a href="/print_missions2.php"><?=T_('Print Missions')?></a>
						<!--
                        <li><a href="/print_missionsYT.php"><?=T_('Print Rosh Hashana Missions')?></a>
						<li><a href="/print_missionsYT2.php"><?=T_('Print Yom Kippur / Succos Missions')?></a>
                        <!--<li><a href="/print_missions_summer.php"><?=T_('Print Summer Missions')?></a>-->
                        <li><a href="/mark_missions2.php"><?=T_('Mark Missions')?></a>
                        <li><a href="/sefer_hamitzvos.php"><?=T_('Mark Yahadus')?></a>
                        <li><a href="/task_customization.php">Personalize Your Missions</a></li>
                        <li><a href="/newTask.php">Add Tasks</a></li>
                        <li><a href="/mission_sheets_checklist.php"><?=T_("Teacher's Missions Checklist")?></a></li>
                        <li><a href="/missions_report.php"><?=T_('Missions Accomplished Report')?></a></li>
					</ul>
					
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
						<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Campaigns" src="/images/icon_auction.png"></span><?=T_('Achievement Cards')?></div></a>
					</li>
						
					<ul class='list_second'>
                        <li><a href="/newAchievementTasks.php">Add Achievement Task</a></li>
						<li><a href="/manual_points.php">Add / Subtract Points</a></li>
					</ul>
					
					
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
						<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Chidon" src="/images/chidon.png"></span><?=T_('Chidon')?></div></a>
					</li>
						
					<ul class='list_second'>
                        <!--<li><a href="/uploadChidonFile.php">Upload File for Chidon</a></li>-->
						<!--<li><a href="/chidon_report.php">Registered for Chidon</a></li>-->
						<li><a href="/reports/chidon/shabbaton_enrollment.php">Shabbaton Enrolled Report</a></li>
						<li><a href="/chidon_tests.php">Enter Chidon Test Marks</a></li>
						<li><a href="/chidon_school_reg.php">Enroll Chaperones</a></li>
						<?php if ($admin_user['auth'] == 'super') : ?>
						<li><a href="/enrollment_hq.php">Activate Enrollment HQ</a></li>
						<?php else : ?>
						<li><a href="/enrollment.php">Activate Enrollment</a></li>
						<?php endif; ?>
						<li><a href="/review_enrollment.php">Review Enrollment</a></li>
						<li><a href="/chidon_review.php">Print Enrollment Info</a></li>
					</ul>

					<li class="list_parent<?=isset($ui_type) && $ui_type == 'reports' ? ' current' : ''?>">
						<a href="/admin_report_list.php<?=$url_id?>" title="reports"><div><span class="icon"><img height="28" width="28" alt="Reports" src="/images/icon_report.png"></span><?=T_('Reports')?></div></a>
					</li>

					<ul class="list_second">
						<? if ($admin_user['auth'] == 'super') : ?>
						<li><a href="/reports/"><?=T_("Office Reports")?></a></li>
						<? endif; ?>
						<!--
						<li>
							<a href="#"><?=T_('Soldier Reports')?></a>
							<ul>
								<li><a href="admin_stats.php<?=$url_id2?>subjects=army&amp;start_date=0&amp;cols[s]=1&amp;cols[m]=1&amp;cols[r]=1&amp;order_by=.r&amp;registered_only=1&amp;report_type=rank&amp;view=1"><?=T_('Base Rank Report')?></a></li>
								<li><a href="admin_stats.php<?=$url_id2?>subjects=army&amp;start_date=0&amp;cols[s]=1&amp;cols[m]=1&amp;cols[r]=1&amp;order_by=cr&amp;report_type=rank_class&amp;view=1"><?=T_('Platoon Rank Report')?></a></li>
							</ul>
						</li>
						-->	
						<? if ($admin_user['auths']['school'][0] == 162) : ?>
						
						<li><a href="/admin_print_pdf.php">Print Certificates</a></li>
						
						<? endif; ?>
						
						<!--<li><a href="add_ons_report.php">Add On Report</a></li>-->
						
						<li><a href="/registered_report.php"><?=T_("Registered Report")?></a></li>
						
						<li><a href="/parent_report.php">Parents Report</a></li>
						 
						<li><a href="/non_registered_report.php"><?=T_("Not Yet Registered Report")?></a></li> 
						
						<li><a href="/barcodes_report.php"><?=T_("Barcodes Report")?></a></li>
						
						<!--<li><a href="charge_card_report.php"><?=T_('Charge Card Report')?></a></li>-->
                                                
                        <li><a href="/miles.php"><?=T_('Miles Report')?></a></li>
						
						<li><a href="/auctionMiles.php">Auction Miles Report</a></li>
                        
                        <li><a href="/missions_report.php"><?=T_('Missions Done Report')?></a></li>
                        <!--
                        <li><a href="/stickers_report.php"><?=T_('Sticker Report')?></a></li>
                        
                        <li><a href="/stickers_report_by_week.php"><?=T_('Stickers Earned By Date')?></a></li>
						-->
						
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
                        					
						<!--<li><a href="/medal_report.php"><?=T_('Medal and Rank Statement')?></a></li>-->
						
						<!--<li><a href="/admin_print_pdf.php"><?=T_('Print Certificate')?></a></li>-->
						
						<!--
						<li>
							<a href="#"><?=T_('Mileage')?></a>
							<ul>
								<li><a href="/admin_stats.php<?=$url_id2?>subjects=all&amp;start_date=<?=chaiElul()?>&amp;cols[p]=1&amp;cols[y]=1&amp;cols[u]=1&amp;cols[w]=1&amp;order_by=.y&amp;registered_only=1&amp;report_type=points&amp;view=1"><?=T_('Base Mileage Report')?></a></li>
								<li><a href="/admin_stats.php<?=$url_id2?>subjects=all&amp;start_date=<?=chaiElul()?>&amp;cols[p]=1&amp;cols[y]=1&amp;cols[u]=1&amp;cols[w]=1&amp;order_by=cy&amp;report_type=points_class&amp;view=1"><?=T_('Platoon Mileage Report')?></a></li>
							</ul>
						</li>
						
						<li>
							<a href="#"><?=T_('Medal/Rank')?></a>
							<ul>
								<li><a href="/admin_medal_report.php<?=$url_id?>"><?=T_('Medal Report')?></a></li>
								<li><a href="/admin_rank_report.php<?=$url_id?>"><?=T_('Rank Report')?></a></li>
							</ul>
						</li>
                   
						<li>
							<a href="#"><?=T_('Create')?></a>
							<ul>
								<li><a href="/admin_stats.php<?=$url_id?>"><?=T_('Create your own Stats Report')?></a></li>
							<li><a href="/admin_mission_report.php<?=$url_id?>"><?=T_('Mission Report')?></a></li>
							</ul>
						</li>
                        -->
					</ul>
                    
                    <li>
                        <a href="/reports/shipping/">
							<span class="icon">
								<img src="/images/icon_report.png" width="28" height="28" />Shipping Reports
							</span>
						</a>
					</li>
					
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'info' ? ' current' : ''?>">
						<a href="#" title="campaigns"><div><span class="icon"><img height="28" width="28" alt="campaigns" src="/images/parentIcons/Campaigns.gif"></span><?=T_('Campaigns')?></div></a>
					</li>
					
					<ul class="list_second">
						<li>
							<a href="#">Tanya</a>
							<ul>
								<!--<li><a href="/new_tanya_report.php"><?=T_('Tanya Report')?></a></li>-->
								<!--<li><a href="/tanya_enroll.php"><?=T_('TBP Hachloto Form')?></a></li>-->
								<!--<li><a href="/editPlatoonLines2.php">Platoon Marking</a></li>-->
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
							</ul>
						</li>
						<li>
							<a href="#">Mishna Bal Peh</a>
							<ul>
								<!--<li><a href="mishna_settings.php">Setup Points Per Line</a></li>-->
								<li><a href="/assign_mishnos.php">Assign Mesechtos</a></li>
								<li><a href="/mark_mishnos.php">Group Marking</a></li>
								<li><a href="/mark_mishnos_single.php">Individual Marking</a></li>
								<li><a href="/mishna_report.php">Reports</a></li>
							</ul>
						</li>
					</ul>
					
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'info' ? ' current' : ''?>">
						<a href="#" title="rally"><div><span class="icon"><img height="28" width="28" alt="rally" src="/images/parentIcons/Rally.gif"></span><?=T_('Rally')?></div></a>
					</li>
					
					<ul class="list_second">
						<li><a href="/promotion_report.php"><?=T_('Promotion Picture Report')?></a></li>
						<!--<li><a href="/medal_rank_ceremony.php"><?=T_('Medal and Rank Ceremony')?></a></li>-->
						<li><a href="/medal_rank_ceremony3.php"><?=T_("Teacher's Medal Ceremony Report")?></a></li>
						<li><a href="/raffles/shared/forms/winners_form.php"><?=T_("Raffle Winners")?></a></li>
						<?php if ($admin_user['auth'] == 'super') : ?>
						<li><a href="/missing_medals.php">Missing Medals</a></li>
						<?php endif; ?>
						<!--<li><a href="/raffle_winners.php">Raffle Winners</a></li>-->
                        <!--<li><a href="/promotion_preview.php">Preview Promotions</a></li>-->
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
					
				    <!--
					<li class="list_parent<?=isset($ui_type) && $ui_type == 'info' ? ' current' : ''?>">
						<a href="#" title="info"><div><span class="icon"><img height="28" width="28" alt="Info" src="images/icon_info.png"></span><?=T_('About Us')?></div></a>
					</li>
				    
				    <ul class="list_second">
                        <li>
                            <a href="/faq.php">Plan of Action</a>
                        </li>
                    </ul>
                    
                    <ul class="list_second">
                        <li>
                            <a href="/faq.php">Campaigns</a>
                        </li>
                    </ul>
                    
                    <ul class="list_second">
                        <li>
                            <a href="/faq.php">Updates</a>
                        </li>
                    </ul>
                    
                    <ul class="list_second">
                        <li>
                            <a href="/faq.php">FAQ's</a>
                        </li>
                    </ul>
                -->

					<!--****************************************************-->
					<!-- ***** If the school is using the camp system ***** -->
					<!--****************************************************-->
					<? if ($school_and_camp == true && 1 == 2) : ?>

						<!-- START -->

						<li class="list_parent slide">
							<a href="/camps/content.php?output=staff_profile&admin_id=<?=$admin_id;?>"><span class="icon"><img src="/images/icon_dashboard.png" width="28" height="28" alt="Dashboard" /></span>My Profile</a>
						</li>

						<ul class="list_second slide">
						</ul>


						<!-- START -->
						<li class="list_parent slide">
							<a href="/camps/content.php?output=points"><span class="icon"><img src="/images/icon_points.png" width="28" height="28" alt="Points" /></span>Points</a>
						</li>

						<ul class="list_second slide">
						</ul>

						<li class="list_parent slide"><a href="#print"><span class="icon"><img src="/images/icon_print.png" width="28" height="28" alt="Print Center" /></span>Print Center</a></li>
							<ul class="list_second slide">
								<li><a href="/camps/content.php?output=rankcards"><span class="icon"><img src="/images/icon_rank_card.png" width="28" height="28" alt="Settings" /></span>Rank Cards</a></li>
								<li><a href="/camps/content.php?output=mission_sheets"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Mission Sheets</a></li>
								<li><a href="#"><span class="icon"><img src="/images/icon_add.png" width="22" height="22" alt="Add" /></span>Print Cards</a></li>
							</ul>

						<li class="list_parent slide"><a href="#control"><span class="icon"><img src="/images/icon_control.png" width="28" height="28" alt="Settings" /></span>Control Panel</a></li>
							<ul class="list_second slide">
								<li><a href="/camps/content.php?output=campprofile"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Camp Profile</a></li>
								<li><a href="/camps/content.php?output=grouptypes"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Groups</a></li>
								<li><a href="/camps/content.php?output=missions_dash"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Missions</a></li>
								<li><a href="/camps/content.php?output=campers"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Campers</a></li>
								<li><a href="/camps/content.php?output=staff"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Staff</a></li>
								<li><a href="/camps/content.php?output=store"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Store</a></li>
								<li><a href="/camps/content.php?output=gettingstarted"><span class="icon"><img src="/images/icon_settings.png" width="22" height="22" alt="Settings" /></span>Getting Started</a></li>
							</ul>




						<!-- END -- >

					<? endif; ?> <!--  if ($school_and_camp == true) : --?
					<!--****************************************************-->
					<!-- ***** If the school is using the camp system ***** -->
					<!--****************************************************-->

						<?// if ($school_store > 0) : ?>
						<?if (1 == 2) : ?>
						<li class="list_parent">
							<a href="http://v2.mashpia.com/login/auto/user_id/<?=$admin->admin_id;?>/email/<?=$admin->admin_email;?>/password/<?=MD5($admin->password);?>">
								<span class="icon">
									<img src="/images/icon_dashboard.png" width="28" height="28" alt="Dashboard" />
								</span>
								Store
							</a>
						</li>
						<? endif; ?>
					
						<li class="list_parent link">
							<FORM name="setup_guide" method="post" action="/admin_setup_guide.php">
								<input type="hidden" name="admin_id" value="<?=$admin_user['admin_id'];?>">
								<a href="#" onclick="document.forms['setup_guide'].submit();">
									<span class="icon">
										<img src="/images/icon_wizard.png" width="28" height="28" alt="Dashboard" />
									</span>
									Setup Guide
								</a>
							</FORM>
						</li>
						
						<li>
                            <a href="http://mashpia.com/v2/login/frommashpia/school_id/<?=$admin->school_id ? $admin->school_id : $admin_user['auths']['school'][0]?>/admin_id/<?=$admin_user['admin_id']?>">
								<span class="icon">
									<img src="/images/icon_auction.png" width="28" height="28" alt="Dashboard" />Mileage Program
								</span>
							</a>
						</li>
						
						<!--
						<li>
							<a href="/under_construction.php">
								<span class="icon">
									<img src="/images/icon_auction.png" width="28" height="28" alt="Dashboard" />Mileage Program
								</span>
							</a>
						</li>
						<li>
							<a href="/under_construction.php">
								<span class="icon">
									<img src="/images/icon_auction.png" width="28" height="28" alt="Dashboard" />Tanya Program
								</span>
							</a>
						</li>
						-->
						<li>
						  <a href="/helpdesk/?p=open" id="helpdesk_link" title="support"><div><span class="icon"><img height="28" width="28" alt="Support" src="/images/parentIcons/support icon.gif"></span><?=T_('Support')?></div></a>
						</li>
						
						<? if (isset($_GET['showBlog'])) { ?>
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
						} ?>

<!--</li>-->
					<? //else : ?>
					<!--
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'school' ? ' current' : ''?>">
							<a href="/admin_school.php<?=$url_id?>" title="school"><div><span class="icon">
								<img height="28" width="28" alt="Base Management" src="/images/icon_dashboard.png"></span><?=T_('Base Management')?></div>
							</a>
						</li>
	
						<ul class="list_second">
							<li>
								<a href="#"><?=T_('Students (Soldiers)')?></a>
	
								<ul>
									<li><a href="/admin_user.php<?=$url_id?>"><?=T_('View / Edit')?></a></li>
									<li><a href="/admin_user.php<?=$url_id2?>action=add"><?=T_('Add Individual')?></a></li>
									<li><a href="/admin_school_file.php<?=$url_id?>"><?=T_('Upload School or Class List')?></a></li>
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
						
						<li class="list_parent<?=isset($ui_type) && $ui_type == 'programs' ? ' current' : ''?>">
						<a href="#" title="programs"><div><span class="icon"><img height="28" width="28" alt="Campaigns" src="/images/icon_admin_medal.png"></span><?=T_('Missions')?></div></a>
					</li>

					<ul class='list_second'>
                        <li><a href="/date_tasks_report_new.php"><?=T_('Mark Missions')?></a>
					</ul>

					<? endif; ?> <!-- end if !in_array -->

<? endif;?>

<? if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['class'])):?>
	<? $url_id = isset($class_id) ? "?class_id=$class_id" : ''; ?>
<? endif;?>

<? if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['team'])):?>
	<? $url_id = isset($team_id) ? "?team_id=$team_id" : ''; ?>
<? endif;?>

<? if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['user'])):?>
	<? $url_id = isset($user_id) ? "?user_id=$user_id" : ''; ?>
<? endif;?>



	<? if (!empty($admin_user['auths']['user'])) : ?>
	<? require_once 'parent_menu.php'; ?>
	<? endif; ?>
	
    <li class="list_parent">
    	<a href="/logout.php" onclick="document.location.href=this.href">
    		<span class="icon"><img height="28" width="28" alt="Logout" src="/images/parentIcons/logout.gif"></span>
    		<?=T_('Logout')?>
    	</a>
    </li>

</ul>

		<?php
		/*
		<? global $subject_id; ?>
		
		<? if ( ( !isset($subject_id) || $subject_id == -1 || is_array($subject_id) ) || (isset($admin->is_parent) && $admin->is_parent == 1) ) :?>
		
        <? else: ?>
			<? $subject = mysql_fetch_assoc(mq("SELECT subject_name, subject_type, school_subjects.subject_id enrolled FROM subjects JOIN schools USING (inst_id) LEFT JOIN school_subjects USING (subject_id, school_id) WHERE subjects.subject_id = $subject_id AND school_id = $school_id")); ?>
            <ul class="list_first list_small">
                <!--<li class="list_parent"><a href="/admin_todo_subject.php<?=$url_id?>"><?=T_('Back to Campaign list')?></a></li>-->
                <li class="list_parent current"><a href="#"><?=es($subject['subject_name'])?></a></li>

				<? if(!$subject['enrolled']):?>
                    <li class="list_parent"><a href="/admin_school_subjects.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Enroll')?></a>
				<? else: ?>
                    <? $result = mq("SELECT category_name, category_id, (SELECT COUNT(*) FROM todo_list LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'school' AND todo_list_marks.id = $school_id) WHERE visibility != 'none' AND todo_list.school_id IS NULL AND todo_list.recip = 'school' AND (todo_list.recip_id = $school_id OR todo_list.recip_id IS NULL) AND mark_date IS NULL AND todo_list.category_id = todo_categories.category_id) num FROM todo_categories WHERE subject_id = $subject_id"); ?>
                    <li class="list_parent"><a href="/admin_todo_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Todo List')?></a>
                    <? if(mysql_num_rows($result)): ?>
                        <ul class="list_second">
                        <? while($row = mysql_fetch_assoc($result)): ?>
                            <li><a href="/admin_todo_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;category_id=<?=$row['category_id']?>">(<?=$row['num']?>) <?=es($row['category_name'])?></a></li>
                        <? endwhile; ?>
                        </ul>
                    <? endif; ?>

                    <li class="list_parent"><a href="/admin_school_subjects.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Un-Enroll')?></a></li>
                    <li class="list_split"></li>

                    <?	switch($subject['subject_type']) {
                            case 'school_points':
                    ?>
                        <!--<li class="list_parent"><a href="admin_points_print.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Print Achievement Cards')?></a></li>-->
						<?	break; // school_points
                                case 'Tanya':
                        ?>
                        <li class="list_parent"><a href="#"><?=T_('Tanya Setup')?></a>
                            <ul class="list_second">
                                <li><a href="/admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=introduction"><?=T_('Introduction')?></a></li>
                                <li><a href="/admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=year"><?=T_('Year')?></a></li>
                                <li><a href="/admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=dates"><?=T_('Program Dates')?></a></li>
                                <li><a href="http://www.mashpia.com/file_view.php?id=950252724&amp;m=d"><?=T_('Tanya with Nekudos')?></a></li>
                            </ul>
                        </li>
                        <li class="list_parent"><a href="#"><?=T_('Tanya Launch')?></a>
                            <ul class="list_second">
                                <li><a href="/admin_users_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_("Solder's Enrollment")?></a></li>
                                <li><a href="/admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=ladder"><?=T_('Ladder Setup')?></a></li>
                            </ul>
                        </li>
                        <li class="list_parent"><a href="#"><?=T_('Tanya Management')?></a>
                            <ul class="list_second">
                                <li><a href="#"><?=T_('CD')?></a>
                                    <ul>
                                        <li><a href="http://www.anash.com/Perek_Aleph.zip"><?=T_('Rabbi Garelik')?></a></li>
                                        <!-- <li><a href="#"><?=T_('Mrs Katz')?></a></li> -->
                                    </ul>
                                </li>
                                <li><a href="#"><?=T_('Reports')?></a>
                                    <ul>
                                        <!-- <li><a href="admin_print_pdf.php<?=$url_id2?>type=tbp_yearly_progress"><?=T_('Tanya Baal Peh Yearly Progress Chart')?></a></li> -->
                                        <li><a href="/admin_print_pdf.php<?=$url_id2?>type=tbp_progress_report_post"><?=T_('Tanya Baal Peh Weekly Quota Report')?></a></li>
                                        <li><a href="/admin_print_pdf.php<?=$url_id2?>type=tbp_monthly_quota_post"><?=T_('Tanya Baal Peh Monthly Quota Report')?></a></li>
                                    </ul>
                                </li>
                                <!--
                                <li><a href="#"><?=T_('Mileage card')?></a>
                                    <ul>
                                        <li><a href="admin_tanya_cards.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Print Achievement Cards')?></a></li>
                                    </ul>
                                </li>
                                -->
                                <li><a href="#"><?=T_('Testing')?></a>
                                    <ul>
                                        <li><a href="/admin_tanya_lines_print.php<?=$url_id?>"><?=T_('Print Tanya log sheet/bar-codes')?></a></li>
                                        <li><a href="/admin_tanya_cards.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Checkpoint mileage')?></a></li>
                                    </ul>
                                </li>
                                <li><a href="#"><?=T_('Modify Ladders')?></a>
                                    <ul>
                                        <li><a href="/admin_print_pdf.php<?=$url_id2?>type=tbp_growth_planner"><?=T_('Tanya Baal Peh Growth Planner')?></a></li>
                                        <li><a href="/admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=ladder_only"><?=T_('Manage Ladders')?></a></li>
                                    </ul>
                                </li>
                                <li><a href="#"><?=T_("Levi's Toah")?></a>
                                    <ul>
                                        <li><a href="/admin_users_tanya.php<?=$url_id2?>subject_id=<?=$subject_id?>&amp;mode=pledge"><?=T_('Enter Pledges and Income')?></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <?
                           break; // Tanya
                           default:
                        ?>
                        <!--<li class="list_parent"><a href="admin_points_print.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_('Print Achievement Cards')?></a></li>-->
						<li class="list_parent"><a href="/admin_users_track.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_("Manage Soldier's Ladders/Years")?></a></li>
                        <li class="list_parent"><a href="/admin_users_subject.php<?=$url_id2?>subject_id=<?=$subject_id?>"><?=T_("Solder's Enrollment")?></a></li>
                    <? break;
                       }
                    ?>
                <? endif; ?>
        </ul>
        <? endif; ?>
		*/ ?>
<? else: //dual_auth?>

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
<H1>
	<IMG src="/images/header.gif" width="30" height="30" alt="World Wide Tehillim Club"> Tzivos Hashem


	<?php if ($admin_user['auth'] == 'super' || !empty($admin_user['auths']['school'])) { ?>
	<!-- BEGIN Comm100 Live Chat Button Code -->
		<div>
			<div id="comm100_LiveChatDiv">
			</div>
			<div id="comm100_track" style="z-index:99;">
				<span style="font-size:10px; font-family:Arial, Helvetica, sans-serif;color:#555">
				</span>
			</div>
		</div>

	<!--</center>-->
	<!-- End Comm100 Live Chat Button Code -->
	<? } else if ($admin_user['auth'] == 'super' || !empty($admin_user['auths']['user'])) { ?>
	<!-- BEGIN Comm100 Live Chat Button Code -->
	<!--<center>-->

		<div>
			<div id="comm100_LiveChatDiv">
			</div>
			<div id="comm100_track" style="z-index:99;">
			</div>

		</div>
	<? } ?>



</H1>

<?if(isset($admin_user['auth'])):?>

<DIV id="menu">
<A HREF="/admin.php"><?=T_('Home')?></A>

<?if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['school'])):?>
<? $url_id = isset($school_id) ? "?school_id=$school_id" : ''; ?>

<A HREF="/admin_school.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'school' ? 'class="selected"' : ''?>><?=T_('Base Management')?></A>
<A HREF="/admin_school_subjects.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'programs' ? 'class="selected"' : ''?>><?=T_('Campaigns')?></A>
<A HREF="/admin_report_list.php<?=$url_id?>" <?=isset($ui_type) && $ui_type == 'reports' ? 'class="selected"' : ''?>><?=T_('Reports')?></A>
<?endif;?>

<?if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['class'])):?>
<? $url_id = isset($class_id) ? "?class_id=$class_id" : ''; ?>

<?endif;?>

<?if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['team'])):?>
<? $url_id = isset($team_id) ? "?team_id=$team_id" : ''; ?>

<?endif;?>

<?if($admin_user['auth'] == 'super' || !empty($admin_user['auths']['user'])):?>
<? $url_id = isset($user_id) ? "?user_id=$user_id" : ''; ?>

<?endif;?>

<A HREF="/logout.php"><?=T_('Logout')?></A>
</DIV>

<?else: //dual_auth?>

<?endif;?>

</DIV>
