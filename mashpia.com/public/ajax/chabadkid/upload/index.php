<?php
// authenticate
if (isset($_POST['auth']) && $_POST['auth'] === 'JTaMd105nT' && isset($_POST['school'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
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
            c.class_sub, 
            tc.th_chidon_id 
        FROM
            users u
                JOIN
            schools s USING (school_id)
                JOIN
            classes c ON c.class_id = u.class_id 
                LEFT JOIN 
            th_chidon tc USING (user_id) 
        WHERE
            s.school_number = " . $school_number;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[] = $row;
    }
    echo json_encode($info);
}
