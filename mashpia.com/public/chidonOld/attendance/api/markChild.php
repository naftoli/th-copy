<?php
require_once __DIR__ . '/bootstrap.php';

$sm = attendance_require_staff();
$db = $GLOBALS['MASHPIA_DB'] ?? null;
if (!$db) {
    attendance_json_error('Database not available');
}

$timeId = attendance_get_post_string('time_id');
$chidonId = attendance_get_post_string('chidon_id');
$checkedRaw = attendance_get_post_string('checked');
$checked = ($checkedRaw === 'true' || $checkedRaw === '1' || strtolower($checkedRaw) === 'yes');

if ($timeId === '' || $chidonId === '') {
    attendance_json_error('Invalid Request');
}
if (!ctype_digit((string)$timeId) || !ctype_digit((string)$chidonId)) {
    attendance_json_error('Invalid Request');
}

// Authorize: ensure the staff may mark this child for this time
// - time must belong to current year
// - time's group (att_type_number) must be in staff assignments
// - child must belong to that group (bunk_number or walking_group) and year
$timeStmt = $db->prepare("
    SELECT att_time_id, att_type, att_type_number, year
    FROM th_chidon_attendance_times
    WHERE att_time_id = :id AND year = :year
    LIMIT 1
");
$timeStmt->execute([
    ':id' => (int)$timeId,
    ':year' => $sm->getYear(),
]);
$timeRow = $timeStmt->fetch(PDO::FETCH_ASSOC);
if (!$timeRow) {
    attendance_json_error('Invalid time');
}

$group = (string)$timeRow['att_type_number'];
if (!$sm->assertGroupsAllowed([$group])) {
    attendance_json_error('Not authorized for this group');
}

$field = ($timeRow['att_type'] === 'bunk') ? 'bunk_number' : 'walking_group'; // default matches current app
$childStmt = $db->prepare("
    SELECT th_chidon_id
    FROM th_chidon
    WHERE th_chidon_id = :cid AND year = :year AND {$field} = :group
    LIMIT 1
");
$childStmt->execute([
    ':cid' => (int)$chidonId,
    ':year' => $sm->getYear(),
    ':group' => $group,
]);
if (!$childStmt->fetch()) {
    attendance_json_error('Not authorized for this child');
}

if ($checked) {
    // Idempotent: insert or update
    $stmt = $db->prepare("
        INSERT INTO th_chidon_attendance_marks (att_time_id, th_chidon_id, marked, marked_by)
        VALUES (:time_id, :chidon_id, 1, :staff_id)
        ON DUPLICATE KEY UPDATE marked = 1, marked_by = VALUES(marked_by)
    ");
    $ok = $stmt->execute([
        ':time_id' => (int)$timeId,
        ':chidon_id' => (int)$chidonId,
        ':staff_id' => $sm->getID(),
    ]);
} else {
    $stmt = $db->prepare("
        DELETE FROM th_chidon_attendance_marks
        WHERE att_time_id = :time_id AND th_chidon_id = :chidon_id
    ");
    $ok = $stmt->execute([
        ':time_id' => (int)$timeId,
        ':chidon_id' => (int)$chidonId,
    ]);
}

attendance_json_ok([ 'updated' => (bool)$ok ]);