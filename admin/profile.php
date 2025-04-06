<?php
require_once '../includes/auth-check.php';
require_once '../includes/dbconnect.php';

// Fetch shop admin data
$admin_id = $_SESSION['shop_admin_id'];
$admin = $conn->query("SELECT * FROM shop_admin WHERE id = $admin_id")->fetch_assoc();

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shop_name = trim($_POST['shop_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($shop_name)) $errors[] = 'Shop name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($address)) $errors[] = 'Address is required';

    // Check if password is being changed
    $password_changed = false;
    if (!empty($current_password)) {
        if (empty($new_password)) $errors[] = 'New password is required';
        if ($new_password !== $confirm_password) $errors[] = 'New passwords do not match';
        
        if (empty($errors)) {
            if (!password_verify($current_password, $admin['password'])) {
                $errors[] = 'Current password is incorrect';
            } else {
                $password_changed = true;
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }
    }

    if (empty($errors)) {
        // Update profile
        if ($password_changed) {
            $stmt = $conn->prepare("UPDATE shop_admin SET shop_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $shop_name, $email, $phone, $address, $hashed_password, $admin_id);
        } else {
            $stmt = $conn->prepare("UPDATE shop_admin SET shop_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $shop_name, $email, $phone, $address, $admin_id);
        }
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully';
            // Refresh admin data
            $admin = $conn->query("SELECT * FROM shop_admin WHERE id = $admin_id")->fetch_assoc();
            $_SESSION['shop_admin_shop'] = $admin['shop_name'];
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Profile</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Shop Profile</h2>
                <p class="text-muted">Manage your shop information and password</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Shop Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Shop Name*</label>
                                <input type="text" name="shop_name" class="form-control" value="<?php echo htmlspecialchars($admin['shop_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label>Email*</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Phone*</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Address*</label>
                                <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($admin['address']); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                            
                            <button type="submit" class="btn btn-warning">Change Password</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Shop Statistics</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Account Created
                                <span><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Account Status
                                <span class="badge badge-<?php echo $admin['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>