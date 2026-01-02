<?php
/**
 * Submit Report - Backend Script
 * FixIt - Public Problem Reporting System
 * Developed by Faraz Hussain & Ali Raza - Sukkur IBA University
 */

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db_connect.php';

// Function to sanitize input data
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to handle image upload
function handleImageUpload($file) {
    $uploadDir = '../assets/images/';
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validate file type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, and GIF images are allowed.');
    }
    
    // Validate file size
    if ($file['size'] > $maxFileSize) {
        throw new Exception('File size exceeds maximum limit of 5MB.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'report_' . uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    } else {
        throw new Exception('Failed to upload file.');
    }
}

// Main processing
try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST is allowed.');
    }
    
    // Validate and sanitize input data
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
    $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
    
    // Validate required fields
    if (empty($description)) {
        throw new Exception('Description is required.');
    }
    
    if (strlen($description) < 10) {
        throw new Exception('Description must be at least 10 characters long.');
    }
    
    if (empty($location)) {
        throw new Exception('Location is required.');
    }
    
    // Handle image upload (optional)
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $imagePath = handleImageUpload($_FILES['image']);
        } catch (Exception $e) {
            throw new Exception('Image upload failed: ' . $e->getMessage());
        }
    }
    
    // Prepare SQL query
    $sql = "INSERT INTO reports (description, image, location, status) VALUES (?, ?, ?, 'Pending')";
    
    // Use prepared statement for security
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $stmt->bind_param("sss", $description, $imagePath, $location);
    
    // Execute query
    if ($stmt->execute()) {
        $reportId = $conn->insert_id;
        
        // Send success response
        echo json_encode([
            'success' => true,
            'message' => 'Report submitted successfully!',
            'report_id' => $reportId
        ]);
        
        // Log successful submission (optional)
        error_log("Report submitted successfully. ID: " . $reportId);
        
    } else {
        throw new Exception('Failed to save report: ' . $stmt->error);
    }
    
    // Close statement
    $stmt->close();
    
} catch (Exception $e) {
    // Send error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log error (optional)
    error_log("Report submission error: " . $e->getMessage());
}

// Close database connection
closeConnection($conn);

?>

<!-- 
Instructions for testing:
1. Make sure the assets/images directory has write permissions (chmod 755)
2. Test with a simple HTML form or use the provided report.html page
3. Check PHP error logs if issues occur
4. Verify database connection is working

Sample test data:
- Description: "Broken streetlight on main road near the university gate. Very dark at night and poses safety risk."
- Location: "Main Road, Sukkur IBA University"
- Image: Any valid image file (optional)
-->