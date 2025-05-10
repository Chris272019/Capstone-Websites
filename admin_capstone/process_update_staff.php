<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $surname = $_POST['surname'];
    $middlename = $_POST['middlename'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $sql = "UPDATE staff_account SET firstname=?, surname=?, middlename=?, email_address=?, role=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $firstname, $surname, $middlename, $email, $role, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    exit();
}
?>
