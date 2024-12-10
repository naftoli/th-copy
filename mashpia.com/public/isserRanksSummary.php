<?php
$admin_auth = array('school');
require('header.php');

if (isset($_GET['prev']) && $_GET['prev'] == 1) {
    $prev = true;
} else {
    $prev = false;
}

require_once 'class.rankReport.php';
$rr = new RankReport($prev);
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
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], false );
$schools = $as->getSchools();
?>
<div class='no-print'>
    <h1>Isser's Ranks Summary Sheet</h1>

    <p>
        <? if ($prev) : ?>
            <a href="isserRanksSummary.php">Show next shipment</a>
        <? else : ?>
            <a href="isserRanksSummary.php?prev=1">Show previous shipment</a>
        <? endif; ?>
    </p>

    <div align='center'>
        <input type='button' name='print' value='Print' onclick="window.print()" />
    </div>
</div>
<div id='main'>
    <?
    $ranktotals = array();
    foreach ( $schools as $school_id => $school_name ) {
        $rr->setSchoolId( $school_id );
        $rr->setRanks('byRank', 0, ' ', '', true);
        $ranks = $rr->getRanks();
        $userInfo = $rr->getUserInfo();
        $heNames = $rr->getUserHeNames();
        //echo "<pre>"; print_r($ranks); echo "</pre>";

        foreach ( $ranks as $school => $line ) {
            if ( $school != $school_name ) continue;
            echo "<h2>" . $school_name . "</h2>";
            echo "Ranks earned in " . $school . " from " . $heDatesRanks['start_he'] . " until " . $heDatesRanks['end_he'] . ". <br /><br />";
            $totals = array();

            foreach ( $line as $rank => $info ) {
                foreach ( $rankNames as $rankName => $needed ) {
                    //echo $rankName . "<br />";
                    if ( $rankName == $rank ) {
//                        echo "<h2>" . $rank . "</h2><table>";
                        foreach ( $info as $teacher => $class ) {
                            foreach ( $class as $grade => $info ) {
                                $add = count($info);
                                if (isset($ranktotals[$rank]))
                                    $ranktotals[$rank] += $add;
                                else
                                    $ranktotals[$rank] = $add;
                                if (isset($totals[$rank]))
                                    $totals[$rank] += $add;
                                else
                                    $totals[$rank] = $add;

//                                foreach ($info as $student) {
//                                    $sql = "select user_serial from users where user_id = " . $student;
//                                    $result = mysql_query($sql);
//                                    $row = mysql_fetch_assoc($result);
//                                    echo "<tr><td><input type='checkbox'></td><td>" . $row['user_serial'] . "</td><td>";
//                                    if (!empty($heNames[$student]))
//                                        echo $heNames[$student] . ' - ';
//                                    echo $userInfo[$student];
//                                    echo " (" . $grade . ")";
//                                    echo "</td></tr>";
//                                    echo "<div class='students'>" . $student . " " . $row['user_serial'] . " <input type='checkbox' /></div>";
//                                }
                            }
                        }
//                        echo "</table><br />";
                    }
                }
            }
            if ($super) {
                ?>
                <h2><?=$school?> Totals</h2>
                <table>
                    <tr>
                        <th>Rank</th>
                        <th>Total</th>
                    </tr>
                    <?
                    $gtotal = 0;
                    foreach ($totals as $rank => $total) {
                        $gtotal += $total;
                        echo "<tr><td>" . $rank . "</td><td>" . $total . "</td></tr>";
                    }
                    echo "<tr><th></th><th>" . $gtotal . "</th></tr>";
                    ?>
                </table>
                <?
            }
            echo "<br /><br />";
            echo "<div class='page-break'></div>";
        }
    }
    ?>
    <h2><?=$super ? 'Grand ' : ''?>Totals</h2>
    <table>
        <tr>
            <th>Rank</th>
            <th>Total</th>
        </tr>
        <?
        $gtotal = 0;
        foreach ($ranktotals as $rank => $total) {
            $gtotal += $total;
            echo "<tr><td>" . $rank . "</td><td>" . $total . "</td></tr>";
        }
        echo "<tr><th></th><th>" . $gtotal . "</th></tr>";
        ?>
    </table>
</div>
</BODY>
</HTML>
