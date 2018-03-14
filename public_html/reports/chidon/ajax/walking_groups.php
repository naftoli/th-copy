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
$gender = mysql_real_escape_string($_POST['gender']);
$start = mysql_real_escape_string($_POST['start']);
$end  = mysql_real_escape_string($_POST['end']);

/***************** LOAD MARKS **********************/
$attendence_times = [31, 32, 33];
$user_marks = [];
$user_marks_query = mysql_query(
    " SELECT * FROM th_chidon_attendance_marks WHERE att_time_id IN (" .implode(", ", $attendence_times) . ") "
);
while( $mark = mysql_fetch_assoc( $user_marks_query ) ) {
    $user_marks[$mark['att_time_id']][$mark['th_chidon_id']] = $mark['marked'];
}

/***************** LOAD ZONES **********************/
$chidon_type = $gender == "M" ? "boys" : "girls";
$zones = [];
$zone_users_query = mysql_query(
     " SELECT u.first, u.last, tc.th_chidon_id, tc.walking_zone, tc.host, tc.host_address1, tc.host_address2, "
    ." tc.host_number, tc.between_streets, a.admin_phone_mobile AS parent_number, s.school_name, tc.grade "
    ." FROM th_chidon tc "
    ." JOIN schools s USING (school_id) JOIN users u USING (user_id) "
    ." LEFT JOIN admin_auths aa ON aa.id = u.user_id LEFT JOIN admins a USING (admin_id) "
    ." WHERE tc.year = '$year' "  // imit to the current year.
    ." AND u.gender='$gender' "
    ." AND tc.walking_zone >= $start AND tc.walking_zone <= $end"         // limit to the selected zones...
    .($school_id ? " AND u.school_id = '$school_id' " : "")
    ." ORDER BY tc.walking_zone, u.last, u.first, tc.host "
);

while($row = mysql_fetch_assoc($zone_users_query)){
    $zones[$row['walking_zone']]['users'][] = $row;
}

$zone_chaperones_query = mysql_query(
     " SELECT th_chidon_chap_id, walking_zone, name, phone, school_name, acc_name, acc_address, acc_cross_st FROM th_chidon_chaps "
    ." JOIN schools s USING (school_id) "
    ." WHERE year = '$year' "  // imit to the current year.
    ." AND walking_zone >= $start AND walking_zone <= $end"         // limit to the selected zones...
    ." AND chidon_type = '$chidon_type' "
    ." ORDER BY walking_zone, name "
);

while($row = mysql_fetch_assoc($zone_chaperones_query)){
    $zones[$row['walking_zone']]['chaps'][] = $row;
}

/***************** LOAD COUNSELORS FOR SUPERUSERS **********************/
if($admin_user['auth'] === "super") {
    $zone_bunks_query = mysql_query(
        " SELECT bunk_id, walking_zone, counselor, c_number, host_name, host_address1, host_address2, host_between_streets from th_chidon_bunks "
       ." WHERE year = '$year' "  // imit to the current year.
       ." AND walking_zone >= $start AND walking_zone <= $end"         // limit to the selected zones...
       ." AND chidon_type = '$chidon_type' "
       ." ORDER BY walking_zone, counselor "
   );
   
   while($row = mysql_fetch_assoc($zone_bunks_query)){
       $zones[$row['walking_zone']]['bunks'][] = $row;
   }
}

ksort($zones);

$non_admin_disabled = $admin_user['auth'] === "super" ? "" : "disabled";

/***************** RENDER REPORT **********************/
if($debug) echo "</pre>";
$row_shown = false;
if (count($zones) > 0) {
    foreach ($zones as $zone_number => $zone) { ?>
        <?php if (count ($zone['users']) === 0) continue;
        $row_shown = true;?>
        <h2>Zone <?= $zone_number ?></h2>
        
        <strong>Walking Chaperones:</strong>
        <table>
            <tbody>
                <tr>
                    <th>Name</th><th>Number</th><th>School</th>
                    <th>Host</th><th>Host Address</th><th>Host Cross Streets</th>
                    <?php if($admin_user['auth'] === "super") { ?> <th class="no-print">Actions</th> <?php } ?>
                </tr>
                <? foreach($zone['chaps'] as $chap) {?>
                <tr>
                    <td><?= $chap['name']  ?></td>
                    <td><?= $chap['phone'] ?></td>
                    <td><?= $chap['school_name'] ?></td>
                    <td><?= $chap['acc_name'] ?></td>
                    <td><?= $chap['acc_address'] ?></td>
                    <td><?= $chap['acc_cross_st'] ?></td>
                    <?php if($admin_user['auth'] === "super") { ?>
                        <td class="no-print">
                            <a data-type="chap" data-id="<?=$chap['th_chidon_chap_id']?>" class="button move">Move</a>
                        </td>
                    <?php } ?>
                </tr>
            <?}?>
            </tbody>
        </table>
        
        <?php if($admin_user['auth'] === "super") { ?>
            <br/>
            <strong>Walking Counselors:</strong>
            <table>
                <tbody>
                    <tr>
                        <th>Name</th><th>Number</th>
                        <th>Host</th><th>Host Address</th><th>Host Cross Streets</th>
                        <th class="no-print">Actions</th>
                    </tr>
                    <?php foreach($zone['bunks'] as $bunk) { ?>
                    <tr>
                        <td><?= $bunk['counselor']?></td>
                        <td><?= $bunk['c_number'] ?></td>
                        <td><?= $bunk['host_name'] ?></td>
                        <td><?= $bunk['host_address1'] . " " . $bunk['host_address2'] ?></td>
                        <td><?= $bunk['host_between_streets'] ?></td>
                        <td class="no-print">
                            <a data-type="bunk" data-id="<?=$bunk['bunk_id']?>" class="button move">Move</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
        <br/>
        <strong>Children:</strong>
        <table>
            <tbody>
                <tr>
                    <th>Name</th><th>School</th><th>Host</th><th colspan="2">Host Address</th>
                    <th>Host Cross Streets</th><th>Host Phone Number</th><th>Parent Mobile Phone</th>
                    <th>Thurs</th><th>Fri</th><th>שבת</th>
                    <?php if($admin_user['auth'] === "super") { ?> <th class="no-print">Actions</th> <?php } ?>
                </tr>
                <? foreach($zone['users'] as $user) {?>
                <tr>
                    <td><?= $user['first'] . " " . $user['last']  ?></td>
                    <td><?= $user['school_name'] ?></td>
                    <td>
                        <input type="text" class="td-text" name="host" value="<?= $user['host'] ?>" style="width: 150px;" <?=$non_admin_disabled?>/>
                    </td>
                    <td>
                        <input type="text" class="td-text" name="host_address1" value="<?= $user['host_address1'] ?>" style="width: 35px;" <?=$non_admin_disabled?>/>
                    </td>
                    <td>
                        <input type="text" class="td-text" name="host_address2" value="<?= $user['host_address2'] ?>" style="width: 120px;" <?=$non_admin_disabled?>/>
                    </td>
                    <td>
                        <input type="text" class="td-text" name="between_streets" value="<?= $user['between_streets'] ?>" <?=$non_admin_disabled?>/>
                    </td>
                    <td>
                        <input type="text" class="td-text" name="host_number" value="<?= $user['host_number'] ?>" style="width: 110px;" <?=$non_admin_disabled?>/>
                    </td>
                    <td><?= $user['parent_number'] ?></td>
                    <td>
                    <?php
                    if( $user['grade'] <= 5 ) {
                        echo isset($user_marks[$attendence_times[0]][$user['th_chidon_id']]) ? "✔" : "X";
                    } else {
                        echo "N/A";
                    }?>
                    </td>
                    <td><?= isset($user_marks[$attendence_times[1]][$user['th_chidon_id']]) ? "✔" : "X"?></td>
                    <td><?= isset($user_marks[$attendence_times[2]][$user['th_chidon_id']]) ? "✔" : "X"?></td>
                    <?php if($admin_user['auth'] === "super") { ?>
                    <td class="no-print">
                        <a data-type="user" data-id="<?=$user['th_chidon_id']?>" class="button move">Move</a>
                        <a data-id="<?=$user['th_chidon_id']?>" class="button save">Save</a>
                    </td>
                    <?php } ?>
                </tr>
            <?}?>
            </tbody>
        </table>
        <div class="page-break"></div>
<?  }
}

if (!$row_shown) { // if there are no students found... ?>
    <div class="no-report">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <h2>No Walking Zones Found</h2>
        <p>
            Please double check the numbers entered above. Enter 1 and 100 for all Walking Groups.
        </p>
    </div> 
<? } ?>