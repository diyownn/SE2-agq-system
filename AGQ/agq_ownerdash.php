<?php
require 'db.php';

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

// Check if logout is requested
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Unset all session variables
    session_unset();

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php");
    exit();
}
/*
$sql = "SELECT TransactionID FROM trans_test WHERE TransactionID LIKE '%$query%' OR description LIKE '%$query%'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $results[] = 'Transaction: ' . $row['TransactionID'];
}
*/
?>


<html>

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
    <link rel="stylesheet" type="text/css" href="dashboard.css">
</head>

<body>
    <div class="header-container">
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Search..." oninput="fetchResults()" autocomplete="off">
            <div id="dropdown" class="dropdown" style="display: none;"></div>
            <button class="search-button"> SEARCH </button>
        </div>
    </div>


    <div class="dashboard-body">
        <div class="company-head">
            <div class="company-title">
                COMPANIES
            </div>
            <div>
                <button class="add-company">
                    NEW COMPANY
                    <img class="add-symbol" src="company-logos/plus-sign.png">
                </button>

            </div>
        </div>
 
            <?php
            $companies = "SELECT Company_name, Company_picture FROM company";
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

                echo '</div>'; // Close the last row
            } else {
                echo "No companies found in the database.";
            }
            ?>


</body>
<script>
    function fetchResults() {
        const query = document.getElementById('searchBar').value;
        if (companies.length < 2) {
            document.getElementById('dropdown').style.display = 'none';
            return;
        }

        fetch(`search.php?q=${encodeURIComponent(companies)}`)
            .then(response => response.json())
            .then(data => {
                const dropdown = document.getElementById('dropdown');
                dropdown.innerHTML = '';

                if (data.length > 0) {
                    dropdown.style.display = 'block';
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item';
                        div.textContent = item;
                        dropdown.appendChild(div);
                    });
                } else {
                    dropdown.style.display = 'none';
                }
            });
    }
</script>

</html