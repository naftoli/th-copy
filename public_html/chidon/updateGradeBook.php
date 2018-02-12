<?php
require '../db.php';

$updates = array();
$sql = "select tc.th_chidon_id, tc.user_id, c.class_grade
        from th_chidon tc
        join users u using (user_id)
        join classes c on u.class_id = c.class_id 
        where tc.year = 5778";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $id = $row['th_chidon_id'];
    $user_id = $row['user_id'];
    $grade = $row['class_grade'];
    switch ($grade) {
        case 4:
            $book = 1;
            break;
        case 5:
            $book = 2;
            break;
        case 6:
            $book = 3;
            break;
        case 7:
            $book = 4;
            break;
        case 8:
            $book = 5;
            break;
    }
    $updates[] = "update th_chidon set grade = '" . $grade . "', book = '" . $book . "' where th_chidon_id = " . $id;
}

//echo "<pre>"; print_r( $updates ); echo "</pre>";
foreach ($updates as $sql) {
    mysql_query( $sql );
}
echo "done.";