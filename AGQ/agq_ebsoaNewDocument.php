<?php
require 'db_agq.php';

session_start();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save'])) {
        insertRecord($conn);
    }
    // elseif (isset($_POST['select'])) {
    //     selectRecords($conn);
    // } elseif (isset($_POST['delete'])) {
    //     deleteRecord($conn, $_POST['refNum']);
    // }
}

// Function to insert a record
function insertRecord($conn)
{
    $department = isset($_SESSION['department']) ? $_SESSION['department'] : null;
    $companyName = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : null;
    $docType = isset($_SESSION['selected_documenttype']) ? $_SESSION['selected_documenttype'] : null;

    date_default_timezone_set('Asia/Manila');
    $editDate = date('Y-m-d');

    $sql = "INSERT INTO tbl_expbrk (
        `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, Others, Notes, OceanFreight95,
        AdvanceShipping, Processing, Arrastre, Wharfage, FormsStamps, PhotocopyNotarial,
        Documentation, E2MLodge, ManualStuffing, Handling, PCCI, Total, Prepared_by, Approved_by, 
        Edited_by, EditDate, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssisiiiiiiiiiiiiisssssss",
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
        $_POST['packageType'],
        $_POST['others_amount'],
        $_POST['notes'],
        $_POST['95oceanfreight'],
        $_POST['advanceshippinglines'],
        $_POST['processing'],
        $_POST['arrastre'],
        $_POST['wharfage'],
        $_POST['formsstamps'],
        $_POST['photocopynotarial'],
        $_POST['documentation'],
        $_POST['e2mlodgement'],
        $_POST['stuffing'],
        $_POST['handling'],
        $_POST['pcci_amount'],
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
            window.location.href = "agq_employDocumentView.php";
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
//     $sql = "SELECT * FROM your_table";
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
//     $sql = "DELETE FROM your_table WHERE RefNum = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $refNum);

//     if ($stmt->execute()) {
//         echo "Record deleted successfully!";
//     } else {
//         echo "Error: " . $stmt->error;
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
                    "Advance Shipping Lines",
                    "Processing",
                    "Additional Charges"
                ];
                generateFixedCharges(lclCharges);
            } else if (containerSelected) {
                const containerCharges = [
                    "95 Ocean Freight",
                    "Arrastre",
                    "Wharfage",
                    "Processing",
                    "Forms/Stamps",
                    "Photocopy/Notarial",
                    "Documentation",
                    "E2M Lodgement",
                    "Stuffing",
                    "Handling",
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
                                ? '<option value="Others">Others</option><option value="PCCI">PCCI</option>' 
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

                    // <div class="charges">
                        //     <div class = "col">
                        //         <input type="text" name="charge_type[]" value="${charge}" readonly style="width:360px; flex-direction: column">
                        //         <input type="number" name="${inputName}" id = "${inputName}" placeholder="Enter amount" style="width:360px; flex-direction: column">
                        //     </div>
                    // </div>

                    // const chargesContainer = row.querySelector(".charges");
                    // const colContainer = chargesContainer.querySelector(".col");

                    // // Create the error message div
                    // const errorDiv = document.createElement("div");
                    // errorDiv.id = `${inputName}-error`; // Unique error element ID
                    // errorDiv.className = "invalid-feedback"; // Styling for the error div
                    // errorDiv.style.marginTop = "5px"; // Add spacing between .col and error message
                    // errorDiv.style.display = "none"; // Initially hidden
                    // errorDiv.innerHTML = `*Error message placeholder for ${charge}`; // Example error message

                    // // Insert the error div after .col
                    // colContainer.insertAdjacentElement("afterend", errorDiv);

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
                <input type="text" value="${selectedCharge}" readonly >
                <input type="number" name="${inputName}" placeholder="Enter amount" onchange="validateChargeInput(this)">
                <button type="button" onclick="removeCharge(this)">Remove</button>
            `;

            // <div class="charges">
            //         <div class="col">
            //             <input type="text" value="${selectedCharge}" readonly style="width:360px; flex-direction: column">
            //             <input type="number" name="${inputName}" placeholder="Enter amount" style="width:288px; flex-direction: column" onchange="validateChargeInput(this)">
            //             <button type="button" onclick="removeCharge(this)">Remove</button>
            //         </div>
            //     </div>

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
            // const colContainer = inputElement.closest(".col");
            //const errorElementId = `${inputElement.name}-error`; // Unique error element ID
            // let errorElement = colContainer.nextElementSibling;
            const value = parseFloat(inputElement.value) || 0;

            // Create the error element if it doesn't exist
            // if (!errorElement) {
            //     errorElement = document.createElement("div");
            //     errorElement.id = errorElementId;
            //     errorElement.className = "invalid-feedback";
            //     errorElement.style.marginTop = "5px";
            //     errorElement.style.display = "none";
            //     colContainer.insertAdjacentElement("afterend", errorElement);
            // }

            // // Validate input value
            // const value = parseFloat(inputElement.value) || 0; // Default to 0 if empty
            // if (value > maxAmount) {
            //     inputElement.classList.add("is-invalid");
            //     errorElement.innerHTML = `*Value cannot exceed ${maxAmount.toLocaleString()}`;
            //     errorElement.style.display = "block"; // Show error message
            // } else {
            //     inputElement.classList.remove("is-invalid");
            //     errorElement.style.display = "none"; // Hide error message
            // }

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
            //const inputs = document.querySelectorAll('input[type="number"]');
            const maxAmount = 16500000;
            let isValid = true;

           // inputs.forEach(input => {
                const value = parseFloat(chargeElement.value) || 0;
                // if (!colContainer) {
                //     console.error("Error: .col container not found for input", input);
                //     return;
                // }

                // // Check for error div or create it dynamically
                // let errorDiv = colContainer.nextElementSibling;
                // if (!errorDiv) {
                //     errorDiv = document.createElement("div");
                //     errorDiv.id = `${input.name}-error`;
                //     errorDiv.className = "invalid-feedback";
                //     errorDiv.style.marginTop = "5px";
                //     errorDiv.style.display = "none";
                //     colContainer.insertAdjacentElement("afterend", errorDiv);
                // }

                // // Perform validation
                // const value = parseFloat(input.value) || 0; // Default to 0 if input is empty
                // if (value > maxAmount) {
                //     input.classList.add("is-invalid");
                //     errorDiv.innerHTML = `*Value cannot exceed ${maxAmount.toLocaleString()}`;
                //     errorDiv.style.display = "block";
                //     isValid = false;
                // } else {
                //     input.classList.remove("is-invalid");
                //     errorDiv.style.display = "none";
                // }
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
            //});

            return isValid;
        }

        function validateTextFields(textElement) {
            //const inputs = document.querySelectorAll('input[type="text"]'); // Select all text inputs
            const allowedSymbols = /^[a-zA-Z0-9!@$%^&()_+\-:/|,~ ]+$/; // Allow letters, numbers, and symbols
            let isValid = true; // Track overall validity

            //inputs.forEach(input => {
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

                if (!textElement.value.trim()) {
                    textElement.setCustomValidity("This field is required");
                } else if (!allowedSymbols.test(textElement.value)) {
                    textElement.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
                } else {
                    textElement.setCustomValidity(""); // Reset validation
                }

                textElement.reportValidity(); // Show validation message

                if (!textElement.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                textElement.addEventListener("input", function () {
                    textElement.setCustomValidity(""); // Clear error when user types
                });
            //});

            return isValid; // Return validity status
        }


        function validateNotesField(notesInput) {
            const allowedSymbols = /^[a-zA-Z0-9!.@$%^&()_+\-:/|,~ \r\n]*$/; // Allow letters, numbers, symbols, and line breaks
            const maxLength = 500; // Maximum character limit
            let isValid = true; // Track overall validity

            //const errorElementId = notesInput.name + "-error"; // Unique error element ID
            // let errorElement = notesInput.nextElementSibling; // Locate the error element directly below the input

            // // Create an error element dynamically if it doesn't exist
            // if (!errorElement || errorElement.className !== "invalid-feedback") {
            //     errorElement = document.createElement("div");
            //     errorElement.id = errorElementId;
            //     errorElement.className = "invalid-feedback";
            //     notesInput.insertAdjacentElement("afterend", errorElement); // Place the error element below the input
            // }

            // // Validate the length
            // if (notesInput.value.length > maxLength) {
            //     notesInput.classList.add("is-invalid"); // Add invalid class
            //     errorElement.innerHTML = `*Notes cannot exceed ${maxLength} characters`; // Set error message
            //     errorElement.style.display = "block"; // Show error message
            //     isValid = false; // Mark as invalid
            // } 
            // // Validate allowed symbols (including line breaks)
            // else if (!allowedSymbols.test(notesInput.value)) {
            //     notesInput.classList.add("is-invalid");
            //     errorElement.innerHTML = "*Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~"; // Error message
            //     errorElement.style.display = "block";
            //     isValid = false;
            // } else {
            //     notesInput.classList.remove("is-invalid"); // Remove invalid class
            //     errorElement.style.display = "none"; // Hide error message
            // }

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
        //const dateInputs = document.querySelectorAll('input[type="date"]'); // Select all date inputs
        let isValid = true; // Track overall validity

        //dateInputs.forEach(input => {
            //const errorElementId = input.name + "-error"; // Unique error element ID
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
            //     isValid = false; // Mark as invalid
            // } else {
            //     input.classList.remove("is-invalid"); // Remove invalid class
            //     errorElement.style.display = "none"; // Hide error element
            // }

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
        //});

        return isValid; // Return validity status
    }


    function validateForm() {
        const numberFieldsValid = validateChargeAmount(chargeElement);
        const textFieldsValid = validateTextFields(textElement);
        const dateFieldValid = validateDateFields(dateElement);

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
                    <input type="radio" id="lcl" name="packageType" value="LCL" onclick="togglePackageField()"> LCL
                </label>
                <label>
                    <input type="radio" id="container" name="packageType" value="Full Container" onclick="togglePackageField()"> Full Container
                </label>
            </div>
            <div class="section" id="package-details" style="display: none;">
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
                <input type="submit" name="save" class="save-btn" value="Save">
            </div>
        </form>
    </div>
</body>

</html>