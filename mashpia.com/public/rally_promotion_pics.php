<?php
$admin_auth = ['school'];
require_once 'db.php';
$users = [];
$sql = "
    SELECT 
        rm.date_promoted,
        r.rank_name,
        u.user_id,
        u.last,
        u.first,
        c.class_grade,
        c.class_sub,
        c.class_teacher, 
        u.mobile_pic, 
        u.user_photo_id
    FROM
        `rank_marks` rm
            JOIN
        ranks r USING (rank_ord)
            JOIN
        users u USING (user_id)
            JOIN
        classes c ON (u.class_id = c.class_id)
    WHERE
        user_registered > 0 AND rm.rank_ord != 1
            AND rm.date_promoted >= 2458620
            AND rm.date_promoted <= 2458745
            AND rm.rank_ord > 9
    ORDER BY rank_ord desc, last , first
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
        <?php
        $prevRank = '';
        foreach ( $users as $user ) {
            $rank = $user['rank_name'];
            if ( $prevRank != $rank ) {
                echo "<h1>" . $rank . "</h1>";
                $prevRank = $rank;
            }
            $pic = $user['mobile_pic'] ? '/mobile/reg/' . $user['mobile_pic'] : '/file_view.php?id=' . $user['user_photo_id'];
            echo "<img src='" . $pic . "' style='max-width: 250px; padding: 5px;' />";
        }
        ?>
    </body>
</html>