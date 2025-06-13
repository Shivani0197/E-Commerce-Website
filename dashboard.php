<?php require_once('header.php'); ?>

<!-- Check if the customer is logged in -->
<?php
if (!isset($_SESSION['customer'])) {
    header('location: ' . BASE_URL . 'logout.php');
    exit;
} else {
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute([$_SESSION['customer']['cust_id'], 0]);
    if ($statement->rowCount() > 0) {
        header('location: ' . BASE_URL . 'logout.php');
        exit;
    }
}

// 🔄 Restore recent searches from cookies if session is empty
if (!isset($_SESSION['recent_searches']) && isset($_COOKIE['recent_searches'])) {
    $_SESSION['recent_searches'] = json_decode($_COOKIE['recent_searches'], true);
}
?>

<div class="page">
    <div class="container">
        <div class="row">            
            <div class="col-md-12"> 
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="col-md-12">
                <div class="user-content">
                    <h3 class="text-center">
                        <?php echo LANG_VALUE_90; ?> <!-- Welcome to Dashboard -->
                    </h3>
                </div>                
            </div>

            <!-- Product Recommendations Section -->
            <div class="col-md-12">
                <div class="user-content">
                    <h3 class="text-center">Recommended for You</h3>

                    <?php
                    // Get recent searches from session
                    $recent_searches = $_SESSION['recent_searches'] ?? [];

                    if (!empty($recent_searches)) {
                        try {
                            $pdo = new PDO("mysql:host=localhost;dbname=ecommerceweb", "root", "", [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                            ]);

                            echo "<div class='row'>"; // Start of product row
                            
                            $stmt = $pdo->prepare("SELECT * FROM tbl_product WHERE p_name LIKE CONCAT('%', ?, '%') LIMIT 10");

                            foreach ($recent_searches as $search) {
                                $stmt->execute([$search]);
                                $products = $stmt->fetchAll();

                                if (!empty($products)) {
                                    foreach ($products as $product) {
                                        echo "<div class='col-md-3'>";
                                        echo "<div class='product-box'>";
                                        echo "<img src='assets/uploads/" . htmlspecialchars($product['p_featured_photo']) . "' alt='" . htmlspecialchars($product['p_name']) . "' style='width:100%; height:auto;' />";
                                        echo "<h4>" . htmlspecialchars($product['p_name']) . "</h4>";
                                        echo "<p>Price: RS" . htmlspecialchars($product['p_current_price']) . "</p>";
                                        echo "<a href='product.php?id=" . htmlspecialchars($product['p_id']) . "' class='btn btn-primary'>View Details</a>";
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                }
                            }

                            echo "</div>"; // End of product row
                        } catch (PDOException $e) {
                            echo "<p class='text-center text-danger'>Error fetching recommendations.</p>";
                        }
                    } else {
                        echo "<p class='text-center'>No recommendations at the moment. Start searching for products to see recommendations!</p>";
                    }
                    ?>
                </div>                
            </div>

        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
