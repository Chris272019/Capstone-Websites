<?php
// Start the session
session_start();

// Include your database connection file
include('connection.php');

// Check if the user is logged in
if (!isset($_SESSION['staff_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Query to get statistics from the blood_donation_history table
$stats_query = "
    SELECT 
        COUNT(*) AS total_donations,
        SUM(donation_quantity) AS total_blood_donated,
        SUM(CASE WHEN status = 'Successful' THEN 1 ELSE 0 END) AS successful_donations,
        SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) AS failed_donations,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_donations
    FROM blood_donation_history
";

// Prepare and execute the query
if ($stmt = $conn->prepare($stats_query)) {
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows are returned
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_donations = $row['total_donations'];
        $total_blood_donated = $row['total_blood_donated'];
        $successful_donations = $row['successful_donations'];
        $failed_donations = $row['failed_donations'];
        $pending_donations = $row['pending_donations'];
    } else {
        echo "<p>No donation records found.</p>";
        exit();
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<p>Error preparing the query: " . $conn->error . "</p>";
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation Statistics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        .statistics {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .stat {
            width: 200px;
            padding: 20px;
            text-align: center;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .stat h3 {
            margin: 0;
            color: #333;
        }
        .stat p {
            margin: 5px 0;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>Blood Donation Statistics</h1>

    <div class="statistics">
        <div class="stat">
            <h3>Total Donations</h3>
            <p><?php echo number_format($total_donations); ?></p>
        </div>
        <div class="stat">
            <h3>Successful Donations</h3>
            <p><?php echo number_format($successful_donations); ?></p>
        </div>
        <div class="stat">
            <h3>Failed Donations</h3>
            <p><?php echo number_format($failed_donations); ?></p>
        </div>
        <div class="stat">
            <h3>Pending Donations</h3>
            <p><?php echo number_format($pending_donations); ?></p>
        </div>
        <div class="stat">
            <h3>Total Blood Donated (liters)</h3>
            <p><?php echo number_format($total_blood_donated, 2); ?></p>
        </div>
    </div>

</body>
</html>
