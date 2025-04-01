<?php
require 'db_agq.php';
session_start(); // Ensure session is started

// Check if the session variable is set
if (!isset($_SESSION['SelectedDepartment'])) {
    die("Error: Department not set.");
}

$dept = $_SESSION['SelectedDepartment']; // Assign the department from session

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["comment"]) && isset($_POST["refNum"])) {
    $comment = trim($_POST["comment"]); // Trim to avoid whitespace-only comments
    $refNum = trim($_POST["refNum"]);

    if (empty($comment) || empty($refNum)) {
        die("Error: Comment or RefNum is empty.");
    }

    // Define the mapping of departments to table names
    $tables = [
        "Import Forwarding" => "tbl_impfwd",
        "Export Brokerage"  => "tbl_expbrk",
        "Export Forwarding" => "tbl_expfwd",
        "Import Brokerage"  => "tbl_impbrk",
    ];

    // Validate that the department exists in our mapping
    if (!isset($tables[$dept])) {
        die("Error: Invalid department selected.");
    }

    $table = $tables[$dept];

    // Prepare the SQL statement
    $sql = "UPDATE $table SET Comment = ? WHERE RefNum = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing SQL: " . $conn->error);
    }

    $stmt->bind_param("ss", $comment, $refNum);

    if ($stmt->execute()) {
        echo "Comment saved successfully!";
    } else {
        echo "Error executing query: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
