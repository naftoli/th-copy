<?php
header('Content-type: application/json');
# Communication between chabadorg.clhosting.org and partner
$PublicKey = '474DBD09-F59F-433D-A755-5A97594FC4E1';
$PrivateKey = base64_decode('R9WIfWVU7VXiiEporuuIvbEcFqIwzJYwmc5q4bb2/lQ=');
$timestamp = time();

$centers = $_REQUEST['ids'];
$center = $centers[0];

$BaseUrl = 'https://chabadorg.clhosting.org';
$RequestUrl = "/api/centers/$center?includeDepartments=true";
$URLToPost = "$BaseUrl$RequestUrl";

$raw_auth = hash_hmac('sha1', "$PublicKey|$timestamp|$RequestUrl", $PrivateKey, true);
$signature = base64_encode($raw_auth);

if (isset($_REQUEST['key']))
      $PostedKey = $_REQUEST['key'];
else
      $PostedKey = '';

$Curl_Session = curl_init($URLToPost);
curl_setopt ($Curl_Session, CURLOPT_POST, true);
curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, "Key=" . urlencode($PostedKey));
curl_setopt ($Curl_Session, CURLOPT_RETURNTRANSFER, true);
curl_setopt($Curl_Session, CURLOPT_HTTPHEADER, array
      (
            'Accept: text/json',
            'Content-Type: application/x-www-form-urlencoded',
            "Authorization: h=$PublicKey|$timestamp; s=$signature"
      )
);

$result = curl_exec($Curl_Session);
curl_close ($Curl_Session);

echo $result;