<?php
require 'db_agq.php';
session_start();

/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $name = isset($_POST['Name']) ? htmlspecialchars(trim($_POST['Name'])) : '';
    $department = isset($_POST['Department']) ? htmlspecialchars(trim($_POST['Department'])) : '';

    
    if (!empty($name) && !empty($department)) {
        // Store data in session
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
                echo "Unauthorized Account."; // Work in progress
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
?>


<html>
<link rel="icon" href="images/agq_logo.png" type="image/ico">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- provide viewport -->
    <meta charset="utf-8">
    <meta name="keywords" content=""> <!-- provide keywords -->
    <meta name="description" content=""> <!-- provide description -->
    <title> AGQ Unnamed System </title> <!-- provide title -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/x-icon" href="/AGQ/images/favicon.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/owndash.css">
</head>

<body>
    <div class="header-container">
        <div class="search-container">
            <input type="text" class="search-bar" id="search-input" placeholder="Search transactions...">
            <div id="dropdown" class="dropdown" style="display: none;"></div>
            <button class="search-button" onclick="window.location.href='agq_searchresults.php'""> SEARCH </button>
      </div>
      <div class =" nav-link-container">
                <a href="agq_members.php">Members</a>
                <a href="?logout=true">Logout</a>
        </div>
    </div>


    <div class=" dashboard-body">
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

        <?php
        $companies = "SELECT Company_name, Company_picture FROM tbl_company";
        $result = $conn->query($companies);

        if ($result->num_rows > 0) {
            $index = 0;
            echo '<div class="company-container-row">';

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


    let searchInput = document.getElementById("search-input");
    let dropdown = document.getElementById("dropdown");


    if (!searchInput.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = "none";
    }

    document.addEventListener("click", function(event) {
        if (!searchInput.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });


    function redirectToSearchResults() {
        const query = document.querySelector('.search-bar').value.trim();
        if (query.length > 0) {
            window.location.href = `agq_searchresults.php?q=${encodeURIComponent(query)}`;
        }
    }

    document.querySelector('.search-button').addEventListener('click', () => redirectToSearchResults());
</script>

</html>