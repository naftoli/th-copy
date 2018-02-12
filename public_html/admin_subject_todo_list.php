<? 
$admin_auth = array('school');
require('header.php');

$ui_type = 'programs';
require_once('admin_ui.php');
require_once('calendar.php');

$auth_mode = check_id_access();

require("classes/admin.php");
require("classes/school.php");

$school_id = 0;
$subject_id = $_GET['subject_id'];

$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);

$schools = array();

if ($admin->auth == "super")
{
	$admin_auth = "super";
	$sql = "SELECT * FROM schools ORDER BY school_name";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query))
	{
		$school = new school($row);
		array_push($schools, $school);
	}
}
else
{	
	$admin->get_schools();
	
	foreach ($admin->schools as $school)
	{
		array_push($schools, $school);
	}
	
	if (count($admin->schools) == 1)
		$admin_auth = "not super";
	else
		$admin_auth = "super";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Campaign - To Do List'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY onload="check_school_id();">
	
		<? include('admin_header.php'); ?>
	
		<DIV name="report_div" style="position:absolute; top:50px;">
		</DIV>
		
		<script type="text/javascript">	
			var admin_auth = "<?=$admin_auth;?>"; 
			var school_id = <?=$school_id;?>;
			var subject_id = <?=$subject_id;?>;
			
			function check_school_id()
			{
				if (school_id > 0)
					get_todo_list(school_id);
			}
			
			$(function() {
				$('.marking_list div a.next').click(function(){
					$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
				});
				
				$('.marking_list div a.prev').click(function(){
					$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
				});
				
				$(".school_list select").sSelect().change(function () {
					get_todo_list($(this).val());
				});
				
				$('#mark').live('click', function() {
				
					var a_tag = $(this);
					var action = $(a_tag).attr("action");
					
					if (school_id > 0)
						var url = "ajax_todo_mark.php?action=" + action + "&school_id=" + school_id + "&todo_id=" + $(this).attr('data');
					else
						var url = "ajax_todo_mark.php?action=" + action + "&school_id=" + $("#school_id").val() + "&todo_id=" + $(this).attr('data');
					
					var http = getHTTPObject();
					http.open("GET", url, true);
									
					http.onreadystatechange = function() {
															
						if (http.readyState == 4 && http.status == 200) 
						{
							if (http.responseText > 0)
							{
								if (action == "mark")
								{
									$(a_tag).attr("action", "unmark");
									$(a_tag).html("Un-Mark as done");
								}
								else
								{
									$(a_tag).attr("action", "mark");
									$(a_tag).html("Mark as done");
								}
							}
							else
							{
								alert("Not marked as done");
							}
						} 
												
					}
												
					http.send(null);

				});			
				
			});
			
			function get_todo_list(school_id)
			{
				var url = "ajax_get_todo_list.php?admin_auth=" + admin_auth + "&school_id=" + school_id + "&subject_id=" + subject_id;
					
				var http = getHTTPObject();
				http.open("GET", url, true);
								
				http.onreadystatechange = function() {
														
					if (http.readyState == 4 && http.status == 200) 
					{
						$("div[name=report_div]").html(http.responseText);
					} 
											
				}
											
				http.send(null);
					
			}
				
			function getHTTPObject() {
				var xmlhttp;

				if (window.XMLHttpRequest) 
				{
					xmlhttp = new XMLHttpRequest();
				}
				else if (window.ActiveXObject)
				{ 
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
								
					if (!xmlhttp) 
					{
						xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
					}
				}
								
				return xmlhttp; 
			}
		</script>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
			
			<DIV class="body">
			
				<H1><?=T_('Campaign - To Do List')?></H1>
				
				<? echo "<input type='hidden' name='COUNT' value='" . count($admin->schools) . "'>\n"; ?>
				
				<? if (count($admin->schools) > 1 || $admin->auth == "super") : ?>
				<form name="admin_subject_todo_list" id="admin_subject_todo_list" action="admin_subject_todo_list.php" method="post" accept-charset="UTF-8">
					<div class="infobox2 marking_list clearfix">
					
						<div class="school_list select_box">
							<a class="prev button">
								<span class="icon"></span>
								<span class="label"><?=T_('Previous School')?></span>
							</a>
						
							<SELECT name="school_id" id="school_id">
								<OPTION value="0">Select a school</OPTION>
								<? foreach ($schools as $school) : ?>
								<OPTION value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
								<? endforeach; ?>
							</SELECT>
							
							<a class="next button">
								<span class="icon"></span>
								<span class="label"><?=T_('Next School')?></span>
							</a>						
						</div>
					
					</div>
					
				</form>
				<? endif; ?>
			
				<DIV class="ui_body">
				
					<DIV class="ui_menu">
					</DIV>
				
					<DIV id="report_content" name="report_content" class="content">
						
					</DIV>
					
				</DIV>
				
			</DIV>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
