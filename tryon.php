<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Try-On</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .tryon-container {
            position: relative;
            display: inline-block;
            border: 2px solid #ddd;
            padding: 10px;
            width: 400px;
            height: 500px;
        }
        #userImage, #productImage {
            max-width: 100%;
            display: block;
        }
        #productImage {
            position: absolute;
            top: 50px;
            left: 50px;
            cursor: grab;
            width: 100px;
        }
        video {
            display: none;
            width: 400px;
        }
    </style>
</head>
<body>
    <h2>Virtual Try-On</h2>
    <form action="tryon_process.php" method="POST" enctype="multipart/form-data">
        <label>Upload Your Photo:</label>
        <input type="file" name="user_image" id="uploadUser" accept="image/*">
        <br>
        <button type="button" id="startCamera">Use Camera</button>
        <video id="video" autoplay></video>
        <button type="button" id="capture">Capture</button>
        <canvas id="canvas" style="display: none;"></canvas>
        <br>
        <label>Upload Product Image:</label>
        <input type="file" name="product_image" id="uploadProduct" accept="image/*" required>
        <br>
        <input type="hidden" name="x_position" id="x_position">
        <input type="hidden" name="y_position" id="y_position">
        <button type="submit">Try On</button>
    </form>
    <div class="tryon-container">
        <img id="userImage" src="" alt="Your Image">
        <img id="productImage" src="" alt="Product Image">
    </div>
    <script>
        document.getElementById('uploadUser').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('userImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('uploadProduct').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('productImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        let productImg = document.getElementById("productImage");
        let isDragging = false;
        let offsetX, offsetY;

        productImg.addEventListener("mousedown", (e) => {
            isDragging = true;
            offsetX = e.clientX - productImg.getBoundingClientRect().left;
            offsetY = e.clientY - productImg.getBoundingClientRect().top;
            productImg.style.cursor = "grabbing";
        });

        document.addEventListener("mousemove", (e) => {
            if (isDragging) {
                let xPos = e.clientX - offsetX;
                let yPos = e.clientY - offsetY;
                productImg.style.left = `${xPos}px`;
                productImg.style.top = `${yPos}px`;
                document.getElementById("x_position").value = xPos;
                document.getElementById("y_position").value = yPos;
            }
        });

        document.addEventListener("mouseup", () => {
            isDragging = false;
            productImg.style.cursor = "grab";
        });

        // Camera functionality
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture');
        const startCameraBtn = document.getElementById('startCamera');

        startCameraBtn.addEventListener('click', () => {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                    video.style.display = 'block';
                })
                .catch(error => console.error("Error accessing camera: ", error));
        });

        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            document.getElementById('userImage').src = canvas.toDataURL('image/png');
            video.style.display = 'none';
        });
    </script>
</body>
</html>