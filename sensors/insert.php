<?php
require '../db.php';

$data = json_decode(file_get_contents('php://input'), true);

$temp = $data['temperature'];
$humid = $data['humidity'];

if ($temp && $humid) {
    $stmt = $conn->prepare("INSERT INTO sensor_data (temperature, humidity) VALUES (?, ?)");
    $stmt->bind_param("dd", $temp, $humid);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "DB insert failed"]);
    }
} else {
    echo json_encode(["error" => "Missing data"]);
}
?>
