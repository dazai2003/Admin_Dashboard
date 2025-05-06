<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;

if ($edit_id) {
    $edit_package = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM packages WHERE id = $edit_id"));
}

if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image = $_FILES['image']['name'];

    if ($image) {
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
        $stmt = $conn->prepare("UPDATE packages SET name=?, price=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $price, $description, $image, $edit_id);
    } else {
        $stmt = $conn->prepare("UPDATE packages SET name=?, price=?, description=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $price, $description, $edit_id);
    }

    if ($stmt->execute()) {
        header("Location: manage_packages.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image = $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . basename($image));

    $query = "INSERT INTO packages (name, price, description, image) VALUES ('$name', '$price', '$description', '$image')";
    if (mysqli_query($conn, $query)) {
        header("Location: manage_packages.php");
    } else {
        echo "Error inserting package: " . mysqli_error($conn);
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM packages WHERE id=$id");
    header("Location: manage_packages.php");
}

$packages = mysqli_query($conn, "SELECT * FROM packages");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #EEEEEE;
            color: #393E46;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            flex-wrap: wrap;
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
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        .btn {
            background-color: #00ADB5;
            color: #EEEEEE;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            padding: 0.6rem 1.2rem;
            font-size: 1rem;
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
            margin-bottom: 2rem;
            max-width: 600px;
            flex: 1;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .packages-list {
            flex: 1;
            margin-left: 2rem;
            background-color: #EEEEEE;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .package-item {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
            background-color: #EEEEEE;
            color: #393E46;
        }
        .package-item:last-child {
            border-bottom: none;
        }
        .package-item:hover {
            background-color: #00ADB5;
            color: #EEEEEE;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Packages</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </header>

        <!-- Add/Edit Package Form -->
        <div class="form-container">
            <h3><?= $edit_id ? "Edit Package" : "Add Package" ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Package Name" value="<?= $edit_package['name'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <input type="number" name="price" class="form-control" placeholder="Price" value="<?= $edit_package['price'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <textarea name="description" class="form-control" placeholder="Package Description" required><?= $edit_package['description'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <input type="file" name="image" class="form-control-file" <?= !$edit_id ? 'required' : '' ?>>
                </div>
                <button type="submit" name="<?= $edit_id ? 'update' : 'add' ?>" class="btn btn-primary"><?= $edit_id ? 'Update' : 'Add' ?> Package</button>
            </form>
        </div>

        <!-- Display Packages -->
        <div class="packages-list">
            <h3>Existing Packages</h3>
            <?php while ($package = mysqli_fetch_assoc($packages)) { ?>
                <div class="package-item">
                    <div>
                        <strong><?= $package['name'] ?></strong> - $<?= $package['price'] ?><br>
                        <small><?= $package['description'] ?></small>
                    </div>
                    <div>
                        <a href="manage_packages.php?edit=<?= $package['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="?delete=<?= $package['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this package?')">Delete</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
