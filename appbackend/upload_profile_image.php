<?php
include 'connection.php'; // Ensure this file connects to your database

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image']) && isset($_POST['username'])) {
    $username = $_POST['username'];
    $image = $_FILES['profile_image'];
    $imageName = uniqid() . '_' . basename($image['name']);
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/image/'; // Correct path to public_html/image/
    $uploadPath = $uploadDir . $imageName;

    // Move uploaded file to the existing public_html/image directory
    if (move_uploaded_file($image['tmp_name'], $uploadPath)) {
        // Update database with the new profile image filename
        $sql = "UPDATE users SET profile_image = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $imageName, $username);
        
        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Image uploaded successfully",
                "image" => $imageName
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database update failed"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
