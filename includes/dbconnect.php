<?php
$conn = new mysqli("localhost", "root", "", "project_ayurveda");

// Check connection properly
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to support special characters
$conn->set_charset("utf8mb4");
?>