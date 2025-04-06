<?php
include("dbconnect.php");

// Get article ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); 

    // Fetch article details from database
    $sql = "SELECT * FROM articledb WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $article = $result->fetch_assoc();
    } else {
        echo "<h2 class='text-center text-danger'>Article not found!</h2>";
        exit();
    }
} else {
    echo "<h2 class='text-center text-danger'>Invalid request!</h2>";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Ayurvedic Article</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#"><img src="images/logo.jpeg" alt="Logo" height="50"></a>
            <a class="nav-link" href="articles.php">Back to Articles</a>
        </div>
    </nav>

    <!-- Article Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h1 class="text-center"><?php echo htmlspecialchars($article['title']); ?></h1>
                <p class="text-muted text-center">
                    <strong>By:</strong> <?php echo htmlspecialchars($article['author']); ?> | 
                    <strong>Published on:</strong> <?php echo htmlspecialchars($article['date_published']); ?>
                </p>

                <p><?php echo nl2br($article['content']); ?></p>
            </div>
        </div>

        <div class="text-center my-5">
            <a href="articles.php" class="btn btn-success">Back to Articles</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
