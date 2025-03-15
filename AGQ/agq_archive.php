<?php
require 'db_agq.php';

session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

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
    <div class="search-container">
        <input type="text" class="search-input" placeholder="Search archives..." />
        <button class="search-button">SEARCH</button>
    </div>

    <div class="header-container">
        <div class="spacer"></div>
        <div class="table-title">
            <h1>ARCHIVES</h1>
        </div>
        <div class="undo-button-container">
            <button class="undo-button">UNDO</button>
        </div>
    </div>

    <div class="container">
        <table id="archivesTable">
            <thead>
                <tr>
                    <th>ARCHIVED ID</th>
                    <th>COMPANY NAME</th>
                    <th>REFERENCE NUMBER</th>
                    <th>ARCHIVE DATE</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                $sql = "SELECT archive_id, RefNum, Company_name, archive_date FROM tbl_archive";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["archive_id"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Company_name"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["RefNum"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["archive_date"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No records found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
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
</body>

</html>
<?php $conn->close(); ?>