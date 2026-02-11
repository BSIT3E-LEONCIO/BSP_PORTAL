<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

$error = "";
$success = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $password = $_POST["password"] ?? "";
  $confirm = $_POST["confirm"] ?? "";

  if ($username === "" || $password === "" || $confirm === "") {
    $error = "All fields are required.";
  } elseif ($password !== $confirm) {
    $error = "Passwords do not match.";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $check = $conn->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
    $check->bind_param("s", $username);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();
    if ($exists) {
      $error = "Username already exists.";
    } else {
      $stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
      $stmt->bind_param("ss", $username, $hash);
      if ($stmt->execute()) {
        $success = true;
      } else {
        $error = "Failed to create admin account.";
      }
      $stmt->close();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Admin Account • BSP</title>
    <link rel="stylesheet" href="../../public/css/styles.css" />
  </head>
  <body>
    <div class="container">
      <section class="hero">
        <h1>Create Admin Account</h1>
        <p>Temporary page to create an admin account.</p>
      </section>
      <section class="card">
        <div class="form-title">
          <h2>New Admin</h2>
          <span class="badge">Admin</span>
        </div>
        <?php if ($success): ?>
          <div class="notice success">Admin account created! <a href="../../public/admin_login.php">Go to login</a></div>
        <?php else: ?>
          <?php if ($error): ?>
            <div class="notice error"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <form class="grid" method="post">
            <div class="field">
              <label for="username">Username</label>
              <input id="username" name="username" type="text" required />
            </div>
            <div class="field">
              <label for="password">Password</label>
              <input id="password" name="password" type="password" required />
            </div>
            <div class="field">
              <label for="confirm">Confirm Password</label>
              <input id="confirm" name="confirm" type="password" required />
            </div>
            <div class="button-row">
              <button class="btn" type="submit">Create Admin</button>
              <a class="btn ghost" href="../../public/admin_login.php">Back to Login</a>
            </div>
          </form>
        <?php endif; ?>
      </section>
    </div>
  </body>
</html>
