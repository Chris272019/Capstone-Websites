<?php
function sendFCMNotification($token, $title, $body) {
    $url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = "YOUR_SERVER_KEY"; // Replace with your Firebase server key

    $notification = [
        'title' => $title,
        'body' => $body
    ];

    $data = [
        'to' => $token,
        'notification' => $notification
    ];

    $headers = [
        'Authorization: key=' . $serverKey,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Database connection
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("SELECT verification_status, fcm_token FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['verification_status'] == 'Verified') {
            sendFCMNotification($row['fcm_token'], "Verification Approved", "Your account is now verified!");
        }
    }

    $stmt->close();
    $conn->close();
}
?>
