<?php
require_once "db_agq.php"; // Ensure this file contains a valid $conn connection

session_start();

$documentID = isset($_GET['refNum']) ? $_GET['refNum'] : null;
$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$dept = isset($_SESSION['SelectedDepartment']) ? $_SESSION['SelectedDepartment'] : '';
$company = isset($_SESSION['Company_name']) ? $_SESSION['Company_name'] : '';
$documentType = "Manifesto";

if ($dept) {
    $stmt = $conn->prepare("SELECT RefNum, DocType, Document_picture, Edited_by, EditDate 
                            FROM tbl_document 
                            WHERE RefNum = ? AND DocType = ? AND Department = ? AND Company_name = ?");
    $stmt->bind_param("ssss", $documentID, $documentType, $dept, $company);
} else {
    $stmt = $conn->prepare("SELECT RefNum, DocType, Document_picture, Edited_by, EditDate 
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_document'])) {
    $refnum = $_POST['refNum'];

    $stmt = $conn->prepare("DELETE FROM tbl_document WHERE RefNum = ?");
    $success = $stmt->execute([$refnum]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? "Document deleted successfully." : "Failed to delete document."
    ]);
    exit;
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

<body style="background-image: url('manifbg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
    <a href="agq_transactionCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 39%; top: 55px;">←</a>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border1">
                <p id="title" class="text-center">MANIFESTO</p>

                <p class="text-center"><strong>Document ID:</strong> <?= htmlspecialchars($row['RefNum']); ?></p>
                <p class="text-center"><strong>Document Type:</strong> <?= htmlspecialchars($row['DocType']); ?></p>
                
                <form id="deleteForm" action="ARCHIVE_HANDLE.php?action=archive&refnum" method="POST">
                    <input type="hidden" name="refnum" value="<?= htmlspecialchars($row['RefNum']); ?>">
                    <img src="<?= htmlspecialchars($imageSrc); ?>" class="d-block mx-auto" id="imgholder"
                        alt="Document Image" style="width: 335px; height: 350px">

                    <p class="text-center" style="margin-top: 5%;"><strong>Created by:</strong> <?= htmlspecialchars($row['Edited_by']); ?></p>
                    <p class="text-center"><strong>Date:</strong> <?= htmlspecialchars(date("F d, Y h:i A", strtotime($row['EditDate'])) ?? 'N/A'); ?></p>

                    <div class="d-flex justify-content-center">
                        <button type="button" id="button1" style="margin-top: 6%; margin-bottom: 0%;"
                            onclick="confirmDelete('<?= htmlspecialchars($row['RefNum']); ?>')">
                            Delete
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(refnum) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `delete_document=true&refNum=${refnum}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Deleted!', data.message, 'success').then(() => {
                                    window.location.href="agq_transactionCatcher.php";
                                });
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        });
                }
            });

           
        }


        function archiveDocument(refnum) {
            fetch("ARCHIVE_HANDLE.php?action=delete", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `RefNum=${encodeURIComponent(refnum)}`
                })
                .then(response => response.json()) // Expect JSON response
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Archived!",
                            text: data.message || "The document has been successfully archived.",
                            confirmButtonColor: "#27ae60"
                        }).then(() => {
                            location.reload(); // Reload the page after successful archive
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message || "Failed to archive the document.",
                            confirmButtonColor: "#d33"
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to communicate with the server.",
                        confirmButtonColor: "#d33"
                    });
                });
        }
    </script>
</body>

</html>