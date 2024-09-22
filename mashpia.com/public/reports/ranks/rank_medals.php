<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

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

function getRanks() {
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

function getShipped() {
    $shipped = [];
    $sql = "select * from rank_medals_shipped";
    $result = mysql_query($sql);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    }
    while ($row = mysql_fetch_assoc($result)) {
        $shipped[$row['user_id']][] = $row['rank_ord'];
    }
    return $shipped;
}

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
        WHERE
            u.user_registered > 0 
                AND u.school_id NOT IN (180, 585, 808, 612) 
        GROUP BY u.user_id 
        HAVING rank_ord NOT IN (1, 9, 12)
        ORDER BY school_name , u.last , u.first";
$result = mysql_query($sql);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

$ranks = getRanks();
$classes = getClasses();
$shipped = getShipped();

$info = [];
$user_info = [];
while ($row = mysql_fetch_assoc($result)) {
    // check if already shipped rank
    if (isset($shipped[$row['user_id']])) {
        $shipped_ranks = $shipped[$row['user_id']];
        if (in_array($row['rank_ord'], $shipped_ranks)) {
            continue;
        }
    }
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?= $dir ?>">
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

      #sheet tr, th, td {
        padding: 10px;
        border-bottom: 1px solid #f2f2f2;
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
    <h1>Rank Medals Catchup Report</h1>
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
                    $numRanks = 1;
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
                    // show all rank up to and including current rank based off rank ord
                    // figure out what rank we should start with
                    if ($rank_ord >= 12) $ord = 13;
                    else if ($rank_ord >= 9) $ord = 10;
                    else $ord = 2;
                    for (; $ord <= $rank_ord; $ord++) {
                        echo "<span class='medal'>" . $ranks[$ord] . "</span>";
                        $sheet[$school_id][$user][$user_info[$user]][$grade][] = $ranks[$ord];
                        $for_shipping[$user][] = $ord;
                        $numRanks++;
                        $totalRanks++;
                    }
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
        Total Rank Medals: <?= $totalRanks; ?>
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
            <th>Rank Medal</th>
        </tr>
        <?php
        foreach ($sheet as $school => $more) {
            foreach ($more as $user_id => $info) {
                foreach ($info as $user => $other) {
                    foreach ($other as $grade => $more) {
                        foreach ($more as $medal) {
                            echo "<tr><td>" . $school . "</td><td>" . $user_id . "</td><td>" . $user . "</td><td>" .
                                $grade . "</td><td>" . $medal . "</td></tr>";
                        }
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

  function downloadAsCSV(data, filename = 'rank_medals_shipped.csv') {
    // Get all unique keys from the objects
    const keys = Object.keys(data)

    // create new array
    const csvData = []
    for (let i = 0; i < keys.length; i++) {
      const key = keys[i]
      const value = data[key]
      for (let j = 0; j < value.length; j++) {
        csvData.push({
          [key]: value[j]
        })
      }
    }

    // Create CSV header row
    const csvRows = ['User ID, Rank Medal'];

    // Create CSV data rows
    for (let i in csvData) {
      const row = csvData[i];
      const key = Object.keys(row);
      const values = Object.values(row);
      csvRows.push(`${key},${values}`);
    }

    // Join all rows into a single string
    const csvString = csvRows.join('\n');

    // Create a Blob with the CSV content
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });

    // Create a temporary URL for the Blob
    const url = window.URL.createObjectURL(blob);

    // Create a hidden anchor element
    const link = document.createElement("a");
    link.style.display = "none";
    link.href = url;
    link.download = filename;

    // Append the link to the body
    document.body.appendChild(link);

    // Programmatically click the link to trigger the download
    link.click();

    // Clean up by removing the link and revoking the URL
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  }

  const setAsShipped = () => {
    const for_shipping = <?= json_encode($for_shipping) ?>;
    downloadAsCSV(for_shipping)
  }
</script>
</BODY>
</HTML>
