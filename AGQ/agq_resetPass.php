<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password | AGQ</title>

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
                <p id="title" class="text-center">Reset Password</p>

                <form action="agq_resetPass.php" method="post" class="form-content" onsubmit="return validate_form()">
                    <label for="newPass" class="form-label" id="labels">Enter New Password</label>
                    <input type="password" name="newPword" id="newPass" class="form-control">
                    <div id="pass-error1"></div>

                    <label for="rePass" class="form-label" id="labels">Re-enter Password</label>
                    <input type="password" name="rePword" id="rePass" class="form-control"> 
                    <div id="pass-error2"></div>

                    <div class="d-flex justify-content-center">
                        <input type="submit" id="button3" value="SAVE">
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
        function validate_form(){
            var val_newPass = validate_newPassword();
            var val_rePass = validate_rePassword();

            if (val_newPass && val_rePass){

                return validate_finalPassword();

            }else {
                return false;
            }
        }

        function validate_newPassword(){
            var nPass =document.getElementById("newPass");
            var nPass_error =document.getElementById("pass-error1");

            if(nPass.value == ''){
                nPass.classList.add("is-invalid");
                error_text = "*Please enter your new Password";
                nPass_error.innerHTML = error_text;
                nPass_error.classList.add("invalid-feedback");
                
                return false;
            }else{
                var passregex = /^.{8,}$/; 

                if(!passregex.test(nPass.value)){ 
                    nPass.classList.add("is-invalid");
                    error_text = "*Your Password must be atleast 8 characters";
                    nPass_error.innerHTML = error_text;
                    nPass_error.classList.add("invalid-feedback");
                    
                    return false;
                }else{
                    nPass.classList.remove("is-invalid");
                    nPass_error.innerHTML = "";
                    nPass_error.classList.remove("invalid-feedback");

                    return true;
                }
            }

        }

        function validate_rePassword(){
            var rPass =document.getElementById("rePass");
            var rPass_error =document.getElementById("pass-error2");

            if(rPass.value == ''){
                rPass.classList.add("is-invalid");
                error_text = "*Please re-enter your new Password";
                rPass_error.innerHTML = error_text;
                rPass_error.classList.add("invalid-feedback");
                
                return false;
            }else{
                var passregex = /^.{8,}$/; 

                if(!passregex.test(rPass.value)){ 
                    rPass.classList.add("is-invalid");
                    error_text = "*Your Password must be atleast 8 characters";
                    rPass_error.innerHTML = error_text;
                    rPass_error.classList.add("invalid-feedback");
                    
                    return false;
                }else{
                    rPass.classList.remove("is-invalid");
                    rPass_error.innerHTML = "";
                    rPass_error.classList.remove("invalid-feedback");

                    return true;
                }
            }

        }

        function validate_finalPassword() {
            var nPass =document.getElementById("newPass");
            var rPass =document.getElementById("rePass");
            var rPass_error =document.getElementById("pass-error2");
            var nPass_error =document.getElementById("pass-error1");

            if (nPass.value !== rPass.value) {
                nPass.classList.add("is-invalid");
                error_text = "*Passwords do not match.";
                nPass_error.innerHTML = error_text;
                nPass_error.classList.add("invalid-feedback");

                rPass.classList.add("is-invalid");
                error_text = "*Passwords do not match.";
                rPass_error.innerHTML = error_text;
                rPass_error.classList.add("invalid-feedback");

                return false;
            }else {
                nPass.classList.remove("is-invalid");
                nPass_error.innerHTML = "";
                nPass_error.classList.remove("invalid-feedback");

                rPass.classList.remove("is-invalid");
                rPass_error.innerHTML = "";
                rPass_error.classList.remove("invalid-feedback");

                return true;

            }

        }

    </script>

</body>
</html>

<?php
    require_once "db_agq.php";

    if ((isset($_POST['newPword']) && $_POST['newPword'] != NULL) && 
    (isset($_POST['rePword']) && $_POST['rePword'] != NULL)) {

        $finalPass = $_POST['rePword'];

        $reset_pass = "Update tbl_user set Password = '".$finalPass."' where Password = ''";
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
    }

?>