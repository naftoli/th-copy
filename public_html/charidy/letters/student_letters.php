<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once( dirname(__FILE__).'/../../header.php' );
require_once( dirname(__FILE__).'/../../class.adminSchools.php' );
// get the schools for the user
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$school_ids = array_keys( $schools );

$users_query = mysql_query(
     " SELECT s.school_id, s.school_name,  c.class_grade, c.class_sub, u.first, u.last, a.admin_email "
    ." FROM users u JOIN admin_auths aa ON id = user_id AND auth = 'user' "
    ." JOIN admins a USING (admin_id) JOIN schools s using (school_id) "
	." JOIN classes c using (class_id) "
    ." WHERE admin_email != '' AND u.user_registered IS NOT NULL "
    ." AND u.school_id IN ('" . implode( "', '", $school_ids ) . "') "
    ." ORDER BY s.school_name, c.class_grade, c.class_sub, u.last, u.first"
);

$users = [];
while( $row = mysql_fetch_assoc( $users_query ) ){
    $users[ $row['school_id'] ][] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Student Charidy Campaign Printouts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="letters.css" />
</head>
<body>
    <button class="no-print" id="print">Print Letters</button>
    <?php foreach( $users as $school_id => $school_users ) { ?>
        <div class="letter title-page">
            <h1><?= $schools[ $school_id ]; ?></h1>
            <h2><?= count( $school_users ); ?> Children Letters</h2>
        </div>
        <div class="letters">
            <?php foreach( $school_users as $user ){ ?>
                <div class="letter">
                    <p style="text-align: center;">B"H</p>
                    <p>Dear Mommy and Tatty,</p>

                    <p>Today I have the chance to get a dollar from the Rebbe.</p>
                    
                    <p>It's as simple as 1.2.3.</p>
                    
                    <p>1. Login in to charidy.com/TH</p>
                    
                    <p>2. Press donate</p>
                    
                    <p>3. Enter <?= $user['admin_email'] ?> (your Tzivos Hashem E-mail)</p>
                    
                    <p>
                        When I donate at least $5 I will be entered into a raffle to win a Rebbe Dollar.<br/>
                        I really want to win it more then anything else.
                    </p>
                    
                    <p>I could also get more tickets in the raffle if people donate in my honor.</p>
                    
                    <p>
                        Could we please make a video of us telling our friends and family what Tzivos Hashem does for us. 
                        I am sure if they realize the impact Tzivos Hashem is having they would donate, after all we will bring Moishiach and everyone wants to have a part in that.
                    </p>

                    <p>Thank you so much,</p>

                    <p><?= $user['first'] ?> <?= $user['last'] ?></p>
                    <p>
                        <?php
                            if ( intval($user['class_grade']) > 0 ) {
                                echo "Grade ";
                            }
                        ?>
                        <?= $user['class_grade'] ?> <?= $user['class_sub'] ? " - " . $user['class_sub'] : "" ?><br/>
                        <?= $user['school_name'] ?>
                    </p>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    <script>
        document.querySelector("#print").onclick = function() { window.print(); }
    </script>
</body>
</html>