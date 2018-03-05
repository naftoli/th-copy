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
$chaps_sql = "SELECT * FROM th_chidon_schools ts "
    ."JOIN th_chidon_chaps tc USING (school_id) "
    ."JOIN schools s USING (school_id) "
    ."WHERE ts.registered = 1 "
    ."AND ts.year = " . $year . " "
    ."AND tc.year = " . $year . " ";

if ($school_id) {
    $chaps_sql .= "AND ts.school_id = '" . $school_id . "' "
        ." ORDER BY first_name, last_name;";
} else {
    $chaps_sql .= " ORDER BY school_name, first_name, last_name;";
}

$chaps_query = mysql_query($chaps_sql);

$chaps = [];
while($row = mysql_fetch_assoc($chaps_query)){
    $chaps[] = $row;
}

/***************** RENDER REPORT **********************/
if($debug) echo "</pre>";

if (count($chaps) > 0) { ?>
    <table>
        <thead>
            <? if (!$school_id) { ?>
                <th>School</th>
            <? } ?>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Full Program</th>
            <th>Sweater Size</th>
            <?php if ($admin_user['auth'] == "super") {?>
                <th></th>
            <?php } ?>
        </thead>
        <tbody>
        <? foreach($chaps as $chap) {?>
            <tr id="<?=$chap['th_chidon_chap_id']?>">
                <? if (!$school_id) { ?>
                    <td><?=$chap['school_name']?></td>
                <? } ?>
                <td><?=$chap['first_name']?></td>
                <td><?=$chap['last_name']?></td>
                <td><?=$chap['email']?></td>
                <td><?=$chap['phone']?></td>
                <td><?=$chap['full_program'] ? 'yes' : 'no';?></td>
                <td><?=$chap['sweater_size']?></td>
                <?php if ($admin_user['auth'] == "super") {?>
                <td>
                    <a href='#' class='button edit' data-chap_id="<?=$chap['th_chidon_chap_id']?>">edit</a>
                    <a href='#' class='button delete' data-chap_id="<?=$chap['th_chidon_chap_id']?>">delete</a>
                </td>
                <?php } ?>
                
            </tr>
        <?} // end foreach chap.. ?>
        </tbody>
    </table>
<? } else { // if there are no students found... ?>
    <style>
        .no-report {text-align: center;}
        .no-report > .fa {font-size: 3em;}
        .no-report > .fa, .no-report > h3 {margin-bottom: 10px;}
    </style>
    <div class="no-report">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <h3>No Registered Chaperones For <?=$year?></h3>
        <p>
            Click <a href="#">here</a> to create one.
        </p>
    </div>
<? } ?>