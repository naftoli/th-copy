<?php 
$ch = curl_init('https://www.howsmyssl.com/a/check');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data);
echo "Connection uses " . $json->tls_version ."\n";

echo "on PHP " . phpversion();

?>