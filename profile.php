<?php
include 'auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success = '';
$error = '';
$user = []; // Initialize user array
$showCoinAnimation = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update basic profile info
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $success = "Profile updated successfully!";
            } else {
                $error = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
    
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $user_folder = "uploads/users/" . $_SESSION['user_id'] . "/";
        if (!file_exists($user_folder)) {
            mkdir($user_folder, 0755, true);
        }
        
        // Get current image
        $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_image = $result->fetch_assoc()['profile_image'];
            $stmt->close();
            
            // Generate unique filename
            $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $new_filename = "profile_" . time() . "." . $file_ext;
            $target_file = $user_folder . $new_filename;
            
            // Validate and move uploaded file
            $valid_extensions = ["jpg", "jpeg", "png", "gif"];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file_ext, $valid_extensions)) {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
            } elseif ($_FILES['profile_image']['size'] > $max_size) {
                $error = "File size must be less than 2MB.";
            } elseif (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                // Update database
                $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $new_filename, $_SESSION['user_id']);
                    if ($stmt->execute()) {
                        // Delete old image if not default
                        if ($current_image !== 'default-avatar.jpg' && file_exists($user_folder . $current_image)) {
                            unlink($user_folder . $current_image);
                        }
                        $_SESSION['profile_image'] = $new_filename;
                        $success = isset($success) ? $success . " Profile image updated!" : "Profile image updated!";
                    } else {
                        $error = "Database error updating profile image: " . $stmt->error;
                        unlink($target_file);
                    }
                    $stmt->close();
                } else {
                    $error = "Database error: " . $conn->error;
                    unlink($target_file);
                }
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($current_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                        if ($stmt->execute()) {
                            $success = isset($success) ? $success . " Password changed!" : "Password changed!";
                        } else {
                            $error = "Error changing password: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error = "Database error: " . $conn->error;
                    }
                } else {
                    $error = "Current password is incorrect.";
                }
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
    
    // Handle Add Plant form submission
    if (isset($_POST['add_plant'])) {
        $name = trim($_POST['name']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $link = trim($_POST['link']);
        $image_path = '';

        // Handle file upload
        if (isset($_FILES['plant_image']) && $_FILES['plant_image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/plants/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['plant_image']['name'], PATHINFO_EXTENSION));
            $new_filename = "plant_".time()."_".$_SESSION['user_id'].".".$file_ext;
            $target_file = $target_dir . $new_filename;
            
            // Validate image
            $valid_extensions = ["jpg", "jpeg", "png", "gif"];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file_ext, $valid_extensions)) {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
            } elseif ($_FILES['plant_image']['size'] > $max_size) {
                $error = "File size must be less than 2MB.";
            } elseif (move_uploaded_file($_FILES['plant_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Error uploading file.";
            }
        } else {
            $error = "Please select a plant image.";
        }

        // Insert into database if no errors
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO ayurvedic_plants (user_id, name, category, description, image, link) VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt) {
                $stmt->bind_param("isssss", $_SESSION['user_id'], $name, $category, $description, $image_path, $link);
                
                if ($stmt->execute()) {
                    // Award coin to user and store in database
                    $coinUpdate = $conn->prepare("UPDATE users SET coins = coins + 1 WHERE id = ?");
                    if ($coinUpdate) {
                        $coinUpdate->bind_param("i", $_SESSION['user_id']);
                        if ($coinUpdate->execute()) {
                            $showCoinAnimation = true;
                            $success = "Plant added successfully! You earned 1 coin!";
                            
                            // Log activity
                            $activityStmt = $conn->prepare("INSERT INTO user_activities (user_id, activity_type, points_earned) VALUES (?, 'add_plant', 1)");
                            if ($activityStmt) {
                                $activityStmt->bind_param("i", $_SESSION['user_id']);
                                $activityStmt->execute();
                                $activityStmt->close();
                            }
                        }
                        $coinUpdate->close();
                    }
                } else {
                    $error = "Error adding plant: " . $stmt->error;
                    if (!empty($image_path) && file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                $stmt->close();
            } else {
                $error = "Database error: " . $conn->error;
                if (!empty($image_path) && file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
    }
}

// Get current user data with coins
$stmt = $conn->prepare("SELECT name, email, profile_image, is_premium, premium_expiry, created_at, coins FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    $error = "Database error: " . $conn->error;
}

// Set default values if user data not found
if (!$user) {
    $user = [
        'name' => 'Unknown',
        'email' => '',
        'profile_image' => 'default-avatar.jpg',
        'is_premium' => 0,
        'premium_expiry' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'coins' => 0
    ];
}

// Get user orders
$orders = [];
$stmt = $conn->prepare("SELECT id, order_date, total_amount, status FROM orders WHERE user_id = ? ORDER BY order_date DESC");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $orders = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayurveda Profile | Prakriti Wellness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a6b3a;  /* Ayurvedic green */
            --secondary: #d4a76a;  /* Golden */
            --light: #f8f5f0;  /* Cream */
            --dark: #2a3a1e;  /* Dark green */
            --accent: #a84448;  /* Ayurvedic red */
            --dosha-vata: #7eb6ff;  /* Blue for Vata */
            --dosha-pitta: #ff7e7e;  /* Red for Pitta */
            --dosha-kapha: #7eff7e;  /* Green for Kapha */
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: #333;
            background-image: url('images/ayurveda-pattern.png');
            background-attachment: fixed;
            background-size: 300px;
            background-blend-mode: overlay;
            background-color: rgba(248, 245, 240, 0.9);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s;
            background-color: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            font-family: 'Playfair Display', serif;
            border-bottom: none;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            border-color: var(--secondary);
            color: var(--dark);
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: #c99b5e;
            border-color: #c99b5e;
        }
        
        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .badge-premium {
            background-color: var(--secondary);
            color: var(--dark);
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .profile-avatar {
            border: 5px solid var(--secondary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .ayurvedic-divider {
            height: 3px;
            background: linear-gradient(90deg, var(--dosha-vata), var(--dosha-pitta), var(--dosha-kapha));
            border: none;
            margin: 1.5rem 0;
        }
        
        .dosha-indicator {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--dosha-vata) 33%, var(--dosha-pitta) 33% 66%, var(--dosha-kapha) 66%);
            margin-bottom: 1rem;
            position: relative;
        }
        
        .dosha-indicator::after {
            content: 'Your Ayurvedic Balance';
            position: absolute;
            top: -20px;
            left: 0;
            font-size: 0.8rem;
            color: var(--dark);
        }
        
        .ayurveda-feature {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 8px;
            background-color: rgba(212, 167, 106, 0.1);
        }
        
        .ayurveda-feature i {
            font-size: 1.5rem;
            color: var(--secondary);
            margin-right: 1rem;
        }
        
        .ayurveda-feature h6 {
            margin-bottom: 0.2rem;
            color: var(--primary);
        }
        
        .ayurveda-feature p {
            margin-bottom: 0;
            font-size: 0.9rem;
            color: #666;
        }
        
        .order-status {
            font-weight: 600;
        }
        
        .status-completed {
            color: #28a745;
        }
        
        .status-processing {
            color: #17a2b8;
        }
        
        .status-shipped {
            color: #007bff;
        }
        
        .status-cancelled {
            color: #dc3545;
        }
        
        .profile-image-container {
            position: relative;
            display: inline-block;
        }
        
        .profile-image-upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--secondary);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .profile-image-upload-btn:hover {
            background: var(--primary);
            transform: scale(1.1);
        }
        
        #profileImageInput {
            display: none;
        }
        
        .premium-features {
            border-left: 4px solid var(--secondary);
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .coin-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--secondary);
            color: var(--dark);
            border-radius: 50px;
            font-weight: 600;
            margin-right: 1rem;
        }
        
        .coin-animation {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            display: none;
            animation: coinBounce 2s ease-in-out;
        }
        
        @keyframes coinBounce {
            0% { transform: translate(-50%, -50%) scale(0.1); opacity: 0; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 0; }
        }
        
        #imagePreview {
            text-align: center;
        }
        
        #previewImg {
            max-width: 100%;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar would be included here -->
    
    <div class="container my-5">
        <h2 class="text-center mb-5" style="font-family: 'Playfair Display', serif; color: var(--primary);">
            <i class="fas fa-leaf me-2"></i> Your Ayurvedic Profile
        </h2>
        
        <div class="dosha-indicator"></div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <form method="POST" enctype="multipart/form-data" id="profileImageForm">
                            <div class="profile-image-container">
                                <img src="uploads/users/<?php echo $_SESSION['user_id']; ?>/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                     onerror="this.src='images/default-avatar.jpg'" 
                                     alt="Profile" 
                                     class="rounded-circle profile-avatar mb-3"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                <label for="profileImageInput" class="profile-image-upload-btn" title="Change profile photo">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="profileImageInput" name="profile_image" accept="image/*">
                            </div>
                        </form>
                        
                        <h4 style="color: var(--primary);"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
                        
                        <div class="coin-badge mb-3">
                            <i class="fas fa-coins me-1"></i> <?php echo $user['coins'] ?? 0; ?> Coins
                        </div>
                        
                        <?php if ($user['is_premium']): ?>
                            <div class="badge badge-premium rounded-pill p-2 mb-3">
                                <i class="fas fa-crown me-1"></i> AYURVEDA PREMIUM
                            </div>
                            <?php if ($user['premium_expiry']): ?>
                                <p class="small text-muted">Expires on: <?php echo date('M d, Y', strtotime($user['premium_expiry'])); ?></p>
                            <?php else: ?>
                                <p class="small text-muted">Lifetime wellness membership</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="premium.php" class="btn btn-primary mb-3">
                                <i class="fas fa-crown me-1"></i> Upgrade to Ayurveda Premium
                            </a>
                        <?php endif; ?>
                        
                        <hr class="ayurvedic-divider">
                        
                        <div class="ayurveda-feature">
                            <i class="fas fa-heartbeat"></i>
                            <div>
                                <h6>Wellness Score</h6>
                                <p>85% Balanced</p>
                            </div>
                        </div>
                        
                        <div class="ayurveda-feature">
                            <i class="fas fa-spa"></i>
                            <div>
                                <h6>Primary Dosha</h6>
                                <p>Vata-Pitta</p>
                            </div>
                        </div>
                        
                        <div class="ayurveda-feature">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <h6>Member Since</h6>
                                <p><?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPlantModal">
                            <i class="fas fa-plus me-1"></i> Add Ayurvedic Plant
                        </button>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title" style="color: var(--primary);">
                            <i class="fas fa-seedling me-2"></i> Wellness Recommendations
                        </h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex align-items-center">
                                <i class="fas fa-moon text-primary me-3"></i>
                                <span>Try going to bed by 10 PM</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <i class="fas fa-utensils text-primary me-3"></i>
                                <span>Favor warm, cooked foods</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <i class="fas fa-leaf text-primary me-3"></i>
                                <span>Try our Vata-Pitta tea blend</span>
                            </li>
                        </ul>
                        
                        <?php if ($user['is_premium']): ?>
                            <div class="premium-features mt-3">
                                <h6 class="text-success"><i class="fas fa-star me-2"></i>Premium Features</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i> Personalized consultations</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Exclusive herbal formulas</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Monthly wellness checkups</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title" style="color: var(--primary);">
                            <i class="fas fa-user-edit me-2"></i> Profile Information
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title" style="color: var(--primary);">
                            <i class="fas fa-lock me-2"></i> Change Password
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" style="color: var(--primary);">
                            <i class="fas fa-shopping-bag me-2"></i> Your Ayurvedic Orders
                        </h5>
                        <?php if ($orders && $orders->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                                        <i class="fas fa-<?php 
                                                            switch(strtolower($order['status'])) {
                                                                case 'completed': echo 'check-circle'; break;
                                                                case 'processing': echo 'spinner'; break;
                                                                case 'shipped': echo 'truck'; break;
                                                                case 'cancelled': echo 'times-circle'; break;
                                                                default: echo 'info-circle';
                                                            }
                                                        ?> me-1"></i>
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-basket fa-3x mb-3" style="color: var(--secondary);"></i>
                                <h5>No Ayurvedic Orders Yet</h5>
                                <p class="text-muted">Begin your wellness journey with our authentic Ayurvedic products</p>
                                <a href="products.php" class="btn btn-primary">
                                    <i class="fas fa-leaf me-1"></i> Discover Products
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Plant Modal Form -->
    <div class="modal fade" id="addPlantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Ayurvedic Plant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Plant Name*</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category*</label>
                            <select class="form-select" name="category" required>
                                <option value="rasayana">Rasayana (Rejuvenating)</option>
                                <option value="medhya">Medhya (Brain-Boosting)</option>
                                <option value="digestive">Digestive</option>
                                <option value="respiratory">Respiratory</option>
                                <option value="immunity">Immunity Booster</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description*</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plant Image*</label>
                            <input type="file" class="form-control" name="plant_image" id="plantImage" accept="image/*" required>
                            <small class="text-muted">Upload image (JPG, PNG, max 2MB)</small>
                            <div id="imagePreview" class="mt-2" style="display:none;">
                                <img id="previewImg" src="#" alt="Preview" style="max-height: 150px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Details Link</label>
                            <input type="text" class="form-control" name="link" placeholder="https://example.com/plant-details">
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You'll earn 1 coin for adding a new plant!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_plant" class="btn btn-primary">Add Plant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Coin Animation -->
    <?php if ($showCoinAnimation): ?>
    <div id="coinAnimation" class="coin-animation">
        <img src="images/coin.png" alt="Coin" width="100">
    </div>
    <?php endif; ?>
    
    <!-- Footer would be included here -->
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-submit form when image is selected
        document.getElementById('profileImageInput').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Preview image before upload
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-avatar').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
                
                // Submit form
                document.getElementById('profileImageForm').submit();
            }
        });
        
        // Image preview functionality for plant image
        document.getElementById('plantImage').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('previewImg');
                    preview.src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        <?php if ($showCoinAnimation): ?>
        // Show coin animation
        document.addEventListener('DOMContentLoaded', function() {
            const coin = document.getElementById('coinAnimation');
            coin.style.display = 'block';
            
            setTimeout(() => {
                coin.style.display = 'none';
            }, 2000);
        });
        <?php endif; ?>
    </script>
</body>
</html>