<?php
require 'db_agq.php';

session_start();


$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

if (!$role) {
    echo "<html><head><style>
    body { font-family: Arial, sans-serif; text-align: center; background-color: #f8d7da; }
    .container { margin-top: 50px; padding: 20px; background: white; border-radius: 10px; display: inline-block; }
    h1 { color: #721c24; }
    p { color: #721c24; }
  </style></head><body>
  <div class='container'>
    <h1>Unauthorized Access</h1>
    <p>You do not have permission to view this page. (ERR: R)</p>
  </div>
  </body></html>";
    exit;
}


if (!isset($_SESSION['department'])) {
    session_destroy();
    header("Location: agq_login.php");
    exit();
} elseif ($role == 'admin' || $role == 'Admin' || $role == 'owner' || $role == 'Owner') {
    header("Location: agq_dashCatcher.php");
    exit();
}


if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset();
    session_destroy();
    header("Location: agq_login.php");
    exit();
}

header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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
    <title> Dashboard | AGQ </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/x-icon" href="/AGQ/images/favicon.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/employdash.css">

</head>
<link rel="icon" href="images/agq_logo.png" type="image/ico">

<body>
    <div class="top-container">
        <div class="dept-container">
            <div class="dept-label">
                <?php echo htmlspecialchars($role); ?>
            </div>

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
    </div>


    <div class=" dashboard-body">
        <div class="company-head">
            <div class="company-title">
                COMPANIES
            </div>
        </div>
        <div id="company-container-parent">
            <div class="company-container-row">
                <?php
                $companies = "SELECT Company_name, Company_picture FROM tbl_company";
                $result = $conn->query($companies);

                if ($result->num_rows > 0) {
                    $index = 0;
                    while ($row = $result->fetch_assoc()) {
                        $varName = 'Company' . $index;
                        $varName = $row['Company_name'];

                        $company_name = $row['Company_name'];
                        $company_picture = $row['Company_picture'];

                        $company_picture_base64 = base64_encode($company_picture);
                        $company_picture_src = 'data:image/jpeg;base64,' . $company_picture_base64;


                        if ($index > 0 && $index % 5 === 0) {
                            echo '</div><div class="company-container-row">';
                        }

                        echo '<div class="company-button">';
                        echo '<button class="company-container" onclick="storeCompanySession(\'' . htmlspecialchars($company_name, ENT_QUOTES) . '\')">';
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
            </div>

</body>
<script>
    function storeCompanySession(companyName) {
        fetch('STORE_SESSION.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'company_name=' + encodeURIComponent(companyName)
            })
            .then(response => response.text())
            .then(data => {
                console.log("Session stored:", data);
                window.location.href = "agq_employTransactionView.php";
            })
            .catch(error => console.error("Error:", error));
    }

    history.pushState(null, "", location.href);
    window.onpopstate = function() {
        history.pushState(null, "", location.href);
    };
    document.getElementById("search-input").addEventListener("input", function() {
        let query = this.value.trim();

        if (query.length === 0) {
            document.getElementById("dropdown").style.display = "none";
            return;
        }

        fetch("FETCH_COMPANY.php?query=" + encodeURIComponent(query))
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
            }).catch(error => console.error("Error fetching search results:", error));

    });

    document.addEventListener("DOMContentLoaded", function() {
        let searchInput = document.getElementById("search-input");
        let searchButton = document.getElementById("search-button");
        let dropdown = document.getElementById("dropdown");
        let companyContainerParent = document.getElementById("company-container-parent"); // New parent element for container rows

        if (!searchInput || !searchButton || !dropdown || !companyContainerParent) {
            console.error("Error: One or more elements not found.");
            return;
        }

        searchInput.addEventListener("input", function() {
            let query = this.value.trim();

            if (query.length === 0) {
                dropdown.style.display = "none";
                return;
            }

            fetch("FETCH_COMPANY.php?query=" + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);
                    dropdown.innerHTML = "";


                    if (!data || !Array.isArray(data.company)) {
                        console.error("Error: API response does not contain a valid 'company' array!", data);
                        return;
                    }

                    if (data.company.length > 0) {
                        data.company.forEach(item => {
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
                }).catch(error => console.error("Error fetching search results:", error));
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

            if (!companyContainerParent) {
                console.error("Error: Parent container for 'company-container-row' not found.");
                return;
            }

            if (query === "") {
                fetch("FETCH_COMPANY.php")
                    .then(response => response.json())
                    .then(data => {
                        companyContainerParent.innerHTML = ""; // Clear previous content

                        if (!data.company || data.company.length === 0) {
                            companyContainerParent.innerHTML = "<p>No Companies found.</p>";
                            return;
                        }

                        let companyRowDiv = document.createElement("div");
                        companyRowDiv.classList.add("company-container-row");

                        data.company.forEach((company, index) => {
                            // Create the company button container
                            let companyButtonDiv = document.createElement("div");
                            companyButtonDiv.classList.add("company-button");

                            // Create the company button
                            let companyButton = document.createElement("button");
                            companyButton.classList.add("company-container");
                            companyButton.onclick = function() {
                                storeCompanySession(''.htmlspecialchars($company_name, ENT_QUOTES));
                            };

                            // Create the company logo
                            let companyLogo = document.createElement("img");
                            companyLogo.classList.add("company-logo");
                            companyLogo.src = `data:image/jpeg;base64,${company.Company_picture}`;
                            companyLogo.alt = company.Company_name;

                            // Append the image to the button, and button to the container
                            companyButton.appendChild(companyLogo);
                            companyButtonDiv.appendChild(companyButton);
                            companyRowDiv.appendChild(companyButtonDiv);

                            // Every 5th item, start a new row
                            if ((index + 1) % 5 === 0) {
                                companyContainerParent.appendChild(companyRowDiv);
                                companyRowDiv = document.createElement("div");
                                companyRowDiv.classList.add("company-container-row");
                            }
                        });

                        // Append any remaining companies
                        if (companyRowDiv.children.length > 0) {
                            companyContainerParent.appendChild(companyRowDiv);
                        }
                    }).catch(error => console.error("Error fetching companies:", error));

                return;
            }

            fetch("FILTER_COMPANY.php?query=" + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    companyContainerParent.innerHTML = ""; // Clear previous content

                    if (!data.company || data.company.length === 0) {
                        companyContainerParent.innerHTML = "<p>No Companies found.</p>";
                        return;
                    }

                    let companyRowDiv = document.createElement("div");
                    companyRowDiv.classList.add("company-container-row");

                    data.company.forEach((company, index) => {
                        // Create the company button container
                        let companyButtonDiv = document.createElement("div");
                        companyButtonDiv.classList.add("company-button");

                        // Create the company button
                        let companyButton = document.createElement("button");
                        companyButton.classList.add("company-container");
                        companyButton.onclick = function() {
                            storeCompanySession(''.htmlspecialchars($company_name, ENT_QUOTES));
                        };

                        // Create the company logo
                        let companyLogo = document.createElement("img");
                        companyLogo.classList.add("company-logo");
                        companyLogo.src = `data:image/jpeg;base64,${company.Company_picture}`;
                        companyLogo.alt = company.Company_name;

                        // Append the image to the button, and button to the container
                        companyButton.appendChild(companyLogo);
                        companyButtonDiv.appendChild(companyButton);
                        companyRowDiv.appendChild(companyButtonDiv);

                        // Every 5th item, start a new row
                        if ((index + 1) % 5 === 0) {
                            companyContainerParent.appendChild(companyRowDiv);
                            companyRowDiv = document.createElement("div");
                            companyRowDiv.classList.add("company-container-row");
                        }
                    });

                    // Append any remaining companies
                    if (companyRowDiv.children.length > 0) {
                        companyContainerParent.appendChild(companyRowDiv);
                    }
                }).catch(error => console.error("Error fetching companies:", error));
        })
    });
</script>

</html