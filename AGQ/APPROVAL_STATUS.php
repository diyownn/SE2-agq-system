<?php
include 'db_agq.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json"); // Ensures JSON response

if (!isset($_GET['refNum']) || !isset($_GET['docType']) || !isset($_GET['dept'])) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$refNum = $_GET['refNum'];
$docType = strtoupper($_GET['docType']);
$dept = trim($_GET['dept']);

// Define tables based on both dept and docType
$tables = [
    "Import Forwarding" => [
        "MANIFESTO" => "tbl_document",
        "DEFAULT"   => "tbl_impfwd"
    ],
    "Export Brokerage" => [
        "MANIFESTO" => "tbl_document",
        "DEFAULT"   => "tbl_expbrk"
    ],
    "Export Forwarding" => [
        "MANIFESTO" => "tbl_document",
        "DEFAULT"   => "tbl_expfwd"
    ],
    "Import Brokerage" => [
        "MANIFESTO" => "tbl_document",
        "DEFAULT"   => "tbl_impbrk"
    ]
];

// Check if the department exists
if (!isset($tables[$dept])) {
    echo json_encode(["success" => false, "message" => "Invalid department"]);
    exit;
}

// Determine the table (Manifesto documents go to `tbl_document`, others use department table)
$validTable = $tables[$dept][$docType] ?? $tables[$dept]["DEFAULT"];

// Prepare SQL statement
$query = "SELECT isApproved FROM `$validTable` WHERE RefNum = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $refNum);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["success" => true, "isApproved" => $row['isApproved']]);
} else {
    echo json_encode(["success" => false, "isApproved" => 0, "message" => "Document not found"]);
}

$stmt->close();
$conn->close();
