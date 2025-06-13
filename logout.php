<?php
ob_start();
session_start();
include 'admin/inc/config.php';

$user_id = $_SESSION['user_id'] ?? null;
$login_time = $_SESSION['login_time'] ?? null;

if ($user_id) {
    $conn = new mysqli('localhost', 'root', '', 'ecommerceweb');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get user email
    $stmt = $conn->prepare("SELECT cust_email FROM tbl_customer WHERE cust_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $email = '';
    if ($row = $result->fetch_assoc()) {
        $email = $row['cust_email'];
    }
    $stmt->close();

    // 1. Log logout
    $action_type = 'logout';
    $action_value = 'User logged out';
    $stmt = $conn->prepare("INSERT INTO user_activity (email, action_type, action_value) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $action_type, $action_value);
    $stmt->execute();
    $stmt->close();

    // 2. Log time spent on website
    if ($login_time) {
        $logout_time = time();
        $duration_seconds = $logout_time - $login_time;
        $duration_minutes = round($duration_seconds / 60, 2);
        $action_type = 'time_spent';
        $action_value = "$duration_minutes minutes spent on site";

        $stmt = $conn->prepare("INSERT INTO user_activity (email, action_type, action_value) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $action_type, $action_value);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
}

// 🛠 Store recent searches in cookies before destroying session
if (!empty($_SESSION['recent_searches'])) {
    setcookie('recent_searches', json_encode($_SESSION['recent_searches']), time() + (86400 * 7), "/"); // 7 days
}

// 🧹 Clear session and redirect
if (!empty($_SESSION['cart']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $conn = new mysqli('localhost', 'root', '', 'ecommerceweb');
    if (!$conn->connect_error) {
        // Optional: delete existing cart for this user (to avoid duplicates)
        $conn->query("DELETE FROM saved_cart WHERE user_id = $user_id");

        foreach ($_SESSION['cart'] as $product_id => $qty) {
            $stmt = $conn->prepare("INSERT INTO saved_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $product_id, $qty);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
    }
}


session_destroy();

header("Location: login.php");
exit;
?>
