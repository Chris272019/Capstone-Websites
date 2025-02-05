<?php
// Include database connection
include('connection.php');

// Set content type to JSON
header('Content-Type: application/json');

// Get POST data from Flutter app
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$is_female = isset($_POST['is_female']) ? $_POST['is_female'] : 'false';

// General answers (Q1 to Q25)
$answers = [];
for ($i = 1; $i <= 25; $i++) {
    $answers["Q$i"] = isset($_POST["Q$i"]) ? $_POST["Q$i"] : null; // Use null for missing data
}

// Female-specific answers (Q26 to Q30)
$female_answers = [];
if ($is_female === 'true') {
    for ($i = 26; $i <= 30; $i++) {
        $female_answers["Q$i"] = isset($_POST["Q$i"]) ? $_POST["Q$i"] : null;
    }
}

// Check if user_id is valid
if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is missing.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Construct SQL query dynamically
$columns = array_merge(
    ['user_id', 'is_female'],
    array_keys($answers),
    array_keys($female_answers)
);
$values = array_merge(
    [$user_id, $is_female],
    array_values($answers),
    array_values($female_answers)
);

// Prepare placeholders for prepared statement
$placeholders = array_fill(0, count($values), '?');

// Build query
$query = sprintf(
    "INSERT INTO screening_answers (%s) VALUES (%s)",
    implode(', ', $columns),
    implode(', ', $placeholders)
);

// Prepare the statement
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare query: ' . $conn->error], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Bind parameters dynamically
$types = str_repeat('s', count($values)); // Assume all parameters are strings
$stmt->bind_param($types, ...$values);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Data successfully saved.'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to insert data: ' . $stmt->error], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
