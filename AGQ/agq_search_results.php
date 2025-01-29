<?php
require 'db.php';

$searchQuery = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

$sql = "SELECT TransactionID, description FROM trans_test WHERE TransactionID LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%'";
$result = $conn->query($sql);
?>

<html>

<head>
    <title>Search Results</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>

    <?php if ($result->num_rows > 0) : ?>
        <ul>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <li>
                    <strong>Transaction ID:</strong> <?php echo $row['TransactionID']; ?><br>
                    <strong>Description:</strong> <?php echo $row['description']; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>No results found.</p>
    <?php endif; ?>

    <a href="dashboard.php">Back</a>
</body>

</html>