<?php
require_once "db_agq.php";
session_start();

$docType = $_SESSION['DocType'] ?? null;
$department = $_SESSION['department'] ?? null;
$companyName = $_SESSION['Company_name'] ?? null;
date_default_timezone_set('Asia/Manila');
$editDate = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $editText = trim($_POST['edit'] ?? '');
    $hasError = false;

    // Backend validation for security
    if (empty($editText)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Missing Input",
                    text: "Please enter the created by field."
                });
              </script>';
        $hasError = true;
    }

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

    // Stop execution if there's an error
    if ($hasError) {
        exit;
    }

    // Process upload only if validation passes
    $refNum = rand(1000000000, 9999999999);
    $man_docs = fopen($_FILES['manPic']['tmp_name'], 'rb');

    $stmt = $conn->prepare("INSERT INTO tbl_document (DocumentID, Document_type, Document_picture, Edited_by, EditDate, Company_name, Department) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    $dummyblob = null;
    $stmt->bind_param("isbssss", $refNum, $docType, $dummyblob, $editText, $editDate, $companyName, $department);
    $stmt->send_long_data(2, file_get_contents($_FILES['manPic']['tmp_name']));

    if ($stmt->execute()) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
                Swal.fire({
                    icon: "success",
                    title: "Document Added!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "agq_manifestoView.php";
                    }
                });
              </script>';
    }

    fclose($man_docs);
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
<link rel="icon" href="images/agq_logo.png" type="image/ico">

<body style="background-color: white; background-image:none">
    <a href="agq_choosedocument.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 39%; top: 55px;">←</a>

    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border1">
                <p id="title" class="text-center" style="text-decoration: none; margin-top:0%">MANIFESTO</p>

                <form action="agq_manifestoForm.php" method="POST" class="form-content" enctype="multipart/form-data" onsubmit="return validate_form()">
                    <img src="" class="d-block mx-auto" id="imgholder" alt="" style="width: 335px; height: 350px">
                    <input type="text" name="edit" id="einput" class="form-control" placeholder="Created by" onchange="return validate_edit()">
                    <div id="edit-error"></div>


                    <div class="d-flex justify-content-center">
                        <label class="file-upload d-flex justify-content-center">
                            <input type="file" id="cPic" name="manPic" accept="image/*" onchange="previewImage(event)">
                            <input type="button" id="button1" style="margin-top: 39.5%; margin-bottom: 0%; margin-right: 10px;" value="Upload">
                            <div id="image-error"></div>
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
        function previewImage(event) {
            var imgDisplay = document.getElementById("imgholder");
            imgDisplay.src = URL.createObjectURL(event.target.files[0]);
        }

        function validate_form() {
            let editValid = validate_edit();
            let imageValid = validate_manImg();
            return editValid && imageValid;
        }

        function validate_edit() {
            var editInput = document.getElementById("einput");
            var editError = document.getElementById("edit-error");

            if (editInput.value.trim() === '') {
                editInput.classList.add("is-invalid");
                editError.innerHTML = "*Please input an Author";
                editError.classList.add("invalid-feedback");
                return false;
            } else {
                editInput.classList.remove("is-invalid");
                editError.innerHTML = "";
                editError.classList.remove("invalid-feedback");
                return true;
            }
        }

        function validate_manImg() {
            var fileInput = document.getElementById("cPic");
            var fileError = document.getElementById("image-error");

            if (fileInput.files.length === 0) {
                fileInput.classList.add("is-invalid");
                fileError.innerHTML = "*Please upload an image.";
                fileError.classList.add("invalid-feedback");
                return false;
            } else if (!validateFileSize(fileInput)) {
                return false;
            } else {
                fileInput.classList.remove("is-invalid");
                fileError.innerHTML = "";
                fileError.classList.remove("invalid-feedback");
                return true;
            }
        }

        function validateFileSize(fileInput) {
            var file = fileInput.files[0];
            var fileError = document.getElementById("image-error");

            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                fileInput.classList.add("is-invalid");
                fileError.innerHTML = "*File size must be less than 2MB.";
                fileError.classList.add("invalid-feedback");
                return false;
            } else {
                fileInput.classList.remove("is-invalid");
                fileError.innerHTML = "";
                fileError.classList.remove("invalid-feedback");
                return true;
            }
        }
    </script>

</body>

</html>