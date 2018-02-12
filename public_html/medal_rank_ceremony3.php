<?php
/***************** CONFIGURATION **********************/
ini_set('max_execution_time', 300);
ini_set('display_errors', TRUE);

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require('header.php'); 

/***************** CREATE MEDAL REPORT **********************/
require_once 'class.medalReport.php';
$m = new MedalReport();

/***************** SET DATES **********************/
$previous = false;
if ( isset($_GET['go']) && $_GET['go'] == 'back' ) {
    $previous = true; 
    $m->setPreviousDates();
}

/***************** GENERATE DATES **********************/
$dates = $m->getReportDates();
$heDatesMedals = $m->getHeReportDates();

/***************** RANK REPORT **********************/
require_once 'class.rankReport.php';
$rr = new RankReport();
$rr->setRankNames();
$rankNames = $rr->getRankNames();
$heDatesRanks = $rr->getHeReportDates();

/***************** getRank($user_id) function **********************/
function getRank($user_id) {
    // old way, get rank based on the users name
	//$name = explode(" ", $user); // users may have more then one first name
	//
	//if(count($name) > 2){
	//	$first = join(" ", array_slice ($name, 0, -1));
	//} else {
	//	$first = $name[0];
	//}
	//$last = end($name);
	// new way. get the rank based on the users user_id
	$sql = "select rank_name  
			from ranks r 
			join rank_marks rm 
			using (rank_ord) 
			where rm.user_id = " . $user_id . " 
			order by rm.rank_ord desc 
			limit 0,1";
	// echo "<input type='hidden' value='$sql'/>";
	$result = mysql_query( $sql ) or die( $sql . "<br />" . mysql_error() ); // display an error if MySQL gives us one
	$row = mysql_fetch_assoc( $result );
	return $row['rank_name']; // return the rank to the user
}
// arrays for the various types of medals and how many missions are needed for each in total
$weeks = array( // weekly missions
    'White'     => 15,
    'Red'       => 35,
    'Orange'    => 60,
    'Yellow'    => 90,
    'Green'     => 125,
    'Blue'      => 165,
    'Purple'    => 210,
    'Brown'     => 260,
    'Gray'      => 315,
    'Black'     => 375
);
$months = array( // monthly missions
    'White'     => 5,
    'Red'       => 11,
    'Orange'    => 18,
    'Yellow'    => 26,
    'Green'     => 35,
    'Blue'      => 45,
    'Purple'    => 56,
    'Brown'     => 68,
    'Gray'      => 91,
    'Black'     => 105
);
$special = array( // rare missions
    'White'     => 10,
    'Red'       => 25,
    'Orange'    => 55,
    'Yellow'    => 100,
    'Green'     => 160,
    'Blue'      => 235,
    'Purple'    => 315,
    'Brown'     => 405,
    'Gray'      => 495,
    'Black'     => 585
);
/***************** RENDER PAGE **********************/?>
<!DOCTYPE html>
<HTML DIR="<?=$dir?>">
    <HEAD>
        <TITLE>Medals Ranks Ceremony</TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <style type="text/css">
            @media screen {
                .no-print {display: block;}
                .print-only {display: none;}
            }
            @media print {
                .no-print {display: none;}
                .print-only {display: block;}
                label.fancy-check-container input:checked + span.fancy-check:before {content: "\f096";}
                label.fancy-check-container span.fancy-check:before {content: "\f00d";}
            }
            th, td {
                padding: 3px 10px;
                vertical-align: top;
            }
			tr:not(:first-child) {border-top: dashed 1px grey;}
            .page-break { page-break-after: always;}
            #main {font-size: 14px;}
            .medals {margin-left: 30px;}
        </style>     
    </HEAD>   
    
    <BODY>
        <?php include('admin_header.php'); 
        $super = false; $schools = array();
        //if it's a super user, loop through all schools, otherwise show school associated with account
        if ( $admin->auth == 'super' ) {
            $super = true;
        }
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        // remove a few schools (the testing ones)
        $toRemove = array(269,61,66,110,112,180,82);
        foreach ($schools as $id => $school) {
            if (in_array($id, $toRemove)) unset($schools[$id]);
        }
        ?>
        <div class='no-print'>
            <h1>Medals Ranks Ceremony</h1>
            <?if ( $previous ) { // if we are in previous mode. add a link to current mode?>
                Click <a href='medal_rank_ceremony3.php'>here</a> to show next report dates.<br /><br />
            <?} else { // if we are in current mode. add a link to previous mode?>
                Click <a href='medal_rank_ceremony3.php?go=back'>here</a> to show previous report dates.<br /><br />
            <?}?>
			<p>Click <a href="https://vimeo.com/86615168">here</a> to show the Chayolim the Medals Ceremony Video before handing out their hard earned Medals.</p>
            <div align='center'>
                <input type='button' name='print' value='Print' onclick="window.print();" />
            </div>
        </div>
        <div id='main'>
            <?//for super setup totals arrays
            $grandTotal = 0;
            $grandTotalByMedal = array();
            foreach ( $schools as $school_id => $school_name ) {				
                //set up medals and ranks array
                $m->setSchoolId( $school_id );
                $rr->setSchoolId( $school_id );
                // set and get the medal details
                $m->setMedalDetails();
                $details = $m->getMedalDetails();
                // set and get the medal summary
                $medal_info = $m->getMedalsInfo();
                // set and get the medal summary
                $m->setMedalSummary();
                $summary = $m->getMedalSummary();
                // set and get the medal totals
                $totals = $m->getMedalTotals();
                $medalsTotal = $m->getMedalsTotal();
                // $rr is a rankReport object
                $rr->setRanks('byRank');
                $ranks = $rr->getRanks();
                // get a copy of the ranks by grade
				$rr->setRanks('byGradeRank'); // set by grade
				$ranksByGrade = $rr->getRanks();
				//echo "<pre>"; print_r($ranksByGrade); echo "</pre>";
                // get the user info from the medal and rank report
                $userInfo = $m->getUserInfo();
                $userRankInfo = $rr->getUserInfo();
				// if there are medal details (see line 162)
				if (count($details)) {
					foreach ( $details as $school => $line ) { // for each school
						if ( $school != $school_name ) continue; // make sure it is the current school....
						
						foreach ( $line as $teacher => $class ) { // get the teacher and the class from the school
							foreach ( $class as $grade => $info ) { // split up the class as grade and info?>
								<h2><?=$school_name?> - Grade: <?=$grade?> <?=$teacher?></h2><br />
                                <p style='line-height: 1.4'><strong>Dear Commander <?=$teacher?> </strong>, the following chayolim in your platoon have worked hard doing their
									missions between <?=$heDatesMedals['start_he'] . " and " . $heDatesMedals['end_he']?>.<br />          
                                    <i class="print-only">If a medal is missing, please check it off and give the paper back to your base commander. Known missing medals are marked with an <i class="fa fa-times" aria-hidden="true"></i></i>
                                    <i class="no-print">If a medal is missing, please uncheck it so that we are aware that it did not arrive.<br/>If a medal is unchecked and you did recive it please check it so that we do not send you duplicates. Thanks!</i>
								</p>
								
								<h2>Medals Earned</h2>
								<table>
									<tr>
										<th>Soldier</th>
										<th>Medals</th>
									</tr>
								<? // generate the rest of the table for each class
								foreach ( $info as $user_id => $medals ) { // split the class info into user_id's and medals ?>
									<tr><td><?=getRank($user_id) . " " . $userInfo[$user_id]?></td><td><?// show the users rank and info
									foreach ( $medals as $subject => $more ) { // for each medal as it's subject and details....
										foreach ($more as $medal) { // for each medal.... ?>
                                            <label class="fancy-check-container">
												<? $medal_details = $medal_info[$user_id]['shipped_specific'][$subject][$medal]; ?>
                                                <input type='hidden' class='missingInfo' value="<?=$school_id . ":" . $user_id . ":" . $subject . ":" . $medal?>" />
												<input type='hidden' class='missingInfoNew' value="<?=$medal_details['medal_ord'] . ":" . $user_id . ":" . $medal_details['date_awarded'] . ":" . $medal_details['subject_id']?>" />
                                                <input type='checkbox' class='missingMedal'
													<?=$medal_details['shipped'] ? "checked" : "";?>/>
                                                <span class="fancy-check"></span>
												<input type="hidden" value="<?print_r($medal_details)?>"/>
                                            </label>
                                            <?=$subject . "-" . $medal?>
                                            <?if ($subject == 'יומי דפגרא') {
                                                echo "(for completing ".$special[$medal]." special days of missions)<br />";
                                            } else if (in_array($subject, array('WWTC','מבצעים'))) {
                                                echo "(for completing ".$months[$medal]." months of missions)<br />";
                                            } else {
                                                echo "(for completing ".$weeks[$medal]." weeks of missions)<br />";
                                            }
										} // end of foreach
									} // end of foreach subject?>
									</td></tr>
								<?} // end of for each user?>
								</table>
								<br/><br />
								
								<h2>Soldiers that went up in Rank</h2>
								<?if ( isset($ranksByGrade[$school][$teacher][$grade]) ) {?>
									<table><tr><th>Rank</th><th>Soldier</th></tr>
									<?foreach ( $ranksByGrade[$school][$teacher][$grade] as $rank => $users ) {
										$i = 0;
										foreach ( $users as $user ) {
											echo "<tr><td>";
											if (!$i++) echo $rank;
											echo "</td><td>" . $userRankInfo[$user] . "</td></tr>";
										}// end foreach user
									} // end foreach rank?>
									</table><br /><br />
								<?} // end of if ranksByGrade is set?>
								<div class='page-break'></div>
						  <?} // end of class
						} // end of for each teacher
					} // end of for each school
					// summaries...
					foreach ( $summary as $school => $medals ) {
						if ( $school != $school_name ) continue;   
						$grandTotal += $medalsTotal[$school];
						echo "<h2>Summary</h2>";
						echo "Total of " . $medalsTotal[$school] . " medals earned in " . $school . " from " . $heDatesMedals['start_he'] . " until " . $heDatesMedals['end_he'] . ". <br />";
						echo "<br />";
						foreach ( $medals as $subject => $info ) {
							echo "<div class='students'>" . $subject . " - " . $totals[$school][$subject] . "</div>";
							echo "<div class='medals'>";
							foreach ( $info as $medal => $total ) {
								echo $medal . "-" . $total . "<br />";
								if ( !isset( $grandTotalByMedal[$subject][$medal] ) ) 
									$grandTotalByMedal[$subject][$medal] = $total;
								else 
									$grandTotalByMedal[$subject][$medal] += $total;
							}
							echo "</div>";
						}
					}
					echo "<br /><br />";
					echo "<div class='page-break'></div>";
				}
				else echo "No Medals earned in this school.<br /><br />";
				
				foreach ( $ranks as $school => $line ) {
					if ( $school != $school_name ) continue;
					echo "Ranks earned in " . $school . " from " . $heDatesRanks['start_he'] . " until " . $heDatesRanks['end_he'] . ". <br />"; 
					?>
					<br />
					Before you give out the Rank books, please tell the chayolim we are now going to honor the children who have gone up in rank:<br />
					<?
					foreach ( $line as $rank => $info ) {
						foreach ( $rankNames as $rankName => $needed ) {
							if ( $rankName == $rank ) {
								if($rank == "Private") continue;
								echo "<br />The " . $rank . "s who have earned " . $needed . " medals:<br />"; 
								foreach ( $info as $teacher => $class ) {
									foreach ( $class as $grade => $info ) {
										foreach ($info as $student) {
											echo "<div class='students'>" . $userInfo[$student] . "</div>";
										}
									}	
								}
							}
						} 
					}
					echo "<br />"; 
				}
				echo "<div class='page-break'></div>";
            }
            if ( $super ) {
                echo "<br />";
                echo "Total medals awarded: " . $grandTotal . "<br />";
                echo "<br />Total medals awarded by Medal:<br /><br />";
                foreach ( $grandTotalByMedal as $subject => $medals ) {
                    foreach ( $medals as $medal => $total ) { 
                        echo $subject . " - " . $medal . " : " . $total . "<br />";
                    }
                }
            }         
            ?>
        </div>    
    </BODY>
	<script>
		$('.missingMedal').click( function(event) {
			var on = $(this).is(":checked");
			var val = $(this).prev('input.missingInfoNew').val();
			
			// old post requst. used input.missingInfo params
            //$.post('ajax/saveMissingMedal.php', { info : val, checked : on });
			
			$.post("ajax/medal_rank_ceremony/mark_medal_shipped.php", {params: val, shipped: on}, function(data) {
				data = JSON.parse(data);
				if (!data.success) {
                    event.target.checked = !event.target.checked;
					alert("It seems that there was a server issue marking this down. Please try again later.");
                }
			});
		});
	</script>
</HTML>
 