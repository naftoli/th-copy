<? 
$admin_auth = array('school'); 
require('header.php'); 

$ui_type = 'programs';
require_once('admin_ui.php');
require_once('calendar.php');

include("classes/admin.php");
include("classes/school.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
$admin->get_schools();

if (count($admin->schools) == 1) 
	$admin_school_id = $admin->schools[0]->school_id;
else
	$admin_school_id = 0;

include("classes/subject.php");
$subjects = array();
$sql = "SELECT s.* FROM subjects AS s LEFT JOIN institutions USING (inst_id) WHERE s.subject_type NOT IN ('school_points', 'home_points', 'Tanya') ORDER BY s.subject_name";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$subject = new subject($row);
	array_push($subjects, $subject);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_("Soldier's Ladders/Years"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<script type="text/javascript">
		// Popup window code
		function newPopup(url) {
			popupWindow = window.open(
				url,'popUpWindow','height=400,width=200,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes')
		}
		</script>
	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
	
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">
				
					<? if(!empty($message)) : ?>
						<H2><?=$message?></H2>
					<? endif; ?>
					
				</DIV>
				
				<H1>
					<?=T_('Campaigns')?>
				</H1>
				
				<script type="text/javascript">	
					var school_id = <?=$admin_school_id;?>;
					
					$(document).ready(function() {
						
						
						if (school_id > 0)
						{
												
							var url = "ajax_get_classes.php?school_id=" + school_id;
							$.ajax({
								url: url,
								success: function(data) 
								{
									var newListSelected = $(".class_list").find(".newListSelected");
									$(newListSelected).find("ul").html(data);
									
									var url = "ajax_get_user_tracks.php?school_id=" + school_id;
									$.ajax({
										url: url,
										success: function(data)
										{											
											$("#user_tracks_div").html(data);											
										}
									});
								}
							});
						
						}
					});				
					
					$(function()
					{
					
						//var school_id = <?=$school_id;?>;
						
						$("#track_id").live('change', function() 
						{
							var user_id = $(this).parent().parent().find("td[name=user_id]").attr("data");
							var subject_id = $(this).parent().parent().attr("id");
							var track_id = $(this).val();
							var level = $(this).parent().parent().find("select[name=level]").val();
							
							var url = "ajax_user_track.php?user_id=" + user_id + "&subject_id=" + subject_id + "&track_id=" + track_id + "&level=" + level + "&enrolled=1";
							$.ajax({
								url: url,
								success: function(data) 
								{
									if (data == "0");
										alert("Update not performed");
								}
							});
						});
					
						$("#level").live('change', function() 
						{
							var user_id = $(this).parent().parent().find("td[name=user_id]").attr("data");
							var subject_id = $(this).parent().parent().attr("id");
							var track_id = $(this).parent().parent().find("select[name=track_id]").val();
							var level = $(this).val();
							
							var url = "ajax_user_track.php?user_id=" + user_id + "&subject_id=" + subject_id + "&track_id=" + track_id + "&level=" + level + "&enrolled=1";
							$.ajax({
								url: url,
								success: function(data) 
								{
									if (data == "0");
										alert("Update not performed");
								}
							});

						});
						
						$("#enrolled").live('click', function() 
						{
						
							if ( $(this).attr('checked') )
							{
								$(this).parent().parent().find("select[name=track_id]").attr("disabled", "");
								$(this).parent().parent().find("select[name=level]").attr("disabled", "");
								var enrolled = 1;
							}
							else
							{
								$(this).parent().parent().find("select[name=track_id]").attr("disabled", "disabled");
								$(this).parent().parent().find("select[name=level]").attr("disabled", "disabled");
								var enrolled = 0;
							}
							
							var url = "ajax_user_track.php?user_id=" + $(this).parent().parent().find("td[name=user_id]").attr("data") + "&subject_id=" + $(this).parent().parent().attr("id") + "&track_id=" + $(this).parent().parent().find("select[name=track_id]").val() + "&level=" + $(this).parent().parent().find("select[name=level]").val() + "&enrolled=" + enrolled;
							$.ajax({
								url: url,
								success: function(data) 
								{
									if (data == "0");
										alert("Update not performed");
								}
							});
							
						});					
						
						$("#select_anchor_tag").live("mouseover mouseout", function(event) {
							if (event.type == 'mouseover') 
							{
								if ($(this).attr("class") != "hiLite")
									$(this).attr("style", "background-color:#E8EAEF");
							} 
							else 
							{
								if ($(this).attr("class") != "hiLite")
									$(this).attr("style", "background-color:#eee");
							}
						});	
					
						$('.marking_list div select').each(function() {
							if (!$(this).find('option:selected').next().val()) $(this).siblings('a.next').addClass('disabled');
							if (!$(this).find('option:selected').prev().val()) $(this).siblings('a.prev').addClass('disabled');
						});
						
						$('.marking_list div a.next').click(function(){
							$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
						});
						
						$('.marking_list div a.prev').click(function(){
							$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
						});
									
						$("#school_id").sSelect().change(function () 
						{
							var school_id = $(this).val();
							
							var url = "ajax_get_classes.php?school_id=" + school_id;
							$.ajax({
								url: url,
								success: function(data) 
								{
									var newListSelected = $(".class_list").find(".newListSelected");
									$(newListSelected).find("ul").html(data);
									
									var url = "ajax_get_user_tracks.php?school_id=" + school_id;
									$.ajax({
										url: url,
										success: function(data)
										{											
											$("#user_tracks_div").html(data);
										}
									});
								}
							});
						});
						
						$("#class_id").sSelect().change(function () {
						});

						$("#subject_id").sSelect().change(function () {
						});
					});
					
					function  anchor_tag_click(anchor_tag, field)
					{
						var parent_1 =  $(anchor_tag).parent();
						var parent_2 = $(parent_1).parent();
						var anchor_tags = $(parent_2).find("a");
								
						$.each(anchor_tags, function() { 
							$(this).attr("class", "");
							$(this).attr("style", "background-color:#eee");
						});
								
						var parent_3 = $(parent_2).parent();
						var selected_text = $(parent_3).find(".selectedTxt");
								
						$(selected_text).html($(anchor_tag).html());
								
						$(anchor_tag).css("background", "#D5D8DE");
						$(anchor_tag).attr("class", "hiLite");
								
						$(selected_text).click();	

						alert("field:" + field);
						
						//alert("anchor_tag_click");
						
						//get_user_tracks();
						//var url = "ajax_get_user_tracks.php?school_id=" + school_id;						
					}
					
					function get_user_tracks()
					{	
						alert("school_id:" + school_id);
						
						if (school_id > 0)
							var url = "ajax_get_user_tracks.php?school_id=" + school_id;
						else
							var url = "ajax_get_user_tracks.php?school_id=" + $("#school_id").val();
						
						//url = url + "&class_id=" + $("#class_id").val();
						
						alert(url);
					}
				</script>
				
				
				
				<DIV class="infobox2 marking_list clearfix">
					
					<? if (count($admin->schools) > 1) : ?>
					<!-- ***** SCHOOL ***** -->
					<DIV class="school_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
						
						<SELECT name="school_id" id="school_id">
							<OPTION value="0">Select a School</OPTION>
							<? foreach ($admin->schools as $school) : ?>
							<OPTION value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
							<? endforeach; ?>
						</SELECT>
							
						<a class="next button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>							
					</DIV>
					<!-- ***** SCHOOL ***** -->
					<? endif; ?>
					
					<!-- ***** CLASS ***** -->
					<DIV class="class_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
					
						<SELECT name="class_id" id="class_id">
							<OPTION value="0">All Classes</OPTION>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>							
					</DIV>
					<!-- ***** CLASS ***** -->
					
					<!-- ***** SUBJECT ***** -->
					<DIV class="subject_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
					
						<SELECT name="subject_id" id="subject_id">	
							<OPTION value="0">All Campaigns</OPTION>
							<? foreach ($subjects as $subject) : ?>
							<OPTION value="<?=$subject->subject_id;?>"><?=$subject->subject_name;?></OPTION>
							<? endforeach; ?>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>							
					</DIV>
					<!-- ***** SUBJECT ***** -->

				</DIV>	
				
				
				<DIV class="infobox">
				
					<P>
						<?=T_('You need to set the Ladder and Year for each soldier for them to see reports.')?>
					</P>
					
					<P>
						<?=T_('You should review the ladder charts with the soldier to decide the ladder.')?>
					</P>
					
					<P>
						<?=T_('Year is the age the class will be turning at the end of the year.')?>
					</P>
					
					<P>
						Click <a href="JavaScript:newPopup('http://www.mashpia.com/chart.html');">here</a> to use a chart to help you 
						decide what year to put your child on to.
					</P>
					
				</DIV>
				
				<BR style="clear: both;">

				<DIV name="user_tracks_div" id="user_tracks_div">
				</DIV>
				
			</DIV>
			
		</DIV>
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
