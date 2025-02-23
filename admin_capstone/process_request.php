<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'], $_POST['action'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action == 'accept') {
        $sql = "UPDATE blood_request SET status='Accepted' WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Request Accepted Successfully'); window.location.href='admin_dashboard.php';</script>";
    } elseif ($action == 'reject' && isset($_POST['rejection_reason'])) {
        $rejection_reason = $_POST['rejection_reason'];
        $sql = "UPDATE blood_request SET status='Rejected', rejection_reason=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $rejection_reason, $id);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Request Rejected Successfully'); window.location.href='admin_dashboard.php';</script>";
    }
}

$conn->close();
?>
