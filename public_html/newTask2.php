<?
$admin_auth = array('school'); 
require('header.php');

$parshos = array();
$sql1 = "select * from parshos where start >= " . (unixtojd() - 7) . " and end <= 2456914";
$result1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($result1)) {
    $parshos[$row1['name']] = array( 
        'start' => $row1['start'], 
        'end'   => $row1['end']
    );
}

require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once 'class.missionsDone.php';
$missions = MissionsDone::getAllMissions();

$sql2 = "select label_id, label_name from labels where label_id in (30, 32, 38, 33, 40, 36)";
$result2 = mysql_query($sql2);
$labels = array();
$temp = array();
$indexes = array(30, 32, 38, 33, 40, 36);
while ($row2 = mysql_fetch_assoc($result2)) {
    $temp[$row2['label_id']] = $row2['label_name'];
}
foreach ($indexes as $index) {
    $labels[$index] = $temp[$index];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Create a New Task</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="scripts/jquery-ui-1.9.2.custom.min.css" />
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery-ui-1.9.2.custom.min.js"></script>

        <link rel="stylesheet" href="newTask.css" />
        <script type='text/javascript'>
            $(function() {
                $(".grades").hide();
                
                $("#allGrades").click( function() {
                    if ($("#allGrades input").is(":checked")) {
                        $(".grades input").attr('checked', false);
                        $(".grades").hide();
                    } else { 
                        $(".grades").show();
                    }
                });
                
                $("#toggleParshos").click( function() {
                    $(".parshos input").trigger('click');
                });
                
                $("#campaign").change( function() {
                    if ($(this).val() == '99') {
                        $("#opt").trigger('click');
                        $("#mandatory").hide();
                    } else {
                        $("#mand").trigger('click');
                        $("#mandatory").show();
                    }
                });
                
                $("#submit").click( function() {
                    var errors = '';
                    
                    if ($('#campaign').val() == 0) {
                        if (errors != '') 
                            errors += '\n';
                        errors += 'You must choose a campaign.';
                    }                    
                    if ($("#name").val() == '') {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You have not entered a task.";
                    }
                    if ($("#points").val() == '') {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You must enter a point value.";
                    }
                    if ($("#label").val() == 0 && $("#campaign").val() != 40) {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You must choose a label.";
                    }
                    if (!$("#allGrades input").is(":checked") && !$(".grades input").is(":checked")) {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You must choose a grade.";
                    }
                    if (!$('.mission').is(':checked')) {
                        if (errors != '') 
                            errors += '\n';
                        errors += 'You must check off at least one parsha.';
                    }
                    
                    if (errors != '') {
                        alert(errors);
                        return false;
                    }
                });
            });
        </script>
    </head>
    <body>
        <? include('admin_header.php'); ?>
        <h1>Create a New Task</h1>
        
        <?
        if ($admin_user['auth'] == 'super') {
        	echo "Superadmins cannot use this page.";
			exit;
        }
        if (isset($_GET['msg'])) {
            echo "<div style='color: red'>" . urldecode($_GET['msg']) . "</div><br />";
        }
        ?>
        
        <p>
            <i>Please Note: All fields are mandatory</i>
        </p>
        
        <form action='createNewTask.php' method='post'>
            <?
            if (count($schools) > 1) {
                echo "School:<br />";
                echo "<select name='school_id' id='school_id'>";
                $ids = array();
                foreach ($schools as $id => $school) {
                    $ids[] = $id;
                }
                echo "<option value='" . implode(',', $ids) . "'>All</option>";
                foreach ($schools as $id => $school) {
                    echo "<option value='$id'>$school</option>";
                }
                echo "</select>";
            } else {
                foreach ($schools as $id => $school) {
                    echo "<input type='hidden' name='school_id' 
                        value='$id' />";
                }
            }
            ?>
            
            <p>
                Add Task: (Example: 'I did my quota of volunteer hours' or 'I helped out in my local old age home').<br />
                <input type='text' name='name' size='80' id='name' />
            </p>
            <!--
            <p>
                Add Task Title: (Example: 'Chessed').<br />
                <input type='text' name='category' size='30' id='category' />
            </p>
            -->
            <p>
                Amount of points awarded for completing the task: <br />
                <select name="points" id="points">
                    <option value="1" selected="selected">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                </select>
            </p>
            
            <p>
                Campaign:<br />
                <select name='campaign' id='campaign'>
                    <option value='0'>Choose Campaign</option>
                    <? 
                    foreach ($missions as $id => $mission) {
                        if (in_array($id, array(1, 40, 94)))
                            continue;  
                        echo "<option value='$id'>" . $mission . "</option>";
                    }
                    ?>
                </select>
            </p>
            
            <p id="mandatory">
                This task should be:<br />
                <input type='radio' name='mandatory' id="mand" value='1' checked /> Mandatory <br />
                <input type='radio' name='mandatory' id="opt" value='0' /> Optional <br />
            </p>
            
            <p>
                Task Label for mission sheets:<br />
                (You must choose which label in order to know where to place it on the mission sheets.)<br />
                <select name='label' id='label'>
                    <option value="0" selected="selected">Choose a label</option>
                    <?
                    foreach ($labels as $id => $label) {
                        echo "<option value=" . $id . ">" . $label . (in_array($id, array(30,32,38)) ? " (Daily)" : "") . "</option>";
                    }
                    ?>
                </select>
            </p>

            <fieldset>
                <legend>
                    Grades
                </legend>
                <div id="allGrades">
                    <input type="checkbox" name="classes[]" value="0" checked /> All Grades<br /><br />
                </div>
                <? 
                $classes = array('Pre1a', '1', '2', '3', '4', '5', '6', '7', '8');
                $num = count($classes);
                $cutoff = (int)($num / 4);
                $i = 0;
                echo "<div class='grades'>";
                foreach ($classes as $class) {
                    if (++$i > $cutoff) {
                        echo "</div><div class='grades'>";
                        $i = 1;
                    }
                    echo "<input type='checkbox' name='classes[]' value='" . $class . "'>" . $class . "<br />";

                }
                echo "</div>"; 
                ?>
            </fieldset>
            
            <!--
            <fieldset>
                <legend>
                    Grades
                </legend>
                <div id="allGrades">
                    <input type="checkbox" name="class_grades[]" value="0" checked /> All Classes<br /><br />
                </div>
                <?
                /*
                $num = count($class_grades);
                $cutoff = (int)($num / 5 + 1);
                $i = 0;
                echo "<div class='grades'>";
                foreach ($class_grades as $class) {
                    if (++$i > $cutoff) {
                        echo "</div><div class='grades'>";
                        $i = 1;
                    }
                    echo "<input type='checkbox' name='class_grades[]' value='" . $class . "'>" . $class . "<br />";
                }
                echo "</div>"; 
                 * 
                 */
                ?>
            </fieldset>
            -->

            <fieldset>
                <legend>
                    Parshos
                </legend>
                <?
                $num = count($parshos);
                $cutoff = (int)($num / 4 + 1);
                $i = 0;
                echo "<div class='parshos'>";
                foreach ($parshos as $name => $dates) {                           
                    if (++$i > $cutoff) {
                        echo "</div><div class='parshos'>";
                        $i = 1;
                    }
                    echo "<input type='checkbox' class='mission' name='missions[]' value='" . $name . "' checked>" . $name . "<br />";
                }
                echo "</div>";
                ?>
                <div style='clear: both'></div>
                <div align='center'>
                    <br /><input type='button' id='toggleParshos' value='Toggle' />
                </div>
            </fieldset>
            
            <br />
            <p align='center'>
                <input type='submit' name='submit' id='submit' value='Create Task' />
            </p>
            
        </form>
        
    </body>
</html>
