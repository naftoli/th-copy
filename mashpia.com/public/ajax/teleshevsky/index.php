<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
// authenticate
if (isset($_POST['auth']) && $_POST['auth'] === 'JTaMd105nT' && isset($_POST['school'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
    $school_number = mysql_real_escape_string($_POST['school']);
    $info = [];
    $sql = "
        SELECT 
            u.first,
            u.last,
            u.first_he,
            u.last_he,
            u.dob,
            u.user_serial AS serial_number,
            u.user_code AS barcode,
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
        $info[] = $row;
    }
    echo json_encode($info);
}
