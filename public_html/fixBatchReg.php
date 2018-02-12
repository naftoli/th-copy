<?php
require 'db.php';

$info = array();
$sql = "select * from transactions t
        where t.trans_date > '2016-07-31'
        and t.admin_id is null 
        order by t.trans_date desc";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            th, td {
                padding: 5px;
                font-size: 12px;
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <?php
        $users = array();
        foreach ($info as $row) {
            $amount = intval($row['amount']);
            $desc = $row['description'];
            if (strpos($desc, 'child') !== false) {
                $pos = strpos($desc, '->');
                $newDesc = substr($desc, $pos+2);
                $ids = explode(':', $newDesc);
                foreach ($ids as $id) {
                    $users[] = $id;
                }
            }
        }
        //echo "<pre>"; print_r($users); echo "</pre>";
        $updated = 0;
        foreach ($users as $user) {
            $sql = "update users set user_registered = now() where user_registered is null and user_id = " . $user;
            if (mysql_query($sql)) $updated++;
        }
        echo "updated: " . $updated;
        ?>
    </body>
</html>