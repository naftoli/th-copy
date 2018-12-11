<?php
//header("Location: under_construction.php");
$admin_auth = array('user');
require('header.php'); 

$_SESSION['program_name'] = 'children_tasks';

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \camps\classes\admin($row);
$admin->get_markable_children();

require 'classes/Reports.class.php';
$Reports = new Reports($exceptions = array(2456761, 2456768)); 

$show = false;
if (isset($_POST['action']) && $_POST['action'] == "produce_report") {

	$show = true;
    $days_of_the_week = array("M", "T", "W", "T", "F", "ש", "S");
					
	include("classes/user_track.php");
	include 'class.taskExceptions.php';
	include("classes/date_tasks_mission.php");
	include("classes/daily_task.php");
	include("classes/weekly_task.php");
	include("classes/shabbos_task.php");
	include("classes/no_label_task.php");
	include("classes/task.php");
	include("classes/date_tasks_mark.php");
	
	$child_ids = explode(':', $_POST['children']);
	$period_info = explode(':', $_POST['periods']);
	
    $dates = array();
    $report_names = array();			
	foreach ($period_info as $period) {
        $info = explode(';', $period);
        $dates['start'][] = $info[0];
        $dates['end'][] = $info[1];
        //get report names
        $sql = "SELECT report_name FROM reports WHERE start_date=" . $info[0] . " AND end_date=" . $info[1];
        $query = mysql_query($sql);
        $report = mysql_fetch_assoc($query);
        $report_names[] = $report['report_name'];
    }
	
	$report_dates = array();         		
	$children = array();        
    if ( $_POST['method'] == 'byChild' ) {
		foreach ($child_ids as $child) {
            for ( $i = 0; $i < count( $dates['start'] ); $i++ ) {
                $children[] = generateMissions( $child, $dates['start'][$i], $dates['end'][$i], $report_names[$i] );
				$report_dates[$report_names[$i]] =  $dates['start'][$i];				
			} 
    	}
    } else if ( $_POST['method'] == 'byWeek' ) {
        for ( $i = 0; $i < count( $dates['start'] ); $i++ ) {
            foreach ($child_ids as $child) { 
                $children[] = generateMissions( $child, $dates['start'][$i], $dates['end'][$i], $report_names[$i] );                 
				$report_dates[$report_names[$i]] =  $dates['start'][$i];
			}
        }       
    }				
    /*
    echo "<pre>";
    print_r( $children );
    echo "</pre>";
    exit;
     * 
     */	
}

function generateMissions( $child, $start_date, $end_date, $report_name ) { 
    $sql = "SELECT * FROM users WHERE user_id=" . $child;
    $query = mysql_query($sql);
    $row = mysql_fetch_assoc($query);
    $user = new user($row);
    $user->get_school();
    $school_id = $user->school->school_id;
    $user->get_school_class();      
    $user->get_rank();
    $user->get_user_tracks(-1, $start_date, $end_date);  
    return array( $report_name => $user );
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Childrens Tasks - Tzivos Hashem Management System</title>
		<link href="/admin_styles.css" rel="stylesheet" type="text/css">
		
		<script language="javascript" type="text/javascript">
            var no_of_markable_children = <?=count($admin->children);?>
            
            function check_no_of_children()
            {
                if (no_of_markable_children == 0) {
                    alert("You have no access to this page. Please contact your school administrators.");
                    window.location.href = "admin.php";
                }
            }
        </script>
        
		<script type="text/javascript" src="jquery.js"></script>
		<script type="text/javascript" src="scripts/jquery.autocolumn.js"></script>
		<style type="text/css">
		    .selectionTable tr, th, td {
		        padding: 10px;
		        vertical-align: top;
		    }
		    @media print {
		        .no-print {
		            display: none;
		        }
		    }
		    .page-break {
		        page-break-after: always;
		    }
		    .scrollable {
		        height: 150px;
		        width: 250px;
                overflow-x: scroll;
		    }
		</style>
	</head>

	<body onload="check_no_of_children()">

		<div id="info">
		</div>
		
		<? include('admin_header.php'); ?>
		
		<h1 class="no-print">Children Mission Sheets</h1>
		
		<div class="body left marking_missions">
		    
		    <? if ( !$show ) { ?>
		
			<div class="infobox2 marking_list clearfix">
			    
			    <div align="center">
			        <table class="selectionTable">
			            <tr>
			                <th>Children</th>
			                <th>Weeks</th>
			            </tr>
			            <tr>
			                <td id="child_id">
			               <? for ($cno = 0; $cno < count($admin->children); $cno++) : ?>
                                <input type="checkbox" class='children' name="children[]" value="<?=$admin->children[$cno]->user_id;?>" checked="checked">
                                <?=$admin->children[$cno]->first;?> <?=$admin->children[$cno]->last;?><br />
                            <? endfor; ?>
                            </td>
                            <td id="report_period">
                                <div class="scrollable">
                                    <? foreach ($Reports->reports as $report) : ?>
                                    <? //if ($report['start_date'] >= 2456927) continue; ?>
                                        <input type="checkbox" class='periods' name="periods[]" value="<?=$report['start_date'] . ';' . $report['end_date'];?>" 
                                        <?
                                        //find current week
                                        $jd = unixtojd();
										if ( $jd >= $report['start_date'] && $jd <= $report['end_date'] ) 
											echo "checked='checked'";
                                        ?>
                                        >
                                        <?=$report['report_name'];?> - <?=jdtogregorian($report['start_date']);?><br />
                                    <? endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="center"><input type='button' id='checkChildren' value='Check All' />
                                <input type='button' id='uncheckChildren' value='Uncheck All' /></td>
                            <td align="center"><input type='button' id='checkPeriods' value='Check All' />
                                <input type='button' id='uncheckPeriods' value='Uncheck All' /></td>
                        </tr>
                        <tr>
                            <td align="center" colspan="2">Please choose how you would like the sheets to print:</td>
                        </tr>
                        <tr>
                            <td align="center" colspan="2">
                                <input type='radio' class="method" name="method" value="byChild" checked="checked"/> By Child<br />
                                <input type='radio' class="method" name="method" value="byWeek" /> By Week<br />
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="button" id="get_tasks_button" value="Generate Mission Sheets">
                            </td>
			            </tr>
			        </table>   				
				</div>			
			</div>
			
			<? } else { ?>
			
			<div align='center'>
                <input type='button' value='Print' onclick='window.print()' class="no-print"><br /><br />
            </div>
            
            <? //print_r( $_POST ); exit; ?>
			
			<? 
			foreach ( $children as $info ) {
			    foreach( $info as $report_name => $user ) { 
				
				if ($report_dates[$report_name] >= 2456906) {
					$days_of_the_week = array("ש", "S", "M", "T", "W", "T", "F");
				} else {
					$days_of_the_week = array("M", "T", "W", "T", "F", "ש", "S");
				}
			?>
			           
            <!-- ********** TASKS DIV ********** -->
            <div id="tasks_div">
            
                <!-- ********** PRINT INDIVIDUAL ********** -->
                <div class="print_individual">  
                
                    <!-- ********** PRINT HEADER ********** -->
                    <div class="print_header">
                    
                        <div class="marking module clearfix dontsplit">
                        
                            <div class="rank_image">
                                <img height="70" src="/file_view.php?id=<?=$user->rank_image_id;?>">
                            </div>
                            
                            <? if ($user->user_photo_id) : ?>
                            <div class="user_image">
                                <img height="70" src="/file_view.php?id=<?=$user->user_photo_id;?>">
                            </div>
                            <? endif; ?>                            
                            
                            <p class="print_name"><?=$user->rank_name;?> <?=$user->first;?> <?=$user->last;?></p>
                            <p class="print_week">
                            	My Missions for Shabbos <?=$report_name;?> and following week
                            </p>
                            <? if ( isset( $user->school_class->class_grade ) ) { ?>
                            <p class="print_class">Grade: <?=$user->school_class->class_grade;?> - <?=$user->school_class->class_sub;?> <?=$user->school_class->class_teacher;?></p>
                            <? } ?>
                            <p class="print_sig">Parent's Signature<span></span></p>
                            
                        </div>
                        
                    </div>
                    <!-- ********** PRINT HEADER ********** -->
                    
                    <? include("daily_tasks.php"); ?>
                    
                    <div class="print_content"></div>
                    
                    <div class="tasks_page_two">    
                        <? include("weekly_tasks.php"); ?>
                        <? include("no_label_tasks.php"); ?>
                        <? include("shabbos_tasks.php"); ?>
                    </div>
                    
                </div>
                <!-- ********** PRINT INDIVIDUAL ********** -->
                
    
            </div>
            <!-- ********** TASKS DIV ********** -->
            
            <div style="width:100%; height:1px; boder:1px solid black;"></div>
            <div class="page-break"></div>
                       
                <?
                }
            }
        }
        ?>
        			
		</div>
		
		<form method="post" name="get_tasks_form" id="get_tasks_form" action="childrens_tasks.php">
			<input type="hidden" name="action" value="produce_report" />
			<input type="hidden" name="children" id="children" value="" />
			<input type="hidden" name="periods" id="periods" value="" />
			<input type="hidden" name="method" id="method" value="" />
		</form>
		
		<script type="text/javascript">
			$(document).ready(function(){
				$('.tasks_page_two').columnize({
					columns: 2,
					lastNeverTallest: true, 
					ignoreImageLoading: false,  
					doneFunc: function() {
						var content = $('#content');
						var nav = $('#nav');
						$(nav).css('height', $(content).height());
                    }
				});
			});
			
			$('#get_tasks_button').click(function(){
				var children = '';
				$("#child_id input:checked").each(function(){
					children = children + $(this).val() + ':';
				});
				children = children.substr(0, children.length - 1)
				$('#children').val(children);
				
				var periods = '';
				$("#report_period input:checked").each(function(){
					periods = periods + $(this).val() + ':'
				});
				periods = periods.substr(0, periods.length - 1);				
				$('#periods').val(periods);
				
				var method = $(".method:checked").val();
				$("#method").val( method );
				
				if (children == '' || periods == '')
					alert('You must pick at least one child and one period.');
				else
					$('#get_tasks_form').submit();
			});
			
			$("#checkChildren").click( function() {
                $(".children").attr("checked", true);      
            });
            
            $("#uncheckChildren").click( function() {
                $(".children").attr("checked", false);
            });
            
            $("#checkPeriods").click( function() {
                $(".periods").attr("checked", true);
            });
            
            $("#uncheckPeriods").click( function() {
                $(".periods").attr("checked", false);
            });
		</script>
	</body>	
	
</html>
