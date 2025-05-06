<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;

if ($edit_id) {
    $edit_class = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM classes WHERE id = $edit_id"));
}

if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image = $_FILES['image']['name'];

    if ($image) {
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $image);
        $stmt = $conn->prepare("UPDATE classes SET name=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $description, $image, $edit_id);
    } else {
        $stmt = $conn->prepare("UPDATE classes SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $edit_id);
    }

    if ($stmt->execute()) {
        header("Location: manage_classes.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image = $_FILES['image']['name'];
    $target = "../uploads/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $query = "INSERT INTO classes (name, description, image) VALUES ('$name', '$description', '$image')";
    if (mysqli_query($conn, $query)) {
        header("Location: manage_classes.php");
    } else {
        echo "Error inserting class: " . mysqli_error($conn);
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM classes WHERE id=$id");
    header("Location: manage_classes.php");
}

$classes = mysqli_query($conn, "SELECT * FROM classes");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes</title>
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
        .classes-list {
            flex: 1;
            margin-left: 2rem;
            background-color: #EEEEEE;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .class-item {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
            background-color: #EEEEEE;
            color: #393E46;
        }
        .class-item:last-child {
            border-bottom: none;
        }
        .class-item:hover {
            background-color: #00ADB5;
            color: #EEEEEE;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Classes</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </header>

        <!-- Add/Edit Class Form -->
        <div class="form-container">
            <h3><?= $edit_id ? "Edit Class" : "Add Class" ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Class Name" value="<?= $edit_class['name'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <textarea name="description" class="form-control" placeholder="Class Description" required><?= $edit_class['description'] ?? '' ?></textarea>
                </div>
                <div class="form-group">
                    <input type="file" name="image" class="form-control-file" <?= !$edit_id ? 'required' : '' ?>>
                </div>
                <button type="submit" name="<?= $edit_id ? 'update' : 'add' ?>" class="btn btn-primary"><?= $edit_id ? 'Update' : 'Add' ?> Class</button>
            </form>
        </div>

        <!-- Display Classes -->
        <div class="classes-list">
            <h3>Existing Classes</h3>
            <?php while ($class = mysqli_fetch_assoc($classes)) { ?>
                <div class="class-item">
                    <div>
                        <strong><?= $class['name'] ?></strong><br>
                        <small><?= $class['description'] ?></small>
                    </div>
                    <div>
                        <a href="manage_classes.php?edit=<?= $class['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="?delete=<?= $class['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this class?')">Delete</a>
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
