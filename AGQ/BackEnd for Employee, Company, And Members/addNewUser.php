<?php
$host = 'localhost';
$dbname = 'agq_database';
$username = 'root';
$password = '';

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
        $stmt->bind_param("sssssi", $user_id, $name, $email, $hashedPassword, $department, $otp);

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Users</title>
    <link rel = "stylesheet" type = "text/css" href = "newUser.css">
    
</head>
<body>
    <h1>Create Users</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="Name">Name:</label>
        <input type="text" id="Name" name="Name" required>

        <label for="Email">Email:</label>
        <input type="email" id="Email" name="Email" required>

        <label for="Password">Password:</label>
        <input type="password" id="Password" name="Password" required>

        <label for="Department">Department:</label>
        <input type="text" id="Department" name="Department" required>
        <br>

        <button type="submit">Create User</button>
    </form>

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

</body>
</html>
