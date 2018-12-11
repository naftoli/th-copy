<?php
$admin_auth = array('school'); 
require('header.php'); 

require_once 'class.medalReport.php';
$m = new MedalReport;

$previous = false;
if ( isset($_GET['go']) && $_GET['go'] == 'back' ) {
    $previous = true; 
    $m->setPreviousDates();
}
$heDates = $m->getHeReportDates();

require_once 'class.rankReport.php';
$r = new RankReport;

function getRank( $user ) {
	$name = explode(" ", $user);  
	$sql = "select rank_name 
			from ranks r 
			join rank_marks rm 
			using (rank_ord) 
			join users u 
			using (user_id) 
			where u.last = \"$name[1]\"   
			and u.first = \"$name[0]\"  
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
            .students {
                margin-left: 0.5in;
            }
            .medals {
                margin-left: 1in;
            }
            .page-break {
                page-break-after: always;
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
            
            <div align='center'>
                <input type='button' name='print' value='Print' onclick="window.print()" />
            </div>
        </div>
        <div>          
            <?                     
            //for super setup totals arrays
            $grandTotal = 0;
            $grandTotalByMedal = array();
            foreach ( $schools as $school_id => $school_name ) {
                echo "<h2>" . $school_name . "</h2>";
                 
                //set up medals and ranks array
                $m->setSchoolId( $school_id );
                $r->setSchoolId( $school_id );
            
                $m->setMedalDetails();
                $details = $m->getMedalDetails();
                $m->setMedalSummary();
                $summary = $m->getMedalSummary();
                $totals = $m->getMedalTotals();
                $medalsTotal = $m->getMedalsTotal();
                
                $r->setRanks( 'byRank' );
                $ranks = $r->getRanks();
                $r->setRankNames();
                $rankNames = $r->getRankNames();
                foreach ( $details as $school => $line ) {
                    if ( $school != $school_name ) continue;
                    ?>
                    <b>Medals and Rank Ceremony</b><br /><br /> 

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
                    <?
                    echo "<br />Medals earned in " . $school . " from " . $heDates['start_he'] . " until " . $heDates['end_he'] . ". <br /><br />";
                     
                    foreach ( $line as $teacher => $class ) {
                        echo "Base: " . $school . "<br />";    
                        echo "Commander: " . $teacher . "<br />";
                        foreach ( $class as $grade => $info ) {
                            echo "Platoon: " . $grade . "<br /><br />";
                            foreach ( $info as $user => $medals ) {
                                echo "<div class='students'>" . getRank($user) . " " . $user . "</div>";
                                foreach ( $medals as $subject => $medal ) {
                                    echo "<div class='medals'>" . $subject . "-" . $medal . "</div>";
                                }
                                echo "<br />";
                            }
                        }
                    }
                }
                echo "<br />";
                echo "<div class='page-break'></div>";
                
                foreach ( $summary as $school => $medals ) {
                    if ( $school != $school_name ) continue;   
                    $grandTotal += $medalsTotal[$school];  
                    echo "Total of " . $medalsTotal[$school] . " medals earned in " . $school . " from " . $heDates['start_he'] . " until " . $heDates['end_he'] . ". <br />";
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
                foreach ( $ranks as $school => $line ) {
                    if ( $school != $school_name ) continue;
                    echo "Ranks earned in " . $school . " from " . $heDates['start_he'] . " until " . $heDates['end_he'] . ". <br />"; 
                    ?>
                    <br />
                    Before you give out the Rank books, please tell the chayolim we are now going to honor the children who have gone up in rank:<br />
                    <?
                    foreach ( $line as $rank => $info ) {
                        foreach ( $rankNames as $rankName => $needed ) {
                            if ( $rankName == $rank ) {
                                echo "<br />The " . $rank . "s who have earned " . $needed . " medals:<br />"; 
                                foreach ( $info as $teacher => $class ) {
                                    foreach ( $class as $grade => $student ) {
                                        echo "<div class='students'>" . $student . "</div>";
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
 