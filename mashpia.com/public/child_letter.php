<?php
$admin_auth = array('school');
require 'header.php';

require_once 'class.report.php';
$r = new Report();
$dates = $r->getReportDates();
$heDatesMedals = $r->getHeReportDates();

function getCurrentRank( $user_id ) {
	$sql = "select rank_name  
			from ranks r 
			join rank_marks rm 
			using (rank_ord) 
			where rm.user_id = " . $user_id . " 
			order by rm.rank_ord desc 
			limit 0,1";
	//echo "<input type='hidden' value='$sql'/>";
	$result = mysql_query( $sql ) or die( $sql . "<br />" . mysql_error() );
	$row = mysql_fetch_assoc( $result );
	return $row['rank_name'];
}

function getMedals( $user_id ) {
    global $dates;
    $medals = array();
    $sql = "select mm.*, s.subject_name, m.medal_name from medal_marks mm
            join subjects s using (subject_id)
            join medals m using (medal_ord) 
            where user_id = " . $user_id . "
            and date_awarded >= " . $dates['start'] . "
            and date_awarded <= " . $dates['end'];
    //echo $sql; exit;
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $medals[$row['subject_name']][] = $row['medal_name'];
    }
    return $medals;
}

function getRanks( $user_id ) {
    global $dates;
    $ranks = array();
    $sql = "select rm.*, r.rank_name from rank_marks rm
            join ranks r using (rank_ord) 
            where user_id = " . $user_id . "
            and date_promoted >= " . $dates['start'] . "
            and date_promoted <= " . $dates['end'];
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $ranks[] = $row['rank_name'];
    }
}

function getAdminName( $school_id ) {
    $sql = "select * from admins
            where admin_id = (
            select admin_id from admin_auths
            where id = " . $school_id . "
            and position = 'Base Commander'
            limit 1)";
    //echo $sql; exit;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['first'][0] . '. ' . $row['last'];
}

$weeks = array(
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
$months = array(
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
$special = array(
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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <title>Chayol Letter</title>
        <style>
            th, td {
                font-size: 14px;
                padding: 5px;
            }
            .letter {
                line-height: 1.2;
            }
            @media print {
                .letter {
                    page-break-after: always;
                    height: 10in;
                }
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    
    <body>
        <?php include('admin_header.php'); ?>
        <h1 class="no-print">Chayol Letter</h1>
        <div align="center" class="no-print">
            <button onclick="window.print()">Print</button>
        </div>
        
        <?php
        require_once 'class.adminSchools.php';       
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
        $schools = $as->getSchools();
        foreach ($schools as $id => $school) {
            $children = array();
            $sql = "select user_id, first, last, class_grade, class_sub
                    from users u
                    join classes c on c.class_id = u.class_id 
                    where u.user_registered > 0 
                    and u.school_id = " . $id . "
                    order by class_grade, class_sub, last, first";
            //echo $sql;
            $result = mysql_query( $sql );
            while ($row = mysql_fetch_assoc( $result )) {
                $children[$row['user_id']] = array(
                    'name'  => $row['first'] . ' ' . $row['last'],
                    'grade' => $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'])
                );
            }
            
            echo "<h2>" . $school . "</h2>";
            foreach ($children as $user_id => $child) {
                $ranks = getRanks( $user_id );
                $medals = getMedals( $user_id );
                if (empty($medals)) continue;
                ?>
                <div class="letter">
                    Dear <?=getCurrentRank($user_id)?> <?=$child['name']?><br />
                    Platoon: <?=$child['grade']?><br />
                    <br />
                    Mazal Tov, between <?=$heDatesMedals['start_he']?> and <?=$heDatesMedals['end_he']?> you have
                    <?php if (!empty($ranks)) : ?>
                        <?php if (count($ranks) == 1) : ?>
                            been promoted in rank to <?=$ranks[0]?> and you have
                        <?php else : ?>
                            been promoted in rank to the following ranks:<br />
                            <?php
                            foreach ($ranks as $rank) {
                                echo $rank . "<br />";
                            }
                            ?>
                            and you have 
                        <?php endif; ?>
                    <?php endif; ?>
                    earned the following medals.
                    <br /><br />
                    <table>
                        <tr>
                            <th>Campaign</th>
                            <th>Medal</th>
                            <th>For Completing</th>
                        </tr>
                        <?php
                        foreach ($medals as $subject => $info) {
                            foreach ($info as $medal) {
                                echo "<tr><td>" . $subject . "</td><td>" . $medal . "</td><td>";
                                if ($subject == 'יומי דפגרא') {
                                    echo $special[$medal] . " special days of missions";
                                } else if (in_array($subject, array('WWTC','מבצעים'))) {
                                    echo $months[$medal] . " months of missions";
                                } else {
                                    echo $weeks[$medal] . " weeks of missions";
                                }
                                echo "</td></tr>";
                            }
                        }
                        ?>
                    </table>
                    <br /><br />
                    May you go from strength to strength doing more and more missions until we complete our ultimate mission to bring Moshiach NOW!
                    <br /><br />
                    <div style="float: left">
                        General S. Weinbaum<br />
                        Tzivos Hashem H.Q.
                    </div>
                    <div style="float: right">
                        Base commander <?=getAdminName( $id )?><br />
                        <?=$school?>
                    </div>
                    <div style="clear: both">
                        <br /><br />
                    </div>
                </div>
                <?php
            }
        } ?>
    </body>
</html>