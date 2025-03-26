<?php
require 'db_agq.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_GET['refNum']) && !empty($_GET['refNum'])) {
        $docs = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : '';
        updateRecord($conn, $_POST, [
            "editDate" => date("Y-m-d H:i:s"),
            "companyName" => $_SESSION['Company_name'],
            "department" => $_SESSION['department']
        ]);
    } elseif (isset($_POST['save'])) {
        insertRecord($conn);
    }
    // elseif (isset($_POST['select'])) {
    //     selectRecords($conn);
    // } elseif (isset($_POST['delete'])) {
    //     deleteRecord($conn, $_POST['RefNum']);
    // }
}

if (isset($_GET['refNum'])) {
    $refNum = $_GET['refNum'];

    $sql = "SELECT PackageType FROM tbl_expfwd WHERE RefNum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $refNum);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $packageType = $row['PackageType'] ?? '';
}

if (isset($_GET['refNum'])) {

    $refNum = $_GET['refNum'];
    $sql = "SELECT * FROM tbl_expfwd WHERE RefNum LIKE ?";
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
    $docs = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : '';
    $sql = "UPDATE tbl_expfwd SET 
        `To:` = ?, 
        `Address` = ?, 
        Tin = ?, 
        Attention = ?, 
        `Date` = ?, 
        Vessel = ?, 
        ETA = ?, 
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
        OceanFreight95 = ?,  
        DocsFee = ?,  
        LCLCharge = ?,  
        ExportProcessing = ?,  
        FormsStamps = ?,  
        ArrastreWharf = ?,  
        E2MLodge = ?,  
        THC = ?,  
        FAF = ?,  
        SealFee = ?,  
        Storage = ?,  
        Telex = ?,  
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
        "sssssssssssssssisiiiiiiiiiiiiissssssss",
        $data['to'],
        $data['address'],
        $data['tin'],
        $data['attention'],
        $data['date'],
        $data['vessel'],
        $data['eta'],
        $data['destinationOrigin'],
        $data['er'],
        $data['bhNum'],
        $data['natureOfGoods'],
        $data['packages'],
        $data['weight'],
        $data['measurement'],
        $data['package'],
        $data['others_amount'],
        $data['notes'],
        $data['95oceanfreight'],
        $data['docsfee'],
        $data['lclcharge'],
        $data['exportprocessing'],
        $data['customsformsstamps'],
        $data['arrastrewharfage'],
        $data['e2mfee'],
        $data['thc'],
        $data['faf'],
        $data['sealfee'],
        $data['storage'],
        $data['telexfee'],
        $data['total'],
        $data['prepared'],
        $data['approved'],
        $data['edited'],
        $sessionData['editDate'],
        $docs,
        $sessionData['companyName'],
        $sessionData['department'],
        $data['refNum']
    );

    if ($stmt->execute()) {
        ?>'<script>
        if (confirm("Document Successfully Edited!\\nReturn to Transactions Page?")) {
            window.location.href = "agq_transactionCatcher.php";
        }
            </script>'
        <?php
        return;
    } else {
        return "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

// Function to insert a record
function insertRecord($conn)
{
    $docType = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : null;
    $department = isset($_SESSION['department']) ? $_SESSION['department'] : null;
    $companyName = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : null;
    date_default_timezone_set('Asia/Manila');
    $editDate = date('Y-m-d');

    $refNum = $_POST['refNum'];
    $checkSql = "SELECT RefNum FROM tbl_expbrk WHERE RefNum = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $refNum);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo '<script>alert("Reference Number already exist. Please create the document again.");</script>';
        $checkStmt->close();
        return; // Stop execution if RefNum exists
    }

    $checkStmt->close();

    $sql = "INSERT INTO tbl_expfwd (
        `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, OceanFreight95, Others, Notes,
        DocsFee, LCLCharge, ExportProcessing, FormsStamps, ArrastreWharf, E2MLodge, THC, FAF, SealFee, Storage, Telex,
        Total, Prepared_by, Approved_by, Edited_by, EditDate, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssiisiiiiiiiiiiiisssssss",
        $_POST['to'],
        $_POST['address'],
        $_POST['tin'],
        $_POST['attention'],
        $_POST['date'],
        $_POST['vessel'],
        $_POST['eta'],
        $_POST['refNum'],
        $_POST['destinationOrigin'],
        $_POST['er'],
        $_POST['bhNum'],
        $_POST['natureOfGoods'],
        $_POST['packages'],
        $_POST['weight'],
        $_POST['measurement'],
        $_POST['package'],
        $_POST['95oceanfreight'],
        $_POST['others_amount'],
        $_POST['notes'],
        $_POST['docsfee'],
        $_POST['lclcharge'],
        $_POST['exportprocessing'],
        $_POST['customsformsstamps'],
        $_POST['arrastrewharfage'],
        $_POST['e2mfee'],
        $_POST['thc'],
        $_POST['faf'],
        $_POST['sealfee'],
        $_POST['storage'],
        $_POST['telexfee'],
        $_POST['total'],
        $_POST['prepared'],
        $_POST['approved'],
        $_POST['edited'],
        $editDate,
        $docType,        // Session variable
        $companyName,    // Session variable
        $department      // Session variable
    );

    if ($stmt->execute()) {
        echo '<script>
        if (confirm("Document Successfully Created!\\nReturn to Transactions Page?")) {
            window.location.href = "agq_transactionCatcher.php";
        }
            </script>';
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Function to select all records
// function selectRecords($conn)
// {
//     $sql = "SELECT * FROM tbl_expfwd";
//     $stmt = $conn->prepare($sql);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     echo "<h2>Database Records:</h2>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<pre>" . print_r($row, true) . "</pre>";
//     }
//     $stmt->close();
// }

// // Function to delete a record by RefNum
// function deleteRecord($conn, $refNum)
// {
//     $sql = "DELETE FROM tbl_expfwd WHERE RefNum = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $refNum);

//     if ($stmt->execute()) {
//         echo "Record deleted successfully!";
//     } else {
//         echo "Error: " . $stmt->error;
//     }
//     $stmt->close();
// }

//$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../css/forms.css">
    <title>Statement of Account</title>
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
                    "95 Ocean Freight",
                    "Docs Fee",
                    "LCL Charge",
                    "Export Processing",
                    "Customs Forms/Stamps",
                    "Arrastre/Wharfage",
                    "E2M Fee",
                    "Additional Charges"
                ];
                generateFixedCharges(lclCharges, true);
            } else if (containerSelected) {
                const containerCharges = [
                    "95 Ocean Freight",
                    "THC",
                    "Docs Fee",
                    "FAF",
                    "Seal Fee",
                    "Storage",
                    "Telex Fee",
                    "Additional Charges"
                ];
                generateFixedCharges(containerCharges, false);
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
                                : '<option value="Others">Others</option>'
                            }
                        </select>
                    `;
                } else {

                    const inputName = charge.toLowerCase().replace(/\s+/g, '').replace('/', '');
                    row.innerHTML = `
                        <input type="text" name="charge_type[]" value="${charge}" readonly>
                        <input type="number" name="${inputName}" placeholder="Enter amount" onchange ="validateChargeAmount(this)">
                    `;
                }

                chargesTable.appendChild(row);
            });
        }

        function handleChargeSelection(selectElement, isLCL) {
            const selectedCharge = selectElement.value;
            if (!selectedCharge) return; // Do nothing if no valid selection

            // Check if charge already exists
            if (document.querySelector(`.added-charge[data-charge="${selectedCharge}"]`)) {
                return; // Prevent duplicates
            }

            // Create new charge row
            const chargesTable = document.getElementById("charges-table");
            const newRow = document.createElement("div");
            newRow.className = "table-row added-charge";
            newRow.dataset.charge = selectedCharge; // Store charge type

            // Set input name correctly
            let inputName = selectedCharge.toLowerCase() + "_amount";

            newRow.innerHTML = `
                <input type="text" value="${selectedCharge}" readonly>
                <input type="number" name="${inputName}" placeholder="Enter amount" onchange="validateChargeInput(this)">
                <button type="button" onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);

            selectElement.value = ""; // Clears the dropdown selection after adding a charge
        }

        function removeCharge(button) {
            button.parentElement.remove(); // Remove the selected charge row
        }

        function validateChargeInput(inputElement) {
            const maxAmount = 16500000; 
            const value = parseFloat(inputElement.value) || 0;

            if (value > maxAmount) {
                inputElement.setCustomValidity("Value cannot exceed 16,500,000");
            } else {
                inputElement.setCustomValidity(""); // Reset validation
            }

            inputElement.reportValidity(); // Show validation message

            if (!inputElement.checkValidity()) {
                inputElement.preventDefault(); // Prevent form submission if invalid
            }

            inputElement.addEventListener("input", function () {
                inputElement.setCustomValidity(""); // Clear error when user types
            });
        }

        function validateChargeAmount(chargeElement) {
            const maxAmount = 16500000;
            let isValid = true;

            const value = parseFloat(chargeElement.value) || 0;
                
                if (value > maxAmount) {
                    chargeElement.setCustomValidity("Value cannot exceed 16,500,000");
                } else {
                    chargeElement.setCustomValidity(""); // Reset validation
                }

                chargeElement.reportValidity(); // Show validation message

                if (!chargeElement.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                chargeElement.addEventListener("input", function () {
                    chargeElement.setCustomValidity(""); // Clear error when user types
                });

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

            textElement.addEventListener("input", function () {
                textElement.setCustomValidity(""); // Clear error when user types
            });

            return isValid; // Return validity status
        }

        function validateNotesField(notesInput) {
            const allowedSymbols = /^[a-zA-Z0-9\$%\-\/\., ]+$/; // Allow letters, numbers, and only $ % / . , -
            const maxLength = 500; // Maximum character limit

            if (!notesInput.value.trim()) {
                // If the field is empty
                notesInput.setCustomValidity(""); // Clear validation for empty values (optional)
            } else if (!allowedSymbols.test(notesInput.value)) {
                // Check for invalid symbols
                notesInput.setCustomValidity("Only letters, numbers, and these symbols are allowed: $ % / - , .");
            } else if (notesInput.value.length > maxLength) {
                // Check for length exceeding the limit
                notesInput.setCustomValidity("Notes cannot exceed 500 characters");
            } else {
                // Everything is valid
                notesInput.setCustomValidity(""); // Reset validation
            }

            notesInput.reportValidity(); // Show validation message

            // Clear the custom validation message when the user starts typing
            notesInput.addEventListener("input", function () {
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

            dateElement.addEventListener("input", function () {
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
    }

    function redirection(refnum) {
            if (!refnum || refnum === "") {
                window.location.href = "agq_choosedocument.php";
            } else {
                window.location.href = "agq_transactionCatcher.php";
            }
        }

    </script>

</head>

<body>
<a href="#" onclick="redirection('<?php echo $refNum; ?>')"  style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 20px;">←</a>
    <div class="container">
        <div class="header">STATEMENT OF ACCOUNT</div>
        <form method="POST" onsubmit="return validateForm(event);">
        <div class="section">
                <input type="text" maxlength="50" name="to" placeholder="To" value="<?= isset($row['To:']) ? htmlspecialchars($row['To:']) : ''; ?>" onchange="validateTextFields(this)" style="width: 70%">
                <input type="date" name="date" value="<?= isset($row['Date']) ? $row['Date'] : ''; ?>" onchange="validateDateFields(this)" style="width: 28%">
            </div>
            <div class="section">
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
            </div>
            <div class="section">
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
                <input type="text" maxlength="25" name="edited_by" placeholder="Edited by" value="<?= isset($row['Edited_by']) ? htmlspecialchars($row['Edited_by']) : ''; ?>" onchange="validateTextFields(this)" style="width: 48%">
            </div>
            <div class="footer">
                <!-- <button class="save-btn">Save</button> -->
                <input type="submit" name="save" class="save-btn" value="Save">
            </div>
        </form>
    </div>
</body>

</html>