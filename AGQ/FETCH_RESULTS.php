<?php
require 'db_agq.php';


header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON CONNECTION
if (!isset($_GET['query']) || empty(trim($_GET['query']))) {
    echo json_encode(["error" => "No search query provided."]);
    exit();
}
$search = "%" . trim($_GET['query']) . "%";

if (!$conn) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

// SQL PREPARED STATEMENT
$sql = "SELECT TransactionID, Shipper, Date, Department, Vessel, BLNum, 
               DestinationOrigin, NatureOfGoods, Volume, ER, EstTimeArrival, 
               PackageCount, PackageWeight, PackageMeasurement, RefNum, IsArchived 
        FROM tbl_transaction
        WHERE TransactionID LIKE ? 
           OR Shipper LIKE ? 
           OR Department LIKE ? 
           OR Vessel LIKE ? 
           OR DestinationOrigin LIKE ? 
           OR NatureOfGoods LIKE ? 
           OR RefNum LIKE ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL Prepare failed: " . $conn->error]);
    exit();
}


$stmt->bind_param("sssssss", $search, $search, $search, $search, $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();


$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

if (empty($transactions)) {
    echo json_encode(["message" => "No matching results found."]);
} else {
    echo json_encode($transactions);
}
exit();
