<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['company_name'])) {
        $_SESSION['Company_name'] = $_POST['company_name'];
        echo "Company session stored successfully";
    } 
    
    if (isset($_POST['selected_department'])) {
        $_SESSION['SelectedDepartment'] = $_POST['selected_department'];
        echo "Department session stored successfully";
    }

    if (isset($_POST['document_type'])) {
        $selected_document = htmlspecialchars(trim($_POST['document_type'])); // Sanitize input
        $_SESSION['DocType'] = $selected_document; // Store in session
    
        echo "Session stored successfully: " . $selected_document; // Response for debugging
    } 
} else {
    echo "Failed to store session";
}
?>
