<?php
require 'auth.php';
require 'db.php';

$result = $conn->query("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 50");
$sensor_data = [];

while ($row = $result->fetch_assoc()) {
    $sensor_data[] = $row;
}

echo json_encode($sensor_data);
?>
