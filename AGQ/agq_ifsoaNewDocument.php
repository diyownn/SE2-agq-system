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
        NatureOfGoods, Packages, `Weight`, Measurement, PackageType, OceanFreight95,
        Documentation, TurnOverFee, Handling, Others, Notes, FCLCharge, 
        BLFee, ManifestFee, THC, CIC, ECRS, PSS, Origin, ShippingLine, ExWorkCharges, Total, 
        Prepared_by, Approved_by, Received_by, Printed_name, Creation_date, DocType, Company_name, Department
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssiiiiisiiiiiiiiiiissssssss",
        $_POST['To'],
        $_POST['Address'],
        $_POST['TIN'],
        $_POST['Attention'],
        $_POST['Date'],
        $_POST['Vessel'],
        $_POST['ETDETA'],
        $_POST['ReferenceNo'],
        $_POST['DestinationOrigin'],
        $_POST['ER'],
        $_POST['BLHBLNo'],
        $_POST['NatureofGoods'],
        $_POST['Packages'],
        $_POST['Weight'],
        $_POST['Measurement'],
        $_POST['package'],
        $_POST['95_Ocean_Freight'],
        $_POST['Documentation'],
        $_POST['Turn_Over_Fee'],
        $_POST['Handling'],
        $_POST['Others'],
        $_POST['Notes'],
        $_POST['FCL_Charges'],
        $_POST['BL_Fee'],
        $_POST['Manifest_Fee'],
        $_POST['THC'],
        $_POST['CIC'],
        $_POST['ECRS'],
        $_POST['PSS'],
        $_POST['Origin'],
        $_POST['Shipping_Line_Charges'],
        $_POST['Ex-Work_Charges'],
        $_POST['Total'],
        $_POST['Preparedby'],
        $_POST['Approvedby'],
        $_POST['Receivedby'],
        $_POST['PrintedName'],
        $_POST['Date1'],
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
                    "95_Ocean_Freight",
                    "BL_Fee",
                    "Manifest_Fee",
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
                    "95_Ocean_Freight",
                    "Handling",
                    "Turn_Over_Fee",
                    "BL_Fee",
                    "FCL_Charges",
                    "Documentation",
                    "Manifest_Fee",
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
                                ? '<option value="Others">Others</option><option value="Origin">Origin</option>' 
                                : '<option value="Others">Others</option><option value="Shipping_Line_Charges">Shipping Line Charges</option><option value="Ex-Work_Charges">Ex-Work Charges</option>'
                            }
                        </select>
                    `;
                } else {
                    row.innerHTML = `
                        <input type="text" value="${charge}" readonly>
                        <input type="number" name= "${charge}" placeholder="Enter amount">
                    `;
                }

                if (charge === "Notes") {
                    // Create a text input field for notes instead of number
                    row.innerHTML = `
                        <input type="text" value="Notes" readonly>
                        <input type="text" name="Notes" placeholder="Enter notes">
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
                <input type="number" name="${selectedCharge}" placeholder="Enter amount">
                <button onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);
        }

        function removeCharge(button) {
            button.parentElement.remove(); // Remove the selected charge row
        }

        var doctype = "<?php echo isset($_SESSION['DocType']) ? $_SESSION['DocType'] : ''; ?>"
        var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        var company = "<?php echo isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : ''; ?>";

        console.log("DocType:", doctype);
        console.log("Role:", role);
        console.log("Company:", company);
    </script>
</head>

<body>
    <div class="container">
        <div class="header">STATEMENT OF ACCOUNT</div>
        <form method="POST">
            <div class="section">
                <input type="text" name="To" placeholder="To" style="width: 70%">
                <input type="date" name="Date" placeholder="Date" style="width: 28%">
            </div>
            <div class="section">
                <input type="text" name="Address" placeholder="Address" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" name="TIN" placeholder="TIN" style="width: 48%">
                <input type="text" name="Attention" placeholder="Attention" style="width: 48%">
            </div>
            <div class="section">
                <input type="text" name="Vessel" placeholder="Vessel" style="width: 32%">
                <input type="text" name="ETDETA" placeholder="ETD/ETA" style="width: 32%">
                <input type="text" name="ReferenceNo" placeholder="Reference No" style="width: 32%">
            </div>
            <div class="section">
                <input type="text" name="DestinationOrigin" placeholder="Destination/Origin" style="width: 48%">
                <input type="text" name="ER" placeholder="E.R" style="width: 22%">
                <input type="text" name="BLHBLNo" placeholder="BL/HBL No" style="width: 22%">
            </div>
            <div class="section">
                <input type="text" name="NatureofGoods" placeholder="Nature of Goods" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" name="Packages" placeholder="Packages" style="width: 32%">
                <input type="text" name="Weight" placeholder="Weight" style="width: 32%">
                <input type="text" name="Measurement" placeholder="Measurement" style="width: 32%">
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
                <input type="number" name="Total" placeholder="Total" style="width: 100%">
            </div>
            <div class="section">
                <input type="text" name="Preparedby" placeholder="Prepared by" style="width: 48%">
                <input type="text" name="Approvedby" placeholder="Approved by" style="width: 48%">
            </div>
            <div class="section">
                <input type="text" name="Receivedby" placeholder="Received by" style="width: 24%">
                <input type="text" name="Signature" placeholder="Signature" style="width: 24%">
                <input type="text" name="PrintedName" placeholder="Printed Name" style="width: 24%">
                <input type="date" name="Date1" placeholder="Date" style="width: 24%">
            </div>
            <div class="footer">
                <!-- <button class="save-btn">Save</button> -->
                <input type="submit" name="save" class="save-btn" onclick="window.location.href='agq_employTransactionView'" value="Save">
            </div>
        </form>
    </div>
</body>

</html>