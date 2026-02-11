<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

$paymentJoin = "LEFT JOIN (SELECT p1.* FROM payments p1 INNER JOIN (SELECT user_id, MAX(uploaded_at) AS max_uploaded_at FROM payments GROUP BY user_id) p2 ON p1.user_id = p2.user_id AND p1.uploaded_at = p2.max_uploaded_at) p ON p.user_id = u.id";
$users = $conn->query("SELECT u.id, u.email, u.username, u.membership_expires_at, u.blocked_start, u.blocked_end, u.blocked_reason, a.status AS app_status, p.status AS payment_status FROM users u LEFT JOIN applications a ON a.user_id = u.id $paymentJoin ORDER BY u.created_at DESC");
$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Users • BSP</title>
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
    .badge { border-radius: 8px; padding: 4px 12px; font-size: 0.95em; display: inline-block; }
    .badge.Pending { background: #e3e6ff; color: #2a3eb1; border: 1px solid #b3b8e6; }
    .badge.Approved { background: #e6ffed; color: #389e0d; }
    .badge.Rejected { background: #ffebee; color: #b53e3e; }
    .badge.NotSubmitted { background: #f0f0f0; color: #666; }
    .badge.Blocked { background: #ffe4e1; color: #b71c1c; }
    .status-stack { display: flex; flex-direction: column; gap: 6px; }
    .btn { border: none; border-radius: 6px; padding: 6px 12px; font-weight: 500; cursor: pointer; }
    .btn.Block { background: #f9a825; color: #000; }
    .btn.Unblock { background: #43a047; color: #fff; }
    .btn.Edit { background: #1976d2; color: #fff; }
    .btn.Cancel { background: #777; color: #fff; }
    .inline-form { margin-top: 8px; display: none; gap: 8px; flex-wrap: wrap; }
    .inline-form input { padding: 6px 8px; border: 1px solid #ddd; border-radius: 6px; }
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
        <a class="active" href="manage_users.php">Manage Users</a>
        <a href="receipts.php">Receipts</a>
      </nav>
    </div>
    <div style="margin-top:auto;padding:24px 32px;">
      <a href="admin_logout.php" class="btn" style="width:100%;background:#e53935;color:#fff;text-align:center;">Logout</a>
    </div>
  </aside>
  <main class="main">
    <div class="registrations-table">
      <h3 style="padding: 24px 24px 0 24px;">Manage Users</h3>
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Application Status</th>
            <th>Membership</th>
            <th>Expiration</th>
            <th>Block Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($users && $row = $users->fetch_assoc()): ?>
          <?php
            $appStatus = $row['app_status'] ?? 'not-submitted';
            $paymentStatus = $row['payment_status'] ?? 'not-submitted';
            $appLabel = $appStatus === 'not-submitted' ? 'Not Submitted' : ucfirst($appStatus);
            $appClass = $appStatus === 'not-submitted' ? 'NotSubmitted' : ucfirst($appStatus);
            $paymentLabel = $paymentStatus === 'not-submitted' ? 'Not Submitted' : ucfirst($paymentStatus);
            $paymentClass = $paymentStatus === 'not-submitted' ? 'NotSubmitted' : ucfirst($paymentStatus);
            $membershipStatus = ($appStatus === 'approved' && $paymentStatus === 'approved') ? 'NEW!' : 'Pending';
            $expiresAt = $row['membership_expires_at'] ?? '-';
            $blockedActive = $row['blocked_start'] && $row['blocked_end'] && $today >= $row['blocked_start'] && $today <= $row['blocked_end'];
          ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars($row['username']) ?></strong><br>
              <span style="color:#888;"><?= htmlspecialchars($row['email']) ?></span>
            </td>
            <td>
              <div class="status-stack">
                <span class="badge <?= $appClass ?>">AAR Form <?= $appLabel ?></span>
                <span class="badge <?= $paymentClass ?>">Payment <?= $paymentLabel ?></span>
              </div>
            </td>
            <td>
              <span class="badge <?= $membershipStatus === 'NEW!' ? 'Approved' : 'Pending' ?>"><?= $membershipStatus ?></span>
            </td>
            <td><?= htmlspecialchars($expiresAt) ?></td>
            <td>
              <?php if ($blockedActive): ?>
                <span class="badge Blocked">Blocked</span><br>
                <small><?= htmlspecialchars($row['blocked_start'] . ' to ' . $row['blocked_end']) ?></small><br>
                <small><?= htmlspecialchars($row['blocked_reason'] ?? '') ?></small>
              <?php else: ?>
                <span class="badge Approved">Active</span>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn Block" data-toggle="block-<?= (int)$row['id'] ?>">Temporary Block</button>
              <?php if ($blockedActive): ?>
                <form method="post" action="admin_action.php" style="display:inline;">
                  <input type="hidden" name="action" value="unblock_user" />
                  <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>" />
                  <input type="hidden" name="redirect" value="manage_users" />
                  <button class="btn Unblock" type="submit">Unblock</button>
                </form>
              <?php endif; ?>
              <button class="btn Edit" data-edit="<?= (int)$row['id'] ?>" data-username="<?= htmlspecialchars($row['username']) ?>" data-email="<?= htmlspecialchars($row['email']) ?>">Edit</button>
              <form method="post" action="admin_action.php" class="inline-form" id="block-<?= (int)$row['id'] ?>">
                <input type="hidden" name="action" value="block_user" />
                <input type="hidden" name="user_id" value="<?= (int)$row['id'] ?>" />
                <input type="hidden" name="redirect" value="manage_users" />
                <input type="date" name="blocked_start" required />
                <input type="date" name="blocked_end" required />
                <input type="text" name="blocked_reason" placeholder="Reason (optional)" />
                <button class="btn Block" type="submit">Save Block</button>
                <button class="btn Cancel" type="button" data-cancel="block-<?= (int)$row['id'] ?>">Cancel</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>

  <div id="editUserModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:1200;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:24px;border-radius:12px;max-width:460px;width:92vw;position:relative;">
      <button id="closeEditUserModal" style="position:absolute;top:12px;right:16px;font-size:1.5em;background:none;border:none;cursor:pointer;">&times;</button>
      <h3>Edit User</h3>
      <form method="post" action="admin_action.php" id="editUserForm">
        <input type="hidden" name="action" value="edit_user" />
        <input type="hidden" name="user_id" id="editUserId" value="" />
        <input type="hidden" name="redirect" value="manage_users" />
        <div style="display:grid;gap:12px;">
          <input type="text" name="username" id="editUsername" placeholder="Username" required />
          <input type="email" name="email" id="editEmail" placeholder="Email" required />
          <input type="password" name="password" id="editPassword" placeholder="New password (optional)" />
        </div>
        <div style="margin-top:16px;display:flex;gap:8px;">
          <button class="btn Edit" type="submit">Save</button>
          <button class="btn Cancel" type="button" id="cancelEditUser">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.querySelectorAll('[data-toggle]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-toggle'));
        if (target) {
          target.style.display = target.style.display === 'flex' ? 'none' : 'flex';
        }
      });
    });
    document.querySelectorAll('[data-cancel]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-cancel'));
        if (target) {
          target.style.display = 'none';
        }
      });
    });

    const editModal = document.getElementById('editUserModal');
    const editUserId = document.getElementById('editUserId');
    const editUsername = document.getElementById('editUsername');
    const editEmail = document.getElementById('editEmail');
    const editPassword = document.getElementById('editPassword');

    document.querySelectorAll('[data-edit]').forEach(btn => {
      btn.addEventListener('click', () => {
        editUserId.value = btn.getAttribute('data-edit');
        editUsername.value = btn.getAttribute('data-username') || '';
        editEmail.value = btn.getAttribute('data-email') || '';
        editPassword.value = '';
        editModal.style.display = 'flex';
      });
    });

    document.getElementById('closeEditUserModal').onclick = function() {
      editModal.style.display = 'none';
    };
    document.getElementById('cancelEditUser').onclick = function() {
      editModal.style.display = 'none';
    };
    window.addEventListener('click', function(event) {
      if (event.target === editModal) {
        editModal.style.display = 'none';
      }
    });
  </script>
</body>
</html>
