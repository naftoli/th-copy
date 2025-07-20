<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/db.php';
header('Content-Type: application/json');

// Get parameters
$show_chidon = intval($_GET['show_chidon'] ?? '0');
$show_chayolei = intval($_GET['show_chayolei'] ?? '0');

try {  
    $sql = "SELECT * 
            FROM screen_announcements 
            WHERE (from_date <= CURDATE() AND to_date >= CURDATE())";

    if ($show_chidon === 1 && $show_chayolei === 0) {
        $sql .= " AND type = 'chidon'";
    }
    if ($show_chayolei === 1 && $show_chidon === 0) {
        $sql .= " AND type = 'chayolei'";
    }

    $sql .= " ORDER BY screen_announcement_id DESC";
    $stmt = $MASHPIA_DB->query($sql);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $announcements]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 