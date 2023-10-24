<?php
// Start the session
session_start();

// Headers to allow fetch from ajax
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


// API URL and credentials hardcodet
//$apiUrl = 'https://exolynk.app/api/auth/login';
// $loginData = [
//     'environment' => 'korngold',
//     'password' => 'Qdrf%923',
//     'user' => 'webapp_api',
// ];


// Retrieve the envID from the URL
if (!isset($_GET['envID'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Error: envID not provided.'
    ]);
    exit; // Use exit to halt the script after sending the error response
}
//Validate the envID to prevent malicious input -> not workling!
// if (!preg_match('/^[a-zA-Z0-9_-]+$/', $envID)) {
//     echo json_encode([
//         'success' => false,
//         'error' => 'Invalid envID.'
//     ]);
//     exit;
// }

$envID = $_GET['envID'];

// API URL and credentials from Webserver ENV
$apiUrl = 'https://exolynk.app/api/auth/login';
$loginData = [
    //'environment' => getenv('API_ENV'),
    'environment' => $envID,
    'password' => getenv($envID.'_API_PASSWORD'), //combine envID with Variable name to fetsch correct variable from multiple environments
    'user' => getenv($envID.'_API_USER'),
];
// TODO: set variables as following
// SetEnv *environment*_API_PASSWORD *password*
// SetEnv *environment*_API_USER *username*


// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));

// Execute cURL session and get the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    // Output cURL error
    echo json_encode(['success' => false, 'error' => 'cURL Error: ' . curl_error($ch)]);
    exit;
}

// Close cURL session
curl_close($ch);

// Decode the API response
$apiResponse = json_decode($response, true);

// Check API response
if (isset($apiResponse['login_key'])) {
    // Save the Bearer token to the session
    $_SESSION['bearer_token'] = $apiResponse['login_key'];
    
    // Output success response and bearer token
    echo json_encode(['success' => true, 'bearer' => $apiResponse['login_key']]);
} else {
    // Output failure response
    echo json_encode(['success' => false, 'error' => 'API Error: ' . json_encode($apiResponse)]);
}