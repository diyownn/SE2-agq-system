<?php
session_start();
require 'db_agq.php';
error_reporting(E_ALL);
header("Content-Type: application/json");

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

// Ensure that $dept is valid
if (!empty($dept) && isset($tables[$dept])) {
    $table = $tables[$dept];
    $query = "SELECT RefNum, DocType FROM $table WHERE (RefNum LIKE ? OR DocType LIKE ? OR DocType = 'Manifesto') AND Company_name = ?";
    $like_query = "%{$search}%";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sss", $like_query, $like_query, $company);
        $stmt->execute();
        $result = $stmt->get_result();

        $deptKey = strtolower(str_replace(" ", "", $dept));
        $response[$deptKey] = [];

        while ($row = $result->fetch_assoc()) {
            $response[$deptKey][] = $row;
        }
        $stmt->close();
    }
}

// If the role exists in the tables array and there's a search query, handle it
if (!empty($role) && isset($tables[$role])) {
    $like_query = "%{$search}%";
    $query = "SELECT '$role' AS Department, RefNum, DocType, Company_name 
              FROM {$tables[$role]} 
              WHERE (RefNum LIKE ? OR DocType LIKE ?) AND Company_name = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sss", $like_query, $like_query, $company);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $department = $row['Department'];
            $docType = strtoupper(trim($row['DocType']));

            if (!isset($response[$department])) {
                $response[$department] = [];
            }

            if (!isset($response[$department][$docType])) {
                $response[$department][$docType] = [];
            }

            $response[$department][$docType][] = [
                "RefNum" => $row['RefNum'],
                "DocType" => $row['DocType']
            ];
        }
        $stmt->close();
    }
}

// **Additional fetch from tbl_document**
$document_query = "SELECT RefNum, DocType FROM tbl_document WHERE RefNum LIKE ? OR DocType LIKE ?";

if ($stmt = $conn->prepare($document_query)) {
    $stmt->bind_param("ss", $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();

    $response['documents'] = [];

    while ($row = $result->fetch_assoc()) {
        $response['documents'][] = [
            "RefNum" => $row['RefNum'],
            "DocType" => $row['DocType']
        ];
    }
    $stmt->close();
}

echo json_encode(!empty($response) ? $response : ["error" => "No transactions found"]);
exit();
