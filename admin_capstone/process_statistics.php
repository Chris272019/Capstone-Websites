<?php
// Start the session
session_start();
$message = isset($_SESSION['message']) ? $_SESSION['message'] : "";
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";

// Clear session messages after display
unset($_SESSION['message'], $_SESSION['error']);

// Include database connection
include('connection.php');

// Query to get the donation statistics by status
$stats_query = "
    SELECT 
        COUNT(*) AS total_donations,
        SUM(donation_quantity) AS total_blood_donated,
        SUM(CASE WHEN status = 'Successful' THEN 1 ELSE 0 END) AS successful_donations,
        SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) AS failed_donations,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_donations
    FROM blood_donation_history
";

// Prepare and execute the query for donation statistics
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
        $total_donations = $total_blood_donated = $successful_donations = $failed_donations = $pending_donations = 0;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<p>Error preparing the query: " . $conn->error . "</p>";
    exit();
}

// Query to get the statistics by blood group
$blood_group_query = "
    SELECT 
        blood_group,
        COUNT(*) AS count
    FROM blood_donation_history
    GROUP BY blood_group
";

// Prepare and execute the query for blood group statistics
$blood_groups = [];
if ($stmt = $conn->prepare($blood_group_query)) {
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all the blood groups and their counts
    while ($row = $result->fetch_assoc()) {
        $blood_groups[$row['blood_group']] = $row['count'];
    }

    // Close the statement
    $stmt->close();
} else {
    echo "<p>Error preparing the query: " . $conn->error . "</p>";
    exit();
}

// Query to get the statistics by blood type
$blood_type_query = "
    SELECT 
        blood_type,
        COUNT(*) AS count
    FROM blood_donation_history
    GROUP BY blood_type
";

// Prepare and execute the query for blood type statistics
$blood_types = [];
if ($stmt = $conn->prepare($blood_type_query)) {
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all the blood types and their counts
    while ($row = $result->fetch_assoc()) {
        $blood_types[$row['blood_type']] = $row['count'];
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
    <title>Blood Donation Statistics - Pie Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
    font-family: Arial, sans-serif;
}

.container {
    width: 35%;
    margin: 0 auto;
    padding-top: 20px;
    text-align: center;
}

canvas {
    max-width: 100%;
}

/* Style for h1 */
h1 {
    font-size: 25px;
    color: #333;
    margin-bottom: 20px;
}

/* Style for h2 */
h2 {
    font-size: 20px;
    color: #555;
    margin-bottom: 15px;
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Blood Donation Statistics</h1>

        <!-- Display success or error messages -->
        <?php if ($message): ?>
            <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Pie chart for donation status -->
        <h2>Donation Status</h2>
        <canvas id="donationPieChart" width="400" height="400"></canvas>
        <script>
            const ctx1 = document.getElementById('donationPieChart').getContext('2d');
            const donationPieChart = new Chart(ctx1, {
                type: 'pie',
                data: {
                    labels: ['Successful Donations', 'Failed Donations', 'Pending Donations'],
                    datasets: [{
                        data: [
                            <?php echo $successful_donations; ?>,
                            <?php echo $failed_donations; ?>,
                            <?php echo $pending_donations; ?>
                        ],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107'], // Colors for Successful, Failed, Pending
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw + ' Donations';
                                }
                            }
                        }
                    }
                }
            });
        </script>

        <!-- Pie chart for blood group distribution -->
        <h2>Blood Type Distribution</h2>
        <canvas id="bloodGroupPieChart" width="400" height="400"></canvas>
        <script>
            const ctx2 = document.getElementById('bloodGroupPieChart').getContext('2d');
            const bloodGroupPieChart = new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($blood_groups)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($blood_groups)); ?>,
                        backgroundColor: ['#17a2b8', '#ff6347', '#6c757d', '#28a745', '#dc3545', '#ffc107', '#f0ad4e', '#e83e8c'],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw + ' Donations';
                                }
                            }
                        }
                    }
                }
            });
        </script>

        <!-- Pie chart for blood type distribution -->
        <h2>Blood Group Distribution</h2>
        <canvas id="bloodTypePieChart" width="400" height="400"></canvas>
        <script>
            const ctx3 = document.getElementById('bloodTypePieChart').getContext('2d');
            const bloodTypePieChart = new Chart(ctx3, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($blood_types)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($blood_types)); ?>,
                        backgroundColor: ['#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f'],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw + ' Donations';
                                }
                            }
                        }
                    }
                }
            });
        </script>
    </div>
</body>
</html>
