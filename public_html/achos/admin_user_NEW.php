<? 
$admin_auth = array('school'); 
require('header.php');
require_once('file_save.php');
require_once('calendar.php');

$ui_type = 'school';
require_once('admin_ui.php');

$selects = array("school_id", "class_id");
include("admin_schools.php");

$get_school_id = 0;
if (isset($_GET['school_id']))
	$get_school_id = $_GET['school_id'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE>Soldiers - Tzivos Hashem Management System</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<script>
			function submit_form(user_code)
			{
				document.kiosk_form.elements["user_code"].value = "3" + user_code;
				document.kiosk_form.submit();
			}
		</script>													
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<script>
			<? if ($get_school_id > 0) : ?>
			var school_id = <?=$get_school_id;?>;			
			<? else : ?>
			var school_id = <?=$admin->school_id;?>;
			<? endif; ?>
			
			<? include('admin_schools_select.js'); ?>
			
			$(function()
			{
				$(".submit").click(function () 
				{
					var url = "ajax_get_school_users.php?school_id=" + $("#school_id").val() + "&class_id=" + $(".class_list").find("ul").find("a.hiLite").attr("data");
					url = build_url(url);
					$.ajax({url: url, success: function(data) { $("#users_div").html(data);	} });											
				});
				
				$("#remove_user").live("click", function()
				{
					var tr = $(this).parent().parent();
					var url = "ajax_remove_user.php?user_id=" + $(this).attr("data");
					$.ajax({url: url, success: function(removed) { if (removed == "1") { $(tr).remove(); } } });
				});

				$("#user_edit").live("click", function()
				{
					window.location = "http://mashpia.com/admin_user_edit.php?user_id=" + $(this).attr("data") + "&school_id=" + $("#school_id").val();
				});
				
				$("#order_by_last").live("click", function()
				{
					var url = "ajax_get_school_users.php?school_id=" + $("#school_id").val() + "&class_id=" + $(".class_list").find("ul").find("a.hiLite").attr("data");
					url = url + build_url(url) + "&order_by=last";
					$.ajax({url: url, success: function(data) { $("#users_div").html(data);	} });
				});
				
				$("#order_by_first").live("click", function()
				{
					var url = "ajax_get_school_users.php?school_id=" + $("#school_id").val() + "&class_id=" + $(".class_list").find("ul").find("a.hiLite").attr("data");
					url = url + build_url(url) + "&order_by=first";
					$.ajax({url: url, success: function(data) { $("#users_div").html(data);	} });
				});
				
				$("#order_by_platoon").live("click", function()
				{
					var url = "ajax_get_school_users.php?school_id=" + $("#school_id").val() + "&class_id=" + $(".class_list").find("ul").find("a.hiLite").attr("data");
					url = url + build_url(url) + "&order_by=class_grade, class_sub, last, first";
					$.ajax({url: url, success: function(data) { $("#users_div").html(data);	} });
				});				
			});
			
			function build_url(url)
			{
				if ($("input[name=search_user_serial]").val() != "")
					url = url + "&user_serial=" + $("input[name=search_user_serial]").val();
					
				if ($("input[name=search_first]").val() != "")
					url = url + "&first=" + $("input[name=search_first]").val();
					
				if ($("input[name=search_last]").val() != "")
					url = url + "&last=" + $("input[name=search_last]").val();
					
				if ( $("input[name=search_user_registered]").is(':checked'))
					url = url + "&user_registered=1";
				
				return url;
			}

			function get_data()
			{
				if (page_loaded == false)
					var url = "ajax_get_school_users.php?school_id=" + school_id;					
				else
					var url = "ajax_get_school_users.php?school_id=" + $("#school_id").val() + "&class_id=" + $(".class_list").find("ul").find("a.hiLite").attr("data");
								
				$.ajax({url: url, success: function(data) { $("#users_div").html(data);	} });
			}
		</script>		
													
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">				
					<? if (!empty($message)):?>
						<H2><?=$message?></H2>
					<?endif;?>				
				</DIV>
				
				<H1><?=T_('Base Management')?></H1>
				

				<DIV class="infobox2 marking_list clearfix">
					<? if (count($admin->schools) > 1) : ?>
					<!-- ***** SCHOOL ***** -->
					<DIV class="school_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
						
						<input type='hidden' name='ADMIN SCHOOL ID' value='<?=$admin->school_id;?>'>
						<input type='hidden' name='GET SCHOOL ID' value='<?=$get_school_id;?>'>
						
						<SELECT name="school_id" id="school_id">
							<OPTION value="0">Select a School</OPTION>
							<? foreach ($admin->schools as $school) : ?>
								<? if ( ($get_school_id == $school->school_id) || ($get_school_id == 0 && $admin->school_id == $school->school_id) ) : ?>
								<OPTION SELECTED value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
								<? else : ?>
								<OPTION value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
								<? endif; ?>
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
							<OPTION value="0">All Platoons</OPTION>
						</SELECT>
						
						<a class="next button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>							
					</DIV>
					<!-- ***** CLASS ***** -->

				</DIV>
				
				<DIV class="infobox">
					Click a Soldier's name to view and edit profile details and ID photo.
				</DIV>
				
				<DIV class="infobox2">
				
					<H3>Search by</H3>
					
					<BR>
					
					<P>
						<LABEL style="white-space: nowrap;">
							Serial #
							<INPUT type="text" name="search_user_serial" value="">
						</LABEL>
											
						<LABEL style="white-space: nowrap;">
							First name:
							<INPUT type="text" name="search_first" value="">
						</LABEL>
						
						<LABEL style="white-space: nowrap;">
							Last name: 
							<INPUT type="text" name="search_last" value="">
						</LABEL>
								
						<BR />
						<BR />

						<LABEL>
							Show only registered users: 
							<INPUT type="checkbox" name="search_user_registered" value="1">
						</LABEL>
						
						<BR />
						<BR />
						
						<INPUT class="submit" type="button" value="Go">
					</P>
					
				</DIV>				
				
				<DIV id="users_div">
				</DIV>
				
			</DIV>
		
		</DIV>
		
		<? include('admin_footer.php'); ?>
			
	</BODY>
	
</HTML>
