<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Latest Parameter Readings</title>
    <style>
        .container {
            margin: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .delete-button {
            margin-left: 20px;
            padding: 10px;
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
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
            <a href="/graph.php" class="w3-bar-item w3-button w3-padding-large w3-hide-small">Grafik</a>
            <a href="/editboardview.php" class="w3-bar-item w3-button w3-padding-large w3-hide-small">Board Edit</a>
            <a href="javascript:void(0)" class="w3-padding-large w3-hover-red w3-hide-small w3-right"><i class="fa fa-search"></i></a>
        </div>
    </div>

    <div class="w3-content" style="max-width:2000px;margin-top:46px">
      <h2>Home Monitoring</h2>
      <button class="delete-button" onclick="deleteBoards()">Delete Boards</button>
    </div>

    <div class="container" id="parameter-container">
        <!-- Latest parameter readings will be displayed here -->
    </div>

    <script>
        // Function to fetch and display latest parameter readings
        function fetchAndDisplayData() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("parameter-container").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "fetch_data.php", true);
            xhttp.send();
        }

        // Function to delete boards except 1, 2, 3, 4, 6
        function deleteBoards() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4) {
                    if (this.status == 200) {
                        alert("Delete complete");
                        fetchAndDisplayData(); // Refresh the data
                    } else {
                        alert("Delete failed");
                    }
                }
            };
            xhttp.open("GET", "delete_board.php", true);
            xhttp.send();
        }

        // Initial fetch and display of data
        fetchAndDisplayData();

        // Refresh data every 10 seconds
        setInterval(fetchAndDisplayData, 5000);
    </script>
</body>
</html>
