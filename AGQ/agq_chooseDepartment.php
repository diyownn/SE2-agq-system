<?php
session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';

if (!$role) {
    echo "<html><head><style>
    body { font-family: Arial, sans-serif; text-align: center; background-color: #f8d7da; }
    .container { margin-top: 50px; padding: 20px; background: white; border-radius: 10px; display: inline-block; }
    h1 { color: #721c24; }
    p { color: #721c24; }
  </style></head><body>
  <div class='container'>
    <h1>Unauthorized Access</h1>
    <p>You do not have permission to view this page.</p>
  </div>
  </body></html>";
    exit;
}

if (!$company) {

    echo "Did not get Company";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Departments</title>
    <link rel="stylesheet" href="../css/cd.css">
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
            </div>
        </div>
    </div>

    <div class="container">
        <h1>Company Departments</h1>
        <div class="grid">
            <div class="grid">
                <a href="#" class="box" onclick="storeDepartmentSession('Import Forwarding')">Import Forwarding</a>
                <a href="#" class="box" onclick="storeDepartmentSession('Export Forwarding')">Export Forwarding</a>
                <a href="#" class="box" onclick="storeDepartmentSession('Import Brokerage')">Import Brokerage</a>
                <a href="#" class="box" onclick="storeDepartmentSession('Export Brokerage')">Export Brokerage</a>
            </div>

        </div>
    </div>
</body>
<script>
    function storeDepartmentSession(departmentName) {
        fetch('STORE_SESSION.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'selected_department=' + encodeURIComponent(departmentName)
            })
            .then(response => response.text())
            .then(data => {
                console.log("Session stored:", data);
                window.location.href = "agq_ownTransactionView.php";
            })
            .catch(error => console.error("Error:", error));
    }
</script>

</html>