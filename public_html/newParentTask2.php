<?
$admin_auth = array('user'); 
require('header.php');

include("camps/includes/classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \camps\classes\admin($row);
$admin->get_markable_children();

$parshos = array();
$sql1 = "select * from parshos where start >= " . (unixtojd() - 7) . " and end <= 2456914";
$result1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($result1)) {
    $parshos[$row1['name']] = array( 
        'start' => $row1['start'], 
        'end'   => $row1['end']
    );
}

require_once 'class.missionsDone.php';
$missions = MissionsDone::getAllMissions();

$sql2 = "select label_id, label_name from labels where label_id >= 30 order by label_name";
$result2 = mysql_query($sql2);
$labels = array();
while ($row2 = mysql_fetch_assoc($result2)) {
    $labels[$row2['label_id']] = $row2['label_name'];
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
            	
            	//$("ul.tabs").tabs("div.module");
            	
                $(".children").hide();
                
                $("#allChildren").click( function() {
                    if ($("#allChildren input").is(":checked")) {
                        $(".children input").attr('checked', false);
                        $(".children").hide();
                    } else { 
                        $(".children").show();
                    }
                });
                
                $("#toggleChildren").click( function() {
                    $(".grades input").trigger('click');
                }); 
                
                $("#toggleParshos").click( function() {
                    $(".parshos input").trigger('click');
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
                    if ($("#category").val() == '') {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You have not entered a category.";
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
                    if (!$('.mission').is(':checked')) {
                        if (errors != '') 
                            errors += '\n';
                        errors += 'You must check off at least one parsha.';
                    }
                    if (!$("#allChildren input").is(":checked") && !$(".children input").is(":checked")) {
                        if (errors != '') 
                            errors += '\n';
                        errors += "You must choose at least one child.";
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
        
        <script type="text/javascript" src="scripts/jeditable.mini.js"></script>
        
        <h1>Create a New Task</h1>
        
        <?
        if (isset($_GET['msg'])) {
            echo "<div style='color: red'>" . urldecode($_GET['msg']) . "</div><br />";
        }
        ?>
        
		        <p>
		            <i>Please Note: All fields are mandatory</i>
		        </p>
		        
		        <form action='createNewParentTask.php' method='post'>            
		            <p>
		                Campaign:<br />
		                <select name='campaign' id='campaign'>
		                    <option value='0'>Choose Campaign</option>
		                    <?
		                    foreach ($missions as $id => $mission) {
		                        echo "<option value='$id'>" . $mission . "</option>";
		                    }
		                    ?>
		                </select>
		            </p>
		
		            <p>
		                Add Task: (Example: 'I did my quota of volunteer hours' or 'I helped out in my local old age home').<br />
		                <input type='text' name='name' size='80' id='name' />
		            </p>
		            
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
		            
		            <input type='hidden' name='mandatory' value='0' />
		            
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
		                    Child(ren)
		                </legend> 
		                <div id="allChildren">
		                    <input type="checkbox" name="children[]" value="0" checked /> All Children<br /><br />
		                </div>
		                <?
		                $num = count($admin->children);
		                $cutoff = (int)($num / 5);
		                $i = 0;
		                echo "<div class='children'>";
		                foreach ($admin->children as $child) {
		                    if (++$i > $cutoff) {
		                        echo "</div><div class='children'>";
		                        $i = 1;
		                    }
		                    echo "<input type='checkbox' name='children[]' value='" . $child->user_id . "' />" . 
		                            $child->first . "<br />";
		                }
		                echo "</div>";
		                ?>
		            </fieldset>
		
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
