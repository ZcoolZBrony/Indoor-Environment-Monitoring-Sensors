<?php
$servername = "localhost";
$dbname = "penm2269_ESP32_TestData";
$username = "penm2269_ESP32_TestData";
$password = "ESP32_TestData";

$api_key_value = "tPmAT5Ab3j7F9";

$api_key = $board = $temperature = $humidity = $pressure = $light = $battery = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"]);
    if ($api_key == $api_key_value) {
        $board = test_input($_POST["value1"]);
        $temperature = test_input($_POST["value2"]);
        $humidity = test_input($_POST["value3"]);
        $pressure = test_input($_POST["value4"]);
        $light = test_input($_POST["value5"]);
        $battery = test_input($_POST["value6"]);
        
        // Validate board value
        if (in_array($board, [1, 2, 3, 4, 6, 5])) {
            // Validate the ranges
            if ($temperature >= 26 && $temperature <= 38 &&
                $humidity >= 35 && $humidity <= 100 &&
                $pressure >= 1000 && $pressure <= 1050 &&
                $light >= 0 && $light <= 500 &&
                $battery >= 2.3 && $battery <= 4.4) {
                
                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);
                
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                } 
                
                $sql = "INSERT INTO Home (Board, Temperature, Humidity, Pressure, Light, Battery)
                        VALUES ('" . $board . "', '" . $temperature . "', '" . $humidity . "', '" . $pressure . "', '" . $light . "', '" . $battery . "')";
                
                if ($conn->query($sql) === TRUE) {
                    echo "New record created successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
                
                $conn->close();
            } else {
                echo "One or more values are out of range.";
            }
        } else {
            echo "Invalid board value.";
        }
    } else {
        echo "Wrong API Key provided.";
    }
} else {
    echo "No data posted with HTTP POST.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
