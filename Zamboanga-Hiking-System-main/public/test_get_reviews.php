<?php
// Test file to debug get_reviews.php
// Place this in /public/test_get_reviews.php

echo "<h2>Testing get_reviews.php</h2>";

// Test 1: Check if file exists
echo "<h3>Test 1: File Check</h3>";
if (file_exists('get_reviews.php')) {
    echo "✓ get_reviews.php EXISTS<br>";
} else {
    echo "✗ get_reviews.php NOT FOUND<br>";
}

// Test 2: Check database connection
echo "<h3>Test 2: Database Connection</h3>";
if (file_exists('../includes/db.php')) {
    require_once '../includes/db.php';
    echo "✓ Database connected<br>";
    
    // Test 3: Check if reviews table exists
    echo "<h3>Test 3: Reviews Table</h3>";
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'reviews'");
        if ($checkTable->rowCount() > 0) {
            echo "✓ Reviews table EXISTS<br>";
            
            // Count reviews
            $countStmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
            $count = $countStmt->fetch(PDO::FETCH_ASSOC);
            echo "Total reviews in database: " . $count['total'] . "<br>";
            
            // Count by status
            $statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM reviews GROUP BY status");
            echo "<br>Reviews by status:<br>";
            while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
                echo "- " . $row['status'] . ": " . $row['count'] . "<br>";
            }
        } else {
            echo "✗ Reviews table DOES NOT EXIST<br>";
            echo "<p style='color:red;'>You need to create the reviews table first!</p>";
        }
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ Database connection file not found<br>";
}

// Test 4: Try to call get_reviews.php
echo "<h3>Test 4: Call get_reviews.php</h3>";
echo "<a href='get_reviews.php?trail_id=1' target='_blank'>Click here to test get_reviews.php?trail_id=1</a><br>";
echo "<p>This should open a new tab with JSON response</p>";

// Test 5: Trail ID test
echo "<h3>Test 5: Current Trail ID from URL</h3>";
echo "Trail ID: " . ($_GET['id'] ?? 'Not set') . "<br>";
?>