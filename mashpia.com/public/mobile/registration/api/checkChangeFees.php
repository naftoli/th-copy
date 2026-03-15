<?php
// Both dates in New York timezone
$timezone = new DateTimeZone('America/New_York');
$result = new DateTime('now', $timezone) > new DateTime('2026-10-07T00:00:00', $timezone) ? 1 : 0;

// Return proper JSON response
header('Content-Type: application/json');
echo json_encode($result);