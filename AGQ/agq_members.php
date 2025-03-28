<?php
require 'db_agq.php';

session_start();

$role = isset($_SESSION['department']) ? $_SESSION['department'] : '';

if (!isset($_SESSION['department'])) {
    header("Location: agq_login.php");
    exit();
} elseif ($role == 'Export Brokerage' || $role == 'Export Forwarding' || $role == 'Import Brokerage' || $role == 'Import Forwarding') {
    header("Location: agq_dashCatcher.php");
    exit();
}


if (!$role) {
    header("Location: UNAUTHORIZED.php?error=401r");
}

// Handle form submission (Create User)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $UserID = (string) random_int(10000000, 99999999);
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = "AGQ@2006";
    $defaultPassword = password_hash($password, PASSWORD_BCRYPT);
    $_SESSION['defPass'] = $defaultPassword;
    $department = htmlspecialchars(trim($_POST['department']));
    $otp = null;

    $errors = [];

    // Validate name (only letters and spaces)
    if (empty($name) || !preg_match("/^[a-zA-Z\s ]+$/", $name)) {
        $errors[] = "Name must contain only letters and spaces.";
    }

    // Validate email format
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate department selection
    if (empty($department)) {
        $errors[] = "Please select a department.";
    }

    // Check for errors before proceeding
    if (!empty($errors)) {
        echo json_encode(["success" => false, "errors" => $errors]);
        exit;
    }

    $checkEmailStmt = $conn->prepare("SELECT Email FROM tbl_user WHERE Email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        echo json_encode(["success" => false, "errors" => ["Email already exists. Please use a different email."]]);
        exit;
    }
    $checkEmailStmt->close();

    //$hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO tbl_user (UserID, Name, Email, Password, Department, Otp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $UserID, $name, $email, $defaultPassword, $department, $otp);

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
    <link rel="icon" type="image/x-icon" href="/AGQ/images/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members of the Company</title>
    <link rel="stylesheet" href="motc.css">
    <link rel="icon" type="image/x-icon" href="../AGQ/images/favicon.ico">
</head>

<body>

    <body style="background-image: url('mbg.png'); background-repeat: no-repeat; background-size: cover; background-position: center; background-attachment: fixed;">
        <a href="agq_dashCatcher.php" style="text-decoration: none; color: black; font-size: x-large; position: absolute; left: 20px; top: 20px;">←</a>
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
                                <td><button class="delete-btn" onclick="deleteUser('<?= htmlspecialchars($user['UserID'], ENT_QUOTES); ?>')">Delete</button></td>
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

                <div id="modalErrors" style="color: red; display: none;"></div>


                <form id="userForm">

                    <label for="name">NAME</label>
                    <input type="text" id="name" name="name" onchange="return validate_name()">

                    <label for="email">EMAIL</label>
                    <input type="email" maxlength="100" id="email" name="email" onchange="return validate_email()">


                    <label for="department">DEPARTMENT</label>
                    <select id="department" name="department" required>
                        <option value="">--Select Department--</option>
                        <option value="Admin">Admin</option>
                        <option value="Export Forwarding">Export Forwarding</option>
                        <option value="Export Brokerage">Export Brokerage</option>
                        <option value="Import Brokerage">Import Brokerage</option>
                        <option value="Import Forwarding">Import Forwarding</option>
                    </select>

                    <button type="submit" onchange="return validate_form()">SAVE</button>
                </form>
            </div>
        </div>

        <script>
            function validate_form() {
                var val_name = validate_name();
                var val_pass = validate_password();

                return val_email && val_pass;
            }

            function validate_name() {
                var nPass = document.getElementById("name");
                const allowedSymbols = /^[a-zA-Z0-9. ]*$/; // Added space to allowed characters
                var passregex = /^.{3,100}$/;
                let isValid = true; // Track overall validity

                if (!nPass.value.trim()) {
                    nPass.setCustomValidity("Please enter your name");
                } else if (!allowedSymbols.test(nPass.value)) {
                    nPass.setCustomValidity("Alphanumeric characters, spaces, and/or a period only.");
                } else if (!passregex.test(nPass.value)) {
                    nPass.setCustomValidity("Name must be at least 3 characters");
                } else {
                    nPass.setCustomValidity(""); // Reset validation
                }

                nPass.reportValidity(); // Show validation message

                if (!nPass.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                nPass.addEventListener("input", function() {
                    nPass.setCustomValidity(""); // Clear error when user types
                });

                return isValid;
            }

            function validate_email() {
                var email = document.getElementById("email");
                var emailregex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9_.+-]+$/;
                //var email_error = document.getElementById("email-error");
                let isValid = true; // Track overall validity


                if (!email.value.trim()) {
                    email.setCustomValidity("Please enter your email address");

                } else if (!emailregex.test(email.value)) {
                    email.setCustomValidity("Email should be in the format xxx@xxx");

                } else {
                    email.setCustomValidity(""); // Reset validation
                }

                email.reportValidity(); // Show validation message

                if (!email.checkValidity()) {
                    event.preventDefault(); // Prevent form submission if invalid
                }

                email.addEventListener("input", function() {
                    email.setCustomValidity(""); // Clear error when user types
                });

                return isValid; // Return validity status
            }
        </script>

        <script>
            function openModal() {
                document.getElementById("userModal").style.display = "flex";
                document.getElementById("modalErrors").style.display = "none";
            }

            function closeModal() {
                document.getElementById("userModal").style.display = "none";
            }


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
                fetch("agq_members.php?search=" + encodeURIComponent(document.getElementById('searchInput').value))
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

            // Function to display errors in the modal
            function showErrorMessage(id, message) {
                const errorElement = document.getElementById(id);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = "block";
                } else {
                    console.error(`Element with ID '${id}' not found.`);
                }
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function deleteUser(userId) {
                // Use SweetAlert2 for confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete this user!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // User confirmed, proceed with deletion
                        fetch("agq_members.php?delete_id=" + encodeURIComponent(userId))
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Show success message with SweetAlert2
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message,
                                        icon: 'success'
                                    });
                                    // Remove the row from the table
                                    document.querySelector(`tr[data-user-id="${userId}"]`).remove();
                                } else {
                                    // Show error message with SweetAlert2
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting user:', error);
                                // Show error message with SweetAlert2
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to delete user. Please try again.',
                                    icon: 'error'
                                });
                            });
                    }
                });
            }
        </script>

        <script>
            document.getElementById("userForm").addEventListener("submit", function(event) {
                event.preventDefault();
                let formData = new FormData(this);
                let modalErrors = document.getElementById("modalErrors");

                fetch("agq_members.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Response Data:", data); // Debugging log

                        if (!data.success) {
                            if (data.errors && data.errors.length > 0) {
                                // Display validation errors in the modal
                                modalErrors.innerHTML = data.errors.map(err => `<p>${err}</p>`).join('');
                                modalErrors.style.display = "block";
                            } else {
                                // Display a generic error using SweetAlert2
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'An unknown error occurred.',
                                    icon: 'error'
                                });
                                closeModal();
                            }
                        } else {
                            // Success! Close modal and show success message
                            modalErrors.style.display = "none";
                            closeModal();

                            // Show success message with SweetAlert2
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success'
                            });

                            // Add the new user to the table
                            addRowToTable(data.user);
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting form:', error);

                        // Display network or other errors with SweetAlert2
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to submit. Please try again.',
                            icon: 'error'
                        });

                        closeModal();
                    });
            });
        </script>

    </body>

</html>