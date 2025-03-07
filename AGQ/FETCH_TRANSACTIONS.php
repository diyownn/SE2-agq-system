<?php
session_start();
require 'db_agq.php';
error_reporting(E_ALL);
header("Content-Type: application/json");

$role = $_SESSION['department'] ?? '';
$dept = $_SESSION['SelectedDepartment'] ?? '';
$search = $_GET['search'] ?? '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

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
                                  WHERE (RefNum LIKE ? OR DocType LIKE ?) AND Company_name = ?";
                $params = array_merge($params, [$like_query, $like_query, $company]);
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



if (!empty($search) && isset($tables[$dept])) {
    $table = $tables[$dept];

    $query = "SELECT RefNum, DocType FROM $table WHERE (RefNum LIKE ? OR DocType LIKE ?) AND Company_name = ?";

    if ($stmt = $conn->prepare($query)) {
        $like_query = "%{$search}%";
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
} else {
    if (empty($response) && (isset($tables[$dept]) || $role === "Admin")) {
        $table = $tables[$dept];
        $query = "SELECT RefNum, DocType FROM $table WHERE Company_name = ?";

        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $company);
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
}


echo json_encode(!empty($response) ? $response : ["error" => "No transactions found"]);
