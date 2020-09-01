<?php
$admin_auth = array('school'); 
require('header.php');
require_once('file_save.php');

// get current working year
require_once 'class.globalSettings.php';
$startEnd = GlobalSettings::getCurYearDates();

//get default dates
$dates = array();
$sql = "SELECT * FROM parshos 
        WHERE start >= " . $startEnd['start'] . " 
        and end <= " . $startEnd['end'];        
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $dates[] = $row;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Teacher's Mission Checklist</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui-1.9.2.custom.min.js"></script>
        <style type="text/css">
            @media print { 
                .no-print {
                    display: none;
                }
            }
            fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                line-height: 1.5;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            .gradeSelection {
                width: 25%;
                float: left;
            }
            tr, th, td {
                padding: 10px;
                border: 1px solid black;
                font-size: 12px;
            }
            .page-break {
                page-break-after: always;
            }
            hr {
                display: block;
                color: #C0C0C0;
                background-color: #C0C0C0;
                width: 300px; 
            }
            .schoolInfo {
                font-size: 14px;
                font-weight: bold;
            }
            .schoolInfo img {
                float: left;
                margin-right: 16px;
                margin-bottom: 16px;
            }
            .student {
                width: 100px;
            } 
            .week {
                width: 50px;
            }
            th {
                text-align: center;
            }
        </style>
        <script type="text/javascript">
            $( function() {
                $(".checkallClasses").click( function() {
                    $(".class").attr("checked", true);
                });
                $(".uncheckallClasses").click( function() {
                    $(".class").attr("checked", false);
                });
                
                $(".class").attr("checked", true);
            });
            
            function getImage() {
                var id = $("#schoolID").val();
                $.post('ajax/getImage.php', {id : id}, function( data ) {
                    //alert( data );
                    $("#image").html( data );
                });
            }
        </script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Teacher's Mission Checklist</h1>
        <div class='no-print' align='center'>
            <input type='button' name='print' value='Print' onclick='window.print()' />
        </div>

        <br />
        <div class="infobox" style="font-size: 16px;">
            It will show a check next to a chayol even if he/she only completed 1 task the entire week.<br /> 
            (To see which chayolim checked off tasks for at least 5/7 days per week, go to Mission Marathon>Weekly Raffle>Eligible Students)
        </div>
        <br />
        
        <p>
        	Click <a href="modify_checklist.php">here</a> to modify report.
        </p>
                
        <? 
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();  
        $grandTotals = array();
        $studentTotals = array();
        
        foreach ( $schools as $id => $school ) {    
            //get classes based on school id
            require_once 'class.schoolClasses.php';
            $c = new SchoolClasses( $id );
            $classes = $c->getClasses();

            if ( isset( $_POST['class'] ) ) { 
                $classes = $_POST['class'];
            } else {
                $classes = 'all';
            }
            require_once 'class.schoolsUsers.php';
            $su = new SchoolsUsers( $id );
            $su->setClasses( $classes );
            $users = $su->getUsers();
            $userIDs = $su->getUserIDs();
            //print_r( $users ); exit;
            
            require_once 'class.missionSheet.php';
            $m = new MissionSheet;
            if ( isset($_POST['from']) && isset($_POST['to']) ) {
                $from = $_POST['from'];
				$to = $_POST['to'];
            } else {
                // default to last 4 weeks
                $today = unixtojd();
                foreach ($dates as $idx => $date) {
                    if ($date['end'] >= $today) {
                        // we found current week
                        $to = $date['end'];
                        $from = $dates[$idx-3]['start']; // go back 3 weeks
                        break;
                    }
                }
            }
            
            //get image info
            $sql = "select school_logo_id from schools where school_id = " . $id;
            $res = mysql_query( $sql );
            $r = mysql_fetch_assoc( $res );

            $reports = [];
            foreach ( $dates as $date ) {
                if ( $date['start'] >= $from && $date['end'] <= $to ) $reports[] = $date;
            }
            // echo "<pre>"; print_r( $reports ); echo "</pre>"; exit;

            //create list
            $commanders = array();
            foreach ( $users as $grade => $names ) {                                    
                //get commander info
                //parse $grade into grade/sub
                $subExists = strpos( $grade, '-' );
                if ( $subExists ) {
                    $class = substr( $grade, 0, $subExists );
                    $sub = substr( $grade, $subExists+1 );
                    //echo $class . " " . $sub;
                    $sql = "select class_teacher from classes where class_grade = '$class' and class_sub = '$sub' and school_id = $id";
                } else {
                    $sql = "select class_teacher from classes where class_grade = '$grade' and school_id = $id";
                }
                $res = mysql_query( $sql );
                $teacher = mysql_fetch_assoc( $res );
                $commanders[$grade] = $teacher['class_teacher'];
            ?>
                <br />
                <div class='schoolInfo'>
                    <?=!is_null($r['school_logo_id']) ? linkImgFile($r['school_logo_id'], NULL, '50') : ''?>
                    Base: <?=$school?><br />
                    Commander: <?=$teacher['class_teacher']?><br />
                    Platoon: <?=$grade?><br /> 
                    <br />                    
                </div>
                <div style='clear:both'></div>
                
                <table>
                    <tr>
                        <th class="student">Student</th>
                        <?
                        foreach ( $reports as $report ) {
                            echo "<th class='week'>" . $report['name'] . "</th>";
                        }
                        ?>
                    </tr>
                    <? 
                    //variable to index into userids array
                    $i = 0;
                    $total = array();
                    //variable to hold number of students in class
                    $students = 0;
                    
                    foreach ( $names as $user ) {                       
                        echo "<tr><td>" . $user . "</td>";
                        $students++;
                        foreach ( $reports as $report ) {
                            if ( $m->marked( $userIDs[$grade][$i], $report ) ) {
                                echo "<td>&#10004;</td>";
                                if ( isset( $total[$report['name']] ) )
                                    $total[$report['name']]++;
                                else 
                                    $total[$report['name']] = 1;
                                if ( isset( $grandTotals[$school][$grade][$report['name']] ) )
                                    $grandTotals[$school][$grade][$report['name']]++;
                                else 
                                    $grandTotals[$school][$grade][$report['name']] = 1;
                            } else {
                                echo "<td>&nbsp;</td>";
                                if ( !isset( $total[$report['name']] ) ) {
                                    $total[$report['name']] = 0;
                                }
                            }
                        }
                        echo "</tr>";
                        $i++;
                    }
                    
                    echo "<tr><td>Total Completed</td>";
                    foreach ( $reports as $report ) {
                        echo "<td>" . $total[$report['name']] . "/" . $students . "</td>";
                    }
                    echo "</tr>";
                    $studentTotals[$school][$grade] = $students;
                    ?>
                </table>
                <br />
                <div class='page-break'></div>
            <?
            }
        }
		
        if ( !empty( $grandTotals ) ) {
            echo "<h2>Totals</h2>";
            foreach ( $grandTotals as $school => $info ) {
                echo "<div class='schoolInfo'>" . $school;
				echo !is_null($r['school_logo_id']) ? linkImgFile($r['school_logo_id'], NULL, '50') : '';
				echo "</div><br /><br /><br />"; 
                echo "<table>";
                echo "<tr><th>Grade</th><th>Commander</th>";
                foreach ( $reports as $report ) {
                    echo "<th>" . $report . "</th>";
                } 
				echo "<th>Total</th>";
                echo "</tr>";
                foreach ( $info as $grade => $totals ) {
                    echo "<tr><td>" . $grade . "</td><td>" . $commanders[$grade] . "</td>";
					$classTotals = 0; 
					foreach ( $reports as $report ) {
						if ( isset( $totals[$report['name']] ) ) {
                        	echo "<td>" . $totals[$report['name']] . "/" . $studentTotals[$school][$grade] . "</td>";
							$classTotals += $totals[$report['name']];
						} else {
							echo "<td>0/" . $studentTotals[$school][$grade] . "</td>";
						}
                    }
					echo "<td>" . $classTotals . "/" . $studentTotals[$school][$grade] * 10 . "</td>";
                    echo "</tr>";
                }
                echo "</table><br />";
            }
            echo "<div class='page-break'></div>";
        }                  
        ?>
    </body>
</html>