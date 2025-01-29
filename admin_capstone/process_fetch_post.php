<?php
include('connection.php');

// SQL query to fetch title, description, image_path, created_at, and id from the events table
$sql = "SELECT id, title, description, image_path, created_at FROM events ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

// Check if any posts are available
if (mysqli_num_rows($result) > 0) {
    echo '<div class="card-container">'; // Start the card container

    // Loop through the result set and display each post
    while ($row = mysqli_fetch_assoc($result)) {
        // Format the created_at date
        $created_at = date("F j, Y, g:i a", strtotime($row['created_at']));

        // Truncate description to show only part of it
        $short_description = substr($row['description'], 0, 150);  // You can adjust the length as needed
        $full_description = $row['description'];  // Full description

        // Check if the description is long enough to show "See More" button
        $show_see_more_button = strlen($row['description']) > 150;

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
        echo '<img src="' . $row['image_path'] . '" class="card-img-top" alt="Post Image">';

        // Closing card body
        echo '<div class="card-body"></div>';
        echo '</div>';
    }

    echo '</div>'; // End the card container
} else {
    echo '<p>No posts available.</p>';
}

mysqli_close($conn);
?>



<script>
    // Function to toggle the description visibility
// Function to toggle the description visibility

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
    transform: scale(1.05);
}

.card-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #333;
    margin: 15px;
}

.card-text {
    font-size: 0.9rem;
    color: #555;
    margin: 0 15px;
}

.card-text.description {
    font-style: italic;
}

.card-text.created-at {
    font-size: 0.8rem;
    color: #777;
    margin: 15px;
}

/* Image Style */
.card-img-top {
    width: 100%;
    height: 245px;;
    object-fit: cover;
    border-bottom: 1px solid #ddd;
}

/* Button and Description Styles */
.see-more-btn {
    margin: 15px;
    color: #007bff;
    font-weight: bold;
    text-decoration: none;
    border: none;
    background: none;
    cursor: pointer;
}

.see-more-btn:hover {
    text-decoration: underline;
}

.card-body {
    padding: 15px;
}

/* Flexbox Layout for Cards */
.card-container {
    display: flex;
    flex-wrap: wrap; /* Allow cards to wrap to the next line */
    justify-content: space-between; /* Space between the cards */
}

.card.post-card {
    flex: 1 1 calc(33.333% - 30px); /* Adjust width to 1/3 of the container */
    box-sizing: border-box;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card.post-card {
        flex: 1 1 calc(50% - 30px); /* On smaller screens, 2 cards per row */
    }
}

@media (max-width: 480px) {
    .card.post-card {
        flex: 1 1 100%; /* On mobile, 1 card per row */
    }
}
</style>
