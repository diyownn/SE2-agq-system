<?php
require_once "db_agq.php"; // Ensure this file contains a valid $conn connection

session_start();

$documentID = isset($_GET['refnum']) ? $_GET['refnum'] : null;
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';
$documentType = "Manifesto";

if ($dept) {
    $stmt = $conn->prepare("SELECT RefNum, DocType, Document_picture 
                            FROM tbl_document 
                            WHERE RefNum = ? AND DocType = ? AND Department = ? AND Company_name = ?");
    $stmt->bind_param("ssss", $documentID, $documentType, $dept, $company);
} else {
    $stmt = $conn->prepare("SELECT RefNum, DocType, Document_picture 
                            FROM tbl_document 
                            WHERE RefNum = ? AND DocType = ? AND Company_name = ?");
    $stmt->bind_param("sss", $documentID, $documentType, $company);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!empty($row['Document_picture']) && file_exists($row['Document_picture'])) {
        $imageSrc = $row['Document_picture'];
    } else {
        $imageSrc = "images/default-placeholder.png";
    }
} else {
    $imageSrc = "images/default-placeholder.png";
    $row = ['RefNum' => '', 'DocType' => 'Not Found'];
}

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifesto Form | AGQ</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="agq.css">
    <link rel="icon" href="images/agq_logo.png" type="image/ico">
</head>

<body style="background-color: white; background-image:none">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border1">
                <p id="title" class="text-center">MANIFESTO</p>

                <p class="text-center"><strong>Document ID:</strong> <?= htmlspecialchars($row['RefNum']); ?></p>
                <p class="text-center"><strong>Document Type:</strong> <?= htmlspecialchars($row['DocType']); ?></p>

                <form action="agq_manifestoForm.php?refnum=<?= urlencode($row['RefNum']); ?>&createdby=<?= urlencode($row['Edited_by'] ?? ''); ?>" method="GET">
                    <img src="<?= htmlspecialchars($imageSrc); ?>" class="d-block mx-auto" id="imgholder"
                        alt="Document Image" style="width: 335px; height: 350px">
                    <div class="d-flex justify-content-center">
                        <button type="button" id="button1" style="margin-top: 12%; margin-bottom: 0%;"
                            onclick="window.location.href='agq_manifestoForm.php?refnum=<?php echo htmlspecialchars($documentID);?>'">
                            Edit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>