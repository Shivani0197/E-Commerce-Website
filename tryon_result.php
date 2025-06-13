<?php require_once('header.php'); ?>

<?php
$image = $_GET['image'];
$product_id = $_GET['product_id'];

// Get product details
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_id = ?");
$statement->execute([$product_id]);
$product = $statement->fetch(PDO::FETCH_ASSOC);

// Fetch saved position from the database
$statement = $pdo->prepare("SELECT * FROM tryon_positions WHERE product_id = ?");
$statement->execute([$product_id]);
$saved_position = $statement->fetch(PDO::FETCH_ASSOC);
?>

<h2>Try-On Result</h2>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_detection"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/pose"></script>
<script src="assets/js/script.js"></script>

<!-- Container for the try-on -->
<div id="container">
    <!-- User's Uploaded Image -->
    <img id="user-image" src="<?php echo $image; ?>" alt="Your Uploaded Image">
    
    <!-- Draggable & Resizable Product Image -->
    <img id="product-image" src="uploads/products/<?php echo $product['p_featured_photo']; ?>" 
         alt="Try-On Product" class="resizable">
</div>

<p>Selected Product: <strong><?php echo $product['p_name']; ?></strong></p>

<!-- Save Button -->
<button id="save-btn">Save Position</button>

<script>
document.addEventListener("DOMContentLoaded", async function () {
    let productImage = document.getElementById("product-image");
    let userImage = document.getElementById("user-image");

    // Load Saved Position from DB
    let savedPosition = <?php echo json_encode($saved_position); ?>;
    if (savedPosition) {
        productImage.style.top = savedPosition.top + "px";
        productImage.style.left = savedPosition.left + "px";
        productImage.style.width = savedPosition.width + "px";
    }

    // Load MediaPipe AI Model
    const faceDetection = new FaceDetection.FaceDetection({ locateFile: (file) => "https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/" + file });
    faceDetection.setOptions({ minDetectionConfidence: 0.7 });
    faceDetection.onResults((results) => {
        if (!results.detections.length) return;

        let face = results.detections[0].boundingBox;
        let faceWidth = face.width * userImage.width;
        let faceX = face.xCenter * userImage.width - faceWidth / 2;
        let faceY = face.yCenter * userImage.height - face.width * 0.8;

        productImage.style.width = faceWidth * 1.2 + "px";
        productImage.style.left = faceX + "px";
        productImage.style.top = faceY + "px";
    });

    // Use Webcam Image Processing
    const camera = new Camera(userImage, { onFrame: async () => await faceDetection.send({ image: userImage }) });
    camera.start();

    // Drag & Resize Features
    let isDragging = false, offsetX, offsetY;

    productImage.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.clientX - productImage.offsetLeft;
        offsetY = e.clientY - productImage.offsetTop;
        productImage.style.cursor = "grabbing";
    });

    document.addEventListener("mousemove", (e) => {
        if (isDragging) {
            productImage.style.left = (e.clientX - offsetX) + "px";
            productImage.style.top = (e.clientY - offsetY) + "px";
        }
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
        productImage.style.cursor = "grab";
    });

    // Zoom Functionality
    productImage.addEventListener("wheel", (e) => {
        e.preventDefault();
        let scale = e.deltaY < 0 ? 1.1 : 0.9;
        productImage.style.width = (productImage.offsetWidth * scale) + "px";
    });

    // Save Position to Database
    document.getElementById("save-btn").addEventListener("click", function () {
    let position = {
        product_id: "<?php echo $product_id; ?>", // Include product ID
        top: document.getElementById("product-image").style.top.replace("px", ""),
        left: document.getElementById("product-image").style.left.replace("px", ""),
        width: document.getElementById("product-image").style.width.replace("px", "")
    };

    fetch("save_position.php", {
        method: "POST",
        body: JSON.stringify(position),
        headers: { "Content-Type": "application/json" }
    })
    .then(response => response.json())
    .then(data => console.log("Position Saved:", data))
    .catch(error => console.error("Error:", error));
});

</script>

<?php require_once('footer.php'); ?>
