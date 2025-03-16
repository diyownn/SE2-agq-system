<?php
require 'db_agq.php';

session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

// Fetch all RefNum from tbl_archive
$sqlArchive = "SELECT RefNum FROM tbl_archive";
$resultArchive = $conn->query($sqlArchive);

if ($resultArchive->num_rows > 0) {
    while ($row = $resultArchive->fetch_assoc()) {
        $refNum = $row['RefNum'];

        // Check if RefNum exists in any of the tables where isArchived != 1
        $tables = ["tbl_impfwd", "tbl_impbrk", "tbl_expfwd", "tbl_expbrk"];
        $shouldDelete = false;

        foreach ($tables as $table) {
            $sqlCheck = "SELECT RefNum FROM $table WHERE RefNum = ? AND isArchived != 1";
            $stmtCheck = $conn->prepare($sqlCheck);
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
            $stmtDelete->bind_param("s", $refNum);
            $stmtDelete->execute();
            $stmtDelete->close();
        }
    }
}


if (isset($_GET['search'])) {
    $searchTerm = "%" . $_GET['search'] . "%";
    $stmt = $conn->prepare("SELECT archive_id, RefNum, Company_name, Department, archive_date FROM tbl_archive WHERE archive_id LIKE ? OR RefNum LIKE ? OR Company_name LIKE ?");
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
    $query = "SELECT archive_id, RefNum, Company_name, archive_date FROM tbl_archive";
    $result = $conn->query($query);

    $users = [];
    while ($archive = $result->fetch_assoc()) {
        $archived[] = $archive;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="../css/archive.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <link rel="icon" href="images/agq_logo.png" type="image/ico">
    <title>Archives</title>
</head>

<body>
    <div class="top-container">
        <div class="dept-container">
            <div class="dept-label">
                <?php echo htmlspecialchars($role); ?>
            </div>
        </div>
    </div>

    <a href="agq_owndash.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>


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
            <button class="undo-button" onclick=openModal()>EDIT</button>
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
                $sql = "SELECT archive_id, RefNum, Company_name, Department, archive_date FROM tbl_archive";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
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
                } else {
                    echo "<tr><td colspan='4'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="archiveModal" class="modal">
        <div class="modal-content">
            <form action="">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>EDIT ARCHIVE</h2>

                <div id="modalErrors" style="color: red; display: none;"></div>

                <label for="edit-input">Reference Number</label>
                <input class="edit-input" type="text" id="edit-input" name="edit-input" required>

                <button class="restore-button" id="restore-button" onclick="restoreDocument()">RESTORE</button>
                <button class="delete-button" id="delete-button" onclick="deleteDocument()">DELETE</button>
            </form>
        </div>
    </div>


</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("archiveModal").style.display = "none";
    });

    function openModal() {
        document.getElementById("archiveModal").style.display = "flex";
        document.getElementById("modalErrors").style.display = "none";
    }

    function closeModal() {
        document.getElementById("archiveModal").style.display = "none";
    }


    function deleteDocument() {
        let refNum = document.getElementById("edit-input").value.trim();

        if (refNum === "") {
            return;
        }

        if (confirm("Are you sure you want to permanently delete this document?")) {
            fetch("ARCHIVE_HANDLE.php?action=delete", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "RefNum=" + encodeURIComponent(refNum)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();
                    }
                })
                .catch(error => console.error("Error deleting document:", error));
        }
    }

    function restoreDocument() {
        let refNum = document.getElementById("edit-input").value.trim();

        if (refNum === "") {
            alert("Please enter a Reference Number.");
            return;
        }

        if (confirm("Are you sure you want to restore this document?")) {
            fetch("ARCHIVE_HANDLE?action=restore", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "RefNum=" + encodeURIComponent(refNum)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();

                    }
                })
                .catch(error => console.error("Error restoring document:", error));
        }
    }

    document.querySelector(".search-button").addEventListener("click", function() {
        const searchTerm = document.querySelector(".search-input").value.toLowerCase();
        const rows = document.querySelectorAll("#tableBody tr");

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? "" : "none";
        });
    });

    document.querySelector(".undo-button").addEventListener("click", function() {
        document.querySelector(".search-input").value = "";
        document.querySelectorAll("#tableBody tr").forEach(row => row.style.display = "");
    });

    document.querySelector(".search-input").addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            document.querySelector(".search-button").click();
        }
    });
</script>

</html>
<?php $conn->close(); ?>