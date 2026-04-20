<?php

header('Content-Type: application/json; charset=utf-8');

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chayolei_shipping/class.chayoleiShipping.php';

if (($admin_user['auth'] ?? '') !== 'super') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

global $MASHPIA_DB;

$cs = new ChayoleiShipping();
$cs->ensureChayoleiHachayolShipmentsTable();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) GlobalSettings::getCurrentYear();
    $rows = $cs->getHachayolShipmentDefinitions($year);
    echo json_encode([
        'success' => true,
        'year' => $year,
        'shipments' => $rows,
        'max_shipment_num' => $cs->getMaxHachayolShipmentNum($year),
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        exit;
    }

    $year = (int) ($input['year'] ?? 0);
    $shipments = $input['shipments'] ?? null;

    if ($year < 5700 || !is_array($shipments)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid payload']);
        exit;
    }

    $seen = [];
    foreach ($shipments as $r) {
        $sn = (int) ($r['shipment_num'] ?? 0);
        $is = (int) ($r['issue_start'] ?? 0);
        $ie = (int) ($r['issue_end'] ?? 0);
        if ($sn < 1 || $sn > 99 || $is < 1 || $ie < 1 || $is > $ie) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Each row needs shipment # (1–99) and valid issue range.']);
            exit;
        }
        if (isset($seen[$sn])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Duplicate shipment number: ' . $sn]);
            exit;
        }
        $seen[$sn] = true;
    }

    try {
        $MASHPIA_DB->beginTransaction();
        $del = $MASHPIA_DB->prepare('DELETE FROM chayolei_hachayol_shipments WHERE year = ?');
        $del->execute([$year]);
        $ins = $MASHPIA_DB->prepare(
            'INSERT INTO chayolei_hachayol_shipments (year, shipment_num, issue_start, issue_end) VALUES (?, ?, ?, ?)'
        );
        foreach ($shipments as $r) {
            $ins->execute([
                $year,
                (int) $r['shipment_num'],
                (int) $r['issue_start'],
                (int) $r['issue_end'],
            ]);
        }
        $MASHPIA_DB->commit();
    } catch (Exception $e) {
        $MASHPIA_DB->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
