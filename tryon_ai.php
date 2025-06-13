<?php
$user_image = $_GET['user_image'];
$product_image = $_GET['product_image'];

// Read saved positions
$position_data = json_decode(file_get_contents("positions.json"), true);

// Call Python script for AI processing
$command = escapeshellcmd("python3 tryon_ai.py $user_image $product_image {$position_data['top']} {$position_data['left']} {$position_data['width']}");
$output = shell_exec($command);

if (trim($output) == "Success") {
    echo "<img src='uploads/tryon/final_output.jpg' alt='Final Try-On Result'>";
} else {
    echo "Error processing AI overlay!";
}
?>
