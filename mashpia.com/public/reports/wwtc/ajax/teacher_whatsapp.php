<?php
$debug = false; // default debugging is false
if ($_POST['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** PARAMATERS *****************/
$school_id = mysql_real_escape_string($_POST['school_id']);

/***************** LOAD CLASS INFO FROM THE DATABASE *****************/
$teacher_info = [];
$teacher_info_query = mysql_query("SELECT * FROM classes WHERE school_id = '$school_id' ORDER BY class_grade, class_sub");
while($row = mysql_fetch_assoc($teacher_info_query)){
    $teacher_info[] = $row;
}

?>

<table>
    <thead>
        <th>Teacher</th> <th>Grade</th> <th>Report Gender</th> <th>Show On Whatsapp Report</th>
    </thead>
    <tbody>      
    <?/***************** FOR EACH TEACHER RENDER A ROW IN THE TABLE *****************/
        foreach($teacher_info as $teacher) {?>
        <tr data-class_id="<?=$teacher['class_id']?>">
            <td><?=$teacher['class_teacher']?></td>
            <td><?=$teacher['class_grade'] . ($teacher['class_sub'] ? " - " . $teacher['class_sub'] : "")?></td>
            <td>
                <select class="teacher_gender">
                    <option selected disabled>Not Set</option>
                    <option value="m" <?=$teacher['class_gender'] == "m" ? "selected" : ""?>>Boys</option>
                    <option value="f" <?=$teacher['class_gender'] == "f" ? "selected" : ""?>>Girls</option>
                </select>
            </td>
            <td>
                <label class="fancy-check-container">
                    <input type="checkbox" class="teacher_whatsapp" <?=$teacher['whatsapp'] == "1" ? "checked" : ""?> />
                    <span class="fancy-check"></span>
                </label>
            </td>
        </tr>
        <? } // end foreach teacher
    ?>
    </tbody>
</table>
