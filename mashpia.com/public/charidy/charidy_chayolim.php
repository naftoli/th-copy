<?php
require '../db.php';

$rowNum = 1;
$first = true;
$chayolim = array();
$matchInfo[0] = 0;
$matchInfo[1] = 0;
$matchInfo[2] = 0;
if (($handle = fopen("chayolim.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        if ($first) {
            $first = false;
            continue;
        }
        $num = count($data);
        for ($c = 0; $c < $num; $c++) {
            $value = trim( $data[$c] );
            switch ( $c ) {
                case 0:
                    $rank = $value;
                    break;
                case 1:
                    $fname = $value;
                    break;
                case 2:
                    $lname = $value;
                    break;
                case 3:
                    $totalDonations = intval($value);
                    break;
            }
        }
        
        $sql = "select * from users where last = '" . $lname . "' and first = \"" . $fname . "\" and user_registered > 0";
        $result = mysql_query($sql);
        $numMatches = mysql_num_rows($result);
        
        if ($numMatches == 0) {
            echo $rowNum . ": (0) " . $sql . "<br />";
            $matchInfo[0]++;
        } else if ($numMatches > 1) {
            echo $rowNum . ": (>1) " . $sql . "<br />";
            while ($row = mysql_fetch_assoc($result)) {
                $user = $row['user_id'];
                $sql2 = "select rank_name, max(rm.rank_ord)
                        from ranks r
                        join rank_marks rm using (rank_ord)
                        where user_id = " . $user;
                $res2 = mysql_query($sql2);
                $row2 = mysql_fetch_assoc($res2);
                $found = 0;
                if ($row2['rank_name'] == $rank) {
                    echo $rank . " Found...<br />";
                    $found++;
                }
            }
            if ($found == 1) {
                $chayolim[] = array(
                    'user'  =>  $row,
                    'numD'  =>  $totalDonations
                );
            } else if ($found == 0) {
                echo "No " . $rank . " with this name found...<br />";
            }
            $matchInfo[2]++;
        } else if ($numMatches == 1) {
            $row = mysql_fetch_assoc($result);
            $chayolim[] = array(
                'user'  =>  $row,
                'numD'  =>  $totalDonations
            );
            $matchInfo[1]++;
        }
        $rowNum++;
    }
}

$updated = 0;
foreach ($chayolim as $info) {
    $points = $info['numD'] * 18;
    $sql = "insert into pointsDB.user_points
            set user_id = " . $info['user']['user_id'] . ",
            institution_id = " . $info['user']['school_id'] . ",
            class_id = " . $info['user']['class_id'] . ",
            points = " . $points . ",
            created = now(),  
            resource_name = 'admin_users_manual'";
    //echo $sql . "<br />";
    //if (mysql_query($sql)) $updated++;
}
//echo count($chayolim);
//echo "<br />Updated: " . $updated;
//echo "<pre>"; print_r($matchInfo); echo "</pre>";