
<?php
// B"H

// --- Configuration ---
// IMPORTANT: Load these from environment variables or secure config in production!
define('PINECONE_API_KEY', 'pcsk_5GCS8d_Fvrw9iG46A5TyXQ7srboN69qEe1PR4Y34cRi3FB7JT9wMFkKUu1GQujKYdW7AV6'); // <--- YOUR PINECONE API KEY
define('OPENAI_API_KEY', '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'); // <--- YOUR OPENAI API KEY
define('PINECONE_INDEX_NAME', 'drrtk'); // Your index name
define('PINECONE_CONTROL_PLANE_HOST', 'api.pinecone.io'); // Host for getting index details

// --- Error Reporting (Development vs Production) ---
// ini_set('display_errors', 1); // Enable for development ONLY
// error_reporting(E_ALL);      // Enable for development ONLY
// ini_set('display_errors', 0); // Disable for Production
// error_reporting(0);          // Disable for Production
// ini_set('log_errors', 1);    // Log errors in Production
// ini_set('error_log', '/path/to/your/php-error.log'); // Set log path

// --- CORS Headers ---
// Allow requests from any origin (restrict in production)
header("Access-Control-Allow-Origin: *");
// Allow specific methods
header("Access-Control-Allow-Methods: POST, OPTIONS");
// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Authorization might not be needed from client here, but good practice

// --- Handle CORS Preflight Request ---
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204); // No Content
    exit();
}

// --- Only Allow POST Requests ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed. Only POST is accepted.']);
    exit();
}

// --- Get POST Data ---
$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true); // true for associative array

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload: ' . json_last_error_msg()]);
    exit();
}

// --- Basic Validation ---
if (!defined('PINECONE_API_KEY') || !PINECONE_API_KEY) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server configuration error: Pinecone API key not set.']);
    exit();
}
if (!defined('OPENAI_API_KEY') || !OPENAI_API_KEY) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server configuration error: OpenAI API key not set.']);
    exit();
}
if (!defined('PINECONE_INDEX_NAME') || !PINECONE_INDEX_NAME) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server configuration error: Pinecone index name not set.']);
    exit();
}


// --- Generic API Request Helper using cURL ---
function makeApiRequest(string $url, string $method = 'GET', array $headers = [], ?string $postData = null, int $timeout = 30): array
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method)); // GET, POST, etc.

    // Add Headers
    $processedHeaders = [];
    foreach ($headers as $key => $value) {
        $processedHeaders[] = "$key: $value";
    }
    if (!empty($processedHeaders)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $processedHeaders);
    }

    // Add Post Data if provided
    if ($postData !== null && (strtoupper($method) === 'POST' || strtoupper($method) === 'PUT' || strtoupper($method) === 'PATCH')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    // Execute Request
    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Handle cURL errors
    if ($curlError) {
        throw new Exception("cURL Error for $url: " . $curlError);
    }

    // Decode JSON response
    $responseData = json_decode($responseBody, true);
    if (json_last_error() !== JSON_ERROR_NONE && !empty($responseBody)) {
       // Don't throw immediately if body isn't JSON (e.g., 204 No Content)
       // But log or handle non-JSON if expected JSON
       error_log("Failed to parse JSON response from $url. HTTP Code: $httpCode. Body: $responseBody");
    }


    // Check HTTP Status Code
    if ($httpCode < 200 || $httpCode >= 300) {
        $errorMessage = "API request failed for $url with status $httpCode.";
        if (isset($responseData['message'])) {
             $errorMessage .= " Message: " . $responseData['message'];
        } elseif (isset($responseData['error']['message'])) {
             $errorMessage .= " Message: " . $responseData['error']['message'];
        } elseif (!empty($responseBody)) {
             $errorMessage .= " Body: " . $responseBody;
        }

        $error = new Exception($errorMessage);
        $error->statusCode = $httpCode; // Attach status code
        $error->responseBody = $responseData ?: $responseBody; // Attach response body
        throw $error;
    }

    // Return associative array or raw body if not JSON
    return is_array($responseData) ? $responseData : ['raw_body' => $responseBody];
}

// --- Helper: Get Pinecone Index Host URL ---
function getPineconeIndexHost(): ?string
{
    $url = "https://" . PINECONE_CONTROL_PLANE_HOST . "/indexes/" . PINECONE_INDEX_NAME;
    $headers = [
        'Api-Key' => PINECONE_API_KEY,
        'Accept' => 'application/json'
    ];

    try {
        error_log("Fetching Pinecone index description from: " . $url);
        $indexInfo = makeApiRequest($url, 'GET', $headers);

        if (isset($indexInfo['host']) && !empty($indexInfo['host'])) {
             error_log("Found Pinecone index host: " . $indexInfo['host']);
            return $indexInfo['host'];
        } else {
            error_log("Could not find host for index '" . PINECONE_INDEX_NAME . "'. Response: " . json_encode($indexInfo));
            throw new Exception("Could not find host for index '" . PINECONE_INDEX_NAME . "'");
        }
    } catch (Exception $e) {
        error_log("Failed to describe Pinecone index '" . PINECONE_INDEX_NAME . "': " . $e->getMessage());
        // Re-throw to signal critical failure upstream
        throw new Exception("Failed to get Pinecone index host: " . $e->getMessage(), $e->getCode() ?: 500, $e);
    }
}


// --- Helper: Call OpenAI Embedding API ---
function getOpenAIEmbedding(string $inputText): array
{
    $url = 'https://api.openai.com/v1/embeddings';
    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . OPENAI_API_KEY
    ];
    $postData = json_encode([
        'input' => $inputText,
        'model' => "text-embedding-ada-002"
    ]);

    try {
        error_log("Requesting OpenAI embedding for text snippet: " . substr($inputText, 0, 50) . "...");
        $result = makeApiRequest($url, 'POST', $headers, $postData, 45); // Increased timeout for OpenAI

        if (!isset($result['data'][0]['embedding'])) {
            error_log("Invalid response structure from OpenAI embedding API: " . json_encode($result));
            throw new Exception('Invalid response structure from OpenAI embedding API');
        }
        error_log("Successfully received embedding from OpenAI.");
        return $result['data'][0]['embedding']; // Return the embedding vector array
    } catch (Exception $e) {
        $statusCode = isset($e->statusCode) ? $e->statusCode : 500;
        $responseBody = isset($e->responseBody) ? json_encode($e->responseBody) : $e->getMessage();
        error_log("OpenAI API Error: Status $statusCode, Response: $responseBody");
        // Re-throw a more specific error for upstream handling
        throw new Exception("Failed to get embedding from OpenAI: " . $e->getMessage(), $statusCode, $e);
    }
}

// --- Helper: Query Pinecone Index ---
function queryPinecone(string $pineconeHost, array $vector, int $topK, ?array $filter = null): array
{
    $url = "https://" . $pineconeHost . "/query";
    $headers = [
        'Content-Type' => 'application/json',
        'Api-Key' => PINECONE_API_KEY,
        'Accept' => 'application/json'
    ];
    $queryPayload = [
        'vector' => $vector,
        'topK' => $topK,
        'includeMetadata' => true,
        'includeValues' => false // Usually false for search, true for debugging
        // 'namespace' => 'optional-namespace' // Add if needed
    ];
    if ($filter) {
        $queryPayload['filter'] = $filter;
    }

    try {
        error_log("Querying Pinecone at $url with topK=$topK" . ($filter ? " and filter" : ""));
        $queryResponse = makeApiRequest($url, 'POST', $headers, json_encode($queryPayload));
        error_log("Pinecone query successful. Found " . (isset($queryResponse['matches']) ? count($queryResponse['matches']) : 0) . " matches.");
        return $queryResponse; // Return the raw Pinecone response structure
    } catch (Exception $e) {
        $statusCode = isset($e->statusCode) ? $e->statusCode : 500;
        $responseBody = isset($e->responseBody) ? json_encode($e->responseBody) : $e->getMessage();
        error_log("Pinecone query error: Status $statusCode, Response: $responseBody");
        // Re-throw a more specific error
        throw new Exception("Failed to query Pinecone vectors: " . $e->getMessage(), $statusCode, $e);
    }
}


// --- Main Logic ---
header('Content-Type: application/json'); // Set response type for all responses

try {
    // --- Get Pinecone Host (Do this before routing requires it) ---
    // Note: This adds latency on every request. Cache or hardcode in production if feasible.
    $pineconeIndexHost = getPineconeIndexHost();
    if (!$pineconeIndexHost) {
         // Error already logged in getPineconeIndexHost, exception should have been thrown
         // This check is mostly redundant if getPineconeIndexHost throws on failure
         throw new Exception("Failed to initialize Pinecone connection.", 503); // Service Unavailable
    }

    // --- Routing based on 'action' ---
    if (!isset($data['action'])) {
        throw new Exception("Missing required field: action", 400);
    }

    switch ($data['action']) {
        case 'search_text':
            error_log("Handling action: search_text");

            // Validate input
            if (!isset($data['prompt']) || !is_string($data['prompt']) || empty(trim($data['prompt']))) {
                throw new Exception("Missing or invalid required field: prompt (non-empty string)", 400);
            }
            $prompt = trim($data['prompt']);
            // Use default topK or get from request (with validation)
            $topK = isset($data['topK']) && is_numeric($data['topK']) ? max(1, (int)$data['topK']) : 5; // Default 5, ensure positive int


            // 1. Get Embedding from OpenAI
            error_log("Step 1: Getting embedding for prompt...");
            $embeddingVector = getOpenAIEmbedding($prompt);

            // 2. Query Pinecone using the embedding
            error_log("Step 2: Querying Pinecone with embedding (topK: $topK)...");
            $searchResults = queryPinecone($pineconeIndexHost, $embeddingVector, $topK);

            // 3. Send Results
            error_log("Step 3: Sending search results to client.");
            echo json_encode([
                'success' => true,
                'results' => $searchResults['matches'] ?? [] // Return only the matches array
            ]);
            exit(); // Success

        // Add other actions here if needed later (e.g., 'insert_text')
        // case 'insert_text':
        //     // ... get text, get embedding, call pinecone upsert ...
        //     break;

        default:
            throw new Exception("Unknown action: " . htmlspecialchars($data['action']), 400);
    }

} catch (Exception $e) {
    // --- Centralized Error Handling ---
    $statusCode = $e->getCode() ?: 500; // Default to 500 if code is 0 or not set
    // Ensure status code is in valid HTTP range
    if ($statusCode < 400 || $statusCode > 599) {
        $statusCode = 500;
    }
    http_response_code($statusCode);

    // Log the detailed error
    error_log("Error processing request: " . $e->getMessage() . " | Code: " . $statusCode . " | Trace: " . $e->getTraceAsString());

    // Send generic error message to client
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred processing your request.',
        'details' => $e->getMessage() // Optionally include specific message in dev, remove in prod
    ]);
    exit();
}

?>