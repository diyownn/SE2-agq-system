<?php
session_start();
require 'db_agq.php';
error_reporting(E_ALL);
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$role = $_SESSION['department'] ?? '';
$dept = $_SESSION['SelectedDepartment'] ?? '';
$search = $_GET['search'] ?? '';
$company = $_SESSION['Company_name'] ?? '';

$response = [];
$tables = [
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
    "Import Forwarding" => "tbl_impfwd",
];

// Function to fetch documents
function fetchDocuments($conn, $table, $search, $company, &$response, $departmentKey)
{
    $query = "SELECT RefNum, DocType, isArchived FROM $table 
              WHERE (RefNum LIKE ? OR DocType LIKE ? OR DocType = 'Manifesto') 
              AND Company_name = ?";

    $like_query = "%{$search}%";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sss", $like_query, $like_query, $company);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response[$departmentKey][] = [
                "RefNum" => $row['RefNum'],
                "DocType" => $row['DocType'],
                "ArchivedStatus" => $row['isArchived'] == 1 ? "Archived" : "NotArchived"
            ];
        }
        $stmt->close();
    }
}

// Fetch documents based on department selection
if (!empty($dept) && isset($tables[$dept])) {
    $table = $tables[$dept];
    $deptKey = strtolower(str_replace(" ", "", $dept));

    fetchDocuments($conn, $table, $search, $company, $response, $deptKey);

    // If Import Forwarding, also fetch from tbl_document
    if ($dept === "Import Forwarding") {
        fetchDocuments($conn, "tbl_document", $search, $company, $response, $deptKey);
    }
}

// Fetch documents based on role selection
if (!empty($role) && isset($tables[$role])) {
    $table = $tables[$role];
    $roleKey = strtolower(str_replace(" ", "", $role));

    fetchDocuments($conn, $table, $search, $company, $response, $roleKey);

    // If Import Forwarding, also fetch from tbl_document
    if ($role === "Import Forwarding") {
        fetchDocuments($conn, "tbl_document", $search, $company, $response, $roleKey);
    }
}

echo json_encode(!empty($response) ? $response : ["error" => "No transactions found"]);
exit();
