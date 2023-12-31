<?php

//CORS- enable
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../services/DatabaseUtils.php';
include_once __DIR__ . '/../api/ApiHandler.php';  

$conn = DatabaseUtils::connectToDatabase();

//check request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $currency = isset($_GET['currency']) ? $_GET['currency'] : 'USD';
    $tableName = sprintf('exchange_rates_%s', $currency);
    $limit = isset($_GET['limit']) ? $_GET['limit'] : null;

    $results = DatabaseUtils::getDataFromDB($conn, $tableName, $limit);

    header('Content-Type: application/json');
    echo json_encode($results, JSON_PRETTY_PRINT);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data && isset($data['currency'])) {
        $currency = $data['currency'];
        $dbUtils = new DatabaseUtils();
        $apiHandler = new ApiHandler($dbUtils);
        // get $apiData from the API
        $apiData = $apiHandler->fetchDataAndUpdateDatabase($currency);
        $apiHandler->updateDatabase($currency, $apiData);
        echo json_encode(['status' => 'success', 'message' => 'Database updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
    }
}

$conn->close();
?>
