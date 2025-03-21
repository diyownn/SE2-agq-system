<?php

require_once 'db_agq.php'; // Adjust as needed

header('Content-Type: application/json');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$role = $_SESSION['department'] ?? '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';
$dept = $_SESSION['SelectedDepartment'] ?? ''; // Selected department filter
$response = [];


error_log("Search Query: " . $search_query);

$tables = [
    "Import Forwarding" => "tbl_impfwd",
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
];

$table = $tables[$role] ?? null; // Get table based on role


// If search query is provided, fetch filtered results
if (!empty($search_query)) {
    $like_query = "%{$search_query}%";
    $query = "SELECT '$role' AS Department, RefNum, DocType, Company_name 
              FROM $table 
              WHERE RefNum LIKE ? OR DocType LIKE ? AND Company_name LIKE ?";

    $params = [$like_query, $like_query, $company];
    $types = "sss";


    if (!empty($dept) && isset($tables[$dept])) {
        $table = $tables[$dept];
        $query = "SELECT '$dept' AS Department, RefNum, DocType, Company_name 
                  FROM $table 
                  WHERE RefNum LIKE ? OR DocType LIKE ? AND Company_name LIKE ?";
        $params = [$like_query, $like_query, $company];
    }

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param($types, ...$params);

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
} elseif (empty($search_query) && $role === "Admin") {
    $query = "
        SELECT i.RefNum, i.DocType, c.Company_name
        FROM tbl_impfwd i
        JOIN tbl_company c ON i.Company_name = c.Company_name
        WHERE '$dept' = 'Import Forwarding'
        
        UNION 
        
        SELECT b.RefNum, b.DocType, c.Company_name
        FROM tbl_impbrk b
        JOIN tbl_company c ON b.Company_name = c.Company_name
        WHERE '$dept' = 'Import Brokerage'
        
        UNION 
        
        SELECT f.RefNum, f.DocType, c.Company_name
        FROM tbl_expfwd f
        JOIN tbl_company c ON f.Company_name = c.Company_name
        WHERE '$dept' = 'Export Forwarding'
        
        UNION 
        
        SELECT e.RefNum, e.DocType, c.Company_name
        FROM tbl_expbrk e
        JOIN tbl_company c ON e.Company_name = c.Company_name
        WHERE '$dept' = 'Export Brokerage'
    ";

    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $docType = strtoupper(trim($row['DocType']));
            $department = $dept; // Use selected department

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
        error_log("SQL Execution Error: " . $conn->error);
    }
} else {
    $query = "
        SELECT i.RefNum, i.DocType, c.Company_name
        FROM tbl_impfwd i
        JOIN tbl_company c ON i.Company_name = c.Company_name
        WHERE '$role' = 'Import Forwarding'
        
        UNION 
        
        SELECT b.RefNum, b.DocType, c.Company_name
        FROM tbl_impbrk b
        JOIN tbl_company c ON b.Company_name = c.Company_name
        WHERE '$role' = 'Import Brokerage'
        
        UNION 
        
        SELECT f.RefNum, f.DocType, c.Company_name
        FROM tbl_expfwd f
        JOIN tbl_company c ON f.Company_name = c.Company_name
        WHERE '$role' = 'Export Forwarding'
        
        UNION 
        
        SELECT e.RefNum, e.DocType, c.Company_name
        FROM tbl_expbrk e
        JOIN tbl_company c ON e.Company_name = c.Company_name
        WHERE '$role' = 'Export Brokerage'
    ";

    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $docType = strtoupper(trim($row['DocType']));
            $department = $dept; // Use selected department

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
        error_log("SQL Execution Error: " . $conn->error);
    }
}

$conn->close();

echo json_encode(!empty($response) ? $response : []);
exit();
