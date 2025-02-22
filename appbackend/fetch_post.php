<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
include 'connection.php';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Base URL for image path (ensure this points to the publicly accessible directory)
    $baseUrl = 'http://localhost/Capstone/admin_capstone/image_uploads/';  // Change to your actual domain or localhost
    
    // SQL query to fetch posts from the events table, including the event ID
    $sql = "SELECT id, title, description, image_path, created_at FROM events ORDER BY created_at DESC";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Fetch the results
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if posts exist
    if ($posts) {
        // Add the full image URL to the posts data
        foreach ($posts as &$post) {
            // Ensure the image path is correctly formatted
            $post['image_url'] = !empty($post['image_path']) ? $baseUrl . $post['image_path'] : null;
        }

        // Return posts as JSON
        echo json_encode(['status' => 'success', 'posts' => $posts]);
    } else {
        // If no posts found, return a message
        echo json_encode(['status' => 'fail', 'message' => 'No posts available']);
    }
} catch (PDOException $e) {
    // If an error occurs, return the error message
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
