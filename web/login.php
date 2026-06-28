<?php
require_once "api/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("
        SELECT users.id, users.full_name, users.password_hash, devices.device_id
        FROM users
        JOIN devices ON users.id = devices.user_id
        WHERE users.email = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["device_id"] = $user["device_id"];

            header("Location: dashboard.php");
            exit;
        }
    }

    $error = "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login | Smart Indoor</title>
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
    <h2>Login</h2>
    <p>Access your Smart Indoor dashboard.</p>

    <?php if ($error != ""): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>

    <p>No account yet? <a href="signup.php">Sign Up</a></p>
  </div>
</body>
</html>