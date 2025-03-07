<?php

require_once 'db_agq.php'; // Adjust as needed

header('Content-Type: application/json');
session_start();


error_reporting(E_ALL);
ini_set('display_errors', 1);



$role = $_SESSION['department'] ?? '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$dept = $_SESSION['SelectedDepartment'] ?? ''; // Selected department filter
$response = [];


$tables = [
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
    "Import Forwarding" => "tbl_impfwd"
];

$roleTable = $tables[$role] ?? null;


if (isset($tables[$role])) {
    if (!empty($search_query)) {
        $like_query = "%{$search_query}%";
        $query_parts = [];
        $params = [];
        $types = "";

        
        foreach ($roleTables as $department => $table) {
            if ($role === $department) { 
                $query_parts[] = "SELECT '$department' AS Department, RefNum, DocType, Company_name 
                                  FROM $table 
                                  WHERE RefNum LIKE ? OR DocType LIKE ? OR Company_name LIKE ?";
                $params = array_merge($params, [$like_query, $like_query, $like_query]);
                $types .= "sss";
            }
        }

        if (!empty($query_parts)) {
            $query = implode(" UNION ", $query_parts);

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
        }
    }
}

if (!empty($dept) && !isset($tables[$dept])) {
    echo json_encode(["error" => "Invalid department selected"]);
    exit();
}


$table = $tables[$dept] ?? null;

if ($table && !empty($search_query)) {
    $like_query = "%{$search_query}%";

    // Correctly include Department column
    $query = "SELECT '$dept' AS Department, RefNum, DocType, Company_name FROM $table 
              WHERE RefNum LIKE ? OR DocType LIKE ? OR Company_name LIKE ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sss", $like_query, $like_query, $like_query);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $department = $row['Department']; // Manually added in SQL
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
