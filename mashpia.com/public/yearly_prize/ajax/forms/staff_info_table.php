<?
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    //echo "<h2>Debug log:</h2>";
    //echo "<pre>";
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** GET SOME BASIC INFORMATION **********************/
if(!$_POST['school_id']){
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
} else {
    $schools = [$_POST['school_id'] => ""];
}

/***************** IMPORTS **********************/
require_once(dirname(__FILE__)."/../../functions/get_staff.php");

/***************** LOAD STAFF **********************/
$staff_members = get_staff($_POST['school_id']);


/***************** RENDER BY SCHOOL **********************/
foreach($schools as $school_id => $school_name) {?>
    <? if($school_name != "") { echo "<h2>$school_name</h2>";}?>
    <table>
        <thead>
            <th>Name</th><th>Email</th><th>Cell Number</th><th>Work Number</th><th>Position</th><th>Grade</th><th>Actions</th>
        </thead>
        <tbody>
            <? foreach($staff_members[$school_id] as $staff){
                $staff_type = $staff['type'];
                $staff_id = $staff['id'];
                $staff_mark = "$staff_type-$staff_id";
            ?>
            <tr>
                <input type="hidden" id="school_id_<?=$staff_mark?>" value="<?=$staff['school_id']?>" />
                <input type="hidden" id="class_id_<?=$staff_mark?>" value="<?=$staff['class_id']?>" />
                <td id="name_<?=$staff_mark?>"><?=$staff['name']?></td>
                <td id="email_<?=$staff_mark?>"><?=$staff['email']?></td>
                <td id="cell_phone_<?=$staff_mark?>"><?=$staff['cell_phone']?></td>
                <td id="work_phone_<?=$staff_mark?>"><?=$staff['work_phone']?></td>
                <td id="position_<?=$staff_mark?>"><?=$staff['position']?></td>
                <td><?=$staff['class_grade'] ? $staff['class_grade'].($staff['class_sub'] ? " - ".$staff['class_sub'] : "") : "N/A"?></td>
                <td>
                    <a class="button" id="edit" data-mark="<?=$staff_mark?>">
                        <i class="fa fa-pencil" data-mark="<?=$staff_mark?>" aria-hidden="true"></i> Edit
                    </a>
                </td>
            </tr>
            <? }// end for each staff member ?>
        </tbody>
    </table>
<? }// end for each school ?>