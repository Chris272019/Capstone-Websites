<?php
// Include database connection
include('connection.php');

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    // Fetch user details to get the ID images
    $sql = "SELECT school_identification, company_identification, prc_identification, drivers_identification, sss_gsis_bir 
            FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $imageDirectory = $_SERVER['DOCUMENT_ROOT'] . "/Capstone/image/"; // Full server path
        $imageURLBase = "/Capstone/image/"; // Correct public URL

        // ID types mapping with their respective column names
        $idTypes = [
            "School ID" => $row['school_identification'],
            "Company ID" => $row['company_identification'],
            "PRC ID" => $row['prc_identification'],
            "Driver's License" => $row['drivers_identification'],
            "SSS/GSIS/BIR ID" => $row['sss_gsis_bir']
        ];

        // Flag to check if any image is found
        $hasImage = false;
        echo '<div class="text-center">';

        // Loop through each ID type and display if available
        foreach ($idTypes as $idType => $imageName) {
            // Ensure image name is not null or empty
            if (!empty($imageName) && $imageName !== 'null') {
                $imagePath = $imageDirectory . basename($imageName); // Full server path
                $imageURL = $imageURLBase . basename($imageName); // Publicly accessible URL

                if (file_exists($imagePath)) {
                    echo '<div class="mb-3">';
                    echo '<p><strong>' . htmlspecialchars($idType) . '</strong></p>';
                    echo '<img src="' . htmlspecialchars($imageURL) . '" class="img-fluid img-thumbnail" style="width: 200px; height: auto;" alt="' . htmlspecialchars($idType) . '">';
                    echo '</div>';
                    $hasImage = true;
                } else {
                    // Log missing files
                    error_log("Image file not found: " . $imagePath);
                    echo '<p style="color: red;">File not found: ' . htmlspecialchars($imagePath) . '</p>';
                }
            }
        }

        if (!$hasImage) {
            echo '<div class="alert alert-warning">No verification images found for this user.</div>';
        }

        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">User not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?>
