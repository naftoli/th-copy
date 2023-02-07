<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get info for report: serial number, name, school, class, avg per track, highest track passed, highest eligible reward,
// track registered for
$info = [];
foreach ($schools as $school_id => $name) {
    $sql = "select u.user_id, u.school_id, u.class_id, u.user_serial, u.first, u.last, s.school_name, c.class_grade,    
                c.class_sub, tc.date_paid, tc.khk_trip, tc.th_chidon_id, tc.test_type, tc.reward_type, tc.date_paid, 
                tc.ultimate_trip, tci.highest_track  
            from users u 
            join schools s using (school_id) 
            join classes c on u.class_id = c.class_id 
            join th_chidon tc using (user_id) 
            left join th_chidon_info tci on tc.year = tci.year and tc.user_id = tci.user_id 
            where tc.year = " . $year . " and u.school_id = " . $school_id . " 
            order by school_name, date_paid, class_grade, class_sub, last, first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[] = $row;
    }
}
// echo "<pre>"; print_r($info); echo "</pre>"; exit;
$tracks = ['none', 'yesod', 'yediah', 'havonah', 'iyun'];
$totals = [];
$registered = [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Eligibility / Registered Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            padding: 6px;
            font-size: 12px;
            border-bottom: 1px solid grey;
        }
        .rightBorder {
            border-right: 1px solid black;
        }
        #totals > tr, th, td {
            border: 1px solid grey;
        }
    </style>
</head>
<body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Eligibility / Registered Report</h1>
    <div class="infobox">
      Once registration opens, each child will register for the highest track passed on the 3 tests or the modified track that you gave the child for rewards.
      <br /><br />
      It could be that you mistakenly once missed inputting one mark for one child or that you put it in but it didnt save for whatever reason. We want each child to be able to get what they earned, please review what we have each child as eligible for to confirm that it adds up.
      <br /><br />
      If there are any issues, please message us BEFORE pressing confirm, once you confirm, there will be no changes.
      <br /><br />
      Once registration opens, the children in your school will not have the option to register if you have not pressed the 'Confirm' button.”
    </div>
    <br />
    <?php if ($admin_user['auth'] != 'super') : ?>
    <button style="padding: 10px;" id="confirm">Confirm Eligibility</button>
    <?php endif; ?>
    <br /><br />
    <table>
        <tr>
            <th>School</th>
            <th>Class</th>
            <th>Serial Number</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Track Eligible for</th>
            <th>Eligible Rewards</th>
            <th>Registered For Experience</th>
            <th>Registered for Ultimate Trip</th>
        </tr>
        <?php
        $rewards = [
            'Yesod'     => 'Sweater, Gifts & Yesod Final',
            'Yediah'    => 'Sweater, Gifts, $75 Prize credits, & Yediah Final',
            'Havonah'   => 'Sweater, Gifts, $75 Prize credits, Trip, & Havonah Final',
            'Iyun'      => 'Sweater, Gifts, $75 Prize credits, Trip & Iyun Final',
            'Khk Trip'  => 'Sweater, Gifts, $75 Prize credits, Trip & '
        ];
        $i = 0;
        foreach ($info as $row) {
            $track = $row['highest_track'] ? ucwords($row['highest_track']) : '';
            if (empty($track)) {
                $ct = new ChidonTests();
                $types = $ct->getTypes();
                $highest = $ct->getHighestTrackPassed($row)['highest_track'];
                if (!empty($highest)) $track = $types[$highest];
            }
            $highestTrack = $track;
            if (intval($row['class_grade']) === 8 && (in_array(strtolower($highestTrack), ['havonah', 'iyun']))) {
              // check highest track passed for 5782; if not 'yesod' then can do ultimate trip (khk trip)
              $prev = $ct->getPrevHighestTrack($year - 1, $row['user_id']);
              if ($prev && $prev != 'yesod') $highestTrack = 'Khk Trip';
            }

            $reward = empty($highestTrack) ? 'none' : $rewards[$highestTrack];
            if ($highestTrack == 'Khk Trip') $reward .= ucwords($track) . ' Final';
            $school = $schools[$row['school_id']];
            $class = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $paid = $row['date_paid'] > 0 ? 'yes' : 'no';
            $khk = $row['khk_trip'] > 0 ? 'yes' : '';
            if ($highestTrack == '') $highestTrack = 'none';
            echo "<tr><td>" . $school . "</td><td>" . $class . "</td><td>" . $row['user_serial'] .  "</td><td>" .
                $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . ($highestTrack !== 'Khk Trip' ? $highestTrack : 'Ultimate Trip') .
                "</td><td>" . $reward . "</td><td>" . ($row['date_paid'] ?? '') . "</td><td>" . (intval($row['ultimate_trip']) ? 'yes' : '') .
                "</td></tr>";

            // Totals
            $highestTrack = strtolower($highestTrack);
            if (isset($totals[$row['school_id']][$highestTrack])) $totals[$row['school_id']][$highestTrack]++;
            else $totals[$row['school_id']][$highestTrack] = 1;
            if ($row['date_paid']) {
              if (isset($registered[$row['school_id']][$highestTrack])) $registered[$row['school_id']][$highestTrack]++;
              else $registered[$row['school_id']][$highestTrack] = 1;
            }
        }
        ?>
    </table>
    <?php
    if ($admin_user['auth'] == 'super') {
        echo "<p></p>";
        echo "<table id='totals'>";
        echo "<tr><th class='rightBorder'>School</th>";
        foreach ($tracks as $track) echo "<th>" . ucwords($track) . " Eligible</th>";
        foreach ($tracks as $track) echo "<th>" . ucwords($track) . " Registered</th>";
        echo "</tr>";
        foreach ($schools as $school_id => $name) {
            echo "<tr><td class='rightBorder'>" . $schools[$school_id] . "</td>";
            foreach ($tracks as $track) {
                echo "<td>" . $totals[$school_id][$track] . "</td>";
                echo "<td>" . $registered[$school_id][$track] . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
</body>
<script>
  let year = <?= $year ?>;

  $.post('checkConfirmation.php', { year }, function(result) {
    let res = JSON.parse(result)
    if (res.alreadyConfirmed) $("#confirm").attr('disabled', true)
  })

  $("#confirm").click( function(e) {
    e.preventDefault()
    $(this).attr('disabled', true)
    let button = this
    $.post('saveConfirmation.php', { year }, function(result) {
      let res = JSON.parse(result)
      if (res.success) alert(res.msg)
      else {
        alert(res.error)
        $(button).attr('disabled', false)
      }
    })
  })
</script>
</html>
