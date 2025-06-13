<?php
include('db_connection.php');
session_start();

// Ensure user is logged in
if (!isset($_SESSION['customer']['cust_email'])) {
    echo "Access denied.";
    exit;
}

$email = $_SESSION['customer']['cust_email'];

// Handle Clear Activity action
if (isset($_POST['clear_activity'])) {
    $stmt = $pdo->prepare("DELETE FROM tbl_user_activity WHERE user_email = ?");
    $stmt->execute([$_SESSION['customer']['cust_email']]);
    $_SESSION['activity_cleared'] = true;
    header("Location: customer-order.php");
    exit;
}

if (isset($_SESSION['activity_cleared'])) {
    echo "<script>alert('Activity log cleared');</script>";
    unset($_SESSION['activity_cleared']);
}

// Fetch activity logs
$stmt = $conn->prepare("SELECT * FROM tbl_user_activity WHERE user_email = ? ORDER BY action_time DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Activity Log</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn-clear {
            background-color: #d9534f;
            color: white;
            padding: 8px 14px;
            border: none;
            cursor: pointer;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<h2>Activity Log for <?php echo htmlspecialchars($email); ?></h2>

<form method="post" onsubmit="return confirm('Are you sure you want to clear all activity logs?');">
    <button type="submit" name="clear_activity" class="btn-clear">Clear Activity Log</button>
</form>

<table>
    <tr>
        <th>Action Type</th>
        <th>Details</th>
        <th>Date</th>
        <th>Time</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
        <?php
            $datetime = new DateTime($row['action_time']);
            $date = $datetime->format('Y-m-d');
            $time = $datetime->format('H:i:s');
            $actionData = htmlspecialchars($row['action_data']);
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['action_type']); ?></td>
            <td><?php echo $actionData; ?></td>
            <td><?php echo $date; ?></td>
            <td><?php echo $time; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
