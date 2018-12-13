<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require dirname(__FILE__) . '/../../header.php';

require_once dirname(__FILE__) . '/../../class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

// get logos for schools
$logos = array();
foreach ($schools as $school_id => $school) {
    $sql = "select logo from schools where school_id = " . $school_id;
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $logos[$school_id] = $row['logo'];
    }
}

// get raffle id's
$k = 1;
$raffles = array();
$sql = "select * from raffles where type = 'weekly' and date_ran > 0";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $raffles[$row['raffle_id']] = $row;
    // map raffle id's to week number
    $weeks[$row['name']] = $k++;
}

// get winners
$winners = array();
$winners[0] = array(); // we only start counting from 1 so first index (0) needs to be initialized to empty array
foreach ($schools as $school_id => $school) {
    foreach ($raffles as $id => $raffle) {
        $sql = "select u.first, u.last, u.school_id 
                from users u
                join raffle_winners rw using (user_id) 
                where rw.raffle_id = " . $id . "
                and u.school_id = " . $school_id;
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc( $result )) {
            $winners[$row['school_id']][$raffle['name']][] = $row;
        }
    }
}

echo "<pre>";
//print_r( $weeks );
//print_r( $winners );
echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            @font-face {
                font-family: Gotham;
                src: url('Fonts/GothamNarrow-Bold_1.otf');
            }
            @font-face {
                font-family: School;
                src: url('Fonts/Heebo-Regular.ttf');
            }
            .winner {
                width: 7.5in;
                height: 10in;
                page-break-after: always;
            }
            .names {
                top: 6in;
                position: relative;
                line-height: 1.6;
            }
            .name {
                font-family: Gotham;
                font-size: 30px;
                text-align: center;
            }
            .school {
                page-break-after: always;
            }
            .schoolInfo {
                font-family: School;
                font-size: 20px;
                position: relative;
                bottom: 1.3in;
                left: 5.5in;
                width: 180px;
                overflow-wrap: break-word;
            }
            @media screen {
                .winner {
                    padding-top: 20px;
                    padding-bottom: 20px;
                }
            }
            <?php
            // generate 26 different id's with different background pics
            for ($i = 1; $i <= 26; $i++) :
            ?>
                #week<?=$i?> {
                    background-image: url("Weekly-Prize-Poster-Template_Part<?=$i?>.jpg");
                    background-repeat: no-repeat;
                }
            <?php endfor; ?>
        </style>
    </head>
    <body>
        <?php
        foreach ($winners as $school => $info) {
            echo "<h2 class='school'>School: " . $schools[$school] . "</h2>";
            foreach ($info as $raffle => $names) {                
                echo "<div class='winner' id='week" . $weeks[$raffle] . "'><div class='names'>";
                $j = 0;
                foreach ($names as $name) {                    
                    echo "<div class='name'>" . strtoupper($name['first'] . ' ' . $name['last']) . "</div>";
                    // after 4 names create new page
                    if (++$j > 3) {
                        echo "</div></div>";
                        echo "<div class='schoolInfo'>" . $schools[$school] . "</div>";
                        echo "<div class='winner' id='week" . $weeks[$raffle] . "'><div class='names'>";
                        $j = 0;
                    }
                }
                echo "</div></div>";
                //echo "<div class='schoolInfo'><img src='" . $_SERVER['SERVER_NAME'] . "/mission_report/schoolLogos/" . $logos[$school] . "' width=80 /><br />" . $schools[$school] . "</div>";
                echo "<div class='schoolInfo'>" . $schools[$school] . "</div>";
            }
        }
        ?>
    </body>
</html>