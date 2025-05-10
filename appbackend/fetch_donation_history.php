<?php
include 'connection.php'; // Ensure the correct path to your database connection

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user_id from POST request
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id === 0) {
        echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
        exit();
    }

    // Query to fetch donation history based on user_id
    $sql = "SELECT blood_group, donation_date, donation_quantity, location, status, notes, blood_type 
            FROM blood_donation_history 
            WHERE user_id = ? 
            ORDER BY donation_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $donationHistory = [];

    while ($row = $result->fetch_assoc()) {
        $donationHistory[] = [
            "blood_group" => $row["blood_group"],
            "donation_date" => $row["donation_date"],
            "donation_quantity" => $row["donation_quantity"],
            "location" => $row["location"],
            "status" => $row["status"],
            "notes" => $row["notes"],
            "blood_type" => $row["blood_type"],
        ];
    }

    if (empty($donationHistory)) {
        echo json_encode(["status" => "error", "message" => "No donation history found"]);
    } else {
        echo json_encode(["status" => "success", "donations" => $donationHistory]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
