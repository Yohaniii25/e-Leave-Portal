<?php
require '../includes/dbconfig.php';
$query = trim($_POST['query'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT ID, CONCAT(first_name, ' ', last_name) AS name 
    FROM wp_pradeshiya_sabha_users 
    WHERE CONCAT(first_name, ' ', last_name) LIKE ? 
       OR ID LIKE ?
    LIMIT 10
");
$like = "%$query%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
echo json_encode($users);
?>