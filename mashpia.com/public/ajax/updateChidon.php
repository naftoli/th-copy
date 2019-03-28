<?php
require '../db.php';
require '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$id = mysql_real_escape_string($_POST['id']);
$checked = mysql_real_escape_string(intval($_POST['val']));
$field = mysql_real_escape_string($_POST['field']);

$sql = "UPDATE th_chidon set " . $field . " = " . $checked . " WHERE th_chidon_id = " . $id;
if (mysql_query($sql)) {
    echo 0;
    // if confirming enrollment, send email to parents
    if ($field == 'confirmed' && $checked) {
        $parentSql = "SELECT admin_email FROM admins a "
            ." JOIN admin_auths aa USING (admin_id) "
            ." JOIN th_chidon tc ON tc.user_id = aa.id "
            ." WHERE aa.role_id = 1 "
            ." AND aa.auth = 'user' "
            ." AND tc.th_chidon_id = " . $id . " "
            ." AND tc.year = " . $year ." ";
        $parentRes = mysql_query( $parentSql );
        $parentRow = mysql_fetch_assoc( $parentRes );
        
        $to      = $parentRow['admin_email'];
        $subject = 'Chidon Shabbaton ' . $year;
        $msg     = "Congratulations! Your child is now fully enrolled into the Chidon Shabbaton for " . $year . "!
                    They will receive more information from their Chidon coordinator.";
        $headers = 'From: chidon@tzivoshashem.org' . "\r\n" .
                    'Reply-To: chidon@tzivoshashem.org' . "\r\n";    
        @mail($to, $subject, $msg, $headers);
    }
} else {
    echo 1;
}