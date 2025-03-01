<?php
require 'db_agq.php';

session_start();

/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $name = isset($_POST['Name']) ? htmlspecialchars(trim($_POST['Name'])) : '';
    $department = isset($_POST['Department']) ? htmlspecialchars(trim($_POST['Department'])) : '';


    if (!empty($name) && !empty($department)) {
       
        $_SESSION['Name'] = $name;
        $_SESSION['Department'] = $department;

       
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Please provide all required information.";
    }
} else {
    
    $name = $_SESSION['Name'] ?? '';
    $department = $_SESSION['Department'] ?? '';

    
    if (!empty($name) && !empty($department)) {
        switch ($department) {
            case "admin":
                header("Location: ownerdash.php");
                exit();

            case "Import Forwarding":
            case "Import Brokerage":
            case "Export Forwarding":
            case "Export Brokerage":
                header("Location: employdash.php");
                exit();

            default:
                echo "Unauthorized Account."; // Commented out for testing purposes
                break;
        }
    } else {
        echo "No session data found. Please log in.";
    }
}
*/
/*
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
*/

/*if (!isset($_SESSION['department'])) {
    header("Location: agq_login.php");
    exit();
}
*/


if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset();
    session_destroy();
    header("Location: agq_login.php");
    exit();
}

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT Company_name, Company_picture FROM tbl_company WHERE Company_name LIKE ?");
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("s", $like_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {

    $companies = "SELECT Company_name, Company_picture FROM tbl_company";
    $result = $conn->query($companies);
}

?>


<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- provide viewport -->
    <meta charset="utf-8">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <title> AGQ Unnamed System </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/x-icon" href="/AGQ/images/favicon.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/employDash.css">

</head>
<link rel="icon" href="images/agq_logo.png" type="image/ico">

<body>
    <div class="header-container">
        <div class="search-container">
            <input type="text" class="search-bar" id="search-input" placeholder="Search Companies..." autocomplete="off">
            <div id="dropdown" class="dropdown" style="display: none;"></div>
            <button class="search-button" id="search-button"> SEARCH </button>
        </div>
        <div class="nav-link-container">
            <a href="?logout=true">Logout</a>
        </div>
    </div>
    </div>


    <div class=" dashboard-body">
        <div class="company-head">
            <div class="company-title">
                COMPANIES
            </div>
        </div>
        <div class="company-container-row" id="company-container-row">
            <?php
            $companies = "SELECT Company_name, Company_picture FROM tbl_company";
            $result = $conn->query($companies);

            if ($result->num_rows > 0) {
                $index = 0;

                while ($row = $result->fetch_assoc()) {
                    $varName = 'Company' . $index;
                    $$varName = $row['Company_name'];

                    $company_name = $$varName;
                    $company_picture = $row['Company_picture'];

                    $company_picture_base64 = base64_encode($company_picture);
                    $company_picture_src = 'data:image/jpeg;base64,' . $company_picture_base64;


                    if ($index > 0 && $index % 5 === 0) {
                        echo '</div><div class="company-container-row">';
                    }

                    echo '<div class="company-button">';
                    echo '<button class="company-container" onclick="window.location.href=\'login.php\'">';
                    echo '<img class="company-logo" src="' . $company_picture_src . '" alt="' . $company_name . '">';
                    echo '</button>';
                    echo '</div>';

                    $index++;
                }

                echo '</div>';
            } else {
                echo "No companies found in the database.";
            }
            ?>


</body>
<script>
    document.getElementById("search-input").addEventListener("input", function() {
        let query = this.value.trim();

        if (query.length === 0) {
            document.getElementById("dropdown").style.display = "none";
            return;
        }

        fetch("FETCH_RESULTS.php?query=" + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                console.log("API Response:", data);
                let dropdown = document.getElementById("dropdown");
                dropdown.innerHTML = "";

                if (data.length > 0 && !data.error) {
                    data.forEach(item => {
                        let div = document.createElement("div");
                        div.classList.add("dropdown-item");
                        div.textContent = item.Company_name;
                        div.onclick = function() {
                            document.getElementById("search-input").value = item.Company_name;
                            dropdown.style.display = "none";
                        };
                        dropdown.appendChild(div);
                    });

                    dropdown.style.display = "block";
                } else {
                    dropdown.style.display = "none";
                }
            })
            .catch(error => console.error("Error fetching search results:", error));

    });

    document.addEventListener("DOMContentLoaded", function() {
        let searchInput = document.getElementById("search-input");
        let searchButton = document.getElementById("search-button");
        let container = document.getElementById("company-container-row");
        let dropdown = document.getElementById("dropdown");

        if (!searchInput || !searchButton || !dropdown) {
            console.error("Error: One or more elements not found.");
            return;
        }


        searchInput.addEventListener("input", function() {
            let query = searchInput.value.trim();

            if (query.length === 0) {
                dropdown.style.display = "none";
                return;
            }

            fetch("FETCH_RESULTS.php?query=" + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);
                    dropdown.innerHTML = "";

                    if (data.length > 0 && !data.error) {
                        data.forEach(item => {
                            let div = document.createElement("div");
                            div.classList.add("dropdown-item");
                            div.textContent = item.Company_name;
                            div.onclick = function() {
                                searchInput.value = item.Company_name;
                                dropdown.style.display = "none";
                            };
                            dropdown.appendChild(div);
                        });

                        dropdown.style.display = "block";
                    } else {
                        dropdown.style.display = "none";
                    }
                })
                .catch(error => console.error("Error fetching search results:", error));
        });

        // Hide dropdown when clicking outside
        document.addEventListener("click", function(event) {
            if (!searchInput.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });

        // Perform search when search button is clicked
        searchButton.addEventListener("click", function() {
            let query = searchInput.value.trim();

            if (!container) {
                console.error("Error: Element with ID 'company-container-row' not found.");
                return;
            }

            if (query === "") {
                alert("Please enter a search term.");
                return;
            }


            fetch("FILTER_RESULTS.php?query=" + encodeURIComponent(query))

                .then(response => response.json())
                .then(data => {
                    let container = document.getElementById("company-container-row");
                    container.innerHTML = "";

                    if (!data.company || data.company.length === 0) {
                        container.innerHTML = "<p>No Companies found.</p>";
                        return;
                    }

                    data.company.forEach(company => {
                        let companyDiv = document.createElement("div");
                        companyDiv.classList.add("company-container-row");

                        companyDiv.innerHTML = `
                        <div class="company-button">
                            <button class="company-container" onclick="window.location.href='login.php'">
                                <img class="company-logo" src="data:image/jpeg;base64,${company.Company_picture}" alt="${company.Company_name}">
                            </button>
                        </div>
                    `;

                        container.appendChild(companyDiv);
                    });
                })
                .catch(error => console.error("Error fetching companies:", error));
        });
    });
</script>

</html>