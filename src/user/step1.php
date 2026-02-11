<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

$userId = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT surname, firstname, mi, sex, civil_status, tenure, serve_main, serve_sub, sponsoring_institutions, council, dob, pob, religion, profession, position_title FROM applications WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$app = $result->fetch_assoc();
$stmt->close();

$isEditing = !empty($app);

function e($value) {
  return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Application for Adult Registration • BSP</title>
    <link rel="stylesheet" href="../../public/css/styles.css" />
    <style>
      .blocked-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(6px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .blocked-modal {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px #0003;
        padding: 40px 32px;
        max-width: 420px;
        text-align: center;
      }
      .blocked-modal h2 {
        color: #b71c1c;
        margin-bottom: 16px;
      }
      .blocked-modal p {
        color: #333;
        margin-bottom: 12px;
      }
      body.blocked {
        pointer-events: none;
        user-select: none;
      }
    </style>
  </head>
  <body>
    <?php
    // Blocked user overlay logic
    $stmt = $conn->prepare("SELECT blocked_start, blocked_end, blocked_reason FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $blocked = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $blockedActive = false;
    if ($blocked && $blocked["blocked_start"] && $blocked["blocked_end"]) {
      $today = date("Y-m-d");
      if ($today >= $blocked["blocked_start"] && $today <= $blocked["blocked_end"]) {
        $blockedActive = true;
      }
    }
    if ($blockedActive): ?>
      <script>document.body.classList.add('blocked');</script>
      <div class="blocked-overlay">
        <div class="blocked-modal">
          <h2>Account Blocked</h2>
          <p>Your account has been blocked by the administrator.</p>
          <p><strong>Reason:</strong> <?php echo htmlspecialchars($blocked["blocked_reason"] ?? "Blocked by administrator."); ?></p>
          <p><strong>Blocked Period:</strong><br><?php echo htmlspecialchars($blocked["blocked_start"]); ?> to <?php echo htmlspecialchars($blocked["blocked_end"]); ?></p>
        </div>
      </div>
    <?php endif; ?>
    <div class="container">
      <section class="hero">
        <h1>Application for Adult Registration</h1>
        <p>Step 1: Personal and Scouting Information</p>
      </section>

      <section class="card">
        <div class="form-title">
          <h2>Applicant Information</h2>
          <span class="badge">Step 1</span>
        </div>
        <form id="step1-form" method="post" action="step1_process.php">
          <div class="grid">
            <div class="field">
              <label for="surname">Surname</label>
              <input id="surname" name="surname" type="text" placeholder="Enter surname" value="<?php echo e($app["surname"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="firstname">First Name</label>
              <input id="firstname" name="firstname" type="text" placeholder="Enter first name" value="<?php echo e($app["firstname"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="mi">Middle Name</label>
              <input id="mi" name="mi" type="text" placeholder="Enter middle name" value="<?php echo e($app["mi"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="sex">Sex</label>
              <select id="sex" name="sex" required>
                <option value="">Select</option>
                <option value="Male" <?php echo (isset($app["sex"]) && $app["sex"] === "Male") ? "selected" : ""; ?>>Male</option>
                <option value="Female" <?php echo (isset($app["sex"]) && $app["sex"] === "Female") ? "selected" : ""; ?>>Female</option>
                <option value="Prefer not to say" <?php echo (isset($app["sex"]) && $app["sex"] === "Prefer not to say") ? "selected" : ""; ?>>Prefer not to say</option>
              </select>
            </div>
            <div class="field">
              <label for="civil-status">Civil Status</label>
              <select id="civil-status" name="civil_status" required>
                <option value="">Select</option>
                <option value="Single" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Single") ? "selected" : ""; ?>>Single</option>
                <option value="Married" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Married") ? "selected" : ""; ?>>Married</option>
                <option value="Widowed" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Widowed") ? "selected" : ""; ?>>Widowed</option>
                <option value="Separated" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Separated") ? "selected" : ""; ?>>Separated</option>
              </select>
            </div>
            <div class="field">
              <label for="tenure">Tenure in Scouting</label>
              <input id="tenure" name="tenure" type="text" placeholder="e.g., 5 years" value="<?php echo e($app["tenure"] ?? ""); ?>" required />
            </div>
          </div>

          <h3>To serve as (Choose only one)</h3>
          <div class="grid">
            <div class="option-group" id="unit-leader-group">
              <h3>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_main"
                    value="unit"
                    id="serve_main_unit"
                    <?php echo (isset($app["serve_main"]) && $app["serve_main"] === "unit") ? "checked" : ""; ?>
                    required
                  />
                  A. Unit Leader
                </label>
              </h3>
              <div class="field">
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Langkay Leader/Assistant"
                    data-main="unit"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Langkay Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_1"
                  />
                  Langkay Leader/Assistant
                </label>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Kawan Leader/Assistant"
                    data-main="unit"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Kawan Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_2"
                  />
                  Kawan Leader/Assistant
                </label>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Troop Leader/Assistant"
                    data-main="unit"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Troop Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_3"
                  />
                  Troop Leader/Assistant
                </label>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Outfit Leader/Assistant"
                    data-main="unit"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Outfit Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_4"
                  />
                  Outfit Leader/Assistant
                </label>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Circle Manager/Assistant"
                    data-main="unit"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Circle Manager/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_5"
                  />
                  Circle Manager/Assistant
                </label>
              </div>
            </div>
            <div class="option-group" id="lay-leader-group">
              <h3>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_main"
                    value="lay"
                    id="serve_main_lay"
                    <?php echo (isset($app["serve_main"]) && $app["serve_main"] === "lay") ? "checked" : ""; ?>
                    required
                  />
                  B. Lay Leader
                </label>
              </h3>
              <div class="field">
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Institutional Scouting Representative/ISCOM/ISC"
                    data-main="lay"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Institutional Scouting Representative/ISCOM/ISC") ? "checked" : ""; ?>
                    id="sub_lay_1"
                  />
                  Institutional Scouting Representative/ISCOM/ISC
                </label>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="District/Municipal Commissioner/Coordinator/Member-at-Large"
                    data-main="lay"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "District/Municipal Commissioner/Coordinator/Member-at-Large") ? "checked" : ""; ?>
                    id="sub_lay_2"
                  />
                  District/Municipal Commissioner/Coordinator/Member-at-Large
                </label>
                <label class="radio-option">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Local Council"
                    data-main="lay"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Local Council") ? "checked" : ""; ?>
                    id="sub_lay_3"
                  />
                  Local Council
                </label>
              </div>
            </div>
          </div>

          <div class="grid" style="margin-top: 20px;">
            <div class="field">
              <label for="sponsoring">Sponsoring Institutions</label>
              <input id="sponsoring" name="sponsoring" type="text" placeholder="Enter sponsoring institution" value="<?php echo e($app["sponsoring_institutions"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="council">Council</label>
              <input id="council" name="council" type="text" placeholder="Enter council" value="<?php echo e($app["council"] ?? "Navotas City Council"); ?>" required />
            </div>
            <div class="field">
              <label for="dob">Date of Birth</label>
              <input id="dob" name="dob" type="date" placeholder="YYYY-MM-DD" value="<?php echo e($app["dob"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="pob">Place of Birth</label>
              <input id="pob" name="pob" type="text" placeholder="Enter place of birth" value="<?php echo e($app["pob"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="religion">Religion</label>
              <input id="religion" name="religion" type="text" placeholder="Enter religion" value="<?php echo e($app["religion"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="profession">Profession/Occupation</label>
              <input id="profession" name="profession" type="text" placeholder="Enter profession/occupation" value="<?php echo e($app["profession"] ?? ""); ?>" required />
            </div>
            <div class="field">
              <label for="position">Position/Title</label>
              <input id="position" name="position" type="text" placeholder="Enter position/title" value="<?php echo e($app["position_title"] ?? ""); ?>" required />
            </div>
          </div>

          <div class="button-row">
            <button class="btn" type="submit">Proceed to Safe From Harm</button>
            <?php if ($isEditing) : ?>
              <a class="btn ghost" href="status.php">Cancel Edit</a>
            <?php endif; ?>
          </div>
        </form>
      </section>
    </div>

    <script src="../../public/js/app.js"></script>
  </body>
</html>
