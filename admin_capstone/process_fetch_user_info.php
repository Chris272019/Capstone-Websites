<?php
// Include database connection
include('connection.php');

// Fetch user information for User Management section
$sql = "SELECT username, firstname, middlename, email_address, id, verification_status FROM users";
$result = mysqli_query($conn, $sql);

$users = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Add custom CSS for the table
echo '<style>
    .user-table-container {
        margin: 20px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .user-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .user-table th, .user-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .user-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    .user-table tr:hover {
        background-color: #f5f5f5;
    }
    .search-input {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn-info {
        padding: 6px 12px;
        background-color: #17a2b8;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-info:hover {
        background-color: #138496;
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875em;
        font-weight: 500;
    }
    .status-pending {
        background-color: #ffc107;
        color: #000;
    }
    .status-verified {
        background-color: #28a745;
        color: white;
    }
    .status-rejected {
        background-color: #dc3545;
        color: white;
    }
</style>';

// Generate the HTML for displaying user information
if (!empty($users)) {
    echo '<div class="user-table-container">
            <h3>User Management</h3>
            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Email Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="searchUsername" placeholder="Search Username" class="search-input"></th>
                            <th><input type="text" id="searchFirstname" placeholder="Search First Name" class="search-input"></th>
                            <th><input type="text" id="searchMiddlename" placeholder="Search Middle Name" class="search-input"></th>
                            <th><input type="text" id="searchEmail" placeholder="Search Email" class="search-input"></th>
                            <th><input type="text" id="searchStatus" placeholder="Search Status" class="search-input"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($users as $user) {
                        $statusClass = 'status-' . strtolower($user['verification_status']);
                        echo '<tr>
                                <td>' . htmlspecialchars($user['username']) . '</td>
                                <td>' . htmlspecialchars($user['firstname']) . '</td>
                                <td>' . htmlspecialchars($user['middlename']) . '</td>
                                <td>' . htmlspecialchars($user['email_address']) . '</td>
                                <td><span class="status-badge ' . $statusClass . '">' . htmlspecialchars($user['verification_status']) . '</span></td>
                                <td>
                                    <button class="btn-info" onclick="showProfileModal(' . $user['id'] . ')">View Profile</button>
                                </td>
                              </tr>';
                    }
    echo '</tbody>
        </table>
    </div>
    </div>';
} else {
    echo '<div class="user-table-container">
            <h3>User Management</h3>
            <p>No user information available at the moment.</p>
          </div>';
}
?>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verificationModalLabel">User Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="verificationModalBody">
                <!-- Image will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="rejectUser(${currentUserId})">Reject</button>
                <button type="button" class="btn btn-success" onclick="verifyUser(${currentUserId})">Verify</button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectionModalLabel">Reject User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reject this user for blood donation? The user will be notified with the message: "You are not qualified for the blood donation".</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="rejectUser()">Reject User</button>
            </div>
        </div>
    </div>
</div>

<!-- User Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel">User Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="profileModalBody">
                <!-- Profile data will be loaded here -->
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="verifyUserFromProfile()">Verify</button>
            </div>
        </div>
    </div>
</div>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentUserId = null;
let rejectUserId = null;

function showProfileModal(userId) {
    // Show the modal with loading spinner
    const modal = new bootstrap.Modal(document.getElementById('profileModal'));
    modal.show();
    
    // Fetch the user's profile data
    fetch(`get_profile_data.php?user_id=${userId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('profileModalBody').innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('profileModalBody').innerHTML = '<div class="alert alert-danger">Error loading profile data</div>';
        });
}

function showVerificationModal(userId) {
    currentUserId = userId;
    
    // Show loading state
    Swal.fire({
        title: "Loading...",
        text: "Please wait while we fetch verification details.",
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create form data for the request
    const formData = new FormData();
    formData.append('user_id', userId);

    // Fetch the user's verification image
    $.ajax({
        url: 'get_verification_image.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.fire({
                title: "User Verification",
                html: response,
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: "Verify",
                denyButtonText: "Reject",
                cancelButtonText: "Close",
                confirmButtonColor: "#28a745",
                denyButtonColor: "#dc3545",
                focusConfirm: false,
                didOpen: () => {
                    // Add event listeners for the buttons
                    const confirmButton = Swal.getConfirmButton();
                    const denyButton = Swal.getDenyButton();
                    
                    confirmButton.addEventListener('click', () => {
                        verifyUser(currentUserId);
                    });
                    
                    denyButton.addEventListener('click', () => {
                        rejectUser(currentUserId);
                    });
                }
            });
        },
        error: function() {
            Swal.fire({
                title: "Error",
                text: "Error loading verification details",
                icon: "error"
            });
        }
    });
}

function verifyUser(userId) {
    // Show loading state
    Swal.fire({
        title: "Processing...",
        text: "Please wait while we verify the user.",
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        type: 'GET',
        url: 'process_verification.php',
        data: {
            action: 'verify',
            user_id: userId
        },
        dataType: 'json',
        success: function(response) {
            try {
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: 'User has been verified successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Failed to verify user.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                Swal.fire({
                    title: 'Error!',
                    text: 'Invalid server response.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

function rejectUser(userId) {
    Swal.fire({
        title: "Reject User",
        html: `
            <form id="rejectUserForm">
                <div class="form-group mb-3">
                    <label for="rejection_reason" class="form-label">Rejection Reason</label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required placeholder="Enter reason for rejection"></textarea>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: "Reject User",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#d33",
        focusConfirm: false,
        preConfirm: () => {
            const form = document.getElementById("rejectUserForm");
            const formData = new FormData(form);
            
            // Validate form
            if (!formData.get('rejection_reason')) {
                Swal.showValidationMessage("Please enter a rejection reason");
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
                text: "Please wait while we process the rejection.",
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                type: 'GET',
                url: 'process_verification.php',
                data: {
                    action: 'reject',
                    user_id: userId,
                    rejection_reason: formData.get('rejection_reason')
                },
                dataType: 'json',
                success: function(response) {
                    try {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: 'User has been rejected successfully.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to reject user.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Invalid server response.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Add search functionality for each column
const searchInputs = {
    username: document.getElementById('searchUsername'),
    firstname: document.getElementById('searchFirstname'),
    middlename: document.getElementById('searchMiddlename'),
    email: document.getElementById('searchEmail'),
    status: document.getElementById('searchStatus')
};

for (let key in searchInputs) {
    searchInputs[key].addEventListener('input', function () {
        filterTable();
    });
}

function filterTable() {
    const rows = document.querySelectorAll('#userTable tbody tr');
    rows.forEach(row => {
        let match = true;
        for (let key in searchInputs) {
            const cell = row.querySelector(`td:nth-child(${getColumnIndex(key)})`);
            if (cell && !cell.textContent.toLowerCase().includes(searchInputs[key].value.toLowerCase())) {
                match = false;
                break;
            }
        }
        row.style.display = match ? '' : 'none';
    });
}

function getColumnIndex(column) {
    const columnIndexes = {
        username: 1,
        firstname: 2,
        middlename: 3,
        email: 4,
        status: 5
    };
    return columnIndexes[column];
}

// Functions to handle verify and reject from profile modal
function verifyUserFromProfile() {
    const userId = document.getElementById('profileUserId').value;
    if (!userId) return;
    
    // Hide profile modal
    const profileModal = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
    profileModal.hide();
    
    // Show verification modal
    showVerificationModal(userId);
}
</script>
