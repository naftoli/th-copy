<?php
// authenticate
if (isset($_POST['auth']) && $_POST['auth'] === 'JTaMd105nT' && isset($_POST['school'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
    $year = GlobalSettings::getChidonYear();
    $school_number = mysql_real_escape_string($_POST['school']);
    $info = [];
    $sql = "
        SELECT 
            u.user_id, 
            u.first,
            u.last,
            u.first_he,
            u.last_he,
            u.dob,
            u.user_serial,
            u.user_code,
            c.class_grade,
            c.class_sub 
        FROM
            users u
                JOIN
            schools s USING (school_id)
                JOIN
            classes c ON c.class_id = u.class_id 
        WHERE
            s.school_number = " . $school_number;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        // find out if child is currently enrolled into chidon this yr
        $sqlChidon = "select * from th_chidon where year = $year and user_id = " . $row['user_id'];
        $resultChidon = mysql_query($sqlChidon);
        $row['chidon_enrolled'] = mysql_num_rows($resultChidon);
        $info[] = $row;
    }
    echo json_encode($info);
}
