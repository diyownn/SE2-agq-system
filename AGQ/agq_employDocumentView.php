<?php

require 'db_agq.php';
session_start();

$refNum = isset($_GET['refNum']) ? $_GET['refNum'] : ''; // Get reference number from URL

$docType = isset($_GET['doctype']) ? $_GET['doctype'] : '';
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

function selectRecords($role, $conn, $refNum)
{
  if ($role == "Import Forwarding") {
    $sql = "SELECT * FROM tbl_impfwd WHERE RefNum = ?";
  } else if ($role == "Import Brokerage") {
    $sql = "SELECT * FROM tbl_impbrk WHERE RefNum = ?";
  } else if ($role == "Export Forwarding") {
    $sql = "SELECT * FROM tbl_expfwd WHERE RefNum = ?";
  } else if ($role == "Export Brokerage") {
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

$record = selectRecords($conn, $role, $refNum);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Document View</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="../css/employdocu.css">
</head>

<body>
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
            <td id="to"><?php echo htmlspecialchars($record['To:']); ?></td>
          </tr>
          <tr>
            <td>Address</td>
            <td id="address"><?php echo htmlspecialchars($record['Address']); ?></td>
          </tr>
          <tr>
            <td>TIN</td>
            <td id="tin"><?php echo htmlspecialchars($record['Tin']); ?></td>
          </tr>
          <tr>
            <td>Attention</td>
            <td id="attention"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Date</td>
            <td id="date"><?php echo htmlspecialchars($record['Date']); ?></td>
          </tr>
          <tr>
            <td>Vessel</td>
            <td id="vessel"><?php echo htmlspecialchars($record['Vessel']); ?></td>
          </tr>
          <tr>
            <td>ETD/ETA</td>
            <td id="etd-eta"><?php echo htmlspecialchars($record['ETA']); ?></td>
          </tr>
          <tr>
            <td>Ref No.</td>
            <td id="ref-no"><?php echo htmlspecialchars($record['RefNum']); ?></td>
          </tr>
          <tr>
            <td>Destination/Origin</td>
            <td id="destination-origin"><?php echo htmlspecialchars($record['DestinationOrigin']); ?></td>
          </tr>
          <tr>
            <td>E.R.</td>
            <td id="er"><?php echo htmlspecialchars($record['ER']); ?></td>
          </tr>
          <tr>
            <td>BL/HBL No</td>
            <td id="bl-hbl-no"><?php echo htmlspecialchars($record['BHNum']); ?></td>
          </tr>
          <tr>
            <td>Nature of Goods</td>
            <td id="nature-of-goods"><?php echo htmlspecialchars($record['NatureOfGoods']); ?></td>
          </tr>
          <tr>
            <td>Packages</td>
            <td id="package"><?php echo htmlspecialchars($record['Packages']); ?></td>
          </tr>
          <tr>
            <td>Weight</td>
            <td id="weight"><?php echo htmlspecialchars($record['Weight']); ?></td>
          </tr>
          <tr>
            <td>Volume</td>
            <td id="volume"><?php echo htmlspecialchars($record['Volume']); ?></td>
          </tr>
          <tr>
            <td>Package Type</td>
            <td id="package-type"><?php echo htmlspecialchars($record['PackageType']); ?></td>
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
            <td id="ocean-freight-95"><?php echo htmlspecialchars($record['OceanFreight95']); ?></td>
          </tr>
          <tr>
            <td>Advance Shipping Lines</td>
            <td id="advance-shipping-lines"><?php echo htmlspecialchars($record['AdvanceShipping']); ?></td>
          </tr>
          <tr>
            <td>Processing</td>
            <td id="processing"><?php echo htmlspecialchars($record['Processing']); ?></td>
          </tr>
          <tr>
            <td>5 Ocean Freight</td>
            <td id="ocean-freight-5"><?php echo htmlspecialchars($record['OceanFreight5	']); ?></td>
          </tr>
          <tr>
            <td>Brokerage Fee</td>
            <td id="brokerage-fee"><?php echo htmlspecialchars($record['BrokerageFee']); ?></td>
          </tr>
          <tr>
            <td>VAT 12%</td>
            <td id="vat-12"><?php echo htmlspecialchars($record['Vat12']); ?></td>
          </tr>
          <tr>
            <td>LCL Charge</td>
            <td id="lcl-charge"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Docs Fee</td>
            <td id="docs-fee"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Documentation</td>
            <td id="documentation"><?php echo htmlspecialchars($record['Documentation']); ?></td>
          </tr>
          <tr>
            <td>Turn Over Fee</td>
            <td id="turn-over-fee"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Handling</td>
            <td id="handling"><?php echo htmlspecialchars($record['Handling']); ?></td>
          </tr>
          <tr>
            <td>Manifest Fee</td>
            <td id="manifest-fee"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>THC</td>
            <td id="thc"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>CIC</td>
            <td id="cic"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>ECRS</td>
            <td id="ecrs"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>PSS</td>
            <td id="pss"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Origin</td>
            <td id="origin"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Shipping Line</td>
            <td id="shipping-line"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>FCL Charge</td>
            <td id="fcl-charge"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>ICCO</td>
            <td id="icco"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Arrastre</td>
            <td id="arrastre"><?php echo htmlspecialchars($record['Arrastre']); ?></td>
          </tr>
          <tr>
            <td>Wharfage</td>
            <td id="wharfage"><?php echo htmlspecialchars($record['	Wharfage']); ?></td>
          </tr>
          <tr>
            <td>Forms/Stamps</td>
            <td id="forms-stamps"><?php echo htmlspecialchars($record['FormsStamps']); ?></td>
          </tr>
          <tr>
            <td>Photocopy/Notarial</td>
            <td id="photocopy-notarial"><?php echo htmlspecialchars($record['PhotocopyNotarial']); ?></td>
          </tr>
          <tr>
            <td>E2M Lodgement</td>
            <td id="e2m-lodgement"><?php echo htmlspecialchars($record['E2MLodge']); ?></td>
          </tr>
          <tr>
            <td>Stuffing (Mano)</td>
            <td id="stuffing-mano"><?php echo htmlspecialchars($record['ManualStuffing']); ?></td>
          </tr>
          <tr>
            <td>Trucking</td>
            <td id="trucking"><?php echo htmlspecialchars($record['Attention']); ?></td>
          </tr>
          <tr>
            <td>Others</td>
            <td id="others"><?php echo htmlspecialchars($record['Others']); ?></td>
          </tr>
          <tr>
            <td>Total</td>
            <td id="total"><?php echo htmlspecialchars($record['Total']); ?></td>
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
            <td id="prepared-by"><?php echo htmlspecialchars($record['Total']); ?></td>
          </tr>
          <tr>
            <td>Approved By</td>
            <td id="approved-by"><?php echo htmlspecialchars($record['Total']); ?></td>
          </tr>
        </tbody>
      </table>


    </div>
    <div class="info-view">
      <div class="docu-information">
        <p class="ref-number"><?php echo htmlspecialchars($record['RefNum']); ?></p>
        <p class="document-type"><?php echo htmlspecialchars($record['DocType']); ?></p>
        <p class="date"><strong>Date Created:</strong> <?php echo htmlspecialchars($record['Date']); ?></p>
        <p class="date"><strong>Created By:</strong> <?php echo htmlspecialchars($record['Prepared_by']); ?></p>
        <p class="date"><strong>Date Modified:</strong> <?php echo htmlspecialchars($record['EditDate']); ?></p>
        <p class="date"><strong>Modified By:</strong> <?php echo htmlspecialchars($record['Edited_by']); ?></p>
      </div>

      <p class="comment-header"> Comments:
      <div class="comment-box">
        <textarea id="textbox" maxlength="250" oninput="updateCounter()" readonly><?php echo htmlspecialchars($record['Comment']); ?></textarea>
        <div class="button-container">
          <button class="edit-button" onclick="saveComment()">Edit</button>
          <button class="download-button" onclick="window.location.href='Download/GENERATE_EXCEL.php';">Download</button>
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

    function saveComment() {
      let comment = document.getElementById("textbox").value;
      alert("Comment saved: " + comment);

    }
  </script>
</body>

</html>