<?php
include 'db_connection.php'; // Ensure you have a valid DB connection file

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']); // Sanitize input

    $query = "SELECT is_female, Q1, Q2, Q3, Q4, Q5, Q6, Q7, Q8, Q9, Q10, 
                     Q11, Q12, Q13, Q14, Q15, Q16, Q17, Q18, Q19, Q20, 
                     Q21, Q22, Q23, Q24, Q25, Q26, Q27, Q28, Q29, Q30
              FROM screening_answers 
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "No data found"]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "User ID not provided"]);
}
?>
