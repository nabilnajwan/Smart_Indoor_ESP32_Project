<?php
require_once "db.php";
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["device_id"])) {
    echo json_encode([
        "success" => false,
        "error" => "Login required"
    ]);
    exit;
}

$device_id = $conn->real_escape_string($_SESSION["device_id"]);

$check = $conn->query("SELECT id FROM device_settings WHERE device_id = '$device_id' LIMIT 1");

if ($check->num_rows == 0) {
    $conn->query("
        INSERT INTO device_settings 
        (device_id, temp_threshold, air_threshold, light_threshold, upload_interval, alert_enabled, clap_enabled, output_mode)
        VALUES 
        ('$device_id', 32, 0, 3000, 10, 1, 1, 'AUTO')
    ");
}

$allowed = [
    "temp_threshold",
    "air_threshold",
    "light_threshold",
    "upload_interval",
    "alert_enabled",
    "clap_enabled",
    "output_mode"
];

$updates = [];

foreach ($allowed as $field) {
    if (isset($_POST[$field]) || isset($_GET[$field])) {
        $value = $_POST[$field] ?? $_GET[$field];
        $value = $conn->real_escape_string($value);

        if ($field == "output_mode") {
            $value = strtoupper($value);

            if (!in_array($value, ["AUTO", "ON", "OFF"])) {
                echo json_encode([
                    "success" => false,
                    "error" => "Invalid output_mode"
                ]);
                exit;
            }

            $updates[] = "$field = '$value'";
        } else {
            $updates[] = "$field = '$value'";
        }
    }
}

if (count($updates) == 0) {
    echo json_encode([
        "success" => false,
        "error" => "No settings provided"
    ]);
    exit;
}

$sql = "UPDATE device_settings SET " . implode(", ", $updates) . " WHERE device_id = '$device_id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "success" => true,
        "message" => "Settings updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
}
?>