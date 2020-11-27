<?php
include_once( dirname(__FILE__) . "/../header.php" );

if ( $_SERVER['REQUEST_METHOD'] != "POST" ) {
    if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he') render_json_error("סוג בקשה לא חוקי");
    else render_json_error("Invalid Request", "Invalid Request Type. Expecting POST");
}
// all the params we are expecting
$school_id = post_param( "school_id" );
$class_id  = post_param( "class_id" );
$last   = post_param( "last" );
$dob    = post_param( "dob" );

if ( !$school_id || !$class_id || !$last || !$dob )
    if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) render_json_error("סוג בקשה לא חוקי");
    else render_json_error( "Invalid Request", "Invalid Request Paramaters" );

// make sure that the user exists first
$user_query = mysql_query(
     " SELECT user_id, tuition FROM users JOIN schools USING (school_id) "
    ." WHERE school_id = '$school_id' "
    ." AND class_id = '$class_id' AND last = '$last' AND dob='$dob' "
);

if ( !$user_query || mysql_num_rows( $user_query ) == 0 )
    if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) render_json_error("ילד לא נמצא. הסיבה לכך היא ככל הנראה תאריך לידה שגוי");
    else render_json_error( "Child Not Found. This is likely due to an incorrect Date of Birth" );
else if ( mysql_num_rows( $user_query ) > 1 )
    if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) render_json_error("נמצאו מספר ילדים. אנא צרו קשר עם מפקד הבסיס שלכם או עם bugs@tzivoshashem.org עם המידע שהזנתם");
    else render_json_error(
        "Multiple children found. Please contact your Base Commander or bugs@tzivoshashem.org with the information you entered."
    );

$user = mysql_fetch_assoc( $user_query );
$user_id = $user['user_id'];

$has_parent_query = mysql_query(
    "SELECT * FROM admin_auths WHERE auth='user' AND id = '$user_id';"
);

if ( mysql_num_rows( $has_parent_query ) > 0 )
    if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) render_json_error("לילד זה כבר יש חשבון הורים. אם אתה סבור שזה בטעות, שלח דוא\"ל לכתובת cth@tzivoshashem.org עם צילום מסך של האפשרויות שנבחרו");
    else render_json_error(
         "This child already has a parent account. If you believe this is in error please email cth@tzivoshashem.org with a screenshot of the options selected."
    );

$connect_accounts_query = mysql_query(
     "INSERT INTO admin_auths (admin_id, auth, id, role_id) "
    ."VALUES ('$admin_id', 'user', '$user_id', 1)"
);

if ( !$connect_accounts_query )
    if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) render_json_error("
        שגיאת שרת. אנא שלחו צילום מסך של האפשרויות שבחרתם לכתובת bugs@tzivoshashem.org. תודה
    ");
    else render_json_error(
        "Server Error. Please email a screenshot of the options you selected to bugs@tzivoshashem.org. Thank you ;-)" 
    );

// let the user know that all is good...
json_response( [
    "tuition" => $user['tuition'] == 1 ? true : false
] );