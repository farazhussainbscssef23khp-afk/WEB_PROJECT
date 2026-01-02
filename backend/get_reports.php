<?php
/**
 * FixIt - Get Reports API
 * 
 * This script fetches all reports from the database and returns them in JSON format.
 * Used by the dashboard to display all reported issues.
 * 
 * @author FixIt Development Team
 * @version 1.0
 */

// Set JSON header for API response
header('Content-Type: application/json');

// Include database connection
require_once 'db_connect.php';

try {
    // Create connection
    $conn = new PDO("mysql:host=localhost;dbname=fixit;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL query to fetch all reports ordered by newest first
    $sql = "SELECT id, description, image, location, status, created_at 
            FROM reports 
            ORDER BY created_at DESC";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Fetch all results as associative array
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process the reports data
    $processedReports = array_map(function($report) {
        // Format the created_at date
        $report['created_at_formatted'] = date('F j, Y g:i A', strtotime($report['created_at']));
        
        // Ensure image path is relative to the project root
        if (!empty($report['image'])) {
            // Remove any leading slashes and ensure proper path
            $report['image'] = ltrim($report['image'], '/');
            
            // Check if image file exists
            $imagePath = '../' . $report['image'];
            if (!file_exists($imagePath)) {
                // Use placeholder image if file doesn't exist
                $report['image'] = 'assets/images/placeholder.png';
            }
        } else {
            // Use placeholder if no image is set
            $report['image'] = 'assets/images/placeholder.png';
        }
        
        // Ensure status is properly formatted
        $report['status'] = ucfirst(strtolower($report['status']));
        
        return $report;
    }, $reports);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Reports fetched successfully',
        'data' => $processedReports,
        'count' => count($processedReports)
    ]);
    
} catch (PDOException $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    // Return error response for other exceptions
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
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
 * To test this script directly:
 * 1. Navigate to: http://localhost/FixIt/backend/get_reports.php
 * 2. You should see JSON output with all reports
 * 
 * Expected JSON response format:
 * {
 *   "success": true,
 *   "message": "Reports fetched successfully",
 *   "data": [
 *     {
 *       "id": 1,
 *       "description": "Broken streetlight near main road",
 *       "image": "assets/images/uploads/report_1234567890.jpg",
 *       "location": "Main Road, Block A",
 *       "status": "Pending",
 *       "created_at": "2024-01-15 10:30:00",
 *       "created_at_formatted": "January 15, 2024 10:30 AM"
 *     }
 *   ],
 *   "count": 1
 * }
 * 
 * Integration with dashboard.html:
 * - This script is called via AJAX from dashboard.html
 * - The JavaScript in dashboard.html processes the JSON response
 * - Reports are displayed in cards with images, descriptions, and status
 * - Status can be updated using update_status.php
 */
?>