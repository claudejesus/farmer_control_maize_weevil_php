<?php
require '../db.php';

$result = $conn->query("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 50");
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
