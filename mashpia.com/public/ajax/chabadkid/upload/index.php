<?php
// authenticate
if (isset($_POST['auth']) && $_POST['auth'] === 'JTaMd105nT' && isset($_POST['school'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
    $year = GlobalSettings::getChidonYear(); // GlobalSettings class already loaded in header
    $school_number = isset($_POST['school']) ? mysql_real_escape_string($_POST['school']) : 0;
    $user_id = isset($_POST['user']) ? mysql_real_escape_string($_POST['user']) : 0;
    $rankStmt = $MASHPIA_DB->prepare("
        SELECT 
            rank_ord, date_promoted, rank_name
        FROM
            rank_marks
                JOIN
            ranks USING (rank_ord)
        WHERE
            user_id = :user
        ORDER BY rank_ord DESC
        LIMIT 1
    ");

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
            classes c ON c.class_id = u.class_id ";
    if ($school_number > 0 && $user_id > 0) {
        $sql .= "WHERE s.school_number = $school_number AND u.user_id = $user_id";
    } else if ($school_number > 0) {
        $sql .= "WHERE s.school_number = $school_number";
    } else if ($user_id > 0) {
        $sql .= "WHERE u.user_id = $user_id";
    }
    $stmt = $MASHPIA_DB->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        // add chidon id to row (if exists)
        $row['th_chidon_id'] = 0;
        // find out if child is currently enrolled into chidon this yr
        $enrolled = false;
        $sqlChidon = "select * from th_chidon where year = $year and user_id = " . $row['user_id'];
        $resultChidon = $MASHPIA_DB->query($sqlChidon);
        if ($chidon = $resultChidon->fetch()) {
            $enrolled = true;
            $row['th_chidon_id'] = $chidon['th_chidon_id'];
        }
        $row['chidon_enrolled'] = $enrolled ? 1 : 0;
        // get rank and date promoted
        $rankStmt->execute(['user' => $row['user_id']]);
        $rankRow = $rankStmt->fetch(PDO::FETCH_ASSOC);
        if ($rankRow) {
            $row['rank'] = $rankRow['rank_name'];
            $row['date_promoted'] = jdtogregorian($rankRow['date_promoted']);
        } else {
            $row['rank'] = '';
            $row['date_promoted'] = '';
        }
        $info[] = $row;
    }
    echo json_encode($info);
}
