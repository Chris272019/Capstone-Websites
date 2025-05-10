<?php
// Include database connection
include('connection.php');

// Fetch user details for donation requests
if (isset($_GET['donation_request'])) {
    $sql = "SELECT u.firstname, u.surname, u.middlename, u.email_address, u.id as user_id 
            FROM users u 
            JOIN screening_answers s ON u.id = s.user_id";
    $result = mysqli_query($conn, $sql);    

    $users = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blood Bank System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background: #f5f7fa;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #c10000 0%, #414141 100%);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 1.8em;
            color: #e74c3c;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin: 5px 0;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .nav-links a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-links a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #e74c3c;
        }

        .nav-links i {
            width: 25px;
            margin-right: 10px;
            font-size: 1.2em;
        }

        .nav-links span {
            font-size: 0.95em;
            font-weight: 500;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .admin-info {
            flex: 1;
        }

        .admin-name {
            font-weight: 500;
            font-size: 0.9em;
        }

        .admin-role {
            font-size: 0.8em;
            opacity: 0.8;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgb(255 0 0 / 80%);
            text-decoration: none;
            padding: 10px 20px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            color: #e74c3c;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 1.8em;
            font-weight: 600;
        }

        .welcome-text {
            color: #7f8c8d;
        }

        /* Content Section Styles */
        .content-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h2 span,
            .nav-links span,
            .admin-info,
            .logout-btn span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .nav-links a {
                padding: 15px;
                justify-content: center;
            }

            .nav-links i {
                margin: 0;
                font-size: 1.4em;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <i class="fas fa-hospital"></i>
                <span>Blood Bank</span>
            </h2>
        </div>
        <ul class="nav-links">
            <li>
                <a href="javascript:void(0);" class="nav-link" id="homeLink" data-target="homeContent">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="donationRequestLink" data-target="donationRequestContent">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Donation Requests</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="userManagementLink" data-target="userManagementContent">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="hospitalManagementLink" data-target="hospitalManagementContent">
                    <i class="fas fa-hospital-alt"></i>
                    <span>Hospital Accounts</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="bloodRequestLink" data-target="bloodRequestContent">
                    <i class="fas fa-tint"></i>
                    <span>Blood Requests</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="inventoryLink" data-target="inventoryContent">
                    <i class="fas fa-box"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="scheduleLink" data-target="scheduleContent">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="staffAccountLink" data-target="staffAccountContent">
                    <i class="fas fa-clipboard"></i>
                    <span>Staff Accounts</span>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" class="nav-link" id="postLink" data-target="postContent">
                    <i class="fas fa-pen"></i>
                    <span>Post</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-info">
                    <div class="admin-name">Admin Name</div>
                    <div class="admin-role">System Administrator</div>
                </div>
            </div>
            <form action="process_logout.php" method="POST">
                <button type="submit" name="logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <div class="welcome-text">Welcome back, Administrator!</div>
        </div>

        <!-- Home Section -->
        <div id="homeContent" class="content-section">
            <?php include('process_statistics.php'); ?>
        </div>

        <!-- Donation Requests Section -->
        <div id="donationRequestContent" class="content-section" style="display: none;">
            <h3>Donation Requests</h3>
            <?php include('process_fetch_screening.php'); ?>
        </div>

        <!-- User Management Section -->
        <div id="userManagementContent" class="content-section" style="display: none;">
            <h3>User Management</h3>
            <?php include('process_fetch_user_info.php'); ?>
        </div>

        <!-- Hospital Management Section -->
        <div id="hospitalManagementContent" class="content-section" style="display: none;">
            <h3>Hospital Accounts</h3>
            <button id="addHospitalBtn" class="btn btn-primary mb-3">
                <i class="fas fa-plus"></i> Add Hospital Info
            </button>
            <?php include('process_fetch_hospitals.php'); ?>
        </div>

        <!-- Blood Requests Section -->
        <div id="bloodRequestContent" class="content-section" style="display: none;">
            <h3>Blood Requests</h3>
            <?php include('process_fetch_blood_requests.php'); ?>
        </div>

        <!-- Inventory Section -->
        <div id="inventoryContent" class="content-section" style="display: none;">
            <h3>Blood Collection Inventory</h3>
            <?php include('process_fetch_inventory.php'); ?>
        </div>

        <!-- Schedule Content Section -->
        <div id="scheduleContent" class="content-section" style="display: none;">
            <h3>Schedule</h3>
            <?php include('process_fetch_schedule.php'); ?>
        </div>

        <!-- Staff Account Section -->
        <div id="staffAccountContent" class="content-section" style="display: none;">
            <h3>Staff Account Management</h3>
            <button class="btn btn-primary" id="addStaffBtn">
                <i class="fas fa-plus"></i> Add Staff
            </button>
            <?php include('process_fetch_staff_accounts.php'); ?>
        </div>

        <!-- Post Section -->
        <div id="postContent" class="content-section" style="display: none;">
            <h3>Post</h3>
            <button class="btn btn-primary" id="addPostBtn">
                <i class="fas fa-plus"></i> Add Post
            </button>
            <?php include('process_fetch_post.php'); ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Function to show the selected content and hide others
            function showContent(target) {
                $('.content-section').hide(); // Hide all sections
                $('#' + target).show(); // Show the selected section
            
                // Remove active class from all links
                $('.nav-link').removeClass('active');
            
                // Add active class to the selected link
                $('.nav-link[data-target="' + target + '"]').addClass('active');
            
                // Store the active section in localStorage
                localStorage.setItem('activeSection', target);
            }

            // Event listeners for sidebar links
            $('.nav-link').click(function() {
                const target = $(this).data('target');
                showContent(target);
            });

            // On page load, check localStorage for the active section
            const activeSection = localStorage.getItem('activeSection');
            if (activeSection) {
                showContent(activeSection); // Show the stored section if it exists
            } else {
                // Default to home section if no section is stored
                showContent('homeContent');
                // Highlight the home link by default
                $('#homeLink').addClass('active');
            }

            // Open the Add Staff modal
            $('#addStaffBtn').click(function() {
                $('#addStaffModal').modal('show');
            });

            // Open the Add Hospital Info modal
            $('#addHospitalBtn').click(function() {
                $('#addHospitalModal').modal('show');
            });

            // Handle add post button click
            $("#addPostBtn").click(function() {
                Swal.fire({
                    title: "Add New Post",
                    html: `
                        <form id="postForm">
                            <div class="form-group mb-3">
                                <label for="postTitle" class="form-label">Post Title</label>
                                <input type="text" class="form-control" id="postTitle" name="postTitle" required placeholder="Enter post title">
                            </div>
                            <div class="form-group mb-3">
                                <label for="postPhoto" class="form-label">Upload Photo</label>
                                <input type="file" class="form-control" id="postPhoto" name="postPhoto" accept="image/*" required>
                                <div class="mt-2">
                                    <img id="postPhotoPreview" src="#" alt="Photo Preview" class="img-fluid" style="display: none; max-height: 200px;">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="postDescription" class="form-label">Post Description</label>
                                <textarea class="form-control" id="postDescription" name="postDescription" rows="3" required placeholder="Enter post description"></textarea>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: "Add Post",
                    cancelButtonText: "Cancel",
                    focusConfirm: false,
                    preConfirm: () => {
                        const form = document.getElementById("postForm");
                        const formData = new FormData(form);
                        
                        // Validate form
                        if (!formData.get('postTitle') || !formData.get('postPhoto') || !formData.get('postDescription')) {
                            Swal.showValidationMessage("Please fill in all required fields");
                            return false;
                        }
                        
                        return formData;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = result.value;
                        
                        $.ajax({
                            url: "process_post.php",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                try {
                                    if (response.includes("successfully")) {
                                        Swal.fire({
                                            title: "Success!",
                                            text: "Post has been added successfully.",
                                            icon: "success",
                                            confirmButtonText: "OK"
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: "Failed to add post.",
                                            icon: "error",
                                            confirmButtonText: "OK"
                                        });
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Invalid server response.",
                                        icon: "error",
                                        confirmButtonText: "OK"
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX error:", error);
                                Swal.fire({
                                    title: "Error!",
                                    text: "An error occurred while adding the post.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });

                // Show preview of uploaded photo
                $(document).on('change', '#postPhoto', function(e) {
                    const file = e.target.files[0];
                    const preview = $('#postPhotoPreview');
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            preview.attr('src', event.target.result).show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.hide();
                    }
                });
            });

            // Handle hospital form submission
            $('form[action="process_add_hospital_account.php"]').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    type: 'POST',
                    url: 'process_add_hospital_account.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#addHospitalModal').modal('hide');
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonColor: '#d33',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Handle add staff button click
            $("#addStaffBtn").click(function() {
                Swal.fire({
                    title: "Add New Staff",
                    html: `
                        <form id="addStaffForm">
                            <div class="form-group mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" required placeholder="Enter first name">
                            </div>
                            <div class="form-group mb-3">
                                <label for="surname" class="form-label">Surname</label>
                                <input type="text" class="form-control" id="surname" name="surname" required placeholder="Enter surname">
                            </div>
                            <div class="form-group mb-3">
                                <label for="middlename" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Enter middle name (optional)">
                            </div>
                            <div class="form-group mb-3">
                                <label for="email_address" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email_address" name="email_address" required placeholder="Enter email address">
                            </div>
                            <div class="form-group mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="Interviewer">Interviewer</option>
                                    <option value="Physician">Physician</option>
                                    <option value="Phlebotomist">Phlebotomist</option>
                                </select>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: "Add Staff",
                    cancelButtonText: "Cancel",
                    focusConfirm: false,
                    preConfirm: () => {
                        const form = document.getElementById("addStaffForm");
                        const formData = new FormData(form);
                        const data = Object.fromEntries(formData.entries());
                        
                        // Validate form
                        if (!data.firstname || !data.surname || !data.email_address || !data.role) {
                            Swal.showValidationMessage("Please fill in all required fields");
                            return false;
                        }
                        
                        return data;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = result.value;
                        
                        // Show loading state
                        Swal.fire({
                            title: "Processing...",
                            text: "Please wait while we create the staff account.",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        $.ajax({
                            url: "process_add_staff.php",
                            type: "POST",
                            data: formData,
                            success: function(response) {
                                try {
                                    if (response.includes("Location: admin_dashboard.php")) {
                                        Swal.fire({
                                            title: "Success!",
                                            text: "Staff account has been created successfully.",
                                            icon: "success",
                                            confirmButtonText: "OK"
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: "Failed to create staff account.",
                                            icon: "error",
                                            confirmButtonText: "OK"
                                        });
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Invalid server response.",
                                        icon: "error",
                                        confirmButtonText: "OK"
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX error:", error);
                                Swal.fire({
                                    title: "Error!",
                                    text: "An error occurred while creating staff account.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });
            });

            // Handle add hospital button click
            $("#addHospitalBtn").click(function() {
                Swal.fire({
                    title: "Add Hospital Info",
                    html: `
                        <form id="hospitalForm" class="text-start">
                            <div class="form-group mb-3">
                                <label for="hospital_name" class="form-label">Hospital Name</label>
                                <input type="text" class="form-control" id="hospital_name" name="hospital_name" required placeholder="Enter hospital name">
                            </div>
                            <div class="form-group mb-3">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Enter contact person name">
                            </div>
                            <div class="form-group mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" required placeholder="Enter contact number">
                            </div>
                            <div class="form-group mb-3">
                                <label for="email_address" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email_address" name="email_address" required placeholder="Enter email address">
                            </div>
                            <div class="form-group mb-3">
                                <label for="hospital_address" class="form-label">Hospital Address</label>
                                <input type="text" class="form-control" id="hospital_address" name="hospital_address" required placeholder="Enter hospital address">
                            </div>
                            <div class="form-group mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required placeholder="Enter city">
                            </div>
                            <div class="form-group mb-3">
                                <label for="province" class="form-label">Province</label>
                                <input type="text" class="form-control" id="province" name="province" required placeholder="Enter province">
                            </div>
                            <div class="form-group mb-3">
                                <label for="zip_code" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="Enter zip code (optional)">
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: "Add Hospital",
                    cancelButtonText: "Cancel",
                    focusConfirm: false,
                    didOpen: () => {
                        // Enable form inputs
                        const form = document.getElementById("hospitalForm");
                        if (form) {
                            const inputs = form.querySelectorAll('input');
                            inputs.forEach(input => {
                                input.disabled = false;
                                input.readOnly = false;
                            });
                        }
                    },
                    preConfirm: () => {
                        const form = document.getElementById("hospitalForm");
                        const formData = new FormData(form);
                        
                        // Validate form
                        if (!formData.get('hospital_name') || !formData.get('contact_number') || 
                            !formData.get('email_address') || !formData.get('hospital_address') || 
                            !formData.get('city') || !formData.get('province') || 
                            !formData.get('password')) {
                            Swal.showValidationMessage("Please fill in all required fields");
                            return false;
                        }
                        
                        return formData;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = result.value;
                        
                        // Show loading state
                        Swal.fire({
                            title: "Processing...",
                            text: "Please wait while we add the hospital account.",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        $.ajax({
                            url: "process_add_hospital_account.php",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                try {
                                    if (response.includes("successfully")) {
                                        Swal.fire({
                                            title: "Success!",
                                            text: "Hospital account has been added successfully.",
                                            icon: "success",
                                            confirmButtonText: "OK"
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error!",
                                            text: "Failed to add hospital account.",
                                            icon: "error",
                                            confirmButtonText: "OK"
                                        });
                                    }
                                } catch (e) {
                                    console.error("Error parsing response:", e);
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Invalid server response.",
                                        icon: "error",
                                        confirmButtonText: "OK"
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("AJAX error:", error);
                                Swal.fire({
                                    title: "Error!",
                                    text: "An error occurred while adding the hospital account.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });
            });

            // Handle rejection reason form submission
            $('#rejectionReasonForm').submit(function(e) {
                e.preventDefault();
                
                // Show loading state
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait while we process your request.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: 'process_rejection.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>