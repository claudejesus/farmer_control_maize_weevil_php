<?php
require '../db.php';

$res = $conn->query("SELECT action FROM commands ORDER BY timestamp DESC LIMIT 1");
$cmd = $res->fetch_assoc();

echo json_encode($cmd ?: ["action" => "none"]);
?>
