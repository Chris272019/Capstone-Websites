<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($request_id > 0 && $status !== '') {
    $sql = "UPDATE admin_blood_request SET status = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $request_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>
