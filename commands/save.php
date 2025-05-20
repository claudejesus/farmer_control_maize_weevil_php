<?php
// require '../auth.php';
require '../db.php';

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'];

if (!in_array($action, ['fan_on', 'fan_off'])) {
    echo json_encode(["error" => "Invalid action"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO commands (action) VALUES (?)");
$stmt->bind_param("s", $action);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Insert failed"]);
}
?>
