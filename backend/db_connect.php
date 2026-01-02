<?php
/**
 * Database Connection File
 * FixIt - Public Problem Reporting System
 * Developed by Faraz Hussain & Ali Raza - Sukkur IBA University
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'fixit_db';

// Create connection
try {
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}

// Function to safely close connection
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

// Instructions:
// 1. Update the database credentials above if needed
// 2. Include this file in other PHP scripts using: require_once 'db_connect.php';
// 3. Use the $conn variable to execute queries
// 4. Always close the connection when done: closeConnection($conn);

?>