<?php
require 'db_agq.php';
session_start();


$url = isset($_GET['url']);
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';


if (!$url) {
    header("Location: UNAUTHORIZED.php?error=401u");
}


if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}


if (!isset($_SESSION['department'])) {
    header("Location: UNAUTHORIZED.php?error=401r");
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
<link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">


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
</head>
<body style="background-image: url('obg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
    <div id="loader-container">
        <iframe id="loader-frame" src="LOADER.html"></iframe>
    </div>
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
                    <a href="agq_archive.php">Archive</a>
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
            <a href="agq_archive.php">Archive</a>
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
                    echo '<div class="company-container-row">';
                    while ($row = $result->fetch_assoc()) {
                        $company_name = $row['Company_name'];
                        $company_picture = $row['Company_picture'];


                        $company_picture_base64 = base64_encode($company_picture);
                        $company_picture_src = 'data:image/jpeg;base64,' . $company_picture_base64;


                        echo '<div class="company-button">';
                        echo '<button class="company-container" onclick="storeCompanySession(\'' . htmlspecialchars($company_name, ENT_QUOTES) . '\')">';
                        echo '<img class="company-logo" src="' . $company_picture_src . '" alt="' . $company_name . '">';
                        echo '</button>';
                        echo '</div>';
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
       window.addEventListener("load", function() {
        // Initially hide the main content
        document.body.classList.add('loading');
    
    setTimeout(() => {
        document.getElementById("loader-container").style.display = "none";
        // Show the main content after loader disappears
        document.body.classList.remove('loading');
    }, 1500); // Waits 1.5 seconds before hiding the loader
});

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

// Function to clear search and reload the original companies
function clearSearch() {
    // Clear search input
    document.getElementById("search-input").value = "";
    document.getElementById("mobile-search-input").value = "";
    
    // Reload the original company list without redirecting
    fetchCompanies("FILTER_COMPANY.php"); // Fetch all companies (no query parameter)
}

// Hamburger menu functionality
document.addEventListener("DOMContentLoaded", function() {
    const hamburgerButton = document.getElementById("hamburger-button");
    const mobileMenu = document.getElementById("mobile-menu");
    const menuOverlay = document.getElementById("menu-overlay");

    hamburgerButton.addEventListener("click", function() {
        mobileMenu.classList.toggle("active");
        hamburgerButton.classList.toggle("active");
        menuOverlay.style.display = mobileMenu.classList.contains("active") ? "block" : "none";
    });

    menuOverlay.addEventListener("click", function() {
        mobileMenu.classList.remove("active");
        hamburgerButton.classList.remove("active");
        menuOverlay.style.display = "none";
    });

    setupSearchDropdown("search-input", "dropdown", "search-button");
    setupSearchDropdown("mobile-search-input", "mobile-dropdown", "mobile-search-button");
});

// Global function to fetch companies
function fetchCompanies(url) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            let companyContainerParent = document.getElementById("company-container-parent");
            companyContainerParent.innerHTML = "";
            if (!data.company || data.company.length === 0) {
                companyContainerParent.innerHTML = "<p>No Companies found.</p>";
                return;
            }
            displayCompanies(data.company);
        })
        .catch(error => console.error("Error fetching companies:", error));
}

// Global function to display companies
function displayCompanies(companies) {
    let companyContainerParent = document.getElementById("company-container-parent");
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

function setupSearchDropdown(inputId, dropdownId, buttonId) {
    let searchInput = document.getElementById(inputId);
    let searchButton = document.getElementById(buttonId);
    let dropdown = document.getElementById(dropdownId);
    
    if (!searchInput || !searchButton || !dropdown) {
        console.error("Error: One or more elements not found for " + inputId);
        return;
    }

    // Variable to track previous search value
    let previousSearchValue = "";

    // Handle dropdown search
    searchInput.addEventListener("input", function() {
        let currentValue = this.value.trim();
        
        // If value was something before and now it's empty, reload original companies
        if (previousSearchValue !== "" && currentValue === "") {
            clearSearch();
            return;
        }
        
        previousSearchValue = currentValue;

        if (!currentValue) {
            dropdown.style.display = "none";
            return;
        }

        fetch("FETCH_COMPANY.php?query=" + encodeURIComponent(currentValue))
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
        if (!query) {
            // If search is empty, reload original companies
            clearSearch();
            return;
        }
        
        let url = `FILTER_COMPANY.php?query=${encodeURIComponent(query)}`;
        fetchCompanies(url);
    });

    searchInput.addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            searchButton.click();
        }
    });
}
    </script>


</body>


</html>