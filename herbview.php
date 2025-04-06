<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("dbconnect.php");

// Get herb ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Prevent SQL injection

    // Fetch herb details using prepared statement
    $stmt = $conn->prepare("SELECT * FROM herbdb WHERE id = ?");
    if (!$stmt) {
        die("<h2 class='text-center text-danger'>Database error: " . $conn->error . "</h2>");
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if (!$result) {
        die("<h2 class='text-center text-danger'>Query failed: " . $conn->error . "</h2>");
    }

    if ($result->num_rows > 0) {
        $herb = $result->fetch_assoc();
        
        // Validate required herb fields
        $requiredFields = ['name', 'category', 'image', 'price', 'scientific_name'];
        foreach ($requiredFields as $field) {
            if (!isset($herb[$field])) {
                die("<h2 class='text-center text-danger'>Invalid herb data: missing $field</h2>");
            }
        }
        
        // Sanitize image URL
        $imageUrl = filter_var($herb['image'], FILTER_VALIDATE_URL) ? $herb['image'] : 'default-herb.jpg';
    } else {
        echo "<h2 class='text-center text-danger'>Herb not found!</h2>";
        exit();
    }
} else {
    echo "<h2 class='text-center text-danger'>Invalid request!</h2>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($herb['name']); ?> - Ayurvedic Herb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .jumbotron {
            background: url('<?php echo htmlspecialchars($imageUrl); ?>') center/cover no-repeat;
            color: white;
            padding: 80px 20px;
            text-align: center;
            background-blend-mode: overlay;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .product-img {
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            width: 100%;
        }
        .section-title {
            border-bottom: 2px solid #28a745;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#"><img src="logo.png" alt="Logo" height="50"></a>
            <a class="nav-link" href="ayurvedaplants.html">Back to Plants</a>
        </div>
    </nav>

    <!-- Header -->
    <div class="jumbotron">
        <h1><?php echo htmlspecialchars($herb['name']); ?></h1>
        <p><?php echo htmlspecialchars($herb['category']); ?></p>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-5">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($herb['name']); ?>" class="img-fluid product-img">
                
                <!-- Buy Now Button with Price -->
                <div class="text-center my-3">
                    <h4 class="text-success">Price: ₹<?php echo number_format($herb['price'], 2); ?></h4>
                    <form action="buynow.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $herb['id']; ?>">
                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($herb['name']); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($herb['category']); ?>">
                        <input type="hidden" name="image" value="<?php echo htmlspecialchars($imageUrl); ?>">
                        <input type="hidden" name="price" value="<?php echo htmlspecialchars($herb['price']); ?>">
                        <button type="submit" class="btn btn-primary">Buy Now</button>
                    </form>
                </div>
            </div>

            <div class="col-md-7">
                <h3 class="section-title">Quick Overview</h3>
                <p><strong>Scientific Name:</strong> <?php echo htmlspecialchars($herb['scientific_name']); ?></p>
                <p><strong>Common Names:</strong> <?php echo isset($herb['common_names']) ? htmlspecialchars($herb['common_names']) : 'N/A'; ?></p>
                <p><strong>Found In:</strong> <?php echo isset($herb['found_in']) ? htmlspecialchars($herb['found_in']) : 'N/A'; ?></p>
                <p><strong>Used Parts:</strong> <?php echo isset($herb['used_parts']) ? htmlspecialchars($herb['used_parts']) : 'N/A'; ?></p>
            </div>
        </div>

        <div class="my-5">
            <h3 class="section-title">🌿 History & Traditional Use</h3>
            <p><?php echo isset($herb['history']) ? nl2br(htmlspecialchars($herb['history'])) : 'No historical information available.'; ?></p>
        </div>

        <div class="my-5">
            <h3 class="section-title">💪 Health Benefits</h3>
            <ul>
                <?php 
                if (isset($herb['health_benefits']) && !empty($herb['health_benefits'])) {
                    $benefits = explode(",", $herb['health_benefits']);
                    foreach ($benefits as $benefit) {
                        echo "<li>" . htmlspecialchars(trim($benefit)) . "</li>";
                    }
                } else {
                    echo "<li>No health benefits information available</li>";
                }
                ?>
            </ul>
        </div>

        <div class="my-5">
            <h3 class="section-title">🩺 Diseases & Conditions It Helps With</h3>
            <ul>
                <?php 
                if (isset($herb['diseases']) && !empty($herb['diseases'])) {
                    $diseases = explode(",", $herb['diseases']);
                    foreach ($diseases as $disease) {
                        echo "<li>" . htmlspecialchars(trim($disease)) . "</li>";
                    }
                } else {
                    echo "<li>No disease information available</li>";
                }
                ?>
            </ul>
        </div>

        <!-- How to Use & Recipes Section -->
        <div class="my-5">
            <h3 class="section-title">🍵 How to Use & Recipes</h3>
            <ul>
                <?php
                    // Fetch recipes related to this herb using herb_id
                    $recipeStmt = $conn->prepare("SELECT id, title FROM recipedb WHERE herb_id = ?");
                    $recipeStmt->bind_param("i", $id);
                    $recipeStmt->execute();
                    $recipeResult = $recipeStmt->get_result();
                    
                    if ($recipeResult && $recipeResult->num_rows > 0) {
                        while ($recipe = $recipeResult->fetch_assoc()) {
                            echo "<li><a href='recipeview.php?id=" . $recipe['id'] . "'>" . 
                                 htmlspecialchars($recipe['title']) . "</a></li>";
                        }
                    } else {
                        echo "<li>No recipes available for this herb.</li>";
                    }
                    $recipeStmt->close();
                ?>
            </ul>
        </div>

        <div class="my-5">
            <h3 class="section-title">⚠️ Side Effects & Precautions</h3>
            <ul>
                <?php 
                if (isset($herb['side_effects']) && !empty($herb['side_effects'])) {
                    $side_effects = explode(",", $herb['side_effects']);
                    foreach ($side_effects as $effect) {
                        echo "<li>" . htmlspecialchars(trim($effect)) . "</li>";
                    }
                } else {
                    echo "<li>No known side effects</li>";
                }
                ?>
            </ul>
        </div>

        <div class="text-center my-5">
            <a href="ayurvedaplants.html" class="btn btn-success">Back to Plants</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
$conn->close();
?>