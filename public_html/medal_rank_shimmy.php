<?php
$admin_auth = array('school'); 
require('header.php'); 

require_once 'class.rankReport.php';
$rr = new RankReport();
$rr->overrideDates(2457068, 2457095);
$rr->setRankNames();
$rankNames = $rr->getRankNames();
$heDatesRanks = $rr->getHeReportDates();

function getRank($user) {
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
            <h1>Ranks Ceremony</h1>
            
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

                $rr->setSchoolId( $school_id );                
                $rr->setRanks('byRank');
                $ranks = $rr->getRanks();
                
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
            ?>
        </div>    
    </BODY>
</HTML>
 