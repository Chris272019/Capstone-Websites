<?php
// Include database connection
include('connection.php');

// Fetch hospital account details from the database
$sql = "SELECT id, hospital_name, contact_person, contact_number, email_address FROM hospital_accounts";
$result = mysqli_query($conn, $sql);

// Array to store hospital accounts
$hospital_accounts = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $hospital_accounts[] = $row;
    }
}
?>

<div class="card">
    <h3>Hospital Accounts</h3>
    <?php if (!empty($hospital_accounts)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Hospital Name</th>
                    <th>Contact Person</th>
                    <th>Contact Number</th>
                    <th>Email Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hospital_accounts as $hospital): ?>
                    <tr>
                        <td><?= htmlspecialchars($hospital['hospital_name']) ?></td>
                        <td><?= htmlspecialchars($hospital['contact_person']) ?></td>
                        <td><?= htmlspecialchars($hospital['contact_number']) ?></td>
                        <td><?= htmlspecialchars($hospital['email_address']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hospital accounts found.</p>
    <?php endif; ?>
</div>
