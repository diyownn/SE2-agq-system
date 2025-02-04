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

// Handle form submission to add new user to the database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (string) random_int(10000000, 99999999);
    $name = htmlspecialchars($_POST['Name']);
    $email = htmlspecialchars($_POST['Email']);
    $password = $_POST['Password']; //$hashedPassword = password_hash($password, PASSWORD_DEFAULT); For hashing
    $department = htmlspecialchars($_POST['Department']);
    $otp = null; // Default to null for OTP

    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO tbl_user (User_id, Name, Email, Password, Department, Otp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $user_id, $name, $email, $password, $department, $otp);

    if ($stmt->execute()) {
        echo "<script>alert('User created and saved to the database.');</script>";
    } else {
        echo "<script>alert('Error saving user to the database.');</script>";
    }

    $stmt->close();
}

// Handle deletion of user from the database
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete user from database
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input {
            padding: 8px;
            width: 100%;
            max-width: 300px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>

    <h1>Create Users</h1>

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
                        <td><?= $user['User_id']; ?></td>
                        <td><?= $user['Name']; ?></td>
                        <td><?= $user['Email']; ?></td>
                        <td><?= $user['Password']; ?></td>
                        <td><?= $user['Department']; ?></td>
                        <td><?= isset($user['Otp']) && $user['Otp'] !== null ? $user['Otp'] : ''; ?></td>
                        <td><a href="?delete_id=<?= $user['User_id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No users created yet.</p>
    <?php endif; ?>

</body>

</html>