<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter OTP | AGQ</title>

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
<body>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-offset-4 col-sm-4" id="border">
                <img src="images/agq_logo.png" alt="logo" class="mx-auto d-block" id="agqlogo">
                <p id="title" class="text-center">Enter OTP</p>

                <form action="agq_otp.php" method="post" class="form-content" onsubmit="return validate_otp()">
                    <div class="d-flex justify-content-center flex-column align-items-center" style="margin-top: 5%;">
                        <input type="number" name="otp" id="inputs" class="form-control" style="width: 160px;">
                        <div id="otp-error" class="text-center mt-2"></div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <input type="submit" id="button1" style="margin-bottom: 50.5%;" value="SUBMIT">
                        <input type="button" id="button1" name="resend" style="margin-bottom: 50.5%; margin-left: 5px" value="RESEND">
                    </div>
                </form>

                <a href="agq_forgotEmail.php" style="text-decoration: none; color: black; font-size: x-large; margin:0%; padding:0%">←</a>

            </div>
        </div>
    </div>

<?php
    session_start();// Start the session at the beginning of your script
    require_once "db_agq.php";
    include "agq_mailer.php";

    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';


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

    if (isset($_POST['otp']) && $_POST['otp'] != NULL) {
        
        $otp = $_POST['otp'];

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
        }else {
            $otpVerify = "SELECT * FROM tbl_user WHERE Otp = '$otp'";
            $queryVerify = $conn->query($otpVerify);

        if ($queryVerify->num_rows == 1) {

            $_SESSION['login_attempts'] = 0;
            $_SESSION['lockout_start'] = 0; // Reset lockout start time

            $update_pass = "UPDATE tbl_user SET Password = '', Otp = NULL WHERE Email = '$email'";
            $conn->query($update_pass);

            
            header("Location: agq_resetPass.php");

        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();

            ?>
            <script>
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Invalid Log In",
                    text: "OTP is incorrect or expired",
                    showConfirmButton: false,
                    timer: 5000
                });
            </script>
            <?php

            }

            $conn->close();
        }

} if (isset($_POST['resend'])) {

    $resend_otp = "UPDATE tbl_user SET Otp = NULL WHERE Email = '$email'";
    $conn->query($resend_otp);

    $otp = rand(000000,999999);
                    
    $otpQuery = "UPDATE tbl_user SET Otp = '$otp' WHERE Email = '$email'";
    $conn->query($otpQuery);

    emailVerification($email, $otp);

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
        function validate_otp(){
            var otp = document.getElementById("inputs");
            var otp_error = document.getElementById("otp-error");
        
            if(otp.value == ''){
                otp.classList.add("is-invalid");
                error_text = "*Please enter OTP";
                otp_error.innerHTML = error_text;
                otp_error.classList.add("invalid-feedback");
            return false;
            } else {
                var otpregex = /^.{6,6}$/; 

                if(!otpregex.test(otp.value)){ 
                    otp.classList.add("is-invalid");
                    error_text = "*OTP is only a 6-digit number";
                    otp_error.innerHTML = error_text;
                    otp_error.classList.add("invalid-feedback");
                    return false;
                }

                otp.classList.remove("is-invalid");
                otp_error.innerHTML = "";
                otp_error.classList.remove("invalid-feedback");
                return true;
            }
        }

        function disableInputField() {
            var inputOtp = document.getElementById("inputs");
            inputOtp.disabled = true;
            
            // Enable the fields after 5 minutes (300000 milliseconds)
            
            setTimeout(function() {
                inputOtp.disabled = false;
            }, 300000); 
        }
    </script>
    
</body>
</html>

