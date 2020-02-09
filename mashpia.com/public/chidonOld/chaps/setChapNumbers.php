<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('../../header.php');

require_once '../../class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

require_once '../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = []; // keep info of students in variable and pass it to js for calculations
$grades = [4, 5, 6, 7, 8];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>School Chaperone Requirements</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 14px;
            }
            input[type='text'] {
                width: 30px;
            }
            form {
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <?php include('../../admin_header.php') ?>
        <h1>Set School Chaperone Requirements</h1>
        <form action="setChapNumbers.php" method="post" id="chapForm">
            <p>Ratio of Walking Supervisors per Chidon Students: <input type="text" id="ratio" value="40" />
                <button id="ratioUpdate">Calculate</button> <button id="save">Save Number of Supervisors Needed</button>
            </p>
            <?php foreach ( $schools as $id => $school ) {
                // make sure school has some children in chidon 
                $sql = "select tc.user_id, c.class_grade, test1a, test1b, test2a, test2b, test3a, test3b from th_chidon tc 
                        join users u using (user_id) 
                        join classes c using (class_id) 
                        where tc.deleted = 0 
                        and test1a > 0 
                        and test2a > 0 
                        and year = " . $year . " 
                        and tc.school_id = " . $id;
                $result = mysql_query( $sql );
                while ( $row = mysql_fetch_assoc( $result ) ) {
                    $info[$id][$row['class_grade']][] = $row;
                }
                if ( isset( $info[$id] ) ) {
                    echo "<div id='" . $id . "'>";
                    echo "<h2>" . $school . "</h2>";
                    ?>
                    <p>School-wide Student Limit: <input type="text" class="schoolLimit" value="0" /></p>
                    <p>School-wide Test Avg: <input type="text" class="schoolAvg" value="70" /></p>
                    <p>Total Eligible Students in School: <span class="totalStudents"></span></p>
                    <p>Number of Chaperones Needed: <span class="numChaps">1</span></p>
                    <p>Number of Walking Supervisors Needed: <span class="numSupers"></span></p>
                    <table>
                        <tr>
                            <th>Grade</th>
                            <th>Avg Mark Needed</th>
                            <th>Student Limit</th>
                            <th># of Eligible Students</th>
                        </tr>
                        <?php foreach ( $grades as $grade ) { ?>
                            <tr>
                                <td><?= $grade ?></td>
                                <td><input type="text" class="avg" value="70" /></td>
                                <td><input type="text" class="limit" value="0" /></td>
                                <td class="numStudents"></td>
                            </tr>
                        <?php } ?>
                    </table>
                    </div>
                <?php 
                }
            }
            ?>
        </form>
    </body>
    <script>
        $( function() {
            const info = <?= json_encode( $info ); ?>;

            const calculate = school_id => {
                const ratio = parseInt( $("#ratio").val() );
                const schoolInfo = $("#" + school_id);
                const schoolLimit = parseInt( $(schoolInfo).find(".schoolLimit").val() );
                let totalStudents = 0;
                // get avgs for all grades
                for (let i = 0; i < 6; i++) {
                    let avg = parseFloat( $(schoolInfo).find(".avg").eq(i).val() );
                    let limit = parseInt( $(schoolInfo).find(".limit").eq(i).val() );
                    if ( avg ) {
                        let numStudents = 0;
                        let grade = i + 4;
                        if ( info[school_id][grade] !== undefined ) {
                            for (let student of info[school_id][grade]) {
                                if ( parseFloat(student["test3a"]) > 0 ) {
                                    // if we have a third test mark
                                    if ( (parseFloat(student["test1a"]) + parseFloat(student["test2a"]) + parseFloat(student["test3a"])) / 3 >= avg ) numStudents++;
                                } else {
                                    if ( (parseFloat(student["test1a"]) + parseFloat(student["test2a"])) / 2 >= avg ) numStudents++;
                                }
                            }
                            $(schoolInfo).find(".numStudents").eq(i).text( numStudents );
                        } else {
                            $(schoolInfo).find(".numStudents").eq(i).text(0);
                        }
                        if ( limit ) totalStudents += limit;
                        else totalStudents += numStudents;
                        $(schoolInfo).find(".totalStudents").text( totalStudents );
                    }
                }
                if ( totalStudents ) {
                    // figure out number of walking supers
                    let total = 0;
                    if ( schoolLimit ) total = schoolLimit;
                    else total = totalStudents;
                    const numSupers = parseInt(total / ratio) + 1;
                    $(schoolInfo).find(".numSupers").text( numSupers );
                }
            }

            $("button").click( function( e ) { e.preventDefault(); });

            $(".schoolAvg").blur( function() {
                const avg = parseFloat( $(this).val() );
                const school_id = $(this).parent().parent().attr('id');
                const schoolInfo = "#" + school_id;
                $(schoolInfo).find('.avg').each( function() {
                    $(this).val( avg );
                });
            });

            $("#ratioUpdate").click( function() {
                $("#chapForm div").each( function() {
                    const id = $(this).attr('id');
                    calculate( id );
                });
            });

            $("#save").click( function() {
                let info = [];
                $("#chapForm div").each( function() {
                    const school_id = $(this).attr('id');
                    const schoolInfo = "#" + school_id;
                    const supersNeeded = parseInt( $(schoolInfo).find(".numSupers").text() );
                    if ( supersNeeded ) info.push({ school_id: school_id, numSupers: supersNeeded });
                });
                console.log( info );
                if ( !info.length ) {
                    alert("You need to click on 'calculate' first!");
                    return false;
                } else {
                    $.post('ajax/setSupers.php', { info: info }, function( success ) {
                        const res = JSON.parse( success );
                        if ( res.success ) {
                            alert("Updated.");
                        } else {
                            alert("Error updating.");
                        }
                    });
                }
            });
        });
    </script>
</html>