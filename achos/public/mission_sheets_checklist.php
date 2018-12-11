<?
$admin_auth = array('school'); 
require('header.php');
require_once('file_save.php'); 

//get default dates
$dates = array();
$sql = "SELECT * FROM reports 
        WHERE report_type='mission_cover_sheet' 
        AND visibility != 'none' 
        and start_date >= 2456530 
        and end_date <= 2456914    
        ORDER BY start_date";   
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $dates[] = $row;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mission Sheets Checklist</title>
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
            
            function checkDates() { 
                if ( $(".radio:checked").val() ) {
                    return true;
                } else {
                    var start = $("#from").val();
                    var end = $("#to").val();
                    var dif = end - start;
                    if ( dif > 69 ) {
                        alert("You cannot choose more than a 10 week period.");
                        return false;
                    } else if ( dif < 6 ) {
                        alert("End week must be after start week!");
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        </script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Mission Sheets Checklist</h1>
        <? if ( isset( $_POST['class'] ) || isset( $_POST['weeks'] ) || isset( $_POST['from'] ) ) { ?>
        <div class='no-print' align='center'>
            <input type='button' name='print' value='Print' onclick='window.print()' />
        </div>
        <? 
        }
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
            
            if ( !isset( $_POST['class'] ) && count( $schools ) == 1 ) {
                //show classes selection form
                ?>
                <form action="mission_sheets_checklist.php" method="post" onsubmit="return checkDates()">
                    <fieldset>
                        <legend>
                            Select Class(es)
                        </legend>
                        <div align="center">
                            <input type='button' class='checkallClasses' value="Check All" />
                            <input type='button' class='uncheckallClasses' value="Uncheck All" />
                        </div>
                        <?
                        //calculate how many classes will be showing and show into four columns
                        $numGrades = count( $classes );
                        if ( $numGrades > 1 ) {
                            $middle = (int)ceil( $numGrades / 4.0 );
                            $num = $middle;
                            echo "<div class='gradeSelection'>";
                            $i = 0;
                            foreach ( $classes as $class ) {
                                if ( $i++ == $num ) {
                                    $num += $middle;
                                    echo "</div><div class='gradeSelection'>";
                                }
                                echo "<input type='checkbox' class='class' name='class[]' value=" . $class['class_id'] . " />";
                                $grade = $class['class_grade'] . ( empty( $class['class_sub'] ) ? '' : '-' . $class['class_sub'] );
                                echo " " . $grade . "<br />";
                            }
                            echo "</div>";
                        }
                        ?>
                    </fieldset>
                    <br />
                    <fieldset>
                        <legend>Select Week(s)</legend>
                        <p><i>Option #1 - Choose from a predefined set of weeks</i></p>
                        <p>
                            <input type="radio" name="weeks" value="set1" class='radio' />1st 10 weeks of year (פרשת כי תבוא - חיי שרה)<br />
                            <input type="radio" name="weeks" value="set2" class='radio' />2nd 10 weeks of year (פרשת תולדות - בא)<br />
                            <input type="radio" name="weeks" value="set3" class='radio' />3rd 10 weeks of year (פרשת בשלח - פסח)<br />
                            <input type="radio" name="weeks" value="set4" class='radio' />4th 10 weeks of year (פרשת שמיני - קרח)<br />
                            <input type="radio" name="weeks" value="set5" class='radio' />5th 10 weeks of year (פרשת חקת - כי תצא)
                        </p>
                        <hr />
                        <p><i>Option #2 - Choose your own weeks<br />
                            Please note: You can only choose up to a 10 week period.</i></p>
                        From beginning of: <select name='from' id='from'>
                        <?
                        $today = unixtojd();
                        echo $today;
                        foreach ($dates as $date) {
                            echo "<option value='" . $date['start_date'];
                            if ( $today >= $date['start_date'] && $today <= $date['end_date'] ) 
                                echo "' selected>";
                            else 
                                echo "'>";
                            echo $date['report_name'] . "</option>";
                        }
                        ?>
                        </select><br />
                        Until end of: <select name='to' id='to'>
                        <? 
                        $today += 70;
                        foreach ($dates as $date) {
                            echo "<option value='" . $date['end_date'];
                            if ( $today >= $date['start_date'] && $today <= $date['end_date'] ) 
                                echo "' selected>";
                            else 
                                echo "'>";
                            echo $date['report_name'] . "</option>";
                        }
                        ?>
                        </select><br />
                    </fieldset>
                    <br />
                    <div align='center'>
                        <input type="hidden" name="school" value="<?=$id?>" />
                        <input type="submit" name="submit" value="Submit" />
                    </div>
                </form>
                <? 
            } else if ( count( $schools ) > 1 && ( !isset( $_POST['weeks'] ) && !isset( $_POST['from'] ) ) ) {
                 ?> 
                 <form action="mission_sheets_checklist.php" method="post" onsubmit="return checkDates()">                    
                     <fieldset>
                            <legend>Select Week(s)</legend>
                            <p><i>Option #1 - Choose from a predefined set of weeks</i></p>
                            <p>
                                <input type="radio" name="weeks" value="set1" class='radio' />1st 10 weeks of year (פרשת כי תבוא - חיי שרה)<br />
                                <input type="radio" name="weeks" value="set2" class='radio' />2nd 10 weeks of year (פרשת תולדות - בא)<br />
                                <input type="radio" name="weeks" value="set3" class='radio' />3rd 10 weeks of year (פרשת בשלח - פסח)<br />
                                <input type="radio" name="weeks" value="set4" class='radio' />4th 10 weeks of year (פרשת שמיני - קרח)<br />
                                <input type="radio" name="weeks" value="set5" class='radio' />5th 10 weeks of year (פרשת חקת - כי תצא)
                            </p>
                            <hr />
                            <p><i>Option #2 - Choose your own weeks<br />
                                Please note: You can only choose up to a 10 week period.</i></p>
                            From beginning of: <select name='from' id='from'>
                            <?
                            $today = unixtojd();
                            foreach ($dates as $date) {
                                echo "<option value='" . $date['start_date'];
                                if ( $today >= $date['start_date'] && $today <= $date['end_date'] ) 
                                    echo "' selected>";
                                else 
                                    echo "'>";
                                echo $date['report_name'] . "</option>";
                            }
                            ?>
                            </select><br />
                            Until end of: <select name='to' id='to'>
                            <? 
                            //$today += 70;
                            foreach ($dates as $date) {
                                echo "<option value='" . $date['end_date'];
                                if ( $today >= $date['start_date'] && $today <= $date['end_date'] ) 
                                    echo "' selected>";
                                else 
                                    echo "'>";
                                echo $date['report_name'] . "</option>";
                            }
                            ?>
                            </select><br />
                        </fieldset>
                        <br />
                        <div align='center'>
                            <input type="hidden" name="school" value="<?=$id?>" />
                            <input type="submit" name="submit" value="Submit" />
                        </div>
                    </form>
                <?
                break;
            } else {
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
                                
                //get weeks selected
                if ( isset( $_POST['weeks'] ) ) {
                    switch ( $_POST['weeks'] ) {
                        case 'set1': 
                            $from = 2456173;
                            $to = 2456242;
                            break;
                        case 'set2': 
                            $from = 2456243;
                            $to = 2456312;
                            break;
                        case 'set3':
                            $from = 2456313;
                            $to = 2456382;
                            break;
                        case 'set4':
                            $from = 2456383;
                            $to = 2456452;
                            break;
                        case 'set5':
                            $from = 2456453;
                            $to = 2456522;
                            break;
                        default:
                            break;
                    }
                } else {
                    $from = $_POST['from'];
                    $to = $_POST['to'];
                }
                
                //get image info
                $sql = "select school_logo_id from schools where school_id = " . $id;
                $res = mysql_query( $sql );
                $r = mysql_fetch_assoc( $res );
                
                $reports = array();
                $sql = "select report_id, report_name from reports 
                        where start_date >= $from 
                        and end_date <= $to 
                        order by start_date";
                //echo $sql;
                $result = mysql_query( $sql );
                while ( $row = mysql_fetch_assoc( $result ) ) {
                    $reports[$row['report_id']] = $row['report_name'];
                }

                //create list
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
                                echo "<th class='week'>" . $report . "</th>";
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
                            foreach ( $reports as $rid => $report ) {
                                if ( $m->marked( $userIDs[$grade][$i], $rid ) ) {
                                    echo "<td>&#10004;</td>";
                                    if ( isset( $total[$report] ) )
                                        $total[$report]++;
                                    else 
                                        $total[$report] = 1;
                                    if ( isset( $grandTotals[$school][$grade][$report] ) )
                                        $grandTotals[$school][$grade][$report]++;
                                    else 
                                        $grandTotals[$school][$grade][$report] = 1;
                                } else {
                                    echo "<td>&nbsp;</td>";
                                    if ( !isset( $total[$report] ) ) {
                                        $total[$report] = 0;
                                    }
                                }
                            }
                            echo "</tr>";
                            $i++;
                        }
                        
                        echo "<tr><td>Total Completed</td>";
                        foreach ( $reports as $report ) {
                            echo "<td>" . $total[$report] . "/" . $students . "</td>";
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
                    echo "<div class='schoolInfo'>" . $school . "</div><br />"; 
                    echo "<table>";
                    echo "<tr><th>Grade</th>";
                    foreach ( $reports as $report ) {
                        echo "<th>" . $report . "</th>";
                    } 
                    echo "</tr>";
                    foreach ( $info as $grade => $totals ) {
                        echo "<tr><td>" . $grade . "</td>"; 
                        foreach ( $totals as $report => $total ) {
                            echo "<td>" . $total . "/" . $studentTotals[$school][$grade] . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table><br />";
                }
                echo "<div class='page-break'></div>";
            }                  
        }  
        ?>
    </body>
</html>