<?php
require __DIR__ . '/../../../api/header/db.php';
$stmt = $MASHPIA_DB->prepare("
    SELECT gender FROM users WHERE user_id = :id
");
$stmt->execute([':id' => $_POST['user_id']]);

