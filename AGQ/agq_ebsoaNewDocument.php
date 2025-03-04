<?php
require 'db_agq.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['insert'])) {
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
    $department = isset($_SESSION['Department']) ? $_SESSION['Department'] : null;
    $companyName = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : null;

    $sql = "INSERT INTO tbl_expbrk (
        To:, Address, Tin, Attention, Date, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, Weight, Measurement, PackageType, BrokerageFee, Discount50, Vat12,
        Others, Notes, AdvanceShipping, Processing, Arrastre, Wharfage, FormsStamps, PhotocopyMatarial,
        Documentation, E2MLodge, HaulStuffing, Handling, PCCI, Total, Prepared_by, Approved_by, DocType, 
        Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssiiiissiiiiiiiiiiiiisss",
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
        $_POST['packageType'],
        $_POST['brokerageFee'],
        $_POST['discount50'],
        $_POST['vat12'],
        $_POST['others_amount'],
        $_POST['notes'],
        $_POST['advanceShipping'],
        $_POST['processing'],
        $_POST['arrastre'],
        $_POST['wharfage'],
        $_POST['formsStamps'],
        $_POST['photocopyMaterial'],
        $_POST['documentation'],
        $_POST['e2mLodge'],
        $_POST['haulStuffing'],
        $_POST['handling'],
        $_POST['pcci_amount'],
        $_POST['total'],
        $_POST['prepared_by'],
        $_POST['approved_by'],
        $docType,        // Session variable
        $companyName,    // Session variable
        $department      // Session variable
    );

    if ($stmt->execute()) {
        echo "New record inserted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Function to select all records
function selectRecords($conn)
{
    $sql = "SELECT * FROM your_table";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Database Records:</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
    $stmt->close();
}

// Function to delete a record by RefNum
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
    <link rel="stylesheet" type="text/css" href="../css/ebsoa.css">
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
                    "Advance Shipping Lines",
                    "Processing",
                    "Notes"
                ];
                generateFixedCharges(lclCharges);
            } else if (containerSelected) {
                const containerCharges = [
                    "Arrastre",
                    "Wharfage",
                    "Processing",
                    "Forms/Stamps",
                    "Photocopy/Notarial",
                    "Documentation",
                    "E2M Lodgement",
                    "Handling",
                    "Notes"
                ];
                generateFixedCharges(containerCharges, true);
            }
        }

        function generateFixedCharges(charges, isContainer = false) {
            const chargesTable = document.getElementById("charges-table");

            charges.forEach(charge => {
                const row = document.createElement("div");
                row.className = "table-row";

                if (charge === "Notes") {
                    // Create a text input field for notes instead of number
                    row.innerHTML = `
                        <input type="text" name="charge_type[]" value="Notes" readonly>
                        <input type="text" name="notes" placeholder="Enter notes">
                    `;
                } else {
                    // Number input for all other charges
                    const inputName = charge.toLowerCase().replace(/\s+/g, '').replace('/', '');
                    row.innerHTML = `
                        <input type="text" name="charge_type[]" value="${charge}" readonly>
                        <input type="number" name="${inputName}" placeholder="Enter amount">
                    `;
                }

                chargesTable.appendChild(row);
            });

            // Add the Additional Charges dropdown after all fixed charges
            const additionalRow = document.createElement("div");
            additionalRow.className = "table-row";
            additionalRow.innerHTML = `
                <select onchange="handleChargeSelection(this)">
                    <option disabled selected>Additional Charges</option>
                    <option value="Others">Others</option>
                    <option value="PCCI">PCCI</option>
                </select>
                <div></div>
            `;
            chargesTable.appendChild(additionalRow);
        }

        function handleChargeSelection(selectElement) {
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
                <input type="number" name="${inputName}" placeholder="Enter amount">
                <button type="button" onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);
        }

        function removeCharge(button) {
            const row = button.parentNode;
            row.parentNode.removeChild(row);
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
    <div class="container">
        <div class="header">STATEMENT OF ACCOUNT</div>
        <form method="POST">
            <div class="section">
                <input type="text" name="to" placeholder="To" style="width: 70%" required>
                <input type="date" name="date" placeholder="Date" style="width: 28%" required>
            </div>
            <div class="section">
                <input type="text" name="address" placeholder="Address" style="width: 100%" required>
            </div>
            <div class="section">
                <input type="text" name="tin" placeholder="TIN" style="width: 48%">
                <input type="text" name="attention" placeholder="Attention" style="width: 48%">
            </div>
            <div class="section">
                <input type="text" name="vessel" placeholder="Vessel" style="width: 32%">
                <input type="text" name="eta" placeholder="ETD/ETA" style="width: 32%">
                <input type="text" name="refNum" placeholder="Reference No" style="width: 32%" required>
            </div>
            <div class="section">
                <input type="text" name="destinationOrigin" placeholder="Destination/Origin" style="width: 48%">
                <input type="text" name="er" placeholder="E.R" style="width: 22%">
                <input type="text" name="bhNum" placeholder="BL/HBL No" style="width: 22%">
            </div>
            <div class="section">
                <input type="text" name="natureOfGoods" placeholder="Nature of Goods" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" name="packages" placeholder="Packages" style="width: 32%">
                <input type="text" name="weight" placeholder="Weight" style="width: 32%">
                <input type="text" name="measurement" placeholder="Measurement" style="width: 32%">
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
                <div class="section">
                    <input type="number" id="total" name="total" placeholder="Total" style="width: 100%" readonly>

                   <!-- <button type="button" onclick="calculateTotal()" class="calc-btn">Calculate Total</button> -->

                </div>
                <div class="section">
                    <input type="text" name="prepared_by" placeholder="Prepared by" style="width: 48%">
                    <input type="text" name="approved_by" placeholder="Approved by" style="width: 48%">
                </div>
                <div class="footer">
                    <!-- <button type="submit" name="insert" class="save-btn">Save</button> -->
                    <input type="submit" name="save" class="save-btn" onclick="window.location.href='agq_employTransactionView'" value="Save">
                </div>
            </div>
        </form>
    </div>
</body>

</html>