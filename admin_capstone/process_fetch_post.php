<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('connection.php');

// Debug connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch title, description, image_path, created_at, and id from the events table
$sql = "SELECT id, title, description, image_path, created_at FROM events ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

// Debug query result
if (!$result) {
    echo "Error executing query: " . $conn->error;
    exit;
}

// Debug number of rows
$num_rows = mysqli_num_rows($result);
echo "<!-- Debug: Number of events found: " . $num_rows . " -->";

// Check if any posts are available
if ($num_rows > 0) {
    echo '<div class="card-container">'; // Start the card container

    // Loop through the result set and display each post
    while ($row = mysqli_fetch_assoc($result)) {
        // Format the created_at date
        $created_at = date("F j, Y, g:i a", strtotime($row['created_at']));

        // Truncate description to show only part of it
        $short_description = substr($row['description'], 0, 150);  // Adjust the length as needed
        $full_description = $row['description'];  // Full description

        // Check if the description is long enough to show "See More" button
        $show_see_more_button = strlen($row['description']) > 150;

        // Construct image path (prepend 'image_uploads/' to the stored filename)
        $image_path = "image_uploads/" . $row['image_path'];

        // Display each post in a card
        echo '<div class="card post-card" id="card_' . $row['id'] . '">'; // Use event ID for the card id
        
        // Title
        echo '<h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>';

        // Short description with a "See More" button only if the description is long
        echo '<p class="card-text description" id="desc_' . $row['id'] . '">' . htmlspecialchars($short_description) . '...</p>';
        echo '<p class="card-text description-full" id="desc_full_' . $row['id'] . '" style="display: none;">' . htmlspecialchars($full_description) . '</p>';

        if ($show_see_more_button) {
            echo '<button class="btn btn-link see-more-btn" id="btn_' . $row['id'] . '" onclick="toggleDescription(' . $row['id'] . ')">See More</button>';
        }

        // Created At - wrapped in a separate <p> tag with a class for styling
        echo '<p class="card-text created-at"><strong>Created on:</strong> ' . $created_at . '</p>';
        
        // Image
        echo '<img src="' . htmlspecialchars($image_path) . '" class="card-img-top" alt="Post Image">';

        // Closing card body
        echo '<div class="card-body"></div>';
        echo '</div>';
    }

    echo '</div>'; // End the card container
} else {
    echo '<div class="alert alert-info">
            <h4>No Events Available</h4>
            <p>There are no events posted at the moment.</p>
            <p class="text-muted">New events will appear here once they are added to the system.</p>
          </div>';
}

mysqli_close($conn);
?>

<script>
    // Function to toggle the description visibility
    function toggleDescription(postId) {
        var shortDesc = document.getElementById('desc_' + postId);
        var fullDesc = document.getElementById('desc_full_' + postId);
        var button = document.getElementById('btn_' + postId);

        if (fullDesc.style.display === "none") {
            // Show the full description and hide the short one
            fullDesc.style.display = "block";
            shortDesc.style.display = "none";
            button.innerHTML = "See Less";  // Change button text
        } else {
            // Hide the full description and show the short one
            fullDesc.style.display = "none";
            shortDesc.style.display = "block";
            button.innerHTML = "See More";  // Change button text back
        }
    }
</script>

<style>
/* General Card Style */
.card.post-card {
    width: 100%;
    max-width: 18rem;
    margin: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.card.post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding: 20px;
    gap: 20px;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
    padding: 15px 15px 0;
    margin-bottom: 10px;
}

.card-text {
    color: #666;
    padding: 0 15px;
    margin-bottom: 10px;
}

.card-text.created-at {
    font-size: 0.9rem;
    color: #888;
    margin-top: 10px;
}

.card-img-top {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

.btn-link {
    color: #007bff;
    text-decoration: none;
    padding: 0;
    margin: 0 15px;
}

.btn-link:hover {
    color: #0056b3;
    text-decoration: underline;
}

.description-full {
    display: none;
}

.alert {
    margin: 20px;
    padding: 20px;
    border-radius: 8px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
}

.alert h4 {
    color: #0c5460;
    margin-bottom: 10px;
}

.text-muted {
    color: #6c757d;
    font-size: 0.9rem;
}
</style>
