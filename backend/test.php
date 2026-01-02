<?php
/**
 * FixIt - System Test Script
 * 
 * This script tests the database connection and basic functionality.
 * Run this to verify your setup is working correctly.
 * 
 * @author FixIt Development Team
 * @version 1.0
 */

// Set content type for browser display
header('Content-Type: text/html; charset=utf-8');

// Include database connection
require_once 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .test-section { margin: 20px 0; padding: 15px; background: white; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        .status { font-weight: bold; }
        pre { background-color: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 FixIt System Test</h1>
    <p>This script tests your FixIt installation to ensure everything is working correctly.</p>
    
    <div class="test-section">
        <h2>1. Database Connection Test</h2>
        <?php
        try {
            // Load database credentials from db_connect.php
            require_once 'db_connect.php';
            
            // Use variables defined in db_connect.php
            $conn = new PDO("mysql:host=" . $db_host . ";dbname=" . $db_name, $db_user, $db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo '<div class="test-result success">✅ Database connection successful!</div>';
            
            // Test database version
            $version = $conn->query("SELECT VERSION()")->fetchColumn();
            echo '<div class="test-result info">ℹ️ MySQL Version: ' . htmlspecialchars($version) . '</div>';
            
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>2. Database Schema Test</h2>
        <?php
        try {
            // Check if reports table exists
            $result = $conn->query("SHOW TABLES LIKE 'reports'");
            if ($result->rowCount() > 0) {
                echo '<div class="test-result success">✅ Reports table exists</div>';
                
                // Get table structure
                $columns = $conn->query("SHOW COLUMNS FROM reports");
                echo '<div class="test-result info">📋 Reports table structure:</div>';
                echo '<pre>';
                foreach ($columns as $column) {
                    echo htmlspecialchars($column['Field']) . ' - ' . htmlspecialchars($column['Type']) . ' - ' . htmlspecialchars($column['Null']) . ' - ' . htmlspecialchars($column['Key']) . ' - ' . htmlspecialchars($column['Default']) . "\n";
                }
                echo '</pre>';
                
                // Count reports
                $count = $conn->query("SELECT COUNT(*) FROM reports")->fetchColumn();
                echo '<div class="test-result info">📊 Total reports in database: ' . $count . '</div>';
                
            } else {
                echo '<div class="test-result error">❌ Reports table does not exist</div>';
            }
            
            // Check if admin_users table exists
            $result = $conn->query("SHOW TABLES LIKE 'admin_users'");
            if ($result->rowCount() > 0) {
                echo '<div class="test-result success">✅ Admin users table exists</div>';
            } else {
                echo '<div class="test-result error">❌ Admin users table does not exist</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Database schema test failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>3. File System Test</h2>
        <?php
        $requiredFiles = [
            '../index.html',
            '../report.html',
            '../dashboard.html',
            '../assets/style.css',
            '../assets/script.js',
            '../assets/images/placeholder.png',
            'db_connect.php',
            'submit_report.php',
            'get_reports.php',
            'update_status.php',
            '../fixit_db.sql'
        ];
        
        foreach ($requiredFiles as $file) {
            if (file_exists($file)) {
                echo '<div class="test-result success">✅ ' . htmlspecialchars($file) . ' exists</div>';
            } else {
                echo '<div class="test-result error">❌ ' . htmlspecialchars($file) . ' does not exist</div>';
            }
        }
        
        // Check uploads directory
        $uploadsDir = '../assets/images/uploads/';
        if (is_dir($uploadsDir)) {
            echo '<div class="test-result success">✅ Uploads directory exists</div>';
            if (is_writable($uploadsDir)) {
                echo '<div class="test-result success">✅ Uploads directory is writable</div>';
            } else {
                echo '<div class="test-result error">❌ Uploads directory is not writable</div>';
            }
        } else {
            echo '<div class="test-result error">❌ Uploads directory does not exist</div>';
            echo '<div class="test-result info">ℹ️ Create directory: ' . $uploadsDir . '</div>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>4. Sample Data Test</h2>
        <?php
        try {
            // Get sample reports
            $reports = $conn->query("SELECT id, description, location, status, created_at FROM reports ORDER BY created_at DESC LIMIT 3");
            
            if ($reports->rowCount() > 0) {
                echo '<div class="test-result success">✅ Sample data found</div>';
                echo '<div class="test-result info">📋 Sample reports:</div>';
                echo '<pre>';
                foreach ($reports as $report) {
                    echo 'ID: ' . $report['id'] . '\n';
                    echo 'Description: ' . substr($report['description'], 0, 50) . '...\n';
                    echo 'Location: ' . $report['location'] . '\n';
                    echo 'Status: ' . $report['status'] . '\n';
                    echo 'Created: ' . $report['created_at'] . '\n';
                    echo '---\n';
                }
                echo '</pre>';
            } else {
                echo '<div class="test-result error">❌ No sample data found</div>';
                echo '<div class="test-result info">ℹ️ Import fixit_db.sql to add sample data</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Sample data test failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>5. API Endpoint Test</h2>
        <?php
        // Test API endpoints
        $apiEndpoints = [
            'get_reports.php' => 'GET',
            'submit_report.php' => 'POST',
            'update_status.php' => 'POST'
        ];
        
        foreach ($apiEndpoints as $endpoint => $method) {
            $url = 'http://localhost' . dirname($_SERVER['PHP_SELF']) . '/' . $endpoint;
            echo '<div class="test-result info">🔗 Testing ' . $endpoint . ' (' . $method . ')</div>';
            echo '<div class="test-result info">📍 URL: ' . htmlspecialchars($url) . '</div>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>6. Next Steps</h2>
        <div class="test-result info">
            <p><strong>🎯 To complete your setup:</strong></p>
            <ol>
                <li>Ensure all tests above show success (✅)</li>
                <li>Open <a href="../index.html" target="_blank">Home Page</a> in your browser</li>
                <li>Test the <a href="../report.html" target="_blank">Report Form</a></li>
                <li>Check the <a href="../dashboard.html" target="_blank">Dashboard</a></li>
                <li>Submit a test report and verify it appears in the dashboard</li>
            </ol>
        </div>
        
        <div class="test-result info">
            <p><strong>🔗 Quick Links:</strong></p>
            <ul>
                <li><a href="../index.html" target="_blank">Home Page</a></li>
                <li><a href="../report.html" target="_blank">Report Issue</a></li>
                <li><a href="../dashboard.html" target="_blank">Admin Dashboard</a></li>
                <li><a href="../README.md" target="_blank">README Documentation</a></li>
            </ul>
        </div>
    </div>
    
    <?php
    // Close database connection
    if (isset($conn)) {
$conn = null;
    }
    ?>
    
    <div style="text-align: center; margin-top: 30px; padding: 20px; background-color: #667eea; color: white; border-radius: 5px;">
        <h3>🎉 FixIt System Test Complete!</h3>
        <p>Developed by Faraz Hussain & Ali Raza - Sukkur IBA University</p>
        <p>Making communities better, one report at a time.</p>
    </div>
    
</body>
</html>