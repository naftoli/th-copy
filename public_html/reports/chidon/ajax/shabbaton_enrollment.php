<?php $debug = false;
/***************** DEBUGGING **********************/
// enable debuging
if ($_POST['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

/***************** POST PARAMS **********************/
$school_id = mysql_real_escape_string($_POST['school_id']);

/***************** LOAD DATA **********************/
$users_query = mysql_query(
    "SELECT u.first, u.last, u.first_he, u.last_he, u.user_id, "
    ." c.class_grade, c.class_sub, "
    ." th.host, th.host_address1, th.host_address2, th.between_streets, th.host_number, th.allergies, th.walk_day, th.walk_night, "
    ." a.admin_phone_mobile, a.admin_phone_mobile2 "
    ." FROM th_chidon th "
    ." JOIN users u USING (user_id) "
    ." JOIN classes c USING (class_id) "
    ." JOIN admins a ON parent_id = admin_id "
    ." WHERE th.date_paid IS NOT NULL AND th.school_id = $school_id AND th.year = $year "
    ." ORDER BY class_grade, class_sub, u.last "
);

$users = [];
while($row = mysql_fetch_assoc($users_query)){
    $users[] = $row;
}

/***************** RENDER REPORT **********************/
if($debug) echo "</pre>";

if (count($users) > 0) { ?>
    <table>
        <thead>
            <th>Grade</th><th>Name</th><th>Hebrew Name</th><th>Host</th><th>Father Cell</th><th>Mother Cell</th><th>Allergies</th><th>Walk (Day)</th><th>Walk (Night)</th>
        </thead>
        <tbody>
            <? foreach($users as $user) {?>
            <tr class="users">
                <td><?=$user['class_grade'] . ($user['class_sub'] ? " - " . $user['class_sub'] : "")?></td>
                <td><?=$user['first'] . " " . $user['last']?></td>
                <td><?=$user['first_he'] . " " . $user['last_he']?></td>
                <td>
                    Host: <?=$user['host']?> <br/>
                    <?=$user['host_address1'] . " " . $user['host_address2']?>. <br/>
                    Cross Streets: <?=$user['between_streets']?>. <br/>
                    Phone number: <?=$user['host_number']?>
                </td>
                <td><?=$user['admin_phone_mobile']?></td>
                <td><?=$user['admin_phone_mobile2']?></td>
                <td><?=$user['allergies']?></td>
                <td><?=$user['walk_day'] ? "Yes" : "No"?></td>
                <td><?=$user['walk_night'] ? "Yes" : "No"?></td>
            </tr>
            <?}?>
        </tbody>
    </table>
<? } else { // if there are no students found... ?>
    <div class="no-report">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <h2>No Enrolled Students Found</h2>
        <p>
            Click <a href="/enrollment.php">here</a> to begin the enrollment process....
        </p>
    </div> 
<? } ?>