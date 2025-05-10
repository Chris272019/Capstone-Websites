<?php
// Start the session
session_start();

// Include the connection file
include('connection.php');

// Get success/error messages if they exist
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear the session messages after retrieving them
unset($_SESSION['message'], $_SESSION['error']);

// Check if patient_id parameter exists
if (!isset($_GET['patient_id']) || empty($_GET['patient_id'])) {
    $_SESSION['error'] = "Invalid patient ID.";
    header("Location: hospital_dashboard.php");
    exit();
}

$patient_id = (int)$_GET['patient_id'];

// Query to get blood exchange requests for this patient
$sql = "SELECT * FROM admin_blood_request WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

// Get patient info from blood_request table
$patient_sql = "SELECT surname, firstname, middlename FROM blood_request WHERE id = ?";
$patient_stmt = $conn->prepare($patient_sql);
$patient_stmt->bind_param("i", $patient_id);
$patient_stmt->execute();
$patient_result = $patient_stmt->get_result();
$patient_data = $patient_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Exchange Requests - Blood Donation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/hospital.css">
    <style>
        /* Your existing styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .back-btn {
            margin-bottom: 20px;
            display: inline-block;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
        }
        .back-btn:hover {
            background-color: #e9ecef;
        }
        .patient-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #e60000;
        }
        .exchange-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .exchange-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .exchange-header h3 {
            color: #e60000;
            margin: 0;
        }
        .exchange-details p {
            margin: 8px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #f5c6cb;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
            text-decoration: none;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-success {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            color: #fff;
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            color: #fff;
            background-color: #c82333;
            border-color: #bd2130;
        }
        .status-accepted {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .modal-header {
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .modal-header h3 {
            margin: 0;
            color: #e60000;
        }
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #e60000;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea.form-control {
            min-height: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="hospital_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <h1>Blood Exchange Requests</h1>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($patient_data): ?>
        <div class="patient-info">
            <h2>Patient: <?php echo htmlspecialchars($patient_data['surname'] . ', ' . $patient_data['firstname'] . ' ' . $patient_data['middlename']); ?></h2>
            <p>Patient ID: <?php echo $patient_id; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <?php $request_count = 1; // Initialize counter ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="exchange-card">
                    <div class="exchange-header">
                        <h3>Exchange Request #<?php echo $request_count++; ?></h3>
                    </div>
                    <div class="exchange-details">
                        <p><strong>Hospital:</strong> <?php echo isset($row['hospital_name']) ? htmlspecialchars($row['hospital_name']) : 'N/A'; ?></p>
                        <p><strong>Blood Component:</strong> <?php echo isset($row['blood_component']) ? htmlspecialchars($row['blood_component']) : 'N/A'; ?></p>
                        <p><strong>Blood Group:</strong> <?php echo isset($row['blood_group']) ? htmlspecialchars($row['blood_group']) : 'N/A'; ?></p>
                        <p><strong>Amount (ml):</strong> <?php echo isset($row['amount_ml']) ? htmlspecialchars($row['amount_ml']) : 'N/A'; ?></p>
                        <?php if (isset($row['created_at'])): ?>
                            <p><strong>Date Requested:</strong> <?php echo htmlspecialchars($row['created_at']); ?></p>
                        <?php endif; ?>
                        <?php if (isset($row['status']) && strtolower($row['status']) == 'accepted'): ?>
                            <p>
                                <strong>Status:</strong> 
                                <span class="status-badge status-accepted">
                                    Accepted
                                </span>
                            </p>
                        <?php elseif (isset($row['status']) && strtolower($row['status']) == 'rejected'): ?>
                            <p>
                                <strong>Status:</strong> 
                                <span class="status-badge status-rejected">
                                    Rejected
                                </span>
                            </p>
                            <?php if (isset($row['rejection_reason']) && !empty($row['rejection_reason'])): ?>
                                <p><strong>Rejection Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>
                                <strong>Status:</strong> 
                                <span class="status-badge status-pending">
                                    <?php echo isset($row['status']) ? htmlspecialchars($row['status']) : 'Pending'; ?>
                                </span>
                                
                                <form action="accept_blood_exchange.php" method="POST" class="mt-2">
                                    <input type="hidden" name="exchange_id" value="<?php echo isset($row['request_id']) ? $row['request_id'] : ''; ?>">
                                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                                    <button type="submit" class="btn btn-success">Accept Exchange</button>
                                </form>

                                <button type="button" class="btn btn-danger" onclick="showRejectModal('<?php echo isset($row['request_id']) ? $row['request_id'] : ''; ?>', '<?php echo $patient_id; ?>')">Reject Exchange</button>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert">
                <p>No blood exchange requests found for this patient.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeRejectModal()">&times;</span>
                <h3>Reject Blood Exchange Request</h3>
            </div>
            <form id="rejectForm" action="accept_blood_exchange.php" method="POST">
                <input type="hidden" id="modalExchangeId" name="exchange_id" value="">
                <input type="hidden" id="modalPatientId" name="patient_id" value="">
                <input type="hidden" name="action" value="reject">
                
                <div class="form-group">
                    <label for="rejection_reason">Rejection Reason:</label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-danger">Submit Rejection</button>
            </form>
        </div>
    </div>

    <script>
        // Function to show the rejection modal and populate hidden fields
        function showRejectModal(exchangeId, patientId) {
            document.getElementById('modalExchangeId').value = exchangeId;
            document.getElementById('modalPatientId').value = patientId;
            document.getElementById('rejectModal').style.display = 'block';
        }

        // Function to close the rejection modal
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // Close the modal if user clicks outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('rejectModal');
            if (event.target == modal) {
                closeRejectModal();
            }
        }
    </script>
</body>
</html>

<?php
// Close database connections
$stmt->close();
$patient_stmt->close();
$conn->close();
?>
