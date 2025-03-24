<?php

require 'db_agq.php';
session_start();

$refNum = isset($_GET['refnum']) ? $_GET['refnum'] : '';
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
</head>

<body>
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

      <?php 

      $dept = $record['Department'];

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
                  <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                  <td>BL Fee</td>
                  <td id='bl-fee'>".htmlspecialchars($record['BLFee'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                  <td>Manifest Fee</td>
                  <td id='manifest-fee'>".htmlspecialchars($record['ManifestFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>THC</td>
                  <td id='thc'>".htmlspecialchars($record['THC'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>CIC</td>
                  <td id='cic'>".htmlspecialchars($record['CIC'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>ECRS</td>
                  <td id='ecrs'>".htmlspecialchars($record['ECRS'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>PSS</td>
                  <td id='pss'>".htmlspecialchars($record['PSS'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Origin</td>
                  <td id='origin'>".htmlspecialchars($record['Origin'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td id='total'>Notes: ".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
              </tbody>
            </table>";

          }else if ($docType == "SOA" && $package == "FULL") {
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
                  <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                  <td>Handling</td>
                  <td id='handling'>".htmlspecialchars($record['Handling'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Turn Over Fee</td>
                  <td id='turn-over-fee'>".htmlspecialchars($record['TurnOverFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>BL Fee</td>
                  <td id='bl-fee'>".htmlspecialchars($record['BLFee'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                  <td>FCL Charge</td>
                  <td id='fcl-charge'>".htmlspecialchars($record['FCLCharge'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Documentation</td>
                  <td id='documentation'>".htmlspecialchars($record['Documentation'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Manifest Fee</td>
                  <td id='manifest-fee'>".htmlspecialchars($record['ManifestFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Shipping Lines</td>
                  <td id='shipping-line'>".htmlspecialchars($record['ShippingLine'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Ex-Work Charges</td>
                  <td id='ex-work-charges'>".htmlspecialchars($record['ExWorkCharges'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td id='total'>Notes: ".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
                  <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>LCL Charge</td>
                  <td id='lcl-charge'>".htmlspecialchars($record['LCLCharge'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Docs Fee</td>
                  <td id='docs-fee'>".htmlspecialchars($record['DocsFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Documentation</td>
                  <td id='documentation'>".htmlspecialchars($record['Documentation'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Turn Over Fee</td>
                  <td id='turn-over-fee'>".htmlspecialchars($record['TurnOverFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Handling</td>
                  <td id='handling'>".htmlspecialchars($record['Handling'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td id='total'>Notes: ".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
                </tbody>
            </table>";
          }else if ($docType == "Invoice" && $package == "FULL") {
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
                 <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>FCL Charge</td>
                 <td id='fcl-charge'>".htmlspecialchars($record['FCLCharge'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Documentation</td>
                 <td id='documentation'>".htmlspecialchars($record['Documentation'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Handling</td>
                 <td id='handling'>".htmlspecialchars($record['Handling'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>12% VAT</td>
                 <td id='vat-12'>".htmlspecialchars($record['Vat12'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
                 <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Forwarder</td>
                 <td id='forwarder'>".htmlspecialchars($record['Forwarder'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Warehouse Charges</td>
                 <td id='warehouse-charge'>".htmlspecialchars($record['WarehouseCharge'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>E-Lodgement</td>
                 <td id='eLodge'>".htmlspecialchars($record['ELodge'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Processing</td>
                 <td id='processing'>".htmlspecialchars($record['Processing'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Customs Forms/Stamps</td>
                 <td id='forms-stamps'>".htmlspecialchars($record['FormsStamps'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Photocopy/Notarial</td>
                 <td id='photocopy-notarial'>".htmlspecialchars($record['PhotocopyNotarial'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Documentation</td>
                 <td id='documentation'>".htmlspecialchars($record['Documentation'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Delivery Expense</td>
                 <td id='delivery-expense'>".htmlspecialchars($record['DeliveryExpense'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>MISC.,transpo,tel. Card</td>
                 <td id='miscellaneous'>".htmlspecialchars($record['Miscellaneous'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Door to Door Bacolod (all in)</td>
                 <td id='door2door'>".htmlspecialchars($record['Door2Door'] ?? 'N/A')."</td>
                </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
               </tr>
               </tbody>
           </table>";

          }else if ($docType == "SOA" && $package == "FULL") {
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
                 <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>THC</td>
                 <td id='thc'>".htmlspecialchars($record['THC'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>AISL</td>
                 <td id='eLodge'>".htmlspecialchars($record['AISL'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>GO Fast</td>
                 <td id='gofast'>".htmlspecialchars($record['GOFast'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Processing</td>
                 <td id='processing'>".htmlspecialchars($record['Processing'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Additional Processing</td>
                 <td id='additional-processing'>".htmlspecialchars($record['AdditionalProcessing'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Customs Forms/Stamps</td>
                 <td id='forms-stamps'>".htmlspecialchars($record['FormsStamps'] ?? 'N/A')."</td>
               </tr>
               <tr>
                  <td>Handling</td>
                  <td id='handling'>".htmlspecialchars($record['Handling'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Extra Handling Fee</td>
                  <td id='extra-handling'>".htmlspecialchars($record['ExtraHandlingFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                 <td>Photocopy/Notarial</td>
                 <td id='photocopy-notarial'>".htmlspecialchars($record['PhotocopyNotarial'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Clearance Expenses</td>
                 <td id='clearance-expenses'>".htmlspecialchars($record['ClearanceExpenses'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Hauling and Trucking</td>
                 <td id='hauling-trucking'>".htmlspecialchars($record['HaulingTrucking'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Additional Container</td>
                 <td id='additional-container'>".htmlspecialchars($record['AdditionalContainer'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>StuffingPlant</td>
                 <td id='stuffing-plant'>".htmlspecialchars($record['StuffingPlant'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>IED/Entry Encoding</td>
                 <td id='ied'>".htmlspecialchars($record['IED'] ?? 'N/A')."</td>
               </tr> <tr>
                 <td>Early Gate In</td>
                 <td id='early-gate-in'>".htmlspecialchars($record['EarlyGateIn'] ?? 'N/A')."</td>
               </tr> <tr>
                 <td>TABS</td>
                 <td id='tabs'>".htmlspecialchars($record['TABS'] ?? 'N/A')."</td>
               </tr> <tr>
                 <td>Docs Fee</td>
                 <td id='docs-fee'>".htmlspecialchars($record['DocsFee'] ?? 'N/A')."</td>
               </tr> 
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr> 
               <tr>
                 <td>Detention Charges</td>
                 <td id='detention-charges'>".htmlspecialchars($record['DetentionCharges'] ?? 'N/A')."</td>
               </tr>  
               <tr>
                 <td>Container Deposit</td>
                 <td id='container-deposit'>".htmlspecialchars($record['ContainerDeposit'] ?? 'N/A')."</td>
               </tr> 
               <tr>
                 <td>Late Charge</td>
                 <td id='late-charge'>".htmlspecialchars($record['LateCharge'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Late Collection</td>
                 <td id='late-collection'>".htmlspecialchars($record['LateCollection'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Demurrage</td>
                 <td id='demurrage'>".htmlspecialchars($record['Demurrage'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
                 <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Brokerage Fee</td>
                 <td id='brokerage-fee'>".htmlspecialchars($record['BrokerageFee'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>12% VAT</td>
                 <td id='vat-12'>".htmlspecialchars($record['Vat12'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
               </tr>
               </tbody>
           </table>";

          }else if ($docType == "Invoice" && $package == "FULL") {
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
                 <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Brokerage Fee</td>
                 <td id='brokerage-fee'>".htmlspecialchars($record['BrokerageFee'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>12% VAT</td>
                 <td id='vat-12'>".htmlspecialchars($record['Vat12'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Trucking Service</td>
                 <td id='trucking-service'>".htmlspecialchars($record['TruckingService'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
                  <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                  <td>Docs Fee</td>
                  <td id='docs-fee'>".htmlspecialchars($record['DocsFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>LCL Charge</td>
                  <td id='lcl-charge'>".htmlspecialchars($record['LCLCharge'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Export Processing</td>
                  <td id='export-processing'>".htmlspecialchars($record['ExportProcessing'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                 <td>Customs Forms/Stamps</td>
                 <td id='forms-stamps'>".htmlspecialchars($record['FormsStamps'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Arrastre/Wharfage/Storage</td>
                 <td id='arrastrewharf'>".htmlspecialchars($record['ArrastreWharf'] ?? 'N/A')."</td>
               </tr>
                <tr>
                  <td>E2M Fee</td>
                  <td id='e2m-lodge'>".htmlspecialchars($record['E2MLodge'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td id='total'>Notes: ".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
                </tr>
              </tbody>
            </table>";

          }else if ($docType == "SOA" && $package == "FULL") {
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
                  <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                  <td>THC</td>
                  <td id='thc'>".htmlspecialchars($record['THC'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Docs Fee</td>
                  <td id='docs-fee'>".htmlspecialchars($record['DocsFee'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>FAF</td>
                  <td id='faf'>".htmlspecialchars($record['FAF'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Seal Fee</td>
                  <td id='seal-fee'>".htmlspecialchars($record['SealFee'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                 <td>Storage</td>
                 <td id='storage'>".htmlspecialchars($record['Storage'] ?? 'N/A')."</td>
               </tr>
                <tr>
                  <td>Telex</td>
                  <td id='telex'>".htmlspecialchars($record['Telex'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td id='total'>Notes: ".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
                 <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Brokerage Fee</td>
                 <td id='brokerage-fee'>".htmlspecialchars($record['BrokerageFee'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
               </tr>
               </tbody>
           </table>";

          }else if ($docType == "Invoice" && $package == "FULL") {
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
                 <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>12% VAT</td>
                 <td id='vat-12'>".htmlspecialchars($record['Vat12'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
                 <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Advance Shipping Lines</td>
                 <td id='advance-shipping'>".htmlspecialchars($record['AdvanceShipping'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Processing</td>
                 <td id='processing'>".htmlspecialchars($record['Processing'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
               </tr>
               </tbody>
           </table>";

          }else if ($docType == "SOA" && $package == "FULL") {
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
                  <td id='ocean-freight-95'>".htmlspecialchars($record['OceanFreight95'] ?? 'N/A'). "</td>
                </tr>
                <tr>
                 <td>Arrastre</td>
                 <td id='arrastre'>".htmlspecialchars($record['ArrastreWharf'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Wharfage</td>
                 <td id='wharfage'>".htmlspecialchars($record['Wharfage'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Processing</td>
                 <td id='processing'>".htmlspecialchars($record['Processing'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Customs Forms/Stamps</td>
                 <td id='forms-stamps'>".htmlspecialchars($record['FormsStamps'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>Photocopy/Notarial</td>
                 <td id='photocopy-notarial'>".htmlspecialchars($record['PhotocopyNotarial'] ?? 'N/A')."</td>
               </tr>
                <tr>
                  <td>Documentation</td>
                  <td id='documentation'>".htmlspecialchars($record['Documentation'] ?? 'N/A')."</td>
                </tr>
                 <tr>
                  <td>E2M Lodgement</td>
                  <td id='e2m-lodge'>".htmlspecialchars($record['E2MLodge'] ?? 'N/A')."</td>
                </tr>
                 <tr>
                  <td>Stuffing (Mano)</td>
                  <td id='manual-stuffing'>".htmlspecialchars($record['ManualStuffing'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Handling</td>
                  <td id='handling'>".htmlspecialchars($record['Handling'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Others</td>
                  <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td>Total</td>
                  <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
                </tr>
                <tr>
                  <td id='total'>Notes: ".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
                 <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Brokerage Fee</td>
                 <td id='brokerage-fee'>".htmlspecialchars($record['BrokerageFee'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>50% Discount</td>
                 <td id='discount-50'>".htmlspecialchars($record['Discount50'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>12% VAT</td>
                 <td id='vat-12'>".htmlspecialchars($record['Vat12'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
               </tr>
               </tbody>
           </table>";
          }else if ($docType == "Invoice" && $package == "FULL") {
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
                 <td id='ocean-freight-5'>".htmlspecialchars($record['OceanFreight5'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Brokerage Fee</td>
                 <td id='brokerage-fee'>".htmlspecialchars($record['BrokerageFee'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>50% Discount</td>
                 <td id='discount-50'>".htmlspecialchars($record['Discount50'] ?? 'N/A')."</td>
               </tr>
                <tr>
                 <td>12% VAT</td>
                 <td id='vat-12'>".htmlspecialchars($record['Vat12'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Others</td>
                 <td id='others'>".htmlspecialchars($record['Others'] ?? 'N/A')."</td>
               </tr>
               <tr>
                 <td>Total</td>
                 <td id='total'>".htmlspecialchars($record['Total'] ?? 'N/A')."</td>
               </tr>
               <tr>
                <td>Notes</td>
                <td id='total'>".htmlspecialchars($record['Notes'] ?? 'N/A')."</td>
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
        <p class="date"><strong>Date Created:</strong> <?php echo htmlspecialchars($record['Date'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Created By:</strong> <?php echo htmlspecialchars($record['Prepared_by'] ?? 'N/A'); ?></p>
        <p class="date"><strong>Date Modified:</strong> <?php echo htmlspecialchars($record['EditDate'] ?? 'N/A'); ?></p>
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

      let url = `Download/GENERATE_EXCEL.php?refnum=${encodeURIComponent(refnum)}`;
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