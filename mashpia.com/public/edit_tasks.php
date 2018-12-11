<?
$admin_auth = array('school');
require_once 'header.php';
require_once 'calendar.php';

if ( isset( $_POST['submit'] ) ) {
    echo "<pre>";
    //print_r( $_POST );
    echo "</pre>";

    $school = $_POST['school'];
    $class = isset( $_POST['class'] ) ? $_POST['class'] : 0;
    $user = isset( $_POST['user'] ) ? $_POST['user'] : 0;
    $campaign = $_POST['campaign'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    require_once 'class.editTasks.php';
	$e = new EditTasks($school);
	$tasks = $e->getTasks();
	print_r($tasks);
}

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Edit Tasks</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="icalendar.js"></script>
        <link rel="stylesheet" href="customization.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css" />
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script type="text/javascript">
        $(function() {
            $("#school").change( function() { 
                $("#class").empty();
                $("#user").empty();
                $("#user").append("<option value='-1'>All</option>");
                var id = $(this).val();
                $.getJSON('ajax/getClasses.php', {id : id}, function( data ) {
                    $("#class").append("<option value='-1'>All</option>");
                    $.each( data, function( key, val ) { 
                        var selected = '';
                        <? if ( isset( $_POST['class'] ) ) { ?>
                        if ( key == <?=$_POST['class']?> ) selected = "selected='selected'"; 
                        <? } ?>
                        var str = "<option value=" + key + ' ' + selected + ">" + val + "</option>";
                        $("#class").append( str );
                        $("#class").removeAttr('disabled');
                        if ( selected != '' )
                            $("#class").trigger('change');
                    });
                });
                $.getJSON('ajax/getCampaigns.php', {id : id}, function(data) {
                    $.each(data, function(key, val) {
                        var selected = '';
                        <? if ( isset( $_POST['campaign'] ) ) { ?>
                        if ( key == <?=$_POST['campaign']?> ) selected = "selected='selected'"; 
                        <? } ?>
                        var str = "<option value=" + key + ' ' + selected + ">" + val + "</option>";
                        $("#campaign").append(str);
                    });
                    $("#campaign").removeAttr('disabled');
                });
            });

            $("#class").change( function() {
                $("#user").empty();
                var id = $(this).val();
                $.getJSON('ajax/getUsers.php', {id : id}, function( data ) {
                    $("#user").append("<option value='-1'>All</option>");
                    $.each( data, function( key, val ) { 
                        var selected = '';
                        <? if ( isset( $_POST['user'] ) ) { ?>
                        if ( key == <?=$_POST['user']?> ) selected = "selected='selected'"; 
                        <? } ?>
                        var str = "<option value=" + key + ' ' + selected + ">" + val + "</option>";
                        $("#user").append( str );
                        $("#user").removeAttr('disabled');
                    });
                });
            });
            
            $("#submit").click( function() {
                if ($("#campaign").val() == -1) {
                    alert("Please choose a campaign!");
                    return false;
                }
                if ( $("#start_date").val() > $("#end_date").val() ) {
                    alert("End date can not be before Start date!");
                    return false;
                }
            });
            
            <?
            if ((isset($_POST['school'] ) && $_POST['school'] != -1) || count($schools) == 1) {
                echo "$('#school').trigger('change');";
            }
            ?>
        });  
        </script>
    </head>
    
    <body>
        <? require 'admin_header.php'; ?>
        <h1>Edit Tasks</h1>
        
        <form method="post" action="edit_tasks.php">
            <table>
                <tr>
                <?
                if ( count( $schools ) > 1 ) {
                    echo "<td>School:</td>"; 
                    echo "<td><select name='school' id='school'>";
                    echo "<option value='-1'>All</option>";
                    foreach ( $schools as $id => $name ) {
                        echo "<option value=$id";
                        if ( isset( $_POST['school'] ) && $_POST['school'] == $id ) echo " selected";
                        echo ">$name</option>";
                    } 
                    echo "</select></td>";
                    echo "</tr><tr>";
                    echo "<td>Class:</td>";
                    echo "<td><select name='class' disabled id='class'>";
                    echo "<option value='-1'>All</option>";
                    echo "</select></td>";
                } else if ( count( $schools ) == 1 ) {
                    $school_id = 0; 
                    echo "<td>School:</td>"; 
                    echo "<td><select name='school' disabled id='school'>";
                    foreach ( $schools as $id => $name ) {
                        echo "<option value=$id>$name</option>";
                        $school_id = $id;
                    } 
                    echo "</select></td>";
                    echo "</tr><tr>";
                    echo "<td>Class:</td>";
                    echo "<td><select name='class' id='class'>";
                    echo "<option value='-1'>All</option>";
                    require_once 'class.schoolClasses.php';
                    $sc = new SchoolClasses( $school_id );
                    $classes = $sc->getClasses();
                    foreach ( $classes as $class ) {
                        echo "<option value=" . $class['class_id'] . ">" . $class['class_grade'] . 
                                (empty( $class['class_sub'] ) ? '' : '-' . $class['class_sub']) . "</option>";
                    }						
                    echo "</select></td>";
                    echo "<input type='hidden' name='school' value=" . $school_id . " />";
                }
                ?> 
                </tr>
                <tr>
                    <td>Child:</td>
                    <td>
                        <select name='user' disabled id='user'>
                            <option value='-1'>All</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Campaign:</td>
                    <td>
                        <select name="campaign" id="campaign" disabled>
                            <option value="-1">Choose Campaign</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <span class='dates'>
                        <INPUT type="hidden" id="start_date" name="start_date" value="<?= isset($start_date) ? $start_date : 2456530?>">
                        <LABEL>
                            From: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew(isset($start_date) ? $start_date : 2456530))?>" onClick="getDate(this.form, 'start_date', true);"/>
                        </LABEL>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <INPUT type="hidden" id="end_date" name="end_date" value="<?= isset($end_date) ? $end_date : 2456914?>">
                        <LABEL>
                            To: &nbsp;&nbsp;&nbsp;
                            <INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew(isset($end_date) ? $end_date : 2456914))?>" onClick="getDate(this.form, 'end_date', true);"/>
                        </LABEL>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="submit" id="submit" value="Submit" /></td>
                </tr>
            </table>
        </form>
        
        <br />
        <div class='customize'>
            
        </div>
        
        <br />
        <input type='submit' id='save' value='Save' />
    </body>
</html>
