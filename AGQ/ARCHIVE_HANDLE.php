<?php
require 'db_agq.php';
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Validate database connection
if (!isset($conn) || $conn->connect_error) {
    exit(json_encode(["error" => "Database connection failed: " . ($conn->connect_error ?? 'Unknown error')]));
}

// Ensure session variables exist
$role = $_SESSION['department'] ?? null;
$dept = $_SESSION['SelectedDepartment'] ?? null;
$company = $_SESSION['Company_name'] ?? null;

$tables = [
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
    "Import Forwarding" => "tbl_impfwd",
];

// Determine the correct table
$targetTable = $tables[$role] ?? $tables[$dept] ?? null;
if (!$targetTable) {
    exit(json_encode(["error" => "Invalid department or role."]));
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["action"])) {
    $action = filter_var($_GET["action"]);
    $refNum = $_POST["RefNum"] ?? null;

    if (!$refNum) {
        exit(json_encode(["success" => false, "message" => "Reference Number is required."]));
        
    }

    switch ($action) {
        case "delete":
            deleteDocument($conn, $refNum);
            break;
        case "restore":
            restoreDocument($conn, $refNum, $role, $dept);
            break;
        case "archive":
            archiveDocument($conn, $targetTable, $refNum, $company, $role);
            break;
        default:
            exit(json_encode(["success" => false, "message" => "Invalid action."]));
    }
}

/**
 * Archive document
 */
function archiveDocument($conn, $table, $refNum, $company, $role)
{
    if (!validTable($table)) {
        exit(json_encode(["error" => "Invalid table."]));
    }

    $query = "UPDATE $table SET isArchived = 1 WHERE RefNum = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $refNum);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $insertQuery = "INSERT INTO tbl_archive (Company_name, department, RefNum) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sss", $company, $role, $refNum);

        if ($insertStmt->execute()) {
            $response = ["success" => true, "message" => "Document archived successfully!"];
        } else {
            $response = ["success" => false, "message" => "Failed to insert into archive: " . $insertStmt->error];
        }

        $insertStmt->close();
    } else {
        $response = ["success" => false, "message" => "Document not found or already archived."];
    }

    $stmt->close();
    echo json_encode($response);
}

/**
 * Delete document from all tables
 */
function deleteDocument($conn, $refNum)
{
    $tables = ["tbl_archive", "tbl_impfwd", "tbl_impbrk", "tbl_expfwd", "tbl_expbrk"];
    $deletedFrom = [];

    foreach ($tables as $table) {
        if (!validTable($table)) continue; // Skip invalid tables

        $stmt = $conn->prepare("DELETE FROM $table WHERE RefNum = ?");
        $stmt->bind_param("s", $refNum);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $deletedFrom[] = $table;
        }
        $stmt->close();
    }

    echo json_encode(!empty($deletedFrom) ?
        ["success" => true, "message" => "Deleted"] :
        ["success" => false, "message" => "No matching record found."]);
}

/**
 * Restore document from archive
 */
function restoreDocument($conn, $refNum, $role, $dept)
{
    $stmtDelete = $conn->prepare("DELETE FROM tbl_archive WHERE RefNum = ?");
    $stmtDelete->bind_param("s", $refNum);

    if (!$stmtDelete->execute()) {
        exit(json_encode(["success" => false, "message" => "Failed to remove from archive: " . $stmtDelete->error]));
    }
    $stmtDelete->close();

    // Determine the target table
    $tables = [
        "Export Brokerage"  => "tbl_expbrk",
        "Export Forwarding" => "tbl_expfwd",
        "Import Brokerage"  => "tbl_impbrk",
        "Import Forwarding" => "tbl_impfwd",
    ];
    $targetTable = $tables[$role] ?? $tables[$dept] ?? null;

    if (!$targetTable || !validTable($targetTable)) {
        exit(json_encode(["success" => false, "message" => "Invalid department or role."]));
    }

    // Restore document
    $stmtUpdate = $conn->prepare("UPDATE $targetTable SET isArchived = 0 WHERE RefNum = ?");
    $stmtUpdate->bind_param("s", $refNum);

    if ($stmtUpdate->execute() && $stmtUpdate->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Document successfully restored."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to restore document. Maybe it doesn't exist?"]);
    }

    $stmtUpdate->close();
}

/**
 * Validate table names against predefined lists
 */
function validTable($table)
{
    $allowedTables = ["tbl_expbrk", "tbl_expfwd", "tbl_impbrk", "tbl_impfwd", "tbl_archive"];
    return in_array($table, $allowedTables);
}
