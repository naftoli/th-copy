<?php
ini_set('display_errors',1);
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);

function unreceive_medal_mark() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$subject_id = $parameters[1];
	$medal_ord = $parameters[2];
	
	$sql = "UPDATE medal_marks SET date_shipped=NULL WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id . " AND medal_ord=" . $medal_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode('1');
	else
		return json_encode('0');
}

function update_medal_marks() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$subject_id = $parameters[1];
	$medal_ord = $parameters[2];
	
	$today = date('Y-m-d G:i:s');
	
	$sql = "UPDATE medal_marks SET date_shipped='" . $today . "' WHERE user_id=" . $user_id . " AND subject_id=" . $subject_id . " AND medal_ord=" . $medal_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(substr($today, 0, 10));
	else
		return json_encode('0');
}

function update_ranks_date_book_shipped() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$rank_ord = $parameters[1];
	
	$today = date('Y-m-d G:i:s');
	
	$sql = "UPDATE rank_marks SET date_book_shipped='" . $today . "' WHERE user_id=" . $user_id . " AND rank_ord=" . $rank_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(substr($today, 0, 10));
	else
		return json_encode('0');
}

function update_rank_books() {
    $params = explode("_", $_GET['parameters']);
    $serial = $params[0];
    $rank_ord = $params[1];
    $checked = $params[2];
    $today = date('Y-m-d G:i:s');

    if ($checked) {
        $sql = "UPDATE rank_marks SET date_book_shipped='" . $today . "' WHERE user_id=(
            SELECT user_id FROM users WHERE user_serial='" . $serial . "'
        ) AND rank_ord=" . $rank_ord;
    } else {
        $sql = "UPDATE rank_marks SET date_book_shipped=NULL WHERE user_id=(
            SELECT user_id FROM users WHERE user_serial='" . $serial . "'
        ) AND rank_ord=" . $rank_ord;
    }
    $query = mysql_query($sql);

    if ($query)
        return json_encode('1');
    else
        return json_encode('0');
}

function update_ranks() {
    $params = explode("_", $_GET['parameters']);
    $user_id = $params[0];
    $rank_ord = $params[1];
    $checked = $params[2];
    $type = $params[3];
    $today = date('Y-m-d G:i:s');

    if ($checked) {
        $sql = "UPDATE rank_marks SET date_{$type}_shipped='" . $today . "' WHERE user_id=$user_id AND rank_ord=" . $rank_ord;
    } else {
        $sql = "UPDATE rank_marks SET date_{$type}_shipped=NULL WHERE user_id=$user_id AND rank_ord=" . $rank_ord;
    }
    $query = mysql_query($sql);

    if ($query)
        return json_encode('1');
    else
        return json_encode('0');
}

function update_ranks_all() {
    $params = explode("_", $_GET['parameters']);
    $start = $params[0];
    $end = $params[1];
    $type = $params[2];
    $today = date('Y-m-d G:i:s');
    $sql = "UPDATE rank_marks SET date_{$type}_shipped='" . $today . "' 
            WHERE (
            (date_promoted >= $start AND date_promoted <= $end) OR (rm.date_book_shipped is null OR rm.date_card_shipped is null) 
            ) AND date_{$type}_shipped is null";
    $query = mysql_query($sql);

    if ($query)
        return json_encode('1');
    else
        return json_encode('0');
}

function update_medals_all() {
    $params = explode("_", $_GET['parameters']);
    $start = $params[0];
    $end = $params[1];
    $today = date('Y-m-d G:i:s');
    $sql = "UPDATE medal_marks SET date_shipped='" . $today . "' 
            WHERE (
                (date_awarded >= $start AND date_awarded <= $end) OR mm.date_shipped is null
            ) AND date_shipped is null";
    $query = mysql_query($sql);

    if ($query)
        return json_encode('1');
    else
        return json_encode('0');
}

function unreceive_ranks_date_book_shipped() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$rank_ord = $parameters[1];
	
	$today = date('Y-m-d G:i:s');
	
	$sql = "UPDATE rank_marks SET date_book_shipped=NULL WHERE user_id=" . $user_id . " AND rank_ord=" . $rank_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(substr($today, 0, 10));
	else
		return json_encode('0');
}

function update_ranks_date_card_shipped() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$rank_ord = $parameters[1];
	
	$today = date('Y-m-d G:i:s');
	
	$sql = "UPDATE rank_marks SET date_card_shipped='" . $today . "' WHERE user_id=" . $user_id . " AND rank_ord=" . $rank_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(substr($today, 0, 10));
	else
		return json_encode('0');
}

function unreceive_ranks_date_card_shipped() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$rank_ord = $parameters[1];
	
	$today = date('Y-m-d G:i:s');
	
	$sql = "UPDATE rank_marks SET date_card_shipped=NULL WHERE user_id=" . $user_id . " AND rank_ord=" . $rank_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(substr($today, 0, 10));
	else
		return json_encode('0');
}

function update_ranks_date_card_printed() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$rank_ord = $parameters[1];
	
	$today = date('Y-m-d G:i:s');
	
	$sql = "UPDATE rank_marks SET date_printed='" . $today . "' WHERE user_id=" . $user_id . " AND rank_ord=" . $rank_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(substr($today, 0, 10));
	else
		return json_encode('0');
}

function unreceive_ranks_date_card_printed() {
	$parameters = explode("_", $_GET['parameters']);
	$user_id = $parameters[0];
	$rank_ord = $parameters[1];
	
	$today = date('Y-m-d G:i:s');
		
	$sql = "UPDATE rank_marks SET date_printed=null WHERE user_id=" . $user_id . " AND rank_ord=" . $rank_ord;
	$query = mysql_query($sql);
	
	if ($query)
		return json_encode(substr($today, 0, 10));
	else
		return json_encode('0');
}

function update_add_ons($parameters) {
	$users_info = explode(":", $parameters[0]);
	
	for ($ui_no = 0; $ui_no < count($users_info); $ui_no++) {
		$user_info = explode(";", $users_info[$ui_no]);
		$user_id = $user_info[0];
		$add_on_one = $user_info[1];
		$shirt_size = $user_info[2];
		$add_on_two = $user_info[3];
		
		$sql = "UPDATE users SET ";
		if ($add_on_one == "1") {
			$sql = $sql . "add_on_one=1, shirt_size='" . $shirt_size . "'";
			if ($add_on_two == "1")
				$sql = $sql . ", add_on_two=1 ";
		}
		else {
			$sql = $sql . "add_on_two=1 ";
		}
		$sql = $sql . "WHERE user_id=" . $user_id;
		
		$query = mysql_query($sql);
		if (!$query) {
			return "0";
			break;
		}
	}
	
	return "1";
}

function update_winner_quantity($parameters) {
	$auction_id = $parameters[0];
	$user_id = $parameters[1];
	$prize_id = $parameters[2];
	$quantity = $parameters[3];

	$sql = "UPDATE auction_winners ";
	$sql = $sql . "SET quantity=" . $quantity . " ";
	$sql = $sql . "WHERE auction_id=" . $auction_id . " AND user_id=" . $user_id . " AND prize_id=" . $prize_id;
	$query = mysql_query($sql);
	if ($query)
		return "1";
	else
		return "0";
}

function remove_user_photo($parameters) {
	$user_id = $parameters[0];

	$sql = "SELECT u.user_photo_id, u.mobile_pic, s.school_name FROM users AS u JOIN schools AS s USING (school_id) WHERE user_id=" . $user_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	
	if ($row) {
		$user_photo_id = $row['user_photo_id'];
		$school_name = $row['school_name'];
		
		if ($user_photo_id) {
			$sql1 = "DELETE FROM files WHERE file_id=" . $user_photo_id;
			$query1 = mysql_query($sql1);
		} else if ($row['mobile_pic']) {
			// delete mobile pic
			if (unlink('mobile/reg/' . $row['mobile_pic'])) $query1 = true;
		}
			
		if ($query1) {
			$sql2 = "UPDATE users SET user_photo_id=NULL, mobile_pic = null WHERE user_id=" . $user_id;
			$query2 = mysql_query($sql2);
		}
		
		/*
		require_once('constant_file.php');
		$to = $headquarters;  // see constant_file.php
		$subject= $school_name . ' has uploaded pictures';
		$body = "<br>" . $school_name." has uploaded photos. Please print rank cards.<br>";
		$type = "html";
		
		mail($to, $subject, $body, "From: No Reply <noreply@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . ">\r\nX-Mailer: PHP/" . phpversion() . "\r\nErrors-To: errors@" . str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) . "\r\nMIME-Version: 1.0\r\nContent-Type: text/" . $type . "; charset=utf-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n");
		*/
		if ($query1 && $query2) 
			return "1";
		else
			return "0";
		
	}
}

function update_user($parameters) {
	// user_id, first, last, first_he, last_he, email, dob
	// user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone
	// class_id
	//echo "<pre>"; print_r($parameters); echo "</pre>";
	
	$user_id = mysql_real_escape_string($parameters[0]);
	$first = mysql_real_escape_string($parameters[1]); 
	$last = mysql_real_escape_string($parameters[2]); 
	$first_he = mysql_real_escape_string($parameters[3]); 
	$last_he = mysql_real_escape_string($parameters[4]); 
	$email = mysql_real_escape_string($parameters[5]);
	$dob = mysql_real_escape_string($parameters[6]);

	$user_address1 = mysql_real_escape_string($parameters[7]);
	$user_address2 = mysql_real_escape_string($parameters[8]);
	$user_city = mysql_real_escape_string($parameters[9]);
	$user_state = mysql_real_escape_string($parameters[10]);
	$user_postal = mysql_real_escape_string($parameters[11]);
	$user_country = mysql_real_escape_string($parameters[12]);
	$user_phone = mysql_real_escape_string($parameters[13]);
	
	$class_id = mysql_real_escape_string($parameters[14]);
	
	$sql = "UPDATE users SET first='" . $first . "', ";
	
	$sql .= "last='" . $last . "', ";
	$sql .= "first_he='" . $first_he . "', ";
	$sql .= "last_he='" . $last_he . "', ";
	$sql .= "email='" . $email . "', ";
	$sql .= "dob='" . $dob . "', ";
	
	$sql .= "user_address1='" . $user_address1 . "', ";
	$sql .= "user_address2='" . $user_address2 . "', ";
	$sql .= "user_city='" . $user_city  . "', ";
	$sql .= "user_state='" . $user_state  . "', ";
	$sql .= "user_postal='" . $user_postal  . "', ";
	$sql .= "user_country='" . $user_country  . "', ";
	$sql .= "user_phone='" . $user_phone  . "' ";
	if ($class_id) $sql .= ", class_id=" . $class_id  . " ";
	$sql .= "WHERE user_id=" . $user_id;
	//echo $sql;
	$query = mysql_query($sql);
	if ($query)
		return "1";
	else
		return "0";
}
?>