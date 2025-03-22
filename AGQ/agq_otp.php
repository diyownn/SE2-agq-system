<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP | AGQ</title>

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
                <a href="agq_forgotEmail.php" class="back-button" style="text-decoration: none; color: black; font-size: x-large">←</a>
                <img src="images/agq_logo.png" alt="logo" class="mx-auto d-block" id="agqlogo">
                <p id="title" class="text-center">Enter OTP</p>

                <form id="otpForm" action="agq_otp.php" method="post" class="form-content" onsubmit="validate_otp()">
                    <div class="d-flex justify-content-center flex-column align-items-center" style="margin-top: 5%;">
                        <input type="number" name="otp" id="inputs" class="form-control" style="width: 160px;" onchange="validate_otp()">
                        <div id="otp-error" class="text-center mt-2"></div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="submit" name="submit_otp" id="button1" style="margin-bottom: 50.5%;">SUBMIT</button>
                        <button type="button" id="button1_1" style="margin-bottom: 50.5%; margin-left: 5px;">RESEND</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

<?php
    session_start();
    require_once "db_agq.php";
    include "agq_mailer.php";

    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';


    if (!isset($_SESSION['otp_attempts'])) {
        $_SESSION['otp_attempts'] = 0; 
    }
    if (!isset($_SESSION['last_otpattempt_time'])) {
        $_SESSION['last_otpattempt_time'] = time(); 
    }
    if (!isset($_SESSION['otplockout_start'])) {
        $_SESSION['otplockout_start'] = 0; 
    }

    if (time() - $_SESSION['last_otpattempt_time'] > 300) {
        $_SESSION['otp_attempts'] = 4;
        $_SESSION['otplockout_start'] = 0; // Reset lockout start time
    }

    if (isset($_POST['otp']) && $_POST['otp'] != NULL) {
        
        $otp = $_POST['otp'];

        if ($_SESSION['otp_attempts'] >= 5) {
            $_SESSION['otplockout_start'] = time();
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

            $_SESSION['otp_attempts'] = 0;
            $_SESSION['otplockout_start'] = 0; // Reset lockout start time

            $update_pass = "UPDATE tbl_user SET Password = '', Otp = NULL WHERE Email = '$email'";
            $conn->query($update_pass);

            
            header("Location: agq_resetPass.php");

        } else {
            $_SESSION['otp_attempts']++;
            $_SESSION['last_otpattempt_time'] = time();

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
        }

    } 

    if (isset($_POST['resend'])) {
    
        $resend_otp = "UPDATE tbl_user SET Otp = NULL WHERE Email = '$email'";
        $conn->query($resend_otp);

        $otp = rand(100000,999999);
                    
        $otpQuery = "UPDATE tbl_user SET Otp = '$otp' WHERE Email = '$email'";
        $conn->query($otpQuery);

        emailVerification($email, $otp);
    }

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
        document.getElementById("button1_1").addEventListener("click", function() {
        var form = document.getElementById("otpForm");
        var resendInput = document.createElement("input");
        resendInput.type = "hidden";
        resendInput.name = "resend";
        resendInput.value = "1";
        form.appendChild(resendInput);
        form.submit();
    });
        function validate_otp(){
            var otp = document.getElementById("inputs");
            var otpregex = /^.{6,6}$/; 
            let isValid = true; // Track overall validity

            //var otp_error = document.getElementById("otp-error");
        
            if (!otp.value.trim()) {
                otp.setCustomValidity("Please enter your OTP");

            } else if (!otpregex.test(otp.value)){
                otp.setCustomValidity("OTP is a 6-digit number");
            }else {
                otp.setCustomValidity(""); // Reset validation
                }

                otp.reportValidity(); // Show validation message

                if (!otp.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                otp.addEventListener("input", function () {
                    otp.setCustomValidity(""); // Clear error when user types
                });

            return isValid;
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

