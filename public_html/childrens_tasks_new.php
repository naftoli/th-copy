<?php
//header("Location: under_construction.php");
$admin_auth = array('user');
require('header.php'); 

$_SESSION['program_name'] = 'children_tasks';

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_markable_children();

require 'classes/Reports.class.php';
$Reports = new Reports($exceptions = array(2456761, 2456768)); 
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
		</div>
		
		<form method="post" name="get_tasks_form" id="get_tasks_form" action="mission_report/newParentMultiplePrint.php">
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
