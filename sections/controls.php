<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">System Controls</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-fan me-2"></i>Fan Control
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" onclick="sendCommand('fan_on')">
                        <i class="fas fa-power-off me-2"></i> Turn Fan ON
                    </button>
                    <button class="btn btn-danger btn-lg" onclick="sendCommand('fan_off')">
                        <i class="fas fa-power-off me-2"></i> Turn Fan OFF
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-history me-2"></i>Recent Commands
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Command</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="commandHistory">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load command history
fetch('../commands/history.php')
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('commandHistory');
        tbody.innerHTML = data.map(cmd => `
            <tr>
                <td>${cmd.action}</td>
                <td><span class="badge ${cmd.status === 'success' ? 'bg-success' : 'bg-danger'}">${cmd.status}</span></td>
                <td>${new Date(cmd.timestamp).toLocaleString()}</td>
            </tr>
        `).join('');
    });

// Update command history when sending new commands
function sendCommand(action) {
    fetch('../commands/save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action })
    })
    .then(response => response.json())
    .then(data => {
        const toast = new bootstrap.Toast(document.getElementById('toast'));
        document.getElementById('toastBody').innerHTML = data.success ? 
            '<i class="fas fa-check-circle text-success me-2"></i>Command sent!' :
            '<i class="fas fa-times-circle text-danger me-2"></i>Error: ' + data.error;
        toast.show();
        
        // Refresh command history
        fetch('../commands/history.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('commandHistory');
                tbody.innerHTML = data.map(cmd => `
                    <tr>
                        <td>${cmd.action}</td>
                        <td><span class="badge ${cmd.status === 'success' ? 'bg-success' : 'bg-danger'}">${cmd.status}</span></td>
                        <td>${new Date(cmd.timestamp).toLocaleString()}</td>
                    </tr>
                `).join('');
            });
    });
}
</script>