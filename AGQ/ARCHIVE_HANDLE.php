<?php
require 'db_agq.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_GET["action"])) {
    $action = $_GET["action"];
    $refNum = isset($_POST["refNum"]) ? $conn->real_escape_string($_POST["refNum"]) : '';

    if (empty($refNum)) {
        echo json_encode(["success" => false, "message" => "Reference Number is required."]);
        exit();
    }

    if ($action === "delete") {
        $sql = "DELETE FROM tbl_archive WHERE RefNum = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $refNum);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Document successfully deleted."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting document."]);
        }
        $stmt->close();
    } elseif ($action === "restore") {
        // Fetch the row before deleting
        $sqlFetch = "SELECT * FROM tbl_archive WHERE RefNum = ?";
        $stmtFetch = $conn->prepare($sqlFetch);
        $stmtFetch->bind_param("s", $refNum);
        $stmtFetch->execute();
        $result = $stmtFetch->get_result();
        $row = $result->fetch_assoc();
        $stmtFetch->close();

        if ($row) {
            $company = $row["Company_name"];
            $department = ["Import Forwarding","Import Brokerage","",""];

            // Check which table it belongs to
            $restoreTable = ($department === "Finance") ? "tbl_finance" : "tbl_hr";

            // Restore to appropriate table
            $sqlRestore = "INSERT INTO $restoreTable (RefNum, Company_name, archive_date) VALUES (?, ?, ?)";
            $stmtRestore = $conn->prepare($sqlRestore);
            $stmtRestore->bind_param("sss", $row["RefNum"], $row["Company_name"], $row["archive_date"]);

            if ($stmtRestore->execute()) {
                // Delete from archive after restoring
                $sqlDelete = "DELETE FROM tbl_archive WHERE RefNum = ?";
                $stmtDelete = $conn->prepare($sqlDelete);
                $stmtDelete->bind_param("s", $refNum);
                $stmtDelete->execute();
                $stmtDelete->close();

                echo json_encode(["success" => true, "message" => "Document successfully restored."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error restoring document."]);
            }
            $stmtRestore->close();
        } else {
            echo json_encode(["success" => false, "message" => "Reference Number not found in archives."]);
        }
    }

    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
