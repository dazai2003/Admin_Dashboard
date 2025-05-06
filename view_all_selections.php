<?php
include '../includes/db.php';

// Fetch all client selections
$query = "SELECT u.name AS client_name, 
                 t.name AS trainer_name, 
                 p.name AS package_name, 
                 cl.name AS class_name
          FROM client_selections cs
          JOIN users u ON cs.client_id = u.id
          JOIN trainers t ON cs.trainer_id = t.id
          JOIN packages p ON cs.package_id = p.id
          LEFT JOIN client_class_selections ccs ON ccs.client_id = u.id
          LEFT JOIN classes cl ON ccs.class_id = cl.id";
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
    <title>View All Selections</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .content {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>All Client Selections</h1>
        <button onclick="window.location.href='index.php'" class="btn btn-secondary mb-3">Back to Dashboard</button>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Trainer Name</th>
                    <th>Package Name</th>
                    <th>Class Name</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($selection = mysqli_fetch_assoc($selections)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($selection['client_name']) ?></td>
                        <td><?= htmlspecialchars($selection['trainer_name']) ?></td>
                        <td><?= htmlspecialchars($selection['package_name']) ?></td>
                        <td><?= htmlspecialchars($selection['class_name']) ?></td>
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