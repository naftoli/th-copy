<?php
// B"H

// ############################################################
// # SECURITY WARNING: EXPOSING DETAILED ERRORS TO CLIENT     #
// # This script is modified to send detailed internal error  #
// # messages to the client for debugging ONLY. Do NOT use    #
// # this in production without reverting to generic error   #
// # messages for 5xx status codes to avoid leaking internal #
// # system details.                                          #
// ############################################################

// --- Configuration ---
$openai_api_key = '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'; // Use your actual key
$pinecone_api_key = 'pcsk_5GCS8d_Fvrw9iG46A5TyXQ7srboN69qEe1PR4Y34cRi3FB7JT9wMFkKUu1GQujKYdW7AV6';         // <-- IMPORTANT: SET YOUR PINECONE KEY
$pinecone_index_name = 'drrtk';
$pinecone_control_plane_host = "api.pinecone.io";

// --- Pre-check dependencies ---
if (!extension_loaded('curl')) {
    http_response_code(500); header('Content-Type: application/json');
    echo json_encode(['error' => 'Server Configuration Error: PHP extension "curl" is not loaded. This script requires it for API calls.']);
    exit(1);
}
if (!extension_loaded('openssl')) {
    // cURL often relies on openssl for HTTPS
    http_response_code(500); header('Content-Type: application/json');
    echo json_encode(['error' => 'Server Configuration Error: PHP extension "openssl" is not loaded. This script requires it for HTTPS connections.']);
    exit(1);
}


// --- Basic Validation ---
if (empty($pinecone_api_key)) { /* ... error exit ... */ http_response_code(500); header('Content-Type: application/json'); echo json_encode(['error' => 'Server Configuration Error: Pinecone API Key is not set or empty.']); exit(1); }
if (empty($openai_api_key)) { /* ... error exit ... */ http_response_code(500); header('Content-Type: application/json'); echo json_encode(['error' => 'Server Configuration Error: OpenAI API Key is not set or empty.']); exit(1); }
if (empty($pinecone_index_name)) { /* ... error exit ... */ http_response_code(500); header('Content-Type: application/json'); echo json_encode(['error' => 'Server Configuration Error: Pinecone Index Name is not set or empty.']); exit(1); }


// --- Global Variable for Pinecone Host ---
$pineconeIndexHost = null;

// --- Helper Function to Send JSON Response ---
// (Remains the same as the previous version)
function sendJsonResponse(int $statusCode, array $data): void {
     if ($statusCode >= 400 && !isset($data['error'])) { $data['error'] = 'An unspecified error occurred.'; }
     if (!headers_sent()) { http_response_code($statusCode); header('Content-Type: application/json'); header('Access-Control-Allow-Origin: *'); header('Access-Control-Allow-Methods: POST, OPTIONS'); header('Access-Control-Allow-Headers: Content-Type, Api-Key, Authorization'); }
     if (isset($data['error'])) { error_log("Sending Error Response (HTTP $statusCode): " . json_encode($data)); }
     echo json_encode($data); exit;
}

// --- Handle CORS Preflight Request ---
// (Remains the same)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { /* ... exit ... */ http_response_code(204); header('Access-Control-Allow-Origin: *'); header('Access-Control-Allow-Methods: POST, OPTIONS'); header('Access-Control-Allow-Headers: Content-Type, Api-Key, Authorization'); exit;}

// --- Ensure Request is POST ---
// (Remains the same)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { sendJsonResponse(405, ['error' => 'Method Not Allowed. Only POST requests are accepted.']); }


// --- NEW: HTTP Request Helper using cURL ---
/**
 * Makes an HTTP/S request using PHP's cURL extension.
 * Returns detailed error messages in the 'error' key on failure.
 */
function makeApiRequestWithCurl(string $method, string $url, ?array $payload = null, array $extraHeaders = []): array {
    $debugInfo = "Attempting cURL Request: $method $url";
    $ch = curl_init();

    // --- Base cURL Options ---
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as string
    curl_setopt($ch, CURLOPT_HEADER, false);       // Don't include headers in the output string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 35);         // Total timeout for the request
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  // Timeout for establishing connection
    // SSL Verification - Should be enabled in production
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DEBUG ONLY - If having SSL cert issues
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);   // DEBUG ONLY

    // --- Set HTTP Method ---
    // GET is default, explicitly set others
    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            break;
        case 'GET':
            // Default, no specific option needed for GET payload/headers
            break;
        // Add PUT, DELETE etc. if needed in the future
        default:
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            break;
    }

    // --- Prepare Headers ---
    $headerArray = [];
    $headerArray[] = 'Accept: application/json, */*'; // Standard Accept header
    // Add extra headers (like Api-Key, Authorization)
    $debugInfo .= "\n > Headers (some values redacted):";
    foreach ($extraHeaders as $key => $value) {
         $logValue = (stripos($key, 'key') !== false || stripos($key, 'auth') !== false) ? substr($value, 0, 4) . '***REDACTED***' : $value;
         $debugInfo .= "\n   - $key: $logValue";
        $headerArray[] = "$key: $value";
    }

    // --- Prepare Payload (if any) ---
    if ($payload !== null && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) { // Only add body for relevant methods
        $jsonData = json_encode($payload);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = "Internal Error: Failed to JSON encode payload for $url. Error: " . json_last_error_msg();
             curl_close($ch); // Clean up cURL handle
             error_log($errorMsg);
            return ['statusCode' => 500, 'body' => null, 'error' => $errorMsg];
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        // Add Content-Type header *only* if we have a JSON payload
        $headerArray[] = 'Content-Type: application/json';
        $debugInfo .= "\n > Payload size: " . strlen($jsonData) . " bytes. Content-Type: application/json";
    }

    // --- Set Final Headers ---
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

    // --- Execute Request ---
    $responseBody = curl_exec($ch);

    // --- Check for cURL-Level Errors (Network, SSL, etc.) ---
    if (curl_errno($ch)) {
        $curlErrorMsg = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        $errorMsg = "cURL Request Error for $method $url. Errno: $curlErrno. Error: $curlErrorMsg (Check network, SSL certificates, endpoint availability)";
        error_log($errorMsg . "\n" . $debugInfo); // Log details locally too
        // Map common curl errors to HTTP status codes if possible
        $statusCode = match($curlErrno) {
             CURLE_COULDNT_RESOLVE_HOST, CURLE_COULDNT_CONNECT => 503, // Service Unavailable
             CURLE_OPERATION_TIMEDOUT => 504, // Gateway Timeout
             CURLE_SSL_CONNECT_ERROR, CURLE_SSL_CERTPROBLEM, CURLE_SSL_CIPHER, CURLE_PEER_FAILED_VERIFICATION => 502, // Bad Gateway (SSL issue with upstream)
             default => 500 // Internal Server Error otherwise
         };
        return ['statusCode' => $statusCode, 'body' => null, 'error' => $errorMsg]; // Send detailed cURL error
    }

    // --- Get HTTP Status Code ---
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch); // Close connection
    $debugInfo .= "\n < Response Status: $statusCode";

    // --- Decode Response Body ---
    $decodedBody = null;
    $jsonErrorMsg = null;
    if (!empty($responseBody)) {
        $decodedBody = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             $jsonErrorMsg = "API Response JSON Decode Error for $method $url (Status: $statusCode). Error: ". json_last_error_msg() .". Raw Body Snippet: '" . substr(preg_replace('/\s+/', ' ', $responseBody), 0, 500) . "...'";
             error_log($jsonErrorMsg); // Log locally
             // If status was success, this is an error. If status was error, this is additional detail.
        } else {
             $debugInfo .= "\n < Response Body Decoded Successfully.";
        }
    } else {
        $debugInfo .= "\n < Response Body: Empty.";
    }

    // --- Construct Final Error Message if needed ---
    $finalErrorMsg = null;
    if ($statusCode >= 400) {
        $apiErrorDetail = "No specific error detail found in response body."; // Default
         if (is_array($decodedBody)) { // Try to get message from JSON error structure
             $apiErrorDetail = $decodedBody['message'] ?? ($decodedBody['error'] ?? ($decodedBody['detail'] ?? json_encode($decodedBody)));
         } elseif (!empty($responseBody) && empty($jsonErrorMsg)) { // Use raw body only if no JSON error
             $apiErrorDetail = "Raw Response Snippet: '" . substr(preg_replace('/\s+/', ' ', $responseBody), 0, 200) . "...'";
         }
         // If we had a JSON decode error *in addition* to a 4xx/5xx status, append it.
         if (!empty($jsonErrorMsg)) {
             $apiErrorDetail .= " | Additionally: " . $jsonErrorMsg;
         }
        $finalErrorMsg = "API request failed with status {$statusCode} for $method $url. Detail: " . $apiErrorDetail;
        error_log("API Request Error Detail: " . $finalErrorMsg); // Log detailed message
    } elseif (!empty($jsonErrorMsg)) {
         // Handle case where status was 2xx but JSON decoding failed (treat as 502)
         $finalErrorMsg = $jsonErrorMsg;
         $statusCode = 502;
    }

    // Log full debug info only on error
    if ($finalErrorMsg) {
        error_log("Full Debug Info for Failed cURL Request:\n" . $debugInfo);
    }

    return ['statusCode' => $statusCode, 'body' => $decodedBody, 'error' => $finalErrorMsg];
}


// --- Helper: Call OpenAI Embedding API ---
function getOpenAIEmbedding(string $inputText): array {
    global $openai_api_key;
    if (empty($openai_api_key)) { /* ... handle missing key ... */ throw new Exception("Server Configuration Error: OpenAI key missing internally.", 500); }

    $url = 'https://api.openai.com/v1/embeddings';
    $payload = ['input' => $inputText, 'model' => 'text-embedding-ada-002'];
    $headers = ['Authorization' => 'Bearer ' . $openai_api_key];

    error_log("Calling OpenAI Embedding API via cURL...");
    // USE THE NEW cURL FUNCTION
    $result = makeApiRequestWithCurl('POST', $url, $payload, $headers);

    if ($result['error']) {
        error_log("OpenAI API Call Failed (cURL): " . $result['error']);
        throw new Exception("OpenAI API Error: " . $result['error'], $result['statusCode'] ?: 500);
    }
    if (!isset($result['body']['data'][0]['embedding'])) {
        $errorMsg = "OpenAI API Response Error (cURL): Invalid structure. Expected 'data[0].embedding'. Body: " . json_encode($result['body']);
        error_log($errorMsg); throw new Exception($errorMsg, 502);
    }
    error_log("OpenAI Embedding received successfully via cURL.");
    return $result['body']['data'][0]['embedding'];
}

// --- Helper: Get Pinecone Index Host URL ---
function getPineconeIndexHost(): string {
    global $pinecone_index_name, $pinecone_control_plane_host, $pinecone_api_key, $pineconeIndexHost;
    if (empty($pinecone_api_key) || empty($pinecone_index_name) || empty($pinecone_control_plane_host)) { /* ... handle missing config ... */ throw new Exception("Server Configuration Error: Pinecone config missing internally.", 500); }

    static $hostCache = null;
    if ($pineconeIndexHost !== null) return $pineconeIndexHost;
    if ($hostCache !== null) { $pineconeIndexHost = $hostCache; return $hostCache; }

    error_log("Fetching Pinecone index description via cURL...");
    $url = "https://" . $pinecone_control_plane_host . "/indexes/" . rawurlencode($pinecone_index_name);
    $headers = ['Api-Key' => $pinecone_api_key];
    // USE THE NEW cURL FUNCTION
    $result = makeApiRequestWithCurl('GET', $url, null, $headers); // Method is GET, no payload

    if ($result['error']) {
        error_log("Failed to describe Pinecone index (cURL): " . $result['error']);
        throw new Exception("Failed to get Pinecone index details: " . $result['error'], $result['statusCode'] ?: 500);
    }
    if (!isset($result['body']['host'])) {
         $errorMsg = "Pinecone API Response Error (cURL): 'host' field missing. Body: " . json_encode($result['body']);
         error_log($errorMsg); throw new Exception($errorMsg, 502);
    }
    $hostCache = $result['body']['host']; $pineconeIndexHost = $hostCache;
    error_log("Found and cached Pinecone index host via cURL: " . $hostCache);
    return $hostCache;
}


// --- Main Request Handling Logic ---
try {
    global $pinecone_api_key;
    if (!isset($_POST['action'])) { sendJsonResponse(400, ['error' => 'Missing required POST field: action']); }
    $action = $_POST['action'];
    error_log("Received action: " . $action); // Keep server logs minimal

    switch ($action) {
        case 'insert':
            // ... validation ...
            $textToEmbed = $_POST['text'];
            $embedding = getOpenAIEmbedding($textToEmbed); // Uses cURL internally now
            $indexHost = getPineconeIndexHost();         // Uses cURL internally now
            $upsertPayload = [ /* ... */ ];
            $url = "https://" . $indexHost . "/vectors/upsert";
            $headers = ['Api-Key' => $pinecone_api_key];
             // USE THE NEW cURL FUNCTION
            $result = makeApiRequestWithCurl('POST', $url, $upsertPayload, $headers);
            if ($result['error']) { sendJsonResponse($result['statusCode'], ['error' => "Pinecone upsert failed: " . $result['error']]); }
            sendJsonResponse(200, ['success' => true, 'message' => 'Vector inserted', 'details' => $result['body']]);
            break;

        case 'get':
            // ... validation ...
            $vectorId = trim($_POST['id']);
            $indexHost = getPineconeIndexHost();         // Uses cURL internally now
            $url = "https://" . $indexHost . "/vectors/fetch?ids=" . urlencode($vectorId);
            $headers = ['Api-Key' => $pinecone_api_key];
             // USE THE NEW cURL FUNCTION
            $result = makeApiRequestWithCurl('GET', $url, null, $headers); // Method is GET
            if ($result['error'] && $result['statusCode'] !== 404) { sendJsonResponse($result['statusCode'], ['error' => "Pinecone fetch failed: " . $result['error']]); }
            if ($result['statusCode'] === 404 || !isset($result['body']['vectors'][$vectorId])) { sendJsonResponse(404, ['success' => false, 'error' => "Vector with ID '{$vectorId}' not found."]); }
            sendJsonResponse(200, ['success' => true, 'vector' => $result['body']['vectors'][$vectorId]]);
            break;

        case 'search':
            // ... validation (vector, topK, filter JSON decoding) ...
             $vector = json_decode($_POST['vector'], true);
             if (json_last_error() !== JSON_ERROR_NONE || !is_array($vector)) { sendJsonResponse(400, ['error' => 'Invalid format for POST field: vector (must be a valid JSON array string). JSON Error: ' . json_last_error_msg()]); }
             // ... more validation ...
             $topK = intval($_POST['topK']);
             $filter = null;
             if (isset($_POST['filter']) && is_string($_POST['filter']) && trim($_POST['filter']) !== '') { /* ... decode filter ... */ }
             $indexHost = getPineconeIndexHost(); // Uses cURL internally now
             $queryPayload = [ /* ... */ ];
             $url = "https://" . $indexHost . "/query";
             $headers = ['Api-Key' => $pinecone_api_key];
             // USE THE NEW cURL FUNCTION
             $result = makeApiRequestWithCurl('POST', $url, $queryPayload, $headers);
            if ($result['error']) { sendJsonResponse($result['statusCode'], ['error' => "Pinecone query failed: " . $result['error']]); }
            sendJsonResponse(200, ['success' => true, 'results' => $result['body']]);
            break;

        case 'embed':
            // ... validation ...
            $textToEmbed = $_POST['text'];
            $embedding = getOpenAIEmbedding($textToEmbed); // Uses cURL internally now
            sendJsonResponse(200, ['success' => true, 'embedding' => $embedding]);
            break;

        default:
            sendJsonResponse(400, ['error' => "Invalid action specified: '$action'"]);
            break;
    }

} catch (Throwable $e) { // Catch any exception or error
    // (Error handling remains the same - sends detailed error for debugging)
    $statusCode = method_exists($e, 'getCode') && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    $detailedErrorMessage = sprintf( "[%s] %s in %s:%d", get_class($e), $e->getMessage(), basename($e->getFile()), $e->getLine() );
    error_log($detailedErrorMessage . "\nTrace: " . $e->getTraceAsString()); // Log trace locally
    sendJsonResponse($statusCode, ['error' => "Unhandled Error: " . $detailedErrorMessage]);
}

?>