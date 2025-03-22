<?php

require 'db_agq.php';
session_start();
$docType = isset($_GET['doctype']) ? $_GET['doctype'] : '';
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

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

  <div class="top-container">
    <div class="dept-container">
      <div class="header-container">
        <div class="dept-label">
          <?php echo htmlspecialchars($role); ?>
        </div>
        <div class="company-label">
          <?php echo htmlspecialchars($company); ?>
        </div>
        <div class="selected-doctype">
          <?php echo htmlspecialchars($docType); ?>
        </div>
      </div>
    </div>
  </div>

  <a href="agq_employTransactionView.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>

  <div class="container">
    <div class="document-view">
      <table class="document-table">
        <thead class="transaction-detail-table">
          <tr>
            <th>Transaction Details</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>To</td>
            <td>EURO-MED</td>
          </tr>
          <tr>
            <td>Address</td>
            <td>BLDG. 1000</td>
          </tr>
          <tr>
            <td>TIN</td>
            <td>00-00-00</td>
          </tr>
          <tr>
            <td>Attention</td>
            <td>Ma'am Lolit</td>
          </tr>
          <tr>
            <td>Date</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>Vessel</td>
            <td>CNC BANGKOK ORMCFSINC</td>
          </tr>
          <tr>
            <td>ETD/ETA</td>
            <td>04/04/25</td>
          </tr>
          <tr>
            <td>Ref No.</td>
            <td>aaaaaa </td>
          </tr>
          <tr>
            <td>Destination/Origin</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>E.R.</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>BL/HBL No</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>Nature of Goods</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>Packages</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>Weight</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>Volume</td>
            <td>01/02/25 </td>
          </tr>
          <tr>
            <td>Package</td>
            <td>01/02/25 </td>
          </tr>

          <thead>
            <tr>
              <th>Package Type</th>
            </tr>
          </thead>
        <tbody>
          <tr>
            <td>LCL</td>
          </tr>


          <thead>
            <tr>
              <th>Document ID</th>
              <th>Document Name</th>
            </tr>
          </thead>
        <tbody>
          <tr>
            <td>DOC-001</td>
            <td>Employee Contract</td>
          </tr>
          <tr>
            <td>DOC-002</td>
            <td>Confidentiality Agreement</td>
          </tr>
          <tr>
            <td>DOC-003</td>
            <td>Performance Review</td>
          </tr>
          <tr>
            <td>DOC-004</td>
            <td>RequestExpense Report</td>
          </tr>
          <tr>
            <td>DOC-005</td>
            <td>Leave Request</td>
          </tr>
          <tr>
            <th>Document ID</th>
            <th>Document Name</th>
          </tr>
          </thead>
        <tbody>
          <tr>
            <td>DOC-001</td>
            <td>Employee Contract</td>
          </tr>
          <tr>
            <td>DOC-002</td>
            <td>Confidentiality Agreement</td>
          </tr>
          <tr>
            <td>DOC-003</td>
            <td>Performance Review</td>
          </tr>
          <tr>
            <td>DOC-004</td>
            <td>Expense Report</td>
          </tr>
          <tr>
            <td>DOC-005</td>
            <td>Leave Request</td>
          </tr>
          <tr>
            <th>Document ID</th>
            <th>Document Name</th>
          </tr>
          </thead>
        <tbody>
          <tr>
            <td>DOC-001</td>
            <td>Employee Contract</td>
          </tr>
          <tr>
            <td>DOC-002</td>
            <td>Confidentiality Agreement</td>
          </tr>
          <tr>
            <td>DOC-003</td>
            <td>Performance Review</td>
          </tr>
          <tr>
            <td>DOC-004</td>
            <td>Expense Report</td>
          </tr>
          <tr>
            <td>DOC-005</td>
            <td>Leave Request</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="info-view">
      <div class="docu-information">
        <p class="ref-number">IB-0000</p>
        <p class="document-type">Statement of Account</p>
        <p class="date"><strong>Date Created:</strong> 01/01/2025</p>
        <p class="date"><strong>Created By:</strong> John Smith</p>
        <p class="date"><strong>Date Modified:</strong> 01/01/2025</p>
        <p class="date"><strong>Modified By:</strong> Mary Russell</p>
      </div>

      <p class="comment-header"> Comments:
      <div class="comment-box">
        <textarea id="textbox" maxlength="250" oninput="updateCounter()" readonly></textarea>
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