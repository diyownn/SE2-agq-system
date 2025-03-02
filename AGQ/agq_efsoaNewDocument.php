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

    $sql = "INSERT INTO tbl_expfwd (
        'To:', 'Address', Tin, Attention, 'Date', Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, 'Weight', Measurement, PackageType, OceanFreight, OceanFreight50, BrokerageFee, Others, Notes,
        Vat12, DocsFee, LCLCharge, ExportProcessing, FormsStamps, ArrastreWharf, E2MLodge, FAF, SealFee, Storage, Telex,
        Total, Prepared_by, Approved_by, Received_by, Printed_name, Creation_date, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssiiiiiiiiiiiiiiiiiiiisssssss",
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
        $_POST['oceanFreight50'],
        $_POST['brokerageFee'],
        $_POST['others'],
        $_POST['notes'],
        $_POST['vat12'],
        $_POST['docsFee'],
        $_POST['lclCharge'],
        $_POST['exportProcessing'],
        $_POST['formsStamps'],
        $_POST['arrastreWharf'],
        $_POST['e2mLodge'],
        $_POST['faf'],
        $_POST['sealFee'],
        $_POST['storage'],
        $_POST['telex'],
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

// Function to select all records
function selectRecords($conn)
{
    $sql = "SELECT * FROM tbl_expfwd";
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
    $sql = "DELETE FROM tbl_expfwd WHERE RefNum = ?";
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
    <link rel="stylesheet" type="text/css" href="../css/efsoa.css">
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
                    "Docs Fee",
                    "LCL Charge",
                    "Export Processing",
                    "Customs Forms/Stamps",
                    "Arrastre/Wharfage",
                    "E2M Fee",
                    "Others",
                    "Notes"
                ];
                generateFixedCharges(lclCharges);
            } else if (containerSelected) {
                const containerCharges = [
                    "THC",
                    "Docs Fee",
                    "FAF",
                    "Seal Fee",
                    "Storage",
                    "Telex Fee",
                    "Others",
                    "Notes"
                ];
                generateFixedCharges(containerCharges);
            }
        }

        function generateFixedCharges(charges) {
            const chargesTable = document.getElementById("charges-table");
            charges.forEach(charge => {
                const row = document.createElement("div");
                row.className = "table-row";
                row.innerHTML = `
                    <input type="text" value="${charge}" readonly>
                    <input type="text" placeholder="Enter amount">
                `;
                chargesTable.appendChild(row);
            });
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