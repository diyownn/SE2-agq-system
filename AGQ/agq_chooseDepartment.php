<?php
session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';


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