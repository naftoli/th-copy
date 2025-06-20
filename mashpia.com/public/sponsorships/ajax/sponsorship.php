<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
if (empty($_POST)) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

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
    global $input, $MASHPIA_DB;

    $start_date = $input['start_date'];
    $end_date = $input['end_date'];
    $sponsor = $input['sponsor'];
    $reason = $input['reason'];
    $name = $input['name'];
    $email = $input['email'];
    $phone = $input['phone'];
    $amount_paid = $input['amount_paid'];
    if (isset($input['image_changed'])) {
        if ($input['image_changed'] == 'true') {
            $image = handleFileUpload();
        } 
    } else {
        $image = null;
    }

    $result = $MASHPIA_DB->prepare("
        INSERT INTO mashpiadb.sponsorships (start_date, end_date, sponsor, reason, name, email, phone, amount_paid) 
        VALUES (:start_date, :end_date, :sponsor, :reason, :name, :email, :phone, :amount_paid)");
    $res = $result->execute([':start_date' => $start_date, ':end_date' => $end_date, ':sponsor' => $sponsor, ':reason' => $reason, ':name' => $name, ':email' => $email, ':phone' => $phone, ':amount_paid' => $amount_paid]);
    if ($res) { 
        $sponsorship_id = $MASHPIA_DB->lastInsertId();
        // update image if it was changed
        if (isset($image)) {
            if ($image) {
                $result = $MASHPIA_DB->prepare("UPDATE mashpiadb.sponsorships SET image = :image WHERE sponsorship_id = :id");
                $res = $result->execute([':image' => $image, ':id' => $sponsorship_id]);
            } else if ($image == null) {
                // delete image file
                $filePath = $_SERVER['DOCUMENT_ROOT'] . '/sponsorships/images/' . $image;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                // update database
                $result = $MASHPIA_DB->prepare("UPDATE mashpiadb.sponsorships SET image = NULL WHERE sponsorship_id = :id");
            }
        }
        echo json_encode(['success' => true, 'message' => 'Record created successfully', 'id' => $sponsorship_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record creation failed']);
    }
}

function update() {
    global $input, $MASHPIA_DB;

    $id = $input['sponsorship_id'];
    $start_date = $input['start_date'];
    $end_date = $input['end_date'];
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
        UPDATE mashpiadb.sponsorships SET start_date = :start_date, end_date = :end_date, sponsor = :sponsor, reason = :reason, name = :name, image = :image, email = :email, 
            phone = :phone, amount_paid = :amount_paid WHERE sponsorship_id = :id");
    $res = $result->execute([':id' => $id, ':start_date' => $start_date, ':end_date' => $end_date, ':sponsor' => $sponsor, ':reason' => $reason, ':name' => $name, ':image' => $image, ':email' => $email, ':phone' => $phone, ':amount_paid' => $amount_paid]);
    
    if ($res) { 
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record update failed']);
    }
}

function delete() {
    global $input, $MASHPIA_DB;

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
        // split file name into name and extension
        $fileInfo = pathinfo($file['name']);
        $fileName = $fileInfo['filename'] . '_' . time() . $fileInfo['extension'];
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $fileName;
        } 
    } 
    return null;
}