<?php require_once('header.php'); ?>

<!-- fetching row banner login -->
<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                             
foreach ($result as $row) {
    $banner_login = $row['banner_login'];
}
?>

<!-- login form -->
<?php
if(isset($_POST['form1'])) {
    if(empty($_POST['cust_email']) || empty($_POST['cust_password'])) {
        $error_message = LANG_VALUE_132.'<br>';
    } else {
        $cust_email = strip_tags($_POST['cust_email']);
        $cust_password = strip_tags($_POST['cust_password']);

        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
        $statement->execute(array($cust_email));
        $total = $statement->rowCount();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $row) {
            $cust_status = $row['cust_status'];
            $row_password = $row['cust_password'];
        }

        if($total==0) {
            $error_message .= LANG_VALUE_133.'<br>';
        } else {
            if( $row_password != md5($cust_password) ) {
                $error_message .= LANG_VALUE_139.'<br>';
            } else {
                if($cust_status == 0) {
                    $error_message .= LANG_VALUE_148.'<br>';
                } else {
                    // ✅ Successful login: Store user data in session
                    $_SESSION['customer'] = $row; 
                    $_SESSION['user_id'] = $row['cust_id']; 
                    $_SESSION['login_time'] = time();

                    // ✅ Log login activity
                    $email = $row['cust_email'];
                    $conn = new mysqli('localhost', 'root', '', 'ecommerceweb');
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $action_type = 'login_time';
                    $action_value = 'User logged in';

                    $stmt = $conn->prepare("INSERT INTO user_activity (email, action_type, action_value) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $email, $action_type, $action_value);
                    $stmt->execute();
                    $stmt->close();

                    // ✅ Restore saved cart
                    // ✅ Start cart session
$_SESSION['cart'] = []; // Reset

$conn = new mysqli('localhost', 'root', '', 'ecommerceweb');
if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT product_id, quantity FROM saved_cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
    }
    $stmt->close();
    $conn->close();
}


                    // ✅ Optional: clear saved cart after restoring
                    

                    // ✅ Redirect to dashboard
                    header("location: ".BASE_URL."dashboard.php");
                    exit;
                }
            }
        }
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_login; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_10; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <form action="" method="post">
                        <?php $csrf->echoInputField(); ?>                  
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <?php
                                if(!empty($error_message)) {
                                    echo "<div class='error' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                                }
                                if(!empty($success_message)) {
                                    echo "<div class='success' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$success_message."</div>";
                                }
                                ?>
                                <div class="form-group">
                                    <label for=""><?php echo LANG_VALUE_94; ?> *</label>
                                    <input type="email" class="form-control" name="cust_email">
                                </div>
                                <div class="form-group">
                                    <label for=""><?php echo LANG_VALUE_96; ?> *</label>
                                    <input type="password" class="form-control" name="cust_password">
                                </div>
                                <div class="form-group">
                                    <label for=""></label>
                                    <input type="submit" class="btn btn-success" value="<?php echo LANG_VALUE_4; ?>" name="form1">
                                </div>
                                <a href="forget-password.php" style="color:#e4144d;"><?php echo LANG_VALUE_97; ?>?</a>
                            </div>
                        </div>                        
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
