<?php
// Start the session
@session_start();

// Initialize message variables
$message = '';
$error = '';

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
    <title>Blood Bank Statistics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e74c3c;
        }

        h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 24px;
            color: #34495e;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .chart-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .filter-section h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 18px;
        }

        .filter-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-option {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-option input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        .filter-option label {
            color: #495057;
            font-size: 14px;
        }

        .no-data-message {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }

        .chart-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        canvas {
            max-width: 100%;
            height: 100% !important;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #e74c3c;
        }

        .stat-card h3 {
            margin: 0;
            color: #7f8c8d;
            font-size: 16px;
        }

        .stat-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Blood Bank Statistics Dashboard</h1>
        </div>

        <!-- Display success or error messages -->
        <?php if ($message): ?>
            <div class="message success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Statistics Summary -->
        <div class="stats-summary">
            <div class="stat-card">
                <h3>Total Donations</h3>
                <div class="number"><?php echo $total_donations; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Blood Donated</h3>
                <div class="number"><?php echo $total_blood_donated; ?> units</div>
            </div>
            <div class="stat-card">
                <h3>Successful Donations</h3>
                <div class="number"><?php echo $successful_donations; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Donations</h3>
                <div class="number"><?php echo $pending_donations; ?></div>
            </div>
        </div>

        <div class="charts-container">
            <!-- Donation Status Section -->
            <div class="chart-section">
                <h2>Donation Status Overview</h2>
                <div class="filter-section">
                    <h3>Filter by Status</h3>
                    <div class="filter-options">
                        <div class="filter-option">
                            <input type="checkbox" id="filter-successful" checked>
                            <label for="filter-successful">Successful</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="filter-failed" checked>
                            <label for="filter-failed">Failed</label>
                        </div>
                        <div class="filter-option">
                            <input type="checkbox" id="filter-pending" checked>
                            <label for="filter-pending">Pending</label>
                        </div>
                    </div>
                </div>
                <div class="chart-row">
                    <div class="chart-container">
                        <canvas id="donationPieChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="donationBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Blood Type Distribution Section -->
            <div class="chart-section">
                <h2>Blood Type Distribution</h2>
                <div class="filter-section">
                    <h3>Filter by Blood Type</h3>
                    <div class="filter-options" id="blood-type-filters">
                        <?php foreach (array_keys($blood_types) as $type): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="filter-<?php echo strtolower($type); ?>" checked>
                            <label for="filter-<?php echo strtolower($type); ?>"><?php echo $type; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="chart-row">
                    <div class="chart-container">
                        <canvas id="bloodTypePieChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="bloodTypeBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Blood Group Distribution Section -->
            <div class="chart-section">
                <h2>Blood Group Distribution</h2>
                <div class="filter-section">
                    <h3>Filter by Blood Group</h3>
                    <div class="filter-options" id="blood-group-filters">
                        <?php foreach (array_keys($blood_groups) as $group): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="filter-group-<?php echo strtolower($group); ?>" checked>
                            <label for="filter-group-<?php echo strtolower($group); ?>"><?php echo $group; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="chart-row">
                    <div class="chart-container">
                        <canvas id="bloodGroupPieChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="bloodGroupBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store original data
        const originalData = {
            donation: {
                labels: ['Successful Donations', 'Failed Donations', 'Pending Donations'],
                data: [
                    <?php echo $successful_donations; ?>,
                    <?php echo $failed_donations; ?>,
                    <?php echo $pending_donations; ?>
                ]
            },
            bloodType: {
                labels: <?php echo json_encode(array_keys($blood_types)); ?>,
                data: <?php echo json_encode(array_values($blood_types)); ?>
            },
            bloodGroup: {
                labels: <?php echo json_encode(array_keys($blood_groups)); ?>,
                data: <?php echo json_encode(array_values($blood_groups)); ?>
            }
        };

        // Function to update charts based on filters
        function updateCharts() {
            // Update Donation Status charts
            const donationLabels = [];
            const donationData = [];
            const donationColors = ['#28a745', '#dc3545', '#ffc107'];
            const donationFilteredColors = [];

            ['Successful', 'Failed', 'Pending'].forEach((status, index) => {
                const checkbox = document.getElementById(`filter-${status.toLowerCase()}`);
                if (checkbox.checked) {
                    donationLabels.push(originalData.donation.labels[index]);
                    donationData.push(Math.round(originalData.donation.data[index]));
                    donationFilteredColors.push(donationColors[index]);
                }
            });

            donationPieChart.data.labels = donationLabels;
            donationPieChart.data.datasets[0].data = donationData;
            donationPieChart.data.datasets[0].backgroundColor = donationFilteredColors;
            donationPieChart.update();

            donationBarChart.data.labels = donationLabels;
            donationBarChart.data.datasets[0].data = donationData;
            donationBarChart.data.datasets[0].backgroundColor = donationFilteredColors;
            donationBarChart.update();

            // Update Blood Type charts
            const bloodTypeLabels = [];
            const bloodTypeData = [];
            const bloodTypeColors = ['#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f'];
            const bloodTypeFilteredColors = [];

            originalData.bloodType.labels.forEach((label, index) => {
                const checkbox = document.getElementById(`filter-${label.toLowerCase()}`);
                if (checkbox.checked) {
                    bloodTypeLabels.push(label);
                    bloodTypeData.push(Math.round(originalData.bloodType.data[index]));
                    bloodTypeFilteredColors.push(bloodTypeColors[index % bloodTypeColors.length]);
                }
            });

            bloodTypePieChart.data.labels = bloodTypeLabels;
            bloodTypePieChart.data.datasets[0].data = bloodTypeData;
            bloodTypePieChart.data.datasets[0].backgroundColor = bloodTypeFilteredColors;
            bloodTypePieChart.update();

            bloodTypeBarChart.data.labels = bloodTypeLabels;
            bloodTypeBarChart.data.datasets[0].data = bloodTypeData;
            bloodTypeBarChart.data.datasets[0].backgroundColor = bloodTypeFilteredColors;
            bloodTypeBarChart.update();

            // Update Blood Group charts
            const bloodGroupLabels = [];
            const bloodGroupData = [];
            const bloodGroupColors = ['#17a2b8', '#ff6347', '#6c757d', '#28a745', '#dc3545', '#ffc107', '#f0ad4e', '#e83e8c'];
            const bloodGroupFilteredColors = [];

            originalData.bloodGroup.labels.forEach((label, index) => {
                const checkbox = document.getElementById(`filter-group-${label.toLowerCase()}`);
                if (checkbox.checked) {
                    bloodGroupLabels.push(label);
                    bloodGroupData.push(Math.round(originalData.bloodGroup.data[index]));
                    bloodGroupFilteredColors.push(bloodGroupColors[index % bloodGroupColors.length]);
                }
            });

            bloodGroupPieChart.data.labels = bloodGroupLabels;
            bloodGroupPieChart.data.datasets[0].data = bloodGroupData;
            bloodGroupPieChart.data.datasets[0].backgroundColor = bloodGroupFilteredColors;
            bloodGroupPieChart.update();

            bloodGroupBarChart.data.labels = bloodGroupLabels;
            bloodGroupBarChart.data.datasets[0].data = bloodGroupData;
            bloodGroupBarChart.data.datasets[0].backgroundColor = bloodGroupFilteredColors;
            bloodGroupBarChart.update();
        }

        // Add event listeners to all checkboxes
        document.querySelectorAll('.filter-option input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateCharts);
        });

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
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
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
                                return tooltipItem.label + ': ' + Math.round(tooltipItem.raw) + ' Donations';
                            }
                        }
                    }
                }
            }
        });

        // Bar chart for donation status
        const ctx1Bar = document.getElementById('donationBarChart').getContext('2d');
        const donationBarChart = new Chart(ctx1Bar, {
            type: 'bar',
            data: {
                labels: ['Successful Donations', 'Failed Donations', 'Pending Donations'],
                datasets: [{
                    label: 'Number of Donations',
                    data: [
                        <?php echo $successful_donations; ?>,
                        <?php echo $failed_donations; ?>,
                        <?php echo $pending_donations; ?>
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Donations'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

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
                                return tooltipItem.label + ': ' + Math.round(tooltipItem.raw) + ' Donations';
                            }
                        }
                    }
                }
            }
        });

        // Bar chart for blood group distribution
        const ctx2Bar = document.getElementById('bloodGroupBarChart').getContext('2d');
        const bloodGroupBarChart = new Chart(ctx2Bar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($blood_groups)); ?>,
                datasets: [{
                    label: 'Number of Donations',
                    data: <?php echo json_encode(array_values($blood_groups)); ?>,
                    backgroundColor: ['#17a2b8', '#ff6347', '#6c757d', '#28a745', '#dc3545', '#ffc107', '#f0ad4e', '#e83e8c'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Number of Donations',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

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
                                return tooltipItem.label + ': ' + Math.round(tooltipItem.raw) + ' Donations';
                            }
                        }
                    }
                }
            }
        });

        // Bar chart for blood type distribution
        const ctx3Bar = document.getElementById('bloodTypeBarChart').getContext('2d');
        const bloodTypeBarChart = new Chart(ctx3Bar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($blood_types)); ?>,
                datasets: [{
                    label: 'Number of Donations',
                    data: <?php echo json_encode(array_values($blood_types)); ?>,
                    backgroundColor: ['#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Number of Donations',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Update chart options for better visualization
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + Math.round(tooltipItem.raw) + ' Donations';
                        }
                    }
                }
            }
        };

        const commonBarOptions = {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Number of Donations',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    }
                }
            }
        };

        // Update existing chart configurations with new options
        donationPieChart.options = commonOptions;
        donationBarChart.options = commonBarOptions;
        bloodGroupPieChart.options = commonOptions;
        bloodGroupBarChart.options = commonBarOptions;
        bloodTypePieChart.options = commonOptions;
        bloodTypeBarChart.options = commonBarOptions;
    </script>
</body>
</html>


