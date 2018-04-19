<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../../../classes/user.php');
require_once(dirname(__FILE__).'/../../../calendar.php');

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
// load the user from the database
$user_query = mysql_query(
    "SELECT * FROM users WHERE $user_filter;"
);
$user = new user( mysql_fetch_assoc( $user_query ) );

$user->get_school();
$user->get_class();
$user->get_rank();
$user->get_medals(0, $user->user_start_date, unixtojd(), 0);
/***************** RENDER REPORT **********************/
?>
<h2><?= $user->first; ?> <?= $user->last; ?> - <?= $user->user_serial ?></h2>
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
        <span class="title">Gender / DOB:</span>
        <br/>
        <h3><?= $user->gender; ?> / <?= $user->dob ?></h3>
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
        <?php
            echo $user->school->school_name;
            if ( $admin_user['auth'] === "super" )
                echo " ( ID: " . $user->school->school_id . " )";
        ?>
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
    <div class="info">
        <span class="title">Member Since:</span>
        <h3><?= dateToHebrew($user->user_start_date); ?></h3>
    </div>
    <div class="info info-3rd">
        <span class="title">Mission Type:</span>
        <h3><?= $user->get_mission_type(); ?></h3>
    </div>
    <div class="info info-3rd">
        <span class="title">Language:</span>
        <h3><?= $user->lang; ?></h3>
    </div>
    <div class="info info-3rd">
        <span class="title">Chayolei Soldier:</span>
        <h3><?= $user->is_chayolei() ? "Yes" : "No"; ?></h3>
    </div>
</div>