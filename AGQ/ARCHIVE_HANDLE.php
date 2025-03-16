<?php
require 'db_agq.php';

header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["action"])) {
    $action = $_GET["action"];
    $refNum = isset($_POST["RefNum"]) ? $_POST["RefNum"] : '';

    if (empty($refNum)) {
        echo json_encode(["success" => false, "message" => "Reference Number is required."]);
        exit();
    }

    // Define tables
    $tables = ["tbl_archive", "tbl_impfwd", "tbl_impbrk", "tbl_expfwd", "tbl_expbrk"];

    if ($action === "delete") {
        $deletedFrom = [];

        foreach ($tables as $table) {
            $sqlDelete = "DELETE FROM $table WHERE RefNum = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("s", $refNum);
            $stmtDelete->execute();

            if ($stmtDelete->affected_rows > 0) {
                $deletedFrom[] = $table;
            }

            $stmtDelete->close();
        }

        if (!empty($deletedFrom)) {
            echo json_encode(["success" => true, "message" => "Deleted from: " . implode(", ", $deletedFrom)]);
        } else {
            echo json_encode(["success" => false, "message" => "No matching record found."]);
        }
    } elseif ($action === "restore") {

        // Debug: Log received data
        echo "<script>console.log('DEBUG: Received POST Data:', " . json_encode($_POST) . ");</script>";

        $sqlFetch = "SELECT Company_name, RefNum, Department FROM tbl_archive WHERE RefNum = ?";
        $stmtFetch = $conn->prepare($sqlFetch);
        $stmtFetch->bind_param("s", $refNum);
        $stmtFetch->execute();
        $result = $stmtFetch->get_result();
        $row = $result->fetch_assoc();
        $stmtFetch->close();

        if (!$row) {
            echo json_encode(["success" => false, "message" => "Reference Number not found in archives."]);
            exit();
        }

        $departments = [
            "Import Forwarding" => "tbl_impfwd",
            "Import Brokerage" => "tbl_impbrk",
            "Export Forwarding" => "tbl_expfwd",
            "Export Brokerage" => "tbl_expbrk"
        ];

        $restoreTable = $departments[$row["Department"]] ?? null;

        if (!$restoreTable) {
            echo json_encode(["success" => false, "message" => "Invalid department."]);
            exit();
        }

        // Debug: Check the target table
        echo "<script>console.log('DEBUG: Restoring to table:', '" . $restoreTable . "');</script>";

        // Check if the record exists in the restore table
        $sqlCheck = "SELECT COUNT(*) FROM $restoreTable WHERE RefNum = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $row["RefNum"]);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();

        // Debug: Check if record exists in the table
        echo "<script>console.log('DEBUG: Record exists count:', " . $count . ");</script>";

        if ($count > 0) {
            // Update the existing record
            $sqlUpdate = "UPDATE $restoreTable SET isArchived = 0 WHERE RefNum = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("s", $row["RefNum"]);
        } else {
            // Insert a new record
            $sqlUpdate = "INSERT INTO $restoreTable (Company_name, RefNum, Department, isArchived) VALUES (?, ?, ?, 0)";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("sss", $row["Company_name"], $row["RefNum"], $row["Department"]);
        }

        if ($stmtUpdate->execute() && $stmtUpdate->affected_rows > 0) {
            echo "<script>console.log('SUCCESS: Record updated/inserted successfully!');</script>";

            // Delete from tbl_archive after successful restore
            $sqlDelete = "DELETE FROM tbl_archive WHERE RefNum = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("s", $row["RefNum"]);
            $stmtDelete->execute();

            if ($stmtDelete->affected_rows > 0) {
                echo "<script>console.log('SUCCESS: Record deleted from archive');</script>";
            } else {
                echo "<script>console.warn('WARNING: No record deleted from archive.');</script>";
            }

            $stmtDelete->close();
            echo json_encode(["success" => true, "message" => "Document successfully restored."]);
        } else {
            echo "<script>console.error('ERROR: Query execution failed:', '" . $stmtUpdate->error . "');</script>";
            echo json_encode(["success" => false, "message" => "Error restoring document."]);
        }

        $stmtUpdate->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid action."]);
    }


    $conn->close();
}
