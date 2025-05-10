<?php
// Start the session
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

// Check if we have matched patients
if (!isset($_SESSION['matched_patients']) || empty($_SESSION['matched_patients'])) {
    header("Location: add_walkin_patient.php");
    exit();
}

// Include database connection
include('connection.php');

$matched_patients = $_SESSION['matched_patients'];
$form_data = $_SESSION['form_data'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['use_existing'])) {
        // Use existing patient account
        $user_id = $_POST['user_id'];
        
        // Create a default entry in initial_screening table with pending status
        $sql = "INSERT INTO initial_screening (user_id, status) VALUES (?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Clear session data
            unset($_SESSION['matched_patients']);
            unset($_SESSION['form_data']);
            
            // Redirect to initial screening
            header("Location: initial_screening_staff_dashboard.php?user_id=" . $user_id);
            exit();
        } else {
            $error_message = "Error creating initial screening record: " . $conn->error;
        }
    } elseif (isset($_POST['register_new'])) {
        // Clear session data and redirect back to registration
        unset($_SESSION['matched_patients']);
        header("Location: add_walkin_patient.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Existing Patient</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            color: #d71c1c;
            text-align: center;
            margin-bottom: 30px;
        }

        .patient-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .patient-info {
            margin-bottom: 15px;
        }

        .patient-info strong {
            color: #333;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .use-existing {
            background-color: #d71c1c;
            color: white;
        }

        .use-existing:hover {
            background-color: #b01616;
        }

        .register-new {
            background-color: #666;
            color: white;
        }

        .register-new:hover {
            background-color: #555;
        }

        .warning-message {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Existing Patient Found</h1>
        
        <div class="warning-message">
            <strong>Note:</strong> We found one or more existing patients that match the information provided. 
            Please review the details below and choose whether to use an existing account or register as a new patient.
        </div>

        <?php foreach ($matched_patients as $patient): ?>
        <div class="patient-card">
            <div class="patient-info">
                <strong>Name:</strong> <?php echo htmlspecialchars($patient['firstname'] . ' ' . $patient['middlename'] . ' ' . $patient['surname']); ?>
            </div>
            <div class="patient-info">
                <strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['birthdate']); ?>
            </div>
            <div class="patient-info">
                <strong>Sex:</strong> <?php echo htmlspecialchars($patient['sex']); ?>
            </div>
            <div class="patient-info">
                <strong>Civil Status:</strong> <?php echo htmlspecialchars($patient['civil_status']); ?>
            </div>
            <div class="patient-info">
                <strong>Nationality:</strong> <?php echo htmlspecialchars($patient['nationality']); ?>
            </div>
            <div class="patient-info">
                <strong>Religion:</strong> <?php echo htmlspecialchars($patient['religion']); ?>
            </div>
            <div class="patient-info">
                <strong>Education:</strong> <?php echo htmlspecialchars($patient['education']); ?>
            </div>
            <div class="patient-info">
                <strong>Occupation:</strong> <?php echo htmlspecialchars($patient['occupation']); ?>
            </div>
            <div class="patient-info">
                <strong>Blood Group:</strong> <?php echo htmlspecialchars($patient['blood_group']); ?>
            </div>
            
            <form method="post" style="margin-top: 15px;">
                <input type="hidden" name="user_id" value="<?php echo $patient['id']; ?>">
                <button type="submit" name="use_existing" class="button use-existing">Use This Account</button>
            </form>
        </div>
        <?php endforeach; ?>

        <div class="button-group">
            <form method="post">
                <button type="submit" name="register_new" class="button register-new">Register as New Patient</button>
            </form>
        </div>
    </div>

    <script>
        // Show initial confirmation modal when page loads
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Existing Patient Found',
                html: `
                    <div style="text-align: left; margin: 20px 0;">
                        <p>We found a patient with matching information:</p>
                        <ul style="list-style: none; padding: 0;">
                            <li><strong>Name:</strong> ${<?php echo json_encode($matched_patients[0]['firstname'] . ' ' . $matched_patients[0]['middlename'] . ' ' . $matched_patients[0]['surname']); ?>}</li>
                            <li><strong>Date of Birth:</strong> ${<?php echo json_encode($matched_patients[0]['birthdate']); ?>}</li>
                            <li><strong>Sex:</strong> ${<?php echo json_encode($matched_patients[0]['sex']); ?>}</li>
                        </ul>
                        <p>Would you like to:</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'View Full Details',
                denyButtonText: 'Register as New',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d71c1c',
                denyButtonColor: '#666',
                cancelButtonColor: '#999'
            }).then((result) => {
                if (result.isDenied) {
                    // Register as new patient
                    document.querySelector('form[name="register_new"]').submit();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // Go back to previous page
                    window.location.href = "add_walkin_patient.php";
                }
                // If confirmed, stay on page to show full details
            });
        });

        // Add confirmation dialog for using existing account
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.querySelector('[name="use_existing"]')) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Confirm Action',
                        text: 'Are you sure you want to use this existing account?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#d71c1c',
                        cancelButtonColor: '#666',
                        confirmButtonText: 'Yes, use this account',
                        cancelButtonText: 'No, cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 