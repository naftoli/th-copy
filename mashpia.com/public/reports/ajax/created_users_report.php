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

$school_id = mysql_real_escape_string($_POST['school_id']);

$users_sql = "SELECT user_serial, first, last, class_grade, class_sub, user_registered FROM users u LEFT JOIN classes c USING (class_id) WHERE u.school_id = $school_id ORDER BY user_id ";

// add a limit
$limit = false;
if (isset($_POST['limit']) && $_POST['limit']){
    $limit = mysql_real_escape_string($_POST['limit']);
    $users_sql .= "LIMIT $limit ";
}

if($limit && isset($_POST['offset']) && $_POST['offset']){
    $offset = mysql_real_escape_string($_POST['offset']);
    $users_sql .= "OFFSET $offset";
    $offset = intval($offset);
} else {
    $offset = 0;
}

$users_query = mysql_query($users_sql);

$users = [];
$counter = 0;

while($row = mysql_fetch_assoc($users_query)){
    $row['created_number'] = (++$counter) + $offset;
    $users[] = $row;
}

/* CLOSE THE PRE AND RENDER THE REPORT */
if($debug) echo "</pre>";?>
<table>
    <thead>
        <th>Serial #</th><th>Name</th><th>Grade</th><th>5778 Registration</th><th>Created #</th>
    </thead>
    <tbody>
        <? foreach($users as $user) {?>
        <tr class="users">
            <td><?=$user['user_serial']?></td>
            <td><?=$user['first']?>, <?=$user['last']?></td>
            <td><?=$user['class_grade'] . ($user['class_sub'] ? " - ".$user['class_sub'] : "")?></td>
            <td><?=$user['user_registered'] ? date('m/d/y g:ia', strtotime($user["user_registered"])) : "N/A"?></td>
            <td><?=$user['created_number']?></td>
        </tr>
        <?}?>
    </tbody>
</table>

