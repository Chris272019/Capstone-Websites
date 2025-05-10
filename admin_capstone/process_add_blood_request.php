<?php
// process_add_blood_request.php

include 'connection.php'; // Adjust to your actual DB connection file

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and collect inputs
    $hospital_name = $_POST['hospital_name'] ?? '';
    $blood_component = $_POST['blood_component'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $amount_ml = isset($_POST['amount_ml']) ? (int)$_POST['amount_ml'] : 0;
    $patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;

    // Validate inputs
    if ($hospital_name && $blood_component && $blood_group && $amount_ml > 0) {
        // Insert into database
        $sql = "INSERT INTO admin_blood_request (hospital_name, blood_component, blood_group, amount_ml, patient_id)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $hospital_name, $blood_component, $blood_group, $amount_ml, $patient_id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Blood request added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Insert failed: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid input."]);
    }

    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
