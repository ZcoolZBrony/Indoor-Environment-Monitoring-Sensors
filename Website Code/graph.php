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

// Fetch unique boards
$sql = "SELECT DISTINCT Board FROM Home";
$result = $conn->query($sql);

$boards = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $boards[] = $row['Board'];
    }
} else {
    echo "0 results";
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parameter Graphs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Load Highcharts from CDN -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <style>
        .container {
            margin: 20px;
        }
        .chart {
            width: 100%;
            min-height: 400px;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <!-- Navbar -->
    <div class="w3-top">
        <div class="w3-bar w3-black w3-card">
            <a class="w3-bar-item w3-button w3-padding-large w3-hide-medium w3-hide-large w3-right" href="javascript:void(0)" onclick="myFunction()" title="Toggle Navigation Menu"><i class="fa fa-bars"></i></a>
            <div class="w3-dropdown-hover w3-hide-small">
                <button class="w3-padding-large w3-button" title="More">MORE <i class="fa fa-caret-down"></i></button>     
                <div class="w3-dropdown-content w3-bar-block w3-card-4">
                    <a href="#contact" class="w3-bar-item w3-button w3-padding-large w3-hide-small">Else</a>
                </div>
            </div>
            <a href="/index.php" class="w3-bar-item w3-button w3-padding-large">Actual</a>
            <a href="/grafik.php" class="w3-bar-item w3-button w3-padding-large w3-hide-small">Grafik</a>
            <a href="javascript:void(0)" class="w3-padding-large w3-hover-red w3-hide-small w3-right"><i class="fa fa-search"></i></a>
        </div>
    </div>
    
    <div class="w3-content" style="max-width:2000px;margin-top:46px">
    </div>
    
    <div class="container">
        <label for="boardSelect">Select Board:</label>
        <select id="boardSelect" onchange="fetchAndRefreshCharts()">
            <option value="all">All Boards</option>
            <?php foreach ($boards as $board) { ?>
                <option value="<?php echo $board; ?>">Board <?php echo $board; ?></option>
            <?php } ?>
        </select>
        
        <?php
        // Fetch data for each parameter
        $parameters = ['Temperature', 'Humidity', 'Pressure', 'Light', 'Battery'];

        // Loop through each parameter
        foreach ($parameters as $parameter) {
            // Generate chart div with unique ID
            $chart_id = strtolower($parameter) . '_chart';
            echo "<div id='$chart_id' class='chart'></div>";
        }
        ?>
    </div>

    <script>
        // Function to fetch and update data for all charts
        function fetchAndRefreshCharts() {
            var board = document.getElementById('boardSelect').value;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_graph.php?board=' + board);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    updateCharts(data);
                }
            };
            xhr.send();
        }

        // Function to update charts with new data
        function updateCharts(data) {
            // Loop through each parameter
            <?php foreach ($parameters as $parameter) { ?>
                var chart_id = '<?php echo strtolower($parameter) . '_chart'; ?>';
                var parameterData = data['<?php echo $parameter; ?>'];
                Highcharts.chart(chart_id, {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: '<?php echo $parameter; ?>'
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            day: '%e of %b'
                        },
                        title: {
                            text: 'Time'
                        }
                    },
                    yAxis: {
                        title: {
                            text: '<?php echo $parameter; ?> Value'
                        }
                    },
                    series: parameterData
                });
            <?php } ?>
        }

        // Initial fetch and refresh of charts
        fetchAndRefreshCharts();

        // Refresh charts every 2 minutes
        setInterval(fetchAndRefreshCharts, 120000);
    </script>
</body>
</html>
