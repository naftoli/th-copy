<?
require_once '../db.php';
require_once '../class.mishnaInfo.php';
$mesechtos = MishnaInfo::getMesechtosByList();
echo json_encode($mesechtos);
?>