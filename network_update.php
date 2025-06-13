<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['network'])) {
    $network = $_POST['network'];
    $quality = 'medium'; // Default quality

    if ($network === 'slow-2g' || $network === '2g') {
        $quality = 'low';
    } elseif ($network === '3g') {
        $quality = 'medium';
    } elseif ($network === '4g' || $network === '5g') {
        $quality = 'high';
    }

    $_SESSION['image_quality'] = $quality;

    echo json_encode(['quality' => $quality]);
}
?>