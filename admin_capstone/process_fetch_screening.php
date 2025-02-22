<?php
// Include database connection
include('connection.php');

// Fetch users from screening_answers where verification_status is NOT 'Verified'
$sql = "SELECT u.firstname, u.surname, u.email_address, u.id, s.verification_status
        FROM users u
        JOIN screening_answers s ON u.id = s.user_id
        WHERE s.verification_status != 'Verified'";  // Exclude verified users

$result = mysqli_query($conn, $sql);

$users = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Generate the HTML for displaying user information
if (!empty($users)) {
    echo '<div class="card">
            <h5 class="card-header">User Information</h5>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Surname</th>
                            <th>Email Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($users as $user) {
                        echo '<tr>
                                <td>' . htmlspecialchars($user['firstname']) . '</td>
                                <td>' . htmlspecialchars($user['surname']) . '</td>
                                <td>' . htmlspecialchars($user['email_address']) . '</td>
                                <td>' . htmlspecialchars($user['verification_status']) . '</td>
                                <td>
                                    <a href="?action=verify&user_id=' . $user['id'] . '" class="btn btn-success">Verify</a>
                                    <a href="?action=reject&user_id=' . $user['id'] . '" class="btn btn-danger">Reject</a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewScreeningModal" data-user-id="' . htmlspecialchars($user['id']) . '">View Screening</button>
                                </td>
                              </tr>';
                    }
    echo '</tbody>
        </table>
    </div>
    </div>';
} else {
    echo '<div class="card"><p>No users available for verification.</p></div>';
}
?>

<!-- Bootstrap Modal -->
<div class="modal fade" id="viewScreeningModal" tabindex="-1" aria-labelledby="viewScreeningModalLabel" aria-hidden="true">
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// AJAX Request to Fetch Screening Details
document.addEventListener('DOMContentLoaded', function () {
    var viewScreeningModal = document.getElementById('viewScreeningModal');
    viewScreeningModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var userId = button.getAttribute('data-user-id'); // Extract user ID

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
});
</script>
