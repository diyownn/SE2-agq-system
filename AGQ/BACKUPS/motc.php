<?php
$host = 'localhost';
$dbname = 'agq_database';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Handle form submission (Create User)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $UserID = (string) random_int(10000000, 99999999);
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $department = htmlspecialchars(trim($_POST['department']));
    $otp = null;

    if (empty($name) || empty($email) || empty($password) || empty($department)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO tbl_user (UserID, Name, Email, Password, Department, Otp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $UserID, $name, $email, $password, $department, $otp);

    if ($stmt->execute()) {
        $stmt = $conn->prepare("SELECT UserID, Name, Email, Department FROM tbl_user WHERE UserID = ?");
        $stmt->bind_param("s", $UserID);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        echo json_encode(["success" => true, "message" => "User added successfully!", "user" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Error saving user: " . $stmt->error]);
    }

    $stmt->close();
    exit;
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM tbl_user WHERE UserID = ?");
    $stmt->bind_param("s", $delete_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "User deleted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error deleting user."]);
    }

    $stmt->close();
    exit;
}

// Search functionality
if (isset($_GET['search'])) {
    $searchTerm = "%" . $_GET['search'] . "%";
    $stmt = $conn->prepare("SELECT UserID, Name, Email, Department FROM tbl_user WHERE Name LIKE ? OR Email LIKE ? OR Department LIKE ?");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }

    echo json_encode(["success" => true, "users" => $users]);
    exit;
}

// Fetch all users initially
$query = "SELECT UserID, Name, Email, Department FROM tbl_user";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members of the Company</title>
    <link rel="stylesheet" href="../motc.css">
</head>
<body>

    <div class="header">
        <h1>MEMBERS OF THE COMPANY</h1>
        <div class="top-bar">
            <input type="text" class="search-bar" id="searchInput" placeholder="Search">
            <button class="search-btn" onclick="searchUser()">Search</button>
            <button class="add-user-btn" onclick="openModal()">Add User</button>
        </div>
    </div>

    <div class="container">
        <div class="table-container">
            <table id="userTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr data-user-id="<?= htmlspecialchars($user['UserID']); ?>">
                            <td><?= htmlspecialchars($user['UserID']); ?></td>
                            <td><?= htmlspecialchars($user['Name']); ?></td>
                            <td><?= htmlspecialchars($user['Email']); ?></td>
                            <td><?= htmlspecialchars($user['Department']); ?></td>
                            <td><button class="delete-btn" onclick='deleteUser("<?= htmlspecialchars($user['UserID']); ?>")'>Delete</button></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>EMPLOYEE FORM</h2>
            <form id="userForm">
                <label for="name">NAME</label>
                <input type="text" id="name" name="name" required>

                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" required>

                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" required>

                <label for="department">DEPARTMENT</label>
                <input type="text" id="department" name="department" required>

                <button type="submit">SAVE</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById("userModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("userModal").style.display = "none";
        }

        function deleteUser(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                fetch("motc.php?delete_id=" + encodeURIComponent(userId))
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            document.querySelector(`tr[data-user-id="${userId}"]`).remove();
                        }
                    })
                    .catch(error => console.error('Error deleting user:', error));
            }
        }

        document.getElementById("userForm").addEventListener("submit", function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch("motc.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    addRowToTable(data.user);
                    closeModal();
                }
            })
            .catch(error => console.error('Error submitting form:', error));
        });

        function addRowToTable(user) {
            const tableBody = document.getElementById("userTableBody");
            const row = document.createElement("tr");
            row.setAttribute('data-user-id', user.UserID);
            row.innerHTML = `
                <td>${user.UserID}</td>
                <td>${user.Name}</td>
                <td>${user.Email}</td>
                <td>${user.Department}</td>
                <td><button class="delete-btn" onclick='deleteUser("${user.UserID}")'>Delete</button></td>
            `;
            tableBody.appendChild(row);
        }

        function searchUser() {
            fetch("motc.php?search=" + encodeURIComponent(document.getElementById('searchInput').value))
                .then(response => response.json())
                .then(data => {
                    document.getElementById("userTableBody").innerHTML = data.users.map(user => `
                        <tr data-user-id="${user.UserID}">
                            <td>${user.UserID}</td>
                            <td>${user.Name}</td>
                            <td>${user.Email}</td>
                            <td>${user.Department}</td>
                            <td><button class="delete-btn" onclick='deleteUser("${user.UserID}")'>Delete</button></td>
                        </tr>
                    `).join('');
                });
        }
    </script>

</body>
</html>
