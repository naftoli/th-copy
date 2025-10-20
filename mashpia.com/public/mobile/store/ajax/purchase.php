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

function calculateFinalPrice($price, $discountAmount, $discountType) {
    if ($discountType === 'percent') {
        return ceil($price * (1 - $discountAmount / 100));
    } else {
        return max(0, $price - $discountAmount);
    }
}

require '../../../db.php';
require '../../../class.points.php';

$user = mysql_real_escape_string($_POST['user']);
$cart = json_decode($_POST['cart']);

// ****************** PROCESSING GUARD (similar to Hei Teves) ***************************/
function startPayment() {
    global $user;
    if (!$user) return;
    $sql = "INSERT INTO payment_processing (user_id) VALUES (" . $user . ")";
    mysql_query($sql);
}

function endPayment() {
    global $user;
    if (!$user) return;
    $sql = "DELETE FROM payment_processing WHERE user_id = " . $user;
    mysql_query($sql);
}

function paymentInProgress() {
    global $user;
    if (!$user) return false;
    $sql = "SELECT * FROM payment_processing WHERE user_id = " . $user;
    $result = mysql_query($sql);
    return $result && mysql_num_rows($result) > 0;
}
// **************************************************************************************

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
    if (isset($item->discount_amount) && isset($item->discount_type) && $item->discount_amount > 0) {
        $finalPrice = calculateFinalPrice($price, $item->discount_amount, $item->discount_type);
    } else {
        $finalPrice = $price;
    }
    $usedPoints += ($finalPrice * $qty);
}
if ($usedPoints > $availableP) {
    echo "You do not have enough points for this purchase.";
    exit;
}

// Guard: ensure no other purchase is in progress for this user
if ($user && paymentInProgress()) {
    echo "Your purchase is already being processed. Please wait for it to complete.";
    exit;
}

// Mark processing start
startPayment();

mysql_select_db('pointsDB');
mysql_query("set autocommit=0");
mysql_query("begin");

$success = true;
foreach ($cart as $item) {
    $prizeID = (int)mysql_real_escape_string($item->prize);
    $qty = (int)mysql_real_escape_string($item->qty);
    $price = (int)mysql_real_escape_string($item->price);
    if (isset($item->discount_amount) && isset($item->discount_type) && $item->discount_amount > 0) {
        $finalPrice = calculateFinalPrice($price, $item->discount_amount, $item->discount_type);
    } else {
        $finalPrice = $price;
    }
    $points = $finalPrice * $qty;
    
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
                ." actual_points = " . $points . ","
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

endPayment();
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