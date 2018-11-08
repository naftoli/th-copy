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
$qry = "SELECT u.first, u.last, u.first_he, u.last_he, u.user_id, "
        ." c.class_grade, c.class_sub, s.school_name, "
        ." a.admin_phone_mobile, a.admin_phone_mobile2 "
        ." FROM th_chidon th "
        ." JOIN users u USING (user_id) "
        ." JOIN classes c USING (class_id) "
        ." JOIN admins a ON parent_id = admin_id "
        ." JOIN schools s ON s.school_id = u.school_id " 
        ." WHERE th.year = " . $year;
if ( $school_id > 0 ) $qry .= " AND th.school_id = $school_id ";
$qry .= " ORDER BY s.school_name, c.class_grade, c.class_sub, u.last ";
$users_query = mysql_query( $qry );

$users = [];
while($row = mysql_fetch_assoc($users_query)){
    $users[] = $row;
}

/***************** RENDER REPORT **********************/
if($debug) echo "</pre>";

if (count($users) > 0) { 
    $totals = [];
    ?>
    <table>
        <thead>
            <?php if ($school_id < 0) : ?>
                <th>School</th>
            <?php endif; ?>
            <th>Grade</th><th colspan='2'>Name</th><th colspan='2'>Hebrew Name</th><th>Father Cell</th><th>Mother Cell</th>
        </thead>
        <tbody>
            <?php 
            foreach($users as $user) {
                $grade = $user['class_grade'] . ($user['class_sub'] ? " - " . $user['class_sub'] : "");
                if ( isset( $totals[$grade] ) ) $totals[$grade]++;
                else $totals[$grade] = 1;
            ?>
            <tr class="users">
                <?php 
                if ($school_id < 0) {
                    echo "<td>" . $user['school_name'] . "</td>";
                }
                ?>
                <td><?=$grade?></td>
                <td><?=$user['first']?></td>
                <td><?=$user['last']?></td>
                <td><?=$user['first_he']?></td>
                <td><?=$user['last_he']?></td>
                <td><?=$user['admin_phone_mobile']?></td>
                <td><?=$user['admin_phone_mobile2']?></td>
            </tr>
            <?}?>
        </tbody>
    </table>
    <h2>Totals Per Grade</h2>
    <table>
        <thead>
            <tr>
                <th>Grade</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $grandTotal = 0;
        foreach ( $totals as $grade => $total ) {
            $grandTotal += $total;
            echo "<tr><td>" . $grade . "</td><td>" . $total . "</td></tr>";
        }
        echo "<tr><th style='text-align: right'>Grand Total:</th><th>" . $grandTotal . "</th></tr>";
        ?>
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