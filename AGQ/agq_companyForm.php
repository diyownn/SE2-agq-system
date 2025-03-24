<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Form | AGQ</title>

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
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border1">
                <a href="agq_dashCatcher.php" style="text-decoration: none; color: black; font-size: x-large">←</a>
                <p id="title" class="text-center" style="text-decoration: none; margin-top:0%">COMPANY FORM</p>

                <form action="agq_companyForm.php" method="POST" class="form-content" enctype="multipart/form-data" onsubmit="validate_form()">

                    <img src="" class="d-block mx-auto" id="imgholder" alt="">

                    <input type="text" name=" compName" id="input3" class="form-control" placeholder="Company Name" onchange="validate_compName()">
                    <div id="name-error" style="margin-left: 16%; margin-top: 1%"></div>

                    <div class="d-flex justify-content-center">
                        <label class="file-upload d-flex justify-content-center">
                            <input type="file" id="cPic" name="compPic" accept="image/*" onchange="previewImage(event)">
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

    <?php
    require_once "db_agq.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['compPic']['tmp_name']) && isset($_POST['compName'])) {
        $company_picture = file_get_contents($_FILES['compPic']['tmp_name']);
        $company_name = $_POST['compName'];
        $companyid = (string)random_int(1000000000, 9999999999); 

        $stmt = $conn->prepare("INSERT INTO tbl_company (CompanyID, Company_name, Company_picture) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Preparation failed: " . $conn->error);
        }

        $stmt->bind_param("ssb", $companyid, $company_name, $company_picture);
        //$stmt->send_long_data(4, $company_picture);

        if ($stmt->execute()) {
    ?>
            <script>
                Swal.fire({
                    icon: "success",
                    title: "Company Added!",
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

    <script>
        function previewImage(event) {
            var imgDisplay = document.getElementById("imgholder");
            imgDisplay.src = URL.createObjectURL(event.target.files[0]);
        }

        function validate_form() {
            var val_cimg = validate_compImg();
            var val_cname = validate_compName();
            
           return val_cimg && val_cname;
        }

        function validate_compImg() {
            var fileInput = document.getElementById("cPic");
            //var fileError = document.getElementById("image-error");
            let isValid = true; // Track overall validity

            if (fileInput.files.length === 0) {
                fileInput.setCustomValidity("Please upload company logo");

            } else if (validateFileSize(fileInput)) {

            } else {
                fileInput.setCustomValidity(""); // Reset validation
            }

            fileInput.reportValidity(); // Show validation message

            if (!fileInput.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            fileInput.addEventListener("input", function () {
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

            fileInput.addEventListener("input", function () {
                fileInput.setCustomValidity(""); // Clear error when user types
            });
            return isValid;

        }


        function validate_compName() {
            var comp = document.getElementById("input3");
            const allowedSymbols = /^[a-zA-Z0-9!.@$%^&()_+\-:/|,~ \r\n]*$/; // Allow letters, numbers, symbols, and line breaks
            var nameregex = /^.{2,25}$/; // Maximum character limit
            let isValid = true; // Track overall validity

            //var comp_error = document.getElementById("name-error");

            if (!comp.value.trim()) {
                comp.setCustomValidity("Please enter the company name");
            } else if (!allowedSymbols.test(comp.value)) {
                comp.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
            } else if (!nameregex.test(comp.value)) {
                comp.setCustomValidity("Company Name must be 2-25 characters");

            }else {
                comp.setCustomValidity(""); // Reset validation
                }

                comp.reportValidity(); // Show validation message

                if (!comp.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                comp.addEventListener("input", function () {
                    comp.setCustomValidity(""); // Clear error when user types
                });

            return isValid;



            // if (comp.value == '') {
            //     comp.classList.add("is-invalid");
            //     error_text = "*Please enter the company name";
            //     comp_error.innerHTML = error_text;
            //     comp_error.classList.add("invalid-feedback");
            //     return false;
            // } else {
            //     var nameregex = /^.{2,25}$/;

            //     if (!nameregex.test(comp.value)) {
            //         comp.classList.add("is-invalid");
            //         error_text = "*Company Name must be 2-25 characters";
            //         comp_error.innerHTML = error_text;
            //         comp_error.classList.add("invalid-feedback");
            //         return false;
            //     }

            //     var symbolregex = /[!@#$%^&*()_+\-={};:'"\\|,<>\/?~]/;

            //     if (symbolregex.test(comp.value)) {
            //         comp.classList.add("is-invalid");
            //         error_text = "*Company Name must not contain symbols";
            //         comp_error.innerHTML = error_text;
            //         comp_error.classList.add("invalid-feedback");
            //         return false;
            //     }

            //     comp.classList.remove("is-invalid");
            //     comp_error.innerHTML = "";
            //     comp_error.classList.remove("invalid-feedback");
            //     return true;
            // }


        }
    </script>

</body>

</html>
