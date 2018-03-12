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

/***************** LOAD DATA **********************/
$users_query = mysql_query(
    "SELECT * FROM th_chidon_staff WHERE year = '$year' ORDER BY name;"
);

$users = [];
while($row = mysql_fetch_assoc($users_query)){
    $users[] = $row;
}

/***************** RENDER REPORT **********************/
if($debug) echo "</pre>";?>

<table>
    <thead>
        <th>Name</th><th>Username</th><th>Password</th><th>Walking Zone</th><!--<th>Door Number</th><th>Bus Code</th>--><th>Chaperone Marking</th>
    </thead>
    <tbody>
        <? foreach($users as $user) {?>
        <tr class="users" id="<?=$user['staff_id']?>">
            <td>
                <input type="text" data-staff_id="<?=$user['staff_id']?>" data-column="name" value="<?=$user['name']?>"/>
            </td>
            <td>
                <input type="text" data-staff_id="<?=$user['staff_id']?>" data-column="username" value="<?=$user['username']?>"/>
            </td>
            <td>
                <input type="text" data-staff_id="<?=$user['staff_id']?>" data-column="password" value="<?=$user['password']?>"/>
            </td>
            <td>
                <input type="text" data-staff_id="<?=$user['staff_id']?>" data-column="walking_zone" value="<?=$user['walking_zone']?>"/>
            </td>
            <!--<td>
                <select data-staff_id="<?=$user['staff_id']?>" data-column="door_number" value="<?=$user['door_number']?>">
                    <option value="">No Door</option>
                    <optgroup label="Grade 4-5">
                        <option value="1" <?=$user['door_number'] == "1" ? "selected" : ""?>>Door 1</option>
                        <option value="2" <?=$user['door_number'] == "2" ? "selected" : ""?>>Door 2</option>
                        <option value="3" <?=$user['door_number'] == "3" ? "selected" : ""?>>Door 3</option>
                    </optgroup>
                    <optgroup label="Grade 6-8">
                        <option value="4" <?=$user['door_number'] == "4" ? "selected" : ""?>>Door 1</option>
                        <option value="5" <?=$user['door_number'] == "5" ? "selected" : ""?>>Door 2</option>
                        <option value="6" <?=$user['door_number'] == "6" ? "selected" : ""?>>Door 3</option>
                    </optgroup>
                </select>
            </td>
            <td>
                <input type="text" data-staff_id="<?=$user['staff_id']?>" data-column="bus_code" value="<?=$user['bus_code']?>"/>
            </td>-->
            <td>
                <select data-staff_id="<?=$user['staff_id']?>" data-column="chap_chidon_type" value="<?=$user['chap_chidon_type']?>">
                    <option value="">None</option>
                    <option value="girls" <?=$user['chap_chidon_type'] == "girls" ? "selected" : ""?>>Girls</option>
                    <option value="boys"  <?=$user['chap_chidon_type'] == "boys"  ? "selected" : ""?>>Boys </option>
                </select>
            </td>
        </tr>
        <?}?>
    </tbody>
</table>