<?php
$admin_auth = array('school');
require('header.php');

require_once 'class.medalReport.php';
$m = new MedalReport;

$previous = false;
if (isset($_GET['go']) && $_GET['go'] == 'back') {
    $previous = true;
    $m->setPreviousDates();
}

if (isset($_POST['br']) && $_POST['br'] == 'no') {
    $m->setException(54);
}

$heDates = $m->getHeReportDates();
$m->setMedalDetails();
$details = $m->getMedalDetails();
$userInfo = $m->getUserInfo();
//echo "<pre>"; print_r($details); echo "</pre>";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?= $dir ?>">

<HEAD>
  <TITLE>Medals Labels Report</TITLE>
  <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
  <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
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
  </style>
  <script type="text/javascript">
    function check() {
      if (confirm("Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again."))
        window.print();
    }
  </script>
</HEAD>

<BODY>
<?php include('admin_header.php'); ?>

<div class="no-print">
  <h1>Medals Labels Report</h1>
  <div>
    Current Report is calculated from <?= $heDates['start_he'] ?> up to <?= $heDates['end_he'] ?>.<br/>
      <? if ($previous) { ?>
        Click <a href='medals_labels.php'>here</a> to show next report dates.<br/>
        Click <a href='medals_labels.php?go=back&br=no'>here</a> to exclude Beis Rivka from report.<br/><br/>
      <? } else { ?>
        Click <a href='medals_labels.php?go=back'>here</a> to show previous report dates.<br/>
        Click <a href='medals_labels.php?br=no'>here</a> to exclude Beis Rivka from report.<br/><br/>
      <? } ?>
  </div>
  <div class='instructions'>
    <b>Printing Instructions</b><br/>
    Please set your printer margins to the following:<br/>
    0.5 Top<br/>
    0.3 Left<br/>
    0.0 Right and Bottom<br/><br/>
    <div align='center'>
      <input type='button' name='print' value='Print' onclick="check()"/>
    </div>
  </div>
</div>

<div id="report_div" name="report_div">
  <div class='topSpace'></div>
    <?php
    $sheet = []; // array to hold all labels
    $totalNumMedals = 0;
    $i = 1; //counter for columns
    $rows = 1; //counter for rows
    $tempSchool = '';
    $schoolChanged = false; //variable to find out when school changes
    $shippingName = '';
    $shippingAddress = '';
    $tempGrade = '';
    $gradeChanged = false; //variable to find out when grade changes
    $firstTime = true;
    foreach ($details as $school => $line) {
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
            foreach ($class as $grade => $info) {
                if ($tempGrade != $grade) {
                    $gradeChanged = true;
                }
                $tempGrade = $grade;
                foreach ($info as $user => $medals) {
                    $numMedals = 1;
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
                        echo "<div class='label'>";
                        echo "<span class='name'>" . $school . "<br />" . $userInfo[$user] . " (Grade: " . $grade . ")</span><br />";
                        foreach ($medals as $subject => $info) {
                            foreach ($info as $medal) {
                                if ($numMedals > 8) {
                                    echo "</div>";
                                    checkForBreak();
                                    echo "<div class='label'>";
                                    echo "<span class='name'>" . $school . "<br />" . $userInfo[$user] . " (Grade: " . $grade . ") <strong>#2</strong></span><br />";
                                    $numMedals = 1;
                                }
                                echo "<span class='medal'>" . $subject . "-" . $medal . "</span>";
                                $sheet[$school_id][$user][$userInfo[$user]][$grade][$subject][] = $medal;
                                $numMedals++;
                                $totalNumMedals++;
                            }
                        }
                    } else {
                        echo "<div class='label'>";
                        echo "<span class='name'>" . $school . "<br />" . $userInfo[$user] . " (Grade: " . $grade . ")</span><br />";
                        foreach ($medals as $subject => $info) {
                            foreach ($info as $medal) {
                                if ($numMedals > 8) {
                                    echo "</div>";
                                    checkForBreak();
                                    echo "<div class='label'>";
                                    echo "<span class='name'>" . $school . "<br />" . $userInfo[$user] . " (Grade: " . $grade . ") <strong>#2</strong></span><br />";
                                    $numMedals = 1;
                                }
                                echo "<span class='medal'>" . $subject . "-" . $medal . "</span>";
                                $sheet[$school_id][$user][$userInfo[$user]][$grade][$subject][] = $medal;
                                $numMedals++;
                                $totalNumMedals++;
                            }
                        }
                    }
                    echo "</div>";
                    checkForBreak();
                }
            }
        }
    }
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

    ?>
</div>
<div id="sheet">
  <p>
    Total Medals: <?= $totalNumMedals; ?>
  </p>
  <table>
    <tr>
      <th>School ID</th>
      <th>User ID</th>
      <th>Student</th>
      <th>Grade</th>
      <th>Subject</th>
      <th>Medal</th>
    </tr>
      <?php
      foreach ($sheet as $school => $more) {
          foreach ($more as $user_id => $info) {
              foreach ($info as $user => $other) {
                  foreach ($other as $grade => $more) {
                      foreach ($more as $subject => $medals) {
                          foreach ($medals as $medal) {
                              echo "<tr><td>" . $school . "</td><td>" . $user_id . "</td><td>" . $user . "</td><td>" .
                                  $grade . "</td><td>" . $subject . "</td><td>" . $medal . "</td></tr>";
                          }
                      }
                  }
              }
          }
      }
      ?>
  </table>
</div>
</BODY>
</HTML>
