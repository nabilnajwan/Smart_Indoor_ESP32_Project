<?php
require_once "db.php";
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["device_id"])) {
    echo json_encode([
        "error" => "Login required",
        "hardware_status" => "OFFLINE"
    ]);
    exit;
}

$device_id = $conn->real_escape_string($_SESSION["device_id"]);

$sql = "
    SELECT 
        sd.temperature,
        sd.humidity,
        sd.air_quality,
        sd.light_level,
        sd.system_status,
        sd.output_status,
        sd.created_at,

        COALESCE(ds.upload_interval, 10) AS upload_interval,
        COALESCE(ds.output_mode, 'AUTO') AS output_mode,
        COALESCE(ds.alert_enabled, 1) AS alert_enabled,
        COALESCE(ds.temp_threshold, 32) AS temp_threshold,
        COALESCE(ds.air_threshold, 0) AS air_threshold,
        COALESCE(ds.light_threshold, 3000) AS light_threshold,

        TIMESTAMPDIFF(SECOND, sd.created_at, NOW()) AS seconds_ago,

        CASE 
            WHEN TIMESTAMPDIFF(SECOND, sd.created_at, NOW()) <= GREATEST(COALESCE(ds.upload_interval, 10) * 3, 20)
            THEN 'ONLINE'
            ELSE 'OFFLINE'
        END AS hardware_status

    FROM sensor_data sd
    LEFT JOIN device_settings ds ON sd.device_id = ds.device_id
    WHERE sd.device_id = '$device_id'
    ORDER BY sd.id DESC
    LIMIT 1
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $row["temperature"] = (float)$row["temperature"];
    $row["humidity"] = (float)$row["humidity"];
    $row["air_quality"] = (int)$row["air_quality"];
    $row["light_level"] = (int)$row["light_level"];
    $row["upload_interval"] = (int)$row["upload_interval"];
    $row["alert_enabled"] = (int)$row["alert_enabled"];
    $row["temp_threshold"] = (float)$row["temp_threshold"];
    $row["air_threshold"] = (int)$row["air_threshold"];
    $row["light_threshold"] = (int)$row["light_threshold"];
    $row["seconds_ago"] = (int)$row["seconds_ago"];

    echo json_encode($row);
} else {
    echo json_encode([
        "error" => "No data found",
        "hardware_status" => "OFFLINE"
    ]);
}
?>