<?php
function resizeImage($sourcePath, $destPath, $newWidth) {
    list($width, $height, $type) = getimagesize($sourcePath);

    // Calculate new height preserving aspect ratio
    $newHeight = floor($height * ($newWidth / $width));

    // Create a new blank image
    $tmp = imagecreatetruecolor($newWidth, $newHeight);

    // Create image from source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $src = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    // Resize
    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save to destination path
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($tmp, $destPath, 80); // 80% quality
            break;
        case IMAGETYPE_PNG:
            imagepng($tmp, $destPath, 6); // Compression level
            break;
        case IMAGETYPE_GIF:
            imagegif($tmp, $destPath);
            break;
    }

    // Free memory
    imagedestroy($src);
    imagedestroy($tmp);

    return true;
}

// Example usage (usually called from your product image upload script):
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_image'])) {
    $productId = $_POST['product_id']; // Get product ID from form
    $image = $_FILES['product_image'];
    $uploadDir = "admin/productimages/$productId/";

    // Ensure folder exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageName = basename($image['name']);
    $targetPath = $uploadDir . $imageName;

    // Move uploaded original image
    if (move_uploaded_file($image['tmp_name'], $targetPath)) {
        // Create low and medium versions
        $lowPath = $uploadDir . 'low_' . $imageName;
        $mediumPath = $uploadDir . 'medium_' . $imageName;

        resizeImage($targetPath, $lowPath, 200);   // Low = 200px wide
        resizeImage($targetPath, $mediumPath, 600); // Medium = 600px wide

        echo "Images uploaded and resized successfully.";
    } else {
        echo "Failed to upload image.";
    }
} else {
    echo "Invalid request.";
}
?>
