<?php
// 1. Set the header to tell the ESP32 to expect JSON data
header('Content-Type: application/json');

// 2. Database Credentials
$servername = "localhost";
$dbname = "YOUR_DATABASE_NAME";
$username = "YOUR_DATABASE_USERNAME";
$password = "YOUR_DATABASE_PASSWORD"; 

// 3. Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// 4. Check if the ESP32 sent a device_id
if (isset($_GET["device_id"])) {
    $device_id = $conn->real_escape_string($_GET["device_id"]);

$sql = "SELECT temp_threshold, air_threshold, light_threshold, upload_interval, alert_enabled, clap_enabled, output_mode 
        FROM device_settings 
        WHERE device_id = '$device_id' LIMIT 1";
            
    $result = $conn->query($sql);

    // 6. If settings exist, format them as JSON and send them back
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Ensure numeric values are sent as numbers, not strings
        $row['temp_threshold'] = (float)$row['temp_threshold'];
        $row['air_threshold'] = (int)$row['air_threshold'];
        $row['light_threshold'] = (int)$row['light_threshold'];
        $row['upload_interval'] = (int)$row['upload_interval'];
        $row['alert_enabled'] = (int)$row['alert_enabled'];
        $row['clap_enabled'] = isset($row['clap_enabled']) ? (int)$row['clap_enabled'] : 1;
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "No settings found for this device"]);
    }
} else {
    echo json_encode(["error" => "Missing device_id"]);
}

$conn->close();
?>
