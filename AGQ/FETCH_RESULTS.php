<?php
require 'db_agq.php';

if (isset($_GET['query'])) {
    $search = "%" . $_GET['query'] . "%";


    $sql = "SELECT CompanyID, Company_name FROM tbl_company 
            WHERE CompanyID LIKE ? OR Company_name LIKE ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("SQL Error: " . $conn->error);
    }


    $stmt->bind_param("is", $search, $search);


    $stmt->execute();
    $result = $stmt->get_result();


    $companies = [];
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }

    // Return JSON response
    echo json_encode($companies);
}
