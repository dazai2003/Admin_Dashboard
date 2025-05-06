<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch trainers
$trainers = mysqli_query($conn, "SELECT * FROM trainers");
if (!$trainers) {
    die("Error fetching trainers: " . mysqli_error($conn));
}

// Add or Edit trainer functionality
if (isset($_POST['add_trainer']) || isset($_POST['update_trainer'])) {
    $name = $_POST['name'];
    $specialty = $_POST['specialty'];
    $image = $_FILES['image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        if (isset($_POST['add_trainer'])) {
            $query = "INSERT INTO trainers (name, specialty, image) VALUES ('$name', '$specialty', '$image')";
        } else {
            $trainer_id = $_POST['trainer_id'];
            $query = "UPDATE trainers SET name='$name', specialty='$specialty', image='$image' WHERE id=$trainer_id";
        }

        if (!mysqli_query($conn, $query)) {
            echo "Error processing trainer: " . mysqli_error($conn);
        } else {
            header("Location: manage_trainers.php");
            exit();
        }
    } else {
        echo "Error uploading image.";
    }
}

// Fetch trainer for editing
$trainer = null;
if (isset($_GET['edit'])) {
    $trainer_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM trainers WHERE id = $trainer_id";
    $edit_result = mysqli_query($conn, $edit_query);
    if ($edit_result) {
        $trainer = mysqli_fetch_assoc($edit_result);
    } else {
        echo "Error fetching trainer: " . mysqli_error($conn);
    }
}

// Delete trainer functionality
if (isset($_GET['delete'])) {
    $trainer_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM trainers WHERE id = $trainer_id";
    if (!mysqli_query($conn, $delete_query)) {
        echo "Error deleting trainer: " . mysqli_error($conn);
    } else {
        header("Location: manage_trainers.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trainers</title>
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
        .form-container, .trainers-list {
            background-color: #EEEEEE;
            color: #393E46;
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
        .trainers-list {
            flex: 1;
            margin-left: 2rem;
            background-color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .trainer-item {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
            background-color: #EEEEEE;
            color: #393E46;
        }
        .trainer-item:last-child {
            border-bottom: none;
        }
        .trainer-item:hover {
            background-color: #00ADB5;
            color: #EEEEEE;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Trainers</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </header>

        <!-- Add/Edit Trainer Form -->
        <div class="form-container">
            <h2><?= $trainer ? 'Edit Trainer' : 'Add New Trainer' ?></h2>
            <form action="manage_trainers.php" method="POST" enctype="multipart/form-data">
                <?php if ($trainer) { ?>
                    <input type="hidden" name="trainer_id" value="<?= $trainer['id'] ?>">
                <?php } ?>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" name="name" class="form-control" value="<?= $trainer ? htmlspecialchars($trainer['name']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="specialty">Specialty:</label>
                    <input type="text" name="specialty" class="form-control" value="<?= $trainer ? htmlspecialchars($trainer['specialty']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="image">Image:</label>
                    <input type="file" name="image" class="form-control-file" <?= $trainer ? '' : 'required' ?>>
                </div>
                <button type="submit" name="<?= $trainer ? 'update_trainer' : 'add_trainer' ?>" class="btn btn-primary"><?= $trainer ? 'Update Trainer' : 'Add Trainer' ?></button>
            </form>
        </div>

        <!-- Display Trainers -->
        <div class="trainers-list">
            <h3>Existing Trainers</h3>
            <?php while ($trainer = mysqli_fetch_assoc($trainers)) { ?>
                <div class="trainer-item">
                    <div>
                        <strong><?= htmlspecialchars($trainer['name']) ?></strong><br>
                        <small><?= htmlspecialchars($trainer['specialty']) ?></small>
                    </div>
                    <div>
                        <a href="manage_trainers.php?edit=<?= $trainer['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="manage_trainers.php?delete=<?= $trainer['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this trainer?');">Delete</a>
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