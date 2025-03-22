<?php
session_start();
require 'db_agq.php';

header("Content-Type: application/json");

$query = $_GET['query'] ?? ''; 

$response = [];

if (!empty($query)) {
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
} 

// Return JSON response
echo json_encode(!empty($response) ? $response : ["error" => "No companies found"]);
?>
