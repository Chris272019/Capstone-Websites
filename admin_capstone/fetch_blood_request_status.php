<?php
header('Content-Type: application/json');
include('connection.php');

if (isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);

    $sql = "SELECT status FROM admin_blood_request WHERE patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($status);
    if ($stmt->fetch()) {
        echo json_encode(['status' => $status]);
    } else {
        echo json_encode(['status' => null]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => null]);
}

$conn->close();
?>
