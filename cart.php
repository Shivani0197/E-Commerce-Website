<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $banner_cart = $row['banner_cart'];
}
?>

<?php
$error_message = '';
if (isset($_POST['form1'])) {

    // Track product stock
    $i = 0;
    $statement = $pdo->prepare("SELECT * FROM tbl_product");
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $row) {
        $i++;
        $table_product_id[$i] = $row['p_id'];
        $table_quantity[$i] = $row['p_qty'];
    }

    // Collect posted data
    $i = 0;
    foreach ($_POST['product_id'] as $val) {
        $i++;
        $arr1[$i] = $val;
    }
    $i = 0;
    foreach ($_POST['quantity'] as $val) {
        $i++;
        $arr2[$i] = $val;
    }
    $i = 0;
    foreach ($_POST['product_name'] as $val) {
        $i++;
        $arr3[$i] = $val;
    }

    // Validate quantity
    $allow_update = 1;
    for ($i = 1; $i <= count($arr1); $i++) {
        for ($j = 1; $j <= count($table_product_id); $j++) {
            if ($arr1[$i] == $table_product_id[$j]) {
                $temp_index = $j;
                break;
            }
        }
        if ($table_quantity[$temp_index] < $arr2[$i]) {
            $allow_update = 0;
            $error_message .= '"' . $arr2[$i] . '" items are not available for "' . $arr3[$i] . "\"\\n";
        } else {
            $_SESSION['cart_p_qty'][$i] = $arr2[$i];
        }
    }

    // Log to user activity
    if ($allow_update && isset($_SESSION['customer']['cust_email'])) {
        $email = $_SESSION['customer']['cust_email'];
        foreach ($arr3 as $product_name) {
            $stmt = $pdo->prepare("INSERT INTO tbl_user_activity (user_email, action_type, action_data, action_time) VALUES (?, 'Add to Cart', ?, NOW())");
            $action_data = "Added to cart: " . $product_name;
            $stmt->execute([$email, $action_data]);
        }
    }

    if ($allow_update == 0) {
        echo "<script>alert('$error_message');</script>";
    } else {
        echo "<script>alert('All Items Quantity Update is Successful!');</script>";
    }
}
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $banner_cart; ?>)">
    <div class="overlay"></div>
    <div class="page-banner-inner">
        <h1><?php echo LANG_VALUE_18; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <?php if (!isset($_SESSION['cart_p_id'])): ?>
                    <h2 class="text-center">Cart is Empty!!</h2>
                    <h4 class="text-center">Add products to the cart in order to view it here.</h4>
                <?php else: ?>
                <form action="" method="post">
                    <?php $csrf->echoInputField(); ?>
                    <div class="cart">
                        <table class="table table-responsive table-hover table-bordered">
                            <tr>
                                <th>#</th>
                                <th><?php echo LANG_VALUE_8; ?></th>
                                <th><?php echo LANG_VALUE_47; ?></th>
                                <th><?php echo LANG_VALUE_157; ?></th>
                                <th><?php echo LANG_VALUE_158; ?></th>
                                <th><?php echo LANG_VALUE_159; ?></th>
                                <th><?php echo LANG_VALUE_55; ?></th>
                                <th class="text-right"><?php echo LANG_VALUE_82; ?></th>
                                <th class="text-center"><?php echo LANG_VALUE_83; ?></th>
                            </tr>
                            <?php
                            $table_total_price = 0;

                            $i = 0;
                            foreach ($_SESSION['cart_p_id'] as $key => $value) {
                                $i++;
                                $arr_cart_p_id[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_size_id'] as $key => $value) {
                                $i++;
                                $arr_cart_size_id[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_size_name'] as $key => $value) {
                                $i++;
                                $arr_cart_size_name[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_color_id'] as $key => $value) {
                                $i++;
                                $arr_cart_color_id[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_color_name'] as $key => $value) {
                                $i++;
                                $arr_cart_color_name[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_p_qty'] as $key => $value) {
                                $i++;
                                $arr_cart_p_qty[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_p_current_price'] as $key => $value) {
                                $i++;
                                $arr_cart_p_current_price[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_p_name'] as $key => $value) {
                                $i++;
                                $arr_cart_p_name[$i] = $value;
                            }
                            $i = 0;
                            foreach ($_SESSION['cart_p_featured_photo'] as $key => $value) {
                                $i++;
                                $arr_cart_p_featured_photo[$i] = $value;
                            }

                            for ($i = 1; $i <= count($arr_cart_p_id); $i++): ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><img src="assets/uploads/<?php echo $arr_cart_p_featured_photo[$i]; ?>" alt=""></td>
                                    <td><?php echo $arr_cart_p_name[$i]; ?></td>
                                    <td><?php echo $arr_cart_size_name[$i]; ?></td>
                                    <td><?php echo $arr_cart_color_name[$i]; ?></td>
                                    <td>Rs. <?php echo $arr_cart_p_current_price[$i]; ?></td>
                                    <td>
                                        <input type="hidden" name="product_id[]" value="<?php echo $arr_cart_p_id[$i]; ?>">
                                        <input type="hidden" name="product_name[]" value="<?php echo $arr_cart_p_name[$i]; ?>">
                                        <input type="number" class="qty-field" step="1" min="1" name="quantity[]" value="<?php echo $arr_cart_p_qty[$i]; ?>" data-index="<?php echo $i; ?>">
<input type="hidden" class="unit-price" value="<?php echo $arr_cart_p_current_price[$i]; ?>" data-index="<?php echo $i; ?>">

                                    </td>
                                   <td class="text-right row-total" data-index="<?php echo $i; ?>">
    Rs. <?php echo $row_total_price; ?>
</td>

                                    <td class="text-center">
                                        <a onclick="return confirmDelete();" href="cart-item-delete.php?id=<?php echo $arr_cart_p_id[$i]; ?>&size=<?php echo $arr_cart_size_id[$i]; ?>&color=<?php echo $arr_cart_color_id[$i]; ?>" class="trash">
                                            <i class="fa fa-trash" style="color:red;"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                            <tr>
                                <th colspan="7" class="total-text">Total</th>
                                <th class="total-amount">Rs. <?php echo $table_total_price; ?></th>
                                <th></th>
                            </tr>
                        </table>
                    </div>

                    <div class="cart-buttons">
                        <ul>
                            <li><input type="submit" value="<?php echo LANG_VALUE_20; ?>" class="btn btn-primary" name="form1"></li>
                            <li><a href="index.php" class="btn btn-primary"><?php echo LANG_VALUE_85; ?></a></li>
                            <li><a href="checkout.php" class="btn btn-primary"><?php echo LANG_VALUE_23; ?></a></li>
                        </ul>
                    </div>
                </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyFields = document.querySelectorAll('.qty-field');
    const totalDisplay = document.querySelector('.total-amount');

    function updateTotals() {
        let grandTotal = 0;
        qtyFields.forEach(input => {
            const index = input.dataset.index;
            const qty = parseInt(input.value) || 0;
            const unitPrice = parseFloat(document.querySelector(`.unit-price[data-index="${index}"]`).value);
            const rowTotal = qty * unitPrice;

            // Update row total
            const rowTotalEl = document.querySelector(`.row-total[data-index="${index}"]`);
            if (rowTotalEl) {
                rowTotalEl.textContent = 'Rs. ' + rowTotal.toFixed(2);
            }

            grandTotal += rowTotal;
        });

        // Update grand total
        if (totalDisplay) {
            totalDisplay.textContent = 'Rs. ' + grandTotal.toFixed(2);
        }
    }

    qtyFields.forEach(input => {
        input.addEventListener('input', updateTotals);
    });
});
</script>


<?php require_once('footer.php'); ?>
