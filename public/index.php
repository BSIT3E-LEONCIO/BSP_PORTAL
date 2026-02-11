<?php
session_start();
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Boyscout of the Philippines Portal</title>
    <link rel="stylesheet" href="css/styles.css" />
  </head>
  <body>
    <div class="container">
      <section class="hero">
        <h1>Boyscout of the Philippines Portal</h1>
        <p>Welcome to the Adult Registration and Payment Verification portal.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Login</h2>
          <span class="badge">Main Page</span>
        </div>
        <form id="login-form" class="grid" method="post" action="../src/user/login_process.php">
          <div class="field">
            <label for="login-username">Username</label>
            <input id="login-username" name="username" type="text" placeholder="Enter your username" required />
          </div>
          <div class="field">
            <label for="login-password">Password</label>
            <input id="login-password" name="password" type="password" placeholder="Enter your password" required />
          </div>
          <div class="button-row">
            <button class="btn" type="submit">Login</button>
            <a class="btn secondary" href="../src/user/signup.php">Create</a>
            <button class="small-link" type="button">Forgot password?</button>
          </div>
        </form>
      </section>

      <p class="footer">Boy Scouts of the Philippines • Portal Prototype</p>
    </div>
    <script src="js/app.js"></script>
    <?php if ($error === "notfound") : ?>
      <script>
        alert("Account does not exist.");
      </script>
    <?php elseif ($error === "invalid") : ?>
      <script>
        alert("Incorrect password.");
      </script>
    <?php elseif ($error === "blocked") : ?>
      <script>
        const reason = <?php echo json_encode($_GET["reason"] ?? "Blocked by administrator."); ?>;
        const start = <?php echo json_encode($_GET["start"] ?? ""); ?>;
        const end = <?php echo json_encode($_GET["end"] ?? ""); ?>;
        let message = "Your account is temporarily blocked.";
        if (reason) message += "\nReason: " + reason;
        if (start && end) message += "\nBlocked: " + start + " to " + end;
        alert(message);
      </script>
    <?php endif; ?>
  </body>
</html>
