<?
$admin_auth = array('school'); 
require('header.php');

include("admin_schools.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<TITLE>Mision Marks Update - Tzivos Hashem Management System</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<? include('admin_header.php'); ?>
		
		<DIV>
		
			<DIV class="body">
			
				<H1>Mision Marks Update</H1>
				
				<script>
					var school_id = <?=$admin->school_id;?>;
					
					$(document).ready(function() {					
						var url = "ajax_get_users.php?school_id=" + school_id;
						get_users(url);
					});
				
					$(function() 
					{
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
							var url = "ajax_get_users.php?school_id=" + $(this).val();
							get_users(url);						
						});
						
						$("#order_by_first").live('click', function() {
							var url = "ajax_get_users.php?school_id=" + $("#school_id").val() + "&order_by=first";
							get_users(url);
						});
						
						$("#order_by_last").live('click', function() {
							var url = "ajax_get_users.php?school_id=" + $("#school_id").val() + "&order_by=last";
							get_users(url);
						});

						$("#order_by_class").live('click', function() {
							var url = "ajax_get_users.php?school_id=" + $("#school_id").val() + "&order_by=class_grade";
							get_users(url);
						});

						$("#mission_updater").live('click', function() {
							var url = "ajax_mission_marks_updater.php?user_id=" + $(this).attr("data");
							$.ajax({
								url: url,
								success: function(data) 
								{
									alert("UPDATED");
								}
							});						
						});
					});
					
					function get_users(url)
					{
						$.ajax({
							url: url,
							success: function(data) 
							{
								$("#users_div").html(data);											
							}
						});						
					}
				</script>
				
				<? if (count($admin->schools) > 1) : ?>
				<DIV class="infobox2 marking_list clearfix">				
					<? include("admin_school_select.php"); ?>					
				</DIV>
				<? endif; ?>
				
				<DIV id="users_div">
				</DIV>
				
			</DIV>
			
		</DIV>
		
	</BODY>
	
</HTML>
