<? 
session_start();
$admin_id = $_SESSION['admin_id'];

$admin_auth = array('school'); 

require('header.php'); 
require_once('admin_ui.php');

$ui_type = 'school';

check_id_access();

$schools = array();
include('objects/admin.php');
include('objects/school.php');
$admin = new admin(NULL, $admin_id);
$admin->get_schools();

include('classes/school_class.php');
$school_id = $admin->schools[0]->school_id;
$classes = array();
$sql = "SELECT * FROM classes WHERE school_id=" . $school_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$class = new school_class($row);
	array_push($classes, $class);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
   
<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_("Users' Vouchers"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript">
			function setCheckboxes(form, nameRegex, value) 
			{
				var pattern = new RegExp(nameRegex);

				for(var i = 0; i < form.elements.length; i++) 
				{
					if(pattern.test(form.elements[i].name) && form.elements[i].type == 'checkbox') 
					{
						form.elements[i].checked = (value == -1 ? !form.elements[i].checked : value);
					}
				}
			}
		</SCRIPT>
		
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV class="body">
		
			<H1>Base Management</H1>
			
			<script type="text/javascript">
				$(function(){
					$('.marking_list div select').each(function() {
					});
					
					$('.marking_list div a.next').click(function(){
						var class_names = $(this).parent().attr("class").split(" ");
					
						if (class_names[0] == "school_list")
							$(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
						else
							next_list_item(class_names[0]);						
					});
					
					$('.marking_list div a.prev').click(function(){
						var class_names = $(this).parent().attr("class").split(" ");
					
						if (class_names[0] == "school_list")
							$(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
						else
							previous_list_item(class_names[0]);
					});
					
					$("#select_anchor_tag").live("mouseover mouseout", function(event) {
						if (event.type == 'mouseover') {
							if ($(this).attr("class") != "hiLite")
								$(this).attr("style", "background-color:#E8EAEF");
						} 
						else {
							if ($(this).attr("class") != "hiLite")
								$(this).attr("style", "background-color:#eee");
						}
					});	
					
					$(".school_list select").sSelect().change(function () {
						var url = "ajax_get_classes.php?school_id=" + $(this).val();
						$.ajax({ url: url, type: "GET", success: function(data) { $(".class_list").find(".newListSelected").find(".selectedTxt").html("All Platoons"); $(".class_list").find(".newListSelected").find("ul").html(data);}});	
					});
					
					$(".class_list select").sSelect().change(function () {
					});
					
					$('.submit').click(function() {
					});
					
				});
				
				function anchor_tag_click(anchor_tag) {
					var class_names = $(anchor_tag).parent().parent().parent().parent().attr("class").split(" ");
					$.each($($(anchor_tag).parent().parent()).find("a"), function() { $(this).attr("class", ""); $(this).attr("style", "background-color:#eee");});
					$(anchor_tag).parent().parent().parent().find(".selectedTxt").html($(anchor_tag).html()).click();												
					$(anchor_tag).css("background", "#D5D8DE").attr("class", "hiLite");
				}
				
				function next_list_item(class_name)
				{
					var anchor_tags = $("." + class_name).find("ul").find("a");
					var no_of_anchor_tags = $("." + class_name).find("ul").find("a").size();				
					
					var hilited_anchor_tag_no = 0;
					var selected_text = "";
					var found = false;
					$.each($("." + class_name).find("ul").find("a"), function(index) { 
						if ($(this).attr("class") == "hiLite" && index < (no_of_anchor_tags - 1))
						{
							hilited_anchor_tag_no = index;
							$(this).attr("class", "");
							$(this).attr("style", "background-color:#eee");
							found = true;
						}
						
						if (found == true && index == (hilited_anchor_tag_no + 1))
						{
							selected_text = $(this).html();
							$(this).attr("class", "hiLite");
							$(this).attr("style", "background-color:#D5D8DE");
						}
					});
					
					if (selected_text != "")
						$("." + class_name).find(".newListSelected").find(".selectedTxt").html(selected_text);
				}
				
				function previous_list_item(class_name)
				{
					var anchor_tags = $("." + class_name).find("ul").find("a");
					var no_of_anchor_tags = $("." + class_name).find("ul").find("a").size();
					
					var selected_text = "";
					
					for (atno = (no_of_anchor_tags - 1); atno > 0; atno--) 
					{
						var anchor_tag = anchor_tags[atno];
							
						if ($(anchor_tag).attr("class") == "hiLite")
						{
							$(anchor_tag).attr("class", "");
							$(anchor_tag).attr("style", "background-color:#eee");
								
							atno--;
							anchor_tag = anchor_tags[atno];
							$(anchor_tag).attr("class", "hiLite");
							$(anchor_tag).attr("style", "background-color:#D5D8DE");
							selected_text = $(anchor_tag).html();
							break;
						}
					}
					
					if (selected_text != "")
						$("." + class_name).find(".newListSelected").find(".selectedTxt").html(selected_text);
				}					
			</script>
			
			
			<div class="infobox2 marking_list clearfix">
			
				<!-- ********** SCHOOL ********** -->
				<div class="school_list select_box">
					<a class="prev button">
						<span class="icon"></span>
						<span class="label">Previous School</span>
					</a>
					
					<select id="school_id" name="school_id" style="display: none;">
						<? foreach ($admin->schools as $school) : ?>
						<OPTION value='<?=$school->school_id;?>'><?=$school->school_name;?></OPTION>
						<? endforeach; ?>
					</select>
					
					<a class="next button">
						<span class="icon"></span>
						<span class="label">Next School</span>
					</a>						
				</div>
				<!-- ********** SCHOOL ********** -->
				
				<!-- ********** CLASS ********** -->
				<div class="class_list select_box">
					<a class="prev button">
						<span class="icon"></span>
						<span class="label">Previous Class</span>
					</a>
					
					<select id="class_id" name="class_id" style="display: none;">
						<OPTION value='0'>All Platoons</OPTION>
						<? foreach ($classes as $class) : ?>
						<OPTION value='<?=$class->class_id;?>'><?=$class->class_grade;?> <?=$class->class_sub;?></OPTION>
						<? endforeach; ?>
					</select>
					
					<a class="next button">
						<span class="icon"></span>
						<span class="label">Next Class</span>
					</a>						
				</div>
				<!-- ********** CLASS ********** -->
				
			</div>			

			
			<div class="infobox">
				<p>Step One: Print a report for each teacher, so that they know how many packs of pictures to give out to each child.</p>
				<p>Step Two: Press select all on the withdraw and cash button.</p>
				<p>Step Three: Press withdraw and cash (at the bottom of the page).</p>
				<p>Step Four: Give the report and pictures to the teacher to give out to the children.</p>
			</div>			
			
			<div class="infobox2">
				<center>
				<p>
					<b>Search by:</b>
				</p>
				</center>
				
				<p>
					<input type="hidden" value="82" name="school_id">
					<label style="white-space: nowrap;">Serial #: <input type="text" value="" name="search_user_serial"></label>
					<label style="white-space: nowrap;">First name: <input type="text" value="" name="search_first"></label>
					<label style="white-space: nowrap;">Last name: <input type="text" value="" name="search_last"></label>
				</p>
				
				<center>
					<p>
						<input type="submit" value="Go" class="submit">
					</p>
				</center>
			</div>
			
			<DIV class="report_div">
			</DIV>
			
		</DIV>	
		
	</BODY>
	
</HTML>
