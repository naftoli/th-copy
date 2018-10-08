<?php
$debug = false;
/***************** DEBUGGING **********************/
$_POST['debug'] = true;
// enable debuging
if ($_POST['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getChidonYear();

// get all chidon schools
require_once($_SERVER['DOCUMENT_ROOT'].'/class.adminSchools.php');       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

$ids = [];
$info = [];
$sql = "select c.class_id, count(u.user_id) as total   
        from users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        where s.school_id in (" . implode( ',', array_keys( $schools ) ) . ") 
        and s.test_school != 1 
        and c.class_grade in ('4','5','6','7','8') 
        and c.class_era = 0 
        group by c.class_id";
//echo $sql;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    // initialize vars if not set
    $ids[] = $row['class_id'];
    $info[$row['class_id']]['notReg'] = $row['total'];

    // find out how many users from school are registered for chidon
    $sqlChidon = "select count(*) as registered from th_chidon 
                    where user_id in (
                        select user_id from users where class_id = " . $row['class_id'] . "
                    )    
                    and year = " . $year;
    $resultChidon = mysql_query( $sqlChidon );
    $rowChidon = mysql_fetch_assoc( $resultChidon );
    $info[$row['class_id']]['reg'] = $rowChidon['registered'];
}

$percentages = [];
foreach ( $info as $class_id => $data ) {
    if ( $data['reg'] == 0 ) {
        unset($info[$class_id]);
        continue;
    }
    $percent = round( ($data['reg'] / ($data['notReg'] + $data['reg']) * 100), 2 );
    $percentages[$class_id] = $percent;
}
arsort( $percentages );

$classInfo = [];
$sql = "select s.school_name, c.class_id, c.class_grade, c.class_sub 
        from schools s 
        join classes c using (school_id) 
        where class_id in (" . implode(',', $ids) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $classInfo[$row['class_id']] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            @media print {
               .noPrint {
                   display: none;
                }
            }
            body {
                font-family: Verdana;
            }
            tr, th, td {
                font-size: 14px;
                padding-top: 10px;
                padding-bottom: 10px;
                font-family: Verdana;
            }
        </style>
    </head>
    <body>
        <button onclick='window.print()' class='noPrint'>Print</button>
        <table>
            <thead>
                <tr>
                    <th></th><th>School</th><th>Grade</th><th>Sub</th><th>Chayolim in Base</th><th>Chayolim Registered in Chidon</th><th>Percentage Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach ( $percentages as $class_id => $percent ) { 
                    $school = $classInfo[$class_id]['school_name'];
                    $grade = $classInfo[$class_id]['class_grade'];
                    $sub = $classInfo[$class_id]['class_sub'];
                    echo "<tr><td>#" . $i++ . "</td><td>" . $school . "</td><td>" . $grade . "</td><td>" . $sub . "</td><td>";
                    echo ($info[$class_id]['notReg'] + $info[$class_id]['reg']) . "</td><td>";
                    echo $info[$class_id]['reg'] . "</td><td>" . $percent . "</td></tr>";     
                }
                ?>
            </tbody>
        </table>
    </body>
</html>