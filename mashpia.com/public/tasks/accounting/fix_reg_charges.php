<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$stmt = $MASHPIA_DB->query("
    SELECT 
        *
    FROM
        transactions 
    WHERE
        trans_date >= '2024-08-07 02:19:54'
    ORDER BY trans_id DESC 
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$fields = ['trans_id', 'school_id', 'trans_date', 'description', 'amount', 'admin_id', 'users_registered'];
$qrys = [];

$stmtInsert = $MASHPIA_DB->prepare("
    INSERT IGNORE INTO registration_charges 
    SET trans_id = :trans_id, 
        user_id = :user_id, 
        school_id = :school_id, 
        admin_id = :admin_id, 
        type = :type, 
        amount = :amount, 
        date = :date, 
        year = :year, 
        discount = :discount
");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Transactions</title>
    </head>
    <style>
        body, tr, th, td {
          font-family: Arial;
          font-size: 14px;
        }
        tr, th, td {
          padding: 6px;
        }
    </style>
    <body>
        <h1>Transactions</h1>
        <table style="width:95%; margin: auto;">
            <tr>
                <?php
                foreach ($fields as $field) {
                    echo "<th>$field</th>";
                }
                ?>
            </tr>
            <?php
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($fields as $field) {
                    echo "<td>" . $row[$field] . "</td>";
                }
                echo "</tr>";
            }
            // create qry for reg_charges table
            $trans_id = $row['trans_id'];
            $school_id = $row['school_id'];
            $admin_id = $row['admin_id'];

            ?>
        </table>
    </body>
</html>