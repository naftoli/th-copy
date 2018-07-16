<?php

# Communication between chabadorg.clhosting.org and partner

// $PublicKey = '474DBD09-F59F-433D-A755-5A97594FC4E1';
// $PrivateKey = base64_decode('R9WIfWVU7VXiiEporuuIvbEcFqIwzJYwmc5q4bb2/lQ=');
// $timestamp = time();
// $URLToPost = 'https://chabadorg.clhosting.org/api/login/authenticate';

// $raw_auth = hash_hmac('sha1', "$PublicKey|$timestamp|/api/login/authenticate", $PrivateKey, true);
// $signature = base64_encode($raw_auth);

// if (isset($_POST['key']))
// 	$PostedKey = $_POST['key'];
// else
// 	$PostedKey = '';

// header('Content-type: application/json');

// $Curl_Session = curl_init($URLToPost);
// curl_setopt ($Curl_Session, CURLOPT_POST, true);
// curl_setopt ($Curl_Session, CURLOPT_POSTFIELDS, "Key=" . urlencode($PostedKey));
// curl_setopt ($Curl_Session, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($Curl_Session, CURLOPT_HTTPHEADER, array
//       (
//             'Accept: text/json',
//             'Content-Type: application/x-www-form-urlencoded',
//             "Authorization: h=$PublicKey|$timestamp; s=$signature"
//       )
// );

// $result = curl_exec($Curl_Session);
// $http_status = curl_getinfo($Curl_Session, CURLINFO_HTTP_CODE);
// curl_close ($Curl_Session);

// echo $result;

// $response_array = json_decode($result, true);

// if ($http_status == 200)
// {
// 	/* User has been verified */
// }
// else
// {
// 	/* Invalid User or key, see status or response */
// }
$public = '474DBD09-F59F-433D-A755-5A97594FC4E1';
$private = 'R9WIfWVU7VXiiEporuuIvbEcFqIwzJYwmc5q4bb2/lQ=';
$timestamp = time();
$key = $public . '|' . $timestamp . '|' . '/api/login/authenticate';
$signature = base64_encode( hash_hmac('sha1', $key, base64_decode( $private ) ) );
$auth = 'h=' . $public . '|' . $timestamp . '; s=' . $signature;
//exit;
$params = array('key' => $public);
$defaults = array(
    CURLOPT_URL => 'https://www.chabad.org/api/login/authenticate', 
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $params,
    CURLOPT_HTTPHEADER => array(
        'Accept:'       =>  'text/json', 
        'Content-type:' =>  'application/x-www-form-urlencoded', 
        'Authorization:'=>  $auth
    )
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt_array($ch, $defaults);
$result = curl_exec($ch);
curl_close($ch);
echo $result;
?>
