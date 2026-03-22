<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

function getColumnTypes($table) {
    global $MASHPIA_DB;
    $columns = [];
    $stmt = $MASHPIA_DB->query("SHOW COLUMNS FROM " . $table);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[$row['Field']] = [
            'type' => $row['Type'],
            'null' => $row['Null'],
            'default' => $row['Default']
        ];
    }
    return $columns;
}

$input = json_decode(file_get_contents('php://input'), true);
$table = "th_chidon";

$mapping = [
    'track_passed'  => 'th_chidon_info'
];

$columns = getColumnTypes($table);

$success = true;
$MASHPIA_DB->beginTransaction();

try {
    foreach ($input as $user_id => $more) {
        foreach ($more as $field => $val) {
            if ($field == 'school_id_chidon') {
                $field = 'school_id';
            }
            if (array_key_exists($field, $mapping)) {
                $table = $mapping[$field];
            }
            // figure out if val needs to be a string or an int
            if (
                strpos($columns[$field]['type'], 'int') !== false || 
                strpos($columns[$field]['type'], 'float') !== false || 
                strpos($columns[$field]['type'], 'decimal') !== false || 
                strpos($columns[$field]['type'], 'tinyint') !== false 
            ) {
                $val = empty($val) ? 0 : (int) $val;
            }
            // make sure to have null for date_paid
            if ($field == 'date_paid') {
                $val = NULL;
            }
            
            $sql = "UPDATE " . $table . " SET " . $field . " = :val WHERE user_id = :user AND year = :year";
            $stmt = $MASHPIA_DB->prepare($sql);
            $res = $stmt->execute([
                ':val' => $val,
                ':user' => $user_id,
                ':year' => $year
            ]);
            if (! $res) {
                $success = false;
                $error = $stmt->errorInfo();
                break;
            }
        }
    }
} catch (PDOException $e) {
    $success = false;
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

if ($success) {
    $MASHPIA_DB->commit();
    echo json_encode(['success' => $success]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success' => $success,
        'error' => $error
    ]);
}