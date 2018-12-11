<?php
ini_set('display_errors', 1);
?>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            body {
                font-family: sans-serif;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
<?php
require 'db.php';
$users = array();
$sql = "select * from users where dob > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = array(
        'dob'       => $row['dob'],
        'dob_he'    => trim($row['dob_he']),
        'offset'    => $row['dob_he_offset']
    );
}

$updated = 0;
foreach ($users as $user => $info) {
    $dob = $info['dob'];
    $arrDate = explode('-', $dob);
    $jd = gregoriantojd($arrDate[1], ($arrDate[2] + $info['offset']), $arrDate[0]);
    if ($jd > 0) {
        $jewish = jdtojewish($jd, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH);
        $j = trim( iconv('WINDOWS-1255', 'UTF-8', $jewish) );
        
        if (strcmp($j, $info['dob_he']) != 0) {
            $sql = "update users set dob_he = \"" . mysql_real_escape_string( $j ) . "\" where user_id = " . $user_id;
            if (mysql_query( $sql )) {
                $updated++;
            }
        } else {
            echo $user . "<br />" . $j . "<br />" . $info['dob_he'] . "<br /><br />";
        }
    }
}
echo "Updated: " . $updated;
?>
    </body>
</html>