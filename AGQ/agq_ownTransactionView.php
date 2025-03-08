<?php
require 'db_agq.php';
session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';
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
    <p>You do not have permission to view this page. (ERR: R)</p>
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
    <p>You do not have permission to view this page. (ERR: C)</p>
  </div>
  </body></html>";
    exit;
}

$query = "
SELECT i.RefNum, i.DocType, c.Company_name
FROM tbl_impfwd i
JOIN tbl_company c ON i.Company_name = c.Company_name
WHERE '$dept' = 'Import Forwarding' AND c.Company_name = '$company'
UNION 
SELECT b.RefNum, b.DocType, c.Company_name
FROM tbl_impbrk b
JOIN tbl_company c ON b.Company_name = c.Company_name
WHERE '$dept' = 'Import Brokerage' AND c.Company_name = '$company'
UNION 
SELECT f.RefNum, f.DocType, c.Company_name
FROM tbl_expfwd f
JOIN tbl_company c ON f.Company_name = c.Company_name
WHERE '$dept' = 'Export Forwarding' AND c.Company_name = '$company'
UNION 
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
            window.location.href = 'agq_documentCatcher.php';
        }
    </script>
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
                <div class="selected-dept-label">
                    <?php echo htmlspecialchars($dept); ?>
                </div>
            </div>
        </div>
    </div>

    <!--<pre><?php print_r($transactions); ?></pre>-->

    <div class="container py-3">
        <div class="search-container d-flex flex-wrap justify-content-center">
            <input type="text" class="search-bar form-control" id="search-input" placeholder="Search Transaction Details...">
            <div id="dropdown" class="dropdown" id="dropdown" style="display: none;"></div>
            <button class="search-button" id="search-button">SEARCH</button>
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
                        Object.entries(data).forEach(([department, records]) => {
                            if (!structuredTransactions[department]) {
                                structuredTransactions[department] = {};
                            }

                            records.forEach(record => {
                                let docType = record.DocType ? record.DocType.toUpperCase().trim() : "UNKNOWN";
                                let refNum = record.RefNum || "No RefNum";

                                if (!structuredTransactions[department][docType]) {
                                    structuredTransactions[department][docType] = [];
                                }

                                structuredTransactions[department][docType].push(refNum);
                            });
                        });

                        // Ensure Summary and Others exist in all departments
                        Object.keys(structuredTransactions).forEach(department => {
                            if (!structuredTransactions[department]["SUMMARY"]) {
                                structuredTransactions[department]["SUMMARY"] = [];
                            }
                            if (!structuredTransactions[department]["OTHERS"]) {
                                structuredTransactions[department]["OTHERS"] = [];
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

                    // Define the order of document types
                    const order = ["SOA", "INVOICE", "SUMMARY", "OTHERS"];

                    // Sort document types based on the defined order
                    let sortedDocTypes = Object.keys(docTypes).sort((a, b) => {
                        let indexA = order.indexOf(a.toUpperCase());
                        let indexB = order.indexOf(b.toUpperCase());

                        if (indexA === -1) indexA = order.length; // Put unknown types at the end
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





            searchButton.addEventListener("click", function() {
                let query = searchInput.value.trim();

                if (query === "") {
                    fetchAllTransactions();
                } else {
                    fetchFilteredTransactions(query);
                }
            });
        });





        var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        var company = "<?php echo isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : ''; ?>";
        var selectdep = "<?php echo isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : ''; ?>"


        console.log("Role:", role);
        console.log("Company:", company);
        console.log("Selected Department:", selectdep);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>