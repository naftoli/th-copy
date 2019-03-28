<?php
ini_set('max_execution_time', 300);
ini_set('display_errors', TRUE);
$admin_auth = array('school'); 
require('header.php'); 

require_once 'class.medalReport.php';
$m = new MedalReport();

$previous = false;
if ( isset($_GET['go']) && $_GET['go'] == 'back' ) {
    $previous = true; 
    $m->setPreviousDates();
}

$dates = $m->getReportDates();
$heDatesMedals = $m->getHeReportDates();

require_once 'class.rankReport.php';
$rr = new RankReport();
$rr->setRankNames();
$rankNames = $rr->getRankNames();
$heDatesRanks = $rr->getHeReportDates();

function getRank($user) {
	$sql = "select rank_name 
			from ranks r 
			join rank_marks rm 
			using (rank_ord) 
			join users u 
			using (user_id) 
			where u.user_id = " . $user . " 
			order by rm.rank_ord desc 
			limit 0,1";
	$result = mysql_query( $sql ) or die( mysql_error() );
	$row = mysql_fetch_assoc( $result );
	return $row['rank_name'];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

    <HEAD>
        <TITLE>Medals Ranks Ceremony</TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            @media screen {
                .no-print {
                    display: block;
                }
                .print-only {
                    display: none;
                }
            }
            @media print {
                .no-print {
                    display: none;
                }
                .print-only {
                    display: block;
                }
            }
            th, td {
                padding: 3px 10px;
                vertical-align: top;
            }
            .page-break {
                page-break-after: always;
            }
            #main {
                font-size: 14px;
            }
            .medals { 
                margin-left: 30px;
            }
        </style>     
    </HEAD>   
    
    <BODY>
        <?php include('admin_header.php'); ?>   
        <? 
        $super = false;
        $schools = array();
        //if it's a super user, loop through all schools
        //otherwise show school associated with account
        if ( $admin->auth == 'super' ) {
            $super = true;
        }
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        ?>
        <div class='no-print'>
            <h1>Medals Ranks Ceremony</h1>
            
            <?
            if ( $previous ) {  
                echo "Click <a href='medal_rank_ceremony.php'>here</a> to show next report dates.<br /><br />";
            } else {  
                echo "Click <a href='medal_rank_ceremony.php?go=back'>here</a> to show previous report dates.<br /><br />";
            }
            ?>
			<p>Click <a href="https://vimeo.com/86615168">here</a> to show the Chayolim the Medals Ceremony Video before handing out their hard earned Medals.</p>
            
            <div align='center'>
                <input type='button' name='print' value='Print' onclick="window.print()" />
            </div>
        </div>
        <div id='main'>          
            <?                     
            //for super setup totals arrays
            $grandTotal = 0;
            $grandTotalByMedal = array();
            foreach ( $schools as $school_id => $school_name ) {
                echo "<h2>" . $school_name . "</h2>";
				
                //set up medals and ranks array
                $m->setSchoolId( $school_id );
                $rr->setSchoolId( $school_id );
            
                $m->setMedalDetails();
                $details = $m->getMedalDetails();
                $m->setMedalSummary();
                $summary = $m->getMedalSummary();
                $totals = $m->getMedalTotals();
				$medalsTotal = $m->getMedalsTotal();
				$userInfo = $m->getUserInfo();
                
                $rr->setRanks('byRank');
                $ranks = $rr->getRanks();
				
				if (count($details)) {
					foreach ( $details as $school => $line ) {
						if ( $school != $school_name ) continue;
						?>
						<b>Medals and Rank Ceremony</b><br />
						<?
						echo "<br />Medals earned in " . $school . " from " . $heDatesMedals['start_he'] . " until " . $heDatesMedals['end_he'] . ". <br /><br />";
						?>
						Please take the time at the beginning of the rally (between 2:00 - 2:15) to announce each child 
						who has earned a medals and give them the proper respect they deserve.<br />
						<br />           
						<p><b>Before you do please point out to the children:</b></p>           
						A white medals is 15 weeks of completing this mission<br/>         
						A red medal is for 20 weeks of completing this mission<br/>            
						An orange medal is for 25 weeks of completing this mission<br/>            
						A yellow medal is for 30 weeks of completing this mission<br/>            
						A green medal is for 35 weeks completing this mission<br/>             
						A blue medal is for 40 weeks (that’s almost a whole year) of completing this mission<br />
						<br />
						
						<table>
							<tr>
								<th>Commander</th>
								<th>Platoon</th>
								<th>Soldier</th>
								<th>Medals</th>
							</tr>
	
						<? 
						foreach ( $line as $teacher => $class ) {
							foreach ( $class as $grade => $info ) {
								foreach ( $info as $user => $medals ) {
									echo "<tr><td>" . $teacher . "</td><td>" . $grade . "</td>";
									echo "<td>" . getRank($user) . " " . $userInfo[$user] . "</td><td>";
									foreach ( $medals as $subject => $more ) {
										foreach ($more as $medal) {
											echo $subject . "-" . $medal . "<br />";
										}
									}
									echo "</td></tr>";
								}
							}
						}
						echo "</table>";
						echo "<br /><br />";
					}
					echo "<br />";
					echo "<div class='page-break'></div>";
					
					foreach ( $summary as $school => $medals ) {
						if ( $school != $school_name ) continue;   
						$grandTotal += $medalsTotal[$school];  
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
								echo "<br />The " . $rank . "s who have earned " . $needed . " medals:<br />"; 
								foreach ( $info as $teacher => $class ) {
									foreach ( $class as $grade => $info ) {
										foreach ($info as $student) {
											echo "<div class='students'>" . $student . "</div>";
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
</HTML>
 