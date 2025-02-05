<?php
$host = 'localhost';
$dbname = 'agq_database';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['company_picture']['tmp_name']) && isset($_POST['Company_name'])) {
    $company_picture = file_get_contents($_FILES['company_picture']['tmp_name']);
    $company_name = $_POST['Company_name'];
    $companyid = (string)random_int(1000000000, 9999999999);  // We are going to make this into an input. Random int for now


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO tbl_company (CompanyID, Company_name, Company_picture) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    if (strlen($company_name) > 25) {
        die("Company name must be 25 characters or less.");
    }

    if (preg_match('/[;<>]/', $company_name)) {
        die("Company name must not contain brackets, semicolons, or other disallowed characters.");
    }

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO tbl_company (CompanyID, Company_name, Company_picture) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    $stmt->bind_param("ssb", $companyid, $company_name, $company_picture);
    $stmt->send_long_data(2, $company_picture);

    if ($stmt->execute()) {
        echo "Company added successfully!";
    } else {
        echo "Error uploading company: " . $stmt->error;
    }


    $stmt->close();
    $conn->close();
} else {
    echo "Please fill in all fields.";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload and Preview Image</title>
    <style>
        .image-container {
            width: 300px;
            /*Edit mo nalang Dion*/
            height: 200px;
            /*Edit mo nalang Dion*/
            border: 1px solid #000;
            /*Edit mo nalang Dion*/
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
</head>

<body>

    <h2>Add Company</h2>


    <form action="addNewCompany.php" method="POST" enctype="multipart/form-data">
        <!-- Edit mo nalang Dion -->
        <label for="company_name">Company Name:</label>
        <input type="text" id="company_name" name="Company_name" required><br><br>

        <!-- Edit mo nalang Dion -->
        <label for="company_picture">Choose an image:</label>
        <input type="file" id="company_picture" name="company_picture" accept="image/*" required onchange="previewImage()"><br><br>

        <!-- Edit mo nalang Dion -->
        <div class="image-container" id="imageContainer">
            <img id="imagePreview" src="" alt="Image Preview">
        </div><br><br>

        <!-- Edit mo nalang Dion-->
        <input type="submit" value="Upload Image">
    </form>

    <script>
        function previewImage() {
            const file = document.getElementById('company_picture').files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const imgElement = document.getElementById('imagePreview');
                imgElement.src = e.target.result;
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>

</body>

</html>