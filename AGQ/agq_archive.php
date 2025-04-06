<?php
require 'db_agq.php';
session_start();

$role = "Admin";
$dept = isset($_SESSION['SelectedDepartment']) && !empty($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : 'Import Forwarding';


$searchq = isset($_GET['search']) ? $_GET['search'] : '';

if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}
// Retrieve search term if provided
$search_term = isset($_GET['search']) ? '%' . $conn->real_escape_string($_GET['search']) . '%' : '';

// Build query based on department and search term
$query = "SELECT * FROM tbl_archive";
$conditions = []; // Array to store WHERE conditions
$params = []; // Array to store bind parameters
$types = ""; // Parameter types for bind_param

// Apply department filter
if (!empty($dept)) {
    $conditions[] = "Department LIKE ?";
    $params[] = '%' . $dept . '%'; // Wildcard for LIKE
    $types .= "s"; // String type
}

// Apply search filter
if (!empty($search_term)) {
    $conditions[] = "(Company_name LIKE ? OR RefNum LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Append WHERE conditions if any exist
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch results
$archived = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $archived[] = $row;
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" type="text/css" href="../css/archive.css">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <title>Archives</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body style="background-image: url('a3bg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="top-container">
        <div class="dept-container">
            <div class="dept-label">
                <?php echo htmlspecialchars($role); ?>
            </div>
        </div>
    </div>

    <a href="agq_dashCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>

    <div class="search-container">
        <form class="searchcont method=" GET" action="agq_archive.php">
            <input type="text" class="search-input" name="search" id="searchInput" placeholder="Search archives..." />
            <button class="search-button" type="submit">SEARCH</button>
        </form>
    </div>

    <div class="header-container">
        <div class="spacer"></div>
        <div class="table-title">
            <h1>ARCHIVES</h1>
        </div>
        <div class="undo-button-container">
            <select id="departmentFilter" class="department-dropdown" onchange="updateDepartment(this.value)">
                <option value="" disabled <?php echo empty($dept) ? 'selected' : ''; ?>>All Departments</option>
                <option value="Import Forwarding" <?php echo ($dept == 'Import Forwarding') ? 'selected' : ''; ?>>Import Forwarding</option>
                <option value="Import Brokerage" <?php echo ($dept == 'Import Brokerage') ? 'selected' : ''; ?>>Import Brokerage</option>
                <option value="Export Forwarding" <?php echo ($dept == 'Export Forwarding') ? 'selected' : ''; ?>>Export Forwarding</option>
                <option value="Export Brokerage" <?php echo ($dept == 'Export Brokerage') ? 'selected' : ''; ?>>Export Brokerage</option>
            </select>
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
                    echo "<td>" . $formatted_date . "</td>";
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
        function updateDepartment(selectedDept) {

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "STORE_SESSION.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");


            xhr.send("selected_department=" + encodeURIComponent(selectedDept));


            xhr.onload = function() {
                if (xhr.status == 200) {
                    console.log("Department updated to: " + selectedDept);

                    location.reload();
                }
            };
        }

        function openModal() {
            let modal = document.getElementById("archiveModal");
            modal.style.display = "flex"; // Make modal visible
            setTimeout(() => modal.classList.add("show"), 10); // Trigger animation

            let editInput = document.getElementById("edit-input");
            editInput.value = ""; // Clear previous input
            editInput.focus(); // Set focus on input
        }

        function closeModal() {
            let modal = document.getElementById("archiveModal");
            modal.classList.remove("show");
            setTimeout(() => modal.style.display = "none", 0); // Hide after animation
        }

        function deleteDocument() {
            let editInput = document.getElementById("edit-input");
            if (!editInput) {
                console.error("Error: Input field not found.");
                Swal.fire("Error", "Input field not found.", "error");
                return;
            }

            let refNum = editInput.value.trim();
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

                    let formData = new URLSearchParams();
                    formData.append("RefNum", refNum);

                    fetch("ARCHIVE_HANDLE.php?action=delete", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: formData.toString()
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error("Network response was not ok. Status: " + response.status);
                            }
                            return response.json(); // only call response.json() once
                        })
                        .then(data => { // 🔹 Ensure data is always checked before use
                            if (!data) {
                                throw new Error("Invalid JSON response");
                            }
                            Swal.fire({
                                title: data.success ? "Deleted!" : "Error!",
                                icon: data.success ? "success" : "error",
                            }).then(() => {
                                if (data.success) {
                                    closeModal();
                                    location.reload();
                                }
                            });
                        })
                        .catch(error => {
                            console.error("Fetch Error:", error);
                            Swal.fire("Error", "An error occurred while processing your request. Please try again.", "error");
                        });
                }
            });
        }



        function restoreDocument() {
            let refNum = document.getElementById("edit-input").value.trim();
            Swal.fire({
                title: "Are you sure?",
                text: "You are about to restore this document.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, restore it!"
            }).then((result) => {

                let formData = new URLSearchParams();
                formData.append("RefNum", refNum);
                if (result.isConfirmed) {
                    fetch("ARCHIVE_HANDLE.php?action=restore", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: formData.toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire(data.success ? "Restored!" : "Error!", data.message, data.success ? "success" : "error")
                                .then(() => {
                                    if (data.success) {
                                        closeModal();
                                        location.reload();
                                    }
                                });
                        })
                        .catch(error => {
                            console.error("Error:", error, data.message);
                            Swal.fire("Error", "An error occurred. Please try again.", "error");
                        });
                }
            });
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>