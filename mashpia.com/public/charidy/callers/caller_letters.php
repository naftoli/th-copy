<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once(dirname(__FILE__).'/../../header.php');
if ( $admin_user['auth'] !== "super" ){
    echo "Invalid Account Permissions. HQ account only"; die();
}
// load the current year
require_once(dirname(__FILE__).'/../../class.globalSettings.php');
$year = GlobalSettings::getCurrentYear(); 
// load the list of callers ( sorted by name )
require_once( dirname(__FILE__) . "/classes/Caller.php" );
require_once( dirname(__FILE__) . "/classes/NoCaller.php" );

require_once( dirname(__FILE__) . '/../../raffles/yearly/classes/YearlyRaffle.php');
use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace

// if we only want to print one caller
if( isset( $_GET['id'] ) && $_GET['id'] ){
    if ( $_GET['id'] == "-1" )
        $caller = new NoCaller;
    else
        $caller = Caller::Load( $_GET['id'] );
    $callers = [ $caller ]; //cast to array
} else { // or just get everyone
    $callers = Caller::LoadAll();
}

$ranks = [];
$sql = "select * from ranks";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Caller Printouts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="letters.css" />
</head>
<body>
<?php
    foreach( $callers as $caller ){
        $caller->loadDonors( $year ); // load this years donors
        // print a page for each donor
        ?>
        <div class="donor-letter caller-title-page">
            <h1 class="caller-name"><?= $caller->fullName(); ?></h1>
            <h2 class="caller-count"> <?= count( $caller->donors ); ?> Donors to call</h2>
        </div>
        <?php
        foreach ( $caller->donors as $donor ) { ?>
        <div class="donor-letter">
            <div class="header">
                Caller: <?= $caller->fullName(); ?>
            </div>
            <h1 class="donor-name"><?= $donor->fullName(); ?></h1>
            <h1 class="donor-number"><?= $donor->phone ? $donor->phoneNumber() : $donor->mashpiaPhoneNumber(); ?></h1>
            <div><?= $donor->address(); ?></div>

            <h2 class="heading">Donor Information</h2>
            <div class="donor-info">
                <div class="donated">
                    <?php 
                    $highest = 0; // keep track of highest payment
                    foreach ( [5776,5777,5778,5779] as $yr ) : ?>
                    <span id="donate-<?= $yr ?>">
                        <i class="fas fa-<?= $donor->getDonated( $yr ) ? "check" : "times"; ?>"></i>
                        Donated in <?= $yr ?>
                        <?= $donor->getDonated( $yr ) ? "( $" . $donor->donations[$yr]["amount"] . " )" : "" ?>
                        <?php
                        if ( isset( $donor->donations[$yr]["amount"] ) && $donor->donations[$yr]["amount"] > $highest ) {
                            $highest = $donor->donations[$yr]["amount"] . " (" . $yr . ")";
                        }
                        ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <div class="donated">
                    <span>
                        Biggest Donation: $<?= $highest ?>
                    </span>
                </div>
                <div class="donated">
                    <span>
                        Children in TH:<br />
                        <?php
                        $children = $donor->getChildren();
                        foreach ( $children as $child ) echo $child['first'] . " - " . $ranks[$child['rank']]  . "<br />";
                        ?>
                    </span>
                </div>
                <div class="donated">
                    <span id="<?=$year?>">
                        <?php if( count( $donor->onShabbaton( $year ) ) > 0 ) { ?>
                            <i class="fas fa-check"></i> Children on <?= $year ?> Shabbaton: 
                            <?php // list all the kids first names, comma seperated
                                echo implode(
                                    ", ",
                                    array_map( 
                                        function ( $child ) { return $child['first'] . ' (Bunk: ' . $child['bunk_number'] . ')'; }, 
                                        $donor->onShabbaton( $year ) 
                                    )
                                );
                            ?>
                        <?php } else { ?>
                            <i class="fas fa-times"></i> No Children on <?= $year ?> Shabbaton.
                        <?php } ?>
                    </span>
                </div>
                <div class="donated">
                    <span>
                        Eligible for Yearly Raffle:<br />
                        <?php
                        $yearly_raffle = new YearlyRaffle;
                        $quota = $yearly_raffle->getDayCount();
                        foreach ( $children as $child ) {
                            $num_days = $yearly_raffle->set_user_eligibility( $child['user_id'] )[ $child['user_id'] ];
                            if ( $num_days >= $quota ) echo $child['first'] . " - yes<br />";
                            else echo $child['first'] . " - no<br />";
                        }
                        ?>
                    </span>
                </div>
            </div>
            

            <h2 class="heading">Donor Status</h2>
            <div class="donor-status">
                <div class="donor-status-item">
                    <i class="far fa-square"></i> Donated
                </div>
                <div class="donor-status-item">
                    <i class="far fa-square"></i> Not Interested
                </div>
                <br/>
                <div class="donor-status-item">
                    <i class="far fa-square"></i> Call Back
                </div>
                <div class="donor-status-item">
                    <i class="far fa-square"></i> Email Link to: __________________________________
                </div>
            </div>
            <div class="donor-amount">
                Amount (not quadrupled): $_____________________
            </div>

            <div class="donor-payment">
                <div class="payment-method">
                    Credit Card:
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-discover"></i>
                    <i class="far fa-credit-card"></i>
                </div>
                <div class="card-number">
                    __ __ __ __ __ __ __ __ __ __ __ __ __ __ __ __ <br/>
                    EXP: __ __ / __ __ CVV: __ __ __ __
                </div>
                <div class="mailing_address">
                    <span>Mailing Address:</span>
                    __________________________________________________________<br/>
                    <span>City:</span>_____________________________
                    <span>State:</span>__ __
                    <span>Zip</span>__ __ __ __ __
                </div>
                <div class="email_address">
                    <span>E-Mail Address:</span>
                    __________________________________________________________
                </div>
            </div>
        </div>
        <?php
        } // end for each donor
    } // end for each caller
?>
<script>
    // window.print(); // open the printing dialog
</script>
</body>
</html>


