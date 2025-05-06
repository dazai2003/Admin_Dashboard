<?php
session_start();
include '../includes/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.parent.loadPage('login.php');</script>";
    exit;
}

$upload_dir = __DIR__ . '/../gallery/';

// Ensure the upload directory exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle image upload
if (isset($_POST['upload'])) {
    $image = $_FILES['image']['name'];
    $target_dir = "../uploads/gallery/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $query = "INSERT INTO gallery (image_path) VALUES ('$image')";
        if (!mysqli_query($conn, $query)) {
            echo "Error inserting image: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading image.";
    }
}

$target_dir = "../uploads/gallery/";

// Handle image deletion
if (isset($_GET['delete'])) {
    $image_id = intval($_GET['delete']);
    $image_query = "SELECT image_path FROM gallery WHERE id = $image_id";
    $image_result = mysqli_query($conn, $image_query);
    if ($image_result && mysqli_num_rows($image_result) > 0) {
        $image_row = mysqli_fetch_assoc($image_result);
        $image_path = $target_dir . $image_row['image_path'];
        if (!empty($image_row['image_path']) && file_exists($image_path)) {
            unlink($image_path);
        }
        $delete_query = "DELETE FROM gallery WHERE id = $image_id";
        if (!mysqli_query($conn, $delete_query)) {
            echo "Error deleting image: " . mysqli_error($conn);
        }
    } else {
        echo "Image not found.";
    }
}

// Fetch images
$images = mysqli_query($conn, "SELECT * FROM gallery");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #EEEEEE;
            color: #393E46;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        header {
            background-color: #00ADB5;
            color: #EEEEEE;
            padding: 1.5rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            width: 100%;
        }
        header h1 {
            font-size: 2rem;
            margin: 0;
        }
        .btn {
            background-color: #00ADB5;
            color: #EEEEEE;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 1rem;
        }
        .btn:hover {
            background-color: #222831;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .form-container {
            background-color: #EEEEEE;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            max-width: 600px;
            color: #393E46;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .gallery-list {
            background-color: #EEEEEE;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 1rem;
            color: #393E46;
        }
        .gallery-item {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #EEEEEE;
            color: #393E46;
        }
        .gallery-item img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .gallery-item:hover {
            background-color: #00ADB5;
            color: #EEEEEE;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Gallery</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </header>

        <!-- Upload Image Form -->
        <div class="form-container">
            <h3>Upload New Image</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="file" name="image" class="form-control-file" required>
                </div>
                <button type="submit" name="upload" class="btn btn-primary">Upload Image</button>
            </form>
        </div>

        <!-- Display Gallery -->
        <div class="gallery-list">
            <?php while ($image = mysqli_fetch_assoc($images)) { ?>
                <div class="gallery-item">
                    <img src="../uploads/gallery/<?= htmlspecialchars($image['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Image">
                    <a href="?delete=<?= $image['id'] ?>" class="btn btn-sm btn-outline-danger mt-2" onclick="return confirm('Delete this image?')">Delete</a>
                </div>
            <?php } ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
