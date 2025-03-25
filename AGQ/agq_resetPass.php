<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password | AGQ</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" rel="stylesheet">

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
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
    <body>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border">
                <a href="agq_otp.php" class="back-button" style="text-decoration: none; color: black; font-size: x-large">←</a>
                <img src="images/agq_logo.png" alt="logo" class="mx-auto d-block" id="agqlogo">
                <p id="title" class="text-center">Reset Password</p>

                <form action="agq_resetPass.php" method="post" class="form-content" onsubmit="return validate_form()">
                    
                    <label for="newPass" class="form-label" id="labels">Enter New Password</label>
                    <div class="input-group mb-3">
                        <input type="password" name="newPword" id="newPass" class="form-control" onchange="validate_newPassword()">
                        <span class="input-group-text" id="toggle-password" style="cursor: pointer;">
                            <i class="bi bi-eye-fill" id="toggle-password-icon"></i> 
                        </span>
                        <div id="pass-error1"></div>
                    </div>

                    <label for="rePass" class="form-label" id="labels">Re-enter Password</label>
                    <div class="input-group mb-3">
                        <input type="password" name="rePword" id="rePass" class="form-control" onchange="validate_rePassword()"> 
                        <span class="input-group-text" id="toggle-password1" style="cursor: pointer;">
                            <i class="bi bi-eye-fill" id="toggle-password-icon1"></i> 
                        </span>
                        <div id="pass-error2"></div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <input type="submit" id="button1" style="margin-top: 10%; margin-bottom: 0%" value="SAVE">
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
    session_start();
    require_once "db_agq.php";

    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

    if ((isset($_POST['newPword']) && $_POST['newPword'] != NULL) && 
    (isset($_POST['rePword']) && $_POST['rePword'] != NULL)) {

        $finalPass = $_POST['rePword'];

        $reset_pass = "Update tbl_user set Password = '".$finalPass."' where Email = '".$email."'";
        $conn->query($reset_pass);

        ?>
        <script>
                Swal.fire({
                    icon: "success",
                    title: "Password Updated!",
                    confirmButtonText: "Log In"
                    }).then((result) => {
                    
                        if (result.isConfirmed) {
                           window.location.href = "agq_login.php";
                        }
                    });
            </script>
        <?php

        $conn->close();
        
    }

?>

    <script>
        function validate_form(){
            var val_newPass = validate_newPassword();
            var val_rePass = validate_rePassword();

            if (val_newPass && val_rePass){

                return validate_finalPassword();

            }
        }

        function validate_newPassword(){
            var nPass = document.getElementById("newPass");
            var passregex = /^.{8,100}$/;
            var allNumbersRegex = /^\d+$/;
            var allUppercaseRegex = /^[A-Z]+$/;
            var allLowercaseRegex = /^[a-z]+$/;
            const allowedSymbols = /^[a-zA-Z0-9!.@$%^&()_+\-:/|,~ \r\n]*$/; // Allow letters, numbers, symbols, and line breaks
            let isValid = true; // Track overall validity
            //var nPass_error = document.getElementById("pass-error1");

            if (!nPass.value.trim()) {
                nPass.setCustomValidity("Please enter your password");
            } else if (!passregex.test(nPass.value)) {
                nPass.setCustomValidity("Password must be atleast 8 characters");
            } else if (!allowedSymbols.test(nPass.value)) {
                nPass.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
            } else if (allNumbersRegex.test(nPass.value) || allUppercaseRegex.test(nPass.value) || allLowercaseRegex.test(nPass.value)) {
                nPass.setCustomValidity("Your Password must be alphanumeric or contains symbols");
            }else {
                nPass.setCustomValidity(""); // Reset validation
            }
                nPass.reportValidity(); // Show validation message

                if (!nPass.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                nPass.addEventListener("input", function () {
                    nPass.setCustomValidity(""); // Clear error when user types
                });

            return isValid;

        }

        function validate_rePassword(){
            var rPass = document.getElementById("rePass");
            var passregex = /^.{8,100}$/;
            var allNumbersRegex = /^\d+$/;
            var allUppercaseRegex = /^[A-Z]+$/;
            var allLowercaseRegex = /^[a-z]+$/;
            const allowedSymbols = /^[a-zA-Z0-9!.@$%^&()_+\-:/|,~ \r\n]*$/; // Allow letters, numbers, symbols, and line breaks
            let isValid = true; // Track overall validity
            //var rPass_error = document.getElementById("pass-error2");

            if (!rPass.value.trim()) {
                rPass.setCustomValidity("Please enter your password");
            } else if (!passregex.test(rPass.value)) {
                rPass.setCustomValidity("Password must be atleast 8 characters");
            } else if (!allowedSymbols.test(rPass.value)) {
                rPass.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
            } else if (allNumbersRegex.test(rPass.value) || allUppercaseRegex.test(rPass.value) || allLowercaseRegex.test(rPass.value)) {
                rPass.setCustomValidity("Your Password must be alphanumeric or contains symbols");
            }else {
                rPass.setCustomValidity(""); // Reset validation
            }
                rPass.reportValidity(); // Show validation message

                if (!rPass.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                rPass.addEventListener("input", function () {
                    rPass.setCustomValidity(""); // Clear error when user types
                });

            return isValid;
            // if(rPass.value == ''){
            //     rPass.classList.add("is-invalid");
            //     error_text = "*Please re-enter your new Password";
            //     rPass_error.innerHTML = error_text;
            //     rPass_error.classList.add("invalid-feedback");
            //     return false;
            // } else {
            
            //     var passregex = /^.{8,100}$/; 

            //     if(!passregex.test(rPass.value)){ 
            //         rPass.classList.add("is-invalid");
            //         error_text = "*Your Password must be at least 8 characters";
            //         rPass_error.innerHTML = error_text;
            //         rPass_error.classList.add("invalid-feedback");
            //         return false;
            //     }

            //     var symbolregex = /[^a-zA-Z0-9!@$%^&()_+\-:|,~]/;

            //     if (symbolregex.test(rPass.value)) {
            //         rPass.classList.add("is-invalid");
            //         error_text = "*Your Password contains invalid symbols";
            //         rPass_error.innerHTML = error_text;
            //         rPass_error.classList.add("invalid-feedback");
            //         return false;
            //     }

            //     var allNumbersRegex = /^\d+$/;
            //     var allUppercaseRegex = /^[A-Z]+$/;
            //     var allLowercaseRegex = /^[a-z]+$/;

            //     if (allNumbersRegex.test(rPass.value) || allUppercaseRegex.test(rPass.value) || allLowercaseRegex.test(rPass.value)) {
            //         rPass.classList.add("is-invalid");
            //         error_text = "*Your Password must be alphanumeric";
            //         rPass_error.innerHTML = error_text;
            //         rPass_error.classList.add("invalid-feedback");
            //         return false;
            //     }

            //     rPass.classList.remove("is-invalid");
            //     rPass_error.innerHTML = "";
            //     rPass_error.classList.remove("invalid-feedback");
            //     return true;
            // }
        }

        function validate_finalPassword() {
            var nPass = document.getElementById("newPass");
            var rPass = document.getElementById("rePass");
            let isValid = true; // Track overall validity
            //var rPass_error = document.getElementById("pass-error2");
            //var nPass_error = document.getElementById("pass-error1");

            if (nPass.value !== rPass.value) {
                nPass.setCustomValidity("Passwords do not match."); // Clear error when user types
                rPass.setCustomValidity("Passwords do not match."); // Clear error when user types
            }else {
                rPass.setCustomValidity(""); // Reset validation
            }
                rPass.reportValidity(); // Show validation message

                if (!rPass.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                rPass.addEventListener("input", function () {
                    rPass.setCustomValidity(""); // Clear error when user types
                });

            return isValid;

            // if (nPass.value !== rPass.value) {
            //     nPass.classList.add("is-invalid");
            //     error_text = "*Passwords do not match.";
            //     nPass_error.innerHTML = error_text;
            //     nPass_error.classList.add("invalid-feedback");

            //     rPass.classList.add("is-invalid");
            //     error_text = "*Passwords do not match.";
            //     rPass_error.innerHTML = error_text;
            //     rPass_error.classList.add("invalid-feedback");
            //     return false;
            // } else {
            //     nPass.classList.remove("is-invalid");
            //     nPass_error.innerHTML = "";
            //     nPass_error.classList.remove("invalid-feedback");

            //     rPass.classList.remove("is-invalid");
            //     rPass_error.innerHTML = "";
            //     rPass_error.classList.remove("invalid-feedback");
            //     return true;
            // }
        }


        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordField1 = document.getElementById('newPass');
            const passwordIcon1 = document.getElementById('toggle-password-icon');
            
            if (passwordField1.type === 'password') {
                passwordField1.type = 'text';
                passwordIcon1.classList.remove('bi-eye-fill');
                passwordIcon1.classList.add('bi-eye-slash-fill');
            } else {
                passwordField1.type = 'password';
                passwordIcon1.classList.remove('bi-eye-slash-fill');
                passwordIcon1.classList.add('bi-eye-fill');
            }
        });

        document.getElementById('toggle-password1').addEventListener('click', function() {
            const passwordField2 = document.getElementById('rePass');
            const passwordIcon2 = document.getElementById('toggle-password-icon1');
            
            if (passwordField2.type === 'password') {
                passwordField2.type = 'text';
                passwordIcon2.classList.remove('bi-eye-fill');
                passwordIcon2.classList.add('bi-eye-slash-fill');
            } else {
                passwordField2.type = 'password';
                passwordIcon2.classList.remove('bi-eye-slash-fill');
                passwordIcon2.classList.add('bi-eye-fill');
            }
        });

    </script>

</body>
</html>

