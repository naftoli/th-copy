<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require 'class.chidonTests.php';
$ct = new ChidonTests();
$ct->setStudents();
$students = $ct->getStudents();
$ct->setScores();
$ct->calculateMarks();
$marks = $ct->getMarks();

//echo "<pre>"; print_r($marks); echo "</pre>";
$child_marks = [];
$child_info = [];
foreach ($students as $child) {
    $id = $child['th_chidon_id'];
    $grade = $child['class_grade'];
    $types = ['expert', 'trophy'];
    $totalAvg = 0;
    foreach ($types as $type) {
        $total = 0;
        for ($i = 1; $i <= 4; $i++) {
            $mark = isset($marks[$id][$i][$type]) ? $marks[$id][$i][$type] : 0;
            $total += $mark;
        }
        $avg = round($total / 4, 2);
        $totalAvg += $avg;
    }
    $final = $totalAvg / 2;
    $child_marks[$child['gender']][$id] = $final;

    // khk avg
    $khkTotal = 0;
    for ($i = 1; $i <= 4; $i++) {
        $mark = isset($child["khk_test_$i"]) ? $child["khk_test_$i"] : 0;
        $khkTotal += $mark;
    }
    $khkAvg = round($khkTotal / 4, 2);

    $child_info[$id] = [
        'first' =>  $child['first'],
        'last'  =>  $child['last'],
        'khk'   =>  $child['khk_rep'],
        'rep'   =>  $child['school_rep'],
        'rep_old'   =>  $child['school_rep_old'],
        'school'    =>  $child['school_name'],
        'khk_avg'   =>  $khkAvg,
        'grade'     =>  $grade
    ];
}

// sort by mark desc
foreach ($child_marks as $gender => $other) {
    arsort($child_marks[$gender]);
}
?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Representatives Report</title>
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
    <h1>Representatives Report</h1>
<?php
foreach ($child_marks as $gender => $more) {
    echo "<h2>";
    if ($gender == 'M') echo "Boys";
    else if ($gender == 'F') echo "Girls";
    echo "</h2>";
    ?>
    <table>
        <tr>
            <th>#</th>
            <th>School</th>
            <th>Grade</th>
            <th>Chidon ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Pro/Expert + Trophy Avg</th>
            <th>Representative</th>
            <th>KHK</th>
            <th>KHK Avg</th>
        </tr>
    <?php
    $i = 1;
    foreach ($more as $id => $avg) {
        echo "<tr><td>" . $i++ . "</td><td>" . $child_info[$id]['school'] . "</td><td>" . $child_info[$id]['grade'] . "</td><td>" .
            $id . "</td><td>" . $child_info[$id]['first'] . "</td><td>" . $child_info[$id]['last'] . "</td><td>" .
            $avg . "</td><td>";
        if ($child_info[$id]['rep']) echo 'yes';
        else echo 'no';
        echo "</td><td>";
        if ($child_info[$id]['khk']) echo 'yes' . "</td><td>" . $child_info[$id]['khk_avg'] . "</td></tr>";
        else echo 'no' . "</td><td></td></tr>";
    }
}
?>