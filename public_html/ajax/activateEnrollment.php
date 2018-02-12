<?php
require '../db.php';
require '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$chidon_id = mysql_real_escape_string($_POST['id']);
$can_enroll = mysql_real_escape_string($_POST['can_enroll']);

// make sure there is a chaperone in the school...
$chap_school_id_query = mysql_query("SELECT school_id FROM th_chidon WHERE th_chidon_id = " . $chidon_id); // get the school id as it is not sent it
$chap_school_id = mysql_fetch_assoc($chap_school_id_query)['school_id'];

$chap_check = mysql_query("SELECT * FROM th_chidon_schools WHERE year = " . $year . " AND school_id = " . $chap_school_id);
if(mysql_num_rows($chap_check) == 0){
    echo json_encode([
        "success"   => false,
        "chap"      => false,
        "chap_school_id" => "SELECT school_id FROM th_chidon WHERE th_chidon_id = " . $chidon_id
    ]);
    die();
}

$sql = "UPDATE th_chidon SET can_enroll = '$can_enroll' WHERE th_chidon_id = " . $chidon_id;
if (mysql_query($sql)) {
    
    echo json_encode([
        "success"   => true,
        "chap"      => true
    ]);
    
    // send an email if can_enroll is set to 1
    if($can_enroll == '1'){
        // send email to parent
        $parentSql = "SELECT admin_email FROM admins a "
            ."JOIN admin_auths aa USING (admin_id) "
            ."JOIN th_chidon tc ON tc.user_id = aa.id "
            ."WHERE aa.role_id = 1 "
            ."AND aa.auth = 'user' "
            ."AND tc.th_chidon_id = " . $chidon_id . " " 
            ."AND tc.year = " . $year;
        $parentRes = mysql_query( $parentSql );
        $parentRow = mysql_fetch_assoc( $parentRes );
        
        $to      = $parentRow['admin_email'];
        $subject = 'Chidon Shabbaton Enrollment';
        $msg     = "Chidon Shabbaton Enrollment is now open. Please go to mashpia.com/mobile and click on
                    'Enroll for Chidon Shabbaton' to get started. Enrollment closes on Monday, Daled Adar (Feb 19).";
        $headers = 'From: chidon@tzivoshashem.org' . "\r\n" .
                    'Reply-To: chidon@tzivoshashem.org' . "\r\n";    
        @mail($to, $subject, $msg, $headers);
    }
    
} else {
    echo json_encode([
        "success"   => false,
        "chap"      => true,
        "sql_error" => mysql_error(),
        "sql"       => $sql
    ]);
}