<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();
?>
	<script>
		var group_types = "";
		
		$(document).ready(function() {				
			var camp_id = "<?=$camp_id;?>";
			
			function_name = "get_all_group_types_divisions_groups";				
			parameters = [camp_id];
			var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
			$.getJSON(url, function(data) {
				group_types = data;
				get_selects("group_type_id", 0, false);					
			});
			set_date();			
		});
		
		function set_date(){
			var currentTime = new Date();
			var month = currentTime.getMonth() + 1;
			var day = currentTime.getDate();
			var year = currentTime.getFullYear();
			document.cms_presentation.start_date.value = (month + "/" + (day + 1) + "/" + year);
			document.cms_presentation.end_date.value = (month + "/" + (day + 2) + "/" + year);
		}

		function get_selects(select_name, selected_index, started) {
				var gt_select = "";

				// ***** GROUP TYPES ***** //
				for (cntr1 = 0; cntr1 < group_types.length; cntr1++) {
					group_type = group_types[cntr1];		
					gt_select = gt_select + "\t<option value='" + group_type.group_type_id + "'>" + group_type.group_type_name + "</option>\n";											
				}
				// ***** GROUP TYPES ***** //
				
				if (select_name == "group_type_id") {
					$(document.getElementById("group_type_id")).html(gt_select);
				}
		}		
		
		$(function() {
			$('form a.submit').click(function(e){
				e.preventDefault();
			var start_date = escape(document.getElementById("start_date").value);
			var end_date = escape(document.getElementById("end_date").value);
			var selected_index = document.getElementById("group_type_id").selectedIndex;
			var group_type_id = document.getElementById("group_type_id").value;
			var group_type_name = document.getElementById("group_type_id").options[selected_index].text;
			var url = "includes/print_mission_sheets.php?group_type_name=" + group_type_name + "&group_type_id=" + group_type_id + "&start_date=" + start_date + "&end_date=" + end_date;						
				//var url = 'content.php?output=marking&' + $('#points_form').serialize();
				//alert(url);
				$(this).attr('href',url);
				//slideForward(this);
			window.open(url,'_newtab');			
			
			});
			
			$('.date').dateinput({format:'m/d/yyyy'});
		});
		
		function print_mission_sheets() {
			var start_date = escape(document.getElementById("start_date").value);
			var end_date = escape(document.getElementById("end_date").value);
			var selected_index = document.getElementById("group_type_id").selectedIndex;
			var group_type_id = document.getElementById("group_type_id").value;
			var group_type_name = document.getElementById("group_type_id").options[selected_index].text;
			var url = "includes/print_mission_sheets.php?group_type_name=" + group_type_name + "&group_type_id=" + group_type_id + "&start_date=" + start_date + "&end_date=" + end_date;						
			window.open(url,'mywindow','fullscreen=yes, scrollbars=1');			
		}
	</script>
<link href="styles/dateinput.css" rel="stylesheet" type="text/css" />

	<div class="slider">
	
		<div class="col_title">
			<span>Mark Points</span>
		</div>
		
		<div class="col_content">
		
			<h1>Select missions to mark</h1>
			
			<form action="content.php?output=marking" name="cms_presentation" id="points_form">
				<input type="hidden" name="group_no" id="group_no" value="1">
				<input type="hidden" name="mission_no" id="mission_no" value="1">
				
				<div class="module lists forms" id="lists-group-staff">
					
					<div class="module_content">
					
						<ul>
							<li>
								<span class="icon"></span>
								<span class="title">Show missions for</span>
									
                                    <span>
									
										<select class="select" id="group_type_id" name="group_type_id">
										</select>
									
										<!--<select class="select" id="group_type_id" name="group_type_id" onchange="get_selects('group_type_id', this.selectedIndex, true);">
										</select>-->
										
										<!--<select class="select" id="select_division" name="select_division" onchange="get_selects('select_division', this.selectedIndex, true);">
										</select>
										
										<select class="select" id="select_group" name="select_group" onchange="get_selects('select_group', this.selectedIndex, true);">
										</select>-->
                                    </span>									
							</li>
							
							<li>
								<span class="icon"></span>
								<span class="title">Start Date</span>
								<span class="input">
								<input type="text" name="start_date" id="start_date" value="" class="date" />
								</span>
							</li>
							
							<li>
								<span class="icon"></span>
								<span class="title">End Date</span>
								<span class="input">
								<input type="text" name="end_date" id="end_date" value="" class="date" />
								</span>
							</li>
							
							<li>
								<!--<a href="#" onclick="new_window('http://www.mashpia.com/CampMotivationalSystem/dev/presentation/includes/print_mission_sheets.php');" class="overlay2 button">
									PRINT MISSIONS
								</a>-->
								<a href="#" class="button submit">
									PRINT MISSIONS
								</a>								
							</li>
							
						</ul>

					</div> <!-- <div class="module_content"> -->
						
				</div> <!-- <div class="module lists forms" id="lists-group-staff"> -->
					
			</form>
					
		</div> <!-- <div class="col_content"> -->
				
	</div> <!-- <div class="slider"> -->
