<?php
$img = $_POST['imgBase64'];
$type = $_POST['type'];
$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace('data:image/jpeg;base64,', '', $img);
$img = str_replace('data:image/jpg;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$fileData = base64_decode($img);
//saving
$fileName = 'img_android/' . uniqid() . $type;
file_put_contents($fileName, $fileData);
   echo $fileName; //must be encode with array to access it in your response as an object since you use DataType:json
?>