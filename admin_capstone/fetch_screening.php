<?php
// Include database connection
include('connection.php');

// Check if user_id is provided
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    // Fetch screening answers for the user
    $sql = "SELECT Q1, Q2, Q3, Q4, Q5, Q6, Q7, Q8, Q9, Q10,
                   Q11, Q12, Q13, Q14, Q15, Q16, Q17, Q18, Q19, Q20,
                   Q21, Q22, Q23, Q24, Q25, Q26, Q27, Q28, Q29, Q30
            FROM screening_answers 
            WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Question</th><th>Answer</th></tr></thead>';
        echo '<tbody>';
        foreach ($row as $key => $value) {
            echo '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No screening details found for this user.</p>';
    }
} else {
    echo '<p>User ID not provided.</p>';
}
?>
