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
    <link rel="stylesheet" href="../css/home-icon.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<!-- <link rel="icon" href="images/agq_logo.png" type="image/ico"> -->

<body style="background-image: url('cdobg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
    <div class="top-container">
        <div class="dept-container">
            <div class="header-container">
            <div class="dept-label">
                    <a href="agq_dashCatcher.php" class="home-link">
                        <!-- Home Icon SVG -->
                        <svg class="home-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <?php echo htmlspecialchars($role); ?>
                    </a>
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