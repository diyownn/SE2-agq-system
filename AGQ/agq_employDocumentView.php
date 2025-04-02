<?php

require 'db_agq.php';
session_start();

$refNum = isset($_GET['refNum']) ? $_GET['refNum'] : '';
$url = isset($_GET['url']) ? $_GET['url'] : '';
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

if (!$url) {
  header("Location: UNAUTHORIZED.php?error=401u");
}



function selectRecords($conn, $role, $refNum)
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
  <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">

</head>

<body style="background-image: url('vdbg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
<a href="agq_transactionCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 40px; top: 60px;">←</a>
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
            <td id="date"><?php echo isset($record['Date']) ? htmlspecialchars(date("d F, Y", strtotime($record['Date']))) : 'N/A'; ?></td>
          </tr>
          <tr>
            <td>Vessel</td>
            <td id="vessel"><?php echo htmlspecialchars($record['Vessel'] ?? 'N/A'); ?></td>
          </tr>
          <tr>
            <td>ETD/ETA</td>
            <td id="etd-eta"><?php echo isset($record['ETA']) ? htmlspecialchars(date("F d, Y", strtotime($record['ETA']))) : 'N/A'; ?></td>
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

      <?php 

      $dept = $record['Department'];
      $docType = $record['DocType'];
      $_SESSION['DocType'] = $docType;

      switch ($dept) {

        case "Import Forwarding";
          $docType = $record['DocType'];
          $package = $record['PackageType'];
          

          if ($docType == "SOA" && $package == "LCL") {
            echo" 
            <table>
              <thead>
                <tr>
                  <th>Reimbursable Charges</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>95% Ocean Freight</td>
                  <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>BL Fee</td>
                  <td id='bl-fee'>P".htmlspecialchars(number_format($record['BLFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Manifest Fee</td>
                  <td id='manifest-fee'>P".htmlspecialchars(number_format($record['ManifestFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>THC</td>
                  <td id='thc'>P".htmlspecialchars(number_format($record['THC'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>CIC</td>
                  <td id='cic'>P".htmlspecialchars(number_format($record['CIC'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>ECRS</td>
                  <td id='ecrs'>P".htmlspecialchars(number_format($record['ECRS'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>PSS</td>
                  <td id='pss'>P".htmlspecialchars(number_format($record['PSS'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Origin</td>
                  <td id='origin'>P".htmlspecialchars(number_format($record['Origin'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Notes</td>
                  <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
              </tbody>
            </table>";

          }else if ($docType == "SOA" && $package == "Full Container") {
            echo" 
            <table>
              <thead>
                <tr>
                  <th>Reimbursable Charges</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>95% Ocean Freight</td>
                  <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Handling</td>
                  <td id='handling'>P".htmlspecialchars(number_format($record['Handling'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Turn Over Fee</td>
                  <td id='turn-over-fee'>P".htmlspecialchars(number_format($record['TurnOverFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>BL Fee</td>
                  <td id='bl-fee'>P".htmlspecialchars(number_format($record['BLFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>FCL Charge</td>
                  <td id='fcl-charge'>P".htmlspecialchars(number_format($record['FCLCharge'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Documentation</td>
                  <td id='documentation'>P".htmlspecialchars(number_format($record['Documentation'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Manifest Fee</td>
                  <td id='manifest-fee'>P".htmlspecialchars(number_format($record['ManifestFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Shipping Lines</td>
                  <td id='shipping-line'>P".htmlspecialchars(number_format($record['ShippingLine'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Ex-Work Charges</td>
                  <td id='ex-work-charges'>P".htmlspecialchars(number_format($record['ExWorkCharges'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Notes</td>
                  <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
              </tbody>
            </table>";
          }else if ($docType == "Invoice" && $package == "LCL") {
            echo "
            <table>
              <thead>
                <tr>
                  <th>Reimbursable Charges</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>5% Ocean Freight</td>
                  <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>LCL Charge</td>
                  <td id='lcl-charge'>P".htmlspecialchars(number_format($record['LCLCharge'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Docs Fee</td>
                  <td id='docs-fee'>P".htmlspecialchars(number_format($record['DocsFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Documentation</td>
                  <td id='documentation'>P".htmlspecialchars(number_format($record['Documentation'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Turn Over Fee</td>
                  <td id='turn-over-fee'>P".htmlspecialchars(number_format($record['TurnOverFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Handling</td>
                  <td id='handling'>P".htmlspecialchars(number_format($record['Handling'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Notes</td>
                  <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
                </tbody>
            </table>";
          }else if ($docType == "Invoice" && $package == "Full Container") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>5% Ocean Freight</td>
                <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>FCL Charge</td>
                <td id='fcl-charge'>P".htmlspecialchars(number_format($record['FCLCharge'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Documentation</td>
                <td id='documentation'>P".htmlspecialchars(number_format($record['Documentation'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Handling</td>
                <td id='handling'>P".htmlspecialchars(number_format($record['Handling'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>12% VAT</td>
                <td id='vat-12'>P".htmlspecialchars(number_format($record['Vat12'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";

          }

          break;

        case "Import Brokerage";
          $docType = $record['DocType'];
          $package = $record['PackageType'];

          if ($docType == "SOA" && $package == "LCL") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>95% Ocean Freight</td>
                <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Forwarder</td>
                <td id='forwarder'>P".htmlspecialchars(number_format($record['Forwarder'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Warehouse Charges</td>
                <td id='warehouse-charge'>P".htmlspecialchars(number_format($record['WarehouseCharge'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>E-Lodgement</td>
                <td id='eLodge'>P".htmlspecialchars(number_format($record['ELodge'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Processing</td>
                <td id='processing'>P".htmlspecialchars(number_format($record['Processing'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Customs Forms/Stamps</td>
                <td id='forms-stamps'>P".htmlspecialchars(number_format($record['FormsStamps'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Photocopy/Notarial</td>
                <td id='photocopy-notarial'>P".htmlspecialchars(number_format($record['PhotocopyNotarial'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Documentation</td>
                <td id='documentation'>P".htmlspecialchars(number_format($record['Documentation'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Delivery Expense</td>
                <td id='delivery-expense'>P".htmlspecialchars(number_format($record['DeliveryExpense'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>MISC.,transpo,tel. Card</td>
                <td id='miscellaneous'>P".htmlspecialchars(number_format($record['Miscellaneous'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Door to Door Bacolod (all in)</td>
                <td id='door2door'>P".htmlspecialchars(number_format($record['Door2Door'] ?? 0, 2))."</td>
                </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";

          }else if ($docType == "SOA" && $package == "Full Container") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>95% Ocean Freight</td>
                <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>THC</td>
                <td id='thc'>P".htmlspecialchars(number_format($record['THC'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>AISL</td>
                <td id='eLodge'>P".htmlspecialchars(number_format($record['AISL'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>GO Fast</td>
                <td id='gofast'>P".htmlspecialchars(number_format($record['GOFast'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Processing</td>
                <td id='processing'>P".htmlspecialchars(number_format($record['Processing'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Additional Processing</td>
                <td id='additional-processing'>P".htmlspecialchars(number_format($record['AdditionalProcessing'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Customs Forms/Stamps</td>
                <td id='forms-stamps'>P".htmlspecialchars(number_format($record['FormsStamps'] ?? 0, 2))."</td>
              </tr>
              <tr>
                  <td>Handling</td>
                  <td id='handling'>P".htmlspecialchars(number_format($record['Handling'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Extra Handling Fee</td>
                  <td id='extra-handling'>P".htmlspecialchars(number_format($record['ExtraHandlingFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                <td>Photocopy/Notarial</td>
                <td id='photocopy-notarial'>P".htmlspecialchars(number_format($record['PhotocopyNotarial'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Clearance Expenses</td>
                <td id='clearance-expenses'>P".htmlspecialchars(number_format($record['ClearanceExpenses'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Hauling and Trucking</td>
                <td id='hauling-trucking'>P".htmlspecialchars(number_format($record['HaulingTrucking'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Additional Container</td>
                <td id='additional-container'>P".htmlspecialchars(number_format($record['AdditionalContainer'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>StuffingPlant</td>
                <td id='stuffing-plant'>P".htmlspecialchars(number_format($record['StuffingPlant'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>IED/Entry Encoding</td>
                <td id='ied'>P".htmlspecialchars(number_format($record['IED'] ?? 0, 2))."</td>
              </tr> <tr>
                <td>Early Gate In</td>
                <td id='early-gate-in'>P".htmlspecialchars(number_format($record['EarlyGateIn'] ?? 0, 2))."</td>
              </tr> <tr>
                <td>TABS</td>
                <td id='tabs'>P".htmlspecialchars(number_format($record['TABS'] ?? 0, 2))."</td>
              </tr> <tr>
                <td>Docs Fee</td>
                <td id='docs-fee'>P".htmlspecialchars(number_format($record['DocsFee'] ?? 0, 2))."</td>
              </tr> 
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr> 
              <tr>
                <td>Detention Charges</td>
                <td id='detention-charges'>P".htmlspecialchars(number_format($record['DetentionCharges'] ?? 0, 2))."</td>
              </tr>  
              <tr>
                <td>Container Deposit</td>
                <td id='container-deposit'>P".htmlspecialchars(number_format($record['ContainerDeposit'] ?? 0, 2))."</td>
              </tr> 
              <tr>
                <td>Late Charge</td>
                <td id='late-charge'>P".htmlspecialchars(number_format($record['LateCharge'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Late Collection</td>
                <td id='late-collection'>P".htmlspecialchars(number_format($record['LateCollection'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Demurrage</td>
                <td id='demurrage'>P".htmlspecialchars(number_format($record['Demurrage'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";

          }else if ($docType == "Invoice" && $package == "LCL") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>5% Ocean Freight</td>
                <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Brokerage Fee</td>
                <td id='brokerage-fee'>P".htmlspecialchars(number_format($record['BrokerageFee'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>12% VAT</td>
                <td id='vat-12'>P".htmlspecialchars(number_format($record['Vat12'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";

          }else if ($docType == "Invoice" && $package == "Full Container") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>5% Ocean Freight</td>
                <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Brokerage Fee</td>
                <td id='brokerage-fee'>P".htmlspecialchars(number_format($record['BrokerageFee'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>12% VAT</td>
                <td id='vat-12'>P".htmlspecialchars(number_format($record['Vat12'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Trucking Service</td>
                <td id='trucking-service'>P".htmlspecialchars(number_format($record['TruckingService'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";
          }

          break;

        case "Export Forwarding";
          $docType = $record['DocType'];
          $package = $record['PackageType'];

          if ($docType == "SOA" && $package == "LCL") {
            echo" 
            <table>
              <thead>
                <tr>
                  <th>Reimbursable Charges</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>95% Ocean Freight</td>
                  <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Docs Fee</td>
                  <td id='docs-fee'>P".htmlspecialchars(number_format($record['DocsFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>LCL Charge</td>
                  <td id='lcl-charge'>P".htmlspecialchars(number_format($record['LCLCharge'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Export Processing</td>
                  <td id='export-processing'>P".htmlspecialchars(number_format($record['ExportProcessing'] ?? 0, 2))."</td>
                </tr>
                <tr>
                <td>Customs Forms/Stamps</td>
                <td id='forms-stamps'>P".htmlspecialchars(number_format($record['FormsStamps'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Arrastre/Wharfage/Storage</td>
                <td id='arrastrewharf'>P".htmlspecialchars(number_format($record['ArrastreWharf'] ?? 0, 2))."</td>
              </tr>
                <tr>
                  <td>E2M Fee</td>
                  <td id='e2m-lodge'>P".htmlspecialchars(number_format($record['E2MLodge'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
                </tr>
                <tr>
                 <td>Notes</td>
                  <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
              </tbody>
            </table>";

          }else if ($docType == "SOA" && $package == "Full Container") {
            echo" 
            <table>
              <thead>
                <tr>
                  <th>Reimbursable Charges</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>95% Ocean Freight</td>
                  <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>THC</td>
                  <td id='thc'>P".htmlspecialchars(number_format($record['THC'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Docs Fee</td>
                  <td id='docs-fee'>P".htmlspecialchars(number_format($record['DocsFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>FAF</td>
                  <td id='faf'>P".htmlspecialchars(number_format($record['FAF'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Seal Fee</td>
                  <td id='seal-fee'>P".htmlspecialchars(number_format($record['SealFee'] ?? 0, 2))."</td>
                </tr>
                <tr>
                <td>Storage</td>
                <td id='storage'>P".htmlspecialchars(number_format($record['Storage'] ?? 0, 2))."</td>
              </tr>
                <tr>
                  <td>Telex</td>
                  <td id='telex'>P".htmlspecialchars(number_format($record['Telex'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
                </tr>
                <tr>
                 <td>Notes</td>
                  <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
              </tbody>
            </table>";
          }else if ($docType == "Invoice" && $package == "LCL") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>5% Ocean Freight</td>
                <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Brokerage Fee</td>
                <td id='brokerage-fee'>P".htmlspecialchars(number_format($record['BrokerageFee'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";

          }else if ($docType == "Invoice" && $package == "Full Container") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>5% Ocean Freight</td>
                <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>12% VAT</td>
                <td id='vat-12'>P".htmlspecialchars(number_format($record['Vat12'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";
          }

          break;

        case "Export Brokerage";
          $docType = $record['DocType'];
          $package = $record['PackageType'];

          if ($docType == "SOA" && $package == "LCL") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>95% Ocean Freight</td>
                <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Advance Shipping Lines</td>
                <td id='advance-shipping'>P".htmlspecialchars(number_format($record['AdvanceShipping'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Processing</td>
                <td id='processing'>P".htmlspecialchars(number_format($record['Processing'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";

          }else if ($docType == "SOA" && $package == "Full Container") {
            echo" 
            <table>
              <thead>
                <tr>
                  <th>Reimbursable Charges</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>95% Ocean Freight</td>
                  <td id='ocean-freight-95'>P".htmlspecialchars(number_format($record['OceanFreight95'] ?? 0, 2))."</td>
                </tr>
                <tr>
                <td>Arrastre</td>
                <td id='arrastre'>P".htmlspecialchars(number_format($record['ArrastreWharf'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Wharfage</td>
                <td id='wharfage'>P".htmlspecialchars(number_format($record['Wharfage'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Processing</td>
                <td id='processing'>P".htmlspecialchars(number_format($record['Processing'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Customs Forms/Stamps</td>
                <td id='forms-stamps'>P".htmlspecialchars(number_format($record['FormsStamps'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>Photocopy/Notarial</td>
                <td id='photocopy-notarial'>P".htmlspecialchars(number_format($record['PhotocopyNotarial'] ?? 0, 2))."</td>
              </tr>
                <tr>
                  <td>Documentation</td>
                  <td id='documentation'>P".htmlspecialchars(number_format($record['Documentation'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>E2M Lodgement</td>
                  <td id='e2m-lodge'>P".htmlspecialchars(number_format($record['E2MLodge'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Stuffing (Mano)</td>
                  <td id='manual-stuffing'>P".htmlspecialchars(number_format($record['ManualStuffing'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Handling</td>
                  <td id='handling'>P".htmlspecialchars(number_format($record['Handling'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
                </tr>
                <tr>
                 <td>Notes</td>
                  <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
              </tbody>
            </table>";

          }else if ($docType == "Invoice" && $package == "LCL") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>5% Ocean Freight</td>
                <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Brokerage Fee</td>
                <td id='brokerage-fee'>P".htmlspecialchars(number_format($record['BrokerageFee'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>50% Discount</td>
                <td id='discount-50'>P".htmlspecialchars(number_format($record['Discount50'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>12% VAT</td>
                <td id='vat-12'>P".htmlspecialchars(number_format($record['Vat12'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";
          }else if ($docType == "Invoice" && $package == "Full Container") {
            echo "
            <table>
            <thead>
              <tr>
                <th>Reimbursable Charges</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>5% Ocean Freight</td>
                <td id='ocean-freight-5'>P".htmlspecialchars(number_format($record['OceanFreight5'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Brokerage Fee</td>
                <td id='brokerage-fee'>P".htmlspecialchars(number_format($record['BrokerageFee'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>50% Discount</td>
                <td id='discount-50'>P".htmlspecialchars(number_format($record['Discount50'] ?? 0, 2))."</td>
              </tr>
                <tr>
                <td>12% VAT</td>
                <td id='vat-12'>P".htmlspecialchars(number_format($record['Vat12'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Others</td>
                <td id='others'>P".htmlspecialchars(number_format($record['Others'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Total</td>
                <td id='total'>P".htmlspecialchars(number_format($record['Total'] ?? 0, 2))."</td>
              </tr>
              <tr>
                <td>Notes</td>
                <td id='notes'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
              </tr>
              </tbody>
          </table>";
          
          }

          break;
      }

      ?>

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
        <p class="ref-number"><?php echo htmlspecialchars($refNum) ?? 'N/A'; ?></p>
        <p class="document-type"><?php echo htmlspecialchars($record['DocType'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Date Created:</strong> <?php echo htmlspecialchars(date("d F, Y", strtotime($record['Date'])) ?? 'N/A'); ?></p>
        <p class="date"><strong>Created By:</strong> <?php echo htmlspecialchars($record['Prepared_by'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Date Modified:</strong> <?php echo htmlspecialchars(date("F d, Y h:i A", strtotime($record['EditDate'])) ?? 'N/A'); ?></p>
        <p class="date"><strong>Modified By:</strong> <?php echo htmlspecialchars($record['Edited_by'] ?? 'N/A'); ?></p>
      </div>

      <p class="comment-header"> Comments:
      <div class="comment-box">
        <textarea id="textbox" maxlength="250" oninput="updateCounter()" readonly><?php echo htmlspecialchars($record['Comment'] ?? 'N/A'); ?></textarea>
        <div class="button-container">
          <button class="edit-button" onclick="redirectToDocument2('<?php echo htmlspecialchars($refNum); ?>', '<?php echo htmlspecialchars($record['DocType'] ?? ''); ?>')">
            Edit
          </button>
          <button class="download-button" onclick="downloadDocument('<?php echo htmlspecialchars($refNum); ?>')">Download</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    function downloadDocument(refnum) {
      if (!refnum) {
        console.log("No refnum provided");
        return;
      }

      let url = `Download/GENERATE_EXCEL.php?refNum=${encodeURIComponent(refnum)}`;
      console.log(url)
      window.location.href = url;
    }


    function redirectToDocument2(refnum, doctype) {
      let url = "";
      switch (doctype) {
        case "Invoice":
          url = "agq_invoiceCatcher.php?refNum=" + encodeURIComponent(refnum);
          break;
        case "SOA":
          url = "agq_soaCatcher.php?refNum=" + encodeURIComponent(refnum);
          break;
        default:
          break;
      }

      // Redirect to the determined URL
      window.location.href = url;
    }

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