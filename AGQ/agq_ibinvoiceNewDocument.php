<?php
require_once "db_agq.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save'])) {
        insertRecord($conn);
    } 
    // elseif (isset($_POST['select'])) {
    //     selectRecords($conn);
    // } elseif (isset($_POST['delete'])) {
    //     deleteRecord($conn, $_POST['RefNum']);
    // }
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
    
    $sql = "INSERT INTO tbl_impbrk (
        `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, Others, Notes, OceanFreight5,
        BrokerageFee, Vat12, TruckingService,Total, Prepared_by, Approved_by, Edited_by, EditDate, 
        DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssisiiiiisssssss",
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
        $_POST['edited_by'],
        $editDate,
        $docType,        // Session variable
        $companyName,    // Session variable
        $department      // Session variable
    );

    if ($stmt->execute()) {
        // echo "New record inserted successfully!";
        echo '<script>
        if (confirm("Document Successfully Created!\\nDo you want to view it?")) {
            window.location.href = "agq_transactionCatcher.php";
        }
        </script>';
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Function to select records
// function selectRecords($conn)
// {
//     $sql = "SELECT * FROM tbl_impbrk";
//     $stmt = $conn->prepare($sql);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     echo "<h2>Database Records:</h2>";
//     while ($row = $result->fetch_assoc()) {
//         echo "<pre>" . print_r($row, true) . "</pre>";
//     }
//     $stmt->close();
// }

// // Function to delete a record
// function deleteRecord($conn, $RefNum)
// {
//     $sql = "DELETE FROM tbl_impbrk WHERE RefNum = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $RefNum);

//     if ($stmt->execute()) {
//         echo "Record deleted successfully!";
//     } else {
//         echo "Error deleting record: " . $stmt->error;
//     }
//     $stmt->close();
// }

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../css/forms.css">
    <title>Sales Invoice </title>

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
                        <input type="number" name="${inputName}" placeholder="Enter amount" onchange ="validateChargeAmount(this)">
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
                <input type="number" name="${inputName}" placeholder="Enter amount" onchange="validateChargeInput(this)">
                <button onclick="removeCharge(this)">Remove</button>
            `;
    
            chargesTable.appendChild(newRow);
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
            const allowedSymbols = /^[a-zA-Z0-9!@$%^&()_+\-:/|,~ ]+$/; // Allow letters, numbers, and symbols
            const reverseTinRegex = /^(?!^[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}$).+$/; // Correct regex for TIN format (0000-0000-0000-0000)
            let isValid = true; // Track overall validity

            if (textElement.name === "tin") {
                // Check TIN-specific validation
                if (!textElement.value.trim()) {
                    textElement.setCustomValidity("This field is required");
                } else if (reverseTinRegex.test(textElement.value)) {
                    textElement.setCustomValidity("TIN format is invalid. Correct format: 0000-0000-0000-0000");
                } else {
                    textElement.setCustomValidity(""); // Reset validation
                }
            } else {
                if (!textElement.value.trim()) {
                    textElement.setCustomValidity("This field is required");
                } else if (!allowedSymbols.test(textElement.value)) {
                    textElement.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
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
            const allowedSymbols = /^[a-zA-Z0-9!.@$%^&()_+\-:/|,~ \r\n]*$/; // Allow letters, numbers, symbols, and line breaks
            const maxLength = 500; // Maximum character limit
            let isValid = true; // Track overall validity

            if (!allowedSymbols.test(notesInput.value)) {
                notesInput.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
            } else if (notesInput.value.length > maxLength) {
                notesInput.setCustomValidity("Notes cannot exceed 500 characters");
            } else {
                notesInput.setCustomValidity(""); // Reset validation
            }

            notesInput.reportValidity(); // Show validation message

            if (!notesInput.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            notesInput.addEventListener("input", function () {
                notesInput.setCustomValidity(""); // Clear error when user types
            });
            
            return isValid; // Return validity status
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
    </script>
        
</head>
<body>
<a href="agq_choosedocument.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 20px;">←</a>
<div class="container">
        <div class="header">SALES INVOICE</div>
        <form method="POST" onsubmit="return validateForm(event);">
            <div class="section">
                <input type="text" maxlength="50" name="to" placeholder="To" onchange="validateTextFields(this)" style="width: 70%">
                <input type="date" name="date" placeholder="Date" onchange="validateDateFields(this)" style="width: 28%">
            </div>
            <div class="section">
                <input type="text" maxlength="100" name="address" placeholder="Address" onchange="validateTextFields(this)" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" maxlength="20" name="tin" placeholder="TIN" onchange="validateTextFields(this)" style="width: 48%">
                <input type="text" maxlength="30" name="attention" placeholder="Attention" onchange="validateTextFields(this)" style="width: 48%">
            </div>
            <div class="section">
                <input type="text" maxlength="30" name="vessel" placeholder="Vessel" onchange="validateTextFields(this)" style="width: 32%">
                <input type="date" name="eta" placeholder="ETD/ETA" onchange="validateDateFields(this)" style="width: 32%">
                <input type="text" maxlength="20" name="refNum" placeholder="Reference No" onchange="validateTextFields(this)" style="width: 32%">
            </div>
            <div class="section">
                <input type="text" maxlength="25" name="destinationOrigin" placeholder="Destination/Origin" onchange="validateTextFields(this)" style="width: 48%">
                <input type="text" maxlength="25" name="er" placeholder="E.R" onchange="validateTextFields(this)" style="width: 22%">
                <input type="text" maxlength="25" name="bhNum" placeholder="BL/HBL No" onchange="validateTextFields(this)" style="width: 22%">
            </div>
            <div class="section">
                <input type="text" maxlength="30" name="natureOfGoods" placeholder="Nature of Goods" onchange="validateTextFields(this)" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" maxlength="100" name="packages" placeholder="Packages" onchange="validateTextFields(this)" style="width: 32%">
                <input type="text" maxlength="20" name="weight" placeholder="Weight/Measurement" onchange="validateTextFields(this)" style="width: 32%">
                <input type="text" maxlength="20" name="volume" placeholder="Volume" onchange="validateTextFields(this)" style="width: 32%">
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
                <input type="number" id="total" name="total" placeholder="Total" style="width: 100%" readonly>
                <button type="button" onclick="calculateTotal()" class="calc-btn">Calculate</button>
            </div>
            <div class="section">
                    <textarea name="notes" placeholder="Enter notes" onchange="validateNotesField(this)" style="width: 800px; height:100px; flex-direction: column; resize: none;"></textarea>
            </div>
            <div class="section">
                <input type="text" maxlength="25" name="prepared_by" placeholder="Prepared by" onchange="validateTextFields(this)" style="width: 48%">
                <input type="text" maxlength="25" name="approved_by" placeholder="Approved by" onchange="validateTextFields(this)" style="width: 48%">
                <input type="text" maxlength="25" name="edited_by" placeholder="Edited by" onchange="validateTextFields(this)" style="width: 48%">
            </div>
            <div class="footer">
                <!-- <button class="save-btn">Save</button> -->
                <input type="submit" name="save" class="save-btn" value="Save">
            </div>
                
        </form>
    </div>
</body>
</html>
