<?php
require_once "api/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $device_id = trim($_POST["device_id"]);

    if ($full_name == "" || $email == "" || $password == "" || $device_id == "") {
        $error = "Please fill in all fields.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $full_name, $email, $password_hash);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            $deviceStmt = $conn->prepare("INSERT INTO devices (user_id, device_id) VALUES (?, ?)");
            $deviceStmt->bind_param("is", $user_id, $device_id);
            $deviceStmt->execute();

            $checkSetting = $conn->prepare("SELECT id FROM device_settings WHERE device_id = ?");
            $checkSetting->bind_param("s", $device_id);
            $checkSetting->execute();
            $settingResult = $checkSetting->get_result();

            if ($settingResult->num_rows == 0) {
                $insertSetting = $conn->prepare("
                    INSERT INTO device_settings 
                    (device_id, temp_threshold, air_threshold, light_threshold, upload_interval, alert_enabled, clap_enabled, output_mode)
                    VALUES (?, 32, 0, 3000, 10, 1, 1, 'AUTO')
                ");
                $insertSetting->bind_param("s", $device_id);
                $insertSetting->execute();
            }

            header("Location: login.php");
            exit;
        } else {
            $error = "Email or device ID already exists.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Sign Up | Smart Indoor</title>
  <style>
    body {
      margin: 0;
      font-family: Segoe UI, sans-serif;
      background: #07111f;
      color: white;
      display: grid;
      place-items: center;
      min-height: 100vh;
    }

    .box {
      width: 380px;
      background: #0f172a;
      padding: 30px;
      border-radius: 20px;
      border: 1px solid rgba(148,163,184,0.25);
      box-shadow: 0 20px 45px rgba(0,0,0,0.35);
    }

    h2 {
      margin-top: 0;
    }

    input {
      width: 100%;
      padding: 13px;
      margin: 10px 0;
      border-radius: 12px;
      border: 1px solid rgba(148,163,184,0.3);
      background: #020617;
      color: white;
      box-sizing: border-box;
    }

    button {
      width: 100%;
      padding: 13px;
      border: 0;
      border-radius: 12px;
      background: #0ea5e9;
      color: white;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
    }

    a {
      color: #38bdf8;
      text-decoration: none;
    }

    .error {
      color: #fecaca;
      background: rgba(239,68,68,0.18);
      padding: 10px;
      border-radius: 10px;
      margin-bottom: 12px;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>Create Account</h2>
    <p>Register your Smart Indoor device.</p>

    <?php if ($error != ""): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="full_name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="text" name="device_id" placeholder="Device ID e.g. INDOOR_001" required>
      <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>