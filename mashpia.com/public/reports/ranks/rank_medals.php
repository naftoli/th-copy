<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], false);
$schools = $as->getSchools();

function checkForBreak()
{
    global $i, $rows;
    if (($i % 3) != 0) {
        echo "<div class='space'></div>";
    } else {
        $i = 0; //reset i so that it will show new row
        $rows++; //add row
        if (($rows % 11) == 0) {
            $rows = 1; //reset rows counter and add space to top of new page
            echo "<div class='page-break'></div><div class='topSpace'></div>";
        }
    }
    $i++;
}

function getRanks()
{
    $ranks = [];
    $sql = "select * from ranks";
    $res = mysql_query($sql);
    while ($r = mysql_fetch_assoc($res)) {
        $ranks[$r['rank_ord']] = $r['rank_name'];
    }
    return $ranks;
}

$rankNames = getRanks();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.rankReport.php';
$rr = new RankReport();
$rr->setYear($year);

if (isset($_POST['date_selection'])) {
    $dates = explode(':', $_POST['date_selection']);
    $start = $dates[0];
    $end = $dates[1];
    $rr->overrideDates($start, $end);
}

$reportDates = $rr->getReportDates();
$heDatesRanks = $rr->getHeReportDates();
$shipped = $rr->getRankMedalsShipped();
$super = $admin_user['auth'] == 'super';
$for_shipping = [];
?>
<!DOCTYPE html>
<HTML>
<HEAD>
    <TITLE>Rank Medals Report</TITLE>
    <LINK href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <SCRIPT type="text/javascript" src="../../icalendar.js"></SCRIPT>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
      .label {
        width: 2.15in;
        height: 1in;
        font-size: 12px;
        padding: 5px;
        float: left;
      }

      .space {
        width: .35in;
        height: 1in;
        float: left;
        padding: 5px 20px;
      }

      .page-break {
        clear: both;
        page-break-after: always;
      }

      .medal {
        width: 1in;
        float: left;
        font-size: 9px;
      }

      .name {
        width: 2.15in;
        font-size: 14px;
      }

      .topSpace {
        height: 0.5in;
        width: 7in;
      }

      .instructions {
        width: 50%;
      }

      @media screen {
        #report_div {
          display: none;
        }

        #sheet {
          display: block;
        }

        .no-print {
          display: block;
        }
      }

      @media print {
        #report_div {
          display: block;
        }

        #sheet {
          display: none;
        }

        .no-print {
          display: none;
        }
      }

      button {
        padding: 10px;
        font-size: 14px;
      }

      tr, th, td {
        font-family: "Arial", sans-serif;
        padding: 10px;
        font-size: 14px;
        border-bottom: 1px #f0f0f0 solid;
      }

      select, button, input[type="button"], input[type="submit"] {
        padding: 5px 10px;
        font-size: 16px;
        cursor: pointer;
      }
    </style>
    <script type="text/javascript">
      function check() {
        if (confirm("Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again."))
          window.print();
      }
    </script>
</HEAD>

<BODY>
<?php include('../../admin_header.php'); ?>

<div class="no-print">
    <h1>Rank Medals Report</h1>
    <div>
        Current Report is calculated from <?= $heDatesRanks['start_he'] ?> up to <?= $heDatesRanks['end_he'] ?>.<br/>
        <form action="" method="post">
            <p>
                <?php
                echo $rr->getHtmlSelect(3);
                ?>
                <input type="submit" name="submit" value="Modify Report"/>
            </p>
        </form>
    </div>
    <br />
    <div class='instructions'>
        <b>Printing Instructions</b><br/>
        Please set your printer margins to the following:<br/>
        0.5 Top<br/>
        0.3 Left<br/>
        0.0 Right and Bottom<br/><br/>
        <div>
            <button onclick="check()">Print</button>
        </div>
        <br/>
    </div>
</div>

<div id="report_div" name="report_div">
    <div class='topSpace'></div>
    <?php
    $sheet = []; // array to hold all labels
    $totalRanks = 0;
    foreach ($schools as $school_id => $school_name) {
        if (in_array($school_id, [180, 585, 588, 612, 709])) continue;
        $rr->setSchoolId($school_id);
        $rr->setRanks('byUser');
        $ranks = $rr->getRanks();
        $userInfo = $rr->getUserInfo();
        $heNames = $rr->getUserHeNames();

        $i = 1; //counter for columns
        $rows = 1; //counter for rows
        $tempSchool = '';
        $schoolChanged = false; //variable to find out when school changes
        $shippingName = '';
        $shippingAddress = '';
        $tempGrade = '';
        $gradeChanged = false; //variable to find out when grade changes
        $firstTime = true;
        foreach ($ranks as $school => $line) {
            if ($tempSchool != $school) {
                $qry = "select * from schools where school_name = '" . $school . "'";
                $res = mysql_query($qry);
                $r = mysql_fetch_assoc($res);
                $school_id = $r['school_id'];
                $shippingName = $r['shipping_first'] . " " . $r['shipping_last'];
                $shipping = empty($r['shipping_address2']) ? '' : $r['shipping_address2'] . "<br />";
                $shippingAddress = $r['shipping_address1'] . "<br />" . $shipping . $r['shipping_city'] .
                    ", " . $r['shipping_state'] . " " . $r['shipping_postal'] . "<br />" . $r['shipping_country'];
                $schoolChanged = true;
            }
            $tempSchool = $school;
            foreach ($line as $teacher => $class) {
                foreach ($class as $grade => $users) {
                    if ($tempGrade != $grade) {
                        $gradeChanged = true;
                    }
                    $tempGrade = $grade;
                    foreach ($users as $user => $rank_ords) {
                        if ($schoolChanged || $gradeChanged) {
                            if ($schoolChanged) {
                                //echo "</div>";
                                //checkForBreak();
                                //echo "<div class='page-break'></div><div class='topSpace'></div><div class='label'>";
                                if (!$firstTime) {
                                    echo "<div class='page-break'></div><div class='topSpace'></div>";
                                    $i = 1;
                                } else $firstTime = false;
                                echo "<div class='label'>";
                                echo "<span class='name'><b>" . $school . "</b><br />" . $shippingName . "<br />" . $shippingAddress . "</span>";
                                $schoolChanged = false;
                            } else if ($gradeChanged) {
                                echo "<div class='label'>";
                                echo "<span class='name'><b>" . $school . "</b><br />" . $teacher . "<br />" . $grade . "</span>";
                                $gradeChanged = false;
                            }
                            //put current user info on new label so that we don't lose this user
                            echo "</div>";
                            checkForBreak();
                        }

                        // first check if we have anything to send to this user that wasn't sent yet
                        $num_ords = count($rank_ords);
                        foreach ($rank_ords as $ord) {
                            // check if it was already shipped
                            if (isset($shipped[$user]) && in_array($ord, $shipped[$user])) {
                                $num_ords--;
                            }
                        }

                        if ($num_ords) {
                            echo "<div class='label'>";
                            echo "<span class='name'>" . $school . "<br />" . $userInfo[$user] . " (Grade: " . $grade . ")</span><br />";
                            foreach ($rank_ords as $ord) {
                                // check if it was already shipped
                                if (isset($shipped[$user]) && in_array($ord, $shipped[$user])) {
                                    continue;
                                }
                                echo "<span class='medal'>" . $rankNames[$ord] . "</span>";
                                $sheet[$school_id][$grade][$user][] = $ord;
                                $for_shipping[$user][] = $ord;
                                $totalRanks++;
                            }
                            echo "</div>";
                            checkForBreak();
                        }
                    }
                }
            }
        }
    }
    ?>
</div>
<div id="sheet">
    <p>
        Total Rank Medals: <?= $totalRanks; ?>
    </p>
    <?php if ($super) : ?>
    <p>
        <button id="medalsBtnAll">Set All As Shipped</button>
    </p>
    <?php endif; ?>
    <table>
        <tr>
            <th>School ID</th>
            <th>User ID</th>
            <th>Student</th>
            <th>Grade</th>
            <th>Rank Medal</th>
            <th>Rank Ord</th>
        </tr>
        <?php
        foreach ($sheet as $school => $grades) {
            foreach ($grades as $grade => $users) {
                foreach ($users as $user => $ords) {
                    foreach ($ords as $ord) {
                        $name = $userInfo[$user];
                        echo "<tr><td>" . $school . "</td><td>" . $user . "</td><td>" . $name . "</td><td>" .
                            $grade . "</td><td>" . $rankNames[$ord] . "</td><td>" . $ord . "</td></tr>";
                    }
                }
            }
        }
        ?>
    </table>
</div>
<script>
  $(document).ready(() => {
    $('#medalsBtnAll').click(() => {
      setAsShipped()
    })
  })

  const setAsShipped = async () => {
    // use fetch
    const res = await fetch('ajax/set_as_shipped.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({total: <?= $totalRanks ?>, info: <?= json_encode($for_shipping); ?>})
    })
    const data = await res.json()
    if (data.success) {
      alert(data.ranks_count + ' medal ranks set as shipped.')
    } else {
      alert(data.error)
    }
  }
</script>
</BODY>
</HTML>