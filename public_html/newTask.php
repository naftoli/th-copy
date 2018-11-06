<?
$admin_auth = array('school'); 
require('header.php');

// get current working year
require_once 'class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$parshos = array();
$sql1 = "select * from parshos where year = " . $year . " and start >= " . unixtojd();
$result1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($result1)) {
    $parshos[][$row1['name']] = $row1['id'];
}
// get the schools for the user (chayolei for supers and account tied for other admins)
require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools(); //gets an array of all the school names with their id as the keys

require_once 'class.missionsDone.php';
$missions = MissionsDone::getAllMissions(); // gets array of all mission names with their id as the keys
// select all the public labels
$sql2 = "SELECT label_id, label_name FROM labels WHERE active = 1 ORDER BY label_name";
$result2 = mysql_query($sql2);
$labels = array();
//$temp = array();
//$indexes = array(30, 32, 38, 33, 40, 36);
while ($row2 = mysql_fetch_assoc($result2)) {
    $labels[$row2['label_id']] = $row2['label_name']; // and add them to the labels array by key
}
//foreach ($indexes as $index) {
//    $labels[$index] = $temp[$index];
//}

// now that we have the basic data lets render the page
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
            $(function() { // when the page loads
                $(".grades").hide(); // hide the grades section
                
                $("#allGrades").click( function() { // when all grades is checked
                    if ($("#allGrades input").is(":checked")) { // if it is being unchecked
                        $(".grades input").attr('checked', false); // set each grades checked status to false
                        $(".grades").hide(); // and hide the grades section
                    } else { 
                        $(".grades").show(); // show all the individual grades
                    }
                });
                // master toggle swtich should click all the parshios in the list
                $("#toggleParshos").click( function() {
                    $(".parshos input").trigger('click');
                });
                // creation of the compaign
                $("#submit").click( function() {
                    var errors = ''; // lets default to no errors
                    // small function for adding error messages. Implament?
                    //function addError(error){
                    //    if (errors !== '') errors += '\n'; // add a newline if we already have an error
                    //    errors += error; // and add the error to the errors string
                    //}
                    
                    // validate the campaign
                    if ($('#campaign').val() === 0) { // If the value of the campaign is 0,
                        if (errors !== '')
                            errors += '\n';
                        errors += 'You must choose a campaign.';
                    }
                    // validate that the name and shortName are not blank
                    if ($("#name").val() == '' && $("#shortName").val() == '') {
                        if (errors !== '') 
                            errors += '\n';
                        errors += "You have not entered a task or task title.";
                    }
                    // validate that the points value is not blank
                    if ($("#points").val() == '') {
                        if (errors !== '') 
                            errors += '\n';
                        errors += "You must enter a point value.";
                    }
                    // validate that the label is not blank (unless the campaign is #40)
                    if ($("#label").val() == 0 && $("#campaign").val() != 40) {
                        if (errors !== '') 
                            errors += '\n';
                        errors += "You must choose a label.";
                    }
                    // make sure that at least one grade is checked
                    if (!$("#allGrades input").is(":checked") && !$(".grades input").is(":checked")) {
                        if (errors !== '') 
                            errors += '\n';
                        errors += "You must choose a grade.";
                    }
                    // validate that at least one parsha is checked
                    if (!$('.mission').is(':checked')) {
                        if (errors !== '') 
                            errors += '\n';
                        errors += 'You must check off at least one parsha.';
                    }
                    // validate that Misson sheets or Teachers Grid is checked
                    if (!$("#mm").is(":checked") && !$("#gm").is(":checked")) {
                        if (errors !== '')
                            errors += '\n';
                        errors += "You have not selected where the task should show up!";
                    }
                    // process form
                    if (errors !== '') { // if we have errors
                        alert(errors);  // alert the user
                        return false;  // do not process the form
                    } else { // alert the user that it can take time to process the form
                        alert("It may take up to 5 minutes to process. Please wait and do not navigate away from, or exit, this screen. Thank you.");
                    } // endif
                });// end onsubmit for form
            }); // end script on page load
        </script>
    </head>
    <body>
        <? include('admin_header.php'); ?>
        <h1>Create a New Task</h1>
        
        <?
        // Superadmins cannot access this page.
        if ($admin_user['auth'] == 'super') {
        	echo "Superadmins cannot use this page.";
			exit; // kill the script
        }
        if (isset($_GET['msg'])) { // if a message is set in the GET paramaters, display it in a red box
            echo "<div style='color: red'>" . urldecode($_GET['msg']) . "</div><br />";
        }
        // now lets render the form
        ?>
        <form action='createNewTask.php' method='post'>
            <? 
            if (count($schools) > 1) { // if there is more then one school
                echo "School:<br />"; // render the label
                echo "<select name='school_id' id='school_id'>"; // and the select box
                $ids = array(); // set up a blank array of ids
                foreach ($schools as $id => $school) { // and populate it with the id's of each school
                    $ids[] = $id;
                }
                echo "<option value='" . implode(',', $ids) . "'>All</option>"; // and then impload them with , marks for all the schools
                foreach ($schools as $id => $school) { // and then add a separate option for each school
                    echo "<option value='$id'>$school</option>";
                }
                echo "</select>"; // end the drop down
            } else { // if we only have one school
                foreach ($schools as $id => $school) { // render each school (but we only have one :-) ) as a hidden input field
                    echo "<input type='hidden' name='school_id' 
                        value='$id' />";
                }
            } // end if block for rendering of school input
            ?>
            
            <p>
            	Language: <input type="radio" name="lang" class="lang" value="1" checked /> English 
            	<input type="radio" class="lang" name="lang" value="2" /> Yiddish
            </p>
            
            <p>
            	Add Title: (Example: 'Modeh Ani')<br />
            	<input type='text' name='shortName' size='30' id='shortName' />
            </p>
            
            <p>
                Add Task: (Example: 'I did my quota of volunteer hours' or 'I helped out in my local old age home').<br />
                <input type='text' name='name' size='80' id='name' />
            </p>
            <!--
            <p>
                Add Task Title: (Example: 'Chessed').<br />
                <input type='text' name='category' size='30' id='category' />
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
            -->

            <p>
                Campaign:<br />
                <select name='campaign' id='campaign'>
                    <option value='0'>Choose Campaign</option>
                    <? 
                    foreach ($missions as $id => $mission) { //  for each mission in the array of missions that we pulled from the database on line 24
                        if (in_array($id, array(1, 40, 94)))
                            continue;  // skip numbers 1, 40 and 94
                        echo "<option value='$id'>" . $mission . "</option>"; // the rest render as options
                    }
                    ?>
                </select>
            </p>
            
            <p>
                Task Label for mission sheets:<br />
                (You must choose which label in order to know where to place it on the mission sheets.)<br />
                <select name='label' id='label'>
                    <option value="0">Choose a label</option>
                    <?
                    foreach ($labels as $id => $label) {// render each label (from lines 28-32) as an option with the id as the value.
                        echo "<option value=" . $id . ">" . $label . (in_array($id, array(30,32,38)) ? " (Daily)" : "") . "</option>"; // and if that id is 30, 32, or 38 add (Daily) to the end
                    }
                    ?>
                </select>
            </p>

            <p>
                Mission Type<br />
                (You must choose which soldier type this will show up for)<br />
                <select name='school_type_id' id='school_type_id'>
                    <option value="0">  Chabad  </option>
                    <option value="10"> Frum    </option>
                    <option value="20"> CKids   </option>
                </select>
            </p>
            
            <p>
                Task should show up on: (check all that apply)<br />
                <input type="checkbox" name="mission_marking" id="mm" checked /> Mission Sheets<br />
                <input type="checkbox" name="grid_marking" id="gm" /> Teacher's Grid<br />
            </p>

            <fieldset>
                <legend>
                    Grades
                </legend>
                <div id="allGrades">
                    <input type="checkbox" name="classes[]" value="0" checked /> All Grades<br /><br />
                </div>
                <? // by default all grades is checked above
                $classes = array('Pre1a', '1', '2', '3', '4', '5', '6', '7', '8'); // array of all possible grades
                $num = count($classes); // count them
                $cutoff = (int)($num / 4); // and split into 4 columns
                $i = 0; // initialize i with 0
                echo "<div class='grades'>"; // create a div called grades
                foreach ($classes as $class) { // for each class
                    if (++$i > $cutoff) { // if adding to i goes above the cutoff point
                        echo "</div><div class='grades'>"; // cut the div and create a new one
                        $i = 1; // reset i to one (not 0, becuase the ++ is before the i)
                    }
                    echo "<input type='checkbox' name='classes[]' value='" . $class . "'>" . $class . "<br />"; // and render a checkbox for the class

                }
                echo "</div>"; // close the last div
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
                $num = count($parshos); // count the parshios (from lines 9-17)
                $cutoff = (int)($num / 4 + 1); // divide it into 4 columns
                $i = 0;
                echo "<div class='parshos'>"; // start the first div
				foreach ($parshos as $info) {
	                foreach ($info as $name => $parsha_id) {                           
	                    if (++$i > $cutoff) { // if i is above the cutoff point...
	                        echo "</div><div class='parshos'>"; // close the div and open a new one with the same class
	                        $i = 1; // reset i to 1. Not 0 (since we are using ++i not i++).
	                    }
	                    echo "<input type='checkbox' class='mission' name='parsha_ids[]' value='" . $parsha_id . "' checked>" . $name . "<br />"; // render a checkbox for each parsha
	                }
				}
                echo "</div>"; // end the last div
                ?>
                <div style='clear: both'></div>
                <div align='center'>
                    <br /><input type='button' id='toggleParshos' value='Toggle' />
                </div>
            </fieldset>
            
            <br />
            <p align='center'>
<!--                Submits with POST to /createNewTask.php and redirects there -->
                <input type='submit' name='submit' id='submit' value='Create Task' />
            </p>
            
        </form>
        
    </body>
</html>
