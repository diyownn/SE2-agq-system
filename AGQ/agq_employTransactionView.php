<?php
require 'db_agq.php';
session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';
$isOwner = isset($_SESSION['isOwner']) ? $_SESSION['isOwner'] : false;

if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}

if (!$company) {
    header("Location: UNAUTHORIZED.php?error=401c");
}

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

if ($role == 'Import Forwarding') {
    $query .= "
    UNION
    SELECT d.RefNum, d.DocType, c.Company_name
    FROM tbl_document d
    JOIN tbl_company c ON d.Company_name = c.Company_name
    WHERE c.Company_name = '$company'
    ";
}

$result = $conn->query($query);

$transactions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $docType = strtoupper($row['DocType']);
        $transactions[$docType][] = [
            'RefNum' => (string) $row['RefNum'], // Ensure it's a string
            'DocumentID' => isset($row['DocumentID']) ? (string) $row['DocumentID'] : null // Convert DocumentID to string if available
        ];
    }
}
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <a href="agq_dashCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>

    <div class="container py-3">
        <div class="search-container d-flex flex-wrap justify-content-center">
            <input type="text" class="search-bar form-control" id="search-input" placeholder="Search Transaction Details...">
            <div id="dropdown" class="dropdown" style="display: none;"></div>
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

            if ($role === 'Import Forwarding') {
                $docTypes[] = 'MANIFESTO';
                $labels[] = 'MANIFESTO';
            }

            $docTypeLabels = array_combine(array_map('strtoupper', $docTypes), $labels);
            ?>

            <?php foreach ($docTypes as $docType): ?>
                <div class="transaction">
                    <div class="transaction-header"><?php echo $docTypeLabels[strtoupper($docType)]; ?> <span class="icon">&#x25BC;</span></div>
                    <div class="transaction-content">
                        <?php $normalizedDocType = strtoupper(trim($docType)); ?>
                        <?php if (!empty($transactions[$normalizedDocType])): ?>
                            <?php foreach ($transactions[$normalizedDocType] as $transaction): ?>
                                <div class="transaction-item d-flex justify-content-between align-items-center">
                                    <span class="transaction-text" ondblclick="redirectToDocument('<?php echo htmlspecialchars($transaction['RefNum']); ?>', '<?php echo $normalizedDocType; ?>')">
                                        <?php echo htmlspecialchars($transaction['RefNum']); ?> - <?php echo $normalizedDocType; ?>
                                    </span>
                                    
                                    <?php if (!$isOwner): ?>
                                        <div class="transaction-actions">
                                            <button class="btn btn-sm action-btn check-btn" id="check-btn" title="Complete">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                            <button class="btn btn-sm action-btn edit-btn" id="edit-btn" title="Edit" onclick="redirectToDocument2('<?php echo htmlspecialchars($transaction['RefNum']); ?>', '<?php echo $normalizedDocType; ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm action-btn archive-btn" id="archive-btn" title="Archive" onclick="archiveDocument('<?php echo htmlspecialchars($transaction['RefNum']); ?>')">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
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
        var doctype = "<?php echo isset($_SESSION['DocType']) ? $_SESSION['DocType'] : ''; ?>"
        var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        var company = "<?php echo isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : ''; ?>";

        function archiveDocument(refnum) {
            fetch(`ARCHIVE_HANDLE.php?archived=${encodeURIComponent(refnum)}`, {
                    method: "GET"
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archived!',
                        text: 'The document has been successfully archived.',
                        confirmButtonColor: '#27ae60'
                    });
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to archive the document.',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        function redirectToDocument(refnum, doctype) {
            if (!refnum || !doctype) {
                return;
            } else {
                window.location.href = "agq_documentCatcher.php?refnum=" + encodeURIComponent(refnum) + '&doctype=' + encodeURIComponent(doctype);;
            }
        }

        function redirectToDocument2(refnum, doctype) {
            let url = "";
            switch (doctype) {
                case "INVOICE":
                    url = "agq_invoiceCatcher.php?refNum=" + encodeURIComponent(refnum);
                    break;
                case "SOA":
                    url = "agq_soaCatcher.php?refNum=" + encodeURIComponent(refnum);
                    break;
                default:
                    url = "agq_login.php";
                    break;
            }
            window.location.href = url;
        }

        function editTransaction(refnum, doctype) {
            window.location.href = "agq_edit_transaction.php?refnum=" + encodeURIComponent(refnum) + '&doctype=' + encodeURIComponent(doctype);
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
        let dropdown = document.getElementById("dropdown");

        if (searchInput) {
            searchInput.addEventListener("input", function() {
                let query = this.value.trim().toLowerCase();
                
                if (query.length === 0) {
                    dropdown.style.display = "none";
                    return;
                }

                fetch("FETCH_Transactions.php?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("API Response:", JSON.stringify(data, null, 2));

                        dropdown.innerHTML = "";
                        let seenItems = new Set(); // Track unique entries

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
                                let itemKey = `${refNum}-${docType}`.toLowerCase();

                                if (!seenItems.has(itemKey) && (refNum.includes(query) || docType.includes(query))) {
                                    seenItems.add(itemKey);
                                    
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
                            if (role == "Import Forwarding") {
                                if (!structuredTransactions[department]["MANIFESTO"]) {
                                    structuredTransactions[department]["MANIFESTO"] = [];
                                }
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

                                if (!Array.isArray(refArray)) {
                                    console.warn(`Skipping non-array records for ${docType}:`, refArray);
                                    return;
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

                let structuredTransactions = {};

                // Aggregate transactions across all departments
                Object.entries(transactions).forEach(([department, docTypes]) => {
                    Object.entries(docTypes).forEach(([docType, records]) => {
                        let normalizedDocType = docType.toUpperCase().trim();

                        if (!structuredTransactions[normalizedDocType]) {
                            structuredTransactions[normalizedDocType] = [];
                        }

                        structuredTransactions[normalizedDocType].push(...records);
                    });
                });

                const order = ["SOA", "INVOICE"];
                if (role == "Import Forwarding") {
                    order.push("MANIFESTO");
                }

                let sortedDocTypes = Object.keys(structuredTransactions).sort((a, b) => {
                    let indexA = order.indexOf(a);
                    let indexB = order.indexOf(b);
                    if (indexA === -1) indexA = order.length;
                    if (indexB === -1) indexB = order.length;
                    return indexA - indexB;
                });

                // Create sections for each document type only once
                sortedDocTypes.forEach(docType => {
                    let refs = structuredTransactions[docType];

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
                            transactionItem.classList.add("transaction-item", "d-flex", "justify-content-between", "align-items-center");
                            
                            let transactionText = document.createElement("span");
                            transactionText.classList.add("transaction-text");
                            transactionText.textContent = `${refNum} - ${docType}`;
                            transactionText.ondblclick = function() {
                                redirectToDocument(refNum, docType);
                            };
                            
                            transactionItem.appendChild(transactionText);
                            
                            let isOwner = <?php echo $isOwner ? 'true' : 'false'; ?>;
                            
                            if (!isOwner) {
                                // Employee view - action buttons
                                let actions = document.createElement("div");
                                actions.classList.add("transaction-actions");
                                
                                // Checkmark button
                                let checkBtn = document.createElement("button");
                                checkBtn.classList.add("btn", "btn-sm", "action-btn", "check-btn");
                                checkBtn.id = "check-btn";
                                checkBtn.title = "Complete";
                                checkBtn.innerHTML = '<i class="bi bi-check2"></i>';
                                
                                // Edit button
                                let editBtn = document.createElement("button");
                                editBtn.classList.add("btn", "btn-sm", "action-btn", "edit-btn");
                                editBtn.id = "edit-btn";
                                editBtn.title = "Edit";
                                editBtn.innerHTML = '<i class="bi bi-pencil"></i>';
                                editBtn.onclick = function() {
                                    redirectToDocument2(refNum, docType);
                                };
                                
                                // Archive button
                                let archiveBtn = document.createElement("button");
                                archiveBtn.classList.add("btn", "btn-sm", "action-btn", "archive-btn");
                                archiveBtn.id = "archive-btn";
                                archiveBtn.title = "Archive";
                                archiveBtn.innerHTML = '<i class="bi bi-archive"></i>';
                                archiveBtn.onclick = function() {
                                    archiveDocument(refNum);
                                };
                                
                                actions.appendChild(checkBtn);
                                actions.appendChild(editBtn);
                                actions.appendChild(archiveBtn);
                                transactionItem.appendChild(actions);
                            }
                            
                            transactionContent.appendChild(transactionItem);
                        });

                    } else {
                        transactionContent.innerHTML = "<p>No records found.</p>";
                    }

                    transactionSection.appendChild(transactionHeader);
                    transactionSection.appendChild(transactionContent);
                    container.appendChild(transactionSection);
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
            
            searchInput.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    searchButton.click();
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

        console.log("DocType:", doctype);
        console.log("Role:", role);
        console.log("Company:", company);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>