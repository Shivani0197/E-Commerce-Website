<?php
require 'db_connection.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['user_image']) && isset($_FILES['product_image']) && isset($_POST['x_position']) && isset($_POST['y_position'])) {
        $userImagePath = 'uploads/' . basename($_FILES['user_image']['name']);
        $productImagePath = 'uploads/' . basename($_FILES['product_image']['name']);
        $xPosition = intval($_POST['x_position']);
        $yPosition = intval($_POST['y_position']);

        // Move uploaded files to server
        move_uploaded_file($_FILES['user_image']['tmp_name'], $userImagePath);
        move_uploaded_file($_FILES['product_image']['tmp_name'], $productImagePath);

        // Load images using GD Library
        $userImage = imagecreatefromjpeg($userImagePath);
        $ext = strtolower(pathinfo($productImagePath, PATHINFO_EXTENSION));

if ($ext == 'png') {
    $productImage = imagecreatefrompng($productImagePath);
} elseif ($ext == 'jpeg' || $ext == 'jpg') {
    $productImage = imagecreatefromjpeg($productImagePath);
} elseif ($ext == 'gif') {
    $productImage = imagecreatefromgif($productImagePath);
} else {
    die("Error: Unsupported image format.");
}
 // Ensure product has transparency

        // Get image sizes
        $productWidth = imagesx($productImage);
        $productHeight = imagesy($productImage);

        // Merge images at user-defined position
        imagecopy($userImage, $productImage, $xPosition, $yPosition, 0, 0, $productWidth, $productHeight);

        // Save final image
        $finalImagePath = 'uploads/tryon_result.jpg';
        imagejpeg($userImage, $finalImagePath, 100);

        // Free memory
        imagedestroy($userImage);
        imagedestroy($productImage);

        // Save data to database
        $stmt = $conn->prepare("INSERT INTO tryon_images (user_image, product_image, x_position, y_position) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $userImagePath, $productImagePath, $xPosition, $yPosition);
        $stmt->execute();

        echo "<h3>Try-On Result</h3><img src='$finalImagePath' style='max-width: 100%;'><br><a href='$finalImagePath' download>Download Image</a>";
    } else {
        echo "Error: Please upload both images and ensure positions are set.";
    }
} else {
    echo "Invalid request.";
}
?>
