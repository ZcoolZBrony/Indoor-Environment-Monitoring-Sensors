<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Allowed Boards</title>
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
        .form-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Allowed Boards</h2>
        <div class="form-container">
            <form id="boardForm">
                <label for="board">Board:</label>
                <input type="number" id="board" name="board" required>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <button type="submit">Add/Update Board</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Board</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="boardTableBody">
                <!-- Rows will be added here dynamically -->
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetchBoards();

            document.getElementById("boardForm").addEventListener("submit", function(event) {
                event.preventDefault();
                var formData = new FormData(event.target);
                fetch("manage_boards.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    fetchBoards();
                })
                .catch(error => console.error('Error:', error));
            });
        });

        function fetchBoards() {
            fetch("manage_boards.php")
                .then(response => response.json())
                .then(data => {
                    var tableBody = document.getElementById("boardTableBody");
                    tableBody.innerHTML = "";
                    data.forEach(function(row) {
                        var tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${row.Board}</td>
                            <td>${row.Name}</td>
                            <td><button onclick="deleteBoard(${row.Board})">Delete</button></td>
                        `;
                        tableBody.appendChild(tr);
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteBoard(board) {
            var formData = new FormData();
            formData.append("delete", "1");
            formData.append("board", board);
            fetch("manage_boards.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                fetchBoards();
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
