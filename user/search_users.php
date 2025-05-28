<?php
require '../includes/dbconfig.php';

if (isset($_GET['term'])) {
    $term = $_GET['term'];
    
    $stmt = $conn->prepare("SELECT id, first_name, last_name, service_number FROM wp_pradeshiya_sabha_users WHERE CONCAT(first_name, ' ', last_name) LIKE ? OR service_number LIKE ?");
    $searchTerm = "%$term%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'label' => $row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['service_number'] . ')',
            'value' => $row['first_name'] . ' ' . $row['last_name'],
            'service_number' => $row['service_number'],
            'id' => $row['id']
        ];
    }

    echo json_encode($suggestions);
}
?>
