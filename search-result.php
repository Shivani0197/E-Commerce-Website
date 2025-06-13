<?php
session_start();
ob_start();
require_once('header.php');
require_once('config.php');

// Ensure search text is provided
if (empty($_REQUEST['search_text'])) {
    header('Location: index.php');
    exit;
}

// Sanitize input
$search_text = htmlspecialchars(strip_tags(trim($_REQUEST['search_text'])), ENT_QUOTES, 'UTF-8');

// Log search activity
if (isset($_SESSION['customer']['cust_email'])) {
    $email = $_SESSION['customer']['cust_email'];
    $log_stmt = $pdo->prepare("INSERT INTO tbl_user_activity (user_email, action_type, action_data, action_time) VALUES (?, 'Search', ?, NOW())");
    $log_stmt->execute([$email, "User searched for: $search_text"]);
}

// Database connection (in case config doesn't create PDO)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ecommerceweb', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Store search query for logged-in users
if (isset($_SESSION['customer']['cust_id'])) {
    $user_id = $_SESSION['customer']['cust_id'];
    $stmt = $pdo->prepare("INSERT INTO tbl_recent_searches (user_id, search_term) VALUES (?, ?)");
    $stmt->execute([$user_id, $search_text]);
}

// Store search in session for guests
if (!isset($_SESSION['recent_searches'])) {
    $_SESSION['recent_searches'] = [];
}
if (!in_array($search_text, $_SESSION['recent_searches'])) {
    array_unshift($_SESSION['recent_searches'], $search_text);
    if (count($_SESSION['recent_searches']) > 10) {
        array_pop($_SESSION['recent_searches']);
    }
}

// Pagination
$limit = 10;
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$start = ($page - 1) * $limit;

// Count matching products
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_product WHERE p_is_active = 1 AND p_name LIKE ?");
$stmt->execute(["%$search_text%"]);
$total_products = $stmt->fetchColumn();

// Fetch products
$stmt = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_active = 1 AND p_name LIKE ? LIMIT ?, ?");
$stmt->bindValue(1, "%$search_text%", PDO::PARAM_STR);
$stmt->bindValue(2, $start, PDO::PARAM_INT);
$stmt->bindValue(3, $limit, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$targetpage = 'search-result.php?' . http_build_query(['search_text' => $search_text]);

// Recent Searches
$recent_searches = $_SESSION['recent_searches'];
if (isset($_SESSION['customer']['cust_id'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT search_term FROM tbl_recent_searches WHERE user_id = ? ORDER BY search_date DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $recent_searches = array_merge($recent_searches, $stmt->fetchAll(PDO::FETCH_COLUMN));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
</head>
<body>
    <h1>Search Results for: <?php echo $search_text; ?></h1>

    <?php if ($total_products == 0): ?>
        <p style="color:red;">No results found.</p>
    <?php else: ?>
        <div class="product-list">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <?php $photo = !empty($product['p_featured_photo']) ? $product['p_featured_photo'] : 'default-placeholder.jpg'; ?>
                    <div class="photo" style="background-image:url(assets/uploads/<?php echo htmlspecialchars($photo, ENT_QUOTES); ?>);"></div>
                    <h2><?php echo htmlspecialchars($product['p_name'], ENT_QUOTES); ?></h2>
                    <p>Price: <?php echo htmlspecialchars($product['p_current_price'], ENT_QUOTES); ?></p>
                    <a href="product.php?id=<?php echo htmlspecialchars($product['p_id'], ENT_QUOTES); ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_products > $limit): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= ceil($total_products / $limit); $i++): ?>
                <a href="<?php echo $targetpage . '&page=' . $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <!-- Recent Searches -->
    <h3>Recent Searches</h3>
    <ul>
        <?php foreach ($recent_searches as $recent): ?>
            <li><a href="search-result.php?search_text=<?php echo urlencode($recent); ?>">
                <?php echo htmlspecialchars($recent, ENT_QUOTES); ?>
            </a></li>
        <?php endforeach; ?>
    </ul>

    <!-- Recommendations -->
    <h3>Recommended Products Based on Your Searches</h3>
    <?php
    $recommended_products = [];
    if (empty($recent_searches)) {
        echo "<p>No recommendations yet.</p>";
        $stmt = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_active = 1 ORDER BY p_current_price DESC LIMIT 5");
        $stmt->execute();
        $recommended_products = $stmt->fetchAll();
    } else {
        foreach ($recent_searches as $term) {
            $stmt = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_active = 1 AND p_name LIKE ? LIMIT 5");
            $stmt->execute(["%$term%"]);
            $recommended_products = array_merge($recommended_products, $stmt->fetchAll());
        }
    }

    if (!empty($recommended_products)) {
        echo "<div class='recommended-products'>";
        foreach ($recommended_products as $product) {
            $photo = !empty($product['p_featured_photo']) ? $product['p_featured_photo'] : 'default-placeholder.jpg';
            echo "<div class='product'>";
            echo "<div class='photo' style='background-image:url(assets/uploads/{$photo});'></div>";
            echo "<h2>" . htmlspecialchars($product['p_name'], ENT_QUOTES) . "</h2>";
            echo "<p>Price: " . htmlspecialchars($product['p_current_price'], ENT_QUOTES) . "</p>";
            echo "<a href='product.php?id=" . htmlspecialchars($product['p_id'], ENT_QUOTES) . "'>View Details</a>";
            echo "</div>";
        }
        echo "</div>";
    }
    ?>
</body>
</html>
<?php ob_end_flush(); ?>
