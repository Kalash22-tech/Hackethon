<?php
include("dbconnect.php");

// Check if 'id' is set in URL and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $recipe_id = intval($_GET['id']);

    // Use a prepared statement to fetch recipe details
    $stmt = $conn->prepare("SELECT * FROM recipedb WHERE id = ?");
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $recipe = $result->fetch_assoc();
        $herb_id = $recipe['herb_id']; // Get the associated herb_id
    } else {
        echo "<h2 class='text-center text-danger'>Recipe not found!</h2>";
        exit();
    }
    $stmt->close();
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
    <title><?php echo htmlspecialchars($recipe['title'] ?? 'Recipe'); ?> - Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .banner {
            width: 100%;
            height: 200px;
            background: url('images/recipeviewpage.jpeg') no-repeat center;
            background-size: cover;
        }
        .container {
            margin-top: 20px;
        }
        .sidebar {
            background: #e9f5db;
            padding: 20px;
            border-radius: 10px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-success {
            width: 100%;
        }
    </style>
</head>
<body>

<!-- Banner -->
<div class="banner"></div>

<div class="container">
    <div class="row">
        <!-- Sidebar (Left) -->
        <div class="col-md-4">
            <div class="sidebar">
                <h4>More Recipes with 
                    <?php 
                    // Fetch the herb name using prepared statement
                    $stmt = $conn->prepare("SELECT name FROM herbdb WHERE id = ?");
                    $stmt->bind_param("i", $herb_id);
                    $stmt->execute();
                    $herbResult = $stmt->get_result();

                    if ($herbResult->num_rows > 0) {
                        $herb = $herbResult->fetch_assoc();
                        echo htmlspecialchars($herb['name']);
                    } else {
                        echo "Unknown Herb";
                    }
                    $stmt->close();
                    ?>
                </h4>
                <ul>
                    <?php
                    // Fetch related recipes securely
                    $stmt = $conn->prepare("SELECT id, title FROM recipedb WHERE herb_id = ? AND id != ?");
                    $stmt->bind_param("ii", $herb_id, $recipe_id);
                    $stmt->execute();
                    $relatedResult = $stmt->get_result();

                    if ($relatedResult->num_rows > 0) {
                        while ($related = $relatedResult->fetch_assoc()) {
                            echo "<li><a href='recipeview.php?id=" . $related['id'] . "'>" . htmlspecialchars($related['title']) . "</a></li>";
                        }
                    } else {
                        echo "<p>No other recipes available for this herb.</p>";
                    }
                    $stmt->close();
                    ?>
                </ul>
            </div>
        </div>

        <!-- Recipe Details (Right) -->
        <div class="col-md-8">
            <div class="card p-4">
                <h2><?php echo htmlspecialchars($recipe['title'] ?? 'Unknown Recipe'); ?></h2>
                <p><strong>Herb:</strong> <?php echo htmlspecialchars($herb['name'] ?? 'Unknown Herb'); ?></p>

                <h4>Ingredients</h4>
                <ul>
                    <?php
                    if (!empty($recipe['ingredients'])) {
                        $ingredient_list = explode(',', $recipe['ingredients']);
                        foreach ($ingredient_list as $ingredient) {
                            echo "<li>" . htmlspecialchars(trim($ingredient)) . "</li>";
                        }
                    } else {
                        echo "<li>No ingredients listed.</li>";
                    }
                    ?>
                </ul>

                <h4>Instructions</h4>
                <p><?php echo nl2br(htmlspecialchars($recipe['instructions'] ?? 'No instructions available.')); ?></p>

                <a href="herbview.php?id=<?php echo htmlspecialchars($herb_id ?? 0); ?>" class="btn btn-success">Back to Herb</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
