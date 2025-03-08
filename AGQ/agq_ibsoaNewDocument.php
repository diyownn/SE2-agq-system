<?php

require_once "db_agq.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save'])) {
        insertRecord($conn);
    } elseif (isset($_POST['select'])) {
        selectRecords($conn);
    } elseif (isset($_POST['delete'])) {
        deleteRecord($conn, $_POST['RefNum']);
    }
}

// Function to insert a record
function insertRecord($conn)
{

    $docType = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : null;
    $department = isset($_SESSION['Department']) ? $_SESSION['Department'] : null;
    $companyName = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : null;

    $sql = "INSERT INTO tbl_impbrk (
        `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Measurement, PackageType, Others, Notes, OceanFreight95, Forwarder, WarehouseCharge, 
        ELodge, Processing, FormsStamps, PhotocopyNotarial, Documentation, DeliveryExpense, Miscellaneous, 
        Door2Door, ArrastreWharf, THC, AISL, GOFast, AdditionalProcessing, ExtraHandlingFee, ClearanceExpenses, 
        HaulingTrucking, AdditionalContainer, Handling, StuffingPlant, IED, EarlyGateIn, TABS, DocsFee, 
        DetentionCharges, ContainerDeposit, LateCollection, LateCharge, Demurrage, Total, Prepared_by, Approved_by, 
        Edited_by, EditDate, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssisiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiisssssss",
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
        $_POST['natureofGoods'],
        $_POST['packages'],
        $_POST['weight'],
        $_POST['measurement'],
        $_POST['package'],
        $_POST['others'],
        $_POST['notes'],
        $_POST['95oceanfreight'],
        $_POST['forwarder'],
        $_POST['warehousecharges'],
        $_POST['e-lodgement'],
        $_POST['processing'],
        $_POST['customsformsstamps'],
        $_POST['photocopynotarial'],
        $_POST['documentation'],
        $_POST['deliveryexpense'],
        $_POST['misc.transpo.tel.card'],
        $_POST['doortodoorbacolod'],
        $_POST['arrastrewharfagestorage'],
        $_POST['thc-shippingline'],
        $_POST['aisl'],
        $_POST['gofast'],
        $_POST['additionalprocessing'],
        $_POST['extrahandlingfee'],
        $_POST['clearanceexpenses'],
        $_POST['haulingandtrucking'],
        $_POST['additionalcontainer'],
        $_POST['handling'],
        $_POST['stuffingplant'],
        $_POST['iedentryencoding'],
        $_POST['earlygatein'],
        $_POST['tabs'],
        $_POST['DocsFee'],
        $_POST['detentioncharges'],
        $_POST['containerdeposit'],
        $_POST['latecollection'],
        $_POST['latecharge'],
        $_POST['demurragecosco'],
        $_POST['total'],
        $_POST['prepared'],
        $_POST['approved'],
        $_POST['edited'],
        $editDate = date('Y-m-d'),
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
    $sql = "SELECT * FROM tbl_impbrk";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Database Records:</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
    $stmt->close();
}

// Function to delete a record
function deleteRecord($conn, $RefNum)
{
    $sql = "DELETE FROM tbl_impbrk WHERE RefNum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $RefNum);

    if ($stmt->execute()) {
        echo "Record deleted successfully!";
    } else {
        echo "Error deleting record: " . $stmt->error;
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
</head>

<body>
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
                    "Forwarder",
                    "Warehouse Charges",
                    "E-lodgement",
                    "Processing",
                    "Customs Forms/Stamps",
                    "Photocopy/Notarial",
                    "Documentation",
                    "Delivery Expense",
                    "MISC. Transpo. Tel. Card",
                    "Notes",
                    "Additional Charges"
                ];
                generateFixedCharges(lclCharges, true); // true = LCL mode
            } else if (containerSelected) {
                const containerCharges = [
                    "95 Ocean Freight",
                    "Arrastre / Wharfage Storage",
                    "THC - Shipping Line",
                    "AISL",
                    "GO fast",
                    "Processing",
                    "Additional Processing",
                    "Customs Forms/Stamps",
                    "Extra Handling Fee",
                    "Photocopy/Notarial",
                    "Clearance Expenses",
                    "Hauling and Trucking",
                    "Additional Container",
                    "Handling",
                    "Stuffing Plant",
                    "IED / Entry Encoding",
                    "Early Gate In",
                    "Tabs",
                    "Docs Fee",
                    "Notes",
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
                                ? '<option value="Others">Others</option><option value="Door to Door Bacolod">Door to Door Bacolod</option>' 
                                : '<option value="Others">Others</option><option value="Container Deposit">Container Deposit</option><option value="Late Collection">Late Collection</option><option value="Late Charge">Late Charge</option><option value="Demurrage COSCO">Demurrage Cosco</option><option value="Detention Charges">Detention Charges</option>'
                            }
                        </select>
                    `;
                } else {
                    const inputName = charge.toLowerCase().replace(/\s+/g, '').replace('/', '');
                    row.innerHTML = `
                        <input type="text" value="${charge}" readonly>
                        <input type="number" name="${inputName}" placeholder="Enter amount">
                    `;
                }

                if (charge === "Notes") {
                    // Create a text input field for notes instead of number
                    row.innerHTML = `
                        <input type="text" name="charge_type[]" value="Notes" readonly>
                        <input type="text" name="notes" placeholder="Enter notes">
                    `;
                }

                chargesTable.appendChild(row);
            });
        }

        function handleChargeSelection(selectElement, isLCL) {
            const selectedCharge = selectElement.value;
            if (!selectedCharge) return; // Do nothing if no valid selection

            // Prevent duplicate entries
            const existingEntries = document.querySelectorAll(".added-charge");
            for (let entry of existingEntries) {
                if (entry.dataset.charge === selectedCharge) return;
            }

            // Add new charge field
            const chargesTable = document.getElementById("charges-table");
            const newRow = document.createElement("div");
            newRow.className = "table-row added-charge";
            newRow.dataset.charge = selectedCharge;

            const inputName = selectedCharge.toLowerCase().replace(/\s+/g, '').replace('/', '');

            newRow.innerHTML = `
                <input type="text" value="${selectedCharge}" readonly>
                <input type="number" name="${inputName}" placeholder="Enter amount">
                <button onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);
        }

        function removeCharge(button) {
            button.parentElement.remove();
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
                    <input type="text" name="refNum" placeholder="Reference No" style="width: 32%">
                </div>
                <div class="section">
                    <input type="text" name="destinationOrigin" placeholder="Destination/Origin" style="width: 48%">
                    <input type="text" name="er" placeholder="E.R" style="width: 22%">
                    <input type="text" name="bhNum" placeholder="BL/HBL No" style="width: 22%">
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
                    <input type="text" id="total" name="total" placeholder="Total" style="width: 100%" readonly>
                    <button type="button" onclick="calculateTotal()" class="calc-btn">Calculate</button>
                </div>
                <div class="section">
                    <input type="text" name="prepared" placeholder="Prepared by" style="width: 48%">
                    <input type="text" name="approved" placeholder="Approved by" style="width: 48%">
                    <input type="text" name="edited" placeholder="Edited by" style="width: 48%">
                </div>
                <div class="footer">
                    <input type="submit" name="save" class="save-btn" value="Save">
                </div>
            </form>
        </div>
    </body>

</html>