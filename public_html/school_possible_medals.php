<?php
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');
require('classes/school.php'); 

// ********** SCHOOLS ********** //
$schools = array();
$sql = "SELECT * FROM schools ORDER BY school_name";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{
	$school = new \classes\school($row);
	array_push($schools, $school);
}
// ********** SCHOOLS ********** //

$start_date = beginning_of_hebrew_year();
$end_date = gri('end_date', unixtojd()+30);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Mission Report'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	
	<BODY>
		<?php include('admin_header.php'); ?>
		
		<div>
		
			<div class="body">

				<div class="sub_menu">
				</div>
			
				<div class="noprint">
				
					<H1>Reports</H1>
										
						<script>
							$(document).ready(function() 
							{		
								var page_loaded = false;
								
								$("#school_id").change(function () {
									get_classes();
									page_loaded = true;
								});

								$("#class_id").change(function () 
								{
									// ***** in icalendar.js ***** //
									get_report_two();
								});
																		
								get_classes();
																
								function get_classes() 
								{
									var function_name = "get_school_classes_options";				
									var parameters = [$("#school_id").val()];
									var url = "get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
								
									$.getJSON(url, function(options) 
									{
										$("#class_id").html(options);
										
										// ***** in icalendar.js ***** //
										get_report();
									});
								}	

								function get_report_two()
								{
									if ($("#class_id").val() == null)
										var class_id = 0;
									else
										var class_id = $("#class_id").val();

									var url = "user_possible_medals.php?school_id=" + $("#school_id").val() + "&class_id=" + class_id + "&start_date=" + $("#start_date").val() + "&end_date=" + $("#end_date").val();
									
									var http = getHTTPObject();
									http.open("GET", url, true);
												
									http.onreadystatechange = function() {
																			
										if (http.readyState == 4 && http.status == 200) 
										{
											$("#report_div").html(http.responseText);
										} 
															
									}
															
									http.send(null);				
								}

							});
						</script>

						
					<input type="hidden" name="action" value="report">
						
					<p>
						<label>
							Select Institution													
							<select name="school_id" id="school_id">
								<? foreach ($schools as $school) : ?>
								<option value="<?=$school->school_id;?>"><?=$school->school_name;?></option>
								<? endforeach; ?>
							</select>
						</label>
						
						<br />
						
						<label>
							Choose Platoon
							<select name="class_id" id="class_id">
							</select>
						</label>
							
						<br />
							
						<label>
							Start Date
							<input type="text" name="start_date_disp" id="start_date_disp" value="<?=es(dateToHebrew($start_date))?>" onClick="get_date(this.form, 'start_date', true);">
						</label>

						<INPUT type="hidden" name="start_date" id="start_date" value="<?=$start_date?>">
						
						<br />
						
						<label>
							End Date
							<input type="text" name="end_date_disp" id="end_date_disp" value="<?=es(dateToHebrew($end_date))?>" onClick="get_date(this.form, 'end_date', true);">
						</label>
							
						<INPUT type="hidden" name="end_date" id="end_date" value="<?=$end_date?>"> <?=T_('Usually, last day of school or term.')?>
						
						<BR>
							
					</p>
						
				</div>
				
			</div>
			
		</div>
		
		<div id="report_div" name="report_div">
		</div>
		
	</BODY>
</HTML>
