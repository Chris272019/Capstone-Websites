<?php
// Include database connection
include('connection.php');

// Fetch users who are verified in screening_answers and not in schedule
$sql_verified = "SELECT u.id, u.firstname, u.surname, u.email_address 
        FROM users u
        JOIN screening_answers sa ON u.id = sa.user_id
        LEFT JOIN schedule s ON u.id = s.user_id
        WHERE sa.verification_status = 'Verified' 
        AND s.user_id IS NULL"; // Exclude users already scheduled

$result_verified = mysqli_query($conn, $sql_verified);

// Fetch users who have been rescheduled
$sql_rescheduled = "SELECT u.id, u.firstname, u.surname, u.email_address 
        FROM users u
        JOIN schedule s ON u.id = s.user_id
        WHERE s.status = 'Rescheduled'"; 

$result_rescheduled = mysqli_query($conn, $sql_rescheduled);
?>

<div class="container">
    <h3>Verified Users</h3>
    <div class="card-container">
        <form method="POST" id="verifiedUserForm">
            <?php if ($result_verified && mysqli_num_rows($result_verified) > 0) {
                while ($user = mysqli_fetch_assoc($result_verified)) {
                    echo '<div class="card">
                            <div class="card-header"><h5>User Information</h5></div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="selected_users[]" value="' . htmlspecialchars($user['id']) . '" id="verified_' . htmlspecialchars($user['id']) . '">
                                    <label class="form-check-label" for="verified_' . htmlspecialchars($user['id']) . '">
                                        <p><strong>First Name:</strong> ' . htmlspecialchars($user['firstname']) . '</p>
                                        <p><strong>Surname:</strong> ' . htmlspecialchars($user['surname']) . '</p>
                                        <p><strong>Email Address:</strong> ' . htmlspecialchars($user['email_address']) . '</p>
                                    </label>
                                </div>
                            </div>
                          </div>';
                }
            } else {
                echo '<p>No verified users available for scheduling.</p>';
            } ?>
        </form>
    </div>
</div>

<div class="container">
    <h3>Rescheduled Users</h3>
    <div class="card-container">
        <form method="POST" id="rescheduledUserForm">
            <?php if ($result_rescheduled && mysqli_num_rows($result_rescheduled) > 0) {
                while ($user = mysqli_fetch_assoc($result_rescheduled)) {
                    echo '<div class="card">
                            <div class="card-header"><h5>User Information</h5></div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="selected_users[]" value="' . htmlspecialchars($user['id']) . '" id="rescheduled_' . htmlspecialchars($user['id']) . '">
                                    <label class="form-check-label" for="rescheduled_' . htmlspecialchars($user['id']) . '">
                                        <p><strong>First Name:</strong> ' . htmlspecialchars($user['firstname']) . '</p>
                                        <p><strong>Surname:</strong> ' . htmlspecialchars($user['surname']) . '</p>
                                        <p><strong>Email Address:</strong> ' . htmlspecialchars($user['email_address']) . '</p>
                                    </label>
                                </div>
                            </div>
                          </div>';
                }
            } else {
                echo '<p>No rescheduled users available.</p>';
            } ?>
        </form>
    </div>
</div>

<button type="button" class="btn btn-primary" id="openModalBtn">Schedule Donation</button>

<script>
$(document).ready(function() {
    $('input[name="selected_users[]"]').on('change', function() {
        var selectedIds = [];
        $('input[name="selected_users[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });
    });

    $('#openModalBtn').on('click', function() {
        var selectedUserIds = [];
        $('input[name="selected_users[]"]:checked').each(function() {
            selectedUserIds.push($(this).val());
        });

        if (selectedUserIds.length === 0) {
            Swal.fire({
                title: "Error!",
                text: "Please select at least one user to schedule.",
                icon: "error",
                confirmButtonText: "OK"
            });
            return;
        }

        Swal.fire({
            title: "Schedule Donation",
            html: `
                <form id="scheduleForm">
                    <div class="form-group mb-3">
                        <label for="donation_date" class="form-label">Donation Date</label>
                        <input type="date" class="form-control" id="donation_date" name="donation_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="donation_time" class="form-label">Donation Time</label>
                        <input type="time" class="form-control" id="donation_time" name="donation_time" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required placeholder="Enter donation location">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: "Save Schedule",
            cancelButtonText: "Cancel",
            focusConfirm: false,
            preConfirm: () => {
                const form = document.getElementById("scheduleForm");
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                // Validate form
                if (!data.donation_date || !data.donation_time || !data.location) {
                    Swal.showValidationMessage("Please fill in all required fields");
                    return false;
                }
                
                return {
                    ...data,
                    selected_users: selectedUserIds
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = result.value;
                
                $.ajax({
                    url: "process_add_schedule.php",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        try {
                            if (response.includes("successfully")) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Donation schedule has been saved successfully.",
                                    icon: "success",
                                    confirmButtonText: "OK"
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: "Failed to save donation schedule.",
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
                            text: "An error occurred while saving the schedule.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
        });
    });
});
</script>
