<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';  
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        left join thumbs t on u.user_photo_id = t.file_id 
        where date_paid > 0 and year = " . $year . " and tc.school_id in (" . implode(',', array_keys( $schools )) . ")";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['school_id']][] = $row;
}
// echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Review Enrollment</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
            .pics img {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                border-color: #aaa;
                margin-left: auto;
                margin-right: auto;
                display: block;
            }
        </style>
    </HEAD>

    <BODY>
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Chidon Pictures</h1>
        <?php foreach ( $info as $id => $children ) : ?>
            <h2><?= $schools[$id] ?></h2>
            <table class="pics">
                <tr>
                    <th>Chidon ID</th>
                    <th>Name</th>
                    <th>Chidon Picture</th>
                </tr>
                <?php
                foreach ( $children as $child ) {
                    $img = '';
                    if ( !empty($child['chidon_pic']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/' . $child['chidon_pic']) ) {
                        $img = '/mobile/reg/' . $child['chidon_pic'];
                    } else if ( !empty($child['mobile_pic']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/' . $child['mobile_pic']) ) {
                        $img = '/mobile/reg/' . $child['mobile_pic'];
                    } else if ( !empty($child['thumb']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/thumbs/' . $child['thumb']) ) {
                        $img = '/mobile/reg/thumbs/' . $child['mobile_pic'];
                    }
                    echo "<tr><td>" . $child['th_chidon_id'] . "</td><td>" . $child['first'] . ' ' . $child['last'] . "</td><td>";
                    echo "<img src='" . $img . "' /></td></tr>";
                }
                ?>
            </table>
        <?php endforeach; ?>  
    </BODY>
</HTML>