<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Email | AGQ</title>

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
                <p id="title" class="text-center">Forgot Password</p>

                <form action="agq_forgotEmail.php" method="post" class="form-content">
                    <label for="inputs" class="form-label" id="labels">Enter email</label>
                    <input type="text" name="email" id="inputs" class="form-control" required> 

                    <div class="d-flex justify-content-center">
                        <input type="submit" id="button1" value="NEXT">
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

</body>
</html>

<?php
    require_once "db_agq.php";
    include "agq_mailer.php";

    if ((isset($_POST['email']) && $_POST['email'] != NULL)) {
        
        $email = $_POST['email'];

        $emailVerify = "SELECT * FROM tbl_user WHERE Email = '$email'";
        $queryVerify = $conn->query($emailVerify);

        if ($queryVerify->num_rows>0) {
            $otp = rand(000000,999999);
                    
            $otpQuery = "UPDATE tbl_user SET Otp = '$otp' WHERE Email = '$email'";
            $conn->query($otpQuery);

            emailVerification($email, $otp);

        }else {
            ?>
            <script>
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Invalid",
                    text: "Email is not in the System.",
                    showConfirmButton: false,
                    timer: 3000
                });
            </script>
        
            <?php

        }

        $conn->close();

    }
?>
