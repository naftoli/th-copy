<?
require 'db.php';
require 'class.birthdayEn.php';

// echo "<pre>"; print_r( $_POST ); echo "</pre>";
$parshaDates = array();
if (isset($_GET['school'])) {
    $school_id = $_GET['school'];
    $class_id = $_GET['class'];
    $user_id = $_GET['user'];
    $parsha = $_GET['parsha'];
    $gender = $_GET['gender'] ?? '';
    $dates = explode(":", $parsha);
    $start = $dates[0];
    $end = $dates[1];
    $parshaDates[$start] = $parsha;
} else if (isset($_POST['schools'])) {
    $schools = explode(':', $_POST['schools']);
    $parshas = explode(':', $_POST['parshas']);
    $gender = $_POST['gender'] ?? '';

    // check if parshas are parsha end dates (jd format) or actual dates (yyyy-mm-dd format)
    $dateRange = [];
    $parshasChosen = true;
    foreach ($parshas as $key => $parsha) {
      if (strpos($parsha, '-') !== false) {
        if ($parshasChosen) $parshasChosen = false;
        $date = explode('-', $parsha);
        $jd = gregoriantojd($date[1], $date[2], $date[0]);
        if ($key == 0) {
          $dateRange['start'] = $jd;
        } else if ($key == count($parshas) - 1) {
          $dateRange['end'] = $jd;
        }
      } 
    }
    
    $numDates = 0;
    if (! $parshasChosen) {
      $sqlReport = "select start, end, name from parshos where start >= " . ($dateRange['start'] + 6) . " and end <= " . ($dateRange['end'] + 6);
    } else {
      $sqlReport = "select start, end, name from parshos where end in (" . implode(',', $parshas) . ")";
    }
    $resultReport = mysql_query($sqlReport);
    while ($rowReport = mysql_fetch_assoc($resultReport)) {
        $dates['start'][] = $rowReport['start'];
        $dates['end'][] = $rowReport['end'];
        $parshaDates[$rowReport['start']] = $rowReport['name'];
        $numDates++;
    }

    if (! $parshasChosen) {
      $start = $dateRange['start'];
      $end = $dateRange['end'];
    } else {
      $start = $dates['start'][0];
      $end = $dates['end'][count($dates['end']) - 1];
    }
}

$names = [];
if (isset($school_id)) {
    $sql = "select u.user_id, u.first, u.last, u.first_he, u.last_he, u.he_name, c.class_grade, c.class_sub, s.school_name, u.dob  
            from users u 
            join schools s on (s.school_id = u.school_id) 
            join classes c on (c.class_id = u.class_id)         
            where u.user_registered > 0 
            and u.dob > 0 ";
    if ($school_id > 0) {
        $sql .= " and u.school_id = $school_id ";
    }
    if ($class_id > 0) {
        $sql .= " and u.class_id = " . $class_id;
    }
    if ($user_id > 0) {
        $sql .= " and u.user_id = " . $user_id;
    }
    if ($gender == 'm') {
        $sql .= " and (gender = 'M') ";
    } else if ($gender == 'f') {
        $sql .= " and (gender = 'F') ";
    } 
    $sql .= " order by c.class_grade, c.class_sub, u.last, u.first";
} else {
    $sql = "select u.user_id, u.first, u.last, u.first_he, u.last_he, u.he_name, c.class_grade, c.class_sub, s.school_name, u.dob  
            from users u 
            join schools s on (s.school_id = u.school_id) 
            join classes c on (c.class_id = u.class_id)         
            where u.user_registered > 0 
            and u.dob > 0 
            and u.school_id in (" . implode(',', $schools) . ")";
    if ($gender == 'm') {
        $sql .= " and (gender = 'M' or gender = 'm') ";
    } else if ($gender == 'f') {
        $sql .= " and (gender = 'F' or gender = 'f') ";
    } 
    $sql .= "order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
    //echo $sql;
}
// echo $sql; exit;

$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    //check users dob to see if within dates range
    $b = new BirthdayEn($row['user_id']);
    $age = intval($b->calculateAge($row)[0]);
    $jdBirthday = $b->calculateAge($row)[2];
    // if ($row['user_id'] == 81051) {
    //   echo "Age: " . $age . "<br />";
    //   echo "JD Birthday: " . $jdBirthday . "<br />";
    //   echo "Start: " . $start . "<br />";
    //   echo "End: " . $end . "<br />";
    // }

    if (! $parshasChosen) {
        //if hebrew date within range then we are good and add user to array otherwise don't add to array
        if ($jdBirthday >= $start && $jdBirthday <= $end) {
            $grade = empty($row['class_sub']) ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
            $heName = empty($row['he_name']) ?
                ((empty($row['first_he']) && empty($row['last_he'])) ? $row['first'] . ' ' . $row['last'] :
                    $row['first_he'] . '  ' . $row['last_he']) : $row['he_name'];
            $names[$row['school_name']][$grade][$jdBirthday][$parsha][] = array('age' => $age, 'name' => $heName);
        }
    } else {
        //loop through dates array to check if hebrew jd birthday falls within any of them
        $found = false;
        for ($i = 0; $i < $numDates; $i++) {
            if ($jdBirthday >= $dates['start'][$i] && $jdBirthday <= $dates['end'][$i]) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $grade = empty($row['class_sub']) ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
            $heName = empty($row['he_name']) ?
                ((empty($row['first_he']) && empty($row['last_he'])) ? $row['first'] . ' ' . $row['last'] :
                    $row['first_he'] . ' ' . $row['last_he']) : $row['he_name'];
            $names[$row['school_name']][$grade][$jdBirthday][$parshaDates[$dates['start'][$i]]][] = array('age' => $age, 'name' => $heName);
        }
    }
}
?>
<html>
<head>
  <meta charset="UTF-8">
  <style type="text/css">
    @font-face {
      font-family: DirtyEgo;
      src: url('fonts/DIRTYEGO.TTF');
    }

    .page {
      height: 7.5cm;
    }

    .name {
      margin-left: 7.3in;
      margin-top: 1.5in;
      width: 6cm;
      font-size: 30px;
      font-weight: bold;
      text-align: center;
      font-family: DirtyEgo;
      /* transform: rotate(180deg); */
    }

    .grade {
      font-size: 12px;
      font-weight: normal;
    }

    .page-break {
      page-break-after: always;
    }

    @media print {
      .no-print {
        display: none;
      }
    }

    @media screen {
      .no-print {
        margin-left: 38%;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
      }

      .print {
        margin-left: 16%;
      }
    }
  </style>
</head>

<body>
<?
if (empty($names)) {
    echo "Sorry there are no names that meet your criteria, please go back and revise the options.";
    exit;
}
?>
<div class="no-print">
  <p>Printing Instructions:<br/>
    Step 1: Set the Orientation to <u>Portrait</u><br/>
    Step 2: Check 'Shrink to fit Page Width'<br/>
    Step 3: In Options check 'Print Background (colors & images)'<br/>
    Step 4: In the second tab set all Margins to 0.0 inches (All Sides)<br/>
    Step 5: Set all Headers & Footers to Blank</p>
  <p class='print'>
    <input type="button" value="Print" onclick="window.print()"/>
  </p>
</div>
<?
//echo "<pre>"; print_r( $names ); echo "</pre>";
foreach ($names as $school => $info) {
    foreach ($info as $grade => $dates) {
        foreach ($dates as $date => $other) {
            foreach ($other as $parsha => $children) {
                foreach ($children as $child) {
                    ?>
                  <div class="page"></div>
                  <div class="name">
                      <?= $child['name'] ?><br/>
                    <span class="grade">
	                                <?= $school ?> Platoon: <?= $grade ?> - <?= $child['age'] ?> yrs. old
	                                <br/><?= $date ?> - <?= $parsha ?>
	                            </span>
                  </div>
                  <div class="page-break"></div>
                    <?
                }
            }
        }
    }
}
?>
</body>
</html>
