<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include "db_connection.php"; // Ensure you have a database connection file

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']); // Sanitize input

        $query = "SELECT user_id, donation_date, donation_time, location, status, schedule_type FROM donations WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $donations = [];
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }

        echo json_encode(["status" => "success", "data" => $donations]);
    } else {
        echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
