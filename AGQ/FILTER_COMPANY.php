<?php
require 'db_agq.php';

header('Content-Type: application/json');

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$companies = [];

if (!empty($search_query)) {
 
    $stmt = $conn->prepare("SELECT CompanyID, Company_name, Company_picture FROM tbl_company WHERE Company_name LIKE ?");
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("s", $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    
    $sql = "SELECT CompanyID, Company_name, Company_picture FROM tbl_company";
    $result = $conn->query($sql);
}

// Fetch all results
while ($row = $result->fetch_assoc()) {
   
    
    

    $companies[] = [
        "CompanyID" => $row["CompanyID"],
        "Company_name" => $row["Company_name"],
        "Company_picture" => base64_encode($row["Company_picture"])
    ];
}


if (!empty($search_query)) {
    $stmt->close();
}



$conn->close();

echo json_encode(["company" => $companies]);
exit();

