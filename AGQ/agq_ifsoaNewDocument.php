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
                    "95 Ocean Freight",
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
                    "95 Ocean Freight",
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

                if (charge === "Notes") {
                    // Create a text input field for notes instead of number
                    row.innerHTML = `
                        <input type="text" value="Notes" readonly>
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
            newRow.dataset.charge = selectedCharge; // Store charge type

            let inputName = selectedCharge.toLowerCase() + "_amount";

            newRow.innerHTML = `
                <input type="text" value="${selectedCharge}" readonly>
                <input type="number" name="${inputName}" placeholder="Enter amount">
                <button type="button" onclick="removeCharge(this)">Remove</button>
            `;

            chargesTable.appendChild(newRow);
        }

        function removeCharge(button) {
            button.parentElement.remove(); // Remove the selected charge row
        }

        // var doctype = "<?php echo isset($_SESSION['DocType']) ? $_SESSION['DocType'] : ''; ?>"
        // var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        // var company = "<?php echo isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : ''; ?>";

        // console.log("DocType:", doctype);
        // console.log("Role:", role);
        // console.log("Company:", company);

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