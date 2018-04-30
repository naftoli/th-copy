<?php
ini_set('display_errors',1);
// load up list of charidy ppl
require '../../db.php';

$info = array();
$sql = "select * from charidy where parent_admin_id is null";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[$row['email']][] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";

// functions to find out if any have a parent account by comparing email address / phone number / name / address
function matchByEmail( $email ) {
    if (!empty($email)) {
        $sql = "select * from admins where admin_email like '%" . mysql_real_escape_string($email) . "%'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        } 
    } 
    return false;
}

function matchByPhone( $phone ) {
    if (!empty($phone)) {
        // remove all non numbers from phone
        $phone = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        $phone = str_replace('+', '', $phone);
        $phone = str_replace('-', '', $phone);
        $sql = "select * from admins where phone1 = '" . $phone . "' or phone2 = '" . $phone . "' or phone3 = '" . $phone . "' or phone4 = '" . $phone . "'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        } 
    }
    return false;
}

function matchByName( $first, $last ) {
    if ($first != '' && $last != '') {
        $sql = "select * from admins where LOWER(first) like '%" . mysql_real_escape_string(strtolower($first)) . "%' and LOWER(last) like '%" .
            mysql_real_escape_string(strtolower($last)) . "%'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        }
    }
    return false;
}

function matchByAddress( $address ) {
    if (!empty($address)) {
        $sql = "select * from admins where LOWER(admin_address1) like '%" . mysql_real_escape_string(strtolower($address)) . "%'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result) > 0) {
            $row = mysql_fetch_assoc( $result );
            return $row;
        }
    }
    return false;
}

function showAdminInfo( $admin ) {
    echo "<div style='font-size: 12px;'>";
    echo "Admin ID: " . $admin['admin_id'] . "<br />";
    echo "Name: " . $admin['first'] . ' ' . $admin['last'] . "<br />";
    echo "Address: " . $admin['admin_address1'] . "<br />" . $admin['admin_city'] . ', ' . $admin['state'] . ' ' . $admin['zip'] . "<br />" . $admin['country'] . "<br />";
    echo "Email: " . $admin['admin_email'] . "<br />";
    echo "Numbers: " . $admin['phone1'] . "<br />" . $admin['phone2'] . "<br />" . $admin['phone3'] . "<br />" . $admin['phone4'] . "<br />";
    echo "</div>";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 14px;
            }
        </style>
    </head>
    
    <body>
        <table>
            <tr>
                <th>Line Number</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>City</th>
                <th>State</th>
                <th>Zip</th>
                <th>Country</th>
                <th>Donation Amount</th>
                <th>Year</th>
                <th>Parent Email Match</th>
                <th>Parent Number Match</th>
                <th>Parent Name Match</th>
                <th>Parent Address Match</th>
                <th>Connect to Parent ID</th>
            </tr>
            <?php
            $i = 1;
            $lastEmail = '';
            foreach ($info as $email => $other) {
                foreach ($other as $row) {
                    echo "<tr><td>" . $i++ . "</td><td>" . $email . "</td><td>" . $row['fname'] . "</td><td>" . $row['lname'] . "</td><td>" . $row['phone'] . "</td><td>" . $row['address'] .
                        "</td><td>" . $row['city'] . "</td><td>" . $row['state'] . "</td><td>" . $row['zip'] . "</td><td>" . $row['country'] . "</td><td>" . $row['donation'] .
                        "</td><td>" . $row['year'] . "</td><td>";
                    if ($admin = matchByEmail($email)) {
                        showAdminInfo( $admin );                        
                    }
                    echo "</td><td>";
                    if ($admin = matchByPhone($row['phone'])) {
                       showAdminInfo( $admin ); 
                    }
                    echo "</td><td>";
                    if ($admin = matchByName($row['fname'], $row['lname'])) {
                        showAdminInfo( $admin ); 
                    }
                    echo "</td><td>";
                    if ($admin = matchByAddress($row['address'])) {
                        showAdminInfo( $admin ); 
                    }
                    echo "</td><td><input type='text' size=8 ";
                    if ($row['parent_admin_id']) echo "value='" . $row['parent_admin_id'] . "' ";
                    echo "/> <button class='parent_id' id='" . $row['charidy_id'] . "'>connect</button></td></tr>";
                }
            }
            ?>
        </table>
    </body>
    <script src="http://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script>
        $(".parent_id").click( function() {
            var charidy_id = $(this).attr('id');
            var admin_id = $(this).parent().find('input').val();
            $.post('updateCharidy.php', { charidy_id : charidy_id, parent_id : admin_id }, function(success) {
                if (parseInt(success)) {
                    alert('updated');
                }
            });
        });
    </script>
</html>