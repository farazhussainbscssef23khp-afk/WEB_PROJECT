<?php
/**
 * FixIt - Update Report Status API
 * 
 * This script allows administrators to update the status of reported issues.
 * It receives report ID and new status via POST request and updates the database.
 * 
 * @author FixIt Development Team
 * @version 1.0
 */

// Set JSON header for API response
header('Content-Type: application/json');

// Include database connection
require_once 'db_connect.php';

// Define allowed status values
$allowedStatuses = ['Pending', 'In Progress', 'Resolved', 'Closed'];

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input data
    if (!isset($input['report_id']) || !isset($input['status'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: report_id and status are required'
        ]);
        exit;
    }
    
    $reportId = filter_var($input['report_id'], FILTER_VALIDATE_INT);
    $newStatus = trim($input['status']);
    
    // Validate report ID
    if ($reportId === false || $reportId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid report ID'
        ]);
        exit;
    }
    
    // Validate status
    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status. Allowed values: ' . implode(', ', $allowedStatuses)
        ]);
        exit;
    }
    
    // Create database connection
    // Create database connection using PDO
    try {
        $conn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]);
        exit;
    }
    
    // Check if report exists
    $checkSql = "SELECT id, status FROM reports WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$reportId]);
    $existingReport = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingReport) {
        echo json_encode([
            'success' => false,
            'message' => 'Report not found'
        ]);
        exit;
    }
    
    // Check if status is actually changing
    if ($existingReport['status'] === $newStatus) {
        echo json_encode([
            'success' => true,
            'message' => 'Status is already set to ' . $newStatus,
            'data' => [
                'report_id' => $reportId,
                'old_status' => $existingReport['status'],
                'new_status' => $newStatus,
                'changed' => false
            ]
        ]);
        exit;
    }
    
    // Update the report status
    $updateSql = "UPDATE reports SET status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateResult = $updateStmt->execute([$newStatus, $reportId]);
    
    if ($updateResult && $updateStmt->rowCount() > 0) {
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'report_id' => $reportId,
                'old_status' => $existingReport['status'],
                'new_status' => $newStatus,
                'changed' => true
            ]
        ]);
        
        // Log status change (optional)
        error_log("FixIt: Report ID $reportId status changed from {$existingReport['status']} to $newStatus");
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update status. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    // Return database error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Return error response for other exceptions
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn = null;
    }
}

/**
 * Test the API endpoint:
 * 
 * To test this script:
 * 1. Use a tool like Postman or curl
 * 2. Send a POST request to: http://localhost/FixIt/backend/update_status.php
 * 3. Set Content-Type: application/json
 * 4. Send JSON data like:
 *    {
 *      "report_id": 1,
 *      "status": "In Progress"
 *    }
 * 
 * Expected JSON response format:
 * Success:
 * {
 *   "success": true,
 *   "message": "Status updated successfully",
 *   "data": {
 *     "report_id": 1,
 *     "old_status": "Pending",
 *     "new_status": "In Progress",
 *     "changed": true
 *   }
 * }
 * 
 * Error:
 * {
 *   "success": false,
 *   "message": "Invalid report ID"
 * }
 * 
 * Integration with dashboard.html:
 * - This script is called via AJAX when admin clicks status update buttons
 * - The JavaScript in dashboard.html sends the report ID and new status
 * - On success, the dashboard automatically refreshes the reports list
 * - Status change is logged for audit purposes
 */
?>