<?php
require 'db_agq.php';

session_start();



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save'])) {
        insertRecord($conn);
    } elseif (isset($_POST['select'])) {
        selectRecords($conn);
    } elseif (isset($_POST['delete'])) {
        deleteRecord($conn, $_POST['refNum']);
    }
}

// Function to insert a record
function insertRecord($conn)
{
    $docType = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : null;
    $department = isset($_SESSION['department']) ? $_SESSION['department'] : null;
    $companyName = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : null;
    date_default_timezone_set('Asia/Manila');
    $editDate = date('Y-m-d');

    $sql = "INSERT INTO tbl_impfwd (
        `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, OceanFreight95,
        Documentation, TurnOverFee, Handling, Others, Notes, FCLCharge, 
        BLFee, ManifestFee, THC, CIC, ECRS, PSS, Origin, ShippingLine, ExWorkCharges, Total, 
        Prepared_by, Approved_by, Edited_by, EditDate, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssiiiiisiiiiiiiiiiisssssss",
        $_POST['to'],
        $_POST['address'],
        $_POST['tin'],
        $_POST['attention'],
        $_POST['date'],
        $_POST['vessel'],
        $_POST['eta'],
        $_POST['referenceNo'],
        $_POST['destinationOrigin'],
        $_POST['er'],
        $_POST['bhNo'],
        $_POST['natureofGoods'],
        $_POST['packages'],
        $_POST['weight'],
        $_POST['volume'],
        $_POST['package'],
        $_POST['95oceanfreight'],
        $_POST['documentation'],
        $_POST['turnoverfee'],
        $_POST['handling'],
        $_POST['others_amount'],
        $_POST['notes'],
        $_POST['fclcharges'],
        $_POST['blfee'],
        $_POST['manifestfee'],
        $_POST['thc'],
        $_POST['cic'],
        $_POST['ecrs'],
        $_POST['pss'],
        $_POST['origin_amount'],
        $_POST['shippinglinecharges_amount'],
        $_POST['ex-workcharges_amount'],
        $_POST['total'],
        $_POST['preparedBy'],
        $_POST['approvedBy'],
        $_POST['editedBy'],
        $editDate,
        $docType,        // Session variable
        $companyName,    // Session variable
        $department      // Session variable
    );

    if ($stmt->execute()) {
        // echo "New record inserted successfully!";
        echo '<script>
        if (confirm("Document Successfully Created!\\nDo you want to view it?")) {
            window.location.href = "agq_employTransactionView.php";
        }
            </script>';
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Function to select records
function selectRecords($conn)
{
    $sql = "SELECT * FROM your_table";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "RefNum: " . $row["RefNum"] . " - To: " . $row["To"] . " - Address: " . $row["Address"] . "<br>";
        }
    } else {
        echo "0 results";
    }
}

// Function to delete a record
function deleteRecord($conn, $refNum)
{
    $sql = "DELETE FROM your_table WHERE RefNum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $refNum);

    if ($stmt->execute()) {
        echo "Record deleted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
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
                    "BL Fee",
                    "Manifest Fee",
                    "THC",
                    "CIC",
                    "ECRS",
                    "PSS",
                    "Additional Charges" // LCL-specific
                ];
                generateFixedCharges(lclCharges, true); // true = LCL mode
            } else if (containerSelected) {
                const containerCharges = [
                    "95 Ocean Freight",
                    "Handling",
                    "Turn Over Fee",
                    "BL Fee",
                    "FCL Charges",
                    "Documentation",
                    "Manifest Fee",
                    "Additional Charges" // Full container-specific
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
                                ? '<option value="Others">Others</option><option value="Origin">Origin</option>' 
                                : '<option value="Others">Others</option><option value="ShippingLineCharges">Shipping Line Charges</option><option value="Ex-WorkCharges">Ex-Work Charges</option>'
                            }
                        </select>
                    `;
                } else {

                    const inputName = charge.toLowerCase().replace(/\s+/g, '').replace('/', '');
                    row.innerHTML = `
                        <input type="text" name="charge_type[]" value="${charge}" readonly>
                        <input type="number" name="${inputName}" placeholder="Enter amount">
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
            let inputName = selectedCharge.toLowerCase().replace(/\s+/g, '').replace('/', '') + "_amount";

            // Add row HTML
            newRow.innerHTML = `
                <div class="charges">
                    <div class="col">
                        <input type="text" value="${selectedCharge}" readonly style="width:360px; flex-direction: column">
                        <input type="number" name="${inputName}" placeholder="Enter amount" style="width:288px; flex-direction: column" onchange="validateChargeInput(this)">
                        <button type="button" onclick="removeCharge(this)">Remove</button>
                    </div>
                </div>
            `;

            chargesTable.appendChild(newRow); // Append the new row to the table

            // Reset the dropdown value to allow reselecting the same charge
            selectElement.value = ""; // Clears the dropdown selection after adding a charge
        }

        function removeCharge(button) {
            const row = button.closest(".table-row");
            if (row) {
                row.remove(); // Remove the entire charge row
            }
        }


        function validateChargeInput(inputElement) {
            const maxAmount = 16500000; // Set a max allowable amount
            const colContainer = inputElement.closest(".col");
            const errorElementId = `${inputElement.name}-error`; // Unique error element ID
            let errorElement = colContainer.nextElementSibling;

            // Create the error element if it doesn't exist
            if (!errorElement) {
                errorElement = document.createElement("div");
                errorElement.id = errorElementId;
                errorElement.className = "invalid-feedback";
                errorElement.style.marginTop = "5px";
                errorElement.style.display = "none";
                colContainer.insertAdjacentElement("afterend", errorElement);
            }

            // Validate input value
            const value = parseFloat(inputElement.value) || 0; // Default to 0 if empty
            if (value > maxAmount) {
                inputElement.classList.add("is-invalid");
                errorElement.innerHTML = `*Value cannot exceed ${maxAmount.toLocaleString()}`;
                errorElement.style.display = "block"; // Show error message
            } else {
                inputElement.classList.remove("is-invalid");
                errorElement.style.display = "none"; // Hide error message
            }
        }

        function validateChargeAmount() {
            const inputs = document.querySelectorAll('input[type="number"]');
            const maxAmount = 16500000;
            let isValid = true;

            inputs.forEach(input => {
                const colContainer = input.closest(".col");
                if (!colContainer) {
                    console.error("Error: .col container not found for input", input);
                    return;
                }

                // Check for error div or create it dynamically
                let errorDiv = colContainer.nextElementSibling;
                if (!errorDiv) {
                    errorDiv = document.createElement("div");
                    errorDiv.id = `${input.name}-error`;
                    errorDiv.className = "invalid-feedback";
                    errorDiv.style.marginTop = "5px";
                    errorDiv.style.display = "none";
                    colContainer.insertAdjacentElement("afterend", errorDiv);
                }

                // Perform validation
                const value = parseFloat(input.value) || 0; // Default to 0 if input is empty
                if (value > maxAmount) {
                    input.classList.add("is-invalid");
                    errorDiv.innerHTML = `*Value cannot exceed ${maxAmount.toLocaleString()}`;
                    errorDiv.style.display = "block";
                    isValid = false;
                } else {
                    input.classList.remove("is-invalid");
                    errorDiv.style.display = "none";
                }
            });

            return isValid;
        }

        function validateTextFields() {
            const inputs = document.querySelectorAll('input[type="text"]'); // Select all text inputs
            const allowedSymbols = /^[a-zA-Z0-9!@$%^&()_+\-:/|,~ ]+$/; // Allow letters, numbers, and symbols
            let isValid = true; // Track overall validity

            inputs.forEach(input => {
                // // Exclude the readonly input and the one named "notes"
                // if (input.readOnly || input.name === "notes") {
                //     return; // Skip validation for these inputs
                // }

                // const errorElementId = input.name + "-error"; // Unique error element ID
                // let errorElement = input.nextElementSibling; // Locate the error element directly below the input

                // // Create an error element dynamically if it doesn't exist
                // if (!errorElement || errorElement.className !== "invalid-feedback") {
                //     errorElement = document.createElement("div");
                //     errorElement.id = errorElementId;
                //     errorElement.className = "invalid-feedback";
                //     input.insertAdjacentElement("afterend", errorElement); // Place the error element below the input
                // }

                // // Check if the field is empty
                // if (input.value.trim() === "") {
                //     input.classList.add("is-invalid"); // Add invalid class to input
                //     errorElement.innerHTML = "*This field is required"; // Set error message
                //     errorElement.style.display = "block"; // Show error element
                //     isValid = false; // Mark form as invalid
                // } 
                // // Check if the input contains only allowed symbols, letters, or numbers
                // else if (!allowedSymbols.test(input.value)) {
                //     input.classList.add("is-invalid"); // Add invalid class
                //     const errorText = "*Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~";
                //     errorElement.innerHTML = errorText; // Set error message
                //     errorElement.style.display = "block"; // Show error element
                //     isValid = false; // Mark form as invalid
                // } else {
                //     input.classList.remove("is-invalid"); // Remove invalid class
                //     errorElement.style.display = "none"; // Hide error element
                // }

                if (!input.value.trim()) {
                    input.setCustomValidity("This field is required");
                    } else if (!allowedSymbols.test(input.value)) {
                    input.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
                    } else {
                    input.setCustomValidity(""); // Reset validation
                    }

                    input.reportValidity(); // Show validation message

                    if (!input.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                    }

                    input.addEventListener("input", function () {
                    input.setCustomValidity(""); // Clear error when user types
                    });
            });

            return isValid; // Return validity status
        }


        function validateNotesField(notesInput) {
            const allowedSymbols = /^[a-zA-Z0-9!.@$%^&()_+\-:/|,~ \r\n]*$/; // Allow letters, numbers, symbols, and line breaks
            const maxLength = 255; // Maximum character limit
            let isValid = true; // Track overall validity

            const errorElementId = notesInput.name + "-error"; // Unique error element ID
            let errorElement = notesInput.nextElementSibling; // Locate the error element directly below the input

            // Create an error element dynamically if it doesn't exist
            if (!errorElement || errorElement.className !== "invalid-feedback") {
                errorElement = document.createElement("div");
                errorElement.id = errorElementId;
                errorElement.className = "invalid-feedback";
                notesInput.insertAdjacentElement("afterend", errorElement); // Place the error element below the input
            }

            // Validate the length
            if (notesInput.value.length > maxLength) {
                notesInput.classList.add("is-invalid"); // Add invalid class
                errorElement.innerHTML = `*Notes cannot exceed ${maxLength} characters`; // Set error message
                errorElement.style.display = "block"; // Show error message
                isValid = false; // Mark as invalid
            } 
            // Validate allowed symbols (including line breaks)
            else if (!allowedSymbols.test(notesInput.value)) {
                notesInput.classList.add("is-invalid");
                errorElement.innerHTML = "*Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~"; // Error message
                errorElement.style.display = "block";
                isValid = false;
            } else {
                notesInput.classList.remove("is-invalid"); // Remove invalid class
                errorElement.style.display = "none"; // Hide error message
            }

            return isValid; // Return validity status
        }

        function validateDateFields() {
        const dateInputs = document.querySelectorAll('input[type="date"]'); // Select all date inputs
        let isValid = true; // Track overall validity

        dateInputs.forEach(input => {
            const errorElementId = input.name + "-error"; // Unique error element ID
            let errorElement = input.nextElementSibling; // Locate the error element directly below the input

            // Create an error element dynamically if it doesn't exist
            if (!errorElement || errorElement.className !== "invalid-feedback") {
                errorElement = document.createElement("div");
                errorElement.id = errorElementId;
                errorElement.className = "invalid-feedback";
                input.insertAdjacentElement("afterend", errorElement); // Place the error element below the input
            }

            // Check if the field is empty
            if (input.value.trim() === "") {
                input.classList.add("is-invalid"); // Add invalid class to input
                errorElement.innerHTML = "*This field is required"; // Set error message
                errorElement.style.display = "block"; // Show error element
                isValid = false; // Mark as invalid
            } else {
                input.classList.remove("is-invalid"); // Remove invalid class
                errorElement.style.display = "none"; // Hide error element
            }
        });

        return isValid; // Return validity status
    }


    function validateForm() {
        const numberFieldsValid = validateChargeAmount();
        const textFieldsValid = validateTextFields();
        const dateFieldValid = validateDateFields();

        // Select the notes textarea
        const notesInput = document.querySelector('textarea[name="notes"]');
        const notesFieldValid = validateNotesField(notesInput);

        return numberFieldsValid && textFieldsValid && dateFieldValid && notesFieldValid;
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
        <div class="header">STATEMENT OF ACCOUNT</div>
        <form method="POST" onsubmit="return validateForm();">
            <div class="section">
                <input type="text" name="to" placeholder="To" style="width: 70%">
                <input type="date" name="date" placeholder="Date" style="width: 28%">
            </div>
            <div class="section">
                <input type="text" name="address" placeholder="Address" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" name="tin" placeholder="TIN" style="width: 48%">
                <input type="text" name="attention" placeholder="Attention" style="width: 48%">
            </div>
            <div class="section">
                <input type="text" name="vessel" placeholder="Vessel" style="width: 32%">
                <input type="date" name="eta" placeholder="ETD/ETA" style="width: 32%">
                <input type="text" name="referenceNo" placeholder="Reference No" style="width: 32%">
            </div>
            <div class="section">
                <input type="text" name="destinationOrigin" placeholder="Destination/Origin" style="width: 48%">
                <input type="text" name="er" placeholder="E.R" style="width: 22%">
                <input type="text" name="bhNo" placeholder="BL/HBL No" style="width: 22%">
            </div>
            <div class="section">
                <input type="text" name="natureofGoods" placeholder="Nature of Goods" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" name="packages" placeholder="Packages" style="width: 32%">
                <input type="text" name="weight" placeholder="Weight/Measurement" style="width: 32%">
                <input type="text" name="volume" placeholder="Volume" style="width: 32%">
            </div>
            <div class="section radio-group">
                <label>Package Type:</label>
                <label>
                    <input type="radio" id="lcl" name="package" value="LCL" onclick="togglePackageField()"> LCL
                </label>
                <label>
                    <input type="radio" id="container" name="package" value="Full Container" onclick="togglePackageField()"> Full Container
                </label>
            </div>
            <div class="section" id="package-details">
                <!-- <input type="text" placeholder="Enter package details" style="width: 100%"> -->
            </div>
            <div class="table-container">
                <div class="table-header">
                    <span>Reimbursable Charges</span>
                    <span>Amount</span>
                </div>
                <div id="charges-table"></div>
            </div>
            <div class="section">
                <input type="number" id="total" name="total" placeholder="Total" style="width: 100%" readonly>
                <button type="button" onclick="calculateTotal()" class="calc-btn">Calculate</button>
            </div>
            <div class="section">
                    <textarea name="notes" placeholder="Enter notes" onchange="validateNotesField(this)" style="width: 800px; height:100px; flex-direction: column; resize: none;"></textarea>
                </div>
            <div class="section">
                <input type="text" name="preparedBy" placeholder="Prepared by" style="width: 48%">
                <input type="text" name="approvedBy" placeholder="Approved by" style="width: 48%">
                <input type="text" name="editedBy" placeholder="Edited by" style="width: 24%">
            </div>
            <div class="footer">
                <!-- <button class="save-btn">Save</button> -->
                <input type="submit" name="save" class="save-btn" value="Save">
            </div>
        </form>
    </div>
</body>

</html>