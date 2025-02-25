<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

function emailVerification($email, $otp){

    
    $mail = new PHPMailer(true);                              // Passing true enables exceptions
    try {
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'danikkacarreon@gmail.com';                 // SMTP username
        $mail->Password = 'qidhpsnyieloqokf';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, ssl also accepted
        $mail->Port = 587;                                    // TCP port to connect to
    
        //Recipients
        $mail->setFrom( 'danikkacarreon@gmail.com', 'AGQ Freight Logistics');
        $mail->addAddress( $email);     // Add a recipient
        //Content
        $mail->isHTML(true);  // Set email format to HTML
        $mail->Subject = "AGQ OTP Verification";
        $mail->Body    = "<p>Good day, this your account verification code</p> 
                          <br><h1><b>".$otp."</b></h1>";

        $mail->send();
        ?>
            <script>
                Swal.fire({
                    icon: "success",
                    title: "OTP Sent",
                    confirmButtonText: "Proceed"
                    }).then((result) => {
                    
                        if (result.isConfirmed) {
                           window.location.href = "agq_otp.php";
                        }
                    });
            </script>
        <?php
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }



}


?>