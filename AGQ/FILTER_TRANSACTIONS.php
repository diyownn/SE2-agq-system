<?php

require_once 'db_agq.php'; // Adjust as needed

header('Content-Type: application/json');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$role = $_SESSION['department'] ?? '';
$dept = $_SESSION['SelectedDepartment'] ?? '';
$company = $_SESSION['Company_name'] ?? '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$response = [];

error_log("Search Query: " . $search_query);

$tables = [
    "Import Forwarding" => "tbl_impfwd",
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
];

$table = $tables[$role] ?? null;
$selectedDeptTable = $tables[$dept] ?? null;

// If search query is provided      
if (!empty($search_query)) {
    $like_query = "%{$search_query}%";
    
    if ($table) {
        $query = "SELECT '$role' AS Department, RefNum, DocType, Company_name 
                  FROM $table 
                  WHERE (RefNum LIKE ? OR DocType LIKE ?) 
                  AND Company_name LIKE ?";
        $params = [$like_query, $like_query, $company];
        $types = "sss";
    }
    
    // If a department is selected, use its table
    if (!empty($dept) && $selectedDeptTable) {
        $query = "SELECT '$dept' AS Department, RefNum, DocType, Company_name 
                  FROM $selectedDeptTable 
                  WHERE (RefNum LIKE ? OR DocType LIKE ?) 
                  AND Company_name LIKE ?";
        $params = [$like_query, $like_query, $company];
    }

    // If user is "Import Forwarding", fetch from `tbl_document` as well
    if ($role === "Import Forwarding" || $dept === "Import Forwarding") {
        $query .= " UNION 
                    SELECT 'Import Forwarding' AS Department, RefNum, DocType, Company_name 
                    FROM tbl_document 
                    WHERE (RefNum LIKE ? OR DocType LIKE ?) 
                    AND Company_name LIKE ? 
                    AND isArchived = 0";
        $params = array_merge($params, [$like_query, $like_query, $company]);
    }

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);

        if ($stmt->execute()) {
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
                    "Company" => $row['Company_name']
                ];
            }
        } else {
            error_log("SQL Execution Error: " . $stmt->error);
        }

        $stmt->close();
    } else {
        error_log("SQL Prepare Error: " . $conn->error);
    }

// If search query is empty
} else {
    $query = "";
    
    // Fetch all documents based on department and role
    if ($role !== "Admin") {
        $query = "
            SELECT '$role' AS Department, RefNum, DocType, Company_name 
            FROM $table 
            WHERE Company_name LIKE ?
        ";

        if (!empty($dept) && $selectedDeptTable) {
            $query .= " UNION 
                        SELECT '$dept' AS Department, RefNum, DocType, Company_name 
                        FROM $selectedDeptTable 
                        WHERE Company_name LIKE ?";
        }

        $params = [$company];
        if (!empty($dept) && $selectedDeptTable) {
            $params[] = $company;
        }
    }

    // If role/department is "Import Forwarding", fetch from `tbl_document` where isArchived = 0
    if ($role === "Import Forwarding" || $dept === "Import Forwarding") {
        $query .= " UNION 
                    SELECT 'Import Forwarding' AS Department, RefNum, DocType, Company_name 
                    FROM tbl_document 
                    WHERE Company_name LIKE ? 
                    AND isArchived = 0";
        $params[] = $company;
    }

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);

        if ($stmt->execute()) {
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
                    "Company" => $row['Company_name']
                ];
            }
        } else {
            error_log("SQL Execution Error: " . $stmt->error);
        }

        $stmt->close();
    } else {
        error_log("SQL Prepare Error: " . $conn->error);
    }
}

$conn->close();

echo json_encode(!empty($response) ? $response : []);
exit();
