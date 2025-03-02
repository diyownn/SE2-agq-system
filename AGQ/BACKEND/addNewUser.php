<?php
require '../db_agq.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (string) random_int(10000000, 99999999);
    $name = trim($_POST['Name']);
    $email = trim($_POST['Email']);
    $password = $_POST['Password'];
    $department = trim($_POST['Department']);
    $otp = null;
    $errors = [];

    if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $errors[] = "Name can only contain letters and spaces.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }


    if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/', $password)) {
        $errors[] = "Password must be at least 10 characters long, contain at least one letter, one number, and one special character.";
    }


    $stmt = $conn->prepare("SELECT Email FROM tbl_user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists. Please use a different email.";
    }
    $stmt->close();

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO tbl_user (User_id, Name, Email, Password, Department, Otp) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $user_id, $name, $email, $hashedPassword, $department, $otp);

        if ($stmt->execute()) {
            echo "<script>alert('User created and saved to the database.');</script>";
        } else {
            echo "<script>alert('Error saving user to the database.');</script>";
        }

        $stmt->close();
    }
}

// Fetch all users from the database
$query = "SELECT * FROM tbl_user";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- provide viewport -->
    <meta charset="utf-8">
    <meta name="keywords" content=""> <!-- provide keywords -->
    <meta name="description" content=""> <!-- provide description -->
    <title> Employee Form </title> <!-- provide title -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/x-icon" href="/AGQ/images/favicon.ico">
    <!-- Font Style -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="../css/newUser.css">
</head>

<body>
    <div class="container">
        <div class="form-container">
            <div class="form-box">
                <a href="agq_owndash.php" style="text-decoration: none; color: black; font-size: x-large">←</a>
                <h3 class="text-center fw-bold">EMPLOYEE FORM</h3>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="Name" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" name="Email" placeholder="Email Address" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="password" class="form-control" name="Password" placeholder="Password" onpaste="return false" oncopy="return false" oncut="return false" required>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control" name="Department" required>
                                <option value="">--Select Department--</option>
                                <option value="Admin">Admin</option>
                                <option value="Export Forwarding">Export Forwarding</option>
                                <option value="Export Brokerage">Export Brokerage</option>
                                <option value="Import Brokerage">Import Brokerage</option>
                                <option value="Import Forwarding">Import Forwarding</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-save">SAVE</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let passwordField = document.querySelector('input[name="Password"]');

            passwordField.addEventListener('paste', function(event) {
                event.preventDefault();
            });

            passwordField.addEventListener('copy', function(event) {
                event.preventDefault();
            });

            passwordField.addEventListener('cut', function(event) {
                event.preventDefault();
            });
        });
    </script>

</body>

</html>