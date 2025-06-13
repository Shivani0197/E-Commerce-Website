<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'ecommerceweb';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM sales_data";
$result = $conn->query($query);

$filename = "sales_data.csv";
$file = fopen($filename, "w");

// Write column names
fputcsv($file, ["id", "product_id", "date", "sales", "price", "stock", "competitor_price"]);

// Write data
while ($row = $result->fetch_assoc()) {
    fputcsv($file, $row);
}

fclose($file);
echo "Data exported successfully!";
$conn->close();
?>
