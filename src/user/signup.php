<?php
session_start();
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Account • BSP Portal</title>
    <link rel="stylesheet" href="../../public/css/styles.css" />
  </head>
  <body>
    <div class="container">
      <section class="hero">
        <h1>Create Account</h1>
        <p>Sign up to begin your Adult Registration application.</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Account Details</h2>
          <span class="badge">Step 0</span>
        </div>
        <form id="signup-form" class="grid" method="post" action="signup_process.php">
          <div class="field">
            <label for="signup-email">Email</label>
            <input id="signup-email" name="email" type="email" placeholder="name@example.com" required />
          </div>
          <div class="field">
            <label for="signup-username">Username</label>
            <input id="signup-username" name="username" type="text" placeholder="Choose a username" required />
          </div>
          <div class="field">
            <label for="signup-password">Password</label>
            <input id="signup-password" name="password" type="password" placeholder="Create a password" required />
          </div>
          <div class="button-row">
            <button class="btn" type="submit">Next</button>
            <a class="btn ghost" href="../../public/index.php">Back to Login</a>
          </div>
        </form>
      </section>
    </div>
    <script src="../../public/js/app.js"></script>
    <?php if ($error === "exists") : ?>
      <script>
        alert("Account already exists.");
      </script>
    <?php endif; ?>
  </body>
</html>
