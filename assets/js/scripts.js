// Global variables
let chart;
let autoRefreshInterval;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Handle navigation clicks
    document.querySelectorAll('.nav-link[data-section]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            loadSection(this.dataset.section);
        });
    });

    // Initialize dashboard
    if (document.getElementById('sensorChart')) {
        initChart();
    }
});

// Load section content
function loadSection(section) {
    const spinner = document.getElementById('loadingSpinner');
    const mainContent = document.getElementById('mainContent');
    
    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    document.querySelector(`.nav-link[data-section="${section}"]`).classList.add('active');
    
    spinner.style.display = 'flex';
    
    fetch(`sections/${section}.php`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            mainContent.innerHTML = html;
            
            // Initialize components based on loaded section
            if (section === 'dashboard' || section === 'sensor-data') {
                initChart();
            }
            
            // Clear any existing auto-refresh
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            
            // Set up auto-refresh for sensor data sections
            if (section === 'dashboard' || section === 'sensor-data') {
                autoRefreshInterval = setInterval(fetchAndRender, 15000);
            }
        })
        .catch(error => {
            console.error('Error loading section:', error);
            mainContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load content. Please try again.
                </div>
            `;
        })
        .finally(() => {
            spinner.style.display = 'none';
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

    // Initial fetch and render
    fetchAndRender();
}

// Fetch and render chart data
function fetchAndRender() {
    fetch('sensor_data_api.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            updateSensorTable(data);
            renderChart(data);
        })
        .catch(error => {
            console.error('Error fetching sensor data:', error);
        });
}

// Update sensor data table
function updateSensorTable(data) {
    const tbody = document.querySelector('#sensorTable tbody');
    if (!tbody) return;
    
    tbody.innerHTML = data.slice(0, 10).map(row => `
        <tr>
            <td>${row.temperature.toFixed(1)}°C</td>
            <td>${row.humidity.toFixed(1)}%</td>
            <td>${new Date(row.timestamp).toLocaleString()}</td>
        </tr>
    `).join('');
}

// Render chart with data
function renderChart(data) {
    const ctx = document.getElementById('sensorChart')?.getContext('2d');
    if (!ctx) return;
    
    const labels = data.map(item => new Date(item.timestamp).toLocaleTimeString());
    const temps = data.map(item => item.temperature);
    const humids = data.map(item => item.humidity);
    
    if (chart) {
        chart.data.labels = labels;
        chart.data.datasets[0].data = temps;
        chart.data.datasets[1].data = humids;
        chart.update();
    } else {
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Temperature (°C)',
                        data: temps,
                        borderColor: 'rgba(220, 53, 69, 1)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.3,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Humidity (%)',
                        data: humids,
                        borderColor: 'rgba(13, 110, 253, 1)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.3,
                        fill: true,
                        borderWidth: 2
                    }
                ]
            },
            options: getChartOptions()
        });
    }
}

// Get chart options
function getChartOptions() {
    return {
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
                intersect: false,
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y.toFixed(1)}${context.dataset.label.includes('Temperature') ? '°C' : '%'}`;
                    }
                }
            }
        },
        scales: {
            x: { 
                grid: { display: false },
                ticks: { 
                    maxRotation: 60, 
                    minRotation: 30,
                    callback: function(value, index) {
                        // Only show every 5th label for better readability
                        return index % 5 === 0 ? this.getLabelForValue(value) : '';
                    }
                } 
            },
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return value + (this.scale.id === 'y' ? '°C' : '%');
                    }
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    };
}

// Send control command
function sendCommand(action) {
    const spinner = document.getElementById('loadingSpinner');
    spinner.style.display = 'flex';
    
    fetch('commands/save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        showToast(data.success ? 'success' : 'error', 
                 data.success ? 'Command executed successfully' : data.error || 'Command failed');
        
        // Refresh command history if on controls page
        if (document.getElementById('commandHistory')) {
            loadCommandHistory();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Failed to send command: ' + error.message);
    })
    .finally(() => {
        spinner.style.display = 'none';
    });
}

// Load command history
function loadCommandHistory() {
    fetch('commands/history.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('commandHistory');
            if (!tbody) return;
            
            tbody.innerHTML = data.map(cmd => `
                <tr>
                    <td>${cmd.action.replace('_', ' ').toUpperCase()}</td>
                    <td><span class="badge ${cmd.status === 'success' ? 'bg-success' : 'bg-danger'}">${cmd.status.toUpperCase()}</span></td>
                    <td>${new Date(cmd.timestamp).toLocaleString()}</td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Error loading command history:', error);
        });
}

// Show toast notification
function showToast(type, message) {
    const toastEl = document.getElementById('toast');
    const toastBody = document.getElementById('toastBody');
    
    if (!toastEl || !toastBody) return;
    
    const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
    
    // Set icon based on type
    const icon = type === 'success' ? 
        '<i class="fas fa-check-circle text-success me-2"></i>' :
        '<i class="fas fa-exclamation-circle text-danger me-2"></i>';
    
    toastBody.innerHTML = `${icon}${message}`;
    toast.show();
}

// Register farmer form handler
document.addEventListener('submit', function(e) {
    if (e.target.id === 'registerFarmerForm') {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const spinner = document.getElementById('loadingSpinner');
        
        spinner.style.display = 'flex';
        
        fetch('commands/register_farmer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            showToast(data.success ? 'success' : 'error', data.message);
            
            if (data.success) {
                form.reset();
                // Reload farmers section if currently viewing it
                if (document.querySelector('.nav-link.active').dataset.section === 'farmers') {
                    loadSection('farmers');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Failed to register farmer');
        })
        .finally(() => {
            spinner.style.display = 'none';
        });
    }
});

document.querySelectorAll('.delete-farmer').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this farmer?')) {
            const username = this.dataset.username;
            fetch(`../commands/delete_farmer.php?username=${encodeURIComponent(username)}`)
                .then(response => response.json())
                .then(data => {
                    const toast = new bootstrap.Toast(document.getElementById('toast'));
                    document.getElementById('toastBody').innerHTML = data.success ? 
                        '<i class="fas fa-check-circle text-success me-2"></i>' + data.message :
                        '<i class="fas fa-times-circle text-danger me-2"></i>' + data.message;
                    toast.show();

                    if (data.success) {
                        // Reload the farmers section
                        loadSection('farmers');
                    }
                });
        }
    });
});
