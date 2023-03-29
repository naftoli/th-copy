<?
$admin_auth = array('school'); 
require('header.php');
require_once('calendar.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Missions Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui-1.9.2.custom.min.js"></script>
        <style type='text/css'>
            p {
                font-size: 12px;
            }
            table {
                font-size: 11px;
            }
            th, td {
                padding: 3px 10px;
            }
            .missionSelection {
                width: 30%;
                float: left;
                line-height: 1.5;
                margin-top: 10px;
            }
            .classSelection {
                width: 25%;
                float: left;
                line-height: 1.5;
                margin-top: 10px;
            }
            fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            .page-break {
                page-break-after: always;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
            .totals {
                border-top: 1px dashed purple;
                border-bottom: 1px dashed purple;
            }
            .classes {
                margin: auto;
            }
        </style>
        <script type="text/javascript">
            $( function() {
                $(".checkall").click( function() {
                    $(".mission").attr("checked", true);
                });
                
                $(".uncheckall").click( function() {
                    $(".mission").attr("checked", false);
                });
                
                $(".checkallClasses").click( function() {
                    $(".class").attr("checked", true);
                });
                $(".uncheckallClasses").click( function() {
                    $(".class").attr("checked", false);
                });
                
                $(".class").attr("checked", true);
                                
                $(".dates").hide(); 
                             
                $(".alldates").click( function() {
                    if ( !$(this).is(":checked") ) {
                        $(".dates").show();
                    } else {
                        $(".dates").hide();
                    }
                });
                
            });
        </script> 
        <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>          
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Missions Report</h1>
        <? 
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $userMissions = array();      
        require_once 'class.missionsDone.php';
        $missions = MissionsDone::getAllMissions();
        
        if ( isset( $_POST['submit'] ) ) {
            if ( !isset( $_POST['missions'] ) ) {
                echo "Please go back and choose at least one mission.";
                exit;
            }
            
            //print_r( $_POST );            
            $missionsPosted = $_POST['missions'];
            $start_date = empty( $_POST['start_date'] ) ? null : $_POST['start_date'];
            $end_date = empty( $_POST['end_date'] ) ? null : $_POST['end_date'];
            $start_he = empty( $_POST['start_date_disp'] ) ? null : $_POST['start_date_disp'];
            $end_he = empty( $_POST['end_date_disp'] ) ? null : $_POST['end_date_disp'];
            
            if ( $start_date > $end_date ) {
                echo "Your start date can not be after your end date. Please go back and try again.";
                exit;
            }
             
            foreach ( $schools as $id => $school ) {
                $m = new MissionsDone( $id );
                if ( isset( $start_date ) ) 
                    $m->setDates( $start_date, $end_date );
                if ( isset( $_POST['classes'] ) ) 
                    $m->setClasses( $_POST['classes'] );
                $m->setMissionsDone( $missionsPosted, [], false, true );
                $userMissions[$id] = $m->getMissionsDone();
            }
            
            echo "<div align='center' class='no-print'>";
            echo "<input type='button' value='Print' onclick='window.print()' />";
            echo "</div>";
            
            //initialize totals arrays
            $totals = array();
            $grandTotals = array();
            foreach ( $missionsPosted as $m ) {
                $grandTotals[$missions[$m]['name']] = 0;
            }
                        
            foreach ( $userMissions as $school => $users ) {
                echo "<h2>" . $schools[$school] . "</h2>";
                if ( isset( $start_he ) )
                    echo "<p>Missions earned between " . $start_he . " and " . $end_he . "</p>";
                echo "<table>";
                echo "<tr><th>School</th><th>Grade</th><th>Student</th>";
                foreach ( $missionsPosted as $mp ) {
                    echo "<th>" . $missions[$mp]['name'] . "</th>";
                    //initialize totals for this school
                    $totals[$school][$missions[$mp]['name']] = 0;
                }
                echo "</tr>";
                foreach ( $users as $class => $user ) {
                    foreach ( $user as $name => $mission ) {
                        echo "<tr><td>" . $schools[$school] . "</td><td>" . $class . "</td><td>" . $name . "</td>";
                        foreach ( $missionsPosted as $m ) {
                            if ( !isset( $mission[$missions[$m]['name']] ) ) {
                                echo "<td>0</td>";                            
                            } else {
                                echo  "<td>" . number_format( $mission[$missions[$m]['name']] ) . "</td>";
                                //add to totals per school
                                $totals[$school][$missions[$m]['name']] += $mission[$missions[$m]['name']];
                                //add to grand totals per campaign
                                $grandTotals[$missions[$m]['name']] += $mission[$missions[$m]['name']];                                 
                            }
                        }
                        echo "</tr>";
                    }
                }
                
                echo "<tr class='totals'><td colspan='2' align='right'>Totals:</td>";
                foreach ( $missionsPosted as $m ) {
                    echo "<td>" . number_format( $totals[$school][$missions[$m]['name']] ) . "</td>";
                }
                echo "</tr>";
                  
                 
                echo "</table><br />";
                echo "<div class='page-break'></div>";
            }
            
            echo "<h2>Totals</h2>";
            echo "<p>Missions earned between " . $start_he . " and " . $end_he . "</p>";
            echo "<table><tr><th>School</th>";
            foreach ( $missionsPosted as $m ) {
                echo "<th>" . $missions[$m]['name'] . "</th>";
            }
            echo "</tr>";
            foreach ( $totals as $school => $info ) {
                echo "<tr><td>" . $schools[$school] . "</td>";
                foreach ( $missionsPosted as $m ) {
                    echo "<td>" . number_format( $info[$missions[$m]['name']] ) . "</td>";
                }
                echo "</tr>";
            }
            echo "<tr class='totals'><td align='right'>Grand Totals:</td>";
            foreach ( $missionsPosted as $m ) {
                echo "<td>" . number_format( $grandTotals[$missions[$m]['name']] ) . "</td>";
            }
            echo "</tr></table>"; 
        } else {          
            ?>
            <form action="missions_report.php" method="post">
                Check off the missions that you would like for the report:<br /><br />
                <fieldset>
                    <legend>
                        Select Missions
                    </legend>
                <div align='center'>
                    <input type='button' class='checkall' value="Check All" />
                    <input type='button' class='uncheckall' value="Uncheck All" />
                </div>
                <?
                //calculate how many missions will be showing and show into two columns
                $numMissions = count( $missions );
                if ( $numMissions > 1 ) {
                    $middle = ceil( $numMissions / 3.0 );
                    echo "<div class='missionSelection'>";
                    $i = 0;
                    foreach ( $missions as $id => $mission ) {
                        if ( $i++ == $middle ) {
                            $middle *= 2;
                            echo "</div><div class='missionSelection'>";
                        }
                        echo "<input type='checkbox' class='mission' name='missions[]' value='" . $id . "' />" . $mission['name'] .
                            " <span style='font-size: 12px'>(" . $mission['type'] . ")</span><br />";
                    }
                    echo "</div>";
                }
                ?>
                <div style="clear: both"></div>
                </fieldset>
                <br />
                <fieldset>
                    <legend>
                        Select Dates
                    </legend>
                    <input type='checkbox' name='dates' value='all' checked="checked" class='alldates' />
                    All To Date
                    &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span class='dates'>
                        <INPUT type="hidden" name="start_date" value="">
                        <LABEL>
                            From: 
                            <INPUT type="text" name="start_date_disp" READONLY value="" onClick="getDate(this.form, 'start_date', true);"/>
                        </LABEL>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <INPUT type="hidden" name="end_date" value="">
                        <LABEL>
                            To: 
                            <INPUT type="text" name="end_date_disp" READONLY value="" onClick="getDate(this.form, 'end_date', true);"/>
                        </LABEL>
                    </span>
                </fieldset>
                <? if ( count( $schools ) == 1 ) { ?>
                    <br />
                    <fieldset>
                        <legend>
                            Select Class(es)
                        </legend>
                        <div align='center'>
                            <input type='button' class='checkallClasses' value="Check All" />
                            <input type='button' class='uncheckallClasses' value="Uncheck All" />
                        </div>
                        <?
                        //get classes
                        $school_id = null;
                        $classes = array();
                        foreach ( $schools as $id => $school ) {
                            $school_id = $id;
                        }
                        $sql = "select class_id, class_grade, class_sub 
                                from classes 
                                where school_id = " . $school_id . " 
                                and class_era = 0 
                                order by class_grade, class_sub";
                        $result = mysql_query( $sql );
                        while ( $row = mysql_fetch_assoc( $result ) ) {
                            $classes[] = $row;
                        }
                        
                        //calculate how many classes will be showing and show into 4 columns
                        $numClasses = count( $classes );
                        if ( $numClasses > 1 ) {
                            $column = ceil( $numClasses / 4.0 );
                            $newColumn = $column;
                            echo "<div class='classSelection'>";
                            $i = 0;
                            foreach ( $classes as $class ) {
                                if ( $i++ == $newColumn ) {
                                    $newColumn += $column;
                                    echo "</div><div class='classSelection'>";
                                }
                                echo "<input type='checkbox' class='class' name='classes[]' value=" . $class['class_id'] . " /> " . 
                                    $class['class_grade'] . ( empty( $class['class_sub'] ) ? '' : "-" . $class['class_sub'] ) . "<br />";
                            }
                            echo "</div>";
                        }
                        ?>
                    </fieldset>
                <? } ?>
                <div align='center'>
                    <br /><input type="submit" name="submit" value="Submit" />
                </div>
            </form>
            <?          
       }
    ?>
    </body>
</html>