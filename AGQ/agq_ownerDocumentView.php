<?php

require 'db_agq.php';
session_start();

$refNum = isset($_GET['refnum']) ? $_GET['refnum'] : '';
$url = isset($_GET['url']) ? $_GET['url'] : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

if (!$url) {
  header("Location: UNAUTHORIZED.php?error=401u");
}



function selectRecords($conn, $dept, $refNum)
{
  if ($dept == "Import Forwarding") {
    $sql = "SELECT * FROM tbl_impfwd WHERE RefNum = ?";
  } else if ($dept == "Import Brokerage") {
    $sql = "SELECT * FROM tbl_impbrk WHERE RefNum = ?";
  } else if ($dept == "Export Forwarding") {
    $sql = "SELECT * FROM tbl_expfwd WHERE RefNum = ?";
  } else if ($dept == "Export Brokerage") {
    $sql = "SELECT * FROM tbl_expbrk WHERE RefNum = ?";
  }

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $refNum);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_assoc();

  if ($result->num_rows > 0) {
    echo "<h2>Database Records:</h2>";
    while ($row = $result->fetch_assoc()) {
      echo "<pre>" . print_r($row, true) . "</pre>";
    }
  } else {
    echo "No records found.";
  }

  $stmt->close();
}

$record = selectRecords($conn, $dept, $refNum);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Owner Document View</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/owndocu.css">
</head>


<body>
  <!--<div class="top-container">
        <div class="dept-container">
            <div class="header-container">
                <div class="dept-label">
                    <?php echo htmlspecialchars($role); ?>
                </div>
                <div class="company-label">
                    <?php echo htmlspecialchars($company); ?>
                </div>
                <div class="selected-dept-label">
                    <?php echo htmlspecialchars($dept); ?>
                </div>
                <div class="selected-doctype">
                    <?php echo htmlspecialchars($docType); ?>
                </div>
            </div>
        </div>
    </div>

    <a href="agq_ownTransactionView.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>
-->

  <div class="container">
    <div class="document-view">
      <table class="transaction-detials-table">
        <thead class="transaction-details-header">
          <tr>
            <th>Transaction Details</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>To</td>
            <td id="to"><?php echo htmlspecialchars($record['To:'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Address</td>
            <td id="address"><?php echo htmlspecialchars($record['Address'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>TIN</td>
            <td id="tin"><?php echo htmlspecialchars($record['Tin'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Attention</td>
            <td id="attention"><?php echo htmlspecialchars($record['Attention'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Date</td>
            <td id="date"><?php echo htmlspecialchars($record['Date'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Vessel</td>
            <td id="vessel"><?php echo htmlspecialchars($record['Vessel'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>ETD/ETA</td>
            <td id="etd-eta"><?php echo htmlspecialchars($record['ETA'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Ref No.</td>
            <td id="ref-no"><?php echo htmlspecialchars($record['RefNum'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Destination/Origin</td>
            <td id="destination-origin"><?php echo htmlspecialchars($record['DestinationOrigin'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>E.R.</td>
            <td id="er"><?php echo htmlspecialchars($record['ER'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>BL/HBL No</td>
            <td id="bl-hbl-no"><?php echo htmlspecialchars($record['BHNum'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Nature of Goods</td>
            <td id="nature-of-goods"><?php echo htmlspecialchars($record['NatureOfGoods'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Packages</td>
            <td id="package"><?php echo htmlspecialchars($record['Packages'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Weight</td>
            <td id="weight"><?php echo htmlspecialchars($record['Weight'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Volume</td>
            <td id="volume"><?php echo htmlspecialchars($record['Volume'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Package Type</td>
            <td id="package-type"><?php echo htmlspecialchars($record['PackageType'] ?? 'N/A'); ?></td>
          </tr>
        </tbody>
      </table>

      <table>
        <thead>
          <tr>
            <th>Reimbursable Charges</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>95 Ocean Freight</td>
            <td id="ocean-freight-95"><?php echo htmlspecialchars($record['OceanFreight95'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Advance Shipping Lines</td>
            <td id="advance-shipping-lines"><?php echo htmlspecialchars($record['AdvanceShipping'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Processing</td>
            <td id="processing"><?php echo htmlspecialchars($record['Processing'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>5 Ocean Freight</td>
            <td id="ocean-freight-5"><?php echo htmlspecialchars($record['OceanFreight5	'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Brokerage Fee</td>
            <td id="brokerage-fee"><?php echo htmlspecialchars($record['BrokerageFee'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>VAT 12%</td>
            <td id="vat-12"><?php echo htmlspecialchars($record['Vat12'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>LCL Charge</td>
            <td id="lcl-charge"><?php echo htmlspecialchars($record['LCLCharge'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Docs Fee</td>
            <td id="docs-fee"><?php echo htmlspecialchars($record['DocsFee'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Documentation</td>
            <td id="documentation"><?php echo htmlspecialchars($record['Documentation'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Turn Over Fee</td>
            <td id="turn-over-fee"><?php echo htmlspecialchars($record['TurnOverFee'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Handling</td>
            <td id="handling"><?php echo htmlspecialchars($record['Handling'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Manifest Fee</td>
            <td id="manifest-fee"><?php echo htmlspecialchars($record['ManifestFee'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>THC</td>
            <td id="thc"><?php echo htmlspecialchars($record['THC'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>CIC</td>
            <td id="cic"><?php echo htmlspecialchars($record['CIC'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>ECRS</td>
            <td id="ecrs"><?php echo htmlspecialchars($record['ECRS'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>PSS</td>
            <td id="pss"><?php echo htmlspecialchars($record['PSS'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Origin</td>
            <td id="origin"><?php echo htmlspecialchars($record['Origin'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Shipping Line</td>
            <td id="shipping-line"><?php echo htmlspecialchars($record['ShippingLine'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>FCL Charge</td>
            <td id="fcl-charge"><?php echo htmlspecialchars($record['FCLCharge'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>ICCO</td>
            <td id="icco"><?php echo htmlspecialchars($record['ICCO'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Arrastre</td>
            <td id="arrastre"><?php echo htmlspecialchars($record['Arrastre'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Wharfage</td>
            <td id="wharfage"><?php echo htmlspecialchars($record['	Wharfage'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Forms/Stamps</td>
            <td id="forms-stamps"><?php echo htmlspecialchars($record['FormsStamps'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Photocopy/Notarial</td>
            <td id="photocopy-notarial"><?php echo htmlspecialchars($record['PhotocopyNotarial'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>E2M Lodgement</td>
            <td id="e2m-lodgement"><?php echo htmlspecialchars($record['E2MLodge'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Stuffing (Mano)</td>
            <td id="stuffing-mano"><?php echo htmlspecialchars($record['ManualStuffing'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Trucking</td>
            <td id="trucking"><?php echo htmlspecialchars($record['Trucking'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Others</td>
            <td id="others"><?php echo htmlspecialchars($record['Others'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Total</td>
            <td id="total"><?php echo htmlspecialchars($record['Total'] ?? 'N/A'); ?></td>
          </tr>
        </tbody>
      </table>

      <table class="approvals-table">
        <thead>
          <tr>
            <th>Approvals</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Prepared By</td>
            <td id="prepared-by"><?php echo htmlspecialchars($record['Prepared_by'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>Approved By</td>
            <td id="approved-by"><?php echo htmlspecialchars($record['Approved_by'] ?? 'N/A'); ?></td>
          </tr>
        </tbody>
      </table>

    </div>
    <div class="info-view">
      <div class="docu-information">
        <p class="ref-number"><?php echo htmlspecialchars($refNum); ?></p>
        <p class="document-type"><?php echo htmlspecialchars($record['DocType'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Date Created:</strong> <?php echo htmlspecialchars($record['Date'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Created By:</strong> <?php echo htmlspecialchars($record['Prepared_by'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Date Modified:</strong> <?php echo htmlspecialchars($record['EditDate'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Modified By:</strong> <?php echo htmlspecialchars($record['Edited_by'] ?? 'N/A'); ?></p>
        <div class="comment-box">
          <textarea id="textbox" id="comments" maxlength="250" oninput="updateCounter()"> <?php echo htmlspecialchars($record['Comment'] ?? 'N/A'); ?></textarea>
          <div class="counter" id="counter">0/250</div>
          <div class="button-container" id="save-button">
            <button class="save-button" onclick="saveComment('<?php echo htmlspecialchars($refNum, ENT_QUOTES); ?>')">Save</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      function updateCounter() {
        let textbox = document.getElementById("textbox");
        let counter = document.getElementById("counter");
        let used = textbox.value.length;
        counter.textContent = used + "/250";
      }

      function saveComment(refnum) {
        let comment = document.getElementById("textbox").value;

        if (comment.trim() === "") {
          alert("Please enter a comment.");
          return;
        }

        fetch("SAVE_COMMENT.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "comment=" + encodeURIComponent(comment) + "&refnum=" + encodeURIComponent(refnum)
          })
          .then(response => response.text())
          .then(data => {
            alert(data);
          })
          .catch(error => {
            console.error("Error:", error);
            alert("Failed to save comment.");
          });
      }
    </script>
</body>

</html>