<pre>
<?php // print all the errors to the log
error_reporting(E_ALL);
ini_set("display_errors", 1);

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

$duplicate_email_query = mysql_query(
    "SELECT username, password, first, last, admin_email, email, count(*) AS total " // get the total number of duplicates for each email address
    ."FROM admins LEFT JOIN tickets.msp_portal ON admins.admin_email = email COLLATE utf8_unicode_ci " // get the emails from the helpdesk system
    ."GROUP BY admin_email HAVING total > 1" // group by admin_emails and ignore duplicates
);

echo "Duplicate Email Removal Script\n";
echo "Email Address \t# Of duplicates\n";

while($duplicate_email = mysql_fetch_assoc($duplicate_email_query)){
    // skip to the next row if the admin email is null
    if(!$duplicate_email['admin_email']) continue; 
    // get all the admins with this email address
    $admin_ids = [];
    //$admins_query = ("SELECT admins FROM admins WHERE admin_email = ".$duplicate_email['admin_email']);
    $admin_ids_query = mysql_query(
        "SELECT admin_id, count(*) as children " // get the admin_id and the number of kids
        ."FROM admins a LEFT JOIN admin_auths aa USING (admin_id) " // from the admins table. join on admin auths to determine kid count
        ."WHERE admin_email = '".$duplicate_email['admin_email']."' " // limit to the current email address...
        ."GROUP BY a.admin_id ORDER BY children DESC LIMIT 100 OFFSET 1" // group by the admin_id, go from the most children to the least and only get the first one
    );
    
    while($admin = mysql_fetch_assoc($admin_ids_query)){
        $admin_ids[] = $admin['admin_id'];
    }
    
    //$remove_emails = mysql_query(
    //    "UPDATE admins SET admin_email = NULL WHERE admin_id IN (".implode(", ", $admin_ids).")"
    //);
    
    echo "Email: " . $duplicate_email['admin_email']
        ."\t\tDuplicates: " . (count($admin_ids) + 1)
        ."\t\tReset to null: ".(!!$remove_emails ? "Yes" : "No")."\n";
} // end foreach duplicate email address

?>
</pre>