<?php
include 'db_agq.php'; // Database connection
session_start();

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Extract and sanitize input
$refNum = isset($data['refNum']) ? trim($data['refNum']) : null;
$company = isset($data['company']) ? trim($data['company']) : null;
$docType = isset($data['docType']) ? strtoupper(trim($data['docType'])) : null;
$isApproved = isset($data['isApproved']) ? (int) $data['isApproved'] : null;
$dept = isset($data['dept']) ? trim($data['dept']) : (isset($_SESSION['SelectedDepartment']) ? trim($_SESSION['SelectedDepartment']) : '');

// ✅ Check for missing parameters and display which ones are missing
$missingParams = [];
if (!$refNum) $missingParams[] = "refNum";
if (!$company) $missingParams[] = "company";
if (!$docType) $missingParams[] = "docType";
if (!isset($isApproved)) $missingParams[] = "isApproved"; // Since `isApproved` can be 0, we check with isset()
if (!$dept) $missingParams[] = "dept";

if (!empty($missingParams)) {
    echo json_encode(["success" => false, "message" => "Missing parameters: " . implode(", ", $missingParams)]);
    exit;
}

// ✅ Define department-to-table mapping
$tables = [
    "Import Forwarding" => "tbl_impfwd",
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
];

// ✅ Validate department
if (!isset($tables[$dept])) {
    echo json_encode(["success" => false, "message" => "Invalid department: $dept"]);
    exit;
}
$validTable = $tables[$dept]; // Automatically picks the right table

// ✅ Check if the document exists in the identified table
$checkQuery = "SELECT 1 FROM `$validTable` WHERE RefNum = ? AND Company_name = ? LIMIT 1";
$checkStmt = $conn->prepare($checkQuery);

if (!$checkStmt) {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
    exit;
}

$checkStmt->bind_param("ss", $refNum, $company);
$checkStmt->execute();
$result = $checkStmt->get_result();
$documentExists = $result->num_rows > 0;
$checkStmt->close();

// ✅ If no document is found, return an error
if (!$documentExists) {
    echo json_encode(["success" => false, "message" => "Document not found in department '$dept'"]);
    exit;
}

// ✅ Update the correct table
$updateQuery = "UPDATE `$validTable` SET isApproved = ? WHERE RefNum = ? AND Company_name = ?";
$stmt = $conn->prepare($updateQuery);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("iss", $isApproved, $refNum, $company);
$success = $stmt->execute();
$stmt->close();
$conn->close();

// ✅ Return success or failure response
if ($success) {
    echo json_encode(["success" => true, "message" => "Approval status updated successfully in $validTable"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update approval status"]);
}
