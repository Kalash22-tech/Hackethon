<?php
require_once(__DIR__ . '/../../includes/auth-check.php');

require_once __DIR__ . '/../../includes/dbconnect.php';

// Initialize variables
$error = '';
$success = '';
$productData = [
    'name' => '',
    'category_id' => '',
    'category' => '',
    'price' => '',
    'discount' => '0.00',
    'weight' => '',
    'form' => 'Powder',
    'shelf_life' => '',
    'stock_quantity' => '0',
    'short_description' => '',
    'description' => '',
    'ingredients' => '',
    'preparation' => '',
    'usage_instruction' => '',
    'health_benefits' => '',
    'diseases' => '',
    'how_to_use' => '',
    'precautions' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate and sanitize input
        $productData = [
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'category_id' => filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT),
            'category' => filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING),
            'price' => filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT),
            'discount' => filter_input(INPUT_POST, 'discount', FILTER_VALIDATE_FLOAT) ?? 0.00,
            'weight' => filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_STRING),
            'form' => filter_input(INPUT_POST, 'form', FILTER_SANITIZE_STRING),
            'shelf_life' => filter_input(INPUT_POST, 'shelf_life', FILTER_SANITIZE_STRING),
            'stock_quantity' => filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT),
            'short_description' => filter_input(INPUT_POST, 'short_description', FILTER_SANITIZE_STRING),
            'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING),
            'ingredients' => filter_input(INPUT_POST, 'ingredients', FILTER_SANITIZE_STRING),
            'preparation' => filter_input(INPUT_POST, 'preparation', FILTER_SANITIZE_STRING),
            'usage_instruction' => filter_input(INPUT_POST, 'usage_instruction', FILTER_SANITIZE_STRING),
            'health_benefits' => filter_input(INPUT_POST, 'health_benefits', FILTER_SANITIZE_STRING),
            'diseases' => filter_input(INPUT_POST, 'diseases', FILTER_SANITIZE_STRING),
            'how_to_use' => filter_input(INPUT_POST, 'how_to_use', FILTER_SANITIZE_STRING),
            'precautions' => filter_input(INPUT_POST, 'precautions', FILTER_SANITIZE_STRING)
        ];

        $is_premium = isset($_POST['is_premium']) ? 1 : 0;
        $shopkeeper_id = $_SESSION['shop_admin_id'];

        // Validate required fields
        if (empty($productData['name']) || empty($productData['category_id']) || 
            empty($productData['category']) || empty($productData['price']) || 
            empty($productData['short_description']) || empty($productData['ingredients']) || 
            empty($productData['preparation']) || empty($productData['usage_instruction'])) {
            throw new Exception("All required fields must be filled");
        }

        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = __DIR__ . "/../../images/products/";
            
            // Validate image
            $file_info = getimagesize($_FILES['image']['tmp_name']);
            if (!$file_info) {
                throw new Exception("File is not a valid image");
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file_info['mime'], $allowed_types)) {
                throw new Exception("Only JPG, PNG & GIF files are allowed");
            }

            // Generate unique filename
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'prod_' . uniqid() . '.' . strtolower($file_ext);
            $target_file = $target_dir . $new_filename;

            // Check file size (max 2MB)
            if ($_FILES['image']['size'] > 2000000) {
                throw new Exception("Image size must be less than 2MB");
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $new_filename;
            } else {
                throw new Exception("Error uploading image");
            }
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO products (
            shopkeeper_id, category_id, name, ingredients, preparation, usage_instruction, 
            category, description, image, short_description, price, weight, health_benefits, 
            diseases, how_to_use, precautions, discount, is_premium, form, shelf_life, stock_quantity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "iisssssssssdsssssdsssi",
            $shopkeeper_id, 
            $productData['category_id'], 
            $productData['name'], 
            $productData['ingredients'], 
            $productData['preparation'], 
            $productData['usage_instruction'],
            $productData['category'], 
            $productData['description'], 
            $image, 
            $productData['short_description'], 
            $productData['price'], 
            $productData['weight'], 
            $productData['health_benefits'],
            $productData['diseases'], 
            $productData['how_to_use'], 
            $productData['precautions'], 
            $productData['discount'], 
            $is_premium, 
            $productData['form'], 
            $productData['shelf_life'], 
            $productData['stock_quantity']
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Product added successfully";
            header("Location: list.php");
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product | Premium Admin</title>
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/adminlte.min.css">
    <style>
        .form-section {
            background-color: #fff;
            border-radius: 0.35rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        .form-section h5 {
            color: #4e73df;
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .required-field::after {
            content: " *";
            color: #e74a3b;
        }
        .premium-badge {
            background-color: #f6c23e;
            color: #000;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include '../../navbar.php'; ?>
        
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Add New Product</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="list.php">Products</a></li>
                                <li class="breadcrumb-item active">Add New</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-section">
                                    <h5>Basic Information</h5>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Product Name</label>
                                        <input type="text" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($productData['name']); ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="required-field">Category ID</label>
                                                <input type="number" name="category_id" class="form-control" 
                                                       value="<?php echo htmlspecialchars($productData['category_id']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="required-field">Category</label>
                                                <input type="text" name="category" class="form-control" 
                                                       value="<?php echo htmlspecialchars($productData['category']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="required-field">Price</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">$</span>
                                                    </div>
                                                    <input type="number" step="0.01" name="price" class="form-control" 
                                                           value="<?php echo htmlspecialchars($productData['price']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Discount (%)</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" name="discount" class="form-control" 
                                                           value="<?php echo htmlspecialchars($productData['discount']); ?>">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Weight</label>
                                                <input type="text" name="weight" class="form-control" 
                                                       value="<?php echo htmlspecialchars($productData['weight']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Form</label>
                                                <select name="form" class="form-control">
                                                    <option value="Powder" <?php echo $productData['form'] === 'Powder' ? 'selected' : ''; ?>>Powder</option>
                                                    <option value="Tablet" <?php echo $productData['form'] === 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
                                                    <option value="Capsule" <?php echo $productData['form'] === 'Capsule' ? 'selected' : ''; ?>>Capsule</option>
                                                    <option value="Liquid" <?php echo $productData['form'] === 'Liquid' ? 'selected' : ''; ?>>Liquid</option>
                                                    <option value="Oil" <?php echo $productData['form'] === 'Oil' ? 'selected' : ''; ?>>Oil</option>
                                                    <option value="Not specified" <?php echo $productData['form'] === 'Not specified' ? 'selected' : ''; ?>>Not specified</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Shelf Life</label>
                                                <input type="text" name="shelf_life" class="form-control" 
                                                       value="<?php echo htmlspecialchars($productData['shelf_life']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Stock Quantity</label>
                                                <input type="number" name="stock_quantity" class="form-control" 
                                                       value="<?php echo htmlspecialchars($productData['stock_quantity']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="is_premium" class="custom-control-input" id="is_premium">
                                            <label class="custom-control-label" for="is_premium">
                                                Premium Product <span class="premium-badge ml-2">FEATURED</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h5>Media</h5>
                                    <div class="form-group">
                                        <label>Product Image</label>
                                        <div class="custom-file">
                                            <input type="file" name="image" class="custom-file-input" id="customFile">
                                            <label class="custom-file-label" for="customFile">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Recommended size: 800x800px, Max size: 2MB</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-section">
                                    <h5>Descriptions</h5>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Short Description</label>
                                        <textarea name="short_description" class="form-control" rows="3" required><?php echo htmlspecialchars($productData['short_description']); ?></textarea>
                                        <small class="form-text text-muted">This will appear in product listings</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Full Description</label>
                                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($productData['description']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h5>Product Details</h5>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Ingredients</label>
                                        <textarea name="ingredients" class="form-control" rows="3" required><?php echo htmlspecialchars($productData['ingredients']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Preparation</label>
                                        <textarea name="preparation" class="form-control" rows="3" required><?php echo htmlspecialchars($productData['preparation']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="required-field">Usage Instructions</label>
                                        <textarea name="usage_instruction" class="form-control" rows="3" required><?php echo htmlspecialchars($productData['usage_instruction']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h5>Additional Information</h5>
                                    
                                    <div class="form-group">
                                        <label>Health Benefits</label>
                                        <textarea name="health_benefits" class="form-control" rows="3"><?php echo htmlspecialchars($productData['health_benefits']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Diseases</label>
                                        <textarea name="diseases" class="form-control" rows="3"><?php echo htmlspecialchars($productData['diseases']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>How to Use</label>
                                        <textarea name="how_to_use" class="form-control" rows="3"><?php echo htmlspecialchars($productData['how_to_use']); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Precautions</label>
                                        <textarea name="precautions" class="form-control" rows="3"><?php echo htmlspecialchars($productData['precautions']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-2"></i> Save Product
                                    </button>
                                    <a href="list.php" class="btn btn-secondary">
                                        <i class="fas fa-times mr-2"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="../../js/adminlte.min.js"></script>
    <script>
        // Show filename in file input
        $(document).ready(function () {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
            
            // Form validation
            $('form').submit(function() {
                let valid = true;
                $('.required-field').each(function() {
                    const input = $(this).parent().find('input, textarea, select');
                    if (!input.val().trim()) {
                        valid = false;
                        input.addClass('is-invalid');
                    } else {
                        input.removeClass('is-invalid');
                    }
                });
                return valid;
            });
        });
    </script>
</body>
</html>