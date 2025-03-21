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
  <link type="stylesheet" type="text/css" href="../css/employdocuview.css">
</head>

<body>
  <div class="container">
    <div class="document-view">
      <!-- Table Here -->
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