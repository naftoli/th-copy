<?php
require '../db.php';
require '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$id = mysql_real_escape_string($_POST['id']);
$checked = mysql_real_escape_string(intval($_POST['val']));
$field = mysql_real_escape_string($_POST['field']);

$sql = "update th_chidon set " . $field . " = " . $checked . " where th_chidon_id = " . $id;
if (mysql_query($sql)) {
    echo 0;
    // if confirming enrollment, send email to parents
    if ($field == 'confirmed' && $checked) {
        $parentSql = "select admin_email from admins a
                    join admin_auths aa using (admin_id)
                    join th_chidon tc on tc.user_id = aa.id
                    where aa.role_id = 1
                    and aa.auth = 'user'
                    and tc.th_chidon_id = " . $id . " 
                    and tc.year = " . $year;
        $parentRes = mysql_query( $parentSql );
        $parentRow = mysql_fetch_assoc( $parentRes );
        
        $to      = $parentRow['admin_email'];
        $subject = 'Chidon Shabbaton Enrollment';
        $msg     = "Congratulations! Your child is now enrolled into the Chidon Shabbaton for 5778!
                    They will receive more information from their Chidon coordinator.";
        $headers = 'From: chidon@tzivoshashem.org' . "\r\n" .
                    'Reply-To: chidon@tzivoshashem.org' . "\r\n";    
        @mail($to, $subject, $msg, $headers);
    }
} else {
    echo 1;
}