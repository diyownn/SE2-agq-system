<?php
require 'db_agq.php';

header("Content-Type: application/json");

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (!empty($query)) {
    $stmt = $conn->prepare("SELECT Company_name, Company_picture FROM tbl_company WHERE Company_name LIKE ?");
    $like_query = "%" . $query . "%";
    $stmt->bind_param("s", $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT Company_name, Company_picture FROM tbl_company");
}

$companies = [];
while ($row = $result->fetch_assoc()) {
    $row['Company_picture'] = base64_encode($row['Company_picture']); // Ensure image data is in valid format
    $companies[] = $row;
}

echo json_encode(["company" => $companies]);
?>