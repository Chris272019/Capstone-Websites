<?php
// Include database connection
include('connection.php');

// Define the questions array - general questions (Q1-Q14)
$questions = [
    'Q1' => 'Have you ever been refused as a blood donor?',
    'Q2' => 'Are you giving blood only to be tested for HIV or Hepatitis?',
    'Q3' => 'Are you aware of HIV/Hepatitis transmission despite a negative test?',
    'Q4' => 'Had jaundice or hepatitis, or been incarcerated?',
    'Q5' => 'Traveled outside your country of residence?',
    'Q6' => 'Taken prohibited drugs?',
    'Q7' => 'Had a positive test for HIV, Hepatitis, or Syphilis?',
    'Q8' => 'Had Malaria or Hepatitis in the past?',
    'Q9' => 'Had sexually transmitted diseases?',
    'Q10' => 'Cancer, heart disease, or blood disorders?',
    'Q11' => 'Tuberculosis, asthma, or lung diseases?',
    'Q12' => 'Kidney disease, diabetes, or epilepsy?',
    'Q13' => 'Chickenpox or cold sores?',
    'Q14' => 'Other chronic medical conditions?'
];

// Female-specific questions (Q15-Q19)
$female_questions = [
    'Q15' => 'Recently had a rash or fever?',
    'Q16' => 'Are you currently pregnant or have you ever been pregnant?',
    'Q17' => 'When was your last childbirth?',
    'Q18' => 'Did you have a miscarriage or abortion in the last year?',
    'Q19' => 'Are you currently breastfeeding?'
];

// Check if user_id is provided
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    // Fetch screening answers for the user
    $sql = "SELECT * FROM screening_answers WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Question</th><th>Answer</th></tr></thead>';
        echo '<tbody>';
        
        // Display general questions
        echo '<tr class="table-primary"><td colspan="2"><strong>General Questions</strong></td></tr>';
        foreach ($questions as $key => $question) {
            $answer = isset($row[$key]) ? htmlspecialchars($row[$key]) : 'Not answered';
            echo "<tr><td>" . htmlspecialchars($question) . "</td><td>" . $answer . "</td></tr>";
        }
        
        // Display female-specific questions if the user is female
        if (isset($row['is_female']) && $row['is_female'] == 'true') {
            echo '<tr class="table-info"><td colspan="2"><strong>Female-Specific Questions</strong></td></tr>';
            foreach ($female_questions as $key => $question) {
                $answer = isset($row[$key]) ? htmlspecialchars($row[$key]) : 'Not answered';
                echo "<tr><td>" . htmlspecialchars($question) . "</td><td>" . $answer . "</td></tr>";
            }
        }
        
        echo '</tbody></table>';
    } else {
        echo '<p>No screening details found for this user.</p>';
    }
} else {
    echo '<p>User ID not provided.</p>';
}
?>
