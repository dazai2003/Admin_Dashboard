<?php
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No package ID provided.']);
    exit;
}

$packageId = intval($_GET['id']);

// Database connection
$connection = new mysqli('localhost', 'username', 'password', 'database');

if ($connection->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $connection->connect_error]);
    exit;
}

$query = "SELECT * FROM packages WHERE id = $packageId";
$result = $connection->query($query);

if ($result->num_rows > 0) {
    $package = $result->fetch_assoc();
    echo json_encode($package);
} else {
    echo json_encode(['error' => 'Package not found.']);
}

$connection->close();
?> 