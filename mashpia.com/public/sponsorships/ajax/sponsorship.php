<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$input = $_POST && count($_POST) ? $_POST : json_decode(file_get_contents('php://input'), true);

switch ($input['action']) {
    case 'create':
        create();
        break;
    case 'update':
        update();
        break;
    case 'delete':
        delete();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function create() {
    global $input;

    $parsha_id = $input['parsha_id'];
    $sponsor = $input['sponsor'];
    $reason = $input['reason'];
    $name = $input['name'];
    $email = $input['email'];
    $phone = $input['phone'];
    $amount_paid = $input['amount_paid'];
    if (isset($input['image_changed'])) {
        $image = handleFileUpload();
    }
    else $image = null;

    $result = $MASHPIA_DB->prepare("
        INSERT INTO mashpiadb.sponsorships (parsha_id, sponsor, reason, name, image, email, phone, amount_paid) 
        VALUES (:parsha_id, :sponsor, :reason, :name, :image, :email, :phone, :amount_paid)");
    $res = $result->execute([':parsha_id' => $parsha_id, ':sponsor' => $sponsor, ':reason' => $reason, ':name' => $name, ':image' => $image, ':email' => $email, ':phone' => $phone, ':amount_paid' => $amount_paid]);
    
    if ($res) { 
        echo json_encode(['success' => true, 'message' => 'Record created successfully', 'id' => $MASHPIA_DB->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record creation failed']);
    }
}

function update() {
    global $input;

    $id = $input['sponsorship_id'];
    $parsha_id = $input['parsha_id'];
    $sponsor = $input['sponsor'];
    $reason = $input['reason'];
    $name = $input['name'];
    $email = $input['email'];
    $phone = $input['phone'];
    $amount_paid = $input['amount_paid'];
    if (isset($_FILES['image'])) {
        $image = handleFileUpload();
    } else if (isset($input['image_removed'])) {
        $image = null;
    } else {
        $image = $input['image'] ?? null;
    }

    $result = $MASHPIA_DB->prepare("
        UPDATE mashpiadb.sponsorships SET parsha_id = :parsha_id, sponsor = :sponsor, reason = :reason, name = :name, image = :image, email = :email, 
            phone = :phone, amount_paid = :amount_paid WHERE sponsorship_id = :id");
    $res = $result->execute([':id' => $id, ':parsha_id' => $parsha_id, ':sponsor' => $sponsor, ':reason' => $reason, ':name' => $name, ':image' => $image, ':email' => $email, ':phone' => $phone, ':amount_paid' => $amount_paid]);
    
    if ($res) { 
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record update failed']);
    }
}

function delete() {
    global $input;

    $sponsorship_id = $input['sponsorship_id'];
    $image_files = $input['image_files'] ?? [];
    
    try {
        // Delete image files first
        foreach ($image_files as $filename) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/sponsorships/images/' . $filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Delete database record
        $stmt = $MASHPIA_DB->prepare("DELETE FROM mashpiadb.sponsorships WHERE sponsorship_id = ?");
        $stmt->execute([$sponsorship_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Record deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting record: ' . $e->getMessage()
        ]);
    }
}

// Handle file uploads
function handleFileUpload() {
    $file = $_FILES['image'];
    if (! $file['error']) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/sponsorships/images/';
        $fileName = $file['name'] . '_' . time();
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $fileName;
        } 
    } 
    return null;
}