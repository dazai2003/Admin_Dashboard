<?php
// admin/index.php
// Admin dashboard placeholder

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

// Fetch blogs
$blogs = mysqli_query($conn, "SELECT * FROM blogs ORDER BY created_at DESC");
if (!$blogs) {
    die("Error fetching blogs: " . mysqli_error($conn));
}

$trainers = mysqli_query($conn, "SELECT * FROM trainers");
if (!$trainers) {
    die("Error fetching trainers: " . mysqli_error($conn));
}

// Fetch most popular class
$popular_class_query = "SELECT cl.name, COUNT(ccs.class_id) as count FROM client_class_selections ccs JOIN classes cl ON ccs.class_id = cl.id GROUP BY ccs.class_id ORDER BY count DESC LIMIT 1";
$popular_class_result = mysqli_query($conn, $popular_class_query);
$popular_class = mysqli_fetch_assoc($popular_class_result);

// Fetch most popular trainer
$popular_trainer_query = "SELECT t.name, COUNT(cs.trainer_id) as count FROM client_selections cs JOIN trainers t ON cs.trainer_id = t.id GROUP BY cs.trainer_id ORDER BY count DESC LIMIT 1";
$popular_trainer_result = mysqli_query($conn, $popular_trainer_query);
$popular_trainer = mysqli_fetch_assoc($popular_trainer_result);

// Fetch most popular package
$popular_package_query = "SELECT p.name, COUNT(cs.package_id) as count FROM client_selections cs JOIN packages p ON cs.package_id = p.id GROUP BY cs.package_id ORDER BY count DESC LIMIT 1";
$popular_package_result = mysqli_query($conn, $popular_package_query);
$popular_package = mysqli_fetch_assoc($popular_package_result);

// Fetch number of clients
$client_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'client'";
$client_count_result = mysqli_query($conn, $client_count_query);
$client_count = mysqli_fetch_assoc($client_count_result)['count'];

// Fetch number of trainers
$trainer_count_query = "SELECT COUNT(*) as count FROM trainers";
$trainer_count_result = mysqli_query($conn, $trainer_count_query);
$trainer_count = mysqli_fetch_assoc($trainer_count_result)['count'];

// Fetch top trainer
$top_trainer_query = "SELECT t.name, COUNT(cs.trainer_id) as count FROM client_selections cs JOIN trainers t ON cs.trainer_id = t.id GROUP BY cs.trainer_id ORDER BY count DESC LIMIT 1";
$top_trainer_result = mysqli_query($conn, $top_trainer_query);
$top_trainer = mysqli_fetch_assoc($top_trainer_result)['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        }
        header img {
            height: 50px;
            margin-right: 15px;
        }
        header h1 {
            font-size: 2rem;
            margin: 0;
        }
        .card-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .dashboard-card {
            flex: 1;
            margin: 0 0.5rem;
            background: #00ADB5;
            color: #EEEEEE;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            background: #222831;
        }
        .button-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }
        .btn {
            background-color: #00ADB5;
            color: #EEEEEE;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            padding: 0.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 70px;
        }
        .btn:hover {
            background-color: #222831;
            transform: translateY(-2px);
        }
        .btn i {
            font-size: 1.2rem;
            margin-right: 5px;
        }
        .content {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .icon-card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .icon-card {
            background: #EEEEEE;
            color: #393E46;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
        }
        .icon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            background: #00ADB5;
            color: #EEEEEE;
        }
        .icon-card i {
            font-size: 2.5rem;
            color: #007bff;
            margin-bottom: 0.5rem;
        }
        .icon-card h4 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
        }
        .welcome {
            font-size: 1.5rem;
            font-weight: 600;
            color: #007bff;
        }
        .date {
            font-size: 1.2rem;
            color: #555;
        }
        .logout-btn {
            background-color: #222831;
            color: #EEEEEE;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            display: inline-block;
            margin-left: 1rem;
        }
        .logout-btn:hover {
            background-color: #393E46;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <img src="../assets/images/logo.png" alt="Gym Logo">
                <h1>FitZone Gym</h1>
            </div>
            <div class="d-flex align-items-center">
                <span class="mr-2">Admin</span>
                <i class="fas fa-user-circle fa-2x"></i>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="welcome">Welcome, Admin!</h2>
            <div class="date"><?= date('l, F j, Y') ?></div>
        </div>

        <div class="card-grid mb-4">
            <div class="dashboard-card" style="background-color:rgb(85, 71, 12);">
                <h3>No of Clients</h3>
                <p><?= $client_count ?></p>
            </div>
            <div class="dashboard-card" style="background-color: rgb(85, 71, 12);">
                <h3>No of Trainers</h3>
                <p><?= $trainer_count ?></p>
            </div>
            <div class="dashboard-card" style="background-color: rgb(85, 71, 12);">
                <h3>Top Trainer</h3>
                <p><?= htmlspecialchars($top_trainer) ?></p>
            </div>
        </div>

        <div class="icon-card-grid">
            <a href="manage_classes.php" class="icon-card">
                <i class="fas fa-chalkboard-teacher"></i>
                <h4>Manage Classes</h4>
            </a>
            <a href="manage_blogs.php" class="icon-card">
                <i class="fas fa-blog"></i>
                <h4>Manage Blogs</h4>
            </a>
            <a href="manage_trainers.php" class="icon-card">
                <i class="fas fa-user-tie"></i>
                <h4>Manage Trainers</h4>
            </a>
            <a href="manage_gallery.php" class="icon-card">
                <i class="fas fa-images"></i>
                <h4>Manage Gallery</h4>
            </a>
            <a href="manage_packages.php" class="icon-card">
                <i class="fas fa-box"></i>
                <h4>Manage Packages</h4>
            </a>
            <a href="view_selections.php" class="icon-card">
                <i class="fas fa-eye"></i>
                <h4>View Selections</h4>
            </a>
        </div>

        <div class="content">
            <!-- Remove the PHP include for view_selections.php -->
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

