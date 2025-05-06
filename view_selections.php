<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch client class selections
$query = "SELECT ccs.id, u.name AS client_name, cl.name AS class_name, cl.description, cl.image, t.name AS trainer_name, p.name AS package_name
          FROM client_class_selections ccs
          JOIN users u ON ccs.client_id = u.id
          JOIN classes cl ON ccs.class_id = cl.id
          JOIN client_selections cs ON ccs.client_id = cs.client_id
          JOIN trainers t ON cs.trainer_id = t.id
          JOIN packages p ON cs.package_id = p.id
          WHERE ccs.id IN (
              SELECT MAX(id) FROM client_class_selections GROUP BY client_id
          )";
$selections = mysqli_query($conn, $query);

// Check for query execution errors
if (!$selections) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Selections</title>
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
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        table {
            width: 100%;
            margin-bottom: 2rem;
            border-collapse: collapse;
            background-color: #EEEEEE;
            color: #393E46;
        }
        th, td {
            padding: 1rem;
            border: 1px solid #ddd;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #00ADB5;
            color: #EEEEEE;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1>View Selections</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </header>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client Name</th>
                    <th>Class Name</th>
                    <th>Trainer</th>
                    <th>Package</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($selection = mysqli_fetch_assoc($selections)) { ?>
                    <tr>
                        <td><?= $selection['id'] ?></td>
                        <td><?= htmlspecialchars($selection['client_name']) ?></td>
                        <td><?= htmlspecialchars($selection['class_name']) ?></td>
                        <td><?= htmlspecialchars($selection['trainer_name']) ?></td>
                        <td><?= htmlspecialchars($selection['package_name']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 