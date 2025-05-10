<?php
include 'connection.php'; // Include your DB connection file

// Function to get status dropdown
function getStatusDropdown($conn = null, $name = 'status', $id = 'statusFilter', $selected = '', $class = 'filter-select') {
    // If connection is provided, fetch statuses from database
    if ($conn) {
        $sql = "SELECT DISTINCT status FROM blood_collection_inventory";
        $result = $conn->query($sql);
        
        $statuses = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $statuses[] = $row['status'];
            }
        }
        
        // Add derived statuses that might not be in the database
        $statuses[] = "Low Stock";
        $statuses[] = "Expiring Soon";
        $statuses[] = "Reserved";
        
        // Remove duplicates and sort
        $statuses = array_unique($statuses);
        sort($statuses);
    } else {
        // Default statuses based on the code provided
        $statuses = ["Available", "Low Stock", "Expiring Soon", "Reserved"];
    }
    
    // Build the dropdown HTML
    $html = '<select name="' . $name . '" id="' . $id . '" class="' . $class . '">';
    $html .= '<option value="">All Statuses</option>';
    
    foreach ($statuses as $status) {
        $isSelected = ($selected == $status) ? 'selected' : '';
        $html .= '<option value="' . $status . '" ' . $isSelected . '>' . $status . '</option>';
    }
    
    $html .= '</select>';
    
    return $html;
}

$sql = "SELECT blood_type, blood_group, collection_date, expiration_date, volume_ml, status, created_at, updated_at FROM blood_collection_inventory";
$result = $conn->query($sql);

$bloodData = [];
$bloodTypes = [];
$bloodGroups = [];
$volumes = [];
$expirationDates = [];
$statuses = [];

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

        // Grouping by blood type and blood group
        $label = "{$row['blood_type']} - {$row['blood_group']}";
        $bloodData[$label] = isset($bloodData[$label]) ? $bloodData[$label] + $row['volume_ml'] : $row['volume_ml'];
        $expirationDates[$label] = $row['expiration_date'];

        // Collect unique values for filters
        if (!in_array($row['blood_type'], $bloodTypes)) {
            $bloodTypes[] = $row['blood_type'];
        }
        if (!in_array($row['blood_group'], $bloodGroups)) {
            $bloodGroups[] = $row['blood_group'];
        }
        if (!in_array($row['status'], $statuses)) {
            $statuses[] = $row['status'];
        }

        $tableData[] = $row;
    }
} else {
    $tableData = [];
}

// Sort the filter arrays
sort($bloodTypes);
sort($bloodGroups);
sort($statuses);

// Extracting data for chart labels
$labels = json_encode(array_keys($bloodData));
$volumes = json_encode(array_values($bloodData));
$expirationLabels = json_encode(array_values($expirationDates));

// Convert filter arrays to JSON for JavaScript
$bloodTypesJson = json_encode($bloodTypes);
$bloodGroupsJson = json_encode($bloodGroups);
$statusesJson = json_encode($statuses);

// Prepare data for pie chart - group by blood type only
$bloodTypeData = [];
foreach ($tableData as $row) {
    $bloodType = $row['blood_type'];
    $bloodTypeData[$bloodType] = isset($bloodTypeData[$bloodType]) ? $bloodTypeData[$bloodType] + $row['volume_ml'] : $row['volume_ml'];
}
$pieLabels = json_encode(array_keys($bloodTypeData));
$pieValues = json_encode(array_values($bloodTypeData));
?>

<!-- Report Content Begin -->
<div class="analytics-container">
    <div class="header">
        <h2><i class="fas fa-chart-line"></i> Blood Bank Inventory Analytics</h2>
        <div class="action-buttons">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <button class="btn-export" onclick="exportTableToCSV('blood_inventory.csv')">
                <i class="fas fa-file-export"></i> Export to CSV
            </button>
        </div>
    </div>

    <div class="dashboard-summary">
        <div class="summary-card total">
            <div class="summary-icon">
                <i class="fas fa-tint"></i>
            </div>
            <div class="summary-details">
                <h3>Total Inventory</h3>
                <p class="summary-value"><?php echo $totalUnits; ?> ml</p>
                <p class="summary-subtitle"><?php echo count($tableData); ?> units</p>
            </div>
        </div>
        <div class="summary-card expiring">
            <div class="summary-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="summary-details">
                <h3>Expiring Soon</h3>
                <p class="summary-value"><?php echo $expiringSoon; ?> units</p>
                <p class="summary-subtitle">Within 7 days</p>
            </div>
        </div>
        <div class="summary-card low">
            <div class="summary-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="summary-details">
                <h3>Low Stock</h3>
                <p class="summary-value"><?php echo $lowStock; ?> units</p>
                <p class="summary-subtitle">Below 500ml</p>
            </div>
        </div>
        <div class="summary-card types">
            <div class="summary-icon">
                <i class="fas fa-vials"></i>
            </div>
            <div class="summary-details">
                <h3>Blood Types</h3>
                <p class="summary-value"><?php echo count($bloodTypeData); ?> types</p>
                <p class="summary-subtitle">Available in inventory</p>
            </div>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Blood Inventory by Type and Group</h3>
            </div>
            <div class="chart-body">
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-header">
                <h3>Blood Type Distribution</h3>
            </div>
            <div class="chart-body">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="data-section">
        <div class="data-header">
            <h3>Inventory Details</h3>
            <div class="filter-container">
                <div class="filter-group">
                    <label for="bloodTypeFilter">Blood Type:</label>
                    <select id="bloodTypeFilter" class="filter-select">
                        <option value="">All Types</option>
                        <?php foreach($bloodTypes as $type): ?>
                            <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="bloodGroupFilter">Blood Group:</label>
                    <select id="bloodGroupFilter" class="filter-select">
                        <option value="">All Groups</option>
                        <?php foreach($bloodGroups as $group): ?>
                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="statusFilter">Status:</label>
                    <?php echo getStatusDropdown($conn); ?>
                </div>
                <button id="clearFilters" class="filter-button">Clear Filters</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="inventoryTable" class="data-table">
                <thead>
                    <tr>
                        <th>Blood Type</th>
                        <th>Blood Group</th>
                        <th>Collection Date</th>
                        <th>Expiration Date</th>
                        <th>Volume (ml)</th>
                        <th>Status</th>
                        <th>Added On</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tableData as $row): ?>
                        <tr>
                            <td><?php echo $row['blood_type']; ?></td>
                            <td><?php echo $row['blood_group']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['collection_date'])); ?></td>
                            <td>
                                <?php 
                                    $expDate = strtotime($row['expiration_date']);
                                    $daysUntilExp = ($expDate - $today) / 86400;
                                    $class = $daysUntilExp <= 7 && $daysUntilExp > 0 ? 'expiring-soon' : '';
                                    echo '<span class="' . $class . '">' . date('Y-m-d', $expDate) . '</span>';
                                ?>
                            </td>
                            <td><?php echo $row['volume_ml']; ?> ml</td>
                            <td>
                                <?php 
                                    $statusClass = '';
                                    switch($row['status']) {
                                        case 'Available':
                                            $statusClass = 'status-available';
                                            break;
                                        case 'Reserved':
                                            $statusClass = 'status-reserved';
                                            break;
                                        case 'Low Stock':
                                            $statusClass = 'status-low';
                                            break;
                                        case 'Expiring Soon':
                                            $statusClass = 'status-expiring';
                                            break;
                                        default:
                                            $statusClass = '';
                                    }
                                    echo '<span class="status-badge ' . $statusClass . '">' . $row['status'] . '</span>';
                                ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['updated_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Custom styles for the report */
        .analytics-container {
        max-width: 100%;
            margin: 0 auto;
            background: white;
            padding: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
        }

        .header h2 {
        color: #e63946;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header h2 i {
        background-color: #e63946;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

    .action-buttons {
            display: flex;
            gap: 10px;
        }

    .btn-print, .btn-export {
            padding: 8px 15px;
        border: none;
        border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        }

    .btn-print {
        background-color: #457b9d;
        color: white;
    }

    .btn-export {
        background-color: #1d3557;
        color: white;
        }

    .btn-print:hover, .btn-export:hover {
        opacity: 0.9;
    }

    .dashboard-summary {
            display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

    .summary-card {
            background: white;
            padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .summary-card.total {
        border-left: 4px solid #457b9d;
        }

    .summary-card.expiring {
        border-left: 4px solid #e9c46a;
        }

    .summary-card.low {
        border-left: 4px solid #e76f51;
        }

    .summary-card.types {
        border-left: 4px solid #2a9d8f;
        }

    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        }
        
    .summary-card.total .summary-icon {
        background-color: rgba(69, 123, 157, 0.1);
        color: #457b9d;
        }
        
    .summary-card.expiring .summary-icon {
        background-color: rgba(233, 196, 106, 0.1);
        color: #e9c46a;
        }

    .summary-card.low .summary-icon {
        background-color: rgba(231, 111, 81, 0.1);
        color: #e76f51;
    }

    .summary-card.types .summary-icon {
        background-color: rgba(42, 157, 143, 0.1);
        color: #2a9d8f;
        }

    .summary-details h3 {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .summary-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #212529;
        margin-bottom: 5px;
        }

    .summary-subtitle {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        }

    .chart-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        }

    .chart-header {
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        }

    .chart-header h3 {
        font-size: 1rem;
        color: #343a40;
        margin: 0;
        }

    .chart-body {
        padding: 20px;
        height: 300px;
        }

    .data-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 30px;
        }

    .data-header {
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
            display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
            align-items: center;
        gap: 15px;
    }

    .data-header h3 {
        font-size: 1rem;
        color: #343a40;
        margin: 0;
        }

    .filter-container {
        display: flex;
        flex-wrap: wrap;
            gap: 15px;
        align-items: center;
        }

        .filter-group {
            display: flex;
        align-items: center;
            gap: 5px;
        }

        .filter-select {
            padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 0.875rem;
        }

    .filter-button {
        padding: 8px 12px;
        background-color: #6c757d;
        color: white;
            border: none;
        border-radius: 4px;
            cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        }

    .filter-button:hover {
        background-color: #5a6268;
        }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        }

    .data-table th,
    .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
        }

    .data-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #343a40;
        }

    .data-table tbody tr:hover {
        background-color: #f8f9fa;
            }
            
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
            }
            
    .status-available {
        background-color: rgba(42, 157, 143, 0.1);
        color: #2a9d8f;
            }
            
    .status-reserved {
        background-color: rgba(69, 123, 157, 0.1);
        color: #457b9d;
    }

    .status-low {
        background-color: rgba(231, 111, 81, 0.1);
        color: #e76f51;
            }
            
    .status-expiring {
        background-color: rgba(233, 196, 106, 0.1);
        color: #e9c46a;
    }

    .expiring-soon {
        color: #e76f51;
        font-weight: 500;
        }

    @media screen and (max-width: 768px) {
        .dashboard-summary {
            grid-template-columns: 1fr;
        }
        
        .charts-container {
                grid-template-columns: 1fr;
            }
            
        .data-header {
                flex-direction: column;
                align-items: flex-start;
        }
            }
            
    @media print {
        body {
            padding: 0;
            background-color: white;
            }
        
        .analytics-container {
            box-shadow: none;
            padding: 0;
        }
        
        .action-buttons {
            display: none;
        }
        
        .filter-container {
            display: none;
        }
    }
</style>

<script>
    // Make chart data available globally
    window.reportChartData = {
        labels: <?php echo $labels; ?>,
        volumes: <?php echo $volumes; ?>,
        pieLabels: <?php echo $pieLabels; ?>,
        pieValues: <?php echo $pieValues; ?>
    };
    
    // Initialize charts once DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeReportCharts();
    });
    
    // Function to initialize charts - can be called directly
    function initializeReportCharts() {
        console.log('Initializing report charts');
                        
        // Chart initialization code
        const inventoryCtx = document.getElementById('inventoryChart');
        const distributionCtx = document.getElementById('distributionChart');
        
        if (!inventoryCtx || !distributionCtx) {
            console.error('Chart canvas elements not found');
            return;
                }
        
        // Bar chart for inventory by type and group
        new Chart(inventoryCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: window.reportChartData.labels,
                datasets: [{
                    label: 'Volume (ml)',
                    data: window.reportChartData.volumes,
                    backgroundColor: [
                        'rgba(69, 123, 157, 0.7)',
                        'rgba(42, 157, 143, 0.7)',
                        'rgba(233, 196, 106, 0.7)',
                        'rgba(231, 111, 81, 0.7)',
                        'rgba(230, 57, 70, 0.7)'
                    ],
                    borderColor: [
                        'rgba(69, 123, 157, 1)',
                        'rgba(42, 157, 143, 1)',
                        'rgba(233, 196, 106, 1)',
                        'rgba(231, 111, 81, 1)',
                        'rgba(230, 57, 70, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Volume (ml)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Blood Type - Group'
                        }
                    }
                }
            }
        });
        
        // Pie chart for blood type distribution
        new Chart(distributionCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: window.reportChartData.pieLabels,
                datasets: [{
                    data: window.reportChartData.pieValues,
                    backgroundColor: [
                        'rgba(69, 123, 157, 0.7)',
                        'rgba(42, 157, 143, 0.7)',
                        'rgba(233, 196, 106, 0.7)',
                        'rgba(231, 111, 81, 0.7)',
                        'rgba(230, 57, 70, 0.7)',
                        'rgba(102, 36, 131, 0.7)',
                        'rgba(21, 101, 192, 0.7)',
                        'rgba(0, 172, 193, 0.7)'
                    ],
                    borderColor: [
                        'rgba(69, 123, 157, 1)',
                        'rgba(42, 157, 143, 1)',
                        'rgba(233, 196, 106, 1)',
                        'rgba(231, 111, 81, 1)',
                        'rgba(230, 57, 70, 1)',
                        'rgba(102, 36, 131, 1)',
                        'rgba(21, 101, 192, 1)',
                        'rgba(0, 172, 193, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' ml (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        console.log('Charts initialized successfully');
    }
    
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializeReportFilters();
    });
    
    function initializeReportFilters() {
        console.log('Initializing report filters');
        const tableRows = document.querySelectorAll('#inventoryTable tbody tr');
        const bloodTypeFilter = document.getElementById('bloodTypeFilter');
        const bloodGroupFilter = document.getElementById('bloodGroupFilter');
        const statusFilter = document.getElementById('statusFilter');
        const clearFiltersBtn = document.getElementById('clearFilters');
        
        if (!bloodTypeFilter || !bloodGroupFilter || !statusFilter || !clearFiltersBtn) {
            console.error('Filter elements not found');
            return;
        }
        
    function applyFilters() {
            const bloodTypeValue = bloodTypeFilter.value;
            const bloodGroupValue = bloodGroupFilter.value;
            const statusValue = statusFilter.value;
            
            tableRows.forEach(row => {
                const rowBloodType = row.cells[0].textContent;
                const rowBloodGroup = row.cells[1].textContent;
                const rowStatus = row.cells[5].textContent.trim();
                
                const matchBloodType = !bloodTypeValue || rowBloodType === bloodTypeValue;
                const matchBloodGroup = !bloodGroupValue || rowBloodGroup === bloodGroupValue;
                const matchStatus = !statusValue || rowStatus === statusValue;
                
                if (matchBloodType && matchBloodGroup && matchStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
                }
                
        bloodTypeFilter.addEventListener('change', applyFilters);
        bloodGroupFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        
        clearFiltersBtn.addEventListener('click', function() {
            bloodTypeFilter.value = '';
            bloodGroupFilter.value = '';
            statusFilter.value = '';
            applyFilters();
        });
        
        // Export to CSV functionality
        window.exportTableToCSV = function(filename) {
            const rows = Array.from(document.querySelectorAll('#inventoryTable tr'));
            const csv = rows.map(row => {
                return Array.from(row.querySelectorAll('th, td'))
                    .map(cell => '"' + cell.textContent.trim().replace(/"/g, '""') + '"')
                    .join(',');
            }).join('\n');
            
            const csvFile = new Blob([csv], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        };
        
        console.log('Report filters initialized successfully');
    }
</script>
<!-- Report Content End -->

