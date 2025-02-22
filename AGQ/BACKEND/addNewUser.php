<?php
$host = 'localhost';
$dbname = 'agq_database';
$username = 'root';
$password = '';
/*
session_start();

if (!isset($_SESSION['redirected'])) {
    $_SESSION['redirected'] = true; // To compact pages

    function encrypt_url($url, $key)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted_url = openssl_encrypt($url, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted_url . '::' . $iv);
    }

    function decrypt_url($encrypted_url, $key)
    {
        list($encrypted_url, $iv) = explode('::', base64_decode($encrypted_url), 2);
        return openssl_decrypt($encrypted_url, 'aes-256-cbc', $key, 0, $iv);
    }

    $original_url = 'http://localhost/SOFTENGOFFICIAL/AGQ/addNewUser.php';
    $key = '0jRw1M89WhVwukjsZiZvhPPsRVFgK/IIQnLOYVEWDdi2TXJjx8QPOAOIxMH7b+uW';

    $encrypted_url = encrypt_url($original_url, $key);
    $encoded_url = urlencode($encrypted_url);

    header('Location: addNewUser.php?url=' . $encoded_url);
    exit;
} else {

    unset($_SESSION['redirected']);
}
*/
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];

// Handle form submission to add new user to the database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (string) random_int(10000000, 99999999);
    $name = htmlspecialchars(trim($_POST['Name']));
    $email = htmlspecialchars(trim($_POST['Email']));
    $password = $_POST['Password']; //$Password = password_hash($password, PASSWORD_DEFAULT); For hashing
    $department = htmlspecialchars(trim($_POST['Department']));
    $otp = null;

    // Validation checks
    if (empty($name) || empty($email) || empty($password) || empty($department)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}$/', $password)) {
        $errors[] = "Password must be at least 10 characters long, contain at least one letter, one number, and one special character.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO tbl_user (User_id, Name, Email, Password, Department, Otp) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $user_id, $name, $email, $password, $department, $otp);

        if ($stmt->execute()) {
            echo "<script>alert('User created and saved to the database.');</script>";
        } else {
            echo "<script>alert('Error saving user to the database.');</script>";
        }

        $stmt->close();
    }
}

// Handle deletion of user from the database
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM tbl_user WHERE User_id = ?");
    $stmt->bind_param("s", $delete_id);
    $stmt->execute();
    $stmt->close();
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
                            <input type="password" class="form-control" name="Password" placeholder="Password" required>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control" name="Department" required>
                                <option value="">--Select Department--</option>
                                <option value="Export Brokerage">Export Brokerage</option>
                                <option value="Export Forwarding">Export Forwarding</option>
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
</body>

</html>

<!--
    <h2>Created Users</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Department</th>
                    <th>Otp</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['User_id']); ?></td>
                        <td><?= htmlspecialchars($user['Name']); ?></td>
                        <td><?= htmlspecialchars($user['Email']); ?></td>
                        <td><?= str_repeat('*', 10); ?></td>
                        <td><?= htmlspecialchars($user['Department']); ?></td>
                        <td><?= isset($user['Otp']) && $user['Otp'] !== null ? htmlspecialchars($user['Otp']) : ''; ?></td>
                        <td><a href="?delete_id=<?= htmlspecialchars($user['User_id']); ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No users created yet.</p>
    <?php endif; ?>
    -->