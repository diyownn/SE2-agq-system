<?php
require 'db_agq.php';
session_start();

$url = isset($_GET['url']);
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';


if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}

if (!$company) {
    header("Location: UNAUTHORIZED.php?error=401c");
}

if (!$url) {
    header("Location: UNAUTHORIZED.php?error=401u");
}

$query = "
SELECT i.RefNum, i.DocType, c.Company_name, i.isApproved
FROM tbl_impfwd i
JOIN tbl_company c ON i.Company_name = c.Company_name
WHERE '$role' = 'Import Forwarding' 
AND c.Company_name = '$company'
AND i.isArchived = 0

UNION 

SELECT b.RefNum, b.DocType, c.Company_name, b.isApproved
FROM tbl_impbrk b
JOIN tbl_company c ON b.Company_name = c.Company_name
WHERE '$role' = 'Import Brokerage' 
AND c.Company_name = '$company'
AND b.isArchived = 0

UNION

SELECT f.RefNum, f.DocType, c.Company_name, f.isApproved
FROM tbl_expfwd f
JOIN tbl_company c ON f.Company_name = c.Company_name
WHERE '$role' = 'Export Forwarding' 
AND c.Company_name = '$company'
AND f.isArchived = 0

UNION

SELECT e.RefNum, e.DocType, c.Company_name, e.isApproved
FROM tbl_expbrk e
JOIN tbl_company c ON e.Company_name = c.Company_name
WHERE '$role' = 'Export Brokerage' 
AND c.Company_name = '$company'
AND e.isArchived = 0
";

if ($role == 'Import Forwarding') {
    $query .= "
    UNION
    SELECT d.RefNum, d.DocType, c.Company_name, d.isApproved
    FROM tbl_document d
    JOIN tbl_company c ON d.Company_name = c.Company_name
    WHERE c.Company_name = '$company'
    AND d.isArchived = 0
    ";
}

$result = $conn->query($query);

$transactions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $docType = strtoupper($row['DocType']);
        $transactions[$docType][] = [
            'RefNum' => (string) $row['RefNum'],
            'DocumentID' => isset($row['DocumentID']) ? (string) $row['DocumentID'] : null,
            'isApproved' => $row['isApproved'] // Store isApproved status
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
            <div id="dropdown" class="dropdown" id="dropdown" style="display: none;"></div>
            <button class="search-button" id="search-button">SEARCH</button>
        </div>
        <div>
            <button class="add-company" onclick="window.location.href='agq_choosedocument.php'">
                <span>CREATE</span>
                <div class="icons">
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
                                    <div class="d-flex align-items-center">
                                        <span class="transaction-text" ondblclick="redirectToDocument('<?php echo htmlspecialchars($transaction['RefNum']); ?>', '<?php echo $normalizedDocType; ?>')">
                                            <?php echo htmlspecialchars($transaction['RefNum']); ?> - <?php echo $normalizedDocType; ?>
                                        </span>
                                    </div>
                                    <div class="transaction-actions">
                                        <button class="btn btn-sm action-btn check-btn"
                                            id="check-btn-<?php echo htmlspecialchars($transaction['RefNum']); ?>"
                                            title="Complete"
                                            style="display: <?php echo ($transaction['isApproved'] == 1) ? 'block' : 'none'; ?>;">
                                            <i class="bi bi-check2"></i>
                                        </button>

                                        <button class="btn btn-sm action-btn edit-btn" id="edit-btn" title="Edit"
                                            onclick="redirectToDocument2('<?php echo htmlspecialchars($transaction['RefNum']); ?>', '<?php echo $normalizedDocType; ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm action-btn archive-btn" id="archive-btn" title="Archive"
                                            onclick="archiveDocument('<?php echo htmlspecialchars($transaction['RefNum']); ?>')">
                                            <i class="bi bi-archive"></i>
                                        </button>

                                    </div>

                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-records-container">
                                <p class="no-records-message">No records found.</p>
                                <?php if(isset($_GET['search'])): ?>
                                <button class="return-btn" onclick="clearSearch()">
                                        <span>Return to Transaction View</span>
                                    </button>
                                <?php endif; ?>
                            </div>
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

        function clearSearch() {
            // Redirect to the transaction view page
            window.location.href = "agq_transactionCatcher.php";
        }

        function updateCheckButtons() {
            document.querySelectorAll('.btn btn-sm action-btn check-btn').forEach(button => {
                let refNum = button.id.replace('check-btn-', '');
                fetch(`APPROVAL_STATUS.php?refNum=${refNum}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.isApproved == 1) {
                            button.style.display = "block";
                        } else {
                            button.style.display = "none";
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        }

        // Run the function when the page loads
        document.addEventListener("DOMContentLoaded", updateCheckButtons);


        function archiveDocument(refnum) {
            fetch("ARCHIVE_HANDLE.php?action=archive", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `RefNum=${encodeURIComponent(refnum)}`
                })
                .then(response => response.json()) // Expect JSON response
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Archived!",
                            text: data.message || "The document has been successfully archived.",
                            confirmButtonColor: "#27ae60"
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message || "Failed to archive the document.",
                            confirmButtonColor: "#d33"
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to communicate with the server.",
                        confirmButtonColor: "#d33"
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
                    url = "agq_manifestoView.php?refnum=" + encodeURIComponent(refnum);
                    break;
            }



            window.location.href = url;
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

                if (!query) {
                    dropdown.style.display = "none";
                    return;
                }

                fetch("FETCH_Transactions.php?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("API Response:", JSON.stringify(data, null, 2));

                        dropdown.innerHTML = "";

                        // Identify the correct department key dynamically
                        let departmentKey = Object.keys(data).find(key => Array.isArray(data[key]));
                        let transactions = departmentKey ? data[departmentKey] : [];

                        if (transactions.length > 0) {
                            transactions.forEach(item => {
                                let refNum = item.RefNum || "Unknown RefNum";
                                let docType = item.DocType || "No DocType";
                                let isArchived = item.ArchivedStatus === "Archived"; // Check archived status

                                let div = document.createElement("div");
                                div.classList.add("dropdown-item");
                                div.style.display = "flex";
                                div.style.justifyContent = "space-between"; // Aligns left and right
                                div.style.padding = "10px 15px";

                                if (isArchived) {
                                    div.style.cursor = "not-allowed";
                                    div.style.opacity = "0.5"; // Make archived items appear faded
                                } else {
                                    div.onclick = function() {
                                        searchInput.value = refNum;
                                        dropdown.style.display = "none";
                                    };
                                }

                                div.innerHTML = `
                            <span><strong>${refNum}</strong> - ${docType}</span>
                            <span style="color: red; font-weight: bold;">${isArchived ? "Archived" : ""}</span>
                        `;

                                dropdown.appendChild(div);
                            });

                            // Show dropdown only if there are matching results
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
               location.reload();
            }

            function fetchFilteredTransactions(query) {
                fetch("FILTER_TRANSACTIONS.php?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("Filtered API Response:", data);

                        transactionsContainer.innerHTML = "";

                        if (!data || Object.keys(data).length === 0 || data.error) {
                            // Show no results found message with return button
                            transactionsContainer.innerHTML = `
                                <div class="no-results-container text-center my-5">
                                    <p class="no-records-message">No transactions found.</p>
                                    <button class="btn btn-sm return-btn" onclick="clearSearch()">Return to Transaction View</button>
                                </div>`;
                            return;
                        }

                        let structuredTransactions = {};

                        Object.entries(data).forEach(([department, docTypes]) => {
                            structuredTransactions[department] = {};

                            Object.entries(docTypes).forEach(([docType, records]) => {
                                let normalizedDocType = docType.toUpperCase().trim();

                                // ✅ FIX: Convert non-array records into an array
                                if (!Array.isArray(records)) {
                                    records = [records];
                                }

                                if (!structuredTransactions[department][normalizedDocType]) {
                                    structuredTransactions[department][normalizedDocType] = [];
                                }

                                records.forEach(item => {
                                    structuredTransactions[department][normalizedDocType].push(item.RefNum);
                                });
                            });
                        });

                        generateTransactionHTML(structuredTransactions, transactionsContainer);
                    })
                    .catch(error => {
                        console.error("Error fetching filtered transactions:", error);
                        // Show error message with return button
                        transactionsContainer.innerHTML = `
                            <div class="no-results-container text-center my-5">
                                <p class="no-records-message">Error loading transactions.</p>
                                <button class="btn btn-sm return-btn" onclick="clearSearch()">Return to Transaction View</button>
                            </div>`;
                    });
            }

            function ensureDocumentTypes(structuredTransactions) {
                Object.keys(structuredTransactions).forEach(department => {
                    if (!structuredTransactions[department]["SOA"]) {
                        structuredTransactions[department]["SOA"] = [];
                    }
                    if (!structuredTransactions[department]["INVOICE"]) {
                        structuredTransactions[department]["INVOICE"] = [];
                    }
                    if (role === "Import Forwarding") {
                        if (!structuredTransactions[department]["MANIFESTO"]) {
                            structuredTransactions[department]["MANIFESTO"] = [];
                        }
                    }
                });
            }

            function generateTransactionHTML(transactions, container) {
                container.innerHTML = "";
                
                // Check if we have any transactions at all
                let hasAnyTransactions = false;
                Object.values(transactions).forEach(deptTypes => {
                    Object.values(deptTypes).forEach(records => {
                        if (Array.isArray(records) && records.length > 0) {
                            hasAnyTransactions = true;
                        }
                    });
                });
                
                if (!hasAnyTransactions) {
                    container.innerHTML = `
                        <div class="no-results-container text-center my-5">
                            <p class="no-records-message">No transactions found.</p>
                            <button class="btn btn-sm return-btn" onclick="clearSearch()">Return to Transaction View</button>
                        </div>`;
                    return;
                }

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
                if (role === "Import Forwarding") {
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

                            let leftSide = document.createElement("div");
                            leftSide.classList.add("d-flex", "align-items-center");

                            let transactionText = document.createElement("span");
                            transactionText.classList.add("transaction-text");
                            transactionText.textContent = `${refNum} - ${docType}`;
                            transactionText.ondblclick = function() {
                                redirectToDocument(refNum, docType);
                            };

                            leftSide.appendChild(transactionText);

                            let actions = document.createElement("div");
                            actions.classList.add("transaction-actions");

                            let editBtn = document.createElement("button");
                            editBtn.classList.add("btn", "btn-sm", "action-btn", "edit-btn");
                            editBtn.title = "Edit";
                            editBtn.innerHTML = '<i class="bi bi-pencil"></i>';
                            editBtn.onclick = function() {
                                redirectToDocument2(refNum, docType);
                            };

                            let archiveBtn = document.createElement("button");
                            archiveBtn.classList.add("btn", "btn-sm", "action-btn", "archive-btn");
                            archiveBtn.title = "Archive";
                            archiveBtn.innerHTML = '<i class="bi bi-archive"></i>';
                            archiveBtn.onclick = function() {
                                archiveDocument(refNum);
                            };

                            actions.appendChild(editBtn);
                            actions.appendChild(archiveBtn);

                            transactionItem.appendChild(leftSide);
                            transactionItem.appendChild(actions);
                            transactionContent.appendChild(transactionItem);
                        });

                    } else {
                        let noRecordsContainer = document.createElement("div");
                        noRecordsContainer.classList.add("no-records-container");
                        
                        let noRecordsMessage = document.createElement("p");
                        noRecordsMessage.classList.add("no-records-message");
                        noRecordsMessage.textContent = "No records found.";
                        
                        noRecordsContainer.appendChild(noRecordsMessage);
                        
                        // Add return button if this is a search result
                        if (searchInput.value.trim() !== "") {
                            let returnButton = document.createElement("button");
                            returnButton.classList.add("btn", "btn-sm", "return-btn");
                            returnButton.textContent = "Return to Transaction View";
                            returnButton.onclick = clearSearch;
                            noRecordsContainer.appendChild(returnButton);
                        }
                        
                        transactionContent.appendChild(noRecordsContainer);
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