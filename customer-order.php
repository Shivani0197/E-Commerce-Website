<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    header('location: '.BASE_URL.'logout.php');
    exit;
} else {
    // If customer is logged in, but admin makes him inactive, then force logout
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute([$_SESSION['customer']['cust_id'], 0]);
    if($statement->rowCount()) {
        header('location: '.BASE_URL.'logout.php');
        exit;
    }
}
?>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12"><?php require_once('customer-sidebar.php'); ?></div>
            <div class="col-md-12">
                <div class="user-content">
                    <h3><?php echo LANG_VALUE_25; ?></h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo LANG_VALUE_48; ?></th>
                                    <th><?php echo LANG_VALUE_27; ?></th>
                                    <th><?php echo LANG_VALUE_28; ?></th>
                                    <th><?php echo LANG_VALUE_29; ?></th>
                                    <th><?php echo LANG_VALUE_30; ?></th>
                                    <th><?php echo LANG_VALUE_31; ?></th>
                                    <th><?php echo LANG_VALUE_32; ?></th>
                                </tr>
                            </thead>
                            <tbody>

<?php
$adjacents = 5;
$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE customer_email=? ORDER BY id DESC");
$statement->execute([$_SESSION['customer']['cust_email']]);
$total_pages = $statement->rowCount();

$targetpage = BASE_URL.'customer-order.php';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE customer_email=? ORDER BY id DESC LIMIT $start, $limit");
$statement->execute([$_SESSION['customer']['cust_email']]);
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if ($page == 0) $page = 1;
$prev = $page - 1;
$next = $page + 1;
$lastpage = ceil($total_pages / $limit);
$lpm1 = $lastpage - 1;

$pagination = "";
if($lastpage > 1) {
    $pagination .= "<div class=\"pagination\">";
    if ($page > 1) {
        $pagination .= "<a href=\"$targetpage?page=$prev\">&#171; previous</a>";
    } else {
        $pagination .= "<span class=\"disabled\">&#171; previous</span>";
    }

    if ($lastpage < 7 + ($adjacents * 2)) {
        for ($counter = 1; $counter <= $lastpage; $counter++) {
            $pagination .= ($counter == $page) ?
                "<span class=\"current\">$counter</span>" :
                "<a href=\"$targetpage?page=$counter\">$counter</a>";
        }
    } elseif($lastpage > 5 + ($adjacents * 2)) {
        if($page < 1 + ($adjacents * 2)) {
            for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                $pagination .= ($counter == $page) ?
                    "<span class=\"current\">$counter</span>" :
                    "<a href=\"$targetpage?page=$counter\">$counter</a>";
            }
            $pagination .= "...<a href=\"$targetpage?page=$lpm1\">$lpm1</a><a href=\"$targetpage?page=$lastpage\">$lastpage</a>";
        } elseif($page > ($adjacents * 2) && $lastpage - ($adjacents * 2) > $page) {
            $pagination .= "<a href=\"$targetpage?page=1\">1</a><a href=\"$targetpage?page=2\">2</a>...";
            for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                $pagination .= ($counter == $page) ?
                    "<span class=\"current\">$counter</span>" :
                    "<a href=\"$targetpage?page=$counter\">$counter</a>";
            }
            $pagination .= "...<a href=\"$targetpage?page=$lpm1\">$lpm1</a><a href=\"$targetpage?page=$lastpage\">$lastpage</a>";
        } else {
            $pagination .= "<a href=\"$targetpage?page=1\">1</a><a href=\"$targetpage?page=2\">2</a>...";
            for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                $pagination .= ($counter == $page) ?
                    "<span class=\"current\">$counter</span>" :
                    "<a href=\"$targetpage?page=$counter\">$counter</a>";
            }
        }
    }

    $pagination .= ($page < $counter - 1) ?
        "<a href=\"$targetpage?page=$next\">next &#187;</a>" :
        "<span class=\"disabled\">next &#187;</span>";
    $pagination .= "</div>";
}

$tip = $page * 10 - 10;
foreach ($result as $row) {
    $tip++;
?>
<tr>
    <td><?php echo $tip; ?></td>
    <td>
        <?php
        $statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
        $statement1->execute([$row['payment_id']]);
        $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result1 as $row1) {
            echo 'Product Name: '.$row1['product_name'].'<br>Size: '.$row1['size'].'<br>Color: '.$row1['color'].'<br>Quantity: '.$row1['quantity'].'<br>Unit Price: $'.$row1['unit_price'].'<br><br>';

            // Log order activity
            $log_stmt = $pdo->prepare("INSERT INTO tbl_user_activity (user_email, action_type, action_data, action_time) VALUES (?, 'Order Placed', ?, NOW())");
            $log_data = "Ordered {$row1['product_name']} - Size: {$row1['size']}, Color: {$row1['color']}, Qty: {$row1['quantity']}";
            $log_stmt->execute([$_SESSION['customer']['cust_email'], $log_data]);
        }
        ?>
    </td>
    <td><?php echo $row['payment_date']; ?></td>
    <td><?php echo $row['txnid']; ?></td>
    <td><?php echo '$'.$row['paid_amount']; ?></td>
    <td><?php echo $row['payment_status']; ?></td>
    <td><?php echo $row['payment_method']; ?></td>
    <td><?php echo $row['payment_id']; ?></td>
</tr>
<?php } ?>
                            </tbody>
                        </table>
                        <div class="pagination" style="overflow: hidden;"><?php echo $pagination; ?></div>
                    </div>

                    <!-- User Activity Log -->
                    <br><br>
                    <h4>User Activity Log</h4>
                    <form method="post">
                        <button type="submit" name="clear_activity" class="btn btn-danger" onclick="return confirm('Clear all your activity logs?')">Clear Activity</button>
                    </form>
                    <br>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Action Type</th>
                                    <th>Details</th>
                                    <th>Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
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


$statement = $pdo->prepare("SELECT * FROM tbl_user_activity WHERE user_email = ? ORDER BY action_time DESC LIMIT 50");
$statement->execute([$_SESSION['customer']['cust_email']]);
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$sl = 0;
foreach ($result as $row) {
    $sl++;
    echo '<tr>';
    echo '<td>'.$sl.'</td>';
    echo '<td>'.htmlspecialchars($row['action_type']).'</td>';
    echo '<td>'.htmlspecialchars($row['action_data']).'</td>';
    echo '<td>'.$row['action_time'].'</td>';
    echo '</tr>';
}
?>
                            </tbody>
                        </table>
                    </div>
                    <!-- End Activity Log -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
