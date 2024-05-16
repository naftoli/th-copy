<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rank Report</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
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
    <script type="text/javascript" src="../scripts/jquery-1.8.3.js"></script>
    <script>
      function print_report() {
        //alert("Please note that Microsoft Edge does NOT support printing medals at the moment. Please use Chrome or Firefox if you can.\n\n"+
        //	  "If you are using Internet Explorer please go to 'Page Setup' and enable 'Print Background Colors and Images'");
        window.print();
      }
    </script>
</HEAD>

<BODY>
<? include('../admin_header.php'); ?>
<h1 class="no-print">Rank Report</h1>

<div align='center' class='no-print'>
    <input type='button' value='Print' onclick='print_report()'/>
</div>
<?php
$users = array();
$medals = array();
$thumbs = array();
$images = array();

if ($admin->auth == 'super') {
    $schoolIds = [7, 9, 21, 30, 33, 37, 49, 54, 60, 63, 81, 89, 105, 192, 255, 471, 542, 577, 585, 614, 621, 693, 739];
} else {
    $schoolIds = array_keys($schools);
}

//get rank names
$rankNames = array();
$sql = "select rank_ord, rank_name from ranks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $rankNames[$row['rank_ord']] = $row['rank_name'];
}

$sql = "select s.school_name, u.user_id, u.last, u.first, u.gender, c.class_grade, c.class_sub, rm.rank_ord  
        from rank_marks rm 
        join users u using ( user_id ) 
        join classes c on (c.class_id = u.class_id) 
        join schools s on (s.school_id = u.school_id) 
        where u.user_registered > 0 
        and u.school_id in (" . implode(', ', $schoolIds) . ")";
$sql .= " order by s.school_name, c.class_grade, c.class_sub, u.last, u.first, rm.rank_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $userName = $row['first'] . ' ' . $row['last'];
    $users[$row['school_name']][$row['gender']][$grade][$userName] = $row['rank_ord'];
}

//display info
$genderLookup = [
    'F' => 'Girl',
    'M' => 'Boy'
];
$grandTotals['F'] = [];
$grandTotals['M'] = [];
$generalsTotals['F'] = 0;
$generalsTotals['M'] = 0;
foreach ($users as $school => $info) {
    $totals['F'] = [];
    $totals['M'] = [];
    $totalGenerals['F'] = 0;
    $totalGenerals['M'] = 0;
    foreach ($info as $gender => $other) {
        foreach ($other as $grade => $user) {
            echo "<h2>" . $school . ' - ' . $grade . "</h2>";
            echo "<table>";
            echo "<tr><th>Gender</th><th>Student</th><th>Rank</th></tr>";
            foreach ($user as $name => $rank) {
                echo "<tr><td>" . $gender . "</td><td>" . $name . "</td><td>" . $rankNames[$rank] . "</td></tr>";
                // deal with totals
                if (isset($totals[$gender][$rank]))
                    $totals[$gender][$rank]++;
                else
                    $totals[$gender][$rank] = 1;
                // total generals
                if ($rank >= 9) {
                    $totalGenerals[$gender]++;
                    $generalsTotals[$gender]++;
                }
                // grand totals
                if (isset($grandTotals[$gender][$rank]))
                    $grandTotals[$gender][$rank]++;
                else
                    $grandTotals[$gender][$rank] = 1;
            }
            echo "</table>";
            echo "<div class='page-break'></div>";
        }
    }

    foreach ($totals as $gender => &$info) {
        ksort($info);
    }
    echo "<h2>" . $school . " Totals</h2>";
    echo "<table>";
    echo "<tr><th>Gender</th><th>Rank</th><th>Total</th></tr>";
    foreach ($totals as $gender => $more) {
        foreach ($more as $rank => $total) {
            echo "<tr><td>" . $gender . "</td><td>" . $rankNames[$rank] . "</td><td>" . $total . "</td></tr>";
        }
    }
    echo "</table><br />";
    foreach ($totalGenerals as $gender => $total) {
        if ($total > 0)
            echo "<p>Total " . $genderLookup[$gender] . " Generals: " . $total . "</p>";
    }
    echo "<div class='page-break'></div>";
}

if ($admin->auth == 'super') {
    foreach ($grandTotals as $gender => &$info) {
        ksort($info);
    }
    echo "<h2>Grand Totals</h2>";
    echo "<table>";
    echo "<tr><th>Gender</th><th>Rank</th><th>Total</th><tr>";
    foreach ($grandTotals as $gender => $other) {
        foreach ($other as $rank => $total) {
            echo "<tr><td>" . $gender . "</td><td>" . $rankNames[$rank] . "</td><td>" . $total . "</td></tr>";
        }
    }
    echo "</table>";
    echo "<div class='page-break'></div>";
    echo "<h2>Grand Totals for Generals</h2>";
    foreach ($generalsTotals as $gender => $total) {
        if ($total > 0)
            echo "<p>Total " . $genderLookup[$gender] . " Generals: " . $total . "</p>";
    }
}
?>
</body>
</html>