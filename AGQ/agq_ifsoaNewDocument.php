<?php
require 'db_agq.php';

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

    $sql = "INSERT INTO your_table (
        To:, Address, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, Weight, Measurement, PackageType, OceanFreight, OceanFreight95, 
        LCLCharge, DocsFee, Documentation, TurnOverFee, Handling, Others, Notes, Vat12, FCLCharge, 
        BLFee, ManifestFee, THC, CIC, ECRS, PSS, Origin, ShippingLine, ExWorkCharges, Total, 
        Prepared_by, Approved_by, Received_by, Printed_name, Creation_date, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssiiiiiiiiiiiiiiiiiiiissssssss",
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
        $_POST['oceanFreight'],
        $_POST['oceanFreight95'],
        $_POST['lclCharge'],
        $_POST['docsFee'],
        $_POST['documentation'],
        $_POST['turnOverFee'],
        $_POST['handling'],
        $_POST['others'],
        $_POST['notes'],
        $_POST['vat12'],
        $_POST['fclCharge'],
        $_POST['blFee'],
        $_POST['manifestFee'],
        $_POST['thc'],
        $_POST['cic'],
        $_POST['ecrs'],
        $_POST['pss'],
        $_POST['origin'],
        $_POST['shippingLine'],
        $_POST['exWorkCharges'],
        $_POST['total'],
        $_POST['prepared_by'],
        $_POST['approved_by'],
        $_POST['received_by'],
        $_POST['printed_name'],
        $_POST['creation_date'],
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
    <link rel="stylesheet" type="text/css" href="../css/ifsoa.css">
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
                    "95% Ocean Freight",
                    "BL Fee",
                    "Manifest Fee",
                    "THC",
                    "CIC",
                    "ECRS",
                    "PSS",
                    "Notes",
                    "Additional Charges" // LCL-specific
                ];
                generateFixedCharges(lclCharges, true); // true = LCL mode
            } else if (containerSelected) {
                const containerCharges = [
                    "95% Ocean Freight",
                    "Handling",
                    "Turn Over Fee",
                    "BL Fee",
                    "FCL Charges",
                    "Documentation",
                    "Manifest Fee",
                    "Notes",
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
                                ? '<option value="Origin">Origin</option>' 
                                : '<option value="Shipping Line Charges">Shipping Line Charges</option><option value="Ex-Work Charges">Ex-Work Charges</option>'
                            }
                        </select>
                    `;
                } else {
                    row.innerHTML = `
                        <input type="text" value="${charge}" readonly>
                        <input type="text" placeholder="Enter amount">
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
            newRow.dataset.charge = selectedCharge; // Store charge type

            newRow.innerHTML = `
                <input type="text" value="${selectedCharge}" readonly>
                <input type="text" placeholder="Enter amount">
                <button onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);
        }

        function removeCharge(button) {
            button.parentElement.remove(); // Remove the selected charge row
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