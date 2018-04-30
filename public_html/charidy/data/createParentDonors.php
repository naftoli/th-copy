<?php
require '../../db.php';

function createDonorFromAdmin( $admin ) {
    $phone = $admin['phone3'] ? $admin['phone3'] : ($admin['phone4'] ? $admin['phone4'] : ($admin['phone1'] ? $admin['phone1'] : ($admin['phone2'] ? $admin['phone2'] : '')));
    $sql = "insert IGNORE into charidy_donors
            set first_name = \"" . $admin['first'] . "\",
            last_name = \"" . $admin['last'] . "\",
            address = \"" . $admin['admin_address1'] . "\",
            city = '" . $admin['admin_city'] . "',
            state = '" . $admin['admin_state'] . "',
            zip = '" . $admin['admin_postal'] . "',
            country = '" . $admin['admin_country'] . "',
            phone = '" . $phone . "',
            email = '" . trim($admin['admin_email']) . "',
            parent_admin_id = " . $admin['admin_id'];
    if (mysql_query( $sql )) {
        return true;
    } else {
        echo $sql . "<br />" . mysql_error() . "<br />";
    }
}

$parents = array();
$sql = "select a.* from admins a 
        join admin_auths aa using (admin_id) 
        join users u on u.user_id = aa.id 
        where aa.auth = 'user' 
        and u.user_registered > 0 
        group by aa.admin_id";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    // make sure email is valid
    $email = $row['admin_email'];
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) $parents[] = $row;
}

$created = 0;
foreach ($parents as $parent) {
    if (createDonorFromAdmin( $parent )) {
        $created++;
    }
}

echo "Created: " . $created;