<?php
session_start();
include 'Login_dbconn.php';
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}
$customer_id = $_SESSION['user_id'];
$displayName = null; 

if ($stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ? LIMIT 1")) {
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $displayName = $row['name']; 
    }
    $stmt->close();
}
$providers = [];
$last_search = ['location' => '', 'service' => ''];
$search_performed = false;

if (isset($_GET['location']) || isset($_GET['service'])) {
    $location = trim($_GET['location'] ?? '');
    $service  = trim($_GET['service'] ?? '');

    if ($location !== '' || $service !== '') {
        $search_performed = true;
        $query = "
        SELECT s.service_id, s.provider_id, s.business_name, s.category, s.price, s.locations, s.about_service
        FROM service_details s
        JOIN users u ON s.provider_id = u.user_id
        WHERE 1=1
        AND u.status = 'approved'
        ";
        $params = [];
        $types  = "";

        if ($location !== '') {
            $query .= " AND s.locations LIKE ?";
            $params[] = "%$location%";
            $types   .= "s";
        }
        if ($service !== '') {
            $query .= " AND s.category = ?";
            $params[] = $service;
            $types   .= "s";
        }

        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            $providers = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        } else {
            $providers = [];
        }
        $_SESSION['search_results'] = $providers;
        $_SESSION['last_search'] = ['location' => $location, 'service' => $service];
        header("Location: dashboard_customer.php#search");
        exit;
    }
}

if (isset($_SESSION['search_results'])) {
    $providers = $_SESSION['search_results'];
    $last_search = $_SESSION['last_search'] ?? $last_search;
    $search_performed = true;
    unset($_SESSION['search_results']); 
    unset($_SESSION['last_search']);
}

$customerAppointments = [];
$sql = "SELECT a.*, s.business_name, u.phone 
        FROM appointments a
        LEFT JOIN service_details s ON a.provider_id = s.provider_id
        LEFT JOIN users u ON a.provider_id = u.user_id
        WHERE a.user_id = ?
        ORDER BY a.appointment_id DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) $customerAppointments[] = $row;
    }
    $stmt->close();
}
$showAppointmentsServer = isset($_GET['show_appointments']) && $_GET['show_appointments'] === '1';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>LocalConnect - Customer Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="dashboard_custstyle.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>LocalConnect</h1>
            <p>Find trusted local service providers near you.</p>
        </header>

        <div class="welcome-message">
            
            <p>
                <?php if (!empty($displayName)): ?>
                    Welcome, <b><?= htmlspecialchars($displayName) ?></b>
                <?php else: ?>
                    Welcome!
                <?php endif; ?>
            </p>
    
        </div>

        <div class="top-right-controls">
            <button id="appointmentsToggle" class="appointments-btn" type="button">
                Your Appointments
            </button>
        </div>

        <form method="GET" action="dashboard_customer.php" class="search-box" autocomplete="off">
            <div class="search-grid">
                <div class="location-container">
                    <label for="location" class="form-label">Location</label>
                    <input id="location" name="location" type="text" placeholder="Enter city" class="input-field" value="<?= htmlspecialchars($last_search['location']); ?>">
                </div>
                <div>
                    <label for="serviceCategory" class="form-label">Service</label>
                    <select id="serviceCategory" name="service" class="select-field">
                        <option value="" <?= ($last_search['service'] === '') ? 'selected' : ''; ?>>All Services</option>
                        <option value="Plumber" <?= ($last_search['service'] === 'Plumber') ? 'selected' : ''; ?>>Plumber</option>
                        <option value="Electrician" <?= ($last_search['service'] === 'Electrician') ? 'selected' : ''; ?>>Electrician</option>
                        <option value="Mechanic" <?= ($last_search['service'] === 'Mechanic') ? 'selected' : ''; ?>>Mechanic</option>
                        <option value="Carpenter" <?= ($last_search['service'] === 'Carpenter') ? 'selected' : ''; ?>>Carpenter</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-search">Search Providers</button>
        </form>

        <div id="search" class="results-grid">
            <?php if ($search_performed): ?>
                <?php if (!empty($providers)): ?>
                    <?php foreach ($providers as $row): ?>
                        <div class="provider-card">
                            <div class="card-content-wrapper">
                                <h3><?= htmlspecialchars($row['business_name']); ?></h3>
                                <p class="details"><?= nl2br(htmlspecialchars($row['about_service'] ?? '')); ?></p>
                                <p><b>Category:</b> <?= htmlspecialchars($row['category']); ?></p>
                                <p><b>Price:</b> <?= htmlspecialchars($row['price']); ?></p>
                                <p><b>Location:</b> <?= htmlspecialchars($row['locations']); ?></p>
                            </div>
                            <a href="provider_details.php?provider_id=<?= urlencode($row['provider_id']); ?>&service_id=<?= urlencode($row['service_id']); ?>">
                                <button class="btn btn-profile" type="button">More Details</button>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No service providers found for your search.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="hint">Enter a location and service to find providers nearby.</p>
            <?php endif; ?>
        </div>

        <section id="appointments" class="<?= $showAppointmentsServer ? 'appointments-visible' : ''; ?>">
            <h2>Your Appointments</h2>
            <?php if (empty($customerAppointments)): ?>
                <p>You have no appointments.</p>
            <?php else: ?>
                <div class="results-grid">
                    <?php foreach ($customerAppointments as $appt): ?>
                        <div class="provider-card appointment-card">
                            <div class="card-content-wrapper">
                                <h3><?= htmlspecialchars($appt['business_name'] ?? 'Unknown Provider') ?></h3>
                                <p class="details"><strong>Provider ID:</strong> <?= htmlspecialchars($appt['provider_id']) ?></p>
                                <p class="details"><strong>Appointment ID:</strong> <?= htmlspecialchars($appt['appointment_id']) ?></p>
                                <p class="details"><strong>Phone:</strong> <?= htmlspecialchars($appt['phone'] ?? 'N/A') ?></p>
                                <p class="details"><strong>Your Message:</strong> <?= nl2br(htmlspecialchars($appt['message'] ?? '')) ?></p>
                                <p class="details"><strong>Provider Reply:</strong> <?= nl2br(htmlspecialchars($appt['provider_reply'] ?? 'No reply yet.')) ?></p>
                                <p class="details"><strong>Status:</strong> <?= htmlspecialchars($appt['status'] ?? 'Pending') ?></p>
                            </div>

                            <?php if (strtolower(trim($appt['status'] ?? '')) === 'completed'): ?>
                              <div class="review-section">
                                <button type="button" class="btn btn-profile show-review-modal-btn" 
                                  data-appointment-id="<?= intval($appt['appointment_id']) ?>"
                                  data-business-name="<?= htmlspecialchars($appt['business_name'] ?? 'Provider') ?>">
                                   Write Review
                                </button>
                              </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <div id="reviewModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button id="closeModalBtn" class="modal-close-btn">&times;</button>
            <h3 id="modalTitle">Write a review for...</h3>
            <textarea id="modalTextarea" class="review-textarea" rows="5" placeholder="Share your experience..."></textarea>
            <button id="modalSaveBtn" class="btn save-review-btn">Save Review</button>
            <div id="modalStatus" class="review-status-message"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      const appointmentsToggleBtn = document.getElementById('appointmentsToggle');
      const appointmentsSection = document.getElementById('appointments');

      if (appointmentsToggleBtn && appointmentsSection) {
        const isInitiallyVisible = appointmentsSection.classList.contains('appointments-visible');
        appointmentsSection.style.overflow = 'hidden';
        appointmentsSection.style.maxHeight = isInitiallyVisible ? appointmentsSection.scrollHeight + 'px' : '0';
        appointmentsSection.style.opacity = isInitiallyVisible ? '1' : '0';

        appointmentsToggleBtn.addEventListener('click', function () {
          const isVisible = appointmentsSection.classList.toggle('appointments-visible');
          if (isVisible) {
            appointmentsSection.style.maxHeight = appointmentsSection.scrollHeight + 'px';
            appointmentsSection.style.opacity = '1';
            setTimeout(() => {
                appointmentsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 300);
          } else {
            appointmentsSection.style.maxHeight = '0';
            appointmentsSection.style.opacity = '0';
          }
        });
      }

      const modal = document.getElementById('reviewModal');
      const closeModalBtn = document.getElementById('closeModalBtn');
      const openModalBtns = document.querySelectorAll('.show-review-modal-btn');
      const modalSaveBtn = document.getElementById('modalSaveBtn');
      
      let currentAppointmentId = null;

      function showModal(appointmentId, businessName) {
        currentAppointmentId = appointmentId;
        document.getElementById('modalTitle').textContent = `Write a review for ${businessName}`;
        document.getElementById('modalTextarea').value = '';
        document.getElementById('modalStatus').textContent = '';
        modal.style.display = 'flex';
      }

      function hideModal() {
        modal.style.display = 'none';
        currentAppointmentId = null;
      }

      openModalBtns.forEach(button => {
        button.addEventListener("click", () => {
          const appointmentId = button.getAttribute("data-appointment-id");
          const businessName = button.getAttribute("data-business-name");
          showModal(appointmentId, businessName);
        });
      });

      closeModalBtn.addEventListener('click', hideModal);
      modal.addEventListener('click', function(event) {
        if (event.target === modal) {
          hideModal();
        }
      });

      modalSaveBtn.addEventListener("click", () => {
        if (!currentAppointmentId) return;

        const textarea = document.getElementById('modalTextarea');
        const statusMsg = document.getElementById('modalStatus');
        const reviewText = textarea.value.trim();

        if (reviewText === "") {
          statusMsg.textContent = "Please write a review before saving.";
          return;
        }

        statusMsg.textContent = "Saving...";

        const body = new URLSearchParams();
        body.append('appointment_id', currentAppointmentId);
        body.append('review', reviewText);

        fetch("save_review.php", {
          method: "POST",
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body
        })
        .then(response => response.ok ? response.text() : Promise.reject('Network response was not ok.'))
        .then(text => {
          statusMsg.textContent = text;
          textarea.value = "";
          setTimeout(() => {
            hideModal();
          }, 2000);
        })
        .catch(err => {
          console.error("Fetch Error:", err);
          statusMsg.textContent = "An error occurred. Please try again.";
        });
      });
    });
    </script>
  <a href="raise_complaint.php" id="raiseComplaintBtn" class="complaint-link">Raise a Complaint</a>
</body>
</html>
