<?php
// 1. Database Credentials
$servername = "localhost";
$dbname = "YOUR_DATABASE_NAME";
$username = "YOUR_DATABASE_USERNAME";
$password = "YOUR_DATABASE_PASSWORD";
// 2. Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. Check if device_id is provided in the URL
if (isset($_GET["device_id"])) {
    
    // 4. Retrieve and sanitize the GET data to prevent SQL injection
    $device_id = $conn->real_escape_string($_GET["device_id"]);
    $temperature = isset($_GET["temperature"]) ? $conn->real_escape_string($_GET["temperature"]) : 0;
    $humidity = isset($_GET["humidity"]) ? $conn->real_escape_string($_GET["humidity"]) : 0;
    $air_quality = isset($_GET["air_quality"]) ? $conn->real_escape_string($_GET["air_quality"]) : 0;
    $light_level = isset($_GET["light_level"]) ? $conn->real_escape_string($_GET["light_level"]) : 0;
    $system_status = isset($_GET["system_status"]) ? $conn->real_escape_string($_GET["system_status"]) : 'UNKNOWN';
    $output_status = isset($_GET["output_status"]) ? $conn->real_escape_string($_GET["output_status"]) : 'OFF';

    // 5. Insert the data into the database
    $sql = "INSERT INTO sensor_data (device_id, temperature, humidity, air_quality, light_level, system_status, output_status) 
            VALUES ('$device_id', '$temperature', '$humidity', '$air_quality', '$light_level', '$system_status', '$output_status')";

    if ($conn->query($sql) === TRUE) {
        echo "Data Inserted Successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "Error: No device_id received.";
}

$conn->close();
?>