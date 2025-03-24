<?php
include 'db_agq.php'; // Include your DB connection

if (isset($_GET['refNum'])) {
    $refNum = $_GET['refNum'];

    // Check if the document is approved
    $query = "SELECT isApproved FROM tbl_impfwd WHERE RefNum = ? 
              UNION 
              SELECT isApproved FROM tbl_impbrk WHERE RefNum = ?
              UNION 
              SELECT isApproved FROM tbl_expfwd WHERE RefNum = ?
              UNION 
              SELECT isApproved FROM tbl_expbrk WHERE RefNum = ?
              UNION 
              SELECT isApproved FROM tbl_document WHERE RefNum = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $refNum, $refNum, $refNum, $refNum, $refNum);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["isApproved" => $row['isApproved']]);
    } else {
        echo json_encode(["isApproved" => 0]);
    }

    $stmt->close();
    $conn->close();
}
