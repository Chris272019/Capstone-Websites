<?php
// Include database connection
include('connection.php');

// Fetch all users linked to the schedule table
$sql = "SELECT u.id, u.firstname, u.surname, u.email_address 
        FROM users u
        JOIN schedule s ON u.id = s.user_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {

  echo '<div class="card-container">';
    echo '<form method="POST" id="userManagementCard" action="process_add_schedule.php">'; // Form will submit to process_add_schedule.php
    


    // Loop through each user and display their details in a card
    while ($user = mysqli_fetch_assoc($result)) {
        echo '<div class="card">
                <div class="card-header">
                    <h5>User Information</h5>
                </div>
                <div class="card-body">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="selected_users[]" value="' . htmlspecialchars($user['id']) . '" id="user_' . htmlspecialchars($user['id']) . '">
                        <label class="form-check-label" for="user_' . htmlspecialchars($user['id']) . '">
                            <p><strong>First Name:</strong> ' . htmlspecialchars($user['firstname']) . '</p>
                            <p><strong>Surname:</strong> ' . htmlspecialchars($user['surname']) . '</p>
                            <p><strong>Email Address:</strong> ' . htmlspecialchars($user['email_address']) . '</p>
                        </label>
                    </div>
                </div>
              </div>';
    }

    echo '</div>'; // Close card container

    echo '<div style="text-align: center; margin-top: 20px;">
          </div>'; // Submit button

    echo '</form>';
} else {
    echo '<div class="card-container"><p>No users available for scheduling.</p></div>';
}
?>
<form method="POST" action="process_add_schedule.php" id="scheduleForm">
    <button type="button" class="btn btn-primary" id="openModalBtn" data-bs-toggle="modal" data-bs-target="#scheduleModal">Open Schedule Modal</button>
    
    <!-- Modal for Donation Details -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="scheduleModalLabel">Schedule Donation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Hidden user ID to pass selected users -->
            <input type="hidden" id="userId" name="user_id">
            <div class="mb-3">
  <label for="user_id_display" class="form-label">User ID</label>
  <input type="text" class="form-control" id="user_id_display" readonly>
  <input type="hidden" id="userId" name="user_id"> <!-- Hidden input for form submission -->
</div>


            <!-- Donation Details Form -->
            <div class="mb-3">
              <label for="donation_date" class="form-label">Donation Date</label>
              <input type="date" class="form-control" id="donation_date" name="donation_date" required>
            </div>
            <div class="mb-3">
              <label for="donation_time" class="form-label">Donation Time</label>
              <input type="time" class="form-control" id="donation_time" name="donation_time" required>
            </div>
            <div class="mb-3">
              <label for="location" class="form-label">Location</label>
              <input type="text" class="form-control" id="location" name="location" required>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" id="status" name="status" required>
                <option value="scheduled" selected>Scheduled</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="schedule_type" class="form-label">Schedule Type</label>
              <select class="form-select" id="schedule_type" name="schedule_type" required>
                <option value="Initial Screening">Initial Screening</option>
                <option value="Physical Examination">Physical Examination</option>
                <option value="Blood Collection">Blood Collection</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" id="saveScheduleBtn" class="btn btn-primary">Save Schedule</button>
          </div>
        </div>
      </div>
    </div>
</form>

<script>
$(document).ready(function() {
  // Handle checkbox selection
  $('input[name="selected_users[]"]').on('change', function() {
    var selectedIds = [];
    var selectedNames = [];

    // Gather selected user IDs and names
    $('input[name="selected_users[]"]:checked').each(function() {
      selectedIds.push($(this).data('user-id'));
      selectedNames.push($(this).data('firstname') + ' ' + $(this).data('surname'));
    });

    // Update user ID and names in the modal
    $('#user_id_display').val(selectedIds.join(', ')); // Display IDs in the text field
    $('#userId').val(selectedIds.join(',')); // Hidden input for form submission
  });

  // Handle the modal save button click
  $('#saveScheduleBtn').on('click', function() {
    var selectedUserIds = [];
    $('input[name="selected_users[]"]:checked').each(function() {
      selectedUserIds.push($(this).val()); // Add each selected user ID to the array
    });

    var donationDate = $('#donation_date').val();
    var donationTime = $('#donation_time').val();
    var location = $('#location').val();
    var status = $('#status').val();
    var scheduleType = $('#schedule_type').val();

    // Check if all required fields are filled
    if (selectedUserIds.length === 0 || !donationDate || !donationTime || !location || !status || !scheduleType) {
      alert('Please fill all required fields and select at least one user!');
      return;
    }

    // Submit the data via AJAX
    $.ajax({
      type: 'POST',
      url: 'process_add_schedule.php',
      data: {
        selected_users: selectedUserIds,  // Send all selected user IDs as an array
        donation_date: donationDate,
        donation_time: donationTime,
        location: location,
        status: status,
        schedule_type: scheduleType
      },
      success: function(response) {
        alert(response);  // Show the response message (success or error)
        $('#scheduleModal').modal('hide');
        location.reload(); // Reload to reflect updates
      },
      error: function(error) {
        alert('Error saving schedule.');
      }
    });
  });
});
</script>

