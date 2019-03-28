<?php
ini_set('display_errors',1);
require '../db.php';

$auction_id = 77;
$schoolPrizes = array();
$file = "schoolPrizes.csv";
/*
if (($handle = fopen($file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        $prize_id = $data[0];
        $school_id = $data[1];
        $schoolPrizes[] = array(
            'prize'     =>  $prize_id,
            'school'    =>  $school_id
        );
    }
}

$users = array();
foreach ($schoolPrizes as $index => $info) {
    $prize = $info['prize'];
    $school = $info['school'];
    $sql = "select user_id from auction_user_prizes aup 
            join users u using (user_id) 
            where aup.auction_id = " . $auction_id . "  
            and aup.prize_id = " . $prize . " 
            and u.school_id = " . $school . "
            and user_id not in (
            select user_id from auction_winners where auction_id > 70 and auction_id < 77)";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) == 0) {
        echo "No entries for Prize ID: " . $prize . " in School ID: " . $school . "<br />";
    } else {
        while ($row = mysql_fetch_assoc($result)) {
            $users[$index][] = $row['user_id'];
        }
    }
}
foreach ($users as $prize => $info) {
    echo "Prize: " . $prize . " Users: " . count($info) . "<br />";
}
// assign winners
$winners = array();
$winnerIDs = array();
foreach ($users as $index => $chayolim) {
    $loops = 0;
    $found = true;
    while ($found && $loops < 50) {
        $loops++;
        $max = count($chayolim);
        $winner = rand(0, --$max);
        $foundInternal = false;
        foreach ($winnerIDs as $prize => $userID) {
            if ($userID == $chayolim[$winner]) {
                $foundInternal = true;
                echo "found user id: " . $userID . "<br />";
            }
        }
        if (!$foundInternal) $found = false;
    }
    $winners[$index] = $winner;
    $winnerIDs[$index] = $chayolim[$winner];
    echo "Prize: " . $index . " Winner: " . $chayolim[$winner] . " Loop: " . $loops . "<br />";
}

$names = array();
$sql = "select user_id, last, first from users where user_id in (" . implode(',', $winnerIDs) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $names[$row['user_id']] = $row['first'] . ' '. $row['last'];
}

$bigWinners = array();
$sampleWinners = array();
foreach ($winnerIDs as $index => $user_id) {
    $ids = $schoolPrizes[$index];
    $prize = $ids['prize'];
    $school = $ids['school'];
    $sampleWinners[$prize . ':' . $school] = $names[$user_id];
    $bigWinners[$prize] = $user_id;
}

//echo "<pre>";
//print_r($schoolPrizes);
//print_r($users);
//print_r($winners);
//print_r($winnerIDs);
//print_r($bigWinners);

foreach ($bigWinners as $prize => $user) {
    $sql = "insert into auction_winners
            set auction_id = " . $auction_id . ",
            user_id = " . $user . ",
            prize_id = " . $prize . ",
            quantity = 1";
    //echo $sql . "<br />";
    //mysql_query($sql);
}
*/
$smallPrizes = array(
    394 =>  25,
    128 =>  25,
    408 =>  25,
    6   =>  25,
    384 =>  25,
    407 =>  25,
    379 =>  100,
    309 =>  100
);

$schools = array(269,176,162,45,30,54,2,7,112,66,105,63,81,49,192,89,55,106,5,50,21,37,4,60,86,264,33,185,80,110,194,3,39,19,42,265,9,263,61,255,48,58,87,427,11,40);

$arrSmallPrizes = array();
foreach ($smallPrizes as $prize => $total) {
    foreach ($schools as $school) {
        $arrSmallPrizes[$prize][] = $school;
    }
}

$prizeIDs = array();
$smallUsers = array();
foreach ($arrSmallPrizes as $prize => $other) {
    $prizeIDs[] = $prize;
    foreach ($other as $school) {
        $sql = "select user_id from auction_user_prizes aup 
                join users u using (user_id) 
                where aup.auction_id = " . $auction_id . "  
                and aup.prize_id = " . $prize . " 
                and u.school_id = " . $school . "
                and user_id not in (
                select user_id from auction_winners where auction_id > 70 and auction_id < 77)";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $smallUsers[$prize][$school][] = $row['user_id'];
        }
    }
}
ksort($smallUsers);
echo "<pre>";
print_r($smallUsers);

// assign small winners
$i = 0; // index into schools array
$smallWinners = array();
foreach ($smallPrizes as $prize => $num) {
    $tries = 0;
    while ($num-- && $tries < 50) {
        // find out if prize / school combo has users 
        if (isset($smallUsers[$prize][$schools[$i]])) {
            $max = count($smallUsers[$prize][$schools[$i]]);
            $winner = rand(0, --$max);
            //echo $prize . ":" . $schools[$i] . "-" . $i . " " . $winner . "!" . $smallUsers[$prize][$schools[$i]][$winner] . "<br />";
            $winnerID = $smallUsers[$prize][$schools[$i]][$winner];
            $smallWinners[$prize][] = $winnerID;
            unset($smallUsers[$prize][$schools[$i]][$winner]);
            if (count($smallUsers[$prize][$schools[$i]]) == 0) unset($smallUsers[$prize][$schools[$i]]);
            else $smallUsers[$prize][$schools[$i]] = array_values($smallUsers[$prize][$schools[$i]]);
            echo "Prize: " . $prize . " Winner: " . $winnerID . " Loop: " . $tries . "<br />";
            foreach ($prizeIDs as $p) {
                if ($p == $prize) continue;
                if (isset($smallUsers[$p][$schools[$i]]) && array_search($winnerID, $smallUsers[$p][$schools[$i]]) !== false) {
                    echo "Winner found in another prize: " . $p . " Winner: " . $winnerID . "<br />";
                    $key = array_search($winnerID, $smallUsers[$p][$schools[$i]]);
                    unset($smallUsers[$p][$schools[$i]][$key]);
                    $smallUsers[$p][$schools[$i]] = array_values($smallUsers[$p][$schools[$i]]);
                }
            }
        } else {
            $num++;
            $tries++;
        }
        $i++;
        if ($i == count($schools)) $i = 0;
        //echo "Prize ID:" . $prize . " School: " . $schools[$i] . " Prizes left: " . $num . "<br />";
    }
}
//print_r($smallWinners);

// save winners to db
$qrys = array();
foreach ($smallWinners as $prize => $other) {
    foreach ($other as $user) {
        $sql = "insert into auction_winners
                set auction_id = " . $auction_id . ",
                user_id = " . $user . ",
                prize_id = " . $prize . ",
                quantity = 1";
        $qrys[] = $sql;
    }
} 
//print_r($qrys);
$saved = 0;
foreach ($qrys as $index => $qry) {
    //if (mysql_query($qry)) $saved++;
    //else die($qry . "<br />" . mysql_error());
    //echo "Number " . ($index + 1) . ": " . $qry . "<br />";
} 
echo "Saved: " . $saved;

