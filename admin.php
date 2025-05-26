<?php
// require 'auth.php';
require 'db.php';

// Ensure only admin
// if ($_SESSION['user']['role'] !== 'admin') {
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

// Fetch farmers
$farmers = $conn->query("SELECT username FROM users WHERE role = 'farmer'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: #0d6efd;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Farm Admin</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-section="dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="sensor-data">
                                <i class="fas fa-thermometer-half me-2"></i>Sensor Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="farmers">
                                <i class="fas fa-users me-2"></i>Farmers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-section="controls">
                                <i class="fas fa-fan me-2"></i>Controls
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4" id="mainContent">
                <!-- Content will be loaded dynamically here -->
                <?php include 'sections/dashboard.php'; ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Global chart reference
    let chart;

    // Handle navigation clicks
    document.querySelectorAll('.nav-link[data-section]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active state
            document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Load section content
            const section = this.getAttribute('data-section');
            loadSection(section);
        });
    });

    // Function to load section content
    function loadSection(section) {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const mainContent = document.getElementById('mainContent');
        
        loadingSpinner.style.display = 'flex';
        
        fetch(`sections/${section}.php`)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
                
                // Initialize components based on loaded section
                if (section === 'dashboard' || section === 'sensor-data') {
                    initChart();
                }
                
                loadingSpinner.style.display = 'none';
            })
            .catch(error => {
                console.error('Error loading section:', error);
                loadingSpinner.style.display = 'none';
                mainContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load content. Please try again.
                    </div>
                `;
            });
    }

    // Initialize chart
    function initChart() {
        const ctx = document.getElementById('sensorChart')?.getContext('2d');
        if (!ctx) return;
        
        // Destroy previous chart if exists
        if (chart) {
            chart.destroy();
        }
        
        // Fetch and render chart data
        fetchAndRender();
        
        // Set up auto-refresh
        setInterval(fetchAndRender, 15000);
    }

    function sendCommand(action) {
        fetch('commands/save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action })
        })
        .then(res => res.json())
        .then(data => {
            const toast = new bootstrap.Toast(document.getElementById('commandToast'));
            const toastBody = document.getElementById('toastBody');
            
            if (data.success) {
                toastBody.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Success!</strong> Command sent successfully.
                    </div>
                `;
            } else {
                toastBody.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle text-danger me-2"></i>
                        <strong>Error:</strong> ${data.error || 'Failed to send command'}
                    </div>
                `;
            }
            
            toast.show();
        });
    }

    function fetchAndRender() {
        fetch('sensor_data_api.php')
            .then(response => response.json())
            .then(data => {
                const timestamps = data.map(item => item.timestamp);
                const temperatures = data.map(item => parseFloat(item.temperature));
                const humidities = data.map(item => parseFloat(item.humidity));

                updateTable(data);
                
                const ctx = document.getElementById('sensorChart')?.getContext('2d');
                if (!ctx) return;
                
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
                                    borderColor: 'rgba(220, 53, 69, 1)',
                                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                    tension: 0.3,
                                    fill: true,
                                    borderWidth: 2
                                },
                                {
                                    label: 'Humidity (%)',
                                    data: humidities,
                                    borderColor: 'rgba(13, 110, 253, 1)',
                                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                    tension: 0.3,
                                    fill: true,
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { 
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            scales: {
                                x: { 
                                    grid: {
                                        display: false
                                    },
                                    ticks: { 
                                        maxRotation: 60, 
                                        minRotation: 30 
                                    } 
                                },
                                y: {
                                    beginAtZero: false
                                }
                            },
                            interaction: {
                                mode: 'nearest',
                                axis: 'x',
                                intersect: false
                            }
                        }
                    });
                }
            });
    }

    function updateTable(data) {
        const tbody = document.querySelector('tbody');
        if (!tbody) return;
        
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

    <!-- Toast Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="commandToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">System Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastBody"></div>
        </div>
    </div>
</body>
</html>