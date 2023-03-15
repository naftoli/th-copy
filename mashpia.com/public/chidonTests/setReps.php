<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
$super = $admin_user['auth'] == 'super';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$info = [];
$marks = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
    $ct->setScores();
    $ct->calculateMarks();
    $marks += $ct->getMarks();
}
//echo "<pre>"; print_r($info); print_r($marks); echo "</pre>"; exit;

$trackOrder = [
    'iyun'    => 1,
    'havonah' => 2,
    'yediah'  => 3,
    'yesod'   => 4
];

$numTests = 3;
$child_marks = [];
$child_info = [];
$types = $ct->getTypes();
$questions = $ct->getTestQuestions();
foreach ($info as $school => $children) {
    foreach ($children as $child) {
        $id = $child['th_chidon_id'];
        $grade = $child['class_grade'];
        $avg = 0;
        $num = 0; // variable to know how to decide avg
        foreach ($types as $type => $desc) {
            $total = 0;
            for ($i = 1; $i <= $numTests; $i++) {
                $mark = isset($marks[$id][$i][$type]) ? $marks[$id][$i][$type] : 0;
                $total += $mark;
            }
            $num++;
            $avg += floatval($total / $numTests);
            if (strtolower($desc) == $child['highest_track']) break;
        }
        $finalAvg = round($avg / $num, 2);
        $order = empty($child['highest_track']) ? 5 : $trackOrder[$child['highest_track']];
        $child_marks[$schools[$school]][$grade][$order][$id] = $finalAvg;
        $child_info[$id] = [
            'first'         => $child['first'],
            'last'          => $child['last'],
            'school_rep'    => $child['school_rep'],
            'regional_rep'  => $child['regional_rep'],
            'intl_rep'      => $child['intl_rep'],
            'khk_reg'       => $child['khk_reg'],
            'serial'        => $child['user_serial'],
            'school_team'   => $child['school_team'],
            'regional_team' => $child['regional_team'],
            'intl_team'     => $child['intl_team']
        ];
    }
}
// sort by mark desc
foreach ($child_marks as $school => $more) {
    foreach ($more as $grade => $other) {
        foreach ($other as $order => $more) {
            arsort($child_marks[$school][$grade][$order]);
        }
    }
}
// sort by track order
foreach ($child_marks as $school => $more) {
    foreach ($more as $grade => $other) {
        ksort($child_marks[$school][$grade]);
    }
}

//echo "<pre>"; print_r($child_info); print_r($child_marks); echo "</pre>";
$teams = ['Sefer Hamitzvos', 'Mishna Torah', 'Moreh Nevuchim', 'Pirush Hamishnayos', 'Igeres Horambam'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>School Representatives Report</title>
    <style>
      tr, th, td {
        font-size: 14px;
        padding: 10px;
        border-bottom: 1px solid grey;
        font-family: Arial, Helvetica, sans-serif;
      }
    </style>
</head>
<body>
<h1>School Representatives Report</h1>
<button id="repsOnly">Only Show Reps</button>
<button id="showAll">Show All Students</button>
<?php
foreach ($child_marks as $school => $more) {
    echo "<h2>" . $school . "</h2>";
    // find out how many videos were chosen
    $school_id = array_search($school, $schools);
    $sqlV = "select num_chidon_videos from schools where school_id = " . $school_id;
    $resultV = mysql_query($sqlV);
    $num_videos = mysql_fetch_assoc($resultV)['num_chidon_videos'];
    ?>
    <p id="<?=$school_id?>">
      Please choose how many videos you want to have for the school reps.<br />
      <input type="radio" name="videos" class="videos" value="1" <?= $num_videos == 1 ? 'checked' : '' ?> /> One Video<br />
      <input type="radio" name="videos" class="videos" value="5" <?= $num_videos == 5 ? 'checked' : '' ?> /> Five Videos<br />
    </p>
    <br />
    <table>
        <thead>
        <tr>
            <th>Serial Number</th>
            <th>Grade</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Track Passed</th>
            <th>Total Avg</th>
            <th>School Rep</th>
            <th>School Rep Team</th>
            <th>Regional Rep</th>
            <th>Regional Rep Team</th>
            <th>Intl Rep</th>
            <th>Intl Rep Team</th>
            <th>KHK Registered</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($more as $grade => $details) {
            foreach ($details as $order => $other) {
                foreach ($other as $id => $avg) {
                    if (isset($_GET['repsOnly']) && $_GET['repsOnly'] == 1) {
                        if (! ($child_info[$id]['school_rep'] || $child_info[$id]['regional_rep'] || $child_info[$id]['intl_rep'])) continue;
                    }
                    $serial = $child_info[$id]['serial'];
                    $first = $child_info[$id]['first'];
                    $last = $child_info[$id]['last'];
                    $highest_track = array_search($order, $trackOrder);

                    echo "<tr id='$id'><td>" . $serial . "</td><td>" . $grade . "</td><td>" . $first . "</td><td>" . $last .
                        "</td><td>" . $highest_track . "</td><td>" . $avg . "</td><td>";
                    echo "<input type='checkbox' class='school' ";
                    if (intval($child_info[$id]['school_rep'])) echo " checked ";
                    echo "/></td>";
                    echo "<td><select name='school_team' class='school_team'>";
                    echo "<option value='0'>Please Choose</option>";
                    foreach ($teams as $idx => $team) {
                        echo "<option value='" . ($idx + 1) . "'";
                        if ($child_info[$id]['school_team'] == $team) echo " selected";
                        echo ">" . $team . "</option>";
                    }
                    echo "</select></td>";
                    echo "<td><input type='checkbox' class='regional' ";
                    if (intval($child_info[$id]['regional_rep'])) echo " checked ";
                    if (! $super) echo " disabled ";
                    echo "/></td>";
                    echo "<td><select name='regional_team' class='regional_team'";
                    if (! $super) echo " disabled";
                    echo ">";
                    echo "<option value='0'>Please Choose</option>";
                    foreach ($teams as $idx => $team) {
                        echo "<option value='" . ($idx + 1) . "'";
                        if ($child_info[$id]['regional_team'] == $team) echo " selected";
                        echo ">" . $team . "</option>";
                    }
                    echo "</select></td>";
                    echo "<td><input type='checkbox' class='intl' ";
                    if (intval($child_info[$id]['intl_rep'])) echo " checked ";
                    if (! $super) echo " disabled ";
                    echo "/></td>";
                    echo "<td><select name='intl_team' class='intl_team'";
                    if (! $super) echo " disabled";
                    echo ">";
                    echo "<option value='0'>Please Choose</option>";
                    foreach ($teams as $idx => $team) {
                        echo "<option value='" . ($idx + 1) . "'";
                        if ($child_info[$id]['intl_team'] == $team) echo " selected";
                        echo ">" . $team . "</option>";
                    }
                    echo "</select></td>";
                    echo "<td>";
                    if (intval($child_info[$id]['khk_reg'])) echo 'yes';
                    else echo 'no';
                    echo "</td></tr>";
                }
            }
        }
        ?>
        </tbody>
    </table>
    <?php
}
?>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
<script>
  $(function() {
    function saveRep(elem) {
      let id = $(elem).parent().parent().attr('id')
      let checked = $(elem).is(":checked") ? 1 : 0
      let field = $(elem).attr('class') + '_rep'
      update(id, checked, field)
    }

    function saveTeam(elem) {
      let id = $(elem).parent().parent().attr('id')
      let val = $(elem).val()
      let field = $(elem).attr('class')
      update(id, val, field)
    }

    function update(id, val, field) {
      $.post('setRep.php', { id, val, field }, function(success) {
        if (! parseInt(success)) alert('Error saving.')
      })
    }

    $(".videos").click( function () {
      let numVideos = $(this).val()
      let school_id = $(this).parent().attr('id')
      $.post('setVideos.php', { school_id, numVideos }, function(res) {
        if (parseInt(res)) alert('Error saving.')
      })
    })

    $(".school").click( function () {
      saveRep(this)
    })

    $(".regional").click( function () {
      saveRep(this)
    })

    $(".intl").click( function () {
      saveRep(this)
    })

    $(".school_team").change( function () {
      saveTeam(this)
    })

    $(".regional_team").change( function () {
      saveTeam(this)
    })

    $(".intl_team").change( function () {
      saveTeam(this)
    })

    $("#repsOnly").click( function () {
      location.href = 'setReps.php?repsOnly=1'
    })

    $("#showAll").click( function () {
      location.href = 'setReps.php'
    })
  })
</script>
</html>