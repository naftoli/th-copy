<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 300);
$admin_auth = array('school'); 

require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$year = GlobalSettings::getRegistrationYear();

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
$sql = "select * from raffles where type = 'weekly' and date_ran > 0 and year = " . $year;
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
        $result = mysql_query( $sql ) or die( mysql_error() );
        while ($row = mysql_fetch_assoc( $result )) {
            $winners[$row['school_id']][$raffle['name']][] = $row;
        }
    }
}
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
            // generate 26 different ids with different background pics
            for ( $i = 1; $i <= 26; $i++ ) {
                $urls[$i] = "Weekly-Prize-Poster-Template_Part$i.jpg";
            }
            foreach ( $urls as $i => $url ) {
                echo "#week$i {";
                echo "background-image: url( $url );";
                echo "backgound-repeat: no-repeat;";
                echo "}";
            } 
            ?>
        </style>
    </head>
    <body>
        <?php
        foreach ($winners as $school => $info) {
            if ( isset( $schools[$school] ) ) echo "<h2 class='school'>School: " . $schools[$school] . "</h2>";
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