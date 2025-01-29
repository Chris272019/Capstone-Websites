<?php
// Include database connection
include('connection.php');

// Fetch staff information from staff_account table
$sql = "SELECT firstname, surname, middlename, email_address AS email, role, id FROM staff_account";
$result = mysqli_query($conn, $sql);

$staff_accounts = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $staff_accounts[] = $row;
    }
}

// Generate the HTML for displaying staff account information
if (!empty($staff_accounts)) {
    echo '<div class="card">
            <h5 class="card-header">Staff Information</h5>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Surname</th>
                            <th>Middle Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach ($staff_accounts as $staff) {
                        echo '<tr>
                                <td>' . htmlspecialchars($staff['firstname']) . '</td>
                                <td>' . htmlspecialchars($staff['surname']) . '</td>
                                <td>' . htmlspecialchars($staff['middlename']) . '</td>
                                <td>' . htmlspecialchars($staff['email']) . '</td>
                                <td>' . htmlspecialchars($staff['role']) . '</td>
                                <td>
                                    <button class="btn btn-success" onclick="editStaff(' . $staff['id'] . ')">Edit</button>
                                    <button class="btn btn-danger" onclick="deleteStaff(' . $staff['id'] . ')">Delete</button>
                                </td>
                              </tr>';
                    }
    echo '</tbody>
        </table>
    </div>
    </div>';
} else {
    echo '<div class="card"><p>No staff information available at the moment.</p></div>';
}
?>
