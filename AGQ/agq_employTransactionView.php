<?php
require 'db_agq.php';
session_start();

$docType = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : '';
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

if (!$company) {

    echo "DID NOT GET COMPANY";
}
/*
if (!isset($_SESSION['department'])) {
    header("Location: agq_login.php");
    session_destroy();
    exit();
} elseif ($role == 'Export Brokerage' || $role == 'Export Forwarding' || $role == 'Import Brokerage' || $role == 'Import Forwarding') {
    header("Location: agq_dashCatcher.php");
    session_destroy();
    exit();
}

if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset();
    session_destroy();
    header("Location: agq_login.php");
    exit();
}
*/
$query = "
SELECT i.RefNum, i.DocType, c.Company_name
FROM tbl_impfwd i
JOIN tbl_company c ON i.Company_name = c.Company_name
WHERE '$role' = 'Import Forwarding' AND c.Company_name = '$company'
UNION ALL
SELECT b.RefNum, b.DocType, c.Company_name
FROM tbl_impbrk b
JOIN tbl_company c ON b.Company_name = c.Company_name
WHERE '$role' = 'Import Brokerage' AND c.Company_name = '$company'
UNION ALL
SELECT f.RefNum, f.DocType, c.Company_name
FROM tbl_expfwd f
JOIN tbl_company c ON f.Company_name = c.Company_name
WHERE '$role' = 'Export Forwarding' AND c.Company_name = '$company'
UNION ALL
SELECT e.RefNum, e.DocType, c.Company_name
FROM tbl_expbrk e
JOIN tbl_company c ON e.Company_name= c.Company_name
WHERE '$role' = 'Export Brokerage' AND c.Company_name = '$company'
";
$result = $conn->query($query);

$transactions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transactions[strtoupper($row['DocType'])][] = $row['RefNum'];
    }
}
/*
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
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/otp.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="container py-3">
        <div class="search-container d-flex flex-wrap justify-content-center">
            <input type="text" class="search-bar form-control" placeholder="">
            <button class="search-button">SEARCH</button>
        </div>
        <div>
            <button class="add-company" onclick="window.location.href='agq_choosedocument.php'">
                <span> CREATE </span>
                <div class="icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </button>
        </div>
        <div class="transactions mt-4">
            <div class="transaction">
                <div class="transaction-header">STATEMENT OF ACCOUNT <span class="icon">&#x25BC;</span></div>
                <div class="transaction-content">
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-26</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0009/01-28</span>
                        <input type="checkbox">
                    </div>
                </div>
            </div>
            <div class="transaction">
                <div class="transaction-header">INVOICE <span class="icon">&#x25BC;</span></div>
                <div class="transaction-content">
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/02-21</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                </div>
            </div>
            <div class="transaction">
                <div class="transaction-header">SUMMARY <span class="icon">&#x25BC;</span></div>
                <div class="transaction-content">
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                </div>
            </div>
            <div class="transaction">
                <div class="transaction-header">OTHERS <span class="icon">&#x25BC;</span></div>
                <div class="transaction-content">
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                    <div class="transaction-item d-flex justify-content-between">
                        <span>IB0007/01-25</span>
                        <input type="checkbox">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const headers = document.querySelectorAll(".transaction-header");

            headers.forEach(header => {
                header.addEventListener("click", function() {
                    const content = this.nextElementSibling;
                    const icon = this.querySelector(".icon");

                    if (content.classList.contains("open")) {
                        content.classList.remove("open");
                        this.classList.remove("active");
                    } else {
                        content.classList.add("open");
                        this.classList.add("active");
                    }
                });
            });
        });

        var doctype = "<?php echo isset($_SESSION['selected_documenttype']) ? $_SESSION['selected_documenttype'] : ''; ?>"
        var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        var company = "<?php echo isset($_SESSION['selected_company']) ? $_SESSION['selected_company'] : ''; ?>";

        console.log("DocType:", doctype);
        console.log("Role:", role);
        console.log("Company:", company);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>