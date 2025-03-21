<?php

require 'db_agq.php';

$docType = isset($_SESSION['DocType']) ? $_SESSION['DocType'] : '';
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
  
  <div class="container">
    <div class="document-view">
    <table class="document-table">
        <thead class = "transaction-detail-table">
          <tr>
            <th>Transaction Details</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>To</td>
            <td id = "to">EURO-MED</td>
          </tr>
          <tr>
            <td>Address</td>
            <td id = "address">BLDG. 1000</td>
          </tr>
          <tr>
            <td>TIN</td>
            <td id = "tin">00-00-00</td>
          </tr>
          <tr>
            <td>Attention</td>
            <td id = "attention">Ma'am Lolit</td>
          </tr>
          <tr>
            <td>Date</td>
            <td id = "date">01/02/25 </td>
          </tr>
          <tr>
            <td>Vessel</td>
            <td id = "vessel">CNC BANGKOK ORMCFSINC</td>
          </tr>
          <tr>
            <td>ETD/ETA</td>
            <td id = "etd-eta">04/04/25</td>
          </tr>
          <tr>
            <td>Ref No.</td>
            <td id = "ref-no">EB102-11/12 </td>
          </tr>
          <tr>
            <td>Destination/Origin</td>
            <td id = "destination-origin">aaaa </td>
          </tr>
          <tr>
            <td>E.R.</td>
            <td id = "er">aaaa </td>
          </tr>
          <tr>
            <td>BL/HBL No</td>
            <td id = "bl-hbl-no">aaaaa </td>
          </tr>
          <tr>
            <td>Nature of Goods</td>
            <td id = "nature-of-goods">aaaa </td>
          </tr>
          <tr>
            <td>Packages</td>
            <td id = "package">aaaa</td>
          </tr>
          <tr>
            <td>Weight</td>
            <td id = "weight">aaaa</td>
          </tr>
          <tr>
            <td>Volume</td>
            <td id = "volume">aaaa </td>
          </tr>
          <tr>
            <td>Package</td>
            <td id = "package">aaaaa </td>
          </tr>
          <tr>
            <td>Package Tyoe</td>
            <td id = "package-type">aaaaa</td>
          </tr>

        <thead>
          <tr>
            <th>Reimbursible Charges</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>95 Ocean Freight</td>
            <td>50304</td>
          </tr>
          <tr>
            <td>Advance Shipping Lines</td>
            <td>50304</td>
          </tr>
          <tr>
            <td>Processing</td>
            <td>50304</td>
          </tr>
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

      <p class = "comment-header"> Comments:
      <div class="comment-box">
        <textarea id="textbox" maxlength="250" oninput="updateCounter()" readonly></textarea>
        <div class="button-container">
          <button class="edit-button" onclick="saveComment()">Edit</button>
          <button class="download-button" onclick="saveComment()">Download</button>
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