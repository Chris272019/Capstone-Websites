<?php
// Start the session
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

// Include database connection
include('connection.php');

// Initialize variables for form fields
$firstname = $middlename = $surname = $email_address = $mobile_number = $telephone_number = "";
$birthdate = $age = $sex = $civil_status = $nationality = $religion = $education = $occupation = "";
$house_no = $street = $barangay = $town = $province = $zipcode = $office_address = "";
$username = $password = $blood_group = "";
$error_message = $success_message = "";
$verification_status = "Verified";
$walkin = "Yes";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $surname = trim($_POST['surname']);
    $email_address = trim($_POST['email_address']);
    $mobile_number = trim($_POST['mobile_number']);
    $telephone_number = trim($_POST['telephone_number']);
    $birthdate = trim($_POST['birthdate']);
    $age = trim($_POST['age']);
    $sex = trim($_POST['sex']);
    $civil_status = trim($_POST['civil_status']);
    $nationality = trim($_POST['nationality']);
    $religion = trim($_POST['religion']);
    $education = trim($_POST['education']);
    $occupation = trim($_POST['occupation']);
    
    // Address information
    $house_no = trim($_POST['house_no']);
    $street = trim($_POST['street']);
    $barangay = trim($_POST['barangay']);
    $town = trim($_POST['town']);
    $province = trim($_POST['province']);
    $zipcode = trim($_POST['zipcode']);
    $office_address = trim($_POST['office_address']);
    
    // Account information
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $blood_group = trim($_POST['blood_group']);
    
    // Check if this is a new registration request
    $is_new_registration = isset($_POST['register_new']) && $_POST['register_new'] === 'true';

    // Only check for duplicates if this is not a new registration
    if (!$is_new_registration) {
        // Check for existing patient with the specified parameters
        $check_sql = "SELECT * FROM users WHERE 
            firstname = ? AND 
            surname = ? AND 
            birthdate = ? AND 
            sex = ? AND 
            civil_status = ? AND 
            nationality = ? AND 
            religion = ? AND 
            education = ? AND 
            occupation = ? AND 
            blood_group = ?";
        
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error in prepare statement: ' . $conn->error
            ]);
            exit();
        }

        // Create an array of parameters for the basic check
        $params = array(
            $firstname,
            $surname,
            $birthdate,
            $sex,
            $civil_status,
            $nationality,
            $religion,
            $education,
            $occupation,
            $blood_group
        );

        // Create the type string based on the number of parameters
        $types = str_repeat('s', count($params));

        // Bind parameters
        $check_stmt->bind_param($types, ...$params);
        
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Found matching patient(s)
            $matched_patients = array();
            while ($row = $result->fetch_assoc()) {
                // Additional checks for other fields
                $additionalMatch = true;
                
                // Check optional fields if they are provided
                if (!empty($middlename) && $row['middlename'] !== $middlename) {
                    $additionalMatch = false;
                }
                
                if ($additionalMatch) {
                    $matched_patients[] = $row;
                }
            }
            
            if (!empty($matched_patients)) {
                // Return JSON response for AJAX
                echo json_encode([
                    'status' => 'duplicate',
                    'patients' => $matched_patients
                ]);
                exit();
            }
        }
    }

    // Validate required fields
    if (empty($firstname) || empty($surname) || empty($mobile_number) || empty($birthdate) || empty($sex)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fill all required fields.'
        ]);
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $sql = "INSERT INTO users (
        firstname, middlename, surname, email_address, mobile_number, telephone_number,
        birthdate, age, sex, civil_status, nationality, religion, education, occupation,
        house_no, street, barangay, town, province, zipcode, office_address,
        username, password, verification_status, blood_group, walkin
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error preparing statement: ' . $conn->error
        ]);
        exit();
    }

    $walkin = 'Yes';
    $verification_status = 'Verified';

    $stmt->bind_param("ssssssssssssssssssssssssss",
        $firstname, $middlename, $surname, $email_address, $mobile_number, $telephone_number,
        $birthdate, $age, $sex, $civil_status, $nationality, $religion, $education, $occupation,
        $house_no, $street, $barangay, $town, $province, $zipcode, $office_address,
        $username, $hashed_password, $verification_status, $blood_group, $walkin
    );
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
        // Create initial screening entry
        $screening_sql = "INSERT INTO initial_screening (user_id, status) VALUES (?, 'Pending')";
        $screening_stmt = $conn->prepare($screening_sql);
        $screening_stmt->bind_param("i", $user_id);
        
        if ($screening_stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'user_id' => $user_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error creating initial screening: ' . $conn->error
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error registering patient: ' . $conn->error
        ]);
    }
    exit();
}

// Calculate age function
function calculateAge($birthdate) {
    $today = new DateTime();
    $birth = new DateTime($birthdate);
    $age = $birth->diff($today)->y;
    return $age;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Walk-in Patient</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            margin: 30px auto;
        }

        h1 {
            color: #d71c1c; /* Blood red color */
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        .section-header {
            background-color: #d71c1c;
            color: white;
            padding: 8px 15px;
            margin: 20px 0 15px 0;
            border-radius: 4px;
            font-size: 18px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-group {
            margin-bottom: 15px;
            padding: 0 10px;
            flex: 1 0 200px;
        }

        .form-group.full-width {
            flex: 1 0 100%;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        input[type="number"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .required:after {
            content: " *";
            color: #d71c1c;
        }

        .error-message {
            color: #d71c1c;
            margin-bottom: 15px;
        }

        .success-message {
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .submit-button, .cancel-button {
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button {
            background-color: #d71c1c;
            color: white;
        }

        .submit-button:hover {
            background-color: #b01616;
        }

        .cancel-button {
            background-color: #666;
            color: white;
        }

        .cancel-button:hover {
            background-color: #555;
        }

        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Walk-in Patient</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="section-header">Personal Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname" class="required">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo $firstname; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="middlename">Middle Name</label>
                    <input type="text" id="middlename" name="middlename" value="<?php echo $middlename; ?>">
                </div>
                
                <div class="form-group">
                    <label for="surname" class="required">Last Name</label>
                    <input type="text" id="surname" name="surname" value="<?php echo $surname; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="birthdate" class="required">Date of Birth</label>
                    <input type="date" id="birthdate" name="birthdate" value="<?php echo $birthdate; ?>" required onchange="calculateAgeField()">
                </div>
                
                <div class="form-group">
                    <label for="age" class="required">Age</label>
                    <input type="number" id="age" name="age" value="<?php echo $age; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="sex" class="required">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="" <?php echo empty($sex) ? 'selected' : ''; ?>>Select Sex</option>
                        <option value="Male" <?php echo ($sex == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($sex == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($sex == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="civil_status" class="required">Civil Status</label>
                    <select id="civil_status" name="civil_status" required>
                        <option value="" <?php echo empty($civil_status) ? 'selected' : ''; ?>>Select Civil Status</option>
                        <option value="Single" <?php echo ($civil_status == 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo ($civil_status == 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Divorced" <?php echo ($civil_status == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Widowed" <?php echo ($civil_status == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nationality">Nationality</label>
                    <input type="text" id="nationality" name="nationality" value="<?php echo $nationality; ?>">
                </div>

                <div class="form-group">
                    <label for="religion">Religion</label>
                    <input type="text" id="religion" name="religion" value="<?php echo $religion; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="education">Education</label>
                    <input type="text" id="education" name="education" value="<?php echo $education; ?>">
                </div>

                <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation" value="<?php echo $occupation; ?>">
                </div>

                <div class="form-group">
                    <label for="blood_group">Blood Group</label>
                    <select id="blood_group" name="blood_group">
                        <option value="" <?php echo empty($blood_group) ? 'selected' : ''; ?>>Select Blood Group</option>
                        <option value="A+" <?php echo ($blood_group == 'A+') ? 'selected' : ''; ?>>A positive (A+)</option>
                        <option value="A-" <?php echo ($blood_group == 'A-') ? 'selected' : ''; ?>>A negative (A-)</option>
                        <option value="B+" <?php echo ($blood_group == 'B+') ? 'selected' : ''; ?>>B positive (B+)</option>
                        <option value="B-" <?php echo ($blood_group == 'B-') ? 'selected' : ''; ?>>B negative (B-)</option>
                        <option value="AB+" <?php echo ($blood_group == 'AB+') ? 'selected' : ''; ?>>AB positive (AB+)</option>
                        <option value="AB-" <?php echo ($blood_group == 'AB-') ? 'selected' : ''; ?>>AB negative (AB-)</option>
                        <option value="O+" <?php echo ($blood_group == 'O+') ? 'selected' : ''; ?>>O positive (O+)</option>
                        <option value="O-" <?php echo ($blood_group == 'O-') ? 'selected' : ''; ?>>O negative (O-)</option>
                    </select>
                </div>
            </div>

            <div class="section-header">Contact Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="mobile_number" class="required">Mobile Number</label>
                    <input type="tel" id="mobile_number" name="mobile_number" value="<?php echo $mobile_number; ?>" required>
                </div>

                <div class="form-group">
                    <label for="telephone_number">Telephone Number</label>
                    <input type="tel" id="telephone_number" name="telephone_number" value="<?php echo $telephone_number; ?>">
                </div>

                <div class="form-group">
                    <label for="email_address">Email Address</label>
                    <input type="email" id="email_address" name="email_address" value="<?php echo $email_address; ?>">
                </div>
            </div>

            <div class="section-header">Residential Address</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="house_no">House No.</label>
                    <input type="text" id="house_no" name="house_no" value="<?php echo $house_no; ?>">
                </div>

                <div class="form-group">
                    <label for="street">Street</label>
                    <input type="text" id="street" name="street" value="<?php echo $street; ?>">
                </div>

                <div class="form-group">
                    <label for="barangay">Barangay</label>
                    <input type="text" id="barangay" name="barangay" value="<?php echo $barangay; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="town">Town/City</label>
                    <input type="text" id="town" name="town" value="<?php echo $town; ?>">
                </div>

                <div class="form-group">
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province" value="<?php echo $province; ?>">
                </div>

                <div class="form-group">
                    <label for="zipcode">Zip Code</label>
                    <input type="text" id="zipcode" name="zipcode" value="<?php echo $zipcode; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="office_address">Office Address</label>
                    <textarea id="office_address" name="office_address" rows="3"><?php echo $office_address; ?></textarea>
                </div>
            </div>
            
            <div class="section-header">Account Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                </div>
            </div>
            
            <div class="button-group">
                <button type="button" class="cancel-button" id="cancelButton">Cancel</button>
                <button type="submit" class="submit-button">Add Patient</button>
            </div>
        </form>
    </div>
    
    <script>
        // Calculate age based on birthdate
        function calculateAgeField() {
            const birthdateInput = document.getElementById('birthdate');
            const ageInput = document.getElementById('age');
            
            if (birthdateInput.value) {
                const birthdate = new Date(birthdateInput.value);
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                ageInput.value = age;
            } else {
                ageInput.value = '';
            }
        }
        
        // Run calculation on page load
        document.addEventListener('DOMContentLoaded', calculateAgeField);
        
        // Cancel button - Go back to the previous page
        document.getElementById('cancelButton').addEventListener('click', function() {
            window.location.href = "initial_patient.php";
        });
        
        <?php if (!empty($success_message)): ?>
        // Show success message using SweetAlert
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $success_message; ?>',
            confirmButtonColor: '#d71c1c'
        });
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
        // Show error message using SweetAlert
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo $error_message; ?>',
            confirmButtonColor: '#d71c1c'
        });
        <?php endif; ?>

        // Function to handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Debug log
                
                if (data.status === 'duplicate') {
                    // Show SweetAlert with patient details
                    Swal.fire({
                        title: 'Existing Patient Found',
                        html: `
                            <div style="text-align: left; margin: 20px 0;">
                                <p>We found a patient with matching information:</p>
                                <ul style="list-style: none; padding: 0;">
                                    <li><strong>Name:</strong> ${data.patients[0].firstname} ${data.patients[0].middlename || ''} ${data.patients[0].surname}</li>
                                    <li><strong>Date of Birth:</strong> ${data.patients[0].birthdate}</li>
                                    <li><strong>Sex:</strong> ${data.patients[0].sex}</li>
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
                        if (result.isConfirmed) {
                            // Show full details in a new SweetAlert
                            Swal.fire({
                                title: 'Patient Details',
                                html: `
                                    <div style="text-align: left; margin: 20px 0;">
                                        <p><strong>Name:</strong> ${data.patients[0].firstname} ${data.patients[0].middlename || ''} ${data.patients[0].surname}</p>
                                        <p><strong>Date of Birth:</strong> ${data.patients[0].birthdate}</p>
                                        <p><strong>Sex:</strong> ${data.patients[0].sex}</p>
                                        <p><strong>Civil Status:</strong> ${data.patients[0].civil_status || 'N/A'}</p>
                                        <p><strong>Nationality:</strong> ${data.patients[0].nationality || 'N/A'}</p>
                                        <p><strong>Religion:</strong> ${data.patients[0].religion || 'N/A'}</p>
                                        <p><strong>Education:</strong> ${data.patients[0].education || 'N/A'}</p>
                                        <p><strong>Occupation:</strong> ${data.patients[0].occupation || 'N/A'}</p>
                                        <p><strong>Blood Group:</strong> ${data.patients[0].blood_group || 'N/A'}</p>
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Use This Account',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#d71c1c',
                                cancelButtonColor: '#666'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Create initial screening entry and redirect
                                    fetch('create_initial_screening.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            user_id: data.patients[0].id
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(responseData => {
                                        if (responseData.success) {
                                            // Show success message and redirect
                                            Swal.fire({
                                                title: 'Success!',
                                                text: 'Redirecting to interview page...',
                                                icon: 'success',
                                                timer: 1500,
                                                showConfirmButton: false
                                            }).then(() => {
                                                window.location.href = `initial_screening_staff_dashboard.php?user_id=${data.patients[0].id}`;
                                            });
                                        } else {
                                            Swal.fire('Error', 'Failed to create initial screening record', 'error');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire('Error', 'An error occurred while processing your request', 'error');
                                    });
                                }
                            });
                        } else if (result.isDenied) {
                            // Continue with registration
                            const form = document.querySelector('form');
                            const formData = new FormData(form);
                            
                            // Add a flag to indicate this is a new registration
                            formData.append('register_new', 'true');
                            
                            fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Registration response:', data); // Debug log
                                if (data.status === 'success') {
                                    // Show success message and redirect
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'Patient registered successfully. Redirecting to interview page...',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.href = `initial_screening_staff_dashboard.php?user_id=${data.user_id}`;
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire('Error', data.message || 'An error occurred', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error', 'An error occurred while processing your request', 'error');
                            });
                        }
                    });
                } else if (data.status === 'success') {
                    // Show success message and redirect
                    Swal.fire({
                        title: 'Success!',
                        text: 'Patient registered successfully. Redirecting to interview page...',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = `initial_screening_staff_dashboard.php?user_id=${data.user_id}`;
                    });
                } else {
                    // Show error message
                    Swal.fire('Error', data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while processing your request', 'error');
            });
        });
    </script>
</body>
</html> 