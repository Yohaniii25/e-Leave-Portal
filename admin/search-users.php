<?php
require '../includes/dbconfig.php';

if (!isset($_POST['query'])) {
    echo json_encode([]);
    exit();
}

$query = $_POST['query'];
$stmt = $conn->prepare("SELECT ID, first_name, last_name FROM wp_pradeshiya_sabha_users WHERE first_name LIKE CONCAT('%', ?, '%') OR last_name LIKE CONCAT('%', ?, '%') LIMIT 10");
$stmt->bind_param("ss", $query, $query);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        'ID' => $row['ID'],
        'name' => $row['first_name'] . ' ' . $row['last_name']
    ];
}

echo json_encode($users);
