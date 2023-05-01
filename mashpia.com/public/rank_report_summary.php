<?php
$admin_auth = array('school');
require('header.php');

$ranks = [];
$sql = "select rank_ord, rank_name from ranks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}

function getMedals( $user, $rank ) {
    //find out total number of medals earned
    $sql = "select count(*) as total from medal_marks where user_id = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $numMedals = $row['total'];

    //find out number of medals earned above rank
    if ($rank < 14) { // for less than five star general
        $info = array();
        $sql = "select rank_ord, medals_required from ranks where rank_ord in (" . $rank . ',' . ($rank + 1) . ") order by rank_ord";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $info[$row['rank_ord']] = $row['medals_required'];
        }

        return array(
            'total' => $info[$rank + 1] - $info[$rank],
            'done' => $numMedals - $info[$rank]
        );
    } else {
        return [
            'total' => 0,
            'done'  => 0
        ];
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rank Report</title>
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <style type='text/css'>
      body {
        -webkit-print-color-adjust: exact;
      }
      p {
        font-size: 12px;
      }
      table {
        font-size: 11px;
      }
      th, td {
        padding: 3px 10px;
      }
      .missionSelection {
        width: 30%;
        float: left;
        line-height: 1.5;
        margin-top: 10px;
      }
      .classSelection {
        width: 25%;
        float: left;
        line-height: 1.5;
        margin-top: 10px;
      }
      fieldset {
        border: 1px solid white;
        padding: 10px;
        padding-top: 0px;
        -moz-border-radius: 10px;
        -webkit-border-radius: 10px;
        border-radius: 10px;
      }
      legend {
        margin-left: 20px;
        padding: 5px;
        color: purple;
      }
      .page-break {
        page-break-after: always;
      }
      @media print {
        .no-print {
          display: none;
        }
      }
      .totals {
        border-top: 1px dashed purple;
        border-bottom: 1px dashed purple;
      }
      .classes {
        margin: auto;
      }
      .sort {
        font-size: 14px;
      }
      .sort a {
        text-decoration: underline;
      }
      .sort .sortBy {
        color: purple;
        font-weight: bold;
      }
      .circle {
        border-radius: 50%;
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 1px solid grey;
      }
      .fill {
        /*background-color: red !important;*/
        box-shadow: inset 0 0 0 1000px red;
      }
      .image {
        width: 50px;
        height: 50px;
        border-radius: 50%;
      }
    </style>
    <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
    <script>
      function print_report() {
        alert("Please note that Microsoft Edge does NOT support printing medals at the moment. Please use Chrome or Firefox if you can.\n\n"+
          "If you are using Internet Explorer please go to 'Page Setup' and enable 'Print Background Colors and Images'");
        window.print();
      }
    </script>
</HEAD>

<BODY>
<? include('admin_header.php'); ?>
<h1 class="no-print">Rank Report</h1>

<form action="rank_report.php" method="post" class="sort">
    <? if ( isset( $_GET['sort'] ) && $_GET['sort'] == 'rank' ) { ?>
        <span class="sortBy">Sort By:</span> <a href="rank_report.php">Grade</a> Rank
    <? } else { ?>
        <span class="sortBy">Sort By:</span> Grade <a href="rank_report.php?sort=rank">Rank</a>
    <? } ?>
</form>

<div align='center' class='no-print'>
    <input type='button' value='Print' onclick='print_report()' />
</div>
<?
require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$users = array();
$medals = array();
$thumbs = array();
$images = array();
$grandTotals = array();
$sum = 0;
$userIDs = [];

//get rank names
$rankNames = array();
$sql = "select rank_ord, rank_name from ranks";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $rankNames[$row['rank_ord']] = $row['rank_name'];
}

//default sort method
$orderBy = " order by s.school_name, c.class_grade, c.class_sub, u.last, u.first, rm.rank_ord";

$onlyForSchools = [269, 621, 54, 30, 7, 49, 192, 63, 105, 89, 21, 37, 86, 60, 33, 614, 585, 255, 542, 598, 577, 471, 9];

foreach ( $schools as $id => $school ) {
    if (! in_array($id, $onlyForSchools)) continue;
    $sql = "select s.school_name, u.user_id, u.last, u.first, u.user_photo_id, u.mobile_pic, c.class_grade, c.class_sub, rm.rank_ord, t.thumb  
            from rank_marks rm 
            join users u using ( user_id ) 
            left join thumbs t on t.file_id = u.user_photo_id 
            join classes c on (c.class_id = u.class_id) 
            join schools s on (s.school_id = u.school_id) 
            where u.user_registered > 0 
            and u.school_id = $id $orderBy";
    //echo $sql;

    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $grade = $row['class_grade'] . ( empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'] );
        $users[$row['school_name']][$grade][$row['user_id']] = $row;
        $medals[$row['school_name']][$grade][$row['user_id']] = getMedals($row['user_id'], $row['rank_ord']);
        $thumbs[$row['school_name']][$grade][$row['user_id']] = empty($row['thumb']) ? 0 : $row['thumb'];
        $images[$row['school_name']][$grade][$row['user_id']]['mobile'] = empty($row['mobile_pic']) ? 0 : $row['mobile_pic'];
        $images[$row['school_name']][$grade][$row['user_id']]['regular'] = $row['user_photo_id'];
        $userIDs[] = $row['user_id'];
    }
}

// find all admins for the kids
$admins = [];
$sql = "select a.*, aa.id from admins a 
        join admin_auths aa using (admin_id) 
        where aa.id in (".implode(',', $userIDs).")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $admins[$row['id']] = $row;
}

//sort by rank if needed
if ( isset( $_GET['sort'] ) && $_GET['sort'] == 'rank' ) {
    $temp = array();
    $temp2 = array();
    foreach ( $users as $school => $info ) {
        foreach ( $info as $grade => $user ) {
            foreach ( $user as $user_id => $row ) {
                $temp[$school][$row['rank_ord']][$grade][$user_id] = $row;
            }
        }
        ksort( $temp[$school] );
    }
    unset( $users );
    $users = $temp;
    unset( $thumbs );
    $thumbs = $temp2;
}

//display info
foreach( $users as $school => $other ) {
    $totals = array();

    if ( isset( $_GET['sort'] ) && $_GET['sort'] == 'rank' ) {
        echo "<table><tr><th>Parent ID</th><th>School</th><th>Grade</th><th>First Name</th><th>Last Name</th><th>Rank</th>
                <th>Medals to next Rank</th><th>Parent Cell 1</th><th>Parent Cell 2</th></tr>";
        foreach ( $other as $rank => $more ) {
            foreach ( $more as $grade => $other) {
                foreach ( $other as $user_id => $row ) {
                    $admin_info = $admins[$user_id];
                    if ($images[$school][$grade][$user_id]['mobile']) $img = '/mobile/reg/' . $images[$school][$grade][$user_id]['mobile'];
                    else if ($thumbs[$school][$grade][$user_id]) $img = 'thumbs/' . $thumbs[$school][$grade][$user_id];
                    else $img = 'file_view.php?id=' . $images[$school][$grade][$user_id]['regular'];
                    echo "<tr><td>" . $admin_info['admin_id'] . "</td><td>" . $school . "</td><td>" . $grade . "</td><td>
                        <img class='image' src='" . $img . "' /></td><td>" . $grade . "</td><td>" . $row['first'] . "</td><td>" .
                        $row['last'] . $rankNames[$rank] . "</td><td>";
                    $info = $medals[$school][$grade][$user_id];
                    for ($i = 1; $i <= $info['total']; $i++) {
                        $class = 'circle';
                        if ($i <= $info['done']) {
                            $class .= ' fill';
                        }
                        echo "<div class='" . $class . "'></div>";
                    }
                    echo "</td><td>" . $admin_info['admin_phone_mobile'] . "</td><td>" . $admin_info['admin_phone_mobile2'] . "</td></tr>";
                    if (isset($totals[$rank]))
                        $totals[$rank]++;
                    else
                        $totals[$rank] = 1;
                    if (isset($grandTotals[$rank]))
                        $grandTotals[$rank]++;
                    else
                        $grandTotals[$rank] = 1;
                    $sum++;
                }
            }
        }
    } else {
        echo "<table><tr><th>Parent ID</th><th>School</th><th>Grade</th><th>First Name</th><th>Last Name</th><th>Rank</th>
                <th>Medals to next Rank</th><th>Parent  1</th><th>Parent Cell 2</th></tr>";
        foreach ( $users[$school] as $grade => $more ) {
            foreach ( $more as $user_id => $row ) {
                $rank = $row['rank_ord'];
                $admin_info = $admins[$user_id];
                if ($images[$school][$grade][$user_id]['mobile']) $img = '/mobile/reg/' . $images[$school][$grade][$user_id]['mobile'];
                else if ($thumbs[$school][$grade][$user_id]) $img = 'thumbs/' . $thumbs[$school][$grade][$user_id];
                else $img = 'file_view.php?id=' . $images[$school][$grade][$user_id]['regular'];
                echo "<tr><td>" . $admin_info['admin_id'] . "<td>" . $school . "</td><td>" . $grade . "</td><td>
                    <img class='image' src='" . $img . "' /></td><td>" . $grade . "</td><td>" . $row['first'] . "</td><td>" .
                    $row['last'] . "</td><td>" . $rankNames[$rank] . "</td><td>";
                $info = $medals[$school][$grade][$user_id];
                for ($i = 1; $i <= $info['total']; $i++) {
                    $class = 'circle';
                    if ($i <= $info['done']) {
                        $class .= ' fill';
                    }
                    echo "<div class='" . $class . "'></div>";
                }
                echo "</td><td>" .  $admin_info['admin_phone_mobile'] . "</td><td>" . $admin_info['admin_phone_mobile2'] . "</td></tr>";
                if ( isset( $totals[$rank] ) )
                    $totals[$rank]++;
                else
                    $totals[$rank] = 1;
                if ( isset( $grandTotals[$rank] ) )
                    $grandTotals[$rank]++;
                else
                    $grandTotals[$rank] = 1;
                $sum++;
            }
        }
    }
//    echo "</table>";
//    echo "<div class='page-break'></div>";

    ksort( $totals );
    echo "<h2>" . $school . " Totals</h2>";
    echo "<table>";
    echo "<tr><th>School</th><th>Rank</th><th>Total</th></tr>";
    foreach ($ranks as $ord => $rank) {
        $total = isset( $totals[$ord] ) ? $totals[$ord] : 0;
        echo "<tr><td>" . $school . "</td><td>" . $rank . "</td><td>" . $total . "</td></tr>";
    }
    echo "</table>";
    echo "<div class='page-break'></div>";
}

if ( $admin->auth == 'super' ) {
    ksort( $grandTotals );
    echo "<h2>Grand Totals</h2>";
    echo "<table>";
    echo "<tr><th>Rank</th><th>Total</th><tr>";
    foreach ( $grandTotals as $rank => $total ) {
        echo "<tr><td>" . $rankNames[$rank] . "</td><td>" . $total . "</td></tr>";
    }
    echo "</table>";
}
//echo "Sum: " . $sum;
?>
</body>
</html>