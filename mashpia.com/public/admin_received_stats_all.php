<? 
session_start();

$admin_auth = array('school'); 
require_once('header.php'); 
require_once('calendar.php'); 
assure_id_school('school_id'); // this will only allow the school logged in to see their school

if (isset($_POST['submit'])) {
    //print_r($_POST);
    //exit;
    
    $go = true;
    require_once('classes/school.php');
    require_once('classes/user.php');
    require_once('classes/school_class.php');
    
    $from_awarded = $_POST['from_awarded'];
    $to_awarded = $_POST['to_awarded'];
    
    $from_promoted = $_POST['from_promoted'];
    $to_promoted = $_POST['to_promoted'];
       
    //get all schools
    $sch = "select school_id, school_name from schools where school_era is null order by school_name";
    $res = mysql_query($sch);
    
    $report_users = array();
    while ($sch_row = mysql_fetch_assoc($res)) {
    
        $sql = "SELECT * ";
        $sql .= "FROM users u ";
        $sql .= "join classes c using (class_id) ";       
        $sql .= "where u.school_id=" . $sch_row['school_id'] . " ";   
        $sql .= "ORDER BY c.class_grade, c.class_sub, u.last, u.first"; 
        $query = mysql_query($sql);
            
        while ($row = mysql_fetch_assoc($query)) {
            $report_user = new user($row);
                
            $report_user->get_medals(0, $from_awarded, $to_awarded, 0);
            $report_user->get_ranks($from_promoted, $to_promoted, 0, 0);
                
            if ($report_user->num_rows_medals > 0 || $report_user->num_rows_ranks > 0) {
                $report_user->get_class_info();
                $report_users[$sch_row['school_name']][] = $report_user;
            }
        }
    }
} else { 
    $go = FALSE;
    
    $from_awarded = 2456224;
    $to_awarded = unixtojd();
    
    $from_promoted = 2456224;
    $to_promoted = unixtojd();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_("Soldier's Medal and Rank Report").' - '.T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<style type='text/css'>
            @media all {
                .page-break {
                    display: none;
                }
            }
            @media print {
                .page-break {
                    display: block;
                    page-break-before: always;
                }
            }
            .small {
                font-size: 11px;
            }
        </style>
	</HEAD>

	<BODY>
		<?include('admin_header.php');?>
		
		<DIV CLASS="body">

			<H1>"Soldier's Medal and Rank Report"</H1>
			
                <? if (!$go) { ?>
                    
                    <FORM name="admin_received_stats_form" id="admin_received_stats_form" action="admin_received_stats_all.php" method="post" accept-charset="UTF-8">              
						
						<INPUT type="hidden" name="from_awarded" value="<?=$from_awarded?>"> &nbsp;
						<LABEL>
							Medals Awarded Between: 
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
							Rank Promoted Between: 
							<INPUT type="text" name="from_promoted_disp" READONLY value="<?=es(dateToHebrew($from_promoted))?>" onClick="getDate(this.form, 'from_promoted', true);"/>
						</LABEL>

						<INPUT type="hidden" name="to_promoted" value="<?=$to_promoted?>">
						<LABEL>
							And
							<INPUT type="text" name="to_promoted_disp" READONLY value="<?=es(dateToHebrew($to_promoted))?>" onClick="getDate(this.form, 'to_promoted', true);"/>
						</LABEL>
						
						<br>
						<br>
						
						<INPUT type="submit" name="submit" value="Go">
						    
					</FORM>
											
				<? } else { ?>
				        
				    <script type="text/javascript">
                        $(document).ready(function() {
                            
                            $('.receive_medal').click(function() {
                                var spanId = $(this).attr('data');
                                var span = $('#' + spanId);
                                var checkbox = $(this);
                                
                                if ($(checkbox).attr('checked')) {
                                    var url = "http://mashpia.com/edit_functions.php?function_name=update_medal_marks&parameters=" + $(this).attr('data');                          
                                    var checked = true;
                                }
                                else {                          
                                    var url = "http://mashpia.com/edit_functions.php?function_name=unreceive_medal_mark&parameters=" + $(this).attr('data');
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
                                    var url = "http://mashpia.com/edit_functions.php?function_name=update_ranks_date_book_received&parameters=" + $(this).attr('data');
                                    var checked = true;
                                }
                                else {
                                    var url = "http://mashpia.com/edit_functions.php?function_name=unreceive_ranks_date_book_received&parameters=" + $(this).attr('data'); 
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
                                    var url = "http://mashpia.com/edit_functions.php?function_name=update_ranks_date_card_received&parameters=" + $(this).attr('data');
                                    var checked = true;
                                }
                                else {
                                    var url = "http://mashpia.com/edit_functions.php?function_name=unreceive_ranks_date_card_received&parameters=" + $(this).attr('data'); 
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
                                    var url = "http://mashpia.com/edit_functions.php?function_name=update_ranks_date_card_printed&parameters=" + $(this).attr('data');
                                    var checked = true;
                                }
                                else {
                                    var url = "http://mashpia.com/edit_functions.php?function_name=unreceive_ranks_date_card_printed&parameters=" + $(this).attr('data'); 
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
                    
                    <div align="center">
                        <input type='button' value='Print' onclick='window.print()'><br /><br />
                    </div>
													
							<? foreach ($report_users as $k => $v) { ?>
								
								<div align='center'>
									<?=$k?><br />
									<span class='small'>
									Medals awarded between <?=$_POST['from_awarded_disp']?> and <?=$_POST['to_awarded_disp']?><br />
									Rank promoted between <?=$_POST['from_promoted_disp']?> and <?=$_POST['to_promoted_disp']?><br />
								    </span>
								</div>
								<br />
																
									<TABLE class="pretty_grid" style="font-size:12px;">
									
										<thead>							
											<tr>
												<th>Soldier</th>
												<th colspan="1">Grade</th>
												<th colspan="1">Subject</th>
												<th colspan="1">Medal</th>
												<th colspan="1">Date Earned</th>
												<th colspan="1">Medal Received</th>
												<th colspan="1">Rank</th>
												<th colspan="1">Date Promoted</th>
												<th colspan="1">Rank Book Received</th>
												<th colspan="1">Rank Card Received</th>
												<th colspan="1">Rank Card Printed</th> 	  
											</tr>									
										</thead>
										
										<!-- ********** USERS ********** -->
										<? foreach ($v as $report_user) : ?>
											<tr>
												<td>
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
									<br />
									<div class='page-break'></div>

                <?
                    }
                }
                ?>
									
			<? //if ($admin->auth == 'super' || $no_of_schools > 1) : ?>
			<? //endif; ?>
			<!-- if ($admin->auth == 'super' || $no_of_schools > 1) -->
					
	</BODY>
	
</HTML>