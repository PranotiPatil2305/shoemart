<?php
include('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$category = $_GET['category'] ?? '';
$cart = $_SESSION['cart'] ?? [];

// Handle add to cart
if (isset($_GET['add_to_cart'])) {
    $pid = $_GET['add_to_cart'];
    if (!in_array($pid, $cart)) {
        $cart[] = $pid;
        $_SESSION['cart'] = $cart;
        $msg = "Product added to cart.";
    } else {
        $msg = "Product already in cart.";
    }
    header("Location: user_home.php?category=$category&msg=" . urlencode($msg));
    exit;
}

// Handle remove from cart
if (isset($_GET['remove_from_cart'])) {
    $pid = $_GET['remove_from_cart'];
    $_SESSION['cart'] = array_diff($cart, [$pid]);
    header("Location: user_home.php?category=$category");
    exit;
}

// Handle Buy
if (isset($_GET['buy']) && count($cart) > 0) {
    $ids = implode(',', array_map('intval', $cart));
    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    $total = 0;
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $total += $row['price'];
        $items[] = $row['id'];
    }

    // Save to orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Save order items
    foreach ($items as $pid) {
        $conn->query("INSERT INTO order_items (order_id, product_id) VALUES ($order_id, $pid)");
    }

    $_SESSION['cart'] = []; // Clear cart
    header("Location: user_home.php?msg=" . urlencode("Order placed successfully!"));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shoe Mart - User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="user_home.php">ShoeMart</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <?php foreach (['Men', 'Women', 'Boys', 'Girls'] as $cat): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $category === $cat ? 'active' : '' ?>" href="?category=<?= $cat ?>"><?= $cat ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!-- Cart Button -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle btn btn-light text-dark" href="#" role="button" data-bs-toggle="dropdown">
                        🛒 Cart (<?= count($cart) ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php
                        if (count($cart) > 0) {
                            $ids = implode(',', $cart);
                            $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
                            while ($item = $result->fetch_assoc()) {
                                echo "<li class='dropdown-item'>{$item['name']} - ₹{$item['price']} <a href='?remove_from_cart={$item['id']}&category=$category' class='text-danger ms-2'>&times;</a></li>";
                            }
                            echo "<li><hr class='dropdown-divider'></li>";
                            echo "<li><a href='?buy=1&category=$category' class='dropdown-item text-success'>Buy Now</a></li>";
                        } else {
                            echo "<li class='dropdown-item text-muted'>Cart is empty</li>";
                        }
                        ?>
                    </ul>
                </li>
                <li class="nav-item ms-2"><a href="logout.php" class="btn btn-outline-light">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Feedback Message -->
<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-info text-center"> <?= htmlspecialchars($_GET['msg']) ?> </div>
<?php endif; ?>

<!-- Product Cards -->
<div class="container mt-4">
    <h4><?= $category ? "Showing $category Shoes" : "Select a Category" ?></h4>
    <div class="row">
        <?php
        if ($category) {
            $stmt = $conn->prepare("SELECT * FROM products WHERE category=?");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "
                <div class='col-md-3 mb-4'>
                    <div class='card h-100'>
                        <img src='{$row['image']}' class='card-img-top' style='height:200px; object-fit:cover;'>
                        <div class='card-body'>
                            <h5 class='card-title'>{$row['name']}</h5>
                            <p class='card-text'>Size: {$row['size']}<br>Price: ₹{$row['price']}</p>
                            <a href='?add_to_cart={$row['id']}&category=$category' class='btn btn-primary w-100'>Add to Cart</a>
                        </div>
                    </div>
                </div>";
            }
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
