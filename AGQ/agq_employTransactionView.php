<?php
require 'db_agq.php';
session_start();

$docType = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : '';
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

if (!$role) {
    echo "<html><head><style>
    body { font-family: Arial, sans-serif; text-align: center; background-color: #f8d7da; }
    .container { margin-top: 50px; padding: 20px; background: white; border-radius: 10px; display: inline-block; }
    h1 { color: #721c24; }
    p { color: #721c24; }
  </style></head><body>
  <div class='container'>
    <h1>Unauthorized Access</h1>
    <p>You do not have permission to view this page.</p>
  </div>
  </body></html>";
    exit;
}

if (!$company) {
    echo "<html><head><style>
    body { font-family: Arial, sans-serif; text-align: center; background-color: #f8d7da; }
    .container { margin-top: 50px; padding: 20px; background: white; border-radius: 10px; display: inline-block; }
    h1 { color: #721c24; }
    p { color: #721c24; }
  </style></head><body>
  <div class='container'>
    <h1>Unauthorized Access</h1>
    <p>You do not have permission to view this page.</p>
  </div>
  </body></html>";
    exit;
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
UNION 
SELECT b.RefNum, b.DocType, c.Company_name
FROM tbl_impbrk b
JOIN tbl_company c ON b.Company_name = c.Company_name
WHERE '$role' = 'Import Brokerage' AND c.Company_name = '$company'
UNION
SELECT f.RefNum, f.DocType, c.Company_name
FROM tbl_expfwd f
JOIN tbl_company c ON f.Company_name = c.Company_name
WHERE '$role' = 'Export Forwarding' AND c.Company_name = '$company'
UNION
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
            <input type="text" class="search-bar form-control" id="search-input" placeholder="Search Transaction Details...">
            <div id="dropdown" class="dropdown" id="dropdown" style="display: none;"></div>
            <button class="search-button" id="search-button">SEARCH</button>
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

            <?php
            $docTypes = ['SOA', 'Invoice', 'Summary', 'Others'];
            $labels = ['SOA', 'INVOICE', 'SUMMARY', 'OTHERS'];


            $docTypeLabels = array_combine(array_map('strtoupper', $docTypes), $labels);
            ?>

            <?php foreach ($docTypes as $docType): ?>
                <div class="transaction">

                    <div class="transaction-header"><?php echo $docTypeLabels[strtoupper($docType)]; ?> <span class="icon">&#x25BC;</span></div>
                    <div class="transaction-content">
                        <?php

                        $normalizedDocType = strtoupper(trim($docType));
                        ?>
                        <?php if (!empty($transactions[$normalizedDocType])): ?>
                            <?php foreach ($transactions[$normalizedDocType] as $refNum): ?>
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
            document.body.addEventListener("click", function(event) {
                if (event.target.classList.contains("transaction-header") || event.target.classList.contains("icon")) {
                    const content = event.target.nextElementSibling;
                    content.classList.toggle("open");
                    event.target.classList.toggle("active");
                }
            });
        });


        document.getElementById("search-input").addEventListener("input", function() {
            let query = this.value.trim().toLowerCase();

            if (query.length === 0) {
                document.getElementById("dropdown").style.display = "none";
                return;
            }

            fetch("FETCH_Transactions.php?search=" + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", JSON.stringify(data, null, 2));

                    let dropdown = document.getElementById("dropdown");
                    dropdown.innerHTML = "";

                    // Identify the correct department key dynamically
                    let departmentKey = Object.keys(data).find(key => Array.isArray(data[key]));
                    let transactions = departmentKey ? data[departmentKey] : [];


                    if (transactions.length > 0) {
                        transactions.forEach(item => {
                            let company = (item.Company_name || "").toLowerCase();
                            let refNum = (item.RefNum || "").toLowerCase();
                            let department = (item.Department || "").toLowerCase();
                            let docType = (item.DocType || "").toLowerCase(); // Fix variable naming

                            if (company.includes(query) || refNum.includes(query) || department.includes(query) || docType.includes(query)) {
                                let div = document.createElement("div");
                                div.classList.add("dropdown-item");

                                div.innerHTML = `
                <strong>${item.RefNum || "Unknown Company"}</strong> - ${item.DocType || "No DocType"}
            `;

                                div.onclick = function() {
                                    if (!refNum) { // Fix variable name
                                        document.getElementById("search-input").value = item.DocType || "";
                                    } else {
                                        document.getElementById("search-input").value = item.RefNum || "";
                                    }
                                    document.getElementById("dropdown").style.display = "none";
                                };

                                dropdown.appendChild(div);
                            }
                        });

                        // Show dropdown only if there are matching results
                        dropdown.style.display = dropdown.children.length > 0 ? "block" : "none";
                    } else {
                        dropdown.style.display = "none";
                    }
                })
                .catch(error => {
                    console.error("Error fetching search results:", error);
                    document.getElementById("dropdown").style.display = "none";
                });
        });





        document.addEventListener("DOMContentLoaded", function() {
            let searchInput = document.getElementById("search-input");
            let searchButton = document.getElementById("search-button");
            let dropdown = document.getElementById("dropdown");
            let transactionsContainer = document.querySelector(".transactions"); // Main container

            if (!searchInput || !searchButton || !dropdown || !transactionsContainer) {
                console.error("Error: One or more elements not found.");
                return;
            }

            let debounceTimer;


            function fetchResults(search) {
                if (search.length === 0) {
                    dropdown.style.display = "none";
                    return;
                }

                fetch("FETCH_TRANSACTIONS.php?search=" + encodeURIComponent(search))
                    .then(response => response.json())
                    .then(data => {

                        dropdown.innerHTML = "";

                        let transactions = data.company || [];
                        console.log("API Response:", data);
                        console.log("API Response:", transactions);
                        if (!Array.isArray(transactions)) {
                            console.error("Error: API response does not contain a valid transactions array!", data);
                            return;
                        }

                        if (transactions.length > 0) {
                            transactions.forEach(item => {
                                let company = (item.Company_name || "").toLowerCase();
                                let refNum = (item.RefNum || "").toLowerCase();
                                let department = (item.Department || "").toLowerCase();
                                let docType = (item.DocType || "").toLowerCase();
                                let queryLower = search.toLowerCase();

                                let div = document.createElement("div");
                                div.classList.add("dropdown-item");

                                // Display multiple details instead of just the company name
                                div.innerHTML = `
                                    <strong>${item.Company_name || "Unknown Company"}</strong><br>
                                    <small>RefNum: ${item.RefNum || "N/A"} | Dept: ${item.Department || "N/A"} | DocType: ${item.DocType || "N/A"}</small>
                                `;

                                div.onclick = function() {
                                    // Set the search input value with more details
                                    searchInput.value = `${item.Company_name || ""} - ${item.RefNum || ""} - ${item.Department || ""} - ${item.DocType || ""}`;
                                    dropdown.style.display = "none";
                                }
                            });

                            dropdown.style.display = dropdown.children.length > 0 ? "block" : "none";
                        } else {
                            dropdown.style.display = "none";
                        }
                    })
                    .catch(error => console.error("Error fetching search results:", error));
            }


            searchButton.addEventListener("click", function() {
                let query = searchInput.value.trim();

                if (query === "") {
                    let allTransactions = [];

                    document.addEventListener("DOMContentLoaded", () => {
                        fetch("FETCH_TRANSACTIONS.php?")
                            .then(response => response.json())
                            .then(data => {
                                allTransactions = data;
                                generateTransactions(data);
                            })
                            .catch(error => console.error("Error fetching all transactions:", error));
                    });

                    function searchTransactions(query) {
                        if (query === "") {
                            generateTransactions(allTransactions);
                            return;
                        }

                        fetch(`FETCH_TRANSACTIONS.php?search=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                generateTransactions(data);
                            })
                            .catch(error => console.error("Error fetching transactions:", error));
                    }

                    return;
                }

                fetch("FILTER_TRANSACTIONS.php?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("Full API Response:", data);

                        transactionsContainer.innerHTML = "";

                        if (!data || Object.keys(data).length === 0 || data.error) {
                            transactionsContainer.innerHTML = "<p>No transactions found.</p>";
                            return;
                        }


                        let structuredTransactions = {};

                        Object.entries(data).forEach(([department, docTypes]) => {
                            structuredTransactions[department] = {};

                            Object.entries(docTypes).forEach(([docType, refArray]) => {
                                let normalizedDocType = docType.toUpperCase().trim();

                                if (!structuredTransactions[department][normalizedDocType]) {
                                    structuredTransactions[department][normalizedDocType] = [];
                                }


                                refArray.forEach(item => {
                                    structuredTransactions[department][normalizedDocType].push(item.RefNum);
                                });
                            });
                        });

                        console.log("Structured Transactions:", structuredTransactions);
                        generateTransactionHTML(structuredTransactions, transactionsContainer);
                    })
                    .catch(error => console.error("Error fetching filtered transactions:", error));
            });


            function generateTransactionHTML(transactions, container) {
                container.innerHTML = "";

                Object.entries(transactions).forEach(([department, docTypes]) => {
                    let departmentSection = document.createElement("div");
                    departmentSection.classList.add("department-section");

                    Object.entries(docTypes).forEach(([docType, refs]) => {
                        let transactionSection = document.createElement("div");
                        transactionSection.classList.add("transaction");

                        let transactionHeader = document.createElement("div");
                        transactionHeader.classList.add("transaction-header");
                        transactionHeader.innerHTML = `${docType} <span class="icon">&#x25BC;</span>`;

                        let transactionContent = document.createElement("div");
                        transactionContent.classList.add("transaction-content");

                        if (Array.isArray(refs) && refs.length > 0) {
                            refs.forEach(refNum => {
                                let transactionItem = document.createElement("div");
                                transactionItem.classList.add("transaction-item", "d-flex", "justify-content-between");
                                transactionItem.setAttribute("ondblclick", `redirectToDocument('${refNum}')`);

                                let transactionText = document.createElement("span");
                                transactionText.textContent = refNum;

                                let transactionCheckbox = document.createElement("input");
                                transactionCheckbox.type = "checkbox";

                                transactionItem.appendChild(transactionText);
                                transactionItem.appendChild(transactionCheckbox);
                                transactionContent.appendChild(transactionItem);
                            });
                        } else {
                            transactionContent.innerHTML = "<p>No records found.</p>";
                        }

                        transactionSection.appendChild(transactionHeader);
                        transactionSection.appendChild(transactionContent);
                        departmentSection.appendChild(transactionSection);
                    });

                    container.appendChild(departmentSection);
                });
            }
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