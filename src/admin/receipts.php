<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

$paymentJoin = "LEFT JOIN (SELECT p1.* FROM payments p1 INNER JOIN (SELECT user_id, MAX(uploaded_at) AS max_uploaded_at FROM payments GROUP BY user_id) p2 ON p1.user_id = p2.user_id AND p1.uploaded_at = p2.max_uploaded_at) p ON p.user_id = u.id";
$receipts = $conn->query("SELECT u.id AS user_id, u.email, u.username, u.membership_expires_at, a.surname, a.firstname, a.council, a.serve_sub, a.status AS app_status, p.status AS payment_status, r.id AS receipt_id, r.receipt_no, r.aar_no, r.paid_for, r.amount, r.payment_method, r.released_at, r.note FROM users u LEFT JOIN applications a ON a.user_id = u.id $paymentJoin LEFT JOIN receipts r ON r.user_id = u.id WHERE a.status = 'approved' AND p.status = 'approved' ORDER BY r.released_at DESC, a.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Receipts • BSP</title>
  <link rel="stylesheet" href="../../public/css/styles.css" />
  <style>
    body { display: flex; min-height: 100vh; margin: 0; }
    .sidebar { width: 260px; background: #256029; color: #fff; padding: 32px 0 0 0; }
    .sidebar .logo { text-align: center; margin-bottom: 32px; }
    .sidebar nav { display: flex; flex-direction: column; }
    .sidebar nav a, .sidebar nav .nav-section { color: #fff; text-decoration: none; padding: 12px 32px; display: block; }
    .sidebar nav .active, .sidebar nav a:hover { background: #1b4d1a; }
    .sidebar nav .nav-section { font-weight: bold; margin-top: 24px; }
    .main { flex: 1; background: #f7f8fa; padding: 40px 48px; }
    .registrations-table { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 14px 12px; text-align: left; vertical-align: top; }
    th { color: #888; font-weight: 600; border-bottom: 2px solid #eee; }
    tr:not(:last-child) td { border-bottom: 1px solid #f0f0f0; }
    .btn { border: none; border-radius: 6px; padding: 6px 12px; font-weight: 500; cursor: pointer; }
    .btn.View { background: #1976d2; color: #fff; }
    .btn.Pdf { background: #5e35b1; color: #fff; }
    .btn.Print { background: #43a047; color: #fff; }
    .tag { display: inline-block; padding: 4px 10px; border-radius: 6px; background: #eee; color: #555; font-size: 0.9em; }
  </style>
</head>
<body>
  <aside class="sidebar" style="display:flex;flex-direction:column;height:100vh;">
    <div>
      <div class="logo">
        <img src="../../public/assets/bsp-logo.png" alt="BSP Logo" width="80" />
      </div>
      <nav>
        <a href="admin.php?view=dashboard">Dashboard</a>
        <div class="nav-section">AAR Registrations</div>
        <a href="admin.php?view=pending">Pending</a>
        <a href="admin.php?view=approved">Approved</a>
        <a href="admin.php?view=rejected">Rejected</a>
        <div class="nav-section">Admin Tools</div>
        <a href="manage_users.php">Manage Users</a>
        <a class="active" href="receipts.php">Receipts</a>
      </nav>
    </div>
    <div style="margin-top:auto;padding:24px 32px;">
      <a href="admin_logout.php" class="btn" style="width:100%;background:#e53935;color:#fff;text-align:center;">Logout</a>
    </div>
  </aside>
  <main class="main">
    <div class="registrations-table">
      <h3 style="padding: 24px 24px 0 24px;">Receipts</h3>
      <table>
        <thead>
          <tr>
            <th>Applicant</th>
            <th>Receipt No</th>
            <th>AAR No</th>
            <th>Paid For</th>
            <th>Amount</th>
            <th>Payment Method</th>
            <th>Released</th>
            <th>Membership Expiration</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($receipts && $row = $receipts->fetch_assoc()): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars(($row['surname'] ?? '') . ' ' . ($row['firstname'] ?? '')) ?></strong><br>
              <span style="color:#888;"><?= htmlspecialchars($row['email'] ?? '') ?></span>
            </td>
            <td><?= htmlspecialchars($row['receipt_no'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['aar_no'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['paid_for'] ?? 'AAR Form') ?></td>
            <td><?= isset($row['amount']) ? 'PHP ' . number_format((float)$row['amount'], 2) : 'PHP 0.00' ?></td>
            <td><?= htmlspecialchars($row['payment_method'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($row['released_at'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['membership_expires_at'] ?? '-') ?></td>
            <td>
              <?php if (!empty($row['receipt_id'])): ?>
                <a class="btn View" href="receipt_view.php?receipt_id=<?= (int)$row['receipt_id'] ?>" target="_blank">View</a>
                <a class="btn Pdf" href="receipt_view.php?receipt_id=<?= (int)$row['receipt_id'] ?>&print=1" target="_blank">Save PDF</a>
                <a class="btn Print" href="receipt_view.php?receipt_id=<?= (int)$row['receipt_id'] ?>&print=1" target="_blank">Print</a>
              <?php else: ?>
                <span class="tag">Pending auto-generation</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
