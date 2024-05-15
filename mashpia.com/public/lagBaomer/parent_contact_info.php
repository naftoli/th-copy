<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$fields = ['Parent ID', 'Parent Name', 'Mother Name', 'Father Name', 'Phone Number', 'Mother Phone Number', 'Father Phone Number', 'Email', 'Child Serial Number', 'Child First Name', 'Child Last Name', 'Base Name', 'Rank'];
$db_fields = ['admin_id', ['first', 'last'], 'mother', 'father', 'admin_phone_home', 'admin_phone_mobile2', 'admin_phone_mobile', 'admin_email', 'user_serial', 'child_first', 'child_last', 'school_name', 'rank_ord'];

$ranks = [];
$sql = "select rank_ord, rank_name from ranks";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}

$users = [];
// create sql statement
$sql = "select a.*, u.user_serial, u.first as child_first, u.last as child_last, s.school_name, MAX( rm.rank_ord ) as rank_ord  
        from users u
        join schools s using ( school_id )
        join rank_marks rm using ( user_id ) 
        join ranks r using ( rank_ord ) 
        join admin_auths aa on ( aa.id = u.user_id and aa.auth = 'user' ) 
        join admins a using ( admin_id ) 
        where u.user_registered > 0 
        and r.rank_ord in (13, 14) ";
$sql .= " and u.school_id in ( " . implode( ',', array_keys( $schools ) ) . " ) ";
$sql .= " group by u.user_id ";
$sql .= " order by u.last, u.first";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
    <head>
        <title>Parent Contact Info</title>
        <style>
            table {
                border-collapse: collapse;
                font-family: "Arial", sans-serif;
                font-size: 12px;
            }
            tr, th, td {
                padding: 5px;
                border-bottom: 1px solid black;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <?php foreach ( $fields as $field ) : ?>
                    <th><?= $field ?></th>
                <?php endforeach; ?>
            </tr>
            <?php foreach ( $users as $user ) : ?>
                <tr>
                    <?php foreach ( $db_fields as $field ) : ?>
                        <td><?= is_array( $field ) ? $user[$field[0]] . ' ' . $user[$field[1]] : $field == 'rank_ord' ? $user[$ranks[$field]] : $user[$field] ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </body>
</html>