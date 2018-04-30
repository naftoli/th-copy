<?php
header('Content-Type: application/json');

require '../../db.php';

$key = mysql_real_escape_string($_POST['key']);
if ($key == 'cth5778!') {
    
    $students = array();
    $name = mysql_real_escape_string($_POST['name']);
    $school_id = mysql_real_escape_string($_POST['id']);
    
    if (strlen($name) > 2) {
        //get ranks
        $ranks = array();
        $sql = "select rank_ord, rank_name from ranks";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $ranks[$row['rank_ord']] = $row['rank_name'];
        }
        
        $sql = "select u.user_id, u.first, u.last, u.user_serial, c.class_grade, c.class_sub, u.mobile_pic, t.thumb   
                from users u 
                join classes c on u.class_id = c.class_id
                left join thumbs t on u.user_photo_id = t.file_id 
                where u.school_id = " . $school_id . "
                and u.last like '" . $name . "%'  
                order by u.last, u.first, c.class_grade, c.class_sub";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $rankSql = "select max(rank_ord) as rank from rank_marks where user_id = " . $row['user_id'];
            $rankRes = mysql_query($rankSql);
            $rankRow = mysql_fetch_assoc($rankRes);
            $rank = $rankRow['rank'];
            $students[] = array(
                'serial_num'=> $row['user_serial'],
                'first'     => $row['first'],
                'last'      => $row['last'],
                'grade'     => $grade,
                'thumb'     => 'https://mashpia.com/mobile/reg/thumbs/' . $row['thumb'],
                'pic'       => 'https://mashpia.com/mobile/reg/' . $row['mobile_pic'],
                'rank'      => $ranks[$rank]
            );
        }
    }
    
    echo json_encode($students);
}
?>