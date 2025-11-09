<?php
session_start();
include 'Login_dbconn.php';
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}
$provider_id = intval($_SESSION['user_id']);
$provider_data = null;
if ($stmt = $conn->prepare("SELECT name, status FROM users WHERE user_id = ? LIMIT 1")) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        $provider_data = $res->fetch_assoc();
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status']) && isset($_POST['appointment_id'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $status = null;
        if ($_POST['update_status'] === 'accept') {
            $status = 'Accepted';
        } elseif ($_POST['update_status'] === 'reject') {
            $status = 'Rejected';
        } elseif ($_POST['update_status'] === 'completed') {
            $status = 'Completed';
        }
        $appt_key = $_POST['appt_key'] ?? 'appointment_id';
        $allowed_keys = ['appointment_id'];
        if ($status !== null && in_array($appt_key, $allowed_keys)) {
            $sql = "UPDATE appointments SET status = ? WHERE $appt_key = ? AND provider_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sii", $status, $appointment_id, $provider_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    if (isset($_POST['provider_reply']) && isset($_POST['appointment_id'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $reply = trim($_POST['provider_reply']);
        $appt_key = $_POST['appt_key'] ?? 'appointment_id';
        $allowed_keys = ['appointment_id'];
        if (in_array($appt_key, $allowed_keys)) {
            $sql = "UPDATE appointments SET provider_reply = ? WHERE $appt_key = ? AND provider_id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sii", $reply, $appointment_id, $provider_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    if (isset($_POST['save_profile'])) {
        $business_name = trim($_POST['business_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $about_service = trim($_POST['about_service'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $locations = trim($_POST['locations'] ?? '');
        if ($stmt = $conn->prepare("SELECT service_id FROM service_details WHERE provider_id = ? LIMIT 1")) {
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows) {
            $stmt->close();
              if ($provider_data['status'] === 'approved') {
                $sql = "UPDATE service_details 
                SET business_name = ?, about_service = ?, price = ?, locations = ?
                WHERE provider_id = ?";
                 if ($stmt = $conn->prepare($sql)) {
                  $stmt->bind_param("ssssi", $business_name, $about_service, $price, $locations, $provider_id);
                  $stmt->execute();
                  $stmt->close();
             }
         } else {
        $sql = "UPDATE service_details 
                SET business_name = ?, category = ?, about_service = ?, price = ?, locations = ?
                WHERE provider_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssi", $business_name, $category, $about_service, $price, $locations, $provider_id);
            $stmt->execute();
            $stmt->close();
        }
    }
     } else {
                $stmt->close();
                $sql = "INSERT INTO service_details (provider_id, business_name, category, about_service, price, locations)
                        VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("isssss", $provider_id, $business_name, $category, $about_service, $price, $locations);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        if ($stmt = $conn->prepare("UPDATE users SET status = CASE WHEN status='approved' THEN status ELSE 'pending' END WHERE user_id = ? AND role = 'provider'")) {
            $stmt->bind_param("i", $provider_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    if (isset($_POST['save_review_reply']) && isset($_POST['review_id'])) {
        $review_id = intval($_POST['review_id']);
        $reply = trim($_POST['reply']);
        $check_sql = "SELECT review_id FROM reviews WHERE review_id = ? AND provider_id = ?";
        if ($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("ii", $review_id, $provider_id);
            $check_stmt->execute();
            $check_res = $check_stmt->get_result();
            if ($check_res && $check_res->num_rows > 0) {
                $update_sql = "UPDATE reviews SET reply = ?, reply_at = NOW() WHERE review_id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("si", $reply, $review_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
            $check_stmt->close();
        }
    }
    $base_url = strtok($_SERVER["REQUEST_URI"], '?');
    if (isset($_POST['save_review_reply'])) {
        header("Location: " . $base_url . '#reviews');
    } else {
        header("Location: " . $base_url . '#');
    }
    exit;
}
$service = null;
if ($stmt = $conn->prepare("SELECT * FROM service_details WHERE provider_id = ? LIMIT 1")) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        $service = $res->fetch_assoc();
    }
    $stmt->close();
}
$required_fields = ['business_name','category','about_service','price','locations'];
$profile_incomplete = true; 
if ($service) {
    $profile_incomplete = false;
    foreach ($required_fields as $f) {
        if (!isset($service[$f]) || trim((string)$service[$f]) === '') {
            $profile_incomplete = true;
            break;
        }
    }
}
$appointments = [];
$sql = "SELECT * FROM appointments WHERE provider_id = ? ORDER BY appointment_id DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($r = $res->fetch_assoc()) $appointments[] = $r;
    }
    $stmt->close();
}
$reviews = [];
$sql = "SELECT r.*, u.name as customer_name
        FROM reviews r
        LEFT JOIN users u ON r.customer_id = u.user_id
        WHERE r.provider_id = ?
        ORDER BY r.created_at DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($r = $res->fetch_assoc()) $reviews[] = $r;
    }
    $stmt->close();
}
function getUserInfo($conn, $user_id) {
    $data = ['name' => 'Unknown', 'phone' => '', 'address' => ''];
    $sql = "SELECT name, phone, address FROM users WHERE user_id = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $data = $row;
        }
        $stmt->close();
    }
    return $data;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>LocalConnect - Provider Dashboard</title>
  <link rel="stylesheet" href="dasboard_providerstyle.css">
  </head>
<body>
  <?php if($provider_data && $provider_data['status'] === 'deleted'): ?>
<div class="notice-box notice-deleted">
⚠️ <b>Important Notice</b><br><br>
Your LocalConnect provider account has been <b>permanently removed</b> by the Administration team.<br>
All your service listings, appointments, reviews and account data are no longer accessible.<br><br>
If you believe this action was made by mistake, kindly contact support at<br>
<u>localconnect@gmail.com</u> for clarification.
</div>
<?php endif; ?>
<?php if($provider_data && $provider_data['status'] === 'suspended'): ?>
<div class="notice-box notice-deleted">
⚠️ <b>Notice:</b> Your LocalConnect provider account has been temporarily suspended.
Please contact our support team at <u>localconnect@gmail.com</u> to review and reactivate your account.
</div>
<?php endif; ?>
<?php if($provider_data && $provider_data['status'] === 'rejected'): ?>
<div style="background:#ffeded;padding:20px;margin:20px;border-radius:10px;border:1px solid #ff8a8a;color:#6b0000;font-weight:600;text-align:center;font-size:16px;">
  ❗ <b>Your Profile Was Rejected</b><br><br>
  Your previous submission did not meet our verification requirements.<br>
  Please review and update your bussiness profile.<br><br>
  <button style="padding:10px 20px;background:#0052ff;color:white;border:none;border-radius:6px;font-size:15px;cursor:pointer;margin-top:12px;"
          onclick="window.location.href='update_provider.php';">
     Re-Submit Profile
  </button>
</div>
<?php
exit;
endif;
?>
<?php
  if ($provider_data && $provider_data['status'] === 'pending' && !$profile_incomplete):
?>
  <div class="verification-container">
    <div class="verification-panel">
      <div class="verification-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 256 256"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm64-88a8,8,0,0,1-8,8H128a8,8,0,0,1-8-8V72a8,8,0,0,1,16,0v48h48A8,8,0,0,1,192,128Z"></path></svg>
      </div>
      <h2>Profile Under Verification</h2>
      <p>Thank you for submitting your profile. It is currently being reviewed by our team. Once verified, your services will become visible to the public. You will be notified upon approval.</p>
    </div>
  </div>
<?php else: ?>
  <div class="container">
    <header class="header">
      <h1>LocalConnect</h1>
      <p>Welcome, <?= htmlspecialchars($provider_data['name'] ?? 'Provider') ?>! Manage your services here.</p>
    </header>

    <div class="dashboard-layout">
      <aside class="sidebar">
        <nav>
          <ul>
            <li><a href="#appointments" class="active">Appointments</a></li>
            <li><a href="#reviews">Reviews</a></li>
            <li><a href="#profile">Profile</a></li>
          </ul>
        </nav>
      </aside>
      <main class="main-content">
        <section id="appointments" class="content-section">
          <h2>Appointments</h2>
          <?php if (empty($appointments)): ?>
            <p>No appointments found.</p>
          <?php else: ?>
            <?php foreach ($appointments as $appt): ?>
              <?php
                $appt_id = $appt['appointment_id'];
                $cust_id = $appt['user_id'] ?? 0;
                $cust_info = getUserInfo($conn, intval($cust_id));
              ?>
              <div class="appt-box">
                <p><strong><?= htmlspecialchars($cust_info['name']) ?></strong></p>
                <p>Phone: <?= htmlspecialchars($cust_info['phone']) ?></p>
                <p>Address: <?= htmlspecialchars($appt['address'] ?? $cust_info['address']) ?></p>
                <p>Landmark: <?= htmlspecialchars($appt['landmark'] ?? '') ?></p>
                <p>Message: <?= nl2br(htmlspecialchars($appt['message'] ?? '')) ?></p>
                <p>Status: <?= htmlspecialchars($appt['status'] ?? 'Pending') ?></p>
                <p>Provider Reply: <?= nl2br(htmlspecialchars($appt['provider_reply'] ?? '')) ?></p>
                <div class="appt-actions">
                  <?php if (strtolower(trim($appt['status'] ?? '')) === 'pending'): ?>
                    <form method="POST" style="display:inline-block;margin-right:8px;">
                      <input type="hidden" name="appointment_id" value="<?= intval($appt_id) ?>">
                      <button type="submit" name="update_status" value="accept">Accept</button>
                      <button type="submit" name="update_status" value="reject">Reject</button>
                    </form>
                  <?php endif; ?>
                  <?php if (strtolower(trim($appt['status'] ?? '')) === 'accepted'): ?>
                    <form method="POST" style="display:inline-block; float:right; margin-left:10px;">
                      <input type="hidden" name="appointment_id" value="<?= intval($appt_id) ?>">
                      <button type="submit" class="btn-complete" name="update_status" value="completed">Mark Completed</button>
                    </form>
                  <?php endif; ?>
                  <form method="POST" style="margin-top:8px;">
                    <input type="hidden" name="appointment_id" value="<?= intval($appt_id) ?>">
                    <label>Reply to customer</label><br>
                    <textarea name="provider_reply" rows="2" cols="50"><?= htmlspecialchars($appt['provider_reply'] ?? '') ?></textarea><br>
                    <button type="submit">Save Reply</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
        <section id="reviews" class="content-section hidden">
          <h2>Reviews</h2>
          <?php if (empty($reviews)): ?>
            <p>No reviews yet.</p>
          <?php else: ?>
            <?php foreach ($reviews as $review): ?>
              <div class="review-box">
                <p class="customer-name"><?= htmlspecialchars($review['customer_name'] ?? 'Unknown') ?></p>
                <p class="review-text">"<?= nl2br(htmlspecialchars($review['review'])) ?>"</p>
                <p><small>Reviewed on: <?= date('F j, Y, g:i a', strtotime($review['created_at'])) ?></small></p>
                <?php if (!empty($review['reply'])): ?>
                  <div class="provider-reply">
                    <p><strong>Your Reply:</strong></p>
                    <blockquote class="reply-text">
                      <?= nl2br(htmlspecialchars($review['reply'])) ?>
                    </blockquote>
                    <p><small>Replied on: <?= date('F j, Y, g:i a', strtotime($review['reply_at'])) ?></small></p>
                  </div>
                <?php endif; ?>
                <?php if (empty($review['reply'])): ?>
                  <form method="POST" class="reply-form" action="#reviews">
                    <input type="hidden" name="review_id" value="<?= intval($review['review_id']) ?>">
                    <label for="reply_<?= intval($review['review_id']) ?>">Your Reply:</label>
                    <textarea name="reply" id="reply_<?= intval($review['review_id']) ?>" rows="2"></textarea>
                    <button type="submit" name="save_review_reply">Save Reply</button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
        <section id="profile" class="content-section hidden">
          <h2>Edit Your Profile</h2>
          <form method="POST" action="#profile">
            <input type="hidden" name="save_profile" value="1">
            <label>Your Name / Business Name</label>
            <input type="text" name="business_name" value="<?= htmlspecialchars($service['business_name'] ?? '') ?>" required>
            <label>Service Category</label>
            <select name="category" required <?= ($provider_data['status'] === 'approved') ? 'disabled' : '' ?>>
              <?php
              $categories = ['Plumber','Carpenter','Electrician','Mechanic'];
              foreach ($categories as $cat) {
                  $selected = ($service && ($service['category'] ?? '') === $cat) ? 'selected' : '';
                  echo "<option value=\"" . htmlspecialchars($cat) . "\" $selected>" . htmlspecialchars($cat) . "</option>";
              }
              ?>
            </select>
            <label>About You / Your Business</label>
            <textarea name="about_service" required><?= htmlspecialchars($service['about_service'] ?? '') ?></textarea>
            <label>Service Price</label>
            <input type="text" name="price" value="<?= htmlspecialchars($service['price'] ?? '') ?>">
            <label>Locations (comma separated)</label>
            <input type="text" name="locations" value="<?= htmlspecialchars($service['locations'] ?? '') ?>">
            <button type="submit" name="save_profile"><?= $service ? 'Edit Profile' : 'Save Profile' ?></button>
          </form>
        </section>
      </main>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const links = document.querySelectorAll('.sidebar a');
      const sections = document.querySelectorAll('.content-section');

      function showSection(name) {
        sections.forEach(s => {
          if (s.id === name) s.classList.remove('hidden');
          else s.classList.add('hidden');
        });
        links.forEach(l => {
          if (l.getAttribute('href') === '#'+name) l.classList.add('active');
          else l.classList.remove('active');
        });
      }
      links.forEach(link => {
        link.addEventListener('click', function(e){
          e.preventDefault();
          const target = this.getAttribute('href').substring(1);
          showSection(target);
          history.replaceState(null, '', '#'+target);
        });
      });
      const initial = window.location.hash ? window.location.hash.substring(1) : 'appointments';
      showSection(initial);
    });
  </script>
<?php endif; ?>
<div id="completeProfileModal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="cp-title">
  <div class="modal-backdrop"></div>
  <div class="modal-panel">
    <h3 id="cp-title">Complete your profile</h3>
    <p>To start receiving bookings, please complete your profile details: business name, category, description, price, and the locations you serve.</p>
    <div class="modal-actions">
      <button type="button" class="btn btn-primary" id="cp-go-profile">Go to Profile</button>
      <button type="button" class="btn btn-secondary" id="cp-later">Later</button>
    </div>
  </div>
</div>
<script>
  (function(){
    const PROFILE_INCOMPLETE = <?= $profile_incomplete ? 'true' : 'false' ?>;
    function showModal(id){ document.getElementById(id)?.classList.remove('hidden'); }
    function hideModal(id){ document.getElementById(id)?.classList.add('hidden'); }
    const goProfileBtn = document.getElementById('cp-go-profile');
    const laterBtn = document.getElementById('cp-later');
    if (goProfileBtn) {
      goProfileBtn.addEventListener('click', () => {
        hideModal('completeProfileModal');
        const profileLink = document.querySelector('.sidebar a[href="#profile"]');
        if (profileLink) profileLink.click();
      });
    }
    if (laterBtn) laterBtn.addEventListener('click', () => hideModal('completeProfileModal'));
    document.addEventListener('DOMContentLoaded', () => {
      if (PROFILE_INCOMPLETE && location.hash !== '#profile') {
        showModal('completeProfileModal');
      }
    });
  })();
</script>
</body>
</html>
