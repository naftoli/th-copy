<?php
header('Content-type: application/json');
# Communication between chabadorg.clhosting.org and partner
$PublicKey = '474DBD09-F59F-433D-A755-5A97594FC4E1';
$PrivateKey = base64_decode('R9WIfWVU7VXiiEporuuIvbEcFqIwzJYwmc5q4bb2/lQ=');
$timestamp = time();

// get all mosdos
$mosdos = array();
require "../db.php";
$sql = "select mosad_id from chabad_mosdos group by mosad_id limit 10";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $mosdos[] = $row['mosad_id'];
}

$info = array();
foreach ($mosdos as $mosadID) {
    $BaseUrl = 'https://chabadorg.clhosting.org';
    $RequestUrl = "/api/centers/$mosadID?includeDepartments=true";
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
    $info['mosdos'][] = $result;
}
echo json_encode( $info );