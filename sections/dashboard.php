<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Overview</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
        </div>
        <span class="badge bg-primary">
            <i class="fas fa-user-shield me-1"></i> Admin
        </span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Temperature</h6>
                        <h2 class="card-text">
                            <?= end($temperatures) ?> °C
                        </h2>
                    </div>
                    <i class="fas fa-thermometer-three-quarters fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Humidity</h6>
                        <h2 class="card-text">
                            <?= end($humidities) ?> %
                        </h2>
                    </div>
                    <i class="fas fa-tint fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Farmers</h6>
                        <h2 class="card-text">
                            <?= $farmers->num_rows ?>
                        </h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="card mb-4" id="sensor-data">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-chart-line me-2"></i>Sensor Data Chart
        </h5>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="sensorChart"></canvas>
        </div>
    </div>
</div>

<!-- Sensor Data Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">
            <i class="fas fa-table me-2"></i>Recent Sensor Data
        </h5>
        <span class="badge bg-primary">Last 50 readings</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
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
        </div>
    </div>
</div>