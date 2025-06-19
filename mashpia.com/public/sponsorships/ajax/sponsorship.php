<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$action = $_POST['action'];
$input = $_POST;

if ($action == 'create') {
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
    exit;
}

if ($action == 'update') {
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
    exit;
}

if ($action == 'delete') {
    $id = $input['sponsorship_id'];
    // first get the image path from the database
    $result = $MASHPIA_DB->prepare("SELECT image FROM mashpiadb.sponsorships WHERE sponsorship_id = :id");
    $res = $result->execute([':id' => $id]);
    $image = $res->fetch(PDO::FETCH_ASSOC)['image'];
    if ($image) {
        // delete the image from the disk
        $link = $_SERVER['DOCUMENT_ROOT'] . '/sponsorships/images/' . $image;
        if (file_exists($link)) {
            unlink($link);
        }
    }
    $result = $MASHPIA_DB->prepare("DELETE FROM mashpiadb.sponsorships WHERE sponsorship_id = :id");
    $res = $result->execute([':id' => $id]);
    if ($res) {
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record deletion failed']);
    }
    exit;
}

// Handle file uploads
function handleFileUpload() {
    $file = $_FILES['image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/sponsorships/images/';
        $fileName = $file['name'] . '_' . time();
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            echo $fileName;
            exit;
            return $fileName;
        }
    } 
    return null;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;