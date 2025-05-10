<?php
include 'connection.php'; // Ensure correct path to your database connection

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user_id from POST request
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($user_id === 0) {
        echo json_encode(["status" => "error", "message" => "Invalid user ID"]);
        exit();
    }

    // Check for scheduled appointment
    $sql = "SELECT donation_date, donation_time, location, status, schedule_type 
            FROM schedule 
            WHERE user_id = ? AND status = 'scheduled'
            ORDER BY donation_date ASC LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "schedule" => [
                "donation_date" => $row["donation_date"],
                "donation_time" => $row["donation_time"],
                "location" => $row["location"],
                "status" => $row["status"],
                "schedule_type" => $row["schedule_type"]
            ]
        ]);
    } else {
        // If no scheduled appointment, check if a rescheduled appointment exists
        $sql = "SELECT COUNT(*) AS rescheduled_count FROM schedule WHERE user_id = ? AND status = 'rescheduled'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['rescheduled_count'] > 0) {
            // If a rescheduled appointment exists, return a specific status
            echo json_encode(["status" => "rescheduled", "message" => "Appointment has been rescheduled"]);
        } else {
            // No scheduled or rescheduled appointments
            echo json_encode(["status" => "error", "message" => "No upcoming donation schedule found"]);
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
