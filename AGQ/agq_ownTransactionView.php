<?php
require 'db_agq.php';
session_start();

$url = isset($_GET['url']) ? $_GET['url'] : '';
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}

if (!$company) {
    header("Location: UNAUTHORIZED.php?error=401c");
}

if (!$dept) {
    header("Location: UNAUTHORIZED.php?error=401d");
}

// Changed this condition to check if URL is empty, not just if it's set
if ($url === '') {
    // Removed this redirect to avoid unauthorized page
    // header("Location: UNAUTHORIZED.php?error=401u");
}

$query = "
SELECT i.RefNum, i.DocType, c.Company_name
FROM tbl_impfwd i
JOIN tbl_company c ON i.Company_name = c.Company_name
WHERE '$dept' = 'Import Forwarding' 
AND c.Company_name = '$company'
AND i.isArchived = 0

UNION 

SELECT b.RefNum, b.DocType, c.Company_name
FROM tbl_impbrk b
JOIN tbl_company c ON b.Company_name = c.Company_name
WHERE '$dept' = 'Import Brokerage' 
AND c.Company_name = '$company'
AND b.isArchived = 0

UNION

SELECT f.RefNum, f.DocType, c.Company_name
FROM tbl_expfwd f
JOIN tbl_company c ON f.Company_name = c.Company_name
WHERE '$dept' = 'Export Forwarding' 
AND c.Company_name = '$company'
AND f.isArchived = 0

UNION

SELECT e.RefNum, e.DocType, c.Company_name
FROM tbl_expbrk e
JOIN tbl_company c ON e.Company_name = c.Company_name
WHERE '$dept' = 'Export Brokerage' 
AND c.Company_name = '$company'
AND e.isArchived = 0
";

if ($dept == 'Import Forwarding') {
    $query .= "
    UNION
    SELECT d.RefNum, d.DocType, c.Company_name
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
    <title>Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/otp.css">
    <link rel="stylesheet" href="../css/home-icon.css">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body style="background-image: url('otvbg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">

    <div class="top-container">
        <div class="dept-container">
            <div class="header-container">
                <div class="dept-label">
                    <a href="agq_dashCatcher.php" class="home-link">
                        <!-- Home Icon SVG -->
                        <svg class="home-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <?php echo htmlspecialchars($role); ?>
                    </a>
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

    <a href="agq_chooseDepartment.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>

    <div class="container py-3">
        <div class="search-container d-flex flex-wrap justify-content-center">
            <input type="text" class="search-bar form-control" id="search-input" placeholder="Search Transaction Details...">
            <div id="dropdown" class="dropdown" style="display: none;"></div>
            <button class="search-button" id="search-button">SEARCH</button>
        </div>

        <select id="departmentFilter" class="department-dropdown" onchange="updateDepartment(this.value)">
            <option value="" disabled <?php echo empty($dept) ? 'selected' : ''; ?>>All Departments</option>
            <option value="Import Forwarding" <?php echo ($dept == 'Import Forwarding') ? 'selected' : ''; ?>>Import Forwarding</option>
            <option value="Import Brokerage" <?php echo ($dept == 'Import Brokerage') ? 'selected' : ''; ?>>Import Brokerage</option>
            <option value="Export Forwarding" <?php echo ($dept == 'Export Forwarding') ? 'selected' : ''; ?>>Export Forwarding</option>
            <option value="Export Brokerage" <?php echo ($dept == 'Export Brokerage') ? 'selected' : ''; ?>>Export Brokerage</option>
        </select>

        <div class="transactions mt-4">
            <?php
            $docTypes = ['SOA', 'Invoice'];
            $labels = ['SOA', 'INVOICE'];

            if ($dept === 'Import Forwarding' || $role === 'Import Forwarding') {
                $docTypes[] = 'Manifesto';
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
                                <div class="transaction-item d-flex justify-content-between">
                                    <span ondblclick="redirectToDocument('<?php echo htmlspecialchars($transaction['RefNum']); ?>', '<?php echo $normalizedDocType; ?>')">
                                        <?php echo htmlspecialchars($transaction['RefNum']); ?> - <?php echo $normalizedDocType; ?>
                                    </span>
                                    <div class="checkbox-container">
                                        <input type="checkbox" id="tx-<?php echo htmlspecialchars($transaction['RefNum']); ?>"
                                            class="transaction-checkbox"
                                            data-refnum="<?php echo htmlspecialchars($transaction['RefNum']); ?>"
                                            data-docType="<?php echo htmlspecialchars($normalizedDocType); ?>"
                                            data-dept="<?php echo htmlspecialchars($dept); ?>"
                                            onclick="updateApprovalStatus(this)">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-records-container">
                                <p class="no-records-message">No records found.</p>
                                <?php if (isset($_GET['search'])): ?>
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

    <div id="signatureModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
     background-color:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
        <div style="background:#fff; padding:20px; border-radius:10px; text-align:center;">
            <h3>Please Sign to Approve</h3>
            <canvas id="signature-pad" width="400" height="200" style="border:1px solid #ccc;"></canvas><br><br>
            <button onclick="clearSignature()">Clear</button>
            <button onclick="submitSignature()">Submit Signature</button>
            <button onclick="cancelSignature()">Cancel</button>
        </div>
    </div>

    <script>
        var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        var company = "<?php echo isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : ''; ?>";
        var dept = "<?php echo isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : ''; ?>";


        function updateDepartment(selectedDept) {

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "STORE_SESSION.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");


            xhr.send("selected_department=" + encodeURIComponent(selectedDept));


            xhr.onload = function() {
                if (xhr.status == 200) {
                    console.log("Department updated to: " + selectedDept);

                    location.reload();
                }
            };
        }

        function clearSearch() {
            document.getElementById("search-input").value = "";
            location.reload();
        }

        function redirectToDocument(refnum, doctype) {
            if (!refnum || !doctype) {
                return;
            } else {
                window.location.href = "agq_documentCatcher.php?refNum=" + encodeURIComponent(refnum) + '&doctype=' + encodeURIComponent(doctype);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            updateCheckButtons();
        });

        function updateCheckButtons() {
            document.querySelectorAll('.transaction-checkbox').forEach(button => {
                let RefNum = button.dataset.refnum; // Extract from data attribute
                let DocType = button.dataset.doctype; // Extract docType from data attribute
                let Dept = button.dataset.dept || ''; // If dept is missing, set a default value

                if (!RefNum || !DocType || !Dept) {
                    console.error(`Missing parameters: refNum=${RefNum}, docType=${DocType}, dept=${Dept}`);
                    return;
                }

                let requestUrl = `APPROVAL_STATUS.php?refNum=${encodeURIComponent(RefNum)}&docType=${encodeURIComponent(DocType)}&dept=${encodeURIComponent(Dept)}`;

                console.log(`Fetching approval status for RefNum: ${RefNum}`);
                console.log(`Request URL: ${requestUrl}`);

                fetch(requestUrl)
                    .then(response => response.json())
                    .then(data => {
                        console.log(`Response Data for RefNum ${RefNum}:`, data);
                        if (data.success && data.isApproved == 1) {
                            button.checked = true;
                        } else {
                            button.checked = false;
                        }
                    })
                    .catch(error => console.error(`Error fetching approval status for RefNum ${RefNum}:`, error));
            });
        }

        let currentCheckbox = null;
const canvas = document.getElementById('signature-pad');
const signaturePad = new SignaturePad(canvas);

function clearSignature() {
    signaturePad.clear();
}

function cancelSignature() {
    document.getElementById('signatureModal').style.display = 'none';
    if (currentCheckbox) {
        currentCheckbox.checked = false; // Reset checkbox
        currentCheckbox = null;
    }
}

function submitSignature() {
    if (signaturePad.isEmpty()) {
        alert("Please sign before submitting.");
        return;
    }

    const signatureImage = signaturePad.toDataURL();
    const checkbox = currentCheckbox;

    if (!checkbox) return;

    const refNum = checkbox.getAttribute("data-refnum");
    const docType = checkbox.getAttribute("data-docType");
    const dept = checkbox.getAttribute("data-dept");
    const isApproved = 1;

    fetch("UPDATE_APPROVAL.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                refNum: refNum,
                company: company,
                docType: docType,
                dept: dept,
                isApproved: isApproved,
                signature: signatureImage
            })
        })
        .then(response => response.text())  // Get response as text to see the full response body
        .then(data => {
            console.log("Response from server:", data);  // Log the raw response
            try {
                const jsonResponse = JSON.parse(data);  // Try to parse JSON
                if (jsonResponse.success) {
                    Swal.fire("Success", "Approval recorded with signature.", "success");
                } else {
                    Swal.fire("Error", jsonResponse.message, "error");
                    checkbox.checked = false;
                }
            } catch (e) {
                console.error("Error parsing JSON:", e);
                Swal.fire("Error", "Response from server is not valid JSON.", "error");
            }
            document.getElementById('signatureModal').style.display = 'none';
        })
        .catch(error => {
            console.error("Upload failed:", error);
            Swal.fire("Error", "Something went wrong.", "error");
            checkbox.checked = false;
            document.getElementById('signatureModal').style.display = 'none';
        });

    currentCheckbox = null;
}

function updateApprovalStatus(checkbox) {
    if (checkbox.checked) {
        // Show signature modal
        currentCheckbox = checkbox;
        document.getElementById('signatureModal').style.display = 'flex';
    } else {
        // Optional: handle unchecking logic here
        const refNum = checkbox.getAttribute("data-refnum");
        const docType = checkbox.getAttribute("data-docType");
        const dept = checkbox.getAttribute("data-dept");
        const isApproved = 0;

        fetch("UPDATE_APPROVAL.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    refNum: refNum,
                    company: company,
                    docType: docType,
                    dept: dept,
                    isApproved: isApproved
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire("Error", data.message, "error");
                    checkbox.checked = true;
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire("Error", "Unapproval failed.", "error");
                checkbox.checked = true;
            });
    }
}


        document.addEventListener("DOMContentLoaded", function() {
            document.body.addEventListener("click", function(event) {
                if (event.target.classList.contains("transaction-header") || event.target.classList.contains("icon")) {
                    const transaction = event.target.closest(".transaction");
                    const content = transaction.querySelector(".transaction-content");

                    if (content.classList.contains("open")) {
                        content.style.maxHeight = "0px"; // Collapse
                        setTimeout(() => content.classList.remove("open"), 500); // Remove class after animation
                    } else {
                        content.style.maxHeight = content.scrollHeight + 10 + "px"; // Expand
                        content.classList.add("open");
                    }

                    transaction.querySelector(".transaction-header").classList.toggle("active");
                }
            });
        });


        let searchInput = document.getElementById("search-input");
        let dropdown = document.getElementById("dropdown");

        // Variable to track previous search value
        let previousSearchValue = "";

        if (searchInput) {
            searchInput.addEventListener("input", function() {
                let currentValue = this.value.trim().toLowerCase();

                // If value was something before and now it's empty, redirect to transaction view
                if (previousSearchValue !== "" && currentValue === "") {
                    clearSearch();
                    return;
                }

                previousSearchValue = currentValue;

                if (!currentValue) {
                    dropdown.style.display = "none";
                    return;
                }

                fetch("FETCH_Transactions.php?search=" + encodeURIComponent(currentValue))
                    .then(response => response.json())
                    .then(data => {
                        console.log("API Response:", JSON.stringify(data, null, 2));

                        dropdown.innerHTML = "";

                        // Identify the correct department key dynamically
                        let departmentKey = Object.keys(data).find(key => Array.isArray(data[key]));
                        let transactions = departmentKey ? data[departmentKey] : [];

                        // Check if the search query directly matches any RefNum
                        let exactMatches = transactions.filter(item =>
                            (item.RefNum && item.RefNum.toLowerCase().includes(currentValue))
                        );

                        // Only show dropdown if we have exact matches to RefNum
                        if (exactMatches.length > 0) {
                            exactMatches.forEach(item => {
                                let refNum = item.RefNum || "Unknown RefNum";
                                let docType = item.DocType || "No DocType";
                                let isArchived = item.ArchivedStatus === "Archived";

                                let div = document.createElement("div");
                                div.classList.add("dropdown-item");
                                div.style.display = "flex";
                                div.style.justifyContent = "space-between";
                                div.style.padding = "10px 15px";

                                if (isArchived) {
                                    div.style.cursor = "not-allowed";
                                    div.style.opacity = "0.5";
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

                            dropdown.style.display = "block";
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

            // Function to fetch all transactions (reload page)
            function fetchAllTransactions() {
                clearSearch();
            }

            function fetchFilteredTransactions(query) {
                fetch("FILTER_TRANSACTIONS.php?search=" + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        console.log("Filtered API Response:", data);

                        transactionsContainer.innerHTML = "";

                        if (!data || Object.keys(data).length === 0 || data.error) {
                            transactionsContainer.innerHTML = `
                                <div class="no-results-container text-center my-5">
                                    <p class="no-records-message">No transactions found.</p>
                                    <button class="return-btn" onclick="clearSearch()">
                                        <span>Return to Transaction View</span>
                                    </button>
                                </div>`;
                            return;
                        }

                        let structuredTransactions = {};
                        let records = [];
                        let hasArchivedRecords = false;

                        Object.entries(data).forEach(([department, docTypes]) => {
                            structuredTransactions[department] = {};

                            Object.entries(docTypes).forEach(([docType, refArray]) => {
                                let normalizedDocType = docType.toUpperCase().trim();

                                let filteredRecords = records.filter(item => item.ArchivedStatus !== "Archived");
                                if (hasArchivedRecords) {
                                    console.log("There are archived records.");
                                } else {
                                    console.log("No archived records found.");
                                }

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
                    .catch(error => {
                        console.error("Error fetching filtered transactions:", error);
                        // Show error message with return button
                        transactionsContainer.innerHTML = `
                            <div class="no-results-container text-center my-5">
                                <p class="no-records-message">Error loading transactions.</p>
                                <button class="return-btn" onclick="clearSearch()">
                                    <span>Return to Transaction View</span>
                                </button>
                            </div>`;
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

                if (!hasAnyTransactions && searchInput.value.trim() !== "") {
                    container.innerHTML = `
            <div class="no-results-container text-center my-5">
                <p class="no-records-message">No transactions found.</p>
                <button class="return-btn" onclick="clearSearch()">
                    <span>Return to Transaction View</span>
                </button>
            </div>`;
                    return;
                }

                Object.entries(transactions).forEach(([department, docTypes]) => {
                    let departmentSection = document.createElement("div");
                    departmentSection.classList.add("department-section");

                    const order = ["SOA", "INVOICE"];
                    let orderWithManifesto = ["SOA", "INVOICE", "MANIFESTO"];

                    let sortedOrder = (department === "Import Forwarding") ? orderWithManifesto : order;

                    let sortedDocTypes = Object.keys(docTypes).sort((a, b) => {
                        let indexA = sortedOrder.indexOf(a.toUpperCase());
                        let indexB = sortedOrder.indexOf(b.toUpperCase());

                        if (indexA === -1) indexA = sortedOrder.length;
                        if (indexB === -1) indexB = sortedOrder.length;

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

                                let checkboxContainer = document.createElement("div");
                                checkboxContainer.classList.add("checkbox-container");

                                // Creating checkbox with necessary attributes
                                let checkbox = document.createElement("input");
                                checkbox.type = "checkbox";
                                checkbox.id = `tx-${refNum}`;
                                checkbox.classList.add("transaction-checkbox");
                                checkbox.setAttribute("data-refnum", refNum);
                                checkbox.setAttribute("data-docType", docType);
                                checkbox.setAttribute("data-dept", dept);
                                checkbox.setAttribute("onclick", "updateApprovalStatus(this)");

                                checkboxContainer.appendChild(checkbox);
                                transactionItem.appendChild(transactionText);
                                transactionItem.appendChild(checkboxContainer);
                                transactionContent.appendChild(transactionItem);
                            });
                        } else {
                            let noRecordsContainer = document.createElement("div");
                            noRecordsContainer.classList.add("no-records-container");

                            let noRecordsMessage = document.createElement("p");
                            noRecordsMessage.classList.add("no-records-message");
                            noRecordsMessage.textContent = "No records found.";
                            noRecordsContainer.appendChild(noRecordsMessage);

                            if (searchInput.value.trim() !== "") {
                                let returnButton = document.createElement("button");
                                returnButton.classList.add("return-btn");
                                returnButton.innerHTML = "<span>Return to Transaction View</span>";
                                returnButton.onclick = clearSearch;
                                noRecordsContainer.appendChild(returnButton);
                            }

                            transactionContent.appendChild(noRecordsContainer);
                        }

                        transactionSection.appendChild(transactionHeader);
                        transactionSection.appendChild(transactionContent);
                        departmentSection.appendChild(transactionSection);
                    });

                    container.appendChild(departmentSection);
                });
                updateCheckButtons();
            }

            searchButton.addEventListener("click", function() {
                let query = searchInput.value.trim();

                if (query === "") {
                    fetchAllTransactions();
                } else {
                    fetchFilteredTransactions(query);
                    updateCheckButtons();
                }
            });

            searchInput.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    searchButton.click();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener("click", function(event) {
                if (!searchInput.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.style.display = "none";
                }
            });
        });

        function downloadDocument(refNum, department) {
            const encodedRefNum = encodeURIComponent(refNum);
            const encodedDepartment = encodeURIComponent(department);

            const newWindow = window.open(`/Download/GENERATE_EXCEL.php?request=${encodedRefNum}&user=${encodedDepartment}`, '_blank');

            setTimeout(() => {
                if (newWindow) {
                    newWindow.close();
                }
            }, 3000);
        }

        console.log("Role:", role);
        console.log("Company:", company);
        console.log("Selected Department:", dept);
    </script>

</body>

</html>