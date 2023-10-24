<?php
// Start the session
session_start();

// Headers to allow fetch from ajax
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// Check if the Bearer token is set in the session
if(!isset($_SESSION['bearer_token'])) {
    echo json_encode(['success' => false, 'error' => 'Bearer token not found in session']);
    exit;
}

// Get recordId from POST data
$postData = json_decode(file_get_contents('php://input'), true);
$recordId = $postData['recordId'] ?? null;

// Check if recordId is provided
if(!$recordId) {
    echo json_encode(['success' => false, 'error' => 'recordId not provided']);
    exit;
}

// Form API URL
$apiUrl = "https://exolynk.app/api/record/${recordId}";

// Bearer token obtained from session
$token = $_SESSION['bearer_token'];

// Set up cURL options
$options = [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_HTTPHEADER => [
        'accept: application/json',
        'authorization: Bearer ' . $token
    ]
];

// Initialize and execute cURL session
$ch = curl_init();
curl_setopt_array($ch, $options);
$apiResponse = curl_exec($ch);
curl_close($ch);

// Check if cURL request was successful
if($apiResponse === false) {
    echo json_encode(['success' => false, 'error' => 'API request failed']);
    exit;
}

// Decode API response / output data
$apiData = json_decode($apiResponse, true);

if(
    isset(
        $apiData['ident'], 
        $apiData['values']['last_maintenance_datetime']['Datetime'],
        $apiData['values']['maintenance_interval']['Integer'],
        $apiData['status']
    ) && 
    is_string($apiData['status']) && $apiData['status'] !== '' && 
    is_string($apiData['ident']) && $apiData['ident'] !== '' && 
    is_string($apiData['values']['last_maintenance_datetime']['Datetime']) && $apiData['values']['last_maintenance_datetime']['Datetime'] !== '' &&
    is_int($apiData['values']['maintenance_interval']['Integer'])
) {
    echo json_encode([
        'success' => true, 
        'data' => [
            'ident' => $apiData['ident'], 
            'status' => $apiData['status'], 
            'Datetime' => $apiData['values']['last_maintenance_datetime']['Datetime'],
            'maintenance_interval' => $apiData['values']['maintenance_interval']['Integer']
        ]
    ]);

    session_destroy();
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid ident, Datetime, Status or maintenance_interval value', 
        'raw_response' => $apiData
    ]);

    session_destroy();
}
