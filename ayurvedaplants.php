<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_ayurveda";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding new plants
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_plant'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $image = $_POST['image'];
    $link = $_POST['link'];
    
    $stmt = $conn->prepare("INSERT INTO ayurvedic_plants (name, category, description, image, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $category, $description, $image, $link);
    
    if ($stmt->execute()) {
        $success = "Plant added successfully!";
    } else {
        $error = "Error adding plant: " . $conn->error;
    }
}

// Get all plants
$plants = [];
$result = $conn->query("SELECT * FROM ayurvedic_plants");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $plants[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Ayurvedic Plants</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3a7d44;
            --secondary-color: #6c757d;
            --accent-color: #d4af37;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: #333;
        }
        
        .premium-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--accent-color);
            color: #000;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.8rem;
            z-index: 10;
        }
        
        .admin-panel {
            background-color: var(--dark-bg);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .plant-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .plant-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .plant-img {
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .plant-card:hover .plant-img {
            transform: scale(1.05);
        }
        
        .category-badge {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-premium {
            background-color: var(--accent-color);
            color: #000;
            font-weight: bold;
            border: none;
        }
        
        .btn-premium:hover {
            background-color: #c9a227;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Admin Panel -->
            
  <?php include 'navbar.php'; ?>
        <!-- Plant Display -->
        <h2 class="text-center mb-4"><i class="fas fa-spa"></i> Our Premium Ayurvedic Plants Collection</h2>
        <!-- In your plant display section -->
<div class="row">
    <?php foreach ($plants as $plant): ?>
        <div class="col-md-4 col-lg-3">
            <div class="card plant-card">
                <div class="premium-badge">PREMIUM</div>
                <img src="<?php echo $plant['image']; ?>" class="card-img-top plant-img" alt="<?php echo $plant['name']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $plant['name']; ?></h5>
                    <span class="badge category-badge mb-2"><?php echo ucfirst($plant['category']); ?></span>
                    <p class="card-text"><?php echo $plant['description']; ?></p>
                    <a href="recipeview.php?id=<?php echo $plant['id']; ?>" class="btn btn-sm btn-premium">View Details</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>