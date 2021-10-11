<?
//ini_set("display_errors", 1);
/***************** IMPORTS **********************/
require_once( $_SERVER["DOCUMENT_ROOT"].'/db.php' ); // load the db so that the raffle can do its thing

// enforce admins only
if ( isset( $_COOKIE['admin_id'] ) ){
    $is_parent_query = mysql_query(
        "SELECT auth FROM admin_auths WHERE auth != 'user' AND admin_id = " . mysql_escape_string( $_COOKIE['admin_id'] ) . ";"
    );
    if ( mysql_num_rows( $is_parent_query ) === 0 ){
        http_response_code( 401 );
        echo json_encode( [ "success" => false, "msg" => "Invalid Credentials" ] ); 
        die();
    };
} else {
    http_response_code( 401 );
    echo json_encode( [ "success" => false, "msg" => "Invalid Credentials" ] ); 
    die();
}


$school_id = isset($_POST['school_id']) ? $_POST['school_id'] : false;
$auction_id = isset($_POST['auction_id']) ? $_POST['auction_id'] : false;
$sorting = isset($_POST['sorting']) ? $_POST['sorting'] : "name";

if ($sorting == "school") {$order_sql = "s.school_name, c.class_grade, u.last, u.first";}
elseif ($sorting == "prize_id") {$order_sql = "aw.prize_id, u.last, u.first";}
elseif ($sorting == "name") {$order_sql = "u.last, u.first";}
else {$order_sql = "u.last, u.first";}

$sql = "SELECT
            u.user_id, u.first as first_name, u.last as last_name,
            c.class_grade, c.class_sub, c.class_sub,
            s.hachayol_name, s.school_name,
            p.prize_id, p.prize_name,
            a.admin_address1 as street,
            a.admin_city as city,
            a.admin_state as state,
            a.admin_postal as zip,
            a.admin_country as country
        FROM auction_winners aw
        LEFT JOIN users u USING (user_id)
        LEFT JOIN schools s ON u.school_id = s.school_id
        LEFT JOIN classes c USING (class_id) 
        LEFT JOIN admin_auths aa ON u.user_id = aa.id
        LEFT JOIN admins a USING (admin_id)
        JOIN prizes_auction p USING (prize_id) 
        WHERE aw.auction_id = $auction_id
        ". ($school_id ? "AND u.school_id = $school_id " : "") ."
        ORDER BY $order_sql
";

$query = mysql_query($sql);

$auction_winners = [];
while ($row = mysql_fetch_assoc($query)) {
    $row['grade'] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    unset($row['class_grade']);
    unset($row['class_sub']);
    $auction_winners[] = $row;
}

echo json_encode($auction_winners);
