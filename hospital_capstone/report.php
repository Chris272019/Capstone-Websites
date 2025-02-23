<?php
include 'connection.php'; // Include your DB connection file

$sql = "SELECT blood_type, plasma_type, collection_date, expiration_date, volume_ml, status, created_at, updated_at FROM blood_collection_inventory";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Inventory Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 0px;
        }
        
        .analytics-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #d9534f;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        .report-table th {
            background-color: #d9534f;
            color: white;
            font-weight: bold;
        }

        .report-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .status-indicator {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }

        .status-available {
            background-color: #5cb85c;
        }

        .status-low {
            background-color: #f0ad4e;
        }

        .status-critical {
            background-color: #d9534f;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .summary-card {
            flex: 1;
            background: #fff;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin: 5px;
        }

        .summary-card i {
            font-size: 30px;
            margin-bottom: 5px;
            color: #d9534f;
        }

        @media screen and (max-width: 768px) {
            .summary-cards {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="analytics-container">
    <h2><i class="fas fa-tint"></i> Blood Bank Inventory Analytics</h2>

    <!-- Inventory Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th>Blood Type</th>
                <th>Plasma Type</th>
                <th>Collection Date</th>
                <th>Expiration Date</th>
                <th>Volume (ml)</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalUnits = 0;
            $lowStock = 0;
            $expiringSoon = 0;
            $today = strtotime(date('Y-m-d'));

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $totalUnits += $row['volume_ml'];

                    $expiration_date = strtotime($row['expiration_date']);
                    $diff_days = ($expiration_date - $today) / 86400;

                    if ($diff_days > 0 && $diff_days <= 7) {
                        $expiringSoon++;
                    }

                    if ($row['volume_ml'] < 500) {
                        $lowStock++;
                    }

                    $statusClass = "status-available";
                    if ($row['volume_ml'] < 500) {
                        $statusClass = "status-low";
                    }
                    if ($diff_days > 0 && $diff_days <= 7) {
                        $statusClass = "status-critical";
                    }

                    echo "<tr>
                            <td>{$row['blood_type']}</td>
                            <td>{$row['plasma_type']}</td>
                            <td>{$row['collection_date']}</td>
                            <td>{$row['expiration_date']}</td>
                            <td>{$row['volume_ml']}</td>
                            <td><span class='status-indicator $statusClass'>{$row['status']}</span></td>
                            <td>{$row['created_at']}</td>
                            <td>{$row['updated_at']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <!-- Pie Chart -->
    <canvas id="bloodChart" width="400" height="200"></canvas>
</div>

<script>
    const ctx = document.getElementById('bloodChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Total Units', 'Low Stock', 'Expiring Soon'],
            datasets: [{
                data: [<?php echo $totalUnits; ?>, <?php echo $lowStock; ?>, <?php echo $expiringSoon; ?>],
                backgroundColor: ['#5cb85c', '#f0ad4e', '#d9534f']
            }]
        }
    });
</script>

</body>
</html>

<?php $conn->close(); ?>
