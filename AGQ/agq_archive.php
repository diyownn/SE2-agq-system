<?php
require 'db_agq.php';
session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';


$sqlArchive = "SELECT RefNum FROM tbl_archive";
$resultArchive = $conn->query($sqlArchive);

if ($resultArchive && $resultArchive->num_rows > 0) {
    while ($row = $resultArchive->fetch_assoc()) {
        $refNum = $row['RefNum'];


        $tables = ["tbl_impfwd", "tbl_impbrk", "tbl_expfwd", "tbl_expbrk"];
        $shouldDelete = false;

        foreach ($tables as $table) {
            $sqlCheck = "SELECT RefNum FROM $table WHERE RefNum = ? AND isArchived != 1";
            $stmtCheck = $conn->prepare($sqlCheck);

            if (!$stmtCheck) {
                die(json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]));
            }

            $stmtCheck->bind_param("s", $refNum);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows > 0) {
                $shouldDelete = true;
            }

            $stmtCheck->close();
            if ($shouldDelete) break;
        }

        if ($shouldDelete) {
            $sqlDelete = "DELETE FROM tbl_archive WHERE RefNum = ?";
            $stmtDelete = $conn->prepare($sqlDelete);

            if (!$stmtDelete) {
                die(json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]));
            }

            $stmtDelete->bind_param("s", $refNum);
            $stmtDelete->execute();
            $stmtDelete->close();
        }
    }
}


if (isset($_GET['search'])) {
    $searchTerm = "%" . $_GET['search'] . "%";
    $stmt = $conn->prepare("SELECT archive_id, RefNum, Company_name, Department, archive_date FROM tbl_archive 
                            WHERE archive_id LIKE ? OR RefNum LIKE ? OR Company_name LIKE ? OR Department LIKE ?");
    if (!$stmt) {
        die(json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]));
    }

    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $archived = [];
    while ($archive = $result->fetch_assoc()) {
        $archived[] = $archive;
    }

    echo json_encode(["success" => true, "archived" => $archived]);
    exit;
} else {
    $query = "SELECT archive_id, RefNum, Company_name, Department, archive_date FROM tbl_archive";
    $result = $conn->query($query);

    $archived = [];
    while ($archive = $result->fetch_assoc()) {
        $archived[] = $archive;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" type="text/css" href="../css/archive.css">
    <link rel="icon" href="images/agq_logo.png" type="image/ico">
    <title>Archives</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body style="background-image: url('a4bg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="top-container">
        <div class="dept-container">
            <div class="dept-label">
                <?php echo htmlspecialchars($role); ?>
            </div>
        </div>
    </div>

    <a href="agq_dashCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>

    <div class="search-container">
        <input type="text" class="search-input" placeholder="Search archives..." />
        <button class="search-button" id="search-button">SEARCH</button>
    </div>

    <div class="header-container">
        <div class="spacer"></div>
        <div class="table-title">
            <h1>ARCHIVES</h1>
        </div>
        <div class="undo-button-container">
            <button class="undo-button" onclick="openModal()">EDIT</button>
        </div>
    </div>

    <div class="container">
        <table id="archivesTable">
            <thead>
                <tr>
                    <th>ARCHIVED ID</th>
                    <th>COMPANY NAME</th>
                    <th>REFERENCE NUMBER</th>
                    <th>DEPARTMENT</th>
                    <th>ARCHIVE DATE</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                foreach ($archived as $row) {
                    $formatted_date = !empty($row['archive_date'])
                        ? date("F d, Y h:i A", strtotime($row['archive_date']))
                        : 'No Date Available';

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["archive_id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["Company_name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["RefNum"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["Department"]) . "</td>";
                    echo "<td>" .  $formatted_date  . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="archiveModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>EDIT ARCHIVE</h2>
            <label for="edit-input">Reference Number</label>
            <input class="edit-input" type="text" id="edit-input" name="edit-input" required>
            <button class="restore-button" onclick="restoreDocument()">RESTORE</button>
            <button class="delete-button" onclick="deleteDocument()">DELETE</button>
        </div>
    </div>

    <script>
        function openModal() {
            let modal = document.getElementById("archiveModal");
            modal.style.display = "flex"; // Make modal visible
            setTimeout(() => modal.classList.add("show"), 10); // Trigger animation
        }

        function closeModal() {
            let modal = document.getElementById("archiveModal");
            modal.classList.remove("show");
            setTimeout(() => modal.style.display = "none", 0); // Hide after animation
        }

        function deleteDocument() {
            let refNum = document.getElementById("edit-input").value.trim();
            if (!refNum) {
                Swal.fire("Error", "Please enter a Reference Number.", "error");
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("ARCHIVE_HANDLE.php?action=delete", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "RefNum=" + encodeURIComponent(refNum)
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                title: data.success ? "Deleted!" : "Error!",
                                text: data.message,
                                icon: data.success ? "success" : "error",
                            }).then(() => {
                                if (data.success) {
                                    closeModal();
                                    location.reload();
                                }
                            });
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            Swal.fire("Error", "An error occurred. Please try again.", "error");
                        });
                }
            });
        }

        function restoreDocument() {
            let refNum = document.getElementById("edit-input").value.trim();
            if (!refNum) {
                Swal.fire("Error", "Please enter a Reference Number.", "error");
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "You are about to restore this document.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, restore it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("ARCHIVE_HANDLE.php?action=restore", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "RefNum=" + encodeURIComponent(refNum)
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                title: data.success ? "Restored!" : "Error!",
                                text: data.message,
                                icon: data.success ? "success" : "error",
                            }).then(() => {
                                if (data.success) {
                                    closeModal();
                                    location.reload();
                                }
                            });
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            Swal.fire("Error", "An error occurred. Please try again.", "error");
                        });
                }
            });
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>