<?php
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


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <title>Company Departments</title>
    <link rel="stylesheet" href="../css/cd.css">
    <link rel="stylesheet" href="../css/home-icon.css">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
</head>

<body style="background-image: url('cdbg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
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
    <a href="agq_dashCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 50px;">←</a>

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
                window.location.href = "agq_transactionCatcher.php";
            })
            .catch(error => console.error("Error:", error));
    }
</script>
</body>
</html>