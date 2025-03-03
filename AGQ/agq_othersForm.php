<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Others Form | AGQ</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Font Link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">    
    
    <!-- Local CSS -->
    <link rel = "stylesheet" type="text/css" href="agq.css">
</head>
    <!-- Website Icon -->
    <link rel="icon" href="images/agq_logo.png" type="image/ico">

<body style="background-color: white; background-image:none">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border1">
                <p id="title" class="text-center" style="text-decoration: none; margin-top:0%">OTHERS</p>

                <form action="agq_othersForm.php" method="POST" class="form-content" enctype="multipart/form-data" onsubmit="return validate_otImg()">
                    <img src="" class="d-block mx-auto" id="imgholder" alt="" style="width: 335px; height: 350px">
                    
                    <div class="d-flex justify-content-center">
                        <label class="file-upload d-flex justify-content-center">
                            <input type="file" id="cPic" name="otPic" accept="image/*" onchange="previewImage(event)">
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

        function validate_otImg() {
            var imgDisplay = document.getElementById("imgholder");
            var cpic = document.getElementById("cPic");
            var cpic_error = document.getElementById("image-error");

            if (cpic.files.length === 0) {
                cpic.classList.add("is-invalid");
                error_text = "*Please upload the needed document";
                cpic_error.innerHTML = error_text;
                cpic_error.classList.add("invalid-feedback");
                
                return false;
            } else if (!validateFileSize(cpic)) {
                // Additional check for file size
                return false;

            } else {
                cpic.classList.remove("is-invalid");
                cpic_error.innerHTML = "";
                cpic_error.classList.remove("invalid-feedback");

                return true;
            }

        }

        function validateFileSize(fileInput) {
            var file = fileInput.files[0];
            var fileError = document.getElementById("image-error");

            if (file.size > 2 * 1024 * 1024) { // 2MB in bytes
                fileInput.classList.add("is-invalid");
                error_text = "*File size must be less than or equal to 2MB.";
                fileError.innerHTML = error_text;
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

<?php
 require_once "db_agq.php";

 if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['otPic']['tmp_name'])) {
    $others_docs = file_get_contents($_FILES['otPic']['tmp_name']);
    $refNum = (string)random_int(1000000000, 9999999999);  // We are going to make this into an input. Random int for now

    //sample insert into 1 department, conditions will be applied once departments are complete to specify tbl to insert
    $stmt = $conn->prepare("INSERT INTO tbl_expbrk (exportB_refNum, others) VALUES (?, ?)");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    // $stmt = $conn->prepare("INSERT INTO tbl_expfwd (exportB_refNum, others) VALUES (?, ?)");
    // if (!$stmt) {
    //     die("Preparation failed: " . $conn->error);
    // }

    // $stmt = $conn->prepare("INSERT INTO tbl_impbrk (exportB_refNum, others) VALUES (?, ?)");
    // if (!$stmt) {
    //     die("Preparation failed: " . $conn->error);
    // }

    // $stmt = $conn->prepare("INSERT INTO tbl_impfwd (exportB_refNum, others) VALUES (?, ?)");
    // if (!$stmt) {
    //     die("Preparation failed: " . $conn->error);
    // }

    $stmt->bind_param("ss", $refNum, $others_docs);

    if ($stmt->execute()) {
        ?>
        <script>
                Swal.fire({
                    icon: "success",
                    title: "Document Added!",
                    }).then((result) => {
                    
                    if (result.isConfirmed) {
                       window.location.href = "agq_othersView.php";
                    }
                    });
            </script>
        <?php
    } else {
        echo "Error uploading company: " . $stmt->error;
    }


    $stmt->close();
    $conn->close();
    
    }

?>