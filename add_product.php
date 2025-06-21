<?php include('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $size = $_POST['size'];
    $price = $_POST['price'];

    // Handle image upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO products (name, image, category, size, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $name, $target_file, $category, $size, $price);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success text-center'>Product added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Database error: " . $stmt->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Failed to upload image.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function updateSizes() {
            const cat = document.getElementById("category").value;
            const sizeSelect = document.getElementById("size");
            sizeSelect.innerHTML = "";
            let sizes = [];

            if (cat === "Men" || cat === "Women") {
                sizes = [6, 7, 8, 9, 10];
            } else if (cat === "Boys" || cat === "Girls") {
                sizes = [3, 4, 5];
            }

            sizes.forEach(size => {
                const opt = document.createElement("option");
                opt.value = size;
                opt.text = size;
                sizeSelect.appendChild(opt);
            });
        }
    </script>
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_home.php">ShoeMart Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="add_product.php">Add New Product</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Add Product Form -->
    <div class="container mt-5">
        <h3 class="text-center mb-4">Add New Shoe Product</h3>
        <form method="POST" enctype="multipart/form-data" class="col-md-6 offset-md-3">
            <div class="mb-3">
                <label>Product Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Product Image:</label>
                <input type="file" name="image" accept="image/*" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Category:</label>
                <select name="category" class="form-control" id="category" onchange="updateSizes()" required>
                    <option value="">--Select--</option>
                    <option value="Men">Men</option>
                    <option value="Women">Women</option>
                    <option value="Boys">Boys</option>
                    <option value="Girls">Girls</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Shoe Size:</label>
                <select name="size" id="size" class="form-control" required>
                    <option value="">Select Category First</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Price:</label>
                <input type="number" name="price" class="form-control" required step="0.01">
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Product</button>
        </form>
    </div>
</body>
</html>
