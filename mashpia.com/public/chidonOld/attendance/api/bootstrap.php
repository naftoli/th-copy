<?php
declare(strict_types=1);

require_once __DIR__ . '/functions/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php'; // PDO $MASHPIA_DB
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
require_once __DIR__ . '/../classes/staffManager.php';

function attendance_json_ok(array $data = []): void {
    echo json_encode(array_merge([ 'success' => true ], $data));
    exit;
}

function attendance_json_error(string $message, $details = null, int $httpStatus = 200): void {
    if (!headers_sent()) {
        http_response_code($httpStatus);
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'error' => $message,
        'details' => $details,
    ]);
    exit;
}

function attendance_get_post_string(string $key): string {
    if (!isset($_POST[$key])) return '';
    if (is_array($_POST[$key])) return '';
    return trim((string)$_POST[$key]);
}

function attendance_get_post_array(string $key): array {
    if (!isset($_POST[$key]) || !is_array($_POST[$key])) return [];
    return $_POST[$key];
}

function attendance_require_staff(): StaffManager {
    $token = attendance_get_post_string('login');
    if ($token === '') {
        attendance_json_error('Invalid Login');
    }
    $staffIdRaw = encrypt_decrypt('decrypt', $token);
    $staffId = is_numeric($staffIdRaw) ? (int)$staffIdRaw : 0;
    if (!$staffId) {
        attendance_json_error('Invalid Login');
    }
    $sm = new StaffManager();
    if (!$sm->setStaffByID($staffId)) {
        attendance_json_error('Invalid Login');
    }
    return $sm;
}

