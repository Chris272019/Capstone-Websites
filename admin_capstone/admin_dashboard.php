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
    <title>Admin Dashboard</title>
    <!-- Bootstrap and Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

</head>
<body>

    <!-- Sidebar -->
    <!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></button>
    <h2>Admin Panel</h2>
    <a href="javascript:void(0);" class="nav-link" id="homeLink" data-target="homeContent"><i class="fas fa-home"></i> Home</a>
    <a href="javascript:void(0);" class="nav-link" id="donationRequestLink" data-target="donationRequestContent"><i class="fas fa-hand-holding-heart"></i> Donation Requests</a>
    <a href="javascript:void(0);" class="nav-link" id="userManagementLink" data-target="userManagementContent"><i class="fas fa-users"></i> User Management</a>
    <a href="javascript:void(0);" class="nav-link" id="hospitalManagementLink" data-target="hospitalManagementContent"><i class="fas fa-hospital"></i> Hospital Accounts</a>
    <a href="javascript:void(0);" class="nav-link" id="bloodRequestLink" data-target="bloodRequestContent"><i class="fas fa-tint"></i> Blood Requests</a>
    <a href="javascript:void(0);" class="nav-link" id="scheduleLink" data-target="scheduleContent"><i class="fas fa-calendar-alt"></i> Schedule</a>
    <a href="javascript:void(0);" class="nav-link" id="staffAccountLink" data-target="staffAccountContent"><i class="fas fa-clipboard"></i> Staff Account Management</a>
    <a href="javascript:void(0);" class="nav-link" id="postLink" data-target="postContent"><i class="fas fa-pen"></i> Post</a>
    
    <form action="process_logout.php" method="POST">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</div>

    
    <!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Home Section -->
    <div id="homeContent" class="content-section">
        <p>This is the Home section.</p>
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

    <!-- Schedule Content Section -->
    <div id="scheduleContent" class="content-section" style="display: none;">
        <h3>Schedule</h3>
        <?php include('process_fetch_schedule.php'); ?>
    </div>
    
    <div id="staffAccountContent" class="content-section" style="display: none;">
        <h3>Staff Account Management</h3>
        <button class="btn btn-primary" id="addStaffBtn" data-toggle="modal" data-target="#addStaffModal">
          <i class="fas fa-plus"></i> Add Staff
        </button>   
        <?php include('process_fetch_staff_accounts.php'); ?>
    </div>

    <!-- Post Section -->
    <div id="postContent" class="content-section" style="display: none;">
        <h3>Post</h3>
        <button class="btn btn-primary" id="addPostBtn" data-toggle="modal" data-target="#addPostModal">
          <i class="fas fa-plus"></i> Add Post
        </button>   
        <?php include('process_fetch_post.php'); ?>
    </div>
</div>


    <!-- Modal for Viewing Screening -->
    <div class="modal fade" id="addPostModal" tabindex="-1" aria-labelledby="addPostModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPostModalLabel">Add Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="postForm" action="process_post.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="postTitle" class="form-label">Post Title</label>
                        <input type="text" class="form-control" id="postTitle" name="postTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="postPhoto" class="form-label">Upload Photo</label>
                        <input type="file" class="form-control" id="postPhoto" name="postPhoto" accept="image/*" required>
                        <div class="mt-2">
                            <img id="postPhotoPreview" src="#" alt="Photo Preview" class="img-fluid" style="display: none; max-height: 200px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="postDescription" class="form-label">Post Description</label>
                        <textarea class="form-control" id="postDescription" name="postDescription" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Submit Post
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>



    <!-- Modal for Rejection Reason donation -->
    <div class="modal fade" id="rejectionReasonModal" tabindex="-1" aria-labelledby="rejectionReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reason for Rejection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectionReasonForm">
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label">Please provide the reason for rejection:</label>
                            <textarea id="rejectionReason" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Submit Reason</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding a new staff -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStaffModalLabel">Add New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="process_add_staff.php" method="POST">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" required placeholder="Enter first name">
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <input type="text" class="form-control" id="surname" name="surname" required placeholder="Enter surname">
                        </div>
                        <div class="form-group">
                            <label for="middlename">Middle Name</label>
                            <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Enter middle name (optional)">
                        </div>
                        <div class="form-group">
                            <label for="email_address">Email Address</label>
                            <input type="email" class="form-control" id="email_address" name="email_address" required placeholder="Enter email address">
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="Interviewer">Interviewer</option>
                                <option value="Physician">Physician</option>
                                <option value="Phlebotomist">Phlebotomist</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Add Staff</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Hospital Info Modal -->
    <div class="modal fade" id="addHospitalModal" tabindex="-1" role="dialog" aria-labelledby="addHospitalModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHospitalModalLabel">Add Hospital Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="process_add_hospital_account.php" method="POST">
                        <div class="form-group">
                            <label for="hospital_name">Hospital Name</label>
                            <input type="text" class="form-control" id="hospital_name" name="hospital_name" required placeholder="Enter hospital name">
                        </div>
                        <div class="form-group">
                            <label for="contact_person">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Enter contact person name">
                        </div>
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" required placeholder="Enter contact number">
                        </div>
                        <div class="form-group">
                            <label for="email_address">Email Address</label>
                            <input type="email" class="form-control" id="email_address" name="email_address" required placeholder="Enter email address">
                        </div>
                        <div class="form-group">
                            <label for="hospital_address">Hospital Address</label>
                            <input type="text" class="form-control" id="hospital_address" name="hospital_address" required placeholder="Enter hospital address">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city" required placeholder="Enter city">
                        </div>
                        <div class="form-group">
                            <label for="province">Province</label>
                            <input type="text" class="form-control" id="province" name="province" required placeholder="Enter province">
                        </div>
                        <div class="form-group">
                            <label for="zip_code">Zip Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="Enter zip code (optional)">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                        </div>
                        <button type="submit" class="btn btn-success">Add Hospital</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        // Function to show the selected content and hide others
        function showContent(target) {
            $('.content-section').hide(); // Hide all sections
            $('#' + target).show(); // Show the selected section
            localStorage.setItem('activeSection', target); // Store the active section
        }

        // Event listeners for sidebar links
        $('.nav-link').click(function() {
            const target = $(this).data('target');
            showContent(target);
        });

        // On page load, check localStorage for the active section
        const activeSection = localStorage.getItem('activeSection');
        if (activeSection) {
            showContent (activeSection); // Show the stored section if it exists
        } else {
            showContent('homeContent'); // Default to home section if no section is stored
        }

        // Open the Add Staff modal
        $('#addStaffBtn').click(function() {
            $('#addStaffModal').modal('show');
        });

        // Open the Add Hospital Info modal
        $('#addHospitalBtn').click(function() {
            $('#addHospitalModal').modal('show');
        });

        // Open the post modal
        $('#addPostBtn').click(function() {
            $('#addPostModal').modal('show');
        });
        
    });
    $(document).ready(function () {
        // Show preview of uploaded photo
        $('#postPhoto').change(function (e) {
            const file = e.target.files[0];
            const preview = $('#postPhotoPreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.attr('src', event.target.result).show();
                };
                reader.readAsDataURL(file);
            } else {
                preview.hide();
            }
        });

        // Handle form submission
        $('#postForm').submit(function (e) {
            e.preventDefault(); // Prevent page refresh
            const formData = new FormData(this);

            // Ajax call to submit post data
            $.ajax({
                url: 'process_post.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    alert(response); // Show response from the server
                    if (response.includes('Post added successfully')) {
                        $('#addPostModal').modal('hide');
                        $('#postForm')[0].reset(); // Reset the form
                        $('#postPhotoPreview').hide(); // Hide preview
                        location.reload(); // Refresh page to show new post
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    alert('Failed to add post. Please try again.');
                }
            });
        });
    });
    </script>

</body>
</html>
