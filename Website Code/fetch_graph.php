<?php
// Set the timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// MySQL Connection
$servername = "localhost";
$dbname = "penm2269_ESP32_TestData";
$username = "penm2269_ESP32_TestData";
$password = "ESP32_TestData";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the selected board from the request
$selected_board = isset($_GET['board']) ? $_GET['board'] : 'all';

// Fetch data for each parameter
$parameters = ['Temperature', 'Humidity', 'Pressure', 'Light', 'Battery'];

// Array to hold data for each parameter
$data = array();

// Loop through each parameter
foreach ($parameters as $parameter) {
    // Fetch data for the parameter for each board
    if ($selected_board == 'all') {
        $sql = "SELECT Board, $parameter, reading_time FROM Home ORDER BY reading_time DESC LIMIT 9000";
    } else {
        $board = intval($selected_board);
        $sql = "SELECT Board, $parameter, reading_time FROM Home WHERE Board = $board ORDER BY reading_time DESC LIMIT 9000";
    }
    $result = $conn->query($sql);

    // Prepare data for Highcharts
    $series_data = array();
    while ($row = $result->fetch_assoc()) {
        $timestamp = strtotime($row['reading_time']); // Convert to milliseconds
        // Convert the timestamp to Indonesian time
        $timestamp = strtotime('+7 hours', $timestamp);
        $timestamp *= 1000; // Convert to milliseconds
        $board = (int)$row['Board'];
        $value = (float)$row[$parameter];

        // Append data to series array for each board
        if (!isset($series_data[$board])) {
            $series_data[$board] = array(
                'name' => "Board $board",
                'data' => array()
            );
        }
        $series_data[$board]['data'][] = array($timestamp, $value);
    }

    // Push series data to the main data array
    $data[$parameter] = array_values($series_data);
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
