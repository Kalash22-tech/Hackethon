<?php
include 'auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_premium'])) {
    // In a real application, you would process payment here
    // For demo, we'll just upgrade the user
    
    $duration = $_POST['duration'];
    $expiry_date = null;
    
    if ($duration !== 'lifetime') {
        $expiry_date = date('Y-m-d', strtotime("+$duration months"));
    }
    
    $stmt = $conn->prepare("UPDATE users SET is_premium = 1, premium_expiry = ? WHERE id = ?");
    $stmt->bind_param("si", $expiry_date, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['is_premium'] = true;
        $success = "Congratulations! You're now a premium member.";
    } else {
        $error = "Error upgrading to premium. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include your head content from index.html -->
    <title>Premium Membership - HerbVeda Hub</title>
</head>
<body>
    <!-- Include your navbar from index.html -->
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="mb-4">Upgrade to Premium</h2>
                        <p class="lead">Unlock exclusive benefits with our premium membership</p>
                        
                        <?php if ($_SESSION['is_premium']): ?>
                            <div class="alert alert-success">
                                <h4><i class="fas fa-crown me-2"></i> You're already a premium member!</h4>
                                <p>Enjoy all the premium benefits until 
                                    <?php 
                                        $stmt = $conn->prepare("SELECT premium_expiry FROM users WHERE id = ?");
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $user = $result->fetch_assoc();
                                        
                                        if ($user['premium_expiry']) {
                                            echo date('F j, Y', strtotime($user['premium_expiry']));
                                        } else {
                                            echo "forever (lifetime membership)";
                                        }
                                    ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php elseif (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">6 Months</h5>
                                            <h3 class="price-tag">₹999</h3>
                                            <p class="text-muted">₹166/month</p>
                                            <ul class="list-unstyled text-start my-4">
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 15% discount on all products</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Free monthly consultations</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Access to rare herbs</li>
                                            </ul>
                                            <form method="POST">
                                                <input type="hidden" name="duration" value="6">
                                                <button type="submit" name="upgrade_premium" class="btn btn-primary w-100">Choose Plan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body">
                                            <div class="text-end">
                                                <span class="badge bg-warning text-dark">BEST VALUE</span>
                                            </div>
                                            <h5 class="card-title">Lifetime</h5>
                                            <h3 class="price-tag">₹4999</h3>
                                            <p class="text-muted">One-time payment</p>
                                            <ul class="list-unstyled text-start my-4">
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 15% discount on all products</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Free monthly consultations</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Access to rare herbs</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Priority customer support</li>
                                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Exclusive content</li>
                                            </ul>
                                            <form method="POST">
                                                <input type="hidden" name="duration" value="lifetime">
                                                <button type="submit" name="upgrade_premium" class="btn btn-warning w-100">Choose Plan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include your footer from index.html -->
</body>
</html>