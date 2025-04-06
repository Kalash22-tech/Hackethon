<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = isset($_POST['category']) ? $_POST['category'] : 'Product';
    $price = isset($_POST['price']) ? $_POST['price'] : 'N/A';
    $image = $_POST['image'];

    // Use the product/herb data as needed
    echo "<h1>Buy Now</h1>";
    echo "<p>ID: $id</p>";
    echo "<p>Name: $name</p>";
    echo "<p>Category: $category</p>";
    if ($price !== 'N/A') {
        echo "<p>Price: ₹" . number_format($price, 2) . "</p>";
    }
    echo "<img src='$image' alt='$name' style='max-width: 200px;'>";
} else {
    echo "Invalid request.";
}
?>