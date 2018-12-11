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
		
		function set_date() {
			var currentTime = new Date();
			var month = currentTime.getMonth() + 1;
			var day = currentTime.getDate();
			var year = currentTime.getFullYear();
			document.cms_presentation.adate.value=(month + "/" + day + "/" + year);
		}

		function get_selects(select_name, selected_index, started) {
			var gt_select = "";

			for (cntr1 = 0; cntr1 < group_types.length; cntr1++) {
				group_type = group_types[cntr1];		
				gt_select = gt_select + "\t<option value='" + group_type.group_type_id + "'>" + group_type.group_type_name + "</option>\n";											
			}
				
			if (select_name == "group_type_id") 
				$(document.getElementById("group_type_id")).html(gt_select);
		}		
				
		$(function() {
			$('form a.submit').click(function(e){
				e.preventDefault();
				var selected_index = document.getElementById("group_type_id").selectedIndex;
				var group_type_name = document.getElementById("group_type_id").options[selected_index].text;
				document.getElementById("group_type_name").value = group_type_name;
				var group = '';
				if ($('#group_task_type option:selected').val() == 1) { group = "group_" };
				var url = 'content.php?output=' + group + 'marking&' + $('#points_form').serialize();
				$(this).attr('href',url);
				slideForward(this);
			});
			
			$('.date').dateinput({format:'m/d/yyyy'});
		});
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
				<input type="hidden" name="group_type_name" id="group_type_name" value="">
				<input type="hidden" name="division_no" id="division_no" value="0">
				
				<div class="module lists forms" id="lists-group-staff">
					
					<div class="module_content">
					
						<ul>
							<li>
								<span class="icon"></span>
								<span class="title">Show missions for</span>
									
                                    <span>
									
										<select class="select" id="group_task_type" name="group_task_type">
											<option value="0">Individuals</option>
											<option value="1">Groups</option>
										</select>
                                    </span>									
							</li>
							<li>
								<span class="icon">
								</span>
								
								<span class="title">
									Show missions for
								</span>
									
								<span>									
									<select class="select" id="group_type_id" name="group_type_id">
									</select>
								</span>									
							</li>
							
							<li>
								<span class="icon"></span>
								<span class="title">Date</span>
								<span class="input">
									<input type="text" name="task_date" id="adate" value="" class="date" />
								</span>
							</li>
							
							<li>
								<span class="icon"></span>
								<div class="title"><input type="radio" name="display" value="0" checked="checked" />Unmarked missions only</div>
								<div class="title"><input type="radio" name="display" value="1" />Marked missions only</div>
								<div class="title"><input type="radio" name="display" value="2" />All Missions</div>
							</li>
							<li>
								<a class="button submit" href="#">Show me the missions</a>
								<!--<a class="button" href="content.php?output=marking&task_date=2455381&group_type_id=237&group_no=1&mission_one=0&mission_two=1">Show me the missions</a>-->
							</li>
							
						</ul>

					</div> <!-- <div class="module_content"> -->
						
				</div> <!-- <div class="module lists forms" id="lists-group-staff"> -->
					
			</form>
					
		</div> <!-- <div class="col_content"> -->
				
	</div> <!-- <div class="slider"> -->
