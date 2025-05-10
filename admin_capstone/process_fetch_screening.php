<?php
// Include database connection
include('connection.php');

// Fetch users from screening_answers where verification_status is NOT 'Verified'
$sql = "SELECT u.firstname, u.surname, u.email_address, u.id, s.verification_status
    FROM users u
    JOIN screening_answers s ON u.id = s.user_id
    WHERE s.verification_status NOT IN ('Verified', 'Rejected')";  // Exclude Verified and Rejected users

$result = mysqli_query($conn, $sql);

$users = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Add custom CSS for the table
echo '<style>
    .donation-table-container {
        margin: 20px;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .donation-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .donation-table th, .donation-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .donation-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    .donation-table tr:hover {
        background-color: #f5f5f5;
    }
    .search-input {
        width: 100%;
        padding: 8px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn-primary {
        padding: 6px 12px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary:hover {
        background-color: #0056b3;
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
    echo '<div class="donation-table-container">
            <h3>Donation Requests</h3>
            <div class="table-responsive">
                <table class="donation-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Surname</th>
                            <th>Email Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        <tr>
                            <th><input type="text" id="searchFirstName" placeholder="Search First Name" class="search-input"></th>
                            <th><input type="text" id="searchSurname" placeholder="Search Surname" class="search-input"></th>
                            <th><input type="text" id="searchEmail" placeholder="Search Email" class="search-input"></th>
                            <th><input type="text" id="searchStatus" placeholder="Search Status" class="search-input"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($users as $user) {
                        $statusClass = 'status-' . strtolower($user['verification_status']);
                        echo '<tr>
                                <td>' . htmlspecialchars($user['firstname']) . '</td>
                                <td>' . htmlspecialchars($user['surname']) . '</td>
                                <td>' . htmlspecialchars($user['email_address']) . '</td>
                                <td><span class="status-badge ' . $statusClass . '">' . htmlspecialchars($user['verification_status']) . '</span></td>
                                <td>
                                    <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#viewScreeningModal" 
                                        data-user-id="' . htmlspecialchars($user['id']) . '" 
                                        data-user-name="' . htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['surname']) . '">
                                        View Screening
                                    </button>
                                </td>
                              </tr>';
                    }
    echo '</tbody>
        </table>
    </div>
    </div>';
} else {
    echo '<div class="donation-table-container">
            <h3>Donation Requests</h3>
            <p>No users available for verification.</p>
          </div>';
}
?>

<!-- Bootstrap Modal for Viewing Screening -->
<div class="modal" id="viewScreeningModal" tabindex="-1" aria-labelledby="viewScreeningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewScreeningModalLabel">Screening Answers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="screeningDetails">
                <!-- Screening details will be dynamically loaded here -->
                <p>Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="verifyUser">Verify</button>
                <button type="button" class="btn btn-danger" id="rejectUser">Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- Include Bootstrap 5 JS and SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewScreeningModal = document.getElementById('viewScreeningModal');
    var modal = new bootstrap.Modal(viewScreeningModal);

    // Event listener for showing the screening modal
    viewScreeningModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var userId = button.getAttribute('data-user-id'); // Extract user ID

        document.getElementById('verifyUser').setAttribute('data-user-id', userId);
        document.getElementById('rejectUser').setAttribute('data-user-id', userId);

        // Fetch screening details via AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_screening.php?user_id=' + userId, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('screeningDetails').innerHTML = xhr.responseText;
            } else {
                document.getElementById('screeningDetails').innerHTML = 'Error loading screening details.';
            }
        };
        xhr.send();
    });

    function processVerification(action, userId) {
        // Show loading state
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`process_verification.php?action=${action}&user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                modal.hide();
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: action === 'verify' ? 
                            'User has been successfully verified!' : 
                            'User has been rejected.',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reload the page to update the user list
                            window.location.reload();
                        }
                    });
                } else {
                    throw new Error(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'An error occurred while processing your request.',
                    confirmButtonText: 'OK'
                });
            });
    }

    document.getElementById('verifyUser').addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        Swal.fire({
            title: 'Confirm Verification',
            text: 'Are you sure you want to verify this user?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, verify!'
        }).then((result) => {
            if (result.isConfirmed) {
                processVerification('verify', userId);
            }
        });
    });

    document.getElementById('rejectUser').addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        Swal.fire({
            title: 'Confirm Rejection',
            text: 'Are you sure you want to reject this user?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject!'
        }).then((result) => {
            if (result.isConfirmed) {
                processVerification('reject', userId);
            }
        });
    });
});
</script>