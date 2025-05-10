<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $title = mysqli_real_escape_string($conn, $_POST['postTitle']);
    $description = mysqli_real_escape_string($conn, $_POST['postDescription']);
    $created_at = date('Y-m-d H:i:s');

    // File upload handling
    if (isset($_FILES['postPhoto']) && $_FILES['postPhoto']['error'] == 0) {
        $target_dir = "image_uploads/";
        $file_name = basename($_FILES['postPhoto']['name']);
        $unique_file_name = time() . "_" . $file_name; // Generate unique filename
        $target_file = $target_dir . $unique_file_name;

        // Attempt to move uploaded file
        if (move_uploaded_file($_FILES['postPhoto']['tmp_name'], $target_file)) {
            // Insert only the filename into the database
            $sql = "INSERT INTO events (title, description, image_path) 
                    VALUES ('$title', '$description', '$unique_file_name')";
            
            if (mysqli_query($conn, $sql)) {
                echo "Post added successfully!";
            } else {
                echo "Database error: " . mysqli_error($conn);
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "No file uploaded or file upload error occurred.";
    }
}
?>
