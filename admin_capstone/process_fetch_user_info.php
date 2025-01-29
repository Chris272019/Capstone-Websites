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

// Generate the HTML for displaying user information
if (!empty($users)) {
    echo '<div class="card">
            <h5 class="card-header">User Information</h5>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Email Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($users as $user) {
                        echo '<tr>
                                <td>' . htmlspecialchars($user['username']) . '</td>
                                <td>' . htmlspecialchars($user['firstname']) . '</td>
                                <td>' . htmlspecialchars($user['middlename']) . '</td>
                                <td>' . htmlspecialchars($user['email_address']) . '</td>
                                <td>' . htmlspecialchars($user['verification_status']) . '</td>
                                <td>
                                    <button class="btn btn-success" onclick="verifyUser(' . $user['id'] . ')">Verify</button>
                                    <button class="btn btn-danger" onclick="rejectUser(' . $user['id'] . ')">Reject</button>
                                </td>
                              </tr>';
                    }
    echo '</tbody>
        </table>
    </div>
    </div>';
} else {
    echo '<div class="card"><p>No user information available at the moment.</p></div>';
}
?>
