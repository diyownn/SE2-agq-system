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
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <title>Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/otp.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="top-container">
        <div class="dept-container">
            <div class="header-container">
                <div class="dept-label">
                    <?php echo htmlspecialchars($role); ?>
                </div>
                <div class="company-label">
                    <?php echo htmlspecialchars($company); ?>
                </div>
            </div>
        </div>
    </div>

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
            $docTypes = ['SOA', 'Invoice'];
            $labels = ['SOA', 'INVOICE'];


            $docTypeLabels = array_combine(array_map('strtoupper', $docTypes), $labels);
            ?>

            <?php foreach ($docTypes as $docType): ?>
                <div class="transaction">
                    <div class="transaction-header"><?php echo $docTypeLabels[strtoupper($docType)]; ?> <span class="icon">&#x25BC;</span></div>
                    <div class="transaction-content">
                        <?php $normalizedDocType = strtoupper(trim($docType)); ?>
                        <?php if (!empty($transactions[$normalizedDocType])): ?>
                            <?php foreach ($transactions[$normalizedDocType] as $refNum): ?>
                                <div class="transaction-item d-flex justify-content-between"
                                    ondblclick="redirectToDocument('<?php echo htmlspecialchars($refNum); ?>', '<?php echo $normalizedDocType; ?>')">
                                    <span><?php echo htmlspecialchars($refNum); ?> - <?php echo $normalizedDocType; ?></span>
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
        function redirectToDocument(refnum, doctype) {
            if (!refnum || !doctype) {
                return;
            } else {
                window.location.href = "agq_documentCatcher.php?refnum=" + encodeURIComponent(refnum) + '&doctype=' + encodeURIComponent(doctype);;
            }
        }

        document.body.addEventListener("click", function(event) {
            let header = event.target.closest(".transaction-header");
            if (header) {
                const content = header.nextElementSibling;
                if (content) {
                    content.classList.toggle("open");
                    header.classList.toggle("active");
                }
            }
        });



        let searchInput = document.getElementById("search-input");
        if (searchInput) {
            searchInput.addEventListener("input", function() {
                let query = this.value.trim().toLowerCase();
                let dropdown = document.getElementById("dropdown");

                if (query.length === 0) {
                    dropdown.style.display = "none";
                    return;
                }

                fetch("FETCH_Transactions.php?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("API Response:", JSON.stringify(data, null, 2));

                        dropdown.innerHTML = "";

                        // Extract transactions properly (nested inside departments and document types)
                        let transactions = [];
                        Object.values(data).forEach(department => { // Iterate through departments
                            Object.values(department).forEach(docType => { // Iterate through document types
                                transactions = transactions.concat(docType); // Collect all transactions
                            });
                        });

                        console.log("Extracted Transactions:", transactions); // Debugging

                        if (transactions.length > 0) {
                            transactions.forEach(item => {
                                let refNum = (item.RefNum || "").toLowerCase();
                                let docType = (item.DocType || "").toLowerCase();

                                if (refNum.includes(query) || docType.includes(query)) {
                                    let div = document.createElement("div");
                                    div.classList.add("dropdown-item");
                                    div.innerHTML = `<strong>${item.RefNum || "Unknown RefNum"}</strong> - ${item.DocType || "No DocType"}`;
                                    div.onclick = function() {
                                        searchInput.value = item.RefNum || item.DocType || "";
                                        dropdown.style.display = "none";
                                    };
                                    dropdown.appendChild(div);
                                }
                            });

                            dropdown.style.display = dropdown.children.length > 0 ? "block" : "none";
                        } else {
                            dropdown.style.display = "none";
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching search results:", error);
                        dropdown.style.display = "none";
                    });
            });
        }





        document.addEventListener("DOMContentLoaded", function() {
            let searchInput = document.getElementById("search-input");
            let searchButton = document.getElementById("search-button");
            let transactionsContainer = document.querySelector(".transactions"); // Main container

            if (!searchInput || !searchButton || !transactionsContainer) {
                console.error("Error: One or more elements not found.");
                return;
            }

            function fetchAllTransactions() {
                fetch("FETCH_TRANSACTIONS.php")
                    .then(response => response.json())
                    .then(data => {
                        console.log("All Transactions:", data);

                        if (!data || Object.keys(data).length === 0 || data.error) {
                            transactionsContainer.innerHTML = "<p>No transactions found.</p>";
                            return;
                        }

                        let structuredTransactions = {};

                        // Process API response
                        Object.entries(data).forEach(([department, docTypes]) => {
                            if (!structuredTransactions[department]) {
                                structuredTransactions[department] = {};
                            }

                            // Loop through document types (e.g., INVOICE, SOA)
                            Object.entries(docTypes).forEach(([docType, records]) => {
                                if (!Array.isArray(records)) {
                                    console.warn(`Skipping non-array records for ${docType}:`, records);
                                    return;
                                }

                                let normalizedDocType = docType.toUpperCase().trim();

                                if (!structuredTransactions[department][normalizedDocType]) {
                                    structuredTransactions[department][normalizedDocType] = [];
                                }

                                records.forEach(record => {
                                    let refNum = record.RefNum || "No RefNum";
                                    structuredTransactions[department][normalizedDocType].push(refNum);
                                });
                            });
                        });

                        // **Ensure SOA, Summary, and Others exist in all departments**
                        Object.keys(structuredTransactions).forEach(department => {
                            if (!structuredTransactions[department]["SOA"]) {
                                structuredTransactions[department]["SOA"] = [];
                            }
                            if (!structuredTransactions[department]["INVOICE"]) {
                                structuredTransactions[department]["INVOICE"] = [];
                            }

                        });

                        generateTransactionHTML(structuredTransactions, transactionsContainer);
                    })
                    .catch(error => console.error("Error fetching all transactions:", error));
            }

            function fetchFilteredTransactions(query) {
                fetch("FILTER_TRANSACTIONS.php?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("Filtered API Response:", data);

                        transactionsContainer.innerHTML = "";

                        if (!data || Object.keys(data).length === 0 || data.error) {
                            transactionsContainer.innerHTML = "<p>No transactions found.</p>";
                            return;
                        }
                        console.log("All Transactions:", data);

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

                        generateTransactionHTML(structuredTransactions, transactionsContainer);
                    })
                    .catch(error => console.error("Error fetching filtered transactions:", error));
            }

            function generateTransactionHTML(transactions, container) {
                container.innerHTML = "";

                Object.entries(transactions).forEach(([department, docTypes]) => {
                    let departmentSection = document.createElement("div");
                    departmentSection.classList.add("department-section");

                    const order = ["SOA", "INVOICE"];
                    let sortedDocTypes = Object.keys(docTypes).sort((a, b) => {
                        let indexA = order.indexOf(a.toUpperCase());
                        let indexB = order.indexOf(b.toUpperCase());

                        if (indexA === -1) indexA = order.length;
                        if (indexB === -1) indexB = order.length;

                        return indexA - indexB;
                    });

                    sortedDocTypes.forEach(docType => {
                        let refs = docTypes[docType];

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
                                transactionItem.setAttribute("ondblclick", `redirectToDocument('${refNum}', '${docType}')`);

                                let transactionText = document.createElement("span");
                                transactionText.textContent = `${refNum} - ${docType}`;

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

            searchButton.addEventListener("click", function() {
                let query = searchInput.value.trim();

                if (query === "") {
                    fetchAllTransactions();
                } else {
                    fetchFilteredTransactions(query);
                }
            });
        });

        function downloadDocument(refNum, department) {
            const encodedRefNum = encodeURIComponent(refNum);
            const encodedDepartment = encodeURIComponent(department);

            // Open the download link in a new tab
            const newWindow = window.open(`/Download/GENERATE_EXCEL.php?request=${encodedRefNum}&user=${encodedDepartment}`, '_blank');

            // Close the tab after 3 seconds (give time for the download to start)
            setTimeout(() => {
                if (newWindow) {
                    newWindow.close();
                }
            }, 3000);
        }


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