<?
$admin_auth = array('school'); 
require('header.php');

$selects = array("school_id", "class_id");
include("admin_schools.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<TITLE>SCHOOLS SELECT</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<? include('admin_header.php'); ?>
		
		<H1>Schools Selects</H1>
		
		<script>
			<? include('admin_schools_select.js'); ?>
			
			function get_report()
			{
				var school_id = $("#school_id").val();
				
				<? if (in_array("class_id", $selects)) : ?>
				var class_id = $(".class_list").find("ul").find("a.hiLite").attr("data");
				<? else : ?>
				var class_id = 0;				
				<? endif; ?>
				
				<? if (in_array("user_id", $selects)) : ?>
				var user_id = $(".user_list").find("ul").find("a.hiLite").attr("data");
				<? else : ?>
				var user_id = 0;
				<? endif; ?>
				
				alert("SCHOOL:" + school_id + "\nCLASS:" + class_id + "\nUSER:" + user_id);
			}
		</script>
		
		<DIV class="infobox2 marking_list clearfix">
			<? include("admin_schools_select.php"); ?>
		</DIV>
		
	</BODY>
	
</HTML>

