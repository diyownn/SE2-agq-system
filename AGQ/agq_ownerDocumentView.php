<?php
/**require 'db_agq.php';

session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';
$docType = isset($_GET['doctype']) ? $_GET['doctype'] : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';

if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}

if (!$company) {
    header("Location: UNAUTHORIZED.php?error=401c");
}

if(!$docType){
    header("Location: UNAUTHORIZED.php?error=401t");
}
    **/
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
            <td id="to">EURO-MED</td>
          </tr>
          <tr>
            <td>Address</td>
            <td id="address">BLDG. 1000</td>
          </tr>
          <tr>
            <td>TIN</td>
            <td id="tin">00-00-00</td>
          </tr>
          <tr>
            <td>Attention</td>
            <td id="attention">Ma'am Lolit</td>
          </tr>
          <tr>
            <td>Date</td>
            <td id="date">01/02/25</td>
          </tr>
          <tr>
            <td>Vessel</td>
            <td id="vessel">CNC BANGKOK ORMCFSINC</td>
          </tr>
          <tr>
            <td>ETD/ETA</td>
            <td id="etd-eta">04/04/25</td>
          </tr>
          <tr>
            <td>Ref No.</td>
            <td id="ref-no">EB102-11/12</td>
          </tr>
          <tr>
            <td>Destination/Origin</td>
            <td id="destination-origin">aaaa</td>
          </tr>
          <tr>
            <td>E.R.</td>
            <td id="er">aaaa</td>
          </tr>
          <tr>
            <td>BL/HBL No</td>
            <td id="bl-hbl-no">aaaaa</td>
          </tr>
          <tr>
            <td>Nature of Goods</td>
            <td id="nature-of-goods">aaaa</td>
          </tr>
          <tr>
            <td>Packages</td>
            <td id="package">aaaa</td>
          </tr>
          <tr>
            <td>Weight</td>
            <td id="weight">aaaa</td>
          </tr>
          <tr>
            <td>Volume</td>
            <td id="volume">aaaa</td>
          </tr>
          <tr>
            <td>Package Type</td>
            <td id="package-type">aaaaa</td>
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
            <td id="ocean-freight-95">50304</td>
          </tr>
          <tr>
            <td>Advance Shipping Lines</td>
            <td id="advance-shipping-lines">50304</td>
          </tr>
          <tr>
            <td>Processing</td>
            <td id="processing">50304</td>
          </tr>
          <tr>
            <td>5 Ocean Freight</td>
            <td id="ocean-freight-5">50304</td>
          </tr>
          <tr>
            <td>Brokerage Fee</td>
            <td id="brokerage-fee">50304</td>
          </tr>
          <tr>
            <td>VAT 12%</td>
            <td id="vat-12">50304</td>
          </tr>
          <tr>
            <td>LCL Charge</td>
            <td id="lcl-charge">aaaaa</td>
          </tr>
          <tr>
            <td>Docs Fee</td>
            <td id="docs-fee">aaaaa</td>
          </tr>
          <tr>
            <td>Documentation</td>
            <td id="documentation">aaaaa</td>
          </tr>
          <tr>
            <td>Turn Over Fee</td>
            <td id="turn-over-fee">aaaaa</td>
          </tr>
          <tr>
            <td>Handling</td>
            <td id="handling">aaaaa</td>
          </tr>
          <tr>
            <td>Manifest Fee</td>
            <td id="manifest-fee">aaaaa</td>
          </tr>
          <tr>
            <td>THC</td>
            <td id="thc">aaaaa</td>
          </tr>
          <tr>
            <td>CIC</td>
            <td id="cic">aaaaa</td>
          </tr>
          <tr>
            <td>ECRS</td>
            <td id="ecrs">aaaaa</td>
          </tr>
          <tr>
            <td>PSS</td>
            <td id="pss">aaaaa</td>
          </tr>
          <tr>
            <td>Origin</td>
            <td id="origin">aaaaa</td>
          </tr>
          <tr>
            <td>Shipping Line</td>
            <td id="shipping-line">aaaaa</td>
          </tr>
          <tr>
            <td>FCL Charge</td>
            <td id="fcl-charge">aaaaa</td>
          </tr>
          <tr>
            <td>ICCO</td>
            <td id="icco">aaaaa</td>
          </tr>
          <tr>
            <td>Arrastre</td>
            <td id="arrastre">aaaaa</td>
          </tr>
          <tr>
            <td>Wharfage</td>
            <td id="wharfage">aaaaa</td>
          </tr>
          <tr>
            <td>Forms/Stamps</td>
            <td id="forms-stamps">aaaaa</td>
          </tr>
          <tr>
            <td>Photocopy/Notarial</td>
            <td id="photocopy-notarial">aaaaa</td>
          </tr>
          <tr>
            <td>E2M Lodgement</td>
            <td id="e2m-lodgement">aaaaa</td>
          </tr>
          <tr>
            <td>Stuffing (Mano)</td>
            <td id="stuffing-mano">aaaaa</td>
          </tr>
          <tr>
            <td>Trucking</td>
            <td id="trucking">aaaaa</td>
          </tr>
          <tr>
            <td>Others</td>
            <td id="others">50304</td>
          </tr>
          <tr>
            <td>Total</td>
            <td id="total">50304</td>
          </tr>
        </tbody>
      </table>

      <table class = "approvals-table">
        <thead>
          <tr>
            <th>Approvals</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Prepared By</td>
            <td id="prepared-by">AGQ</td>
          </tr>
          <tr>
            <td>Approved By</td>
            <td id="approved-by">AGQ</td>
          </tr>
        </tbody>
      </table>
        </div>
        <div class="info-view">
            <div class="docu-information">
                <p class="ref-number" id="ref-number">IB-0000</p>
                <p class="document-type" id="docType">Statement of Account</p>  
                <p class="date"><strong>Date Created:</strong> 01/01/2025</p>
                <p class="date"><strong>Created By:</strong> John Smith</p>
                <p class="date"><strong>Date Modified:</strong> 01/01/2025</p>
                <p class="date"><strong>Modified By:</strong> Mary Russell</p>
            <div class="comment-box">
                <textarea id="textbox" id="comments" maxlength="250" oninput="updateCounter()"></textarea>
                <div class="counter" id="counter">0/250</div>
                <div class="button-container" id="save-button">
                    <button class="save-button" onclick="saveComment()">Save</button>
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
            // In a real application, you would send this to your server
        }
    </script>
</body>

</html>