<?php
require 'db_agq.php';
session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';
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
WHERE '$dept' = 'Import Forwarding' AND c.Company_name = '$company'
UNION ALL
SELECT b.RefNum, b.DocType, c.Company_name
FROM tbl_impbrk b
JOIN tbl_company c ON b.Company_name = c.Company_name
WHERE '$dept' = 'Import Brokerage' AND c.Company_name = '$company'
UNION ALL
SELECT f.RefNum, f.DocType, c.Company_name
FROM tbl_expfwd f
JOIN tbl_company c ON f.Company_name = c.Company_name
WHERE '$dept' = 'Export Forwarding' AND c.Company_name = '$company'
UNION ALL
SELECT e.RefNum, e.DocType, c.Company_name
FROM tbl_expbrk e
JOIN tbl_company c ON e.Company_name= c.Company_name
WHERE '$dept' = 'Export Brokerage' AND c.Company_name = '$company'
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
    <script>
        function redirectToDocument(refNum) {
            window.location.href = 'agq_ifsoaNewDocument.php';
        }
    </script>
</head>

<body>
    <div class="container py-3">
        <div class="search-container d-flex flex-wrap justify-content-center">
            <input type="text" class="search-bar form-control" placeholder="">
            <button class="search-button">SEARCH</button>
        </div>

        <div class="transactions mt-4">
            <?php
            $docTypes = ['STATEMENT OF ACCOUNT', 'INVOICE', 'SUMMARY', 'OTHERS'];
            foreach ($docTypes as $docType): ?>
                <div class="transaction">
                    <div class="transaction-header"><?php echo $docType; ?> <span class="icon">&#x25BC;</span></div>
                    <div class="transaction-content">
                        <?php if (isset($transactions[$docType])): ?>
                            <?php foreach ($transactions[$docType] as $refNum): ?>
                                <div class="transaction-item d-flex justify-content-between"
                                    ondblclick="redirectToDocument('<?php echo htmlspecialchars($refNum); ?>')">
                                    <span><?php echo htmlspecialchars($refNum); ?></span>
                                    <input type="checkbox">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No records found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const headers = document.querySelectorAll(".transaction-header");
            headers.forEach(header => {
                header.addEventListener("click", function() {
                    const content = this.nextElementSibling;
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
        /*
                function filterTransactions() {
                    const searchValue = document.querySelector(".search-bar").value.toLowerCase();
                    const items = document.querySelectorAll(".transaction-item");

                    items.forEach(item => {
                        const refNum = item.getAttribute("data-refnum").toLowerCase();
                        const docType = item.getAttribute("data-doctype").toLowerCase();
                        const company = item.getAttribute("data-company").toLowerCase();

                        if (refNum.includes(searchValue) || docType.includes(searchValue) || company.includes(searchValue)) {
                            item.style.display = "flex";
                        } else {
                            item.style.display = "none";
                        }
                    });
                }
        */

        var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        var company = "<?php echo isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : ''; ?>";
        var selectdep ="<?php echo isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';?>"

       
        console.log("Role:", role);
        console.log("Company:", company);
        console.log("Selected Department:", selectdep);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>