<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$input = json_decode(file_get_contents('php://input'), true);

if (!$input['table']) {
    die('No table specified');
}

$result = $MASHPIA_DB->query("SELECT * FROM mashpiadb." . $input['table']);
$result = $result->fetchAll(PDO::FETCH_ASSOC);
$schema = $MASHPIA_DB->query("
    SELECT 
        COLUMN_NAME,
        DATA_TYPE,
        IS_NULLABLE,
        COLUMN_DEFAULT,
        COLUMN_KEY,
        EXTRA
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'mashpiadb'  
    AND TABLE_NAME = '" . $input['table'] . "'
");
$schema = $schema->fetchAll(PDO::FETCH_ASSOC);
$parshos = $MASHPIA_DB->query("SELECT * FROM mashpiadb.parshos where start >= 2460847");
$parshos = $parshos->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $result, 'schema' => $schema, 'parshos' => $parshos]);