<?php
require "db_agq.php";

session_start();
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';


if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}

if (!$company) {
    header("Location: UNAUTHORIZED.php?error=401c");
}


/*

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $transaction_id = isset($_POST['TransactionID']) ? htmlspecialchars(trim($_POST['TransactionID'])) : '';
    $name = isset($_POST['Name']) ? htmlspecialchars(trim($_POST['Name'])) : '';
    $department = isset($_POST['Department']) ? htmlspecialchars(trim($_POST['Department'])) : '';
    $company_id = isset($_POST['CompanyID']) ? htmlspecialchars(trim($_POST['CompanyID'])) : '';
    $keyword = isset($_POST['SearchKeyword']) ? htmlspecialchars(trim($_POST['SearchKeyword'])) : '';
    $query_type = "user_search"; // Example query type, can be modified

    // Validate input
    if (!empty($transaction_id) && !empty($name) && !empty($department) && !empty($company_id)) {
        // Store data in session
        $_SESSION['TransactionID'] = $transaction_id;
        $_SESSION['Name'] = $name;
        $_SESSION['Department'] = $department;
        $_SESSION['CompanyID'] = $company_id;

        // Insert search query into the database
        if (!empty($keyword)) {
            $stmt = $conn->prepare("INSERT INTO search_queries (query_type, keyword) VALUES (?, ?)");
            $stmt->bind_param("ss", $query_type, $keyword);
            $stmt->execute();
            $stmt->close();
        }

        // Redirect
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Please provide all required information.";
    }
} else {
    // Retrieve session data
    $transaction_id = $_SESSION['TransactionID'] ?? '';
    $name = $_SESSION['Name'] ?? '';
    $department = $_SESSION['Department'] ?? '';
    $company_id = $_SESSION['CompanyID'] ?? '';

    if (!empty($transaction_id) && !empty($name) && !empty($department) && !empty($company_id)) {
        switch ($department) {
            case "admin":
                header("Location: ownerChooseDocument.php");
                exit();
            case "Import Forwarding":
            case "Import Brokerage":
            case "Export Forwarding":
            case "Export Brokerage":
                header("Location: employChooseDocument.php");
                exit();
            default:
                echo "Unauthorized Account.";
                break;
        }
    } else {
        echo "No session data found. Please log in.";
    }
}
dasdas 
$conn->close();
*/
?>

<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- provide viewport -->
    <meta charset="utf-8">
    <meta name="keywords" content=""> <!-- provide keywords -->
    <meta name="description" content=""> <!-- provide description -->
    <title> Choose Document | AGQ </title> <!-- provide title -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/documenttype.css">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<link rel="icon" href="images/agq_logo.png" type="image/ico">

<body style="background-image: url('cdobg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="top-container">
        <div class="dept-container">
            <div class="header-container">
                <div class="dept-label">
                    <?php echo htmlspecialchars($role); ?>
                </div>
                <div class="company-label">
                    <?php echo htmlspecialchars($company); ?>
                </div>
            </div>
        </div>
    </div>
    <a href="agq_transactionCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>

    <div class="document-type-body">
        <div class="title-heading">
            <span class="title">
                CHOOSE DOCUMENT
            </span>
        </div>
        <div class="document-bars">
            <button class="document-type-manifesto" id="manifesto" onclick="storeDocumentSession('Manifesto')">
                MANIFESTO
            </button>
            <button class="document-type-soa" id="soa" onclick="storeDocumentSession('SOA')">
                STATEMENT OF ACCOUNT
            </button>
            <button class="document-type-freight-invoice" id="invoice" onclick="storeDocumentSession('Invoice')">
                SALES INVOICE
            </button>
        </div>
            </div>
            
    <script>
        function storeDocumentSession(documentName) {
            fetch('STORE_SESSION.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'document_type=' + encodeURIComponent(documentName)
                })
                .then(response => response.text())
                .then(data => {
                    console.log("Session stored:", data);

                    if (documentName == "Manifesto") {
                        window.location.href = "agq_manifestoForm.php";

                    } else if (documentName == "SOA") {
                        window.location.href = "agq_soaCatcher.php";

                    } else if (documentName == "Invoice") {
                        window.location.href = "agq_invoiceCatcher.php";

                    } else if (documentName == "Summary") {
                        window.location.href = "agq_summaryForm.php";

                    } else {
                        window.location.href = "agq_othersForm.php";
                    }
                })
                .catch(error => console.error("Error:", error));
        }

        function disableInputField() {
            var manButton = document.getElementById("manifesto");
            manButton.disabled = true;
            manButton.classList.add("disabled");

        }

        var doctype = "<?php echo isset($_SESSION['selected_documenttype']) ? $_SESSION['selected_documenttype'] : ''; ?>"
        var role = "<?php echo isset($_SESSION['department']) ? $_SESSION['department'] : ''; ?>";
        var company = "<?php echo isset($_SESSION['selected_company']) ? $_SESSION['selected_company'] : ''; ?>";

        console.log("DocType:", doctype);
        console.log("Role:", role);
        console.log("Company:", company);
    </script>

    <?php
    if ($role !== 'Import Forwarding') {
        echo "<script>disableInputField();</script>";
    }


    ?>
</body>

</html>