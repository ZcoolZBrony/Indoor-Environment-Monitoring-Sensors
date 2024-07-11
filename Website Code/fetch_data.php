<?php
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

// Fetch latest parameter readings for each board along with board names
$sql = "SELECT Home.Board, AllowedBoards.Name, Home.Temperature, Home.Humidity, Home.Pressure, Home.Light, Home.Battery, Home.reading_time
        FROM Home
        JOIN AllowedBoards ON Home.Board = AllowedBoards.Board
        WHERE (Home.Board, Home.reading_time) IN 
              (SELECT Board, MAX(reading_time) 
               FROM Home 
               GROUP BY Board)
        ORDER BY Home.Board";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<h2>Board " . $row["Board"] . " - " . $row["Name"] . "</h2>";
        echo "<table>";
        echo "<tr><th>Parameter</th><th>Value</th></tr>";
        echo "<tr><td>Temperature</td><td>" . $row["Temperature"] . " Celcius</tr>";
        echo "<tr><td>Humidity</td><td>" . $row["Humidity"] . "</td></tr>";
        echo "<tr><td>Pressure</td><td>" . $row["Pressure"] . " mmbar</td></tr>";
        echo "<tr><td>Light</td><td>" . $row["Light"] . "</td></tr>";
        echo "<tr><td>Battery Volts</td><td>" . $row["Battery"] . "</td></tr>";
        echo "<tr><td>Reading Time</td><td>" . $row["reading_time"] . "</td></tr>";
        echo "</table>";
    }
} else {
    echo "0 results";
}
$conn->close();
?>
