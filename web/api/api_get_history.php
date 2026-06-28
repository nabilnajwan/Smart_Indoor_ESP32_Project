<?php
require_once "db.php";
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["device_id"])) {
    echo json_encode([]);
    exit;
}

$device_id = $conn->real_escape_string($_SESSION["device_id"]);
$limit = isset($_GET["limit"]) ? intval($_GET["limit"]) : 20;

if ($limit <= 0) {
    $limit = 20;
}

if ($limit > 100) {
    $limit = 100;
}

$sql = "
    SELECT temperature, humidity, air_quality, light_level, system_status, output_status, created_at
    FROM sensor_data
    WHERE device_id = '$device_id'
    ORDER BY id DESC
    LIMIT $limit
";

$result = $conn->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>