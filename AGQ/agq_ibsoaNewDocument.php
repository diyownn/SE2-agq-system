<?php

require_once "db_agq.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['insert'])) {
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
        `To`, Address, Tin, Attention, Date, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, Weight, Measurement, PackageType, BrokerageFee, Vat12,
        Others, Notes, TruckingService, Forwarder, WarehouseCharge, E lodge, Processing, FormsStamps, 
        PhotocopyNotarial, Documentation, DeliveryExpense, Miscellaneous, Door2Door, ArrastreWharf, THC, 
        AISL, GOFast, AdditionalProcessing, ExtraHandlingFee, ClearanceExpenses, HaulingTrucking, 
        AdditionalContainer, Handling, StuffingPlant, IED, EarlyLayGateIn, TABS, DocsFee, DetentionCharges, 
        ContainerDeposit, LateCollection, LateCharge, Demurrage, Total, Prepared_by, Approved_by, 
        Received_by, Printed_name, Creation_date, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiisssssss",
        $_POST['To'],
        $_POST['Address'],
        $_POST['Tin'],
        $_POST['Attention'],
        $_POST['Date'],
        $_POST['Vessel'],
        $_POST['ETA'],
        $_POST['RefNum'],
        $_POST['DestinationOrigin'],
        $_POST['ER'],
        $_POST['BHNum'],
        $_POST['NatureOfGoods'],
        $_POST['Packages'],
        $_POST['Weight'],
        $_POST['Measurement'],
        $_POST['PackageType'],
        $_POST['BrokerageFee'],
        $_POST['Vat12'],
        $_POST['Others'],
        $_POST['Notes'],
        $_POST['TruckingService'],
        $_POST['Forwarder'],
        $_POST['WarehouseCharge'],
        $_POST['Elodge'],
        $_POST['Processing'],
        $_POST['FormsStamps'],
        $_POST['PhotocopyNotarial'],
        $_POST['Documentation'],
        $_POST['DeliveryExpense'],
        $_POST['Miscellaneous'],
        $_POST['Door2Door'],
        $_POST['ArrastreWharf'],
        $_POST['THC'],
        $_POST['AISL'],
        $_POST['GOFast'],
        $_POST['AdditionalProcessing'],
        $_POST['ExtraHandlingFee'],
        $_POST['ClearanceExpenses'],
        $_POST['HaulingTrucking'],
        $_POST['AdditionalContainer'],
        $_POST['Handling'],
        $_POST['StuffingPlant'],
        $_POST['IED'],
        $_POST['EarlyLayGateIn'],
        $_POST['TABS'],
        $_POST['DocsFee'],
        $_POST['DetentionCharges'],
        $_POST['ContainerDeposit'],
        $_POST['LateCollection'],
        $_POST['LateCharge'],
        $_POST['Demurrage'],
        $_POST['Total'],
        $_POST['Prepared_by'],
        $_POST['Approved_by'],
        $_POST['Received_by'],
        $_POST['Printed_name'],
        $_POST['Creation_date'],
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
    <link rel="stylesheet" type="text/css" href="../css/ibsoa.css">
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
                    "Forwarder",
                    "Warehouse Charges",
                    "E-lodgement",
                    "Processing",
                    "Customs Forms/Stamps",
                    "Photocopy/Notarial",
                    "Documentation",
                    "Delivery Expense",
                    "MISC., Transpo, Tel. Card",
                    "Notes",
                    "Additional Charges"
                ];
                generateFixedCharges(lclCharges, true); // true = LCL mode
            } else if (containerSelected) {
                const containerCharges = [
                    "Arrastre / Wharfage / Storage",
                    "THC - Shipping Line",
                    "AISL",
                    "GO fast",
                    "Processing",
                    "Additional Processing",
                    "Customs Forms/Stamps",
                    "Extra Handling Fee",
                    "Notarial Fee",
                    "Xerox Expenses",
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
                                ? '<option value="Door to Door Bacolod">Door to Door Bacolod</option>' 
                                : '<option value="Container Deposit">Container Deposit</option><option value="Late Collection">Late Collection</option><option value="Late Charge">Late Charge</option><option value="Demurrage COSCO">Demurrage Cosco</option>'
                            }
                        </select>
                    `;
                } else {
                    row.innerHTML = `
                        <input type="text" value="${charge}" readonly>
                        <input type="text" placeholder="Enter amount">
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

            newRow.innerHTML = `
                <input type="text" value="${selectedCharge}" readonly>
                <input type="text" placeholder="Enter amount">
                <button onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);
        }

        function removeCharge(button) {
            button.parentElement.remove();
        }
    </script>
    </head>

    <body>
        <div class="container">
            <div class="header">STATEMENT OF ACCOUNT</div>
            <form method="POST">
                <div class="section">
                    <input type="text" placeholder="To" style="width: 70%">
                    <input type="text" placeholder="Date" style="width: 28%">
                </div>
                <div class="section">
                    <input type="text" placeholder="Address" style="width: 100%">
                </div>
                <div class="section">
                    <input type="text" placeholder="TIN" style="width: 48%">
                    <input type="text" placeholder="Attention" style="width: 48%">
                </div>
                <div class="section">
                    <input type="text" placeholder="Vessel" style="width: 32%">
                    <input type="text" placeholder="ETD/ETA" style="width: 32%">
                    <input type="text" placeholder="Reference No" style="width: 32%">
                </div>
                <div class="section">
                    <input type="text" placeholder="Destination/Origin" style="width: 48%">
                    <input type="text" placeholder="E.R" style="width: 22%">
                    <input type="text" placeholder="BL/HBL No" style="width: 22%">
                </div>
                <div class="section">
                    <input type="text" placeholder="Nature of Goods" style="width: 100%">
                </div>
                <div class="section">
                    <input type="text" placeholder="Packages" style="width: 32%">
                    <input type="text" placeholder="Weight" style="width: 32%">
                    <input type="text" placeholder="Measurement" style="width: 32%">
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
                    <input type="text" placeholder="Enter package details" style="width: 100%">
                </div>
                <div class="table-container">
                    <div class="table-header">
                        <span>Reimbursable Charges</span>
                        <span>Amount</span>
                    </div>
                    <div id="charges-table"></div>
                </div>
                <div class="section">
                    <input type="text" placeholder="Total" style="width: 100%">
                </div>
                <div class="section">
                    <input type="text" placeholder="Prepared by" style="width: 48%">
                    <input type="text" placeholder="Approved by" style="width: 48%">
                </div>
                <div class="section">
                    <input type="text" placeholder="Received by" style="width: 24%">
                    <input type="text" placeholder="Signature" style="width: 24%">
                    <input type="text" placeholder="Printed Name" style="width: 24%">
                    <input type="text" placeholder="Date" style="width: 24%">
                </div>
                <div class="footer">
                    <button class="save-btn">Save</button>
                </div>
            </form>
        </div>
    </body>

</html>