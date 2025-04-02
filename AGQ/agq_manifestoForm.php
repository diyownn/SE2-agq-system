<?php
require_once "db_agq.php";
session_start();

$refNum = $_GET['refNum'] ?? null;
$editedBy = $_GET['editedby'] ?? '';
$docType = "Manifesto";
$department = $_SESSION['department'] ?? '';
$companyName = $_SESSION['Company_name'] ?? '';
date_default_timezone_set('Asia/Manila');
$editDate = date('Y-m-d H:i:s');

$imageSrc = "";

if ($refNum) {
    require_once "db_agq.php";

    $stmt = $conn->prepare("SELECT Document_picture FROM tbl_document WHERE RefNum = ?");
    $stmt->bind_param("s", $refNum);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['Document_picture']) && file_exists($row['Document_picture'])) {
            $imageSrc = $row['Document_picture']; // Use existing image
        }
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $refNum = random_int(1000000000, 9999999999);
    $name = isset($_SESSION['name']) ? $_SESSION['name'] : 'no name';

    date_default_timezone_set('Asia/Manila');
    $editDate = date('Y-m-d H:i:s');

    $hasError = false;

    // Backend validation for security
    // if (empty($name)) {
    //     echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    //     echo '<script>
    //         document.addEventListener("DOMContentLoaded", function() {
    //             Swal.fire({
    //                 icon: "error",
    //                 title: "Missing Input",
    //                 text: "Please enter the edited by field."
    //             });
    //         });
    //       </script>';
    //     $hasError = true;
    // }

    if (!isset($_FILES['manPic']) || $_FILES['manPic']['error'] !== 0) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Missing Image",
                    text: "Please upload an image before submitting."
                });
              </script>';
        $hasError = true;
    }

    if ($hasError) {
        exit;
    }

    $imageData = file_get_contents($_FILES['manPic']['tmp_name']);
    $imageHash = hash('sha256', $imageData);

    $stmt = $conn->prepare("SELECT Document_picture FROM tbl_document WHERE SHA2(Document_picture, 256) = ?");
    $stmt->bind_param("s", $imageHash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
                Swal.fire({
                    icon: "warning",
                    title: "Duplicate Image",
                    text: "This image already exists in the system."
                });
              </script>';
        exit;
    }

    // Store the image in the "uploads" directory instead of the database
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); // Create directory if not exists
    }
    $fileExtension = pathinfo($_FILES["manPic"]["name"], PATHINFO_EXTENSION);
    $fileName = $refNum . "." . $fileExtension;
    $targetFilePath = $targetDir . basename($fileName);

    if (!move_uploaded_file($_FILES["manPic"]["tmp_name"], $targetFilePath)) {
        echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Upload Failed",
                    text: "Failed to upload image. Please try again."
                });
              </script>';
        exit;
    }

    // Insert data into the database with the file path
    $stmt = $conn->prepare("INSERT INTO tbl_document (RefNum, DocType, Document_picture, Edited_by, EditDate, Company_name, Department) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    $stmt->bind_param("sssssss", $refNum, $docType, $targetFilePath, $name, $editDate, $companyName, $department);

    if ($stmt->execute()) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "success",
                    title: "Document Added!",
                    timer: 1500, 
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "agq_manifestoView.php?refNum=' . htmlspecialchars($refNum) . '";
                });
            });
          </script>';
    }

    $stmt->close();
    $conn->close();
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifesto Form | AGQ</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Font Link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <!-- Local CSS -->
    <link rel="stylesheet" type="text/css" href="agq.css">
</head>
<!-- Website Icon -->
<link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">

<body style="background-image: url('manifbg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
    <a href="#" onclick="redirection('<?php echo htmlspecialchars(($refNum)); ?>')" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 39%; top: 55px;">←</a>

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border1">
                <p id="title" class="text-center" style="text-decoration: none; margin-top:0%">MANIFESTO</p>

                <form action="agq_manifestoForm.php" method="POST" class="form-content" enctype="multipart/form-data" onsubmit="validate_manImg()">
                    <img src="<?= htmlspecialchars($imageSrc); ?>" class="d-block mx-auto" id="imgholder"
                        alt="Document Image" style="width: 335px; height: 350px; display: <?= empty($imageSrc) ? 'none' : 'block' ?>;">

                    <div class="d-flex justify-content-center">
                        <label class="file-upload d-flex justify-content-center">
                            <input type="file" id="cPic" name="manPic" accept="image/*" onchange="previewImage(event)">
                            <input type="button" id="button1" style="margin-top: 39.5%; margin-bottom: 0%; margin-right: 10px;" value="Upload">
                            <div id="image-error" class="text-danger"></div>

                        </label>
                        <input type="submit" id="button1" style="margin-top: 12%; margin-bottom: 0%;" value="Save">
                    </div>
                </form>



            </div>
        </div>
    </div>

    <!-- Bootstrap Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    <!-- Sweet Alert Popper -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




    <script>
        // function redirection(refnum) {
        //     if (!refnum || refnum === 'null' || refnum.trim() === '') {
        //         window.location.href = "agq_chooseDocument.php";
        //     } else {
        //         window.location.href = "agq_transactionCatcher.php";
        //     }
        // }

        function redirection(refnum) {
            if (!refnum || refnum === "") {
                // Using SweetAlert2 for navigation confirmation
                Swal.fire({
                    title: 'Leave this page?',
                    text: "Any unsaved changes will be lost.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, leave page',
                    cancelButtonText: 'Stay here'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "agq_choosedocument.php";
                    }
                });
            } else {
                // Using SweetAlert2 for navigation confirmation
                Swal.fire({
                    title: 'Leave this page?',
                    text: "Any unsaved changes will be lost.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, leave page',
                    cancelButtonText: 'Stay here'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "agq_transactionCatcher.php";
                    }
                });
            }
            return false; // Prevent default link behavior
        }

        function previewImage(event) {
            var imgDisplay = document.getElementById("imgholder");
            imgDisplay.src = URL.createObjectURL(event.target.files[0]);
            imgDisplay.style.display = "block"; // Show the image
        }

        function validate_manImg() {
            var fileInput = document.getElementById("cPic");
            //var fileError = document.getElementById("image-error");
            let isValid = true; // Track overall validity

            if (fileInput.files.length === 0) {
                fileInput.setCustomValidity("Please upload Manifesto Document");

            } else if (validateFileSize(fileInput)) {

            } else {
                fileInput.setCustomValidity(""); // Reset validation
            }

            fileInput.reportValidity(); // Show validation message

            if (!fileInput.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            fileInput.addEventListener("input", function() {
                fileInput.setCustomValidity(""); // Clear error when user types
            });

            return isValid;
        }

        function validateFileSize(fileInput) {
            var file = fileInput.files[0];
            //var fileError = document.getElementById("image-error");
            let isValid = true; // Track overall validity


            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                fileInput.setCustomValidity("Image is more than 2mb");

            } else {
                fileInput.setCustomValidity(""); // Reset validation
            }
            fileInput.reportValidity(); // Show validation message

            if (!fileInput.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            fileInput.addEventListener("input", function() {
                fileInput.setCustomValidity(""); // Clear error when user types
            });
            return isValid;

        }
    </script>

</body>

</html>