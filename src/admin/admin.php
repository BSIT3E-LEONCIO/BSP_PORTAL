<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

// Dashboard stats
$paymentJoin = "LEFT JOIN (SELECT p1.* FROM payments p1 INNER JOIN (SELECT user_id, MAX(uploaded_at) AS max_uploaded_at FROM payments GROUP BY user_id) p2 ON p1.user_id = p2.user_id AND p1.uploaded_at = p2.max_uploaded_at) p ON p.user_id = a.user_id";
$pendingCount = $conn->query("SELECT COUNT(*) AS cnt FROM applications a $paymentJoin WHERE a.status != 'rejected' AND (p.status IS NULL OR p.status != 'rejected') AND (a.status = 'pending' OR p.status = 'pending' OR p.status IS NULL)")->fetch_assoc()['cnt'];
$approvedCount = $conn->query("SELECT COUNT(*) AS cnt FROM applications a $paymentJoin WHERE a.status = 'approved' AND p.status = 'approved'")->fetch_assoc()['cnt'];
$rejectedCount = $conn->query("SELECT COUNT(*) AS cnt FROM applications a $paymentJoin WHERE a.status = 'rejected' OR p.status = 'rejected'")->fetch_assoc()['cnt'];
$totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];

// Recent Decisions
$recent = $conn->query("SELECT a.*, u.email FROM applications a JOIN users u ON a.user_id = u.id $paymentJoin WHERE a.status = 'approved' AND p.status = 'approved' ORDER BY a.updated_at DESC LIMIT 1")->fetch_assoc();

$view = $_GET['view'] ?? 'dashboard';
$registrations = null;
if (in_array($view, ['pending', 'approved', 'rejected'])) {
  $baseSelect = "SELECT a.*, u.email, u.username, p.id AS payment_id, p.status AS payment_status FROM applications a JOIN users u ON a.user_id = u.id $paymentJoin";
  if ($view === 'approved') {
    $where = "WHERE a.status = 'approved' AND p.status = 'approved'";
  } elseif ($view === 'rejected') {
    $where = "WHERE a.status = 'rejected' AND (p.status IS NULL OR p.status != 'pending') OR (p.status = 'rejected' AND a.status = 'approved')";
  } else {
    $where = "WHERE (a.status = 'pending' OR (a.status = 'approved' AND (p.status = 'pending' OR p.status IS NULL)) OR (a.status = 'pending' AND p.status = 'rejected'))";
  }
  $registrations = $conn->query("$baseSelect $where ORDER BY a.created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard • BSP</title>
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
    .dashboard-cards { display: flex; gap: 32px; margin-bottom: 32px; }
    .dashboard-card { background: #fff; border-radius: 12px; padding: 24px 32px; min-width: 180px; box-shadow: 0 2px 8px #0001; }
    .dashboard-card h3 { margin: 0 0 8px 0; font-size: 1.1em; color: #888; }
    .dashboard-card .count { font-size: 2.2em; font-weight: bold; }
    .recent-decisions { background: #fff; border-radius: 12px; padding: 24px 32px; margin-bottom: 32px; box-shadow: 0 2px 8px #0001; }
    .registrations-table { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 14px 12px; text-align: left; }
    th { color: #888; font-weight: 600; border-bottom: 2px solid #eee; }
    tr:not(:last-child) td { border-bottom: 1px solid #f0f0f0; }
    .badge { border-radius: 8px; padding: 4px 12px; font-size: 0.95em; }
    .badge.Pending { background: #e3e6ff; color: #2a3eb1; border: 1px solid #b3b8e6; }
    .badge.Approved { background: #e6ffed; color: #389e0d; }
    .badge.Rejected { background: #ffebee; color: #b53e3e; }
    .badge.NotSubmitted { background: #f0f0f0; color: #666; }
    .status-stack { display: flex; flex-direction: column; gap: 6px; }
    .actions button, .actions a { margin-right: 6px; }
    .btn { border: none; border-radius: 6px; padding: 6px 18px; font-weight: 500; cursor: pointer; }
    .btn.View { background: #1976d2; color: #fff; }
    .btn.Approve { background: #43a047; color: #fff; }
    .btn.Reject { background: #e53935; color: #fff; }
    .btn.Delete { background: #333; color: #fff; }
  </style>
</head>
<body>
  <aside class="sidebar" style="display:flex;flex-direction:column;height:100vh;">
    <div>
      <div class="logo">
        <img src="../../public/assets/bsp-logo.png" alt="BSP Logo" width="80" />
      </div>
      <nav>
        <a href="admin.php?view=dashboard" class="<?= $view==='dashboard'?'active':'' ?>">Dashboard</a>
        <div class="nav-section">AAR Registrations</div>
        <a href="admin.php?view=pending" class="<?= $view==='pending'?'active':'' ?>">Pending</a>
        <a href="admin.php?view=approved" class="<?= $view==='approved'?'active':'' ?>">Approved</a>
        <a href="admin.php?view=rejected" class="<?= $view==='rejected'?'active':'' ?>">Rejected</a>
        <div class="nav-section">Admin Tools</div>
        <a href="manage_users.php">Manage Users</a>
        <a href="receipts.php">Receipts</a>
      </nav>
    </div>
    <div style="margin-top:auto;padding:24px 32px;">
      <a href="admin_logout.php" class="btn" style="width:100%;background:#e53935;color:#fff;text-align:center;">Logout</a>
    </div>
  </aside>
  <main class="main">
    <?php if ($view === 'dashboard'): ?>
      <h2>Admin Dashboard</h2>
      <div class="dashboard-cards">
        <div class="dashboard-card">
          <h3>Pending AAR</h3>
          <div class="count"><?= $pendingCount ?></div>
        </div>
        <div class="dashboard-card">
          <h3>Approved AAR</h3>
          <div class="count"><?= $approvedCount ?></div>
        </div>
        <div class="dashboard-card">
          <h3>Rejected AAR</h3>
          <div class="count"><?= $rejectedCount ?></div>
        </div>
        <div class="dashboard-card">
          <h3>Total Users</h3>
          <div class="count"><?= $totalUsers ?></div>
        </div>
      </div>
      <div class="recent-decisions">
        <h3>Recent Decisions</h3>
        <?php if ($recent): ?>
          <div>
            <strong><?= htmlspecialchars($recent['surname'] . ' ' . $recent['firstname']) ?></strong><br>
            <span><?= htmlspecialchars($recent['email']) ?></span><br>
            <span class="badge Approved">Approved</span>
            <span><?= date('M d, Y h:i A', strtotime($recent['updated_at'])) ?></span>
          </div>
        <?php else: ?>
          <p>No recent approvals.</p>
        <?php endif; ?>
      </div>
    <?php elseif (in_array($view, ['pending', 'approved', 'rejected'])): ?>
      <div class="registrations-table">
        <h3 style="padding: 24px 24px 0 24px;">AAR Registrations (<?= ucfirst($view) ?>)</h3>
        <table>
          <thead>
            <tr>
              <th>Applicant</th>
              <th>Council</th>
              <th>Role</th>
              <th>Status</th>
              <th>Submitted</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($registrations && $row = $registrations->fetch_assoc()): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($row['surname'] . ' ' . $row['firstname']) ?></strong><br>
                <span style="color:#888;"><?= htmlspecialchars($row['email']) ?></span>
              </td>
              <td><?= htmlspecialchars($row['council']) ?></td>
              <td><?= htmlspecialchars($row['serve_sub']) ?></td>
              <td>
                <?php
                  $paymentStatus = $row['payment_status'] ?? 'not-submitted';
                  $paymentLabel = $paymentStatus === 'not-submitted' ? 'Not Submitted' : ucfirst($paymentStatus);
                  $paymentClass = $paymentStatus === 'not-submitted' ? 'NotSubmitted' : ucfirst($paymentStatus);
                ?>
                <div class="status-stack">
                  <span class="badge <?= ucfirst($row['status']) ?>">AAR Form <?= ucfirst($row['status']) ?></span>
                  <span class="badge <?= $paymentClass ?>">Payment <?= $paymentLabel ?></span>
                </div>
              </td>
              <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
              <td class="actions">
                <button class="btn View" data-user-id="<?= (int)$row['user_id'] ?>">View</button>
                <?php if ($row['status'] === 'pending'): ?>
                  <form method="post" action="admin_action.php" style="display:inline;">
                    <input type="hidden" name="action" value="approve_app" />
                    <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($view) ?>" />
                    <button class="btn Approve" type="submit">Approve Form</button>
                  </form>
                  <form method="post" action="admin_action.php" style="display:inline;">
                    <input type="hidden" name="action" value="reject_app" />
                    <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($view) ?>" />
                    <button class="btn Reject" type="submit">Reject Form</button>
                  </form>
                <?php endif; ?>
                <?php if ($paymentStatus === 'pending' && !empty($row['payment_id'])): ?>
                  <form method="post" action="admin_action.php" style="display:inline;">
                    <input type="hidden" name="action" value="approve_payment" />
                    <input type="hidden" name="payment_id" value="<?= (int)$row['payment_id'] ?>" />
                    <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($view) ?>" />
                    <button class="btn Approve" type="submit">Approve Payment</button>
                  </form>
                  <form method="post" action="admin_action.php" style="display:inline;">
                    <input type="hidden" name="action" value="reject_payment" />
                    <input type="hidden" name="payment_id" value="<?= (int)$row['payment_id'] ?>" />
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($view) ?>" />
                    <button class="btn Reject" type="submit">Reject Payment</button>
                  </form>
                <?php endif; ?>
                <form method="post" action="admin_action.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this application?');">
                  <input type="hidden" name="action" value="delete_app" />
                  <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars($view) ?>" />
                  <button class="btn Delete" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    <!-- Modal for viewing registration details -->
    <div id="viewModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#0008;z-index:1000;align-items:center;justify-content:center;">
      <div style="background:#fff;padding:32px 24px;border-radius:12px;max-width:900px;width:95vw;max-height:90vh;overflow:auto;position:relative;">
        <button id="closeModal" style="position:absolute;top:12px;right:16px;font-size:1.5em;background:none;border:none;cursor:pointer;">&times;</button>
        <div id="modalContent">
          <!-- Details will be loaded here -->
        </div>
      </div>
    </div>
    <script>
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('btn') && e.target.classList.contains('View')) {
        const userId = e.target.getAttribute('data-user-id');
        fetch('view_registration.php?user_id=' + userId)
          .then(res => res.json())
          .then(data => {
            let html = `<div style='display:grid;grid-template-columns:1fr 1fr;gap:20px;'>`;
            html += `<div><h2>AAR Form</h2><table style='width:100%;margin-bottom:16px;'>`;
            for (const [key, value] of Object.entries(data.summary)) {
              html += `<tr><td style='font-weight:600;'>${key}</td><td>${value}</td></tr>`;
            }
            html += `</table></div>`;

            html += `<div><h2>Payment Images</h2>`;
            if (data.payment_front || data.payment_back) {
              html += `<div style='display:flex;flex-wrap:wrap;gap:16px;'>`;
              if (data.payment_front) {
                html += `<img src='../../${data.payment_front}' alt='Payment Front' class='payment-img' style='max-width:220px;max-height:180px;border:1px solid #ccc;cursor:pointer;'>`;
              }
              if (data.payment_back) {
                html += `<img src='../../${data.payment_back}' alt='Payment Back' class='payment-img' style='max-width:220px;max-height:180px;border:1px solid #ccc;cursor:pointer;'>`;
              }
              html += `</div>`;
            } else {
              html += `<p>No payment images uploaded.</p>`;
            }
            html += `</div></div>`;

            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('viewModal').style.display = 'flex';

            // Payment image modal
            if (!document.getElementById('paymentImageModal')) {
              const imgModal = document.createElement('div');
              imgModal.id = 'paymentImageModal';
              imgModal.style.display = 'none';
              imgModal.style.position = 'fixed';
              imgModal.style.top = '0';
              imgModal.style.left = '0';
              imgModal.style.width = '100vw';
              imgModal.style.height = '100vh';
              imgModal.style.background = '#0008';
              imgModal.style.zIndex = '1100';
              imgModal.style.alignItems = 'center';
              imgModal.style.justifyContent = 'center';
              imgModal.innerHTML = `<div style='position:relative;background:#fff;padding:24px;border-radius:12px;max-width:90vw;max-height:90vh;display:flex;flex-direction:column;align-items:center;'>
                <button id='closePaymentImgModal' style='position:absolute;top:12px;right:16px;font-size:1.5em;background:none;border:none;cursor:pointer;'>&times;</button>
                <img id='paymentImgModalImg' src='' alt='Payment Image' style='max-width:80vw;max-height:80vh;border:1px solid #ccc;'>
              </div>`;
              document.body.appendChild(imgModal);
              document.getElementById('closePaymentImgModal').onclick = function() {
                imgModal.style.display = 'none';
              };
              imgModal.onclick = function(event) {
                if (event.target === imgModal) {
                  imgModal.style.display = 'none';
                }
              };
            }
            Array.from(document.querySelectorAll('.payment-img')).forEach(img => {
              img.onclick = function() {
                const modal = document.getElementById('paymentImageModal');
                const modalImg = document.getElementById('paymentImgModalImg');
                modalImg.src = img.src;
                modal.style.display = 'flex';
              };
            });
          });
      }
    });
    document.getElementById('closeModal').onclick = function() {
      document.getElementById('viewModal').style.display = 'none';
    };
    window.onclick = function(event) {
      if (event.target === document.getElementById('viewModal')) {
        document.getElementById('viewModal').style.display = 'none';
      }
    };
    </script>
  </main>
</body>
</html>
