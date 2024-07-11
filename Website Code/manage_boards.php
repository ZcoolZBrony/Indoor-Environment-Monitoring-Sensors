<?php
$servername = "localhost";
$dbname = "penm2269_ESP32_TestData";
$username = "penm2269_ESP32_TestData";
$password = "ESP32_TestData";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Handle delete request
        $board = intval($_POST['board']);
        $sql = "DELETE FROM AllowedBoards WHERE Board = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $board);
        if ($stmt->execute()) {
            echo "Board deleted successfully";
        } else {
            echo "Error deleting board: " . $conn->error;
        }
        $stmt->close();
    } else {
        // Handle add/update request
        $board = intval($_POST['board']);
        $name = $_POST['name'];
        
        $sql = "REPLACE INTO AllowedBoards (Board, Name) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $board, $name);
        if ($stmt->execute()) {
            echo "Board added/updated successfully";
        } else {
            echo "Error adding/updating board: " . $conn->error;
        }
        $stmt->close();
    }
} else {
    // Handle fetch request
    $sql = "SELECT * FROM AllowedBoards";
    $result = $conn->query($sql);
    $boards = array();
    while ($row = $result->fetch_assoc()) {
        $boards[] = $row;
    }
    echo json_encode($boards);
}

$conn->close();
?>
