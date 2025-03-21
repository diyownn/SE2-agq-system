<?php
require 'db_agq.php';

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
    <div class="top-container">
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


    <div class="container">
        <div class="document-view">
        </div>
        <div class="info-view">
            <div class="docu-information">
                <p class="ref-number" id="ref-number">IB-0000</p>
                <p class="date" id="date"><strong>Date:</strong> 01/01/2025</p>
                <p class="document-type" id="docType">Statement of Account</p>
            </div>
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