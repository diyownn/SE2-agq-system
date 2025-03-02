<?php
session_start();
require 'db_agq.php';

header("Content-Type: application/json");

// Validate session
$role = $_SESSION['department'] ?? '';

// Retrieve query parameter
$query = $_GET['query'] ?? '';

// Initialize response array
$response = ["company" => [], "expbrk" => [], "expfwd" => [], "impbrk" => [], "impfwd" => []];

// Fetch company details
if ($stmt = $conn->prepare("SELECT Company_name, Company_picture FROM tbl_company WHERE Company_name LIKE ?")) {
    $like_query = "%{$query}%";
    $stmt->bind_param("s", $like_query);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['Company_picture'])) {
            $row['Company_picture'] = base64_encode($row['Company_picture']);
        }
        $response["company"][] = $row;
    }
    $stmt->close();
}

// Define department-specific queries
$tables = [
    "Export Brokerage"  => "tbl_expbrk",
    "Export Forwarding" => "tbl_expfwd",
    "Import Brokerage"  => "tbl_impbrk",
    "Import Forwarding" => "tbl_impfwd"
];

if (isset($tables[$role])) {
    $table = $tables[$role];

    if ($stmt = $conn->prepare("SELECT RefNum FROM $table WHERE RefNum LIKE ?")) {
        $stmt->bind_param("s", $like_query);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response[strtolower(str_replace(" ", "", $role))][] = $row;
        }
        $stmt->close();
    }
}

// Output JSON response
echo json_encode($response);
