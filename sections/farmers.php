<?php
require '../db.php';
$farmers = $conn->query("SELECT username, created_at FROM users WHERE role = 'farmer'");
$farmer_list = $farmers->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Farmer Management</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <input type="text" id="farmerSearch" class="form-control" placeholder="Search farmers by username...">
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-user-plus me-2"></i>Register New Farmer
                </h5>
            </div>
            <div class="card-body">
                <form id="registerFarmerForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-users me-2"></i>Registered Farmers
                </h5>
            </div>
            <div class="card-body">
                <!-- <div class="list-group">
                    <?php foreach ($farmer_list as $farmer): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user me-2"></i><?= $farmer['username'] ?>
                            <small class="d-block text-muted">Registered: <?= $farmer['created_at'] ?></small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger delete-farmer" data-username="<?= $farmer['username'] ?>">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div> -->
                <div id="farmerList">
                    <ul class="list-group">
                        <?php foreach ($farmer_list as $f): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="farmer-name"><?= $f['username'] ?></span>
                                <button class="btn btn-sm btn-danger delete-farmer" data-username="<?= $f['username'] ?>">Delete</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerFarmerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('../commands/register_farmer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const toast = new bootstrap.Toast(document.getElementById('toast'));
        document.getElementById('toastBody').innerHTML = data.success ? 
            '<i class="fas fa-check-circle text-success me-2"></i>' + data.message :
            '<i class="fas fa-times-circle text-danger me-2"></i>' + data.message;
        toast.show();
        
        if (data.success) {
            this.reset();
            // Reload the farmers section
            loadSection('farmers');
        }
    });
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
</script>