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
    <title>IF Sales Invoice </title>
    
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
                    "Ocean Freight",
                    "LCL Charge",
                    "Docs Fee",
                    "Documentation",
                    "Turn Over Fee",
                    "Handling",
                    "Others",
                    "Total"   
                ];
                generateFixedCharges(lclCharges);
            } else if (containerSelected) {
                const containerCharges = [
                    "FCL charge",
                    "Documentation",
                    "Handling Fee",
                    "Ocean Freight",
                    "12% VAT",
                    "Others",
                    "Total"
                ];
                generateFixedCharges(containerCharges, true);
            }
        }
    
        function generateFixedCharges(charges, isContainer = false) {
            const chargesTable = document.getElementById("charges-table");
    
            charges.forEach(charge => {
                const row = document.createElement("div");
                row.className = "table-row";
    
                if (charge === "Additional Charges") {
                    row.innerHTML = `
                        <select onchange="handleChargeSelection(this)">
                            <option value="">Additional Charges</option>
                            <option value="Others">Other Charges</option>
                            <option value="PCCI">PCCI</option>
                        </select>
                    `;
                } else {
                    row.innerHTML = `
                        <input type="text" value="${charge}" readonly>
                        <input type="number" placeholder="Enter amount">
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
    
            newRow.innerHTML = `
                <input type="text" value="${selectedCharge}" readonly>
                <input type="number" placeholder="Enter amount">
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
        <div class="header">SALES INVOICE</div>
        
        <div class="section">
            <input type="text" placeholder="To" style="width: 70%">
            <input type="date" placeholder="Date" style="width: 28%">
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
            <input type="text" placeholder="Notes" style="width: 100%">
        </div>
        <div class="section">
            <input type="text" placeholder="Prepared by" style="width: 48%">
            <input type="text" placeholder="Approved by" style="width: 48%">
        </div>
        <div class="section">
            <input type="text" placeholder="Received by" style="width: 24%">
            <input type="text" placeholder="Signature" style="width: 24%">
            <!-- <input type="text" placeholder="Printed Name" style="width: 24%"> -->
            <input type="date" placeholder="Date" style="width: 24%">
        </div>
        <div class="footer">
            <button class="save-btn">Save</button>
        </div>
    </div>
</body>
</html>
