<?php
session_start();
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login • BSP</title>
    <link rel="stylesheet" href="css/styles.css" />
  </head>
  <body>
    <div class="container">
      <section class="hero">
        <h1>Admin Login</h1>
        <p>Approve or reject applications and payments.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Administrator Access</h2>
          <span class="badge">Admin</span>
        </div>
        <form class="grid" method="post" action="../src/admin/admin_login_process.php">
          <div class="field">
            <label for="admin-username">Username</label>
            <input id="admin-username" name="username" type="text" placeholder="Enter admin username" required />
          </div>
          <div class="field">
            <label for="admin-password">Password</label>
            <input id="admin-password" name="password" type="password" placeholder="Enter admin password" required />
          </div>
          <div class="button-row">
            <button class="btn" type="submit">Login</button>
            <a class="btn ghost" href="index.php">Back to User Login</a>
          </div>
        </form>
      </section>
    </div>
    <?php if ($error === "notfound") : ?>
      <script>
        alert("Admin account not found.");
      </script>
    <?php elseif ($error === "invalid") : ?>
      <script>
        alert("Incorrect password.");
      </script>
    <?php endif; ?>
  </body>
</html>
