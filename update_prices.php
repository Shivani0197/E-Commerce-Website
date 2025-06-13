<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'ecommerceweb';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$forecast_file = "price_forecast.csv";
$file = fopen($forecast_file, "r");

// Skip header
fgetcsv($file);

while (($row = fgetcsv($file)) !== FALSE) {
    $date = $row[0];
    $predicted_sales = $row[1];

    // Define a simple pricing rule: if demand increases, raise price by 10%, otherwise lower it by 5%
    $price_adjustment = ($predicted_sales > 50) ? 1.10 : 0.95;

    // Update product prices dynamically (for all products)
    $update_query = "UPDATE products SET price = price * $price_adjustment";
    $conn->query($update_query);
}

fclose($file);
echo "Prices updated successfully!";
$conn->close();
?>
