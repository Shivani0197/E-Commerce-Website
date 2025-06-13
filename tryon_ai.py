import cv2
import sys
import numpy as np
import os
import mediapipe as mp

# Load user image
user_image_path = sys.argv[1]   # User's uploaded image
output_path = sys.argv[2]       # Processed output image path
product_id = sys.argv[3]        # Product ID

user_img = cv2.imread(user_image_path)
if user_img is None:
    print("Error: User image not found!")
    sys.exit(1)

# Load MediaPipe Face Mesh
mp_face_mesh = mp.solutions.face_mesh
face_mesh = mp_face_mesh.FaceMesh(static_image_mode=True, max_num_faces=1)

# Convert to RGB for MediaPipe processing
rgb_img = cv2.cvtColor(user_img, cv2.COLOR_BGR2RGB)
results = face_mesh.process(rgb_img)

if not results.multi_face_landmarks:
    print("Error: No face detected!")
    sys.exit(1)

# Extract face landmarks
face_landmarks = results.multi_face_landmarks[0].landmark

# Forehead position (Landmarks for forehead estimation)
forehead_x = int(face_landmarks[10].x * user_img.shape[1])  # Above nose
forehead_y = int(face_landmarks[10].y * user_img.shape[0]) - 40  # Slightly above forehead

# Load product image (cap)
product_folder = r"C:\xampp\htdocs\eCommerceSite-PHP\assets\uploads"
valid_extensions = ["png", "jpg", "jpeg"]
product_img_path = None

for ext in valid_extensions:
    potential_path = os.path.join(product_folder, f"product-featured-{product_id}.{ext}")
    if os.path.exists(potential_path):
        product_img_path = potential_path
        break

if not product_img_path:
    print(f"Error: Product image not found for Product ID {product_id}")
    sys.exit(1)

product_img = cv2.imread(product_img_path, cv2.IMREAD_UNCHANGED)
if product_img is None:
    print("Error: Failed to load product image!")
    sys.exit(1)

# Resize cap to fit forehead width
cap_width = int((face_landmarks[454].x - face_landmarks[234].x) * user_img.shape[1] * 1.2)
cap_height = int(cap_width * (product_img.shape[0] / product_img.shape[1]))  # Keep aspect ratio
resized_cap = cv2.resize(product_img, (cap_width, cap_height))

# Cap position (above forehead)
cap_x = forehead_x - (cap_width // 2)
cap_y = forehead_y - (cap_height // 2)

# Ensure cap is within bounds
cap_x = max(0, min(cap_x, user_img.shape[1] - cap_width))
cap_y = max(0, min(cap_y, user_img.shape[0] - cap_height))

# Overlay cap onto user image
if resized_cap.shape[-1] == 4:  # If cap has transparency (RGBA)
    overlay = resized_cap[:, :, :3]
    mask = resized_cap[:, :, 3] / 255.0

    roi = user_img[cap_y:cap_y + resized_cap.shape[0], cap_x:cap_x + resized_cap.shape[1]]

    for c in range(3):  # Alpha blending
        roi[:, :, c] = (roi[:, :, c] * (1 - mask) + overlay[:, :, c] * mask).astype(np.uint8)

else:  # If no transparency
    user_img[cap_y:cap_y + resized_cap.shape[0], cap_x:cap_x + resized_cap.shape[1]] = resized_cap

# Save final try-on image
cv2.imwrite(output_path, user_img)
print("Success: Cap positioned correctly!")
