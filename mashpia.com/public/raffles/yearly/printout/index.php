<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require(dirname(__FILE__) . '/../../../header.php');

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

// load the schools
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

require_once( dirname(__FILE__).'/../classes/YearlyRaffle.php' );
use raffles\yearly\YearlyRaffle as YearlyRaffle; // use the raffle class from its namespace
$yearly_raffle = new YearlyRaffle();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Yearly Prize Printout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Pangolin" rel="stylesheet">
    <style>
        body {
            margin: 0px;
            padding: 0px;
        }
        img {
            max-width: 100%;
        }
        .page {
            height: 279mm;
            width: 216mm;
            page-break-after: always;
        }
        .page-generated {
            background: #0565A4;
            color: #fff;
            font-family: 'Pangolin', cursive;
            text-align: center;
            padding: 25px 15px;
            box-sizing: border-box;
        }
        table {
            width: 100%;
        }
        td:first-child, td:last-child {
            min-width: 75px;
        }
        @media print {
            html, body {
                width: 216mm;
                height: 279mm;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <img src="Yearly Raffle Prize Posters 5779-1.png" />
    </div>
    <div class="page">
        <img src="Yearly Raffle Prize Posters 5779-2.png" />
    </div>
    <div class="page">
        <img src="Yearly Raffle Prize Posters 5779-3.png" />
    </div>
    <div class="page">
        <img src="Yearly Raffle Prize Posters 5779-4.png" />
    </div>
    <div class="page">
        <img src="Yearly Raffle Prize Posters 5779-5.png" />
    </div>
    <div class="page">
        <img src="Yearly Raffle Prize Posters 5779-6.png" />
    </div>
    <?php 
    foreach( $schools as $school_id => $school ) {
        $users = $yearly_raffle->get_eligible_users( false, $school_id );?>
        <div class="page page-generated">
            <h1><?= $school ?></h1>
            <table>
                <tbody>
                    <?php foreach ( $users as $index => $user ){ ?>
                        <tr>
                            <td><?= $user['class_grade'] . " " . $user['class_sub']?></td>
                            <td><?= $user['last'] . ", " . $user['first']?></td>
                            <td><?= $user['days']?>/160</td>
                        </tr>
                        <?php if ( ($index + 1) % 34 === 0 ) { ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="page page-generated">
                                <h1><?= $school ?></h1>
                                <table>
                                    <tbody>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</body>
</html>