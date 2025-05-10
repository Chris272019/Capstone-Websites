<?php
include('connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Adding Screening Questions to Initial Screening Table</h2>";

// Array of columns to add
$questionsToAdd = [
    'Q1' => 'Do you feel well and healthy today?',
    'Q2' => 'Taken alcohol in the last 12 hours?',
    'Q3' => 'Taken aspirin in the last 3 days?',
    'Q4' => 'Medications or vaccines in the last 8 weeks?',
    'Q5' => 'Donated blood in the past 3 months?',
    'Q6' => 'Visited Zika-infected areas in the past 6 months?',
    'Q7' => 'Had sexual contact with a Zika-infected person?',
    'Q8' => 'Received blood or had surgery in the last 12 months?',
    'Q9' => 'Tattoo, piercing, or contact with blood in the last 12 months?',
    'Q10' => 'High-risk sexual contact in the last 12 months?'
];

// Check if columns already exist
$result = $conn->query("DESCRIBE initial_screening");
if (!$result) {
    die("Error checking table structure: " . $conn->error);
}

$existingColumns = [];
while ($row = $result->fetch_assoc()) {
    $existingColumns[] = $row['Field'];
}

// Add each column if it doesn't exist
foreach ($questionsToAdd as $columnName => $questionText) {
    if (!in_array($columnName, $existingColumns)) {
        $sql = "ALTER TABLE initial_screening ADD COLUMN $columnName VARCHAR(10) DEFAULT NULL";
        
        if ($conn->query($sql)) {
            echo "<p>Added column $columnName successfully</p>";
        } else {
            echo "<p>Error adding column $columnName: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Column $columnName already exists</p>";
    }
}

// Create a reference table for questions
$createQuestionTable = "CREATE TABLE IF NOT EXISTS initial_screening_questions (
    id VARCHAR(10) PRIMARY KEY,
    question_text TEXT NOT NULL
)";

if ($conn->query($createQuestionTable)) {
    echo "<p>Initial screening questions table created or already exists</p>";
    
    // Insert or update question texts
    foreach ($questionsToAdd as $id => $text) {
        $stmt = $conn->prepare("INSERT INTO initial_screening_questions (id, question_text) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE question_text = ?");
        $stmt->bind_param("sss", $id, $text, $text);
        
        if ($stmt->execute()) {
            echo "<p>Added/updated question: $id</p>";
        } else {
            echo "<p>Error adding question $id: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    }
} else {
    echo "<p>Error creating questions table: " . $conn->error . "</p>";
}

echo "<h2>Operation Complete</h2>";

$conn->close();
?> 