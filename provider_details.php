<?php
session_start();
include 'Login_dbconn.php';

if (!isset($_GET['provider_id'])) {
    die("Provider ID missing");
}
$provider_id = intval($_GET['provider_id']);

$provider_sql = "SELECT u.user_id, u.name, u.email, u.phone, u.address,
                        s.business_name, s.category, s.about_service, s.price, s.locations
                 FROM users u
                 JOIN service_details s ON u.user_id = s.provider_id
                 WHERE u.user_id = $provider_id AND u.role = 'provider'";
$provider_result = mysqli_query($conn, $provider_sql);
$provider = mysqli_fetch_assoc($provider_result);

if (!$provider) {
    die("Provider not found");
}

$reviews_sql = "SELECT r.*, u.name AS customer_name 
                FROM reviews r
                JOIN users u ON r.customer_id = u.user_id
                WHERE r.provider_id = $provider_id
                ORDER BY r.created_at DESC";
$reviews_result = mysqli_query($conn, $reviews_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($provider['business_name']); ?> - Details</title>
    <link rel="stylesheet" href="provider_details.css">
</head>
<body>
<div class="container">
    <div class="provider-details-container">
        <div class="provider-header">
            <h2><?php echo htmlspecialchars($provider['business_name']); ?></h2>
            <div class="provider-meta">
                Provided by <?php echo htmlspecialchars($provider['name']); ?> |
                <?php echo htmlspecialchars($provider['category']); ?>
            </div>
        </div>
        <div class="service-info">
            <p><strong>About Service:</strong> <?php echo nl2br(htmlspecialchars($provider['about_service'])); ?></p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($provider['price']); ?></p>
            <p><strong>Locations:</strong> <?php echo nl2br(htmlspecialchars($provider['locations'])); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($provider['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($provider['address'])); ?></p>
        </div>

        <div class="review-section">
            <h3>
<div style="text-align: right; margin-bottom: 15px;">
    <a href="appointment_details.php?provider_id=<?php echo isset($_GET['provider_id']) ? intval($_GET['provider_id']) : 0; ?>&service_id=<?php echo isset($_GET['service_id']) ? intval($_GET['service_id']) : 0; ?>"
   class="btn-book-appointment">
   Book Appointment
</a>
</div>
   Customer Reviews</h3>

            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                    <div class="review">
                        <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong><br>
                        <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                        <small>Posted on: <?php echo $review['created_at']; ?></small>

                        <?php if (!empty($review['reply'])): ?>
                            <div class="reply">
                                <strong>Provider Reply:</strong><br>
                                <?php echo nl2br(htmlspecialchars($review['reply'])); ?><br>
                                <small>Replied on: <?php echo $review['reply_at']; ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reviews">No reviews yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
