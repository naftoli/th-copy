<?php
$teams = isset($_POST['team']) ? (array) $_POST['team'] : [];
$grades = isset($_POST['grade']) ? (array) $_POST['grade'] : [];
$gender = isset($_POST['gender']) ? strtoupper(trim($_POST['gender'])) : 'B';

$grade_placeholders = [];
foreach (array_map('intval', $grades) as $i => $g) {
    $grade_placeholders[] = ':grade' . $i;
}
$grade_list = implode(',', $grade_placeholders ?: ['0']);

$sql = "SELECT * FROM th_chidon_winners tcw 
        JOIN users u ON u.user_serial = tcw.serial 
        JOIN th_chidon tc ON tc.user_id = u.user_id AND tc.year = tcw.year 
        WHERE tcw.year = " . intval($year) . " 
        AND tcw.grade IN (" . $grade_list . ") ";
if (in_array($gender, ['F', 'M'])) {
    $sql .= "AND tcw.gender = :gender ";
}

// figure out the team sql
$blue_trophy = 0;
$teamSql = [];
$trophies = [];
$khk_trophies = [];

foreach ($teams as $team) {
    switch ($team) {
        case 'Mishne Torah':
        case 'Sefer Hamitzvos':
            $teamSql[] = $team;
            break;
        case 'Blue Trophy':
            $blue_trophy = 1;
            break;
        case 'Gold Trophy':
        case 'Silver Trophy':
        case 'Bronze Trophy':
            $trophy = explode(' ', $team)[0];
            $trophies[] = $trophy;
            break;
        case 'KHK Gold Trophy':
        case 'KHK Silver Trophy':
        case 'KHK Bronze Trophy':
            $trophy = explode(' ', $team)[1];
            $khk_trophies[] = $trophy;
            break;
    }
}

if ($blue_trophy) 
    $sql .= " AND tcw.blue_trophy = 1";
if (!empty($teamSql)) {
    $sql .= " AND tcw.team IN (" . implode(',', array_map(function ($t) use ($MASHPIA_DB) {
        return $MASHPIA_DB->quote($t);
    }, $teamSql)) . ")";
}
if (!empty($trophies)) {
    $sql .= " AND tcw.trophy IN (" . implode(',', array_map([$MASHPIA_DB, 'quote'], $trophies)) . ")";
}
if (!empty($khk_trophies)) {
    $sql .= " AND tcw.khk_trophy IN (" . implode(',', array_map([$MASHPIA_DB, 'quote'], $khk_trophies)) . ")";
}
$sql .= " GROUP BY tcw.serial";

$stmt = $MASHPIA_DB->prepare($sql);
$params = [];
foreach (array_map('intval', $grades) as $i => $g) {
    $params[':grade' . $i] = $g;
}
if (in_array($gender, ['F', 'M'])) {
    $params[':gender'] = $gender;
}
$stmt->execute($params);
// $stmt->debugDumpParams();
$info = $stmt->fetchAll(PDO::FETCH_ASSOC);