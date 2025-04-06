<?php
require "db_agq.php";

session_start();

$url = isset($_GET['url']);
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';


if (!$url) {
    header("Location: UNAUTHORIZED.php?error=401u");
}

if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_GET['refNum']) && !empty($_GET['refNum'])) {
        $docs = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : '';
        date_default_timezone_set('Asia/Manila');
        updateRecord($conn, $_POST, [
            "editDate" => date("Y-m-d H:i:s"),
            "companyName" => $_SESSION['Company_name'],
            "department" => $_SESSION['department']
        ]);
    } elseif (isset($_POST['save'])) {
        insertRecord($conn);
    }
}

$refNum = isset($_GET['refNum']) && !empty($_GET['refNum']) ? $_GET['refNum'] : "";

if (!empty($refNum)) {
    $sql = "SELECT * FROM tbl_impbrk WHERE RefNum LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $refNum);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

if (isset($_GET['refNum'])) {
    $refNum = $_GET['refNum'];
    $sql = "SELECT * FROM tbl_impbrk WHERE RefNum LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $refNum);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    $conn->close();
}

function updateRecord($conn, $data, $sessionData)
{
    $name = isset($_SESSION['name']) ? $_SESSION['name'] : 'no name';
    $docs = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : '';
    $refNum = isset($_GET['refNum']) && !empty($_GET['refNum']) ? $_GET['refNum'] : "";

    $sql = "UPDATE tbl_impbrk SET 
        `To:` = ?, 
        `Address` = ?, 
        Tin = ?, 
        Attention = ?, 
        Vessel = ?, 
        ETA = ?, 
        RefNum = ?,
        DestinationOrigin = ?, 
        ER = ?, 
        BHNum = ?, 
        NatureOfGoods = ?, 
        Packages = ?, 
        `Weight` = ?, 
        Volume = ?, 
        PackageType = ?, 
        Others = ?, 
        Notes = ?, 
        OceanFreight5 = ?, 
        BrokerageFee = ?, 
        Vat12 = ?, 
        TruckingService = ?, 
        Total = ?, 
        Prepared_by = ?, 
        Approved_by = ?, 
        Edited_by = ?, 
        EditDate = ?, 
        DocType = ?, 
        Company_name = ?, 
        Department = ?
    WHERE RefNum = ?";  // Using RefNum to identify the record

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sssssssssssssssdsdddddssssssss",
        $data['to'],
        $data['address'],
        $data['tin'],
        $data['attention'],
        $data['vessel'],
        $data['eta'],
        $data['refNum'],
        $data['destinationOrigin'],
        $data['er'],
        $data['bhNum'],
        $data['natureOfGoods'],
        $data['packages'],
        $data['weight'],
        $data['volume'],
        $data['package'],
        $data['others_amount'],
        $data['notes'],
        $data['5oceanfreight'],
        $data['brokeragefee'],
        $data['12vat'],
        $data['truckingservice_amount'],
        $data['total'],
        $data['prepared_by'],
        $data['approved_by'],
        $name,
        $sessionData['editDate'],
        $docs,
        $sessionData['companyName'],
        $sessionData['department'],
        $refNum
    );

    if ($stmt->execute()) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Success!",
                text: "Document Successfully Edited!",
                icon: "success",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Return to Transactions Page",
                cancelButtonText: "Stay Here"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "agq_transactionCatcher.php";
                }
            });
        });
        </script>';
    return;
    } else {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Error!",
                text: "Error updating record: ' . $stmt->error . '",
                icon: "error",
                confirmButtonText: "OK"
            });
        });
    </script>';
    return;
    }

    $stmt->close();
}


// Function to insert a record
function insertRecord($conn)
{
    $name = isset($_SESSION['name']) ? $_SESSION['name'] : 'no name';
    $docType = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : null;
    $department = isset($_SESSION['department']) ? $_SESSION['department'] : null;
    $companyName = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : null;
    
    date_default_timezone_set('Asia/Manila');
    $createDate = date('Y-m-d');
    $editDate = date('Y-m-d H:i:s');

    $refNum = $_POST['refNum'];
    $checkSql = "SELECT RefNum FROM tbl_expbrk WHERE RefNum = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $refNum);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        ?>
        <script>
            // Using SweetAlert2 for duplicate reference number
            Swal.fire({
                title: 'Error!',
                text: 'Reference Number already exists. Please create the document again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>
        <?php
        $checkStmt->close();
        return; // Stop execution if RefNum exists
    }

    $checkStmt->close();

    $sql = "INSERT INTO tbl_impbrk (
        `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, Others, Notes, OceanFreight5,
        BrokerageFee, Vat12, TruckingService,Total, Prepared_by, Approved_by, Edited_by, EditDate, 
        DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssdsdddddsssssss",
        $_POST['to'],
        $_POST['address'],
        $_POST['tin'],
        $_POST['attention'],
        $createDate,
        $_POST['vessel'],
        $_POST['eta'],
        $_POST['refNum'],
        $_POST['destinationOrigin'],
        $_POST['er'],
        $_POST['bhNum'],
        $_POST['natureOfGoods'],
        $_POST['packages'],
        $_POST['weight'],
        $_POST['volume'],
        $_POST['package'],
        $_POST['others_amount'],
        $_POST['notes'],
        $_POST['5oceanfreight'],
        $_POST['brokeragefee'],
        $_POST['12vat'],
        $_POST['truckingservice_amount'],
        $_POST['total'],
        $_POST['prepared_by'],
        $_POST['approved_by'],
        $name,
        $editDate,
        $docType,        // Session variable
        $companyName,    // Session variable
        $department      // Session variable
    );

    if ($stmt->execute()) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Success!",
                    text: "Document Successfully Created!",
                    icon: "success",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Return to Transactions Page",
                    cancelButtonText: "Stay Here"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "agq_transactionCatcher.php";
                    }
                });
            });
        </script>';
    } else {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "Error: ' . $stmt->error . '",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>';
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../css/forms.css">
    <title>Sales Invoice </title>
     <!-- Add SweetAlert2 library -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Ensure SweetAlert2 is fully loaded -->
    <script>
        // Make sure SweetAlert2 is available globally
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded properly');
        }
    </script>
    <script>
        function togglePackageField() {
            document.getElementById("package-details").style.display = "block";
            updateReimbursableCharges();
        }

        function updateReimbursableCharges() {
            const lclSelected = document.getElementById("lcl").checked;
            const containerSelected = document.getElementById("container").checked;
            const chargesTable = document.getElementById("charges-table");
            chargesTable.innerHTML = ""; // Clear existing charges

            if (lclSelected) {
                const lclCharges = [
                    "5 Ocean Freight",
                    "Brokerage Fee",
                    "12 VAT",
                    "Additional Charges"
                ];
                generateFixedCharges(lclCharges);
            } else if (containerSelected) {
                const containerCharges = [
                    "5 Ocean Freight",
                    "Brokerage Fee",
                    "12 VAT",
                    "Additional Charges"
                ];
                generateFixedCharges(containerCharges, true);
            }
        }

        function generateFixedCharges(charges, isLCL) {
            const chargesTable = document.getElementById("charges-table");

            charges.forEach(charge => {
                const row = document.createElement("div");
                row.className = "table-row";

                if (charge === "Additional Charges") {
                    row.innerHTML = `
                        <select onchange="handleChargeSelection(this, ${isLCL})">
                            <option value="">Additional Charges</option>
                            ${isLCL 
                                ? '<option value="Others">Others</option>' 
                                : '<option value="Others">Others</option><option value="Trucking Service">Trucking Service</option>'
                            }
                        </select>
                    `;
                } else {
                    const inputName = charge.toLowerCase().replace(/\s+/g, '').replace('/', '');
                    row.innerHTML = `
                        <input type="text" value="${charge}" readonly>
                        <input type="number" name="${inputName}" step="0.01" placeholder="Enter amount" onchange="validateChargeAmount(this)">
                    `;
                }

                chargesTable.appendChild(row);
            });
        }

        function handleChargeSelection(selectElement) {
            const selectedCharge = selectElement.value;
            if (!selectedCharge) return; // Do nothing if default is selected

            // Prevent duplicate entries
            const existingEntries = document.querySelectorAll(".added-charge");
            for (let entry of existingEntries) {
                if (entry.dataset.charge === selectedCharge) return;
            }

            // Add new charge field
            const chargesTable = document.getElementById("charges-table");
            const newRow = document.createElement("div");
            newRow.className = "table-row added-charge";
            newRow.dataset.charge = selectedCharge; // Store charge type

            let inputName = selectedCharge.toLowerCase() + "_amount";

            newRow.innerHTML = `
                <input type="text" value="${selectedCharge}" readonly>
                <input type="number" name="${inputName}" step="0.01" placeholder="Enter amount" onchange="validateChargeInput(this)">
                <button type="button" onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);
        }

        function removeCharge(button) {
            button.parentElement.remove(); // Remove the selected charge row
        }

        function validateChargeInput(inputElement) {
            const value = parseFloat(inputElement.value) || 0;
            const maxAmount = 16500000;

            // Check for maximum allowed amount
            if (value > maxAmount) {
                inputElement.setCustomValidity("Value cannot exceed 16,500,000");
            } else if (!/^\d+(\.\d{1,2})?$/.test(inputElement.value)) {
                // Regex ensures value is a number with up to 2 decimal places
                inputElement.setCustomValidity("Please enter a valid amount (up to 2 decimal places)");
            } else {
                inputElement.setCustomValidity(""); // Clear validation
            }

            inputElement.reportValidity();
        }

        function validateChargeAmount(chargeElement) {
            const maxAmount = 16500000;
            const value = parseFloat(chargeElement.value) || 0;
            let isValid = true; // Track overall validity

            if (value > maxAmount) {
                chargeElement.setCustomValidity("Value cannot exceed 16,500,000");
            } else if (!/^\d+(\.\d{1,2})?$/.test(chargeElement.value)) {
                // Regex ensures value is a number with up to 2 decimal places
                chargeElement.setCustomValidity("Please enter a valid amount (up to 2 decimal places)");
            } else {
                chargeElement.setCustomValidity(""); // Reset validation
            }

            chargeElement.reportValidity(); // Show validation message

            return isValid;
        }

        function validateTextFields(textElement) {
            const allowedSymbols = /^[a-zA-Z0-9\$%\-\/\., ]+$/; // Allow letters, numbers, and only $ % / . , -
            const reverseTinRegex = /^(?!^[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{3}$).+$/; // Correct regex for TIN format (0000-0000-0000-0000)
            let isValid = true; // Track overall validity

            if (textElement.name === "tin") {
                // Check TIN-specific validation
                if (!textElement.value.trim()) {
                    textElement.setCustomValidity("This field is required");
                } else if (reverseTinRegex.test(textElement.value)) {
                    textElement.setCustomValidity("TIN format is invalid. Correct format: xxx-xxx-xxx-xxx");
                } else {
                    textElement.setCustomValidity(""); // Reset validation
                }
            } else {
                if (!textElement.value.trim()) {
                    textElement.setCustomValidity("This field is required");
                } else if (!allowedSymbols.test(textElement.value)) {
                    textElement.setCustomValidity("Only letters, numbers, and these symbols are allowed: $ % / - , .");
                } else {
                    textElement.setCustomValidity(""); // Reset validation
                }
            }

            textElement.reportValidity(); // Show validation message

            if (!textElement.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            textElement.addEventListener("input", function() {
                textElement.setCustomValidity(""); // Clear error when user types
            });

            return isValid; // Return validity status
        }


        function validateNotesField(notesInput) {
            const allowedSymbols = /^[a-zA-Z0-9\$%\-\/\., \n#*]+$/; // Allow letters, numbers, $ % / . , - and newlines
            const maxLength = 500; // Maximum character limit

            if (!notesInput.value.trim()) {
                // If the field is empty
                notesInput.setCustomValidity(""); // Clear validation for empty values (optional)
            } else if (!allowedSymbols.test(notesInput.value)) {
                // Check for invalid symbols
                notesInput.setCustomValidity("Only letters, numbers, and these symbols are allowed: $ % / - , . # * Newline is also allowed.");
            } else if (notesInput.value.length > maxLength) {
                // Check for length exceeding the limit
                notesInput.setCustomValidity("Notes cannot exceed 500 characters");
            } else {
                // Everything is valid
                notesInput.setCustomValidity(""); // Reset validation
            }

            notesInput.reportValidity(); // Show validation message

            // Clear the custom validation message when the user starts typing
            notesInput.addEventListener("input", function() {
                notesInput.setCustomValidity("");
            });

            return notesInput.checkValidity(); // Return true if valid, false otherwise
        }


        function validateDateFields(dateElement) {
            let isValid = true; // Track overall validity

            if (!dateElement.value.trim()) {
                dateElement.setCustomValidity("This field is required");
            } else {
                dateElement.setCustomValidity(""); // Reset validation
            }

            dateElement.reportValidity(); // Show validation message

            if (!dateElement.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            dateElement.addEventListener("input", function() {
                dateElement.setCustomValidity(""); // Clear error when user types
            });

            return isValid; // Return validity status
        }


        function validateForm(event) {
            let isValid = true;

            // Validate number fields
            const chargeElements = document.querySelectorAll('#charges-table input[type="number"]');
            chargeElements.forEach((chargeElement) => {
                if (!validateChargeAmount(chargeElement)) {
                    isValid = false;
                }
            });

            // Validate text fields
            const textFields = document.querySelectorAll('input[type="text"]');
            textFields.forEach((textField) => {
                if (!validateTextFields(textField)) {
                    isValid = false;
                }
            });

            // Validate date fields
            const dateFields = document.querySelectorAll('input[type="date"]');
            dateFields.forEach((dateField) => {
                if (!validateDateFields(dateField)) {
                    isValid = false;
                }
            });

            // Validate notes field
            const notesInput = document.querySelector('textarea[name="notes"]');
            if (!validateNotesField(notesInput)) {
                isValid = false;
            }

            // If any field is invalid, prevent form submission
            if (!isValid) {
                event.preventDefault(); // Stop form submission
            }

            return isValid; // Return the overall validity
        }

        function calculateTotal() {
            let total = 0;
            const numberInputs = document.querySelectorAll('#charges-table input[type="number"]');

            numberInputs.forEach(input => {
                if (input.value && !isNaN(input.value)) {
                    total += parseFloat(input.value);
                }
            });

            document.getElementById("total").value = total.toFixed(2);

              // Show SweetAlert2 notification with calculated total
              Swal.fire({
                title: 'Total Calculated',
                text: `The total amount is ${total.toFixed(2)}`,
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }

        function redirection(refnum) {
            if (!refnum || refnum === "") {
                // Using SweetAlert2 for navigation confirmation
                Swal.fire({
                    title: 'Leave this page?',
                    text: "Any unsaved changes will be lost.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, leave page',
                    cancelButtonText: 'Stay here'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "agq_choosedocument.php";
                    }
                });
            } else {
                // Using SweetAlert2 for navigation confirmation
                Swal.fire({
                    title: 'Leave this page?',
                    text: "Any unsaved changes will be lost.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, leave page',
                    cancelButtonText: 'Stay here'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "agq_transactionCatcher.php";
                    }
                });
            }
            return false; // Prevent default link behavior
        }
    </script>

</head>

<body>
    <a href="#" onclick="return redirection('<?php echo htmlspecialchars($refNum, ENT_QUOTES, 'UTF-8'); ?>')" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 20px;">←</a>
    <div class="container">
        <div class="header">SALES INVOICE</div>
        <form method="POST" onsubmit="return validateForm(event);">
            <div class="section">
                <input type="text" maxlength="50" name="to" placeholder="To" value="<?= isset($row['To:']) ? htmlspecialchars($row['To:']) : ''; ?>" onchange="validateTextFields(this)" style="width: 70%">
                <input type="text" maxlength="100" name="address" placeholder="Address" value="<?= isset($row['Address']) ? htmlspecialchars($row['Address']) : ''; ?>" onchange="validateTextFields(this)" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" maxlength="20" name="tin" placeholder="TIN" value="<?= isset($row['Tin']) ? htmlspecialchars($row['Tin']) : ''; ?>" onchange="validateTextFields(this)" style="width: 48%">
                <input type="text" maxlength="30" name="attention" placeholder="Attention" value="<?= isset($row['Attention']) ? htmlspecialchars($row['Attention']) : ''; ?>" onchange="validateTextFields(this)" style="width: 48%">
            </div>
            <div class="section">
                <input type="text" maxlength="30" name="vessel" placeholder="Vessel" value="<?= isset($row['Vessel']) ? htmlspecialchars($row['Vessel']) : ''; ?>" onchange="validateTextFields(this)" style="width: 32%">
                <input type="date" name="eta" value="<?= isset($row['ETA']) ? $row['ETA'] : ''; ?>" onchange="validateDateFields(this)" style="width: 32%">
                <input type="text" maxlength="20" name="refNum" placeholder="Reference No" value="<?= isset($row['RefNum']) ? htmlspecialchars($row['RefNum']) : ''; ?>" onchange="validateTextFields(this)" style="width: 32%">
            </div>
            <div class="section">
                <input type="text" maxlength="25" name="destinationOrigin" placeholder="Destination/Origin" value="<?= isset($row['DestinationOrigin']) ? htmlspecialchars($row['DestinationOrigin']) : ''; ?>" onchange="validateTextFields(this)" style="width: 48%">
                <input type="text" maxlength="25" name="er" placeholder="E.R" value="<?= isset($row['ER']) ? htmlspecialchars($row['ER']) : ''; ?>" style="width: 22%">
                <input type="text" maxlength="25" name="bhNum" placeholder="BL/HBL No" value="<?= isset($row['BHNum']) ? htmlspecialchars($row['BHNum']) : ''; ?>" onchange="validateTextFields(this)" style="width: 22%">
            </div>
            <div class="section">
                <input type="text" maxlength="30" name="natureOfGoods" placeholder="Nature of Goods" value="<?= isset($row['NatureOfGoods']) ? htmlspecialchars($row['NatureOfGoods']) : ''; ?>" onchange="validateTextFields(this)" style="width: 100%">
                <input type="text" maxlength="100" name="packages" placeholder="Packages" value="<?= isset($row['Packages']) ? htmlspecialchars($row['Packages']) : ''; ?>" onchange="validateTextFields(this)" style="width: 32%">
                <input type="text" maxlength="20" name="weight" placeholder="Weight/Measurement" value="<?= isset($row['Weight']) ? htmlspecialchars($row['Weight']) : ''; ?>" onchange="validateTextFields(this)" style="width: 32%">
                <input type="text" maxlength="20" name="volume" placeholder="Volume" value="<?= isset($row['Volume']) ? htmlspecialchars($row['Volume']) : ''; ?>" onchange="validateTextFields(this)" style="width: 32%">
            </div>
            <div class="section radio-group">
                <label>Package Type:</label>
                <label>
                    <input type="radio" id="lcl" name="package" value="LCL" onclick="togglePackageField()" required> LCL
                </label>
                <label>
                    <input type="radio" id="container" name="package" value="Full Container" onclick="togglePackageField()"> Full Container
                </label>
            </div>
            <div class="section" id="package-details">
                <!-- Package details will be populated by JavaScript -->
            </div>
            <div class="table-container">
                <div class="table-header">
                    <span>Reimbursable Charges</span>
                    <span>Amount</span>
                </div>
                <div id="charges-table">
                    <!-- Charges will be populated by JavaScript -->
                </div>
            </div>
            <div class="section">
                <input type="number" id="total" name="total" placeholder="Total" value="<?= isset($row['Total']) ? $row['Total'] : ''; ?>" style="width: 100%" readonly>
                <button type="button" onclick="calculateTotal()" class="calc-btn">Calculate</button>
            </div>
            <div class="section">
                <textarea name="notes" placeholder="Enter notes" onchange="validateNotesField(this)" style="width: 800px; height:100px; flex-direction: column; resize: none;"><?= isset($row['Notes']) ? htmlspecialchars($row['Notes']) : ''; ?></textarea>
            </div>
            <div class="section">
                <input type="text" maxlength="25" name="prepared_by" placeholder="Prepared by" value="<?= isset($row['Prepared_by']) ? htmlspecialchars($row['Prepared_by']) : ''; ?>" onchange="validateTextFields(this)" style="width: 48%">
                <input type="text" maxlength="25" name="approved_by" placeholder="Approved by" value="<?= isset($row['Approved_by']) ? htmlspecialchars($row['Approved_by']) : ''; ?>" onchange="validateTextFields(this)" style="width: 48%">
            </div>
            <div class="footer">
                <input type="submit" name="save" class="save-btn" value="Save">
            </div>

        </form>
    </div>

    <script>
        // Initialize package field on page load if needed
        window.onload = function() {
            // Check if a package type is already selected (useful for edit mode)
            <?php if (isset($row['PackageType']) && $row['PackageType']): ?>
                const packageType = "<?= htmlspecialchars($row['PackageType']); ?>";
                if (packageType === "LCL") {
                    document.getElementById("lcl").checked = true;
                } else if (packageType === "Full Container") {
                    document.getElementById("container").checked = true;
                }
                togglePackageField();
            <?php endif; ?>
        };
    </script>
</body>

</html>