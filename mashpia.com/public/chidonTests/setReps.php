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
echo "<pre>"; print_r($marks); echo "</pre>"; exit;

$numTests = 3;
$child_marks = [];
$child_info = [];
$types = $ct->getTypes();
foreach ($info as $school => $children) {
    foreach ($children as $child) {
        $id = $child['th_chidon_id'];
        $grade = $child['class_grade'];
        $avg = 0;
        foreach ($types as $type => $desc) {
            $total = 0;
            for ($i = 1; $i <= $numTests; $i++) {
                $mark = isset($marks[$id][$i][$type]) ? $marks[$id][$i][$type] : 0;
                $total += $mark;
            }
            $avg += floatval($total / $numTests);
        }
        $final = round($avg / count($types), 2);
        $child_marks[$schools[$school]][$grade][$id] = $final;
        $child_info[$id] = [
            'first'         => $child['first'],
            'last'          => $child['last'],
            'school_rep'    => $child['school_rep'],
            'regional_rep'  => $child['regional_rep'],
            'intl_rep'      => $child['intl_rep'],
            'khk_reg'       => $child['khk_reg']
        ];
    }
}
// sort by mark desc
foreach ($child_marks as $school => $more) {
    foreach ($more as $grade => $other) {
        arsort($child_marks[$school][$grade]);
    }
}
echo "<pre>"; print_r($child_info); print_r($child_marks); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>School Representatives Report</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
      tr, th, td {
        font-size: 14px;
        padding: 5px;
      }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>School Representatives Report</h1>
<?php
foreach ($child_marks as $school => $more) {
    echo "<h2>" . $school . "</h2>";
    ?>
    <table>
        <thead>
        <tr>
            <th>Chidon ID</th>
            <th>Grade</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Track Passed</th>
            <th>Total Avg</th>
            <th>School Rep</th>
            <th>School Rep Team</th>
            <th>Regional Rep</th>
            <th>Intl Rep</th>
            <th>KHK Registered</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($more as $grade => $other) {
            foreach ($other as $id => $avg) {
                $highest_track = $child_info[$id]['highest_track'];
                $first = $child_info[$id]['first'];
                $last = $child_info[$id]['last'];
                echo "<tr id=$id><td>" . $id . "</td><td>" . $grade . "</td><td>" . $first . "</td><td>" . $last . "</td><td>" . $avg . "</td><td>";
                echo "<input type='checkbox' class='school' ";
                if (intval($child_info[$id]['school_rep'])) echo " checked ";
                echo "/></td><td><input type='checkbox' class='regional' ";
                if (intval($child_info[$id]['regional_rep'])) echo " checked ";
                echo "/></td><td><input type='checkbox' class='intl' ";
                if (intval($child_info[$id]['intl_rep'])) echo " checked ";
                echo "/></td><td><input type='checkbox' class='reg' ";
                if (intval($child_info[$id]['khk_reg'])) echo " checked ";
                echo "/></td></tr>";
            }
        }
        ?>
        </tbody>
    </table>
    <?php
}
?>
</body>
<script>
  $(function() {
    $(".school").click( function() {
      let id = $(this).attr('id')
      let checked = $(this).is(":checked") ? 1 : 0
      update(id, checked, 'school_rep')
    })

    $(".regional").click( function() {
      let id = $(this).attr('id')
      let checked = $(this).is(":checked") ? 1 : 0
      update(id, checked, 'regional_rep')
    })

    $(".intl").click( function() {
      let id = $(this).attr('id')
      let checked = $(this).is(":checked") ? 1 : 0
      update(id, checked, 'intl_rep')
    })

    function update(id, checked, field) {
      $.post('setRep.php', { id: id, state: checked, field: field }, function(success) {
        if (parseInt(success)) {
          alert("Saved.")
        } else {
          alert('Error saving.')
        }
      })
    }
  })
</script>
</html>