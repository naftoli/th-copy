<? 
$admin_auth = array('school'); 
require('header.php');

//$admin_id = $_POST['admin_id'];
$admin_id = 2;
include('objects/admin.php');
include('objects/school.php');
$admin = new admin(NULL, $admin_id);
$admin->get_schools();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE>School Students</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>		
	</HEAD>

	<body>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
					
			<H1>
				Students
			</H1>
				
			<!-- JQUERY code must be placed here in order for it to work -->
			<SCRIPT>
				$(function() {
					$('.marking_list div a.next').click(function(){
						$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
					});
					
					$('.marking_list div a.prev').click(function(){
						$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
					});
				
					$(".school_list select").sSelect().change(function () {
						get_report("ajax_school_students.php?school_id=" + $(this).val());
					})

					$(".user_tag").live('click', function() {
						document.user_edit_form.elements["user_id"].value = $(this).attr("data");
						document.user_edit_form.elements["school_id"].value = $("#school_select_id").val();
						document.user_edit_form.submit();
					});
					
					get_report("ajax_school_students.php?school_id=" + $("#school_select_id").val());					
				});
				
				function get_report(url) {
					$.ajax({ url: url, success: function(data) { $("#report_div").html(data); } });
				}
			</SCRIPT>		
				
			<DIV class="infobox2 marking_list clearfix">
				<DIV class="school_list select_box">
					<a class="prev button">
						<span class="icon"></span>
						<span class="label"></span>
					</a>
					
					<SELECT id="school_select_id" name="school_select_id">
						<? foreach ($admin->schools as $school) : ?>
						<OPTION value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
						<? endforeach; ?>
					</SELECT>
						
					<a class="next button">
						<span class="icon"></span>
						<span class="label"></span>
					</a>						
				</DIV>			
			</DIV>
			
			<DIV class="infobox2 marking_list clearfix">
				<CENTER>
					<DIV id="report_div">
					</DIV>
				</CENTER>
			</DIV>
			
			<FORM name="user_edit_form" method="post" action="admin_user.php">
				<input type="hidden" name="action" value="edit">
				<input type="hidden" name="user_id" value="">
				<input type="hidden" name="school_id" value="">
			</FORM>
			
		</DIV> 
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
