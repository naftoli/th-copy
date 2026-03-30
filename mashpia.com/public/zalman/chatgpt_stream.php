<?php
// Disable output buffering
if (ob_get_level()) ob_end_clean();

// Set headers for streaming
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Replace with your actual API key
$api_key = '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';

// Get the prompt and conversation history from POST data
$prompt = isset($_POST['prompt']) ? $_POST['prompt'] : '';
$history = isset($_POST['history']) ? json_decode($_POST['history'], true) : [];

if (empty($prompt)) {
    echo "data: {\"error\": \"No prompt provided\"}\n\n";
    ob_flush();
    flush();
    exit;
}

// Prepare the API request
$url = 'https://api.openai.com/v1/chat/completions';
$headers = [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json',
];

// Build messages array with history and new prompt
$messages = $history;
$messages[] = ['role' => 'user', 'content' => $prompt];

$data = [
    'model' => 'gpt-4o-mini',
    'messages' => $messages,
    'stream' => true,
    'temperature' => 0.7
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        if (strpos($line, 'data: ') === 0) {
            $json = substr($line, 6);
            if ($json === '[DONE]') {
                echo "data: [DONE]\n\n";
                ob_flush();
                flush();
                return strlen($data);
            }
            $decoded = json_decode($json, true);
            if (isset($decoded['choices'][0]['delta']['content'])) {
                $content = $decoded['choices'][0]['delta']['content'];
                echo "data: " . json_encode(['content' => $content]) . "\n\n";
                ob_flush();
                flush();
            }
        }
    }
    return strlen($data);
});

// Execute the request
curl_exec($ch);
curl_close($ch); 