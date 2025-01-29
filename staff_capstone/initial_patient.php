<?php
// Start the session
session_start();

// Include your database connection file
include('connection.php');

// Query to get user details from the users table where id matches user_id in initial_screening and status is not 'Verified'
$query = "
    SELECT u.id AS user_id, u.firstname, u.middlename, u.surname 
    FROM users u
    INNER JOIN initial_screening iscreen ON u.id = iscreen.user_id
    WHERE iscreen.status != 'Verified'";

$result = $conn->query($query);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Store the user details in an array
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details with Unverified Status</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
        }

        h1 {
            color: #d71c1c; /* Blood red color */
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #d71c1c; /* Blood red color */
            color: white;
        }

        .action-button {
            display: inline-block;
            background-color: #d71c1c; /* Blood red color */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .action-button:hover {
            background-color: #b01616; /* Darker red */
        }

        .logout-button {
            display: inline-block;
            background-color: #444; /* Neutral dark color */
            color: white;
            padding: 10px 20px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
            text-align: center;
            display: block;
            width: 150px;
            margin: 20px auto 0;
        }

        .logout-button:hover {
            background-color: #333; /* Darker neutral color */
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>User Details with Unverified Status</h1>

        <?php if (empty($users)) { ?>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No users found with unverified blood collection status.</p>
        <?php } else { ?>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($user['middlename']); ?></td>
                            <td><?php echo htmlspecialchars($user['surname']); ?></td>
                            <td>
                                <a href="initial_screening_staff_dashboard.php?user_id=<?php echo urlencode($user['user_id']); ?>" class="action-button">
                                View Details
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>

        <!-- Logout Button -->
        <a href="javascript:void(0);" class="logout-button" id="logoutButton">Logout</a>
    </div>
    
<script>
    // Add SweetAlert confirmation to the logout button
    document.getElementById('logoutButton').addEventListener('click', function () {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out of your session.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d71c1c',
            cancelButtonColor: '#444',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the logout script
                window.location.href = "staff_logout.php";
            }
        });
    });
</script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
