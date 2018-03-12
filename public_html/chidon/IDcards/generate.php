<?php
ini_set('display_errors',1);

$admin_auth = array('school');
require ( $_SERVER['DOCUMENT_ROOT'].'/header.php' );

require_once ( $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();

// params passed in via GET to render the page....
$gender = isset($_GET['gender'])   && $_GET['gender'] ? mysql_real_escape_string($_GET['gender'])    : false;
$grade  = isset($_GET['grade'])    && $_GET['grade']  ? mysql_real_escape_string($_GET['grade'])     : false;
$type   = isset($_GET['type'])     && $_GET['type']   ? mysql_real_escape_string($_GET['type'])      : false;

// Get the SQL....
$info_sql = "";
// get the students....
if( $type === "student" ) {
    require_once(dirname(__FILE__)."/generator/student_card.php");
    $info_sql = "SELECT tc.*, s.*, u.*, tcb.bunk_name, tcb.counselor, tcb.c_number, tct.*" // do not load all the tcb info....
        ." FROM th_chidon tc " // from the chidon
        ." JOIN schools s USING (school_id) " // get the school info...
        ." JOIN users u ON u.user_id = tc.user_id " // sync up with the users...
        ." LEFT JOIN th_chidon_bunks tcb USING (bunk_id) " // sync up with the chidon bunks...
        ." LEFT JOIN th_chidon_teams tct ON tct.team_id = tc.team_id " // sync up with the teams...
        ." WHERE tc.year = $year " // keep to the current year....
        ." AND tc.date_paid IS NOT NULL " // make sure that they paid ;-)
        .($gender   ? " AND u.gender = '$gender' "  : "") // limit by gender (if applicalble)
        .($grade    ? " AND tc.grade = '$grade' "   : "") // limit by grade  (if applicalble)
        ." ORDER BY s.school_name, tc.grade, u.last, u.first"; // order by the school name, grade, last name, and then first name.
// Get the chaperones.....
} else if ( $type === "chaperone" ) {
    require_once(dirname(__FILE__)."/generator/chap_card.php");
    $info_sql = "SELECT * FROM th_chidon_chaps chaps "
        ." JOIN schools USING (school_id) "
        ." WHERE year = '$year' "
        .($gender  ? " AND chidon_type = '" . ($gender == "M" ? "boys" : "girls" )."' "  : ""); // limit by gender (if applicalble);
// Get the bunk....
} else if ( $type === "bunk" ) {
    require_once(dirname(__FILE__)."/generator/bunk_card.php");
    $info_sql = "SELECT * FROM th_chidon_bunks bunks "
        ." WHERE year = '$year' "
        .($gender  ? " AND chidon_type = '" . ($gender == "M" ? "boys" : "girls" )."' "  : ""); // limit by gender (if applicalble);
}

if ( $type === "custom" ) {
    require_once(dirname(__FILE__)."/generator/custom_card.php");
    $info = [ // create the item as a nested array to allow for multiple cards at once in the future?
        [
            'title'         => $_GET['title'],
            'gender'        => $_GET['gender'],
            'name'          => $_GET['name'],
            'id_number'     => $_GET['id_number'],
            'school_name'   => $_GET['school_name'],
            'school_location'   => $_GET['school_location'],
            'team'          => $_GET['team'],
            'bunk'          => $_GET['bunk'],
            'grade'         => $_GET['grade'],
        ]
    ];
} else {
    // load the info into an array....
    $info = [];
    $result = mysql_query($info_sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Chidon ID Cards - Generated</title>
        <link href="cardstyle.css" rel="stylesheet"/>
    </head>
    
    <body>
        <?php
        foreach ($info as $row) {
            if( $type === "student" ) {
                student_card($row, $year);
            } else if ( $type === "chaperone" ) {
                chap_card($row, $year);
            } else if ( $type === "bunk" ) {
                bunk_card($row, $year);
            } else if ( $type === "custom" ) {
                custom_card($row, $year);
            }
        }
        ?>
    </body>
</html>