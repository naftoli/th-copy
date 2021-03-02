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

$child_info = [];
foreach ($students as $child) {
    if ($child['school_rep'] || $child['khk_rep']) {
        $id = $child['th_chidon_id'];
        $grade = $child['class_grade'];
        $child_info[$child['gender']][$grade][$child['school_name']][$id] = [
            'first' => $child['first'],
            'last' => $child['last'],
            'khk' => $child['khk_rep'],
            'rep' => $child['school_rep']
        ];
    }
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
foreach ($child_info as $gender => $more) {
    echo "<h2>";
    if ($gender == 'M') echo "Boys";
    else if ($gender == 'F') echo "Girls";
    echo "</h2>";
    ?>
    <table>
        <tr>
            <th>#</th>
            <th>Grade</th>
            <th>School</th>
            <th>Chidon ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Representative</th>
            <th>KHK</th>
        </tr>
    <?php
    foreach ($more as $grade => $other) {
        foreach ($other as $school => $more) {
            $i = 1;
            foreach ($more as $id => $child) {
                echo "<tr><td>" . $i++ . "</td><td>" . $grade . "</td><td>" . $school . "</td><td>" . $id . "</td><td>" .
                    $child['first'] . "</td><td>" . $child['last'] . "</td><td>";
                if ($child['rep']) echo 'yes' . "</td><td>";
                else echo 'no' . "</td><td>";
                if ($child['khk']) echo 'yes' . "</td></tr>";
                else echo 'no' . "</td></tr>";
            }
        }
    }
    echo "</table>";
}
?>
