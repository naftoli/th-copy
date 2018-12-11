<? // enable debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once( dirname(__FILE__).'/../../../header.php' );
// load the required files
require_once( dirname(__FILE__).'/../../../class.schoolsUsers.php' );
require_once( dirname(__FILE__).'/../classes/YearlyRaffle.php' );

use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace

$school_id = mysql_real_escape_string( $_POST['school_id'] );

if( !$school_id ){
    echo "Please Select A School"; die();
}
// get the users
$users = new SchoolsUsers( $school_id );
$users = $users->getUsers(false, false);
// spin up the raffle
$yearly_raffle = new YearlyRaffle();
$yearly_raffle->set_school_eligibility( $school_id );

?>
<table>
    <thead>
        <tr>
            <th>Grade</th><th>Serial #</th><th>Last</th><th>First</th><th>Eligible</th><th>Eligibilty status</th>
        </tr>
    </thead>
    <tbody>
        <?php // render a row for each user
        foreach( $users as $user ){ 
            if ( isset( $yearly_raffle->eligibility[ $user['user_id'] ] ) )
                $user_eligibility = $yearly_raffle->eligibility[$user['user_id']];
            else
                $user_eligibility = false;
            ?>
            <tr>
                <td><?= $user['class_grade'] . ( $user['class_sub'] ? " - " . $user['class_sub'] : "") ?></td>
                <td><?= $user['user_serial'] ?></td>
                <td><?= $user['last'] ?></td>
                <td><?= $user['first'] ?></td>
                <td><?= $user_eligibility && $user_eligibility >= 160 ? "YES" : "NO" ?></td>
                <td><?= $user_eligibility ? $user_eligibility : "0" ?>/160 </td>
            </tr>
        <?php 
        } 
        ?>
    </tbody>
</table>