<?php
include 'db_agq.php'; // Database connection
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Extract and sanitize input
$refNum = isset($data['refNum']) ? trim($data['refNum']) : null;
$company = isset($data['company']) ? trim($data['company']) : null;
$docType = isset($data['docType']) ? strtoupper(trim($data['docType'])) : null;
$isApproved = isset($data['isApproved']) ? (int) $data['isApproved'] : null;
$dept = isset($data['dept']) ? trim($data['dept']) : (isset($_SESSION['SelectedDepartment']) ? trim($_SESSION['SelectedDepartment']) : '');
error_log("Department received: " . $dept);
$signature = isset($data['signature']) ? $data['signature'] : null;

// ✅ Check for missing parameters
$missingParams = [];
if (!$refNum) $missingParams[] = "refNum";
if (!$company) $missingParams[] = "company";
if (!$docType) $missingParams[] = "docType";
if (!isset($isApproved)) $missingParams[] = "isApproved";
if (!$dept) $missingParams[] = "dept";

if (!empty($missingParams)) {
    echo json_encode(["success" => false, "message" => "Missing parameters: " . implode(", ", $missingParams)]);
    exit;
}

$tables = [
    "Import Forwarding" => "tbl_impfwd",
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
];

$approvedBy = null;
if ($signature) {
    // Store the base64 signature directly in Approved_by column
    $approvedBy = $signature;
}

// ✅ Validate the department and choose the appropriate table
if (!isset($tables[$dept])) {
    echo json_encode(["success" => false, "message" => "Invalid department: $dept"]);
    exit;
}

$validTable = $tables[$dept];

// ✅ Check if the document exists in the chosen table
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
    echo json_encode(["success" => false, "message" => "Document not found in '$validTable'"]);
    exit;
}

// ✅ Proceed with updating the 'Approved_by' field with the signature
$updateQuery = "UPDATE `$validTable` SET Approved_by = ? WHERE RefNum = ? AND Company_name = ?";
$stmt = $conn->prepare($updateQuery);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("sss", $approvedBy, $refNum, $company);
$success = $stmt->execute();
$stmt->close();


// ✅ If docType is 'MANIFESTO', update `tbl_document` instead
if ($docType === "MANIFESTO") {
    $checkQuery = "SELECT 1 FROM tbl_document WHERE RefNum = ? LIMIT 1";
    $updateQuery = "UPDATE tbl_document SET isApproved = ? WHERE RefNum = ?";
} else {
    // ✅ Validate department
    if (!isset($tables[$dept])) {
        echo json_encode(["success" => false, "message" => "Invalid department: $dept"]);
        exit;
    }
    $validTable = $tables[$dept];

    $checkQuery = "SELECT 1 FROM `$validTable` WHERE RefNum = ? AND Company_name = ? LIMIT 1";
    $updateQuery = "UPDATE `$validTable` SET isApproved = ? WHERE RefNum = ? AND Company_name = ?";
}

// ✅ Check if the document exists before updating
$checkStmt = $conn->prepare($checkQuery);

if (!$checkStmt) {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
    exit;
}

if ($docType === "MANIFESTO") {
    $checkStmt->bind_param("s", $refNum);
} else {
    $checkStmt->bind_param("ss", $refNum, $company);
}

$checkStmt->execute();
$result = $checkStmt->get_result();
$documentExists = $result->num_rows > 0;
$checkStmt->close();

// ✅ If no document is found, return an error
if (!$documentExists) {
    echo json_encode(["success" => false, "message" => "Document not found in " . ($docType === "MANIFESTO" ? "tbl_document" : "department '$dept'")]);
    exit;
}

// ✅ Proceed with updating the approval status
$stmt = $conn->prepare($updateQuery);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
    exit;
}

if ($docType === "MANIFESTO") {
    $stmt->bind_param("is", $isApproved, $refNum);
} else {
    $stmt->bind_param("iss", $isApproved, $refNum, $company);
}

$success = $stmt->execute();
$stmt->close();
$conn->close();

// ✅ Return success or failure response
if ($success) {
    echo json_encode(["success" => true, "message" => "Approval status updated successfully in " . ($docType === "MANIFESTO" ? "tbl_document" : $validTable)]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update approval status"]);
}
