<?php

// Add this at the top of your login page (agq_login.php)

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, don't allow direct access to login page
if (isset($_SESSION['department']) && !isset($_GET['logout'])) {
    // They're trying to access login directly without logging out
    header("Location: agq_dashCatcher.php");
    exit();
}

// If they're logging out properly, destroy the session
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset();
    session_destroy();
    // Continue to login page
}

// Set cache headers to prevent back-button access to page after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>

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
    <link rel="stylesheet" type="text/css" href="../css/agq.css">

    <!-- Website Icon -->
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">

</head>

<body>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border">
                <img src="images/agq_logo.png" alt="logo" class="mx-auto d-block" id="agqlogo">
                <p id="title" class="text-center">Document Management System</p>

                <form action="agq_login.php" method="post" class="form-content" onsubmit="validate_form()">
                    <label for="inputs" class="form-label" id="labels">Email</label>
                    <input type="text" maxlength="100" name="email" id="inputs" class="form-control" onchange="validate_email()">
                    <div id="email-error"></div>

                    <label for="inputs0" class="form-label" id="labels">Password</label>
                    <div class="input-group mb-3">
                        <input type="password" name="password" id="inputs0" class="form-control" onchange="validate_password()">
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
    require_once "db_agq.php";
    include "agq_mailer.php";

    require __DIR__ . '/vendor/autoload.php';

    use Dotenv\Dotenv;

    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();


    $def = $_ENV['DEFAULT_PASSWORD'];

    // Initialize session variables if not set
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    if (!isset($_SESSION['last_attempt_time'])) {
        $_SESSION['last_attempt_time'] = time();
    }
    if (!isset($_SESSION['lockout_start'])) {
        $_SESSION['lockout_start'] = 0;
    }

    // Reset attempts and lockout start if 300 seconds have passed
    if (time() - $_SESSION['last_attempt_time'] > 300) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_start'] = 0;
    }

    // Lockout logic
    if ($_SESSION['lockout_start'] > 0 && (time() - $_SESSION['lockout_start'] < 300)) {
        echo "<script>
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Account Locked',
                    text: 'Please wait 5 minutes before trying again.',
                    showConfirmButton: false,
                    timer: 5000
                });
            </script>";
        echo "<script>
                document.querySelector('input[name=email]').disabled = true;
                document.querySelector('input[name=password]').disabled = true;
                document.querySelector('button[type=submit]').disabled = true;
            </script>";
        exit;
    }

    if ((isset($_POST['email']) && $_POST['email'] != NULL) &&
        (isset($_POST['password']) && $_POST['password'] != NULL)
    ) {
        $email = $_POST['email'];
        $pass = $_POST['password'];

        // Prepare and execute the SELECT query to verify email
        $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $queryVerify = $stmt->get_result();

        if ($queryVerify->num_rows > 0) {
            $row = $queryVerify->fetch_assoc();
            $storedHashedPassword = $row['Password']; // Retrieve hashed password

            if (password_verify($pass, $storedHashedPassword)) {
                $defPass = isset($_SESSION['defPass']) ? $_SESSION['defPass'] : '';
                $role = $row['Department'];
                $name = $row['Name'];

                // Set session variables
                $_SESSION['department'] = $role;
                $_SESSION['name'] = $name;

                $_SESSION['login_attempts'] = 0; // Reset login attempts
                $_SESSION['lockout_start'] = 0; // Reset lockout time

                if (password_verify($def, $storedHashedPassword)) {
                    $otp = rand(100000, 999999);

                    // Prepare and execute the UPDATE query for OTP
                    $otpStmt = $conn->prepare("UPDATE tbl_user SET Otp = ? WHERE Email = ?");
                    $otpStmt->bind_param("is", $otp, $email);
                    $otpStmt->execute();

                    $_SESSION['email'] = $email;

                    // Call the email verification function
                    emailVerification($email, $otp, $name);

                    $otpStmt->close();
                } else {
                    $_SESSION['department'] = $role;
                    header("location: agq_dashCatcher.php");
                    exit;
                }
            } else {
                // Password is incorrect
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();

                // Check if lockout should start
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['lockout_start'] = time();
                    echo "<script>
                            Swal.fire({
                                position: 'center',
                                icon: 'warning',
                                title: 'Account Locked',
                                text: 'You have reached the maximum login attempts. Please wait 5 minutes before trying again.',
                                showConfirmButton: false,
                                timer: 5000
                            });
                        </script>";
                    echo "<script>
                            document.querySelector('input[name=email]').disabled = true;
                            document.querySelector('input[name=password]').disabled = true;
                            document.querySelector('button[type=submit]').disabled = true;
                        </script>";
                    exit;
                }

                echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Invalid Log In',
                            text: 'Account does not Exist or Email and Password do not match.',
                            showConfirmButton: false,
                            timer: 5000
                        });
                    </script>";
            }
        } else {
            // Email not found
            echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        title: 'Invalid Log In',
                        text: 'Account does not Exist or Email and Password do not match.',
                        showConfirmButton: false,
                        timer: 5000
                    });
                </script>";
        }

        // Close the prepared statement
        $stmt->close();
    }

    // Close the database connection
    $conn->close();
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

            return val_email && val_pass;
        }

        function validate_email() {
            var email = document.getElementById("inputs");
            var emailregex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.+-]+$/;
            //var email_error = document.getElementById("email-error");
            let isValid = true; // Track overall validity


            if (!email.value.trim()) {
                email.setCustomValidity("Please enter your email address");

            } else if (!emailregex.test(email.value)) {
                email.setCustomValidity("Email should be in the format xxx@xxx");

            } else {
                email.setCustomValidity(""); // Reset validation
            }

            email.reportValidity(); // Show validation message

            if (!email.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            email.addEventListener("input", function() {
                email.setCustomValidity(""); // Clear error when user types
            });

            return isValid; // Return validity status

        }

        function validate_password() {
            var nPass = document.getElementById("inputs0");
            const allowedSymbols = /^[a-zA-Z0-9!.@$%^&()_+\-:/|,~ \r\n]*$/; // Allow letters, numbers, symbols, and line breaks
            var passregex = /^.{8,100}$/;
            let isValid = true; // Track overall validity
            //var nPass_error = document.getElementById("pass-error");

            if (!nPass.value.trim()) {
                nPass.setCustomValidity("Please enter your password");
            } else if (!allowedSymbols.test(nPass.value)) {
                nPass.setCustomValidity("Only letters, numbers, and these symbols are allowed: ! @ $ % ^ & ( ) _ + / - : | , ~");
            } else if (!passregex.test(nPass.value)) {
                nPass.setCustomValidity("Password must be atleast 8 characters");

            } else {
                nPass.setCustomValidity(""); // Reset validation
            }

            nPass.reportValidity(); // Show validation message

            if (!nPass.checkValidity()) {
                event.preventDefault(); // Prevent form submission if invalid
            }

            nPass.addEventListener("input", function() {
                nPass.setCustomValidity(""); // Clear error when user types
            });

            return isValid;

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