<?php
ini_set('display_errors', TRUE);
$admin_auth = array('school'); 
require('../header.php');

// a little bit of security
if (
    $admin_user['auth'] == 'super' &&
    (
        ($_SERVER['HTTP_HOST'] == '192.168.56.4' && $_SERVER['SERVER_NAME'] == '192.168.56.4') ||
        ($_SERVER['HTTP_HOST'] == 'mashpia.com' && $_SERVER['SERVER_NAME'] == 'mashpia.com')
    )
) {
    // retrieve records from db
    $info = array();
    $institutions = array();
    $sql = "select * from chabad_mosdos";
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $mosadId = $row['mosad_id'];
        $parentId = $row['primary_mosad_id'];
        $institution = $row['name'];
        $category = $row['mosad_category'];
        $type = $row['mosad_type'];

        if ($parentId) {
            $main = $parentId;
        } else {
            $main = $mosadId;
        }
        if (!array_key_exists($main, $institutions)) $institutions[$main] = $institution;
        $desc = $category . " (" . $institution . ")";
        if ($type == 'Primary') $desc .= " [<strong><i>primary</i></strong>]";
        $info[$main][$institutions[$main]][] = $desc;
    }
} else {
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
                font-family: Arial;
            }
            th, td {
                width: auto;
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>Mosad ID</th>
                <th>Mosad Name</th>
                <th>Type(s) of Mosdos</th>
            </tr>
            <?php
            foreach ($info as $id => $other) {
                foreach ($other as $first => $more) {
                    echo "<tr><td>" . $id . "</td><td>" . $first . "</td><td>";
                    foreach ($more as $cat) {
                        echo $cat . "<br />";
                    }
                    echo "</td></tr>";
                }
            }
            ?>
        </table>
    </body>
</html>