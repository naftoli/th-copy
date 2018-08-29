<? 
session_start();

$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php'); 
assure_id_school('school_id'); // this will only allow the school logged in to see their school

include('objects/admin.php');
include('classes/school.php');
include('classes/user.php');
include('classes/school_class.php');	

$admin = new admin(null, $_SESSION['admin_id']);
$admin->get_schools();

$no_of_schools = count($admin->schools);

if ($no_of_schools == 1) {
	$school_id = $_SESSION['school_id'];
	$admin->get_school($school_id);
}
else {
	//print_r($_POST);
	if (isset($_POST['school_id'])) {
		$school_id = $_POST['school_id'];
		$admin->get_school($school_id);
	}
	else {
		$school_id = 0;
	}
}

$go = '0';	
$subject_id = 0;
$class_id = 0;
$user_id = 0;
	
$from_awarded = 2456305;
$to_awarded = unixtojd();

$from_promoted = 2456305;
$to_promoted = unixtojd();
	
if (isset($_POST['go']) && $_POST['go'] == '1') {
	$go = $_POST['go'];
	$user_id = $_POST['user_id'];
	
	$class_id = $_POST['hiddenClassId'];
		
	$subject_id = $_POST['subject_id'];
	$medals_filter = $_POST['medals_filter'];
		
	$ranks_card_filter = $_POST['ranks_card_filter'];
	$ranks_book_filter = $_POST['ranks_book_filter'];
		
	$from_awarded = $_POST['from_awarded'];
	$to_awarded = $_POST['to_awarded'];
	$from_promoted = $_POST['from_promoted'];
	$to_promoted = $_POST['to_promoted'];
		
	$sql = "SELECT * ";
	$sql .= "FROM users u 
	         join classes c using (class_id) 
	         WHERE ";		
	if ($school_id > 0)
		$sql .= "u.school_id=" . $school_id . " ";	
	if ($user_id > 0)
		$sql .= "AND u.user_id=" . $user_id . " ";	
	if ($class_id > 0)
		$sql .= "AND u.class_id=" . $class_id . " ";
    if ($school_id == 61)
        $sql .= "ORDER BY u.last, u.first";
    else 
        $sql .= "ORDER BY c.class_grade, c.class_sub, u.last, u.first";
		
	$query = mysql_query($sql);
		
	$report_users = array();
	while ($row = mysql_fetch_assoc($query)) {
		$report_user = new user($row);
			
		$report_user->get_medals($subject_id, $from_awarded, $to_awarded, $medals_filter);
		$report_user->get_ranks($from_promoted, $to_promoted, $ranks_card_filter, $ranks_book_filter);
			
		if ($report_user->num_rows_medals > 0 || $report_user->num_rows_ranks > 0) {
			$report_user->get_class_info();
			array_push($report_users, $report_user);
		}
	}
	
}

$user_id = gri('user_id', -1);
$subject = gri('subject_id', -1);

if (isset($_POST['medals_filter'])) 
	$medals_filter = $_POST['medals_filter'];
else
	$medals_filter = 0;

if (isset($_POST['ranks_card_filter']))	
	$ranks_card_filter = $_POST['ranks_card_filter'];
else
	$ranks_card_filter = 0;

if (isset($_POST['ranks_book_filter'])) 
	$ranks_book_filter = $_POST['ranks_book_filter'];
else	   
	$ranks_book_filter = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""https://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_("Soldier's Medal and Rank Report").' - '.T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		</script>
	</HEAD>

	<BODY>
		<?include('admin_header.php');?>
		
		<div id="info">
		</div>
		
		<DIV CLASS="body">

			<H1>"Soldier's Medal and Rank Report"</H1>
			
			<? if(!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>

				<script type="text/javascript">
					$(document).ready(function() {
					
						$('.submit').click(function() {
							if ($(this).attr('data') == '1') {
								$('#go').val('1');
								$('#hiddenClassId').val($('#class_id').val());
								document.forms["admin_received_stats_form"].submit();
							}
						});

						$('.receive_medal').click(function() {
							var spanId = $(this).attr('data');
							var span = $('#' + spanId);
							var checkbox = $(this);
							
							if ($(checkbox).attr('checked')) {
								var url = "https://mashpia.com/edit_functions.php?function_name=update_medal_marks&parameters=" + $(this).attr('data');							
								var checked = true;
							}
							else {							
								var url = "https://mashpia.com/edit_functions.php?function_name=unreceive_medal_mark&parameters=" + $(this).attr('data');
								var checked = false;
							}
							
							$.getJSON(url, function(success) {
								if (success == 0) {
									alert('Medal Received not updated');
								}
								else  {
									// If the mark was received then the date is returned and we display it //
									if (checked == true) 
										$(span).html(success);
									// If the mark was set to not received we clear the date //
									else
										$(span).html('');
								}
							});							
						});

						$('.date_book_received_checkbox').click(function() {
							var spanId = $(this).attr('data') + "_0";
							var span = $('#' + spanId);
						
							var checkbox = $(this);
							
							if ($(checkbox).attr('checked')) {
								var url = "https://mashpia.com/edit_functions.php?function_name=update_ranks_date_book_received&parameters=" + $(this).attr('data');
								var checked = true;
							}
							else {
								var url = "https://mashpia.com/edit_functions.php?function_name=unreceive_ranks_date_book_received&parameters=" + $(this).attr('data'); 
								var checked = false;
							}
							
							$.getJSON(url, function(success) {
								if (success == 0) {
									alert('Date book received not updated');
								}
								else { 
									if (checked == true) 
										$(span).html(success);
									else
										$(span).html('');
								}
							});
						});
													
						$('.date_card_received_checkbox').click(function() {
							var spanId = $(this).attr('data') + "_1";
							var span = $('#' + spanId);
						
							var checkbox = $(this);
							
							if ($(checkbox).attr('checked')) {
								var url = "https://mashpia.com/edit_functions.php?function_name=update_ranks_date_card_received&parameters=" + $(this).attr('data');
								var checked = true;
							}
							else {
								var url = "https://mashpia.com/edit_functions.php?function_name=unreceive_ranks_date_card_received&parameters=" + $(this).attr('data'); 
								var checked = false;
							}
							
							$.getJSON(url, function(success) {
								if (success == 0) {
									alert('Date card received not updated');
								}
								else { 
									if (checked == true) 
										$(span).html(success);
									else
										$(span).html('');
								}
							});							
						});
						
						$('.date_card_printed_checkbox').click(function() {
							var spanId = $(this).attr('data') + "_2";
							var span = $('#' + spanId);
						
							var checkbox = $(this);
							
							if ($(checkbox).attr('checked')) {
								var url = "https://mashpia.com/edit_functions.php?function_name=update_ranks_date_card_printed&parameters=" + $(this).attr('data');
								var checked = true;
							}
							else {
								var url = "https://mashpia.com/edit_functions.php?function_name=unreceive_ranks_date_card_printed&parameters=" + $(this).attr('data'); 
								var checked = false;
							}
							
							$.getJSON(url, function(success) {
								if (success == 0) {
									alert('Date card printed not updated');
								}
								else { 
									if (checked == true) 
										$(span).html(success);
									else
										$(span).html('');
								}
							});							
						});
						
					});
				</script>
				
				<!--<FORM  name="admin_received_stats_form" id="admin_received_stats_form" action="admin_received_stats.php" method="get" accept-charset="UTF-8">-->
				<FORM  name="admin_received_stats_form" id="admin_received_stats_form" action="admin_received_stats.php" method="post" accept-charset="UTF-8">				
					<input type="hidden" name="go" id="go" value="1">
					<input type="hidden" id="hiddenClassId" name="hiddenClassId" value="">
					
					<? if ($no_of_schools > 1) : ?>
					<P>
						<LABEL>
							Select Institution
							<SELECT name="school_id">
								<OPTION value="o">Choose a school</OPTION>
								<? foreach ($admin->schools as $school) : ?>
								<OPTION <? if ($school->school_id == $school_id) echo ' selected="selected" '; ?> value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
								<? endforeach; ?>
							</SELECT>
						</LABEL>
						
						<INPUT class="submit" data="0" type="submit" name="submit" value="'Go'">
					</P>
					<? else : ?>
					<P>
						<LABEL>School:&nbsp;<?=$admin->schools[0]->school_name;?></LABEL>
					</P>
					<? endif; ?>
					
					<? if ($school_id > 0) : ?>
						
						Choose Subject
						<SELECT name="subject_id">							
							<OPTION value="0">&lt;All&gt;</OPTION>
							<? foreach ($admin->school->subjects as $subject) : ?>
							<OPTION <? if ($subject['subject_id'] == $subject_id) echo ' selected="selected" '; ?> value="<?=$subject['subject_id'];?>">
								<?=$subject['subject_name'];?>
							</OPTION>
							<? endforeach; ?>
						</SELECT>

						<br>
						
						Choose Platoon
						<SELECT id="class_id" name="class_id">
							<OPTION value="0">&lt;All&gt;</OPTION>
							<? foreach ($admin->school->classes as $class) : ?>
							<OPTION <? if ($class->class_id == $class_id) echo ' selected="selected" '; ?> value="<?=$class->class_id;?>">
								<?=$class->class_grade . '' . $class->class_sub;?>
							</OPTION>
							<? endforeach; ?>
						</SELECT>
						
						<br>
						
						Choose Soldier
						<SELECT name="user_id">
							<OPTION value="0">&lt;All&gt;</OPTION>
							<? foreach ($admin->school->users as $user) : ?>
								<OPTION <? if ($user->user_id == $user_id) echo ' selected="selected" '; ?> data="<?=$user->class_id;?>" value="<?=$user->user_id;?>">
									<?=$user->first . ' ' . $user->last;?>
								</OPTION>
							<? endforeach; ?>
						</SELECT>
						
						<br>
						<br>
						
						Medals Filter
						<SELECT NAME="medals_filter">
							<OPTION <? if ($medals_filter == 0) echo " selected='selected' "; ?> VALUE="0">&lt;All&gt;</OPTION>
							<OPTION <? if ($medals_filter == 1) echo " selected='selected' "; ?> VALUE="1">Awarded But Not Received</OPTION>
							<OPTION <? if ($medals_filter == 2) echo " selected='selected' "; ?> VALUE="2">Awarded And Received</OPTION>
						</SELECT>
						
						<br>
						
						Rank Book Received Filter
						<SELECT NAME="ranks_book_filter">
							<OPTION <? if ($ranks_book_filter == 0) echo " selected='selected' "; ?> VALUE="0">&lt;All&gt;</OPTION>
							<OPTION <? if ($ranks_book_filter == 1) echo " selected='selected' "; ?> VALUE="1">Received</OPTION>
							<OPTION <? if ($ranks_book_filter == 2) echo " selected='selected' "; ?> VALUE="2">Not Received</OPTION>
						</SELECT>
						
						<br>
												
						Rank Card Received Filter
						<SELECT NAME="ranks_card_filter">
							<OPTION <? if ($ranks_card_filter == 0) echo " selected='selected' "; ?> VALUE="0">&lt;All&gt;</OPTION>
							<OPTION <? if ($ranks_card_filter == 1) echo " selected='selected' "; ?> VALUE="1">Received</OPTION>
							<OPTION <? if ($ranks_card_filter == 2) echo " selected='selected' "; ?> VALUE="2">Not Received</OPTION>
						</SELECT>
						
						<br>
						<br>
						
						<INPUT type="hidden" name="from_awarded" value="<?=$from_awarded?>"> &nbsp;
						<LABEL>
							Medals Awarded Between
							<INPUT type="text" name="from_awarded_disp" READONLY value="<?=es(dateToHebrew($from_awarded))?>" onClick="getDate(this.form, 'from_awarded', true);"/>
						</LABEL>
						
						<INPUT type="hidden" name="to_awarded" value="<?=$to_awarded?>">
						<LABEL>
							And
							<INPUT type="text" name="to_awarded_disp" READONLY value="<?=es(dateToHebrew($to_awarded))?>" onClick="getDate(this.form, 'to_awarded', true);"/>
						</LABEL>
						
						<br>
						
						<INPUT type="hidden" name="from_promoted" value="<?=$from_promoted?>">&nbsp;
						<LABEL>
							Rank Promoted Between: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<INPUT type="text" name="from_promoted_disp" READONLY value="<?=es(dateToHebrew($from_promoted))?>" onClick="getDate(this.form, 'from_promoted', true);"/>
						</LABEL>

						<INPUT type="hidden" name="to_promoted" value="<?=$to_promoted?>">
						<LABEL>
							And
							<INPUT type="text" name="to_promoted_disp" READONLY value="<?=es(dateToHebrew($to_promoted))?>" onClick="getDate(this.form, 'to_promoted', true);"/>
						</LABEL>
						
						<br>
						<br>
						
						<? if ($no_of_schools == 1) : ?>
						<INPUT class="submit" data="1" type="button" value="Go">
						<? endif; ?>
						
					<? endif; ?>					
					<!-- if ($school_id > 0)  -->
					
					<? if ($go == '1') : ?>
						
						<div align="center">
                       		<input type='button' value='Print' onclick='window.print()'><br /><br />
                    	</div>
						
						<FORM action="admin_received_stats.php" method="post" accept-charset="UTF-8" name="y">    						
							<TABLE class="pretty_grid" style="font-size:12px;">
							
								<thead>							
									<tr>
										<th>User ID</th>
										<th>Soldier</th>
										<th colspan="1">Grade</th>
										<th colspan="1">Subject</th>
										<th colspan="1">Medal</th>
										<th colspan="1">Date Earned</th>
										<th colspan="1">Medal Sent By HQ</th>
										<th colspan="1">Medal Recived From HQ</th>
										<th colspan="1">Rank</th>
										<th colspan="1">Date Promoted</th>
										<th colspan="1">Rank Book Received</th>
										<th colspan="1">Rank Card Received</th>
										<th colspan="1">Rank Card Printed</th> 	  
									</tr>									
								</thead>
								
								<!-- ********** USERS ********** -->
								<? foreach ($report_users as $report_user) : ?>
									<tr>
										<td><?=$report_user->user_id?></td>
										<td style="white-space:nowrap; text-align:left">
											<span style="color:blue;"><?=$report_user->last . "  - " . $report_user->first; ?></span>
										</td>
										
										<td>
											<? if ($report_user->class_sub == '') : ?>
												<?=$report_user->class_grade;?>
											<? else : ?>
												<?=$report_user->class_grade . "-" . $report_user->class_sub;?>
											<? endif; ?>
										</td>
										
										<td>
											<INPUT type="hidden" name="school_id" value="<?=$report_user->school_id;?>">
										</td>
										
										<td>
											<INPUT type="hidden" name="class_id" value="<?=$report_user->class_id;?>">
										</td>
										
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
								
									<!-- ********** MEDALS ********** -->
									<? foreach ($report_user->medals as $medal) : ?>
										<tr>
											<td></td>
											<td></td>
											<td>
												<?=$medal['subject_name'];?>
											</td>
											<td>
												<?=$medal['medal_name'];?>
											</td>
											<td style="white-space:nowrap">
												<?=dateToHebrew($medal['date_awarded']);?>
											</td>
											<td>
												<?= !is_null($medal['date_shipped']) ? substr($medal['date_shipped'], 0, 10) : "Not Shipped Yet";?>
											</td>
											<TD style="text-align:center">											
												<? if (!is_null($medal['date_received'])) : ?>
												<span id="<?=$report_user->user_id . '_' . $medal['subject_id'] . '_' . $medal['medal_ord'];?>"><?=substr($medal['date_received'], 0, 10);?></span>
												<LABEL>
													<INPUT type="checkbox" checked="checked" data="<?=$report_user->user_id . '_' . $medal['subject_id'] . '_' . $medal['medal_ord'];?>" class="receive_medal">
												</LABEL>																									
												<? else : ?>
												<span id="<?=$report_user->user_id . '_' . $medal['subject_id'] . '_' . $medal['medal_ord'];?>"></span>
												<LABEL>
													<INPUT type="checkbox" data="<?=$report_user->user_id . '_' . $medal['subject_id'] . '_' . $medal['medal_ord'];?>" class="receive_medal">
												</LABEL>																									
												<? endif; ?>												
											</TD>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>	
										</tr>
									
									<? endforeach; ?>
									<!-- ********** MEDALS ********** -->
									
									<!-- ********** RANKS ********** -->
									<? foreach ($report_user->ranks as $rank) : ?>
										<tr>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<TD></TD>
											
											<td>
												<? if ($rank['rank_color'] != '') : ?>
												<span style="color:<?=$rank['rank_color'];?>;"><?=$rank['rank_name'];?></span>
												<? else : ?>
												<span><?=$rank['rank_name'];?></span>
												<? endif; ?>
											</td>
											
											<td>
												<?=dateToHebrew($rank['date_promoted']);?>
											</td>
											
											<TD style="text-align:center">											
												<? if ($rank['date_book_received'] == '') : ?>
													<span id="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>_0"></span>
													<LABEL>
														<INPUT type="checkbox" class="date_book_received_checkbox" data="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>">
													</LABEL>
												<? else : ?>
													<span id="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>_0"><?=substr($rank['date_book_received'], 0, 10);?></span>
													<LABEL>
														<INPUT type="checkbox" checked="checked" class="date_book_received_checkbox" data="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>">
													</LABEL>													
												<? endif; ?>
											</TD>
											
											<TD style="text-align:center">
												<? if ($rank['date_card_received'] == '') : ?>
													<span id="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>_1"></span>
													<LABEL>
														<INPUT type="checkbox" class="date_card_received_checkbox" data="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>">
													</LABEL>
												<? else : ?>
													<span id="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>_1"><?=substr($rank['date_card_received'], 0, 10);?></span>
													<LABEL>
														<INPUT type="checkbox" checked="checked" class="date_card_received_checkbox" data="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>">
													</LABEL>													
												<? endif; ?>
											</TD>
											
											<TD style="text-align:center">
												<? if (is_null($rank['date_printed'])) : ?>
													<span id="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>_2"></span>
													<LABEL>
														<INPUT type="checkbox" class="date_card_printed_checkbox" data="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>">
													</LABEL>
												<? else : ?>
													<span id="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>_2"><?=substr($rank['date_printed'], 0, 10);?></span>
													<LABEL>
														<INPUT type="checkbox" checked="checked" class="date_card_printed_checkbox" data="<?=$report_user->user_id . '_' . $rank['rank_ord'];?>">
													</LABEL>													
												<? endif; ?>
											</TD>	
										</tr>
									
									<? endforeach; ?>
									<!-- ********** RANKS ********** -->
									
								<? endforeach; ?>
								<!-- ********** USERS ********** -->
								
							</TABLE>
						</FORM>
						
					<? endif; ?>
					
				</FORM>
				
			<? //if ($admin->auth == 'super' || $no_of_schools > 1) : ?>
			<? //endif; ?>
			<!-- if ($admin->auth == 'super' || $no_of_schools > 1) -->
			
			
	</BODY>
	
</HTML>
