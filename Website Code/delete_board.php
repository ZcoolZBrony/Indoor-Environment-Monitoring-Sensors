<?php
$servername = "localhost";
// REPLACE with your Database name
$dbname = "penm2269_ESP32_TestData";
$username = "penm2269_ESP32_TestData";
$password = "ESP32_TestData";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL statement to delete all rows except where Board is 1, 2, 3, 4, or 6
$sql = "DELETE FROM Home WHERE Board NOT IN (1, 2, 3, 4, 6)";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Records deleted successfully";
} else {
    echo "Error deleting records: " . $conn->error;
}

// Close the connection
$conn->close();
?>
