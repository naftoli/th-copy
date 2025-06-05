<?php
/**
 * Honeypot script to block malicious IPs via Cloudflare Firewall API.
 */

// Cloudflare API credentials
// $zoneId1   = "b51178aafdca38647c372516075a03eb";
// $zoneId2   = "d1ed068e5162777f0b72c51014810f2f";
$accountID = "6023f58b336bda28b3254cac4c0836f7";
$apiToken  = "JRi5UvrFmDR9Um8W3noaR8Nba23GaS-dQhO3-J5m"; //Honeypoy API token

// Get the real visitor IP, considering Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $visitorIp = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // In case of multiple IPs in X-Forwarded-For, take the first one
    $visitorIp = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
} else {
    // Fallback to remote address if no Cloudflare headers are present
    $visitorIp = $_SERVER['REMOTE_ADDR'];
}

// first get rules
$apiUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountID}/firewall/access_rules/rules";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer {$apiToken}"
]);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$res = json_decode($response);
echo "<pre>"; print_r($res); echo "</pre>";
$rules = [];
foreach ($res->result as $rule) {
    if ($rule->configuration->value == $visitorIp) {
        $rules[] = $rule->id;
    }
}
echo "<pre>"; print_r($rules); echo "</pre>"; exit;
// Cloudflare Firewall API endpoint
$apiUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountID}/firewall/access_rules/rules/";

foreach ($rules as $ruleID) {
    // Send request to Cloudflare API
    $ch = curl_init($apiUrl . $ruleID);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer {$apiToken}"
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($response);
    echo "<pre>"; print_r($res); echo "</pre>";
}

// Log the blocked IP with the requested page
$requestedPage = $_SERVER['REQUEST_URI'];
error_log("Removed honeypot rule for IP: {$visitorIp} - Requested Page: {$requestedPage}");

// Respond with a 404 error to mimic the original behavior
header("HTTP/1.1 404 Not Found");
if ($res->success) {
    exit("You have now been allowed back to this site.");
}