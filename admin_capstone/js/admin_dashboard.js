// Load screening details when the modal opens
$('#viewScreeningModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var userId = button.data('user-id');
    $(this).data('user-id', userId); // Store user ID in the modal

    // Fetch screening details via AJAX
    $.ajax({
        url: 'process_fetch_screening.php',
        type: 'GET',
        data: { user_id: userId },
        success: function (response) {
            $('#screeningDetails').html(response);
        },
        error: function () {
            $('#screeningDetails').html('<p>Error loading screening details.</p>');
        }
    });
});

// Handle verification/rejection actions
$(document).on('click', '.action-btn', function () {
    var action = $(this).data('action');
    var userId = $('#viewScreeningModal').data('user-id');

    if (action === 'reject') {
        // Show rejection reason modal
        $('#rejectionReasonModal').modal('show');

        // Set default rejection reason
        $('#rejectionReason').val('User did not meet eligibility criteria for blood donation.');

        // Submit rejection reason
        $('#rejectionReasonForm').off('submit').on('submit', function (e) {
            e.preventDefault(); // Prevent form from refreshing the page
            var reason = $('#rejectionReason').val();

            if (reason.trim() === '') {
                alert('Rejection reason cannot be empty.');
                return;
            }

            // Send rejection request to the server
            $.ajax({
                url: 'process_verification.php',
                type: 'POST',
                data: { user_id: userId, action: action, reason: reason },
                success: function (response) {
                    try {
                        var result = JSON.parse(response);
                        alert(result.message);
                        if (result.status === 'success') {
                            $('#rejectionReasonModal').modal('hide');
                            $('#viewScreeningModal').modal('hide');
                            location.reload(); // Reload the page to reflect updates
                        }
                    } catch (e) {
                        alert('Unexpected response: ' + response);
                    }
                },
                error: function (xhr) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        });
    } else if (action === 'verify') {
        // Direct verification logic
        $.ajax({
            url: 'process_verification.php',
            type: 'POST',
            data: { user_id: userId, action: action },
            success: function (response) {
                var result = JSON.parse(response);
                alert(result.message);
                if (result.status === 'success') {
                    $('#viewScreeningModal').modal('hide');
                    location.reload();
                }
            },
            error: function () {
                alert('Failed to update verification status.');
            }
        });
    }
});

// Sidebar toggle
document.getElementById('toggleSidebar').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    sidebar.classList.toggle('collapsed');
});

// Function to show the corresponding section and hide others
function showContent(contentId) {
    // Hide all content sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Show the selected content section
    const selectedContent = document.getElementById(contentId);
    if (selectedContent) {
        selectedContent.style.display = 'block';
    }
}

// Event listeners for navbar buttons
document.getElementById('homeLink').addEventListener('click', () => showContent('homeContent'));
document.getElementById('donationRequestLink').addEventListener('click', () => showContent('donationRequestContent'));
document.getElementById('userManagementLink').addEventListener('click', () => showContent('userManagementContent'));
document.getElementById('hospitalManagementLink').addEventListener('click', () => showContent('hospitalManagementContent'));
document.getElementById('bloodRequestLink').addEventListener('click', () => showContent('bloodRequestContent'));
document.getElementById('scheduleLink').addEventListener('click', () => showContent('scheduleContent'));
document.getElementById('staffAccountLink').addEventListener('click', () => showContent('staffAccountContent'));
document.getElementById('postLink').addEventListener('click', () => showContent('postContent'));


// Default view on page load - Home section is visible by default
window.onload = () => showContent('homeContent');

// Function to verify a user
function verifyUser(userId) {
    if (confirm('Are you sure you want to verify this user?')) {
        // Make an AJAX request to update the user status
        $.ajax({
            url: 'update_user_status.php',
            method: 'POST',
            data: {
                userId: userId,
                action: 'verify'
            },
            success: function(response) {
                alert(response);
                location.reload(); // Reload the page to reflect the changes
            },
            error: function() {
                alert('Error updating user status');
            }
        });
    }
}

// Reject User
function rejectUser(userId) {
    // Show the modal for entering rejection reason
    $('#rejectModal').modal('show');
    
    // Store user ID in a global variable to use when submitting the rejection
    window.userIdToReject = userId;
}

// Set a default rejection reason when the modal is shown
$('#rejectModal').on('show.bs.modal', function () {
    $('#rejectionReason').val('You are rejected'); // Set default rejection reason
});

// Submit Rejection Reason
function submitRejection(rejectionReason) {
    rejectionReason = rejectionReason.trim(); // Trim leading/trailing spaces
    
    // Check if the rejection reason is empty
    if (rejectionReason === '') {
        alert('Please enter a rejection reason'); // Show alert if empty
        return; // Exit the function if the rejection reason is empty
    }

    // Make an AJAX request to update the user status and insert the rejection reason
    $.ajax({
        url: 'update_user_status.php',
        method: 'POST',
        data: {
            userId: window.userIdToReject, // Pass the user ID to be rejected
            action: 'reject',
            rejectionReason: rejectionReason // Send the value from the textarea
        },
        success: function(response) {
            alert(response); // Show the response message
            $('#rejectModal').modal('hide'); // Hide the modal using Bootstrap 5 method
            location.reload(); // Reload the page to reflect the changes
        },
        error: function() {
            alert('Error rejecting user'); // Show error if AJAX fails
        }
    });
}

// JavaScript to toggle the sections
document.getElementById('hospitalManagementLink').addEventListener('click', function() {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(function(section) {
        section.style.display = 'none';
    });

    // Show the Hospital Management section
    document.getElementById('hospitalManagementContent').style.display = 'block';
});

















