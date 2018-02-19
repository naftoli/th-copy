<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

if ($admin_user['auth'] != 'super') {
    echo "You have no permission to view this page.";
    exit;
}

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$userInfo = array();
foreach ($schools as $sid => $schoolName) {
    $sql = "SELECT tc.*, u.first, u.last, c.* "
            ." FROM th_chidon tc "
            ." JOIN users u using (user_id) "
            ." JOIN classes c on u.class_id = c.class_id "
            ." WHERE tc.year = " . $year . " "
            ." AND u.school_id = " . $sid . " "
            ." AND tc.deleted = 0 "; // only not deleted kids
    $sql .= " order by class_grade, class_sub, tc.school_rep desc, u.last, u.first";
    echo "<input type='hidden' name='sql' value=\"" . $sql . "\" />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $name = $row['first'] . ' ' . $row['last'];
        $t1a = $row['test1a'];
        $t1b = $row['test1b'];
        $t2a = $row['test2a'];
        $t2b = $row['test2b'];
        $t3a = $row['test3a'];
        $t3b = $row['test3b'];
        $userInfo[$sid][$grade][$row['th_chidon_id']][$name] = array(
            't1a' => $t1a,
            't1b' => $t1b,
            't2a' => $t2a,
            't2b' => $t2b,
            't3a' => $t3a,
            't3b' => $t3b,
            'contestant'  => $row['contestant'],
            'rep' => $row['school_rep'],
            'enrolled' => $row['date_paid'],
            'can_enroll' => $row['can_enroll'],
            'edit'  => $row['allow_edit']
        );
    }
}
// echo "<pre>"; print_r($users); echo "</pre>";
?>
<!DOCTYPE html>
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Shabbaton Eligibility</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 5px;
            }
            caption {
                border-bottom: solid 1px black;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            .tests input {
                width: 30px;
            }
            input[disabled] {
                color: #A9A9A9;
                padding: 2px;
                margin: 0 0 0 0;
                background-image: none;
            }
            .options {
                text-align: center;
            }
            a.button{
                display: inline-block;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Shabbaton Eligibility</h1>
        
        <div class="options">
            <a class="button" id="generate_csv">Export to CSV (Excel/LibreOffice/Google Sheets)</a>
        </div>
 
        <?php foreach ($userInfo as $sid => $info) : ?>
            <table class="tests">
                <caption><?=$schools[$sid]?></caption>
                <tr>
                    <th>Grade</th>
                    <th>Student</th>
                    <th>Avg Part 1</th>
                    <th>Contestant</th>
                    <th>Avg Part 2</th>
                    <th>Avg All</th>
                    <th>Representative</th>
                    <th>Shabbaton Eligibility</th>
                    <th>Activate Enrollment</th>
                    <th>Enrolled</th>
                    <th>Allow Edits</th>
                </tr>
                <?php
                $curGrade = 0;
                foreach ($info as $grade => $other) {
                    foreach ($other as $chidon_id => $more) {
                        foreach ($more as $name => $tests) {
                            // calculate avgs
                            $t1a = intval($tests['t1a']);
                            $t1b = intval($tests['t1b']);
                            $t2a = intval($tests['t2a']);
                            $t2b = intval($tests['t2b']);
                            $t3a = intval($tests['t3a']);
                            $t3b = intval($tests['t3b']);
                            
                            $avg1 = (intval($tests['t1a']) + intval($tests['t2a']) + intval($tests['t3a'])) / 3;
                            $avg2 = (intval($tests['t1b']) + intval($tests['t2b']) + intval($tests['t3b'])) / 3;
                            $avg = ($avg1 + $avg2) / 2;
                            
                            if ($curGrade != $grade) {
                                echo "<tr><td colspan='12'><h2></h2></td></tr>";
                                $curGrade = $grade;
                            }
                            
                            echo "<tr id=" . $chidon_id . " class='student'><td>" . $grade . "</td><td>" . $name . "</td><td>" . number_format($avg1, 2) . "</td><td>";
                            echo "<input type='checkbox' class='contestant' ";
                            if ($tests['contestant']) echo "checked ";
                            echo " /></td><td>";
                            echo number_format($avg2, 2) . "</td><td>" . number_format($avg, 2) . "</td><td>";
                            echo "<input type='checkbox' class='rep' ";
                            if ($tests['rep']) echo "checked ";
                            echo " /></td><td>";
                            if ($tests['rep']) echo "Representative";
                            else if ($tests['contestant']) echo "Contestant";
                            echo "</td><td>";
                            echo "<input type='checkbox' class='activate'";
                            if ($tests['can_enroll']) echo "checked ";
                            echo " /></td><td>";
                            if ($tests['enrolled']) echo $tests['enrolled'];
                            echo "</td><td>";
                            echo "<input type='checkbox' class='edit' ";
                            if ($tests['edit']) echo "checked ";
                            echo "/></td></tr>";
                        }
                    }
                }
                ?>
            </table>
            <br />
        <?php endforeach; ?>
        <button onclick="location.href='review_enrollment.php';">Review Enrollment ></button>
    </body>
    <script>        
        $(".contestant").click( function() {
            var id = $(this).parent().parent().attr('id');
            var val = $(this).is(":checked") ? 1 : 0;
            $.post('ajax/updateChidon.php', { id : id, field : 'contestant', val : val }, function( error ) {
                if (parseInt(error)) {
                    alert("Error Updating.");
                } else {
                    //alert("Updated.");
                }
            });
        });
        
        $(".rep").click( function() {
            var id = $(this).parent().parent().attr('id');
            var val = $(this).is(":checked") ? 1 : 0;
            $.post('ajax/updateChidon.php', { id : id, field : 'school_rep', val : val }, function( error ) {
                if (parseInt(error)) {
                    alert("Error Updating.");
                } else {
                    //alert("Updated.");
                }
            });
        });
        
        $(".activate").click( function() {
            var id = $(this).parent().parent().attr('id');
            var val = $(this).is(":checked") ? 1 : 0;
            if (val) {
                $.post('ajax/activateEnrollment.php', { id : id, can_enroll: val }, function( success ) {
                    response = JSON.parse( success );
                    if(!response.chap) {
                        alert('It appears that you have not setup any Chaperones yet!. Redirecting you to Chaperones page.');
                        location.href = "/chidon_school_reg.php";
                    } else if (!response.success) {
                        event.target.checked = !event.target.checked;
                        alert("Error updating enrollment Status");
                    }
                });
            }
        });
        
        $(".edit").change( function() {
            var id = $(this).parent().parent().attr('id');
            var val = $(this).is(":checked") ? 1 : 0;
            $.post('ajax/updateChidon.php', { id : id, field : 'allow_edit', val : val }, function( error ) {
                if (parseInt(error) != 0) {
                    alert('Error updating.');
                }
            });
        });
        
        $("#generate_csv").click(genrate_csv);
        
        function genrate_csv() {
            var rows = []; // the rows for the csv export
            var csvContent = "School,Grade,Name,Avg Part 1,Avg Part 2,Avg All,Eligibility,Enrolled\n"; //"Serial,First,Last,Grade,# Created\n"; // the baisc csv file
            var universalBOM = "\uFEFF";

            $.each($(".tests"), function(school_index, school) {
                var school_name = $(school).find("> caption").text();
                $.each($(school).find("tr.student"), function(student_index, student_row) {
                    var csv_row = [school_name];
                    csv_row.push('"' + $(student_row).find("td")[0].innerText + '\t"'); // add the grade
                    csv_row.push('"' + $(student_row).find("td")[1].innerText + '\t"'); // add the name
                    csv_row.push('"' + $(student_row).find("td")[2].innerText + '\t"'); // add the avg for the first test
                    csv_row.push('"' + $(student_row).find("td")[4].innerText + '\t"'); // add the avg for the second test
                    csv_row.push('"' + $(student_row).find("td")[5].innerText + '\t"'); // add the avg for all tests
                    csv_row.push('"' + $(student_row).find("td")[7].innerText + '\t"'); // add the eligibility status
                    csv_row.push('"' + $(student_row).find("td")[9].innerText + '\t"'); // add when they are enrolled
                    
                    csvContent += csv_row.join(",") + "\n";
                });
            });

            var hiddenElement = document.createElement('a');
            hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
            hiddenElement.target = '_blank'; // in a new tab
            hiddenElement.download = 'shbbaton-eligibility.csv'; // with this file_name
            hiddenElement.click(); // and click it
        }
    </script>
</html>