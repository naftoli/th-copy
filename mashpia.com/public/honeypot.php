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

// Cloudflare Firewall API endpoint
$apiUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountID}/firewall/access_rules/rules";

$data = [
    "mode"          => "block",
    "configuration" => [
        "target" => "ip",
        "value"  => $visitorIp
    ],
    "notes"         => "Blocked by honeypot script",
];

// Send request to Cloudflare API
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer {$apiToken}"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

// Log the blocked IP with the requested page
$requestedPage = $_SERVER['REQUEST_URI'];
error_log("Blocked IP: {$visitorIp} - Requested Page: {$requestedPage}");

// Respond with a 404 error to mimic the original behavior
header("HTTP/1.1 404 Not Found");
exit("Bye bye, hacker. You have now been banned from this site. If this was a mistake, please reach out to dev@tzivoshashem.org");