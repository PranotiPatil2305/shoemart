<?php
include('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">ShoeMart Admin</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="add_product.php">Add New</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_orders.php">Orders</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="logout.php" class="btn btn-outline-light">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h3>All Orders</h3>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Buyer Name</th>
                <th>Product</th>
                <th>Size</th>
                <th>Price</th>
                <th>Total Order Price</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "
        SELECT o.id AS order_id, o.total_price, u.name AS buyer_name,
               p.name AS product_name, p.price AS product_price,
               oi.size
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON oi.order_id = o.id
        JOIN products p ON p.id = oi.product_id
        ORDER BY o.id DESC";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['order_id']}</td>
                    <td>{$row['buyer_name']}</td>
                    <td>{$row['product_name']}</td>
                    <td>{$row['size']}</td>
                    <td>₹{$row['product_price']}</td>
                    <td>₹{$row['total_price']}</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center'>No orders found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
