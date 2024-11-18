<?php
// Include the database configuration file
include 'config.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the action (approve or decline) from the request
    $action = $_POST['action'];

    // Ensure required data is present
    if (!isset($_POST['leave_id']) || !isset($_POST['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }

    $leave_id = $_POST['leave_id'];
    $manager_id = $_POST['user_id']; // ID of the manager approving or declining the request
    $comment = isset($_POST['comment']) ? $_POST['comment'] : null;

    if ($action === 'approve') {
        // Approve action: Update the leave record with approval details
        $stmt = $conn->prepare("
            UPDATE TBL_LEAVE
            SET current_status = 1,
                comments = ?,
                approved_by = ?,
                approval_timestamp = NOW()
            WHERE leave_id = ?
        ");
        $stmt->bind_param("sii", $comment, $manager_id, $leave_id);
    } elseif ($action === 'decline') {
        // Decline action: Delete the leave record from the database
        $stmt = $conn->prepare("DELETE FROM TBL_LEAVE WHERE leave_id = ?");
        $stmt->bind_param("i", $leave_id);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

    if ($stmt->execute()) {
        // If the query was successful, respond with a success message
        echo json_encode([
            'status' => 'success',
            'message' => $action === 'approve' ? 'Leave approved' : 'Leave declined'
        ]);

        // Get leave and user details
        $stmt = $conn->prepare("
            SELECT
                l.*,
                u.email,
                u.username as employee_name,
                m.username as manager_name
            FROM tbl_leave l
            JOIN tbl_user u ON l.user_id = u.user_id
            LEFT JOIN tbl_user m ON l.approved_by = m.user_id
            WHERE l.leave_id = ?
        ");

        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $leaveData = $result->fetch_assoc();

        if ($leaveData) {
            $leaveDetails = [
                'employee_name' => $leaveData['employee_name'],
                'department' => $leaveData['department'],
                'leave_type' => $leaveData['leave_type'],
                'start_date' => $leaveData['startdate'],
                'end_date' => $leaveData['end_date'],
                'duration' => (new DateTime($leaveData['end_date']))->diff(new DateTime($leaveData['startdate']))->days + 1,
                'manager_name' => $leaveData['manager_name'],
                'review_date' => date('Y-m-d'),
                'review_time' => date('H:i:s'),
                'notes' => $_POST['comment'] ?? 'No comments provided'
            ];

            // Send email notification
            $emailService = new EmailService();
            $status = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';
            $emailService->sendLeaveStatusUpdate($leaveData['email'], $leaveDetails, $status);
        }
    } else {
        // If there was an error with the query execution, respond with an error message
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update leave status'
        ]);
    }

    // Close the statement and the database connection
    $stmt->close();
    $conn->close();
} else {
    // If the request method is not POST, respond with an error
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
