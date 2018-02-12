<?php
include ("db.php");
include ("classes/mission_sheet.php");
include ("classes/group.php");
include ("classes/user.php");

$group_type_name = $_GET['group_type_name'];
$group_type_id = $_GET['group_type_id'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

$mission_sheets = array();

$date_array = split("/", $start_date); 
$start_date = gregoriantojd($date_array[0], $date_array[1], $date_array[2]);

$mission_sheet = new mission_sheet();
$mission_sheet->new_mission_sheet($start_date);
$mission_sheet->get_groups($group_type_id);
$mission_sheet->get_missions($group_type_id);
array_push($mission_sheets, $mission_sheet);

$date_array = split("/", $end_date);
$end_date = gregoriantojd($date_array[0], $date_array[1], $date_array[2]);

for ($dno = 1; $dno < ($end_date - $start_date + 1); $dno++) {
	$mission_sheet = new mission_sheet();
	$mission_sheet->new_mission_sheet($start_date + $dno);
	$mission_sheet->get_groups($group_type_id);
	$mission_sheet->get_missions($group_type_id);
	array_push($mission_sheets, $mission_sheet);
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<link rel="alternate" media="print" href="index.php">
		<link href="../styles/reset.css" rel="stylesheet" type="text/css" />
		<link href="../styles/styles.css" rel="stylesheet" type="text/css" />
		<link href="../styles/print.css" rel="stylesheet" type="text/css" media="print" />
	</head>

	<body class="print" onload="window.print();">

		<? for ($msno = 0; $msno < count($mission_sheets); $msno++) : ?>
			<? $mission_sheet = $mission_sheets[$msno]; ?>
			
			<h1><?=$mission_sheet->date_title;?></h1>
			
			<div id="col_content" class="col_content">
							
			<? for ($gno = 0; $gno < count($mission_sheet->groups); $gno++) : ?>
				<? $group = $mission_sheet->groups[$gno]; ?>							   					
				
				<? for ($mno = 0; $mno < count($mission_sheet->missions); $mno++) : ?>
					<? $mission = $mission_sheet->missions[$mno];  ?>
					
												
						<div class="module" id="module-info">
							
							<!-- <div class="module_conntent"> -->
							<div class="module_conntent">
													
								<!-- <div class="marking"> -->
								<div class="marking">

									<!-- <div class="col names"> -->
									<div id="col_names" class="col names">
									
										<div class="mission_name">
											<div class="cell"><?=$group['group_name'];?></div>
										</div>
										
										<div class="row task_names">
											<div class="cell"></div>
										</div>
										
										<? for ($mbno = 0; $mbno < count($group['members']); $mbno++) : ?>
											<? $member = $group['members'][$mbno]; ?>
											<div class="cell"><?=$member->first . " " . $member->last;?></div>
										<? endfor; ?>
										
									</div> 
									<!-- <div class="col names"> -->
									
									
									<div class="mission_window">
											
										<div class="mission_container">
												
											<div class="missions">
													
												<div id="mission_one" class="col mission">
												
													<div class="row mission_name">										
														<div class="cell"><?=$mission['mission_name'];?></div>
													</div>
													
													<div class="row task_names">
														<? for ($tno = 0; $tno < count($mission['tasks']); $tno++) : ?>
															<? $task = $mission['tasks'][$tno]; ?>															
															<div class="cell">
																<span><?=$task['task_name'];?></span>
															</div>
														<? endfor; ?>
													</div>
													
													<div id="mission_one_tasks">
														<? for ($mbno = 0; $mbno < count($group['members']); $mbno++) : ?>
															<? $member = $group['members'][$mbno]; ?>
															<div class="row">
																<? for ($tno = 0; $tno < count($mission['tasks']); $tno++) : ?>
																<div class="cell checkbox empty">
																	<span>
																		<input type="checkbox">
																	</span>
																</div>
																<? endfor; ?>
															</div>
														<? endfor; ?>
													</div>
													
												</div> 
												<!-- <div class="col mission" id="mission_one"> -->	
																									
											</div> <!-- <div class="missions"> -->
													
										</div> <!-- <div class="mission_container"> -->
												
									</div> <!-- <div class="mission_window"> -->
											
									<div class="clear"></div>
											
								</div> 
								<!-- <div class="marking"> -->
													
							</div> 
							<!-- <div class="module_conntent"> -->
							
						</div> 
						<!-- <div class="module"> -->
						
					
					<input type="hidden" name="MISSION NO" value="<?=$mno;?>">
					<? //if ($mno > 0 && $mno % 4 == 0) echo "<HR>"; ?>
					
				<? endfor; ?>
				
				<? //if ($gno > 0 && $gno % 4 == 0) echo "<HR>"; ?>
				
			<? endfor; ?>
			</div>
			<!-- <div id="col_content" class="col_content"> -->
			
			<!--<hr>-->
			
		<? endfor; ?>	
	</body>
	
</html>	