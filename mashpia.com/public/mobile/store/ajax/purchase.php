<?php
function rand_num_string($intLength=20)
{
	$strBarCode = "";
	while (strlen($strBarCode) < $intLength)
	{
		$strBarCode .= (string) rand(0, 999999999);
	}
	return substr($strBarCode, 0, $intLength);
}

require '../../../db.php';
require '../../../class.points.php';

$user = mysql_real_escape_string($_POST['user']);
$cart = json_decode($_POST['cart']);

// get institution id
$sql = "select school_id from users where user_id = " . $user;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$institution_id = $row['school_id'];

// ensure there are no negative amounts in cart
foreach ($cart as $item) {
    $qty = intval( $item->qty );
    if ( $qty < 0 ) {
        echo "You cannot purchase negative amounts.";
        exit;
    }   
}

// ensure user has enough points for this purchase
$p = new Points( $user );
$availableP = $p->getStorePoints();
$usedPoints = 0;
foreach ($cart as $item) {
    $qty = (int)mysql_real_escape_string($item->qty);
    $price = (int)mysql_real_escape_string($item->price);
    $usedPoints += ($price * $qty);
}
if ($usedPoints > $availableP) {
    echo "You do not have enough points for this purchase.";
    exit;
}

mysql_select_db('pointsDB');
mysql_query("set autocommit=0");
mysql_query("begin");

$success = true;
foreach ($cart as $item) {
    $prizeID = (int)mysql_real_escape_string($item->prize);
    $qty = (int)mysql_real_escape_string($item->qty);
    $price = (int)mysql_real_escape_string($item->price);
    $points = $price * $qty;
    
    if ($qty) {
        // Create a serial
        $strSerial = FALSE;
        while (!$strSerial)
        {
            $strSerial = rand_num_string(10);
            $sql = "select serial from pointsDB.user_prizes where serial = '" . $strSerial . "'";
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0)
                $strSerial = FALSE;
        }
        
        $sql = "insert into user_prizes set"
                ." prize_id = " . $prizeID . ","
                ." user_id = " . $user . ","
                ." institution_id = " . $institution_id . ","
                ." quantity = " . $qty . ","
                ." serial = '" . $strSerial . "',"
                ." status = 'Checked Out',"
                ." created = now()";
        
        if (mysql_query($sql)) {
            $id = mysql_insert_id();
            // update user points
            $sql = "insert into user_points set "
                    ." prize_id = " . $prizeID . ","
                    ." user_prize_id = " . $id . ","
                    ." user_id = " . $user . ","
                    ." institution_id = " . $institution_id . ","
                    ." points = -" . $points . ","
                    ." resource_name = 'store',"
                    ." created = now()";
            
            if (mysql_query($sql)) {
                // update stock
                $sql = "update prizes
                        set prize_count = prize_count - " . $qty . "
                        where prize_id = " . $prizeID;
                if (!mysql_query($sql)) {
                    $success = false;
                    break;
                }
            } else {
                $success = false;
                break;
            }
        } else {
            $success = false;
            break;
        }
    }
}

if (!$success) {
    mysql_query("rollback");
    echo mysql_error();
} else {
    mysql_query("commit");
    echo 0;
}
mysql_query("set autocommit=1");
exit;
?>