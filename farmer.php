<?php
// require 'auth.php';
require 'db.php';

// Ensure only farmers
// if ($_SESSION['user']['role'] !== 'farmer') {
//     header('Location: farmer.php');
//     exit;
// }

// Fetch sensor data
$result = $conn->query("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 50");
$sensor_data = [];
while ($row = $result->fetch_assoc()) {
    $sensor_data[] = $row;
}

// Chart data
$timestamps = array_column($sensor_data, 'timestamp');
$temperatures = array_column($sensor_data, 'temperature');
$humidities = array_column($sensor_data, 'humidity');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Farmer Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial; margin: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background-color: #006400; color: white; }
    </style>
</head>
<body>

<h2>Welcome, <?= $_SESSION['user']['username'] ?> (Farmer)</h2>
<a href="logout.php">Logout</a>

<h3>Sensor Data</h3>
<table>
    <thead>
        <tr>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sensor_data as $row): ?>
        <tr>
            <td><?= $row['temperature'] ?></td>
            <td><?= $row['humidity'] ?></td>
            <td><?= $row['timestamp'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Sensor Chart</h3>
<canvas id="sensorChart" height="100"></canvas>

<script>
let chart;
let ctx = document.getElementById('sensorChart').getContext('2d');

// Initial render
fetchAndRender();

setInterval(fetchAndRender, 15000); // auto-refresh every 15 seconds

function fetchAndRender() {
    fetch('sensor_data_api.php')
        .then(response => response.json())
        .then(data => {
            const timestamps = data.map(item => item.timestamp);
            const temperatures = data.map(item => parseFloat(item.temperature));
            const humidities = data.map(item => parseFloat(item.humidity));

            updateTable(data);
            if (chart) {
                chart.data.labels = timestamps;
                chart.data.datasets[0].data = temperatures;
                chart.data.datasets[1].data = humidities;
                chart.update();
            } else {
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: timestamps,
                        datasets: [
                            {
                                label: 'Temperature (°C)',
                                data: temperatures,
                                borderColor: 'red',
                                backgroundColor: 'rgba(255,0,0,0.2)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Humidity (%)',
                                data: humidities,
                                borderColor: 'blue',
                                backgroundColor: 'rgba(0,0,255,0.2)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            x: { ticks: { maxRotation: 60, minRotation: 30 } }
                        }
                    }
                });
            }
        });
}

function updateTable(data) {
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';
    data.forEach(row => {
        tbody.innerHTML += `
            <tr>
                <td>${row.temperature}</td>
                <td>${row.humidity}</td>
                <td>${row.timestamp}</td>
            </tr>`;
    });
}
</script>



</body>
</html>
