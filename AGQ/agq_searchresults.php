<?php
require 'db_agq.php';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

?>
<html>

<head>
    <title>Search Results</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>

    <?php
    if (!empty($searchQuery)) {
        // Prepare a safe SQL query
        $searchParam = "%{$searchQuery}%";
        $stmt = $conn->prepare("SELECT TransactionID FROM tbl_transaction WHERE TransactionID LIKE ?");
        $stmt->bind_param("s", $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) : ?>
            <ul>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <li><strong>Transaction ID:</strong> <?php echo htmlspecialchars($row['TransactionID']); ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else : ?>
            <p>No results found.</p>
    <?php endif;

        $stmt->close();
    } else {
        echo "<p>Please enter a search query.</p>";
    }
    ?>

    <a href="agq_employdash.php">Back</a>
</body>

</html>