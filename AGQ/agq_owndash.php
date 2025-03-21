<?php
require 'db_agq.php';
session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
/*
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
*/

if (!isset($_SESSION['department'])) {
    header("Location: agq_login.php");
    session_destroy();
    exit();
} elseif ($role == 'Export Brokerage' || $role == 'Export Forwarding' || $role == 'Import Brokerage' || $role == 'Import Forwarding') {
    header("Location: agq_dashCatcher.php");
    session_destroy();
    exit();
}

if (isset($_SESSION['selected_company'])) {
    $companyName = $_SESSION['selected_company'];
}

header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");



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
<link rel="icon" href="images/agq_logo.png" type="image/ico">

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
    <link rel="stylesheet" type="text/css" href="../css/owndash.css">
    <style>
        .hamburger-menu {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s ease;
        }

        .hamburger-menu span {
            display: block;
            height: 3px;
            width: 100%;
            background-color: #94ae5e;
            border-radius: 3px;
            transition: all 0.3s ease;
            transform-origin: center;
        }

        /* Hamburger to X transformation */
        .hamburger-menu.active {
            position: fixed;
            right: 20px;
        }

        .hamburger-menu.active span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .hamburger-menu.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger-menu.active span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background-color: white;
            z-index: 1000;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            padding: 80px 20px 20px;
            box-sizing: border-box;
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-search-container {
            margin-bottom: 25px;
            width: 100%;
        }

        .mobile-search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .mobile-search-icon {
            position: absolute;
            right: 15px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }

        #mobile-search-input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid rgb(204, 204, 204);
            border-radius: 50px;
            font-size: 16px;
            background-color: rgb(209, 209, 209);
            font-weight: bold;
            height: 44px;
            box-sizing: border-box;
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .mobile-nav-links a {
            text-decoration: none;
            color: #94ae5e;
            font-weight: bold;
            font-size: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Adjust existing media queries */
        @media screen and (max-width: 1024px) {
            .dashboard-body {
                margin: 30px 40px 10px 40px;
            }

            #company-container-parent {
                padding-left: 20px;
                padding-right: 20px;
            }

            .company-container-row {
                gap: 50px;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media screen and (max-width: 768px) {
            .hamburger-menu {
                display: flex;
                position: absolute;
                top: 40px;
                right: 20px;
            }

            .mobile-menu {
                display: block;
            }

            .search-container,
            .nav-link-container {
                display: none;
            }

            .header-container {
                justify-content: center;
                margin-top: 60px;
                padding-bottom: 20px;
            }

            .company-button {
                flex: 0 0 calc(33.333% - 34px);
                max-width: 180px;
                margin: 0;
            }

            .company-logo {
                width: 150px;
                height: 150px;
            }

            .company-container-row {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 50px;
                width: 100%;
            }
        }

        @media screen and (max-width: 480px) {
            .dept-label {
                width: 100%;
                border-radius: 0;
                position: relative;
                text-align: center;
                min-width: unset;
            }

            .hamburger-menu {
                top: 40px;
                right: 20px;
            }

            .company-button {
                flex: 0 0 calc(50% - 10px);
                max-width: none;
            }

            .company-logo {
                width: 120px;
                height: 120px;
            }

            .company-title {
                font-size: 28px;
            }

            .company-container-row {
                gap: 20px;
                justify-content: center;
            }

            .dashboard-body {
                margin: 20px 10px 10px 10px;
            }

            .company-head {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 20px;
            }

            .add-company {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
            }
        }

        @media screen and (max-width: 320px) {
            .company-button {
                flex: 1 1 100%;
            }

            .company-logo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>

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
                    <a href="agq_members.php">Members</a>
                    <a href="?logout=true">Logout</a>
                </div>

                <!-- Hamburger Menu Button -->
                <div class="hamburger-menu" id="hamburger-button">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="menu-overlay" id="menu-overlay"></div>
    <div class="mobile-menu" id="mobile-menu">
        <!-- Add close icon at the top -->

        <div class="mobile-search-container">
            <div class="mobile-search-input-wrapper">
                <input type="text" class="search-bar" id="mobile-search-input" placeholder="Search Companies..." autocomplete="off">
                <button class="mobile-search-icon" id="mobile-search-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94ae5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </div>
            <div id="mobile-dropdown" class="dropdown" style="display: none;"></div>
        </div>

        <div class="mobile-nav-links">
            <a href="agq_members.php">Members</a>
            <a href="?logout=true">Logout</a>
        </div>
    </div>

    <div class="dashboard-body">
        <div class="company-head">
            <div class="company-title">
                COMPANIES
            </div>
            <div>
                <button class="add-company" onclick="window.location.href='agq_companyForm.php'">
                    <span>NEW COMPANY </span>
                    <div class="icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </button>
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
        </div>
    </div>

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
                    window.location.href = "agq_chooseDepartment.php";
                })
                .catch(error => console.error("Error:", error));
        }

        history.pushState(null, "", location.href);
        window.onpopstate = function() {
            history.pushState(null, "", location.href);
        };

        // Hamburger menu functionality
        document.addEventListener("DOMContentLoaded", function() {
            const hamburgerButton = document.getElementById("hamburger-button");
            const mobileMenu = document.getElementById("mobile-menu");
            const menuOverlay = document.getElementById("menu-overlay");

            hamburgerButton.addEventListener("click", function() {
                mobileMenu.classList.toggle("active");
                hamburgerButton.classList.toggle("active"); // Add this line to toggle active class for hamburger
                menuOverlay.style.display = mobileMenu.classList.contains("active") ? "block" : "none";
            });

            menuOverlay.addEventListener("click", function() {
                mobileMenu.classList.remove("active");
                hamburgerButton.classList.remove("active"); // Add this line to remove active class
                menuOverlay.style.display = "none";
            });


            setupSearchDropdown("search-input", "dropdown", "search-button");
            setupSearchDropdown("mobile-search-input", "mobile-dropdown", "mobile-search-button");
        });

        function setupSearchDropdown(inputId, dropdownId, buttonId) {
            let searchInput = document.getElementById(inputId);
            let searchButton = document.getElementById(buttonId);
            let dropdown = document.getElementById(dropdownId);
            let companyContainerParent = document.getElementById("company-container-parent");

            if (!searchInput || !searchButton || !dropdown || !companyContainerParent) {
                console.error("Error: One or more elements not found for " + inputId);
                return;
            }

            // Fetch and display companies
            function fetchCompanies(url) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        companyContainerParent.innerHTML = "";
                        if (!data.company || data.company.length === 0) {
                            companyContainerParent.innerHTML = "<p>No Companies found.</p>";
                            return;
                        }
                        displayCompanies(data.company);
                    })
                    .catch(error => console.error("Error fetching companies:", error));
            }

            // Display companies in grid format
            function displayCompanies(companies) {
                let companyRowDiv = document.createElement("div");
                companyRowDiv.classList.add("company-container-row");

                companies.forEach((company, index) => {
                    let companyButtonDiv = document.createElement("div");
                    companyButtonDiv.classList.add("company-button");

                    let companyButton = document.createElement("button");
                    companyButton.classList.add("company-container");
                    companyButton.onclick = () => storeCompanySession(company.Company_name);

                    let companyLogo = document.createElement("img");
                    companyLogo.classList.add("company-logo");
                    companyLogo.src = `data:image/jpeg;base64,${company.Company_picture}`;
                    companyLogo.alt = company.Company_name;

                    companyButton.appendChild(companyLogo);
                    companyButtonDiv.appendChild(companyButton);
                    companyRowDiv.appendChild(companyButtonDiv);

                    if ((index + 1) % 5 === 0) {
                        companyContainerParent.appendChild(companyRowDiv);
                        companyRowDiv = document.createElement("div");
                        companyRowDiv.classList.add("company-container-row");
                    }
                });

                if (companyRowDiv.children.length > 0) {
                    companyContainerParent.appendChild(companyRowDiv);
                }
            }

            // Handle dropdown search
            searchInput.addEventListener("input", function() {
                let query = this.value.trim();

                if (!query) {
                    dropdown.style.display = "none";
                    return;
                }

                fetch("FETCH_COMPANY.php?query=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        dropdown.innerHTML = "";
                        if (!data || !Array.isArray(data.company)) {
                            console.error("Invalid API response", data);
                            return;
                        }

                        if (data.company.length > 0) {
                            data.company.forEach(item => {
                                let div = document.createElement("div");
                                div.classList.add("dropdown-item");
                                div.textContent = item.Company_name;
                                div.onclick = () => {
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
            document.addEventListener("click", event => {
                if (!searchInput.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.style.display = "none";
                }
            });

            // Search button click handler
            searchButton.addEventListener("click", () => {
                let query = searchInput.value.trim();
                let url = query ? `FILTER_COMPANY.php?query=${encodeURIComponent(query)}` : "FILTER_COMPANY.php";
                fetchCompanies(url);
            });
        }
    </script>

</body>

</html>