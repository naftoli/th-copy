<?
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
            
            function checkDates() { 
                if ( $(".radio:checked").val() ) {
                    return true;
                } else {
                    var start = $("#from").val();
                    var end = $("#to").val();
                    var dif = end - start;

                    if ( dif < 6 ) {
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
        <h1 class="no-print">Teacher's Mission Checklist</h1>
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
                        From beginning of: <select name='from' id='from'>
                        <?
                        $today = unixtojd();
                        echo $today;
                        foreach ($dates as $date) {
                            echo "<option value='" . $date['start'];
                            if ( $today >= $date['start'] && $today <= $date['end'] ) 
                                echo "' selected>";
                            else 
                                echo "'>";
                            echo $date['name'] . "</option>";
                        }
                        ?>
                        </select><br />
                        Until end of: <select name='to' id='to'>
                        <? 
                        $today += 70;
                        foreach ($dates as $date) {
                            echo "<option value='" . $date['end'];
                            if ( $today >= $date['start'] && $today <= $date['end'] ) 
                                echo "' selected>";
                            else 
                                echo "'>";
                            echo $date['name'] . "</option>";
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
                            From beginning of: <select name='from' id='from'>
                            <?
                            $today = unixtojd();
                            foreach ($dates as $date) {
                                echo "<option value='" . $date['start'];
                                if ( $today >= $date['start'] && $today <= $date['end'] ) 
                                    echo "' selected>";
                                else 
                                    echo "'>";
                                echo $date['name'] . "</option>";
                            }
                            ?>
                            </select><br />
                            Until end of: <select name='to' id='to'>
                            <? 
                            foreach ($dates as $date) {
                                echo "<option value='" . $date['end'];
                                if ( $today >= $date['start'] && $today <= $date['end'] ) 
                                    echo "' selected>";
                                else 
                                    echo "'>";
                                echo $date['name'] . "</option>";
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
            }
        }  
        ?>
    </body>
</html>