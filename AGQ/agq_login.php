<!DOCTYPE html>
<html lang="en">
<link rel="icon" href="images/agq_logo.png" type="image/ico">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AGQ</title>

    <!-- Website Icon -->
    <link rel="icon" href="images/agq_logo.png" type="image/ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Font Link -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <!-- Local CSS -->
    <link rel="stylesheet" type="text/css" href="agq.css">

    <!-- Website Icon -->
    <link rel="icon" href="images/agq_logo.png" type="image/ico">

</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border">
                <img src="images/agq_logo.png" alt="logo" class="mx-auto d-block" id="agqlogo">
                <p id="title" class="text-center">Document Management System</p>

                <form action="agq_login.php" method="post" class="form-content" onsubmit="return validate_form()">
                    <label for="inputs" class="form-label" id="labels">Email</label>
                    <input type="text" name="email" id="inputs" class="form-control">
                    <div id="email-error"></div>

                    <label for="inputs0" class="form-label" id="labels">Password</label>
                    <div class="input-group mb-3">
                        <input type="password" name="password" id="inputs0" class="form-control">
                        <span class="input-group-text" id="toggle-password" style="cursor: pointer;">
                            <i class="bi bi-eye-fill" id="toggle-password-icon"></i>
                        </span>
                        <div id="pass-error"></div>
                    </div>

                    <p class="text-center" id="forgotP"><a href="agq_forgotEmail.php">Forgot Password?</a></p>

                    <div class="d-flex justify-content-center">
                        <input type="submit" id="button" value="LOGIN">
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
    session_start(); // Start the session at the beginning of your script
    require_once "db_agq.php";
    include "agq_mailer.php";

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0; // Initialize login attempts counter if not set
    }
    if (!isset($_SESSION['last_attempt_time'])) {
        $_SESSION['last_attempt_time'] = time(); // Initialize last attempt time if not set
    }
    if (!isset($_SESSION['lockout_start'])) {
        $_SESSION['lockout_start'] = 0; // Initialize lockout start time if not set
    }

    // Reset login attempts if 5 minutes have passed since the last lockout period
    if (time() - $_SESSION['last_attempt_time'] > 300) {
        $_SESSION['login_attempts'] = 4;
        $_SESSION['lockout_start'] = 0; // Reset lockout start time
    }

    if ((isset($_POST['email']) && $_POST['email'] != NULL) &&
        (isset($_POST['password']) && $_POST['password'] != NULL)
    ) {

        $email = $_POST['email'];
        $pass = $_POST['password'];

        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['lockout_start'] = time();
            echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Account Locked',
                        text: 'Due to numerous failed attempts, you have been locked out for 5 minutes.',
                        showConfirmButton: false,
                        timer: 5000
                    }).then(() => {
                        disableInputField();
                    });
                  </script>";
        } else {
            $loginVerify = "SELECT * FROM tbl_user WHERE Email = '$email' AND Password = '$pass'";
            $queryVerify = $conn->query($loginVerify);

            if ($queryVerify->num_rows > 0) {

                $row = $queryVerify->fetch_assoc();
                $role = $row['Department'];
                $pword = $row['Password'];

                // Reset login attempts counter on successful login
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_start'] = 0; // Reset lockout start time

                $_SESSION['department'] = $role;

                header("location: agq_dashCatcher.php");

                if ($pword == "agqLogistics") {
                    $otp = rand(000000, 999999);

                    $otpQuery = "UPDATE tbl_user SET Otp = '$otp' WHERE Email = '$email' AND Password = '$pass'";
                    $conn->query($otpQuery);

                    $_SESSION['email'] = $email;
                    emailVerification($email, $otp);
                }
            } else {
                // Increment login attempts counter on failed login
                // $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();

    ?>
                <script>
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Invalid Log In",
                        text: "Account does not Exist or Email and Password does not match.",
                        showConfirmButton: false,
                        timer: 5000
                    });
                </script>
    <?php
            }


            $conn->close();
        }
    }

    ?>

    <!-- Bootstrap Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    <!-- Sweet Alert Popper -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function validate_form() {
            var val_email = validate_email();
            var val_pass = validate_password();

            if (val_email && val_pass) {

                return true;

            } else {
                return false;
            }
        }

        function validate_email() {
            var email = document.getElementById("inputs");
            var email_error = document.getElementById("email-error");

            if (email.value == '') {
                email.classList.add("is-invalid");
                error_text = "*Please enter your email address";
                email_error.innerHTML = error_text;
                email_error.classList.add("invalid-feedback");

                return false;
            } else {
                var emailregex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.+-]+$/;

                if (!emailregex.test(email.value)) {
                    email.classList.add("is-invalid");
                    error_text = "*Email should be in the format xxx@xxx";
                    email_error.innerHTML = error_text;
                    email_error.classList.add("invalid-feedback");

                    return false;
                }

                email.classList.remove("is-invalid");
                email_error.innerHTML = "";
                email_error.classList.remove("invalid-feedback");

                return true;
            }
        }

        function validate_password() {
            var nPass = document.getElementById("inputs0");
            var nPass_error = document.getElementById("pass-error");

            if (nPass.value == '') {
                nPass.classList.add("is-invalid");
                error_text = "*Please enter your Password";
                nPass_error.innerHTML = error_text;
                nPass_error.classList.add("invalid-feedback");

                return false;
            } else {
                var passregex = /^.{8,100}$/;

                if (!passregex.test(nPass.value)) {
                    nPass.classList.add("is-invalid");
                    error_text = "*Your Password must be atleast 8 characters";
                    nPass_error.innerHTML = error_text;
                    nPass_error.classList.add("invalid-feedback");

                    return false;
                }

                nPass.classList.remove("is-invalid");
                nPass_error.innerHTML = "";
                nPass_error.classList.remove("invalid-feedback");

                return true;

            }

        }

        function disableInputField() {
            var inputEmail = document.getElementById("inputs");
            var inputPass = document.getElementById("inputs0");
            inputEmail.disabled = true;
            inputPass.disabled = true;

            // Enable the fields after 5 minutes (300000 milliseconds)

            setTimeout(function() {
                inputEmail.disabled = false;
                inputPass.disabled = false;
            }, 300000);
        }

        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordField = document.getElementById('inputs0');
            const passwordIcon = document.getElementById('toggle-password-icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.classList.remove('bi-eye-fill');
                passwordIcon.classList.add('bi-eye-slash-fill');
            } else {
                passwordField.type = 'password';
                passwordIcon.classList.remove('bi-eye-slash-fill');
                passwordIcon.classList.add('bi-eye-fill');
            }
        });
    </script>

</body>

</html>