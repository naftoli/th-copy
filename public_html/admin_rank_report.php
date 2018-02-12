<?php

//if (!isset($_GET['admin_id'])) 
	//header("Location: admin.php");
	
$admin_auth = array('school'); 

require('header.php'); 
require_once('calendar.php');
require_once('classes/rank.php');
require_once('classes/school.php');

require_once('objects/admin.php');
$admin = new admin(NULL, $_GET['admin_id']);
$admin->get_school_id();

$ranks = array();
$sql = "SELECT ranks.*, COUNT(*) num ";
$sql = $sql . "FROM ranks ";
$sql = $sql . "LEFT JOIN rank_marks USING (rank_ord) ";
$sql = $sql . "WHERE rank_ord <= (SELECT MAX(rank_ord) FROM rank_marks) ";
$sql = $sql . "GROUP BY rank_ord, rank_name, rank_color ";
$sql = $sql . "ORDER BY rank_ord";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{
	$rank = new rank($row);
	array_push($ranks, $rank);
}

$school_id = 0;
if (isset($_GET['school_id']))
	$school_id = $_GET['school_id'];
	
$schools = array();
$sql = "SELECT * ";
$sql = $sql . "FROM schools ";
if ($school_id > 0 && $admin->auth != 'super')
	$sql = $sql . "WHERE school_id=" . $school_id . " ";
$sql = $sql . "ORDER BY school_name";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{
	$school = new school($row);
	$school->get_student_rank_totals($ranks);
	array_push($schools, $school);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_("Rank Report"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">	

	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">

			<H1><?=T_("Rank Report")?></H1>
			
			<P><?=T_('Click on a name to "drill-down" for more detail.')?></P>

			<P><?=T_('Note: This counts ALL ranks received! Not just the highest rank received.')?></P>

		<script>
			$(function()
			{
				$('#back_button').live('click', function() {
					$("#report_div_one").css("display", "block");
					$("#report_div_two").css("display", "none");
				});
			});
			
			function get_school_ranks(school_id)
			{				
				var url = "ajax_get_school_ranks.php?school_id=" + school_id;
				$.ajax({ url: url, success: function(data) { $("#report_div_one").css("display", "none"); $("#report_div_two").css("display", "block");$("#report_div_two").html(data); } });
			}
			
			function get_class_ranks(class_id)
			{
				var url = "ajax_get_class_ranks.php?class_id=" + class_id;
				$.ajax({ url: url, success: function(data) { $("#report_div_one").css("display", "none"); $("#report_div_two").css("display", "block");$("#report_div_two").html(data); } });
			}
		</script>		
			
			<DIV id="report_div">
			
				<DIV id="report_div_one">
					<TABLE class="pretty_grid">
					
						<CAPTION>Schools</CAPTION>
						
						<THEAD>
							<TR>
								<TH></TH>
								<? foreach ($ranks as $rank): ?>
								<TH <?php if ($rank->rank_color != "") echo "style='color:" . $rank->rank_color . ";'"; ?>>
								<?=$rank->rank_name?>
								</TH>
								<? endforeach; ?>
							</TR>
						</THEAD>
						
						<TBODY>					
							<? foreach ($schools as $school) : ?>
							<tr>
								<th style="text-align: left;">
									<a onclick="get_school_ranks(<?=$school->school_id;?>);" href="javascript:void(0) ;"><?=$school->school_name;?></a>								
								</th>
								<? foreach ($school->ranks as $rank) : ?>
								<td style="text-align: center;"><? if ($rank->total_students > 0) echo $rank->total_students;?></td>
								<? endforeach; ?>
							</tr>
							<? endforeach; ?>
						</TBODY>	

						<TFOOT>
							<TR>
								<TH>Totals</TH>
								<? foreach ($ranks as $rank): ?>
								<TH <?php if ($rank->rank_color != "") echo "style='color:" . $rank->rank_color . ";'"; ?>>
								<? if ($rank->total_students > 0) echo $rank->total_students;?>
								</TH>
								<? endforeach; ?>						
							</TR>
						</TFOOT>
						
					</TABLE>
				</DIV>
				
				<DIV id="report_div_two">
				</DIV>
				
				<DIV id="report_div_three">
				</DIV>
				
			</DIV>
			
			
		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
</HTML>
