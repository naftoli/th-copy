<?php
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') { http_response_code(204); exit; }
if ($method !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => ['code' => 'METHOD_NOT_ALLOWED', 'message' => 'Use POST']]);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$errors = [];
if (empty($input['firstName']))     $errors['firstName'] = 'Required';
if (empty($input['lastName']))      $errors['lastName']  = 'Required';
// Expecting native <input type="date"> → YYYY-MM-DD
if (empty($input['dob']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['dob'])) $errors['dob'] = 'Use YYYY-MM-DD';
if (empty($input['parentEmail']) || !filter_var($input['parentEmail'], FILTER_VALIDATE_EMAIL))
  $errors['parentEmail'] = 'Invalid email';
// Keep phone simple for the mock; backend can tighten later
if (empty($input['parentPhone']))   $errors['parentPhone'] = 'Invalid phone';

if ($errors) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'error' => [
      'code' => 'VALIDATION_ERROR',
      'message' => 'Fix the fields below',
      'fields' => $errors
    ]
  ]);
  exit;
}

http_response_code(201);
echo json_encode([
  'ok' => true,
  'userId' => 'usr_' . rand(100000, 999999),
  'next' => ['checkEmail' => true]
]);
