<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

$auctions = array();
$sql = "select * from auctions where auction_name like '%5777%'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $auctions[] = $row;    
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Raffle Winners</title>
        <style>
            th, td {
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>
    
    <body>
        <? include('admin_header.php'); ?>
        <h1>Raffle Winners</h1>
        
        <?php if (!isset($_POST['submit'])) : ?>
        <form method="post" action="raffle_winners.php">
            <select name="auction">
                <?
                foreach ($auctions as $auction) {
                    echo "<option value='" . $auction['auction_id'] . "'>" . $auction['auction_name'] . "</option>";
                }
                ?>
            </select>
            <input type="submit" name="submit" value="Go" />
        </form>
        <?php else : ?>
        
        <?php
        $auction_id = $_POST['auction'];
        foreach ($auctions as $auction) {
            if ($auction['auction_id'] == $auction_id) break;
        }
        //echo "<pre>" . print_r($auction); echo "</pre>";
        // get all schools connected to admin
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolIDs = array();
        foreach ($schools as $id => $school) {
            $schoolIDs[] = $id;
        }
        
        // get all winners for raffle
        $winners = array();
        $sql = "select s.school_name, u.user_id, u.first, u.last, p.prize_id, p.prize_name, c.class_grade, c.class_sub  
                from auction_winners aw 
                join users u using (user_id) 
                join schools s on (u.school_id = s.school_id) 
                join prizes_auction p using (prize_id) 
                join classes c on c.class_id = u.class_id 
                where aw.auction_id = " . $auction_id . "
                and u.school_id in (" . implode(',', $schoolIDs) . ") 
                order by s.school_name, c.class_sub, c.class_grade, u.last, u.first";
        //echo $sql;
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc($result)) {
            $winners[] = $row;
        }
        
        if (!empty($winners)) {
            echo "<h2>" . $auction['auction_name'] . "</h2>";
            ?>
            <table>
                <tr>
                    <?php if (count($schools) > 1) echo "<th>School</th>"; ?>
                    <th>Grade</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Prize Won</th>
                </tr>
                <?php
                foreach ($winners as $winner) {
                    echo "<tr><td>";
                    if (count($schools) > 1) echo $winner['school_name'] . "</td><td>";
                    echo $winner['class_grade'] . ($winner['class_sub'] ? '-' . $winner['class_sub'] : '') . "</td><td>" .
                        $winner['last'] . "</td><td>" . $winner['first'] . "</td><td>" . $winner['prize_name'] . "</td></tr>";
                }
                ?>
            </table>
        <?php } else { ?>
            No Winners Yet.
        <?php } ?>
        <?php endif; ?>
    </body>
</html>