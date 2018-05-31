<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../../../classes/user.php');
require_once(dirname(__FILE__).'/../../../classes/admin.php');
require_once(dirname(__FILE__).'/../../../calendar.php');
require_once(dirname(__FILE__).'/../../../class.globalSettings.php');

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once(dirname(__FILE__).'/../../../header.php');

/***************** POST DATA **********************/
if ( 
    isset( $_POST[ 'serial_number' ] ) &&
    preg_match( "/7{2}\d{4,5}/", $_POST[ 'serial_number' ] )
) {
    $serial_number = mysql_real_escape_string( $_POST['serial_number'] );
    $user_filter = " user_serial = '$serial_number' ";
} else if ( 
    isset( $_POST[ 'barcode' ] ) && 
    preg_match( "/3{1}\d{19}/", $_POST[ 'barcode' ] )
) {
    $barcode = mysql_real_escape_string( $_POST['barcode'] );
    $barcode = substr( $barcode, 1 ); // cut off the opening 3
    $user_filter = " user_code = '$barcode' ";
} else { // bad input
    echo "Please enter a valid serial number or digit barcode."; die();
}

// make sure that schools can only see children in their school
if ( $admin_user['auth'] !== "super" ) {
    require_once dirname(__FILE__).'/../../../class.adminSchools.php';
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
    $user_filter .= " AND school_id IN ('" . implode( "', '", array_keys ($schools) ) . "') ";
}

// load the user from the database
$user_query = mysql_query(
    "SELECT * FROM users WHERE $user_filter;"
);

if ( mysql_num_rows( $user_query ) === 0 ) {
    echo "Could not find soldier with provided serial number / barcode"; die();
}

$user = new user( mysql_fetch_assoc( $user_query ) );

$user->get_school();
$user->get_class();
$user->get_rank();
$user->get_medals(0, $user->user_start_date, unixtojd(), 0);
$user->get_childs_parent();
$user->get_prizes_won();
// load this years chidon info
$th_chidon = $user->get_chidon_info( GlobalSettings::getChidonYear() );
// get the info for tanya
$tanya_info_query = mysql_query(
     " SELECT date_tasks.quantity, date_tasks_marks.done_qty, "
    ." date_tasks.description, date_tasks_missions.mission_description "
    ." FROM user_tracks JOIN users USING ( user_id ) "
    ." JOIN date_tasks_missions USING ( subject_id, track_id, level, school_type_id, lang_id ) "
    ." JOIN date_tasks USING ( date_tasks_mission_id ) "
    ." LEFT JOIN date_tasks_marks USING ( user_id, date_task_id ) "
    ." WHERE users.user_id = '" . $user->user_id . "' AND user_tracks.subject_id = 1 AND grid_id = 8001 "
    ." AND start_date > " . GlobalSettings::getCurYearDates()['start'] . " "
);

$tanya_info = [];
while( $row = mysql_fetch_assoc( $tanya_info_query ) )
    $tanya_info[] = $row;
//** Load Tanya information */
/***************** RENDER REPORT **********************/
if ( $th_chidon ) include( dirname(__FILE__) . "/../../../chidon_passwords.php" );
?>
<input type="hidden" id="user_id" value="<?= $user->user_id; ?>"/>
<h2>
    <a href="/admin_user.php?action=edit&user_id=<?= $user->user_id . ($user->school ? "&school_id=".$user->school->school_id : "") ?>" target="_blank">
        <?= $user->first; ?> <?= $user->last; ?> - <?= $user->user_serial ?>
    </a>
</h2>
<div class="photo">
    <img src="//mashpia.com/<?= $user->get_profile_picture(); ?>" alt="profile_picture" class="profile_picture" />
    <img class="rank" src="/mobile/img_new/ranks/<?= $user->rank_ord; ?>.svg" alt="<?= $user->rank_name; ?>"/>
</div>
<div class="primary_info">
    <div class="info">
        <span class="title">Name:</span>
        <h3><?= $user->first; ?> <?= $user->last;?></h3>
    </div>
    <div class="info">
        <span class="title">Hebrew Name:</span>
        <h3><?= $user->first_he; ?> <?= $user->last_he;?></h3>
    </div>
    <div class="info">
        <span class="title">Rank:</span>
        <br/>
        <h3><?= $user->rank_name; ?></h3>
    </div>
    <div class="info">
        <span class="title">DOB:</span>
        <br/>
        <h3 style="font-size: 1em;"><?= $user->dob ?> / <?= $user->dob_he ?></h3>
    </div>
    <div class="info">
        <span class="title">Serial Number:</span><br/>
        <h3><?= $user->user_serial; ?></h3>
    </div>
    <div class="info">
        <span class="title">Barcode:</span>
        <h3>3<?= $user->user_code; ?></h3>
    </div>
    <div class="info">
        <span class="title">Base:</span>
        <h3>
        <?php // create link for superusers to edit schools 
        if ( $admin_user['auth'] == "super" && $user->school ) { ?>
            <a href="/admin_school2.php?admin_id=<?= $admin_user['admin_id'] ?>&school_id=<?= $user->school->school_id ?>&action=edit" target="_blank">
        <?php } // end opening a tag
        // make sure the user has a school. and if so show the information
        if ( $user->school ) {
            echo $user->school->school_name;
            if ( $admin_user['auth'] === "super" )
                echo " ( ID: " . $user->school->school_id . " )</a>"; // close the tag for superusers
        } else {
            echo "No Base";
        } ?>
        </h3>
    </div>
    <div class="info">
        <span class="title">Platoon:</span>
        <br/>
        <h3><?= $user->get_grade(); ?></h3>
    </div>
</div>
<div class="other_info">
    <div class="info">
        <span class="title">Address:</span>
        <h3><?= $user->get_address(); ?></h3>
    </div>
    <div class="info info-quarter">
        <span class="title">Member Since:</span>
        <h3><?= dateToHebrew($user->user_start_date); ?></h3>
    </div>
    <div class="info info-quarter">
        <span class="title">Registered</span>
        <h3><?= $user->user_registered ? $user->user_registered : "N/A" ?></h3>
    </div>
    <div class="info info-quarter">
        <span class="title">Mission Type:</span>
        <h3><?= $user->get_mission_type(); ?></h3>
    </div>
    <div class="info info-quarter">
        <span class="title">Language:</span>
        <h3><?= $user->lang; ?></h3>
    </div>
    <div class="info info-quarter">
        <span class="title">Gender:</span>
        <h3><?= $user->gender === "M" ? "Boy" : "Girl"; ?></h3>
    </div>
    <div class="info info-quarter">
        <span class="title">Chayolei Soldier:</span>
        <h3><?= $user->is_chayolei() ? "Yes" : "No"; ?></h3>
    </div>
    <div class="info">
        <h2>Parent Account (#<?= $user->childs_parent->admin_id ?>)</h2>
        <div class="inner-info">
            <span class="title">Username:</span>
            <h3><?=$user->childs_parent->username?></h3>
        </div>
        <div class="inner-info">
            <span class="title">Password:</span>
            <h3><?=$user->childs_parent->password?></h3>
        </div>
        <div style="padding: 0px 5px 5px;">
            <span class="title">Name:</span>
            <h3>
                <?php
                if ($user->childs_parent->first) 
                    echo $user->childs_parent->title . " " 
                    . $user->childs_parent->first . " " 
                    . $user->childs_parent->last;
                else 
                    echo "N/A"?>
            </h3>
        </div>
        <div class="inner-info">
            <span class="title">Phone:</span>
            <h3><?=$user->childs_parent->admin_phone_mobile?></h3>
        </div>
        <div class="inner-info">
            <span class="title">E-mail:</span>
            <h3><?=$user->childs_parent->admin_email?></h3>
        </div>
    </div>
    <div class="info">
        <h2>Prizes Won</h2>
        <?php 
        if ( count($user->prizes_won) > 0) {
            foreach( $user->prizes_won as $prize ) { ?>
            <div class="prize">
                <img src="//mashpia.com<?= $prize['picture'] ?>" alt="<?= $prize['prize_name'] ?>" />
                <span><?= $prize['prize_name'] ?> in <?= $prize['raffle_name'] ?></span>
            </div>
        <?php 
            } 
        } else { ?>
            No Prizes Won Yet <i class="fa fa-frown-o" aria-hidden="true"></i>
        <?php } ?>
    </div>
    <?php if ( $th_chidon ) {?>
        <div>
            <h2>Chidon Info (ID: <?= $th_chidon['th_chidon_id'] ?>)</h2>
            <div class="info centered">
                <span class="title">Test Scores</span>
                <table>
                    <tbody>
                        <tr>
                            <th>1a</th><th>1b</th><th>2a</th><th>2b</th><th>3a</th><th>3b</th>
                        </tr>
                        <tr>
                            <td><?= $th_chidon['test1a'] ?></td><td><?= $th_chidon['test1b'] ?></td>
                            <td><?= $th_chidon['test2a'] ?></td><td><?= $th_chidon['test2b'] ?></td>
                            <td><?= $th_chidon['test3a'] ?></td><td><?= $th_chidon['test3b'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="info">
                <div class="inner-info">
                    <span class="title">Contestant:</span>
                    <h3><?= $th_chidon['contestant'] ? "Yes" : "No" ?></h3>
                </div>
                <div class="inner-info">
                    <span class="title">Representative:</span>
                    <h3><?= $th_chidon['school_rep'] ? "Yes" : "No" ?></h3>
                </div>
                <div class="inner-info">
                    <span class="title">Amount Paid:</span>
                    <h3>$<?= $th_chidon['paid'] ?></h3>
                </div>
                <div class="inner-info">
                    <span class="title">Grade / Book:</span>
                    <h3><?= $th_chidon['grade'] ?> / <?= $th_chidon['book'] ?></h3>
                </div>
            </div>
            <div class="info">
                <span class="title">Shabbaton Host</span>
                <h3>
                    <?= $th_chidon['host'] ?> Family<br/>
                    <?= $th_chidon['host_address1'] ?> <?= $th_chidon['host_address2'] ?><br/>
                    Btw. <?= $th_chidon['between_streets'] ?><br/>
                    <?= $th_chidon['host_number'] ?><br/>
                </h3>
            </div>
            <div class="info">
                <div class="inner-info">
                    <span class="title">Shoe Size:</span>
                    <h3><?= $th_chidon['shoe_size']?></h3>
                </div>
                <div class="inner-info">
                    <span class="title">T-Shirt Size:</span>
                    <h3><?= $th_chidon['size'] ?></h3>
                </div>
                <div class="inner-info">
                    <span class="title">Walking Zone:</span>
                    <h3><?= $th_chidon['walking_zone']?></h3>
                </div>
                <div class="inner-info">
                    <span class="title">Test Language:</span>
                    <h3><?= $th_chidon['test_lang'] ?></h3>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="info">
        <h2>Tanya Bal Peh</h2>
        <table>
            <thead>
                <tr>
                    <th>Month</th><th>Quota</th><th>Compleated</th>
                </tr>
            </thead> 
            <tbody>
                <?php foreach( $tanya_info as $tanya ){ ?>
                    <tr>
                        <td><?= str_replace("שבת מברכים ", "", $tanya['mission_description']) ?></td>
                        <td><?= $tanya['quantity'] ?></td>
                        <td><?= $tanya['done_qty'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <!-- $tanya_info -->
        </table>
    </div>
    <div class="info">
        <h2>Medal Board</h2>
        <div id="medal-board">
            <div class="loader"></div>
        </div>
    </div>
    <div>
        <h2>Rank Board</h2>
        <div id="rank-board">
            <div class="loader"></div>
        </div>
    </div>
</div>
