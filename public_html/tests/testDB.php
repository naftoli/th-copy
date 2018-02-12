<?php
require_once '../class.db.php';

try {
    $db = DB::getInstance();
    var_dump($db);
} catch (PDOException $e) {
    echo $e->getMessage();
    echo "error";
}