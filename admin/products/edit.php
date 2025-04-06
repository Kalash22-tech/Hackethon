<?php
require_once '../../includes/auth-check.php';
require_once '../../includes/dbconnect.php';

// Fetch product data
$product_id = $_GET['id'];
$product = $db->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form data
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $category = $_POST['category'];
    $ingredients = $_POST['ingredients'];
    $preparation = $_POST['preparation'];
    $usage_instruction = $_POST['usage_instruction'];
    $description = $_POST['description'];
    $short_description = $_POST['short_description'];
    $price = $_POST['price'];
    $weight = $_POST['weight'];
    $health_benefits = $_POST['health_benefits'];
    $diseases = $_POST['diseases'];
    $how_to_use = $_POST['how_to_use'];
    $precautions = $_POST['precautions'];
    $discount = $_POST['discount'] ?? 0.00;
    $is_premium = isset($_POST['is_premium']) ? 1 : 0;
    $form = $_POST['form'];
    $shelf_life = $_POST['shelf_life'];
    $stock_quantity = $_POST['stock_quantity'];

    // Handle image upload
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../images/products/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        
        // Delete old image if exists
        if ($image && file_exists($target_dir . $image)) {
            unlink($target_dir . $image);
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = basename($_FILES["image"]["name"]);
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE products SET 
        name = ?, category_id = ?, ingredients = ?, preparation = ?, usage_instruction = ?,
        category = ?, description = ?, image = ?, short_description = ?, price = ?, weight = ?,
        health_benefits = ?, diseases = ?, how_to_use = ?, precautions = ?, discount = ?,
        is_premium = ?, form = ?, shelf_life = ?, stock_quantity = ?
        WHERE id = ?");

    $stmt->bind_param(
        "sisssssssdsssssdsssii",
        $name, $category_id, $ingredients, $preparation, $usage_instruction,
        $category, $description, $image, $short_description, $price, $weight,
        $health_benefits, $diseases, $how_to_use, $precautions, $discount,
        $is_premium, $form, $shelf_life, $stock_quantity, $product_id
    );

    if ($stmt->execute()) {
        header("Location: list.php?success=Product updated successfully");
        exit();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body>
    <?php include '../../navbar.php'; ?>
    
    <div class="container mt-5">
        <h2>Edit Product</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Product Name*</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category ID*</label>
                        <input type="number" name="category_id" class="form-control" value="<?php echo htmlspecialchars($product['category_id']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Category*</label>
                        <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($product['category']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Price*</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Discount (%)</label>
                        <input type="number" step="0.01" name="discount" class="form-control" value="<?php echo htmlspecialchars($product['discount']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Weight</label>
                        <input type="text" name="weight" class="form-control" value="<?php echo htmlspecialchars($product['weight']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Form</label>
                        <select name="form" class="form-control">
                            <option value="Powder" <?php echo $product['form'] == 'Powder' ? 'selected' : ''; ?>>Powder</option>
                            <option value="Tablet" <?php echo $product['form'] == 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
                            <option value="Capsule" <?php echo $product['form'] == 'Capsule' ? 'selected' : ''; ?>>Capsule</option>
                            <option value="Liquid" <?php echo $product['form'] == 'Liquid' ? 'selected' : ''; ?>>Liquid</option>
                            <option value="Oil" <?php echo $product['form'] == 'Oil' ? 'selected' : ''; ?>>Oil</option>
                            <option value="Not specified" <?php echo $product['form'] == 'Not specified' ? 'selected' : ''; ?>>Not specified</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Shelf Life</label>
                        <input type="text" name="shelf_life" class="form-control" value="<?php echo htmlspecialchars($product['shelf_life']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_premium" class="form-check-input" id="is_premium" <?php echo $product['is_premium'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_premium">Premium Product</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Short Description*</label>
                        <textarea name="short_description" class="form-control" rows="3" required><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ingredients*</label>
                        <textarea name="ingredients" class="form-control" rows="3" required><?php echo htmlspecialchars($product['ingredients']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Preparation*</label>
                        <textarea name="preparation" class="form-control" rows="3" required><?php echo htmlspecialchars($product['preparation']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Usage Instructions*</label>
                        <textarea name="usage_instruction" class="form-control" rows="3" required><?php echo htmlspecialchars($product['usage_instruction']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Health Benefits</label>
                        <textarea name="health_benefits" class="form-control" rows="3"><?php echo htmlspecialchars($product['health_benefits']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Diseases</label>
                        <textarea name="diseases" class="form-control" rows="3"><?php echo htmlspecialchars($product['diseases']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>How to Use</label>
                        <textarea name="how_to_use" class="form-control" rows="3"><?php echo htmlspecialchars($product['how_to_use']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Precautions</label>
                        <textarea name="precautions" class="form-control" rows="3"><?php echo htmlspecialchars($product['precautions']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" class="form-control-file">
                        <?php if ($product['image']): ?>
                            <div class="mt-2">
                                <img src="../../images/products/<?php echo $product['image']; ?>" width="100">
                                <p class="text-muted">Current image: <?php echo $product['image']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="list.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="../../js/bootstrap.bundle.min.js"></script>
</body>
</html>