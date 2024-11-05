<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
$australian = GlobalSettings::getAustralian();

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

function get_ranks()
{
    $ranks = [];
    $sql = "select * from ranks";
    $result = mysql_query($sql);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }
    while ($row = mysql_fetch_assoc($result)) {
        $ranks[$row['rank_ord']] = $row['rank_name'];
    }
    return $ranks;
}

function getClasses() {
    $classes = [];
    $sql = "select * from classes where class_era = 0";
    $result = mysql_query($sql);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }
    while ($row = mysql_fetch_assoc($result)) {
        $classes[$row['class_id']] = $row;
    }
    return $classes;
}

$exceptions = array_unique( array_merge([585, 808, 612], $australian) );
$sql = "SELECT 
            s.school_name,
            c.class_id, 
            u.user_id, 
            u.first,
            u.last,
            MAX(rm.rank_ord) AS rank_ord
        FROM
            users u
                JOIN
            schools s USING (school_id)
                JOIN
            rank_marks rm USING (user_id)
                JOIN
            classes c ON u.class_id = c.class_id 
                JOIN 
            user_registration ur ON u.user_id = ur.user_id 
                LEFT JOIN
            rank_medals_shipped rms ON u.user_id = rms.user_id 
        WHERE
            u.user_registered > 0 AND ur.year = $year 
                AND u.school_id NOT IN (" . implode(',', $exceptions) . ") 
                AND rms.user_id IS NULL
        GROUP BY u.user_id 
        HAVING rank_ord > 1  
        ORDER BY school_name , u.last , u.first";
$result = mysql_query($sql);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

$ranks = get_ranks();
$classes = getClasses();

$info = [];
$user_info = [];
while ($row = mysql_fetch_assoc($result)) {
    $school = $row['school_name'];
    $user_id = $row['user_id'];
    $name = $row['first'] . ' ' . $row['last'];
    $user_info[$user_id] = $name;
    $class_id = $row['class_id'];
    $class_info = $classes[$class_id];
    $teacher = $class_info['class_teacher'];
    $grade = $class_info['class_grade'] . (empty($class_info['class_sub']) ? '' : '-' . $class_info['class_sub']);
    $rank_ord = $row['rank_ord'];
    $info[$school][$teacher][$grade][$user_id] = $rank_ord;
}

//echo "<pre>"; print_r($info); echo "</pre>"; exit;
$for_shipping = [];
$actual_ranks = []; // array to hold highest rank per child
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?= $dir ?>">
<HEAD>
    <TITLE>Rank Books Report</TITLE>
    <LINK href="../admin_styles.css" rel="stylesheet" type="text/css">
    <SCRIPT type="text/javascript" src="../icalendar.js"></SCRIPT>
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
    </style>
    <script type="text/javascript">
      function check() {
        if (confirm("Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again."))
          window.print();
      }
    </script>
</HEAD>

<BODY>
<?php include('../admin_header.php'); ?>

<div class="no-print">
    <h1>Rank Books Catchup Report</h1>
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
    $totalBooks = 0;
    $i = 1; //counter for columns
    $rows = 1; //counter for rows
    $tempSchool = '';
    $schoolChanged = false; //variable to find out when school changes
    $shippingName = '';
    $shippingAddress = '';
    $tempGrade = '';
    $gradeChanged = false; //variable to find out when grade changes
    $firstTime = true;
    foreach ($info as $school => $line) {
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
                foreach ($info as $user => $rank_ord) {
                    $numBooks = 1;
                    $actual_ranks[$user] = $rank_ord;
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
                    echo "<div class='label'>";
                    echo "<span class='name'>" . $school . "<br />" . $user_info[$user] . " (Grade: " . $grade . ")</span><br />";
                    // figure out which books to show
                    if ($rank_ord > 11) {
                        $book = 3;
                    } else if ($rank_ord > 8) {
                        $book = 2;
                    } else {
                        $book = 1;
                    }
                    echo "<span class='medal'>Book #" . $book . "</span>";
                    $sheet[$school_id][$user][$user_info[$user]][$grade] = $book;
                    $for_shipping[$user] = $book;
                    $numBooks++;
                    $totalBooks++;
                    echo "</div>";
                    checkForBreak();
                }
            }
        }
    }
    ?>
</div>
<div id="sheet">
    <p>
        Total Rank Medals: <?= $totalBooks; ?>
    </p>
    <p>
        <button id="medalsBtnAll">Set All As Shipped</button>
    </p>
    <table>
        <tr>
            <th>School ID</th>
            <th>User ID</th>
            <th>Student</th>
            <th>Grade</th>
            <th>Rank</th>
            <th>Book #</th>
        </tr>
        <?php
        foreach ($sheet as $school => $more) {
            foreach ($more as $user_id => $info) {
                foreach ($info as $user => $other) {
                    foreach ($other as $grade => $book) {
                        echo "<tr><td>" . $school . "</td><td>" . $user_id . "</td><td>" . $user . "</td><td>" .
                            $grade . "</td><td>" . $ranks[ $actual_ranks[$user_id] ] . "</td><td>" . $book . "</td></tr>";
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
    const res = await fetch('set_as_shipped.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({total: <?= $totalBooks ?>, info: <?= json_encode($for_shipping); ?>})
    })
    const data = await res.json()
    if (data.success) {
      alert(data.books_count + ' books set as shipped.')
    } else {
      alert(data.error)
    }
  }
</script>
</BODY>
</HTML>
